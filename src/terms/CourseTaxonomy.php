<?php

namespace Lithe\Course\Terms;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CourseTaxonomy {
    public static function init() {
        $class = new self();
        add_action('init', [$class, 'lithe_course_register_course_taxonomies']);
    }

    public function lithe_course_register_course_taxonomies() {
        // Register Course Category Taxonomy
        register_taxonomy('lithe_course_category', 'lithe_course', [
            'labels' => [
                'name' => __('Course Categories', 'lithe-course'),
                'singular_name' => __('Course Category', 'lithe-course'),
                'search_items' => __('Search Course Categories', 'lithe-course'),
                'popular_items' => __('Popular Course Categories', 'lithe-course'),
                'all_items' => __('All Course Categories', 'lithe-course'),
                'parent_item' => __('Parent Course Category', 'lithe-course'),
                'parent_item_colon' => __('Parent Course Category:', 'lithe-course'),
                'edit_item' => __('Edit Course Category', 'lithe-course'),
                'update_item' => __('Update Course Category', 'lithe-course'),
                'add_new_item' => __('Add New Course Category', 'lithe-course'),
                'new_item_name' => __('New Course Category Name', 'lithe-course'),
                'separate_items_with_commas' => __('Separate course categories with commas', 'lithe-course'),
                'add_or_remove_items' => __('Add or remove course categories', 'lithe-course'),
                'choose_from_most_used' => __('Choose from the most used course categories', 'lithe-course'),
                'not_found' => __('No course categories found.', 'lithe-course'),
                'menu_name' => __('Course Categories', 'lithe-course'),
            ],
            'hierarchical' => true,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rest_base' => 'course-categories',
            'rewrite' => [
                'slug' => 'lithe-course-category',
                'with_front' => false,
                'hierarchical' => true,
            ],
        ]);
    }
}

CourseTaxonomy::init();
