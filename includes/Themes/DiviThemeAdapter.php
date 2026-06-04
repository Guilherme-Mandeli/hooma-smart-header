<?php

namespace HoomaModules\HoomaSmartHeader\Themes;

class DiviThemeAdapter implements ThemeAdapterInterface
{
    private $initial_logo_url;

    public function is_compatible()
    {
        $theme = wp_get_theme();
        // Check current theme or parent
        if (stripos($theme->get('Name'), 'Divi') !== false || stripos($theme->get_template(), 'Divi') !== false) {
            return true;
        }
        return false;
    }

    public function apply_initial_logo($initial_logo_url)
    {
        $this->initial_logo_url = $initial_logo_url;

        // Hook early to start buffering
        if (did_action('wp_loaded')) {
            $this->start_buffer();
        } else {
            add_action('wp_loaded', [$this, 'start_buffer'], 0);
        }
        add_action('shutdown', [$this, 'end_buffer'], 0);

        // Mark as applied for JS to see
        add_filter('hooma_sh_initial_logo_applied', '__return_true');
    }

    public function requires_js_fallback()
    {
        return false; // Divi handled via PHP
    }

    public function get_name()
    {
        return 'Divi';
    }

    public function start_buffer()
    {
        if (is_admin() || wp_doing_ajax() || wp_is_json_request()) {
            return;
        }

        ob_start([$this, 'replace_logo_in_buffer']);
    }

    public function end_buffer()
    {
        if (ob_get_level() > 0) {
            @ob_end_flush();
        }
    }

    public function replace_logo_in_buffer($html)
    {
        if (empty($this->initial_logo_url)) {
            return $html;
        }

        // Prevent XML errors
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        // Use flags to handle HTML5 better, though Divi is mostly standard
        $html_encoded = function_exists('mb_convert_encoding') ? mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8') : $html;
        $doc->loadHTML($html_encoded, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new \DOMXPath($doc);

        // Divi specific selectors
        $imgs = $xpath->query(
            '//img[
                contains(@class,"et_pb_menu") 
                or contains(@class,"et_pb_menu__logo")
                or contains(@class,"logo")
                or @id="logo"
            ]'
        );

        $replaced = false;

        foreach ($imgs as $img) {
            if ($img->hasAttribute('data-hooma-logo')) {
                continue;
            }

            $img->setAttribute('src', $this->initial_logo_url);
            $img->removeAttribute('srcset');
            $img->removeAttribute('sizes');
            $img->setAttribute('data-hooma-logo', 'initial');
            $replaced = true;
        }

        if ($replaced) {
            $html = $doc->saveHTML();
        }

        libxml_clear_errors();

        return $html;
    }
}
