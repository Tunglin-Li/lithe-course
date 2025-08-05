<?php

namespace Lithe\Course\Posts\Lesson;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LessonCompletion {
    private static $instance = null;

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        add_action('wp_ajax_update_lesson_completion', [self::$instance, 'lithe_course_handle_completion_update']);
    }

    public function lithe_course_handle_completion_update() {
        check_ajax_referer('lesson_completion_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        $completed = isset($_POST['completed']) ? filter_var(wp_unslash($_POST['completed']), FILTER_VALIDATE_BOOLEAN) : false;

        if (!$lesson_id) {
            wp_send_json_error('Invalid lesson ID');
        }

        $result = LessonMeta::lithe_course_update_lesson_completion_status($lesson_id, $completed);

        if ($result) {
            wp_send_json_success([
                'completed' => $completed,
                'lesson_id' => $lesson_id
            ]);
        } else {
            wp_send_json_error('Failed to update completion status');
        }
    }
}

LessonCompletion::init(); 