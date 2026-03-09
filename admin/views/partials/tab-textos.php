<?php if ( ! defined( 'ABSPATH' ) ) exit;
$s = $settings->get_settings();

$font_weights = array( '400' => 'Normal', '600' => 'Semibold', '700' => 'Bold' );
$font_sizes   = array( 10, 11, 12, 13, 14, 15, 16, 18, 20, 22, 24, 28, 32 );
?>

<!-- Orden de elementos (drag to sort) -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">🔄 <?php _e( 'Orden y Visibilidad de Elementos', ACF7I_TEXT_DOMAIN ); ?></h3>
    <p class="acf7i-helper-text"><?php _e( 'Arrastra para reordenar. Usa el toggle para mostrar u ocultar cada elemento.', ACF7I_TEXT_DOMAIN ); ?></p>

    <ul class="acf7i-sortable" id="acf7i-elements-sort">
        <?php
        $default_order = array( 'icon', 'text_main', 'text_secondary', 'text_limits', 'text_types', 'button' );
        $order         = $s['elements_order'] ?? $default_order;
        $element_labels = array(
            'icon'           => array( 'icon' => '☁️', 'label' => __( 'Ícono',               ACF7I_TEXT_DOMAIN ) ),
            'text_main'      => array( 'icon' => '✏️', 'label' => __( 'Texto principal',      ACF7I_TEXT_DOMAIN ) ),
            'text_secondary' => array( 'icon' => '✏️', 'label' => __( 'Texto secundario',     ACF7I_TEXT_DOMAIN ) ),
            'text_limits'    => array( 'icon' => 'ℹ️', 'label' => __( 'Texto de límites',     ACF7I_TEXT_DOMAIN ) ),
            'text_types'     => array( 'icon' => '📎', 'label' => __( 'Archivos permitidos',  ACF7I_TEXT_DOMAIN ) ),
            'button'         => array( 'icon' => '🔘', 'label' => __( 'Botón seleccionar',    ACF7I_TEXT_DOMAIN ) ),
        );
        foreach ( $order as $element_key ) :
            $el = $element_labels[ $element_key ] ?? array( 'icon' => '•', 'label' => $element_key );
        ?>
            <li class="acf7i-sortable-item" data-key="<?php echo esc_attr( $element_key ); ?>">
                <span class="acf7i-drag-handle">⠿</span>
                <span class="acf7i-element-label"><?php echo esc_html( $el['icon'] . ' ' . $el['label'] ); ?></span>
                <label class="acf7i-toggle acf7i-toggle-sm">
                    <input type="checkbox"
                           name="settings[element_visible_<?php echo esc_attr( $element_key ); ?>]"
                           value="1" class="acf7i-live"
                           data-preview="element-visible-<?php echo esc_attr( $element_key ); ?>"
                           <?php checked( $s[ 'element_visible_' . $element_key ] ?? true ); ?>>
                    <span class="acf7i-toggle-slider"></span>
                </label>
            </li>
        <?php endforeach; ?>
    </ul>
    <input type="hidden" name="settings[elements_order]" id="acf7i-elements-order-input"
           value="<?php echo esc_attr( implode( ',', $order ) ); ?>">
</div>

<!-- Texto principal -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">✏️ <?php _e( 'Texto Principal', ACF7I_TEXT_DOMAIN ); ?></h3>
    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-full">
            <label><?php _e( 'Texto', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" name="settings[text_main]" class="acf7i-input acf7i-live"
                   data-preview="text-main"
                   value="<?php echo esc_attr( $s['text_main'] ?? 'Arrastra tus archivos aquí' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Tamaño de fuente (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="10" max="32" step="1"
                       name="settings[text_main_size]" data-preview="text-main-size"
                       value="<?php echo esc_attr( $s['text_main_size'] ?? 18 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['text_main_size'] ?? 18 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color del texto', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[text_main_color]" data-preview="text-main-color"
                   value="<?php echo esc_attr( $s['text_main_color'] ?? '#050018' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Peso de fuente', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[text_main_weight]" class="acf7i-select acf7i-live"
                    data-preview="text-main-weight">
                <?php foreach ( $font_weights as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['text_main_weight'] ?? '700', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Cursiva', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[text_main_italic]" value="1"
                       class="acf7i-live" data-preview="text-main-italic"
                       <?php checked( $s['text_main_italic'] ?? false ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Alineación', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-btn-group acf7i-live" data-preview="text-main-align">
                <?php foreach ( array( 'left' => '⬅', 'center' => '⬛', 'right' => '➡' ) as $val => $lbl ) : ?>
                    <button type="button"
                            class="acf7i-btn-option <?php echo ( $s['text_main_align'] ?? 'center' ) === $val ? 'active' : ''; ?>"
                            data-name="settings[text_main_align]"
                            data-value="<?php echo esc_attr( $val ); ?>">
                        <?php echo esc_html( $lbl ); ?>
                    </button>
                <?php endforeach; ?>
                <input type="hidden" name="settings[text_main_align]"
                       value="<?php echo esc_attr( $s['text_main_align'] ?? 'center' ); ?>">
            </div>
        </div>

    </div>
</div>

<!-- Texto secundario -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">✏️ <?php _e( 'Texto Secundario', ACF7I_TEXT_DOMAIN ); ?></h3>
    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-full">
            <label><?php _e( 'Texto', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" name="settings[text_secondary]" class="acf7i-input acf7i-live"
                   data-preview="text-secondary"
                   value="<?php echo esc_attr( $s['text_secondary'] ?? 'o haz clic para seleccionar' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Tamaño de fuente (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="10" max="24" step="1"
                       name="settings[text_secondary_size]" data-preview="text-secondary-size"
                       value="<?php echo esc_attr( $s['text_secondary_size'] ?? 14 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['text_secondary_size'] ?? 14 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color del texto', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[text_secondary_color]" data-preview="text-secondary-color"
                   value="<?php echo esc_attr( $s['text_secondary_color'] ?? '#07325A' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Opacidad (%)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="30" max="100" step="5"
                       name="settings[text_secondary_opacity]" data-preview="text-secondary-opacity"
                       value="<?php echo esc_attr( $s['text_secondary_opacity'] ?? 80 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['text_secondary_opacity'] ?? 80 ); ?>%</span>
            </div>
        </div>

    </div>
</div>

<!-- Botón -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">🔘 <?php _e( 'Botón — Selecciona tus archivos', ACF7I_TEXT_DOMAIN ); ?></h3>
    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-full">
            <label><?php _e( 'Texto del botón', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" name="settings[btn_text]" class="acf7i-input acf7i-live"
                   data-preview="btn-text"
                   value="<?php echo esc_attr( $s['btn_text'] ?? 'Selecciona tus archivos' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de fondo', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[btn_bg]" data-preview="btn-bg"
                   value="<?php echo esc_attr( $s['btn_bg'] ?? '#00BCFF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de texto', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[btn_color]" data-preview="btn-color"
                   value="<?php echo esc_attr( $s['btn_color'] ?? '#050018' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de fondo hover', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[btn_bg_hover]" data-preview="btn-bg-hover"
                   value="<?php echo esc_attr( $s['btn_bg_hover'] ?? '#07325A' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color de texto hover', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[btn_color_hover]" data-preview="btn-color-hover"
                   value="<?php echo esc_attr( $s['btn_color_hover'] ?? '#FFFFFF' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Radio del borde (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="0" max="40" step="1"
                       name="settings[btn_radius]" data-preview="btn-radius"
                       value="<?php echo esc_attr( $s['btn_radius'] ?? 8 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['btn_radius'] ?? 8 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Tamaño de fuente (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="11" max="22" step="1"
                       name="settings[btn_font_size]" data-preview="btn-font-size"
                       value="<?php echo esc_attr( $s['btn_font_size'] ?? 14 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['btn_font_size'] ?? 14 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Estilo base', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[btn_style]" class="acf7i-select acf7i-live"
                    data-preview="btn-style">
                <?php foreach ( array( 'solid' => 'Sólido', 'outline' => 'Outline', 'ghost' => 'Ghost' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['btn_style'] ?? 'solid', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Ancho del botón', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[btn_width]" class="acf7i-select acf7i-live"
                    data-preview="btn-width">
                <?php foreach ( array( 'auto' => 'Automático', 'full' => 'Ancho completo', 'custom' => 'Personalizado' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['btn_width'] ?? 'auto', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>
</div>