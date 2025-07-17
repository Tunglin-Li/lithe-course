<?php

namespace Lithe\Course\Posts\Course;

class Enrollment {
    private static $instance = null;
    private $api_namespace = 'lithe-course/v1';

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        // Register REST API routes
        add_action('rest_api_init', [self::$instance, 'register_rest_routes']);
        
        // Content restriction filter
        add_filter('the_content', [self::$instance, 'restrict_lesson_content']);
        
        // Enqueue enrollment script
        add_action('wp_enqueue_scripts', [self::$instance, 'enqueue_scripts']);
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route($this->api_namespace, '/enroll/(?P<course_id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_enrollment_endpoint'],
            'permission_callback' => [$this, 'enrollment_permission_check'],
            'args' => [
                'course_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);
        
        // Register unenroll endpoint
        register_rest_route($this->api_namespace, '/unenroll', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_unenrollment_endpoint'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'user_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'course_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);
        
        // Register get enrolled students endpoint
        register_rest_route($this->api_namespace, '/course/(?P<course_id>\d+)/students', [
            'methods' => 'GET',
            'callback' => [$this, 'get_enrolled_students_endpoint'],
            'permission_callback' => [$this, 'admin_permission_check'],
            'args' => [
                'course_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);
    }

    /**
     * Check if user has permission to enroll
     *
     * @param \WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function enrollment_permission_check($request) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return new \WP_Error(
                'rest_forbidden',
                __('You must be logged in to enroll in courses.', 'lithe-course'),
                ['status' => 401]
            );
        }

        // Verify nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error(
                'rest_forbidden',
                __('Invalid security token.', 'lithe-course'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Handle REST API enrollment endpoint
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function handle_enrollment_endpoint($request) {
        $course_id = $request->get_param('course_id');
        $user_id = get_current_user_id();

        // Check if course is free
        if (!$this->is_free_course($course_id)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('This is a paid course and requires purchase', 'lithe-course')
            ], 400);
        }

        // Attempt to enroll user
        if ($this->enroll_user($user_id, $course_id)) {
            return new \WP_REST_Response([
                'success' => true,
                'message' => __('Successfully enrolled', 'lithe-course')
            ], 200);
        }


        return new \WP_REST_Response([
            'success' => false,
            'message' => __('Enrollment failed', 'lithe-course')
        ], 500);
    }

    /**
     * Get course type
     * 
     * @param int $course_id Course ID
     * @return string Course type (public, free, paid)
     */
    public function get_course_type($course_id) {
        $course_type = get_post_meta($course_id, '_course_type', true);
        
        // Default to 'free' if not set for backward compatibility
        if (empty($course_type)) {
            return 'free';
        }
        
        return $course_type;
    }

    /**
     * Check if a course is free
     * 
     * @param int $course_id Course ID
     * @return bool Is free
     */
    public function is_free_course($course_id) {
        $course_type = $this->get_course_type($course_id);
        
        // Public and free courses are considered "free" for enrollment purposes
        if (in_array($course_type, ['public', 'free'])) {
            return true;
        }
        
        // For paid courses, check if there's a linked product
        if ($course_type === 'paid') {
            $product_id = get_post_meta($course_id, '_linked_product_id', true);
            
            // If no linked product, it's effectively free
            if (empty($product_id)) {
                return true;
            }
            
            // Check if WooCommerce is active
            if (!function_exists('wc_get_product')) {
                // If WooCommerce is not active, consider it free
  
                return true;
            }
            
            // Get the WooCommerce product
            $product = wc_get_product($product_id);
            
            // If product doesn't exist or is not purchasable, consider it free
            if (!$product || !$product->is_purchasable()) {
                return true;
            }
            
            // Get the product price
            $price = $product->get_price();
            
            // If price is not set or is 0, it's a free course
            return empty($price) || floatval($price) <= 0;
        }
        
        return false;
    }

    /**
     * Enroll a user in a course
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @return bool Success
     */
    public function enroll_user($user_id, $course_id) {
        // Check if already enrolled
        if ($this->user_has_course_access($user_id, $course_id)) {
            return true;
        }

        // Grant access by updating user meta
        return update_user_meta($user_id, '_has_access_to_course_' . $course_id, true);
    }

    /**
     * Check if a user has access to a course
     * 
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $course_id Course ID
     * @return bool Has access
     */
    public function user_has_course_access($user_id = null, $course_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        if (!$course_id) {
            $course_id = get_the_ID();
        }

        // Check user meta for course access
        return (bool) get_user_meta($user_id, '_has_access_to_course_' . $course_id, true);
    }

    /**
     * Restrict content based on enrollment
     * 
     * @param string $content Post content
     * @return string Modified content
     */
    public function restrict_lesson_content($content) {
        // Only apply restrictions to lesson pages
        if (!is_singular('lithe_lesson')) {
            return $content;
        }
        
        $lesson_id = get_the_ID();
        $course_id = get_post_meta($lesson_id, '_parent_course_id', true);
        
        // If no course ID found, allow access
        if (!$course_id) {
            return $content;
        }
        
        // Get course type
        $course_type = $this->get_course_type($course_id);
        
        // Public courses - no restrictions, everyone can access
        if ($course_type === 'public') {
            return $content;
        }
        
        // For free and paid courses, check if user is logged in
        if (!is_user_logged_in()) {
            $post_title = get_the_title();
            $login_url = wp_login_url(get_permalink());
            
            return sprintf(
                '<div class="lithe-course-access-message">
                    <h3>%s</h3>
                    <p>%s</p>
                    <a href="%s" class="wpaa-login-button">%s</a>
                </div>',
                __('Login Required', 'lithe-course'),
                /* translators: %s: lesson title */
                sprintf(__('Please log in to view this lesson: "%s"', 'lithe-course'), $post_title),
                esc_url($login_url),
                __('Log In', 'lithe-course')
            );
        }
        
        // Check if logged-in user has access to the course
        if ($this->user_has_course_access(null, $course_id)) {
            return $content;
        }
        
        // User doesn't have access, show enrollment message
        return $this->get_enrollment_message($course_id);
    }

    /**
     * Get enrollment message for a course
     * 
     * @param int $course_id Course ID
     * @return string HTML message
     */
    private function get_enrollment_message($course_id) {
        $is_free = $this->is_free_course($course_id);
        $course_title = get_the_title($course_id);
        $course_url = get_permalink($course_id);
        
        if ($is_free) {
            // For free courses
            if (!is_user_logged_in()) {
                return sprintf(
                    '<div class="lithe-course-access-message">
                        <h3>%s</h3>
                        <p>%s</p>
                        <a href="%s" class="wpaa-login-button">%s</a>
                    </div>',
                    __('Access Restricted', 'lithe-course'),
                    /* translators: %s: course title */
                    sprintf(__('This lesson is part of the course "%s". Please log in to enroll.', 'lithe-course'), $course_title),
                    esc_url(wp_login_url(get_permalink())),
                    __('Log In', 'lithe-course')
                );
            } else {
                // Generate enrollment button directly
                $enroll_button = sprintf(
                    '<button class="wp-block-button__link wp-element-button wpaa-enroll-button" data-course="%d">%s</button>',
                    $course_id,
                    __('Enroll Now', 'lithe-course')
                );
                
                return sprintf(
                    '<div class="lithe-course-access-message">
                        <h3>%s</h3>
                        <p>%s</p>
                        <a href="%s" class="lithe-course-link">%s</a>
                        %s
                    </div>',
                    __('Enrollment Required', 'lithe-course'),
                    /* translators: %s: course title */
                    sprintf(__('This lesson is part of the course "%s". Please enroll to view this content.', 'lithe-course'), $course_title),
                    esc_url($course_url),
                    __('Go to Course', 'lithe-course'),
                    $enroll_button
                );
            }
        }
        
        // For paid courses (placeholder for future implementation)
        return sprintf(
            '<div class="lithe-course-access-message">
                <h3>%s</h3>
                <p>%s</p>
                <a href="%s" class="lithe-course-link">%s</a>
            </div>',
            __('Paid Course', 'lithe-course'),
            /* translators: %s: course title */
            sprintf(__('This lesson is part of the paid course "%s".', 'lithe-course'), $course_title),
            esc_url($course_url),
            __('View Course Details', 'lithe-course')
        );
    }

    /**
     * Enqueue enrollment scripts
     */
    public function enqueue_scripts() {
        global $post;
        
        // Check if the page contains our shortcode
        $has_shortcode = false;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wpaa_test_auto_enrollment')) {
            $has_shortcode = true;
        }
        
        // Only enqueue on course and lesson pages or pages with our shortcode
        if (!$has_shortcode && !is_singular(['lithe_course', 'lithe_lesson'])) {
            return;
        }

        wp_enqueue_style(
            'wpaa-enrollment',
            LITHE_COURSE_PLUGIN_URL . 'assets/css/enrollment.css',
            [],
            LITHE_COURSE_VERSION
        );
        
        wp_enqueue_script(
            'wpaa-enrollment',
            LITHE_COURSE_PLUGIN_URL . 'assets/js/enrollment.js',
            ['jquery'],
            LITHE_COURSE_VERSION,
            true
        );
        
        // Pass REST API URL instead of AJAX URL
        wp_localize_script('wpaa-enrollment', 'wpaaEnrollment', [
            'apiUrl' => rest_url($this->api_namespace),
            'nonce' => wp_create_nonce('wp_rest'),
            'debug_info' => [
                'plugin_url' => LITHE_COURSE_PLUGIN_URL,
                'site_url' => site_url(),
                'admin_url' => admin_url(),
            ]
        ]);
    }

    /**
     * Check if user has admin permission
     *
     * @param \WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function admin_permission_check($request) {
        // Check if user is logged in and is admin
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return new \WP_Error(
                'rest_forbidden',
                __('You must be an administrator to perform this action.', 'lithe-course'),
                ['status' => 403]
            );
        }

        // Verify nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error(
                'rest_forbidden',
                __('Invalid security token.', 'lithe-course'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Handle REST API unenrollment endpoint
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function handle_unenrollment_endpoint($request) {
        $course_id = $request->get_param('course_id');
        $user_id = $request->get_param('user_id');
        
        // Get user and course data for the response
        $user = get_userdata($user_id);
        $course = get_post($course_id);
        
        if (!$user || !$course || $course->post_type !== 'lithe_course') {
            return new \WP_Error(
                'invalid_data',
                __('Invalid user or course.', 'lithe-course'),
                ['status' => 400]
            );
        }
        
        // Unenroll the user from the course
        $result = delete_user_meta($user_id, '_has_access_to_course_' . $course_id);
        
        if ($result) {
            return new \WP_REST_Response([
                'success' => true,
                'message' =>
                    sprintf(
                        /* translators: %1$s: user display name, %2$s: course title */ 
                        __('User "%1$s" has been unenrolled from course "%2$s".', 'lithe-course'),
                        $user->display_name,
                        $course->post_title
                    )
            ], 200);
        }
        
        return new \WP_Error(
            'unenrollment_failed',
            __('Failed to unenroll user from the course.', 'lithe-course'),
            ['status' => 500]
        );
    }
    
    /**
     * Handle REST API get enrolled students endpoint
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_enrolled_students_endpoint($request) {
        $course_id = $request->get_param('course_id');
        
        // Verify course exists
        $course = get_post($course_id);
        if (!$course || $course->post_type !== 'lithe_course') {
            return new \WP_Error(
                'invalid_course',
                __('Invalid course.', 'lithe-course'),
                ['status' => 400]
            );
        }
        
        // Get enrolled users
        $enrolled_users = $this->get_enrolled_users($course_id);
        $enrollment_count = count($enrolled_users);
        
        // Format users data for response
        $users_data = [];
        foreach ($enrolled_users as $user) {
            $users_data[] = [
                'id' => $user->ID,
                'display_name' => $user->display_name,
                'user_email' => $user->user_email,
            ];
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => [
                'course_id' => $course_id,
                'course_title' => $course->post_title,
                'enrollment_count' => $enrollment_count,
                'enrolled_users' => $users_data,
            ]
        ], 200);
    }
    
    /**
     * Get enrolled users for a course
     * 
     * @param int $course_id Course ID
     * @return array Enrolled users
     */
    private function get_enrolled_users($course_id) {
        global $wpdb;
        
        // Check cache first
        $cache_key = "lithe_course_enrolled_users_{$course_id}";
        $users = wp_cache_get($cache_key, 'lithe_course');
        
        if (false === $users) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom enrolled users query, result is cached below
            $users = $wpdb->get_results($wpdb->prepare(
                "SELECT u.* FROM {$wpdb->users} u
                JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                WHERE um.meta_key = %s AND um.meta_value = '1'",
                '_has_access_to_course_' . $course_id
            ));
            
            // Cache the result for 5 minutes
            wp_cache_set($cache_key, $users, 'lithe_course', 300);
        }
        
        return $users ? $users : [];
    }
}

Enrollment::init();
