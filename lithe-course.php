<?php
/**
 * Plugin Name: Lithe Course
 * Plugin URI: 
 * Description: A custom plugin for WPAA Academy functionality
 * Version: 1.0.0
 * Author: 
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lithe-course
 * Domain Path: /languages
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