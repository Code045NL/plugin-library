
<?php
class Code045_Remote_Connection {
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
        $response = wp_remote_get($this->remote_url . '/wp-json/plugin-library/v1/plugin-groups', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json'
            ]
        ]);

        if (is_wp_error($response)) {
            $this->errors[] = 'Error fetching plugin groups: ' . $response->get_error_message();
            error_log('Error fetching plugin groups: ' . $response->get_error_message());
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        error_log('Raw response body: ' . $body);

        if (empty($body)) {
            $this->errors[] = 'Empty response body';
            error_log('Empty response body');
            return [];
        }

        $plugin_groups = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errors[] = 'JSON decode error: ' . json_last_error_msg();
            error_log('JSON decode error: ' . json_last_error_msg());
            return [];
        }

        error_log('Response body: ' . $body);
        return $plugin_groups;
    }

    public function copy_plugin($plugin_zip_url) {
        $response = wp_remote_get($plugin_zip_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key
            ]
        ]);

        if (is_wp_error($response)) {
            $this->errors[] = 'Error downloading plugin: ' . $response->get_error_message();
            return false;
        }

        $plugin_zip_content = wp_remote_retrieve_body($response);
        if (empty($plugin_zip_content)) {
            $this->errors[] = 'Empty plugin zip file';
            return false;
        }

        $upload_dir = wp_upload_dir();
        $plugin_path = $upload_dir['path'] . '/' . basename($plugin_zip_url);

        file_put_contents($plugin_path, $plugin_zip_content);

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