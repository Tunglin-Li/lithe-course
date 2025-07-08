<?php

namespace Lithe\Course\Posts\Course;

class Course {
    public static function init() {
        $class = new self();
        add_action('init', [$class, 'register_course_post_type']);
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
        ]);
    }
}

Course::init();