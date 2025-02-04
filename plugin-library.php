<?php
/**
 * Plugin Name: Plugin Library
 * Description: Provides zip files for installed plugins and manages plugins through the Code045 Plugin Management System.
 * Version: 2.0.0
 * Author: Code045
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/zip-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-rest-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-remote-connection.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/client-plugins-list-page.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/server-plugins-list-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/rest-api.php';

// Register settings
function plugin_library_register_settings() {
    register_setting('plugin_library_client_settings', 'plugin_library_client_remote_url');
    register_setting('plugin_library_client_settings', 'plugin_library_client_username');
    register_setting('plugin_library_client_settings', 'plugin_library_client_password');
    register_setting('plugin_library_server_settings', 'plugin_library_mode');
}
add_action('admin_init', 'plugin_library_register_settings');

// Initialize the plugin
function plugin_library_init() {
    // Always add the settings page
    add_action('admin_menu', 'plugin_library_add_settings_page');
    add_action('rest_api_init', 'plugin_library_rest_api_init');
    add_action('admin_menu', 'plugin_library_plugins_add_admin_menu');
    add_action('wp_enqueue_scripts', 'plugin_library_enqueue_fontawesome');
}

add_action('plugins_loaded', 'plugin_library_init');

// Add settings page
function plugin_library_add_settings_page() {
    add_menu_page(
        'Plugin Library Settings',
        'Plugin Library Settings',
        'manage_options',
        'plugin-library',
        'plugin_library_settings_page'
    );
}

// Add plugins list page
function plugin_library_plugins_add_admin_menu() {
    add_submenu_page(
        'plugin-library',
        'Plugins List',
        'Plugins List',
        'manage_options',
        'plugin-library-plugins',
        'plugin_library_plugins_list_page'
    );
}

// Enqueue FontAwesome
function plugin_library_enqueue_fontawesome() {
    wp_enqueue_style('plugin-library-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}

// Display the appropriate plugins list page based on the mode
function plugin_library_plugins_list_page() {
    $mode = get_option('plugin_library_mode', ''); // Default to empty

    if ($mode === 'client') {
        plugin_library_client_plugins_list_page();
    } elseif ($mode === 'server') {
        plugin_library_server_plugins_list_page();
    } else {
        echo '<div class="notice notice-warning"><p>Please set the mode to either "client" or "server" in the Plugin Library settings page.</p></div>';
    }
}
?>


