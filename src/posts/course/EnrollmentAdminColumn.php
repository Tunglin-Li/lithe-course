<?php

namespace Lithe\Course\Posts\Course;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Handles the admin interface for course enrollments
 */
class EnrollmentAdmin {
    private static $instance = null;

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        // Add enrollment columns to course admin
        add_filter('manage_lithe_course_posts_columns', [self::$instance, 'lithe_course_add_enrollment_columns']);
        add_action('manage_lithe_course_posts_custom_column', [self::$instance, 'lithe_course_render_enrollment_columns'], 10, 2);
    }

    /**
     * Add enrollment columns to courses admin
     */
    public function lithe_course_add_enrollment_columns($columns) {
        $new_columns = [];
        
        // Insert columns after title
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['enrollments'] = __('Enrollments', 'lithe-course');
            }
        }
        
        return $new_columns;
    }

    /**
     * Render enrollment column content
     */
    public function lithe_course_render_enrollment_columns($column, $post_id) {
        switch ($column) {
            case 'enrollments':
                echo esc_html($this->lithe_course_get_enrollment_count($post_id));
                break;
        }
    }

    /**
     * Get enrollment count for a course
     */
    private function lithe_course_get_enrollment_count($course_id) {
        global $wpdb;
        
        // Check cache first
        $cache_key = "lithe_course_enrollment_count_{$course_id}";
        $count = wp_cache_get($cache_key, 'lithe_course');
        
        if (false === $count) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom enrollment count query, result is cached below
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} 
                WHERE meta_key = %s AND meta_value = '1'",
                '_has_access_to_course_' . $course_id
            ));
            
            // Cache the result for 5 minutes
            wp_cache_set($cache_key, $count, 'lithe_course', 300);
        }
        
        return $count ? intval($count) : 0;
    }
}

EnrollmentAdmin::init(); 