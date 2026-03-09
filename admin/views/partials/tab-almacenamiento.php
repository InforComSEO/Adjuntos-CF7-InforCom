<?php if ( ! defined( 'ABSPATH' ) ) exit;
$s          = $settings->get_settings();
$folder     = sanitize_file_name( $s['local_folder'] ?? 'cf7-adjuntos' );
$full_path  = WP_CONTENT_DIR . '/uploads/' . $folder . '/';
$full_url   = WP_CONTENT_URL  . '/uploads/' . $folder . '/';
?>

<!-- Destino -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">🎯 <?php _e( 'Destino de los Archivos', ACF7I_TEXT_DOMAIN ); ?></h3>
    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-toggle">
            <label>📁 <?php _e( 'Guardar en servidor local (uploads/)', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[storage_local]" value="1"
                       id="acf7i-storage-local"
                       <?php checked( $s['storage_local'] ?? true ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label>☁️ <?php _e( 'Guardar en Google Drive', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[storage_drive]" value="1"
                       id="acf7i-storage-drive"
                       <?php checked( $s['storage_drive'] ?? false ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

    </div>
    <div class="acf7i-alert acf7i-alert-warning" id="acf7i-no-storage-alert" style="display:none;">
        ⚠️ <?php _e( 'Debes tener al menos un destino activo.', ACF7I_TEXT_DOMAIN ); ?>
    </div>
</div>

<!-- Almacenamiento Local -->
<div class="acf7i-section" id="acf7i-local-section">
    <h3 class="acf7i-section-title">📁 <?php _e( 'Almacenamiento Local', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-path-display">
        <label><?php _e( 'Ruta actual en el servidor:', ACF7I_TEXT_DOMAIN ); ?></label>
        <div class="acf7i-path-box">
            <code id="acf7i-path-preview"><?php echo esc_html( $full_path ); ?></code>
            <?php if ( is_writable( $full_path ) || is_writable( dirname( $full_path ) ) ) : ?>
                <span class="acf7i-badge acf7i-badge-success">✅ <?php _e( 'Con permisos', ACF7I_TEXT_DOMAIN ); ?></span>
            <?php else : ?>
                <span class="acf7i-badge acf7i-badge-error">❌ <?php _e( 'Sin permisos de escritura', ACF7I_TEXT_DOMAIN ); ?></span>
            <?php endif; ?>
        </div>
        <label><?php _e( 'URL pública del archivo:', ACF7I_TEXT_DOMAIN ); ?></label>
        <div class="acf7i-path-box">
            <code><?php echo esc_html( $full_url ); ?></code>
        </div>
    </div>

    <div class="acf7i-field-grid">

        <div class="acf7i-field acf7i-field-full">
            <label><?php _e( 'Nombre de la carpeta de subida', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-path-input-wrap">
                <span class="acf7i-path-prefix">wp-content/uploads/</span>
                <input type="text" name="settings[local_folder]" class="acf7i-input"
                       id="acf7i-folder-name"
                       value="<?php echo esc_attr( $s['local_folder'] ?? 'cf7-adjuntos' ); ?>"
                       placeholder="cf7-adjuntos">
                <span class="acf7i-path-suffix">/</span>
            </div>
            <small><?php _e( 'Solo letras minúsculas, números y guiones. Sin espacios.', ACF7I_TEXT_DOMAIN ); ?></small>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Subcarpeta por formulario', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[subfolder_by_form]" value="1"
                       id="acf7i-subfolder-form"
                       <?php checked( $s['subfolder_by_form'] ?? true ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field" id="acf7i-subfolder-name-wrap">
            <label><?php _e( 'Nombre de subcarpeta del formulario', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[subfolder_form_name]" class="acf7i-select">
                <option value="title" <?php selected( $s['subfolder_form_name'] ?? 'title', 'title' ); ?>>
                    <?php _e( 'Título del formulario (por defecto)', ACF7I_TEXT_DOMAIN ); ?>
                </option>
                <option value="id" <?php selected( $s['subfolder_form_name'] ?? 'title', 'id' ); ?>>
                    <?php _e( 'ID del formulario', ACF7I_TEXT_DOMAIN ); ?>
                </option>
                <option value="custom" <?php selected( $s['subfolder_form_name'] ?? 'title', 'custom' ); ?>>
                    <?php _e( 'Nombre personalizado por formulario', ACF7I_TEXT_DOMAIN ); ?>
                </option>
            </select>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Subcarpeta por fecha (YYYY/MM/)', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[subfolder_by_date]" value="1"
                       <?php checked( $s['subfolder_by_date'] ?? false ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

    </div>
</div>

<!-- Nomenclatura de archivos -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">✏️ <?php _e( 'Nomenclatura de Archivos', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-alert acf7i-alert-info">
        ℹ️ <?php _e( 'El archivo se guarda como: <strong>nombre original + fecha + hora + extensión</strong>', ACF7I_TEXT_DOMAIN ); ?>
        <br>
        <?php _e( 'Ejemplo:', ACF7I_TEXT_DOMAIN ); ?>
        <strong><span id="acf7i-filename-preview">pago mayo 08032026 1230.png</span></strong>
    </div>

    <div class="acf7i-field-grid">

        <div class="acf7i-field">
            <label><?php _e( 'Formato de fecha', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[filename_date_format]" class="acf7i-select" id="acf7i-date-format">
                <?php
                $date_formats = array(
                    'dmY'        => 'DDMMYYYY — ' . date( 'dmY' ),
                    'Ymd'        => 'YYYYMMDD — ' . date( 'Ymd' ),
                    'd-m-Y'      => 'DD-MM-YYYY — ' . date( 'd-m-Y' ),
                    'Y-m-d'      => 'YYYY-MM-DD — ' . date( 'Y-m-d' ),
                );
                foreach ( $date_formats as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['filename_date_format'] ?? 'dmY', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Formato de hora', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[filename_time_format]" class="acf7i-select" id="acf7i-time-format">
                <?php
                $time_formats = array(
                    'Hi'     => 'HHmm — '   . date( 'Hi' ),
                    'H-i'    => 'HH-mm — '  . date( 'H-i' ),
                    'H:i'    => 'HH:mm — '  . date( 'H:i' ),
                    'H:i:s'  => 'HH:mm:ss — ' . date( 'H:i:s' ),
                );
                foreach ( $time_formats as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['filename_time_format'] ?? 'Hi', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Separador entre nombre y fecha', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[filename_separator]" class="acf7i-select" id="acf7i-separator">
                <option value=" "  <?php selected( $s['filename_separator'] ?? ' ', ' ' ); ?>><?php _e( 'Espacio', ACF7I_TEXT_DOMAIN ); ?></option>
                <option value="-"  <?php selected( $s['filename_separator'] ?? ' ', '-' ); ?>>Guión (-)</option>
                <option value="_"  <?php selected( $s['filename_separator'] ?? ' ', '_' ); ?>><?php _e( 'Guión bajo (_)', ACF7I_TEXT_DOMAIN ); ?></option>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Espacios en el nombre original', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[filename_spaces]" class="acf7i-select" id="acf7i-spaces">
                <option value="keep"       <?php selected( $s['filename_spaces'] ?? 'keep',  'keep' ); ?>><?php _e( 'Conservar espacios', ACF7I_TEXT_DOMAIN ); ?></option>
                <option value="dash"       <?php selected( $s['filename_spaces'] ?? 'keep',  'dash' ); ?>><?php _e( 'Reemplazar con guión', ACF7I_TEXT_DOMAIN ); ?></option>
                <option value="underscore" <?php selected( $s['filename_spaces'] ?? 'keep',  'underscore' ); ?>><?php _e( 'Reemplazar con guión bajo', ACF7I_TEXT_DOMAIN ); ?></option>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Prefijo personalizado', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" name="settings[filename_prefix]" class="acf7i-input"
                   id="acf7i-prefix"
                   value="<?php echo esc_attr( $s['filename_prefix'] ?? '' ); ?>"
                   placeholder="<?php _e( 'Ej: cliente-', ACF7I_TEXT_DOMAIN ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Sufijo personalizado', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" name="settings[filename_suffix]" class="acf7i-input"
                   id="acf7i-suffix"
                   value="<?php echo esc_attr( $s['filename_suffix'] ?? '' ); ?>"
                   placeholder="<?php _e( 'Ej: -cf7', ACF7I_TEXT_DOMAIN ); ?>">
        </div>

    </div>
</div>