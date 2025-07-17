<?php

namespace Lithe\Course\Admin;

class CourseOrganizer {
    private static $instance = null;

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        add_action('admin_menu', [self::$instance, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [self::$instance, 'enqueue_scripts']);
        add_action('wp_ajax_update_course_structure', [self::$instance, 'update_course_structure']);
    }

    public function add_menu_page() {
        add_submenu_page(
            'edit.php?post_type=lithe_course',
            __('Course Organizer', 'lithe-course'),
            __('Course Organizer', 'lithe-course'),
            'edit_posts',
            'course-organizer',
            [$this, 'render_page']
        );
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'lithe_course_page_course-organizer') {
            return;
        }

        wp_enqueue_style('lithe-course-organizer', LITHE_COURSE_PLUGIN_URL . 'assets/css/course-organizer.css', [], LITHE_COURSE_VERSION);
        wp_enqueue_script(
            'lithe-course-organizer',
            LITHE_COURSE_PLUGIN_URL . 'assets/js/course-organizer.js',
            ['jquery', 'jquery-ui-sortable', 'jquery-ui-droppable'],
            LITHE_COURSE_VERSION,
            true
        );

        wp_localize_script('lithe-course-organizer', 'wpaaOrganizer', [
            'nonce' => wp_create_nonce('lithe_course_organizer'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'i18n' => [
                'confirmSave' => __('Are you sure you want to save the changes?', 'lithe-course'),
                'error' => __('An error occurred. Please try again.', 'lithe-course'),
                'saved' => __('Changes saved successfully.', 'lithe-course')
            ]
        ]);
    }

    public function render_page() {
        $courses = get_posts([
            'post_type' => 'lithe_course',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        // Get the selected course ID from URL parameter or the first course
        $selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 
            (!empty($courses) ? $courses[0]->ID : 0);

        ?>
        <div class="wrap">
            <h1><?php _e('Course Organizer', 'lithe-course'); ?></h1>

            <div class="lithe-course-selector">
                <form method="get" action="">
                    <input type="hidden" name="post_type" value="lithe_course">
                    <input type="hidden" name="page" value="course-organizer">
                    <select name="course_id" id="course_id" class="widefat" style="max-width: 300px; margin-right: 10px; display: inline-block; vertical-align: middle;">
                        <?php foreach ($courses as $course) : ?>
                            <option value="<?php echo $course->ID; ?>" <?php selected($selected_course_id, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button"><?php _e('Select Course', 'lithe-course'); ?></button>
                </form>
            </div>
            
            <?php if ($selected_course_id) : ?>
                <div class="lithe-course-organizer">
                    <div class="lithe-course-list">
                        <div class="lithe-course" data-id="<?php echo $selected_course_id; ?>">
                            <h2><?php echo get_the_title($selected_course_id); ?></h2>
                            <div class="lithe-modules-container">
                                <?php
                                $modules = get_posts([
                                    'post_type' => 'lithe_module',
                                    'posts_per_page' => -1,
                                    'meta_key' => '_parent_course_id',
                                    'meta_value' => $selected_course_id,
                                    'orderby' => 'menu_order title',
                                    'order' => 'ASC'
                                ]);

                                foreach ($modules as $module) :
                                ?>
                                    <div class="lithe-module" data-id="<?php echo $module->ID; ?>">
                                        <div class="module-header">
                                            <span class="dashicons dashicons-menu handle"></span>
                                            <h3><?php echo esc_html($module->post_title); ?></h3>
                                        </div>
                                        <div class="lithe-lessons-container">
                                            <?php
                                            $lessons = get_posts([
                                                'post_type' => 'lithe_lesson',
                                                'posts_per_page' => -1,
                                                'meta_key' => '_parent_module_id',
                                                'meta_value' => $module->ID,
                                                'orderby' => 'menu_order title',
                                                'order' => 'ASC'
                                            ]);

                                            foreach ($lessons as $lesson) :
                                            ?>
                                                <div class="lithe-lesson" data-id="<?php echo $lesson->ID; ?>">
                                                    <span class="dashicons dashicons-menu handle"></span>
                                                    <?php echo esc_html($lesson->post_title); ?>
                                                    <div class="lesson-actions">
                                                        <a href="<?php echo get_edit_post_link($lesson->ID); ?>" class="button button-small">
                                                            <?php _e('Edit', 'lithe-course'); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="wpaa-unassigned">
                        <h2><?php _e('Unassigned Items', 'lithe-course'); ?></h2>
                        <div class="wpaa-unassigned-modules">
                            <h3><?php _e('Modules', 'lithe-course'); ?></h3>
                            <?php
                            $unassigned_modules = get_posts([
                                'post_type' => 'lithe_module',
                                'posts_per_page' => -1,
                                'meta_query' => [
                                    [
                                        'key' => '_parent_course_id',
                                        'compare' => 'NOT EXISTS'
                                    ]
                                ],
                                'orderby' => 'title',
                                'order' => 'ASC'
                            ]);

                            if (!empty($unassigned_modules)) :
                                foreach ($unassigned_modules as $module) :
                                ?>
                                    <div class="lithe-module" data-id="<?php echo $module->ID; ?>">
                                        <span class="dashicons dashicons-menu handle"></span>
                                        <?php echo esc_html($module->post_title); ?>
                                    </div>
                                <?php endforeach;
                            else : ?>
                                <p class="no-items"><?php _e('No unassigned modules', 'lithe-course'); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="wpaa-unassigned-lessons">
                            <h3><?php _e('Lessons', 'lithe-course'); ?></h3>
                            <?php
                            $unassigned_lessons = get_posts([
                                'post_type' => 'lithe_lesson',
                                'posts_per_page' => -1,
                                'meta_query' => [
                                    [
                                        'key' => '_parent_module_id',
                                        'compare' => 'NOT EXISTS'
                                    ]
                                ],
                                'orderby' => 'title',
                                'order' => 'ASC'
                            ]);

                            if (!empty($unassigned_lessons)) :
                                foreach ($unassigned_lessons as $lesson) :
                                ?>
                                    <div class="lithe-lesson" data-id="<?php echo $lesson->ID; ?>">
                                        <span class="dashicons dashicons-menu handle"></span>
                                        <?php echo esc_html($lesson->post_title); ?>
                                        <div class="lesson-actions">
                                            <a href="<?php echo get_edit_post_link($lesson->ID); ?>" class="button button-small">
                                                <?php _e('Edit', 'lithe-course'); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach;
                            else : ?>
                                <p class="no-items"><?php _e('No unassigned lessons', 'lithe-course'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="lithe-course-actions" style="margin-top: 20px;">
                    <a href="<?php echo admin_url('post-new.php?post_type=lithe_module&course_id=' . $selected_course_id); ?>" class="button button-primary">
                        <?php _e('Add New Module', 'lithe-course'); ?>
                    </a>
                    <a href="<?php echo admin_url('post-new.php?post_type=lithe_lesson'); ?>" class="button">
                        <?php _e('Add New Lesson', 'lithe-course'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function update_course_structure() {
        check_ajax_referer('lithe_course_organizer', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $data = json_decode(stripslashes($_POST['structure']), true);

        foreach ($data as $course_id => $course_data) {
            if (isset($course_data['modules'])) {
                foreach ($course_data['modules'] as $order => $module_data) {
                    $module_id = $module_data['id'];
                    
                    // Update module order and set parent course using meta
                    wp_update_post([
                        'ID' => $module_id,
                        'menu_order' => $order
                    ]);
                    
                    // Set parent course via meta
                    update_post_meta($module_id, '_parent_course_id', $course_id);

                    if (isset($module_data['lessons'])) {
                        foreach ($module_data['lessons'] as $lesson_order => $lesson_id) {
                            // Update lesson order
                            wp_update_post([
                                'ID' => $lesson_id,
                                'menu_order' => $lesson_order
                            ]);
                            
                            // Set parent module via meta
                            update_post_meta($lesson_id, '_parent_module_id', $module_id);
                            
                            // Set parent course via meta (for quicker queries)
                            update_post_meta($lesson_id, '_parent_course_id', $course_id);
                        }
                    }
                }
            }
        }

        wp_send_json_success();
    }
}

CourseOrganizer::init(); 