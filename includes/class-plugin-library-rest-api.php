<?php
// filepath: /workspaces/plugin-library/includes/class-plugin-library-rest-api.php

class Plugin_Library_REST_API {
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        $mode = get_option('plugin_library_mode', ''); // Default to empty

        if ($mode === 'server') {
            register_rest_route('plugin-library/v1', '/plugins', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_plugins'),
            ));
        }

        register_rest_route('plugin-library/v1', '/install-plugin', array(
            'methods' => 'POST',
            'callback' => array($this, 'install_plugin'),
            'permission_callback' => '__return_true',
        ));
    }

    public function get_plugins() {
        $plugins = get_plugins();
        $plugin_data = array();
        $backup_dir = ABSPATH . 'plugin-library';

        foreach ($plugins as $plugin_file => $plugin_info) {
            $slug = dirname($plugin_file);
            $plugin_version = $plugin_info['Version'];
            $zip_file = $backup_dir . '/' . $slug . '.zip';
            $zip_exists = file_exists($zip_file);

            $plugin_data[] = array(
                'name' => $plugin_info['Name'],
                'version' => $plugin_version,
                'slug' => $slug,
                'zip_url' => $zip_exists ? home_url('/plugin-library/' . $slug . '.zip') : null,
                'zip_exists' => $zip_exists
            );
        }

        return new WP_REST_Response($plugin_data, 200);
    }

    public function install_plugin(WP_REST_Request $request) {
        $zip_url = sanitize_text_field($request->get_param('zip_url'));
        $plugin_slug = sanitize_text_field($request->get_param('plugin_slug'));

        // Include necessary WordPress files for plugin installation
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';

        // Create a new instance of Plugin_Upgrader
        $upgrader = new Plugin_Upgrader();

        // Install the plugin from the zip URL
        $result = $upgrader->install($zip_url);

        if (is_wp_error($result)) {
            return new WP_REST_Response(array('success' => false, 'message' => $result->get_error_message()), 400);
        } else {
            // Rename the plugin folder to the slug
            $installed_plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
            $extracted_plugin_dir = WP_PLUGIN_DIR . '/' . $upgrader->result['destination_name'];
            if (is_dir($extracted_plugin_dir) && !is_dir($installed_plugin_dir)) {
                rename($extracted_plugin_dir, $installed_plugin_dir);
            }
            return new WP_REST_Response(array('success' => true, 'message' => 'Plugin installed successfully.'), 200);
        }
    }
}

// Initialize the REST API class
new Plugin_Library_REST_API();
?>