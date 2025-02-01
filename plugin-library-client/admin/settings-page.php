<?php
function code045_plugin_library_settings_page() {
    ?>
    <div class="wrap">
        <h1>Code045 Plugin Library Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('code045_plugin_library_settings');
            do_settings_sections('code045_plugin_library_settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Remote URL</th>
                    <td><input type="text" name="code045_remote_url" value="<?php echo esc_attr(get_option('code045_remote_url')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="code045_api_key" value="<?php echo esc_attr(get_option('code045_api_key')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
?>