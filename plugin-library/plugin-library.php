<?php
/**
 * Plugin Name: Plugin Library
 * Description: Provides zip files for installed plugins.
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
require_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/plugins-list-page.php';

// Add admin menu
add_action('admin_menu', 'plugin_library_add_admin_menu');
function plugin_library_add_admin_menu() {
    add_menu_page(
        'Plugin Library',
        'Plugin Library',
        'manage_options',
        'plugin-library',
        'plugin_library_settings_page'
    );

    add_submenu_page(
        'plugin-library',
        'Installed Plugins',
        'Installed Plugins',
        'manage_options',
        'plugin-library-plugins',
        'plugin_library_plugins_list_page'
    );
}

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'plugin_library_enqueue_admin_scripts');
function plugin_library_enqueue_admin_scripts() {
    wp_enqueue_style('plugin-library-admin-style', plugin_dir_url(__FILE__) . 'admin/css/admin-style.css');
    wp_enqueue_script('plugin-library-admin-script', plugin_dir_url(__FILE__) . 'admin/js/admin-script.js', array('jquery'), null, true);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}

// Initialize the REST API
function plugin_library_rest_api_init() {
    $rest_api = new Plugin_Library_REST_API();
    $rest_api->init();
}
add_action('rest_api_init', 'plugin_library_rest_api_init');
?>