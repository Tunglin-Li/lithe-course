<?php
/**
 * Plugin Name: Lithe Course
 * Plugin URI: https://github.com/your-username/lithe-course
 * Description: A comprehensive learning management system (LMS) plugin for WordPress with modern blocks, course organization, and student enrollment features.
 * Version: 1.0.0
 * Author: Tunglin Li
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lithe-course
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 8.0
 * Network: false
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LITHE_COURSE_VERSION', '1.0.0');
define('LITHE_COURSE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LITHE_COURSE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LITHE_COURSE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load Composer autoloader
require_once LITHE_COURSE_PLUGIN_DIR . '/vendor/autoload.php';
\A7\autoload(LITHE_COURSE_PLUGIN_DIR . 'src');

// Plugin activation/deactivation hooks
register_activation_hook(__FILE__, 'lithe_course_activate');
register_deactivation_hook(__FILE__, 'lithe_course_deactivate');

/**
 * Plugin activation hook
 */
function lithe_course_activate() {
    // Flush rewrite rules to ensure post types are registered
    flush_rewrite_rules();
    
    // Set default options if needed
    if (!get_option('lithe_course_version')) {
        add_option('lithe_course_version', LITHE_COURSE_VERSION);
    }
}

/**
 * Plugin deactivation hook
 */
function lithe_course_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Load plugin text domain for internationalization
 */
function lithe_course_load_textdomain() {
    load_plugin_textdomain(
        'lithe-course',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}
add_action('plugins_loaded', 'lithe_course_load_textdomain');