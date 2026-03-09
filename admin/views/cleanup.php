<?php if ( ! defined( 'ABSPATH' ) ) exit;

$settings     = get_option( 'acf7i_settings', array() );
$last_cleanup = get_option( 'acf7i_last_cleanup', null );
$next_cleanup = wp_next_scheduled( 'acf7i_auto_cleanup' );
$temp_dir     = ACF7I_UPLOAD_DIR . 'temp/';
$temp_count   = ACF7I_Cleanup_Page::count_temp_files( $temp_dir );
$temp_size    = ACF7I_Cleanup_Page::get_temp_size( $temp_dir );
?>

<div class="acf7i-wrap">
    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/header.php'; ?>

    <div class="acf7i-page-content">

        <div class="acf7i-page-header">
            <h2>🧹 <?php _e( 'Limpieza Automática', ACF7I_TEXT_DOMAIN ); ?></h2>
            <p><?php _e( 'Elimina archivos temporales huérfanos y mantiene el servidor limpio.', ACF7I_TEXT_DOMAIN ); ?></p>
        </div>

        <div class="acf7i-cleanup-layout">

            <!-- Estado actual -->
            <div class="acf7i-card">
                <div class="acf7i-card-header">
                    <h3>📊 <?php _e( 'Estado Actual', ACF7I_TEXT_DOMAIN ); ?></h3>
                </div>
                <div class="acf7i-card-body">

                    <div class="acf7i-cleanup-stats">

                        <div class="acf7i-cleanup-stat">
                            <span class="acf7i-cleanup-stat-icon">📁</span>
                            <div>
                                <strong><?php echo esc_html( $temp_count ); ?></strong>
                                <span><?php _e( 'Archivos temporales', ACF7I_TEXT_DOMAIN ); ?></span>
                            </div>
                        </div>

                        <div class="acf7i-cleanup-stat">
                            <span class="acf7i-cleanup-stat-icon">💾</span>
                            <div>
                                <strong><?php echo esc_html( ACF7I_Dashboard::format_bytes( $temp_size ) ); ?></strong>
                                <span><?php _e( 'Espacio temporal', ACF7I_TEXT_DOMAIN ); ?></span>
                            </div>
                        </div>

                        <div class="acf7i-cleanup-stat">
                            <span class="acf7i-cleanup-stat-icon">⏰</span>
                            <div>
                                <strong>
                                    <?php echo $next_cleanup
                                        ? esc_html( human_time_diff( $next_cleanup ) )
                                        : __( 'No programada', ACF7I_TEXT_DOMAIN ); ?>
                                </strong>
                                <span><?php _e( 'Próxima limpieza', ACF7I_TEXT_DOMAIN ); ?></span>
                            </div>
                        </div>

                        <?php if ( $last_cleanup ) : ?>
                        <div class="acf7i-cleanup-stat">
                            <span class="acf7i-cleanup-stat-icon">✅</span>
                            <div>
                                <strong>
                                    <?php echo esc_html( date_i18n(
                                        'd/m/Y H:i',
                                        strtotime( $last_cleanup['timestamp'] )
                                    )); ?>
                                </strong>
                                <span><?php _e( 'Última limpieza', ACF7I_TEXT_DOMAIN ); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <?php if ( $last_cleanup ) : ?>
                    <div class="acf7i-alert acf7i-alert-info" style="margin-top:14px;">
                        <?php printf(
                            __( 'Última ejecución: eliminados %d archivo(s) temporal(es) y %d registro(s) de BD.', ACF7I_TEXT_DOMAIN ),
                            $last_cleanup['deleted_files'] ?? 0,
                            $last_cleanup['deleted_db']    ?? 0
                        ); ?>
                    </div>
                    <?php endif; ?>

                    <div style="padding-top:16px; border-top:1px solid var(--acf7i-border); margin-top:16px; display:flex; align-items:center; gap:12px;">
                        <button type="button" class="acf7i-btn acf7i-btn-primary" id="acf7i-manual-cleanup">
                            🧹 <?php _e( 'Ejecutar limpieza ahora', ACF7I_TEXT_DOMAIN ); ?>
                        </button>
                        <span id="acf7i-cleanup-result" class="acf7i-save-status"></span>
                    </div>

                </div>
            </div>

            <!-- Configuración -->
            <div class="acf7i-card">
                <div class="acf7i-card-header">
                    <h3>⚙️ <?php _e( 'Configuración de Limpieza', ACF7I_TEXT_DOMAIN ); ?></h3>
                </div>
                <div class="acf7i-card-body">
                    <form id="acf7i-cleanup-form">
                        <input type="hidden" name="settings_type" value="global">
                        <input type="hidden" name="nonce"
                               value="<?php echo wp_create_nonce( 'acf7i_admin_nonce' ); ?>">
                        <input type="hidden" name="action" value="acf7i_save_settings">

                        <div class="acf7i-field acf7i-field-toggle" style="margin-bottom:18px;">
                            <label><?php _e( 'Limpieza automática activada', ACF7I_TEXT_DOMAIN ); ?></label>
                            <label class="acf7i-toggle">
                                <input type="checkbox" name="settings[cleanup_enabled]" value="1"
                                       <?php checked( $settings['cleanup_enabled'] ?? true ); ?>>
                                <span class="acf7i-toggle-slider"></span>
                            </label>
                        </div>

                        <div class="acf7i-field" style="margin-bottom:18px;">
                            <label><?php _e( 'Eliminar temporales con más de:', ACF7I_TEXT_DOMAIN ); ?></label>
                            <div class="acf7i-size-input">
                                <input type="number" name="settings[cleanup_interval]"
                                       class="acf7i-input-sm" min="1" max="9999"
                                       value="<?php echo esc_attr( $settings['cleanup_interval'] ?? 24 ); ?>">
                                <select name="settings[cleanup_unit]" class="acf7i-select-sm">
                                    <?php foreach ( array(
                                        'minutes' => __( 'Minutos', ACF7I_TEXT_DOMAIN ),
                                        'hours'   => __( 'Horas',   ACF7I_TEXT_DOMAIN ),
                                        'days'    => __( 'Días',    ACF7I_TEXT_DOMAIN ),
                                        'weeks'   => __( 'Semanas', ACF7I_TEXT_DOMAIN ),
                                    ) as $val => $lbl ) : ?>
                                        <option value="<?php echo esc_attr( $val ); ?>"
                                            <?php selected( $settings['cleanup_unit'] ?? 'hours', $val ); ?>>
                                            <?php echo esc_html( $lbl ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small><?php _e( 'Solo se eliminan archivos huérfanos (no procesados). Los archivos finales nunca se tocan.', ACF7I_TEXT_DOMAIN ); ?></small>
                        </div>

                        <div class="acf7i-alert acf7i-alert-warning">
                            ⚠️ <?php _e( 'Un tiempo muy corto puede eliminar archivos de formularios que tardan en procesarse. Recomendado: 24 horas.', ACF7I_TEXT_DOMAIN ); ?>
                        </div>

                        <div style="padding-top:16px; border-top:1px solid var(--acf7i-border); margin-top:16px;">
                            <button type="submit" class="acf7i-btn acf7i-btn-primary">
                                💾 <?php _e( 'Guardar configuración', ACF7I_TEXT_DOMAIN ); ?>
                            </button>
                            <span class="acf7i-save-status" id="acf7i-cleanup-save-status"></span>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/footer.php'; ?>
</div>

<style>
.acf7i-cleanup-layout    { display:flex; flex-direction:column; gap:20px; }
.acf7i-cleanup-stats     { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; }
.acf7i-cleanup-stat      { display:flex; align-items:center; gap:12px; background:var(--acf7i-bg-page); border:1px solid var(--acf7i-border); border-radius:var(--acf7i-radius); padding:14px 16px; }
.acf7i-cleanup-stat-icon { font-size:24px; flex-shrink:0; }
.acf7i-cleanup-stat > div { display:flex; flex-direction:column; gap:2px; }
.acf7i-cleanup-stat strong { font-size:18px; font-weight:800; color:var(--acf7i-azul1); }
.acf7i-cleanup-stat span   { font-size:11px; color:var(--acf7i-text-muted); }
</style>