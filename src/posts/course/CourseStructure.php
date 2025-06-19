<?php

namespace Lithe\Course\Posts\CourseStructure;

class CourseStructure {
    private static $instance = null;

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        // add_action('add_meta_boxes', [self::$instance, 'add_meta_box']);
        // add_action('admin_enqueue_scripts', [self::$instance, 'enqueue_scripts']);
        // add_action('wp_ajax_update_module_order', [self::$instance, 'update_module_order']);
        // add_action('wp_ajax_add_new_module', [self::$instance, 'add_new_module']);
        // add_action('wp_ajax_delete_module', [self::$instance, 'delete_module']);
        // add_action('wp_ajax_update_module_title', [self::$instance, 'update_module_title']);
        // add_action('wp_ajax_update_lesson_order', [self::$instance, 'update_lesson_order']);
        // add_action('wp_ajax_add_new_lesson', [self::$instance, 'add_new_lesson']);
        // add_action('wp_ajax_delete_lesson', [self::$instance, 'delete_lesson']);
    }

    public function add_meta_box() {
        add_meta_box(
            'lithe_course_structure',
            __('Course Structure', 'lithe-course'),
            [$this, 'render_meta_box'],
            'lithe_course',
            'normal',
            'default'
        );
    }

    public function enqueue_scripts($hook) {
        global $post;

        if ($hook == 'post.php' || $hook == 'post-new.php') {
            if ('lithe_course' === $post->post_type) {
                // Enqueue CSS
                wp_enqueue_style(
                    'lithe-course-structure',
                    LITHE_COURSE_PLUGIN_URL . 'assets/css/course-structure.css',
                    [],
                    LITHE_COURSE_VERSION
                );
                
                // Enqueue JS
                wp_enqueue_script(
                    'lithe-course-structure',
                    LITHE_COURSE_PLUGIN_URL . 'assets/js/course-structure.js',
                    ['jquery', 'jquery-ui-sortable'],
                    LITHE_COURSE_VERSION,
                    true
                );

                wp_localize_script('lithe-course-structure', 'wpaaStructure', [
                    'nonce' => wp_create_nonce('course_structure_nonce'),
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'i18n' => [
                        'confirmDelete' => __('Are you sure you want to delete this module? This will also delete all lessons within this module.', 'lithe-course'),
                        'addModuleTitle' => __('Enter module title', 'lithe-course'),
                        'addModule' => __('Add Module', 'lithe-course'),
                        'cancel' => __('Cancel', 'lithe-course')
                    ]
                ]);
            }
        }
    }


    public function render_meta_box($post) {
        global $wpdb;
        
        wp_nonce_field('lithe_course_structure_nonce', 'lithe_course_structure_nonce');

        // Get all modules for this course using meta instead of post_parent
        $modules = get_posts([
            'post_type' => 'lithe_module',
            'meta_key' => '_parent_course_id',
            'meta_value' => $post->ID,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'posts_per_page' => -1,
            'suppress_filters' => true
        ]);

        ?>
        <div class="lithe-course-structure">
            <div class="lithe-modules-list" data-course-id="<?php echo $post->ID; ?>">
                <?php if (!empty($modules)) : ?>
                    <?php foreach ($modules as $module) : 
                        // Get lessons for this module using meta instead of post_parent
                        $lessons = get_posts([
                            'post_type' => 'lithe_lesson',
                            'meta_key' => '_parent_module_id',
                            'meta_value' => $module->ID,
                            'orderby' => 'menu_order',
                            'order' => 'ASC',
                            'posts_per_page' => -1,
                            'suppress_filters' => true
                        ]);
                    ?>
                        <div class="lithe-module-item" data-id="<?php echo $module->ID; ?>">
                            <div class="lithe-module-header">
                                <div class="lithe-module-header-left">
                                    <span class="dashicons dashicons-menu handle"></span>
                                    <span class="dashicons dashicons-arrow-down lithe-module-toggle"></span>
                                    <div class="lithe-module-title-wrapper">
                                        <h3 class="lithe-module-title"><?php echo esc_html($module->post_title); ?></h3>
                                        <div class="lithe-module-edit-form" style="display: none;">
                                            <input type="text" class="lithe-module-title-input" value="<?php echo esc_attr($module->post_title); ?>">
                                            <button type="button" class="wpaa-button wpaa-button-small wpaa-button-primary save-module-title">
                                                <?php _e('Save', 'lithe-course'); ?>
                                            </button>
                                            <button type="button" class="wpaa-button wpaa-button-small cancel-module-edit">
                                                <?php _e('Cancel', 'lithe-course'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="lithe-module-actions">
                                    <button type="button" class="wpaa-button wpaa-button-small edit-module">
                                        <?php _e('Edit', 'lithe-course'); ?>
                                    </button>
                                    <button type="button" class="wpaa-button wpaa-button-small wpaa-button-danger delete-module">
                                        <?php _e('Delete', 'lithe-course'); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="lithe-module-content">
                                <div class="lithe-module-lessons">
                                    <ul class="lithe-lessons-list" data-module-id="<?php echo $module->ID; ?>">
                                        <?php if (!empty($lessons)) : ?>
                                            <?php foreach ($lessons as $lesson) : ?>
                                                <li class="lithe-lesson-item" data-id="<?php echo $lesson->ID; ?>">
                                                    <span class="dashicons dashicons-menu handle"></span>
                                                    <span class="lithe-lesson-title">
                                                        <?php echo esc_html($lesson->post_title); ?>
                                                    </span>
                                                    <div class="lithe-lesson-actions">
                                                        <a href="<?php echo get_edit_post_link($lesson->ID); ?>" class="wpaa-button wpaa-button-small">
                                                            <?php _e('Edit', 'lithe-course'); ?>
                                                        </a>
                                                        <button type="button" class="wpaa-button wpaa-button-small wpaa-button-danger delete-lesson">
                                                            <?php _e('Delete', 'lithe-course'); ?>
                                                        </button>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <li class="wpaa-no-lessons">
                                                <?php _e('No lessons found. Add a new lesson to get started.', 'lithe-course'); ?>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="lithe-lesson-form" style="display: none;">
                                        <div class="wpaa-form-input-wrapper">
                                            <input type="text" class="wpaa-new-lesson-title wpaa-form-input" placeholder="<?php esc_attr_e('Enter lesson title', 'lithe-course'); ?>">
                                        </div>
                                        <button type="button" class="wpaa-button wpaa-button-primary save-lesson">
                                            <?php _e('Add Lesson', 'lithe-course'); ?>
                                        </button>
                                        <button type="button" class="wpaa-button cancel-lesson">
                                            <?php _e('Cancel', 'lithe-course'); ?>
                                        </button>
                                    </div>
                                    <button type="button" class="wpaa-button wpaa-add-lesson-button">
                                        <?php _e('Add New Lesson', 'lithe-course'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="wpaa-no-modules">
                        <?php _e('No modules found. Add a new module to get started.', 'lithe-course'); ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="lithe-module-actions">
                <button type="button" class="wpaa-button wpaa-button-primary wpaa-add-module-button">
                    <?php _e('Add New Module', 'lithe-course'); ?>
                </button>
            </div>
            <div class="lithe-module-form" style="display: none;">
                <div class="wpaa-form-input-wrapper">
                    <input type="text" class="wpaa-new-module-title wpaa-form-input" placeholder="<?php esc_attr_e('Enter module title', 'lithe-course'); ?>">
                </div>
                <button type="button" class="wpaa-button wpaa-button-primary wpaa-save-module">
                    <?php _e('Add Module', 'lithe-course'); ?>
                </button>
                <button type="button" class="wpaa-button wpaa-cancel-module">
                    <?php _e('Cancel', 'lithe-course'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    public function update_module_order() {
        check_ajax_referer('course_structure_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $module_order = isset($_POST['module_order']) ? array_map('intval', $_POST['module_order']) : [];

        if (empty($module_order)) {
            wp_send_json_error('No modules to update');
        }

        foreach ($module_order as $order => $id) {
            wp_update_post([
                'ID' => $id,
                'menu_order' => $order
            ]);
        }

        wp_send_json_success();
    }

    public function add_new_module() {
        check_ajax_referer('course_structure_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $course_id = intval($_POST['course_id']);
        $title = sanitize_text_field($_POST['title']);

        if (empty($title)) {
            wp_send_json_error('Title is required');
        }

        // Get the highest menu_order
        $modules = get_posts([
            'post_type' => 'lithe_module',
            'meta_key' => '_parent_course_id',
            'meta_value' => $course_id,
            'orderby' => 'menu_order',
            'order' => 'DESC',
            'posts_per_page' => 1,
            'fields' => 'ids'
        ]);

        $menu_order = !empty($modules) ? get_post_field('menu_order', $modules[0]) + 1 : 0;

        $module_id = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'lithe_module',
            'post_status' => 'publish',
            'menu_order' => $menu_order
        ]);

        if (is_wp_error($module_id)) {
            wp_send_json_error($module_id->get_error_message());
        }

        // Set the parent course using meta
        update_post_meta($module_id, '_parent_course_id', $course_id);

        $module = get_post($module_id);
        $html = $this->get_module_html($module);

        wp_send_json_success([
            'html' => $html,
            'moduleId' => $module_id
        ]);
    }

    public function delete_module() {
        check_ajax_referer('course_structure_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $module_id = intval($_POST['module_id']);

        // Get all lessons for this module using meta instead of post_parent
        $lessons = get_posts([
            'post_type' => 'lithe_lesson',
            'meta_key' => '_parent_module_id',
            'meta_value' => $module_id,
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        // Delete all lessons
        foreach ($lessons as $lesson_id) {
            wp_delete_post($lesson_id, true);
        }

        // Delete the module
        $result = wp_delete_post($module_id, true);

        if (!$result) {
            wp_send_json_error('Failed to delete module');
        }

        wp_send_json_success();
    }

    public function update_module_title() {
        check_ajax_referer('course_structure_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $module_id = intval($_POST['module_id']);
        $title = sanitize_text_field($_POST['title']);

        if (empty($title)) {
            wp_send_json_error('Title is required');
        }

        $result = wp_update_post([
            'ID' => $module_id,
            'post_title' => $title
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success([
            'title' => $title
        ]);
    }

    public function update_lesson_order() {
        check_ajax_referer('course_structure_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $lesson_id = intval($_POST['lesson_id']);
        $new_module_id = intval($_POST['module_id']);
        $position = intval($_POST['position']);
        $lesson_order = isset($_POST['lesson_order']) ? array_map('intval', $_POST['lesson_order']) : [];

        // First, update the moved lesson's module (parent) using meta
        update_post_meta($lesson_id, '_parent_module_id', $new_module_id);
        
        // Get the course ID from the module
        $course_id = get_post_meta($new_module_id, '_parent_course_id', true);
        if ($course_id) {
            update_post_meta($lesson_id, '_parent_course_id', $course_id);
        }

        // Then update the order of all lessons in the module
        if (!empty($lesson_order)) {
            foreach ($lesson_order as $order => $id) {
                wp_update_post([
                    'ID' => $id,
                    'menu_order' => $order
                ]);
            }
        }

        wp_send_json_success();
    }

    public function add_new_lesson() {
        check_ajax_referer('course_structure_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $module_id = intval($_POST['module_id']);
        $title = sanitize_text_field($_POST['title']);

        if (empty($title)) {
            wp_send_json_error('Title is required');
        }

        // Get the highest menu_order
        $lessons = get_posts([
            'post_type' => 'lithe_lesson',
            'meta_key' => '_parent_module_id',
            'meta_value' => $module_id,
            'orderby' => 'menu_order',
            'order' => 'DESC',
            'posts_per_page' => 1,
            'fields' => 'ids'
        ]);

        $menu_order = !empty($lessons) ? get_post_field('menu_order', $lessons[0]) + 1 : 0;

        $lesson_id = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'lithe_lesson',
            'post_status' => 'publish',
            'menu_order' => $menu_order
        ]);

        if (is_wp_error($lesson_id)) {
            wp_send_json_error($lesson_id->get_error_message());
        }

        // Set the parent module using meta
        update_post_meta($lesson_id, '_parent_module_id', $module_id);
        
        // Get the course ID from the module and set it for the lesson
        $course_id = get_post_meta($module_id, '_parent_course_id', true);
        if ($course_id) {
            update_post_meta($lesson_id, '_parent_course_id', $course_id);
        }

        $lesson = get_post($lesson_id);
        $html = $this->get_lesson_html($lesson);

        wp_send_json_success([
            'html' => $html,
            'lessonId' => $lesson_id
        ]);
    }

    public function delete_lesson() {
        check_ajax_referer('course_structure_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $lesson_id = intval($_POST['lesson_id']);
        $result = wp_delete_post($lesson_id, true);

        if (!$result) {
            wp_send_json_error('Failed to delete lesson');
        }

        wp_send_json_success();
    }

    private function get_module_html($module) {
        ob_start();
        ?>
        <div class="lithe-module-item" data-id="<?php echo $module->ID; ?>">
            <div class="lithe-module-header">
                <div class="lithe-module-header-left">
                    <span class="dashicons dashicons-menu handle"></span>
                    <span class="dashicons dashicons-arrow-down lithe-module-toggle"></span>
                    <div class="lithe-module-title-wrapper">
                        <h3 class="lithe-module-title"><?php echo esc_html($module->post_title); ?></h3>
                        <div class="lithe-module-edit-form" style="display: none;">
                            <input type="text" class="lithe-module-title-input" value="<?php echo esc_attr($module->post_title); ?>">
                            <button type="button" class="wpaa-button wpaa-button-small wpaa-button-primary save-module-title">
                                <?php _e('Save', 'lithe-course'); ?>
                            </button>
                            <button type="button" class="wpaa-button wpaa-button-small cancel-module-edit">
                                <?php _e('Cancel', 'lithe-course'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="lithe-module-actions">
                    <button type="button" class="wpaa-button wpaa-button-small edit-module">
                        <?php _e('Edit', 'lithe-course'); ?>
                    </button>
                    <button type="button" class="wpaa-button wpaa-button-small wpaa-button-danger delete-module">
                        <?php _e('Delete', 'lithe-course'); ?>
                    </button>
                </div>
            </div>
            <div class="lithe-module-content">
                <div class="lithe-module-lessons">
                    <ul class="lithe-lessons-list" data-module-id="<?php echo $module->ID; ?>">
                        <?php
                        $lessons = get_posts([
                            'post_type' => 'lithe_lesson',
                            'meta_key' => '_parent_module_id',
                            'meta_value' => $module->ID,
                            'orderby' => 'menu_order',
                            'order' => 'ASC',
                            'posts_per_page' => -1
                        ]);

                        foreach ($lessons as $lesson) {
                            echo $this->get_lesson_html($lesson);
                        }
                        ?>
                    </ul>
                    <div class="lithe-lesson-form" style="display: none;">
                        <div class="wpaa-form-input-wrapper">
                            <input type="text" class="wpaa-new-lesson-title wpaa-form-input" placeholder="<?php esc_attr_e('Enter lesson title', 'lithe-course'); ?>">
                        </div>
                        <button type="button" class="wpaa-button wpaa-button-primary save-lesson">
                            <?php _e('Add Lesson', 'lithe-course'); ?>
                        </button>
                        <button type="button" class="wpaa-button cancel-lesson">
                            <?php _e('Cancel', 'lithe-course'); ?>
                        </button>
                    </div>
                    <button type="button" class="wpaa-button wpaa-add-lesson-button">
                        <?php _e('Add New Lesson', 'lithe-course'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_lesson_html($lesson) {
        if (!$lesson || !isset($lesson->ID)) {
            error_log('Invalid lesson object passed to get_lesson_html');
            return '';
        }

        ob_start();
        ?>
        <li class="lithe-lesson-item" data-id="<?php echo $lesson->ID; ?>">
            <span class="dashicons dashicons-menu handle"></span>
            <span class="lithe-lesson-title"><?php echo esc_html($lesson->post_title); ?></span>
            <div class="lithe-lesson-actions">
                <a href="<?php echo get_edit_post_link($lesson->ID); ?>" class="wpaa-button wpaa-button-small">
                    <?php _e('Edit', 'lithe-course'); ?>
                </a>
                <button type="button" class="wpaa-button wpaa-button-small wpaa-button-danger delete-lesson">
                    <?php _e('Delete', 'lithe-course'); ?>
                </button>
            </div>
        </li>
        <?php
        return ob_get_clean();
    }
}

CourseStructure::init();
