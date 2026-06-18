<?php

namespace HoomaModules\HoomaSmartHeader\Controllers\Frontend;

use HoomaModules\HoomaSmartHeader\Services\ConditionEvaluator;

class LayoutResponsive
{
    private $option_name = 'hooma_smart_header_settings';

    public function init()
    {
        add_filter('body_class', [$this, 'add_body_classes']);
        add_action('wp_head', [$this, 'output_critical_css'], 1);
        add_action('wp_body_open', [$this, 'inject_placeholder_div']);
        add_action('template_redirect', [$this, 'start_buffer'], 1);
    }

    public function add_body_classes($classes)
    {
        $options = get_option($this->option_name);
        $layout = isset($options['layout']) ? $options['layout'] : [];

        $has_placeholder = isset($layout['placeholder']) && $layout['placeholder'] === '1';

        // Check if Force Fixed is active globally or on any device
        $use_custom_bp = isset($options['mobile']['use_custom_breakpoints']) && $options['mobile']['use_custom_breakpoints'] === '1';
        $force_fixed = false;
        if ($use_custom_bp) {
            $force_fixed = (isset($layout['force_fixed_mobile']) && $layout['force_fixed_mobile'] === '1') ||
                           (isset($layout['force_fixed_tablet']) && $layout['force_fixed_tablet'] === '1') ||
                           (isset($layout['force_fixed_desktop']) && $layout['force_fixed_desktop'] === '1');
        } else {
            $force_fixed = isset($layout['force_fixed_global']) && $layout['force_fixed_global'] === '1';
        }

        if ($has_placeholder || $force_fixed) {
            $classes[] = 'hoo-sh-compensation'; // Master class for layout fixes
        }

        if ($has_placeholder) {
            $classes[] = 'hooma-sh-has-placeholder';
        }

        // Force Fixed Classes
        if ($use_custom_bp) {
            if (isset($layout['force_fixed_mobile']) && $layout['force_fixed_mobile'] === '1')
                $classes[] = 'force-header-fixed-mobile';
            if (isset($layout['force_fixed_tablet']) && $layout['force_fixed_tablet'] === '1')
                $classes[] = 'force-header-fixed-tablet';
            if (isset($layout['force_fixed_desktop']) && $layout['force_fixed_desktop'] === '1')
                $classes[] = 'force-header-fixed-desktop';
        } else {
            if (isset($layout['force_fixed_global']) && $layout['force_fixed_global'] === '1')
                $classes[] = 'force-header-fixed-global';
        }

        return $classes;
    }

    public function start_buffer()
    {
        if (is_admin() || wp_doing_ajax() || wp_is_json_request()) {
            return;
        }
        ob_start([$this, 'inject_view_transition_attribute']);
    }

    public function inject_view_transition_attribute($html)
    {
        if (empty($html)) {
            return $html;
        }

        $options = get_option($this->option_name);
        $view_transition_name = isset($options['selectors']['view_transition_name']) ? trim($options['selectors']['view_transition_name']) : '';
        $header_selector = isset($options['selectors']['header']) ? trim($options['selectors']['header']) : '';

        if (empty($view_transition_name) || empty($header_selector)) {
            return $html;
        }

        $pattern = '';
        if (strpos($header_selector, '#') === 0) {
            $id = substr($header_selector, 1);
            $pattern = '/(<[^>]+id=[\'"]' . preg_quote($id, '/') . '[\'"][^>]*>)/i';
        } elseif (strpos($header_selector, '.') === 0) {
            $class = substr($header_selector, 1);
            $pattern = '/(<[^>]+class=[\'"][^\'"]*\b' . preg_quote($class, '/') . '\b[^\'"]*[\'"][^>]*>)/i';
        } elseif (preg_match('/^[a-zA-Z0-9_-]+$/', $header_selector)) {
            $pattern = '/(<' . preg_quote($header_selector, '/') . '\b[^>]*>)/i';
        }

        if (empty($pattern)) {
            return $html;
        }

        $html = preg_replace_callback($pattern, function($matches) use ($view_transition_name) {
            $tag = $matches[1];
            
            if (stripos($tag, 'view-transition-name=') !== false) {
                return $tag;
            }

            $injected = ' view-transition-name="' . esc_attr($view_transition_name) . '"';

            return preg_replace('/^(<\w+)(\s|>)/i', '$1' . $injected . '$2', $tag);
        }, $html, 1);

        return $html;
    }

    public function inject_placeholder_div()
    {
        $options = get_option($this->option_name);
        if (isset($options['layout']['placeholder']) && $options['layout']['placeholder'] === '1') {
            echo '<!-- Hooma Smart Header Placeholder -->';
            echo '<div id="hoo-sh-placeholder" style="display:none;"></div>';
            
            // Inline critical script to sync height as early as possible
            $header_selector = isset($options['selectors']['header']) ? $options['selectors']['header'] : 'header';
            ?>
            <script id="hooma-sh-early-sync">
                (function() {
                    var selector = '<?php echo esc_js($header_selector); ?>';
                    var observer = new MutationObserver(function(mutations, obs) {
                        var h = document.querySelector(selector);
                        if (h) {
                            var height = h.offsetHeight;
                            if (height > 0) {
                                document.documentElement.style.setProperty('--hoo-header-height', height + 'px');
                                document.documentElement.style.setProperty('--hoo-dynamic-header-height', height + 'px');
                                obs.disconnect();
                            }
                        }
                    });
                    observer.observe(document.documentElement, { childList: true, subtree: true });
                })();
            </script>
            <?php
        }
    }

    public function output_critical_css()
    {
        $options = get_option($this->option_name);
        $css = '';

        // --- Configuration & Selectors ---
        $header_selector = isset($options['selectors']['header']) ? $options['selectors']['header'] : 'header';
        $use_custom_bp = isset($options['mobile']['use_custom_breakpoints']) && $options['mobile']['use_custom_breakpoints'] === '1';
        $bp_mobile = isset($options['mobile']['breakpoint']) ? intval($options['mobile']['breakpoint']) : 767;
        $bp_tablet = isset($options['mobile']['tablet_breakpoint']) ? intval($options['mobile']['tablet_breakpoint']) : 980;

        $admin_bar_offset = is_admin_bar_showing() ? 32 : 0;
        $admin_bar_mobile_offset = is_admin_bar_showing() ? 46 : 0; // WP changes admin bar to 46px under 782px

        $backup_offset_desk = isset($options['layout']['backup_offset_height']) && $options['layout']['backup_offset_height'] !== '' ? floatval($options['layout']['backup_offset_height']) : 0;
        $backup_height_desk = isset($options['layout']['backup_height']) && $options['layout']['backup_height'] !== '' ? floatval($options['layout']['backup_height']) : 0;

        $backup_offset_tablet = isset($options['layout']['backup_offset_height_tablet']) && $options['layout']['backup_offset_height_tablet'] !== '' ? floatval($options['layout']['backup_offset_height_tablet']) : $backup_offset_desk;
        $backup_height_tablet = isset($options['layout']['backup_height_tablet']) && $options['layout']['backup_height_tablet'] !== '' ? floatval($options['layout']['backup_height_tablet']) : $backup_height_desk;

        $backup_offset_mobile = isset($options['layout']['backup_offset_height_mobile']) && $options['layout']['backup_offset_height_mobile'] !== '' ? floatval($options['layout']['backup_offset_height_mobile']) : $backup_offset_tablet;
        $backup_height_mobile = isset($options['layout']['backup_height_mobile']) && $options['layout']['backup_height_mobile'] !== '' ? floatval($options['layout']['backup_height_mobile']) : $backup_height_tablet;


        // Force Fixed Logic Base with Modern Performance Optimizations & Morphing Support
        $force_fixed_css = "
            position: fixed !important; 
            top: var(--hoo-header-top, 0px) !important; 
            width: 100% !important; 
            z-index: 99999 !important;
            content-visibility: visible;
            will-change: transform, opacity;
            transform: translateY(var(--hoo-sh-scroll-y, 0px)) !important;
            transition: transform var(--hoo-layout-transition, 400ms ease-in-out), opacity 0.4s ease, height var(--hoo-layout-transition, 400ms ease-in-out), margin-top var(--hoo-layout-transition, 400ms ease-in-out), top var(--hoo-layout-transition, 400ms ease-in-out), -webkit-backdrop-filter var(--hoo-layout-transition, 400ms ease-in-out), backdrop-filter var(--hoo-layout-transition, 400ms ease-in-out) !important;
        ";

        // --- Block 2: Conditional Fixation (via Body Class) ---
        if ($use_custom_bp) {
            if (isset($options['layout']['force_fixed_mobile']) && $options['layout']['force_fixed_mobile'] === '1') {
                $css .= "@media (max-width: {$bp_mobile}px) { body.hoo-sh-compensation {$header_selector} { {$force_fixed_css} } }";
            }
            if (isset($options['layout']['force_fixed_tablet']) && $options['layout']['force_fixed_tablet'] === '1') {
                $min = $bp_mobile + 1;
                $css .= "@media (min-width: {$min}px) and (max-width: {$bp_tablet}px) { body.hoo-sh-compensation {$header_selector} { {$force_fixed_css} } }";
            }
            if (isset($options['layout']['force_fixed_desktop']) && $options['layout']['force_fixed_desktop'] === '1') {
                $min = $bp_tablet + 1;
                $css .= "@media (min-width: {$min}px) { body.hoo-sh-compensation {$header_selector} { {$force_fixed_css} } }";
            }
        } else {
            if (isset($options['layout']['force_fixed_global']) && $options['layout']['force_fixed_global'] === '1') {
                $css .= "body.hoo-sh-compensation {$header_selector} { {$force_fixed_css} }";
            }
        }

        // --- Placeholder & Variables ---
        $active_placeholder = isset($options['layout']['placeholder']) && $options['layout']['placeholder'] === '1';
        $run_initial = ConditionEvaluator::check(isset($options['behavior']) ? $options['behavior'] : []);
        if (!$run_initial) $active_placeholder = false;

        $target_selector = isset($options['layout']['target']) ? trim($options['layout']['target']) : '';

        // Determine Effective Height (PHP-First)
        $final_height_desk = 0; $final_height_tablet = 0; $final_height_mobile = 0;
        $height_mode = isset($options['layout']['height_mode']) ? $options['layout']['height_mode'] : 'auto';

        if ($height_mode === 'manual') {
            $manual_val = isset($options['layout']['height_val']) ? floatval($options['layout']['height_val']) : 0;
            $final_height_desk = $manual_val;
            $final_height_tablet = $manual_val;
            $final_height_mobile = $manual_val;
        } else {
            // Versioned cookie names to prevent caching obsolete values after option saves
            $last_saved = isset($options['last_saved']) ? $options['last_saved'] : '0';
            $cookie_name_desk = 'hsh_cached_h_desk_' . $last_saved;
            $cookie_name_tablet = 'hsh_cached_h_tab_' . $last_saved;
            $cookie_name_mobile = 'hsh_cached_h_mob_' . $last_saved;

            $cookie_height_desk = isset($_COOKIE[$cookie_name_desk]) ? floatval($_COOKIE[$cookie_name_desk]) : 0;
            $cookie_height_tablet = isset($_COOKIE[$cookie_name_tablet]) ? floatval($_COOKIE[$cookie_name_tablet]) : 0;
            $cookie_height_mobile = isset($_COOKIE[$cookie_name_mobile]) ? floatval($_COOKIE[$cookie_name_mobile]) : 0;

            // Apply cookie value or fall back to device-specific backup height independently
            $final_height_desk = ($cookie_height_desk > 0 && $cookie_height_desk <= 1500) ? $cookie_height_desk : $backup_height_desk;
            $final_height_tablet = ($cookie_height_tablet > 0 && $cookie_height_tablet <= 1500) ? $cookie_height_tablet : $backup_height_tablet;
            $final_height_mobile = ($cookie_height_mobile > 0 && $cookie_height_mobile <= 1500) ? $cookie_height_mobile : $backup_height_mobile;
        }

        $total_placeholder_height_desk = $final_height_desk + $backup_offset_desk;

        // Helper function for CSS generation
        $gen_vars = function($f_height, $b_offset, $admin_offset) {
            $total = $f_height + $b_offset;
            $top = $admin_offset + $b_offset;
            $max_h = $b_offset > 0 ? "{$b_offset}px" : "none";
            $over = $b_offset > 0 ? "hidden" : "visible";
            $pull = -1 * $f_height;
            return "
                --hoo-header-height: {$total}px;
                --hoo-dynamic-header-height: {$total}px;
                --hoo-header-top: {$top}px;
                --hoo-header-top-base: {$admin_offset}px;
                --hoo-top-header-max-height: {$max_h};
                --hoo-top-header-overflow: {$over};
                --hoo-pull-up-mt: {$pull}px;
            ";
        };

        $css .= ":root {\n";
        $css .= $gen_vars($final_height_desk, $backup_offset_desk, $admin_bar_offset);
        $css .= "    --hoo-sh-scroll-y: 0px;\n";
        $css .= "    --hoo-top-header-mt: 0px;\n";
        $css .= "    --hoo-main-header-mt: 0px;\n";
        $css .= "}\n";

        // WP changes admin bar to 46px under 782px
        if ($admin_bar_offset > 0) {
            $css .= "@media screen and (max-width: 782px) { :root { " . $gen_vars($final_height_mobile, $backup_offset_mobile, $admin_bar_mobile_offset) . " } }\n";
        }

        // Custom Breakpoints overrides
        $min_tablet = $bp_mobile + 1;
        $css .= "@media (max-width: {$bp_mobile}px) { :root { " . $gen_vars($final_height_mobile, $backup_offset_mobile, ($admin_bar_offset > 0 && $bp_mobile <= 782 ? $admin_bar_mobile_offset : $admin_bar_offset)) . " } }\n";
        $css .= "@media (min-width: {$min_tablet}px) and (max-width: {$bp_tablet}px) { :root { " . $gen_vars($final_height_tablet, $backup_offset_tablet, ($admin_bar_offset > 0 && $bp_tablet <= 782 ? $admin_bar_mobile_offset : $admin_bar_offset)) . " } }\n";


        $view_transition_name = isset($options['selectors']['view_transition_name']) ? trim($options['selectors']['view_transition_name']) : '';
        if (!empty($view_transition_name)) {
            $css .= "\n{$header_selector} {\n";
            $css .= "    view-transition-name: {$view_transition_name};\n";
            $css .= "}\n";
            $css .= "\n::view-transition-group( {$view_transition_name} ) {\n";
            $css .= "    animation-duration: 0.4s;\n";
            $css .= "    animation-timing-function: ease-in-out;\n";
            $css .= "}\n";
        }

        // Defensive styles for Top Header (Fixed and Full Width) - Injected as early as possible
        $css .= "
            #top-header {
                position: fixed !important;
                top: var(--hoo-header-top-base, 0px) !important;
                width: 100% !important;
                z-index: 99998 !important;
                left: 0 !important;
                right: 0 !important;
                margin-top: var(--hoo-top-header-mt, 0px) !important;
                max-height: var(--hoo-top-header-max-height) !important;
                overflow: var(--hoo-top-header-overflow) !important;
                -webkit-transition: margin-top var(--hoo-layout-transition, 400ms ease-in-out) !important;
                transition: margin-top var(--hoo-layout-transition, 400ms ease-in-out) !important;
                will-change: margin-top;
            }
        ";

        // Safety rule: Disable transitions while a View Transition is active to allow the API to capture the target snapshot instantly.
        $css .= "\n:root:active-view-transition {$header_selector} { transition: none !important; }\n";

        if ($active_placeholder) {
            // Coupled styles: Only take effect if the body class is present
            $css .= 'body.hoo-sh-compensation #hoo-sh-placeholder { 
                display: block !important; 
                width: 100% !important; 
                visibility: hidden !important;
                height: var(--hoo-header-height, ' . $total_placeholder_height_desk . 'px) !important;
                max-height: var(--hoo-header-height, ' . $total_placeholder_height_desk . 'px) !important;
            }';

            // Ensure header is fixed if placeholder is active and class is present
            $css .= "body.hoo-sh-compensation {$header_selector} {
                position: fixed !important;
                top: var(--hoo-header-top, 0px) !important;
                width: 100% !important;
                z-index: 99999 !important;
            }";
        }

        // Divi page-container padding-top compensation
        $css .= "
            body:not(.et_transparent_nav) #page-container {
                padding-top: var(--hoo-header-height) !important;
            }
        ";

        // Negative Margin Compensation (Pull Up)
        $layout_opts = isset($options['layout']) ? $options['layout'] : [];
        $pull_conditions = [
            'display_mode' => isset($layout_opts['pull_up_display_mode']) ? $layout_opts['pull_up_display_mode'] : 'exclude',
            'display_types' => isset($layout_opts['pull_up_display_types']) ? $layout_opts['pull_up_display_types'] : [],
            'display_ids' => isset($layout_opts['pull_up_display_ids']) ? $layout_opts['pull_up_display_ids'] : '',
            'display_body_classes' => isset($layout_opts['pull_up_display_body_classes']) ? $layout_opts['pull_up_display_body_classes'] : ''
        ];
        $run_pull_up = ConditionEvaluator::check($pull_conditions);

        if ($run_pull_up && $final_height_desk > 0 && !empty($target_selector)) {
            $pull_up_disable_desktop = isset($options['layout']['pull_up_disable_desktop']) && $options['layout']['pull_up_disable_desktop'] === '1';
            $pull_up_disable_tablet = isset($options['layout']['pull_up_disable_tablet']) && $options['layout']['pull_up_disable_tablet'] === '1';
            $pull_up_disable_mobile = isset($options['layout']['pull_up_disable_mobile']) && $options['layout']['pull_up_disable_mobile'] === '1';

            $pull_up_css_rule = "body.hoo-sh-compensation {$target_selector} { margin-top: var(--hoo-pull-up-mt) !important; }";

            if (!$use_custom_bp) {
                $css .= $pull_up_css_rule;
            } else {
                $min_tablet = $bp_mobile + 1;
                $min_desktop = $bp_tablet + 1;
                if (!$pull_up_disable_mobile) $css .= "@media (max-width: {$bp_mobile}px) { {$pull_up_css_rule} }";
                if (!$pull_up_disable_tablet) $css .= "@media (min-width: {$min_tablet}px) and (max-width: {$bp_tablet}px) { {$pull_up_css_rule} }";
                if (!$pull_up_disable_desktop) $css .= "@media (min-width: {$min_desktop}px) { {$pull_up_css_rule} }";
            }
        }

        if (!empty($css)) {
            echo '<style id="hooma-sh-layout-critical">' . $css . '</style>';
        }
    }
}
