<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Página del log de archivos en el panel de administración
 */
class ACF7I_Log_Page {

    public function render() {
        $log      = new ACF7I_Log();
        $stats    = $log->get_stats();
        $forms    = $log->get_unique_forms();
        $types    = $log->get_unique_filetypes();

        // Filtros desde GET
        $filters = array(
            'form_id'   => intval( $_GET['form_id']   ?? 0 ),
            'status'    => sanitize_text_field( $_GET['status']    ?? '' ),
            'filetype'  => sanitize_text_field( $_GET['filetype']  ?? '' ),
            'storage'   => sanitize_text_field( $_GET['storage']   ?? '' ),
            'date_from' => sanitize_text_field( $_GET['date_from'] ?? '' ),
            'date_to'   => sanitize_text_field( $_GET['date_to']   ?? '' ),
            'search'    => sanitize_text_field( $_GET['search']    ?? '' ),
            'orderby'   => sanitize_text_field( $_GET['orderby']   ?? 'uploaded_at' ),
            'order'     => sanitize_text_field( $_GET['order']     ?? 'DESC' ),
            'page'      => max( 1, intval( $_GET['paged'] ?? 1 ) ),
            'per_page'  => 20,
        );

        $result = $log->get_list( $filters );
        require_once ACF7I_PLUGIN_DIR . 'admin/views/log.php';
    }
}