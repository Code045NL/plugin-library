<?php
class Plugin_Library_Client {
    private $servers;

    public function __construct() {
        $this->servers = get_option('plugin_library_servers', array());

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('init', array($this, 'create_custom_table')); // Add this line to call create_custom_table on init
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

        add_submenu_page(
            'plugin-library-settings',
            'Client Settings',
            'Client Settings',
            'manage_options',
            'client-plugin-library-settings',
            array($this, 'client_plugin_library_settings_page')
        );
    }

    public function client_plugin_library_page() {
        include PL_PLUGIN_DIR . 'admin/plugin-library-client-page.php';
    }

    public function client_plugin_library_settings_page() {
        // Get the settings
        $remote_url = get_option('plugin_library_client_remote_url');
        $username = get_option('plugin_library_client_username');
        $password = get_option('plugin_library_client_password');
        $debug = get_option('plugin_library_client_debug', false);

        // Initialize variables
        $installed_plugins_count = 0;
        $zip_files_count = 0;
        $plugins = array();

        if (!empty($remote_url) && !empty($username) && !empty($password)) {
            // Fetch remote plugins from the custom table
            $response = wp_remote_get($remote_url . '/wp-json/plugin-library/v1/plugins', array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
                ),
            ));

            if (!is_wp_error($response)) {
                // Get the response body
                $body = wp_remote_retrieve_body($response);

                // Decode the JSON response
                $plugins = json_decode($body, true);

                // Count the number of installed plugins
                $installed_plugins_count = count($plugins);

                // Count the number of created zip files
                foreach ($plugins as $plugin) {
                    if (!empty($plugin['zip_url'])) {
                        $zip_files_count++;
                    }
                }
            }
        }

        ?>
        <div class="wrap">
            <h1>Client Settings</h1>
            <h2>Overview</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Remote Server URL</th>
                    <td><?php echo esc_html($remote_url); ?></td>
                </tr>
                <tr>
                    <th scope="row">Number of Installed Plugins</th>
                    <td><?php echo esc_html($installed_plugins_count); ?></td>
                </tr>
                <tr>
                    <th scope="row">Number of Created Zip Files</th>
                    <td><?php echo esc_html($zip_files_count); ?></td>
                </tr>
            </table>
            <form method="post" action="options.php">
                <?php
                settings_fields('plugin_library_client_settings');
                do_settings_sections('plugin_library_client_settings');
                submit_button();
                ?>
            </form>
            <?php if ($debug && !empty($plugins)) : ?>
                <h2>Debug Information</h2>
                <pre><?php var_dump($plugins); ?></pre>
            <?php endif; ?>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting('plugin_library_client_settings', 'plugin_library_servers');
        register_setting('plugin_library_client_settings', 'plugin_library_client_debug');
        register_setting('plugin_library_client_settings', 'plugin_library_client_remote_url');
        register_setting('plugin_library_client_settings', 'plugin_library_client_username');
        register_setting('plugin_library_client_settings', 'plugin_library_client_password');

        add_settings_section(
            'plugin_library_client_settings_section',
            'Plugin Library Client Settings',
            null,
            'plugin_library_client_settings'
        );

        add_settings_field(
            'plugin_library_client_debug',
            'Enable Debug Options',
            array($this, 'debug_option_callback'),
            'plugin_library_client_settings',
            'plugin_library_client_settings_section'
        );

        add_settings_field(
            'plugin_library_client_remote_url',
            'Remote URL',
            array($this, 'remote_url_callback'),
            'plugin_library_client_settings',
            'plugin_library_client_settings_section'
        );

        add_settings_field(
            'plugin_library_client_username',
            'Username',
            array($this, 'username_callback'),
            'plugin_library_client_settings',
            'plugin_library_client_settings_section'
        );

        add_settings_field(
            'plugin_library_client_password',
            'Password',
            array($this, 'password_callback'),
            'plugin_library_client_settings',
            'plugin_library_client_settings_section'
        );
    }

    public function debug_option_callback() {
        $debug = get_option('plugin_library_client_debug', false);
        echo '<input type="checkbox" name="plugin_library_client_debug" value="1"' . checked(1, $debug, false) . '>';
    }

    public function remote_url_callback() {
        $remote_url = get_option('plugin_library_client_remote_url', '');
        echo '<input type="text" name="plugin_library_client_remote_url" value="' . esc_attr($remote_url) . '" class="regular-text">';
    }

    public function username_callback() {
        $username = get_option('plugin_library_client_username', '');
        echo '<input type="text" name="plugin_library_client_username" value="' . esc_attr($username) . '" class="regular-text">';
    }

    public function password_callback() {
        $password = get_option('plugin_library_client_password', '');
        echo '<input type="password" name="plugin_library_client_password" value="' . esc_attr($password) . '" class="regular-text">';
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
            zip_url varchar(255) NOT NULL,
            server_url varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Ensure the backup directory exists
        $backup_dir = ABSPATH . 'plugin-library';
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
    }
}