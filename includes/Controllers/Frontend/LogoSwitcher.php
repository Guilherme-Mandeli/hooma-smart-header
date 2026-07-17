<?php

namespace HoomaModules\HoomaSmartHeader\Controllers\Frontend;

use HoomaModules\HoomaSmartHeader\Services\ThemeDetector;
use Hooma;

class LogoSwitcher
{

    public function init()
    {
        // Moved from direct execution to template_redirect to allow conditional checks (is_page, etc)
        add_action('template_redirect', [$this, 'setup_initial_logo']);
    }

    public function setup_initial_logo()
    {
        $options = Hooma::settings()->get('hooma_smart_header.settings', []);
        $switcher = isset($options['logo_switcher']) ? $options['logo_switcher'] : [];

        // 1. Basic Enabled Check
        if (empty($switcher)) {
            return;
        }

        if (empty($switcher['enabled']) || $switcher['enabled'] !== '1') {
            return;
        }

        // 2. Display Conditions Check
        if (!\HoomaModules\HoomaSmartHeader\Services\ConditionEvaluator::check($switcher)) {
            return;
        }

        // 3. Select Initial Logo (PHP only applies global/desktop logo, tablet/mobile is handled in JS)
        $initial_logo = !empty($switcher['initial_logo']) ? $switcher['initial_logo'] : '';

        if (empty($initial_logo)) {
            return;
        }

        $initial_logo = esc_url($initial_logo);

        // Permitir que otros módulos modifiquen el logo inicial dinámicamente mediante el bus de eventos
        $initial_logo = Hooma::events()->filter('hooma_smart_header.initial_logo', $initial_logo, $options);

        // Detect Strategy
        $adapter = ThemeDetector::get_adapter();

        // Execute Strategy
        $adapter->apply_initial_logo($initial_logo);

        // Save state (optional debug info, only for administrators to avoid DB write overhead on public visits)
        if (Hooma::auth()->can('manage_options')) {
            Hooma::cache()->set('hooma_sh_last_run_strategy', [
                'strategy' => $adapter->get_name(),
                'fallback' => $adapter->requires_js_fallback(),
                'timestamp' => time(),
                'theme' => get_stylesheet()
            ]);
        }
    }

}
