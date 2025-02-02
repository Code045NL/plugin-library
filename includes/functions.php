<?php
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