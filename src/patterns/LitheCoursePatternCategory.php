<?php

add_action( 'init', 'lithe_course_register_pattern_categories' );

function lithe_course_register_pattern_categories() {
	register_block_pattern_category( 'lithe-course', array(
		'label'       => __( 'Lithe Course', 'lithe-course' ),
		'description' => __( 'Patterns for Lithe Course.', 'lithe-course' )
	) );
}