<?php
/**
 * Plugin Name: Lithe Course
 * Plugin URI: https://github.com/Tunglin-Li/lithe-course
 * Description: A comprehensive learning management system (LMS) plugin for WordPress with modern blocks, course organization, and student enrollment features.
 * Version: 1.0.0
 * Author: Tunglin Li
 * Author URI: https://github.com/Tunglin-Li/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lithe-course
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 8.0
 * 
 * Development: This plugin uses @wordpress/scripts for building blocks.
 * Source code is available in the blocks/ directory and on GitHub.
 * 
 * Third-party libraries used:
 * - @dnd-kit (MIT License): https://github.com/clauderic/dnd-kit
 * - Motion (MIT License): https://github.com/motion-dev/motion
 * - @wordpress/icons (GPL v2+): https://github.com/WordPress/gutenberg
 * - @wordpress/scripts (GPL v2+): https://github.com/WordPress/gutenberg

 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LITHECOURSE_VERSION', '1.0.0');
define('LITHECOURSE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LITHECOURSE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LITHECOURSE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load Composer autoloader
require_once LITHECOURSE_PLUGIN_DIR . '/vendor/autoload.php';
\A7\autoload(LITHECOURSE_PLUGIN_DIR . 'src');

