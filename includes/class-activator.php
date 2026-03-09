<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Clase de activación del plugin
 */
class ACF7I_Activator {

    public static function activate() {
        self::check_requirements();
        ACF7I_Installer::activate();
    }

    /**
     * Verifica requisitos mínimos
     */
    private static function check_requirements() {
        if ( version_compare( PHP_VERSION, ACF7I_MIN_PHP, '<' ) ) {
            deactivate_plugins( ACF7I_PLUGIN_BASE );
            wp_die( sprintf(
                /* translators: %s: required PHP version */
                __( 'Adjuntos CF7 - InforCom requiere PHP %s o superior.', ACF7I_TEXT_DOMAIN ),
                ACF7I_MIN_PHP
            ));
        }
    }
}