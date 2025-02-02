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
?>