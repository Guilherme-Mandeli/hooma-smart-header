<?php

namespace HoomaModules\HoomaSmartHeader\Services;

class ConditionEvaluator
{
    /**
     * Checks if display conditions are met.
     *
     * @param array $settings {
     *     @type string $display_mode 'include' or 'exclude'
     *     @type array  $display_types Array of post types
     *     @type string $display_ids String of comma-separated IDs
     * }
     * @return bool True if behavior should run/show, false otherwise.
     */
    public static function check($settings)
    {
        $mode = isset($settings['display_mode']) && in_array($settings['display_mode'], ['include', 'exclude'])
            ? $settings['display_mode']
            : 'exclude';

        $types = isset($settings['display_types']) ? (array) $settings['display_types'] : [];
        $ids_str = isset($settings['display_ids']) ? $settings['display_ids'] : '';
        $body_classes_str = isset($settings['display_body_classes']) ? $settings['display_body_classes'] : '';

        // Convert IDs string to array of integers
        $ids = array_filter(array_map('intval', explode(',', $ids_str)));

        // Convert body classes string to array
        $body_classes = array_filter(array_map('trim', explode(',', $body_classes_str)));

        // Normalize current state
        $current_id = 0;
        $current_type = '';

        if (is_singular()) {
            $current_id = get_the_ID();
            $current_type = get_post_type();
        } elseif (is_front_page()) {
            $current_id = get_option('page_on_front');
            $current_type = 'page';
        }

        $match = false;

        // 1. Check if ID matches (Specific priority)
        if (!empty($ids) && in_array($current_id, $ids)) {
            $match = true;
        }

        // 2. Check if Type matches
        if (!$match && !empty($types) && in_array($current_type, $types)) {
            $match = true;
        }

        // 3. Check if Body Class matches
        if (!$match && !empty($body_classes)) {
            $current_body_classes = get_body_class();
            foreach ($body_classes as $class) {
                if (in_array($class, $current_body_classes)) {
                    $match = true;
                    break;
                }
            }
        }

        $result = true;
        if ($mode === 'include') {
            $result = $match;
        } else {
            $result = !$match;
        }

        return $result;
    }
}
