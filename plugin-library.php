<?php
/*
 * Plugin Name: Plugin Library
 * Description: Provides zip files for installed plugins and manages plugins through the Code045 Plugin Management System.
 * Version: 2.2.0
 * Author: Code045
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-server.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-client.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/dashboard-page.php';

// Initialize the plugin
function plugin_library_init() {
    // Conditionally include and initialize the correct class based on the mode
    $mode = get_option('plugin_library_mode', 'client'); // Default to client mode

    if ($mode === 'server') {
        $server = new Plugin_Library_Server();
        $server->create_custom_table();
    } elseif ($mode === 'client') {
        $client = new Plugin_Library_Client();
        $client->create_custom_table();
    }
}
add_action('plugins_loaded', 'plugin_library_init');

// Add settings page to the admin menu
function plugin_library_add_settings_page() {
    add_menu_page(
        'Plugin Library',
        'Plugin Library',
        'manage_options',
        'plugin-library-dashboard',
        'plugin_library_dashboard_page'
    );
}
add_action('admin_menu', 'plugin_library_add_settings_page');