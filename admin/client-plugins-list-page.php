<?php
// filepath: /workspaces/plugin-library/admin/client-plugins-list-page.php

function plugin_library_client_plugins_list_page() {
    // Ensure the settings are loaded
    $remote_url = get_option('plugin_library_client_remote_url');
    $username = get_option('plugin_library_client_username');
    $password = get_option('plugin_library_client_password');

    if (empty($remote_url) || empty($username) || empty($password)) {
        echo '<p>Please set the remote URL, username, and password in the settings page.</p>';
        return;
    }

    // Handle form submission for installing or updating the plugin
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['install_plugin']) || isset($_POST['update_plugin']))) {
        $zip_url = sanitize_text_field($_POST['zip_url']);
        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);

        // Include necessary WordPress files for plugin installation
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';

        // Create a new instance of Plugin_Upgrader
        $upgrader = new Plugin_Upgrader();

        // Delete the existing plugin folder if it exists
        $installed_plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
        if (is_dir($installed_plugin_dir)) {
            // Use the delete function from the WordPress Filesystem API
            global $wp_filesystem;
            WP_Filesystem();
            $wp_filesystem->delete($installed_plugin_dir, true);
        }

        // Install or update the plugin from the zip URL
        $result = $upgrader->install($zip_url);

        if (is_wp_error($result)) {
            echo '<p>Failed to install/update the plugin: ' . $result->get_error_message() . '</p>';
        } else {
            // Rename the plugin folder to the slug
            $extracted_plugin_dir = WP_PLUGIN_DIR . '/' . $upgrader->result['destination_name'];
            if (is_dir($extracted_plugin_dir) && !is_dir($installed_plugin_dir)) {
                rename($extracted_plugin_dir, $installed_plugin_dir);
            }
            echo '<p>Plugin installed/updated successfully.</p>';
        }
    }

    // Get the list of installed plugins on the client
    $installed_plugins = get_plugins();

    // Fetch remote plugins from the custom table
    $response = wp_remote_get($remote_url . '/wp-json/plugin-library/v1/plugins', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
        ),
    ));

    if (is_wp_error($response)) {
        echo '<p>Failed to fetch remote plugins: ' . $response->get_error_message() . '</p>';
        return;
    }

    $remote_plugins = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($remote_plugins) || !is_array($remote_plugins)) {
        echo '<p>No plugins found on the remote WordPress install.</p>';
        return;
    }

    echo '<h1>Installed Plugins</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th style="width: 30%;">Plugin Name</th><th>Version</th><th>Zip Version</th><th>Actions</th><th>Status</th></tr></thead>';
    echo '<tbody>';
    foreach ($remote_plugins as $remote_plugin) {
        if (!is_array($remote_plugin) || !isset($remote_plugin['plugin_name'], $remote_plugin['plugin_version'], $remote_plugin['zip_url'])) {
            continue;
        }
        $plugin_slug = $remote_plugin['plugin_slug'];
        $installed_version = null;

        // Check if the plugin is installed
        foreach ($installed_plugins as $plugin_file => $plugin_data) {
            if (dirname($plugin_file) === $plugin_slug) {
                $installed_version = $plugin_data['Version'];
                break;
            }
        }

        echo '<tr>';
        echo '<td>' . esc_html($remote_plugin['plugin_name']) . '</td>';
        echo '<td>' . esc_html($remote_plugin['plugin_version']) . '</td>';
        echo '<td><a href="' . esc_url($remote_plugin['zip_url']) . '">Download</a></td>';
        echo '<td>';
        if ($installed_version) {
            if (version_compare($remote_plugin['plugin_version'], $installed_version, '>')) {
                echo '<form method="post" style="display:inline;">';
                echo '<input type="hidden" name="zip_url" value="' . esc_attr($remote_plugin['zip_url']) . '">';
                echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($remote_plugin['plugin_slug']) . '">';
                echo '<button type="submit" name="update_plugin" class="button">Update</button>';
                echo '</form>';
            } else {
                echo 'Up to date';
            }
        } else {
            echo '<form method="post" style="display:inline;">';
            echo '<input type="hidden" name="zip_url" value="' . esc_attr($remote_plugin['zip_url']) . '">';
            echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($remote_plugin['plugin_slug']) . '">';
            echo '<button type="submit" name="install_plugin" class="button">Install</button>';
        }
        echo '</td>';
        echo '<td>';
        if ($installed_version) {
            if (version_compare($remote_plugin['plugin_version'], $installed_version, '>')) {
                echo '<span style="color: orange;">&#x25CF; Update Available</span>';
            } else {
                echo '<span style="color: green;">&#x25CF; Plugin Available</span>';
            }
        } else {
            echo '<span style="color: red;">&#x25CF; Not Installed</span>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
?>