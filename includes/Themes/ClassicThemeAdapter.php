<?php

namespace HoomaModules\HoomaSmartHeader\Themes;

class ClassicThemeAdapter implements ThemeAdapterInterface
{
    private $initial_logo_url;

    public function is_compatible()
    {
        // Broad compatibility, usually if custom-logo is supported
        return current_theme_supports('custom-logo');
    }

    public function apply_initial_logo($initial_logo_url)
    {
        $this->initial_logo_url = $initial_logo_url;

        // Filter the output of the_custom_logo()
        add_filter('get_custom_logo', [$this, 'filter_get_custom_logo']);

        // Filter the raw image source data for the logo attachment
        add_filter('wp_get_attachment_image_src', [$this, 'filter_logo_attachment_src'], 10, 4);

        add_filter('hooma_sh_initial_logo_applied', '__return_true');
    }

    public function requires_js_fallback()
    {
        // If the theme doesn't use standard functions, this might fail, necessitating fallback
        // But the fallback adapter is separate.
        return false;
    }

    public function get_name()
    {
        return 'Classic Theme';
    }

    public function filter_get_custom_logo($html)
    {
        if (empty($this->initial_logo_url)) {
            return $html;
        }

        // Replace src
        $html = preg_replace('/src=["\']([^"\']+)["\']/', 'src="' . esc_url($this->initial_logo_url) . '"', $html);

        // Remove srcset/sizes
        $html = preg_replace('/srcset=["\']([^"\']+)["\']/', '', $html);
        $html = preg_replace('/sizes=["\']([^"\']+)["\']/', '', $html);

        // Add marker
        $html = str_replace('<img ', '<img data-hooma-logo="initial" ', $html);

        return $html;
    }

    public function filter_logo_attachment_src($image, $attachment_id, $size, $icon)
    {
        $custom_logo_id = get_theme_mod('custom_logo');

        if ($custom_logo_id && $attachment_id == $custom_logo_id) {
            if (isset($image[0])) {
                $image[0] = $this->initial_logo_url;
                // Ideally we should also adjust width/height if we knew them, but URL is most important
            }
        }

        return $image;
    }
}
