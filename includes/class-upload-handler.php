<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Maneja la subida de archivos via AJAX
 */
class ACF7I_Upload_Handler {

    /**
     * Procesa la subida de un archivo
     */
    public function handle_upload() {

        // Verificar nonce
        if ( ! check_ajax_referer( 'acf7i_upload_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => 'Nonce inválido.' ), 403 );
        }

        // Verificar que se recibió un archivo
        if ( empty( $_FILES['file'] ) ) {
            wp_send_json_error( array( 'message' => 'No se recibió ningún archivo.' ) );
        }

        $file       = $_FILES['file'];
        $field_name = sanitize_key( $_POST['field_name'] ?? 'adjuntos' );
        $session_id = sanitize_text_field( $_POST['session_id'] ?? '' );

        // Verificar errores de subida de PHP
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( array(
                'message' => $this->get_upload_error_message( $file['error'] ),
            ));
        }

        // Obtener configuración según el formulario actual
        // En este punto buscamos la config por field_name en todas las formas
        $settings = get_option( 'acf7i_settings', array() );

        // Seguridad: validar en el servidor
        $security = new ACF7I_Security();

        // 1. Validar tipo de archivo (MIME real)
        $mime_result = $security->validate_mime( $file['tmp_name'], $file['name'] );
        if ( ! $mime_result['valid'] ) {
            wp_send_json_error( array( 'message' => $mime_result['message'] ) );
        }

        // 2. Validar extensión contra lista permitida
        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        $allowed = array_merge(
            $settings['allowed_types'] ?? array( 'jpg','jpeg','png','pdf' ),
            $settings['custom_types']  ?? array()
        );
        $allowed = array_map( 'strtolower', $allowed );

        if ( ! in_array( $ext, $allowed, true ) ) {
            wp_send_json_error( array( 'message' => 'Tipo de archivo no permitido.' ) );
        }

        // 3. Validar tamaño
        $max_size  = intval( $settings['max_filesize']      ?? 5 );
        $max_unit  = $settings['max_filesize_unit']          ?? 'MB';
        $max_bytes = $this->to_bytes( $max_size, $max_unit );

        if ( $file['size'] > $max_bytes ) {
            wp_send_json_error( array(
                'message' => sprintf( 'El archivo supera el límite de %d%s', $max_size, $max_unit ),
            ));
        }

        // 4. Bloquear ejecutables peligrosos independientemente de la config
        $dangerous = array( 'php','php3','php4','php5','phtml','phar','pl','py','jsp','asp','sh','bat','cmd','exe','com','scr' );
        if ( in_array( $ext, $dangerous, true ) ) {
            wp_send_json_error( array( 'message' => 'Este tipo de archivo no está permitido por seguridad.' ) );
        }

        // 5. Guardar en carpeta temporal
        $temp_result = $this->save_to_temp( $file, $session_id, $settings );

        if ( ! $temp_result['success'] ) {
            wp_send_json_error( array( 'message' => $temp_result['message'] ) );
        }

        // Registrar en tabla temporal
        $this->register_temp_file( $temp_result, $session_id );

        wp_send_json_success( array(
            'temp_path'     => $temp_result['temp_path'],
            'temp_url'      => $temp_result['temp_url'],
            'original_name' => $file['name'],
            'filename'      => $temp_result['filename'],
            'filesize'      => $file['size'],
            'filetype'      => $ext,
        ));
    }

    /**
     * Elimina un archivo temporal
     */
    public function delete_temp() {
        if ( ! check_ajax_referer( 'acf7i_upload_nonce', 'nonce', false ) ) {
            wp_send_json_error();
        }

        $temp_path  = sanitize_text_field( $_POST['temp_path']  ?? '' );
        $session_id = sanitize_text_field( $_POST['session_id'] ?? '' );

        if ( empty( $temp_path ) ) {
            wp_send_json_error();
        }

        // Solo eliminar archivos dentro de nuestra carpeta temp
        $temp_dir   = ACF7I_UPLOAD_DIR . 'temp/';
        $real_path  = realpath( $temp_path );
        $real_temp  = realpath( $temp_dir );

        if ( $real_path && $real_temp && strpos( $real_path, $real_temp ) === 0 ) {
            if ( file_exists( $real_path ) ) {
                unlink( $real_path );
            }
            // Limpiar de la tabla temporal
            global $wpdb;
            $wpdb->delete(
                $wpdb->prefix . 'acf7i_temp',
                array(
                    'filepath'   => $temp_path,
                    'session_id' => $session_id,
                ),
                array( '%s', '%s' )
            );
        }

        wp_send_json_success();
    }

    /**
     * Guarda el archivo en la carpeta temporal
     */
    private function save_to_temp( $file, $session_id, $settings ) {
        $temp_dir = ACF7I_UPLOAD_DIR . 'temp/';
        $temp_url = ACF7I_UPLOAD_URL . 'temp/';

        // Crear carpeta temp si no existe
        if ( ! file_exists( $temp_dir ) ) {
            wp_mkdir_p( $temp_dir );
            file_put_contents( $temp_dir . '.htaccess',
                "Options -Indexes\nDeny from all\n"
            );
            file_put_contents( $temp_dir . 'index.php',
                '<?php // Silence is golden.'
            );
        }

        // Generar nombre del archivo
        $original_name = sanitize_file_name( $file['name'] );
        $filename      = $this->generate_filename( $original_name, $settings );
        $temp_path     = $temp_dir . $filename;
        $temp_url_full = $temp_url . $filename;

        // Mover archivo subido
        if ( ! move_uploaded_file( $file['tmp_name'], $temp_path ) ) {
            return array(
                'success' => false,
                'message' => 'Error al guardar el archivo temporalmente.',
            );
        }

        return array(
            'success'   => true,
            'temp_path' => $temp_path,
            'temp_url'  => $temp_url_full,
            'filename'  => $filename,
        );
    }

    /**
     * Genera el nombre del archivo según la configuración
     */
    public function generate_filename( $original_name, $settings ) {
        $ext  = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );
        $base = pathinfo( $original_name, PATHINFO_FILENAME );

        // Aplicar configuración de espacios
        $spaces = $settings['filename_spaces'] ?? 'keep';
        if ( $spaces === 'dash' ) {
            $base = str_replace( ' ', '-', $base );
        } elseif ( $spaces === 'underscore' ) {
            $base = str_replace( ' ', '_', $base );
        }

        // Prefijo y sufijo
        $prefix    = sanitize_text_field( $settings['filename_prefix'] ?? '' );
        $suffix    = sanitize_text_field( $settings['filename_suffix'] ?? '' );
        $separator = $settings['filename_separator'] ?? ' ';

        // Fecha y hora
        $date_format = $settings['filename_date_format'] ?? 'dmY';
        $time_format = $settings['filename_time_format'] ?? 'Hi';
        $date_str    = current_time( $date_format );
        $time_str    = current_time( $time_format );

        // Construir nombre
        $filename = $prefix . $base . $separator . $date_str . $separator . $time_str . $suffix . '.' . $ext;

        // Sanitizar pero mantener caracteres básicos
        $filename = preg_replace( '/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-\_\.\(\)]/u', '', $filename );
        $filename = trim( $filename );

        // Asegurar que sea único
        $counter  = 0;
        $base_name = $filename;
        while ( file_exists( ACF7I_UPLOAD_DIR . 'temp/' . $filename ) ) {
            $counter++;
            $filename = pathinfo( $base_name, PATHINFO_FILENAME ) . '-' . $counter . '.' . $ext;
        }

        return $filename;
    }

    /**
     * Registra el archivo temporal en la base de datos
     */
    private function register_temp_file( $result, $session_id ) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'acf7i_temp',
            array(
                'session_id' => $session_id,
                'filename'   => $result['filename'],
                'filepath'   => $result['temp_path'],
                'filesize'   => filesize( $result['temp_path'] ),
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%d', '%s' )
        );
    }

    /**
     * Convierte valor + unidad a bytes
     */
    private function to_bytes( $value, $unit ) {
        $units = array( 'KB' => 1024, 'MB' => 1024 * 1024, 'GB' => 1024 * 1024 * 1024 );
        return $value * ( $units[ strtoupper( $unit ) ] ?? $units['MB'] );
    }

    /**
     * Mensajes de error de subida de PHP
     */
    private function get_upload_error_message( $code ) {
        $messages = array(
            UPLOAD_ERR_INI_SIZE   => 'El archivo supera el límite del servidor (upload_max_filesize).',
            UPLOAD_ERR_FORM_SIZE  => 'El archivo supera el límite del formulario.',
            UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente.',
            UPLOAD_ERR_NO_FILE    => 'No se recibió ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'No se encontró la carpeta temporal del servidor.',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco.',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida.',
        );
        return $messages[ $code ] ?? 'Error desconocido al subir el archivo.';
    }
}