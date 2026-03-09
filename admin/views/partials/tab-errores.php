<?php if ( ! defined( 'ABSPATH' ) ) exit;
$s = $settings->get_settings();

$error_types = array(
    'tipo_no_permitido' => array(
        'label'   => __( 'Tipo no permitido', ACF7I_TEXT_DOMAIN ),
        'default' => '"{filename}" no está permitido. Solo se aceptan: {tipos}',
        'vars'    => '{filename}, {tipos}',
    ),
    'archivo_muy_grande' => array(
        'label'   => __( 'Archivo muy grande', ACF7I_TEXT_DOMAIN ),
        'default' => '"{filename}" supera el límite de {maxsize}',
        'vars'    => '{filename}, {maxsize}',
    ),
    'total_muy_grande' => array(
        'label'   => __( 'Total muy grande', ACF7I_TEXT_DOMAIN ),
        'default' => 'El total de archivos supera el límite de {totalsize}',
        'vars'    => '{totalsize}',
    ),
    'maximo_archivos' => array(
        'label'   => __( 'Máximo de archivos', ACF7I_TEXT_DOMAIN ),
        'default' => 'Solo puedes subir un máximo de {maxfiles} archivos',
        'vars'    => '{maxfiles}',
    ),
    'minimo_archivos' => array(
        'label'   => __( 'Mínimo de archivos', ACF7I_TEXT_DOMAIN ),
        'default' => 'Debes subir al menos {minfiles} archivos',
        'vars'    => '{minfiles}',
    ),
    'archivo_duplicado' => array(
        'label'   => __( 'Archivo duplicado', ACF7I_TEXT_DOMAIN ),
        'default' => '"{filename}" ya fue agregado',
        'vars'    => '{filename}',
    ),
    'archivo_corrupto' => array(
        'label'   => __( 'Archivo corrupto', ACF7I_TEXT_DOMAIN ),
        'default' => '"{filename}" no se puede leer o está dañado',
        'vars'    => '{filename}',
    ),
    'campo_requerido' => array(
        'label'   => __( 'Campo requerido', ACF7I_TEXT_DOMAIN ),
        'default' => 'Debes adjuntar al menos un archivo antes de enviar',
        'vars'    => '',
    ),
    'error_servidor' => array(
        'label'   => __( 'Error del servidor', ACF7I_TEXT_DOMAIN ),
        'default' => 'Error al subir "{filename}". Intenta nuevamente',
        'vars'    => '{filename}',
    ),
    'timeout_subida' => array(
        'label'   => __( 'Timeout de subida', ACF7I_TEXT_DOMAIN ),
        'default' => 'La subida de "{filename}" tardó demasiado. Intenta con un archivo más pequeño',
        'vars'    => '{filename}',
    ),
);

$success_types = array(
    'subida_exitosa' => array(
        'label'   => __( 'Archivo subido correctamente', ACF7I_TEXT_DOMAIN ),
        'default' => '"{filename}" se adjuntó correctamente',
        'vars'    => '{filename}',
    ),
    'todos_listos' => array(
        'label'   => __( 'Todos los archivos listos', ACF7I_TEXT_DOMAIN ),
        'default' => 'Todos los archivos están listos para enviar',
        'vars'    => '',
    ),
);
?>

<!-- Simulador de errores -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">🧪 <?php _e( 'Simular Error en Vista Previa', ACF7I_TEXT_DOMAIN ); ?></h3>
    <div class="acf7i-error-simulator">
        <?php foreach ( $error_types as $error_key => $error ) : ?>
            <button type="button" class="acf7i-sim-error-btn acf7i-btn acf7i-btn-secondary acf7i-sm"
                    data-error="<?php echo esc_attr( $error_key ); ?>">
                <?php echo esc_html( $error['label'] ); ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- Estilos globales de error -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">🎨 <?php _e( 'Estilos Globales de Mensajes de Error', ACF7I_TEXT_DOMAIN ); ?></h3>
    <p class="acf7i-helper-text"><?php _e( 'Estos estilos aplican a todos los errores. Puedes personalizarlos individualmente más abajo.', ACF7I_TEXT_DOMAIN ); ?></p>

    <div class="acf7i-field-grid">

        <div class="acf7i-field">
            <label><?php _e( 'Color de fondo', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[error_bg]" data-preview="error-bg"
                   value="<?php echo esc_attr( $s['error_bg'] ?? '#FFF0ED' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color del texto', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[error_color]" data-preview="error-color"
                   value="<?php echo esc_attr( $s['error_color'] ?? '#050018' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Color del borde', ACF7I_TEXT_DOMAIN ); ?></label>
            <input type="text" class="acf7i-color-picker acf7i-live"
                   name="settings[error_border_color]" data-preview="error-border-color"
                   value="<?php echo esc_attr( $s['error_border_color'] ?? '#FF3600' ); ?>">
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Tipo de borde', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[error_border_style]" class="acf7i-select acf7i-live"
                    data-preview="error-border-style">
                <?php foreach ( array( 'left-only' => 'Solo izquierda', 'solid' => 'Sólido', 'dashed' => 'Punteado', 'none' => 'Ninguno' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['error_border_style'] ?? 'left-only', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Radio del borde (px)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="0" max="20" step="1"
                       name="settings[error_radius]" data-preview="error-radius"
                       value="<?php echo esc_attr( $s['error_radius'] ?? 6 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['error_radius'] ?? 6 ); ?>px</span>
            </div>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Posición', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[error_position]" class="acf7i-select acf7i-live"
                    data-preview="error-position">
                <?php foreach ( array( 'below' => 'Debajo del campo', 'above' => 'Arriba del campo', 'inline' => 'Inline' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['error_position'] ?? 'below', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field">
            <label><?php _e( 'Animación de entrada', ACF7I_TEXT_DOMAIN ); ?></label>
            <select name="settings[error_anim_in]" class="acf7i-select acf7i-live"
                    data-preview="error-anim-in">
                <?php foreach ( array( 'fade' => 'Fade', 'slide-down' => 'Slide down', 'slide-up' => 'Slide up', 'bounce' => 'Bounce', 'none' => 'Ninguna' ) as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>"
                        <?php selected( $s['error_anim_in'] ?? 'slide-down', $val ); ?>>
                        <?php echo esc_html( $lbl ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Botón X para cerrar', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[error_closeable]" value="1"
                       class="acf7i-live" data-preview="error-closeable"
                       <?php checked( $s['error_closeable'] ?? true ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field acf7i-field-toggle">
            <label><?php _e( 'Auto-desaparecer', ACF7I_TEXT_DOMAIN ); ?></label>
            <label class="acf7i-toggle">
                <input type="checkbox" name="settings[error_autodismiss]" value="1"
                       class="acf7i-live" data-preview="error-autodismiss"
                       <?php checked( $s['error_autodismiss'] ?? false ); ?>>
                <span class="acf7i-toggle-slider"></span>
            </label>
        </div>

        <div class="acf7i-field" id="acf7i-autodismiss-time-wrap">
            <label><?php _e( 'Tiempo visible (segundos)', ACF7I_TEXT_DOMAIN ); ?></label>
            <div class="acf7i-slider-wrap">
                <input type="range" class="acf7i-slider acf7i-live" min="1" max="30" step="1"
                       name="settings[error_autodismiss_time]" data-preview="error-autodismiss-time"
                       value="<?php echo esc_attr( $s['error_autodismiss_time'] ?? 5 ); ?>">
                <span class="acf7i-slider-val"><?php echo esc_html( $s['error_autodismiss_time'] ?? 5 ); ?>s</span>
            </div>
        </div>

    </div>
</div>

<!-- Mensajes por tipo de error -->
<div class="acf7i-section">
    <h3 class="acf7i-section-title">📝 <?php _e( 'Textos de Errores', ACF7I_TEXT_DOMAIN ); ?></h3>

    <div class="acf7i-accordion">
        <?php foreach ( $error_types as $error_key => $error ) : ?>
        <div class="acf7i-accordion-item">
            <div class="acf7i-accordion-header">
                <span>❌ <?php echo esc_html( $error['label'] ); ?></span>
                <span class="acf7i-accordion-arrow">▼</span>
            </div>
            <div class="acf7i-accordion-body">
                <div class="acf7i-field acf7i-field-full">
                    <label><?php _e( 'Texto del mensaje', ACF7I_TEXT_DOMAIN ); ?></label>
                    <input type="text"
                           name="settings[error_text_<?php echo esc_attr( $error_key ); ?>]"
                           class="acf7i-input acf7i-live"
                           data-preview="error-text-<?php echo esc_attr( $error_key ); ?>"
                           value="<?php echo esc_attr( $s[ 'error_text_' . $error_key ] ?? $error['default'] ); ?>"
                           placeholder="<?php echo esc_attr( $error['default'] ); ?>">
                    <?php if ( $error['vars'] ) : ?>
                        <small><?php _e( 'Variables disponibles:', ACF7I_TEXT_DOMAIN ); ?>
                            <code><?php echo esc_html( $error['vars'] ); ?></code>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Mensajes de éxito -->
        <?php foreach ( $success_types as $msg_key => $msg ) : ?>
        <div class="acf7i-accordion-item acf7i-accordion-success">
            <div class="acf7i-accordion-header">
                <span>✅ <?php echo esc_html( $msg['label'] ); ?></span>
                <span class="acf7i-accordion-arrow">▼</span>
            </div>
            <div class="acf7i-accordion-body">
                <div class="acf7i-field acf7i-field-full">
                    <label><?php _e( 'Texto del mensaje', ACF7I_TEXT_DOMAIN ); ?></label>
                    <input type="text"
                           name="settings[msg_text_<?php echo esc_attr( $msg_key ); ?>]"
                           class="acf7i-input acf7i-live"
                           data-preview="msg-text-<?php echo esc_attr( $msg_key ); ?>"
                           value="<?php echo esc_attr( $s[ 'msg_text_' . $msg_key ] ?? $msg['default'] ); ?>"
                           placeholder="<?php echo esc_attr( $msg['default'] ); ?>">
                    <?php if ( $msg['vars'] ) : ?>
                        <small><?php _e( 'Variables:', ACF7I_TEXT_DOMAIN ); ?> <code><?php echo esc_html( $msg['vars'] ); ?></code></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>