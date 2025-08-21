<?php

namespace Lithe\Course\Patterns;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SingleCourseLayout {
    public static function init() {
        add_action( 'init', [__CLASS__, 'lithecourse_register_course_layout_pattern'] );
    }

    public static function lithecourse_register_course_layout_pattern() {
        register_block_pattern( 'lithecourse/course-layout', array(
            'title'      => __( 'Single Course Layout', 'lithe-course' ),
            'categories' => array( 'lithe-course' ),
            'content'    => '<!-- wp:group {"metadata":{"categories":["lithe-course"],"patternName":"lithecourse/course-layout","name":"Single Course Layout"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:group {"className":"is-style-default","style":{"position":{"type":"sticky","top":"0px"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-default"><!-- wp:post-terms {"term":"lithecourse_category"} /-->

<!-- wp:post-title /-->

<!-- wp:lithecourse/enrolled-student /-->

<!-- wp:post-excerpt /-->

<!-- wp:lithecourse/course-metadata /-->

<!-- wp:lithecourse/enrollment-button /--></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:lithecourse/course-video /-->

<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">' . esc_html__('What you\'ll learn', 'lithe-course') . '</h2>
<!-- /wp:heading -->

<!-- wp:lithecourse/course-metadata {"metaType":"learnings"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"is-style-default","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-default"><!-- wp:heading -->
<h2 class="wp-block-heading">' . esc_html__('Requirements', 'lithe-course') . '</h2>
<!-- /wp:heading -->

<!-- wp:lithecourse/course-metadata {"metaType":"prerequisites"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">' . esc_html__('Who this course is for', 'lithe-course') . '</h2>
<!-- /wp:heading -->

<!-- wp:lithecourse/course-metadata {"metaType":"suitableFor"} /--></div>
<!-- /wp:group -->

<!-- wp:post-content /-->

<!-- wp:lithecourse/course-outline {"style":{"border":{"radius":"4px"}}} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->'
        ) );
    }
}

SingleCourseLayout::init();
