<?php
function plugin_library_plugins_list_page() {
    $mode = get_option('plugin_library_mode', ''); // Default to server mode

    if ($mode === 'client') {
        // Ensure the settings are loaded
        $remote_url = get_option('plugin_library_client_remote_url');
        $username = get_option('plugin_library_client_username');
        $password = get_option('plugin_library_client_password');

        if (empty($remote_url) || empty($username) || empty($password)) {
            echo '<p>Please set the remote URL, username, and password in the settings page.</p>';
            return;
        }

        // Instantiate the remote connection class with the correct arguments
        $remote_connection = new Plugin_Library_Remote_Connection($remote_url, $username, $password);
        $remote_plugins = $remote_connection->get_installed_plugins();

        if (empty($remote_plugins) || !is_array($remote_plugins)) {
            echo '<p>No plugins found on the remote WordPress install.</p>';
            return;
        }

        $installed_plugins = get_plugins();

        echo '<h1>Installed Plugins</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th style="width: 30%;">Plugin Name</th><th>Version</th><th>Actions</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        foreach ($remote_plugins as $remote_plugin) {
            if (!is_array($remote_plugin) || !isset($remote_plugin['name'], $remote_plugin['version'], $remote_plugin['slug'])) {
                continue;
            }

            $plugin_name = esc_html($remote_plugin['name']);
            $plugin_version = esc_html($remote_plugin['version']);
            $plugin_slug = esc_html($remote_plugin['slug']);
            $status = isset($installed_plugins[$plugin_name]) ? 'Installed' : 'Not Installed';

            echo '<tr>';
            echo '<td>' . $plugin_name . '</td>';
            echo '<td>' . $plugin_version . '</td>';
            echo '<td><a href="' . admin_url('admin.php?page=plugin-library-client-settings&action=install-plugin&plugin=' . $plugin_slug) . '">Install</a></td>';
            echo '<td>' . $status . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } 

    if ($mode === 'server') {
        // Server mode
        $installed_plugins = get_plugins();

        echo '<h1>Installed Plugins</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th style="width: 30%;">Plugin Name</th><th>Version</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($installed_plugins as $plugin_file => $plugin_data) {
            $plugin_name = esc_html($plugin_data['Name']);
            $plugin_version = esc_html($plugin_data['Version']);

            echo '<tr>';
            echo '<td>' . $plugin_name . '</td>';
            echo '<td>' . $plugin_version . '</td>';
            echo '<td><a href="' . admin_url('plugin-install.php?tab=plugin-information&plugin=' . $plugin_file) . '">View Details</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
    else {
        echo '<p>Set the mode</p>';
}
}


// Handle plugin installation action
add_action('admin_init', function() {
    if (isset($_GET['action']) && $_GET['action'] === 'install-plugin' && isset($_GET['plugin'])) {
        $remote_url = get_option('plugin_library_client_remote_url');
        $api_key = get_option('plugin_library_client_api_key');
        $plugin_slug = sanitize_text_field($_GET['plugin']);

        $client = new Plugin_Library_Client($remote_url, $api_key);
        $result = $client->download_and_install_plugin($plugin_slug);

        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        } else {
            wp_redirect(admin_url('plugins.php?plugin_status=all&paged=1&s=' . $plugin_slug));
            exit;
        }
    }
});
?>