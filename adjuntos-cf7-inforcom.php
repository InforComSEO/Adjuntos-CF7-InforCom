<?php
/**
 * Plugin Name:       Adjuntos CF7 — InforCom
 * Plugin URI:        https://inforcom.es
 * Description:       Sistema completo de carga de archivos para Contact Form 7 con almacenamiento local, Google Drive y correo.
 * Version:           1.0.0
 * Author:            InforCom
 * Author URI:        https://inforcom.es
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       acf7i
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Constantes ──────────────────────────────────────────
define( 'ACF7I_VERSION',     '1.0.0' );
define( 'ACF7I_DB_VERSION',  '1.0.0' ); // Versión del esquema de base de datos (para migraciones)
define( 'ACF7I_MIN_PHP',     '7.4' );   // Versión mínima de PHP requerida
define( 'ACF7I_TEXT_DOMAIN', 'acf7i' );
define( 'ACF7I_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'ACF7I_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'ACF7I_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'ACF7I_UPLOAD_DIR',  WP_CONTENT_DIR . '/uploads/cf7-adjuntos/' );
define( 'ACF7I_UPLOAD_URL',  WP_CONTENT_URL . '/uploads/cf7-adjuntos/' );

// ─── Autoloader ──────────────────────────────────────────
spl_autoload_register( function( $class ) {
    $map = array(
        // Core
        'ACF7I_Activator'        => 'includes/class-activator.php',
        'ACF7I_Installer'        => 'includes/class-installer.php',
        'ACF7I_Main'             => 'includes/class-main.php',
        'ACF7I_Ajax_Handlers'    => 'includes/class-ajax-handlers.php',
        'ACF7I_Security'         => 'includes/class-security.php',
        'ACF7I_Upload_Handler'   => 'includes/class-upload-handler.php',
        'ACF7I_Storage_Local'    => 'includes/class-storage-local.php',
        'ACF7I_Storage_Drive'    => 'includes/class-storage-drive.php',
        'ACF7I_Log'              => 'includes/class-log.php',
        'ACF7I_Mailer'           => 'includes/class-mailer.php',
        'ACF7I_Notifications'    => 'includes/class-notifications.php',
        'ACF7I_Cleaner'          => 'includes/class-cleaner.php',
        'ACF7I_File_Viewer'      => 'includes/class-file-viewer.php',
        'ACF7I_CF7_Tag'          => 'includes/class-cf7-tag.php',
        'ACF7I_Settings_Wrapper' => 'includes/class-settings-wrapper.php',
        // Admin
        'ACF7I_Admin'            => 'admin/class-admin.php',
        'ACF7I_Dashboard'        => 'admin/class-dashboard.php',
        'ACF7I_Settings_Global'  => 'admin/class-settings-global.php',
        'ACF7I_Settings_Form'    => 'admin/class-settings-form.php',
        'ACF7I_Log_Page'         => 'admin/class-log-page.php',
        'ACF7I_Drive_Page'       => 'admin/class-drive-page.php',
        'ACF7I_Cleanup_Page'     => 'admin/class-cleanup-page.php',
        // Public
        'ACF7I_Public'           => 'public/class-public.php',
    );

    if ( isset( $map[ $class ] ) ) {
        $file = ACF7I_PLUGIN_DIR . $map[ $class ];
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
});

// ─── Activación / Desactivación ──────────────────────────
register_activation_hook( __FILE__, array( 'ACF7I_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, function() {
    $timestamp = wp_next_scheduled( 'acf7i_auto_cleanup' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'acf7i_auto_cleanup' );
    }
    flush_rewrite_rules();
});

// ─── Inicialización ───────────────────────────────────────
add_action( 'plugins_loaded', function() {

    // Verificar que CF7 está activo
    if ( ! function_exists( 'wpcf7_get_current_contact_form' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>Adjuntos CF7 — InforCom</strong>: ';
            _e( 'Requiere que Contact Form 7 esté instalado y activo.', ACF7I_TEXT_DOMAIN );
            echo '</p></div>';
        });
        return;
    }

    // Comprobar actualización de la DB
    ACF7I_Installer::maybe_upgrade();

    // Cargar i18n
    load_plugin_textdomain(
        ACF7I_TEXT_DOMAIN,
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );

    // Arrancar el orquestador principal
    $plugin = new ACF7I_Main();
    $plugin->run();

    // CSS dinámico frontend
    add_action( 'wp_head', 'acf7i_render_dynamic_css' );

});

// ─── CSS dinámico frontend ─────────────────────────────────
function acf7i_render_dynamic_css() {
    global $post;
    if ( ! $post || ! has_shortcode( $post->post_content, 'contact-form-7' ) ) return;

    $settings = get_option( 'acf7i_settings', array() );
    ?>
    <style id="acf7i-dynamic-css">
        :root {
            --acf7i-drop-bg:            <?php echo sanitize_hex_color( $settings['dropzone_bg']           ?? '#FFFFFF' ); ?>;
            --acf7i-drop-bg-hover:      <?php echo sanitize_hex_color( $settings['dropzone_bg_hover']     ?? '#E8F7FF' ); ?>;
            --acf7i-drop-border:        <?php echo sanitize_hex_color( $settings['dropzone_border_color'] ?? '#07325A' ); ?>;
            --acf7i-drop-border-hover:  <?php echo sanitize_hex_color( $settings['dropzone_border_hover'] ?? '#00BCFF' ); ?>;
            --acf7i-drop-radius:        <?php echo intval( $settings['dropzone_border_radius'] ?? 12 ); ?>px;
            --acf7i-drop-border-style:  <?php echo esc_attr( $settings['dropzone_border_style'] ?? 'dashed' ); ?>;
            --acf7i-drop-border-width:  <?php echo intval( $settings['dropzone_border_width']  ?? 2 ); ?>px;
            --acf7i-drop-padding:       <?php echo intval( $settings['dropzone_padding']        ?? 40 ); ?>px;
            --acf7i-drop-min-height:    <?php echo intval( $settings['dropzone_min_height']     ?? 200 ); ?>px;
            --acf7i-drop-align:         <?php echo esc_attr( $settings['dropzone_align']        ?? 'center' ); ?>;
            --acf7i-icon-color:         <?php echo sanitize_hex_color( $settings['icon_color']  ?? '#00BCFF' ); ?>;
            --acf7i-icon-size:          <?php echo intval( $settings['icon_size']               ?? 48 ); ?>px;
            --acf7i-text-main-size:     <?php echo intval( $settings['text_main_size']          ?? 18 ); ?>px;
            --acf7i-text-main-color:    <?php echo sanitize_hex_color( $settings['text_main_color'] ?? '#050018' ); ?>;
            --acf7i-text-main-weight:   <?php echo esc_attr( $settings['text_main_weight']      ?? '700' ); ?>;
            --acf7i-btn-bg:             <?php echo sanitize_hex_color( $settings['btn_bg']      ?? '#00BCFF' ); ?>;
            --acf7i-btn-color:          <?php echo sanitize_hex_color( $settings['btn_color']   ?? '#050018' ); ?>;
            --acf7i-btn-radius:         <?php echo intval( $settings['btn_radius']              ?? 8 ); ?>px;
            --acf7i-progress-color:     <?php echo sanitize_hex_color( $settings['progress_color'] ?? '#00BCFF' ); ?>;
            --acf7i-progress-bg:        <?php echo sanitize_hex_color( $settings['progress_bg']    ?? '#E8F7FF' ); ?>;
            --acf7i-progress-height:    <?php echo intval( $settings['progress_height']            ?? 6 ); ?>px;
            --acf7i-error-bg:           <?php echo sanitize_hex_color( $settings['error_bg']       ?? '#FFF0ED' ); ?>;
            --acf7i-error-border:       <?php echo sanitize_hex_color( $settings['error_border_color'] ?? '#FF3600' ); ?>;
        }
    </style>
    <?php
}