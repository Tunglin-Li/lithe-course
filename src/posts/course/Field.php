<?php

namespace Lithe\Course\Posts\Course;

class Field {
    private static $instance = null;

    private $metaboxes = [];

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        add_action('init', [self::$instance, 'init_metaboxes']);
        add_action('init', [self::$instance, 'register_meta_fields']);
        add_action('init', [self::$instance, 'register_postmeta']);
        // Removed classic metaboxes to avoid conflicts with blocks
        // add_action('add_meta_boxes', [self::$instance, 'add_meta_boxes']);
        // add_action('save_post_lithe_course', [self::$instance, 'save_course_meta']);
        // add_action('admin_enqueue_scripts', [self::$instance, 'enqueue_admin_scripts']);
    }


    public function register_postmeta() {
        register_post_meta('lithe_course', 'tester', [
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

    public function init_metaboxes() {
        $this->metaboxes = [
            'features' => [
                'id' => 'lithe_course_features',
                'title' => __('Course Features', 'lithe-course'),
                'has_icon' => false,
            ],
            'learnings' => [
                'id' => 'lithe_course_learnings',
                'title' => __('What You Will Learn', 'lithe-course'),
                'has_icon' => false,
            ],
            'suitable' => [
                'id' => 'lithe_course_suitable',
                'title' => __('Who This Course Is For', 'lithe-course'),
                'has_icon' => false,
            ],
            'requirements' => [
                'id' => 'lithe_course_requirements',
                'title' => __('Prerequisites', 'lithe-course'),
                'has_icon' => false,
            ]
        ];
    }

    // Commented out to avoid conflicts with blocks
    /*
    public function enqueue_admin_scripts($hook) {
        global $post;

        if ($hook == 'post.php' || $hook == 'post-new.php') {
            if ('lithe_course' === $post->post_type) {
                wp_enqueue_style(
                    'wpaa-admin-style',
                    LITHE_COURSE_PLUGIN_URL . 'assets/css/admin.css',
                    [],
                    LITHE_COURSE_VERSION
                );

                wp_enqueue_script(
                    'lithe-course-features',
                    LITHE_COURSE_PLUGIN_URL . 'assets/js/course-features.js',
                    ['jquery', 'jquery-ui-sortable'],
                    LITHE_COURSE_VERSION,
                    true
                );
            }
        }
    }
    */

    // Commented out to avoid conflicts with blocks
    /*
    public function add_meta_boxes() {
        foreach ($this->metaboxes as $key => $metabox) {
            add_meta_box(
                $metabox['id'],
                $metabox['title'],
                [$this, 'render_metabox'],
                'lithe_course',
                'normal',
                'high',
                ['key' => $key],
            );
        }
    }
    */

    // Commented out to avoid conflicts with blocks
    /*
    public function render_metabox($post, $metabox) {
        $key = $metabox['args']['key'];
        $config = $this->metaboxes[$key];

        wp_nonce_field("{$config['id']}_nonce", "{$config['id']}_nonce");
        $items = get_post_meta($post->ID, "_{$key}", true) ?: [];
        ?>
        <div class="wpaa-repeatable-items" data-type="<?php echo esc_attr($key); ?>">
            <div class="items-list">
                <?php
                if (!empty($items)) {
                    foreach ($items as $index => $item) {
                        $this->render_item_row($key, $item, $index);
                    }
                }
                ?>
            </div>
            <button type="button" class="button button-secondary add-item">
                <?php _e('Add New Item', 'lithe-course'); ?>
            </button>
        </div>

        <script type="text/template" id="tmpl-<?php echo $key; ?>-row">
            <?php $this->render_item_row($key, ['text' => ''], '{{data.index}}'); ?>
        </script>
        <?php
    }
    */

    // Commented out to avoid conflicts with blocks
    /*
    private function render_item_row($key, $item, $index) {
        ?>
        <div class="item-row">
            <span class="dashicons dashicons-menu handle"></span>
            <input type="text" name="<?php echo $key; ?>[<?php echo $index; ?>][text]" 
                value="<?php echo esc_attr($item['text']); ?>" 
                class="item-text" placeholder="<?php _e('Enter description', 'lithe-course'); ?>">
            <button type="button" class="button button-link-delete remove-item">
                <span class="dashicons dashicons-no"></span>
            </button>
        </div>
        <?php
    }
    */

    // Commented out to avoid conflicts with blocks
    /*
    public function save_course_meta($post_id) {
        foreach ($this->metaboxes as $key => $metabox) {
            if (!isset($_POST["{$metabox['id']}_nonce"]) || 
                !wp_verify_nonce($_POST["{$metabox['id']}_nonce"], "{$metabox['id']}_nonce")) {
                continue;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                continue;
            }

            if (isset($_POST[$key])) {
                $items = array_values(array_map(function($item) {
                    return [
                        'text' => sanitize_text_field($item['text'])
                    ];
                }, $_POST[$key]));

                update_post_meta($post_id, "_{$key}", $items);
            } else {
                delete_post_meta($post_id, "_{$key}");
            }
        }
    }
    */

    public function get_course_meta($course_id, $key) {
        $items = get_post_meta($course_id, "_{$key}", true) ?: [];
        return $items;
    }

    public function register_meta_fields() {
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

        // Register other meta fields
        foreach ($this->metaboxes as $key => $metabox) {
            register_post_meta('lithe_course', "_{$key}", [
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