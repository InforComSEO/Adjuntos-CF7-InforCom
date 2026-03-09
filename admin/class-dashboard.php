<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ACF7I_Dashboard {

    public function render() {
        $log      = new ACF7I_Log();
        $stats    = $log->get_stats();
        $settings = get_option( 'acf7i_settings', array() );
        require_once ACF7I_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Formatea bytes a unidad legible
     */
    public static function format_bytes( $bytes ) {
        if ( $bytes <= 0 ) return '0 B';
        if ( $bytes >= 1073741824 ) return round( $bytes / 1073741824, 2 ) . ' GB';
        if ( $bytes >= 1048576 )    return round( $bytes / 1048576,    2 ) . ' MB';
        if ( $bytes >= 1024 )       return round( $bytes / 1024,       2 ) . ' KB';
        return $bytes . ' B';
    }
}