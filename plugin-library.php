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
require_once plugin_dir_path( __FILE__ ) . 'includes/plugin-library-settings.php';

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