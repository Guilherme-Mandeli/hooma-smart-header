<?php

namespace HoomaModules\HoomaSmartHeader\Controllers\Admin;

use HoomaModules\HoomaSmartHeader\Services\ThemeDetector;

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

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos suficientes.');
        }

        $selector = isset($_POST['selector']) ? sanitize_text_field($_POST['selector']) : '';
        $url = isset($_POST['url']) ? esc_url($_POST['url']) : home_url();

        if (empty($selector)) {
            wp_send_json_error('Selector vacío');
        }

        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);

        if (is_wp_error($response)) {
            wp_send_json_error('Error al conectar con el sitio: ' . $response->get_error_message());
        }

        $html = wp_remote_retrieve_body($response);

        if (empty($html)) {
            wp_send_json_error('Respuesta vacía del sitio');
        }

        $found = false;
        $count = 0;

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $xpath_query = $this->css_to_xpath($selector);

        if ($xpath_query) {
            $nodes = $xpath->query($xpath_query);
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
        $defaults = ThemeDetector::detect();
        wp_send_json_success($defaults);
    }

    private function css_to_xpath($selector)
    {
        $selector = trim($selector);
        if (strpos($selector, '#') === 0) {
            $id = substr($selector, 1);
            return "//*[@id='$id']";
        }
        if (strpos($selector, '.') === 0) {
            $class = substr($selector, 1);
            return "//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
        }
        if (preg_match('/^[a-zA-Z0-9]+$/', $selector)) {
            return "//" . $selector;
        }
        return null;
    }
}
