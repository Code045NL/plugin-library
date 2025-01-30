<?php
class Code045_Server_Manager {
    private $api_key;

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        $this->api_key = get_option('code045_settings')['code045_server_api_key'];
    }

    public function register_rest_routes() {
        // Register REST routes here
    }

    public function add_admin_menu() {
        add_menu_page('Code045 Server Manager', 'Code045 Server', 'manage_options', 'code045-server', array($this, 'render_admin_page'), 'dashicons-database');
    }

    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Code045 Server Configuration</h1>
            <div class="card">
                <h2>API Key</h2>
                <p><?php echo esc_html($this->api_key); ?></p>
            </div>
        </div>
        <?php
    }
}