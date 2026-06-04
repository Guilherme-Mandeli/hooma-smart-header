jQuery(document).ready(function ($) {
    if (typeof HoomaSH === 'undefined') return;

    // Verify Selector Logic
    $('.hooma-sh-verify-btn').on('click', function () {
        var $btn = $(this);
        var $input = $btn.prev('input');
        var $status = $btn.next('.hooma-sh-status');
        var selector = $input.val();
        var urlToVerify = $('#hsh-verify-url').val();

        if (!selector) {
            selector = $input.attr('placeholder');
            if (!selector) return;
        }

        $btn.prop('disabled', true);
        $status.text(HoomaSH.strings.verifying).removeClass('found not-found');

        $.post(HoomaSH.ajax_url, {
            action: 'hooma_sh_verify_selector',
            nonce: HoomaSH.nonce,
            selector: selector,
            url: urlToVerify
        }, function (res) {
            $btn.prop('disabled', false);
            if (res.success) {
                $status.text(HoomaSH.strings.found + ' (' + res.data.count + ')').addClass('found');
            } else {
                $status.text(HoomaSH.strings.not_found).addClass('not-found');
            }
        });
    });

    // Restore Defaults Logic
    $('#hsh-restore-defaults').on('click', function () {
        if (!confirm('¿Restaurar detección automática? Esto sobrescribirá los selectores actuales.')) return;

        var $btn = $(this);
        $btn.prop('disabled', true);

        $.post(HoomaSH.ajax_url, {
            action: 'hooma_sh_restore_defaults',
            nonce: HoomaSH.nonce
        }, function (res) {
            $btn.prop('disabled', false);
            if (res.success) {
                // Fill inputs
                $('input[name="hooma_smart_header_settings[selectors][header]"]').val(res.data.header);
                $('input[name="hooma_smart_header_settings[selectors][sticky]"]').val(res.data.sticky);
                $('input[name="hooma_smart_header_settings[selectors][logo]"]').val(res.data.logo);
                alert('Restaurado correctamente. Guarda los cambios para aplicar.');
            }
        });
    });

    // AJAX Save and Build Logic
    $('#hooma-sh-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $submitBtn = $form.find('input[type="submit"]');
        var $console = $('#hsh-build-console');
        
        // Ensure console exists and is visible
        if (!$console.length) {
            $form.after('<div id="hsh-build-console" class="hsh-build-console"></div>');
            $console = $('#hsh-build-console');
        }
        
        $console.show().html('<div class="status-msg">Iniciando proceso...</div>');
        $submitBtn.prop('disabled', true).val('Guardando...');

        $console.append('<div class="line">1. Guardando configuración...</div>');

        // Step 1: Save Settings via AJAX
        $.post(HoomaSH.ajax_url, {
            action: 'hsh_save_settings',
            nonce: HoomaSH.nonce,
            settings: $form.serialize()
        }, function (res) {
            if (res.success) {
                $console.append('<div class="line success-msg">✓ Configuración guardada correctamente.</div>');
                
                // Step 2: Trigger Build
                $console.append('<div class="line">2. Ejecutando verificación y build...</div>');
                
                $.post(HoomaSH.ajax_url, {
                    action: 'hsh_trigger_build',
                    nonce: HoomaSH.nonce
                }, function (buildRes) {
                    $submitBtn.prop('disabled', false).val('Guardar cambios');
                    
                    if (buildRes.success) {
                        $console.append('<div class="line success-msg">✓ Build completada con éxito.</div>');
                        $console.append('<pre>' + buildRes.data.output + '</pre>');
                        $console.append('<div class="line status-msg">Proceso finalizado con éxito.</div>');
                    } else {
                        var errorClass = buildRes.data.fallback ? 'warning-msg' : 'error-msg';
                        var prefix = buildRes.data.fallback ? '⚠ Fallback activado: ' : '✗ Error: ';
                        
                        $console.append('<div class="line ' + errorClass + '">' + prefix + buildRes.data.message + '</div>');
                        if (buildRes.data.output) {
                            $console.append('<pre style="color: #f44747;">' + buildRes.data.output + '</pre>');
                        }
                        $console.append('<div class="line status-msg">Proceso finalizado con advertencias/errores.</div>');
                    }
                    
                    // Auto-scroll to bottom of console
                    $console.scrollTop($console[0].scrollHeight);
                });

            } else {
                $submitBtn.prop('disabled', false).val('Guardar cambios');
                $console.append('<div class="line error-msg">✗ Error al guardar: ' + res.data + '</div>');
            }
        }).fail(function() {
            $submitBtn.prop('disabled', false).val('Guardar cambios');
            $console.append('<div class="line error-msg">✗ Error de conexión con el servidor.</div>');
        });
    });
});
