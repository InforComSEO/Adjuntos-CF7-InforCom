<?php if ( ! defined( 'ABSPATH' ) ) exit;

$credentials   = get_option( 'acf7i_drive_credentials', null );
$is_connected  = ! empty( $credentials['client_email'] );
$settings      = get_option( 'acf7i_settings', array() );
?>

<div class="acf7i-wrap">

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/header.php'; ?>

    <div class="acf7i-page-content">

        <div class="acf7i-page-header">
            <h2>☁️ <?php _e( 'Integración con Google Drive', ACF7I_TEXT_DOMAIN ); ?></h2>
            <p><?php _e( 'Guarda los archivos subidos directamente en tu Google Drive usando una Service Account.', ACF7I_TEXT_DOMAIN ); ?></p>
        </div>

        <div class="acf7i-drive-layout">

            <!-- ============================================
                 COLUMNA IZQUIERDA: ESTADO + CONFIGURACIÓN
            ============================================ -->
            <div class="acf7i-drive-config">

                <!-- Estado de conexión -->
                <div class="acf7i-card">
                    <div class="acf7i-card-header">
                        <h3>🔌 <?php _e( 'Estado de la Conexión', ACF7I_TEXT_DOMAIN ); ?></h3>
                    </div>
                    <div class="acf7i-card-body">
                        <?php if ( $is_connected ) : ?>
                            <div class="acf7i-drive-status connected">
                                <span class="acf7i-status-dot connected"></span>
                                <div class="acf7i-status-info">
                                    <strong><?php _e( 'Conectado', ACF7I_TEXT_DOMAIN ); ?></strong>
                                    <span><?php echo esc_html( $credentials['client_email'] ); ?></span>
                                    <small><?php _e( 'Proyecto:', ACF7I_TEXT_DOMAIN ); ?> <?php echo esc_html( $credentials['project_id'] ?? '—' ); ?></small>
                                </div>
                            </div>

                            <div class="acf7i-drive-actions">
                                <button type="button" class="acf7i-btn acf7i-btn-secondary"
                                        id="acf7i-drive-test">
                                    🔍 <?php _e( 'Probar conexión', ACF7I_TEXT_DOMAIN ); ?>
                                </button>
                                <button type="button" class="acf7i-btn acf7i-btn-danger"
                                        id="acf7i-drive-disconnect">
                                    🔌 <?php _e( 'Desconectar Drive', ACF7I_TEXT_DOMAIN ); ?>
                                </button>
                            </div>

                            <div id="acf7i-test-result" class="acf7i-alert" style="display:none;"></div>

                        <?php else : ?>
                            <div class="acf7i-drive-status disconnected">
                                <span class="acf7i-status-dot disconnected"></span>
                                <div class="acf7i-status-info">
                                    <strong><?php _e( 'No conectado', ACF7I_TEXT_DOMAIN ); ?></strong>
                                    <span><?php _e( 'Sube el archivo JSON de tu Service Account para conectar.', ACF7I_TEXT_DOMAIN ); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Subir credenciales JSON -->
                <?php if ( ! $is_connected ) : ?>
                <div class="acf7i-card">
                    <div class="acf7i-card-header">
                        <h3>🔑 <?php _e( 'Subir Credenciales JSON', ACF7I_TEXT_DOMAIN ); ?></h3>
                    </div>
                    <div class="acf7i-card-body">
                        <p><?php _e( 'Una vez que hayas seguido los pasos de la guía, descarga el archivo JSON de tu Service Account y súbelo aquí:', ACF7I_TEXT_DOMAIN ); ?></p>

                        <div class="acf7i-json-upload-area" id="acf7i-json-drop">
                            <div class="acf7i-json-upload-icon">📄</div>
                            <p><?php _e( 'Arrastra el archivo JSON aquí', ACF7I_TEXT_DOMAIN ); ?></p>
                            <p class="acf7i-text-muted"><?php _e( 'o', ACF7I_TEXT_DOMAIN ); ?></p>
                            <label class="acf7i-btn acf7i-btn-primary" for="acf7i-json-file">
                                📁 <?php _e( 'Seleccionar archivo JSON', ACF7I_TEXT_DOMAIN ); ?>
                            </label>
                            <input type="file" id="acf7i-json-file" accept=".json" style="display:none;">
                        </div>

                        <div id="acf7i-credentials-result" class="acf7i-alert" style="display:none;"></div>

                        <div class="acf7i-alert acf7i-alert-warning" style="margin-top:12px;">
                            🔒 <?php _e( 'El archivo JSON se guarda encriptado en la base de datos de WordPress. Nunca se expone públicamente.', ACF7I_TEXT_DOMAIN ); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Configuración de Drive -->
                <?php if ( $is_connected ) : ?>
                <div class="acf7i-card">
                    <div class="acf7i-card-header">
                        <h3>⚙️ <?php _e( 'Configuración de Drive', ACF7I_TEXT_DOMAIN ); ?></h3>
                    </div>
                    <div class="acf7i-card-body">
                        <form id="acf7i-drive-settings-form">
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'acf7i_admin_nonce' ); ?>">
                            <input type="hidden" name="settings_type" value="global">

                            <div class="acf7i-field acf7i-field-full" style="margin-bottom:18px;">
                                <label><?php _e( 'ID de la carpeta de destino en Drive', ACF7I_TEXT_DOMAIN ); ?></label>
                                <div style="display:flex; gap:8px;">
                                    <input type="text" name="settings[drive_folder_id]"
                                           class="acf7i-input" id="acf7i-drive-folder-id"
                                           value="<?php echo esc_attr( $settings['drive_folder_id'] ?? '' ); ?>"
                                           placeholder="1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms">
                                    <button type="button" class="acf7i-btn acf7i-btn-secondary"
                                            id="acf7i-verify-folder">
                                        🔍 <?php _e( 'Verificar', ACF7I_TEXT_DOMAIN ); ?>
                                    </button>
                                </div>
                                <small>
                                    <?php _e( 'Copia el ID de la URL de tu carpeta en Drive:', ACF7I_TEXT_DOMAIN ); ?>
                                    <code>drive.google.com/drive/folders/<strong>[ID_AQUÍ]</strong></code>
                                </small>
                                <div id="acf7i-folder-verify-result" style="display:none;" class="acf7i-alert acf7i-alert-info" style="margin-top:8px;"></div>
                            </div>

                            <div class="acf7i-field acf7i-field-toggle" style="margin-bottom:12px;">
                                <label><?php _e( 'Subcarpeta por formulario', ACF7I_TEXT_DOMAIN ); ?></label>
                                <label class="acf7i-toggle">
                                    <input type="checkbox" name="settings[drive_subfolder_by_form]" value="1"
                                           <?php checked( $settings['drive_subfolder_by_form'] ?? false ); ?>>
                                    <span class="acf7i-toggle-slider"></span>
                                </label>
                            </div>

                            <div class="acf7i-field acf7i-field-toggle">
                                <label><?php _e( 'Subcarpeta por fecha (YYYY/MM)', ACF7I_TEXT_DOMAIN ); ?></label>
                                <label class="acf7i-toggle">
                                    <input type="checkbox" name="settings[drive_subfolder_by_date]" value="1"
                                           <?php checked( $settings['drive_subfolder_by_date'] ?? false ); ?>>
                                    <span class="acf7i-toggle-slider"></span>
                                </label>
                            </div>

                            <div class="acf7i-form-actions" style="padding:16px 0 0; border-top:1px solid var(--acf7i-border); margin-top:18px;">
                                <button type="submit" class="acf7i-btn acf7i-btn-primary">
                                    💾 <?php _e( 'Guardar configuración de Drive', ACF7I_TEXT_DOMAIN ); ?>
                                </button>
                                <span class="acf7i-save-status" id="acf7i-drive-save-status"></span>
                            </div>

                        </form>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- ============================================
                 COLUMNA DERECHA: GUÍA PASO A PASO
            ============================================ -->
            <div class="acf7i-drive-guide">
                <div class="acf7i-card">
                    <div class="acf7i-card-header">
                        <h3>📖 <?php _e( 'Guía de Configuración — Google Drive', ACF7I_TEXT_DOMAIN ); ?></h3>
                        <span class="acf7i-badge acf7i-badge-info">Service Account</span>
                    </div>
                    <div class="acf7i-card-body">

                        <div class="acf7i-guide-intro">
                            <p><?php _e( 'Sigue estos pasos para conectar el plugin con Google Drive. Solo necesitas hacerlo una vez.', ACF7I_TEXT_DOMAIN ); ?></p>
                        </div>

                        <!-- PASO 1 -->
                        <div class="acf7i-guide-step">
                            <div class="acf7i-step-number">1</div>
                            <div class="acf7i-step-content">
                                <h4><?php _e( 'Crear un proyecto en Google Cloud Console', ACF7I_TEXT_DOMAIN ); ?></h4>
                                <ol>
                                    <li><?php _e( 'Ve a', ACF7I_TEXT_DOMAIN ); ?> <a href="https://console.cloud.google.com/" target="_blank" class="acf7i-link">console.cloud.google.com ↗</a></li>
                                    <li><?php _e( 'Inicia sesión con tu cuenta de Google', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Haz clic en el selector de proyectos (arriba)', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Clic en <strong>"Nuevo proyecto"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Dale un nombre (ej: <code>adjuntos-cf7</code>) y clic en <strong>"Crear"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                </ol>
                                <div class="acf7i-alert acf7i-alert-info">
                                    💡 <?php _e( 'Asegúrate de que el nuevo proyecto esté seleccionado antes de continuar.', ACF7I_TEXT_DOMAIN ); ?>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 2 -->
                        <div class="acf7i-guide-step">
                            <div class="acf7i-step-number">2</div>
                            <div class="acf7i-step-content">
                                <h4><?php _e( 'Activar la API de Google Drive', ACF7I_TEXT_DOMAIN ); ?></h4>
                                <ol>
                                    <li><?php _e( 'En el menú izquierdo ve a <strong>APIs y servicios → Biblioteca</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Busca <strong>"Google Drive API"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Haz clic en el resultado y luego en <strong>"Habilitar"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                </ol>
                                <a href="https://console.cloud.google.com/apis/library/drive.googleapis.com"
                                   target="_blank" class="acf7i-btn acf7i-btn-secondary acf7i-sm">
                                    🔗 <?php _e( 'Ir a Google Drive API', ACF7I_TEXT_DOMAIN ); ?>
                                </a>
                            </div>
                        </div>

                        <!-- PASO 3 -->
                        <div class="acf7i-guide-step">
                            <div class="acf7i-step-number">3</div>
                            <div class="acf7i-step-content">
                                <h4><?php _e( 'Crear una cuenta de servicio (Service Account)', ACF7I_TEXT_DOMAIN ); ?></h4>
                                <ol>
                                    <li><?php _e( 'Ve a <strong>APIs y servicios → Credenciales</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Clic en <strong>"+ Crear credenciales"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Selecciona <strong>"Cuenta de servicio"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Dale un nombre (ej: <code>cf7-uploader</code>)', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Clic en <strong>"Crear y continuar"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'En el rol, selecciona <strong>"Básico → Editor"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Clic en <strong>"Continuar"</strong> y luego <strong>"Listo"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                </ol>
                            </div>
                        </div>

                        <!-- PASO 4 -->
                        <div class="acf7i-guide-step">
                            <div class="acf7i-step-number">4</div>
                            <div class="acf7i-step-content">
                                <h4><?php _e( 'Descargar el archivo JSON de credenciales', ACF7I_TEXT_DOMAIN ); ?></h4>
                                <ol>
                                    <li><?php _e( 'En la lista de cuentas de servicio, haz clic en la que creaste', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Ve a la pestaña <strong>"Claves"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Clic en <strong>"Agregar clave → Crear clave nueva"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Selecciona formato <strong>JSON</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Clic en <strong>"Crear"</strong> — se descargará un archivo <code>.json</code>', ACF7I_TEXT_DOMAIN ); ?></li>
                                </ol>
                                <div class="acf7i-alert acf7i-alert-warning">
                                    ⚠️ <?php _e( 'Guarda este archivo en un lugar seguro. Solo se puede descargar una vez.', ACF7I_TEXT_DOMAIN ); ?>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 5 -->
                        <div class="acf7i-guide-step">
                            <div class="acf7i-step-number">5</div>
                            <div class="acf7i-step-content">
                                <h4><?php _e( 'Compartir tu carpeta de Drive con la Service Account', ACF7I_TEXT_DOMAIN ); ?></h4>
                                <ol>
                                    <li><?php _e( 'Abre <strong>Google Drive</strong> en tu navegador', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Crea una carpeta nueva (ej: <code>Adjuntos CF7</code>) o usa una existente', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Clic derecho en la carpeta → <strong>"Compartir"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li>
                                        <?php _e( 'En el campo de correo escribe el email de tu cuenta de servicio:', ACF7I_TEXT_DOMAIN ); ?>
                                        <?php if ( $is_connected ) : ?>
                                            <br><code class="acf7i-service-email"><?php echo esc_html( $credentials['client_email'] ); ?></code>
                                            <button type="button" class="acf7i-btn-link acf7i-copy-btn"
                                                    data-copy="<?php echo esc_attr( $credentials['client_email'] ); ?>">
                                                📋 <?php _e( 'Copiar', ACF7I_TEXT_DOMAIN ); ?>
                                            </button>
                                        <?php else : ?>
                                            <br><span class="acf7i-text-muted"><?php _e( '(aparecerá aquí cuando conectes tu Service Account)', ACF7I_TEXT_DOMAIN ); ?></span>
                                        <?php endif; ?>
                                    </li>
                                    <li><?php _e( 'Selecciona rol <strong>"Editor"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Clic en <strong>"Enviar"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                </ol>
                                <div class="acf7i-alert acf7i-alert-info">
                                    💡 <?php _e( 'Este paso es crucial. Sin este permiso el plugin no podrá subir archivos a tu Drive.', ACF7I_TEXT_DOMAIN ); ?>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 6 -->
                        <div class="acf7i-guide-step">
                            <div class="acf7i-step-number">6</div>
                            <div class="acf7i-step-content">
                                <h4><?php _e( 'Obtener el ID de la carpeta de Drive', ACF7I_TEXT_DOMAIN ); ?></h4>
                                <ol>
                                    <li><?php _e( 'Abre la carpeta en Google Drive', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Mira la URL del navegador:', ACF7I_TEXT_DOMAIN ); ?></li>
                                </ol>
                                <div class="acf7i-url-example">
                                    <code>drive.google.com/drive/folders/<strong class="acf7i-highlight">1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms</strong></code>
                                </div>
                                <p><?php _e( 'La parte resaltada es el <strong>ID de la carpeta</strong>. Cópialo y pégalo en el campo de configuración.', ACF7I_TEXT_DOMAIN ); ?></p>
                            </div>
                        </div>

                        <!-- PASO 7 -->
                        <div class="acf7i-guide-step acf7i-step-final">
                            <div class="acf7i-step-number">✅</div>
                            <div class="acf7i-step-content">
                                <h4><?php _e( 'Conectar el plugin', ACF7I_TEXT_DOMAIN ); ?></h4>
                                <ol>
                                    <li><?php _e( 'Sube el archivo <code>.json</code> descargado en el panel de la izquierda', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Pega el ID de la carpeta en la configuración', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'Haz clic en <strong>"Probar conexión"</strong> para verificar', ACF7I_TEXT_DOMAIN ); ?></li>
                                    <li><?php _e( 'En la configuración de almacenamiento activa <strong>"Guardar en Google Drive"</strong>', ACF7I_TEXT_DOMAIN ); ?></li>
                                </ol>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/footer.php'; ?>

</div>