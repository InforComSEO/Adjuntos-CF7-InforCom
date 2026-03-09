<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ACF7I_Settings_Form {

    public function render() {
        $form_id  = intval( $_GET['form_id'] ?? 0 );

        // Obtener todos los formularios CF7
        $forms = get_posts( array(
            'post_type'      => 'wpcf7_contact_form',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));

        // Configuraci¨®n mezclada (global + form)
        $settings_merged = ACF7I_Admin::get_settings( $form_id );
        $settings        = new ACF7I_Settings_Wrapper( $settings_merged );
        $active_tab      = sanitize_key( $_GET['tab'] ?? 'apariencia' );

        require_once ACF7I_PLUGIN_DIR . 'admin/views/settings-form.php';
    }
}