<?php
function code045_plugin_library_plugins_list_page() {
    $remote_url = get_option('code045_remote_url');
    $api_key = get_option('code045_api_key');

    if (empty($remote_url) || empty($api_key)) {
        echo '<p>Please set the remote URL and API key in the settings page.</p>';
        return;
    }

    $remote_connection = new Code045_Remote_Connection($remote_url, $api_key);
    $remote_plugins = $remote_connection->get_installed_plugins();

    if (empty($remote_plugins) || !is_array($remote_plugins)) {
        echo '<p>No plugins found on the remote WordPress install.</p>';
        return;
    }

    $installed_plugins = get_plugins();

    echo '<h1>Installed Plugins</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th style="width: 30%;">Plugin Name</th><th>Version</th><th>Zip Version</th><th>Actions</th><th>Status</th></tr></thead>';
    echo '<tbody>';
    foreach ($remote_plugins as $remote_plugin) {
        if (!is_array($remote_plugin) || !isset($remote_plugin['name'], $remote_plugin['version'], $remote_plugin['zip_url'])) {
            continue;
        }

        $plugin_name = $remote_plugin['name'];
        $zip_version = $remote_plugin['version'];
        $installed_version = 'Not Installed';
        foreach ($installed_plugins as $installed_plugin_file => $installed_plugin_data) {
            if ($installed_plugin_data['Name'] === $plugin_name) {
                $installed_version = $installed_plugin_data['Version'];
                break;
            }
        }

        $button_text = 'Install';
        $show_button = true;
        $status = '';
        if ($installed_version !== 'Not Installed') {
            if (version_compare($installed_version, $zip_version, '<')) {
                $button_text = 'Update';
                $status = '<span style="color: orange;">&#x25CF; Update Available</span>';
            } else {
                $status = '<span style="color: green;">&#x25CF; Plugin Available</span>';
                $show_button = false; // Hide button if installed version is same or newer
            }
        } else {
            $status = '<span style="color: red;">&#x25CF; Create Zip</span>';
        }

        echo '<tr>';
        echo '<td>' . esc_html($plugin_name) . '</td>';
        echo '<td>' . esc_html($installed_version) . '</td>';
        echo '<td>' . esc_html($zip_version) . '</td>';
        echo '<td>';
        if ($show_button) {
            echo '<a href="#" class="button code045-install-plugin" data-plugin-zip-url="' . esc_attr($remote_plugin['zip_url']) . '">' . esc_html($button_text) . '</a>';
        }
        echo '</td>';
        echo '<td>' . $status . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
?>