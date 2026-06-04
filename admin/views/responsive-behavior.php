<?php
defined('HOOMA_PATH') || exit;

$options = get_option('hooma_smart_header_settings');

// Default Breakpoints from ThemeDetector
$detector_defaults = [
    'mobile_breakpoint' => 767,
    'tablet_breakpoint' => 980
];

try {
    if (class_exists('\\HoomaModules\\HoomaSmartHeader\\Services\\ThemeDetector')) {
        $detector_defaults = \HoomaModules\HoomaSmartHeader\Services\ThemeDetector::detect();
    }
} catch (\Exception $e) {
}

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

<form method="post" action="options.php" id="hooma-sh-form">
    <?php settings_fields('hooma_smart_header_group'); ?>
    <input type="hidden" name="hooma_smart_header_settings[mobile][sentinel_breakpoints]" value="1">

    <!-- Block 1: Breakpoints -->
    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Puntos de Interrupción</h2>
            <p class="description">Define los rangos para Mobile y Tablet. Se detectan automáticamente valores óptimos según tu tema.</p>
        </div>
        <div class="hooma-sh-content">
            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Activar puntos de interrupción</strong>
                    <p class="description">Habilitar configuración manual de breakpoints.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="hidden" name="hooma_smart_header_settings[mobile][use_custom_breakpoints]" value="0">
                    <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[mobile][use_custom_breakpoints]"
                        id="hsh-use-custom-breakpoints" value="1"
                        <?php checked(_hsh_val($options, 'mobile.use_custom_breakpoints'), '1'); ?>>
                </div>
            </div>

            <div class="hsh-row hsh-breakpoint-row">
                <div class="hsh-row-label">
                    <strong>Mobile Breakpoint (px)</strong>
                    <p class="description">Dispositivos con ancho menor o igual a este valor se consideran Mobile.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="number" name="hooma_smart_header_settings[mobile][breakpoint]"
                        value="<?php echo esc_attr(_hsh_val($options, 'mobile.breakpoint', $detector_defaults['mobile_breakpoint'])); ?>"
                        class="small-text"> px
                </div>
            </div>

            <div class="hsh-row hsh-breakpoint-row">
                <div class="hsh-row-label">
                    <strong>Tablet Breakpoint (px)</strong>
                    <p class="description">Dispositivos con ancho menor o igual a este valor (y mayor que Mobile) se consideran Tablet.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="number" name="hooma_smart_header_settings[mobile][tablet_breakpoint]"
                        value="<?php echo esc_attr(_hsh_val($options, 'mobile.tablet_breakpoint', $detector_defaults['tablet_breakpoint'])); ?>"
                        class="small-text"> px
                </div>
            </div>
        </div>
    </div>

    <!-- Block 2: Force Header Fixed -->
    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Forzar Header Fixed</h2>
            <p class="description">Fuerza la posición <code>fixed</code> ignorando cualquier otro comportamiento de scroll.</p>
        </div>
        <div class="hooma-sh-content">
            <?php 
                $use_custom_bp = _hsh_val($options, 'mobile.use_custom_breakpoints') === '1';
            ?>

            <?php if (!$use_custom_bp): ?>
                <div class="hsh-row">
                    <div class="notice notice-warning inline" style="margin: 0; width: 100%;">
                        <p>Para control granular por dispositivo, habilita los <strong>puntos de interrupción</strong> arriba.</p>
                    </div>
                </div>
                <div class="hsh-row">
                    <div class="hsh-row-label">
                        <strong>Forzar Globalmente</strong>
                        <p class="description">Forzar Fixed en todas las resoluciones.</p>
                    </div>
                    <div class="hsh-row-control">
                        <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[layout][force_fixed_global]" value="1"
                            <?php checked(_hsh_val($options, 'layout.force_fixed_global'), '1'); ?>>
                    </div>
                </div>
            <?php else: ?>
                <div class="hsh-row">
                    <div class="hsh-row-label">
                        <strong>Forzar por Dispositivo</strong>
                    </div>
                    <div class="hsh-row-control">
                        <div class="hsh-checkbox-group">
                            <label>
                                <input type="hidden" name="hooma_smart_header_settings[layout][force_fixed_desktop]" value="0">
                                <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[layout][force_fixed_desktop]" value="1"
                                    <?php checked(_hsh_val($options, 'layout.force_fixed_desktop'), '1'); ?>>
                                Desktop (> <?php echo esc_html(_hsh_val($options, 'mobile.tablet_breakpoint', $detector_defaults['tablet_breakpoint'])); ?>px)
                            </label>
                            <label>
                                <input type="hidden" name="hooma_smart_header_settings[layout][force_fixed_tablet]" value="0">
                                <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[layout][force_fixed_tablet]" value="1"
                                    <?php checked(_hsh_val($options, 'layout.force_fixed_tablet'), '1'); ?>>
                                Tablet (<?php echo esc_html(_hsh_val($options, 'mobile.breakpoint', $detector_defaults['mobile_breakpoint']) + 1); ?>px - <?php echo esc_html(_hsh_val($options, 'mobile.tablet_breakpoint', $detector_defaults['tablet_breakpoint'])); ?>px)
                            </label>
                            <label>
                                <input type="hidden" name="hooma_smart_header_settings[layout][force_fixed_mobile]" value="0">
                                <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[layout][force_fixed_mobile]" value="1"
                                    <?php checked(_hsh_val($options, 'layout.force_fixed_mobile'), '1'); ?>>
                                Mobile (≤ <?php echo esc_html(_hsh_val($options, 'mobile.breakpoint', $detector_defaults['mobile_breakpoint'])); ?>px)
                            </label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Block 3: Layout Compensation -->
    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Compensación de Diseño</h2>
        </div>
        <div class="hooma-sh-content">
            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Placeholder (Push Down)</strong>
                    <p class="description">Crea espacio simulado superior insertando un elemento invisible para evitar que el header cubra el contenido.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[layout][placeholder]" id="hsh-layout-placeholder" value="1"
                        <?php checked(_hsh_val($options, 'layout.placeholder'), '1'); ?>>
                </div>
            </div>

            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Target de Margen Negativo</strong>
                    <p class="description">Selector para aplicar margen negativo (Pull Up). Útil para superponer el header al contenido.</p>
                </div>
                <div class="hsh-row-control">
                    <input type="text" name="hooma_smart_header_settings[layout][target]" id="hsh-layout-target"
                        value="<?php echo esc_attr(_hsh_val($options, 'layout.target')); ?>" class="regular-text"
                        placeholder="#main-content">
                </div>
            </div>

            <!-- Pull Up Conditions -->
            <div class="hsh-row">
                <div class="hsh-row-content" style="width: 100%;">
                    <div style="background: #fcfcfc; border: 1px solid #dcdde1; padding: 20px; border-radius: 4px;">
                        <?php 
                            $pull_opts = isset($options['layout']) ? $options['layout'] : [];
                        ?>
                        
                        <!-- Device Settings Accordion -->
                        <details style="margin-bottom: 10px;">
                            <summary style="cursor: pointer; font-weight: 600;">Ajustes de Dispositivos (Margen Negativo)</summary>
                            <div style="padding: 15px 0 0 0;">
                                <?php if (!$use_custom_bp): ?>
                                    <div class="notice notice-warning inline" style="margin-bottom: 15px;">
                                        <p>Para ajustar, habilita <a href="#" onclick="jQuery('#hsh-use-custom-breakpoints').focus(); return false;">puntos de interrupción</a> arriba.</p>
                                    </div>
                                <?php endif; ?>

                                <div class="hsh-checkbox-group" style="<?php echo !$use_custom_bp ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">
                                    <label>
                                        <input type="hidden" name="hooma_smart_header_settings[layout][pull_up_disable_desktop]" value="0">
                                        <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[layout][pull_up_disable_desktop]" value="1"
                                            <?php checked(_hsh_val($pull_opts, 'pull_up_disable_desktop'), '1'); ?>
                                            <?php disabled($use_custom_bp, false); ?>>
                                        Desactivar en Desktop
                                    </label>
                                    <label>
                                        <input type="hidden" name="hooma_smart_header_settings[layout][pull_up_disable_tablet]" value="0">
                                        <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[layout][pull_up_disable_tablet]" value="1"
                                            <?php checked(_hsh_val($pull_opts, 'pull_up_disable_tablet'), '1'); ?>
                                            <?php disabled($use_custom_bp, false); ?>>
                                        Desactivar en Tablet
                                    </label>
                                    <label>
                                        <input type="hidden" name="hooma_smart_header_settings[layout][pull_up_disable_mobile]" value="0">
                                        <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[layout][pull_up_disable_mobile]" value="1"
                                            <?php checked(_hsh_val($pull_opts, 'pull_up_disable_mobile'), '1'); ?>
                                            <?php disabled($use_custom_bp, false); ?>>
                                        Desactivar en Mobile
                                    </label>
                                </div>
                            </div>
                        </details>

                        <hr style="border-top: 1px solid #dcdde1; margin: 15px 0;">

                        <!-- Display Conditions Accordion -->
                        <details>
                            <summary style="cursor: pointer; font-weight: 600;">Condiciones de visualización (Margen Negativo)</summary>
                            <div style="padding: 15px 0 0 0;">
                                <div style="margin-bottom: 10px;">
                                    <label style="margin-right: 15px;">
                                        <input type="radio" name="hooma_smart_header_settings[layout][pull_up_display_mode]" value="exclude" 
                                            <?php checked(_hsh_val($pull_opts, 'pull_up_display_mode', 'exclude'), 'exclude'); ?>>
                                        Funcionar siempre, excepto en…
                                    </label>
                                    <label>
                                        <input type="radio" name="hooma_smart_header_settings[layout][pull_up_display_mode]" value="include" 
                                            <?php checked(_hsh_val($pull_opts, 'pull_up_display_mode'), 'include'); ?>>
                                        Funcionar solo en…
                                    </label>
                                </div>

                                <div style="background: #fff; border: 1px solid #dcdde1; padding: 10px; border-radius: 4px; margin-bottom: 10px; max-height: 150px; overflow-y: auto;">
                                    <strong style="display: block; margin-bottom: 5px;">Tipos de contenido:</strong>
                                    <?php 
                                    $selected_types = _hsh_val($pull_opts, 'pull_up_display_types', []);
                                    $post_types = get_post_types(['public' => true], 'objects');
                                    foreach ($post_types as $type): 
                                        if ($type->name === 'attachment') continue;
                                    ?>
                                        <label style="display: block; margin-bottom: 3px;">
                                            <input type="checkbox" class="hsh-toggle" name="hooma_smart_header_settings[layout][pull_up_display_types][]" 
                                                value="<?php echo esc_attr($type->name); ?>" 
                                                <?php checked(in_array($type->name, $selected_types), true); ?>>
                                            <?php echo esc_html($type->label); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div style="margin-bottom: 10px;">
                                    <strong style="display: block; margin-bottom: 5px;">IDs de entradas/páginas:</strong>
                                    <input type="text" name="hooma_smart_header_settings[layout][pull_up_display_ids]" 
                                        value="<?php echo esc_attr(_hsh_val($pull_opts, 'pull_up_display_ids')); ?>" 
                                        class="regular-text" placeholder="Ej: 12, 45" style="width: 100%;">
                                </div>

                                <div>
                                    <strong style="display: block; margin-bottom: 5px;">Clase de la tag body:</strong>
                                    <input type="text" name="hooma_smart_header_settings[layout][pull_up_display_body_classes]" 
                                        value="<?php echo esc_attr(_hsh_val($pull_opts, 'pull_up_display_body_classes')); ?>" 
                                        class="regular-text" placeholder="class1, class2" style="width: 100%;">
                                    <p class="description">Introduce las clases separadas por comas.</p>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </div>

            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Cálculo de Altura</strong>
                </div>
                <div class="hsh-row-control">
                    <select name="hooma_smart_header_settings[layout][height_mode]" id="hsh-lc-height-mode" class="regular-text">
                        <option value="auto" <?php selected(_hsh_val($options, 'layout.height_mode'), 'auto'); ?>
                            >Automático (Altura del Header)</option>
                        <option value="manual" <?php selected(_hsh_val($options, 'layout.height_mode'), 'manual'); ?>>Manual (PX)</option>
                    </select>
                </div>
            </div>

            <div class="hsh-row" id="hsh-lc-manual-val-row" style="display: none;">
                <div class="hsh-row-label">
                    <strong>Valor Manual (px)</strong>
                </div>
                <div class="hsh-row-control">
                    <input type="number" name="hooma_smart_header_settings[layout][height_val]"
                        value="<?php echo esc_attr(_hsh_val($options, 'layout.height_val', 60)); ?>"
                        class="small-text"> px
                </div>
            </div>

            <div class="hsh-row hsh-lc-backup-val-row align-top" style="display: none;">
                <div class="hsh-row-label">
                    <strong>Altura de Respaldo (px)</strong>
                    <p class="description">Altura estimada para la carga inicial. Si dejas Tablet/Mobile vacíos, heredarán el valor superior.</p>
                </div>
                <div class="hsh-row-control is-column">
                    <div style="margin-bottom: 5px;">
                        <strong style="display:inline-block; width: 60px;">Desktop:</strong>
                        <input type="number" name="hooma_smart_header_settings[layout][backup_height]"
                            value="<?php echo esc_attr(_hsh_val($options, 'layout.backup_height', 60)); ?>"
                            class="small-text" step="0.01"> px
                    </div>
                    <div class="hsh-responsive-backup-fields" style="margin-bottom: 5px; opacity: <?php echo $use_custom_bp ? '1' : '0.5'; ?>;">
                        <strong style="display:inline-block; width: 60px;">Tablet:</strong>
                        <input type="number" name="hooma_smart_header_settings[layout][backup_height_tablet]"
                            value="<?php echo esc_attr(_hsh_val($options, 'layout.backup_height_tablet', '')); ?>"
                            class="small-text" step="0.01" <?php disabled($use_custom_bp, false); ?>> px
                    </div>
                    <div class="hsh-responsive-backup-fields" style="margin-bottom: 5px; opacity: <?php echo $use_custom_bp ? '1' : '0.5'; ?>;">
                        <strong style="display:inline-block; width: 60px;">Mobile:</strong>
                        <input type="number" name="hooma_smart_header_settings[layout][backup_height_mobile]"
                            value="<?php echo esc_attr(_hsh_val($options, 'layout.backup_height_mobile', '')); ?>"
                            class="small-text" step="0.01" <?php disabled($use_custom_bp, false); ?>> px
                    </div>
                </div>
            </div>

            <div class="hsh-row hsh-lc-backup-offset-row align-top" style="display: none;">
                <div class="hsh-row-label">
                    <strong>Altura Offset Top Header (px)</strong>
                    <p class="description">Altura del Top Header. Si dejas Tablet/Mobile vacíos, heredarán el valor superior.</p>
                </div>
                <div class="hsh-row-control is-column">
                    <div style="margin-bottom: 5px;">
                        <strong style="display:inline-block; width: 60px;">Desktop:</strong>
                        <input type="number" name="hooma_smart_header_settings[layout][backup_offset_height]"
                            value="<?php echo esc_attr(_hsh_val($options, 'layout.backup_offset_height', 0)); ?>"
                            class="small-text" step="0.01"> px
                    </div>
                    <div class="hsh-responsive-backup-fields" style="margin-bottom: 5px; opacity: <?php echo $use_custom_bp ? '1' : '0.5'; ?>;">
                        <strong style="display:inline-block; width: 60px;">Tablet:</strong>
                        <input type="number" name="hooma_smart_header_settings[layout][backup_offset_height_tablet]"
                            value="<?php echo esc_attr(_hsh_val($options, 'layout.backup_offset_height_tablet', '')); ?>"
                            class="small-text" step="0.01" <?php disabled($use_custom_bp, false); ?>> px
                    </div>
                    <div class="hsh-responsive-backup-fields" style="margin-bottom: 5px; opacity: <?php echo $use_custom_bp ? '1' : '0.5'; ?>;">
                        <strong style="display:inline-block; width: 60px;">Mobile:</strong>
                        <input type="number" name="hooma_smart_header_settings[layout][backup_offset_height_mobile]"
                            value="<?php echo esc_attr(_hsh_val($options, 'layout.backup_offset_height_mobile', '')); ?>"
                            class="small-text" step="0.01" <?php disabled($use_custom_bp, false); ?>> px
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <button type="submit" class="button button-primary button-large">Guardar Ajustes Responsive</button>
        <div id="hsh-build-console" class="hsh-build-console"></div>
    </div>
</form>

<script>
    jQuery(document).ready(function ($) {
        function toggleLC() {
            var hMode = $('#hsh-lc-height-mode').val();
            if (hMode === 'manual') {
                $('#hsh-lc-manual-val-row').css('display', 'flex');
                $('.hsh-lc-backup-val-row').hide();
                $('.hsh-lc-backup-offset-row').hide();
            } else {
                $('#hsh-lc-manual-val-row').hide();
                $('.hsh-lc-backup-val-row').css('display', 'flex');
                $('.hsh-lc-backup-offset-row').css('display', 'flex');
            }
        }
        $('#hsh-lc-height-mode').on('change', toggleLC);
        toggleLC();

        function toggleBreakpoints() {
            if ($('#hsh-use-custom-breakpoints').is(':checked')) {
                $('.hsh-breakpoint-row').find('input').prop('disabled', false);
                $('.hsh-breakpoint-row').css('opacity', '1');
                $('.hsh-responsive-backup-fields').find('input').prop('disabled', false);
                $('.hsh-responsive-backup-fields').css('opacity', '1');
            } else {
                $('.hsh-breakpoint-row').find('input').prop('disabled', true);
                $('.hsh-breakpoint-row').css('opacity', '0.5');
                $('.hsh-responsive-backup-fields').find('input').prop('disabled', true);
                $('.hsh-responsive-backup-fields').css('opacity', '0.5');
            }
        }
        $('#hsh-use-custom-breakpoints').on('change', toggleBreakpoints);
        toggleBreakpoints();

        // Range value display
        $('input[type="range"]').on('input', function () {
            $(this).next('.range-val').text($(this).val() + 'px');
        });

        // Cascade Placeholder Display
        function updateCascadePlaceholders() {
            // Backup Height
            var bhDesk = $('input[name="hooma_smart_header_settings[layout][backup_height]"]').val();
            var bhTablet = $('input[name="hooma_smart_header_settings[layout][backup_height_tablet]"]').val();
            
            var phTablet = bhDesk ? bhDesk : '0';
            $('input[name="hooma_smart_header_settings[layout][backup_height_tablet]"]').attr('placeholder', phTablet);
            
            var phMobile = bhTablet ? bhTablet : phTablet;
            $('input[name="hooma_smart_header_settings[layout][backup_height_mobile]"]').attr('placeholder', phMobile);

            // Backup Offset Height
            var btDesk = $('input[name="hooma_smart_header_settings[layout][backup_offset_height]"]').val();
            var btTablet = $('input[name="hooma_smart_header_settings[layout][backup_offset_height_tablet]"]').val();
            
            var ptTablet = btDesk ? btDesk : '0';
            $('input[name="hooma_smart_header_settings[layout][backup_offset_height_tablet]"]').attr('placeholder', ptTablet);
            
            var ptMobile = btTablet ? btTablet : ptTablet;
            $('input[name="hooma_smart_header_settings[layout][backup_offset_height_mobile]"]').attr('placeholder', ptMobile);
        }

        $('.hsh-lc-backup-val-row input, .hsh-lc-backup-offset-row input').on('input', updateCascadePlaceholders);
        updateCascadePlaceholders();

        // Enforce mutual exclusion between placeholder and negative margin target (pull up)
        function enforceLayoutCompensationExclusion() {
            var isPlaceholder = $('#hsh-layout-placeholder').is(':checked');
            if (isPlaceholder) {
                $('#hsh-layout-target').val('').prop('disabled', true);
                $('#hsh-layout-target').closest('.hsh-row').css('opacity', '0.5');
            } else {
                $('#hsh-layout-target').prop('disabled', false);
                $('#hsh-layout-target').closest('.hsh-row').css('opacity', '1');
            }
        }
        $('#hsh-layout-placeholder').on('change', enforceLayoutCompensationExclusion);
        enforceLayoutCompensationExclusion();
    });
</script>