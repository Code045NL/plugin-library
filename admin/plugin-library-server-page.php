<?php
// filepath: /workspaces/plugin-library/admin/plugin-library-server-page.php

function plugin_library_server_plugins_list_page() {
    ?>
    <div class="wrap">
        <h1>Server Plugin List</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-name">Plugin Name</th>
                    <th scope="col" class="manage-column column-version">Version</th>
                    <th scope="col" class="manage-column column-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get the list of installed plugins
                $plugins = get_plugins();
                foreach ($plugins as $plugin_file => $plugin_data) {
                    $plugin_slug = dirname($plugin_file);
                    $zip_file = ABSPATH . 'plugin-library/' . $plugin_slug . '.zip';
                    ?>
                    <tr>
                        <td><?php echo esc_html($plugin_data['Name']); ?></td>
                        <td><?php echo esc_html($plugin_data['Version']); ?></td>
                        <td>
                            <?php if (file_exists($zip_file)) { ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="plugin_slug" value="<?php echo esc_attr($plugin_slug); ?>">
                                    <button type="submit" name="update_plugin" class="button">Update</button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="plugin_slug" value="<?php echo esc_attr($plugin_slug); ?>">
                                    <button type="submit" name="delete_plugin" class="button">Delete</button>
                                </form>
                            <?php } else { ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="plugin_slug" value="<?php echo esc_attr($plugin_slug); ?>">
                                    <button type="submit" name="create_zip" class="button">Create</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <form method="post" style="margin-top: 20px;">
            <button type="submit" name="clean_database" class="button button-primary">Clean Database</button>
        </form>
    </div>
    <?php
}

// Handle plugin update, delete, create, and clean database actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $server = new Plugin_Library_Server();

    if (isset($_POST['update_plugin'])) {
        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
        // Add your update plugin logic here
        // Example: Update the plugin from a remote source
        // update_plugin($plugin_slug);

        // Create or update the zip file
        $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
        $backup_dir = ABSPATH . 'plugin-library';
        $server->create_plugin_zip($plugin_dir, $backup_dir, $plugin_slug);

        echo '<div class="notice notice-success is-dismissible"><p>Plugin updated and zip file created/updated successfully.</p></div>';
    } elseif (isset($_POST['delete_plugin'])) {
        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
        // Add your delete plugin logic here
        // Example: Delete the plugin
        delete_plugins(array($plugin_slug . '/' . $plugin_slug . '.php'));

        // Delete the zip file
        $backup_dir = ABSPATH . 'plugin-library';
        $server->delete_plugin_zip($plugin_slug, $backup_dir);

        echo '<div class="notice notice-success is-dismissible"><p>Plugin and zip file deleted successfully.</p></div>';
    } elseif (isset($_POST['create_zip'])) {
        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
        // Create the zip file
        $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
        $backup_dir = ABSPATH . 'plugin-library';
        $server->create_plugin_zip($plugin_dir, $backup_dir, $plugin_slug);

        echo '<div class="notice notice-success is-dismissible"><p>Zip file created successfully.</p></div>';
    } elseif (isset($_POST['clean_database'])) {
        $server->clean_database();
        echo '<div class="notice notice-success is-dismissible"><p>Database cleaned successfully.</p></div>';
    }
}

plugin_library_server_plugins_list_page();
?>