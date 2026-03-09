<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Clase principal del plugin
 * Orquesta todos los módulos
 */
class ACF7I_Main {

    protected $loader_actions = array();
    protected $loader_filters = array();

    public function __construct() {
        $this->load_modules();
    }

    /**
     * Carga e inicializa todos los módulos
     */
    private function load_modules() {

        // Tag de CF7
        $cf7_tag = new ACF7I_CF7_Tag();
        $this->add_action( 'wpcf7_init', $cf7_tag, 'register_tag' );
        $this->add_filter( 'wpcf7_validate_adjuntos-cf7',  $cf7_tag, 'validate_tag', 10, 2 );
        $this->add_filter( 'wpcf7_validate_adjuntos-cf7*', $cf7_tag, 'validate_tag', 10, 2 );

        // Manejador de subidas (AJAX)
        $upload_handler = new ACF7I_Upload_Handler();
        $this->add_action( 'wp_ajax_acf7i_upload',             $upload_handler, 'handle_upload' );
        $this->add_action( 'wp_ajax_nopriv_acf7i_upload',      $upload_handler, 'handle_upload' );
        $this->add_action( 'wp_ajax_acf7i_delete_temp',        $upload_handler, 'delete_temp' );
        $this->add_action( 'wp_ajax_nopriv_acf7i_delete_temp', $upload_handler, 'delete_temp' );

        // Visualizador de archivos
        $file_viewer = new ACF7I_File_Viewer();
        $this->add_action( 'init',              $file_viewer, 'register_endpoint' );
        $this->add_action( 'template_redirect', $file_viewer, 'handle_view_request' );

        // Mailer
        $mailer = new ACF7I_Mailer();
        $this->add_action( 'wpcf7_mail_sent',       $mailer, 'process_form_submission' );
        $this->add_filter( 'wpcf7_mail_components', $mailer, 'filter_mail_components', 10, 3 );

        // Limpieza automática
        $cleaner = new ACF7I_Cleaner();
        $this->add_action( 'acf7i_auto_cleanup', $cleaner, 'run_cleanup' );

        // Notificaciones
        $notifications = new ACF7I_Notifications();
        $this->add_action( 'acf7i_file_uploaded', $notifications, 'on_file_uploaded' );

        // AJAX Admin (centralizados en ACF7I_Ajax_Handlers)
        $ajax = new ACF7I_Ajax_Handlers();
        $this->add_action( 'init', $ajax, 'register' );

        // Frontend
        $public = new ACF7I_Public();
        $this->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

        // Admin
        if ( is_admin() ) {
            $admin = new ACF7I_Admin();
            $this->add_action( 'admin_menu',            $admin, 'register_menus' );
            $this->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_assets' );
            $this->add_filter(
                'plugin_action_links_' . ACF7I_PLUGIN_BASE,
                $admin,
                'add_plugin_links'
            );
        }
    }

    /**
     * Registra una acción
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->loader_actions[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );
    }

    /**
     * Registra un filtro
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->loader_filters[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );
    }

    /**
     * Ejecuta todos los hooks registrados
     */
    public function run() {
        foreach ( $this->loader_filters as $hook ) {
            add_filter(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
        foreach ( $this->loader_actions as $hook ) {
            add_action(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }
}