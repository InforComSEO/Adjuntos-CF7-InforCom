/**
 * Adjuntos CF7 - InforCom
 * Live Preview — Actualización en tiempo real del panel de vista previa
 */

( function ( $ ) {
    'use strict';

    const ACF7I_Preview = {

        // Mapa de data-preview → función de actualización
        handlers: {},

        init() {
            this.registerHandlers();
            this.bindEvents();
            this.bindSimulators();
            this.bindDeviceSwitcher();
            this.bindSortable();
            this.initFiletypeCounter();
            this.initDangerousTypesWarning();
            this.initStorageToggle();
            this.initExpireToggle();
            this.initFilenamePreview();
            this.initAccordion();
        },

        // =============================================
        // REGISTRO DE HANDLERS DE PREVIEW
        // =============================================
        registerHandlers() {
            const p = this;

            // Zona drop
            p.handlers['dropzone-bg']           = ( v ) => p.css( '#preview-dropzone', 'background-color', v );
            p.handlers['dropzone-bg-hover']      = ( v ) => p.setCssVar( '--acf7i-drop-bg-hover', v );
            p.handlers['dropzone-border-color']  = ( v ) => p.css( '#preview-dropzone', 'border-color', v );
            p.handlers['dropzone-border-hover']  = ( v ) => p.setCssVar( '--acf7i-drop-border-hover', v );
            p.handlers['dropzone-border-style']  = ( v ) => p.css( '#preview-dropzone', 'border-style', v );
            p.handlers['dropzone-border-width']  = ( v ) => p.css( '#preview-dropzone', 'border-width', v + 'px' );
            p.handlers['dropzone-border-radius'] = ( v ) => p.css( '#preview-dropzone', 'border-radius', v + 'px' );
            p.handlers['dropzone-padding']       = ( v ) => p.css( '#preview-dropzone', 'padding', v + 'px' );
            p.handlers['dropzone-min-height']    = ( v ) => p.css( '#preview-dropzone', 'min-height', v + 'px' );
            p.handlers['dropzone-align']         = ( v ) => p.css( '#preview-dropzone', 'text-align', v );
            p.handlers['dropzone-shadow']        = ( v ) => p.css( '#preview-dropzone', 'box-shadow', v ? '0 4px 24px rgba(0,188,255,0.12)' : 'none' );

            // Ícono
            p.handlers['icon-show']        = ( v ) => p.toggle( '#preview-icon', v );
            p.handlers['icon-size']        = ( v ) => p.css( '#preview-icon svg, #preview-icon img', 'width', v + 'px' ) && p.css( '#preview-icon svg, #preview-icon img', 'height', v + 'px' );
            p.handlers['icon-color']       = ( v ) => p.css( '#preview-icon svg', 'stroke', v );
            p.handlers['icon-type']        = ( v ) => p.updateIcon( v );
            p.handlers['icon-custom-url']  = ( v ) => p.updateCustomIcon( v );

            // Textos
            p.handlers['text-main']              = ( v ) => p.text( '#preview-text-main', v );
            p.handlers['text-main-size']         = ( v ) => p.css( '#preview-text-main', 'font-size', v + 'px' );
            p.handlers['text-main-color']        = ( v ) => p.css( '#preview-text-main', 'color', v );
            p.handlers['text-main-weight']       = ( v ) => p.css( '#preview-text-main', 'font-weight', v );
            p.handlers['text-main-italic']       = ( v ) => p.css( '#preview-text-main', 'font-style', v ? 'italic' : 'normal' );
            p.handlers['text-main-align']        = ( v ) => p.css( '#preview-text-main', 'text-align', v );

            p.handlers['text-secondary']         = ( v ) => p.text( '#preview-text-secondary', v );
            p.handlers['text-secondary-size']    = ( v ) => p.css( '#preview-text-secondary', 'font-size', v + 'px' );
            p.handlers['text-secondary-color']   = ( v ) => p.css( '#preview-text-secondary', 'color', v );
            p.handlers['text-secondary-opacity'] = ( v ) => p.css( '#preview-text-secondary', 'opacity', v / 100 );

            // Botón
            p.handlers['btn-text']       = ( v ) => p.text( '#preview-btn', v );
            p.handlers['btn-bg']         = ( v ) => p.css( '#preview-btn', 'background-color', v );
            p.handlers['btn-color']      = ( v ) => p.css( '#preview-btn', 'color', v );
            p.handlers['btn-radius']     = ( v ) => p.css( '#preview-btn', 'border-radius', v + 'px' );
            p.handlers['btn-font-size']  = ( v ) => p.css( '#preview-btn', 'font-size', v + 'px' );
            p.handlers['btn-width']      = ( v ) => p.css( '#preview-btn', 'width', v === 'full' ? '100%' : 'auto' );
            p.handlers['btn-style']      = ( v ) => p.updateBtnStyle( v );

            // Tipos de archivo permitidos
            p.handlers['allowed-types']  = () => p.updateAllowedTypes();

            // Límites → texto de límites en preview
            p.handlers['min-files']      = () => p.updateLimitsText();
            p.handlers['max-files']      = () => p.updateLimitsText();
            p.handlers['max-filesize']   = () => p.updateLimitsText();
            p.handlers['max-filesize-unit'] = () => p.updateLimitsText();

            // Barra de progreso
            p.handlers['progress-show']   = ( v ) => p.toggle( '.acf7i-preview-progress-wrap', v );
            p.handlers['progress-color']  = ( v ) => p.css( '.acf7i-preview-progress-fill', 'background-color', v );
            p.handlers['progress-bg']     = ( v ) => p.css( '.acf7i-preview-progress-bar', 'background-color', v );
            p.handlers['progress-height'] = ( v ) => p.css( '.acf7i-preview-progress-bar', 'height', v + 'px' );
            p.handlers['progress-radius'] = ( v ) => {
                p.css( '.acf7i-preview-progress-bar', 'border-radius', v + 'px' );
                p.css( '.acf7i-preview-progress-fill', 'border-radius', v + 'px' );
            };
            p.handlers['progress-pct']    = ( v ) => p.toggle( '.acf7i-preview-progress-pct', v );

            // Ficha de archivo
            p.handlers['file-card-bg']     = ( v ) => p.css( '.acf7i-preview-file-item', 'background-color', v );
            p.handlers['file-card-border'] = ( v ) => p.css( '.acf7i-preview-file-item', 'border-color', v );
            p.handlers['file-card-accent'] = ( v ) => p.css( '.acf7i-preview-file-item', 'border-left-color', v );
            p.handlers['file-card-radius'] = ( v ) => p.css( '.acf7i-preview-file-item', 'border-radius', v + 'px' );
            p.handlers['file-remove-color']= ( v ) => p.css( '.acf7i-preview-file-remove', 'color', v );

            // Errores
            p.handlers['error-bg']           = ( v ) => p.css( '.acf7i-preview-error', 'background-color', v );
            p.handlers['error-color']        = ( v ) => p.css( '.acf7i-preview-error-text', 'color', v );
            p.handlers['error-border-color'] = ( v ) => p.css( '.acf7i-preview-error', 'border-color', v );
            p.handlers['error-border-style'] = ( v ) => p.updateErrorBorderStyle( v );
            p.handlers['error-radius']       = ( v ) => p.css( '.acf7i-preview-error', 'border-radius', v + 'px' );
            p.handlers['error-closeable']    = ( v ) => p.toggle( '.acf7i-preview-error-close', v );

            // Visibilidad de elementos
            [ 'icon', 'text_main', 'text_secondary', 'text_limits', 'text_types', 'button' ].forEach( key => {
                p.handlers[ 'element-visible-' + key ] = ( v ) => p.updateElementVisibility( key, v );
            });
        },

        // =============================================
        // BIND DE EVENTOS
        // =============================================
        bindEvents() {
            const p = this;

            // Color pickers (wp-color-picker)
            $( document ).on( 'acf7i:color-change', function ( e, data ) {
                p.dispatch( data.preview, data.value );
            });

            // Sliders
            $( document ).on( 'input', '.acf7i-slider.acf7i-live', function () {
                const $this   = $( this );
                const preview = $this.data( 'preview' );
                const val     = $this.val();
                // Actualizar el valor mostrado
                $this.siblings( '.acf7i-slider-val' ).text( val + ( $this.attr( 'name' ).includes( 'opacity' ) ? '%' : ( $this.attr( 'name' ).includes( 'time' ) ? 's' : 'px' ) ) );
                p.dispatch( preview, val );
            });

            // Inputs de texto
            $( document ).on( 'input', 'input[type="text"].acf7i-live, input[type="number"].acf7i-live', function () {
                const $this   = $( this );
                const preview = $this.data( 'preview' );
                p.dispatch( preview, $this.val() );
            });

            // Selects
            $( document ).on( 'change', 'select.acf7i-live', function () {
                const $this   = $( this );
                const preview = $this.data( 'preview' );
                p.dispatch( preview, $this.val() );
            });

            // Checkboxes / Toggles
            $( document ).on( 'change', 'input[type="checkbox"].acf7i-live', function () {
                const $this   = $( this );
                const preview = $this.data( 'preview' );
                p.dispatch( preview, $this.is( ':checked' ) );
            });

            // Botones de grupo (alineación, etc.)
            $( document ).on( 'click', '.acf7i-btn-option', function () {
                const $this = $( this );
                const name  = $this.data( 'name' );
                const val   = $this.data( 'value' );
                $this.siblings( '.acf7i-btn-option' ).removeClass( 'active' );
                $this.addClass( 'active' );
                $( `input[name="${ name }"]` ).val( val );
                const preview = $this.closest( '.acf7i-btn-group' ).data( 'preview' );
                p.dispatch( preview, val );
            });

            // Selector de íconos
            $( document ).on( 'click', '.acf7i-icon-option', function () {
                const $this = $( this );
                const val   = $this.data( 'value' );
                $this.siblings( '.acf7i-icon-option' ).removeClass( 'active' );
                $this.addClass( 'active' );
                $( 'input[name="settings[icon_type]"]' ).val( val );
                $( '#acf7i-custom-icon-wrap' ).toggle( val === 'custom' );
                p.dispatch( 'icon-type', val );
            });

            // Checkboxes de tipos de archivo
            $( document ).on( 'change', '.acf7i-type-check', function () {
                p.updateTypeCounter();
                p.checkDangerousTypes();
                p.updateAllowedTypes();
            });

            // Botones marcar/desmarcar grupo
            $( document ).on( 'click', '.acf7i-group-check-all', function () {
                const group = $( this ).data( 'group' );
                $( `.acf7i-filetype-checkboxes[data-group="${ group }"] .acf7i-type-check` ).prop( 'checked', true ).trigger( 'change' );
            });

            $( document ).on( 'click', '.acf7i-group-uncheck-all', function () {
                const group = $( this ).data( 'group' );
                $( `.acf7i-filetype-checkboxes[data-group="${ group }"] .acf7i-type-check` ).prop( 'checked', false ).trigger( 'change' );
            });

            // Marcar/desmarcar todos
            $( document ).on( 'click', '#acf7i-allow-all', function () {
                $( '.acf7i-type-check' ).prop( 'checked', true ).trigger( 'change' );
            });

            $( document ).on( 'click', '#acf7i-deny-all', function () {
                $( '.acf7i-type-check' ).prop( 'checked', false ).trigger( 'change' );
            });

            // Copiar shortcodes
            $( document ).on( 'click', '.acf7i-btn-copy', function () {
                const targetId = $( this ).data( 'target' );
                const text     = $( '#' + targetId ).text();
                navigator.clipboard.writeText( text ).then( () => {
                    const $btn = $( this );
                    const original = $btn.text();
                    $btn.text( acf7i_admin.strings.copied );
                    setTimeout( () => $btn.text( original ), 2000 );
                });
            });

            // Storage toggles
            $( document ).on( 'change', '#acf7i-storage-local, #acf7i-storage-drive', function () {
                ACF7I_Preview.validateStorageSelection();
            });

            // Expiración de link
            $( document ).on( 'change', 'input[name="settings[mail_link_expires]"]', function () {
                const show = $( this ).val() === 'custom';
                $( '#acf7i-expire-custom-wrap' ).toggle( show );
            });

            // Auto dismiss toggle
            $( document ).on( 'change', 'input[name="settings[error_autodismiss]"]', function () {
                $( '#acf7i-autodismiss-time-wrap' ).toggle( $( this ).is( ':checked' ) );
            });

            // Subcarpeta por formulario
            $( document ).on( 'change', '#acf7i-subfolder-form', function () {
                $( '#acf7i-subfolder-name-wrap' ).toggle( $( this ).is( ':checked' ) );
            });

            // Guardar formulario via AJAX
            $( document ).on( 'submit', '#acf7i-settings-form', function ( e ) {
                e.preventDefault();
                ACF7I_Preview.saveSettings( $( this ) );
            });

            // Cambiar formulario en selector
            $( document ).on( 'change', '.acf7i-form-switcher', function () {
                const formId = $( this ).val();
                if ( formId ) {
                    window.location.href = acf7i_admin.ajax_url.replace( 'admin-ajax.php', '' ) +
                        'admin.php?page=acf7i-form-settings&form_id=' + formId;
                }
            });

            // Hover del botón preview
            $( document ).on( 'mouseenter', '#preview-btn', function () {
                const hoverBg    = $( 'input[name="settings[btn_bg_hover]"]' ).val();
                const hoverColor = $( 'input[name="settings[btn_color_hover]"]' ).val();
                $( this ).css({ 'background-color': hoverBg, 'color': hoverColor });
            }).on( 'mouseleave', '#preview-btn', function () {
                const bg    = $( 'input[name="settings[btn_bg]"]' ).val();
                const color = $( 'input[name="settings[btn_color]"]' ).val();
                $( this ).css({ 'background-color': bg, 'color': color });
            });
        },

        // =============================================
        // SIMULADORES DE ESTADO
        // =============================================
        bindSimulators() {
            $( document ).on( 'click', '.acf7i-sim-btn', function () {
                $( '.acf7i-sim-btn' ).removeClass( 'active' );
                $( this ).addClass( 'active' );
                ACF7I_Preview.applyState( $( this ).data( 'state' ) );
            });

            $( document ).on( 'click', '.acf7i-sim-error-btn', function () {
                const errorKey = $( this ).data( 'error' );
                ACF7I_Preview.simulateError( errorKey );
            });
        },

        applyState( state ) {
            const $dropzone   = $( '#preview-dropzone' );
            const $fileItem   = $( '#preview-file-item' );
            const $errorItem  = $( '#preview-error-item' );
            const $progressFill = $( '.acf7i-preview-progress-fill' );

            $dropzone.removeClass( 'state-dragging state-error state-success' );
            $fileItem.hide();
            $errorItem.hide();

            switch ( state ) {
                case 'normal':
                    break;
                case 'dragging':
                    $dropzone.addClass( 'state-dragging' );
                    break;
                case 'uploading':
                    $fileItem.show();
                    $progressFill.css( 'width', '60%' );
                    $( '.acf7i-preview-progress-pct' ).text( '60%' );
                    break;
                case 'success':
                    $fileItem.show();
                    $progressFill.css( 'width', '100%' );
                    $( '.acf7i-preview-progress-pct' ).text( '✅ Listo' );
                    $dropzone.addClass( 'state-success' );
                    break;
                case 'error':
                    $errorItem.show();
                    $dropzone.addClass( 'state-error' );
                    break;
            }
        },

        simulateError( errorKey ) {
            const $errorText = $( '.acf7i-preview-error-text' );
            const $errorItem = $( '#preview-error-item' );
            const textInput  = $( `input[name="settings[error_text_${ errorKey }]"]` );
            const text       = textInput.length ? textInput.val() : 'Error simulado';

            const sample = text
                .replace( '{filename}', 'archivo-ejemplo.ext' )
                .replace( '{maxsize}', '5MB' )
                .replace( '{maxfiles}', '5' )
                .replace( '{minfiles}', '1' )
                .replace( '{tipos}', 'JPG, PNG, PDF' )
                .replace( '{totalsize}', '25MB' );

            $errorText.text( sample );
            $errorItem.show().addClass( 'animate-in' );
            setTimeout( () => $errorItem.removeClass( 'animate-in' ), 500 );
        },

        // =============================================
        // SWITCHER DE DISPOSITIVO
        // =============================================
        bindDeviceSwitcher() {
            $( document ).on( 'click', '.acf7i-device-btn', function () {
                const device   = $( this ).data( 'device' );
                const $viewport = $( '#acf7i-preview-viewport' );

                $( '.acf7i-device-btn' ).removeClass( 'active' );
                $( this ).addClass( 'active' );

                $viewport.removeClass( 'device-desktop device-tablet device-mobile' );
                $viewport.addClass( `device-${ device }` );
            });
        },

        // =============================================
        // DRAG & DROP ORDENAR ELEMENTOS
        // =============================================
        bindSortable() {
            if ( ! $( '#acf7i-elements-sort' ).length ) return;

            $( '#acf7i-elements-sort' ).sortable({
                handle: '.acf7i-drag-handle',
                axis: 'y',
                update() {
                    const order = [];
                    $( '#acf7i-elements-sort .acf7i-sortable-item' ).each( function () {
                        order.push( $( this ).data( 'key' ) );
                    });
                    $( '#acf7i-elements-order-input' ).val( order.join( ',' ) );
                    ACF7I_Preview.updatePreviewOrder( order );
                }
            });
        },

        updatePreviewOrder( order ) {
            const $container = $( '#acf7i-preview-container' );
            const elementMap = {
                'icon'           : '#preview-icon',
                'text_main'      : '#preview-text-main',
                'text_secondary' : '#preview-text-secondary',
                'text_limits'    : '#preview-text-limits',
                'text_types'     : '#preview-text-types',
                'button'         : '#preview-btn',
            };
            order.forEach( ( key, i ) => {
                const $el = $container.find( elementMap[ key ] );
                if ( $el.length ) {
                    $el.css( 'order', i );
                }
            });
        },

        // =============================================
        // GUARDAR CONFIGURACIÓN VIA AJAX
        // =============================================
        saveSettings( $form ) {
            const $btn    = $( '#acf7i-save-btn' );
            const $status = $( '#acf7i-save-status' );
            const data    = $form.serializeArray();

            $btn.prop( 'disabled', true ).text( '⏳ Guardando...' );
            $status.removeClass( 'success error' ).text( '' );

            // Manejar checkboxes no marcados
            $form.find( 'input[type="checkbox"]' ).each( function () {
                if ( ! $( this ).is( ':checked' ) ) {
                    data.push({ name: $( this ).attr( 'name' ), value: '0' });
                }
            });

            data.push({ name: 'action', value: 'acf7i_save_settings' });

            $.post( acf7i_admin.ajax_url, data )
                .done( function ( response ) {
                    if ( response.success ) {
                        $status.addClass( 'success' ).text( '✅ ' + acf7i_admin.strings.saved );
                    } else {
                        $status.addClass( 'error' ).text( '❌ ' + acf7i_admin.strings.error );
                    }
                })
                .fail( function () {
                    $status.addClass( 'error' ).text( '❌ ' + acf7i_admin.strings.error );
                })
                .always( function () {
                    $btn.prop( 'disabled', false ).text( '💾 Guardar Configuración' );
                    setTimeout( () => $status.text( '' ).removeClass( 'success error' ), 4000 );
                });
        },

        // =============================================
        // HELPERS
        // =============================================
        dispatch( previewKey, value ) {
            if ( this.handlers[ previewKey ] ) {
                this.handlers[ previewKey ]( value );
            }
        },

        css( selector, prop, value ) {
            $( selector ).css( prop, value );
            return true;
        },

        text( selector, value ) {
            $( selector ).text( value );
        },

        toggle( selector, show ) {
            $( selector ).toggle( !! show );
        },

        setCssVar( varName, value ) {
            document.getElementById( 'acf7i-preview-container' )
                    ?.style.setProperty( varName, value );
        },

        updateIcon( type ) {
            const icons = {
                'upload-cloud': `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>`,
                'folder'      : `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>`,
                'paperclip'   : `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>`,
                'arrow-up'    : `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>`,
                'image'       : `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`,
                'file'        : `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>`,
            };
            if ( icons[ type ] ) {
                const color = $( 'input[name="settings[icon_color]"]' ).val() || '#00BCFF';
                $( '#preview-icon' ).html( icons[ type ] );
                $( '#preview-icon svg' ).css( 'stroke', color );
            }
        },

        updateCustomIcon( url ) {
            if ( url ) {
                $( '#preview-icon' ).html( `<img src="${ url }" style="width:48px;height:48px;">` );
            }
        },

        updateBtnStyle( style ) {
            const $btn = $( '#preview-btn' );
            const bg   = $( 'input[name="settings[btn_bg]"]' ).val() || '#00BCFF';
            const color= $( 'input[name="settings[btn_color]"]' ).val() || '#050018';

            $btn.removeClass( 'btn-solid btn-outline btn-ghost' ).addClass( `btn-${ style }` );

            if ( style === 'outline' ) {
                $btn.css({ 'background-color': 'transparent', 'border': `2px solid ${ bg }`, 'color': bg });
            } else if ( style === 'ghost' ) {
                $btn.css({ 'background-color': 'transparent', 'border': 'none', 'color': bg });
            } else {
                $btn.css({ 'background-color': bg, 'border': 'none', 'color': color });
            }
        },

        updateErrorBorderStyle( style ) {
            const $error = $( '.acf7i-preview-error' );
            const color  = $( 'input[name="settings[error_border_color]"]' ).val() || '#FF3600';

            $error.css({ 'border': 'none', 'border-left': 'none' });

            if ( style === 'left-only' ) {
                $error.css( 'border-left', `3px solid ${ color }` );
            } else if ( style !== 'none' ) {
                $error.css( 'border', `1px ${ style } ${ color }` );
            }
        },

        updateAllowedTypes() {
            const types = [];
            $( '.acf7i-type-check:checked' ).each( function () {
                types.push( '.' + $( this ).val() );
            });
            const $typesEl = $( '#preview-text-types' );
            const prefix   = $( 'input[name="settings[text_types_prefix]"]' ).val() || 'Archivos permitidos:';
            let html = `<span class="acf7i-preview-prefix">${ prefix }</span> `;
            const visible = types.slice( 0, 6 );
            visible.forEach( t => {
                html += `<span class="acf7i-ext-badge">${ t }</span> `;
            });
            if ( types.length > 6 ) {
                html += `<span class="acf7i-ext-badge">+${ types.length - 6 } más</span>`;
            }
            $typesEl.html( html );
        },

        updateLimitsText() {
            const maxFiles = $( 'input[name="settings[max_files]"]' ).val() || '5';
            const maxSize  = $( 'input[name="settings[max_filesize]"]' ).val() || '5';
            const unit     = $( 'select[name="settings[max_filesize_unit]"]' ).val() || 'MB';
            $( '#preview-text-limits' ).text(
                `Máximo ${ maxFiles } archivos | Hasta ${ maxSize }${ unit } cada uno`
            );
        },

        updateElementVisibility( key, show ) {
            const map = {
                'icon'           : '#preview-icon',
                'text_main'      : '#preview-text-main',
                'text_secondary' : '#preview-text-secondary',
                'text_limits'    : '#preview-text-limits',
                'text_types'     : '#preview-text-types',
                'button'         : '#preview-btn',
            };
            if ( map[ key ] ) {
                $( map[ key ] ).toggle( !! show );
            }
        },

        initFiletypeCounter() {
            this.updateTypeCounter();
        },

        updateTypeCounter() {
            const count = $( '.acf7i-type-check:checked' ).length;
            $( '#acf7i-type-count' ).text( count );
        },

        initDangerousTypesWarning() {
            this.checkDangerousTypes();
        },

        checkDangerousTypes() {
            const dangerous = [ 'php', 'php3', 'php4', 'php5', 'exe', 'sh', 'bat', 'env' ];
            let found = false;
            $( '.acf7i-type-check:checked' ).each( function () {
                if ( dangerous.includes( $( this ).val() ) ) {
                    found = true;
                    return false;
                }
            });
            $( '#acf7i-dangerous-alert' ).toggle( found );
        },

        initStorageToggle() {
            this.validateStorageSelection();
        },

        validateStorageSelection() {
            const local = $( '#acf7i-storage-local' ).is( ':checked' );
            const drive = $( '#acf7i-storage-drive' ).is( ':checked' );
            $( '#acf7i-no-storage-alert' ).toggle( ! local && ! drive );
            $( '#acf7i-local-section' ).toggle( local );
        },

        initExpireToggle() {
            const val = $( 'input[name="settings[mail_link_expires]"]:checked' ).val();
            $( '#acf7i-expire-custom-wrap' ).toggle( val === 'custom' );
            $( '#acf7i-autodismiss-time-wrap' ).toggle(
                $( 'input[name="settings[error_autodismiss]"]' ).is( ':checked' )
            );
        },

        initFilenamePreview() {
            const update = () => {
                const dateFormat = $( '#acf7i-date-format' ).val() || 'dmY';
                const timeFormat = $( '#acf7i-time-format' ).val() || 'Hi';
                const separator  = $( '#acf7i-separator' ).val() || ' ';
                const prefix     = $( '#acf7i-prefix' ).val() || '';
                const suffix     = $( '#acf7i-suffix' ).val() || '';
                const spaces     = $( '#acf7i-spaces' ).val() || 'keep';

                let name = 'pago mayo';
                if ( spaces === 'dash' )       name = name.replace( / /g, '-' );
                if ( spaces === 'underscore' ) name = name.replace( / /g, '_' );

                const now      = new Date();
                const date_str = ACF7I_Preview.formatDate( now, dateFormat );
                const time_str = ACF7I_Preview.formatTime( now, timeFormat );

                const preview = `${ prefix }${ name }${ separator }${ date_str }${ separator }${ time_str }${ suffix }.png`;
                $( '#acf7i-filename-preview' ).text( preview );
            };

            $( document ).on( 'change input', '#acf7i-date-format, #acf7i-time-format, #acf7i-separator, #acf7i-spaces, #acf7i-prefix, #acf7i-suffix', update );
            update();
        },

        formatDate( date, format ) {
            const d = String( date.getDate() ).padStart( 2, '0' );
            const m = String( date.getMonth() + 1 ).padStart( 2, '0' );
            const Y = date.getFullYear();
            return format.replace( 'dmY', d + m + Y ).replace( 'Ymd', Y + m + d )
                         .replace( 'd-m-Y', `${ d }-${ m }-${ Y }` ).replace( 'Y-m-d', `${ Y }-${ m }-${ d }` );
        },

        formatTime( date, format ) {
            const H = String( date.getHours() ).padStart( 2, '0' );
            const i = String( date.getMinutes() ).padStart( 2, '0' );
            const s = String( date.getSeconds() ).padStart( 2, '0' );
            return format.replace( 'H:i:s', `${ H }:${ i }:${ s }` )
                         .replace( 'H:i', `${ H }:${ i }` )
                         .replace( 'H-i', `${ H }-${ i }` )
                         .replace( 'Hi', H + i );
        },

        initAccordion() {
            $( document ).on( 'click', '.acf7i-accordion-header', function () {
                const $item = $( this ).closest( '.acf7i-accordion-item' );
                $item.toggleClass( 'open' );
                $item.find( '.acf7i-accordion-body' ).slideToggle( 200 );
            });
        },

        initFilenamePreview() {
            const update = () => {
                $( '#acf7i-path-preview' ).text(
                    WP_CONTENT_DIR + '/uploads/' + ( $( '#acf7i-folder-name' ).val() || 'cf7-adjuntos' ) + '/'
                );
            };
            $( document ).on( 'input', '#acf7i-folder-name', update );
        },

    };

    // Inicializar cuando el DOM esté listo
    $( document ).ready( function () {
        ACF7I_Preview.init();
    });

})( jQuery );