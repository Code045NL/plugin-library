<?php
class Code045_Plugin_Library {

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_pages'));
    }

    public function add_admin_pages() {
        add_menu_page(
            'Code045 Plugin Library Settings',
            'Code045 Plugin Library',
            'manage_options',
            'code045-plugin-library-settings',
            'code045_plugin_library_settings_page',
            '',
            100
        );

        add_submenu_page(
            'code045-plugin-library-settings',
            'Installed Plugins',
            'Installed Plugins',
            'manage_options',
            'code045-plugin-library-plugins',
            'code045_plugin_library_plugins_list_page'
        );
    }
}
?>