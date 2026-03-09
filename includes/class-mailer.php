<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sistema de correos del plugin
 * Maneja el shortcode [adjuntos-cf7-mail] y el reenvío desde el log
 */
class ACF7I_Mailer {

    /**
     * Procesa el envío de archivos cuando CF7 envía el formulario
     * Llamado desde el hook wpcf7_mail_sent
     *
     * @param WPCF7_ContactForm $contact_form
     */
    public function process_form_submission( $contact_form ) {
        $form_id  = $contact_form->id();
        $settings = ACF7I_Admin::get_settings( $form_id );

        // Obtener los datos del POST
        $submission = WPCF7_Submission::get_instance();
        if ( ! $submission ) return;

        $posted_data = $submission->get_posted_data();

        // Buscar campos de adjuntos en los datos enviados
        foreach ( $posted_data as $key => $value ) {
            if ( strpos( $key, 'adjuntos' ) === false && ! $this->is_adjuntos_field( $key, $form_id ) ) {
                continue;
            }

            if ( empty( $value ) ) continue;

            // Valor puede ser string con rutas separadas por ||
            $temp_paths = is_array( $value )
                ? $value
                : explode( '||', $value );

            $temp_paths = array_filter( array_map( 'trim', $temp_paths ) );
            if ( empty( $temp_paths ) ) continue;

            // Procesar cada archivo
            foreach ( $temp_paths as $temp_path ) {
                $this->process_file(
                    $temp_path,
                    $key,
                    $form_id,
                    $settings,
                    $posted_data
                );
            }
        }
    }

    /**
     * Procesa un archivo individual: mueve, guarda en Drive, registra en log
     */
    private function process_file( $temp_path, $field_name, $form_id, $settings, $posted_data ) {

        if ( ! file_exists( $temp_path ) ) return;

        $original_name = basename( $temp_path );
        $filesize      = filesize( $temp_path );
        $ext           = strtolower( pathinfo( $temp_path, PATHINFO_EXTENSION ) );

        $local_path = null;
        $local_url  = null;
        $drive_id   = null;
        $drive_url  = null;

        // ── Almacenamiento Local ──────────────────────
        if ( ! empty( $settings['storage_local'] ) ) {
            $local_storage = new ACF7I_Storage_Local( $settings );
            $local_result  = $local_storage->store( $temp_path, $original_name, $form_id, $settings );

            if ( $local_result['success'] ) {
                $local_path = $local_result['path'];
                $local_url  = $local_result['url'];
                // temp_path ya fue movido, actualizar referencia
                $temp_path  = $local_path;
            }
        }

        // ── Almacenamiento Drive ──────────────────────
        if ( ! empty( $settings['storage_drive'] ) ) {
            $drive_storage = new ACF7I_Storage_Drive( $settings );
            if ( $drive_storage->is_connected() ) {
                $file_to_upload = $local_path ?? $temp_path;
                $drive_result   = $drive_storage->store(
                    $file_to_upload,
                    $original_name,
                    $form_id
                );

                if ( $drive_result['success'] ) {
                    $drive_id  = $drive_result['drive_id'];
                    $drive_url = $drive_result['drive_url'];
                }
            }
        }

        // Si no se pudo almacenar en ningún lado, limpiar y salir
        if ( ! $local_path && ! $drive_id ) {
            @unlink( $temp_path );
            return;
        }

        // ── Generar token de visualización ────────────
        $view_token    = ACF7I_Security::generate_view_token();
        $token_expires = ACF7I_Security::calculate_expiry( $settings );

        // ── Registrar en el log ────────────────────────
        $log     = new ACF7I_Log();
        $form    = get_post( $form_id );
        $log_id  = $log->insert( array(
            'form_id'       => $form_id,
            'form_title'    => $form ? $form->post_title : 'Form #' . $form_id,
            'filename'      => basename( $local_path ?? $temp_path ),
            'original_name' => $original_name,
            'filesize'      => $filesize,
            'filetype'      => $ext,
            'storage'       => ( $local_path && $drive_id ) ? 'both' : ( $drive_id ? 'drive' : 'local' ),
            'local_path'    => $local_path,
            'local_url'     => $local_url,
            'drive_id'      => $drive_id,
            'drive_url'     => $drive_url,
            'view_token'    => $view_token,
            'token_expires' => $token_expires,
            'sender_email'  => $posted_data['your-email'] ?? $posted_data['email'] ?? '',
            'sender_name'   => $posted_data['your-name']  ?? $posted_data['name']  ?? '',
            'status'        => 'uploaded',
        ));

        // ── Notificación ────────────────────���─────────
        $notifications = new ACF7I_Notifications();
        $notifications->on_file_uploaded( array(
            'filename'   => $original_name,
            'form_title' => $form ? $form->post_title : 'Form #' . $form_id,
        ));

        // ── Limpiar archivo temp huérfano ─────────────
        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . 'acf7i_temp',
            array( 'filepath' => $temp_path ),
            array( '%s' )
        );
    }

    /**
     * Procesa el shortcode [adjuntos-cf7-mail] dentro del correo de CF7
     * Retorna el bloque HTML/texto con los links de los archivos
     */
    public function process_mail_shortcode( $content ) {
        if ( strpos( $content, '[adjuntos-cf7-mail]' ) === false ) {
            return $content;
        }

        // Obtener los archivos del último log por sesión
        $block = $this->build_mail_block();
        return str_replace( '[adjuntos-cf7-mail]', $block, $content );
    }

    /**
     * Construye el bloque HTML de archivos para el correo
     */
    private function build_mail_block() {
        // Obtener los archivos de la sesión actual
        // CF7 procesa el correo después de wpcf7_mail_sent,
        // así que buscamos los últimos archivos registrados
        global $wpdb;

        $log_entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}acf7i_log
                 WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                 ORDER BY uploaded_at DESC
                 LIMIT 20"
            )
        );

        if ( empty( $log_entries ) ) {
            return '';
        }

        $settings   = get_option( 'acf7i_settings', array() );
        $send_mode  = $settings['mail_send_mode']  ?? 'link';
        $text_before= $settings['mail_text_before'] ?? 'Se adjuntaron los siguientes archivos:';
        $text_after = $settings['mail_text_after']  ?? '';
        $link_text  = $settings['mail_link_text']   ?? 'Ver archivo';
        $show_name  = $settings['mail_show_name']   ?? true;
        $show_size  = $settings['mail_show_size']   ?? true;
        $block_style= $settings['mail_block_style'] ?? 'list';

        $block  = '';
        $block .= $text_before ? '<p><strong>' . esc_html( $text_before ) . '</strong></p>' : '';

        if ( $block_style === 'table' ) {
            $block .= $this->build_table_block( $log_entries, $settings );
        } else {
            $block .= $this->build_list_block( $log_entries, $settings );
        }

        $block .= $text_after ? '<p>' . esc_html( $text_after ) . '</p>' : '';

        return $block;
    }

    /**
     * Construye el bloque en formato lista
     */
    private function build_list_block( $entries, $settings ) {
        $send_mode = $settings['mail_send_mode'] ?? 'link';
        $link_text = $settings['mail_link_text'] ?? 'Ver archivo';
        $show_size = $settings['mail_show_size'] ?? true;

        $html  = '<ul style="margin:10px 0; padding-left:20px;">';

        foreach ( $entries as $entry ) {
            $view_url = $this->get_view_url( $entry->view_token );

            $html .= '<li style="margin-bottom:8px;">';
            $html .= '<strong>' . esc_html( $entry->original_name ) . '</strong>';

            if ( $show_size ) {
                $html .= ' <span style="color:#8A9BB0;">(' . $this->format_bytes( $entry->filesize ) . ')</span>';
            }

            // Link según modo
            if ( in_array( $send_mode, array( 'link', 'both' ) ) ) {
                $html .= ' — <a href="' . esc_url( $view_url ) . '"
                              style="color:#00BCFF; text-decoration:none;">'
                       . esc_html( $link_text ) . '</a>';
            }

            if ( in_array( $send_mode, array( 'drive', 'both' ) ) && ! empty( $entry->drive_url ) ) {
                $html .= ' | <a href="' . esc_url( $entry->drive_url ) . '"
                                style="color:#00BCFF; text-decoration:none;">Ver en Drive</a>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }

    /**
     * Construye el bloque en formato tabla
     */
    private function build_table_block( $entries, $settings ) {
        $send_mode = $settings['mail_send_mode'] ?? 'link';
        $link_text = $settings['mail_link_text'] ?? 'Ver archivo';

        $html  = '<table style="width:100%; border-collapse:collapse; margin:10px 0;">';
        $html .= '<thead><tr style="background:#050018;">';
        $html .= '<th style="padding:10px; text-align:left; color:#FFFFFF; font-size:12px;">Archivo</th>';
        $html .= '<th style="padding:10px; text-align:left; color:#FFFFFF; font-size:12px;">Tamaño</th>';
        $html .= '<th style="padding:10px; text-align:left; color:#FFFFFF; font-size:12px;">Enlace</th>';
        $html .= '</tr></thead><tbody>';

        foreach ( $entries as $i => $entry ) {
            $bg       = $i % 2 === 0 ? '#F8FBFF' : '#FFFFFF';
            $view_url = $this->get_view_url( $entry->view_token );

            $html .= '<tr style="background:' . $bg . ';">';
            $html .= '<td style="padding:10px; font-size:13px; border-bottom:1px solid #E8EEFF;">';
            $html .= esc_html( $entry->original_name );
            $html .= '</td>';
            $html .= '<td style="padding:10px; font-size:12px; color:#8A9BB0; border-bottom:1px solid #E8EEFF;">';
            $html .= esc_html( $this->format_bytes( $entry->filesize ) );
            $html .= '</td>';
            $html .= '<td style="padding:10px; border-bottom:1px solid #E8EEFF;">';

            if ( in_array( $send_mode, array( 'link', 'both' ) ) ) {
                $html .= '<a href="' . esc_url( $view_url ) . '"
                             style="background:#00BCFF; color:#050018; padding:5px 12px;
                                    border-radius:4px; font-size:12px; text-decoration:none;
                                    font-weight:600;">'
                       . esc_html( $link_text ) . '</a>';
            }

            if ( in_array( $send_mode, array( 'drive', 'both' ) ) && ! empty( $entry->drive_url ) ) {
                $html .= ' <a href="' . esc_url( $entry->drive_url ) . '"
                              style="background:#07325A; color:#FFFFFF; padding:5px 12px;
                                     border-radius:4px; font-size:12px; text-decoration:none;">
                              Drive</a>';
            }

            $html .= '</td></tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Genera la URL de visualización con token
     */
    public function get_view_url( $token ) {
        return add_query_arg(
            array( 'acf7i-view' => $token ),
            home_url( '/' )
        );
    }

    /**
     * Reenvía el correo desde el log
     */
    public function resend_from_log( $log_id, $recipient = '' ) {
        global $wpdb;

        $entry = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}acf7i_log WHERE id = %d",
                $log_id
            )
        );

        if ( ! $entry ) return false;

        $settings  = ACF7I_Admin::get_settings( $entry->form_id );
        $send_mode = $settings['mail_send_mode']  ?? 'link';
        $link_text = $settings['mail_link_text']  ?? 'Ver archivo';

        $to      = ! empty( $recipient ) ? $recipient : $entry->sender_email;
        $subject = sprintf(
            __( 'Tus archivos adjuntos — %s', ACF7I_TEXT_DOMAIN ),
            $entry->form_title
        );

        $view_url = $this->get_view_url( $entry->view_token );

        $message  = '<p>' . __( 'Se adjuntaron los siguientes archivos:', ACF7I_TEXT_DOMAIN ) . '</p>';
        $message .= '<ul>';
        $message .= '<li><strong>' . esc_html( $entry->original_name ) . '</strong>';
        $message .= ' (' . esc_html( $this->format_bytes( $entry->filesize ) ) . ')';

        if ( in_array( $send_mode, array( 'link', 'both' ) ) ) {
            $message .= ' — <a href="' . esc_url( $view_url ) . '">' . esc_html( $link_text ) . '</a>';
        }
        if ( in_array( $send_mode, array( 'drive', 'both' ) ) && ! empty( $entry->drive_url ) ) {
            $message .= ' | <a href="' . esc_url( $entry->drive_url ) . '">Ver en Drive</a>';
        }

        $message .= '</li></ul>';

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Verifica si un campo pertenece a un tag adjuntos-cf7
     */
    private function is_adjuntos_field( $field_name, $form_id ) {
        $form = WPCF7_ContactForm::get_instance( $form_id );
        if ( ! $form ) return false;
        $tags = $form->scan_form_tags( array( 'type' => array( 'adjuntos-cf7', 'adjuntos-cf7*' ) ) );
        foreach ( $tags as $tag ) {
            if ( $tag->name === $field_name ) return true;
        }
        return false;
    }

    /**
     * Registra el hook de CF7
     */
    public function register_hooks() {
        add_action( 'wpcf7_mail_sent',    array( $this, 'process_form_submission' ) );
        add_filter( 'wpcf7_mail_components', array( $this, 'filter_mail_components' ), 10, 3 );
    }

    /**
     * Filtra los componentes del correo de CF7 para procesar el shortcode
     */
    public function filter_mail_components( $components, $contact_form, $mail ) {
        if ( isset( $components['body'] ) ) {
            $components['body'] = $this->process_mail_shortcode( $components['body'] );
        }
        return $components;
    }

    private function format_bytes( $bytes ) {
        if ( $bytes >= 1073741824 ) return round( $bytes / 1073741824, 2 ) . ' GB';
        if ( $bytes >= 1048576 )    return round( $bytes / 1048576, 2 )    . ' MB';
        if ( $bytes >= 1024 )       return round( $bytes / 1024, 2 )       . ' KB';
        return $bytes . ' B';
    }
}