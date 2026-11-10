jQuery(document).ready(function ($) {
    // Media Uploader Logic
    var mediaUploader;

    // Reactivity & Cascading Previews Function
    function updateCascadePreviews() {
        // --- 1. Initial Logo (Antes) ---
        var initVal = $('#hsh-input-init').val();
        var initTabVal = $('#hsh-input-init-tablet').val();
        var initMobVal = $('#hsh-input-init-mobile').val();

        // Desktop
        if (initVal) {
            $('#hsh-preview-init').attr('src', initVal).show();
            $('#hsh-preview-init').siblings('.hsh-logo-placeholder').hide();
            $('#hsh-preview-init').parent().addClass('has-image');
            $('.hsh-remove-button[data-target="hsh-input-init"]').show();
        } else {
            $('#hsh-preview-init').attr('src', '').hide();
            $('#hsh-preview-init').siblings('.hsh-logo-placeholder').show();
            $('#hsh-preview-init').parent().removeClass('has-image');
            $('.hsh-remove-button[data-target="hsh-input-init"]').hide();
        }

        // Tablet
        var resolvedTabInitSrc = '';
        var isTabInitInherited = false;
        if (initTabVal) {
            resolvedTabInitSrc = initTabVal;
            isTabInitInherited = false;
        } else if (initVal) {
            resolvedTabInitSrc = initVal;
            isTabInitInherited = true;
        }

        if (resolvedTabInitSrc) {
            $('#hsh-preview-init-tablet').attr('src', resolvedTabInitSrc).show();
            $('#hsh-preview-init-tablet').siblings('.hsh-logo-placeholder').hide();
            $('#hsh-preview-init-tablet').parent().addClass('has-image').toggleClass('is-inherited', isTabInitInherited);
            $('#hsh-preview-init-tablet').css('opacity', isTabInitInherited ? '0.4' : '1.0');
            $('#hsh-preview-init-tablet').closest('.hsh-logo-card').find('.hoo-inherited-badge').toggle(isTabInitInherited);
        } else {
            $('#hsh-preview-init-tablet').attr('src', '').hide();
            $('#hsh-preview-init-tablet').siblings('.hsh-logo-placeholder').show();
            $('#hsh-preview-init-tablet').parent().removeClass('has-image is-inherited');
            $('#hsh-preview-init-tablet').closest('.hsh-logo-card').find('.hoo-inherited-badge').hide();
        }
        if (initTabVal) {
            $('.hsh-remove-button[data-target="hsh-input-init-tablet"]').show();
        } else {
            $('.hsh-remove-button[data-target="hsh-input-init-tablet"]').hide();
        }

        // Mobile
        var resolvedMobInitSrc = '';
        var isMobInitInherited = false;
        if (initMobVal) {
            resolvedMobInitSrc = initMobVal;
            isMobInitInherited = false;
        } else if (initTabVal) {
            resolvedMobInitSrc = initTabVal;
            isMobInitInherited = true;
        } else if (initVal) {
            resolvedMobInitSrc = initVal;
            isMobInitInherited = true;
        }

        if (resolvedMobInitSrc) {
            $('#hsh-preview-init-mobile').attr('src', resolvedMobInitSrc).show();
            $('#hsh-preview-init-mobile').siblings('.hsh-logo-placeholder').hide();
            $('#hsh-preview-init-mobile').parent().addClass('has-image').toggleClass('is-inherited', isMobInitInherited);
            $('#hsh-preview-init-mobile').css('opacity', isMobInitInherited ? '0.4' : '1.0');
            $('#hsh-preview-init-mobile').closest('.hsh-logo-card').find('.hoo-inherited-badge').toggle(isMobInitInherited);
        } else {
            $('#hsh-preview-init-mobile').attr('src', '').hide();
            $('#hsh-preview-init-mobile').siblings('.hsh-logo-placeholder').show();
            $('#hsh-preview-init-mobile').parent().removeClass('has-image is-inherited');
            $('#hsh-preview-init-mobile').closest('.hsh-logo-card').find('.hoo-inherited-badge').hide();
        }
        if (initMobVal) {
            $('.hsh-remove-button[data-target="hsh-input-init-mobile"]').show();
        } else {
            $('.hsh-remove-button[data-target="hsh-input-init-mobile"]').hide();
        }

        // --- 2. Alt Logo (Después) ---
        var altVal = $('#hsh-input-alt').val();
        var altTabVal = $('#hsh-input-alt-tablet').val();
        var altMobVal = $('#hsh-input-alt-mobile').val();

        // Desktop
        if (altVal) {
            $('#hsh-preview-alt').attr('src', altVal).show();
            $('#hsh-preview-alt').siblings('.hsh-logo-placeholder').hide();
            $('#hsh-preview-alt').parent().addClass('has-image');
            $('.hsh-remove-button[data-target="hsh-input-alt"]').show();
        } else {
            $('#hsh-preview-alt').attr('src', '').hide();
            $('#hsh-preview-alt').siblings('.hsh-logo-placeholder').show();
            $('#hsh-preview-alt').parent().removeClass('has-image');
            $('.hsh-remove-button[data-target="hsh-input-alt"]').hide();
        }

        // Tablet
        var resolvedTabAltSrc = '';
        var isTabAltInherited = false;
        if (altTabVal) {
            resolvedTabAltSrc = altTabVal;
            isTabAltInherited = false;
        } else if (altVal) {
            resolvedTabAltSrc = altVal;
            isTabAltInherited = true;
        }

        if (resolvedTabAltSrc) {
            $('#hsh-preview-alt-tablet').attr('src', resolvedTabAltSrc).show();
            $('#hsh-preview-alt-tablet').siblings('.hsh-logo-placeholder').hide();
            $('#hsh-preview-alt-tablet').parent().addClass('has-image').toggleClass('is-inherited', isTabAltInherited);
            $('#hsh-preview-alt-tablet').css('opacity', isTabAltInherited ? '0.4' : '1.0');
            $('#hsh-preview-alt-tablet').closest('.hsh-logo-card').find('.hoo-inherited-badge').toggle(isTabAltInherited);
        } else {
            $('#hsh-preview-alt-tablet').attr('src', '').hide();
            $('#hsh-preview-alt-tablet').siblings('.hsh-logo-placeholder').show();
            $('#hsh-preview-alt-tablet').parent().removeClass('has-image is-inherited');
            $('#hsh-preview-alt-tablet').closest('.hsh-logo-card').find('.hoo-inherited-badge').hide();
        }
        if (altTabVal) {
            $('.hsh-remove-button[data-target="hsh-input-alt-tablet"]').show();
        } else {
            $('.hsh-remove-button[data-target="hsh-input-alt-tablet"]').hide();
        }

        // Mobile
        var resolvedMobAltSrc = '';
        var isMobAltInherited = false;
        if (altMobVal) {
            resolvedMobAltSrc = altMobVal;
            isMobAltInherited = false;
        } else if (altTabVal) {
            resolvedMobAltSrc = altTabVal;
            isMobAltInherited = true;
        } else if (altVal) {
            resolvedMobAltSrc = altVal;
            isMobAltInherited = true;
        }

        if (resolvedMobAltSrc) {
            $('#hsh-preview-alt-mobile').attr('src', resolvedMobAltSrc).show();
            $('#hsh-preview-alt-mobile').siblings('.hsh-logo-placeholder').hide();
            $('#hsh-preview-alt-mobile').parent().addClass('has-image').toggleClass('is-inherited', isMobAltInherited);
            $('#hsh-preview-alt-mobile').css('opacity', isMobAltInherited ? '0.4' : '1.0');
            $('#hsh-preview-alt-mobile').closest('.hsh-logo-card').find('.hoo-inherited-badge').toggle(isMobAltInherited);
        } else {
            $('#hsh-preview-alt-mobile').attr('src', '').hide();
            $('#hsh-preview-alt-mobile').siblings('.hsh-logo-placeholder').show();
            $('#hsh-preview-alt-mobile').parent().removeClass('has-image is-inherited');
            $('#hsh-preview-alt-mobile').closest('.hsh-logo-card').find('.hoo-inherited-badge').hide();
        }
        if (altMobVal) {
            $('.hsh-remove-button[data-target="hsh-input-alt-mobile"]').show();
        } else {
            $('.hsh-remove-button[data-target="hsh-input-alt-mobile"]').hide();
        }
    }

    // Media Uploader click handler
    $('.hsh-upload-button').on('click', function (e) {
        e.preventDefault();
        var button = $(this);
        var targetId = button.data('target');

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Seleccionar Logo',
            button: {
                text: 'Usar este Logo'
            },
            multiple: false
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#' + targetId).val(attachment.url);
            updateCascadePreviews();
        });

        mediaUploader.open();
    });

    // Remove button handler
    $('.hsh-remove-button').on('click', function (e) {
        e.preventDefault();
        var targetId = $(this).data('target');

        $('#' + targetId).val('');
        updateCascadePreviews();
    });

    // Initial check on load
    updateCascadePreviews();

    // Logo Switcher Toggle
    var $checkbox = $('input[name="hooma_smart_header_settings[logo_switcher][enabled]"]');
    function toggleLogoSwitcher() {
        var isEnabled = $checkbox.is(':checked');
        $('.hsh-logo-switcher-dependent').toggleClass('hsh-disabled-section', !isEnabled);
    }

    if ($checkbox.length) {
        toggleLogoSwitcher();
        $checkbox.on('change', toggleLogoSwitcher);
    }

    // Detect Width Logic
    $('#hsh-detect-width-btn').on('click', function () {
        var $btn = $(this);
        var $wrap = $btn.closest('.hooma-sh-verify-wrapper');
        var $input = $wrap.find('input');
        var $status = $wrap.find('.hooma-sh-status');

        var selector = $('input[name="hooma_smart_header_settings[selectors][logo]"]').val();
        var urlToVerify = (typeof HoomaSH !== 'undefined' && HoomaSH.home_url) ? HoomaSH.home_url : '/';

        if (!selector) {
            alert('Por favor define un selector primero.');
            return;
        }

        $btn.prop('disabled', true).text('Cargando...');
        $status.text('');

        // Create hidden iframe with desktop dimensions
        var $iframe = $('<iframe id="hsh-detect-frame"></iframe>').css({
            'position': 'fixed',
            'top': '-9999px',
            'left': '0',
            'width': '1280px',
            'height': '800px',
            'visibility': 'hidden',
            'z-index': '-1000'
        });
        $('body').append($iframe);

        $iframe.attr('src', urlToVerify);

        $iframe.on('load', function () {
            try {
                var iframeWin = $iframe[0].contentWindow;
                var iframeDoc = iframeWin.document;
                var el = iframeDoc.querySelector(selector);

                if (el) {
                    var rect = el.getBoundingClientRect();
                    var width = parseFloat(rect.width.toFixed(2));

                    if (width > 0) {
                        $input.val(width);
                        $status.css('color', 'green').text('Visualizado: ' + width + 'px');
                    } else {
                        $status.css('color', 'red').text('Ancho es 0');
                    }
                } else {
                    $status.css('color', 'red').text('Selector no encontrado');
                }

            } catch (e) {
                console.error('HoomaSH Detect Error:', e);
                $status.css('color', 'red').text('Error de acceso (Security/CORS)');
            }

            $btn.prop('disabled', false).text('Detectar');
            $iframe.remove();
        });

        // Timeout safety (10s)
        setTimeout(function () {
            if ($('#hsh-detect-frame').length) {
                $btn.prop('disabled', false).text('Detectar');
                $status.css('color', 'red').text('Timeout');
                $('#hsh-detect-frame').remove();
            }
        }, 10000);
    });
});
