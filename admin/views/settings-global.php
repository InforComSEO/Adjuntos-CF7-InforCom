<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="acf7i-wrap">

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/header.php'; ?>

    <div class="acf7i-settings-layout">

        <!-- Panel de configuración -->
        <div class="acf7i-settings-panel">

            <div class="acf7i-panel-header">
                <h2>⚙️ <?php _e( 'Configuración Global', ACF7I_TEXT_DOMAIN ); ?></h2>
                <p><?php _e( 'Estos valores se aplican a todos los formularios salvo que tengan config propia.', ACF7I_TEXT_DOMAIN ); ?></p>
            </div>

            <!-- Tabs -->
            <div class="acf7i-tabs">
                <?php foreach ( $settings->get_tabs_list() as $tab_key => $tab ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=acf7i-settings&tab=' . $tab_key ) ); ?>"
                       class="acf7i-tab <?php echo $active_tab === $tab_key ? 'active' : ''; ?>"
                       data-tab="<?php echo esc_attr( $tab_key ); ?>">
                        <?php echo esc_html( $tab['icon'] . ' ' . $tab['label'] ); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <form id="acf7i-settings-form" class="acf7i-form">
                <input type="hidden" name="settings_type" value="global">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'acf7i_admin_nonce' ); ?>">

                <div class="acf7i-tab-content <?php echo $active_tab === 'apariencia'     ? 'active' : ''; ?>" data-tab="apariencia">
                    <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-apariencia.php'; ?>
                </div>
                <div class="acf7i-tab-content <?php echo $active_tab === 'textos'         ? 'active' : ''; ?>" data-tab="textos">
                    <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-textos.php'; ?>
                </div>
                <div class="acf7i-tab-content <?php echo $active_tab === 'archivos'       ? 'active' : ''; ?>" data-tab="archivos">
                    <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-archivos.php'; ?>
                </div>
                <div class="acf7i-tab-content <?php echo $active_tab === 'errores'        ? 'active' : ''; ?>" data-tab="errores">
                    <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-errores.php'; ?>
                </div>
                <div class="acf7i-tab-content <?php echo $active_tab === 'correo'         ? 'active' : ''; ?>" data-tab="correo">
                    <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-correo.php'; ?>
                </div>
                <div class="acf7i-tab-content <?php echo $active_tab === 'almacenamiento' ? 'active' : ''; ?>" data-tab="almacenamiento">
                    <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-almacenamiento.php'; ?>
                </div>

                <div class="acf7i-form-actions">
                    <button type="submit" class="acf7i-btn acf7i-btn-primary" id="acf7i-save-btn">
                        💾 <?php _e( 'Guardar Configuración', ACF7I_TEXT_DOMAIN ); ?>
                    </button>
                    <span class="acf7i-save-status" id="acf7i-save-status"></span>
                </div>
            </form>
        </div>

        <!-- Vista previa -->
        <div class="acf7i-preview-panel">
            <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/preview-panel.php'; ?>
        </div>

    </div>

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/footer.php'; ?>
</div>