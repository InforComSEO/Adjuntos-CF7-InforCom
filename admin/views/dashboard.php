<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="acf7i-wrap">

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/header.php'; ?>

    <div class="acf7i-dashboard">

        <!-- Stats -->
        <div class="acf7i-stats-grid">

            <div class="acf7i-stat-card acf7i-stat-celeste">
                <div class="acf7i-stat-icon">📁</div>
                <div class="acf7i-stat-info">
                    <span class="acf7i-stat-number"><?php echo esc_html( $stats['total'] ?? 0 ); ?></span>
                    <span class="acf7i-stat-label"><?php _e( 'Archivos totales', ACF7I_TEXT_DOMAIN ); ?></span>
                </div>
            </div>

            <div class="acf7i-stat-card acf7i-stat-blue">
                <div class="acf7i-stat-icon">📅</div>
                <div class="acf7i-stat-info">
                    <span class="acf7i-stat-number"><?php echo esc_html( $stats['today'] ?? 0 ); ?></span>
                    <span class="acf7i-stat-label"><?php _e( 'Subidos hoy', ACF7I_TEXT_DOMAIN ); ?></span>
                </div>
            </div>

            <div class="acf7i-stat-card acf7i-stat-green">
                <div class="acf7i-stat-icon">💾</div>
                <div class="acf7i-stat-info">
                    <span class="acf7i-stat-number">
                        <?php echo esc_html( ACF7I_Dashboard::format_bytes( $stats['total_size'] ?? 0 ) ); ?>
                    </span>
                    <span class="acf7i-stat-label"><?php _e( 'Espacio usado', ACF7I_TEXT_DOMAIN ); ?></span>
                </div>
            </div>

            <div class="acf7i-stat-card acf7i-stat-red">
                <div class="acf7i-stat-icon">❌</div>
                <div class="acf7i-stat-info">
                    <span class="acf7i-stat-number"><?php echo esc_html( $stats['errors'] ?? 0 ); ?></span>
                    <span class="acf7i-stat-label"><?php _e( 'Errores', ACF7I_TEXT_DOMAIN ); ?></span>
                </div>
            </div>

        </div>

        <!-- Accesos rápidos -->
        <div class="acf7i-row" style="margin-top:20px;">

            <div class="acf7i-card">
                <div class="acf7i-card-header">
                    <h3>🚀 <?php _e( 'Accesos Rápidos', ACF7I_TEXT_DOMAIN ); ?></h3>
                </div>
                <div class="acf7i-card-body">
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=acf7i-settings' ) ); ?>"
                           class="acf7i-btn acf7i-btn-secondary">
                            ⚙️ <?php _e( 'Configuración Global', ACF7I_TEXT_DOMAIN ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=acf7i-log' ) ); ?>"
                           class="acf7i-btn acf7i-btn-secondary">
                            📁 <?php _e( 'Ver Log de Archivos', ACF7I_TEXT_DOMAIN ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=acf7i-drive' ) ); ?>"
                           class="acf7i-btn acf7i-btn-secondary">
                            ☁️ <?php _e( 'Configurar Google Drive', ACF7I_TEXT_DOMAIN ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=acf7i-cleanup' ) ); ?>"
                           class="acf7i-btn acf7i-btn-secondary">
                            🧹 <?php _e( 'Limpieza Automática', ACF7I_TEXT_DOMAIN ); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="acf7i-card">
                <div class="acf7i-card-header">
                    <h3>📋 <?php _e( 'Uso del Shortcode', ACF7I_TEXT_DOMAIN ); ?></h3>
                </div>
                <div class="acf7i-card-body">
                    <div class="acf7i-shortcode-block">
                        <label><?php _e( 'Campo de adjuntos (opcional):', ACF7I_TEXT_DOMAIN ); ?></label>
                        <div class="acf7i-shortcode-copy">
                            <code id="sc-1">[adjuntos-cf7 mis-archivos]</code>
                            <button class="acf7i-btn acf7i-btn-secondary acf7i-sm acf7i-btn-copy"
                                    data-target="sc-1">📋</button>
                        </div>
                    </div>
                    <div class="acf7i-shortcode-block">
                        <label><?php _e( 'Campo requerido:', ACF7I_TEXT_DOMAIN ); ?></label>
                        <div class="acf7i-shortcode-copy">
                            <code id="sc-2">[adjuntos-cf7* mis-archivos]</code>
                            <button class="acf7i-btn acf7i-btn-secondary acf7i-sm acf7i-btn-copy"
                                    data-target="sc-2">📋</button>
                        </div>
                    </div>
                    <div class="acf7i-shortcode-block">
                        <label><?php _e( 'En el correo CF7:', ACF7I_TEXT_DOMAIN ); ?></label>
                        <div class="acf7i-shortcode-copy">
                            <code id="sc-3">[adjuntos-cf7-mail]</code>
                            <button class="acf7i-btn acf7i-btn-secondary acf7i-sm acf7i-btn-copy"
                                    data-target="sc-3">📋</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/footer.php'; ?>

</div>