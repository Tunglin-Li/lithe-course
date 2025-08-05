<?php

namespace Lithe\Course\Posts\Course;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Field {
    private static $instance = null;

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        add_action('init', [self::$instance, 'lithe_course_register_meta_fields']);
    }

    public function lithe_course_register_meta_fields() {
        // Register course type meta field
        register_post_meta('lithe_course', '_course_type', [
            'type' => 'string',
            'single' => true,
            'default' => 'free',
            'show_in_rest' => true,
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
        
        // Register video meta
        register_post_meta('lithe_course', '_video', [
            'type' => 'object',
            'single' => true,
            'show_in_rest' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'video_url' => [
                            'type' => 'string',
                            'format' => 'uri'
                        ],
                        'video_platform' => [
                            'type' => 'string',
                            'enum' => ['youtube', 'vimeo', 'bunnycdn']
                        ],
                        'video_id' => [
                            'type' => 'string'
                        ]
                    ]
                ]
            ],
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);

        // Register course information meta fields used by blocks
        $course_info_fields = ['features', 'learnings', 'suitable', 'requirements'];
        
        foreach ($course_info_fields as $field) {
            register_post_meta('lithe_course', "_{$field}", [
                'type' => 'array',
                'single' => true,
                'show_in_rest' => [
                    'schema' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'text' => [
                                    'type' => 'string'
                                ]
                            ]
                        ]
                    ]
                ],
                'auth_callback' => function($allowed, $meta_key, $post_id) {
                    return current_user_can('edit_post', $post_id);
                }
            ]);
        }
    }
}

Field::init();