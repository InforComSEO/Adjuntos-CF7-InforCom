<?php if ( ! defined( 'ABSPATH' ) ) exit;
$s = $settings->get_settings();
?>

<!-- ===========================
     ZONA DROP - CONTENEDOR
=========================== -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">📦 <?php _e( 'Zona de Drop — Contenedor', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-field-grid">

        <div class="acf7i-field">
            <label><?php _e( 'Color de fondo', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[dropzone_bg]"
                   data-preview="dropzone-bg"
                   value="<?php echo esc_attr( $s['dropzone_bg'] ?? '#FFFFFF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de fondo al arrastrar', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[dropzone_bg_hover]"
                   data-preview="dropzone-bg-hover"
                   value="<?php echo esc_attr( $s['dropzone_bg_hover'] ?? '#E8F7FF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de borde', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[dropzone_border_color]"
                   data-preview="dropzone-border-color"
                   value="<?php echo esc_attr( $s['dropzone_border_color'] ?? '#07325A' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de borde al arrastrar', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[dropzone_border_hover]"
                   data-preview="dropzone-border-hover"
                   value="<?php echo esc_attr( $s['dropzone_border_hover'] ?? '#00BCFF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Tipo de borde', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[dropzone_border_style]" class="acf7i-select acf7i-live"
                    data-preview="dropzone-border-style">
                <?php foreach ( array( 'dashed' => 'Punteado', 'solid' => 'Sólido', 'dotted' => 'Puntos', 'double' => 'Doble', 'none' => 'Ninguno' ) as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['dropzone_border_style'] ?? 'dashed', $val ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Grosor del borde (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="1" max="5" step="1"
                       name="settings[dropzone_border_width]"
                       data-preview="dropzone-border-width"
                       value="<?php echo esc_attr( $s['dropzone_border_width'] ?? 2 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['dropzone_border_width'] ?? 2 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Radio del borde (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="0" max="40" step="1"
                       name="settings[dropzone_border_radius]"
                       data-preview="dropzone-border-radius"
                       value="<?php echo esc_attr( $s['dropzone_border_radius'] ?? 12 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['dropzone_border_radius'] ?? 12 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Padding interno (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="10" max="80" step="2"
                       name="settings[dropzone_padding]"
                       data-preview="dropzone-padding"
                       value="<?php echo esc_attr( $s['dropzone_padding'] ?? 40 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['dropzone_padding'] ?? 40 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Altura mínima (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="100" max="400" step="10"
                       name="settings[dropzone_min_height]"
                       data-preview="dropzone-min-height"
                       value="<?php echo esc_attr( $s['dropzone_min_height'] ?? 200 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['dropzone_min_height'] ?? 200 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Alineación del contenido', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-btn-group acf7i-live" data-preview="dropzone-align">
                <?php foreach ( array( 'left' => '⬅ Izq', 'center' => '⬛ Centro', 'right' => 'Der ➡' ) as $val => $lbl ) : ?>
                    <button type="button" class="acf7i-btn-option <?php echo ( $s['dropzone_align'] ?? 'center' ) === $val ? 'active' : ''; ?>"
                            data-name="settings[dropzone_align]" data-value="<?php echo esc_attr( $val ); ?>">
                        <?php echo esc_html( $lbl ); ?>
                    </button>
                <?php endforeach; ?>
                <input type="hidden" name="settings[dropzone_align]" value="<?php echo esc_attr( $s['dropzone_align'] ?? 'center' ); ?>">
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Animación al entrar archivo', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[dropzone_drag_animation]" class="acf7i-select acf7i-live"
                    data-preview="dropzone-drag-animation">
                <?php foreach ( array( 'none' => 'Ninguna', 'pulse' => 'Pulso', 'glow' => 'Brillo', 'scale' => 'Escala', 'border-pulse' => 'Borde animado' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['dropzone_drag_animation'] ?? 'pulse', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Sombra', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[dropzone_shadow]" value="1"
                       class="acf7i-live" data-preview="dropzone-shadow"
                       <?php checked( $s['dropzone_shadow'] ?? false ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

    </div>
</div>

<!-- ===========================
     ÍCONO PRINCIPAL
=========================== -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">☁️ <?php _e( 'Ícono Principal', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Mostrar ícono', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[icon_show]" value="1"
                       class="acf7i-live" data-preview="icon-show"
                       <?php checked( $s['icon_show'] ?? true ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Tipo de ícono', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-icon-selector acf7i-live" data-preview="icon-type">
                <?php
                $icons = array(
                    'upload-cloud' => '☁️',
                    'folder'       => '📁',
                    'paperclip'    => '📎',
                    'arrow-up'     => '⬆️',
                    'image'        => '🖼️',
                    'file'         => '📄',
                    'custom'       => '🖌️',
                );
                foreach ( $icons as $icon_val => $icon_emoji ) :
                ?>
                    <button type="button"
                            class="acf7i-icon-option <?php echo ( $s['icon_type'] ?? 'upload-cloud' ) === $icon_val ? 'active' : ''; ?>"
                            data-name="settings[icon_type]"
                            data-value="<?php echo esc_attr( $icon_val ); ?>"
                            title="<?php echo esc_attr( $icon_val ); ?>">
                        <?php echo $icon_emoji; ?>
                    </button>
                <?php endforeach; ?>
                <input type="hidden" name="settings[icon_type]" value="<?php echo esc_attr( $s['icon_type'] ?? 'upload-cloud' ); ?>">
            </div>
        </div>

        <div class="acf7i-field" id="acf7i-custom-icon-wrap"
             style="<?php echo ( $s['icon_type'] ?? '' ) !== 'custom' ? 'display:none;' : ''; ?>">
            <label><?php _e( 'URL del ícono personalizado', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-media-field">
                <input type="text" name="settings[icon_custom_url]" class="acf7i-input acf7i-live"
                       data-preview="icon-custom-url"
                       value="<?php echo esc_attr( $s['icon_custom_url'] ?? '' ); ?>"
                       placeholder="https://...">
                <button type="button" class="acf7i-btn acf7i-btn-secondary acf7i-media-upload-btn">
                    📤 <?php _e( 'Subir', ACF7I_TEXT_DOMAIN ); ?>
                </button>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Tamaño del ícono (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="24" max="120" step="4"
                       name="settings[icon_size]"
                       data-preview="icon-size"
                       value="<?php echo esc_attr( $s['icon_size'] ?? 48 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['icon_size'] ?? 48 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color del ícono', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[icon_color]"
                   data-preview="icon-color"
                   value="<?php echo esc_attr( $s['icon_color'] ?? '#00BCFF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color del ícono hover', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[icon_color_hover]"
                   data-preview="icon-color-hover"
                   value="<?php echo esc_attr( $s['icon_color_hover'] ?? '#07325A' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Animación del ícono', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[icon_animation]" class="acf7i-select acf7i-live"
                    data-preview="icon-animation">
                <?php foreach ( array( 'none' => 'Ninguna', 'pulse' => 'Pulso', 'bounce' => 'Rebote', 'spin' => 'Girar', 'scale' => 'Escala' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['icon_animation'] ?? 'none', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>
</div>

<!-- ===========================
     FICHA DE ARCHIVO
=========================== -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">📄 <?php _e( 'Ficha de Archivo Subido', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-field-grid">

        <div class="acf7i-field">
            <label><?php _e( 'Color de fondo de la ficha', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[file_card_bg]"
                   data-preview="file-card-bg"
                   value="<?php echo esc_attr( $s['file_card_bg'] ?? '#F8FBFF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de borde de la ficha', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[file_card_border]"
                   data-preview="file-card-border"
                   value="<?php echo esc_attr( $s['file_card_border'] ?? '#07325A' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color acento izquierdo', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[file_card_accent]"
                   data-preview="file-card-accent"
                   value="<?php echo esc_attr( $s['file_card_accent'] ?? '#00BCFF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Radio del borde (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="0" max="20" step="1"
                       name="settings[file_card_radius]"
                       data-preview="file-card-radius"
                       value="<?php echo esc_attr( $s['file_card_radius'] ?? 8 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['file_card_radius'] ?? 8 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Diseño de la ficha', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[file_card_layout]" class="acf7i-select acf7i-live"
                    data-preview="file-card-layout">
                <?php foreach ( array( 'row' => 'Fila horizontal', 'card' => 'Tarjeta', 'compact' => 'Compacto' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['file_card_layout'] ?? 'row', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color del botón eliminar', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[file_remove_color]"
                   data-preview="file-remove-color"
                   value="<?php echo esc_attr( $s['file_remove_color'] ?? '#FF3600' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Animación al aparecer', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[file_card_anim_in]" class="acf7i-select acf7i-live"
                    data-preview="file-card-anim-in">
                <?php foreach ( array( 'none' => 'Ninguna', 'fade' => 'Fade', 'slide' => 'Slide', 'bounce' => 'Bounce' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['file_card_anim_in'] ?? 'fade', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>
</div>

<!-- ===========================
     BARRA DE PROGRESO
=========================== -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">📊 <?php _e( 'Barra de Progreso', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Mostrar barra de progreso', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[progress_show]" value="1"
                       class="acf7i-live" data-preview="progress-show"
                       <?php checked( $s['progress_show'] ?? true ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de progreso', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[progress_color]"
                   data-preview="progress-color"
                   value="<?php echo esc_attr( $s['progress_color'] ?? '#00BCFF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de fondo de la barra', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[progress_bg]"
                   data-preview="progress-bg"
                   value="<?php echo esc_attr( $s['progress_bg'] ?? '#E8F7FF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color en estado éxito', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[progress_success]"
                   data-preview="progress-success"
                   value="<?php echo esc_attr( $s['progress_success'] ?? '#00BCFF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color en estado error', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[progress_error]"
                   data-preview="progress-error"
                   value="<?php echo esc_attr( $s['progress_error'] ?? '#FF3600' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Altura (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="2" max="20" step="1"
                       name="settings[progress_height]"
                       data-preview="progress-height"
                       value="<?php echo esc_attr( $s['progress_height'] ?? 6 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['progress_height'] ?? 6 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Radio del borde (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="0" max="10" step="1"
                       name="settings[progress_radius]"
                       data-preview="progress-radius"
                       value="<?php echo esc_attr( $s['progress_radius'] ?? 3 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['progress_radius'] ?? 3 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Mostrar porcentaje en texto', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[progress_pct]" value="1"
                       class="acf7i-live" data-preview="progress-pct"
                       <?php checked( $s['progress_pct'] ?? true ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Estilo de animación', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[progress_animation]" class="acf7i-select acf7i-live"
                    data-preview="progress-animation">
                <?php foreach ( array( 'linear' => 'Lineal', 'pulse' => 'Pulso', 'stripes' => 'Rayas animadas' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['progress_animation'] ?? 'linear', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>
</div>