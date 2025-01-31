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

// Register settings
function code045_register_settings() {
    register_setting('code045_plugin_library_settings', 'code045_remote_url');
    register_setting('code045_plugin_library_settings', 'code045_remote_username');
    register_setting('code045_plugin_library_settings', 'code045_remote_password');
}
add_action('admin_init', 'code045_register_settings');

// Initialize the plugin
function code045_plugin_library_init() {
    $plugin_library = new Code045_Plugin_Library();
    $plugin_library->init();
}
add_action( 'plugins_loaded', 'code045_plugin_library_init' );
?>