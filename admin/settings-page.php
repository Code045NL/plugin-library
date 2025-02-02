<?php
function plugin_library_settings_page() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plugin_library_mode'])) {
        $mode = sanitize_text_field($_POST['plugin_library_mode']);
        update_option('plugin_library_mode', $mode);
        echo '<div class="updated"><p>Mode updated to: ' . esc_html($mode) . '</p></div>';
    }

    $mode = get_option('plugin_library_mode'); // Default to server mode

    ?>
    <div class="wrap">
        <h1>Plugin Library Settings</h1>
        <form method="post">
            <label for="plugin_library_mode">Mode:</label>
            <select name="plugin_library_mode" id="plugin_library_mode">
                <option value="" <?php selected($mode, ''); ?>>Choose your mode</option>
                <option value="server" <?php selected($mode, 'server'); ?>>Server</option>
                <option value="client" <?php selected($mode, 'client'); ?>>Client</option>
            </select>
            <input type="submit" class="button" value="Save">
        </form>
    </div>
    <?php
    if ($mode === 'client') {
        ?>
        <div class="wrap">
            <h1>Plugin Library Client Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('plugin_library_client_settings');
                do_settings_sections('plugin_library_client_settings');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Remote URL</th>
                        <td><input type="text" name="plugin_library_client_remote_url" value="<?php echo esc_attr(get_option('plugin_library_client_remote_url')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Username</th>
                        <td><input type="text" name="plugin_library_client_username" value="<?php echo esc_attr(get_option('plugin_library_client_username')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Password</th>
                        <td><input type="password" name="plugin_library_client_password" value="<?php echo esc_attr(get_option('plugin_library_client_password')); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    if ($mode === 'server') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_api_key'])) {
            $client_url = sanitize_text_field($_POST['client_url']);
            $api_key = generate_api_key($client_url);
            echo '<div class="updated"><p>API Key generated: ' . esc_html($api_key) . '</p></div>';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_api_key'])) {
            $api_key = sanitize_text_field($_POST['api_key']);
            remove_api_key($api_key);
            echo '<div class="updated"><p>API Key removed.</p></div>';
        }

        echo '<h2>API Keys</h2>';
        echo '<form method="post" style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="text" name="client_url" placeholder="Client URL" required>';
        echo '<input type="submit" name="generate_api_key" class="button" value="Generate API Key">';
        echo '</form>';
        $api_keys = get_option('plugin_library_api_keys', []);
        if (!empty($api_keys)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>API Key</th><th>Client URL</th><th>Actions</th></thead>';
            echo '<tbody>';
            foreach ($api_keys as $api_key) {
                echo '<tr>';
                echo '<td>' . esc_html($api_key['key']) . '</td>';
                echo '<td>' . esc_html($api_key['client_url']) . '</td>';
                echo '<td>';
                echo '<form method="post" style="display:inline;">';
                echo '<input type="hidden" name="api_key" value="' . esc_attr($api_key['key']) . '">';
                echo '<input type="submit" name="remove_api_key" class="button" value="Remove">';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
    }
}
?>