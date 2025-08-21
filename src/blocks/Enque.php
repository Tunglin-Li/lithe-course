<?php

namespace Lithe\Course\Blocks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Enqueue {
    public static function init() {
        $class = new self();    
        add_action('init', [$class, 'lithecourse_module_block_init']);
        add_action('enqueue_block_editor_assets', [$class, 'lithecourse_enqueue_editor_assets']);
        add_filter('block_categories_all', [$class, 'lithecourse_add_course_block_category'], 10, 2);
        add_action('enqueue_block_editor_assets', [$class, 'lithecourse_enqueue_setting_panel_course']);
        add_action('init', [$class, 'lithecourse_set_script_translations']);
    }

    public function lithecourse_module_block_init() {

        // Set up JavaScript translations for blocks

        register_block_type(LITHECOURSE_PLUGIN_DIR . 'build/course-outline');
        register_block_type(LITHECOURSE_PLUGIN_DIR . 'build/lesson-sidebar');
        register_block_type(LITHECOURSE_PLUGIN_DIR . 'build/enrollment-button');
        register_block_type(LITHECOURSE_PLUGIN_DIR . 'build/course-video');
        register_block_type(LITHECOURSE_PLUGIN_DIR . 'build/course-metadata');
        register_block_type(LITHECOURSE_PLUGIN_DIR . 'build/enrolled-student');
        
        
    }


    /**
     * Add Course block category to the top of block categories
     *
     * @param array $categories Array of block categories
     * @param WP_Post $post Post being edited
     * @return array Modified array of block categories
     */
    public function lithecourse_add_course_block_category($categories, $post) {
        $custom_category = array(
            array(
                'slug'  => 'lithe-course',
                'title' => __( 'Course', 'lithe-course' ),
                'icon'  => 'welcome-learn-more',
            ),
        );

        // arrange the category
        $position = 4;
    
        return array_merge( array_slice( $categories, 0, $position, true ), $custom_category, array_slice( $categories, $position, null, true ) );
    }

    public function lithecourse_enqueue_editor_assets() {
        wp_enqueue_style('dashicons');

        // register my course block variation
        // use index.asset.php to get the dependencies
        $asset_file = include(LITHECOURSE_PLUGIN_DIR . 'build/my-course/index.asset.php');

        wp_enqueue_script(
            'lithecourse-my-course-script',
            LITHECOURSE_PLUGIN_URL . 'build/my-course/index.js',
            $asset_file['dependencies'],
            $asset_file['version'],
            true
        );
        
        // Set translations for my-course script after it's enqueued
        wp_set_script_translations( 'lithecourse-my-course-script', 'lithe-course', LITHECOURSE_PLUGIN_DIR . 'languages' );
       
    }

    public function lithecourse_enqueue_setting_panel_course() {
        // Only load on course edit screens
        if (!function_exists('get_current_screen')) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || ($screen->post_type !== 'lithecourse_course' && $screen->id !== 'lithecourse_course')) {
            return;
        }

        // Check if the build file exists
        $build_file = LITHECOURSE_PLUGIN_DIR . 'build/setting-panel-course/index.js';
        if (file_exists($build_file)) {
            wp_enqueue_script(
                'lithecourse-setting-panel-course-script',
                LITHECOURSE_PLUGIN_URL . 'build/setting-panel-course/index.js',
                ['wp-plugins', 'wp-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data'],
                filemtime($build_file),
                true
            );

            wp_enqueue_style(
                'lithecourse-setting-panel-course-style',
                LITHECOURSE_PLUGIN_URL . 'build/setting-panel-course/style-index.css',
                [],
                filemtime($build_file)
            );
            wp_set_script_translations( 'lithecourse-setting-panel-course-script', 'lithe-course', LITHECOURSE_PLUGIN_DIR . 'languages' );
        }
    }

    /**
     * Set up script translations for JavaScript files
     */
    public function lithecourse_set_script_translations() {
        // Set translations for block scripts that use @wordpress/scripts build process
        wp_set_script_translations( 'lithecourse-course-metadata-editor-script', 'lithe-course', LITHECOURSE_PLUGIN_DIR . 'languages' );
        wp_set_script_translations( 'lithecourse-course-outline-editor-script', 'lithe-course', LITHECOURSE_PLUGIN_DIR . 'languages' );
        wp_set_script_translations( 'lithecourse-lesson-sidebar-editor-script', 'lithe-course', LITHECOURSE_PLUGIN_DIR . 'languages' );
        wp_set_script_translations( 'lithecourse-enrollment-button-editor-script', 'lithe-course', LITHECOURSE_PLUGIN_DIR . 'languages' );
        wp_set_script_translations( 'lithecourse-course-video-editor-script', 'lithe-course', LITHECOURSE_PLUGIN_DIR . 'languages' );
        wp_set_script_translations( 'lithecourse-enrolled-student-editor-script', 'lithe-course', LITHECOURSE_PLUGIN_DIR . 'languages' );
       

    }
}

Enqueue::init();