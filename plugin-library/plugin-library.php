<?php
/**
 * Plugin Name: Plugin Library
 * Description: Provides zip files for installed plugins and manages plugins through the Code045 Plugin Management System.
 * Version: 1.0.0
 * Author: Code045
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/zip-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/api-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-rest-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-server.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-client.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-remote-connection.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/server-settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/client-settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/plugins-list-page.php';

// Register settings
function plugin_library_register_settings() {
    register_setting('plugin_library_client_settings', 'plugin_library_client_remote_url');
    register_setting('plugin_library_client_settings', 'plugin_library_client_api_key');
    register_setting('plugin_library_server_settings', 'plugin_library_mode');
}
add_action('admin_init', 'plugin_library_register_settings');

// Initialize the plugin
function plugin_library_init() {
    $mode = get_option('plugin_library_mode', 'server'); // Default to server mode

    // Always add the settings page
    add_action('admin_menu', 'plugin_library_add_settings_page');

    if ($mode === 'server') {
        // Server mode
        add_action('admin_menu', 'plugin_library_server_add_admin_menu');
        add_action('rest_api_init', 'plugin_library_rest_api_init');
    } else {
        // Client mode
        add_action('admin_menu', 'plugin_library_client_add_admin_menu');
    }
}
add_action('plugins_loaded', 'plugin_library_init');

// Add settings page
function plugin_library_add_settings_page() {
    add_menu_page(
        'Plugin Library Settings',
        'Plugin Library Settings',
        'manage_options',
        'plugin-library-settings',
        'plugin_library_server_settings_page'
    );
}

// Add admin menu for server mode
function plugin_library_server_add_admin_menu() {
    add_submenu_page(
        'plugin-library-settings',
        'Installed Plugins',
        'Installed Plugins',
        'manage_options',
        'plugin-library-plugins',
        'plugin_library_plugins_list_page'
    );
}

// Add admin menu for client mode
function plugin_library_client_add_admin_menu() {
    add_submenu_page(
        'plugin-library-settings',
        'Installed Plugins',
        'Installed Plugins',
        'manage_options',
        'plugin-library-client-plugins',
        'plugin_library_plugins_list_page'
    );
}

// Initialize the REST API for server mode
function plugin_library_rest_api_init() {
    $rest_api = new Plugin_Library_REST_API();
    $rest_api->init();
}
?>