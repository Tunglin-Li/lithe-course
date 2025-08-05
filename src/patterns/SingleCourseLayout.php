<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'init', 'lithe_course_register_course_layout_pattern' );

function lithe_course_register_course_layout_pattern() {
	register_block_pattern( 'lithe-course/course-layout', array(
		'title'      => __( 'Single Course Layout', 'lithe-course' ),
		'categories' => array( 'lithe-course' ),
		'content'    => '<!-- wp:group {"metadata":{"categories":["lithe-course"],"patternName":"lithe-course/course-layout","name":"Single Course Layout"},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:group {"className":"is-style-default","style":{"position":{"type":"sticky","top":"0px"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-default"><!-- wp:post-terms {"term":"lithe_course_category"} /-->

<!-- wp:post-title /-->

<!-- wp:lithe-course/enrolled-student /-->

<!-- wp:post-excerpt /-->

<!-- wp:lithe-course/course-metadata /-->

<!-- wp:lithe-course/enrollment-button /--></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:lithe-course/course-video /-->

<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">What you\'ll learn</h2>
<!-- /wp:heading -->

<!-- wp:lithe-course/course-metadata {"metaType":"learnings"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"is-style-default","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-default"><!-- wp:heading -->
<h2 class="wp-block-heading">Requirements</h2>
<!-- /wp:heading -->

<!-- wp:lithe-course/course-metadata {"metaType":"prerequisites"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">Who this course is for</h2>
<!-- /wp:heading -->

<!-- wp:lithe-course/course-metadata {"metaType":"suitableFor"} /--></div>
<!-- /wp:group -->

<!-- wp:post-content /-->

<!-- wp:lithe-course/course-outline {"style":{"border":{"radius":"4px"}}} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->'
	) );
}
