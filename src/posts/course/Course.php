<?php

namespace Lithe\Course\Posts\Course;

class Course {
    public static function init() {
        $class = new self();
        add_action('init', [$class, 'register_course_post_type']);
        // add_filter('block_editor_settings_all', [$class, 'disable_course_block_locking'], 10, 2);
    }

    public function register_course_post_type() {
     // Register Course Post Type
        register_post_type('lithe_course', [
            'labels' => [
                'name' => __('Courses', 'lithe-course'),
                'singular_name' => __('Course', 'lithe-course'),
                'add_new' => __('Add New Course', 'lithe-course'),
                'add_new_item' => __('Add New Course', 'lithe-course'),
                'edit_item' => __('Edit Course', 'lithe-course'),
                // 'parent_item_colon' => __('Parent Course:', 'lithe-course'),
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-welcome-learn-more',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'rewrite' => ['slug' => 'courses'],
            'show_in_rest' => true,
            'hierarchical' => true,
            // Add block template
            'template' => [
                ['lithe-course/meta-course-feature', [
                    'lock' => [
                        'remove' => true
                        ]
                    ]
                ],
            ],



        ]);
    }

    public function disable_course_block_locking($settings, $context) {
        // Disable block locking for course post type
        if ($context->post && $context->post->post_type === 'lithe_course') {
            $settings['canLockBlocks'] = false;
        }

        return $settings;
    }

    // Removed maybe_create_product method - now handled by AutoCreateProduct class
    
}

Course::init();