<?php

namespace Lithe\Course\Posts\Course;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Enrollment {
    private static $instance = null;
    private $api_namespace = 'lithecourse/v1';

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        // Register REST API routes
        add_action('rest_api_init', [self::$instance, 'lithecourse_register_rest_routes']);
        
        // Content restriction filter
        add_filter('the_content', [self::$instance, 'lithecourse_restrict_lesson_content']);
        

    }

    /**
     * Get course type from meta field
     * 
     * @param int $course_id Course ID
     * @return string Course type (public, free, paid)
     */
    public static function lithecourse_get_course_type($course_id) {
        $course_type = get_post_meta($course_id, '_course_type', true);
        
        // Default to 'free' if not set for backward compatibility
        if (empty($course_type)) {
            return 'free';
        }
        
        return $course_type;
    }

    /**
     * Check if a user has access to a course
     * 
     * @param int $user_id User ID (optional, defaults to current user)
     * @param int $course_id Course ID
     * @return bool Has access
     */
    public static function lithecourse_user_has_course_access($user_id = null, $course_id = null) {
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
     * Get the first lesson URL for a course
     * 
     * @param int $course_id Course ID
     * @return string|null First lesson URL or null if no lessons
     */
    public static function lithecourse_get_first_lesson_url($course_id) {
        // Get the first module for this course (ordered by menu_order)
        $first_module = get_posts([
            'post_type' => 'lithecourse_module',
            'posts_per_page' => 1,
            'meta_key' => '_parent_course_id',
            'meta_value' => $course_id,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ]);
        
        if (empty($first_module)) {
            return null;
        }
        
        // Get the first lesson from the first module (ordered by menu_order)
        $first_lesson = get_posts([
            'post_type' => 'lithecourse_lesson',
            'posts_per_page' => 1,
            'meta_key' => '_parent_module_id',
            'meta_value' => $first_module[0]->ID,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ]);
        
        if (empty($first_lesson)) {
            return null;
        }
        
        return get_permalink($first_lesson[0]->ID);
    }

    /**
     * Get enrollment button HTML for a course
     * 
     * @param int $course_id Course ID
     * @return string HTML button
     */
    public static function lithecourse_get_enrollment_button_html($course_id) {
        $course_type = self::lithecourse_get_course_type($course_id);
        
        // For public courses, show a "View Course" or "Start Learning" button
        if ($course_type === 'public') {
            $first_lesson_url = self::lithecourse_get_first_lesson_url($course_id);
            
            if ($first_lesson_url) {
                return sprintf(
                    '<a href="%s" class="wp-block-button__link wp-element-button lithecourse-start-learning-button">%s</a>',
                    esc_url($first_lesson_url),
                    __('Start Learning', 'lithe-course')
                );
            } else {
                // No lessons found, link to course content section
                return sprintf(
                    '<a href="#course-content" class="wp-block-button__link wp-element-button lithecourse-view-course-button">%s</a>',
                    __('View Course', 'lithe-course')
                );
            }
        }
        
        // For free and paid courses, check login status first
        if (!is_user_logged_in()) {
            return sprintf(
                '<a href="%s" class="wp-block-button__link wp-element-button lithecourse-login-button">%s</a>',
                esc_url(wp_login_url(get_permalink($course_id))),
                __('Log in to Enroll', 'lithe-course')
            );
        }

        $user_id = get_current_user_id();
        
        // Check if user is enrolled in this course
        if (self::lithecourse_user_has_course_access($user_id, $course_id)) {
            // User is already enrolled - show Continue Learning button
            $first_lesson_url = self::lithecourse_get_first_lesson_url($course_id);
            
            if ($first_lesson_url) {
                return sprintf(
                    '<a href="%s" class="wp-block-button__link wp-element-button lithecourse-continue-button">%s</a>',
                    esc_url($first_lesson_url),
                    __('Continue Learning', 'lithe-course')
                );
            } else {
                // No lessons found, link to course content section
                return sprintf(
                    '<a href="#course-content" class="wp-block-button__link wp-element-button lithecourse-continue-button">%s</a>',
                    __('Continue Learning', 'lithe-course')
                );
            }
        }

        // User is not enrolled, show appropriate enrollment button based on course type
        if ($course_type === 'free') {
            // Free course - show Enroll button
            return sprintf(
                '<a href="#" class="wp-block-button__link wp-element-button lithecourse-enroll-button" data-course="%d">%s</a>',
                $course_id,
                __('Enroll Now', 'lithe-course')
            );
        } else if ($course_type === 'paid') {
            // Paid course - check for linked product
            $product_id = get_post_meta($course_id, '_linked_product_id', true);
            
            if (empty($product_id) || !function_exists('wc_get_product')) {
                // No product linked or WooCommerce not active
                if (current_user_can('manage_options')) {
                    // Admin sees configuration issue
                    $edit_link = admin_url('post.php?post=' . $course_id . '&action=edit');
                    return sprintf(
                        '<div class="lithecourse-configuration-notice">%s <a href="%s">%s</a></div>',
                        __('Please link a product to this course.', 'lithe-course'),
                        esc_url($edit_link),
                        __('Edit Course', 'lithe-course')
                    );
                }
                return '';
            }
            
            $product = wc_get_product($product_id);
            
            if (!$product || !$product->is_purchasable()) {
                // Product exists but is not purchasable
                if (current_user_can('manage_options')) {
                    // Admin sees configuration issue
                    return sprintf(
                        '<div class="lithecourse-configuration-notice">%s</div>',
                        __('Product exists but is not purchasable.', 'lithe-course')
                    );
                }
                return '';
            }
            
            // Valid product, show buy button
            $checkout_url = add_query_arg('add-to-cart', $product_id, wc_get_checkout_url());
            
            return sprintf(
                '<a href="%s" class="wp-block-button__link wp-element-button lithecourse-buy-now-button">%s</a>',
                esc_url($checkout_url),
                __('Buy Course', 'lithe-course')
            );
        }
        
        // Fallback - should not reach here
        return '';
    }

    /**
     * Register REST API routes
     */
    public function lithecourse_register_rest_routes() {
        register_rest_route($this->api_namespace, '/enroll/(?P<course_id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'lithecourse_handle_enrollment_endpoint'],
            'permission_callback' => [$this, 'lithecourse_enrollment_permission_check'],
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
            'callback' => [$this, 'lithecourse_handle_unenrollment_endpoint'],
            'permission_callback' => [$this, 'lithecourse_admin_permission_check'],
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
            'callback' => [$this, 'lithecourse_get_enrolled_students_endpoint'],
            'permission_callback' => [$this, 'lithecourse_admin_permission_check'],
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
    public function lithecourse_enrollment_permission_check($request) {
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
    public function lithecourse_handle_enrollment_endpoint($request) {
        $course_id = $request->get_param('course_id');
        $user_id = get_current_user_id();

        // Check if course is free
        if (!$this->lithecourse_is_free_course($course_id)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('This is a paid course and requires purchase', 'lithe-course')
            ], 400);
        }

        // Attempt to enroll user
        if ($this->lithecourse_enroll_user($user_id, $course_id)) {
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
     * Check if a course is free
     * 
     * @param int $course_id Course ID
     * @return bool Is free
     */
    public function lithecourse_is_free_course($course_id) {
        $course_type = self::lithecourse_get_course_type($course_id);
        
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
    public function lithecourse_enroll_user($user_id, $course_id) {
        // Check if already enrolled
        if (self::lithecourse_user_has_course_access($user_id, $course_id)) {
            return true;
        }

        // Grant access by updating user meta
        return update_user_meta($user_id, '_has_access_to_course_' . $course_id, true);
    }

    /**
     * Restrict content based on enrollment
     * 
     * @param string $content Post content
     * @return string Modified content
     */
    public function lithecourse_restrict_lesson_content($content) {
        // Only apply restrictions to lesson pages
        if (!is_singular('lithecourse_lesson')) {
            return $content;
        }
        
        $lesson_id = get_the_ID();
        $course_id = get_post_meta($lesson_id, '_parent_course_id', true);
        
        // If no course ID found, allow access
        if (!$course_id) {
            return $content;
        }
        
        // Get course type
        $course_type = self::lithecourse_get_course_type($course_id);
        
        // Public courses - no restrictions, everyone can access
        if ($course_type === 'public') {
            return $content;
        }
        
        // For free and paid courses, check if user is logged in
        if (!is_user_logged_in()) {
            $post_title = get_the_title();
            $login_url = wp_login_url(get_permalink());
            
            return sprintf(
                '<div class="lithecourse-access-message">
                    <h3>%s</h3>
                    <p>%s</p>
                    <a href="%s" class="lithecourse-login-button">%s</a>
                </div>',
                __('Login Required', 'lithe-course'),
                /* translators: %s: lesson title */
                sprintf(__('Please log in to view this lesson: "%s"', 'lithe-course'), $post_title),
                esc_url($login_url),
                __('Log In', 'lithe-course')
            );
        }
        
        // Check if logged-in user has access to the course
        if (self::lithecourse_user_has_course_access(null, $course_id)) {
            return $content;
        }
        
        // User doesn't have access, show enrollment message
        return $this->lithecourse_get_enrollment_message($course_id);
    }

    /**
     * Get enrollment message for a course
     * 
     * @param int $course_id Course ID
     * @return string HTML message
     */
    private function lithecourse_get_enrollment_message($course_id) {
        $course_title = get_the_title($course_id);
        $course_url = get_permalink($course_id);
        $course_type = self::lithecourse_get_course_type($course_id);
        
        if (!is_user_logged_in()) {
            return sprintf(
                '<div class="lithecourse-access-message">
                    <h3>%s</h3>
                    <p>%s</p>
                    <a href="%s" class="wp-block-button__link wp-element-button">%s</a>
                </div>',
                __('Login Required', 'lithe-course'),
                /* translators: %s: course title */
                sprintf(__('This lesson is part of the course "%s". Please log in to access this content.', 'lithe-course'), $course_title),
                esc_url(wp_login_url($course_url)),
                __('Log In', 'lithe-course')
            );
        }
        
        // User is logged in but not enrolled - direct them to course page
        $message_title = $course_type === 'paid' ? __('Purchase Required', 'lithe-course') : __('Enrollment Required', 'lithe-course');
        $action_text = $course_type === 'paid' ? __('View Course & Purchase', 'lithe-course') : __('Go to Course & Enroll', 'lithe-course');
        
        return sprintf(
            '<div class="lithecourse-access-message">
                <h3>%s</h3>
                <p>%s</p>
                <a href="%s" class="wp-block-button__link wp-element-button">%s</a>
            </div>',
            $message_title,
            /* translators: %s: course title */
            sprintf(__('This lesson is part of the course "%s". Please visit the course page to enroll.', 'lithe-course'), $course_title),
            esc_url($course_url),
            $action_text
        );
    }



    /**
     * Check if user has admin permission
     *
     * @param \WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function lithecourse_admin_permission_check($request) {
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
    public function lithecourse_handle_unenrollment_endpoint($request) {
        $course_id = $request->get_param('course_id');
        $user_id = $request->get_param('user_id');
        
        // Get user and course data for the response
        $user = get_userdata($user_id);
        $course = get_post($course_id);
        
        if (!$user || !$course || $course->post_type !== 'lithecourse_course') {
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
    public function lithecourse_get_enrolled_students_endpoint($request) {
        $course_id = $request->get_param('course_id');
        
        // Verify course exists
        $course = get_post($course_id);
        if (!$course || $course->post_type !== 'lithecourse_course') {
            return new \WP_Error(
                'invalid_course',
                __('Invalid course.', 'lithe-course'),
                ['status' => 400]
            );
        }
        
        // Get enrolled users
        $enrolled_users = $this->lithecourse_get_enrolled_users($course_id);
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
    private function lithecourse_get_enrolled_users($course_id) {
        global $wpdb;
        
        // Check cache first
        $cache_key = "lithecourse_enrolled_users_{$course_id}";
        $users = wp_cache_get($cache_key, 'lithe-course');
        
        if (false === $users) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom enrolled users query, result is cached below
            $users = $wpdb->get_results($wpdb->prepare(
                "SELECT u.* FROM {$wpdb->users} u
                JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                WHERE um.meta_key = %s AND um.meta_value = '1'",
                '_has_access_to_course_' . $course_id
            ));
            
            // Cache the result for 5 minutes
            wp_cache_set($cache_key, $users, 'lithe-course', 300);
        }
        
        return $users ? $users : [];
    }
}

Enrollment::init();
