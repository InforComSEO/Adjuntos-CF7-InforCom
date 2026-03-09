<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Página de limpieza y herramientas del panel de administración
 */
class ACF7I_Cleanup_Page {

    public function render() {
        require_once ACF7I_PLUGIN_DIR . 'admin/views/cleanup.php';
    }

    /**
     * Cuenta archivos temporales en la carpeta temp
     */
    public static function count_temp_files( $dir ) {
        if ( ! file_exists( $dir ) ) return 0;

        $files    = glob( $dir . '*' );
        $excluded = array( '.htaccess', 'index.php' );
        $count    = 0;

        foreach ( $files as $file ) {
            if ( is_file( $file ) && ! in_array( basename( $file ), $excluded ) ) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Calcula el tamaño total de la carpeta temp
     */
    public static function get_temp_size( $dir ) {
        if ( ! file_exists( $dir ) ) return 0;

        $files    = glob( $dir . '*' );
        $excluded = array( '.htaccess', 'index.php' );
        $size     = 0;

        foreach ( $files as $file ) {
            if ( is_file( $file ) && ! in_array( basename( $file ), $excluded ) ) {
                $size += filesize( $file );
            }
        }
        return $size;
    }
}