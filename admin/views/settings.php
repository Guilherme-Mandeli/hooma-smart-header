<?php
defined('HOOMA_PATH') || exit;

// Retrieve existing options
$options = get_option('hooma_smart_header_settings');

// Debugging
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Hooma SH Settings: Loaded.');
}

$defaults = [
    'header' => 'header',
    'sticky' => '',
    'logo' => ''
];

try {
    if (class_exists('\\HoomaModules\\HoomaSmartHeader\\Services\\ThemeDetector')) {
        $defaults = \HoomaModules\HoomaSmartHeader\Services\ThemeDetector::detect();
    } else {
        if (isset($module_namespace)) {
            $dyn_class = $module_namespace . "\\Services\\ThemeDetector";
            if (class_exists($dyn_class)) {
                $defaults = $dyn_class::detect();
            }
        }
    }
} catch (\Exception $e) {
    error_log('Hooma SH Exception: ' . $e->getMessage());
} catch (\Error $e) {
    error_log('Hooma SH Fatal Error: ' . $e->getMessage());
}

if (false === $options) {
    $options = ['selectors' => $defaults];
}

// Ensure variable existence - Dynamic detection from index.php
if (!isset($module_version) || empty($module_version)) {
    $module_index_path = dirname(dirname(__DIR__)) . '/index.php';
    if (file_exists($module_index_path)) {
        $file_data = get_file_data($module_index_path, ['Version' => 'Version']);
        $module_version = !empty($file_data['Version']) ? $file_data['Version'] : '1.0.0';
    } else {
        $module_version = '1.0.0';
    }
}

// Helper to get value
if (!function_exists('_hsh_val')) {
    function _hsh_val($options, $key, $default = '')
    {
        $keys = explode('.', $key);
        $val = $options;
        foreach ($keys as $k) {
            if (isset($val[$k])) {
                $val = $val[$k];
            } else {
                return $default;
            }
        }
        return $val;
    }
}
?>

<div class="wrap hooma-sh-wrap">
    <h1>Smart Header Control</h1>

    <form method="post" action="options.php" id="hooma-sh-form">
        <?php settings_fields('hooma_smart_header_group'); ?>

        <!-- Sentinel to ensure 'selectors' key is sent -->
        <input type="hidden" name="hooma_smart_header_settings[selectors][sentinel]" value="1">

        <!-- Selectors -->
        <div class="hooma-sh-section">
            <div class="hooma-sh-section-header">
                <h2>Selectores Principales</h2>
                <p class="description">Configura el selector del header principal de tu sitio.</p>
            </div>
            <div class="hooma-sh-content">
                <div class="hsh-row">
                    <div class="hsh-row-label">
                        <strong>Header Selector</strong>
                        <p class="description">Ej: <code>header</code>, <code>#main-header</code></p>
                    </div>
                    <div class="hsh-row-control">
                        <div class="hooma-sh-verify-wrapper">
                            <input type="text" class="regular-text"
                                name="hooma_smart_header_settings[selectors][header]"
                                value="<?php echo esc_attr(_hsh_val($options, 'selectors.header')); ?>"
                                placeholder="<?php echo esc_attr($defaults['header']); ?>">
                            <button type="button" class="button hooma-sh-verify-btn">Verificar</button>
                            <span class="hooma-sh-status"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Transition Name -->
        <div class="hooma-sh-section">
            <div class="hooma-sh-section-header">
                <h2>View Transition Name</h2>
                <p class="description">Nombre de la transición de vista que se aplicará al header para animaciones entre páginas (@view-transition).</p>
            </div>
            <div class="hooma-sh-content">
                <div class="hsh-row">
                    <div class="hsh-row-label">
                        <strong>Nombre de transición</strong>
                    </div>
                    <div class="hsh-row-control">
                        <input type="text" class="regular-text"
                            name="hooma_smart_header_settings[selectors][view_transition_name]"
                            value="<?php echo esc_attr(_hsh_val($options, 'selectors.view_transition_name', 'header')); ?>"
                            placeholder="header">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tools & Debug -->
        <div class="hooma-sh-section">
            <div class="hooma-sh-section-header">
                <h2>Herramientas</h2>
            </div>
            <div class="hooma-sh-content">
                <div class="hsh-row">
                    <div class="hsh-row-label">
                        <strong>URL para verificación</strong>
                        <p class="description">URL que se usará para comprobar si los selectores existen.</p>
                    </div>
                    <div class="hsh-row-control">
                        <input type="text" id="hsh-verify-url" class="regular-text" placeholder="<?php echo home_url(); ?>"
                            value="<?php echo home_url(); ?>">
                    </div>
                </div>
                <div class="hsh-row">
                    <div class="hsh-row-label">
                        <strong>Restaurar Detección Automática</strong>
                    </div>
                    <div class="hsh-row-control">
                        <button type="button" class="button" id="hsh-restore-defaults">Restaurar Valores</button>
                    </div>
                </div>
            </div>
        </div>

        <?php submit_button(); ?>
        <div id="hsh-build-console" class="hsh-build-console"></div>
    </form>

    <!-- Info -->
    <div style="margin-top: 30px; border-top: 1px solid #dcdde1; padding-top: 20px; color: #646970; font-size: 13px;">
        <span style="margin-right: 20px;"><strong>Versión:</strong> <?php echo esc_html($module_version); ?></span>
        <span><strong>Tema Detectado:</strong> <?php echo esc_html(wp_get_theme()->get('Name')); ?></span>
    </div>
</div>