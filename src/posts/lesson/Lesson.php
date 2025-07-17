<?php

namespace Lithe\Course\Posts\Lesson;

class Lesson {
    private static $instance = null;

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        add_action('init', [self::$instance, 'register_lesson_post_type']);
        // Add migration code
        add_action('admin_init', [self::$instance, 'migrate_lesson_relationships']);
    }

    public function register_lesson_post_type() {
        // Register Lesson Post Type
        $args = [
            'labels' => [
                'name' => __('Lessons', 'lithe-course'),
                'singular_name' => __('Lesson', 'lithe-course'),
                'add_new' => __('Add New Lesson', 'lithe-course'),
                'add_new_item' => __('Add New Lesson', 'lithe-course'),
                'edit_item' => __('Edit Lesson', 'lithe-course'),
                'parent_item_colon' => __('Parent Module:', 'lithe-course'),
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_menu' => false,
            'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
            'rewrite' => ['slug' => 'lesson'],
            'show_in_rest' => true,
            'hierarchical' => false, // Changed from true to false since we'll use custom meta
        ];

        $result = register_post_type('lithe_lesson', $args);
    }

    /**
     * Migrate existing lesson relationships from post_parent to custom meta
     */
    public function migrate_lesson_relationships() {
        // Only run once by checking for a flag in options
        if (get_option('lithe_lesson_migration_complete')) {
            return;
        }
        
        // Get all lessons
        $lessons = get_posts([
            'post_type' => 'lithe_lesson',
            'posts_per_page' => -1
        ]);
        
        foreach ($lessons as $lesson) {
            // Get the parent module ID
            $parent_module_id = $lesson->post_parent;
            
            if ($parent_module_id) {
                // Set the custom meta for the parent module
                update_post_meta($lesson->ID, '_parent_module_id', $parent_module_id);
                
                // Get the associated course (grandparent) and store it too
                $parent_course_id = get_post_field('post_parent', $parent_module_id);
                if ($parent_course_id) {
                    update_post_meta($lesson->ID, '_parent_course_id', $parent_course_id);
                } else {
                    // Or check if the module has the _parent_course_id meta
                    $module_course_id = get_post_meta($parent_module_id, '_parent_course_id', true);
                    if ($module_course_id) {
                        update_post_meta($lesson->ID, '_parent_course_id', $module_course_id);
                    }
                }
                
                // Remove the post_parent relationship
                wp_update_post([
                    'ID' => $lesson->ID,
                    'post_parent' => 0
                ]);
            
            }
        }
        
        // Set flag to indicate migration is complete
        update_option('lithe_lesson_migration_complete', true);
    
    }
}

Lesson::init();