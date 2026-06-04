<?php

namespace HoomaModules\HoomaSmartHeader\Controllers\Frontend;

class InitialBehavior
{
    private $option_name = 'hooma_smart_header_settings';

    public function init()
    {
        add_filter('body_class', [$this, 'add_body_classes']);
        add_action('wp_head', [$this, 'output_critical_css'], 1);
    }

    public function add_body_classes($classes)
    {
        $options = get_option($this->option_name);
        $behavior = isset($options['behavior']) ? $options['behavior'] : [];

        if (!isset($behavior['hide_on_scroll']) || $behavior['hide_on_scroll'] !== '1') {
            return $classes;
        }

        if (!\HoomaModules\HoomaSmartHeader\Services\ConditionEvaluator::check($behavior)) {
            return $classes;
        }

        $classes[] = 'hooma-sh-pre-init';
        return $classes;
    }

    public function output_critical_css()
    {
        $options = get_option($this->option_name);
        $behavior = isset($options['behavior']) ? $options['behavior'] : [];

        if (isset($behavior['hide_on_scroll']) && $behavior['hide_on_scroll'] === '1') {
            if (!\HoomaModules\HoomaSmartHeader\Services\ConditionEvaluator::check($behavior)) {
                return;
            }

            $header_selector = isset($options['selectors']['header']) ? $options['selectors']['header'] : 'header';
            $animation = isset($behavior['animation']) ? $behavior['animation'] : 'fade';

            $css_selector = "body.hooma-sh-pre-init " . $header_selector;

            $css = "
                {$css_selector} {
                    pointer-events: none;
                    transition: opacity 0.4s ease, transform 0.4s ease-in-out;
            ";

            if ($animation === 'slide') {
                $css .= "transform: translateY(-100%);";
            } else {
                $css .= "opacity: 0;";
            }

            $css .= " }";

            echo '<style id="hooma-sh-initial-behavior">' . $css . '</style>';
        }
    }
}
