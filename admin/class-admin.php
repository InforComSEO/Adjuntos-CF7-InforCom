<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Clase principal del panel de administración
 * Registra menús, encola assets y sirve de puente entre páginas
 */
class ACF7I_Admin {

    /**
     * Inicializa todos los hooks del panel
     */
    public function init() {
        add_action( 'admin_menu',            array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'plugin_action_links_' . ACF7I_PLUGIN_BASE,
                    array( $this, 'add_plugin_links' ) );
    }

    // ─────────────────────────────────────────────────────
    // MENÚS
    // ─────────────────────────────────────────────────────

    public function register_menus() {

        // Menú principal
        add_menu_page(
            __( 'Adjuntos CF7', ACF7I_TEXT_DOMAIN ),
            __( 'Adjuntos CF7', ACF7I_TEXT_DOMAIN ),
            'manage_options',
            'acf7i-dashboard',
            array( $this, 'page_dashboard' ),
            'data:image/svg+xml;base64,' . base64_encode( $this->get_menu_icon() ),
            58
        );

        // Dashboard
        add_submenu_page(
            'acf7i-dashboard',
            __( 'Dashboard', ACF7I_TEXT_DOMAIN ),
            '📊 ' . __( 'Dashboard', ACF7I_TEXT_DOMAIN ),
            'manage_options',
            'acf7i-dashboard',
            array( $this, 'page_dashboard' )
        );

        // Configuración global
        add_submenu_page(
            'acf7i-dashboard',
            __( 'Configuración Global', ACF7I_TEXT_DOMAIN ),
            '⚙️ ' . __( 'Config Global', ACF7I_TEXT_DOMAIN ),
            'manage_options',
            'acf7i-settings',
            array( $this, 'page_settings_global' )
        );

        // Configuración por formulario
        add_submenu_page(
            'acf7i-dashboard',
            __( 'Config por Formulario', ACF7I_TEXT_DOMAIN ),
            '📋 ' . __( 'Por Formulario', ACF7I_TEXT_DOMAIN ),
            'manage_options',
            'acf7i-form-settings',
            array( $this, 'page_settings_form' )
        );

        // Log de archivos
        add_submenu_page(
            'acf7i-dashboard',
            __( 'Log de Archivos', ACF7I_TEXT_DOMAIN ),
            '📁 ' . __( 'Log de Archivos', ACF7I_TEXT_DOMAIN ),
            'manage_options',
            'acf7i-log',
            array( $this, 'page_log' )
        );

        // Google Drive
        add_submenu_page(
            'acf7i-dashboard',
            __( 'Google Drive', ACF7I_TEXT_DOMAIN ),
            '☁️ ' . __( 'Google Drive', ACF7I_TEXT_DOMAIN ),
            'manage_options',
            'acf7i-drive',
            array( $this, 'page_drive' )
        );

        // Limpieza
        add_submenu_page(
            'acf7i-dashboard',
            __( 'Limpieza Automática', ACF7I_TEXT_DOMAIN ),
            '🧹 ' . __( 'Limpieza', ACF7I_TEXT_DOMAIN ),
            'manage_options',
            'acf7i-cleanup',
            array( $this, 'page_cleanup' )
        );
    }

    // ─────────────────────────────────────────────────────
    // PÁGINAS (callbacks)
    // ─────────────────────────────────────────────────────

    public function page_dashboard() {
        $page = new ACF7I_Dashboard();
        $page->render();
    }

    public function page_settings_global() {
        $page = new ACF7I_Settings_Global();
        $page->render();
    }

    public function page_settings_form() {
        $page = new ACF7I_Settings_Form();
        $page->render();
    }

    public function page_log() {
        $page = new ACF7I_Log_Page();
        $page->render();
    }

    public function page_drive() {
        $page = new ACF7I_Drive_Page();
        $page->render();
    }

    public function page_cleanup() {
        $page = new ACF7I_Cleanup_Page();
        $page->render();
    }

    // ─────────────────────────────────────────────────────
    // ASSETS ADMIN
    // ─────────────────────────────────────────────────────

    public function enqueue_assets( $hook ) {

        // Solo en páginas del plugin
        if ( strpos( $hook, 'acf7i' ) === false ) return;

        // CSS admin
        wp_enqueue_style(
            'acf7i-admin',
            ACF7I_PLUGIN_URL . 'assets/css/admin.css',
            array( 'wp-color-picker' ),
            ACF7I_VERSION
        );

        // JS jQuery UI Sortable
        wp_enqueue_script( 'jquery-ui-sortable' );

        // Color picker de WordPress
        wp_enqueue_style( 'wp-color-picker' );

        // Media uploader de WordPress (para icono personalizado)
        wp_enqueue_media();

        // JS Admin general
        wp_enqueue_script(
            'acf7i-admin',
            ACF7I_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'wp-color-picker' ),
            ACF7I_VERSION,
            true
        );

        // JS Live Preview
        wp_enqueue_script(
            'acf7i-live-preview',
            ACF7I_PLUGIN_URL . 'assets/js/live-preview.js',
            array( 'jquery', 'jquery-ui-sortable', 'acf7i-admin' ),
            ACF7I_VERSION,
            true
        );

        // JS Drive (solo en la página de Drive)
        if ( strpos( $hook, 'acf7i-drive' ) !== false ) {
            wp_enqueue_script(
                'acf7i-drive',
                ACF7I_PLUGIN_URL . 'admin/js/drive.js',
                array( 'jquery', 'acf7i-admin' ),
                ACF7I_VERSION,
                true
            );
        }

        // Localizar variables para todos los scripts admin
        wp_localize_script( 'acf7i-admin', 'acf7i_admin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'acf7i_admin_nonce' ),
            'strings'  => array(
                'saved'   => __( 'Guardado correctamente', ACF7I_TEXT_DOMAIN ),
                'error'   => __( 'Error al guardar', ACF7I_TEXT_DOMAIN ),
                'copied'  => __( 'Copiado', ACF7I_TEXT_DOMAIN ),
                'resent'  => __( 'Correo reenviado', ACF7I_TEXT_DOMAIN ),
                'confirm' => __( '¿Estás seguro?', ACF7I_TEXT_DOMAIN ),
            ),
        ));
    }

    // ─────────────��───────────────────────────────────────
    // LINKS EN LA LISTA DE PLUGINS
    // ─────────────────────────────────────────────────────

    public function add_plugin_links( $links ) {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=acf7i-settings' ) . '">'
                . __( 'Configurar', ACF7I_TEXT_DOMAIN ) . '</a>',
            '<a href="' . admin_url( 'admin.php?page=acf7i-log' ) . '">'
                . __( 'Log', ACF7I_TEXT_DOMAIN ) . '</a>',
        );
        return array_merge( $plugin_links, $links );
    }

    // ─────────────────────────────────────────────────────
    // HELPER: OBTENER CONFIGURACIÓN MEZCLADA (global + form)
    // ─────────────────────────────────────────────────────

    /**
     * Devuelve la configuración activa para un formulario.
     * Si el formulario tiene config propia, sobreescribe la global.
     *
     * @param int $form_id  ID del formulario (0 = solo global)
     * @return array
     */
    public static function get_settings( $form_id = 0 ) {
        $global = get_option( 'acf7i_settings', array() );

        if ( $form_id <= 0 ) {
            return $global;
        }

        $form_settings = get_post_meta( $form_id, '_acf7i_form_settings', true );

        if ( empty( $form_settings ) || ! is_array( $form_settings ) ) {
            return $global;
        }

        // Mezclar: la config del formulario sobreescribe la global
        // pero solo los campos que estén definidos en la config del formulario
        return array_merge( $global, array_filter( $form_settings, fn( $v ) => $v !== '' && $v !== null ) );
    }

    // ─────────────────────────────────────────────────────
    // HELPER: ÍCONO SVG para el menú de WP
    // ─────────────────────────────────────────────────────

    private function get_menu_icon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="16 16 12 12 8 16"/>
                    <line x1="12" y1="12" x2="12" y2="21"/>
                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                </svg>';
    }
}