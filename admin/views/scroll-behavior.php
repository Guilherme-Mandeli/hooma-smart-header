<?php
defined('HOOMA_PATH') || exit;
$options = get_option('hooma_smart_header_settings');
$scroll_behavior = isset($options['scroll_behavior']) ? $options['scroll_behavior'] : [];

// Helper from initial-behavior
if (!function_exists('_hsh_val')) {
    function _hsh_val($options, $key, $default = '')
    {
        $keys = explode('.', $key);
        $val = $options;
        foreach ($keys as $k) {
            if (isset($val[$k])) {
                $val = $val[$k];
            } else {
                return $default;
            }
        }
        return $val;
    }
}
?>

<div class="hooma-sh-section">
    <div class="hooma-sh-section-header">
        <h2>Comportamiento en Scroll</h2>
        <p class="description">Controla la visibilidad del elemento al hacer scroll hacia arriba o hacia abajo (Smart Reveal).</p>
    </div>

    <form method="post" action="options.php" id="hooma-sh-form">
        <?php settings_fields('hooma_smart_header_group'); ?>

        <div class="hooma-sh-content">
            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Habilitar Comportamiento</strong>
                    <p class="description">Si está activo, el elemento se ocultará al bajar y se mostrará al subir.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[scroll_behavior][enabled]"
                        id="hsh-sb-enabled" value="1" <?php checked(_hsh_val($scroll_behavior, 'enabled'), '1'); ?>>
                </div>
            </div>

            <div class="hsh-row hsh-sb-dependent">
                <div class="hsh-row-label">
                    <strong>Sensibilidad de Scroll (px)</strong>
                    <p class="description">Cantidad mínima de píxeles a desplazar para activar el cambio.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="number" name="hooma_smart_header_settings[scroll_behavior][sensitivity]"
                        value="<?php echo isset($scroll_behavior['sensitivity']) ? esc_attr($scroll_behavior['sensitivity']) : '5'; ?>"
                        class="small-text" placeholder="5">
                </div>
            </div>
            
            <script>
                jQuery(document).ready(function($) {
                    function hsh_toggle_sb_global() {
                         var active = $('#hsh-sb-enabled').is(':checked');
                         if (active) {
                             $('.hsh-sb-dependent').css('opacity', '1').css('pointer-events', 'auto');
                         } else {
                             $('.hsh-sb-dependent').css('opacity', '0.5').css('pointer-events', 'none');
                         }
                    }
                    $('#hsh-sb-enabled').on('change', hsh_toggle_sb_global);
                    hsh_toggle_sb_global();
                });
            </script>
        </div>
    </form>
</div>

<!-- Device Settings -->
<div class="hooma-sh-section hsh-sb-dependent">
    <div class="hooma-sh-section-header">
        <h2>Ajustes por Dispositivos</h2>
        <p class="description">Desactiva este comportamiento en dispositivos específicos.</p>
    </div>
    <div class="hooma-sh-content">
        <?php 
            $use_custom_bp = _hsh_val($options, 'mobile.use_custom_breakpoints') === '1';
            $resp_link = '?page=hooma-smart-header&tab=responsive-behavior';
        ?>
        
        <?php if (!$use_custom_bp): ?>
            <div class="hsh-row">
                <div class="notice notice-warning inline" style="margin: 0; width: 100%;">
                    <p>Para ajustar, es necesario habilitar los <strong>puntos de interrupción</strong> en <a href="<?php echo esc_url($resp_link); ?>">Responsive & Layout</a>.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="hsh-row <?php echo !$use_custom_bp ? 'hsh-disabled-section' : ''; ?>">
            <div class="hsh-row-label">
                <strong>Desactivar en Dispositivos</strong>
            </div>
            <div class="hsh-row-control">
                <div class="hsh-checkbox-group">
                    <label>
                        <input type="checkbox" class="hsh-toggle" form="hooma-sh-form" name="hooma_smart_header_settings[scroll_behavior][mobile][disable_desktop]" value="1"
                            <?php checked(_hsh_val($scroll_behavior, 'mobile.disable_desktop'), '1'); ?>
                            <?php disabled($use_custom_bp, false); ?>>
                        Desktop
                    </label>
                    <label>
                        <input type="checkbox" class="hsh-toggle" form="hooma-sh-form" name="hooma_smart_header_settings[scroll_behavior][mobile][disable_tablet]" value="1"
                             <?php checked(_hsh_val($scroll_behavior, 'mobile.disable_tablet'), '1'); ?>
                             <?php disabled($use_custom_bp, false); ?>>
                        Tablet
                    </label>
                    <label>
                        <input type="checkbox" class="hsh-toggle" form="hooma-sh-form" name="hooma_smart_header_settings[scroll_behavior][mobile][disable_mobile]" value="1"
                             <?php checked(_hsh_val($scroll_behavior, 'mobile.disable_mobile'), '1'); ?>
                             <?php disabled($use_custom_bp, false); ?>>
                        Mobile
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Display Conditions -->
<div class="hooma-sh-section hsh-sb-dependent">
    <div class="hooma-sh-section-header">
        <h2>Condiciones de visualización</h2>
        <p class="description">Limita dónde se habilita el comportamiento de Smart Reveal.</p>
    </div>
    <div class="hooma-sh-content">
        <div class="hsh-row-content">
            <strong style="margin-bottom: 10px; display: block; font-size: 14px;">1. Modo de aplicación</strong>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px;">
                    <input type="radio" form="hooma-sh-form" name="hooma_smart_header_settings[scroll_behavior][display_mode]" value="exclude" 
                        <?php checked(_hsh_val($scroll_behavior, 'display_mode', 'exclude'), 'exclude'); ?>>
                    Funcionar siempre, excepto en…
                </label>
                <label style="display: block;">
                    <input type="radio" form="hooma-sh-form" name="hooma_smart_header_settings[scroll_behavior][display_mode]" value="include" 
                        <?php checked(_hsh_val($scroll_behavior, 'display_mode'), 'include'); ?>>
                    Funcionar solo en…
                </label>
            </div>

            <div class="hsh-sub-card">
                 <strong style="margin-bottom: 10px; display: block; font-size: 14px;">2. Tipos de contenido</strong>
                 <div class="hsh-checkbox-group" style="margin-bottom: 20px; background: #fff; border: 1px solid #dcdde1; padding: 10px; border-radius: 4px;">
                    <?php 
                    $selected_types = _hsh_val($scroll_behavior, 'display_types', []);
                    $post_types = get_post_types(['public' => true], 'objects');
                    foreach ($post_types as $type): 
                        if ($type->name === 'attachment') continue;
                    ?>
                        <label style="margin-right: 15px;">
                            <input type="checkbox" form="hooma-sh-form" name="hooma_smart_header_settings[scroll_behavior][display_types][]" 
                                value="<?php echo esc_attr($type->name); ?>" 
                                <?php checked(in_array($type->name, $selected_types), true); ?>>
                            <?php echo esc_html($type->label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <strong style="margin-bottom: 10px; display: block; font-size: 14px;">3. IDs Específicos</strong>
                <input type="text" form="hooma-sh-form" name="hooma_smart_header_settings[scroll_behavior][display_ids]" 
                    value="<?php echo esc_attr(_hsh_val($scroll_behavior, 'display_ids')); ?>" 
                     class="regular-text" style="width: 100%; margin-bottom: 20px;" placeholder="12,45">

                <strong style="margin-bottom: 10px; display: block; font-size: 14px;">4. Clase de la tag body</strong>
                <input type="text" form="hooma-sh-form" name="hooma_smart_header_settings[scroll_behavior][display_body_classes]" 
                    value="<?php echo esc_attr(_hsh_val($scroll_behavior, 'display_body_classes')); ?>" 
                    class="regular-text" placeholder="class1, class2" style="width: 100%;">
                <p class="description" style="margin-top: 5px;">Introduce las clases separadas por comas. Si alguna de estas clases se encuentra en la etiqueta body, se aplicará la condición.</p>
            </div>
        </div>
    </div>
</div>

<div style="margin-top: 20px;">
    <button type="submit" form="hooma-sh-form" class="button button-primary button-large">Guardar Comportamiento</button>
    <div id="hsh-build-console" class="hsh-build-console"></div>
</div>