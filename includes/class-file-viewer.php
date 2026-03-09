<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Visualizador de archivos protegido con token
 * URL: /?acf7i-view=TOKEN
 * Solo permite ver, no descargar, no editar, no eliminar
 */
class ACF7I_File_Viewer {

    /**
     * Registra el endpoint de visualización
     */
    public function register_endpoint() {
        add_rewrite_rule(
            '^acf7i-view/([a-f0-9]{64})/?$',
            'index.php?acf7i_view_token=$matches[1]',
            'top'
        );
        add_rewrite_tag( '%acf7i_view_token%', '([a-f0-9]{64})' );
    }

    /**
     * Maneja la solicitud de visualización
     */
    public function handle_view_request() {

        // Verificar por query var
        $token = get_query_var( 'acf7i_view_token' );

        // Verificar por GET param
        if ( empty( $token ) ) {
            $token = sanitize_text_field( $_GET['acf7i-view'] ?? '' );
        }

        if ( empty( $token ) ) return;

        // Validar formato del token
        if ( ! preg_match( '/^[a-f0-9]{64}$/', $token ) ) {
            $this->show_error( 'Token de acceso inválido.' );
            exit;
        }

        // Buscar en la base de datos
        global $wpdb;
        $entry = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}acf7i_log WHERE view_token = %s LIMIT 1",
                $token
            )
        );

        if ( ! $entry ) {
            $this->show_error( 'Archivo no encontrado o el enlace ya no es válido.' );
            exit;
        }

        // Verificar expiración
        if ( ! empty( $entry->token_expires ) ) {
            if ( strtotime( $entry->token_expires ) < time() ) {
                $this->show_error( 'Este enlace ha expirado.' );
                exit;
            }
        }

        // Servir el archivo
        $this->serve_file( $entry );
        exit;
    }

    /**
     * Sirve el archivo para visualización en el navegador
     */
    private function serve_file( $entry ) {

        // Determinar qué mostrar: preferir visualización en browser
        $viewable_in_browser = array( 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'webm', 'mp3', 'ogg' );
        $ext                 = strtolower( $entry->filetype );
        $is_viewable         = in_array( $ext, $viewable_in_browser );

        if ( $is_viewable && ! empty( $entry->local_path ) && file_exists( $entry->local_path ) ) {
            $this->serve_local_file( $entry );
        } elseif ( ! empty( $entry->drive_url ) ) {
            // Redirigir a Drive (ya es solo lectura)
            wp_redirect( $entry->drive_url );
            exit;
        } elseif ( ! empty( $entry->local_url ) ) {
            // Mostrar página de visualización
            $this->render_viewer_page( $entry );
        } else {
            $this->show_error( 'El archivo no está disponible.' );
        }
    }

    /**
     * Sirve el archivo local directamente en el navegador
     */
    private function serve_local_file( $entry ) {
        $path = $entry->local_path;
        $ext  = strtolower( $entry->filetype );

        $mime_types = array(
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'mp3'  => 'audio/mpeg',
            'ogg'  => 'audio/ogg',
            'wav'  => 'audio/wav',
        );

        $mime = $mime_types[ $ext ] ?? 'application/octet-stream';

        // Headers de solo visualización (sin descarga)
        header( 'Content-Type: ' . $mime );
        header( 'Content-Length: ' . filesize( $path ) );
        header( 'Content-Disposition: inline; filename="' . basename( $entry->original_name ) . '"' );
        header( 'X-Content-Type-Options: nosniff' );
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'Cache-Control: no-store, no-cache, must-revalidate' );
        header( 'Pragma: no-cache' );

        // Limpiar cualquier buffer previo
        while ( ob_get_level() ) ob_end_clean();

        readfile( $path );
    }

    /**
     * Renderiza una página de visualización para tipos no directamente mostrables
     */
    private function render_viewer_page( $entry ) {
        $settings  = get_option( 'acf7i_settings', array() );
        $site_name = get_bloginfo( 'name' );

        header( 'Content-Type: text/html; charset=UTF-8' );
        ?><!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html( $entry->original_name ); ?> — <?php echo esc_html( $site_name ); ?></title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #F0F4F8;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .viewer-card {
                    background: #FFFFFF;
                    border-radius: 16px;
                    box-shadow: 0 8px 40px rgba(5,0,24,0.12);
                    max-width: 480px;
                    width: 100%;
                    overflow: hidden;
                }
                .viewer-header {
                    background: #050018;
                    padding: 24px 28px;
                    display: flex;
                    align-items: center;
                    gap: 14px;
                }
                .viewer-header-icon {
                    font-size: 32px;
                }
                .viewer-header h1 {
                    color: #FFFFFF;
                    font-size: 15px;
                    font-weight: 700;
                    word-break: break-all;
                }
                .viewer-header span {
                    color: rgba(255,255,255,0.5);
                    font-size: 12px;
                    display: block;
                    margin-top: 3px;
                }
                .viewer-body {
                    padding: 28px;
                }
                .viewer-meta {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    margin-bottom: 24px;
                }
                .viewer-meta-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px 0;
                    border-bottom: 1px solid #E8EEFF;
                    font-size: 13px;
                }
                .viewer-meta-row:last-child { border-bottom: none; }
                .viewer-meta-label { color: #8A9BB0; font-weight: 500; }
                .viewer-meta-value { color: #050018; font-weight: 600; }
                .viewer-badge {
                    background: rgba(0,188,255,0.10);
                    color: #07325A;
                    padding: 3px 10px;
                    border-radius: 20px;
                    font-size: 11px;
                    font-weight: 700;
                    text-transform: uppercase;
                }
                .viewer-notice {
                    background: rgba(0,188,255,0.06);
                    border-left: 3px solid #00BCFF;
                    padding: 12px 16px;
                    border-radius: 0 6px 6px 0;
                    font-size: 12px;
                    color: #07325A;
                    margin-bottom: 20px;
                    line-height: 1.5;
                }
                .viewer-footer {
                    text-align: center;
                    font-size: 11px;
                    color: #8A9BB0;
                    padding: 16px 28px;
                    border-top: 1px solid #E8EEFF;
                }
                .viewer-footer a {
                    color: #00BCFF;
                    text-decoration: none;
                }
                .viewer-icon-big {
                    font-size: 64px;
                    text-align: center;
                    margin-bottom: 20px;
                    display: block;
                }
            </style>
        </head>
        <body>
            <div class="viewer-card">
                <div class="viewer-header">
                    <div class="viewer-header-icon">📎</div>
                    <div>
                        <h1><?php echo esc_html( $entry->original_name ); ?></h1>
                        <span><?php echo esc_html( $site_name ); ?></span>
                    </div>
                </div>
                <div class="viewer-body">
                    <span class="viewer-icon-big"><?php echo $this->get_file_emoji( $entry->filetype ); ?></span>

                    <div class="viewer-notice">
                        🔒 <?php _e( 'Este archivo es de solo visualización. No puede ser editado ni eliminado a través de este enlace.', ACF7I_TEXT_DOMAIN ); ?>
                    </div>

                    <div class="viewer-meta">
                        <div class="viewer-meta-row">
                            <span class="viewer-meta-label"><?php _e( 'Archivo', ACF7I_TEXT_DOMAIN ); ?></span>
                            <span class="viewer-meta-value"><?php echo esc_html( $entry->original_name ); ?></span>
                        </div>
                        <div class="viewer-meta-row">
                            <span class="viewer-meta-label"><?php _e( 'Tipo', ACF7I_TEXT_DOMAIN ); ?></span>
                            <span class="viewer-badge"><?php echo esc_html( strtoupper( $entry->filetype ) ); ?></span>
                        </div>
                        <div class="viewer-meta-row">
                            <span class="viewer-meta-label"><?php _e( 'Tamaño', ACF7I_TEXT_DOMAIN ); ?></span>
                            <span class="viewer-meta-value"><?php echo esc_html( $this->format_bytes( $entry->filesize ) ); ?></span>
                        </div>
                        <div class="viewer-meta-row">
                            <span class="viewer-meta-label"><?php _e( 'Formulario', ACF7I_TEXT_DOMAIN ); ?></span>
                            <span class="viewer-meta-value"><?php echo esc_html( $entry->form_title ); ?></span>
                        </div>
                        <div class="viewer-meta-row">
                            <span class="viewer-meta-label"><?php _e( 'Fecha', ACF7I_TEXT_DOMAIN ); ?></span>
                            <span class="viewer-meta-value"><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $entry->uploaded_at ) ) ); ?></span>
                        </div>
                        <?php if ( ! empty( $entry->token_expires ) ) : ?>
                        <div class="viewer-meta-row">
                            <span class="viewer-meta-label"><?php _e( 'Enlace expira', ACF7I_TEXT_DOMAIN ); ?></span>
                            <span class="viewer-meta-value"><?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $entry->token_expires ) ) ); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
                <div class="viewer-footer">
                    <?php _e( 'Enviado a través de', ACF7I_TEXT_DOMAIN ); ?>
                    <a href="<?php echo esc_url( home_url() ); ?>"><?php echo esc_html( $site_name ); ?></a>
                    — Adjuntos CF7 · InforCom
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Muestra una página de error
     */
    private function show_error( $message ) {
        header( 'HTTP/1.0 404 Not Found' );
        header( 'Content-Type: text/html; charset=UTF-8' );
        ?><!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Enlace no válido</title>
            <style>
                body { font-family: sans-serif; background:#F0F4F8; display:flex; align-items:center; justify-content:center; min-height:100vh; }
                .box { background:#fff; border-radius:12px; padding:40px; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,0.1); max-width:400px; }
                .icon { font-size:56px; margin-bottom:16px; }
                h2 { color:#050018; margin:0 0 12px; font-size:20px; }
                p { color:#8A9BB0; font-size:14px; }
                a { color:#00BCFF; text-decoration:none; display:inline-block; margin-top:16px; font-weight:600; }
            </style>
        </head>
        <body>
            <div class="box">
                <div class="icon">🔒</div>
                <h2>Enlace no válido</h2>
                <p><?php echo esc_html( $message ); ?></p>
                <a href="<?php echo esc_url( home_url() ); ?>">← Volver al inicio</a>
            </div>
        </body>
        </html>
        <?php
    }

    private function get_file_emoji( $ext ) {
        $map = array(
            'pdf' => '📕', 'doc' => '📘', 'docx' => '📘',
            'xls' => '📗', 'xlsx' => '📗', 'ppt' => '📙', 'pptx' => '📙',
            'mp3' => '🎵', 'wav' => '🎵', 'ogg' => '🎵',
            'mp4' => '🎬', 'mov' => '🎬', 'avi' => '🎬',
            'zip' => '🗜️', 'rar' => '🗜️',
            'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️',
            'gif' => '🖼️', 'svg' => '🖼️',
        );
        return $map[ strtolower( $ext ) ] ?? '📄';
    }

    private function format_bytes( $bytes ) {
        if ( $bytes >= 1073741824 ) return round( $bytes / 1073741824, 2 ) . ' GB';
        if ( $bytes >= 1048576 )    return round( $bytes / 1048576, 2 )    . ' MB';
        if ( $bytes >= 1024 )       return round( $bytes / 1024, 2 )       . ' KB';
        return $bytes . ' B';
    }
}