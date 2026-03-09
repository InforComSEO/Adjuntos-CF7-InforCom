<?php if ( ! defined( 'ABSPATH' ) ) exit;

$notifications = ACF7I_Notifications::get_recent( 15 );
?>

<div class="acf7i-notif-header">
    <h4><?php _e( 'Notificaciones', ACF7I_TEXT_DOMAIN ); ?></h4>
    <?php if ( ! empty( $notifications ) ) : ?>
        <button class="acf7i-notif-mark-all" id="acf7i-mark-all-read">
            <?php _e( 'Marcar todas como leídas', ACF7I_TEXT_DOMAIN ); ?>
        </button>
    <?php endif; ?>
</div>

<div class="acf7i-notif-list">
    <?php if ( ! empty( $notifications ) ) : ?>
        <?php foreach ( $notifications as $notif ) : ?>
            <div class="acf7i-notif-item <?php echo ! $notif->read_status ? 'unread' : ''; ?>"
                 data-id="<?php echo esc_attr( $notif->id ); ?>">
                <div class="acf7i-notif-icon">
                    <?php echo $notif->type === 'success' ? '✅' : ( $notif->type === 'error' ? '❌' : 'ℹ️' ); ?>
                </div>
                <div class="acf7i-notif-content">
                    <p><?php echo esc_html( $notif->message ); ?></p>
                    <span class="acf7i-notif-time">
                        <?php echo esc_html( human_time_diff( strtotime( $notif->created_at ), current_time( 'timestamp' ) ) ); ?>
                        <?php _e( 'atrás', ACF7I_TEXT_DOMAIN ); ?>
                    </span>
                </div>
                <?php if ( ! $notif->read_status ) : ?>
                    <div class="acf7i-notif-dot"></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="acf7i-notif-empty">
            <span>🔔</span>
            <p><?php _e( 'No hay notificaciones nuevas.', ACF7I_TEXT_DOMAIN ); ?></p>
        </div>
    <?php endif; ?>
</div>