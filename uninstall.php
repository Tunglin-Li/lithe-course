<?php
/**
 * Uninstall script for Lithe Course plugin
 * 
 * This file is executed when the plugin is deleted through the WordPress admin.
 * It removes all plugin data from the database.
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('lithe_course_version');

// Get all course posts and delete them with their meta
$courses = get_posts([
    'post_type' => 'lithe_course',
    'numberposts' => -1,
    'post_status' => 'any',
    'fields' => 'ids'
]);

foreach ($courses as $course_id) {
    wp_delete_post($course_id, true);
}

// Get all lesson posts and delete them with their meta
$lessons = get_posts([
    'post_type' => 'lithe_lesson',
    'numberposts' => -1,
    'post_status' => 'any',
    'fields' => 'ids'
]);

foreach ($lessons as $lesson_id) {
    wp_delete_post($lesson_id, true);
}

// Get all module posts and delete them with their meta
$modules = get_posts([
    'post_type' => 'lithe_module',
    'numberposts' => -1,
    'post_status' => 'any',
    'fields' => 'ids'
]);

foreach ($modules as $module_id) {
    wp_delete_post($module_id, true);
}

// Remove user meta related to course enrollment
global $wpdb;

// Delete all user meta keys that start with '_has_access_to_course_'
$wpdb->query(
    "DELETE FROM {$wpdb->usermeta} 
     WHERE meta_key LIKE '_has_access_to_course_%'"
);

// Delete lesson completion meta
$wpdb->query(
    "DELETE FROM {$wpdb->usermeta} 
     WHERE meta_key LIKE '_lesson_completion_%'"
);

// Remove any custom tables (if you add any in the future)
// Example: $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lithe_course_enrollments");

// Clear any scheduled hooks
wp_clear_scheduled_hook('lithe_course_daily_cleanup');

// Remove plugin capabilities (if you add any custom capabilities)
// Example: remove_cap('administrator', 'manage_courses');

// Flush rewrite rules to clean up custom post type rules
flush_rewrite_rules(); 