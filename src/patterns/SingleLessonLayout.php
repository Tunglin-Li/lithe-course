<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'init', 'lithe_course_register_lesson_layout_pattern' );

function lithe_course_register_lesson_layout_pattern() {
	register_block_pattern( 'lithe-course/lesson-layout', array(
		'title'      => __( 'Single Lesson Layout', 'lithe-course' ),
		'categories' => array( 'lithe-course' ),
		'content'    => '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:post-content /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:lithe-course/lesson-sidebar {"style":{"border":{"radius":"4px"}}} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->'
	) );
}