<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Limpieza automática de archivos temporales huérfanos
 */
class ACF7I_Cleaner {

    /**
     * Ejecuta la limpieza programada
     * Hook: acf7i_auto_cleanup (cada hora via WP Cron)
     */
    public function run_cleanup() {
        $settings = get_option( 'acf7i_settings', array() );

        if ( empty( $settings['cleanup_enabled'] ) ) return;

        $interval = intval( $settings['cleanup_interval'] ?? 24 );
        $unit     = $settings['cleanup_unit'] ?? 'hours';

        // Calcular el umbral de tiempo
        $threshold = $this->calculate_threshold( $interval, $unit );

        $deleted_db    = $this->cleanup_database( $threshold );
        $deleted_files = $this->cleanup_temp_files( $threshold );
        $deleted_empty = $this->cleanup_empty_dirs();

        // Registrar notificación si se limpiaron archivos
        if ( $deleted_files > 0 ) {
            $notifications = new ACF7I_Notifications();
            $notifications->on_info( sprintf(
                'Limpieza automática: %d archivo(s) temporal(es) eliminado(s).',
                $deleted_files
            ));
        }

        // Guardar último resultado
        update_option( 'acf7i_last_cleanup', array(
            'timestamp'     => current_time( 'mysql' ),
            'deleted_db'    => $deleted_db,
            'deleted_files' => $deleted_files,
            'deleted_dirs'  => $deleted_empty,
        ));

        return array(
            'deleted_db'    => $deleted_db,
            'deleted_files' => $deleted_files,
            'deleted_dirs'  => $deleted_empty,
        );
    }

    /**
     * Elimina registros huérfanos de la tabla temporal
     */
    private function cleanup_database( $threshold ) {
        global $wpdb;
        $table = $wpdb->prefix . 'acf7i_temp';

        // Primero obtener los archivos a eliminar
        $orphans = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT filepath FROM {$table} WHERE created_at < %s",
                $threshold
            )
        );

        // Eliminar archivos físicos
        foreach ( $orphans as $filepath ) {
            if ( file_exists( $filepath ) ) {
                @unlink( $filepath );
            }
        }

        // Eliminar registros de DB
        return (int) $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE created_at < %s",
                $threshold
            )
        );
    }

    /**
     * Limpia archivos temporales huérfanos de la carpeta /temp/
     */
    private function cleanup_temp_files( $threshold ) {
        $temp_dir    = ACF7I_UPLOAD_DIR . 'temp/';
        $deleted     = 0;
        $threshold_ts= strtotime( $threshold );

        if ( ! file_exists( $temp_dir ) ) return 0;

        $files = glob( $temp_dir . '*' );
        if ( ! $files ) return 0;

        foreach ( $files as $file ) {
            // Saltar protecciones
            if ( in_array( basename( $file ), array( '.htaccess', 'index.php', '.', '..' ) ) ) {
                continue;
            }

            if ( is_file( $file ) && filemtime( $file ) < $threshold_ts ) {
                if ( @unlink( $file ) ) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Elimina directorios vacíos dentro de la carpeta del plugin
     */
    private function cleanup_empty_dirs() {
        $base    = ACF7I_UPLOAD_DIR;
        $deleted = 0;

        if ( ! file_exists( $base ) ) return 0;

        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $base, RecursiveDirectoryIterator::SKIP_DOTS ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ( $iter as $path ) {
            if ( $path->isDir() ) {
                $dir_path = $path->getPathname();
                // Solo eliminar si está vacío (excepto archivos de protección)
                $contents = array_diff(
                    scandir( $dir_path ),
                    array( '.', '..', '.htaccess', 'index.php' )
                );
                if ( empty( $contents ) ) {
                    // No eliminar la carpeta raíz
                    if ( realpath( $dir_path ) !== realpath( $base ) ) {
                        @rmdir( $dir_path );
                        $deleted++;
                    }
                }
            }
        }

        return $deleted;
    }

    /**
     * Calcula el timestamp umbral según la configuración
     */
    private function calculate_threshold( $interval, $unit ) {
        $seconds_map = array(
            'minutes' => MINUTE_IN_SECONDS,
            'hours'   => HOUR_IN_SECONDS,
            'days'    => DAY_IN_SECONDS,
            'weeks'   => WEEK_IN_SECONDS,
        );
        $seconds = $interval * ( $seconds_map[ $unit ] ?? HOUR_IN_SECONDS );
        return date( 'Y-m-d H:i:s', time() - $seconds );
    }

    /**
     * Ejecuta la limpieza manualmente desde el panel
     */
    public static function run_manual() {
        $cleaner = new self();
        return $cleaner->run_cleanup();
    }
}