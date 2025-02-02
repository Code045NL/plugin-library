<?php
// This file contains general utility functions used throughout the plugin

// Function to generate and store API key
function generate_api_key($client_url) {
    $api_keys = get_option('plugin_library_api_keys', []);
    $api_key = bin2hex(random_bytes(32));
    $api_keys[] = ['key' => $api_key, 'client_url' => $client_url];
    update_option('plugin_library_api_keys', $api_keys);
    return $api_key;
}

// Function to remove an API key
function remove_api_key($api_key) {
    $api_keys = get_option('plugin_library_api_keys', []);
    $api_keys = array_filter($api_keys, function($key) use ($api_key) {
        return $key['key'] !== $api_key;
    });
    update_option('plugin_library_api_keys', $api_keys);
}

// Function to get plugin data by slug
function get_plugin_data_by_slug($plugin_slug) {
    $plugins = get_plugins();
    foreach ($plugins as $plugin_file => $plugin_data) {
        if (dirname($plugin_file) === $plugin_slug) {
            $backup_dir = ABSPATH . 'plugin-library';
            $plugin_version = $plugin_data['Version'];
            $zip_file = $backup_dir . '/' . $plugin_slug . '-' . $plugin_version . '.zip';
            if (file_exists($zip_file)) {
                $plugin_data['zip_url'] = home_url('/plugin-library/' . $plugin_slug . '-' . $plugin_version . '.zip');
            }
            return $plugin_data;
        }
    }
    return null;
}
?>