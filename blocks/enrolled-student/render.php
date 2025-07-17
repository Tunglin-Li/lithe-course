<?php
/**
 * Enrolled Student Count Block Renderer
 * Displays the actual number of students enrolled in a course
 */

// Get attributes
$course_id = $attributes['courseId'] ?? get_the_ID();
$text_format = $attributes['textFormat'] ?? '{count} students enrolled';
$show_icon = $attributes['showIcon'] ?? true;

// Get enrollment count using the same method as in EnrollmentAdminColumn
global $wpdb;

$count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->usermeta} 
    WHERE meta_key = %s AND meta_value = '1'",
    '_has_access_to_course_' . $course_id
));

$enrollment_count = $count ? intval($count) : 0;

// Replace {count} placeholder with actual count
$display_text = str_replace('{count}', $enrollment_count, $text_format);

// Apply block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'enrolled-student-count',
]);

?>

<div <?php echo $wrapper_attributes; ?>>
    <div class="enrolled-student-display">
        <?php if ($show_icon) : ?>
            <svg class="enrolled-student-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M15.5 9.5a1 1 0 100-2 1 1 0 000 2zm0 1.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-2.25 6v-2a2.75 2.75 0 00-2.75-2.75h-4A2.75 2.75 0 003.75 15v2h1.5v-2c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2h1.5zm7-2v2h-1.5v-2c0-.69-.56-1.25-1.25-1.25H15v-1.5h2.5A2.75 2.75 0 0120.25 15zM9.5 8.5a1 1 0 11-2 0 1 1 0 012 0zm1.5 0a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" fill-rule="evenodd"></path>
            </svg>
        <?php endif; ?>
        <span class="enrolled-student-text">
            <?php echo esc_html($display_text); ?>
        </span>
    </div>
</div> 