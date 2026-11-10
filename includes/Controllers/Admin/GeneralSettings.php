<?php

namespace HoomaModules\HoomaSmartHeader\Controllers\Admin;

use HoomaModules\HoomaSmartHeader\Services\ThemeDetector;
use Hooma;

class GeneralSettings
{
    public function init()
    {
        add_action('wp_ajax_hooma_sh_verify_selector', [$this, 'ajax_verify_selector']);
        add_action('wp_ajax_hooma_sh_restore_defaults', [$this, 'ajax_restore_defaults']);
    }

    public function ajax_verify_selector()
    {
        check_ajax_referer('hooma_sh_nonce', 'nonce');

        if (!Hooma::auth()->can('manage_options')) {
            wp_send_json_error('No tienes permisos suficientes.');
        }

        $selector = isset($_POST['selector']) ? sanitize_text_field($_POST['selector']) : '';
        $raw_url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : home_url();

        // Validate URL host to prevent SSRF against internal/external networks
        $home_host = wp_parse_url(home_url(), PHP_URL_HOST);
        $target_host = wp_parse_url($raw_url, PHP_URL_HOST);
        $url = ($target_host === $home_host) ? $raw_url : home_url();

        if (empty($selector)) {
            wp_send_json_error('Selector vacío');
        }

        try {
            $response_obj = Hooma::http()->get($url, [
                'timeout'           => 10,
                'sslverify'         => false,
                'allow_unsafe_urls' => true
            ]);
            $html = $response_obj->body();
            if (!$response_obj->successful() || empty($html)) {
                wp_send_json_error('Respuesta vacía o error del sitio: HTTP ' . $response_obj->status());
            }
        } catch (\RuntimeException $e) {
            wp_send_json_error('Error al conectar con el sitio: ' . $e->getMessage());
        }

        $found = false;
        $count = 0;

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $xpath_query = $this->css_to_xpath($selector);

        if ($xpath_query) {
            $nodes = @$xpath->query($xpath_query);
            if ($nodes && $nodes->length > 0) {
                $found = true;
                $count = $nodes->length;
            }
        }

        if ($found) {
            wp_send_json_success(['count' => $count]);
        } else {
            wp_send_json_error('No encontrado');
        }
    }

    public function ajax_restore_defaults()
    {
        check_ajax_referer('hooma_sh_nonce', 'nonce');

        if (!Hooma::auth()->can('manage_options')) {
            wp_send_json_error('No tienes permisos suficientes.');
        }

        $defaults = ThemeDetector::detect();
        wp_send_json_success($defaults);
    }

    private function css_to_xpath($selector)
    {
        $selector = trim($selector);
        if (empty($selector)) return null;

        $sub_selectors = array_filter(array_map('trim', explode(',', $selector)));
        $xpaths = [];

        foreach ($sub_selectors as $sub) {
            $parts = preg_split('/\s+/', $sub);
            $query = '';
            foreach ($parts as $part) {
                if (strpos($part, '#') === 0) {
                    $id = substr($part, 1);
                    $query .= "//*[@id='$id']";
                } elseif (strpos($part, '.') === 0) {
                    $class = substr($part, 1);
                    $query .= "//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
                } elseif (preg_match('/^[a-zA-Z0-9_-]+$/', $part)) {
                    $query .= "//" . $part;
                }
            }
            if (!empty($query)) {
                $xpaths[] = $query;
            }
        }

        return !empty($xpaths) ? implode(' | ', $xpaths) : null;
    }
}
