<?php

namespace Lithe\Course\Blocks;

class RestrictBlocks {
    public static function init() {
        $class = new self();
        add_filter('allowed_block_types_all', [$class, 'restrict_course_blocks'], 10, 2);
    }

    /**
     * Restrict course blocks to only appear in template editor
     */
    public function restrict_course_blocks($allowed_blocks, $block_editor_context) {
        // List of your course blocks to restrict
        $course_blocks = [
            'lithe-course/course-outline',
            'lithe-course/course-metadata', 
            'lithe-course/course-video',
            'lithe-course/enrollment-button',
            'lithe-course/lesson-sidebar',
            'lithe-course/enrolled-student',

        ];

        // Check if we're in template editor
        $is_template_editor = $this->is_template_editor($block_editor_context);
        
        // If we're in template editor, allow all blocks (return original value)
        if ($is_template_editor) {
            return $allowed_blocks;
        }

        // If we're not in template editor, we need to restrict course blocks
        // Handle different types of $allowed_blocks values
        if ($allowed_blocks === true || $allowed_blocks === null) {
            // All blocks are allowed by default, get all registered blocks and remove course blocks
            $all_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
            $all_block_names = array_keys($all_blocks);
            return array_values(array_diff($all_block_names, $course_blocks));
        } elseif (is_array($allowed_blocks)) {
            // Specific blocks are allowed, remove course blocks from the list
            return array_values(array_diff($allowed_blocks, $course_blocks));
        }

        // Fallback: return original value if we can't handle it
        return $allowed_blocks;
    }

    /**
     * Check if we should allow course blocks (only in specific contexts)
     */
    private function is_template_editor($block_editor_context) {
        // Check if we have a post context
        if (isset($block_editor_context->post)) {
            $post = $block_editor_context->post;
            
            // Allow course blocks ONLY in course and lesson post types
            if ($post->post_type === 'lithe_course' || $post->post_type === 'lithe_lesson') {
                return true;
            }
            
            // For all other post types (including pages), restrict course blocks
            return false;
        }

        // Check if we're in the Site Editor (FSE template editing)
        global $pagenow;
        if ($pagenow === 'site-editor.php') {
            return true;
        }

        // Check for Gutenberg plugin site editor
        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($page === 'gutenberg-edit-site') {
            return true;
        }

        // Check if we're editing a wp_template post type
        $post_type = filter_input(INPUT_GET, 'postType', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($post_type === 'wp_template') {
            return true;
        }

        // Check if we're editing a wp_template_part post type  
        if ($post_type === 'wp_template_part') {
            return true;
        }

        // All other contexts should restrict course blocks
        return false;
    }
}

RestrictBlocks::init();
