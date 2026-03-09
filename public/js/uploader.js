/**
 * Adjuntos CF7 - InforCom
 * Uploader — Maneja la subida AJAX de archivos al servidor
 */

( function ( $, window ) {
    'use strict';

    const ACF7I_Uploader = {

        /**
         * Sube un archivo al servidor vía AJAX
         * @param {File}     file      — Archivo a subir
         * @param {Object}   config    — Config del campo
         * @param {Object}   callbacks — { onProgress, onSuccess, onError }
         * @returns {XMLHttpRequest}
         */
        upload( file, config, callbacks = {} ) {
            const formData = new FormData();
            formData.append( 'action',     'acf7i_upload' );
            formData.append( 'nonce',      config.nonce );
            formData.append( 'file',       file );
            formData.append( 'field_name', config.name );
            formData.append( 'session_id', this.getSessionId() );

            const xhr = new XMLHttpRequest();

            // Progreso
            xhr.upload.addEventListener( 'progress', ( e ) => {
                if ( e.lengthComputable ) {
                    const pct = Math.round( ( e.loaded / e.total ) * 100 );
                    if ( callbacks.onProgress ) callbacks.onProgress( pct );
                }
            });

            // Completado
            xhr.addEventListener( 'load', () => {
                if ( xhr.status >= 200 && xhr.status < 300 ) {
                    try {
                        const response = JSON.parse( xhr.responseText );
                        if ( response.success ) {
                            if ( callbacks.onSuccess ) callbacks.onSuccess( response.data );
                        } else {
                            const errMsg = response.data?.message
                                || config.errors?.error_servidor
                                || 'Error al subir el archivo';
                            if ( callbacks.onError ) callbacks.onError( errMsg );
                        }
                    } catch ( e ) {
                        if ( callbacks.onError ) {
                            callbacks.onError( config.errors?.error_servidor || 'Error del servidor' );
                        }
                    }
                } else {
                    if ( callbacks.onError ) {
                        callbacks.onError( config.errors?.error_servidor || 'Error del servidor' );
                    }
                }
            });

            // Timeout
            xhr.addEventListener( 'timeout', () => {
                if ( callbacks.onError ) {
                    callbacks.onError(
                        ACF7I_Validator.buildMessage(
                            config.errors?.timeout_subida
                                || 'La subida de "{filename}" tardó demasiado',
                            { filename: file.name }
                        )
                    );
                }
            });

            // Error de red
            xhr.addEventListener( 'error', () => {
                if ( callbacks.onError ) {
                    callbacks.onError( config.errors?.error_servidor || 'Error de conexión' );
                }
            });

            xhr.timeout = 120000; // 2 minutos máximo
            xhr.open( 'POST', acf7i_public.ajax_url, true );
            xhr.send( formData );

            return xhr;
        },

        /**
         * Elimina un archivo temporal del servidor
         */
        deleteTemp( tempPath, config ) {
            $.post( acf7i_public.ajax_url, {
                action:     'acf7i_delete_temp',
                nonce:      config.nonce,
                temp_path:  tempPath,
                session_id: this.getSessionId(),
            });
        },

        /**
         * Genera o recupera un session ID único por visita
         */
        getSessionId() {
            let id = sessionStorage.getItem( 'acf7i_session' );
            if ( ! id ) {
                id = 'acf7i_' + Date.now() + '_' + Math.random().toString(36).substr(2,9);
                sessionStorage.setItem( 'acf7i_session', id );
            }
            return id;
        },

        /**
         * Devuelve el ícono emoji según la extensión del archivo
         */
        getFileIcon( filename ) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                // Imágenes
                jpg: '🖼️', jpeg: '🖼️', png: '🖼️', gif: '🖼️',
                webp: '🖼️', svg: '🖼️', bmp: '🖼️', tiff: '🖼️',
                // Documentos
                pdf:  '📕', doc: '📘', docx: '📘', xls: '📗',
                xlsx: '📗', ppt: '📙', pptx: '📙', txt: '📄',
                csv:  '📊', rtf: '📄', odt: '📄', ods: '📊',
                // Audio
                mp3: '🎵', wav: '🎵', ogg: '🎵', m4a: '🎵',
                flac:'🎵', aac: '🎵',
                // Video
                mp4: '🎬', mov: '🎬', avi: '🎬', mkv: '🎬',
                webm:'🎬', wmv: '🎬',
                // Comprimidos
                zip: '🗜️', rar: '🗜️', '7z': '🗜️', tar: '🗜️',
                gz:  '🗜️',
                // Diseño
                psd: '🎨', ai: '🎨', eps: '🎨', fig: '🎨',
                // Código
                html:'💻', css: '💻', js: '💻', json: '💻',
                xml: '💻', sql: '💻', php: '💻', md: '💻',
            };
            return icons[ ext ] || '📄';
        },

        /**
         * Formatea el tamaño de un archivo
         */
        formatSize( bytes ) {
            return ACF7I_Validator.formatSize( bytes );
        },

        /**
         * Genera una miniatura para imágenes
         */
        generateThumbnail( file, callback ) {
            const imageExts = ['jpg','jpeg','png','gif','webp','bmp'];
            const ext = file.name.split('.').pop().toLowerCase();

            if ( ! imageExts.includes( ext ) ) {
                callback( null );
                return;
            }

            const reader = new FileReader();
            reader.onload = ( e ) => callback( e.target.result );
            reader.onerror = () => callback( null );
            reader.readAsDataURL( file );
        },
    };

    window.ACF7I_Uploader = ACF7I_Uploader;

})( jQuery, window );