<?php
class Plugin_Library_Server {
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route('plugin-library/v1', '/plugins', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_installed_plugins'),
            'permission_callback' => '__return_true',
        ));
    }

    public function get_installed_plugins() {
        if (!current_user_can('manage_options')) {
            return new WP_Error('rest_forbidden', esc_html__('You do not have permissions to view the plugins.', 'plugin-library'), array('status' => 401));
        }

        $plugins = get_plugins();
        $plugin_data = array();

        foreach ($plugins as $plugin_file => $plugin_info) {
            $plugin_data[] = array(
                'name' => $plugin_info['Name'],
                'version' => $plugin_info['Version'],
                'zip_url' => $this->get_plugin_zip_url($plugin_file),
            );
        }

        return rest_ensure_response($plugin_data);
    }

    private function get_plugin_zip_url($plugin_file) {
        // Generate the URL to download the plugin as a zip file
        // This is a placeholder implementation, you need to implement the actual logic
        return home_url('/wp-content/plugins/' . dirname($plugin_file) . '.zip');
    }
}

// Initialize the server functionality
new Plugin_Library_Server();
?>