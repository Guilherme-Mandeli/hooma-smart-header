<?php

namespace HoomaModules\HoomaSmartHeader\Themes;

class BlockThemeAdapter implements ThemeAdapterInterface
{
    private $initial_logo_url;
    private $logo_applied = false;

    public function is_compatible()
    {
        return function_exists('wp_is_block_theme') && wp_is_block_theme();
    }

    public function apply_initial_logo($initial_logo_url)
    {
        $this->initial_logo_url = $initial_logo_url;
        add_filter('render_block', [$this, 'filter_site_logo_block'], 10, 2);
    }

    public function requires_js_fallback()
    {
        // If we haven't applied it by the time this is checked (footer?), then yes.
        // But usually this decision is made at init.
        // Let's assume false, as we trust the filter. 
        // However, if the block isn't present, we might want fallback?
        // For now, return false implies "I intend to handle it".
        return false;
    }

    public function get_name()
    {
        return 'Block Theme (FSE)';
    }

    public function filter_site_logo_block($block_content, $block)
    {
        if ($block['blockName'] === 'core/site-logo') {

            if (empty($this->initial_logo_url)) {
                return $block_content;
            }

            // Simple regex replacement for src, avoiding complex DOM overhead for small block
            // However, DOM is safer.
            // Let's use string replacement for src attribute

            // Pattern to match src="..."
            $pattern = '/src=["\']([^"\']+)["\']/';

            $new_content = preg_replace($pattern, 'src="' . esc_url($this->initial_logo_url) . '"', $block_content);

            // Also remove srcset/sizes to prevent overriding
            $new_content = preg_replace('/srcset=["\']([^"\']+)["\']/', '', $new_content);
            $new_content = preg_replace('/sizes=["\']([^"\']+)["\']/', '', $new_content);

            // Add marker
            $new_content = str_replace('<img ', '<img data-hooma-logo="initial" ', $new_content);

            $this->logo_applied = true;

            // Signal global success if needed
            add_filter('hooma_sh_initial_logo_applied', '__return_true');

            return $new_content;
        }

        return $block_content;
    }
}
