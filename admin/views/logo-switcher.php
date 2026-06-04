<?php
defined('HOOMA_PATH') || exit;
$options = get_option('hooma_smart_header_settings');
$switcher = isset($options['logo_switcher']) ? $options['logo_switcher'] : [];

// Safe helper
if (!function_exists('_hsh_val')) {
    function _hsh_val($options, $key, $default = '')
    {
        $keys = explode('.', $key);
        $val = $options;
        foreach ($keys as $k) {
            if (isset($val[$k]))
                $val = $val[$k];
            else
                return $default;
        }
        return $val;
    }
}
?>

<form method="post" action="options.php" id="hooma-sh-form">
    <style>
    .hsh-logo-columns {
        display: flex;
        gap: 28px;
        flex-wrap: wrap;
        width: 100%;
    }

    .hsh-logo-column {
        background: #ffffff;
        padding: 24px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        flex: 1;
        min-width: 320px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-sizing: border-box;
    }

    .hsh-logo-column:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
        border-color: #cbd5e1 !important;
    }

    .hsh-logo-column-title {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hsh-logo-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
        transition: all 0.2s ease;
        position: relative;
    }

    .hsh-logo-card:hover {
        border-color: #cbd5e1;
        background-color: #f1f5f9;
    }

    .hsh-logo-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .hsh-logo-card-title {
        font-weight: 600;
        font-size: 11px;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .hsh-logo-preview-container {
        height: 120px;
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }

    .hsh-logo-preview-container.has-image {
        border-style: solid;
        border-color: #e2e8f0;
    }

    .hsh-logo-preview-container.is-inherited {
        border-style: dashed !important;
        border-color: #fde68a !important;
        background-color: #fffdf5 !important;
    }

    .hsh-logo-preview-container.is-inherited::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: radial-gradient(#fcd34d 1.5px, transparent 1.5px);
        background-size: 8px 8px;
        opacity: 0.25;
        pointer-events: none;
        z-index: 0;
    }

    .hsh-logo-preview-img {
        max-width: 90%;
        max-height: 100px;
        object-fit: contain;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1;
        position: relative;
    }

    .hsh-logo-preview-img:hover {
        transform: scale(1.04);
    }

    .hsh-logo-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        color: #94a3b8;
        font-size: 11px;
        user-select: none;
    }

    .hsh-logo-placeholder svg {
        width: 28px;
        height: 28px;
        stroke: currentColor;
        fill: none;
        opacity: 0.6;
        transition: all 0.2s ease;
    }

    .hsh-logo-card:hover .hsh-logo-placeholder svg {
        transform: scale(1.1);
        color: #64748b;
    }

    .hsh-inherited-pill {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    .hsh-logo-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .hsh-logo-upload-btn {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
        border-radius: 6px !important;
        font-weight: 600 !important;
        padding: 0 12px !important;
        height: 32px !important;
        line-height: 30px !important;
        font-size: 12px !important;
        transition: all 0.2s ease !important;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .hsh-logo-upload-btn:hover {
        background: #f8fafc !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    .hsh-logo-remove-btn {
        color: #ef4444 !important;
        text-decoration: none !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        transition: color 0.2s ease !important;
        cursor: pointer;
    }

    .hsh-logo-remove-btn:hover {
        color: #dc2626 !important;
        text-decoration: underline !important;
    }
    </style>
    <?php settings_fields('hooma_smart_header_group'); ?>
    <input type="hidden" name="hooma_smart_header_settings[logo_switcher][sentinel_logo]" value="1">

    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Cambio de Logo</h2>
            <?php
            // Detect strategy for display
            $adapter = \HoomaModules\HoomaSmartHeader\Services\ThemeDetector::get_adapter();
            $strategy_name = $adapter->get_name();
            $strategy_class = $adapter->requires_js_fallback() ? 'notice-warning' : 'notice-success';
            ?>
            <div class="notice <?php echo $strategy_class; ?> inline" style="margin-top: 10px; margin-bottom: 10px;">
                <p>
                    <strong>Estrategia Detectada:</strong> <?php echo esc_html($strategy_name); ?>
                    <?php if ($adapter->requires_js_fallback()): ?>
                        <br><em>(Se usará JavaScript como fallback).</em>
                    <?php else: ?>
                        <br><em>(Integración nativa PHP aplicada).</em>
                    <?php endif; ?>
                </p>
            </div>
            <p class="description">Alterna entre dos logos cuando el elemento base adquiere un estado específico (Clase CSS).</p>
        </div>

        <div class="hooma-sh-content">
            <!-- Activator -->
            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Habilitar Cambio de Logo</strong>
                </div>
                <div class="hsh-row-control">
                    <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[logo_switcher][enabled]" value="1"
                        <?php checked(_hsh_val($switcher, 'enabled'), '1'); ?>>
                </div>
            </div>

            <!-- Configuration -->
            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Selector del logo a ser cambiado</strong>
                    <p class="description">El selector de la etiqueta <code>&lt;img&gt;</code> que se alternará. (Por defecto: <code>.custom-logo, .site-logo img</code>).</p>
                </div>
                <div class="hsh-row-control">
                    <input type="text" name="hooma_smart_header_settings[selectors][logo]"
                        value="<?php echo esc_attr(_hsh_val($options, 'selectors.logo')); ?>" class="regular-text">
                </div>
            </div>

            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Dimensiones</strong>
                    <p class="description">Déjalo vacío para usar el original. "Detectar" buscará el atributo <code>width</code> o estilo inline.</p>
                </div>
                <div class="hsh-row-control">
                    <div class="hooma-sh-verify-wrapper" style="display: flex; align-items: center; gap: 10px;">
                        <label>Largura máxima (px):
                            <input type="number" step="0.01"
                                name="hooma_smart_header_settings[logo_switcher][max_width]"
                                value="<?php echo esc_attr(_hsh_val($switcher, 'max_width')); ?>" class="small-text"
                                placeholder="Auto">
                        </label>
                        <button type="button" class="button" id="hsh-detect-width-btn">Detectar</button>
                        <span class="hooma-sh-status"></span>
                    </div>
                </div>
            </div>

            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Selector del elemento a ser observado</strong>
                    <p class="description">El elemento que será observado (Ej.:<code>#logo</code>, <code>.logo-container img</code>).</p>
                </div>
                <div class="hsh-row-control">
                    <input type="text" name="hooma_smart_header_settings[logo_switcher][trigger_selector]"
                        value="<?php echo esc_attr(_hsh_val($switcher, 'trigger_selector', 'header')); ?>"
                        class="regular-text">
                </div>
            </div>

            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Esperando por la clase</strong>
                    <p class="description">La clase que provoca el cambio (e.g. <code>is-fixed</code>, <code>hoo-is-sticky</code>).</p>
                </div>
                <div class="hsh-row-control">
                    <input type="text" name="hooma_smart_header_settings[logo_switcher][state_class]"
                        value="<?php echo esc_attr(_hsh_val($switcher, 'state_class', 'hoo-is-sticky')); ?>"
                        class="regular-text">
                </div>
            </div>
        </div>
    </div>

    <!-- Logos -->
    <?php
    // Resolviendo cascadas en PHP para la carga inicial
    $init_logo = _hsh_val($switcher, 'initial_logo');
    $init_logo_tablet = _hsh_val($switcher, 'initial_logo_tablet');
    $init_logo_mobile = _hsh_val($switcher, 'initial_logo_mobile');

    $init_tablet_src = $init_logo_tablet ?: $init_logo;
    $init_tablet_opacity = $init_logo_tablet ? '1.0' : '0.4';
    $init_tablet_has_src = !empty($init_tablet_src);
    $init_tablet_is_inherited = !$init_logo_tablet && !empty($init_logo);

    $init_mobile_src = $init_logo_mobile ?: ($init_logo_tablet ?: $init_logo);
    $init_mobile_opacity = $init_logo_mobile ? '1.0' : '0.4';
    $init_mobile_has_src = !empty($init_mobile_src);
    $init_mobile_is_inherited = !$init_logo_mobile && (!empty($init_logo_tablet) || !empty($init_logo));

    $alt_logo = _hsh_val($switcher, 'alt_logo');
    $alt_logo_tablet = _hsh_val($switcher, 'alt_logo_tablet');
    $alt_logo_mobile = _hsh_val($switcher, 'alt_logo_mobile');

    $alt_tablet_src = $alt_logo_tablet ?: $alt_logo;
    $alt_tablet_opacity = $alt_logo_tablet ? '1.0' : '0.4';
    $alt_tablet_has_src = !empty($alt_tablet_src);
    $alt_tablet_is_inherited = !$alt_logo_tablet && !empty($alt_logo);

    $alt_mobile_src = $alt_logo_mobile ?: ($alt_logo_tablet ?: $alt_logo);
    $alt_mobile_opacity = $alt_logo_mobile ? '1.0' : '0.4';
    $alt_mobile_has_src = !empty($alt_mobile_src);
    $alt_mobile_is_inherited = !$alt_logo_mobile && (!empty($alt_logo_tablet) || !empty($alt_logo));
    ?>
    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Logotipos Responsivos</h2>
            <p class="description">Puedes definir logotipos únicos para Tablet y Móvil. Si no se especifican, se aplicará el diseño en cascada (Móvil hereda de Tablet, y Tablet de Escritorio).</p>
        </div>
        <div class="hooma-sh-content">
            <div class="hsh-row" style="padding: 24px;">
                <div class="hsh-logo-columns">
                    
                    <!-- Columna de Logo Inicial (Antes) -->
                    <div class="hsh-logo-column">
                        <h3 class="hsh-logo-column-title">
                            Logo Inicial (Antes)
                        </h3>
                        
                        <!-- Escritorio Initial -->
                        <div class="hsh-logo-card">
                            <div class="hsh-logo-card-header">
                                <span class="hsh-logo-card-title">Escritorio (Principal)</span>
                            </div>
                            <div class="hsh-logo-preview-container <?php echo $init_logo ? 'has-image' : ''; ?>">
                                <div class="hsh-logo-placeholder" style="display: <?php echo $init_logo ? 'none' : 'flex'; ?>;">
                                    <svg viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                                    </svg>
                                    <span>Sin logotipo</span>
                                </div>
                                <img id="hsh-preview-init" class="hsh-logo-preview-img" src="<?php echo esc_url($init_logo); ?>"
                                    style="display: <?php echo $init_logo ? 'block' : 'none'; ?>;">
                            </div>
                            <div class="hsh-logo-actions">
                                <input type="hidden" name="hooma_smart_header_settings[logo_switcher][initial_logo]"
                                    id="hsh-input-init" value="<?php echo esc_attr($init_logo); ?>">
                                <button type="button" class="button hsh-logo-upload-btn hsh-upload-button" data-target="hsh-input-init"
                                    data-preview="hsh-preview-init">Seleccionar Logo</button>
                                <a href="#" class="hsh-logo-remove-btn hsh-remove-button" data-target="hsh-input-init" data-preview="hsh-preview-init"
                                    style="display: <?php echo $init_logo ? 'inline' : 'none'; ?>;">Quitar</a>
                            </div>
                        </div>

                        <!-- Tablet Initial -->
                        <div class="hsh-logo-card">
                            <div class="hsh-logo-card-header">
                                <span class="hsh-logo-card-title">Tablet (Opcional)</span>
                                <span class="hsh-inherited-pill hoo-inherited-badge init-inherited" style="display: <?php echo $init_tablet_is_inherited ? 'inline-block' : 'none'; ?>;">[Heredado]</span>
                            </div>
                            <div class="hsh-logo-preview-container <?php echo $init_tablet_has_src ? 'has-image' : ''; ?> <?php echo $init_tablet_is_inherited ? 'is-inherited' : ''; ?>">
                                <div class="hsh-logo-placeholder" style="display: <?php echo $init_tablet_has_src ? 'none' : 'flex'; ?>;">
                                    <svg viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                                    </svg>
                                    <span>Sin logotipo</span>
                                </div>
                                <img id="hsh-preview-init-tablet" class="hsh-logo-preview-img" src="<?php echo esc_url($init_tablet_src); ?>"
                                    style="display: <?php echo $init_tablet_has_src ? 'block' : 'none'; ?>; opacity: <?php echo $init_tablet_opacity; ?>;">
                            </div>
                            <div class="hsh-logo-actions">
                                <input type="hidden" name="hooma_smart_header_settings[logo_switcher][initial_logo_tablet]"
                                    id="hsh-input-init-tablet" value="<?php echo esc_attr($init_logo_tablet); ?>">
                                <button type="button" class="button hsh-logo-upload-btn hsh-upload-button" data-target="hsh-input-init-tablet"
                                    data-preview="hsh-preview-init-tablet">Seleccionar Logo</button>
                                <a href="#" class="hsh-logo-remove-btn hsh-remove-button" data-target="hsh-input-init-tablet" data-preview="hsh-preview-init-tablet"
                                    style="display: <?php echo $init_logo_tablet ? 'inline' : 'none'; ?>;">Quitar</a>
                            </div>
                        </div>

                        <!-- Mobile Initial -->
                        <div class="hsh-logo-card" style="margin-bottom: 0;">
                            <div class="hsh-logo-card-header">
                                <span class="hsh-logo-card-title">Móvil (Opcional)</span>
                                <span class="hsh-inherited-pill hoo-inherited-badge init-inherited" style="display: <?php echo $init_mobile_is_inherited ? 'inline-block' : 'none'; ?>;">[Heredado]</span>
                            </div>
                            <div class="hsh-logo-preview-container <?php echo $init_mobile_has_src ? 'has-image' : ''; ?> <?php echo $init_mobile_is_inherited ? 'is-inherited' : ''; ?>">
                                <div class="hsh-logo-placeholder" style="display: <?php echo $init_mobile_has_src ? 'none' : 'flex'; ?>;">
                                    <svg viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                                    </svg>
                                    <span>Sin logotipo</span>
                                </div>
                                <img id="hsh-preview-init-mobile" class="hsh-logo-preview-img" src="<?php echo esc_url($init_mobile_src); ?>"
                                    style="display: <?php echo $init_mobile_has_src ? 'block' : 'none'; ?>; opacity: <?php echo $init_mobile_opacity; ?>;">
                            </div>
                            <div class="hsh-logo-actions">
                                <input type="hidden" name="hooma_smart_header_settings[logo_switcher][initial_logo_mobile]"
                                    id="hsh-input-init-mobile" value="<?php echo esc_attr($init_logo_mobile); ?>">
                                <button type="button" class="button hsh-logo-upload-btn hsh-upload-button" data-target="hsh-input-init-mobile"
                                    data-preview="hsh-preview-init-mobile">Seleccionar Logo</button>
                                <a href="#" class="hsh-logo-remove-btn hsh-remove-button" data-target="hsh-input-init-mobile" data-preview="hsh-preview-init-mobile"
                                    style="display: <?php echo $init_logo_mobile ? 'inline' : 'none'; ?>;">Quitar</a>
                            </div>
                        </div>

                    </div>

                    <!-- Columna de Logo Alternativo (Después) -->
                    <div class="hsh-logo-column">
                        <h3 class="hsh-logo-column-title">
                            Logo Alternativo (Después)
                        </h3>
                        
                        <!-- Escritorio Alt -->
                        <div class="hsh-logo-card">
                            <div class="hsh-logo-card-header">
                                <span class="hsh-logo-card-title">Escritorio (Principal)</span>
                            </div>
                            <div class="hsh-logo-preview-container <?php echo $alt_logo ? 'has-image' : ''; ?>">
                                <div class="hsh-logo-placeholder" style="display: <?php echo $alt_logo ? 'none' : 'flex'; ?>;">
                                    <svg viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                                    </svg>
                                    <span>Sin logotipo</span>
                                </div>
                                <img id="hsh-preview-alt" class="hsh-logo-preview-img" src="<?php echo esc_url($alt_logo); ?>"
                                    style="display: <?php echo $alt_logo ? 'block' : 'none'; ?>;">
                            </div>
                            <div class="hsh-logo-actions">
                                <input type="hidden" name="hooma_smart_header_settings[logo_switcher][alt_logo]" id="hsh-input-alt"
                                    value="<?php echo esc_attr($alt_logo); ?>">
                                <button type="button" class="button hsh-logo-upload-btn hsh-upload-button" data-target="hsh-input-alt"
                                    data-preview="hsh-preview-alt">Seleccionar Logo</button>
                                <a href="#" class="hsh-logo-remove-btn hsh-remove-button" data-target="hsh-input-alt" data-preview="hsh-preview-alt"
                                    style="display: <?php echo $alt_logo ? 'inline' : 'none'; ?>;">Quitar</a>
                            </div>
                        </div>

                        <!-- Tablet Alt -->
                        <div class="hsh-logo-card">
                            <div class="hsh-logo-card-header">
                                <span class="hsh-logo-card-title">Tablet (Opcional)</span>
                                <span class="hsh-inherited-pill hoo-inherited-badge alt-inherited" style="display: <?php echo $alt_tablet_is_inherited ? 'inline-block' : 'none'; ?>;">[Heredado]</span>
                            </div>
                            <div class="hsh-logo-preview-container <?php echo $alt_tablet_has_src ? 'has-image' : ''; ?> <?php echo $alt_tablet_is_inherited ? 'is-inherited' : ''; ?>">
                                <div class="hsh-logo-placeholder" style="display: <?php echo $alt_tablet_has_src ? 'none' : 'flex'; ?>;">
                                    <svg viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                                    </svg>
                                    <span>Sin logotipo</span>
                                </div>
                                <img id="hsh-preview-alt-tablet" class="hsh-logo-preview-img" src="<?php echo esc_url($alt_tablet_src); ?>"
                                    style="display: <?php echo $alt_tablet_has_src ? 'block' : 'none'; ?>; opacity: <?php echo $alt_tablet_opacity; ?>;">
                            </div>
                            <div class="hsh-logo-actions">
                                <input type="hidden" name="hooma_smart_header_settings[logo_switcher][alt_logo_tablet]" id="hsh-input-alt-tablet"
                                    value="<?php echo esc_attr($alt_logo_tablet); ?>">
                                <button type="button" class="button hsh-logo-upload-btn hsh-upload-button" data-target="hsh-input-alt-tablet"
                                    data-preview="hsh-preview-alt-tablet">Seleccionar Logo</button>
                                <a href="#" class="hsh-logo-remove-btn hsh-remove-button" data-target="hsh-input-alt-tablet" data-preview="hsh-preview-alt-tablet"
                                    style="display: <?php echo $alt_logo_tablet ? 'inline' : 'none'; ?>;">Quitar</a>
                            </div>
                        </div>

                        <!-- Mobile Alt -->
                        <div class="hsh-logo-card" style="margin-bottom: 0;">
                            <div class="hsh-logo-card-header">
                                <span class="hsh-logo-card-title">Móvil (Opcional)</span>
                                <span class="hsh-inherited-pill hoo-inherited-badge alt-inherited" style="display: <?php echo $alt_mobile_is_inherited ? 'inline-block' : 'none'; ?>;">[Heredado]</span>
                            </div>
                            <div class="hsh-logo-preview-container <?php echo $alt_mobile_has_src ? 'has-image' : ''; ?> <?php echo $alt_mobile_is_inherited ? 'is-inherited' : ''; ?>">
                                <div class="hsh-logo-placeholder" style="display: <?php echo $alt_mobile_has_src ? 'none' : 'flex'; ?>;">
                                    <svg viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                                    </svg>
                                    <span>Sin logotipo</span>
                                </div>
                                <img id="hsh-preview-alt-mobile" class="hsh-logo-preview-img" src="<?php echo esc_url($alt_mobile_src); ?>"
                                    style="display: <?php echo $alt_mobile_has_src ? 'block' : 'none'; ?>; opacity: <?php echo $alt_mobile_opacity; ?>;">
                            </div>
                            <div class="hsh-logo-actions">
                                <input type="hidden" name="hooma_smart_header_settings[logo_switcher][alt_logo_mobile]" id="hsh-input-alt-mobile"
                                    value="<?php echo esc_attr($alt_logo_mobile); ?>">
                                <button type="button" class="button hsh-logo-upload-btn hsh-upload-button" data-target="hsh-input-alt-mobile"
                                    data-preview="hsh-preview-alt-mobile">Seleccionar Logo</button>
                                <a href="#" class="hsh-logo-remove-btn hsh-remove-button" data-target="hsh-input-alt-mobile" data-preview="hsh-preview-alt-mobile"
                                    style="display: <?php echo $alt_logo_mobile ? 'inline' : 'none'; ?>;">Quitar</a>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Device Settings -->
    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Ajustes por Dispositivos</h2>
        </div>
        <div class="hooma-sh-content">
            <?php 
                $use_custom_bp = _hsh_val($options, 'mobile.use_custom_breakpoints') === '1';
                $resp_link = '?page=hooma-smart-header&tab=responsive-behavior';
            ?>
            
            <?php if (!$use_custom_bp): ?>
                <div class="hsh-row">
                    <div class="notice notice-warning inline" style="margin: 0; width: 100%;">
                        <p>Para ajustar, es necesario habilitar los <strong>puntos de interrupción</strong> en <a href="<?php echo esc_url($resp_link); ?>">Responsive & Layout</a>.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="hsh-row <?php echo !$use_custom_bp ? 'hsh-disabled-section' : ''; ?>">
                <div class="hsh-row-label">
                    <strong>Desactivar en</strong>
                    <p class="description">Si se selecciona, el cambio de logo será desactivado en las resoluciones indicadas.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="hidden" name="hooma_smart_header_settings[logo_switcher][sentinel_flags]" value="1">
                    <div class="hsh-checkbox-group">
                        <label>
                            <input type="hidden" name="hooma_smart_header_settings[logo_switcher][disable_desktop]" value="0">
                            <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[logo_switcher][disable_desktop]" value="1"
                                <?php checked(_hsh_val($switcher, 'disable_desktop'), '1'); ?>
                                <?php disabled($use_custom_bp, false); ?>>
                            Desactivar en Desktop
                        </label>
                        <label>
                            <input type="hidden" name="hooma_smart_header_settings[logo_switcher][disable_tablet]" value="0">
                            <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[logo_switcher][disable_tablet]" value="1"
                                <?php checked(_hsh_val($switcher, 'disable_tablet'), '1'); ?>
                                <?php disabled($use_custom_bp, false); ?>>
                            Desactivar en Tablet
                        </label>
                        <label>
                            <input type="hidden" name="hooma_smart_header_settings[logo_switcher][disable_mobile]" value="0">
                            <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[logo_switcher][disable_mobile]" value="1"
                                <?php checked(_hsh_val($switcher, 'disable_mobile'), '1'); ?>
                                <?php disabled($use_custom_bp, false); ?>>
                            Desactivar en Mobile
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Display Conditions -->
    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Condiciones de visualización</h2>
            <p class="description">Determinan dónde se aplicará el cambio de logo.</p>
        </div>
        <div class="hooma-sh-content">
            <div class="hsh-row-content">
                <strong style="margin-bottom: 10px; display: block; font-size: 14px;">1. Modo de aplicación</strong>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="radio" name="hooma_smart_header_settings[logo_switcher][display_mode]" value="exclude" 
                            <?php checked(_hsh_val($switcher, 'display_mode', 'exclude'), 'exclude'); ?>>
                        Funcionar siempre, excepto en…
                    </label>
                    <label style="display: block;">
                        <input type="radio" name="hooma_smart_header_settings[logo_switcher][display_mode]" value="include" 
                            <?php checked(_hsh_val($switcher, 'display_mode'), 'include'); ?>>
                        Funcionar solo en…
                    </label>
                </div>

                <div class="hsh-sub-card">
                    <strong style="margin-bottom: 10px; display: block; font-size: 14px;">2. Tipos de contenido</strong>
                    <div class="hsh-checkbox-group" style="margin-bottom: 20px; background: #fff; padding: 10px; border: 1px solid #dcdde1; border-radius: 4px; max-height: 150px; overflow-y: auto;">
                        <?php 
                        $selected_types = _hsh_val($switcher, 'display_types', []);
                        $post_types = get_post_types(['public' => true], 'objects');
                        foreach ($post_types as $type): 
                            if ($type->name === 'attachment') continue;
                        ?>
                            <label style="margin-right: 15px;">
                                <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[logo_switcher][display_types][]" 
                                    value="<?php echo esc_attr($type->name); ?>" 
                                    <?php checked(in_array($type->name, $selected_types), true); ?>>
                                <?php echo esc_html($type->label); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <strong style="margin-bottom: 10px; display: block; font-size: 14px;">3. IDs de entradas o páginas</strong>
                    <input type="text" name="hooma_smart_header_settings[logo_switcher][display_ids]" 
                        value="<?php echo esc_attr(_hsh_val($switcher, 'display_ids')); ?>" 
                        class="regular-text" placeholder="Ej: 12, 45" style="width: 100%; margin-bottom: 20px;">

                    <strong style="margin-bottom: 10px; display: block; font-size: 14px;">4. Clase de la tag body</strong>
                    <input type="text" name="hooma_smart_header_settings[logo_switcher][display_body_classes]" 
                        value="<?php echo esc_attr(_hsh_val($switcher, 'display_body_classes')); ?>" 
                        class="regular-text" placeholder="class1, class2" style="width: 100%;">
                    <p class="description" style="margin-top: 5px;">Introduce las clases separadas por comas.</p>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <button type="submit" class="button button-primary button-large">Guardar Cambios</button>
        <div id="hsh-build-console" class="hsh-build-console"></div>
    </div>
</form>
<script>
    jQuery(document).ready(function ($) {
        var $checkbox = $('input[name="hooma_smart_header_settings[logo_switcher][enabled]"]');
        var $container = $('.hooma-sh-content');

        // Elements to disable (exclude the switcher itself)
        // We select inputs, selects, buttons not inside the switcher label
        // Actually, easiest is to target everything and then re-enable the switcher

        function toggleLogoSwitcher() {
            var isEnabled = $checkbox.is(':checked');

            // All inputs/buttons inside .hooma-sh-content
            var $allInputs = $container.find('input, select, textarea, button, a.hsh-remove-button, a.hsh-upload-button');

            // Exclude the checkbox itself
            var $targets = $allInputs.not($checkbox);

            if (isEnabled) {
                // Enable
                $targets.prop('disabled', false);
                $targets.css('opacity', '1');
                $targets.css('pointer-events', 'auto');
                // Ensure specific button styles if needed
                $('.hsh-disabled-overlay').remove();
            } else {
                // Disable
                $targets.prop('disabled', true);
                $targets.css('opacity', '0.5');
                $targets.css('pointer-events', 'none');
            }
        }

        // Init
        toggleLogoSwitcher();

        // Change
        $checkbox.on('change', toggleLogoSwitcher);
        // Detect Width Logic
        // Detect Width Logic
        $('#hsh-detect-width-btn').on('click', function () {
            var $btn = $(this);
            var $wrap = $btn.closest('.hooma-sh-verify-wrapper');
            var $input = $wrap.find('input');
            var $status = $wrap.find('.hooma-sh-status');

            var selector = $('input[name="hooma_smart_header_settings[selectors][logo]"]').val();
            var urlToVerify = (typeof HoomaSH !== 'undefined' && HoomaSH.home_url) ? HoomaSH.home_url : '/';

            if (!selector) {
                alert('Por favor define un selector primero.');
                return;
            }

            $btn.prop('disabled', true).text('Cargando...');
            $status.text('');

            // Create hidden iframe with desktop dimensions
            var $iframe = $('<iframe id="hsh-detect-frame"></iframe>').css({
                'position': 'fixed',
                'top': '-9999px',
                'left': '0',
                'width': '1280px',
                'height': '800px',
                'visibility': 'hidden',
                'z-index': '-1000'
            });
            $('body').append($iframe);

            $iframe.attr('src', urlToVerify);

            $iframe.on('load', function () {
                try {
                    var iframeWin = $iframe[0].contentWindow;
                    var iframeDoc = iframeWin.document;
                    var el = iframeDoc.querySelector(selector);

                    if (el) {
                        var rect = el.getBoundingClientRect();
                        var width = parseFloat(rect.width.toFixed(2));

                        if (width > 0) {
                            $input.val(width);
                            $status.css('color', 'green').text('Visualizado: ' + width + 'px');
                        } else {
                            $status.css('color', 'red').text('Ancho es 0');
                        }
                    } else {
                        $status.css('color', 'red').text('Selector no encontrado');
                    }

                } catch (e) {
                    console.error('HoomaSH Detect Error:', e);
                    $status.css('color', 'red').text('Error de acceso (Security/CORS)');
                }

                $btn.prop('disabled', false).text('Detectar');
                $iframe.remove();
            });

            // Timeout safety (10s)
            setTimeout(function () {
                if ($('#hsh-detect-frame').length) {
                    $btn.prop('disabled', false).text('Detectar');
                    $status.css('color', 'red').text('Timeout');
                    $('#hsh-detect-frame').remove();
                }
            }, 10000);
        });

    });
</script>