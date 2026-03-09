<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gestión del log de archivos subidos
 */
class ACF7I_Log {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'acf7i_log';
    }

    /**
     * Inserta un nuevo registro en el log
     */
    public function insert( $data ) {
        global $wpdb;

        $defaults = array(
            'form_id'       => 0,
            'form_title'    => '',
            'filename'      => '',
            'original_name' => '',
            'filesize'      => 0,
            'filetype'      => '',
            'storage'       => 'local',
            'local_path'    => null,
            'local_url'     => null,
            'drive_id'      => null,
            'drive_url'     => null,
            'view_token'    => null,
            'token_expires' => null,
            'sender_email'  => null,
            'sender_name'   => null,
            'status'        => 'uploaded',
            'uploaded_at'   => current_time( 'mysql' ),
        );

        $row = wp_parse_args( $data, $defaults );

        $formats = array(
            '%d', '%s', '%s', '%s',
            '%d', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s',
        );

        $result = $wpdb->insert( $this->table, $row, $formats );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Obtiene una entrada del log por ID
     */
    public function get( $id ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id )
        );
    }

    /**
     * Obtiene entradas del log con filtros y paginación
     */
    public function get_list( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'form_id'    => 0,
            'status'     => '',
            'date_from'  => '',
            'date_to'    => '',
            'search'     => '',
            'filetype'   => '',
            'storage'    => '',
            'orderby'    => 'uploaded_at',
            'order'      => 'DESC',
            'per_page'   => 20,
            'page'       => 1,
        );

        $args    = wp_parse_args( $args, $defaults );
        $where   = array( '1=1' );
        $values  = array();

        // Filtro por formulario
        if ( ! empty( $args['form_id'] ) ) {
            $where[]  = 'form_id = %d';
            $values[] = intval( $args['form_id'] );
        }

        // Filtro por estado
        if ( ! empty( $args['status'] ) ) {
            $where[]  = 'status = %s';
            $values[] = sanitize_text_field( $args['status'] );
        }

        // Filtro por tipo de archivo
        if ( ! empty( $args['filetype'] ) ) {
            $where[]  = 'filetype = %s';
            $values[] = sanitize_text_field( $args['filetype'] );
        }

        // Filtro por almacenamiento
        if ( ! empty( $args['storage'] ) ) {
            $where[]  = 'storage = %s';
            $values[] = sanitize_text_field( $args['storage'] );
        }

        // Filtro por fecha desde
        if ( ! empty( $args['date_from'] ) ) {
            $where[]  = 'DATE(uploaded_at) >= %s';
            $values[] = sanitize_text_field( $args['date_from'] );
        }

        // Filtro por fecha hasta
        if ( ! empty( $args['date_to'] ) ) {
            $where[]  = 'DATE(uploaded_at) <= %s';
            $values[] = sanitize_text_field( $args['date_to'] );
        }

        // Búsqueda por nombre
        if ( ! empty( $args['search'] ) ) {
            $where[]  = '( original_name LIKE %s OR sender_email LIKE %s OR form_title LIKE %s )';
            $like     = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
            $values[] = $like;
            $values[] = $like;
            $values[] = $like;
        }

        // Sanitizar orderby y order
        $allowed_orderby = array( 'uploaded_at', 'original_name', 'filesize', 'form_title', 'status' );
        $orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'uploaded_at';
        $order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        // Paginación
        $per_page = max( 1, intval( $args['per_page'] ) );
        $page     = max( 1, intval( $args['page'] ) );
        $offset   = ( $page - 1 ) * $per_page;

        $where_clause = implode( ' AND ', $where );

        // Contar total
        $count_sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where_clause}";
        $total     = ! empty( $values )
            ? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $values ) )
            : (int) $wpdb->get_var( $count_sql );

        // Obtener registros
        $sql = "SELECT * FROM {$this->table} WHERE {$where_clause}
                ORDER BY {$orderby} {$order}
                LIMIT %d OFFSET %d";

        $query_values   = array_merge( $values, array( $per_page, $offset ) );
        $rows           = $wpdb->get_results( $wpdb->prepare( $sql, $query_values ) );

        return array(
            'items'      => $rows,
            'total'      => $total,
            'per_page'   => $per_page,
            'page'       => $page,
            'total_pages'=> ceil( $total / $per_page ),
        );
    }

    /**
     * Elimina una entrada del log (con opción de borrar archivo físico)
     */
    public function delete_entry( $id, $delete_file = false ) {
        global $wpdb;

        $entry = $this->get( $id );
        if ( ! $entry ) return false;

        // Eliminar archivo físico si se solicitó
        if ( $delete_file ) {
            // Eliminar del servidor local
            if ( ! empty( $entry->local_path ) ) {
                $storage = new ACF7I_Storage_Local();
                $storage->delete( $entry->local_path );
            }

            // Eliminar de Google Drive
            if ( ! empty( $entry->drive_id ) ) {
                $drive = new ACF7I_Storage_Drive();
                if ( $drive->is_connected() ) {
                    $drive->delete( $entry->drive_id );
                }
            }
        }

        // Eliminar el registro del log
        return (bool) $wpdb->delete(
            $this->table,
            array( 'id' => $id ),
            array( '%d' )
        );
    }

    /**
     * Elimina múltiples entradas
     */
    public function delete_bulk( $ids, $delete_files = false ) {
        $deleted = 0;
        foreach ( $ids as $id ) {
            if ( $this->delete_entry( intval( $id ), $delete_files ) ) {
                $deleted++;
            }
        }
        return $deleted;
    }

    /**
     * Actualiza el estado de una entrada
     */
    public function update_status( $id, $status ) {
        global $wpdb;
        return $wpdb->update(
            $this->table,
            array( 'status' => sanitize_text_field( $status ) ),
            array( 'id'     => intval( $id ) ),
            array( '%s' ),
            array( '%d' )
        );
    }

    /**
     * Obtiene estadísticas del log
     */
    public function get_stats() {
        global $wpdb;
        $today = current_time( 'Y-m-d' );

        return array(
            'total'           => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" ),
            'today'           => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE DATE(uploaded_at) = %s", $today ) ),
            'errors'          => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table} WHERE status = 'error'" ),
            'total_size'      => (int) $wpdb->get_var( "SELECT SUM(filesize) FROM {$this->table}" ),
            'by_storage'      => $wpdb->get_results( "SELECT storage, COUNT(*) as count FROM {$this->table} GROUP BY storage" ),
            'by_type'         => $wpdb->get_results( "SELECT filetype, COUNT(*) as count FROM {$this->table} GROUP BY filetype ORDER BY count DESC LIMIT 10" ),
            'by_form'         => $wpdb->get_results( "SELECT form_title, COUNT(*) as count FROM {$this->table} GROUP BY form_id ORDER BY count DESC LIMIT 10" ),
        );
    }

    /**
     * Obtiene los tipos de archivo únicos para el filtro
     */
    public function get_unique_filetypes() {
        global $wpdb;
        return $wpdb->get_col( "SELECT DISTINCT filetype FROM {$this->table} ORDER BY filetype ASC" );
    }

    /**
     * Obtiene formularios únicos para el filtro
     */
    public function get_unique_forms() {
        global $wpdb;
        return $wpdb->get_results( "SELECT DISTINCT form_id, form_title FROM {$this->table} ORDER BY form_title ASC" );
    }
}