<?php

namespace Lithe\Course\Posts\Course;

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
        add_filter('manage_lithe_course_posts_columns', [self::$instance, 'add_enrollment_columns']);
        add_action('manage_lithe_course_posts_custom_column', [self::$instance, 'render_enrollment_columns'], 10, 2);
    }

    /**
     * Add enrollment columns to courses admin
     */
    public function add_enrollment_columns($columns) {
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
    public function render_enrollment_columns($column, $post_id) {
        switch ($column) {
            case 'enrollments':
                echo $this->get_enrollment_count($post_id);
                break;
        }
    }

    /**
     * Get enrollment count for a course
     */
    private function get_enrollment_count($course_id) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
            WHERE meta_key = %s AND meta_value = '1'",
            '_has_access_to_course_' . $course_id
        ));
        
        return $count ? intval($count) : 0;
    }
}

EnrollmentAdmin::init(); 