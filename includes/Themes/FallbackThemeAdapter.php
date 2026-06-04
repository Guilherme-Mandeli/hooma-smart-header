<?php

namespace HoomaModules\HoomaSmartHeader\Themes;

class FallbackThemeAdapter implements ThemeAdapterInterface
{
    public function is_compatible()
    {
        return true; // Always compatible as last resort
    }

    public function apply_initial_logo($initial_logo_url)
    {
        // Do nothing in PHP. 
        // rely on JS to detect that no header modification happened.
        // Or explicitly pass a flag to JS saying "Force Fallback"?

        // Actually, the main JS logic checks for data-hooma-logo="initial".
        // If it's not there, JS does the work.
    }

    public function requires_js_fallback()
    {
        return true;
    }

    public function get_name()
    {
        return 'JavaScript Fallback';
    }
}
