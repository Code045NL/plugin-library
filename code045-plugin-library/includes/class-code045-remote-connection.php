<?php
class Code045_Remote_Connection {
    private $remote_url;
    private $username;
    private $password;
    private $errors = [];

    public function __construct($url, $user, $pass) {
        $this->remote_url = rtrim($url, '/');
        $this->username = $user;
        $this->password = $pass;
    }

    public function get_installed_plugins() {
        $response = wp_remote_get($this->remote_url . '/wp-json/wp/v2/plugins', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password)
            ]
        ]);

        if (is_wp_error($response)) {
            $this->errors[] = 'Error fetching plugins: ' . $response->get_error_message();
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            $this->errors[] = 'Empty response body';
            return [];
        }

        return json_decode($body, true);
    }

    public function copy_plugin($plugin_slug) {
        $response = wp_remote_get($this->remote_url . '/plugin-library/' . $plugin_slug . '.zip', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password)
            ]
        ]);

        if (is_wp_error($response)) {
            $this->errors[] = 'Error downloading plugin: ' . $response->get_error_message();
            return false;
        }

        $plugin_zip = wp_remote_retrieve_body($response);
        if (empty($plugin_zip)) {
            $this->errors[] = 'Empty plugin zip file';
            return false;
        }

        $upload_dir = wp_upload_dir();
        $plugin_path = $upload_dir['path'] . '/' . $plugin_slug . '.zip';

        file_put_contents($plugin_path, $plugin_zip);

        $result = unzip_file($plugin_path, WP_PLUGIN_DIR);

        unlink($plugin_path);

        if (is_wp_error($result)) {
            $this->errors[] = 'Error unzipping plugin: ' . $result->get_error_message();
            return false;
        }

        return true;
    }

    public function get_errors() {
        return $this->errors;
    }
}
?>