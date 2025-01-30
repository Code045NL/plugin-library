<?php
class Code045_Plugin_Library {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_pages'));
        add_action('admin_init', array($this, 'settings_init'));
    }

    public function add_admin_pages() {
        add_menu_page('Code045 Plugin Library', 'Plugin Library', 'manage_options', 'code045-plugin-library', array($this, 'settings_page'));
        add_submenu_page('code045-plugin-library', 'Installed Plugins', 'Installed Plugins', 'manage_options', 'code045-plugins-list', array($this, 'plugins_list_page'));
    }

    public function settings_page() {
        include plugin_dir_path(__FILE__) . '../admin/partials/settings-page.php';
    }

    public function plugins_list_page() {
        include plugin_dir_path(__FILE__) . '../admin/partials/plugins-list-page.php';
    }

    public function settings_init() {
        register_setting('code045_plugin_library', 'code045_remote_url');
        register_setting('code045_plugin_library', 'code045_remote_username');
        register_setting('code045_plugin_library', 'code045_remote_password');
    }
}
?>