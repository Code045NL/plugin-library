<?php
class Code045_Plugin_Manager {
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Code045 Plugin Manager',
            'Code045 Plugins',
            'manage_options',
            'code045-plugin-manager',
            array($this, 'render_admin_page'),
            'dashicons-admin-plugins'
        );
    }

    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Code045 Plugin Manager</h1>
            <div id="code045-plugin-manager">
                <div class="code045-status"></div>
                <div class="code045-plugins-list"></div>
            </div>
        </div>
        <?php
    }

    public function register_rest_routes() {
        register_rest_route('code045/v1', '/plugins/check/(?P<name>[a-zA-Z0-9-_]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'check_plugin_installation'),
            'permission_callback' => function() {
                return current_user_can('install_plugins');
            }
        ));

        register_rest_route('code045/v1', '/plugins/install/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'install_plugin'),
            'permission_callback' => function() {
                return current_user_can('install_plugins');
            }
        ));
    }

    public function check_plugin_installation($request) {
        $plugin_name = $request['name'];
        
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $installed = false;

        foreach ($all_plugins as $plugin_path => $plugin_data) {
            if (strpos($plugin_path, $plugin_name) !== false) {
                $installed = true;
                break;
            }
        }

        return new WP_REST_Response(array(
            'installed' => $installed
        ), 200);
    }

    public function install_plugin($request) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugin_id = $request['id'];
        
        try {
            $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
            $installed = $upgrader->install($plugin_url);

            if (is_wp_error($installed)) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => $installed->get_error_message()
                ), 500);
            }

            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Plugin installed successfully'
            ), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $e->getMessage()
            ), 500);
        }
    }
}

$code045_plugin_manager = new Code045_Plugin_Manager();