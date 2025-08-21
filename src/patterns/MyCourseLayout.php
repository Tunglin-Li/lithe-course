<?php

namespace Lithe\Course\Patterns;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MyCourseLayout {
    public static function init() {
        add_action( 'init', [__CLASS__, 'lithecourse_register_my_course_layout_pattern'] );
    }

    public static function lithecourse_register_my_course_layout_pattern() {
        register_block_pattern( 'lithecourse/my-course-layout', array(
            'title'      => __( 'My Course Layout', 'lithe-course' ),
            'categories' => array( 'lithe-course' ),
            'content'    => '<!-- wp:group {"metadata":{"categories":["lithe-course"],"patternName":"lithecourse/my-course-layout","name":"My Course Layout"},"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull"><!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull"><!-- wp:heading {"align":"wide"} -->
<h2 class="wp-block-heading alignwide">' . esc_html__('My Course', 'lithe-course') . '</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":8,"query":{"postType":"lithecourse_course","offset":0,"filterByEnrolled":true,"perPage":9},"namespace":"lithecourse-my-course-list","align":"wide"} -->
<div class="wp-block-query alignwide"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group -->
<div class="wp-block-group"><!-- wp:post-featured-image {"isLink":true} /-->

<!-- wp:post-author-name /-->

<!-- wp:post-title {"level":3,"isLink":true} /-->

<!-- wp:post-excerpt /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->'
        ) );
    }
}

MyCourseLayout::init();
