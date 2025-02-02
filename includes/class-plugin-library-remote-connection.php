<?php
class Plugin_Library_Remote_Connection {
    private $remote_url;
    private $username;
    private $password;
    private $errors = [];

    public function __construct($remote_url, $username, $password) {
        $this->remote_url = rtrim($remote_url, '/');
        $this->username = $username;
        $this->password = $password;
    }

    public function get_installed_plugins() {
        $response = wp_remote_get($this->remote_url . '/wp-json/plugin-library/v1/plugins', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password)
            )
        ));

        if (is_wp_error($response)) {
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function get_plugin_groups() {
        $response = wp_remote_get($this->remote_url . '/wp-json/plugin-library/v1/plugin-groups', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password)
            )
        ));

        if (is_wp_error($response)) {
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}
?>