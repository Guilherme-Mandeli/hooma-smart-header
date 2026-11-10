jQuery(document).ready(function ($) {
    // Scroll Behavior Toggle
    function hsh_toggle_sb_global() {
        var active = $('#hsh-sb-enabled').is(':checked');
        $('.hsh-sb-dependent').toggleClass('hsh-disabled-section', !active);
    }

    if ($('#hsh-sb-enabled').length) {
        $('#hsh-sb-enabled').on('change', hsh_toggle_sb_global);
        hsh_toggle_sb_global();
    }
});
