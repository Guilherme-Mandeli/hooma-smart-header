<?php
/*
 * Module Name: Hooma Smart Header
 * Description: Control avanzado y flexible del Header (Scroll, Sticky, Mobile/Tablet, Logos dinámicos).
 * Version: 1.0.260618
 * Author: Hooma Team
 * Requires Hooma: 1.0.0
 */

defined('HOOMA_PATH') || exit;

// O Core injeta: $module_namespace, $module_slug, $module_version, $modules_url

if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Hooma SH Index: Loaded. Slug: ' . (isset($module_slug) ? $module_slug : 'unset'));
}

if (is_admin()) {
    $class = "HoomaModules\\" . $module_namespace . "\\Controllers\\Admin\\AdminController";
    if (class_exists($class)) {
        (new $class($module_slug, $module_version, $modules_url . $module_slug))->init();
    }
} else {
    $class = "HoomaModules\\" . $module_namespace . "\\Controllers\\Frontend\\FrontendController";
    if (class_exists($class)) {
        (new $class($module_slug, $module_version, $modules_url . $module_slug))->init();
    }
}
