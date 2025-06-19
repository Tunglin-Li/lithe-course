<?php

namespace Lithe_Course\Blocks;

class Enqueue {
    public static function init() {
        $class = new self();    
        add_action('init', [$class, 'module_block_init']);
        add_action('enqueue_block_editor_assets', [$class, 'enqueue_editor_assets']);
        add_filter('block_categories_all', [$class, 'add_course_block_category'], 10, 2);
        add_action('enqueue_block_editor_assets', [$class, 'enqueue_setting_panel_course']);
    }

    public function module_block_init() {
        register_block_type(LITHE_COURSE_PLUGIN_DIR . 'build/course-outline');
        register_block_type(LITHE_COURSE_PLUGIN_DIR . 'build/lesson-sidebar');
        register_block_type(LITHE_COURSE_PLUGIN_DIR . 'build/enrollment-button');
        register_block_type(LITHE_COURSE_PLUGIN_DIR . 'build/course-video');
        register_block_type(LITHE_COURSE_PLUGIN_DIR . 'build/course-metadata');
        register_block_type(LITHE_COURSE_PLUGIN_DIR . 'build/meta-course-feature');
        register_block_type(LITHE_COURSE_PLUGIN_DIR . 'build/meta-course-content');
    }

    /**
     * Add Course block category to the top of block categories
     *
     * @param array $categories Array of block categories
     * @param WP_Post $post Post being edited
     * @return array Modified array of block categories
     */
    public function add_course_block_category($categories, $post) {
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

    public function enqueue_editor_assets() {
        wp_enqueue_style('dashicons');

        // use index.asset.php to get the dependencies
        // $asset_file = include(LITHE_COURSE_PLUGIN_DIR . 'build/editor/index.asset.php');

        // wp_enqueue_script(
        //     'lithe-course-editor-script',
        //     LITHE_COURSE_PLUGIN_URL . 'build/editor/index.js',
        //     $asset_file['dependencies'],
        //     $asset_file['version'],
        //     true
        // );

        // register my course block variation
        // use index.asset.php to get the dependencies
        $asset_file = include(LITHE_COURSE_PLUGIN_DIR . 'build/my-course/index.asset.php');

        wp_enqueue_script(
            'lithe-course-my-course-script',
            LITHE_COURSE_PLUGIN_URL . 'build/my-course/index.js',
            $asset_file['dependencies'],
            $asset_file['version'],
            true
        );
    }

    public function enqueue_setting_panel_course() {
        // Only load on course edit screens
        if (!function_exists('get_current_screen')) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || ($screen->post_type !== 'lithe_course' && $screen->id !== 'lithe_course')) {
            return;
        }

        // Check if the build file exists
        $build_file = LITHE_COURSE_PLUGIN_DIR . 'build/setting-panel-course/index.js';
        if (file_exists($build_file)) {
            wp_enqueue_script(
                'lithe-course-setting-panel-course-script',
                LITHE_COURSE_PLUGIN_URL . 'build/setting-panel-course/index.js',
                ['wp-plugins', 'wp-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data'],
                filemtime($build_file),
                true
            );

            wp_enqueue_style(
                'lithe-course-setting-panel-course-style',
                LITHE_COURSE_PLUGIN_URL . 'build/setting-panel-course/style-index.css',
                [],
                filemtime($build_file)
            );
        }
    }
}

Enqueue::init();