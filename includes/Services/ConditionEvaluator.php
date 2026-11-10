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
     *     @type string $display_body_classes String of comma-separated body classes
     * }
     * @param array|null $current_classes Optional current body classes to avoid recursive filter loops.
     * @return bool True if behavior should run/show, false otherwise.
     */
    public static function check($settings, $current_classes = null)
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
            if (get_option('show_on_front') === 'page') {
                $current_id = (int) get_option('page_on_front');
                $current_type = 'page';
            } else {
                $current_id = 0;
                $current_type = 'home';
            }
        } elseif (is_home()) {
            $current_id = (int) get_option('page_for_posts');
            $current_type = 'page';
        } elseif (is_post_type_archive()) {
            $current_type = get_query_var('post_type');
            if (is_array($current_type)) {
                $current_type = reset($current_type);
            }
        } elseif (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            if ($term && isset($term->term_id)) {
                $current_id = (int) $term->term_id;
            }
            $current_type = 'archive';
        }

        $match = false;

        // 1. Check if ID matches (Specific priority)
        if (!empty($ids) && in_array($current_id, $ids, true)) {
            $match = true;
        }

        // 2. Check if Type matches
        if (!$match && !empty($types) && in_array($current_type, $types, true)) {
            $match = true;
        }

        // 3. Check if Body Class matches safely (preventing recursive filter calls)
        if (!$match && !empty($body_classes)) {
            $classes_to_check = [];
            if (is_array($current_classes)) {
                $classes_to_check = $current_classes;
            } elseif (!doing_filter('body_class') && function_exists('get_body_class')) {
                $classes_to_check = get_body_class();
            }

            if (!empty($classes_to_check)) {
                foreach ($body_classes as $class) {
                    if (in_array($class, $classes_to_check, true)) {
                        $match = true;
                        break;
                    }
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
