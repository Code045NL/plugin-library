<?php
function plugin_library_server_settings_page() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plugin_library_mode'])) {
        $mode = sanitize_text_field($_POST['plugin_library_mode']);
        update_option('plugin_library_mode', $mode);
        echo '<div class="updated"><p>Mode updated to: ' . esc_html($mode) . '</p></div>';
    }

    $mode = get_option('plugin_library_mode', 'server'); // Default to server mode

    ?>
    <div class="wrap">
        <h1>Plugin Library Settings</h1>
        <form method="post">
            <label for="plugin_library_mode">Mode:</label>
            <select name="plugin_library_mode" id="plugin_library_mode">
                <option value="server" <?php selected($mode, 'server'); ?>>Server</option>
                <option value="client" <?php selected($mode, 'client'); ?>>Client</option>
            </select>
            <input type="submit" class="button" value="Save">
        </form>
    </div>
    <?php
}

function plugin_library_client_settings_page() {
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
                    <th scope="row">API Key</th>
                    <td><input type="text" name="plugin_library_client_api_key" value="<?php echo esc_attr(get_option('plugin_library_client_api_key')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
?>