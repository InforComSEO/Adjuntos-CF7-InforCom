<?php if ( ! defined( 'ABSPATH' ) ) exit;
$s = $settings->get_settings();
$allowed = $s['allowed_types'] ?? array( 'jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx', 'mp4', 'zip' );

$file_groups = array(
    'imagenes' => array(
        'label' => '🖼️ ' . __( 'Imágenes', ACF7I_TEXT_DOMAIN ),
        'types' => array(
            'jpg'  => 'JPG / JPEG', 'jpeg' => null,
            'png'  => 'PNG',  'gif'  => 'GIF',
            'webp' => 'WEBP', 'svg'  => 'SVG',
            'bmp'  => 'BMP',  'ico'  => 'ICO',
            'tiff' => 'TIFF', 'heic' => 'HEIC',
            'avif' => 'AVIF',
        ),
    ),
    'documentos' => array(
        'label' => '📄 ' . __( 'Documentos', ACF7I_TEXT_DOMAIN ),
        'types' => array(
            'pdf'  => 'PDF ⭐', 'doc'  => 'DOC',
            'docx' => 'DOCX ⭐','xls'  => 'XLS',
            'xlsx' => 'XLSX ⭐','ppt'  => 'PPT',
            'pptx' => 'PPTX',  'odt'  => 'ODT',
            'ods'  => 'ODS',   'odp'  => 'ODP',
            'rtf'  => 'RTF',   'txt'  => 'TXT',
            'csv'  => 'CSV',
        ),
    ),
    'audio' => array(
        'label' => '🎵 ' . __( 'Audio', ACF7I_TEXT_DOMAIN ),
        'types' => array(
            'mp3'  => 'MP3 ⭐', 'wav' => 'WAV',
            'ogg'  => 'OGG',   'm4a' => 'M4A',
            'flac' => 'FLAC',  'aac' => 'AAC',
            'wma'  => 'WMA',   'aiff'=> 'AIFF',
        ),
    ),
    'video' => array(
        'label' => '🎬 ' . __( 'Video', ACF7I_TEXT_DOMAIN ),
        'types' => array(
            'mp4'  => 'MP4 ⭐', 'mov' => 'MOV',
            'avi'  => 'AVI',   'mkv' => 'MKV',
            'webm' => 'WEBM',  'wmv' => 'WMV',
            'flv'  => 'FLV',   'm4v' => 'M4V',
            '3gp'  => '3GP',
        ),
    ),
    'comprimidos' => array(
        'label' => '🗜️ ' . __( 'Comprimidos', ACF7I_TEXT_DOMAIN ),
        'types' => array(
            'zip' => 'ZIP ⭐', 'rar' => 'RAR',
            '7z'  => '7Z',    'tar' => 'TAR',
            'gz'  => 'GZ',    'bz2' => 'BZ2',
        ),
    ),
    'diseno' => array(
        'label' => '🎨 ' . __( 'Diseño', ACF7I_TEXT_DOMAIN ),
        'types' => array(
            'psd'    => 'PSD', 'ai'  => 'AI',
            'eps'    => 'EPS', 'fig' => 'FIG',
            'sketch' => 'SKETCH',
        ),
    ),
    'codigo' => array(
        'label' => '💻 ' . __( 'Código', ACF7I_TEXT_DOMAIN ),
        'types' => array(
            'html' => 'HTML', 'css'  => 'CSS',
            'js'   => 'JS',   'json' => 'JSON',
            'xml'  => 'XML',  'sql'  => 'SQL',
            'md'   => 'MD',   'yaml' => 'YAML',
        ),
    ),
);

// Tipos peligrosos
$dangerous_types = array( 'php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'sh', 'bat', 'cmd', 'env', 'js' );
?>

<!-- Contador de tipos activos -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">📁 <?php _e( 'Tipos de Archivos Permitidos', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-filetypes-toolbar">
        <button type="button" class="acf7i-btn acf7i-btn-secondary acf7i-sm" id="acf7i-allow-all">
            ✅ <?php _e( 'Permitir todos', ACF7I_TEXT_DOMAIN ); ?>
        </button>
        <button type="button" class="acf7i-btn acf7i-btn-secondary acf7i-sm" id="acf7i-deny-all">
            ❌ <?php _e( 'Desmarcar todos', ACF7I_TEXT_DOMAIN ); ?>
        </button>
        <span class="acf7i-type-counter">
            <?php _e( 'Activos:', ACF7I_TEXT_DOMAIN ); ?>
            <strong id="acf7i-type-count"><?php echo count( $allowed ); ?></strong>
            <?php _e( 'tipos', ACF7I_TEXT_DOMAIN ); ?>
        </span>
    </div>

    <?php foreach ( $file_groups as $group_key => $group ) :
        // Filtrar null (alias como jpeg que comparte con jpg)
        $group_types = array_filter( $group['types'], fn( $v ) => $v !== null );
        $group_exts  = array_keys( $group['types'] );
        $all_checked = count( array_intersect( $group_exts, $allowed ) ) === count( $group_exts );
    ?>
    <div class="acf7i-filetype-group">
        <div class="acf7i-filetype-group-header">
            <span class="acf7i-group-label"><?php echo esc_html( $group['label'] ); ?></span>
            <div class="acf7i-group-actions">
                <button type="button" class="acf7i-btn-link acf7i-group-check-all"
                        data-group="<?php echo esc_attr( $group_key ); ?>">
                    <?php _e( 'Marcar todo', ACF7I_TEXT_DOMAIN ); ?>
                </button>
                <button type="button" class="acf7i-btn-link acf7i-group-uncheck-all"
                        data-group="<?php echo esc_attr( $group_key ); ?>">
                    <?php _e( 'Desmarcar todo', ACF7I_TEXT_DOMAIN ); ?>
                </button>
            </div>
        </div>

        <div class="acf7i-filetype-checkboxes" data-group="<?php echo esc_attr( $group_key ); ?>">
            <?php foreach ( $group_types as $ext => $ext_label ) :
                $is_dangerous = in_array( $ext, $dangerous_types );
                $is_checked   = in_array( $ext, $allowed );
            ?>
                <label class="acf7i-filetype-checkbox <?php echo $is_dangerous ? 'acf7i-type-danger' : ''; ?>"
                       title="<?php echo $is_dangerous ? esc_attr__( '⚠️ Tipo potencialmente peligroso', ACF7I_TEXT_DOMAIN ) : ''; ?>">
                    <input type="checkbox"
                           name="settings[allowed_types][]"
                           value="<?php echo esc_attr( $ext ); ?>"
                           class="acf7i-type-check acf7i-live"
                           data-preview="allowed-types"
                           <?php checked( $is_checked ); ?>>
                    <span class="acf7i-type-badge <?php echo $is_dangerous ? 'danger' : ''; ?>">
                        <?php echo esc_html( $ext_label ); ?>
                        <?php if ( $is_dangerous ) echo ' ⚠️'; ?>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Extensiones personalizadas -->
    <div class="acf7i-section acf7i-section-sm">
        <label><strong><?php _e( 'Extensiones personalizadas (separadas por coma):', ACF7I_TEXT_DOMAIN ); ?></strong></label>
        <input type="text" name="settings[custom_types]" class="acf7i-input"
               placeholder="ej: dwg, step, iges"
               value="<?php echo esc_attr( implode( ', ', $s['custom_types'] ?? array() ) ); ?>">
        <small><?php _e( 'Sin punto ni espacio. Ej: dwg, step, iges', ACF7I_TEXT_DOMAIN ); ?></small>
    </div>

    <!-- Aviso si hay tipos peligrosos activos -->
    <div class="acf7i-alert acf7i-alert-warning" id="acf7i-dangerous-alert" style="display:none;">
        ⚠️ <?php _e( 'Tienes tipos de archivo potencialmente peligrosos activados. Asegúrate de que tu servidor esté correctamente protegido.', ACF7I_TEXT_DOMAIN ); ?>
    </div>
</div>

<!-- Límites de cantidad -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">🔢 <?php _e( 'Cantidad de Archivos', ACF7I_TEXT_DOMAIN ); ?></h3>
    <div class="acf7i-field-grid">

        <div class="acf7i-field">
            <label><?php _e( 'Mínimo de archivos', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="0" max="20" step="1"
                       name="settings[min_files]" data-preview="min-files"
                       value="<?php echo esc_attr( $s['min_files'] ?? 0 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['min_files'] ?? 0 ); ?></span>
            </div>
            <small><?php _e( '0 = campo opcional', ACF7I_TEXT_DOMAIN ); ?></small>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Máximo de archivos', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-number-toggle-wrap">
                <div class="acf7i-slider-wrap">
                    <input type="range" class="acf7i-slider acf7i-live" min="1" max="50" step="1"
                           name="settings[max_files]" data-preview="max-files"
                           id="acf7i-max-files-slider"
                           value="<?php echo esc_attr( $s['max_files'] ?? 5 ); ?>">
                    <span class="acf7i-slider-val"><?php echo esc_html( $s['max_files'] ?? 5 ); ?></span>
                </div>
                <label class="acf7i-toggle-inline">
                    <input type="checkbox" name="settings[max_files_unlimited]" value="1"
                           id="acf7i-unlimited-files"
                           <?php checked( $s['max_files_unlimited'] ?? false ); ?>>
                    <span><?php _e( 'Sin límite', ACF7I_TEXT_DOMAIN ); ?></span>
                </label>
            </div>
        </div>

    </div>
</div>

<!-- Tamaño máximo -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">⚖️ <?php _e( 'Tamaño de Archivos', ACF7I_TEXT_DOMAIN ); ?></h3>

    <?php
    $server_upload_max = ini_get( 'upload_max_filesize' );
    $server_post_max   = ini_get( 'post_max_size' );
    ?>

    <div class="acf7i-alert acf7i-alert-info">
        ℹ️ <?php printf(
            __( 'Límite del servidor: <strong>%s</strong> por archivo — POST máx: <strong>%s</strong>', ACF7I_TEXT_DOMAIN ),
            esc_html( $server_upload_max ),
            esc_html( $server_post_max )
        ); ?>
    </div>

    <div class="acf7i-field-grid">

        <div class="acf7i-field">
            <label><?php _e( 'Tamaño máximo por archivo', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-size-input">
                <input type="number" name="settings[max_filesize]" class="acf7i-input-sm acf7i-live"
                       data-preview="max-filesize" min="1" max="9999"
                       value="<?php echo esc_attr( $s['max_filesize'] ?? 5 ); ?>">
                <select name="settings[max_filesize_unit]" class="acf7i-select-sm acf7i-live"
                        data-preview="max-filesize-unit">
                    <option value="KB" <?php selected( $s['max_filesize_unit'] ?? 'MB', 'KB' ); ?>>KB</option>
                    <option value="MB" <?php selected( $s['max_filesize_unit'] ?? 'MB', 'MB' ); ?>>MB</option>
                    <option value="GB" <?php selected( $s['max_filesize_unit'] ?? 'MB', 'GB' ); ?>>GB</option>
                </select>
            </div>
            <div class="acf7i-server-warning" id="acf7i-size-warning" style="display:none;">
                ⚠️ <?php _e( 'El valor supera el límite del servidor.', ACF7I_TEXT_DOMAIN ); ?>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Tamaño máximo total (suma)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-size-input">
                <input type="number" name="settings[max_total_size]" class="acf7i-input-sm" min="1" max="9999"
                       value="<?php echo esc_attr( $s['max_total_size'] ?? 25 ); ?>">
                <select name="settings[max_total_unit]" class="acf7i-select-sm">
                    <option value="KB" <?php selected( $s['max_total_unit'] ?? 'MB', 'KB' ); ?>>KB</option>
                    <option value="MB" <?php selected( $s['max_total_unit'] ?? 'MB', 'MB' ); ?>>MB</option>
                    <option value="GB" <?php selected( $s['max_total_unit'] ?? 'MB', 'GB' ); ?>>GB</option>
                </select>
            </div>
        </div>

    </div>
</div>