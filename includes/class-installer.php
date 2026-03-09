<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Crea/elimina las tablas de la base de datos y la estructura de carpetas
 */
class ACF7I_Installer {

    /**
     * Ejecuta la instalación completa
     */
    public static function activate() {
        self::create_tables();
        self::create_directories();
        self::schedule_cron();
        self::set_default_options();
        flush_rewrite_rules();
    }

    /**
     * Limpia al desactivar
     */
    public static function deactivate() {
        self::unschedule_cron();
        flush_rewrite_rules();
    }

    /**
     * Limpieza completa al desinstalar
     */
    public static function uninstall() {
        $settings = get_option( 'acf7i_settings', array() );

        // Eliminar tablas si el usuario lo configuró así
        if ( ! empty( $settings['uninstall_delete_tables'] ) ) {
            self::drop_tables();
        }

        // Eliminar opciones
        if ( ! empty( $settings['uninstall_delete_settings'] ) ) {
            delete_option( 'acf7i_settings' );
            delete_option( 'acf7i_drive_credentials' );
            delete_option( 'acf7i_last_cleanup' );
            delete_transient( 'acf7i_drive_token' );
        }
    }

    /**
     * Crea las tablas de la base de datos
     */
    private static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        // Tabla: log de archivos
        $sql_log = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}acf7i_log (
            id             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id        BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            form_title     VARCHAR(255)        NOT NULL DEFAULT '',
            filename       VARCHAR(500)        NOT NULL DEFAULT '',
            original_name  VARCHAR(500)        NOT NULL DEFAULT '',
            filesize       BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            filetype       VARCHAR(20)         NOT NULL DEFAULT '',
            storage        VARCHAR(10)         NOT NULL DEFAULT 'local',
            local_path     TEXT                         DEFAULT NULL,
            local_url      TEXT                         DEFAULT NULL,
            drive_id       VARCHAR(255)                 DEFAULT NULL,
            drive_url      TEXT                         DEFAULT NULL,
            view_token     VARCHAR(64)                  DEFAULT NULL,
            token_expires  DATETIME                     DEFAULT NULL,
            sender_email   VARCHAR(255)                 DEFAULT NULL,
            sender_name    VARCHAR(255)                 DEFAULT NULL,
            status         VARCHAR(20)         NOT NULL DEFAULT 'uploaded',
            uploaded_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY form_id    (form_id),
            KEY status     (status),
            KEY view_token (view_token),
            KEY uploaded_at(uploaded_at)
        ) $charset;";

        // Tabla: archivos temporales
        $sql_temp = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}acf7i_temp (
            id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(100)        NOT NULL DEFAULT '',
            filename   VARCHAR(500)        NOT NULL DEFAULT '',
            filepath   TEXT                NOT NULL,
            filesize   BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY session_id (session_id),
            KEY created_at (created_at)
        ) $charset;";

        // Tabla: notificaciones internas
        $sql_notif = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}acf7i_notifications (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            type        VARCHAR(20)         NOT NULL DEFAULT 'info',
            message     TEXT                NOT NULL,
            read_status TINYINT(1)          NOT NULL DEFAULT 0,
            created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY read_status (read_status),
            KEY created_at  (created_at)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_log );
        dbDelta( $sql_temp );
        dbDelta( $sql_notif );

        // Guardar versión de la DB
        update_option( 'acf7i_db_version', ACF7I_VERSION );
    }

    /**
     * Elimina las tablas de la base de datos
     */
    private static function drop_tables() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}acf7i_log" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}acf7i_temp" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}acf7i_notifications" );
        delete_option( 'acf7i_db_version' );
    }

    /**
     * Crea la estructura de directorios
     */
    private static function create_directories() {
        $settings = get_option( 'acf7i_settings', array() );
        $folder   = sanitize_file_name( $settings['local_folder'] ?? 'cf7-adjuntos' );
        $base     = WP_CONTENT_DIR . '/uploads/' . $folder . '/';
        $temp     = $base . 'temp/';

        $dirs = array( $base, $temp );

        foreach ( $dirs as $dir ) {
            if ( ! file_exists( $dir ) ) {
                wp_mkdir_p( $dir );
            }

            // Proteger con .htaccess
            $htaccess = $dir . '.htaccess';
            if ( ! file_exists( $htaccess ) ) {
                file_put_contents( $htaccess,
                    "Options -Indexes\n" .
                    "<FilesMatch \"\.(php|php3|php4|php5|phtml|phar|pl|py|jsp|asp|sh|bat|exe)$\">\n" .
                    "    Order allow,deny\n" .
                    "    Deny from all\n" .
                    "</FilesMatch>\n"
                );
            }

            // index.php silencioso
            $index = $dir . 'index.php';
            if ( ! file_exists( $index ) ) {
                file_put_contents( $index, '<?php // Silence is golden.' );
            }
        }
    }

    /**
     * Programa el cron de limpieza
     */
    private static function schedule_cron() {
        if ( ! wp_next_scheduled( 'acf7i_auto_cleanup' ) ) {
            wp_schedule_event( time(), 'hourly', 'acf7i_auto_cleanup' );
        }
    }

    /**
     * Elimina el cron de limpieza
     */
    private static function unschedule_cron() {
        $timestamp = wp_next_scheduled( 'acf7i_auto_cleanup' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'acf7i_auto_cleanup' );
        }
    }

    /**
     * Establece las opciones por defecto
     */
    private static function set_default_options() {
        if ( get_option( 'acf7i_settings' ) !== false ) return;

        $defaults = array(
            // Apariencia
            'dropzone_bg'             => '#FFFFFF',
            'dropzone_bg_hover'       => '#E8F7FF',
            'dropzone_border_color'   => '#07325A',
            'dropzone_border_hover'   => '#00BCFF',
            'dropzone_border_style'   => 'dashed',
            'dropzone_border_width'   => 2,
            'dropzone_border_radius'  => 12,
            'dropzone_padding'        => 40,
            'dropzone_min_height'     => 200,
            'dropzone_align'          => 'center',
            'dropzone_shadow'         => false,
            'dropzone_drag_animation' => 'pulse',
            // Ícono
            'icon_show'               => true,
            'icon_type'               => 'upload-cloud',
            'icon_size'               => 48,
            'icon_color'              => '#00BCFF',
            'icon_color_hover'        => '#07325A',
            'icon_animation'          => 'none',
            // Textos
            'text_main'               => 'Arrastra tus archivos aquí',
            'text_main_size'          => 18,
            'text_main_color'         => '#050018',
            'text_main_weight'        => '700',
            'text_secondary'          => 'o haz clic para seleccionar',
            'text_secondary_size'     => 14,
            'text_secondary_color'    => '#07325A',
            'text_secondary_opacity'  => 80,
            // Botón
            'btn_text'                => 'Selecciona tus archivos',
            'btn_bg'                  => '#00BCFF',
            'btn_color'               => '#050018',
            'btn_bg_hover'            => '#07325A',
            'btn_color_hover'         => '#FFFFFF',
            'btn_radius'              => 8,
            'btn_font_size'           => 14,
            'btn_style'               => 'solid',
            'btn_width'               => 'auto',
            // Barra de progreso
            'progress_show'           => true,
            'progress_color'          => '#00BCFF',
            'progress_bg'             => '#E8F7FF',
            'progress_height'         => 6,
            'progress_radius'         => 3,
            'progress_pct'            => true,
            'progress_animation'      => 'linear',
            // Archivos
            'allowed_types'           => array( 'jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx' ),
            'max_files'               => 5,
            'min_files'               => 0,
            'max_filesize'            => 5,
            'max_filesize_unit'       => 'MB',
            'max_total_size'          => 25,
            'max_total_unit'          => 'MB',
            // Nomenclatura
            'filename_date_format'    => 'dmY',
            'filename_time_format'    => 'Hi',
            'filename_separator'      => ' ',
            'filename_spaces'         => 'keep',
            // Almacenamiento
            'storage_local'           => true,
            'storage_drive'           => false,
            'local_folder'            => 'cf7-adjuntos',
            'subfolder_by_form'       => true,
            'subfolder_form_name'     => 'title',
            'subfolder_by_date'       => false,
            // Correo
            'mail_send_mode'          => 'link',
            'mail_link_expires'       => 'never',
            'mail_text_before'        => 'Se adjuntaron los siguientes archivos:',
            'mail_link_text'          => 'Ver archivo',
            'mail_block_style'        => 'list',
            'mail_show_name'          => true,
            'mail_show_size'          => true,
            // Errores
            'error_bg'                => '#FFF0ED',
            'error_color'             => '#050018',
            'error_border_color'      => '#FF3600',
            'error_border_style'      => 'left-only',
            'error_radius'            => 6,
            'error_closeable'         => true,
            'error_autodismiss'       => false,
            'error_autodismiss_time'  => 5,
            // Limpieza
            'cleanup_enabled'         => true,
            'cleanup_interval'        => 24,
            'cleanup_unit'            => 'hours',
            // Notificaciones
            'notifications_enabled'   => true,
        );

        update_option( 'acf7i_settings', $defaults );
    }

    /**
     * Comprueba si la DB necesita actualización
     */
    public static function maybe_upgrade() {
        $installed = get_option( 'acf7i_db_version', '0' );
        if ( version_compare( $installed, ACF7I_VERSION, '<' ) ) {
            self::create_tables();
        }
    }
}