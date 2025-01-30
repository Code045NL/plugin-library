<?php
class Code045_Client_Manager {
    private $server_url;
    private $api_key;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        $options = get_option('code045_settings');
        $this->server_url = $options['code045_client_server_url'];
        $this->api_key = $options['code045_client_api_key'];
    }

    public function add_admin_menu() {
        add_submenu_page('code045_plugin_library', 'Connected Plugins', 'Connected Plugins', 'manage_options', 'code045-connected-plugins', array($this, 'render_connected_plugins_page'));
    }

    public function render_connected_plugins_page() {
        ?>
        <div class="wrap">
            <h1>Connected Plugins</h1>
            <div class="card">
                <h2>Plugins on Server</h2>
                <p>List of plugins installed on the connected server will be displayed here.</p>
            </div>
        </div>
        <?php
    }
}