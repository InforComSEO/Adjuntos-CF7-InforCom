<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registro y validación del tag de CF7
 * Uso: [adjuntos-cf7 nombre] o [adjuntos-cf7* nombre]
 */
class ACF7I_CF7_Tag {

    /**
     * Registra el tag en CF7
     */
    public function register_tag() {
        wpcf7_add_form_tag(
            array( 'adjuntos-cf7', 'adjuntos-cf7*' ),
            array( $this, 'render_tag' ),
            array( 'name-attr' => true )
        );
    }

    /**
     * Renderiza el HTML del campo en el formulario
     */
    public function render_tag( $tag ) {
        if ( empty( $tag->name ) ) return '';

        $form_id  = $this->get_current_form_id();
        $settings = ACF7I_Admin::get_settings( $form_id );

        // Clase CSS base
        $class = wpcf7_form_controls_class( $tag->type );

        // ID único del campo
        $field_id = 'acf7i-field-' . esc_attr( $tag->name );

        // ¿Es requerido?
        $required = $tag->is_required();

        // Datos de configuración para JS
        $config = array(
            'name'            => $tag->name,
            'required'        => $required,
            'min_files'       => intval( $settings['min_files']       ?? 0 ),
            'max_files'       => intval( $settings['max_files']       ?? 5 ),
            'max_filesize'    => intval( $settings['max_filesize']    ?? 5 ),
            'max_filesize_unit' => $settings['max_filesize_unit']     ?? 'MB',
            'max_total_size'  => intval( $settings['max_total_size']  ?? 25 ),
            'max_total_unit'  => $settings['max_total_unit']          ?? 'MB',
            'allowed_types'   => $settings['allowed_types']           ?? array( 'jpg','jpeg','png','pdf' ),
            'custom_types'    => $settings['custom_types']            ?? array(),
            'nonce'           => wp_create_nonce( 'acf7i_upload_nonce' ),
            'ajax_url'        => admin_url( 'admin-ajax.php' ),
            // Textos de errores
            'errors'          => $this->get_error_texts( $settings ),
            // Textos de mensajes
            'messages'        => $this->get_message_texts( $settings ),
        );

        ob_start();
        ?>
        <div class="acf7i-field-wrap"
             id="<?php echo esc_attr( $field_id ); ?>"
             data-config="<?php echo esc_attr( json_encode( $config ) ); ?>"
             data-name="<?php echo esc_attr( $tag->name ); ?>">

            <!-- Input oculto que CF7 necesita para el submit -->
            <input type="hidden"
                   name="<?php echo esc_attr( $tag->name ); ?>"
                   id="<?php echo esc_attr( $tag->name ); ?>-hidden"
                   class="acf7i-hidden-input"
                   value="">

            <!-- Zona Drop -->
            <div class="acf7i-dropzone"
                 id="acf7i-dropzone-<?php echo esc_attr( $tag->name ); ?>"
                 role="button"
                 tabindex="0"
                 aria-label="<?php echo esc_attr( $settings['text_main'] ?? 'Arrastra o selecciona archivos' ); ?>"
                 aria-describedby="acf7i-desc-<?php echo esc_attr( $tag->name ); ?>"
                 data-name="<?php echo esc_attr( $tag->name ); ?>">

                <?php echo $this->render_dropzone_content( $settings ); ?>

                <!-- Input file real (oculto visualmente) -->
                <input type="file"
                       id="acf7i-input-<?php echo esc_attr( $tag->name ); ?>"
                       class="acf7i-file-input"
                       multiple
                       accept="<?php echo esc_attr( $this->get_accept_attr( $config ) ); ?>"
                       aria-hidden="true"
                       tabindex="-1">

            </div>

            <!-- Descripción accesible -->
            <p id="acf7i-desc-<?php echo esc_attr( $tag->name ); ?>"
               class="acf7i-sr-only">
                <?php echo esc_html( $settings['text_main'] ?? 'Arrastra o selecciona archivos' ); ?>.
                <?php if ( $required ) echo esc_html__( 'Este campo es requerido.', ACF7I_TEXT_DOMAIN ); ?>
            </p>

            <!-- Contenedor de archivos subidos -->
            <div class="acf7i-files-list"
                 id="acf7i-files-<?php echo esc_attr( $tag->name ); ?>"
                 aria-live="polite"
                 aria-label="<?php esc_attr_e( 'Archivos adjuntados', ACF7I_TEXT_DOMAIN ); ?>">
            </div>

            <!-- Contenedor de errores propios -->
            <div class="acf7i-errors-wrap"
                 id="acf7i-errors-<?php echo esc_attr( $tag->name ); ?>"
                 aria-live="assertive"
                 role="alert">
            </div>

        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderiza el contenido de la zona drop
     */
    private function render_dropzone_content( $settings ) {
        $order = isset( $settings['elements_order'] )
            ? explode( ',', $settings['elements_order'] )
            : array( 'icon', 'text_main', 'text_secondary', 'text_limits', 'text_types', 'button' );

        $allowed      = $settings['allowed_types']    ?? array( 'jpg','jpeg','png','pdf' );
        $custom_types = $settings['custom_types']     ?? array();
        $all_types    = array_merge( $allowed, array_filter( $custom_types ) );
        $separator    = $settings['text_types_sep']   ?? ' | ';
        $max_visible  = intval( $settings['text_types_max'] ?? 8 );
        $prefix       = $settings['text_types_prefix'] ?? 'Archivos permitidos:';
        $max_files    = intval( $settings['max_files'] ?? 5 );
        $max_size     = intval( $settings['max_filesize'] ?? 5 );
        $max_unit     = $settings['max_filesize_unit'] ?? 'MB';

        ob_start();

        foreach ( $order as $element ) {
            $visible_key = 'element_visible_' . $element;
            $is_visible  = isset( $settings[ $visible_key ] ) ? (bool) $settings[ $visible_key ] : true;
            if ( ! $is_visible ) continue;

            switch ( $element ) {

                case 'icon':
                    $icon_type = $settings['icon_type'] ?? 'upload-cloud';
                    $icon_size = intval( $settings['icon_size'] ?? 48 );
                    $icon_color= $settings['icon_color'] ?? '#00BCFF';
                    echo '<div class="acf7i-icon" aria-hidden="true">';
                    echo $this->get_svg_icon( $icon_type, $icon_size, $icon_color, $settings );
                    echo '</div>';
                    break;

                case 'text_main':
                    $text = $settings['text_main'] ?? 'Arrastra tus archivos aquí';
                    echo '<p class="acf7i-text-main">' . esc_html( $text ) . '</p>';
                    break;

                case 'text_secondary':
                    $text = $settings['text_secondary'] ?? 'o haz clic para seleccionar';
                    echo '<p class="acf7i-text-secondary">' . esc_html( $text ) . '</p>';
                    break;

                case 'text_limits':
                    if ( ! empty( $settings['text_limits_custom'] ) ) {
                        $limits_text = $settings['text_limits_custom'];
                        $limits_text = str_replace( '{maxfiles}', $max_files, $limits_text );
                        $limits_text = str_replace( '{maxsize}', $max_size . $max_unit, $limits_text );
                    } else {
                        $limits_text = sprintf(
                            'Máximo %d archivos | Hasta %d%s cada uno',
                            $max_files, $max_size, $max_unit
                        );
                    }
                    echo '<p class="acf7i-text-limits">' . esc_html( $limits_text ) . '</p>';
                    break;

                case 'text_types':
                    if ( ! empty( $all_types ) ) {
                        $visible_types = array_slice( $all_types, 0, $max_visible );
                        $more          = count( $all_types ) - count( $visible_types );
                        echo '<p class="acf7i-text-types">';
                        echo '<span class="acf7i-types-prefix">' . esc_html( $prefix ) . ' </span>';
                        foreach ( $visible_types as $i => $ext ) {
                            echo '<span class="acf7i-ext-badge">.' . esc_html( strtolower( $ext ) ) . '</span>';
                        }
                        if ( $more > 0 ) {
                            echo '<span class="acf7i-ext-badge acf7i-ext-more">+' . esc_html( $more ) . ' más</span>';
                        }
                        echo '</p>';
                    }
                    break;

                case 'button':
                    $btn_text = $settings['btn_text'] ?? 'Selecciona tus archivos';
                    echo '<button type="button" class="acf7i-select-btn" tabindex="0"
                               aria-label="' . esc_attr( $btn_text ) . '">';
                    echo esc_html( $btn_text );
                    echo '</button>';
                    break;
            }
        }

        return ob_get_clean();
    }

    /**
     * Devuelve el SVG del ícono según el tipo
     */
    private function get_svg_icon( $type, $size, $color, $settings ) {
        // Ícono personalizado
        if ( $type === 'custom' && ! empty( $settings['icon_custom_url'] ) ) {
            return '<img src="' . esc_url( $settings['icon_custom_url'] ) . '"
                        width="' . esc_attr( $size ) . '"
                        height="' . esc_attr( $size ) . '"
                        alt="" role="presentation">';
        }

        $svgs = array(
            'upload-cloud' => '<polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>',
            'folder'       => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
            'paperclip'    => '<path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>',
            'arrow-up'     => '<line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>',
            'image'        => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
            'file'         => '<path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/>',
        );

        $paths = $svgs[ $type ] ?? $svgs['upload-cloud'];

        return sprintf(
            '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none"
                  stroke="%s" stroke-width="1.5" stroke-linecap="round"
                  stroke-linejoin="round" class="acf7i-icon-svg"
                  role="presentation" aria-hidden="true">%s</svg>',
            $size, $size, esc_attr( $color ), $paths
        );
    }

    /**
     * Genera el atributo accept del input file
     */
    private function get_accept_attr( $config ) {
        $types   = array_merge( $config['allowed_types'], $config['custom_types'] );
        $accepts = array_map( fn( $ext ) => '.' . strtolower( trim( $ext ) ), $types );
        return implode( ',', array_unique( $accepts ) );
    }

    /**
     * Obtiene los textos de error configurados
     */
    private function get_error_texts( $settings ) {
        $defaults = array(
            'tipo_no_permitido'  => '"{filename}" no está permitido. Solo se aceptan: {tipos}',
            'archivo_muy_grande' => '"{filename}" supera el límite de {maxsize}',
            'total_muy_grande'   => 'El total de archivos supera el límite de {totalsize}',
            'maximo_archivos'    => 'Solo puedes subir un máximo de {maxfiles} archivos',
            'minimo_archivos'    => 'Debes subir al menos {minfiles} archivos',
            'archivo_duplicado'  => '"{filename}" ya fue agregado',
            'archivo_corrupto'   => '"{filename}" no se puede leer o está dañado',
            'campo_requerido'    => 'Debes adjuntar al menos un archivo antes de enviar',
            'error_servidor'     => 'Error al subir "{filename}". Intenta nuevamente',
            'timeout_subida'     => 'La subida de "{filename}" tardó demasiado',
        );

        $texts = array();
        foreach ( $defaults as $key => $default ) {
            $texts[ $key ] = $settings[ 'error_text_' . $key ] ?? $default;
        }
        return $texts;
    }

    /**
     * Obtiene los textos de mensajes de estado
     */
    private function get_message_texts( $settings ) {
        return array(
            'subida_exitosa' => $settings['msg_text_subida_exitosa'] ?? '"{filename}" se adjuntó correctamente',
            'todos_listos'   => $settings['msg_text_todos_listos']   ?? 'Todos los archivos están listos',
        );
    }

    /**
     * Obtiene el ID del formulario CF7 actual
     */
    private function get_current_form_id() {
        $form = wpcf7_get_current_contact_form();
        return $form ? $form->id() : 0;
    }

    /**
     * Validación del campo en CF7
     */
    public function validate_tag( $result, $tag ) {
        $name     = $tag->name;
        $required = $tag->is_required();
        $value    = isset( $_POST[ $name ] ) ? sanitize_text_field( $_POST[ $name ] ) : '';

        // Si es requerido y está vacío
        if ( $required && empty( $value ) ) {
            $form_id  = wpcf7_get_current_contact_form()->id();
            $settings = ACF7I_Admin::get_settings( $form_id );
            $msg      = $settings['error_text_campo_requerido']
                        ?? 'Debes adjuntar al menos un archivo antes de enviar';

            // Invalidar sin mostrar el error visual de CF7
            // Nuestro JS manejará la visualización
            $result->invalidate( $tag, '' );
        }

        return $result;
    }
}