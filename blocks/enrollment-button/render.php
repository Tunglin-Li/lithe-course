<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Block Name: Enrollment Button
 * Description: Shows the appropriate enrollment button based on course type and user status
 */

// Pass REST API data to the view script (automatically enqueued via block.json)
wp_localize_script('lithe-course-enrollment-button-view-script', 'litheCourseEnrollment', [
    'apiUrl' => rest_url('lithe-course/v1'),
    'nonce' => wp_create_nonce('wp_rest'),
]);

// Get block attributes
$button_style = isset($attributes['buttonStyle']) ? $attributes['buttonStyle'] : 'primary';

// Get the course ID from the current post context
$course_id = get_the_ID();

// If not a course, don't display anything
if (get_post_type() !== 'lithe_course') {
    return '';
}

// Use the Enrollment class to generate the correct button
use Lithe\Course\Posts\Course\Enrollment;

// Add wrapper with CSS classes
$wrapper_class = 'wp-block-button lithe-course-enrollment-button-wrap';
if ($button_style) {
    $wrapper_class .= " is-style-{$button_style}";
}

// Output the button HTML with wrapper
echo '<div class="' . esc_attr($wrapper_class) . '">';
echo Enrollment::lithe_course_get_enrollment_button_html($course_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<span class="lithe-course-enrollment-status" style="display: none;"></span>';
echo '</div>'; 