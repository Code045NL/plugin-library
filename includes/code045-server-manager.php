<?php
class Code045_Server_Manager {
    private $api_key;

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        $this->api_key = get_option('code045_settings')['code045_server_api_key'];
    }

    public function register_rest_routes() {
        // Register REST routes here
    }
}