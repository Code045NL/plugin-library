<?php
/*
 * Plugin Name: Plugin Library
 * Description: Provides zip files for installed plugins and manages plugins through the Code045 Plugin Management System.
 * Version: 2.1.0
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
    add_action('admin_menu', 'plugin_library_plugins_add_admin_menu');
    add_action('wp_enqueue_scripts', 'plugin_library_enqueue_fontawesome');
    add_action('update_option_plugin_library_mode', 'plugin_library_update_mode', 10, 2);
    add_action('upgrader_process_complete', 'plugin_library_upgrader_process_complete', 10, 2);
    add_action('deleted_plugin', 'plugin_library_deleted_plugin', 10, 2);
}

add_action('plugins_loaded', 'plugin_library_init');

// Add settings page
function plugin_library_add_settings_page() {
    add_menu_page(
        'Remote Plugin Library ',
        'Remote Plugin Library ',
        'manage_options',
        'remote-library',
        'plugin_library_plugins_list_page'
    );
}

// Add plugins list page
function plugin_library_plugins_add_admin_menu() {
    add_submenu_page(
        'remote-library',
        'Remote Plugin Settings',
        'Remote Plugin Settings',
        'manage_options',
        'remote-library-settings',
        'plugin_library_settings_page'
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

// Update mode and register/unregister REST API routes
function plugin_library_update_mode($old_value, $new_value) {
    if ($new_value === 'server') {
        add_action('rest_api_init', array('Plugin_Library_REST_API', 'register_routes'));
    } elseif ($new_value === 'client') {
        remove_action('rest_api_init', array('Plugin_Library_REST_API', 'register_routes'));
    }
}

// Handle plugin installations and updates
function plugin_library_upgrader_process_complete($upgrader_object, $options) {
    if ($options['type'] === 'plugin' && ($options['action'] === 'install' || $options['action'] === 'update')) {
        $plugin_slug = dirname($options['plugin']);
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $options['plugin']);
        $plugin_name = $plugin_data['Name'];
        $plugin_version = $plugin_data['Version'];
        $zip_url = home_url('/plugin-library/' . $plugin_slug . '.zip');

        // Update the custom table
        global $wpdb;
        $table_name = $wpdb->prefix . 'plugin_library_api_info';
        $wpdb->replace(
            $table_name,
            array(
                'plugin_slug' => $plugin_slug,
                'plugin_name' => $plugin_name,
                'plugin_version' => $plugin_version,
                'zip_url' => $zip_url,
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
    }
}

// Handle plugin deletions
function plugin_library_deleted_plugin($plugin) {
    $plugin_slug = dirname($plugin);

    // Delete the entry from the custom table
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_library_api_info';
    $wpdb->delete(
        $table_name,
        array('plugin_slug' => $plugin_slug),
        array('%s')
    );
}
?>