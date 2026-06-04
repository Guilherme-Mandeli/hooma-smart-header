(function($) {
    'use strict';

    $(function() {
        const $btn = $('#hsh-start-build');
        const $spinner = $('#hsh-build-spinner');
        const $console = $('#hsh-build-console');

        $btn.on('click', function() {
            if ($btn.hasClass('disabled')) return;

            if (!confirm('¿Estás seguro de que quieres regenerar la build del frontend?')) {
                return;
            }

            $btn.addClass('disabled').text('Generando...');
            $spinner.addClass('is-active');
            $console.show().html('<div class="status-msg">Enviando solicitud de build...</div>').fadeIn();

            $.ajax({
                url: HoomaSH.ajax_url,
                type: 'POST',
                data: {
                    action: 'hsh_trigger_build',
                    nonce: HoomaSH.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $console.append('<div class="line success-msg">✓ Build completada con éxito.</div>');
                        if (response.data.output) {
                            $console.append('<pre>' + response.data.output + '</pre>');
                        }
                        $console.append('<div class="line status-msg">Proceso finalizado con éxito.</div>');
                        
                        setTimeout(() => {
                           window.location.reload();
                        }, 2500);
                    } else {
                        var errorClass = response.data.fallback ? 'warning-msg' : 'error-msg';
                        var prefix = response.data.fallback ? '⚠ Fallback activado: ' : '✗ Error: ';
                        
                        $console.append('<div class="line ' + errorClass + '">' + prefix + response.data.message + '</div>');
                        if (response.data.output) {
                             $console.append('<pre style="color: #f44747;">' + response.data.output + '</pre>');
                        }
                        $console.append('<div class="line status-msg">Proceso finalizado con advertencias/errores.</div>');
                    }
                    $console.scrollTop($console[0].scrollHeight);
                },
                error: function() {
                    $console.append('<div class="line error-msg">✗ Error de conexión con el servidor.</div>');
                },
                complete: function() {
                    $btn.removeClass('disabled').text('Generar Nueva Build');
                    $spinner.removeClass('is-active');
                }
            });
        });
    });

})(jQuery);
