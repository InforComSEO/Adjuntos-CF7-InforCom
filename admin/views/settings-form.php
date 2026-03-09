<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="acf7i-wrap">

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/header.php'; ?>

    <div class="acf7i-page-header">
        <h2>📋 <?php _e( 'Configuración por Formulario', ACF7I_TEXT_DOMAIN ); ?></h2>
        <p><?php _e( 'Sobreescribe la configuración global para un formulario específico.', ACF7I_TEXT_DOMAIN ); ?></p>
    </div>

    <!-- Selector de formulario -->
    <div class="acf7i-form-selector-wrap">
        <label for="acf7i-form-select"><strong><?php _e( 'Selecciona un formulario:', ACF7I_TEXT_DOMAIN ); ?></strong></label>
        <select id="acf7i-form-select" class="acf7i-select acf7i-form-switcher">
            <option value=""><?php _e( '— Elige un formulario —', ACF7I_TEXT_DOMAIN ); ?></option>
            <?php foreach ( $forms as $form ) : ?>
                <option value="<?php echo esc_attr( $form->ID ); ?>"
                    <?php selected( $form_id, $form->ID ); ?>>
                    <?php echo esc_html( $form->post_title ); ?>
                    (ID: <?php echo esc_html( $form->ID ); ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ( $form_id > 0 ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?post=' . $form_id . '&action=edit' ) ); ?>"
               class="acf7i-btn acf7i-btn-secondary" target="_blank">
                ✏️ <?php _e( 'Editar formulario en CF7', ACF7I_TEXT_DOMAIN ); ?>
            </a>
            <button type="button" class="acf7i-btn acf7i-btn-danger" id="acf7i-reset-form-config"
                    data-form-id="<?php echo esc_attr( $form_id ); ?>">
                🗑️ <?php _e( 'Eliminar config de este formulario', ACF7I_TEXT_DOMAIN ); ?>
            </button>
        <?php endif; ?>
    </div>

    <?php if ( $form_id > 0 ) : ?>

        <?php
        // Reutilizamos el mismo layout de settings-global
        // pero con form_id y settings_merged
        $settings = new class( $settings_merged ) {
            private $data;
            public function __construct( $data ) { $this->data = $data; }
            public function get( $key, $default = '' ) { return $this->data[ $key ] ?? $default; }
            public function get_settings() { return $this->data; }
            public function get_tabs_list() {
                return array(
                    'apariencia'     => array( 'icon' => '🎨', 'label' => 'Apariencia' ),
                    'textos'         => array( 'icon' => '✏️',  'label' => 'Textos' ),
                    'archivos'       => array( 'icon' => '📁', 'label' => 'Archivos' ),
                    'errores'        => array( 'icon' => '❌', 'label' => 'Errores y Mensajes' ),
                    'correo'         => array( 'icon' => '📧', 'label' => 'Correo' ),
                    'almacenamiento' => array( 'icon' => '💾', 'label' => 'Almacenamiento' ),
                );
            }
        };
        $active_tab = sanitize_key( $_GET['tab'] ?? 'apariencia' );
        ?>

        <div class="acf7i-settings-layout">

            <!-- Panel de configuración -->
            <div class="acf7i-settings-panel" id="acf7i-settings-panel">

                <div class="acf7i-panel-header">
                    <h3>
                        📋 <?php echo esc_html( get_the_title( $form_id ) ); ?>
                        <span class="acf7i-badge acf7i-badge-info">ID: <?php echo esc_html( $form_id ); ?></span>
                    </h3>
                    <p><?php _e( 'Los campos vacíos heredan la configuración global.', ACF7I_TEXT_DOMAIN ); ?></p>
                </div>

                <!-- Tabs -->
                <div class="acf7i-tabs">
                    <?php foreach ( $settings->get_tabs_list() as $tab_key => $tab ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=acf7i-form-settings&form_id=' . $form_id . '&tab=' . $tab_key ) ); ?>"
                           class="acf7i-tab <?php echo $active_tab === $tab_key ? 'active' : ''; ?>"
                           data-tab="<?php echo esc_attr( $tab_key ); ?>">
                            <?php echo esc_html( $tab['icon'] . ' ' . $tab['label'] ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <form id="acf7i-settings-form" class="acf7i-form">
                    <input type="hidden" name="settings_type" value="form">
                    <input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'acf7i_admin_nonce' ); ?>">

                    <div class="acf7i-tab-content <?php echo $active_tab === 'apariencia' ? 'active' : ''; ?>" data-tab="apariencia">
                        <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-apariencia.php'; ?>
                    </div>
                    <div class="acf7i-tab-content <?php echo $active_tab === 'textos' ? 'active' : ''; ?>" data-tab="textos">
                        <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-textos.php'; ?>
                    </div>
                    <div class="acf7i-tab-content <?php echo $active_tab === 'archivos' ? 'active' : ''; ?>" data-tab="archivos">
                        <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-archivos.php'; ?>
                    </div>
                    <div class="acf7i-tab-content <?php echo $active_tab === 'errores' ? 'active' : ''; ?>" data-tab="errores">
                        <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-errores.php'; ?>
                    </div>
                    <div class="acf7i-tab-content <?php echo $active_tab === 'correo' ? 'active' : ''; ?>" data-tab="correo">
                        <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-correo.php'; ?>
                    </div>
                    <div class="acf7i-tab-content <?php echo $active_tab === 'almacenamiento' ? 'active' : ''; ?>" data-tab="almacenamiento">
                        <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/tab-almacenamiento.php'; ?>
                    </div>

                    <div class="acf7i-form-actions">
                        <button type="submit" class="acf7i-btn acf7i-btn-primary" id="acf7i-save-btn">
                            💾 <?php _e( 'Guardar Configuración', ACF7I_TEXT_DOMAIN ); ?>
                        </button>
                        <button type="button" class="acf7i-btn acf7i-btn-secondary" id="acf7i-reset-tab-btn">
                            🔄 <?php _e( 'Restablecer esta sección', ACF7I_TEXT_DOMAIN ); ?>
                        </button>
                        <span class="acf7i-save-status" id="acf7i-save-status"></span>
                    </div>
                </form>
            </div>

            <!-- Vista previa -->
            <div class="acf7i-preview-panel" id="acf7i-preview-panel">
                <?php include ACF7I_PLUGIN_DIR . 'admin/views/partials/preview-panel.php'; ?>
            </div>

        </div>

    <?php else : ?>
        <div class="acf7i-empty-state acf7i-empty-state-lg">
            <span>📋</span>
            <h3><?php _e( 'Selecciona un formulario para configurarlo', ACF7I_TEXT_DOMAIN ); ?></h3>
            <p><?php _e( 'Elige un formulario CF7 del selector de arriba para ver y editar su configuración individual.', ACF7I_TEXT_DOMAIN ); ?></p>
        </div>
    <?php endif; ?>

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/footer.php'; ?>
</div>