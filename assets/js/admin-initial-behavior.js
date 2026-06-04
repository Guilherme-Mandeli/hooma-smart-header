jQuery(document).ready(function ($) {
    // Initial Behavior Logic

    // Toggle Trigger Type
    function hsh_toggle_trigger() {
        var type = $('#hsh-trigger-type').val();
        if (type === 'element') {
            $('#hsh-trigger-distance-row').hide();
            $('#hsh-trigger-element-row').show();
        } else {
            $('#hsh-trigger-distance-row').show();
            $('#hsh-trigger-element-row').hide();
        }
    }

    // Toggle Global Enable
    function hsh_toggle_global() {
        var active = $('#hsh-hide-on-scroll').is(':checked');
        if (active) {
            $('.hsh-global-dependent').css('opacity', '1').css('pointer-events', 'auto');
            $('.hsh-global-dependent').find('input, select, textarea').prop('disabled', false);
            // Re-run trigger toggle to ensure correct state
            hsh_toggle_trigger();
        } else {
            $('.hsh-global-dependent').css('opacity', '0.5').css('pointer-events', 'none');
            // Do not disable inputs, otherwise they won't post. Just visual disable?
            // Actually traditionally disabled inputs don't post.
            // But we need to save the state if they uncheck the master toggle?
            // Usually if disabled, settings are lost or not sent.
            // Let's rely on CSS mostly, but enable/disable prevents interacting.
            // If strictly disabled, 'required' fields might be bypassed, but we don't have many.
            $('.hsh-global-dependent').find('input, select, textarea').prop('disabled', true);
        }
    }

    $('#hsh-trigger-type').on('change', hsh_toggle_trigger);
    $('#hsh-hide-on-scroll').on('change', hsh_toggle_global);

    // Init
    hsh_toggle_trigger();
    hsh_toggle_global();
});
