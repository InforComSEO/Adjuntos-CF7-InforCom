<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gestiona el almacenamiento en Google Drive
 * Usando Service Account (JSON credentials)
 */
class ACF7I_Storage_Drive {

    private $credentials = null;
    private $access_token = null;
    private $settings = array();

    // Google Drive API endpoints
    const TOKEN_URL   = 'https://oauth2.googleapis.com/token';
    const UPLOAD_URL  = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart';
    const FILES_URL   = 'https://www.googleapis.com/drive/v3/files';
    const SHARE_URL   = 'https://www.googleapis.com/drive/v3/files/{fileId}/permissions';

    public function __construct( $settings = array() ) {
        $this->settings    = ! empty( $settings )
            ? $settings
            : get_option( 'acf7i_settings', array() );
        $this->credentials = get_option( 'acf7i_drive_credentials', null );
    }

    /**
     * Verifica si Drive está configurado y conectado
     */
    public function is_connected() {
        return ! empty( $this->credentials )
            && isset( $this->credentials['client_email'] )
            && isset( $this->credentials['private_key'] );
    }

    /**
     * Sube un archivo a Google Drive
     *
     * @param string $file_path     Ruta local del archivo
     * @param string $filename      Nombre del archivo en Drive
     * @param int    $form_id       ID del formulario
     * @return array { success, drive_id, drive_url, message }
     */
    public function store( $file_path, $filename, $form_id = 0 ) {

        if ( ! $this->is_connected() ) {
            return array(
                'success' => false,
                'message' => 'Google Drive no está configurado.',
            );
        }

        if ( ! file_exists( $file_path ) ) {
            return array(
                'success' => false,
                'message' => 'Archivo no encontrado: ' . $file_path,
            );
        }

        // Obtener token de acceso
        $token_result = $this->get_access_token();
        if ( ! $token_result['success'] ) {
            return $token_result;
        }

        // Obtener/crear carpeta de destino en Drive
        $folder_id = $this->get_or_create_folder( $form_id );
        if ( ! $folder_id ) {
            return array(
                'success' => false,
                'message' => 'Error al acceder a la carpeta en Google Drive.',
            );
        }

        // Subir el archivo
        return $this->upload_file( $file_path, $filename, $folder_id );
    }

    /**
     * Obtiene un token de acceso usando JWT (Service Account)
     */
    private function get_access_token() {

        // Verificar si tenemos un token válido en caché
        $cached = get_transient( 'acf7i_drive_token' );
        if ( $cached ) {
            $this->access_token = $cached;
            return array( 'success' => true );
        }

        // Crear JWT
        $jwt = $this->create_jwt();
        if ( ! $jwt ) {
            return array(
                'success' => false,
                'message' => 'Error al generar el JWT para Google Drive.',
            );
        }

        // Solicitar token
        $response = wp_remote_post( self::TOKEN_URL, array(
            'timeout' => 30,
            'body'    => array(
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ),
        ));

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => 'Error de conexión con Google: ' . $response->get_error_message(),
            );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['access_token'] ) ) {
            return array(
                'success' => false,
                'message' => 'No se pudo obtener el token de Google Drive: ' . ( $body['error_description'] ?? 'Error desconocido' ),
            );
        }

        // Cachear el token (expira en 1 hora, cacheamos por 55 min)
        set_transient( 'acf7i_drive_token', $body['access_token'], 55 * MINUTE_IN_SECONDS );
        $this->access_token = $body['access_token'];

        return array( 'success' => true );
    }

    /**
     * Crea el JWT para autenticación con Service Account
     */
    private function create_jwt() {
        if ( ! $this->credentials ) return false;

        $now = time();

        // Header
        $header = base64_encode( json_encode( array(
            'alg' => 'RS256',
            'typ' => 'JWT',
        )));
        $header = str_replace( array('+','/','='), array('-','_',''), $header );

        // Payload (Claims)
        $payload = base64_encode( json_encode( array(
            'iss'   => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive',
            'aud'   => self::TOKEN_URL,
            'exp'   => $now + 3600,
            'iat'   => $now,
        )));
        $payload = str_replace( array('+','/','='), array('-','_',''), $payload );

        // Firma
        $sign_input  = $header . '.' . $payload;
        $private_key = $this->credentials['private_key'];

        $signature = '';
        if ( ! openssl_sign( $sign_input, $signature, $private_key, 'SHA256' ) ) {
            return false;
        }

        $signature = base64_encode( $signature );
        $signature = str_replace( array('+','/','='), array('-','_',''), $signature );

        return $sign_input . '.' . $signature;
    }

    /**
     * Sube el archivo a Drive usando multipart upload
     */
    private function upload_file( $file_path, $filename, $folder_id ) {

        $file_content = file_get_contents( $file_path );
        if ( $file_content === false ) {
            return array( 'success' => false, 'message' => 'Error al leer el archivo.' );
        }

        $mime_type = $this->get_mime_type( $file_path );
        $boundary  = '-------' . wp_generate_password( 16, false );

        // Metadata del archivo
        $metadata = json_encode( array(
            'name'    => $filename,
            'parents' => array( $folder_id ),
        ));

        // Construir cuerpo multipart
        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= $metadata . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$mime_type}\r\n\r\n";
        $body .= $file_content . "\r\n";
        $body .= "--{$boundary}--";

        $response = wp_remote_post( self::UPLOAD_URL, array(
            'timeout' => 120,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type'  => 'multipart/related; boundary=' . $boundary,
            ),
            'body' => $body,
        ));

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => 'Error al subir a Drive: ' . $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            return array(
                'success' => false,
                'message' => 'Drive API Error: ' . ( $data['error']['message'] ?? 'Error desconocido' ),
            );
        }

        $file_id  = $data['id'];

        // Hacer el archivo accesible (solo lectura) para quien tenga el link
        $this->set_file_permissions( $file_id );

        $drive_url = 'https://drive.google.com/file/d/' . $file_id . '/view?usp=sharing';

        return array(
            'success'   => true,
            'drive_id'  => $file_id,
            'drive_url' => $drive_url,
            'message'   => 'Archivo subido a Google Drive correctamente.',
        );
    }

    /**
     * Establece permisos de solo lectura con link
     */
    private function set_file_permissions( $file_id ) {
        $url = str_replace( '{fileId}', $file_id, self::SHARE_URL );

        wp_remote_post( $url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode( array(
                'role' => 'reader',
                'type' => 'anyone',
            )),
        ));
    }

    /**
     * Obtiene o crea la carpeta de destino en Drive
     */
    private function get_or_create_folder( $form_id = 0 ) {

        // Carpeta raíz configurada
        $root_folder_id = $this->settings['drive_folder_id'] ?? '';

        if ( empty( $root_folder_id ) ) {
            return 'root'; // Carpeta raíz de Drive
        }

        // Sin subcarpetas
        if ( empty( $this->settings['drive_subfolder_by_form'] ) ) {
            return $root_folder_id;
        }

        // Subcarpeta por formulario
        if ( $form_id > 0 ) {
            $form  = get_post( $form_id );
            $name  = $form ? sanitize_text_field( $form->post_title ) : 'form-' . $form_id;

            // Buscar si ya existe la carpeta
            $existing = $this->find_folder( $name, $root_folder_id );
            if ( $existing ) return $existing;

            // Crear la carpeta
            return $this->create_folder( $name, $root_folder_id );
        }

        return $root_folder_id;
    }

    /**
     * Busca una carpeta en Drive por nombre y padre
     */
    private function find_folder( $name, $parent_id ) {
        $query    = urlencode( "name='{$name}' and mimeType='application/vnd.google-apps.folder' and '{$parent_id}' in parents and trashed=false" );
        $url      = self::FILES_URL . '?q=' . $query . '&fields=files(id,name)';

        $response = wp_remote_get( $url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
            ),
        ));

        if ( is_wp_error( $response ) ) return null;

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        return $data['files'][0]['id'] ?? null;
    }

    /**
     * Crea una carpeta en Drive
     */
    private function create_folder( $name, $parent_id ) {
        $response = wp_remote_post( self::FILES_URL, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode( array(
                'name'     => $name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents'  => array( $parent_id ),
            )),
        ));

        if ( is_wp_error( $response ) ) return null;

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        return $data['id'] ?? null;
    }

    /**
     * Elimina un archivo de Drive
     */
    public function delete( $file_id ) {
        if ( ! $this->is_connected() || ! $file_id ) return false;

        $token_result = $this->get_access_token();
        if ( ! $token_result['success'] ) return false;

        $response = wp_remote_request(
            self::FILES_URL . '/' . $file_id,
            array(
                'method'  => 'DELETE',
                'timeout' => 15,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->access_token,
                ),
            )
        );

        return ! is_wp_error( $response ) &&
               wp_remote_retrieve_response_code( $response ) === 204;
    }

    /**
     * Obtiene el MIME type de un archivo
     */
    private function get_mime_type( $path ) {
        if ( function_exists( 'finfo_open' ) ) {
            $finfo = finfo_open( FILEINFO_MIME_TYPE );
            $mime  = finfo_file( $finfo, $path );
            finfo_close( $finfo );
            if ( $mime ) return $mime;
        }
        return 'application/octet-stream';
    }

    /**
     * Guarda las credenciales del Service Account
     */
    public static function save_credentials( $json_content ) {
        $data = json_decode( $json_content, true );

        if ( ! $data || ! isset( $data['type'] ) || $data['type'] !== 'service_account' ) {
            return array(
                'success' => false,
                'message' => 'El archivo JSON no es válido o no es una Service Account.',
            );
        }

        $required = array( 'client_email', 'private_key', 'project_id' );
        foreach ( $required as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return array(
                    'success' => false,
                    'message' => "El archivo JSON no contiene el campo requerido: {$field}",
                );
            }
        }

        update_option( 'acf7i_drive_credentials', $data );
        delete_transient( 'acf7i_drive_token' );

        return array(
            'success' => true,
            'email'   => $data['client_email'],
            'project' => $data['project_id'],
        );
    }

    /**
     * Prueba la conexión con Drive
     */
    public function test_connection() {
        if ( ! $this->is_connected() ) {
            return array( 'success' => false, 'message' => 'No hay credenciales configuradas.' );
        }

        $token_result = $this->get_access_token();
        if ( ! $token_result['success'] ) return $token_result;

        // Listar archivos para verificar conexión
        $response = wp_remote_get( self::FILES_URL . '?pageSize=1&fields=files(id,name)', array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
            ),
        ));

        if ( is_wp_error( $response ) ) {
            return array( 'success' => false, 'message' => $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code === 200 ) {
            return array(
                'success' => true,
                'message' => 'Conexión con Google Drive exitosa.',
                'email'   => $this->credentials['client_email'],
            );
        }

        return array( 'success' => false, 'message' => 'Error al conectar con Google Drive (código: ' . $code . ')' );
    }
}