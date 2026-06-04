<?php

namespace HoomaModules\HoomaSmartHeader\Controllers\Admin;

class AdminController
{
    private $slug;
    private $version;
    private $url;
    private $option_group = 'hooma_smart_header_group';
    private $option_name = 'hooma_smart_header_settings';

    public function __construct($slug, $version, $url)
    {
        $this->slug = $slug;
        $this->version = $version;
        $this->url = $url;
    }

    public function init()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Hooma SH AdminController: init called. Slug: ' . $this->slug);
        }

        // Critical Fix: check if admin_init already passed
        if (did_action('admin_init')) {
            $this->register_settings();
        } else {
            add_action('admin_init', [$this, 'register_settings']);
        }

        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // Initialize Sub-Controllers (Ajax Handlers)
        $general_settings = new GeneralSettings();
        $general_settings->init();

        $build_controller = new BuildController();
        $build_controller->init();

        // AJAX Save Settings
        add_action('wp_ajax_hsh_save_settings', [$this, 'handle_ajax_save']);
    }

    public function register_settings()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Hooma SH AdminController: register_settings executing.');
        }
        register_setting($this->option_group, $this->option_name, [
            'sanitize_callback' => [$this, 'sanitize_settings']
        ]);
    }

    public function sanitize_settings($input)
    {
        $existing = get_option($this->option_name);
        if (!is_array($existing)) {
            $existing = [];
        }

        // Use sentinels or key presence to know which tab was submitted
        $keys = ['selectors', 'behavior', 'mobile', 'debug', 'layout', 'scroll_behavior', 'logo_switcher'];

        foreach ($keys as $key) {
            if (isset($input[$key])) {
                $new_data = $input[$key];

                // Rule: If a section is submitted, certain keys that disappear (unselected checkboxes) 
                // must be explicitly set to empty to override existing data.

                // 1. Initial Behavior (behavior)
                if ($key === 'behavior') {
                    if (isset($new_data['sentinel'])) {
                        if (!isset($new_data['display_types'])) {
                            $new_data['display_types'] = [];
                        }
                        if (!isset($new_data['hide_on_scroll'])) {
                            $new_data['hide_on_scroll'] = '0';
                        }
                        if (!isset($new_data['show_once'])) {
                            $new_data['show_once'] = '0';
                        }
                        unset($new_data['sentinel']);
                    }
                    
                    if (isset($new_data['sentinel_flags'])) {
                        if (!isset($new_data['mobile'])) {
                            $new_data['mobile'] = ['disable_mobile' => '0', 'disable_tablet' => '0', 'disable_desktop' => '0'];
                        } else {
                            if (!isset($new_data['mobile']['disable_mobile'])) $new_data['mobile']['disable_mobile'] = '0';
                            if (!isset($new_data['mobile']['disable_tablet'])) $new_data['mobile']['disable_tablet'] = '0';
                            if (!isset($new_data['mobile']['disable_desktop'])) $new_data['mobile']['disable_desktop'] = '0';
                        }
                        unset($new_data['sentinel_flags']);
                    }
                }

                // 2. Mobile (responsive & layout)
                if ($key === 'mobile') {
                    // ... (existing)
                    // Context 1: Breakpoints (from Responsive Behavior)
                    if (isset($new_data['sentinel_breakpoints'])) {
                        if (!isset($new_data['use_custom_breakpoints'])) {
                            $new_data['use_custom_breakpoints'] = '0';
                        }
                        unset($new_data['sentinel_breakpoints']);
                    }
                }

                // 3. Layout
                if ($key === 'layout') {
                    if (!isset($new_data['placeholder'])) {
                        $new_data['placeholder'] = '0';
                    }

                    // Enforce mutual exclusion between placeholder and negative margin target (pull up)
                    if ($new_data['placeholder'] === '1') {
                        $new_data['target'] = '';
                    }

                    // Pull Up Device Flags
                    if (!isset($new_data['pull_up_disable_desktop']))
                        $new_data['pull_up_disable_desktop'] = '0';
                    if (!isset($new_data['pull_up_disable_tablet']))
                        $new_data['pull_up_disable_tablet'] = '0';
                    if (!isset($new_data['pull_up_disable_mobile']))
                        $new_data['pull_up_disable_mobile'] = '0';

                    // Pull Up Display Conditions
                    if (isset($new_data['pull_up_display_mode'])) {
                        if (!isset($new_data['pull_up_display_types'])) {
                            $new_data['pull_up_display_types'] = [];
                        }
                    }
                }

                // 4. Scroll Behavior
                if ($key === 'scroll_behavior') {
                    if (!isset($new_data['enabled'])) {
                        $new_data['enabled'] = '0';
                    }
                    if (!isset($new_data['display_types'])) {
                        $new_data['display_types'] = [];
                    }
                    if (!isset($new_data['mobile'])) {
                        $mobile_defaults = ['disable_mobile' => '0', 'disable_tablet' => '0', 'disable_desktop' => '0'];
                        $new_data['mobile'] = $mobile_defaults;
                    } else {
                        $defaults = ['disable_mobile' => '0', 'disable_tablet' => '0', 'disable_desktop' => '0'];
                        foreach ($defaults as $k => $v) {
                            if (!isset($new_data['mobile'][$k]))
                                $new_data['mobile'][$k] = $v;
                        }
                    }
                }

                // 5. Logo Switcher
                if ($key === 'logo_switcher') {
                    if (isset($new_data['sentinel_logo'])) {
                        if (!isset($new_data['enabled'])) {
                            $new_data['enabled'] = '0';
                        }
                        unset($new_data['sentinel_logo']);
                    }

                    // Device Flags
                    if (isset($new_data['sentinel_flags'])) {
                        if (!isset($new_data['disable_mobile']))
                            $new_data['disable_mobile'] = '0';
                        if (!isset($new_data['disable_tablet']))
                            $new_data['disable_tablet'] = '0';
                        if (!isset($new_data['disable_desktop']))
                            $new_data['disable_desktop'] = '0';
                        unset($new_data['sentinel_flags']);
                    }

                    // Display Types (if sentinel or just ensure array?)
                    // If display_mode is set, we can assume this block was sent.
                    if (isset($new_data['display_mode'])) {
                        if (!isset($new_data['display_types'])) {
                            $new_data['display_types'] = []; // Clear if none selected
                        }
                    }
                }

                $existing[$key] = $this->smart_merge(isset($existing[$key]) ? $existing[$key] : [], $new_data);
            }
        }

        // Recursive Sanitization (Basic)
        $existing = $this->sanitize_recursive($existing);

        // Invalidar la caché de altura dinámica (cached_height) para forzar re-cálculo en frontend
        if (isset($existing['layout']['cached_height'])) {
            unset($existing['layout']['cached_height']);
        }

        // Registrar timestamp de último guardado administrativo
        $existing['last_saved'] = time();

        // Calentar la caché de logotipos con la nueva configuración
        $preloader = new \HoomaModules\HoomaSmartHeader\Controllers\Frontend\LogoPreloader();
        $preloader->warm_cache($existing);

        return $existing;
    }

    public function handle_ajax_save()
    {
        check_ajax_referer('hooma_sh_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos suficientes.');
        }

        // The form data comes as a query string or JSON, but usually parsed into $_POST
        if (!isset($_POST['settings'])) {
            wp_send_json_error('No se recibieron configuraciones.');
        }

        // Parse the serialized form data if necessary, or just use $_POST
        // In our case, we will send it as a structured object or serialized string.
        // Let's assume the JS sends the serialized form as 'settings'
        parse_str($_POST['settings'], $form_data);

        if (!isset($form_data[$this->option_name])) {
            wp_send_json_error('Formato de datos no válido.');
        }

        $input = $form_data[$this->option_name];
        
        // Use existing sanitize logic
        $sanitized = $this->sanitize_settings($input);
        
        // Update option
        $updated = update_option($this->option_name, $sanitized);

        wp_send_json_success([
            'message' => 'Configuración guardada correctamente.',
            'updated' => $updated
        ]);
    }

    private function smart_merge($existing, $new)
    {
        if (!is_array($new)) {
            return $new;
        }

        if (array_keys($new) === range(0, count($new) - 1)) {
            return $new;
        }

        if (empty($new) && !empty($existing) && array_keys($existing) === range(0, count($existing) - 1)) {
            return $new;
        }

        if (!is_array($existing)) {
            $existing = [];
        }

        foreach ($new as $key => $value) {
            if (isset($existing[$key]) && is_array($existing[$key]) && is_array($value)) {
                $existing[$key] = $this->smart_merge($existing[$key], $value);
            } else {
                $existing[$key] = $value;
            }
        }

        return $existing;
    }

    private function sanitize_recursive($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize_recursive'], $data);
        }
        return is_string($data) ? sanitize_text_field($data) : $data;
    }

    public function enqueue_assets($hook)
    {
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'settings';

        wp_enqueue_style('hooma-sh-admin', $this->url . '/assets/css/admin-smart-header.css', [], $this->version);

        // General JS (Settings & Shared verify/restore)
        wp_enqueue_script('hooma-sh-admin-general', $this->url . '/assets/js/admin-general.js', ['jquery'], $this->version, true);

        // Modules
        if ($active_tab === 'initial-behavior' || $active_tab === 'desktop-behavior') {
            wp_enqueue_script('hooma-sh-admin-initial', $this->url . '/assets/js/admin-initial-behavior.js', ['jquery'], $this->version, true);
            wp_enqueue_script('hooma-sh-admin-conditions', $this->url . '/assets/js/admin-conditions.js', ['jquery'], $this->version, true);
        }

        if ($active_tab === 'scroll-behavior') {
            wp_enqueue_script('hooma-sh-admin-conditions', $this->url . '/assets/js/admin-conditions.js', ['jquery'], $this->version, true);
        }

        if ($active_tab === 'responsive-behavior') {
            wp_enqueue_script('hooma-sh-admin-responsive', $this->url . '/assets/js/admin-responsive-behavior.js', ['jquery'], $this->version, true);
        }

        if ($active_tab === 'logo-switcher') {
            wp_enqueue_script('hooma-sh-admin-logo', $this->url . '/assets/js/admin-logo-switcher.js', ['jquery'], $this->version, true);
        }

        if ($active_tab === 'build') {
            wp_enqueue_script('hooma-sh-admin-build', $this->url . '/assets/js/admin-build.js', ['jquery'], $this->version, true);
        }

        // Localize General (renamed handle)
        wp_localize_script('hooma-sh-admin-general', 'HoomaSH', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hooma_sh_nonce'),
            'home_url' => home_url(),
            'strings' => [
                'verifying' => 'Verificando...',
                'found' => 'Selector encontrado',
                'not_found' => 'Selector no encontrado',
                'restoring' => 'Restaurando...',
            ]
        ]);
    }
}
