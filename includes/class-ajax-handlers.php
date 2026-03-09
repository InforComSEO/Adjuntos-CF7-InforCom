<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Centraliza todos los handlers AJAX del plugin
 */
class ACF7I_Ajax_Handlers {

    public function register() {

        // ── Frontend (sin login) ──────────────────────
        add_action( 'wp_ajax_nopriv_acf7i_upload',      array( $this, 'handle_upload' ) );
        add_action( 'wp_ajax_nopriv_acf7i_delete_temp', array( $this, 'handle_delete_temp' ) );
        add_action( 'wp_ajax_acf7i_upload',             array( $this, 'handle_upload' ) );
        add_action( 'wp_ajax_acf7i_delete_temp',        array( $this, 'handle_delete_temp' ) );

        // ── Admin ─────────────────────────────────────
        add_action( 'wp_ajax_acf7i_save_settings',          array( $this, 'save_settings' ) );
        add_action( 'wp_ajax_acf7i_delete_log',             array( $this, 'delete_log' ) );
        add_action( 'wp_ajax_acf7i_bulk_delete_log',        array( $this, 'bulk_delete_log' ) );
        add_action( 'wp_ajax_acf7i_resend_mail',            array( $this, 'resend_mail' ) );
        add_action( 'wp_ajax_acf7i_reset_form_config',      array( $this, 'reset_form_config' ) );
        add_action( 'wp_ajax_acf7i_manual_cleanup',         array( $this, 'manual_cleanup' ) );
        add_action( 'wp_ajax_acf7i_get_dashboard_stats',    array( $this, 'get_dashboard_stats' ) );

        // ── Notificaciones ────────────────────────────
        ACF7I_Notifications::register_ajax();

        // ── Variables JS admin ────────────────────────
        add_action( 'admin_head', array( __CLASS__, 'inject_admin_vars' ) );
    }

    // ─────────────────────────────────────────────────
    // UPLOAD
    // ─────────────────────────────────────────────────

    public function handle_upload() {
        $handler = new ACF7I_Upload_Handler();
        $handler->handle_upload();
    }

    public function handle_delete_temp() {
        $handler = new ACF7I_Upload_Handler();
        $handler->delete_temp();
    }

    // ─────────────────────────────────────────────────
    // GUARDAR CONFIGURACIÓN
    // ─────────────────────────────────────────────────

    public function save_settings() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Sin permisos.' ) );
        }

        $type    = sanitize_text_field( $_POST['settings_type'] ?? 'global' );
        $form_id = intval( $_POST['form_id'] ?? 0 );
        $raw     = $_POST['settings'] ?? array();

        // Sanitizar
        $settings = $this->sanitize_settings( $raw );

        if ( $type === 'form' && $form_id > 0 ) {
            // Guardar config del formulario específico
            $existing = get_post_meta( $form_id, '_acf7i_form_settings', true ) ?: array();
            $merged   = array_merge( $existing, $settings );
            update_post_meta( $form_id, '_acf7i_form_settings', $merged );
        } else {
            // Guardar config global
            $existing = get_option( 'acf7i_settings', array() );
            $merged   = array_merge( $existing, $settings );
            update_option( 'acf7i_settings', $merged );
        }

        wp_send_json_success( array(
            'message' => __( 'Configuración guardada correctamente.', ACF7I_TEXT_DOMAIN ),
        ));
    }

    /**
     * Sanitiza el array de settings recibido por POST
     */
    private function sanitize_settings( $raw ) {
        if ( ! is_array( $raw ) ) return array();

        $clean = array();

        foreach ( $raw as $key => $value ) {
            $key = sanitize_key( $key );

            if ( is_array( $value ) ) {
                // Array de tipos permitidos, etc.
                $clean[ $key ] = array_map( 'sanitize_text_field', $value );
            } elseif ( $value === '1' || $value === '0' ) {
                // Booleanos
                $clean[ $key ] = (bool) intval( $value );
            } elseif ( is_numeric( $value ) ) {
                $clean[ $key ] = intval( $value );
            } elseif ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
                $clean[ $key ] = esc_url_raw( $value );
            } elseif ( strpos( $key, 'color' ) !== false || strpos( $key, 'bg' ) !== false ) {
                // Colores HEX
                $clean[ $key ] = sanitize_hex_color( $value ) ?? sanitize_text_field( $value );
            } elseif ( strpos( $key, 'text' ) !== false || strpos( $key, 'msg' ) !== false ) {
                // Textos largos
                $clean[ $key ] = sanitize_textarea_field( $value );
            } else {
                $clean[ $key ] = sanitize_text_field( $value );
            }
        }

        return $clean;
    }

    // ─────────────────────────────────────────────────
    // LOG
    // ─────────────────────────────────────────────────

    public function delete_log() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $log_id      = intval( $_POST['log_id']      ?? 0 );
        $delete_file = (bool) intval( $_POST['delete_file'] ?? 0 );

        if ( ! $log_id ) {
            wp_send_json_error( array( 'message' => 'ID de log inválido.' ) );
        }

        $log    = new ACF7I_Log();
        $result = $log->delete_entry( $log_id, $delete_file );

        if ( $result ) {
            wp_send_json_success( array(
                'message' => __( 'Registro eliminado correctamente.', ACF7I_TEXT_DOMAIN ),
            ));
        } else {
            wp_send_json_error( array(
                'message' => __( 'Error al eliminar el registro.', ACF7I_TEXT_DOMAIN ),
            ));
        }
    }

    public function bulk_delete_log() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $ids         = array_map( 'intval', (array) ( $_POST['ids'] ?? array() ) );
        $delete_files= (bool) intval( $_POST['delete_files'] ?? 0 );

        if ( empty( $ids ) ) {
            wp_send_json_error( array( 'message' => 'No se proporcionaron IDs.' ) );
        }

        $log     = new ACF7I_Log();
        $deleted = $log->delete_bulk( $ids, $delete_files );

        wp_send_json_success( array(
            'deleted' => $deleted,
            'message' => sprintf(
                __( '%d registro(s) eliminado(s).', ACF7I_TEXT_DOMAIN ),
                $deleted
            ),
        ));
    }

    // ─────────────────────────────────────────────────
    // REENVÍO DE CORREO
    // ─────────────────────────────────────────────────

    public function resend_mail() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $log_id    = intval( $_POST['log_id']    ?? 0 );
        $recipient = sanitize_email( $_POST['recipient'] ?? '' );

        if ( ! $log_id ) {
            wp_send_json_error( array( 'message' => 'ID de log inválido.' ) );
        }

        $mailer = new ACF7I_Mailer();
        $result = $mailer->resend_from_log( $log_id, $recipient );

        if ( $result ) {
            wp_send_json_success( array(
                'message' => __( 'Correo reenviado correctamente.', ACF7I_TEXT_DOMAIN ),
            ));
        } else {
            wp_send_json_error( array(
                'message' => __( 'Error al reenviar el correo.', ACF7I_TEXT_DOMAIN ),
            ));
        }
    }

    // ─────────────────────────────────────────────────
    // RESET CONFIG FORMULARIO
    // ─────────────────────────────────────────────────

    public function reset_form_config() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $form_id = intval( $_POST['form_id'] ?? 0 );
        if ( ! $form_id ) wp_send_json_error();

        delete_post_meta( $form_id, '_acf7i_form_settings' );

        wp_send_json_success( array(
            'message' => __( 'Configuración del formulario eliminada. Se usará la global.', ACF7I_TEXT_DOMAIN ),
        ));
    }

    // ─────────────────────────────────────────────────
    // LIMPIEZA MANUAL
    // ─────────────────────────────────────────────────

    public function manual_cleanup() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $result = ACF7I_Cleaner::run_manual();

        wp_send_json_success( array(
            'message' => sprintf(
                __( '✅ Limpieza completada: %d archivo(s) y %d registro(s) eliminados.', ACF7I_TEXT_DOMAIN ),
                $result['deleted_files'],
                $result['deleted_db']
            ),
            'result'  => $result,
        ));
    }

    // ─────────────────────────────────────────────────
    // DASHBOARD STATS (refresh)
    // ─────────────────────────────────────────────────

    public function get_dashboard_stats() {
        check_ajax_referer( 'acf7i_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $log   = new ACF7I_Log();
        $stats = $log->get_stats();
        $local = new ACF7I_Storage_Local();

        wp_send_json_success( array(
            'stats'      => $stats,
            'used_space' => ACF7I_Dashboard::format_bytes( $local->get_used_space() ),
        ));
    }

    // ─────────────────────────────────────────────────
    // VARIABLES JS ADMIN (fallback via admin_head)
    // ─────────────────────────────────────────────────

    /**
     * Inyecta variables JS para el admin (nonce, ajax_url, strings)
     * Llamado desde admin_head cuando ya existe la pantalla de admin
     */
    public static function inject_admin_vars() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'acf7i' ) === false ) return;
        ?>
        <script>
        var acf7i_admin = <?php echo wp_json_encode( array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'acf7i_admin_nonce' ),
            'strings'  => array(
                'saved'   => __( 'Guardado correctamente', ACF7I_TEXT_DOMAIN ),
                'error'   => __( 'Error al guardar', ACF7I_TEXT_DOMAIN ),
                'copied'  => __( 'Copiado', ACF7I_TEXT_DOMAIN ),
                'resent'  => __( 'Correo reenviado', ACF7I_TEXT_DOMAIN ),
                'confirm' => __( '¿Estás seguro?', ACF7I_TEXT_DOMAIN ),
            ),
        ) ); ?>;
        </script>
        <?php
    }
}