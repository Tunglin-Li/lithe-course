<?php

namespace Lithe\Course\Posts\Course;

class CourseStructureAPI {
    private static $instance = null;
    private $api_namespace = 'lithe-course/v1';

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        add_action('rest_api_init', [self::$instance, 'register_rest_routes']);
    }

    public function register_rest_routes() {
        // Get course structure
        register_rest_route($this->api_namespace, '/course/(?P<course_id>\d+)/structure', [
            'methods' => 'GET',
            'callback' => [$this, 'get_course_structure'],
            'permission_callback' => [$this, 'check_edit_permission'],
            'args' => [
                'course_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);

        // Create module
        register_rest_route($this->api_namespace, '/module', [
            'methods' => 'POST',
            'callback' => [$this, 'create_module'],
            'permission_callback' => [$this, 'check_edit_permission'],
            'args' => [
                'course_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'title' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Update module
        register_rest_route($this->api_namespace, '/module/(?P<module_id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_module'],
            'permission_callback' => [$this, 'check_edit_permission'],
            'args' => [
                'module_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'title' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Delete module
        register_rest_route($this->api_namespace, '/module/(?P<module_id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_module'],
            'permission_callback' => [$this, 'check_edit_permission'],
            'args' => [
                'module_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);

        // Create lesson
        register_rest_route($this->api_namespace, '/lesson', [
            'methods' => 'POST',
            'callback' => [$this, 'create_lesson'],
            'permission_callback' => [$this, 'check_edit_permission'],
            'args' => [
                'module_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'title' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Delete lesson
        register_rest_route($this->api_namespace, '/lesson/(?P<lesson_id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_lesson'],
            'permission_callback' => [$this, 'check_edit_permission'],
            'args' => [
                'lesson_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);

        // Move lesson to different module
        register_rest_route($this->api_namespace, '/lesson/(?P<lesson_id>\d+)/move', [
            'methods' => 'PUT',
            'callback' => [$this, 'move_lesson'],
            'permission_callback' => [$this, 'check_edit_permission'],
            'args' => [
                'lesson_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'module_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'position' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param >= 0;
                    }
                ],
            ],
        ]);

        // Update module order
        register_rest_route($this->api_namespace, '/course/(?P<course_id>\d+)/module-order', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_module_order'],
            'permission_callback' => [$this, 'check_edit_permission'],
            'args' => [
                'course_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'module_order' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_array($param) && !empty($param);
                    }
                ],
            ],
        ]);

        // Update lesson order within a module
        register_rest_route($this->api_namespace, '/module/(?P<module_id>\d+)/lesson-order', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_lesson_order'],
            'permission_callback' => [$this, 'check_edit_permission'],
            'args' => [
                'module_id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'lesson_order' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_array($param) && !empty($param);
                    }
                ],
            ],
        ]);
    }

    public function check_edit_permission($request) {
        if (!current_user_can('edit_posts')) {
            return new \WP_Error(
                'rest_forbidden',
                __('You do not have permission to edit posts.', 'lithe-course'),
                ['status' => 403]
            );
        }
        
        // Verify nonce if it exists (for additional security)
        $nonce = $request->get_header('X-WP-Nonce');
        if ($nonce && !wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error(
                'rest_forbidden',
                __('Invalid security token.', 'lithe-course'),
                ['status' => 403]
            );
        }
        
        return true;
    }

    public function get_course_structure($request) {
        $course_id = $request->get_param('course_id');
        
        // Get all modules for this course
        $modules = get_posts([
            'post_type' => 'lithe_module',
            'meta_key' => '_parent_course_id',
            'meta_value' => $course_id,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'posts_per_page' => -1
        ]);

        $structure = [];
        foreach ($modules as $module) {
            // Get lessons for this module
            $lessons = get_posts([
                'post_type' => 'lithe_lesson',
                'meta_key' => '_parent_module_id',
                'meta_value' => $module->ID,
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'posts_per_page' => -1
            ]);

            $lessons_data = [];
            foreach ($lessons as $lesson) {
                $lessons_data[] = [
                    'id' => $lesson->ID,
                    'title' => $lesson->post_title,
                    'edit_link' => get_edit_post_link($lesson->ID),
                    'menu_order' => $lesson->menu_order
                ];
            }

            $structure[] = [
                'id' => $module->ID,
                'title' => $module->post_title,
                'lessons' => $lessons_data,
                'menu_order' => $module->menu_order
            ];
        }

        return new \WP_REST_Response($structure, 200);
    }

    public function create_module($request) {
        $course_id = $request->get_param('course_id');
        $title = $request->get_param('title');

        if (empty($title)) {
            return new \WP_Error(
                'missing_title',
                __('Title is required.', 'lithe-course'),
                ['status' => 400]
            );
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
            return $module_id;
        }

        // Set the parent course using meta
        update_post_meta($module_id, '_parent_course_id', $course_id);

        return new \WP_REST_Response([
            'id' => $module_id,
            'title' => $title,
            'lessons' => [],
            'menu_order' => $menu_order
        ], 201);
    }

    public function update_module($request) {
        $module_id = $request->get_param('module_id');
        $title = $request->get_param('title');

        if (empty($title)) {
            return new \WP_Error(
                'missing_title',
                __('Title is required.', 'lithe-course'),
                ['status' => 400]
            );
        }

        $result = wp_update_post([
            'ID' => $module_id,
            'post_title' => $title
        ]);

        if (is_wp_error($result)) {
            return $result;
        }

        return new \WP_REST_Response([
            'id' => $module_id,
            'title' => $title
        ], 200);
    }

    public function delete_module($request) {
        $module_id = $request->get_param('module_id');

        // Get all lessons for this module
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
            return new \WP_Error(
                'delete_failed',
                __('Failed to delete module.', 'lithe-course'),
                ['status' => 500]
            );
        }

        return new \WP_REST_Response(null, 204);
    }

    public function create_lesson($request) {
        $module_id = $request->get_param('module_id');
        $title = $request->get_param('title');

        if (empty($title)) {
            return new \WP_Error(
                'missing_title',
                __('Title is required.', 'lithe-course'),
                ['status' => 400]
            );
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
            return $lesson_id;
        }

        // Set the parent module using meta
        update_post_meta($lesson_id, '_parent_module_id', $module_id);
        
        // Get the course ID from the module and set it for the lesson
        $course_id = get_post_meta($module_id, '_parent_course_id', true);
        if ($course_id) {
            update_post_meta($lesson_id, '_parent_course_id', $course_id);
        }

        return new \WP_REST_Response([
            'id' => $lesson_id,
            'title' => $title,
            'edit_link' => get_edit_post_link($lesson_id),
            'menu_order' => $menu_order
        ], 201);
    }

    public function delete_lesson($request) {
        $lesson_id = $request->get_param('lesson_id');
        
        $result = wp_delete_post($lesson_id, true);

        if (!$result) {
            return new \WP_Error(
                'delete_failed',
                __('Failed to delete lesson.', 'lithe-course'),
                ['status' => 500]
            );
        }

        return new \WP_REST_Response(null, 204);
    }

    public function update_module_order($request) {
        $course_id = $request->get_param('course_id');
        $module_order = $request->get_param('module_order');

        if (empty($module_order)) {
            return new \WP_Error(
                'missing_module_order',
                __('Module order is required.', 'lithe-course'),
                ['status' => 400]
            );
        }

        // Validate that all modules belong to this course
        foreach ($module_order as $module_id) {
            $parent_course = get_post_meta($module_id, '_parent_course_id', true);
            if ($parent_course != $course_id) {
                return new \WP_Error(
                    'invalid_module',
                    __('Invalid module ID provided.', 'lithe-course'),
                    ['status' => 400]
                );
            }
        }

        // Update module order with sequential numbers
        foreach ($module_order as $order => $module_id) {
            wp_update_post([
                'ID' => $module_id,
                'menu_order' => $order
            ]);
        }

        return new \WP_REST_Response(['success' => true], 200);
    }

    public function update_lesson_order($request) {

        
        $module_id = $request->get_param('module_id');
        $lesson_order = $request->get_param('lesson_order');

        if (empty($lesson_order)) {
            return new \WP_Error(
                'missing_lesson_order',
                __('Lesson order is required.', 'lithe-course'),
                ['status' => 400]
            );
        }

        // Validate that all lessons belong to this module
        foreach ($lesson_order as $lesson_id) {
            $parent_module = get_post_meta($lesson_id, '_parent_module_id', true);
            if ($parent_module != $module_id) {
                return new \WP_Error(
                    'invalid_lesson',
                    __('Invalid lesson ID provided.', 'lithe-course'),
                    ['status' => 400]
                );
            }
        }

        // Update lesson order with sequential numbers
        foreach ($lesson_order as $order => $lesson_id) {
            wp_update_post([
                'ID' => $lesson_id,
                'menu_order' => $order
            ]);
        }

        return new \WP_REST_Response(['success' => true], 200);
    }

    public function move_lesson($request) {

        
        $lesson_id = $request->get_param('lesson_id');
        $new_module_id = $request->get_param('module_id');
        $position = $request->get_param('position');

        // Get current lesson details
        $lesson = get_post($lesson_id);
        if (!$lesson || $lesson->post_type !== 'lithe_lesson') {
            return new \WP_Error(
                'invalid_lesson',
                __('Invalid lesson ID.', 'lithe-course'),
                ['status' => 400]
            );
        }

        // Validate target module exists
        $target_module = get_post($new_module_id);
        if (!$target_module || $target_module->post_type !== 'lithe_module') {
            return new \WP_Error(
                'invalid_module',
                __('Invalid module ID.', 'lithe-course'),
                ['status' => 400]
            );
        }

        // Get current module for validation
        $current_module_id = get_post_meta($lesson_id, '_parent_module_id', true);

        // Update the lesson's parent module
        update_post_meta($lesson_id, '_parent_module_id', $new_module_id);

        // Get course ID from the new module and update lesson
        $course_id = get_post_meta($new_module_id, '_parent_course_id', true);
        if ($course_id) {
            update_post_meta($lesson_id, '_parent_course_id', $course_id);
        }

        // Get all lessons in the target module to reorder them
        $target_lessons = get_posts([
            'post_type' => 'lithe_lesson',
            'meta_key' => '_parent_module_id',
            'meta_value' => $new_module_id,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        // Remove the moved lesson from the array if it's already there
        $target_lessons = array_diff($target_lessons, [$lesson_id]);

        // Insert the moved lesson at the specified position
        array_splice($target_lessons, $position, 0, $lesson_id);

        // Update menu order for all lessons in the target module
        foreach ($target_lessons as $order => $lesson_id_to_update) {
            wp_update_post([
                'ID' => $lesson_id_to_update,
                'menu_order' => $order
            ]);
        }

        // If moving between different modules, reorder the source module as well
        if ($current_module_id && $current_module_id != $new_module_id) {
            $source_lessons = get_posts([
                'post_type' => 'lithe_lesson',
                'meta_key' => '_parent_module_id',
                'meta_value' => $current_module_id,
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);

            // Reorder source module lessons
            foreach ($source_lessons as $order => $source_lesson_id) {
                wp_update_post([
                    'ID' => $source_lesson_id,
                    'menu_order' => $order
                ]);
            }
        }

        return new \WP_REST_Response([
            'success' => true,
            'lesson_id' => $lesson_id,
            'new_module_id' => $new_module_id,
            'position' => $position
        ], 200);
    }
}

CourseStructureAPI::init(); 