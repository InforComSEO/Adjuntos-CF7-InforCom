<?php if ( ! defined( 'ABSPATH' ) ) exit;
$s = $settings->get_settings();
?>

<div class="acf7i-section">
    <h3 class="acf7i-section-title">📧 <?php _e( 'Modo de Envío', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-full">
            <label><?php _e( 'Tipo de envío en el correo', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-radio-group">
                <?php foreach ( array( 'link' => '🔗 Link de visualización', 'attachment' => '📎 Adjunto directo', 'both' => '🔗📎 Ambos' ) as $val => $lbl ) : ?>
                    <label class="acf7i-radio-option">
                        <input type="radio" name="settings[mail_send_mode]"
                               value="<?php echo esc_attr( $val ); ?>"
                               class="acf7i-live" data-preview="mail-send-mode"
                               <?php checked( $s['mail_send_mode'] ?? 'link', $val ); ?>>
                        <span><?php echo esc_html( $lbl ); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Opciones de adjunto -->
        <div class="acf7i-field" id="acf7i-attachment-opts">
            <label><?php _e( 'Límite de tamaño para adjuntar (mayor = solo link)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-size-input">
                <input type="number" name="settings[mail_attach_max]" class="acf7i-input-sm"
                       min="1" max="50" value="<?php echo esc_attr( $s['mail_attach_max'] ?? 10 ); ?>">
                <select name="settings[mail_attach_max_unit]" class="acf7i-select-sm">
                    <option value="MB" <?php selected( $s['mail_attach_max_unit'] ?? 'MB', 'MB' ); ?>>MB</option>
                    <option value="KB" <?php selected( $s['mail_attach_max_unit'] ?? 'MB', 'KB' ); ?>>KB</option>
                </select>
            </div>
            <small><?php _e( 'Si el archivo supera este tamaño, se enviará solo el link aunque el modo sea "Adjunto".', ACF7I_TEXT_DOMAIN ); ?></small>
        </div>

    </div>
</div>

<!-- Expiración del link -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">⏱️ <?php _e( 'Expiración del Link', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-full">
            <label><?php _e( 'El link de visualización expira:', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-radio-group">
                <?php foreach ( array( 'never' => '♾️ Nunca', 'custom' => '⏰ Después de...' ) as $val => $lbl ) : ?>
                    <label class="acf7i-radio-option">
                        <input type="radio" name="settings[mail_link_expires]"
                               value="<?php echo esc_attr( $val ); ?>"
                               class="acf7i-live" data-preview="mail-link-expires"
                               <?php checked( $s['mail_link_expires'] ?? 'never', $val ); ?>>
                        <span><?php echo esc_html( $lbl ); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="acf7i-field" id="acf7i-expire-custom-wrap">
            <label><?php _e( 'Tiempo de expiración', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-size-input">
                <input type="number" name="settings[mail_expire_value]" class="acf7i-input-sm"
                       min="1" value="<?php echo esc_attr( $s['mail_expire_value'] ?? 30 ); ?>">
                <select name="settings[mail_expire_unit]" class="acf7i-select-sm">
                    <?php foreach ( array( 'days' => 'Días', 'weeks' => 'Semanas', 'months' => 'Meses' ) as $val => $lbl ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>"
                            <?php selected( $s['mail_expire_unit'] ?? 'days', $val ); ?>>
                            <?php echo esc_html( $lbl ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

    </div>
</div>

<!-- Shortcode para el correo -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">🔖 <?php _e( 'Integración con CF7 Mail', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-alert acf7i-alert-info">
        💡 <?php _e( 'Copia este shortcode y pégalo en el cuerpo del correo dentro del editor de CF7 (pestaña "Correo").', ACF7I_TEXT_DOMAIN ); ?>
    </div>

    <div class="acf7i-shortcode-block">
        <label><?php _e( 'Shortcode para el correo:', ACF7I_TEXT_DOMAIN ); ?></label>
        <div class="acf7i-shortcode-copy">
            <code id="acf7i-mail-shortcode">[adjuntos-cf7-mail]</code>
            <button type="button" class="acf7i-btn-copy acf7i-btn acf7i-btn-secondary acf7i-sm"
                    data-target="acf7i-mail-shortcode">
                📋 <?php _e( 'Copiar', ACF7I_TEXT_DOMAIN ); ?>
            </button>
        </div>
        <small><?php _e( 'Incluye el nombre, link o adjunto del archivo según la configuración de envío.', ACF7I_TEXT_DOMAIN ); ?></small>
    </div>
</div>

<!-- Plantilla del correo -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">✉️ <?php _e( 'Plantilla del Bloque de Archivos en el Correo', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-full">
            <label><?php _e( 'Texto antes de los archivos', ACF7I_TEXT_DOMAIN ); ?></label>
            <textarea name="settings[mail_text_before]" class="acf7i-textarea" rows="2"
                      placeholder="<?php _e( 'Ej: Se adjuntaron los siguientes archivos:', ACF7I_TEXT_DOMAIN ); ?>"
            ><?php echo esc_textarea( $s['mail_text_before'] ?? 'Se adjuntaron los siguientes archivos:' ); ?></textarea>
        </div>

        <div class="acf7i-field acf7i-field-full">
            <label><?php _e( 'Texto después de los archivos', ACF7I_TEXT_DOMAIN ); ?></label>
            <textarea name="settings[mail_text_after]" class="acf7i-textarea" rows="2"
            ><?php echo esc_textarea( $s['mail_text_after'] ?? '' ); ?></textarea>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Estilo del bloque de archivos', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[mail_block_style]" class="acf7i-select">
                <?php foreach ( array( 'list' => 'Lista', 'table' => 'Tabla', 'cards' => 'Tarjetas' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['mail_block_style'] ?? 'list', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Texto del link', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" name="settings[mail_link_text]" class="acf7i-input"
                   value="<?php echo esc_attr( $s['mail_link_text'] ?? 'Ver archivo' ); ?>">
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Mostrar nombre del archivo', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[mail_show_name]" value="1"
                       <?php checked( $s['mail_show_name'] ?? true ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Mostrar tamaño del archivo', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[mail_show_size]" value="1"
                       <?php checked( $s['mail_show_size'] ?? true ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Mostrar miniatura si es imagen', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[mail_show_thumb]" value="1"
                       <?php checked( $s['mail_show_thumb'] ?? false ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

    </div>
</div>