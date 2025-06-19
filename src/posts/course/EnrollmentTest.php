<?php

namespace Lithe\Course\Posts\Course;

/**
 * This is a test class for debugging the enrollment functionality.
 * Not intended for production use.
 */
class EnrollmentTest {
    private static $instance = null;

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        // Add test shortcode for displaying enrollment information
        add_shortcode('wpaa_test_enrollment', [self::$instance, 'test_shortcode']);
        
        // Add debug AJAX actions
        add_action('wp_ajax_test_is_free_course', [self::$instance, 'test_is_free_course']);
        add_action('wp_ajax_test_ping', [self::$instance, 'test_ping_ajax']);
        add_action('wp_ajax_nopriv_test_ping', [self::$instance, 'test_ping_ajax']);
    }
    
    /**
     * Test AJAX endpoint for debugging is_free_course logic
     */
    public function test_is_free_course() {
        if (!isset($_REQUEST['course_id'])) {
            wp_send_json_error('No course ID provided');
        }
        
        $course_id = intval($_REQUEST['course_id']);
        $enrollment = new Enrollment();
        
        // Check product linkage
        $product_id = get_post_meta($course_id, '_linked_product_id', true);
        $product = $product_id ? wc_get_product($product_id) : null;
        
        $debug_info = [
            'course_id' => $course_id,
            'product_id' => $product_id,
            'product_exists' => !empty($product),
            'product_purchasable' => $product ? $product->is_purchasable() : null,
            'product_price' => $product ? $product->get_price() : null,
            'is_free' => $enrollment->is_free_course($course_id),
            'wc_function_exists' => function_exists('wc_get_product'),
        ];
        
        wp_send_json_success($debug_info);
    }

    /**
     * Get enrollment button for a course
     * 
     * @param int $course_id Course ID
     * @param bool $is_free Whether the course is free
     * @param bool $is_enrolled Whether the user is already enrolled
     * @return string HTML button
     */
    private function get_enrollment_button($course_id, $is_free, $is_enrolled) {
        if (!is_user_logged_in()) {
            return sprintf(
                '<a href="%s" class="wp-block-button__link wp-element-button wpaa-login-button">%s</a>',
                esc_url(wp_login_url(get_permalink($course_id))),
                __('Log in to Enroll', 'lithe-course')
            );
        }

        if ($is_enrolled) {
            // User is already enrolled - show Continue Learning button
            // Get the first lesson to link to
            $lessons = get_posts([
                'post_type' => 'lithe_lesson',
                'posts_per_page' => 1,
                'meta_key' => '_parent_course_id',
                'meta_value' => $course_id,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            ]);
            
            if (!empty($lessons)) {
                $first_lesson_id = $lessons[0]->ID;
                $first_lesson_url = get_permalink($first_lesson_id);
                
                return sprintf(
                    '<a href="%s" class="wp-block-button__link wp-element-button wpaa-continue-button">%s</a>',
                    esc_url($first_lesson_url),
                    __('Continue Learning', 'lithe-course')
                );
            } else {
                // No lessons found, link to course content section
                return sprintf(
                    '<a href="#course-content" class="wp-block-button__link wp-element-button wpaa-continue-button">%s</a>',
                    __('Continue Learning', 'lithe-course')
                );
            }
        }

        if ($is_free) {
            // Free course - show Enroll button
            return sprintf(
                '<button class="wp-block-button__link wp-element-button wpaa-enroll-button" data-course="%d">%s</button>',
                $course_id,
                __('Enroll Now', 'lithe-course')
            );
        } else {
            // Paid course - show Buy button if product exists
            $product_id = get_post_meta($course_id, '_linked_product_id', true);
            
            if (empty($product_id) || !function_exists('wc_get_product')) {
                // No product linked or WooCommerce not active
                if (current_user_can('manage_options')) {
                    // Admin sees configuration issue
                    $edit_link = admin_url('post.php?post=' . $course_id . '&action=edit');
                    return sprintf(
                        '<div class="wpaa-configuration-notice">%s <a href="%s">%s</a></div>',
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
                        '<div class="wpaa-configuration-notice">%s</div>',
                        __('Product exists but is not purchasable.', 'lithe-course')
                    );
                }
                return '';
            }
            
            // Valid product, show buy button
            $checkout_url = add_query_arg('add-to-cart', $product_id, wc_get_checkout_url());
            
            return sprintf(
                '<a href="%s" class="wp-block-button__link wp-element-button wpaa-buy-now-button">%s</a>',
                esc_url($checkout_url),
                __('Buy Course', 'lithe-course')
            );
        }
    }

    /**
     * Test shortcode to display enrollment information
     */
    public function test_shortcode($atts) {
        $atts = shortcode_atts([
            'course_id' => null,
        ], $atts);

        $course_id = $atts['course_id'] ? intval($atts['course_id']) : get_the_ID();
        
        if (!$course_id || get_post_type($course_id) !== 'lithe_course') {
            return '<p>Please provide a valid course ID or use on a course page.</p>';
        }

        $enrollment = new Enrollment();
        $user_id = get_current_user_id();
        $is_enrolled = $user_id ? $enrollment->user_has_course_access($user_id, $course_id) : false;
        $is_free = $enrollment->is_free_course($course_id);
        
        // Get linked product information
        $product_id = get_post_meta($course_id, '_linked_product_id', true);
        $formatted_price = __('Free', 'lithe-course');
        $product_info = __('No linked product', 'lithe-course');
        
        if (!empty($product_id)) {
            $product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;
            if ($product) {
                $formatted_price = $product->get_price_html();
                $product_info = sprintf(
                    '%s (#%d)',
                    $product->get_name(),
                    $product_id
                );
            } else {
                $product_info = sprintf(__('Product #%d not found', 'lithe-course'), $product_id);
            }
        }

        $output = '<div class="wpaa-enrollment-test">';
        $output .= '<h3>Enrollment Test Information</h3>';
        $output .= '<ul>';
        $output .= '<li><strong>Course ID:</strong> ' . $course_id . '</li>';
        $output .= '<li><strong>Linked Product:</strong> ' . $product_info . '</li>';
        $output .= '<li><strong>Price:</strong> ' . $formatted_price . '</li>';
        $output .= '<li><strong>Course Type:</strong> ' . ($is_free ? 'Free' : 'Paid') . '</li>';
        $output .= '<li><strong>WooCommerce Active:</strong> ' . (function_exists('wc_get_product') ? 'Yes' : 'No') . '</li>';
        $output .= '<li><strong>User ID:</strong> ' . ($user_id ? $user_id : 'Not logged in') . '</li>';
        $output .= '<li><strong>Enrolled:</strong> ' . ($is_enrolled ? 'Yes' : 'No') . '</li>';
        $output .= '</ul>';
        
        $output .= '<div class="wpaa-test-enrollment-button">';
        $output .= $this->get_enrollment_button($course_id, $is_free, $is_enrolled);
        $output .= '</div>';
        
        // Add a debug button
        $output .= '<div class="wpaa-enrollment-debug">';
        $output .= '<h4>Debug Tools</h4>';
        $output .= '<p><button class="button test-free-course" data-course="' . $course_id . '">Test Free Course Detection</button></p>';
        $output .= '<p><button class="button test-ajax-ping">Test AJAX Connectivity</button></p>';
        $output .= '<div class="debug-results"></div>';
        $output .= '</div>';
        
        // Add inline JavaScript for debugging
        $output .= '
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Test free course detection
            $(".test-free-course").on("click", function() {
                var courseId = $(this).data("course");
                var resultsContainer = $(this).parent().parent().find(".debug-results");
                
                resultsContainer.html("Running test...");
                
                $.ajax({
                    url: "' . admin_url('admin-ajax.php') . '",
                    type: "POST",
                    data: {
                        action: "test_is_free_course",
                        course_id: courseId
                    },
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            var html = "<h4>Course Type Debug Results:</h4><pre>" + JSON.stringify(data, null, 2) + "</pre>";
                            resultsContainer.html(html);
                        } else {
                            resultsContainer.html("Error: " + response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        resultsContainer.html("<div style=\'color:red\'>AJAX error occurred: " + error + " (Status: " + xhr.status + ")</div>");
                    }
                });
            });
            
            // Test AJAX connectivity
            $(".test-ajax-ping").on("click", function() {
                var resultsContainer = $(this).parent().parent().find(".debug-results");
                
                resultsContainer.html("Testing AJAX connectivity...");
                
                $.ajax({
                    url: "' . admin_url('admin-ajax.php') . '",
                    type: "POST",
                    data: {
                        action: "test_ping"
                    },
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            var html = "<h4>AJAX Connectivity Test:</h4><pre>" + JSON.stringify(data, null, 2) + "</pre>";
                            html += "<p style=\'color:green; font-weight:bold\'>✓ AJAX is working correctly!</p>";
                            html += "<p>If enrollment still fails, the issue is likely in the enrollment handler itself, not in the AJAX connectivity.</p>";
                            resultsContainer.html(html);
                        } else {
                            resultsContainer.html("Error: " + response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMsg = "<div style=\'color:red; font-weight:bold\'>✗ AJAX Error: " + error + " (Status: " + xhr.status + ")</div>";
                        errorMsg += "<p>This indicates your WordPress AJAX system is not working correctly.</p>";
                        errorMsg += "<p>Possible causes:</p>";
                        errorMsg += "<ul>";
                        errorMsg += "<li>- Wrong admin-ajax.php URL</li>";
                        errorMsg += "<li>- Server configuration issue</li>";
                        errorMsg += "<li>- Plugin conflict</li>";
                        errorMsg += "<li>- .htaccess issue</li>";
                        errorMsg += "</ul>";
                        resultsContainer.html(errorMsg);
                    }
                });
            });
        });
        </script>';
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Simple AJAX ping test to verify connectivity
     */
    public function test_ping_ajax() {
        wp_send_json_success([
            'message' => 'AJAX endpoint is working!',
            'time' => current_time('mysql'),
            'user_id' => get_current_user_id(),
        ]);
    }
}

EnrollmentTest::init(); 