<?php if ( ! defined( 'ABSPATH' ) ) exit;
$unread = ACF7I_Notifications::get_unread_count();
?>
<div class="acf7i-header">
    <div class="acf7i-header-brand">
        <div class="acf7i-header-logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="#00BCFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="16 16 12 12 8 16"/>
                <line x1="12" y1="12" x2="12" y2="21"/>
                <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
            </svg>
        </div>
        <div class="acf7i-header-title">
            <h1>
                <span class="acf7i-brand-white">Adjuntos</span>
                <span class="acf7i-brand-celeste">CF7</span>
            </h1>
            <span class="acf7i-brand-sub">InforCom · v<?php echo esc_html( ACF7I_VERSION ); ?></span>
        </div>
    </div>
    <div class="acf7i-header-actions">
        <div class="acf7i-notifications-wrap">
            <button type="button" class="acf7i-bell-btn" id="acf7i-bell-toggle"
                    title="<?php esc_attr_e( 'Notificaciones', ACF7I_TEXT_DOMAIN ); ?>">
                🔔
                <?php if ( $unread > 0 ) : ?>
                    <span class="acf7i-bell-badge"><?php echo esc_html( $unread > 99 ? '99+' : $unread ); ?></span>
                <?php endif; ?>
            </button>
            <div class="acf7i-notifications-panel" id="acf7i-notifications-panel">
                <div class="acf7i-notif-header">
                    <h4><?php _e( 'Notificaciones', ACF7I_TEXT_DOMAIN ); ?></h4>
                    <button type="button" class="acf7i-notif-mark-all" id="acf7i-mark-all-read">
                        <?php _e( 'Marcar todas como leídas', ACF7I_TEXT_DOMAIN ); ?>
                    </button>
                </div>
                <div class="acf7i-notif-list">
                    <?php
                    $notifications = ACF7I_Notifications::get_recent( 15 );
                    if ( ! empty( $notifications ) ) :
                        foreach ( $notifications as $notif ) :
                            $icons = array( 'success' => '✅', 'error' => '❌', 'info' => 'ℹ️', 'warning' => '⚠️' );
                    ?>
                        <div class="acf7i-notif-item <?php echo $notif->read_status ? '' : 'unread'; ?>"
                             data-id="<?php echo esc_attr( $notif->id ); ?>">
                            <span class="acf7i-notif-icon"><?php echo $icons[ $notif->type ] ?? 'ℹ️'; ?></span>
                            <div class="acf7i-notif-content">
                                <p><?php echo esc_html( $notif->message ); ?></p>
                                <span class="acf7i-notif-time"><?php echo esc_html( human_time_diff( strtotime( $notif->created_at ) ) . ' ' . __( 'atrás', ACF7I_TEXT_DOMAIN ) ); ?></span>
                            </div>
                            <?php if ( ! $notif->read_status ) : ?>
                                <span class="acf7i-notif-dot"></span>
                            <?php endif; ?>
                        </div>
                    <?php
                        endforeach;
                    else : ?>
                        <div class="acf7i-notif-empty">
                            <span>🔔</span>
                            <p><?php _e( 'Sin notificaciones', ACF7I_TEXT_DOMAIN ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=acf7i-settings' ) ); ?>"
           class="acf7i-header-link">⚙️ <?php _e( 'Configuración', ACF7I_TEXT_DOMAIN ); ?></a>
        <a href="https://inforcom.es" target="_blank" class="acf7i-header-link">🌐 InforCom</a>
    </div>
</div>

<!-- Navegación -->
<nav class="acf7i-nav">
    <?php
    $nav_items = array(
        'acf7i-dashboard'     => array( 'icon' => '📊', 'label' => 'Dashboard' ),
        'acf7i-settings'      => array( 'icon' => '⚙️',  'label' => 'Config Global' ),
        'acf7i-form-settings' => array( 'icon' => '📋', 'label' => 'Por Formulario' ),
        'acf7i-log'           => array( 'icon' => '📁', 'label' => 'Log' ),
        'acf7i-drive'         => array( 'icon' => '☁️',  'label' => 'Google Drive' ),
        'acf7i-cleanup'       => array( 'icon' => '🧹', 'label' => 'Limpieza' ),
    );
    $current_page = sanitize_text_field( $_GET['page'] ?? '' );
    foreach ( $nav_items as $slug => $item ) :
    ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>"
           class="acf7i-nav-item <?php echo $current_page === $slug ? 'active' : ''; ?>">
            <?php echo esc_html( $item['icon'] . ' ' . $item['label'] ); ?>
        </a>
    <?php endforeach; ?>
</nav>