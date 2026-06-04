<?php

namespace HoomaModules\HoomaSmartHeader\Services;

use HoomaModules\HoomaSmartHeader\Themes\ThemeAdapterInterface;
use HoomaModules\HoomaSmartHeader\Themes\DiviThemeAdapter;
use HoomaModules\HoomaSmartHeader\Themes\BlockThemeAdapter;
use HoomaModules\HoomaSmartHeader\Themes\ClassicThemeAdapter;
use HoomaModules\HoomaSmartHeader\Themes\FallbackThemeAdapter;

class ThemeDetector
{
    /**
     * Detects the active theme and returns the appropriate adapter.
     *
     * @return ThemeAdapterInterface
     */
    public static function get_adapter()
    {
        $divi = new DiviThemeAdapter();
        if ($divi->is_compatible()) {
            return $divi;
        }

        $block = new BlockThemeAdapter();
        if ($block->is_compatible()) {
            return $block;
        }

        $classic = new ClassicThemeAdapter();
        if ($classic->is_compatible()) {
            return $classic;
        }

        return new FallbackThemeAdapter();
    }

    /**
     * Detects theme defaults for configuration (Selectors, Breakpoints).
     * Used by Admin UI to restore defaults.
     *
     * @return array
     */
    public static function detect()
    {
        $theme = wp_get_theme();
        $theme_name = $theme->get_stylesheet();
        $parent_theme = $theme->parent() ? $theme->parent()->get_stylesheet() : '';

        // Logic based on Documento Técnico
        if (stripos($theme_name, 'divi') !== false || stripos($parent_theme, 'divi') !== false) {
            return [
                'header' => '#main-header',
                'sticky' => '.et-fixed-header',
                'logo' => '#logo',
                'mobile_breakpoint' => 767,
                'tablet_breakpoint' => 980
            ];
        }

        if (stripos($theme_name, 'astra') !== false || stripos($parent_theme, 'astra') !== false) {
            return [
                'header' => '.site-header',
                'sticky' => '.ast-sticky-header-moved', // Example for Astra
                'logo' => '.site-logo-img .custom-logo-link img',
                'mobile_breakpoint' => 767,
                'tablet_breakpoint' => 921
            ];
        }

        // Default WordPress/Fallback
        return [
            'header' => 'header, .site-header',
            'sticky' => '.is-sticky, .sticky',
            'logo' => '.custom-logo img, #site-logo img',
            'mobile_breakpoint' => 767,
            'tablet_breakpoint' => 980
        ];
    }
}
