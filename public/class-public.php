<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Clase del frontend público
 * Encola scripts y estilos en el frontend
 */
class ACF7I_Public {

    public function enqueue_scripts() {
        // Solo si hay un formulario CF7 en la página
        if ( ! $this->page_has_cf7() ) return;

        // Estilos del frontend
        wp_enqueue_style(
            'acf7i-frontend',
            ACF7I_PLUGIN_URL . 'public/css/frontend.css',
            array(),
            ACF7I_VERSION
        );

        // Validador
        wp_enqueue_script(
            'acf7i-validator',
            ACF7I_PLUGIN_URL . 'public/js/validator.js',
            array( 'jquery' ),
            ACF7I_VERSION,
            true
        );

        // Uploader
        wp_enqueue_script(
            'acf7i-uploader',
            ACF7I_PLUGIN_URL . 'public/js/uploader.js',
            array( 'jquery', 'acf7i-validator' ),
            ACF7I_VERSION,
            true
        );

        // Dropzone principal
        wp_enqueue_script(
            'acf7i-dropzone',
            ACF7I_PLUGIN_URL . 'public/js/dropzone.js',
            array( 'jquery', 'acf7i-uploader' ),
            ACF7I_VERSION,
            true
        );

        // Localizar variables para el frontend
        wp_localize_script( 'acf7i-dropzone', 'acf7i_public', array(
            'ajax_url'   => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'acf7i_upload_nonce' ),
            'plugin_url' => ACF7I_PLUGIN_URL,
        ));
    }

    /**
     * Detecta si la página actual tiene un formulario CF7
     */
    private function page_has_cf7() {
        global $post;
        if ( ! $post ) return false;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'contact-form-7' ) ) {
            return true;
        }
        // Fallback: siempre cargar si CF7 está activo (para page builders)
        return apply_filters( 'acf7i_always_load_scripts', false );
    }
}