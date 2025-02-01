<?php
/**
 * Plugin Name: Code045 Plugin Library
 * Description: Manages WordPress plugins through the Code045 Plugin Management System
 * Version: 1.0.0
 * Author: Code045
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/class-code045-plugin-library.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-code045-remote-connection.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/plugins-list-page.php';

// Register settings
add_action('admin_init', 'code045_register_settings');

// Initialize the plugin
function code045_plugin_library_init() {
    $plugin_library = new Code045_Plugin_Library();
    $plugin_library->init();
}
add_action('plugins_loaded', 'code045_plugin_library_init');
?>