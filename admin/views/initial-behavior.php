<?php
defined('HOOMA_PATH') || exit;

$options = get_option('hooma_smart_header_settings');
$defaults = [
    'header' => 'header',
    'sticky' => '',
    'logo' => ''
];
// Helper to get value
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
        <h2>Comportamiento inicial</h2>
        <p class="description">Configura cómo se comporta el header cuando el usuario empieza a hacer scroll en la página.</p>
    </div>
    
    <form method="post" action="options.php" id="hooma-sh-form">
        <?php settings_fields('hooma_smart_header_group'); ?>
        <input type="hidden" name="hooma_smart_header_settings[behavior][sentinel]" value="1">

        <div class="hooma-sh-content">
            
            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Ocultar antes de hacer scroll</strong>
                    <p class="description">El header estará oculto al cargar la página y aparecerá según los triggers.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[behavior][hide_on_scroll]"
                        id="hsh-hide-on-scroll" value="1" <?php checked(_hsh_val($options, 'behavior.hide_on_scroll'), '1'); ?>>
                </div>
            </div>

            <div class="hsh-row hsh-global-dependent">
                <div class="hsh-row-label">
                    <strong>Mostrar cuando</strong>
                    <p class="description">Determina qué evento hará que el header sea visible.</p>
                </div>
                <div class="hsh-row-control">
                    <select name="hooma_smart_header_settings[behavior][trigger_type]" id="hsh-trigger-type"
                        class="hsh-behavior-dependent regular-text">
                        <option value="distance" <?php selected(_hsh_val($options, 'behavior.trigger_type'), 'distance'); ?>>Distancia de scroll</option>
                        <option value="element" <?php selected(_hsh_val($options, 'behavior.trigger_type'), 'element'); ?>>Encuentre un elemento</option>
                    </select>
                </div>
            </div>

            <div class="hsh-row hsh-global-dependent" id="hsh-trigger-distance-row">
                <div class="hsh-row-label">
                    <strong>Scroll (px)</strong>
                    <p class="description">Distancia en píxeles hasta que se haga visible.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="number" name="hooma_smart_header_settings[behavior][scroll_min]"
                        value="<?php echo esc_attr(_hsh_val($options, 'behavior.scroll_min', 100)); ?>"
                        class="small-text hsh-behavior-dependent">
                </div>
            </div>

            <div class="hsh-row hsh-global-dependent" id="hsh-trigger-element-row">
                <div class="hsh-row-label">
                    <strong>Selector de Elemento</strong>
                    <p class="description">Se usará la distancia hasta el tope de este elemento (Ej: <code>#custom-section</code>).</p>
                </div>
                <div class="hsh-row-control">
                    <input type="text" name="hooma_smart_header_settings[behavior][trigger_selector]"
                        value="<?php echo esc_attr(_hsh_val($options, 'behavior.trigger_selector')); ?>"
                        class="regular-text hsh-behavior-dependent" placeholder="#custom-section">
                </div>
            </div>

            <div class="hsh-row hsh-global-dependent">
                <div class="hsh-row-label">
                    <strong>Animación</strong>
                    <p class="description">Efecto visual al aparecer o desaparecer el header.</p>
                </div>
                <div class="hsh-row-control">
                    <select name="hooma_smart_header_settings[behavior][animation]" class="hsh-behavior-dependent regular-text">
                        <option value="fade" <?php selected(_hsh_val($options, 'behavior.animation'), 'fade'); ?>>Fade</option>
                        <option value="slide" <?php selected(_hsh_val($options, 'behavior.animation'), 'slide'); ?>>Slide</option>
                        <option value="none" <?php selected(_hsh_val($options, 'behavior.animation'), 'none'); ?>>Instantáneo</option>
                    </select>
                </div>
            </div>

            <div class="hsh-row hsh-global-dependent">
                <div class="hsh-row-label">
                    <strong>No volver a ocultar</strong>
                    <p class="description">Si está activo, el header se mantendrá visible al volver a subir.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="checkbox" class="hsh-toggle hsh-behavior-dependent" name="hooma_smart_header_settings[behavior][show_once]" value="1"
                        <?php checked(_hsh_val($options, 'behavior.show_once'), '1'); ?>>
                </div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                function hsh_toggle_trigger() {
                    var type = $('#hsh-trigger-type').val();
                    if (type === 'element') {
                        $('#hsh-trigger-distance-row').hide();
                        $('#hsh-trigger-element-row').css('display', 'flex');
                    } else {
                        $('#hsh-trigger-distance-row').css('display', 'flex');
                        $('#hsh-trigger-element-row').hide();
                    }
                }
                
                function hsh_toggle_global() {
                    var active = $('#hsh-hide-on-scroll').is(':checked');
                    if (active) {
                        $('.hsh-global-dependent').css('opacity', '1').css('pointer-events', 'auto');
                        $('.hsh-global-dependent').find('input, select, textarea').prop('disabled', false);
                        // Re-run trigger toggle to ensure correct state
                        hsh_toggle_trigger();
                    } else {
                        $('.hsh-global-dependent').css('opacity', '0.5').css('pointer-events', 'none');
                        $('.hsh-global-dependent').find('input, select, textarea').prop('disabled', true);
                    }
                }

                $('#hsh-trigger-type').change(hsh_toggle_trigger);
                $('#hsh-hide-on-scroll').change(hsh_toggle_global);
                
                // Init
                hsh_toggle_trigger(); 
                hsh_toggle_global();
            });
        </script>
    </form>
</div>

<!-- Device Settings -->
<div class="hooma-sh-section hsh-global-dependent">
    <div class="hooma-sh-section-header">
        <h2>Ajustes por Dispositivos</h2>
        <p class="description">Controla en qué resoluciones se aplica el comportamiento inicial.</p>
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
                <p class="description">El comportamiento inicial no se ejecutará en los dispositivos seleccionados.</p>
            </div>
            <div class="hsh-row-control">
                <input type="hidden" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][sentinel_flags]" value="1">
                <div class="hsh-checkbox-group">
                    <label>
                        <input type="hidden" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][mobile][disable_desktop]" value="0">
                        <input type="checkbox" class="hsh-toggle" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][mobile][disable_desktop]" value="1"
                            <?php checked(_hsh_val($options, 'behavior.mobile.disable_desktop'), '1'); ?>
                            <?php disabled($use_custom_bp, false); ?>>
                        Desktop
                    </label>
                    <label>
                        <input type="hidden" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][mobile][disable_tablet]" value="0">
                        <input type="checkbox" class="hsh-toggle" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][mobile][disable_tablet]" value="1"
                            <?php checked(_hsh_val($options, 'behavior.mobile.disable_tablet', '0'), '1'); ?>
                            <?php disabled($use_custom_bp, false); ?>>
                        Tablet
                    </label>
                    <label>
                        <input type="hidden" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][mobile][disable_mobile]" value="0">
                        <input type="checkbox" class="hsh-toggle" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][mobile][disable_mobile]" value="1"
                            <?php checked(_hsh_val($options, 'behavior.mobile.disable_mobile', '0'), '1'); ?>
                            <?php disabled($use_custom_bp, false); ?>>
                        Mobile
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Display Conditions -->
<div class="hooma-sh-section hsh-global-dependent">
    <div class="hooma-sh-section-header">
        <h2>Condiciones de visualización</h2>
        <p class="description">Determina el alcance de aplicación del comportamiento inicial en tu sitio.</p>
    </div>
    <div class="hooma-sh-content">
        <div class="hsh-row-content">
            <strong style="margin-bottom: 10px; display: block; font-size: 14px;">1. Modo de aplicación</strong>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px;">
                    <input type="radio" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][display_mode]" value="exclude" 
                        <?php checked(_hsh_val($options, 'behavior.display_mode', 'exclude'), 'exclude'); ?>>
                    Funcionar siempre, excepto en…
                </label>
                <label style="display: block;">
                    <input type="radio" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][display_mode]" value="include" 
                        <?php checked(_hsh_val($options, 'behavior.display_mode'), 'include'); ?>>
                    Funcionar solo en…
                </label>
            </div>

            <div class="hsh-sub-card">
                <strong style="margin-bottom: 10px; display: block; font-size: 14px;">2. Tipos de contenido</strong>
                <div class="hsh-checkbox-group" style="margin-bottom: 20px; background: #fff; padding: 10px; border: 1px solid #dcdde1; border-radius: 4px; max-height: 150px; overflow-y: auto;">
                    <?php 
                    $selected_types = _hsh_val($options, 'behavior.display_types', []);
                    $post_types = get_post_types(['public' => true], 'objects');
                    foreach ($post_types as $type): 
                        if ($type->name === 'attachment') continue;
                    ?>
                        <label style="margin-right: 15px;">
                            <input type="checkbox" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][display_types][]" 
                                value="<?php echo esc_attr($type->name); ?>" 
                                <?php checked(in_array($type->name, $selected_types), true); ?>>
                            <?php echo esc_html($type->label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <strong style="margin-bottom: 10px; display: block; font-size: 14px;">3. IDs de entradas o páginas</strong>
                <input type="text" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][display_ids]" 
                    value="<?php echo esc_attr(_hsh_val($options, 'behavior.display_ids')); ?>" 
                    class="regular-text" placeholder="Ej: 12, 45" style="width: 100%; margin-bottom: 20px;">

                <strong style="margin-bottom: 10px; display: block; font-size: 14px;">4. Clase de la tag body</strong>
                <input type="text" form="hooma-sh-form" name="hooma_smart_header_settings[behavior][display_body_classes]" 
                    value="<?php echo esc_attr(_hsh_val($options, 'behavior.display_body_classes')); ?>" 
                    class="regular-text" placeholder="class1, class2" style="width: 100%;">
                <p class="description" style="margin-top: 5px;">Introduce las clases separadas por comas.</p>
            </div>
        </div>
    </div>
</div>

<div style="margin-top: 20px;">
    <button type="submit" form="hooma-sh-form" class="button button-primary button-large">Guardar Comportamiento</button>
    <div id="hsh-build-console" class="hsh-build-console"></div>
</div>