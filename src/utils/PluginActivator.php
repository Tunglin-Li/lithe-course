<?php

namespace Lithe\Course\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class PluginActivator {
    /**
     * Initialize the plugin activator
     * 
     * @param string $plugin_file The main plugin file path
     */
    public static function init() {
        $class = new self();
        register_activation_hook(LITHECOURSE_PLUGIN_BASENAME, [$class, 'lithecourse_activate']);
        register_deactivation_hook(LITHECOURSE_PLUGIN_BASENAME, [$class, 'lithecourse_deactivate']);
    }

    /**
     * Plugin activation hook
     */
    public static function lithecourse_activate() {
        // Flush rewrite rules to include our custom post types
        // Post types will be registered through normal WordPress hooks
        do_action( 'init' );
        flush_rewrite_rules();
        
        // Set default options if needed
        if (!get_option('LITHECOURSE_VERSION')) {
            add_option('LITHECOURSE_VERSION', LITHECOURSE_VERSION);
        }
    }

    /**
     * Plugin deactivation hook
     */
    public static function lithecourse_deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

PluginActivator::init();

