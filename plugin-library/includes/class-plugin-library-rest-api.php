<?php
class Plugin_Library_REST_API {

    public function init() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route('plugin-library/v1', '/plugins', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_installed_plugins'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('plugin-library/v1', '/plugin-groups', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_plugin_groups'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('plugin-library/v1', '/install-plugin', array(
            'methods' => 'POST',
            'callback' => array($this, 'install_plugin'),
            'permission_callback' => '__return_true',
        ));
    }

    public function get_installed_plugins() {
        $plugins = get_plugins();
        $plugin_data = array();

        foreach ($plugins as $plugin_file => $plugin_info) {
            $plugin_data[] = array(
                'name' => $plugin_info['Name'],
                'version' => $plugin_info['Version'],
                'slug' => dirname($plugin_file),
            );
        }

        return rest_ensure_response($plugin_data);
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
?>