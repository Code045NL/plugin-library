<?php
class Plugin_Library_Client {
    private $remote_url;
    private $api_key;

    public function __construct($remote_url, $api_key) {
        $this->remote_url = rtrim($remote_url, '/');
        $this->api_key = $api_key;
    }

    public function download_and_install_plugin($plugin_slug) {
        $response = wp_remote_get($this->remote_url . '/wp-json/plugin-library/v1/plugins/' . $plugin_slug . '/download', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key
            )
        ));

        if (is_wp_error($response)) {
            return new WP_Error('download_failed', 'Failed to download plugin.');
        }

        $body = wp_remote_retrieve_body($response);
        $temp_file = wp_tempnam($plugin_slug . '.zip');

        if (!file_put_contents($temp_file, $body)) {
            return new WP_Error('write_failed', 'Failed to write plugin zip file.');
        }

        $result = unzip_file($temp_file, WP_PLUGIN_DIR);

        if (is_wp_error($result)) {
            return $result;
        }

        unlink($temp_file);
        return true;
    }
}
?>