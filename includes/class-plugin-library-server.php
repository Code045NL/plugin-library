<?php
// filepath: /workspaces/plugin-library/includes/class-plugin-library-server.php

class Plugin_Library_Server {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('rest_api_init', array($this, 'register_routes'));
        add_action('upgrader_process_complete', array($this, 'upgrader_process_complete'), 10, 2);
        add_action('deleted_plugin', array($this, 'deleted_plugin'), 10, 2);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'plugin-library-settings',
            'Server Plugin List',
            'Server Plugin List',
            'manage_options',
            'server-plugin-library',
            array($this, 'server_plugin_library_page')
        );
    }

    public function server_plugin_library_page() {
        include plugin_dir_path(__FILE__) . '../admin/plugin-library-server-page.php';
    }

    public function register_routes() {
        register_rest_route('plugin-library/v1', '/plugins', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_plugins'),
        ));

        register_rest_route('plugin-library/v1', '/install-plugin', array(
            'methods' => 'POST',
            'callback' => array($this, 'install_plugin'),
            'permission_callback' => '__return_true',
        ));
    }

    public function get_plugins() {
        // Load the WordPress plugin library
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    
        // Get all plugins
        $all_plugins = get_plugins();
    
        // Prepare the response
        $plugins = array();
        $backup_dir = ABSPATH . 'plugin-library';
        
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            $plugin_slug = dirname($plugin_file);
            $zip_file = $backup_dir . '/' . $plugin_slug . '.zip';
        
            if (file_exists($zip_file)) {
                $plugins[] = array(
                    'slug' => $plugin_slug,
                    'name' => $plugin_data['Name'],
                    'version' => $plugin_data['Version'],
                    'zip_url' => home_url('/plugin-library/' . $plugin_slug . '.zip'),
                );
            }
        }
        
        return new WP_REST_Response($plugins, 200);
    
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
    
            // Get plugin data
            $plugin_data = get_plugin_data($installed_plugin_dir . '/' . $plugin_slug . '.php');
            $plugin_name = $plugin_data['Name'];
            $plugin_version = $plugin_data['Version'];
    
            // Store plugin info in the custom table
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
    
            return new WP_REST_Response(array('success' => true, 'message' => 'Plugin installed successfully.'), 200);
        }
    }

    public function upgrader_process_complete($upgrader_object, $options) {
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

    public function deleted_plugin($plugin) {
        $plugin_slug = dirname($plugin);

        // Delete the entry from the custom table
        global $wpdb;
        $table_name = $wpdb->prefix . 'plugin_library_api_info';
        $wpdb->delete(
            $table_name,
            array('plugin_slug' => $plugin_slug),
            array('%s')
        );

        // Delete the zip file
        $backup_dir = ABSPATH . 'plugin-library';
        $this->delete_plugin_zip($plugin_slug, $backup_dir);
    }

    public function create_custom_table() {
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

    public function create_plugin_zip($plugin_dir, $backup_dir, $plugin_slug) {
        $zip = new ZipArchive();
        $zip_file = $backup_dir . '/' . $plugin_slug . '.zip';

        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $root_path = realpath($plugin_dir);
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root_path), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $file_path = $file->getRealPath();
                    $relative_path = $plugin_slug . '/' . substr($file_path, strlen($root_path) + 1);
                    $zip->addFile($file_path, $relative_path);
                }
            }

            $zip->close();

            // Get plugin data
            $plugin_data = get_plugin_data($plugin_dir . '/' . $plugin_slug . '.php');
            $plugin_name = $plugin_data['Name'];
            $plugin_version = $plugin_data['Version'];

            // Update the custom table
            $this->zip_created_or_updated($plugin_slug, $plugin_name, $plugin_version);
        } else {
            throw new Exception('Failed to create zip file.');
        }
    }

    public function delete_plugin_zip($plugin_slug, $backup_dir) {
        $zip_file = $backup_dir . '/' . $plugin_slug . '.zip';
        if (file_exists($zip_file)) {
            unlink($zip_file);

            // Update the custom table
            $this->zip_deleted($plugin_slug);
        }
    }

    public function zip_created_or_updated($plugin_slug, $plugin_name, $plugin_version) {
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

    public function zip_deleted($plugin_slug) {
        // Delete the entry from the custom table
        global $wpdb;
        $table_name = $wpdb->prefix . 'plugin_library_api_info';
        $wpdb->delete(
            $table_name,
            array('plugin_slug' => $plugin_slug),
            array('%s')
        );
    }

    public function clean_database() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'plugin_library_api_info';

        // Remove duplicate rows
        $wpdb->query("
            DELETE t1 FROM $table_name t1
            INNER JOIN $table_name t2 
            WHERE 
                t1.id < t2.id AND 
                t1.plugin_slug = t2.plugin_slug
        ");

        // Remove rows for deleted plugins
        $installed_plugins = get_plugins();
        $installed_slugs = array_map('dirname', array_keys($installed_plugins));

        $placeholders = implode(',', array_fill(0, count($installed_slugs), '%s'));
        $wpdb->query($wpdb->prepare("
            DELETE FROM $table_name 
            WHERE plugin_slug NOT IN ($placeholders)
        ", $installed_slugs));
    }
}
?>