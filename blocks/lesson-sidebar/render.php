<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Block Name: Course Module
 * Description: Displays a list of course modules
 */

// Ensure dashicons are always loaded for this block
wp_enqueue_style('dashicons');

// Get the course ID from the current post context
$course_id = get_the_ID();

// Get current user ID for completion status
$user_id = get_current_user_id();

// If we're not on a course page, check if we're on a lesson page and get the associated course ID
if (get_post_type() === 'lithe_lesson') {
    // Get the parent course ID from the lesson metadata
    $course_id = get_post_meta(get_the_ID(), '_parent_course_id', true);
}

// If we still don't have a course ID, return
if (!$course_id) {
    return;
}

// Get course type to determine if it's public
use Lithe\Course\Posts\Course\Enrollment;
$course_type = Enrollment::lithe_course_get_course_type($course_id);
$is_public_course = ($course_type === 'public');

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
$title_background_color = $attributes['titleBackgroundColor'];
$title_text_color = $attributes['titleTextColor'];
$lesson_background_color = $attributes['lessonBackgroundColor'];
$lesson_text_color = $attributes['lessonTextColor'];
$current_lesson_color = $attributes['currentLessonColor'];

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

// Pass data to the view script
wp_localize_script('lithe-course-lesson-sidebar-view-script', 'litheLessonSidebar', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'isPublicCourse' => $is_public_course,
    'lessonTextColor' => $lesson_text_color,
    'nonce' => wp_create_nonce('lesson_completion_nonce'),
]);
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
                            $completed = Lithe\Course\Posts\Lesson\LessonMeta::get_lesson_completion_status($lesson->ID, $user_id);
                            $current_lesson = get_the_ID() === $lesson->ID ? 'current-lesson' : '';
                            
                            // Determine lesson content style
                            $lesson_content_style = $lesson_style;
                            if ($current_lesson && $current_lesson_color) {
                                $lesson_content_style .= "background-color: {$current_lesson_color};";
                            }
                        ?>
                            <li class="lesson-item <?php echo esc_attr($current_lesson); ?>">
                                <div class="lesson-content" style="<?php echo esc_attr($lesson_content_style); ?>">
                                    <?php if ($user_id && !$is_public_course) : ?>
                                        <label class="lesson-completion">
                                            <input type="checkbox" 
                                                   class="lesson-completion-checkbox"
                                                   data-lesson-id="<?php echo esc_attr($lesson->ID); ?>"
                                                   <?php checked($completed); ?>>
                                            <span class="completion-status" 
                                                  style="<?php 
                                                      $completion_style = '';
                                                      if ($lesson_text_color) {
                                                          $completion_style .= "border-color: {$lesson_text_color};";
                                                          if ($completed) {
                                                              $completion_style .= "background-color: {$lesson_text_color};";
                                                          } else {
                                                              $completion_style .= "background-color: transparent;";
                                                          }
                                                      }
                                                      echo esc_attr($completion_style);
                                                  ?>"></span>
                                        </label>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url(get_permalink($lesson->ID)); ?>" style="<?php echo esc_attr($lesson_text_color ? "color: {$lesson_text_color};" : ''); ?>">
                                        <?php echo esc_html($lesson->post_title); ?>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

