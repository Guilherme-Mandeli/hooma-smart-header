jQuery(document).ready(function ($) {
    // Admin Conditions Module
    // Handles Device Settings (checkboxes) and Display Conditions (radio/checkbox/inputs)
    // Expects specific classes: .hsh-conditions-container, .hsh-display-mode-radio, .hsh-display-types

    // Currently, typical usage is direct binding or re-usable functions.
    // If we want to use this in multiple tabs, we should use classes relative to a container.

    function initConditions($container) {
        if (!$container.length) return;

        // Toggle Display Mode (Include/Exclude)
        $container.find('input[name*="[display_mode]"]').on('change', function () {
            // Logic dependent on mode? Currently UI doesn't hide anything based on mode, 
            // but maybe in future.
            // For now just visually updates if needed.
        });

        // Ensure "Include Specific IDs" allows typing
        // Nothing special needed unless validation required.
    }

    // Init for any container present on page
    $('.hsh-conditions-wrapper').each(function () {
        initConditions($(this));
    });

    // Also, handle the specific structure from existing views if they don't use wrapper yet.
    // Ideally update views to add wrapper class.
});
