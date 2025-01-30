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
        $plugin_url = $this->remote_url . '/wp-content/plugins/' . $plugin_slug;
        $local_path = WP_PLUGIN_DIR . '/' . $plugin_slug;

        // Create local directory if it doesn't exist
        if (!file_exists($local_path)) {
            mkdir($local_path, 0755, true);
        }

        // Copy plugin files from remote to local
        $this->recursive_copy($plugin_url, $local_path);
    }

    private function recursive_copy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursive_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
?>