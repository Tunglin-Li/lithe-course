<?php

namespace Lithe\Course\Blocks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Handle query modifications for the My Course block
 */
class MyCourseQuery {
    private static $instance = null;

    /**
     * Initialize the class and set up hooks
     */
    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        add_filter('pre_render_block', [self::$instance, 'lithecourse_my_courses_pre_render_block'], 10, 2);
        add_filter('rest_lithecourse_query', [self::$instance, 'lithecourse_rest_lithecourse_query'], 10, 2);
    }

    /**
     * Filter to modify the query block to show only enrolled courses
     *
     * @param mixed $pre_render Pre-rendered content
     * @param array $parsed_block The block being rendered
     * @return mixed The original pre_render value
     */
    public function lithecourse_my_courses_pre_render_block($pre_render, $parsed_block) {
        // Verify it's the correct block by checking the namespace
        if (!empty($parsed_block['attrs']['namespace']) && 'lithecourse-my-course-list' === $parsed_block['attrs']['namespace']) {
    
            add_filter('query_loop_block_query_vars', function ($query, $block) {
                
    
                $current_user_id = get_current_user_id();
                if ($current_user_id) {
                    global $wpdb;
    
                    // Check cache first
                    $cache_key = "lithecourse_user_access_{$current_user_id}";
                    $course_ids = wp_cache_get($cache_key, 'lithe-course');
    
                    if (false === $course_ids) {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom user course access query, result is cached below
                        $course_ids = $wpdb->get_col(
                            $wpdb->prepare(
                                "SELECT meta_key FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s",
                                $current_user_id,
                                '_has_access_to_course_%'
                            )
                        );
                        
                        // Cache the result for 5 minutes
                        wp_cache_set($cache_key, $course_ids, 'lithe-course', 300);
                    }
    
                    if (!empty($course_ids)) {
                        // Extract course IDs from meta_key (e.g., '_has_access_to_course_123' → '123')
                        $course_ids = array_map(function ($meta_key) {
                            return str_replace('_has_access_to_course_', '', $meta_key);
                        }, $course_ids);
    
                        // Modify query to filter only the courses the user has access to
                        $query['post__in'] = $course_ids;
                    } else {
                        // If no courses are found, return an empty query
                        $query['post__in'] = [0];
                    }
                }
                $query['order'] = 'DESC';
    
                return $query;
            }, 10, 2);
        }
    
        return $pre_render;
    }

    /**
     * Filter REST API queries for enrolled courses in the block editor
     * 
     * This ensures that in the block editor, the preview shows only enrolled courses
     *
     * @param array $query Current query args
     * @param \WP_REST_Request $request The REST API request
     * @return array Modified query args
     */
    public function lithecourse_rest_lithecourse_query($query, $request) {

        // grab value from the request
        $enrolledFilter = $request['filterByEnrolled'];

        if ($enrolledFilter) {
            $current_user_id = get_current_user_id();
            if ($current_user_id) {
                global $wpdb;

                // Check cache first
                $cache_key = "lithecourse_user_access_{$current_user_id}";
                $course_ids = wp_cache_get($cache_key, 'lithe-course');

                if (false === $course_ids) {
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom user course access query, result is cached below
                    $course_ids = $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT meta_key FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s",
                            $current_user_id,
                            '_has_access_to_course_%'
                        )
                    );
                    
                    // Cache the result for 5 minutes
                    wp_cache_set($cache_key, $course_ids, 'lithe-course', 300);
                }

                if (!empty($course_ids)) {
                    // Extract course IDs from meta_key (e.g., '_has_access_to_course_123' → '123')
                    $course_ids = array_map(function ($meta_key) {
                        return str_replace('_has_access_to_course_', '', $meta_key);
                    }, $course_ids);

                    // Modify query to filter only the courses the user has access to
                    $query['post__in'] = $course_ids;
                } else {
                    // If no courses are found, return an empty query
                    $query['post__in'] = [0];
                }
            }
                
            $query['order'] = 'ASC';
            }
        return $query;
    }
}

// Initialize the class
MyCourseQuery::init();
