<?php

namespace HoomaModules\HoomaSmartHeader\Controllers\Frontend;

use HoomaModules\HoomaSmartHeader\Services\ConditionEvaluator;

class FrontendController
{
    private $slug;
    private $version;
    private $url;
    private $option_name = 'hooma_smart_header_settings';

    public function __construct($slug, $version, $url)
    {
        $this->slug = $slug;
        $this->version = $version;
        $this->url = $url;
    }

    public function init()
    {
        // Enqueue Assets (Global)
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Universal Module Filter (Registered earlier for reliability)
        add_filter('script_loader_tag', [$this, 'add_module_type'], 10, 3);

        // Initialize Sub-Controllers (Sectors)
        $logo_switcher = new LogoSwitcher();
        $logo_switcher->init();

        $initial_behavior = new InitialBehavior();
        $initial_behavior->init();

        $layout_responsive = new LayoutResponsive();
        $layout_responsive->init();

        $logo_preloader = new LogoPreloader();
        $logo_preloader->init();
    }

    public function enqueue_assets()
    {
        $options = get_option($this->option_name);
        $last_saved = isset($options['last_saved']) ? $options['last_saved'] : $this->version;
        $version = $this->version . '.' . $last_saved;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (isset($options['debug']) && isset($options['behavior']['debug']) && $options['behavior']['debug'] === '1') {
                error_log('Hooma SH Debug: enqueue_assets running.');
            }
        }

        wp_enqueue_style('hooma-smart-header', $this->url . '/assets/css/hooma-smart-header.css', [], $version);

        // El archivo .min.js siempre existe:
        // - Si la build fue exitosa: contiene el bundle real.
        // - Si la build falló (fallback): contiene un stub que importa los módulos individuales.
        // En ningún caso se produce un 404.
        $bundle_path = dirname(dirname(dirname(__DIR__))) . '/assets/js/dist/hooma-smart-header.min.js';
        $bundle_url  = $this->url . '/assets/js/dist/hooma-smart-header.min.js';

        // Protección "cold-start": si nunca se ha ejecutado la build ni el fallback,
        // el archivo puede no existir. Lo creamos aquí directamente para evitar el 404.
        if (!file_exists($bundle_path)) {
            $dist_dir = dirname($bundle_path);
            if (!is_dir($dist_dir)) {
                @mkdir($dist_dir, 0755, true);
            }
            $stub  = "// Hooma Smart Header — Fallback Loader (build no disponible)\n";
            $stub .= "import '../hooma-smart-header.js';\n";
            @file_put_contents($bundle_path, $stub);
        }

        wp_enqueue_script('hooma-smart-header', $bundle_url, [], $version, true);

        // Pre-calculate conditions
        // ... (rest of the logic remains the same)
        $run_behavior = ConditionEvaluator::check(isset($options['behavior']) ? $options['behavior'] : []);
        $run_scroll_behavior = ConditionEvaluator::check(isset($options['scroll_behavior']) ? $options['scroll_behavior'] : []);

        // run_logo_switcher = Logic for Logo Switcher (Display Conditions)
        $logo_opts = isset($options['logo_switcher']) ? $options['logo_switcher'] : [];
        $run_logo_switcher = ConditionEvaluator::check($logo_opts);

        // run_pull_up = Logic for Negative Margin (Display Conditions)
        $layout_opts = isset($options['layout']) ? $options['layout'] : [];
        $run_pull_up = ConditionEvaluator::check([
            'display_mode' => isset($layout_opts['pull_up_display_mode']) ? $layout_opts['pull_up_display_mode'] : 'exclude',
            'display_types' => isset($layout_opts['pull_up_display_types']) ? $layout_opts['pull_up_display_types'] : [],
            'display_ids' => isset($layout_opts['pull_up_display_ids']) ? $layout_opts['pull_up_display_ids'] : '',
            'display_body_classes' => isset($layout_opts['pull_up_display_body_classes']) ? $layout_opts['pull_up_display_body_classes'] : ''
        ]);

        $vars = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hooma_sh_nonce'),
            'selectors' => isset($options['selectors']) ? $options['selectors'] : [],
            'behavior' => isset($options['behavior']) ? $options['behavior'] : [],
            'mobile' => isset($options['mobile']) ? $options['mobile'] : [],
            'layout' => isset($options['layout']) ? $options['layout'] : [],
            'scroll_behavior' => isset($options['scroll_behavior']) ? $options['scroll_behavior'] : [],
            'logo_switcher' => isset($options['logo_switcher']) ? $options['logo_switcher'] : [],
            'debug' => (isset($options['debug']) && $options['debug'] === '1'),
            'last_saved' => $last_saved,
            'run_behavior' => $run_behavior,
            'run_scroll_behavior' => $run_scroll_behavior,
            'run_logo_switcher' => $run_logo_switcher,
            'run_pull_up' => $run_pull_up
        ];

        wp_localize_script('hooma-smart-header', 'HoomaSHConfig', $vars);
    }

    public function add_module_type($tag, $handle, $src)
    {
        // Only target our main script
        if ($handle === 'hooma-smart-header') {
            // Ensure it's treated as a module (harmless for IIFE bundles, required for fallback native modules)
            if (strpos($tag, 'type="module"') === false) {
                $tag = str_replace('<script ', '<script type="module" ', $tag);
            }
            
            // Prevent caching plugins (WP Rocket, Autoptimize, SG Optimizer, LiteSpeed)
            // from moving this script to a cache directory or combining it.
            // If moved, relative imports (e.g., import from './hooma-helpers.js') will 404 
            // and type="module" might be stripped, causing the "Cannot use import statement outside a module" error.
            if (strpos($tag, 'data-noptimize') === false) {
                $tag = str_replace('<script ', '<script data-no-optimize="1" data-no-minify="1" data-noptimize="1" ', $tag);
            }
        }
        return $tag;
    }

}
