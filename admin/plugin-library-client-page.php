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

        // Dump the result for debugging if debug is enabled
        

        // Display the list of plugins in a table
        echo '<h2>Available Plugins</h2>';
        echo '<table class="widefat fixed" cellspacing="0">';
        echo '<thead><tr><th>Name</th><th>Version</th><th>Action</th></tr></thead>';
        echo '<tbody>';
        if (!empty($plugins)) {
            foreach ($plugins as $plugin) {
                echo '<tr>';
                echo '<td>' . esc_html($plugin['name']) . '</td>';
                echo '<td>' . esc_html($plugin['version']) . '</td>';
                echo '<td>';
                echo '<form method="post">';
                echo '<input type="hidden" name="zip_url" value="' . esc_url($plugin['zip_url']) . '">';
                echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($plugin['slug']) . '">';
                echo '<input type="hidden" name="server_url" value="' . esc_url($remote_url) . '">';
                echo '<input type="submit" name="install_plugin" value="Install" class="button button-primary">';
                echo '<input type="submit" name="update_plugin" value="Update" class="button">';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="3">No plugins found.</td></tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }

    // Handle form submission for installing or updating the plugin
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['install_plugin']) || isset($_POST['update_plugin']))) {
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

            // Get plugin data
            $plugin_data = get_plugin_data($installed_plugin_dir . '/' . $plugin_slug . '.php');

            // Store plugin info in the custom table
            global $wpdb;
            $table_name = $wpdb->prefix . 'plugin_library_client_info';
            $wpdb->replace(
                $table_name,
                array(
                    'plugin_slug' => $plugin_slug,
                    'plugin_name' => $plugin_data['Name'],
                    'plugin_version' => $plugin_data['Version'],
                    'zip_url' => $zip_url,
                    'server_url' => $server_url,
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                )
            );

            echo '<p>Plugin installed/updated successfully.</p>';
        }
    }
