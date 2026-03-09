/**
 * Adjuntos CF7 - InforCom
 * Validator — Validación 100% frontend antes de subir
 */

( function ( window ) {
    'use strict';

    const ACF7I_Validator = {

        /**
         * Valida un archivo antes de subirlo
         * @param {File}   file    — Archivo a validar
         * @param {Object} config  — Configuración del campo
         * @param {Array}  current — Archivos ya en lista
         * @returns {Object} { valid: bool, error: string|null, errorKey: string|null }
         */
        validateFile( file, config, current = [] ) {

            // 1. Tipo de archivo
            const typeResult = this.validateType( file, config );
            if ( ! typeResult.valid ) return typeResult;

            // 2. Tamaño individual
            const sizeResult = this.validateSize( file, config );
            if ( ! sizeResult.valid ) return sizeResult;

            // 3. Duplicado
            const dupResult = this.validateDuplicate( file, current );
            if ( ! dupResult.valid ) return dupResult;

            // 4. Máximo de archivos (incluyendo el nuevo)
            const maxResult = this.validateMaxFiles( current, config );
            if ( ! maxResult.valid ) return maxResult;

            // 5. Tamaño total
            const totalResult = this.validateTotalSize( file, current, config );
            if ( ! totalResult.valid ) return totalResult;

            return { valid: true, error: null, errorKey: null };
        },

        /**
         * Valida que el formulario cumpla con el mínimo antes del submit
         */
        validateMinFiles( current, config ) {
            const min = parseInt( config.min_files || 0, 10 );
            if ( min > 0 && current.length < min ) {
                return {
                    valid:    false,
                    errorKey: 'minimo_archivos',
                    error:    this.buildMessage(
                        config.errors?.minimo_archivos || 'Debes subir al menos {minfiles} archivos',
                        { minfiles: min }
                    ),
                };
            }
            return { valid: true };
        },

        /**
         * Valida que el campo requerido tenga al menos un archivo
         */
        validateRequired( current, config ) {
            if ( config.required && current.length === 0 ) {
                return {
                    valid:    false,
                    errorKey: 'campo_requerido',
                    error:    config.errors?.campo_requerido
                              || 'Debes adjuntar al menos un archivo antes de enviar',
                };
            }
            return { valid: true };
        },

        // --------------------------------------------------
        // Validaciones individuales
        // --------------------------------------------------

        validateType( file, config ) {
            const allowed = [
                ...( config.allowed_types || [] ),
                ...( config.custom_types  || [] ),
            ].map( t => t.toLowerCase().replace( /^\./, '' ) );

            if ( allowed.length === 0 ) return { valid: true };

            const ext = this.getExtension( file.name );
            const mime = file.type.toLowerCase();

            if ( ! allowed.includes( ext ) ) {
                const tiposList = allowed.map( t => '.' + t ).join( ', ' );
                return {
                    valid:    false,
                    errorKey: 'tipo_no_permitido',
                    error:    this.buildMessage(
                        config.errors?.tipo_no_permitido
                            || '"{filename}" no está permitido. Solo se aceptan: {tipos}',
                        { filename: file.name, tipos: tiposList }
                    ),
                };
            }

            // Validación adicional de MIME conocidos
            const mimeMap = {
                'jpg': ['image/jpeg'],  'jpeg': ['image/jpeg'],
                'png': ['image/png'],   'gif':  ['image/gif'],
                'webp':['image/webp'],  'pdf':  ['application/pdf'],
                'mp4': ['video/mp4'],   'mp3':  ['audio/mpeg','audio/mp3'],
                'zip': ['application/zip','application/x-zip-compressed'],
                'docx':['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'xlsx':['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            };

            if ( mimeMap[ ext ] && mime && ! mimeMap[ ext ].includes( mime ) ) {
                return {
                    valid:    false,
                    errorKey: 'archivo_corrupto',
                    error:    this.buildMessage(
                        config.errors?.archivo_corrupto
                            || '"{filename}" no se puede leer o está dañado',
                        { filename: file.name }
                    ),
                };
            }

            return { valid: true };
        },

        validateSize( file, config ) {
            const max  = parseInt( config.max_filesize || 5, 10 );
            const unit = ( config.max_filesize_unit || 'MB' ).toUpperCase();
            const maxBytes = this.toBytes( max, unit );

            if ( file.size > maxBytes ) {
                return {
                    valid:    false,
                    errorKey: 'archivo_muy_grande',
                    error:    this.buildMessage(
                        config.errors?.archivo_muy_grande
                            || '"{filename}" supera el límite de {maxsize}',
                        { filename: file.name, maxsize: max + unit }
                    ),
                };
            }
            return { valid: true };
        },

        validateDuplicate( file, current ) {
            const exists = current.some(
                f => f.name === file.name && f.size === file.size
            );
            if ( exists ) {
                return {
                    valid:    false,
                    errorKey: 'archivo_duplicado',
                    error:    this.buildMessage(
                        '"{filename}" ya fue agregado',
                        { filename: file.name }
                    ),
                };
            }
            return { valid: true };
        },

        validateMaxFiles( current, config ) {
            const unlimited = config.max_files_unlimited;
            const max       = parseInt( config.max_files || 5, 10 );

            if ( ! unlimited && current.length >= max ) {
                return {
                    valid:    false,
                    errorKey: 'maximo_archivos',
                    error:    this.buildMessage(
                        config.errors?.maximo_archivos
                            || 'Solo puedes subir un máximo de {maxfiles} archivos',
                        { maxfiles: max }
                    ),
                };
            }
            return { valid: true };
        },

        validateTotalSize( newFile, current, config ) {
            const maxTotal  = parseInt( config.max_total_size || 25, 10 );
            const unit      = ( config.max_total_unit || 'MB' ).toUpperCase();
            const maxBytes  = this.toBytes( maxTotal, unit );
            const usedBytes = current.reduce( ( acc, f ) => acc + f.size, 0 );

            if ( usedBytes + newFile.size > maxBytes ) {
                return {
                    valid:    false,
                    errorKey: 'total_muy_grande',
                    error:    this.buildMessage(
                        config.errors?.total_muy_grande
                            || 'El total de archivos supera el límite de {totalsize}',
                        { totalsize: maxTotal + unit }
                    ),
                };
            }
            return { valid: true };
        },

        // --------------------------------------------------
        // Helpers
        // --------------------------------------------------

        getExtension( filename ) {
            return filename.split( '.' ).pop().toLowerCase().trim();
        },

        toBytes( value, unit ) {
            const units = { 'KB': 1024, 'MB': 1024 * 1024, 'GB': 1024 * 1024 * 1024 };
            return value * ( units[ unit ] || units['MB'] );
        },

        formatSize( bytes ) {
            if ( bytes >= 1024 * 1024 * 1024 ) return ( bytes / (1024*1024*1024) ).toFixed(1) + ' GB';
            if ( bytes >= 1024 * 1024 )        return ( bytes / (1024*1024) ).toFixed(1) + ' MB';
            if ( bytes >= 1024 )               return ( bytes / 1024 ).toFixed(1) + ' KB';
            return bytes + ' B';
        },

        buildMessage( template, vars = {} ) {
            let msg = template;
            for ( const [ key, val ] of Object.entries( vars ) ) {
                msg = msg.replace( new RegExp( `\\{${ key }\\}`, 'g' ), val );
            }
            return msg;
        },
    };

    window.ACF7I_Validator = ACF7I_Validator;

})( window );