<?php
class Code045_Server_Manager {
    private $api_key;

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        $this->api_key = get_option('code045_settings')['code045_server_api_key'];
    }

    public function register_rest_routes() {
        register_rest_route('code045/v1', '/server-endpoint', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_server_request'),
            'permission_callback' => '__return_true',
        ));
    }

    public function handle_server_request($request) {
        return new WP_REST_Response(array('message' => 'Server response', 'api_key' => $this->api_key), 200);
    }
}