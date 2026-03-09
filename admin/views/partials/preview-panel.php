<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="acf7i-preview-header">
    <h3>👁️ <?php _e( 'Vista Previa en Tiempo Real', ACF7I_TEXT_DOMAIN ); ?></h3>
    <div class="acf7i-preview-device-switcher">
        <button type="button" class="acf7i-device-btn active" data-device="desktop" title="Desktop">🖥️</button>
        <button type="button" class="acf7i-device-btn" data-device="tablet"  title="Tablet">📱</button>
        <button type="button" class="acf7i-device-btn" data-device="mobile"  title="Mobile">📲</button>
    </div>
</div>

<div class="acf7i-preview-viewport device-desktop" id="acf7i-preview-viewport">
    <div class="acf7i-preview-container" id="acf7i-preview-container">

        <!-- Zona drop -->
        <div id="preview-dropzone">

            <div id="preview-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                     stroke="#00BCFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="16 16 12 12 8 16"/>
                    <line x1="12" y1="12" x2="12" y2="21"/>
                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                </svg>
            </div>

            <p class="acf7i-preview-text-main" id="preview-text-main">
                Arrastra tus archivos aquí
            </p>
            <p class="acf7i-preview-text-secondary" id="preview-text-secondary">
                o haz clic para seleccionar
            </p>
            <p class="acf7i-preview-text-limits" id="preview-text-limits">
                Máximo 5 archivos | Hasta 5MB cada uno
            </p>
            <p class="acf7i-preview-text-types" id="preview-text-types">
                <span class="acf7i-preview-prefix">Archivos permitidos:</span>
                <span class="acf7i-ext-badge">.jpg</span>
                <span class="acf7i-ext-badge">.png</span>
                <span class="acf7i-ext-badge">.pdf</span>
                <span class="acf7i-ext-badge">.docx</span>
                <span class="acf7i-ext-badge">.xlsx</span>
            </p>

            <button type="button" class="acf7i-preview-btn" id="preview-btn">
                Selecciona tus archivos
            </button>

        </div>

        <!-- Ejemplo de ficha de archivo -->
        <div class="acf7i-preview-file-item" id="preview-file-item" style="display:none;">
            <div class="acf7i-preview-file-icon">📄</div>
            <div class="acf7i-preview-file-info">
                <span class="acf7i-preview-file-name">documento-ejemplo.pdf</span>
                <div class="acf7i-preview-file-meta">
                    <span class="acf7i-preview-file-ext">pdf</span>
                    <span class="acf7i-preview-file-size">1.2 MB</span>
                </div>
            </div>
            <div class="acf7i-preview-progress-wrap">
                <div class="acf7i-preview-progress-bar">
                    <div class="acf7i-preview-progress-fill" style="width:75%"></div>
                </div>
                <span class="acf7i-preview-progress-pct">75%</span>
            </div>
            <button type="button" class="acf7i-preview-file-remove">✕</button>
        </div>

        <!-- Ejemplo de error -->
        <div class="acf7i-preview-error" id="preview-error-item" style="display:none;">
            <span class="acf7i-preview-error-icon">⛔</span>
            <span class="acf7i-preview-error-text">El archivo supera el límite permitido.</span>
            <button type="button" class="acf7i-preview-error-close">✕</button>
        </div>

    </div>
</div>

<!-- Simuladores de estado -->
<div class="acf7i-preview-simulators">
    <p class="acf7i-sim-label"><?php _e( 'Simular estado:', ACF7I_TEXT_DOMAIN ); ?></p>
    <div class="acf7i-sim-buttons">
        <button type="button" class="acf7i-sim-btn active" data-state="normal">Normal</button>
        <button type="button" class="acf7i-sim-btn" data-state="dragging">Arrastrando</button>
        <button type="button" class="acf7i-sim-btn" data-state="uploading">Subiendo</button>
        <button type="button" class="acf7i-sim-btn" data-state="success">Éxito</button>
        <button type="button" class="acf7i-sim-btn" data-state="error">Error</button>
    </div>
</div>