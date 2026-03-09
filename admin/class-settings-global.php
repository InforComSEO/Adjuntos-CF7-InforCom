<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ACF7I_Settings_Global {

    public function render() {
        $settings   = get_option( 'acf7i_settings', array() );
        $active_tab = sanitize_key( $_GET['tab'] ?? 'apariencia' );

        // Wrapper de settings para las vistas parciales
        $settings_obj = new ACF7I_Settings_Wrapper( $settings );
        $settings     = $settings_obj; // alias para los partials

        require_once ACF7I_PLUGIN_DIR . 'admin/views/settings-global.php';
    }
}