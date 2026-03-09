<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Wrapper de acceso a settings para las vistas parciales
 * Evita redeclaración de clase si se incluye en múltiples páginas
 */
if ( ! class_exists( 'ACF7I_Settings_Wrapper' ) ) {
    class ACF7I_Settings_Wrapper {

        private $data;

        public function __construct( $data ) {
            $this->data = is_array( $data ) ? $data : array();
        }

        public function get( $key, $default = '' ) {
            return $this->data[ $key ] ?? $default;
        }

        public function get_settings() {
            return $this->data;
        }

        public function get_tabs_list() {
            return array(
                'apariencia'     => array( 'icon' => '🎨', 'label' => 'Apariencia' ),
                'textos'         => array( 'icon' => '✏️',  'label' => 'Textos' ),
                'archivos'       => array( 'icon' => '📁', 'label' => 'Archivos' ),
                'errores'        => array( 'icon' => '❌', 'label' => 'Errores y Mensajes' ),
                'correo'         => array( 'icon' => '📧', 'label' => 'Correo' ),
                'almacenamiento' => array( 'icon' => '💾', 'label' => 'Almacenamiento' ),
            );
        }
    }
}
