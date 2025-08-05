<?php

namespace Lithe\Course\Template;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TemplateRegistration {
    public static function init() {
        $class = new self();
        add_filter('get_block_templates', [$class, 'add_block_templates'], 10, 3);
    }

    public function add_block_templates($query_result, $query, $template_type) {
        // Only add templates for 'wp_template' type
        if ($template_type !== 'wp_template') {
            return $query_result;
        }

        // Check for single-lithe_course template specifically
        $template_file_path = LITHE_COURSE_PLUGIN_DIR . 'src/template/templates/single-lithe_course.html';
        
        if (file_exists($template_file_path)) {
            // Check if this template is already in the results
            $template_exists = false;
            foreach ($query_result as $template) {
                if (isset($template->slug) && $template->slug === 'single-lithe_course') {
                    $template_exists = true;
                    break;
                }
            }
            
            // Only add if it doesn't already exist
            if (!$template_exists) {
                $html = file_get_contents($template_file_path);
                $single_course_template = new \WP_Block_Template();
                $single_course_template->id = 'lithe-course//single-lithe_course';
                $single_course_template->theme = get_stylesheet();
                $single_course_template->slug = 'single-lithe_course';
                $single_course_template->source = 'plugin';
                $single_course_template->type = 'wp_template';
                $single_course_template->title = __('Single Course', 'lithe-course');
                $single_course_template->description = __('Template for displaying a single course (by Lithe Course)', 'lithe-course');
                $single_course_template->status = 'publish';
                $single_course_template->has_theme_file = false;
                $single_course_template->is_custom = false;
                $single_course_template->author = 0;
                $single_course_template->plugin = 'Lithe Course';
                $single_course_template->wp_id = null;
                $single_course_template->area = 'uncategorized';
                $single_course_template->content = $html;
                
                $query_result[] = $single_course_template;
            }
        }
        
        return $query_result;
    }
}

// TemplateRegistration::init();