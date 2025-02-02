<?php
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