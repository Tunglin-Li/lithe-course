<?php

namespace Lithe\Course\Posts\Lesson;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LessonMeta {
    private static $instance = null;
    private static $meta_key = 'lithe_course_completed_lessons';

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
    }

    public static function lithe_course_get_lesson_completion_status($lesson_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }

        $completed_lessons = get_user_meta($user_id, self::$meta_key, true);
        
        if (!is_array($completed_lessons)) {
            $completed_lessons = array();
        }

        return in_array($lesson_id, $completed_lessons);
    }

    public static function lithe_course_update_lesson_completion_status($lesson_id, $completed, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }

        $completed_lessons = get_user_meta($user_id, self::$meta_key, true);
        
        if (!is_array($completed_lessons)) {
            $completed_lessons = array();
        }

        if ($completed && !in_array($lesson_id, $completed_lessons)) {
            $completed_lessons[] = $lesson_id;
        } else if (!$completed) {
            $completed_lessons = array_diff($completed_lessons, [$lesson_id]);
        }

        return update_user_meta($user_id, self::$meta_key, $completed_lessons);
    }
}

LessonMeta::init(); 