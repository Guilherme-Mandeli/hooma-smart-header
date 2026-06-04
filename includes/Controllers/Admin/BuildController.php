<?php

namespace HoomaModules\HoomaSmartHeader\Controllers\Admin;

class BuildController
{
    private $option_name = 'hooma_smart_header_settings';

    public function init()
    {
        add_action('wp_ajax_hsh_trigger_build', [$this, 'handle_build_trigger']);
    }

    public function handle_build_trigger()
    {
        check_ajax_referer('hooma_sh_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('No tienes permisos suficientes.');
        }

        $service = new \HoomaModules\HoomaSmartHeader\Services\BuildService();
        $result = $service->execute();

        if ($result['success']) {
            wp_send_json_success([
                'message' => $result['message'],
                'output' => $result['output']
            ]);
        } else {
            wp_send_json_error([
                'message' => $result['message'],
                'output' => $result['output'],
                'fallback' => isset($result['fallback']) ? $result['fallback'] : false
            ]);
        }
    }
}
