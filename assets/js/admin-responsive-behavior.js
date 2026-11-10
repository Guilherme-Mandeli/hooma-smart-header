jQuery(document).ready(function ($) {
    // Height calculation mode toggle
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

    // Custom breakpoints toggle
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

    // Cascade Backup Height Placeholders
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

    // Enforce mutual exclusion between placeholder and negative margin target (pull up) without destructive clearing
    function enforceLayoutCompensationExclusion() {
        var isPlaceholder = $('#hsh-layout-placeholder').is(':checked');
        if (isPlaceholder) {
            $('#hsh-layout-target').prop('disabled', true);
            $('#hsh-layout-target').closest('.hsh-row').css('opacity', '0.5');
        } else {
            $('#hsh-layout-target').prop('disabled', false);
            $('#hsh-layout-target').closest('.hsh-row').css('opacity', '1');
        }
    }
    $('#hsh-layout-placeholder').on('change', enforceLayoutCompensationExclusion);
    enforceLayoutCompensationExclusion();
});
