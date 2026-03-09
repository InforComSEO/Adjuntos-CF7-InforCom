<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="acf7i-wrap">

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/header.php'; ?>

    <div class="acf7i-page-content">

        <div class="acf7i-page-header">
            <h2>📁 <?php _e( 'Log de Archivos', ACF7I_TEXT_DOMAIN ); ?></h2>
            <p><?php _e( 'Registro completo de todos los archivos subidos a través de los formularios.', ACF7I_TEXT_DOMAIN ); ?></p>
        </div>

        <!-- Mini stats -->
        <div class="acf7i-log-stats">
            <div class="acf7i-log-stat">
                <span class="acf7i-log-stat-num"><?php echo esc_html( $stats['total'] ); ?></span>
                <span class="acf7i-log-stat-label"><?php _e( 'Total', ACF7I_TEXT_DOMAIN ); ?></span>
            </div>
            <div class="acf7i-log-stat">
                <span class="acf7i-log-stat-num acf7i-num-celeste"><?php echo esc_html( $stats['today'] ); ?></span>
                <span class="acf7i-log-stat-label"><?php _e( 'Hoy', ACF7I_TEXT_DOMAIN ); ?></span>
            </div>
            <div class="acf7i-log-stat">
                <span class="acf7i-log-stat-num acf7i-num-red"><?php echo esc_html( $stats['errors'] ); ?></span>
                <span class="acf7i-log-stat-label"><?php _e( 'Errores', ACF7I_TEXT_DOMAIN ); ?></span>
            </div>
            <div class="acf7i-log-stat">
                <span class="acf7i-log-stat-num"><?php echo esc_html( ACF7I_Dashboard::format_bytes( $stats['total_size'] ?? 0 ) ); ?></span>
                <span class="acf7i-log-stat-label"><?php _e( 'Espacio total', ACF7I_TEXT_DOMAIN ); ?></span>
            </div>
        </div>

        <!-- Filtros -->
        <div class="acf7i-log-filters">
            <form method="GET" action="" id="acf7i-filters-form">
                <input type="hidden" name="page" value="acf7i-log">

                <div class="acf7i-filters-row">

                    <!-- Búsqueda -->
                    <div class="acf7i-filter-field acf7i-filter-search">
                        <input type="text" name="search"
                               class="acf7i-input"
                               placeholder="🔍 <?php esc_attr_e( 'Buscar por nombre, email, formulario...', ACF7I_TEXT_DOMAIN ); ?>"
                               value="<?php echo esc_attr( $filters['search'] ); ?>">
                    </div>

                    <!-- Formulario -->
                    <div class="acf7i-filter-field">
                        <select name="form_id" class="acf7i-select">
                            <option value=""><?php _e( '— Formulario —', ACF7I_TEXT_DOMAIN ); ?></option>
                            <?php foreach ( $forms as $form ) : ?>
                                <option value="<?php echo esc_attr( $form->form_id ); ?>"
                                    <?php selected( $filters['form_id'], $form->form_id ); ?>>
                                    <?php echo esc_html( $form->form_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="acf7i-filter-field">
                        <select name="status" class="acf7i-select">
                            <option value=""><?php _e( '— Estado —', ACF7I_TEXT_DOMAIN ); ?></option>
                            <option value="uploaded" <?php selected( $filters['status'], 'uploaded' ); ?>>✅ <?php _e( 'Subido', ACF7I_TEXT_DOMAIN ); ?></option>
                            <option value="error"    <?php selected( $filters['status'], 'error' ); ?>>❌ <?php _e( 'Error', ACF7I_TEXT_DOMAIN ); ?></option>
                        </select>
                    </div>

                    <!-- Tipo de archivo -->
                    <div class="acf7i-filter-field">
                        <select name="filetype" class="acf7i-select">
                            <option value=""><?php _e( '— Tipo —', ACF7I_TEXT_DOMAIN ); ?></option>
                            <?php foreach ( $types as $type ) : ?>
                                <option value="<?php echo esc_attr( $type ); ?>"
                                    <?php selected( $filters['filetype'], $type ); ?>>
                                    .<?php echo esc_html( strtoupper( $type ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Almacenamiento -->
                    <div class="acf7i-filter-field">
                        <select name="storage" class="acf7i-select">
                            <option value=""><?php _e( '— Almacenamiento —', ACF7I_TEXT_DOMAIN ); ?></option>
                            <option value="local" <?php selected( $filters['storage'], 'local' ); ?>>📁 Local</option>
                            <option value="drive" <?php selected( $filters['storage'], 'drive' ); ?>>☁️ Drive</option>
                            <option value="both"  <?php selected( $filters['storage'], 'both' ); ?>>📁☁️ Ambos</option>
                        </select>
                    </div>

                    <!-- Fecha desde -->
                    <div class="acf7i-filter-field">
                        <input type="date" name="date_from" class="acf7i-input"
                               title="<?php esc_attr_e( 'Desde', ACF7I_TEXT_DOMAIN ); ?>"
                               value="<?php echo esc_attr( $filters['date_from'] ); ?>">
                    </div>

                    <!-- Fecha hasta -->
                    <div class="acf7i-filter-field">
                        <input type="date" name="date_to" class="acf7i-input"
                               title="<?php esc_attr_e( 'Hasta', ACF7I_TEXT_DOMAIN ); ?>"
                               value="<?php echo esc_attr( $filters['date_to'] ); ?>">
                    </div>

                    <div class="acf7i-filter-actions">
                        <button type="submit" class="acf7i-btn acf7i-btn-primary acf7i-sm">
                            🔍 <?php _e( 'Filtrar', ACF7I_TEXT_DOMAIN ); ?>
                        </button>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=acf7i-log' ) ); ?>"
                           class="acf7i-btn acf7i-btn-secondary acf7i-sm">
                            ✕ <?php _e( 'Limpiar', ACF7I_TEXT_DOMAIN ); ?>
                        </a>
                    </div>

                </div>
            </form>
        </div>

        <!-- Acciones en bulk -->
        <div class="acf7i-log-bulk-actions">
            <label class="acf7i-bulk-select-all">
                <input type="checkbox" id="acf7i-select-all">
                <span><?php _e( 'Seleccionar todo', ACF7I_TEXT_DOMAIN ); ?></span>
            </label>
            <div class="acf7i-bulk-btns" id="acf7i-bulk-btns" style="display:none;">
                <span class="acf7i-selected-count">
                    <strong id="acf7i-selected-num">0</strong>
                    <?php _e( 'seleccionados', ACF7I_TEXT_DOMAIN ); ?>
                </span>
                <button type="button" class="acf7i-btn acf7i-btn-secondary acf7i-sm"
                        id="acf7i-bulk-delete-records">
                    📋 <?php _e( 'Eliminar registros', ACF7I_TEXT_DOMAIN ); ?>
                </button>
                <button type="button" class="acf7i-btn acf7i-btn-danger acf7i-sm"
                        id="acf7i-bulk-delete-all">
                    🗑️ <?php _e( 'Eliminar registros + archivos', ACF7I_TEXT_DOMAIN ); ?>
                </button>
            </div>
            <div class="acf7i-log-info">
                <?php printf(
                    __( 'Mostrando %d de %d registros', ACF7I_TEXT_DOMAIN ),
                    count( $result['items'] ),
                    $result['total']
                ); ?>
            </div>
        </div>

        <!-- Tabla del log -->
        <div class="acf7i-card">
            <div class="acf7i-card-body" style="padding:0;">
                <?php if ( ! empty( $result['items'] ) ) : ?>
                <div class="acf7i-log-table-wrap">
                    <table class="acf7i-log-table">
                        <thead>
                            <tr>
                                <th class="acf7i-col-check"><input type="checkbox" id="acf7i-check-all-th"></th>
                                <th><?php _e( 'Archivo', ACF7I_TEXT_DOMAIN ); ?></th>
                                <th><?php _e( 'Formulario', ACF7I_TEXT_DOMAIN ); ?></th>
                                <th><?php _e( 'Remitente', ACF7I_TEXT_DOMAIN ); ?></th>
                                <th><?php _e( 'Peso', ACF7I_TEXT_DOMAIN ); ?></th>
                                <th><?php _e( 'Almacenamiento', ACF7I_TEXT_DOMAIN ); ?></th>
                                <th><?php _e( 'Fecha', ACF7I_TEXT_DOMAIN ); ?></th>
                                <th><?php _e( 'Estado', ACF7I_TEXT_DOMAIN ); ?></th>
                                <th><?php _e( 'Acciones', ACF7I_TEXT_DOMAIN ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $result['items'] as $item ) : ?>
                            <tr class="acf7i-log-row" data-id="<?php echo esc_attr( $item->id ); ?>">

                                <td class="acf7i-col-check">
                                    <input type="checkbox" class="acf7i-row-check"
                                           value="<?php echo esc_attr( $item->id ); ?>">
                                </td>

                                <td class="acf7i-col-file">
                                    <div class="acf7i-log-file">
                                        <span class="acf7i-log-file-icon">
                                            <?php echo $this->get_file_emoji( $item->filetype ); ?>
                                        </span>
                                        <div class="acf7i-log-file-info">
                                            <span class="acf7i-log-filename" title="<?php echo esc_attr( $item->original_name ); ?>">
                                                <?php echo esc_html( $item->original_name ); ?>
                                            </span>
                                            <span class="acf7i-log-ext">
                                                .<?php echo esc_html( strtoupper( $item->filetype ) ); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="acf7i-log-form">
                                        <?php echo esc_html( $item->form_title ); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ( ! empty( $item->sender_email ) ) : ?>
                                        <span class="acf7i-log-sender">
                                            <?php if ( ! empty( $item->sender_name ) ) : ?>
                                                <strong><?php echo esc_html( $item->sender_name ); ?></strong><br>
                                            <?php endif; ?>
                                            <a href="mailto:<?php echo esc_attr( $item->sender_email ); ?>"
                                               class="acf7i-link">
                                                <?php echo esc_html( $item->sender_email ); ?>
                                            </a>
                                        </span>
                                    <?php else : ?>
                                        <span class="acf7i-text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="acf7i-log-size">
                                        <?php echo esc_html( ACF7I_Dashboard::format_bytes( $item->filesize ) ); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="acf7i-log-storage">
                                        <?php if ( in_array( $item->storage, array( 'local', 'both' ) ) ) : ?>
                                            <span class="acf7i-storage-badge local" title="<?php esc_attr_e( 'Servidor local', ACF7I_TEXT_DOMAIN ); ?>">📁</span>
                                        <?php endif; ?>
                                        <?php if ( in_array( $item->storage, array( 'drive', 'both' ) ) ) : ?>
                                            <span class="acf7i-storage-badge drive" title="<?php esc_attr_e( 'Google Drive', ACF7I_TEXT_DOMAIN ); ?>">☁️</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="acf7i-log-date">
                                        <?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $item->uploaded_at ) ) ); ?>
                                        <small><?php echo esc_html( date_i18n( 'H:i', strtotime( $item->uploaded_at ) ) ); ?></small>
                                    </span>
                                </td>

                                <td>
                                    <?php if ( $item->status === 'uploaded' ) : ?>
                                        <span class="acf7i-badge acf7i-badge-success">✅ <?php _e( 'OK', ACF7I_TEXT_DOMAIN ); ?></span>
                                    <?php else : ?>
                                        <span class="acf7i-badge acf7i-badge-error">❌ <?php _e( 'Error', ACF7I_TEXT_DOMAIN ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $item->token_expires ) && strtotime( $item->token_expires ) < time() ) : ?>
                                        <span class="acf7i-badge acf7i-badge-warning" title="<?php esc_attr_e( 'Link expirado', ACF7I_TEXT_DOMAIN ); ?>">⏰</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="acf7i-log-actions">

                                        <!-- Ver archivo -->
                                        <?php if ( ! empty( $item->view_token ) ) : ?>
                                            <a href="<?php echo esc_url( add_query_arg( 'acf7i-view', $item->view_token, home_url('/') ) ); ?>"
                                               target="_blank"
                                               class="acf7i-action-btn acf7i-action-view"
                                               title="<?php esc_attr_e( 'Ver archivo', ACF7I_TEXT_DOMAIN ); ?>">
                                                👁️
                                            </a>
                                        <?php endif; ?>

                                        <!-- Reenviar correo -->
                                        <button type="button"
                                                class="acf7i-action-btn acf7i-action-resend acf7i-log-resend"
                                                data-id="<?php echo esc_attr( $item->id ); ?>"
                                                data-email="<?php echo esc_attr( $item->sender_email ); ?>"
                                                title="<?php esc_attr_e( 'Reenviar correo', ACF7I_TEXT_DOMAIN ); ?>">
                                            📧
                                        </button>

                                        <!-- Eliminar -->
                                        <button type="button"
                                                class="acf7i-action-btn acf7i-action-delete acf7i-log-delete"
                                                data-id="<?php echo esc_attr( $item->id ); ?>"
                                                title="<?php esc_attr_e( 'Eliminar', ACF7I_TEXT_DOMAIN ); ?>">
                                            🗑️
                                        </button>

                                    </div>
                                </td>

                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ( $result['total_pages'] > 1 ) : ?>
                <div class="acf7i-pagination">
                    <?php
                    $base_url = admin_url( 'admin.php?' . http_build_query( array_merge(
                        array_filter( $filters ),
                        array( 'page' => 'acf7i-log' )
                    )));

                    for ( $i = 1; $i <= $result['total_pages']; $i++ ) :
                        $is_current = $i === $result['page'];
                    ?>
                        <a href="<?php echo esc_url( add_query_arg( 'paged', $i, $base_url ) ); ?>"
                           class="acf7i-page-btn <?php echo $is_current ? 'active' : ''; ?>">
                            <?php echo esc_html( $i ); ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <?php else : ?>
                    <div class="acf7i-empty-state" style="padding:60px 20px;">
                        <span>📭</span>
                        <p><?php _e( 'No se encontraron archivos con los filtros aplicados.', ACF7I_TEXT_DOMAIN ); ?></p>
                        <?php if ( ! empty( array_filter( array( $filters['search'], $filters['form_id'], $filters['status'] ) ) ) ) : ?>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=acf7i-log' ) ); ?>"
                               class="acf7i-btn acf7i-btn-secondary acf7i-sm" style="margin-top:12px;">
                                ✕ <?php _e( 'Limpiar filtros', ACF7I_TEXT_DOMAIN ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <?php require_once ACF7I_PLUGIN_DIR . 'admin/views/partials/footer.php'; ?>

</div>

<?php
// CSS de estilos del log
?>
<style>
.acf7i-log-stats {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.acf7i-log-stat {
    background: var(--acf7i-bg-card);
    border: 1px solid var(--acf7i-border);
    border-radius: var(--acf7i-radius);
    padding: 14px 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 120px;
    box-shadow: var(--acf7i-shadow);
}
.acf7i-log-stat-num {
    font-size: 24px;
    font-weight: 800;
    color: var(--acf7i-azul1);
    line-height: 1;
}
.acf7i-num-celeste { color: var(--acf7i-celeste); }
.acf7i-num-red     { color: var(--acf7i-rojo); }
.acf7i-log-stat-label {
    font-size: 12px;
    color: var(--acf7i-text-muted);
    font-weight: 500;
}
.acf7i-log-filters {
    background: var(--acf7i-bg-card);
    border: 1px solid var(--acf7i-border);
    border-radius: var(--acf7i-radius);
    padding: 16px 20px;
    margin-bottom: 16px;
}
.acf7i-filters-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.acf7i-filter-field { flex: 1; min-width: 140px; }
.acf7i-filter-search { flex: 2; min-width: 220px; }
.acf7i-filter-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-shrink: 0;
}
.acf7i-log-bulk-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.acf7i-bulk-select-all {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    cursor: pointer;
    color: var(--acf7i-text-secondary);
}
.acf7i-bulk-btns {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.acf7i-selected-count {
    font-size: 13px;
    color: var(--acf7i-text-secondary);
}
.acf7i-log-info {
    margin-left: auto;
    font-size: 12px;
    color: var(--acf7i-text-muted);
}
.acf7i-log-table-wrap { overflow-x: auto; }
.acf7i-log-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.acf7i-log-table thead th {
    padding: 12px 14px;
    background: var(--acf7i-bg-page);
    font-size: 11px;
    font-weight: 700;
    color: var(--acf7i-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border-bottom: 2px solid var(--acf7i-border);
    white-space: nowrap;
    text-align: left;
}
.acf7i-log-table tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--acf7i-border);
    vertical-align: middle;
}
.acf7i-log-table tbody tr:hover { background: rgba(0,188,255,0.03); }
.acf7i-col-check { width: 36px; }
.acf7i-log-file { display: flex; align-items: center; gap: 10px; }
.acf7i-log-file-icon { font-size: 22px; flex-shrink: 0; }
.acf7i-log-file-info { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.acf7i-log-filename {
    font-weight: 600;
    color: var(--acf7i-azul1);
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
.acf7i-log-ext {
    font-size: 10px;
    font-weight: 700;
    color: var(--acf7i-text-muted);
    text-transform: uppercase;
}
.acf7i-log-form {
    font-size: 12px;
    color: var(--acf7i-text-secondary);
    max-width: 150px;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.acf7i-log-sender strong { font-size: 12px; color: var(--acf7i-azul1); display: block; }
.acf7i-log-sender a { font-size: 11px; }
.acf7i-log-size { font-size: 12px; color: var(--acf7i-text-secondary); font-weight: 600; white-space: nowrap; }
.acf7i-log-storage { display: flex; gap: 4px; }
.acf7i-storage-badge { font-size: 18px; cursor: default; }
.acf7i-log-date { display: flex; flex-direction: column; gap: 1px; font-size: 12px; color: var(--acf7i-text-secondary); white-space: nowrap; }
.acf7i-log-date small { color: var(--acf7i-text-muted); font-size: 11px; }
.acf7i-log-actions { display: flex; gap: 4px; align-items: center; }
.acf7i-action-btn {
    width: 30px;
    height: 30px;
    border: 1px solid var(--acf7i-border);
    background: var(--acf7i-bg-input);
    border-radius: var(--acf7i-radius-sm);
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: var(--acf7i-transition);
}
.acf7i-action-btn:hover { border-color: var(--acf7i-celeste); background: rgba(0,188,255,0.06); }
.acf7i-action-delete:hover { border-color: var(--acf7i-rojo); background: rgba(255,54,0,0.06); }
.acf7i-pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    padding: 16px 20px;
    border-top: 1px solid var(--acf7i-border);
    flex-wrap: wrap;
}
.acf7i-page-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--acf7i-border);
    border-radius: var(--acf7i-radius-sm);
    font-size: 12px;
    font-weight: 600;
    color: var(--acf7i-text-secondary);
    text-decoration: none;
    transition: var(--acf7i-transition);
}
.acf7i-page-btn:hover,
.acf7i-page-btn.active {
    background: var(--acf7i-celeste);
    border-color: var(--acf7i-celeste);
    color: var(--acf7i-azul1);
}
</style>