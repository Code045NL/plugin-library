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
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-rest-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-library-remote-connection.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/plugins-list-page.php';
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
	add_action( 'admin_enqueue_scripts', 'enqueue_admin_style' );
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

// Add admin menu for server mode
function plugin_library_plugins_add_admin_menu() {
    add_submenu_page(
        'plugin-library',
        'Installed Plugins',
        'Installed Plugins',
        'manage_options',
        'plugin-library-plugins',
        'plugin_library_plugins_list_page'
    );
}

function enqueue_admin_style() {
wp_enqueue_style( 'admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css',array(), null  );
}
?>


