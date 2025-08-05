<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Block Name: Course Module
 * Description: Displays a list of course modules
 */

// Get the course ID from the current post context
$course_id = get_the_ID();

// If we're not on a course page, try to get the course ID from the block attributes
if (get_post_type() !== 'lithe_course') {
    $course_id = isset($attributes['courseId']) ? $attributes['courseId'] : null;
    
    // Fallback to ACF field if using it
    if (!$course_id && function_exists('get_field')) {
        $course_id = get_field('courseId');
    }
}

if (!$course_id) {
    return;
}

// Get all modules for this course
$modules = get_posts([
    'post_type' => 'lithe_module',
    'meta_key' => '_parent_course_id',
    'meta_value' => $course_id,
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'posts_per_page' => -1
]);

if (empty($modules)) {
    echo '<p class="no-modules">' . esc_html__('No modules found.', 'lithe-course') . '</p>';
    return;
}

// Extract styles from block attributes
$style = isset($attributes['style']) ? $attributes['style'] : [];
$border_style = isset($style['border']) ? $style['border'] : [];
$border_radius = isset($border_style['radius']) ? $border_style['radius'] : (isset($attributes['borderRadius']) ? $attributes['borderRadius'] : '4px');

// Get color attributes with defaults
$title_background_color = isset($attributes['titleBackgroundColor']) ? $attributes['titleBackgroundColor'] : '#ffffff';
$title_text_color = isset($attributes['titleTextColor']) ? $attributes['titleTextColor'] : '#000000';
$lesson_background_color = isset($attributes['lessonBackgroundColor']) ? $attributes['lessonBackgroundColor'] : '';
$lesson_text_color = isset($attributes['lessonTextColor']) ? $attributes['lessonTextColor'] : '';

// Prepare inline styles
$module_style = '';
if ($border_radius) {
    $module_style .= "border-radius: {$border_radius};";
}

$module_header_style = '';
if ($title_background_color) {
    $module_header_style .= "background-color: {$title_background_color};";
}
if ($title_text_color) {
    $module_header_style .= "color: {$title_text_color};";
}
if ($border_radius) {
    $module_header_style .= "border-radius: {$border_radius} {$border_radius} 0 0;";
}

$lesson_style = '';
if ($lesson_background_color) {
    $lesson_style .= "background-color: {$lesson_background_color};";
}
if ($lesson_text_color) {
    $lesson_style .= "color: {$lesson_text_color};";
}
?>

<div <?php echo wp_kses_data(get_block_wrapper_attributes(['class' => 'lithe-course-modules'])); ?>>
    <?php foreach ($modules as $module) : ?>
        <div class="lithe-module" data-id="<?php echo esc_attr($module->ID); ?>" style="<?php echo esc_attr($module_style); ?>">
            <div class="module-header" style="<?php echo esc_attr($module_header_style); ?>">
                <h3 class="module-title"><?php echo esc_html($module->post_title); ?></h3>
                <span class="dashicons dashicons-arrow-down module-toggle"></span>
            </div>
            <div class="module-content">
                <?php
                // Get lessons for this module
                $lessons = get_posts([
                    'post_type' => 'lithe_lesson',
                    'meta_key' => '_parent_module_id',
                    'meta_value' => $module->ID,
                    'orderby' => 'menu_order',
                    'order' => 'ASC',
                    'posts_per_page' => -1
                ]);

                if (!empty($lessons)) : ?>
                    <ul class="module-lessons">
                        <?php foreach ($lessons as $lesson) : 
                            // Check if this is the current page
                            $current_lesson = get_the_ID() === $lesson->ID ? 'current-lesson' : '';
                        ?>
                            <li class="lesson-item <?php echo esc_attr($current_lesson); ?>">
                                <div class="lesson-content" style="<?php echo esc_attr($lesson_style); ?>">
                                    <span class="lesson-title">
                                        <?php echo esc_html($lesson->post_title); ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>