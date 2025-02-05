<?php
// filepath: /workspaces/plugin-library/includes/functions.php

// Function to get plugin data by slug
function get_plugin_data_by_slug($plugin_slug) {
    $plugins = get_plugins();
    foreach ($plugins as $plugin_file => $plugin_data) {
        $slug = dirname($plugin_file);
        if ($slug === $plugin_slug) {
            $backup_dir = ABSPATH . 'plugin-library';
            $zip_file = $backup_dir . '/' . $plugin_slug . '.zip';
            if (file_exists($zip_file)) {
                $plugin_data['zip_url'] = home_url('/plugin-library/' . $plugin_slug . '.zip');
            }
            return $plugin_data;
        }
    }
    return null;
}

// Enqueue admin styles
function plugin_library_enqueue_admin_styles() {
    wp_enqueue_style('plugin-library-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'plugin_library_enqueue_admin_styles');

// Create custom table during plugin activation
function plugin_library_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_library_api_info';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        plugin_slug varchar(255) NOT NULL,
        plugin_name varchar(255) NOT NULL,
        plugin_version varchar(255) NOT NULL,
        zip_url varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'plugin_library_create_custom_table');
?>