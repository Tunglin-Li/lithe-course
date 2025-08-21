<?php

namespace Lithe\Course\Patterns;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LitheCoursePatternCategory {
    public static function init() {
        add_action( 'init', [__CLASS__, 'lithecourse_register_pattern_categories'] );
    }

    public static function lithecourse_register_pattern_categories() {
        register_block_pattern_category( 'lithe-course', array(
            'label'       => __( 'Lithe Course', 'lithe-course' ),
            'description' => __( 'Patterns for Lithe Course.', 'lithe-course' )
        ) );
    }
}

LitheCoursePatternCategory::init();