/**
 * Adjuntos CF7 - InforCom
 * Drive — Manejo de la página de Google Drive
 */

( function ( $ ) {
    'use strict';

    const ACF7I_Drive = {

        init() {
            this.bindJsonUpload();
            this.bindTestConnection();
            this.bindDisconnect();
            this.bindVerifyFolder();
            this.bindSaveSettings();
            this.bindCopyButtons();
        },

        // =============================================
        // SUBIDA DEL JSON DE CREDENCIALES
        // =============================================
        bindJsonUpload() {
            const $area  = $( '#acf7i-json-drop' );
            const $input = $( '#acf7i-json-file' );

            if ( ! $area.length ) return;

            // Drag & drop sobre el área
            $area.on( 'dragenter dragover', ( e ) => {
                e.preventDefault();
                $area.addClass( 'dragging' );
            }).on( 'dragleave drop', ( e ) => {
                e.preventDefault();
                $area.removeClass( 'dragging' );
                if ( e.type === 'drop' ) {
                    const file = e.originalEvent.dataTransfer?.files[0];
                    if ( file ) this.processJsonFile( file );
                }
            });

            // Clic en label abre el selector
            $input.on( 'change', function () {
                const file = this.files[0];
                if ( file ) ACF7I_Drive.processJsonFile( file );
                this.value = '';
            });
        },

        processJsonFile( file ) {
            const $result = $( '#acf7i-credentials-result' );

            if ( ! file.name.endsWith( '.json' ) ) {
                this.showResult( $result, 'error', '❌ El archivo debe ser un .json' );
                return;
            }

            const reader = new FileReader();
            reader.onload = ( e ) => {
                const content = e.target.result;

                // Validar que sea JSON válido
                try {
                    const parsed = JSON.parse( content );
                    if ( parsed.type !== 'service_account' ) {
                        this.showResult( $result, 'error', '❌ El archivo no es una Service Account de Google.' );
                        return;
                    }
                } catch ( err ) {
                    this.showResult( $result, 'error', '❌ El archivo JSON no es válido.' );
                    return;
                }

                // Mostrar loading
                this.showResult( $result, 'info', '⏳ Guardando credenciales...' );

                // Enviar al servidor
                $.post( acf7i_admin.ajax_url, {
                    action:       'acf7i_save_drive_credentials',
                    nonce:        acf7i_admin.nonce,
                    json_content: content,
                }, ( response ) => {
                    if ( response.success ) {
                        this.showResult( $result, 'success',
                            '✅ Credenciales guardadas correctamente. Recargando...'
                        );
                        setTimeout( () => location.reload(), 1500 );
                    } else {
                        this.showResult( $result, 'error',
                            '❌ ' + ( response.data?.message || 'Error al guardar las credenciales.' )
                        );
                    }
                }).fail( () => {
                    this.showResult( $result, 'error', '❌ Error de conexión. Intenta nuevamente.' );
                });
            };

            reader.readAsText( file );
        },

        // =============================================
        // PROBAR CONEXIÓN
        // =============================================
        bindTestConnection() {
            $( document ).on( 'click', '#acf7i-drive-test', () => {
                const $btn    = $( '#acf7i-drive-test' );
                const $result = $( '#acf7i-test-result' );

                $btn.prop( 'disabled', true ).text( '⏳ Probando...' );

                $.post( acf7i_admin.ajax_url, {
                    action: 'acf7i_test_drive_connection',
                    nonce:  acf7i_admin.nonce,
                }, ( response ) => {
                    $btn.prop( 'disabled', false ).text( '🔍 Probar conexión' );

                    if ( response.success ) {
                        this.showResult( $result, 'success',
                            '✅ ' + response.data.message
                        );
                    } else {
                        this.showResult( $result, 'error',
                            '❌ ' + ( response.data?.message || 'Error de conexión.' )
                        );
                    }
                }).fail( () => {
                    $btn.prop( 'disabled', false ).text( '🔍 Probar conexión' );
                    this.showResult( $result, 'error', '❌ Error de conexión con el servidor.' );
                });
            });
        },

        // =============================================
        // DESCONECTAR DRIVE
        // =============================================
        bindDisconnect() {
            $( document ).on( 'click', '#acf7i-drive-disconnect', () => {
                if ( ! confirm( '¿Desconectar Google Drive? Los archivos ya subidos no se eliminarán.' ) ) {
                    return;
                }

                $.post( acf7i_admin.ajax_url, {
                    action: 'acf7i_disconnect_drive',
                    nonce:  acf7i_admin.nonce,
                }, ( response ) => {
                    if ( response.success ) {
                        location.reload();
                    }
                });
            });
        },

        // =============================================
        // VERIFICAR CARPETA
        // =============================================
        bindVerifyFolder() {
            $( document ).on( 'click', '#acf7i-verify-folder', () => {
                const folderId = $( '#acf7i-drive-folder-id' ).val().trim();
                const $result  = $( '#acf7i-folder-verify-result' );

                if ( ! folderId ) {
                    this.showResult( $result, 'warning', '⚠️ Ingresa el ID de la carpeta primero.' );
                    return;
                }

                $.post( acf7i_admin.ajax_url, {
                    action:    'acf7i_verify_drive_folder',
                    nonce:     acf7i_admin.nonce,
                    folder_id: folderId,
                }, ( response ) => {
                    if ( response.success ) {
                        this.showResult( $result, 'success',
                            response.data.message +
                            ` <a href="${ response.data.url }" target="_blank" class="acf7i-link">Abrir en Drive ↗</a>`
                        );
                    } else {
                        this.showResult( $result, 'error', '❌ ' + response.data?.message );
                    }
                });
            });
        },

        // =============================================
        // GUARDAR CONFIGURACIÓN DE DRIVE
        // =============================================
        bindSaveSettings() {
            $( document ).on( 'submit', '#acf7i-drive-settings-form', ( e ) => {
                e.preventDefault();
                const $btn    = $( '#acf7i-drive-settings-form .acf7i-btn-primary' );
                const $status = $( '#acf7i-drive-save-status' );
                const data    = $( '#acf7i-drive-settings-form' ).serializeArray();

                $btn.prop( 'disabled', true ).text( '⏳ Guardando...' );

                data.push({ name: 'action', value: 'acf7i_save_settings' });

                $.post( acf7i_admin.ajax_url, data, ( response ) => {
                    $btn.prop( 'disabled', false ).text( '💾 Guardar configuración de Drive' );
                    if ( response.success ) {
                        $status.addClass( 'success' ).text( '✅ Guardado' );
                    } else {
                        $status.addClass( 'error' ).text( '❌ Error' );
                    }
                    setTimeout( () => $status.removeClass( 'success error' ).text( '' ), 3000 );
                });
            });
        },

        // =============================================
        // COPIAR EMAIL DE SERVICE ACCOUNT
        // =============================================
        bindCopyButtons() {
            $( document ).on( 'click', '.acf7i-copy-btn', function () {
                const text = $( this ).data( 'copy' );
                navigator.clipboard.writeText( text ).then( () => {
                    const $btn     = $( this );
                    const original = $btn.text();
                    $btn.text( '✅ Copiado' );
                    setTimeout( () => $btn.text( original ), 2000 );
                });
            });
        },

        // =============================================
        // HELPER: MOSTRAR RESULTADO
        // =============================================
        showResult( $el, type, message ) {
            const classes = {
                success: 'acf7i-alert-info',
                error:   'acf7i-alert-error',
                warning: 'acf7i-alert-warning',
                info:    'acf7i-alert-info',
            };
            $el.removeClass( 'acf7i-alert-info acf7i-alert-error acf7i-alert-warning' )
               .addClass( classes[ type ] || 'acf7i-alert-info' )
               .html( message )
               .show();
        },
    };

    $( document ).ready( () => ACF7I_Drive.init() );

})( jQuery );