<?php

namespace HoomaModules\HoomaSmartHeader\Themes;

interface ThemeAdapterInterface
{
    /**
     * Determine if this adapter is compatible with the current theme.
     *
     * @return bool
     */
    public function is_compatible();

    /**
     * Apply the initial logo strategy.
     *
     * @param string $initial_logo_url The URL of the initial logo.
     * @return void
     */
    public function apply_initial_logo($initial_logo_url);

    /**
     * Check if a JavaScript fallback is required.
     *
     * @return bool
     */
    public function requires_js_fallback();

    /**
     * Get the identifier of the theme strategy.
     *
     * @return string
     */
    public function get_name();
}
