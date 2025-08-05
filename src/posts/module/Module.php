<?php

namespace Lithe\Course\Posts\Module;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module {
    private static $instance = null;

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        add_action('init', [self::$instance, 'lithe_course_register_module_post_type']);
        // Add migration code
        add_action('admin_init', [self::$instance, 'lithe_course_migrate_module_relationships']);
    }

    public function lithe_course_register_module_post_type() {
        // Register Module Post Type
        $args = [
            'labels' => [
                'name' => __('Modules', 'lithe-course'),
                'singular_name' => __('Module', 'lithe-course'),
                'add_new' => __('Add New Module', 'lithe-course'),
                'add_new_item' => __('Add New Module', 'lithe-course'),
                'edit_item' => __('Edit Module', 'lithe-course'),
                'parent_item_colon' => __('Parent Course:', 'lithe-course'),
            ],
            'public' => false, // No frontend pages needed
            'show_in_menu' => false,
            'supports' => [
                'title', // Only title needed for data storage
            ],
            'rewrite' => false, // No URL rewriting needed
            'show_in_rest' => true, // Keep for API access
            'menu_position' => 5,
            'hierarchical' => false,
            'has_archive' => false,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'publicly_queryable' => false, // No frontend access
            'show_ui' => true, // Keep admin interface
        ];

        $result = register_post_type('lithe_module', $args);
    }
    
    /**
     * Migrate existing module relationships from post_parent to custom meta
     */
    public function lithe_course_migrate_module_relationships() {
        // Only run once by checking for a flag in options
        if (get_option('lithe_module_migration_complete')) {
            return;
        }
        
        // Get all modules
        $modules = get_posts([
            'post_type' => 'lithe_module',
            'posts_per_page' => -1
        ]);
        
        foreach ($modules as $module) {
            // Get the parent course ID
            $parent_course_id = $module->post_parent;
            
            if ($parent_course_id) {
                // Set the custom meta for the parent course
                update_post_meta($module->ID, '_parent_course_id', $parent_course_id);
                
                // Remove the post_parent relationship
                wp_update_post([
                    'ID' => $module->ID,
                    'post_parent' => 0
                ]);
                

            }
        }
        
        // Set flag to indicate migration is complete
        update_option('lithe_module_migration_complete', true);
    
    }
}

Module::init();
