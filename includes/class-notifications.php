<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sistema de notificaciones internas del plugin
 * Campana en el panel de administración
 */
class ACF7I_Notifications {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'acf7i_notifications';
    }

    /**
     * Crea una notificación cuando se sube un archivo
     */
    public function on_file_uploaded( $data ) {
        $settings = get_option( 'acf7i_settings', array() );
        if ( empty( $settings['notifications_enabled'] ) ) return;

        $this->create( array(
            'type'    => 'success',
            'message' => sprintf(
                '📎 "%s" subido desde "%s"',
                $data['filename']   ?? 'Archivo',
                $data['form_title'] ?? 'Formulario'
            ),
        ));
    }

    /**
     * Crea una notificación de error
     */
    public function on_error( $message ) {
        $this->create( array(
            'type'    => 'error',
            'message' => '❌ ' . $message,
        ));
    }

    /**
     * Crea una notificación informativa
     */
    public function on_info( $message ) {
        $this->create( array(
            'type'    => 'info',
            'message' => 'ℹ️ ' . $message,
        ));
    }

    /**
     * Inserta una notificación en la base de datos
     */
    public function create( $data ) {
        global $wpdb;

        return $wpdb->insert(
            $this->table,
            array(
                'type'        => sanitize_text_field( $data['type']    ?? 'info' ),
                'message'     => sanitize_text_field( $data['message'] ?? '' ),
                'read_status' => 0,
                'created_at'  => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%d', '%s' )
        );
    }

    /**
     * Obtiene las notificaciones recientes
     */
    public static function get_recent( $limit = 15 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'acf7i_notifications';
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Obtiene el número de notificaciones no leídas
     */
    public static function get_unread_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'acf7i_notifications';
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table} WHERE read_status = 0"
        );
    }

    /**
     * Marca todas como leídas
     */
    public static function mark_all_read() {
        global $wpdb;
        $table = $wpdb->prefix . 'acf7i_notifications';
        return $wpdb->update(
            $table,
            array( 'read_status' => 1 ),
            array( 'read_status' => 0 ),
            array( '%d' ),
            array( '%d' )
        );
    }

    /**
     * Marca una como leída
     */
    public static function mark_read( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'acf7i_notifications';
        return $wpdb->update(
            $table,
            array( 'read_status' => 1 ),
            array( 'id' => intval( $id ) ),
            array( '%d' ),
            array( '%d' )
        );
    }

    /**
     * Registra los endpoints AJAX de notificaciones
     */
    public static function register_ajax() {
        add_action( 'wp_ajax_acf7i_mark_notifications_read', array( __CLASS__, 'ajax_mark_all_read' ) );
        add_action( 'wp_ajax_acf7i_mark_notification_read',  array( __CLASS__, 'ajax_mark_read' ) );
        add_action( 'wp_ajax_acf7i_get_notifications',       array( __CLASS__, 'ajax_get_notifications' ) );
    }

    public static function ajax_mark_all_read() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        self::mark_all_read();
        wp_send_json_success();
    }

    public static function ajax_mark_read() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        self::mark_read( intval( $_POST['id'] ?? 0 ) );
        wp_send_json_success( array( 'unread' => self::get_unread_count() ) );
    }

    public static function ajax_get_notifications() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        wp_send_json_success( array(
            'items'  => self::get_recent( 15 ),
            'unread' => self::get_unread_count(),
        ));
    }
}