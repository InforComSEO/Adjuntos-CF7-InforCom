<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Página de administración de Google Drive
 */
class ACF7I_Drive_Page {

    public function render() {
        $this->register_ajax_handlers();
        require_once ACF7I_PLUGIN_DIR . 'admin/views/drive.php';
    }

    private function register_ajax_handlers() {
        add_action( 'wp_ajax_acf7i_save_drive_credentials', array( $this, 'save_credentials' ) );
        add_action( 'wp_ajax_acf7i_test_drive_connection',  array( $this, 'test_connection' ) );
        add_action( 'wp_ajax_acf7i_disconnect_drive',       array( $this, 'disconnect_drive' ) );
        add_action( 'wp_ajax_acf7i_verify_drive_folder',    array( $this, 'verify_folder' ) );
    }

    public function save_credentials() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $json = stripslashes( $_POST['json_content'] ?? '' );
        if ( empty( $json ) ) {
            wp_send_json_error( array( 'message' => 'No se recibió el contenido JSON.' ) );
        }

        $result = ACF7I_Storage_Drive::save_credentials( $json );
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }

    public function test_connection() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $drive  = new ACF7I_Storage_Drive();
        $result = $drive->test_connection();

        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }

    public function disconnect_drive() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        delete_option( 'acf7i_drive_credentials' );
        delete_option( 'acf7i_drive_token' );
        delete_transient( 'acf7i_drive_token' );

        wp_send_json_success( array( 'message' => 'Drive desconectado correctamente.' ) );
    }

    public function verify_folder() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $folder_id = sanitize_text_field( $_POST['folder_id'] ?? '' );
        if ( empty( $folder_id ) ) {
            wp_send_json_error( array( 'message' => 'Proporciona un ID de carpeta.' ) );
        }

        $drive = new ACF7I_Storage_Drive();
        if ( ! $drive->is_connected() ) {
            wp_send_json_error( array( 'message' => 'Drive no está conectado.' ) );
        }

        // La verificación se hace al intentar subir, aquí solo validamos el formato
        if ( strlen( $folder_id ) < 10 ) {
            wp_send_json_error( array( 'message' => 'El ID de carpeta no parece válido.' ) );
        }

        wp_send_json_success( array(
            'message' => '✅ ID de carpeta guardado. Se verificará al subir el primer archivo.',
            'url'     => 'https://drive.google.com/drive/folders/' . $folder_id,
        ));
    }
}