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
                    echo '<button type="submit" name="create_zip" class="button"><i class="fa-solid fa-file-zipper"></i></button>';
                    echo '</form>';
                }
                if ($zip_exists) {
                    echo '<form method="post" style="display:inline;">';
                    echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($plugin_slug) . '">';
                    echo '<input type="hidden" name="plugin_version" value="' . esc_attr($plugin_version) . '">';
                    echo '<button type="submit" name="remove_zip" class="button"><i class="fa-solid fa-trash"></i></button>';
                    echo '</form>';
                }
                if ($zip_exists && version_compare($plugin_version, $zip_version, '>')) {
                    echo '<form method="post" style="display:inline;">';
                    echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($plugin_slug) . '">';
                    echo '<input type="hidden" name="plugin_version" value="' . esc_attr($plugin_version) . '">';
                    echo '<button type="submit" name="update_zip" class="button"><i class="fa-solid fa-arrow-up"></i></button>';
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
        }
        
    
    else {
        echo '<p>Set the mode</p>';
}
}

