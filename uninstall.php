<?php
// Solo ejecutar si WordPress lo llama directamente
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Eliminar opciones
delete_option( 'acf7i_settings' );
delete_option( 'acf7i_db_version' );
delete_option( 'acf7i_drive_credentials' );
delete_option( 'acf7i_drive_token' );

// Eliminar opciones por formulario (post meta)
$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_acf7i_form_settings' ) );

// Eliminar tablas
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}acf7i_log" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}acf7i_temp" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}acf7i_notifications" );

// Cancelar tareas programadas
wp_clear_scheduled_hook( 'acf7i_auto_cleanup' );