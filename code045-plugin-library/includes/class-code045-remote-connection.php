<?php
class Code045_Remote_Connection {
    private $remote_url;
    private $username;
    private $password;

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
            return [];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function copy_plugin($plugin_slug) {
        $response = wp_remote_get($this->remote_url . '/wp-json/wp/v2/plugins/' . $plugin_slug . '/download', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password)
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $plugin_zip = wp_remote_retrieve_body($response);
        $upload_dir = wp_upload_dir();
        $plugin_path = $upload_dir['path'] . '/' . $plugin_slug . '.zip';

        file_put_contents($plugin_path, $plugin_zip);

        $result = unzip_file($plugin_path, WP_PLUGIN_DIR);

        unlink($plugin_path);

        return $result;
    }
}
?>