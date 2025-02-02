<?php
class Plugin_Library_Remote_Connection {
    private $remote_url;
    private $api_key;
    private $errors = [];

    public function __construct($remote_url, $api_key) {
        $this->remote_url = rtrim($remote_url, '/');
        $this->api_key = $api_key;
    }

    public function get_installed_plugins() {
        $response = wp_remote_get($this->remote_url . '/wp-json/plugin-library/v1/plugins', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key
            )
        ));

        if (is_wp_error($response)) {
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function get_plugin_groups() {
        // Add your code here
    }
}
?>