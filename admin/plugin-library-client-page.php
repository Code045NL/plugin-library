<?php

// Ensure the settings are loaded
$remote_url = get_option('plugin_library_client_remote_url');
$username = get_option('plugin_library_client_username');
$password = get_option('plugin_library_client_password');
$debug = get_option('plugin_library_client_debug', false);

if (empty($remote_url) || empty($username) || empty($password)) {
    echo '<p>Please set the remote URL, username, and password in the settings page.</p>';
    return;
}

// Fetch remote plugins from the custom table
$response = wp_remote_get($remote_url . '/wp-json/plugin-library/v1/plugins', array(
    'headers' => array(
        'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
    ),
));

if (is_wp_error($response)) {
    echo '<p>Failed to fetch remote plugins: ' . $response->get_error_message() . '</p>';
} else {
    // Get the response body
    $body = wp_remote_retrieve_body($response);

    // Decode the JSON response
    $plugins = json_decode($body, true);

    // Fetch the list of installed plugins on the client
    $installed_plugins = get_plugins();

    // Dump the result for debugging if debug is enabled
    if ($debug) {
        echo '<h2>Debug Information</h2>';
        echo '<pre>';
        var_dump($plugins);
        var_dump($installed_plugins);
        echo '</pre>';
    }

    // Display the list of plugins in a table
    echo '<h2>Remote Plugins</h2>';
    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr><th>Name</th><th>Version</th><th>Action</th></tr></thead>';
    echo '<tbody>';
    if (!empty($plugins)) {
        foreach ($plugins as $plugin) {
            $is_installed = false;
            foreach ($installed_plugins as $installed_plugin) {
                if ($installed_plugin['Name'] === $plugin['name']) {
                    $is_installed = true;
                    break;
                }
            }

            echo '<tr>';
            echo '<td>' . esc_html($plugin['name']) . '</td>';
            echo '<td>' . esc_html($plugin['version']) . '</td>';
            echo '<td>';
            if (!$is_installed) {
                echo '<form method="post">';
                echo '<input type="hidden" name="zip_url" value="' . esc_url($plugin['zip_url']) . '">';
                echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($plugin['slug']) . '">';
                echo '<input type="hidden" name="server_url" value="' . esc_url($remote_url) . '">';
                echo '<input type="submit" name="install_plugin" value="Install" class="button button-primary">';
                echo '</form>';
            } else {
                echo '<span>Installed</span>';
            }
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3">No plugins found.</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

// Handle form submission for installing the plugin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install_plugin'])) {
    $zip_url = sanitize_text_field($_POST['zip_url']);
    $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
    $server_url = sanitize_text_field($_POST['server_url']);

    // Include necessary WordPress files for plugin installation
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/misc.php';

    // Create a new instance of Plugin_Upgrader
    $upgrader = new Plugin_Upgrader();

    // Install the plugin from the zip URL
    $result = $upgrader->install($zip_url);

    if (is_wp_error($result)) {
        echo '<p>Failed to install the plugin: ' . $result->get_error_message() . '</p>';
    } else {
        echo '<p>Plugin installed successfully.</p>';
    }
}

// Get the list of installed plugins on the client
$installed_plugins = get_plugins();

// Display the list of installed plugins
echo '<h2>Installed Plugins</h2>';
echo '<ul>';
foreach ($installed_plugins as $plugin_file => $plugin_data) {
    echo '<li>' . esc_html($plugin_data['Name']) . ' - ' . esc_html($plugin_data['Version']) . '</li>';
}
echo '</ul>';