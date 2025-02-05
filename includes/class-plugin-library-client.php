<?php
// filepath: /workspaces/plugin-library/includes/class-plugin-library-client.php

class Plugin_Library_Client {
    private $servers;

    public function __construct() {
        $this->servers = get_option('plugin_library_servers', array());

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'plugin-library-settings',
            'Client Plugin List',
            'Client Plugin List',
            'manage_options',
            'client-plugin-library',
            array($this, 'client_plugin_library_page')
        );
    }

    public function client_plugin_library_page() {
        include plugin_dir_path(__FILE__) . '../admin/plugin-library-client-page.php';
    }

    public function register_settings() {
        register_setting('plugin_library_client_settings', 'plugin_library_servers');
    }

    public function create_custom_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'plugin_library_client_info';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            plugin_slug varchar(255) NOT NULL,
            plugin_name varchar(255) NOT NULL,
            plugin_version varchar(255) NOT NULL,
            server_plugin_id varchar(255) NOT NULL,
            server_url varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function get_installed_plugins($server_url, $username, $password) {
        $response = wp_remote_get($server_url . '/wp-json/plugin-library/v1/plugins', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $password)
            )
        ));

        if (is_wp_error($response)) {
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function get_plugin_groups($server_url, $username, $password) {
        $response = wp_remote_get($server_url . '/wp-json/plugin-library/v1/plugin-groups', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $password)
            )
        ));

        if (is_wp_error($response)) {
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function install_plugin(WP_REST_Request $request) {
        $zip_url = sanitize_text_field($request->get_param('zip_url'));
        $plugin_slug = sanitize_text_field($request->get_param('plugin_slug'));
        $server_plugin_id = sanitize_text_field($request->get_param('server_plugin_id'));
        $server_url = sanitize_text_field($request->get_param('server_url'));

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

            // Store plugin info in the custom table
            global $wpdb;
            $table_name = $wpdb->prefix . 'plugin_library_client_info';
            $wpdb->replace(
                $table_name,
                array(
                    'plugin_slug' => $plugin_slug,
                    'plugin_name' => $upgrader->result['destination_name'],
                    'plugin_version' => $upgrader->result['destination_name'],
                    'server_plugin_id' => $server_plugin_id,
                    'server_url' => $server_url,
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                )
            );

            return new WP_REST_Response(array('success' => true, 'message' => 'Plugin installed successfully.'), 200);
        }
    }
}
?>