            $( '#acf7i-delete-all' ).on( 'click', function () {
                $( '#acf7i-delete-modal' ).remove();
                callback( true );
            });

            $( '#acf7i-modal-cancel, .acf7i-modal-overlay' ).on( 'click', function ( e ) {
                if ( e.target === this ) {
                    $( '#acf7i-delete-modal' ).remove();
                }
            });
        },

        showResendModal( logId, originalEmail ) {
            $( '#acf7i-resend-modal' ).remove();

            const modal = `
            <div id="acf7i-resend-modal" class="acf7i-modal-overlay">
                <div class="acf7i-modal">
                    <div class="acf7i-modal-header">
                        <h3>📧 Reenviar correo</h3>
                    </div>
                    <div class="acf7i-modal-body">
                        <div class="acf7i-field">
                            <label>Destinatario</label>
                            <input type="email" id="acf7i-resend-email"
                                   class="acf7i-input"
                                   value="${ originalEmail }"
                                   placeholder="correo@ejemplo.com">
                            <small>Puedes cambiar el destinatario antes de reenviar.</small>
                        </div>
                    </div>
                    <div class="acf7i-modal-footer">
                        <button class="acf7i-btn acf7i-btn-primary" id="acf7i-resend-confirm">
                            📧 Reenviar
                        </button>
                        <button class="acf7i-btn acf7i-btn-secondary" id="acf7i-resend-cancel">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>`;

            $( 'body' ).append( modal );

            $( '#acf7i-resend-confirm' ).on( 'click', function () {
                const email = $( '#acf7i-resend-email' ).val().trim();
                if ( ! email ) {
                    $( '#acf7i-resend-email' ).focus();
                    return;
                }

                $( '#acf7i-resend-confirm' ).prop( 'disabled', true ).text( '⏳ Enviando...' );

                $.post( acf7i_admin.ajax_url, {
                    action:    'acf7i_resend_mail',
                    nonce:     acf7i_admin.nonce,
                    log_id:    logId,
                    recipient: email,
                }, function ( response ) {
                    $( '#acf7i-resend-modal' ).remove();
                    if ( response.success ) {
                        ACF7I_Admin.showToast( '✅ ' + acf7i_admin.strings.resent, 'success' );
                    } else {
                        ACF7I_Admin.showToast( '❌ ' + acf7i_admin.strings.error, 'error' );
                    }
                });
            });

            $( '#acf7i-resend-cancel, .acf7i-modal-overlay' ).on( 'click', function ( e ) {
                if ( e.target === this ) {
                    $( '#acf7i-resend-modal' ).remove();
                }
            });
        },

        // =============================================
        // RESET CONFIG DE FORMULARIO
        // =============================================
        initResetFormConfig() {
            $( document ).on( 'click', '#acf7i-reset-form-config', function () {
                const formId = $( this ).data( 'form-id' );
                if ( ! confirm( '¿Eliminar la configuración de este formulario? Se usará la configuración global.' ) ) {
                    return;
                }
                $.post( acf7i_admin.ajax_url, {
                    action:  'acf7i_reset_form_config',
                    nonce:   acf7i_admin.nonce,
                    form_id: formId,
                }, function ( response ) {
                    if ( response.success ) {
                        location.reload();
                    }
                });
            });
        },

        // =============================================
        // NAV HIGHLIGHT
        // =============================================
        initNavHighlight() {
            const current = new URLSearchParams( window.location.search ).get( 'page' );
            $( '.acf7i-nav-item' ).each( function () {
                const href = new URLSearchParams( $( this ).attr( 'href' ).split( '?' )[1] || '' ).get( 'page' );
                if ( href === current ) {
                    $( this ).addClass( 'active' );
                }
            });
        },

        // =============================================
        // TOAST NOTIFICATIONS
        // =============================================
        showToast( message, type = 'info' ) {
            $( '.acf7i-toast' ).remove();

            const colors = {
                success: '#00BCFF',
                error:   '#FF3600',
                warning: '#FFC700',
                info:    '#07325A',
            };

            const toast = `
            <div class="acf7i-toast acf7i-toast-${ type }"
                 style="border-left-color: ${ colors[ type ] || colors.info }">
                ${ message }
            </div>`;

            $( 'body' ).append( toast );

            setTimeout( () => {
                $( '.acf7i-toast' ).addClass( 'show' );
            }, 10 );

            setTimeout( () => {
                $( '.acf7i-toast' ).removeClass( 'show' );
                setTimeout( () => $( '.acf7i-toast' ).remove(), 300 );
            }, 3500 );
        },
    };

    // Inicializar
    $( document ).ready( function () {
        ACF7I_Admin.init();
    });

    // Exponer globalmente para uso desde otros módulos
    window.ACF7I_Admin = ACF7I_Admin;

})( jQuery );