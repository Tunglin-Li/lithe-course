<?php
/**
 * Block Name: Enrollment Button
 * Description: Shows the appropriate enrollment button based on course type and user status
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Only enqueue button styles when this block is actually used (performance optimization)
if (!wp_style_is('wp-block-library', 'enqueued')) {
    wp_enqueue_style('wp-block-library');
}

// Force WordPress to load individual block styles and enqueue core button CSS
add_filter('should_load_separate_core_block_assets', '__return_true');

$button_css_path = includes_url('blocks/button/style.min.css');
if (!wp_style_is('wp-block-button-core-style', 'enqueued')) {
    wp_enqueue_style(
        'wp-block-button-core-style',
        $button_css_path,
        array('wp-block-library'),
        get_bloginfo('version')
    );
}

// Get block attributes
$button_style = isset($attributes['buttonStyle']) ? $attributes['buttonStyle'] : 'primary';
$custom_text = isset($attributes['customText']) ? $attributes['customText'] : [];

// Get the course ID from the current post context
$course_id = get_the_ID();

// If not a course, don't display anything
if (get_post_type() !== 'lithe_course') {
    return '';
}

/**
 * Get course type from meta field
 * 
 * @param int $course_id Course ID
 * @return string Course type (public, free, paid)
 */
function get_course_type($course_id) {
    $course_type = get_post_meta($course_id, '_course_type', true);
    
    // Default to 'free' if not set for backward compatibility
    if (empty($course_type)) {
        return 'free';
    }
    
    return $course_type;
}

/**
 * Get enrollment button for a course based on new course type system
 * 
 * @param int $course_id Course ID
 * @return string HTML button
 */
function get_enrollment_button($course_id) {
    $course_type = get_course_type($course_id);
    
    // For public courses, show a "View Course" or "Start Learning" button
    if ($course_type === 'public') {
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
                '<a href="%s" class="wp-block-button__link wp-element-button wpaa-start-learning-button">%s</a>',
                esc_url($first_lesson_url),
                __('Start Learning', 'lithe-course')
            );
        } else {
            // No lessons found, link to course content section
            return sprintf(
                '<a href="#course-content" class="wp-block-button__link wp-element-button wpaa-view-course-button">%s</a>',
                __('View Course', 'lithe-course')
            );
        }
    }
    
    // For free and paid courses, check login status first
    if (!is_user_logged_in()) {
        return sprintf(
            '<a href="%s" class="wp-block-button__link wp-element-button wpaa-login-button">%s</a>',
            esc_url(wp_login_url(get_permalink($course_id))),
            __('Log in to Enroll', 'lithe-course')
        );
    }

    $user_id = get_current_user_id();
    $is_enrolled = false;
    
    // Check if user is enrolled in this course
    $is_enrolled = (bool) get_user_meta($user_id, '_has_access_to_course_' . $course_id, true);
    
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

    // User is not enrolled, show appropriate enrollment button based on course type
    if ($course_type === 'free') {
        // Free course - show Enroll button
        return sprintf(
            '<a href="#" class="wp-block-button__link wp-element-button wpaa-enroll-button" data-course="%d">%s</a>',
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
    
    // Fallback - should not reach here
    return '';
}

// Add wrapper with CSS classes
$wrapper_class = 'wp-block-button wpaa-enrollment-button-wrap';
if ($button_style) {
    $wrapper_class .= " is-style-{$button_style}";
}

// Output the button HTML with wrapper
echo '<div class="' . esc_attr($wrapper_class) . '">';
// Use the updated get_enrollment_button function
echo get_enrollment_button($course_id);
// Add a placeholder for enrollment status messages
echo '<span class="wpaa-enrollment-status" style="display: none;"></span>';
echo '</div>'; 