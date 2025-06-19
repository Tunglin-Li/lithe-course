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
        
        // Add meta box for course enrollments
        add_action('add_meta_boxes', [self::$instance, 'add_enrollment_meta_box']);
        
        // Enqueue admin script for enrollment management
        add_action('admin_enqueue_scripts', [self::$instance, 'enqueue_admin_scripts']);
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

    /**
     * Add meta box for course enrollments
     */
    public function add_enrollment_meta_box() {
        add_meta_box(
            'lithe_course_enrollments',
            __('Course Enrollments', 'lithe-course'),
            [$this, 'render_enrollment_meta_box'],
            'lithe_course',
            'normal',
            'default'
        );
    }

    /**
     * Render enrollment meta box
     */
    public function render_enrollment_meta_box($post) {
        $course_id = $post->ID;
        $enrollment_count = $this->get_enrollment_count($course_id);
        $enrolled_users = $this->get_enrolled_users($course_id);
        
        echo '<div class="wpaa-admin-enrollments">';
        
        echo '<div class="wpaa-admin-enrollment-count">';
        echo '<p>' . sprintf(
            _n(
                'There is <strong>%d user</strong> enrolled in this course.',
                'There are <strong>%d users</strong> enrolled in this course.',
                $enrollment_count,
                'lithe-course'
            ),
            $enrollment_count
        ) . '</p>';
        echo '</div>';
        
        if (!empty($enrolled_users)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>' . __('User', 'lithe-course') . '</th>';
            echo '<th>' . __('Email', 'lithe-course') . '</th>';
            echo '<th>' . __('Enrolled On', 'lithe-course') . '</th>';
            echo '<th>' . __('Actions', 'lithe-course') . '</th>';
            echo '</tr></thead>';
            
            echo '<tbody>';
            foreach ($enrolled_users as $user) {
                echo '<tr>';
                echo '<td>' . esc_html($user->display_name) . ' (#' . $user->ID . ')</td>';
                echo '<td>' . esc_html($user->user_email) . '</td>';
                echo '<td>' . __('N/A', 'lithe-course') . '</td>'; // We don't store enrollment date yet
                echo '<td>';
                echo '<a href="#" class="button unenroll-user" data-user="' . $user->ID . '" data-course="' . $course_id . '">';
                echo __('Unenroll', 'lithe-course');
                echo '</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            
            echo '</table>';
        } else {
            echo '<p>' . __('No users are enrolled in this course yet.', 'lithe-course') . '</p>';
        }
        
        echo '</div>';
    }

    /**
     * Get enrolled users for a course
     */
    private function get_enrolled_users($course_id) {
        global $wpdb;
        
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT u.* FROM {$wpdb->users} u
            JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            WHERE um.meta_key = %s AND um.meta_value = '1'",
            '_has_access_to_course_' . $course_id
        ));
        
        return $users;
    }

    /**
     * Enqueue admin scripts for enrollment management
     */
    public function enqueue_admin_scripts($hook) {
        global $post;
        
        // Only enqueue on course edit page
        if ($hook !== 'post.php' || !$post || get_post_type($post) !== 'lithe_course') {
            return;
        }
        
        wp_enqueue_script(
            'wpaa-enrollment',
            LITHE_COURSE_PLUGIN_URL . 'assets/js/enrollment.js',
            ['jquery'],
            LITHE_COURSE_VERSION,
            true
        );
        
        wp_localize_script('wpaa-enrollment', 'wpaaEnrollment', [
            'apiUrl' => esc_url_raw(rest_url('lithe-course/v1')),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }
}

EnrollmentAdmin::init(); 