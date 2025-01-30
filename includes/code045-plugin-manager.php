<?php
class Code045_Client_Manager {
    private $server_url;
    private $api_key;

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        $options = get_option('code045_settings');
        $this->server_url = $options['code045_client_server_url'];
        $this->api_key = $options['code045_server_api_key'];
    }

    public function register_rest_routes() {
        register_rest_route('code045/v1', '/client-endpoint', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_client_request'),
            'permission_callback' => '__return_true',
        ));
    }

    public function handle_client_request($request) {
        $response = wp_remote_get($this->server_url . '/wp-json/code045/v1/server-endpoint', array(
            'headers' => array('Authorization' => 'Bearer ' . $this->api_key),
        ));
        $body = wp_remote_retrieve_body($response);
        return new WP_REST_Response(json_decode($body), 200);
    }
}