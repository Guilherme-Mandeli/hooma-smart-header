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
});
