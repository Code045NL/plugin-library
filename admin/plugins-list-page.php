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

            // Install or update the plugin from the zip URL
            $result = $upgrader->install($zip_url);

            if (is_wp_error($result)) {
                echo '<p>Failed to install/update the plugin: ' . $result->get_error_message() . '</p>';
            } else {
                // Rename the plugin folder to the slug
                $installed_plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
                $extracted_plugin_dir = WP_PLUGIN_DIR . '/' . basename($zip_url, '.zip');
                if (is_dir($extracted_plugin_dir) && !is_dir($installed_plugin_dir)) {
                    rename($extracted_plugin_dir, $installed_plugin_dir);
                }
                echo '<p>Plugin installed/updated successfully.</p>';
            }
        }

        // Get the list of installed plugins on the client
        $installed_plugins = get_plugins();

        // Instantiate the remote connection class with the correct arguments
        $remote_connection = new Plugin_Library_Remote_Connection($remote_url, $username, $password);
        $remote_plugins = $remote_connection->get_installed_plugins();

        if (empty($remote_plugins) || !is_array($remote_plugins)) {
            echo '<p>No plugins found on the remote WordPress install.</p>';
            return;
        }

        echo '<h1>Installed Plugins</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th style="width: 30%;">Plugin Name</th><th>Version</th><th>Zip Version</th><th>Actions</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        foreach ($remote_plugins as $remote_plugin) {
            if (!is_array($remote_plugin) || !isset($remote_plugin['name'], $remote_plugin['version'], $remote_plugin['zip_url'])) {
                continue;
            }
            $plugin_slug = $remote_plugin['slug'];
            $installed_version = isset($installed_plugins[$plugin_slug . '/' . $plugin_slug . '.php']) ? $installed_plugins[$plugin_slug . '/' . $plugin_slug . '.php']['Version'] : null;

            echo '<tr>';
            echo '<td>' . esc_html($remote_plugin['name']) . '</td>';
            echo '<td>' . esc_html($remote_plugin['version']) . '</td>';
            echo '<td><a href="' . esc_url($remote_plugin['zip_url']) . '">Download</a></td>';
            echo '<td>';
            if ($installed_version) {
                if (version_compare($remote_plugin['version'], $installed_version, '>')) {
                    echo '<form method="post" style="display:inline;">';
                    echo '<input type="hidden" name="zip_url" value="' . esc_attr($remote_plugin['zip_url']) . '">';
                    echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($remote_plugin['slug']) . '">';
                    echo '<button type="submit" name="update_plugin" class="button">Update</button>';
                    echo '</form>';
                } else {
                    echo 'Up to date';
                }
            } else {
                echo '<form method="post" style="display:inline;">';
                echo '<input type="hidden" name="zip_url" value="' . esc_attr($remote_plugin['zip_url']) . '">';
                echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($remote_plugin['slug']) . '">';
                echo '<button type="submit" name="install_plugin" class="button">Install</button>';
                echo '</form>';
            }
            echo '</td>';
            echo '<td>';
            if ($installed_version) {
                if (version_compare($remote_plugin['version'], $installed_version, '>')) {
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

    if ($mode === 'server') {
            $plugins = get_plugins();
            $backup_dir = ABSPATH . 'plugin-library';
        
            if (!file_exists($backup_dir)) {
                mkdir($backup_dir, 0755, true);
            }
        
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['create_zip']) || isset($_POST['update_zip']))) {
                $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
                $plugin_version = sanitize_text_field($_POST['plugin_version']);
                $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
        
                // Delete older version zip files
                $old_zip_files = glob($backup_dir . '/' . $plugin_slug . '-*.zip');
                foreach ($old_zip_files as $old_zip_file) {
                    if (basename($old_zip_file) !== $plugin_slug . '-' . $plugin_version . '.zip') {
                        unlink($old_zip_file);
                    }
                }
        
                create_plugin_zip($plugin_dir, $backup_dir, $plugin_slug, $plugin_version);
                echo '<div class="updated"><p>Zip file created/updated for ' . esc_html($plugin_slug) . '.</p></div>';
            }
        
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_zip'])) {
                $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
                $plugin_version = sanitize_text_field($_POST['plugin_version']);
                $zip_file = $backup_dir . '/' . $plugin_slug . '-' . $plugin_version . '.zip';
                if (file_exists($zip_file)) {
                    unlink($zip_file);
                    echo '<div class="updated"><p>Zip file removed for ' . esc_html($plugin_slug) . '.</p></div>';
                } else {
                    echo '<div class="error"><p>Zip file not found for ' . esc_html($plugin_slug) . '.</p></div>';
                }
            }
        
            echo '<div class="wrap">';

            echo '<div class="remote-library">';
            echo '<h1>Installed Plugins</h1>';
            echo '<form method="post">';
            echo '<table class="wp-list-table widefat fixed striped plugin-library-table">';
            echo '<thead><tr><th style="width: 30%;">Plugin Name</th><th>Version</th><th>Zip Version</th><th>Actions</th><th>Status</th></tr></thead>';
            echo '<tbody>';
        
            foreach ($plugins as $plugin_file => $plugin_data) {
                $plugin_slug = dirname($plugin_file);
                if ($plugin_slug === 'plugin-library') {
                    continue; // Skip the plugin-library plugin
                }
                $plugin_version = $plugin_data['Version'];
                $zip_file = $backup_dir . '/' . $plugin_slug . '-' . $plugin_version . '.zip';
                $zip_exists = file_exists($zip_file);
                $zip_version = $zip_exists ? $plugin_version : 'N/A';
        
                echo '<tr>';
                echo '<td>' . esc_html($plugin_data['Name']) . '</td>';
                echo '<td>' . esc_html($plugin_version) . '</td>';
                echo '<td>' . esc_html($zip_version) . '</td>';
                echo '<td>';
                if (!$zip_exists) {
                    echo '<form method="post" style="display:inline;">';
                    echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($plugin_slug) . '">';
                    echo '<input type="hidden" name="plugin_version" value="' . esc_attr($plugin_version) . '">';
                    echo '<button type="submit" name="create_zip" class="button"><span class="dashicons dashicons-media-archive"></span></button>';
                    echo '</form>';
                }
                if ($zip_exists) {
                    echo '<form method="post" style="display:inline;">';
                    echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($plugin_slug) . '">';
                    echo '<input type="hidden" name="plugin_version" value="' . esc_attr($plugin_version) . '">';
                    echo '<button type="submit" name="remove_zip" class="button"><span class="dashicons dashicons-trash"></span></button>';
                    echo '</form>';
                }
                if ($zip_exists && version_compare($plugin_version, $zip_version, '>')) {
                    echo '<form method="post" style="display:inline;">';
                    echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($plugin_slug) . '">';
                    echo '<input type="hidden" name="plugin_version" value="' . esc_attr($plugin_version) . '">';
                    echo '<button type="submit" name="update_zip" class="button"><span class="dashicons dashicons-update"></span></button>';
                    echo '</form>';
                }
                echo '</td>';
                echo '<td>';
                if (!$zip_exists) {
                    echo '<span style="color: red;">&#x25CF; Create Zip</span>';
                } elseif ($zip_exists && version_compare($plugin_version, $zip_version, '>')) {
                    echo '<span style="color: orange;">&#x25CF; Update Available</span>';
                } else {
                    echo '<span style="color: green;">&#x25CF; Plugin Available</span>';
                }
                echo '</td>';
                echo '</tr>';
            }
        
            echo '</tbody>';
            echo '</table>';
            echo '</form>';
            echo '</div>';
            echo '</div>';

        }
        
}

