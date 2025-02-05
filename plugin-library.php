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

// Register settings
function plugin_library_register_settings() {
    register_setting('plugin_library_settings', 'plugin_library_mode');
}
add_action('admin_init', 'plugin_library_register_settings');

// Initialize the plugin
function plugin_library_init() {
    // Always add the general settings page
    add_action('admin_menu', 'plugin_library_add_general_settings_page');
    add_action('admin_enqueue_scripts', 'plugin_library_enqueue_admin_styles');

    // Conditionally include and initialize the correct class based on the mode
    $mode = get_option('plugin_library_mode', ''); // Default to empty

    if ($mode === 'server') {
        $server = new Plugin_Library_Server();
        $server->create_custom_table();
    } elseif ($mode === 'client') {
        $client = new Plugin_Library_Client();
        $client->create_custom_table();
    }
}

add_action('plugins_loaded', 'plugin_library_init');

// Add general settings page
function plugin_library_add_general_settings_page() {
    add_menu_page(
        'Plugin Library Settings',
        'Plugin Library Settings',
        'manage_options',
        'plugin-library-settings',
        'plugin_library_settings_page'
    );
}

// General settings page callback
function plugin_library_settings_page() {
    ?>
    <div class="wrap">
        <h1>Plugin Library Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('plugin_library_settings');
            do_settings_sections('plugin_library_settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Mode</th>
                    <td>
                        <select name="plugin_library_mode">
                            <option value="" <?php selected(get_option('plugin_library_mode'), ''); ?>>Select Mode</option>
                            <option value="client" <?php selected(get_option('plugin_library_mode'), 'client'); ?>>Client</option>
                            <option value="server" <?php selected(get_option('plugin_library_mode'), 'server'); ?>>Server</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Enqueue admin styles
function plugin_library_enqueue_admin_styles() {
    wp_enqueue_style('plugin-library-admin-style', plugin_dir_url(__FILE__) . 'admin/admin-style.css');
}
?>