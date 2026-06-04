jQuery(document).ready(function ($) {
    // Responsive Behavior Specific Logic

    // Toggle Custom Breakpoints
    function hsh_toggle_custom_breakpoints() {
        var isCustom = $('#hsh-use-custom-breakpoints').is(':checked');
        if (isCustom) {
            $('.hsh-custom-breakpoints-group').slideDown();
        } else {
            $('.hsh-custom-breakpoints-group').slideUp();
        }
    }

    $('#hsh-use-custom-breakpoints').on('change', hsh_toggle_custom_breakpoints);

    // Toggle Manual Height
    $('#hsh-height-mode').on('change', function () {
        if ($(this).val() === 'manual') {
            $('#hsh-manual-height').show();
        } else {
            $('#hsh-manual-height').hide();
        }
    }).trigger('change');

    // Toggle Placeholder
    $('#hsh-placeholder-toggle').on('change', function () {
        if ($(this).is(':checked')) {
            $('#hsh-placeholder-options').slideDown();
        } else {
            $('#hsh-placeholder-options').slideUp();
        }
    }).trigger('change');

    // Init Custom Breakpoints
    hsh_toggle_custom_breakpoints();
});
