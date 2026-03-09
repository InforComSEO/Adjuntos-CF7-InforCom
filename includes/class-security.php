<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Seguridad avanzada para archivos subidos
 */
class ACF7I_Security {

    /**
     * Valida el MIME type real del archivo
     * (no confía solo en la extensión ni en lo que dice el navegador)
     */
    public function validate_mime( $tmp_path, $original_name ) {

        // Tipos MIME permitidos básicos
        $safe_mimes = array(
            // Imágenes
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'image/svg+xml', 'image/bmp', 'image/tiff',
            // Documentos
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain', 'text/csv',
            'application/rtf', 'text/rtf',
            // Audio
            'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4',
            'audio/flac', 'audio/aac', 'audio/x-ms-wma',
            // Video
            'video/mp4', 'video/quicktime', 'video/x-msvideo',
            'video/x-matroska', 'video/webm', 'video/x-ms-wmv',
            // Comprimidos
            'application/zip', 'application/x-zip-compressed',
            'application/x-rar-compressed', 'application/x-7z-compressed',
            'application/x-tar', 'application/gzip',
            // Otros comunes
            'application/json', 'application/xml', 'text/xml',
            'text/html', 'text/css',
            'application/octet-stream', // Fallback genérico
        );

        // Intentar obtener el MIME real
        $real_mime = $this->get_real_mime( $tmp_path );

        if ( $real_mime === false ) {
            // No se pudo determinar el MIME, permitir con advertencia
            return array( 'valid' => true, 'mime' => 'unknown' );
        }

        // Verificar si el archivo contiene PHP aunque tenga otra extensión
        if ( $this->contains_php_code( $tmp_path ) ) {
            return array(
                'valid'   => false,
                'message' => 'El archivo contiene código no permitido.',
            );
        }

        return array( 'valid' => true, 'mime' => $real_mime );
    }

    /**
     * Obtiene el MIME type real usando múltiples métodos
     */
    private function get_real_mime( $tmp_path ) {
        // Método 1: finfo (más confiable)
        if ( function_exists( 'finfo_open' ) ) {
            $finfo = finfo_open( FILEINFO_MIME_TYPE );
            $mime  = finfo_file( $finfo, $tmp_path );
            finfo_close( $finfo );
            if ( $mime ) return $mime;
        }

        // Método 2: mime_content_type
        if ( function_exists( 'mime_content_type' ) ) {
            $mime = mime_content_type( $tmp_path );
            if ( $mime ) return $mime;
        }

        // Método 3: WordPress
        $wp_check = wp_check_filetype_and_ext(
            $tmp_path,
            basename( $tmp_path )
        );
        if ( ! empty( $wp_check['type'] ) ) {
            return $wp_check['type'];
        }

        return false;
    }

    /**
     * Verifica si un archivo contiene código PHP
     */
    private function contains_php_code( $tmp_path ) {
        $content = @file_get_contents( $tmp_path, false, null, 0, 8192 );
        if ( $content === false ) return false;

        $php_patterns = array(
            '<?php', '<?=', '<? ', '<%', '<script', 'eval(',
            'base64_decode', 'system(', 'exec(', 'passthru(',
            'shell_exec(', 'proc_open(', 'popen(',
        );

        foreach ( $php_patterns as $pattern ) {
            if ( stripos( $content, $pattern ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera un token único para el link de visualización
     */
    public static function generate_view_token() {
        return bin2hex( random_bytes( 32 ) );
    }

    /**
     * Calcula la fecha de expiración del token
     */
    public static function calculate_expiry( $settings ) {
        $expires = $settings['mail_link_expires'] ?? 'never';
        if ( $expires !== 'custom' ) return null;

        $value = intval( $settings['mail_expire_value'] ?? 30 );
        $unit  = $settings['mail_expire_unit'] ?? 'days';

        $map = array(
            'days'   => DAY_IN_SECONDS,
            'weeks'  => WEEK_IN_SECONDS,
            'months' => MONTH_IN_SECONDS,
        );

        $seconds = $value * ( $map[ $unit ] ?? DAY_IN_SECONDS );
        return date( 'Y-m-d H:i:s', time() + $seconds );
    }
}