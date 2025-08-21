<?php
/**
 * Uninstall script for Lithe Course plugin
 * 
 * This file is executed when the plugin is deleted through the WordPress admin.
 * It removes only plugin settings while preserving all user data and content.
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options (only plugin settings, not user data)
delete_option('LITHECOURSE_VERSION');

// Flush rewrite rules to clean up custom post type rules
flush_rewrite_rules();