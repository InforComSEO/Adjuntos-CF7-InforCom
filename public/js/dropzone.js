/**
 * Adjuntos CF7 - InforCom
 * Dropzone — Lógica principal del campo de subida
 */

( function ( $, window ) {
    'use strict';

    const ACF7I_Dropzone = {

        // Almacena estado por campo: { files[], xhrs{} }
        state: {},

        init() {
            this.bindAllFields();
            this.bindCF7Events();
        },

        // =============================================
        // INICIALIZAR TODOS LOS CAMPOS EN LA PÁGINA
        // =============================================
        bindAllFields() {
            $( '.acf7i-field-wrap' ).each( ( i, wrap ) => {
                this.initField( $( wrap ) );
            });
        },

        initField( $wrap ) {
            const name   = $wrap.data( 'name' );
            const config = this.parseConfig( $wrap.data( 'config' ) );

            if ( ! name || ! config ) return;

            // Inicializar estado del campo
            this.state[ name ] = { files: [], xhrs: {}, config };

            const $dropzone  = $wrap.find( '.acf7i-dropzone' );
            const $fileInput = $wrap.find( '.acf7i-file-input' );
            const $selectBtn = $wrap.find( '.acf7i-select-btn' );
            const $filesList = $wrap.find( '.acf7i-files-list' );
            const $errorsWrap= $wrap.find( '.acf7i-errors-wrap' );

            // ── Drag & Drop ──────────────────────────────
            $dropzone
                .on( 'dragenter dragover', ( e ) => {
                    e.preventDefault();
                    e.stopPropagation();
                    $dropzone.addClass( 'dragging' );
                })
                .on( 'dragleave', ( e ) => {
                    e.preventDefault();
                    if ( ! $dropzone[0].contains( e.relatedTarget ) ) {
                        $dropzone.removeClass( 'dragging' );
                    }
                })
                .on( 'drop', ( e ) => {
                    e.preventDefault();
                    e.stopPropagation();
                    $dropzone.removeClass( 'dragging' );
                    const files = e.originalEvent.dataTransfer?.files;
                    if ( files?.length ) this.processFiles( name, files, $wrap );
                });

            // ── Clic en la zona drop ─────────────────────
            $dropzone.on( 'click', ( e ) => {
                if ( $( e.target ).hasClass( 'acf7i-select-btn' ) ) return;
                $fileInput.trigger( 'click' );
            });

            // ── Clic en el botón ─────────────────────────
            $selectBtn.on( 'click', ( e ) => {
                e.stopPropagation();
                $fileInput.trigger( 'click' );
            });

            // ── Teclado (accesibilidad) ──────────────────
            $dropzone.on( 'keydown', ( e ) => {
                if ( e.key === 'Enter' || e.key === ' ' ) {
                    e.preventDefault();
                    $fileInput.trigger( 'click' );
                }
            });

            // ── Input file onChange ──────────────────────
            $fileInput.on( 'change', ( e ) => {
                const files = e.target.files;
                if ( files?.length ) {
                    this.processFiles( name, files, $wrap );
                    // Reset input para permitir subir el mismo archivo
                    e.target.value = '';
                }
            });
        },

        // =============================================
        // PROCESAR ARCHIVOS SELECCIONADOS
        // =============================================
        processFiles( name, fileList, $wrap ) {
            const state      = this.state[ name ];
            const config     = state.config;
            const $errorsWrap= $wrap.find( '.acf7i-errors-wrap' );

            Array.from( fileList ).forEach( file => {
                // Validar
                const result = ACF7I_Validator.validateFile( file, config, state.files );

                if ( ! result.valid ) {
                    this.showError( $errorsWrap, result.error, config );
                    return;
                }

                // Agregar a la lista y subir
                this.addFileToList( name, file, $wrap );
            });
        },

        // =============================================
        // AGREGAR ARCHIVO A LA LISTA Y SUBIR
        // =============================================
        addFileToList( name, file, $wrap ) {
            const state      = this.state[ name ];
            const config     = state.config;
            const fileId     = 'acf7i-file-' + Date.now() + '-' + Math.random().toString(36).substr(2,5);
            const $filesList = $wrap.find( '.acf7i-files-list' );
            const $dropzone  = $wrap.find( '.acf7i-dropzone' );

            // Agregar a la lista interna ANTES de subir
            state.files.push( file );

            // Generar miniatura si es imagen
            ACF7I_Uploader.generateThumbnail( file, ( thumb ) => {
                const icon    = thumb
                    ? `<img src="${ thumb }" alt="" class="acf7i-file-thumb">`
                    : ACF7I_Uploader.getFileIcon( file.name );
                const ext     = file.name.split('.').pop().toUpperCase();
                const size    = ACF7I_Uploader.formatSize( file.size );
                const isImage = !! thumb;

                const $item = $( `
                <div class="acf7i-file-item" id="${ fileId }" data-filename="${ file.name }" data-filesize="${ file.size }">
                    <div class="acf7i-file-type-icon ${ isImage ? 'acf7i-file-thumb-wrap' : '' }">
                        ${ icon }
                    </div>
                    <div class="acf7i-file-info">
                        <span class="acf7i-file-name" title="${ this.escHtml(file.name) }">${ this.escHtml( file.name ) }</span>
                        <span class="acf7i-file-meta">
                            <span class="acf7i-file-ext">.${ ext.toLowerCase() }</span>
                            <span class="acf7i-file-size">${ size }</span>
                        </span>
                    </div>
                    <div class="acf7i-progress-wrap">
                        <div class="acf7i-progress-bar">
                            <div class="acf7i-progress-fill uploading" style="width:0%"></div>
                        </div>
                        <span class="acf7i-progress-pct">0%</span>
                    </div>
                    <button type="button" class="acf7i-file-remove"
                            aria-label="Eliminar ${ this.escHtml( file.name ) }"
                            data-id="${ fileId }">✕</button>
                </div>` );

                $filesList.append( $item );
                $dropzone.addClass( 'has-files' );

                // Scroll suave al nuevo item
                $item[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                // Subir archivo
                const xhr = ACF7I_Uploader.upload( file, config, {
                    onProgress: ( pct ) => {
                        $item.find( '.acf7i-progress-fill' ).css( 'width', pct + '%' );
                        $item.find( '.acf7i-progress-pct' ).text( pct + '%' );
                    },
                    onSuccess: ( data ) => {
                        $item.find( '.acf7i-progress-fill' )
                             .removeClass( 'uploading' )
                             .addClass( 'complete' )
                             .css( 'width', '100%' );
                        $item.find( '.acf7i-progress-pct' )
                             .addClass( 'complete' )
                             .text( '✅' );

                        // Guardar ruta temp en el item
                        $item.data( 'temp-path', data.temp_path );
                        $item.data( 'uploaded', true );

                        // Actualizar el input oculto con todas las rutas
                        this.updateHiddenInput( name, $wrap );

                        delete state.xhrs[ fileId ];
                    },
                    onError: ( msg ) => {
                        $item.find( '.acf7i-progress-fill' )
                             .removeClass( 'uploading' )
                             .addClass( 'error' );
                        $item.find( '.acf7i-progress-pct' )
                             .addClass( 'error' )
                             .text( '❌' );

                        // Remover de la lista interna
                        state.files = state.files.filter( f => f !== file );

                        const $errorsWrap = $wrap.find( '.acf7i-errors-wrap' );
                        this.showError( $errorsWrap, msg, config );

                        // Remover el item fallido después de un momento
                        setTimeout( () => {
                            $item.fadeOut( 300, () => {
                                $item.remove();
                                if ( state.files.length === 0 ) {
                                    $dropzone.removeClass( 'has-files' );
                                }
                            });
                        }, 2500 );

                        delete state.xhrs[ fileId ];
                    },
                });

                state.xhrs[ fileId ] = xhr;

                // Botón eliminar
                $item.find( '.acf7i-file-remove' ).on( 'click', ( e ) => {
                    e.stopPropagation();
                    this.removeFile( name, fileId, file, $item, $wrap );
                });
            });
        },

        // =============================================
        // ELIMINAR ARCHIVO
        // =============================================
        removeFile( name, fileId, file, $item, $wrap ) {
            const state   = this.state[ name ];
            const config  = state.config;
            const $dropzone = $wrap.find( '.acf7i-dropzone' );

            // Cancelar XHR si está subiendo
            if ( state.xhrs[ fileId ] ) {
                state.xhrs[ fileId ].abort();
                delete state.xhrs[ fileId ];
            }

            // Eliminar archivo temporal del servidor
            const tempPath = $item.data( 'temp-path' );
            if ( tempPath ) {
                ACF7I_Uploader.deleteTemp( tempPath, config );
            }

            // Remover de la lista interna
            state.files = state.files.filter( f => f !== file );

            // Animar y eliminar el item
            $item.style && ( $item.style.overflow = 'hidden' );
            $item.animate({ opacity: 0, height: 0, marginBottom: 0, padding: 0 }, 250, () => {
                $item.remove();
                if ( state.files.length === 0 ) {
                    $dropzone.removeClass( 'has-files' );
                }
                this.updateHiddenInput( name, $wrap );
            });
        },

        // =============================================
        // ACTUALIZAR INPUT OCULTO
        // =============================================
        updateHiddenInput( name, $wrap ) {
            const $filesList = $wrap.find( '.acf7i-files-list' );
            const paths      = [];

            $filesList.find( '.acf7i-file-item[data-uploaded="true"]' ).each( function () {
                const path = $( this ).data( 'temp-path' );
                if ( path ) paths.push( path );
            });

            $wrap.find( '.acf7i-hidden-input' ).val( paths.join( '||' ) );
        },

        // =============================================
        // MOSTRAR ERROR
        // =============================================
        showError( $errorsWrap, message, config ) {
            const errorId = 'acf7i-err-' + Date.now();
            const closeable = config.error_closeable !== false;

            const $error = $( `
            <div class="acf7i-error-msg" id="${ errorId }" role="alert" aria-live="assertive">
                <span class="acf7i-error-icon" aria-hidden="true">⛔</span>
                <span class="acf7i-error-text">${ this.escHtml( message ) }</span>
                ${ closeable ? `<button type="button" class="acf7i-error-close" aria-label="Cerrar mensaje de error">✕</button>` : '' }
            </div>` );

            $errorsWrap.append( $error );

            // Auto-desaparecer si está configurado
            if ( config.error_autodismiss ) {
                const time = parseInt( config.error_autodismiss_time || 5, 10 ) * 1000;
                setTimeout( () => this.dismissError( $error ), time );
            }

            // Botón cerrar
            $error.find( '.acf7i-error-close' ).on( 'click', () => {
                this.dismissError( $error );
            });

            // Scroll al error
            $error[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        },

        dismissError( $error ) {
            $error.animate({ opacity: 0, height: 0, marginBottom: 0, padding: 0 }, 200, () => {
                $error.remove();
            });
        },

        // =============================================
        // INTEGRACIÓN CON CF7 — VALIDACIÓN AL SUBMIT
        // =============================================
        bindCF7Events() {

            // Antes del submit de CF7
            $( document ).on( 'wpcf7submit', '.wpcf7-form', ( e ) => {
                // CF7 ya envió, limpiar archivos temp huérfanos
            });

            // Interceptar submit para validar campos requeridos
            $( document ).on( 'wpcf7beforesubmit', '.wpcf7-form', ( e ) => {
                let isValid = true;

                $( '.acf7i-field-wrap' ).each( ( i, wrap ) => {
                    const $wrap   = $( wrap );
                    const name    = $wrap.data( 'name' );
                    const state   = this.state[ name ];
                    if ( ! state ) return;

                    const config      = state.config;
                    const $errorsWrap = $wrap.find( '.acf7i-errors-wrap' );
                    const $dropzone   = $wrap.find( '.acf7i-dropzone' );

                    // Limpiar errores previos del submit
                    $errorsWrap.find( '.acf7i-submit-error' ).remove();

                    // Validar requerido
                    const reqResult = ACF7I_Validator.validateRequired( state.files, config );
                    if ( ! reqResult.valid ) {
                        this.showSubmitError( $errorsWrap, reqResult.error, config );
                        $dropzone.addClass( 'has-error' );
                        $dropzone[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        isValid = false;
                        return false;
                    }

                    // Validar mínimo de archivos
                    const minResult = ACF7I_Validator.validateMinFiles( state.files, config );
                    if ( ! minResult.valid ) {
                        this.showSubmitError( $errorsWrap, minResult.error, config );
                        $dropzone.addClass( 'has-error' );
                        $dropzone[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        isValid = false;
                        return false;
                    }

                    // Verificar que todos los archivos estén subidos
                    const pendingUploads = Object.keys( state.xhrs ).length;
                    if ( pendingUploads > 0 ) {
                        this.showSubmitError(
                            $errorsWrap,
                            'Espera a que todos los archivos terminen de subirse.',
                            config
                        );
                        isValid = false;
                        return false;
                    }

                    // Remover clase de error si todo está bien
                    $dropzone.removeClass( 'has-error' );
                });

                if ( ! isValid ) {
                    e.preventDefault();
                    return false;
                }
            });

            // Después de enviar exitosamente: limpiar el campo
            $( document ).on( 'wpcf7mailsent', '.wpcf7-form', ( e ) => {
                const $form = $( e.target );
                $form.find( '.acf7i-field-wrap' ).each( ( i, wrap ) => {
                    this.resetField( $( wrap ) );
                });
            });

            // En mail failed: mantener archivos pero limpiar errores de submit
            $( document ).on( 'wpcf7mailfailed', '.wpcf7-form', () => {
                $( '.acf7i-submit-error' ).remove();
            });

            // Suprimir mensajes de error de CF7 en nuestros campos
            $( document ).on( 'wpcf7invalid', '.wpcf7-form', () => {
                setTimeout( () => {
                    $( '.acf7i-field-wrap .wpcf7-not-valid-tip' ).remove();
                    $( '.acf7i-field-wrap + .wpcf7-not-valid-tip' ).remove();
                }, 10 );
            });
        },

        showSubmitError( $errorsWrap, message, config ) {
            const $error = $( `
            <div class="acf7i-error-msg acf7i-submit-error" role="alert">
                <span class="acf7i-error-icon" aria-hidden="true">⛔</span>
                <span class="acf7i-error-text">${ this.escHtml( message ) }</span>
                <button type="button" class="acf7i-error-close" aria-label="Cerrar">✕</button>
            </div>` );

            $errorsWrap.prepend( $error );

            $error.find( '.acf7i-error-close' ).on( 'click', () => {
                this.dismissError( $error );
                $( '.acf7i-dropzone' ).removeClass( 'has-error' );
            });
        },

        // =============================================
        // RESET DEL CAMPO
        // =============================================
        resetField( $wrap ) {
            const name  = $wrap.data( 'name' );
            if ( ! this.state[ name ] ) return;

            this.state[ name ].files = [];
            this.state[ name ].xhrs  = {};

            $wrap.find( '.acf7i-files-list' ).empty();
            $wrap.find( '.acf7i-errors-wrap' ).empty();
            $wrap.find( '.acf7i-hidden-input' ).val( '' );
            $wrap.find( '.acf7i-dropzone' ).removeClass( 'has-files has-error dragging' );
        },

        // =============================================
        // HELPERS
        // =============================================
        parseConfig( data ) {
            if ( typeof data === 'object' ) return data;
            try {
                return JSON.parse( data );
            } catch ( e ) {
                return null;
            }
        },

        escHtml( str ) {
            const div = document.createElement( 'div' );
            div.appendChild( document.createTextNode( str ) );
            return div.innerHTML;
        },
    };

    // Inicializar cuando el DOM esté listo
    $( document ).ready( function () {
        ACF7I_Dropzone.init();
    });

    window.ACF7I_Dropzone = ACF7I_Dropzone;

})( jQuery, window );