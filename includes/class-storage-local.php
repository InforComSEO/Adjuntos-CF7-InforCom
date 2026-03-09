<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gestiona el almacenamiento local de archivos
 * en /wp-content/uploads/carpeta/
 */
class ACF7I_Storage_Local {

    private $settings = array();

    public function __construct( $settings = array() ) {
        $this->settings = ! empty( $settings )
            ? $settings
            : get_option( 'acf7i_settings', array() );
    }

    /**
     * Mueve un archivo desde temp a su destino final
     *
     * @param string $temp_path    Ruta temporal del archivo
     * @param string $original_name Nombre original del archivo
     * @param int    $form_id      ID del formulario CF7
     * @param array  $settings     Configuración activa
     * @return array { success, path, url, filename, message }
     */
    public function store( $temp_path, $original_name, $form_id = 0, $settings = array() ) {

        if ( ! empty( $settings ) ) {
            $this->settings = $settings;
        }

        // Verificar que el archivo temporal existe
        if ( ! file_exists( $temp_path ) ) {
            return array(
                'success' => false,
                'message' => 'Archivo temporal no encontrado: ' . $temp_path,
            );
        }

        // Obtener el directorio destino
        $dest_dir = $this->get_destination_dir( $form_id );

        // Crear el directorio si no existe
        $dir_result = $this->ensure_directory( $dest_dir );
        if ( ! $dir_result['success'] ) {
            return $dir_result;
        }

        // Generar nombre final del archivo
        $handler  = new ACF7I_Upload_Handler();
        $filename = $handler->generate_filename( $original_name, $this->settings );

        // Asegurar nombre único en el destino
        $filename = $this->ensure_unique_filename( $dest_dir, $filename );

        $dest_path = $dest_dir . $filename;
        $dest_url  = $this->path_to_url( $dest_path );

        // Mover archivo (no copiar, para liberar espacio)
        if ( ! rename( $temp_path, $dest_path ) ) {
            // Intentar con copy + unlink como fallback
            if ( ! copy( $temp_path, $dest_path ) ) {
                return array(
                    'success' => false,
                    'message' => 'Error al mover el archivo a su destino final.',
                );
            }
            @unlink( $temp_path );
        }

        // Establecer permisos seguros
        chmod( $dest_path, 0644 );

        return array(
            'success'  => true,
            'path'     => $dest_path,
            'url'      => $dest_url,
            'filename' => $filename,
            'message'  => 'Archivo guardado correctamente.',
        );
    }

    /**
     * Calcula el directorio de destino según la configuración
     */
    public function get_destination_dir( $form_id = 0 ) {
        $base_folder = sanitize_file_name(
            $this->settings['local_folder'] ?? 'cf7-adjuntos'
        );
        $dir = WP_CONTENT_DIR . '/uploads/' . $base_folder . '/';

        // Subcarpeta por formulario
        if ( ! empty( $this->settings['subfolder_by_form'] ) && $form_id > 0 ) {
            $form_folder = $this->get_form_folder_name( $form_id );
            if ( $form_folder ) {
                $dir .= $form_folder . '/';
            }
        }

        // Subcarpeta por fecha
        if ( ! empty( $this->settings['subfolder_by_date'] ) ) {
            $dir .= current_time( 'Y' ) . '/' . current_time( 'm' ) . '/';
        }

        return $dir;
    }

    /**
     * Obtiene la URL pública correspondiente a un directorio
     */
    public function get_destination_url( $form_id = 0 ) {
        $base_folder = sanitize_file_name(
            $this->settings['local_folder'] ?? 'cf7-adjuntos'
        );
        $url = WP_CONTENT_URL . '/uploads/' . $base_folder . '/';

        if ( ! empty( $this->settings['subfolder_by_form'] ) && $form_id > 0 ) {
            $form_folder = $this->get_form_folder_name( $form_id );
            if ( $form_folder ) {
                $url .= $form_folder . '/';
            }
        }

        if ( ! empty( $this->settings['subfolder_by_date'] ) ) {
            $url .= current_time( 'Y' ) . '/' . current_time( 'm' ) . '/';
        }

        return $url;
    }

    /**
     * Determina el nombre de la subcarpeta del formulario
     */
    private function get_form_folder_name( $form_id ) {
        $mode = $this->settings['subfolder_form_name'] ?? 'title';

        switch ( $mode ) {
            case 'id':
                return 'form-' . $form_id;

            case 'custom':
                // Buscar nombre personalizado en la config del formulario
                $form_settings = get_post_meta( $form_id, '_acf7i_form_settings', true );
                if ( ! empty( $form_settings['custom_folder_name'] ) ) {
                    return sanitize_file_name( $form_settings['custom_folder_name'] );
                }
                // Fallback al título
                // Continúa al case 'title'

            case 'title':
            default:
                $form = get_post( $form_id );
                if ( $form ) {
                    return sanitize_file_name(
                        strtolower( str_replace( ' ', '-', $form->post_title ) )
                    );
                }
                return 'form-' . $form_id;
        }
    }

    /**
     * Crea el directorio y lo protege
     */
    private function ensure_directory( $dir ) {
        if ( file_exists( $dir ) ) {
            if ( ! is_writable( $dir ) ) {
                return array(
                    'success' => false,
                    'message' => 'Sin permisos de escritura en: ' . $dir,
                );
            }
            return array( 'success' => true );
        }

        if ( ! wp_mkdir_p( $dir ) ) {
            return array(
                'success' => false,
                'message' => 'No se pudo crear el directorio: ' . $dir,
            );
        }

        // Proteger con .htaccess
        $htaccess = $dir . '.htaccess';
        if ( ! file_exists( $htaccess ) ) {
            file_put_contents( $htaccess,
                "Options -Indexes\n" .
                "<FilesMatch \"\.(php|php3|php4|php5|phtml|phar|pl|py|jsp|asp|sh|bat|exe)$\">\n" .
                "    Order allow,deny\n" .
                "    Deny from all\n" .
                "</FilesMatch>\n"
            );
        }

        // index.php silencioso
        $index = $dir . 'index.php';
        if ( ! file_exists( $index ) ) {
            file_put_contents( $index, '<?php // Silence is golden.' );
        }

        return array( 'success' => true );
    }

    /**
     * Asegura que el nombre del archivo sea único en el directorio
     */
    private function ensure_unique_filename( $dir, $filename ) {
        if ( ! file_exists( $dir . $filename ) ) {
            return $filename;
        }

        $ext  = pathinfo( $filename, PATHINFO_EXTENSION );
        $base = pathinfo( $filename, PATHINFO_FILENAME );
        $i    = 1;

        while ( file_exists( $dir . $base . '-' . $i . '.' . $ext ) ) {
            $i++;
        }

        return $base . '-' . $i . '.' . $ext;
    }

    /**
     * Convierte una ruta de servidor a URL pública
     */
    public function path_to_url( $path ) {
        $wp_content_dir = WP_CONTENT_DIR;
        $wp_content_url = WP_CONTENT_URL;
        return str_replace( $wp_content_dir, $wp_content_url, $path );
    }

    /**
     * Elimina un archivo del almacenamiento local
     */
    public function delete( $path ) {
        // Seguridad: solo eliminar dentro de nuestra carpeta
        $base_folder = sanitize_file_name(
            $this->settings['local_folder'] ?? 'cf7-adjuntos'
        );
        $allowed_base = WP_CONTENT_DIR . '/uploads/' . $base_folder . '/';
        $real_path    = realpath( $path );
        $real_base    = realpath( $allowed_base );

        if ( ! $real_path || ! $real_base ) {
            return false;
        }

        if ( strpos( $real_path, $real_base ) !== 0 ) {
            return false; // Fuera de la carpeta permitida
        }

        if ( file_exists( $real_path ) ) {
            return unlink( $real_path );
        }

        return false;
    }

    /**
     * Calcula el espacio total usado por la carpeta del plugin
     */
    public function get_used_space() {
        $base = WP_CONTENT_DIR . '/uploads/' . ( $this->settings['local_folder'] ?? 'cf7-adjuntos' ) . '/';
        if ( ! file_exists( $base ) ) return 0;

        $size = 0;
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $base, RecursiveDirectoryIterator::SKIP_DOTS )
        );
        foreach ( $iter as $file ) {
            if ( $file->isFile() ) $size += $file->getSize();
        }
        return $size;
    }
}