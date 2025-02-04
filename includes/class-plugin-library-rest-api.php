<?php
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
            $zip_file = $backup_dir . '/' . $slug  . '.zip';
            $zip_exists = file_exists($zip_file);

            $plugin_data[] = array(
                'name' => $plugin_info['Name'],
                'version' => $plugin_version,
                'slug' => $slug,
                'zip_url' => $zip_exists ? home_url('/plugin-library/' . $slug  . '.zip') : null,
                'zip_exists' => $zip_exists
            );
        }

        return new WP_REST_Response($plugin_data, 200);
    }

    public function get_plugin_groups() {
        $plugin_groups = get_option('plugin_library_groups', array());
        return rest_ensure_response($plugin_groups);
    }

    public function install_plugin(WP_REST_Request $request) {
        $plugin_slug = $request->get_param('plugin_slug');
        $plugin_zip_url = $request->get_param('plugin_zip_url');

        if (empty($plugin_slug) || empty($plugin_zip_url)) {
            return new WP_Error('missing_params', 'Missing required parameters', array('status' => 400));
        }

        $temp_file = download_url($plugin_zip_url);

        if (is_wp_error($temp_file)) {
            return $temp_file;
        }

        $result = unzip_file($temp_file, WP_PLUGIN_DIR);

        unlink($temp_file);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array('success' => true));
    }
}

// Initialize the REST API class
new Plugin_Library_REST_API();
?>