<?php
class Plugin_Library_Client {
    private $servers;

    public function __construct() {
        $this->servers = get_option('plugin_library_servers', array());

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('init', array($this, 'create_custom_table'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'plugin-library',
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

    public function display_settings() {
        ?>
        <div class="wrap">
            <h2>Client Settings</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('plugin_library_client_settings');
                do_settings_sections('plugin_library_client_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}