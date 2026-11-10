<?php

namespace HoomaModules\HoomaSmartHeader\Controllers\Frontend;

use Hooma;

class LogoPreloader
{
    private $cache_key = 'hooma_sh_header_images_cache';

    public function init()
    {
        // Prioridad 1 para el preload
        add_action('wp_head', [$this, 'output_preloads'], 1);
        
        // Manejar el trigger asíncrono en background de forma segura
        add_action('init', [$this, 'handle_async_scan_trigger']);

        // Evaluar si es necesario calentar la caché en segundo plano
        add_action('template_redirect', [$this, 'maybe_trigger_scan_or_buffer'], 1);
    }

    public function output_preloads()
    {
        // Obtener directamente del caché para máxima velocidad (cero consultas pesadas por página)
        $cached_data = Hooma::cache()->get($this->cache_key, false);
        $options = Hooma::settings()->get('hooma_smart_header.settings', []);
        $last_saved = is_array($options) && isset($options['last_saved']) ? $options['last_saved'] : 0;

        if (is_array($cached_data) && isset($cached_data['last_saved']) && $cached_data['last_saved'] === $last_saved && isset($cached_data['images'])) {
            $images_to_preload = $cached_data['images'];
        } else {
            // Si el caché no existe, es antiguo o no coincide el last_saved
            $images_to_preload = $this->get_native_images(is_array($options) ? $options : null);
        }

        // Filtro global del Core para permitir a otros módulos añadir URLs a precargar
        $images_to_preload = Hooma::events()->filter('hooma_smart_header.preloads', $images_to_preload, $options);

        // Deduping por src
        $merged = [];
        foreach ($images_to_preload as $img_data) {
            if (empty($img_data['src'])) continue;
            $merged[$img_data['src']] = $img_data;
        }

        echo "<!-- Hooma SH Preloader: " . count($merged) . " images found -->\n";

        if (!empty($merged)) {
            foreach ($merged as $img_data) {
                $src = esc_url($img_data['src']);
                $srcset = !empty($img_data['srcset']) ? ' imagesrcset="' . esc_attr($img_data['srcset']) . '"' : '';
                $sizes = !empty($img_data['sizes']) ? ' imagesizes="' . esc_attr($img_data['sizes']) . '"' : '';
                echo '<link rel="preload" as="image" href="' . $src . '"' . $srcset . $sizes . ">\n";
            }
        }
    }

    public function get_native_images($options = null)
    {
        if ($options === null) {
            $options = Hooma::settings()->get('hooma_smart_header.settings', []);
        }
        $images = [];

        // 1. Logos configurados en Logo Switcher
        $initial = isset($options['logo_switcher']['initial_logo']) ? trim($options['logo_switcher']['initial_logo']) : '';
        $initial_tablet = isset($options['logo_switcher']['initial_logo_tablet']) ? trim($options['logo_switcher']['initial_logo_tablet']) : '';
        $initial_mobile = isset($options['logo_switcher']['initial_logo_mobile']) ? trim($options['logo_switcher']['initial_logo_mobile']) : '';

        $alt = isset($options['logo_switcher']['alt_logo']) ? trim($options['logo_switcher']['alt_logo']) : '';
        $alt_tablet = isset($options['logo_switcher']['alt_logo_tablet']) ? trim($options['logo_switcher']['alt_logo_tablet']) : '';
        $alt_mobile = isset($options['logo_switcher']['alt_logo_mobile']) ? trim($options['logo_switcher']['alt_logo_mobile']) : '';

        if (!empty($initial)) $images[] = ['src' => $initial];
        if (!empty($initial_tablet)) $images[] = ['src' => $initial_tablet];
        if (!empty($initial_mobile)) $images[] = ['src' => $initial_mobile];

        if (!empty($alt)) $images[] = ['src' => $alt];
        if (!empty($alt_tablet)) $images[] = ['src' => $alt_tablet];
        if (!empty($alt_mobile)) $images[] = ['src' => $alt_mobile];

        // 2. Logo nativo de WordPress (Theme Customizer)
        $logo_id = get_theme_mod('custom_logo');
        if ($logo_id) {
            $logo_url = wp_get_attachment_image_url($logo_id, 'full');
            if ($logo_url) {
                $srcset = wp_get_attachment_image_srcset($logo_id, 'full');
                $sizes = wp_get_attachment_image_sizes($logo_id, 'full');
                $images[] = [
                    'src' => $logo_url,
                    'srcset' => $srcset ?: '',
                    'sizes' => $sizes ?: ''
                ];
            }
        }

        // 3. Divi Theme Logo Backup
        $divi_options = get_option('et_divi');
        if (is_array($divi_options) && !empty($divi_options['divi_logo'])) {
            $images[] = ['src' => $divi_options['divi_logo']];
        }

        return $images;
    }

    public function warm_cache($options = null)
    {
        if ($options === null) {
            $options = Hooma::settings()->get('hooma_smart_header.settings', []);
        }
        $native_images = $this->get_native_images($options);
        $last_saved = is_array($options) && isset($options['last_saved']) ? $options['last_saved'] : 0;

        // Primero guardamos los nativos conocidos con scanned => false
        // Esto provocará que el frontend dispare el escaneo asíncrono en background al primer hit de usuario.
        $cache_data = [
            'scanned'    => false,
            'last_saved' => $last_saved,
            'images'     => $native_images
        ];
        Hooma::cache()->set($this->cache_key, $cache_data);
    }

    public function maybe_trigger_scan_or_buffer()
    {
        if (is_admin() || wp_doing_ajax() || wp_is_json_request()) {
            return;
        }

        // Evitar bucles infinitos si es la petición de nuestro propio escáner
        if (isset($_GET['hsh_preloader_scan']) || isset($_GET['hsh_do_background_scan'])) {
            return;
        }

        $cached_data = Hooma::cache()->get($this->cache_key, false);
        $options = Hooma::settings()->get('hooma_smart_header.settings', []);
        $last_saved = is_array($options) && isset($options['last_saved']) ? $options['last_saved'] : 0;

        // Si la caché ya existe, está marcada como escaneada y el last_saved coincide, no hacemos nada
        if (is_array($cached_data) && isset($cached_data['last_saved']) && $cached_data['last_saved'] === $last_saved && !empty($cached_data['scanned'])) {
            return;
        }

        // Si la caché está fría, usamos un semáforo transient para prevenir Race Conditions
        $lock_key = 'hsh_preloader_scan_lock';
        if (Hooma::cache()->get($lock_key)) {
            // Ya hay un escaneo en cola o proceso por otra petición, omitimos para evitar sobrecarga de CPU
            return;
        }

        // Establecer el semáforo por 30 segundos
        Hooma::cache()->set($lock_key, '1', 30);

        // Disparar trigger asíncrono no bloqueante
        $this->trigger_async_scan($last_saved);
    }

    private function trigger_async_scan($last_saved)
    {
        $token = wp_generate_password(20, false);
        Hooma::cache()->set('hsh_scan_token_' . $last_saved, $token, 60);

        $url = add_query_arg([
            'hsh_do_background_scan' => $token,
            'hsh_version'            => $last_saved
        ], home_url('/'));

        try {
            Hooma::http()->get($url, [
                'blocking'          => false,
                'timeout'           => 0.5,
                'sslverify'         => false,
                'allow_unsafe_urls' => true,
                'headers'           => [
                    'User-Agent' => 'HoomaSmartHeaderPreloaderTrigger/1.0'
                ]
            ]);
        } catch (\RuntimeException $e) {
            Hooma::logger()->error('Hooma SH Preloader Trigger Error: ' . $e->getMessage());
        }
    }

    public function handle_async_scan_trigger()
    {
        if (!isset($_GET['hsh_do_background_scan']) || !isset($_GET['hsh_version'])) {
            return;
        }

        $token = sanitize_text_field($_GET['hsh_do_background_scan']);
        $last_saved = intval($_GET['hsh_version']);

        $saved_token = Hooma::cache()->get('hsh_scan_token_' . $last_saved);

        if (!$saved_token || $saved_token !== $token) {
            return;
        }

        // Consumir el token de inmediato
        Hooma::cache()->delete('hsh_scan_token_' . $last_saved);

        // Realizar el escaneo completo
        $this->scan_public_html_and_save($last_saved);

        // Liberar el semáforo
        Hooma::cache()->delete('hsh_preloader_scan_lock');

        exit;
    }

    public function scan_public_html_and_save($last_saved, $options = null)
    {
        if ($options === null) {
            $options = Hooma::settings()->get('hooma_smart_header.settings', []);
        }

        $header_selector = isset($options['selectors']['header']) ? trim($options['selectors']['header']) : '';
        $native_images = $this->get_native_images($options);

        // Si no hay selector, no podemos escanear, pero guardamos marcado como scanned para evitar bucles
        if (empty($header_selector)) {
            $cache_data = [
                'scanned'    => true,
                'last_saved' => $last_saved,
                'images'     => $native_images
            ];
            Hooma::cache()->set($this->cache_key, $cache_data);
            return;
        }

        // Petición loopback a la página principal
        $url = add_query_arg('hsh_preloader_scan', '1', home_url('/'));
        
        try {
            $response_obj = Hooma::http()->get($url, [
                'timeout'           => 15,
                'sslverify'         => false,
                'allow_unsafe_urls' => true,
                'headers'           => [
                    'User-Agent' => 'HoomaSmartHeaderPreloaderScanner/1.0'
                ]
            ]);
            $code = $response_obj->status();
            $html = $response_obj->body();
        } catch (\RuntimeException $e) {
            $code = 500;
            $html = '';
        }

        if ($code !== 200 || empty($html)) {
            $cache_data = [
                'scanned'    => true,
                'last_saved' => $last_saved,
                'images'     => $native_images
            ];
            Hooma::cache()->set($this->cache_key, $cache_data);
            return;
        }

        // Instanciar DOMDocument y DOMXPath para el análisis
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $xpath = new \DOMXPath($doc);

        $query = '';
        $clean_selector = trim($header_selector);

        if (strpos($clean_selector, '#') === 0) {
            $parts = explode(' ', ltrim($clean_selector, '#'));
            $id = $parts[0];
            $query = "//*[@id='$id']//img";
        } elseif (strpos($clean_selector, '.') === 0) {
            $parts = explode(' ', ltrim($clean_selector, '.'));
            $class = $parts[0];
            $query = "//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]//img";
        } elseif (preg_match('/^[a-zA-Z0-9_-]+$/', $clean_selector)) {
            $query = "//" . $clean_selector . "//img";
        }

        if (empty($query)) {
            $query = "//header//img | //*[@id='masthead']//img | //*[contains(@class, 'site-header')]//img";
        }

        $img_nodes = $xpath->query($query);
        $images_found = [];
        $unique_src = [];

        if ($img_nodes && $img_nodes->length > 0) {
            foreach ($img_nodes as $img) {
                // Atributos alternativos para plugins de Lazy Load
                $src = $img->getAttribute('data-src') ?: $img->getAttribute('data-lazy-src') ?: $img->getAttribute('src');
                $srcset = $img->getAttribute('data-srcset') ?: $img->getAttribute('data-lazy-srcset') ?: $img->getAttribute('srcset');
                $sizes = $img->getAttribute('data-sizes') ?: $img->getAttribute('data-lazy-sizes') ?: $img->getAttribute('sizes');
                
                if (!empty($src) && strpos($src, 'data:image') === false && !in_array($src, $unique_src)) {
                    $images_found[] = [
                        'src' => $src,
                        'srcset' => $srcset ?: '',
                        'sizes' => $sizes ?: ''
                    ];
                    $unique_src[] = $src;
                }
            }
        }
        libxml_clear_errors();

        // MERGE de las imágenes encontradas y las nativas conocidas
        $all_images = [];
        $unique_src_merge = [];

        foreach (array_merge($native_images, $images_found) as $img_data) {
            if (empty($img_data['src'])) continue;
            if (!in_array($img_data['src'], $unique_src_merge)) {
                $all_images[] = $img_data;
                $unique_src_merge[] = $img_data['src'];
            }
        }

        // Guardar la caché final completamente escaneada y segura
        $cache_data = [
            'scanned'    => true,
            'last_saved' => $last_saved,
            'images'     => $all_images
        ];
        Hooma::cache()->set($this->cache_key, $cache_data);
    }
}
