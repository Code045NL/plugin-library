<?php

function plugin_library_settings_page() {
    $mode = get_option('plugin_library_mode', 'client'); // Default to client mode
    ?>
    <div class="wrap">
        <h1>Plugin Library Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('plugin_library_settings');
            do_settings_sections('plugin_library_settings');
            submit_button();
            ?>
        </form>
        <?php
        if ($mode === 'client') {
            require_once plugin_dir_path(__FILE__) . '../includes/class-plugin-library-client.php';
            $client = new Plugin_Library_Client();
            $client->display_settings();
        } elseif ($mode === 'server') {
            require_once plugin_dir_path(__FILE__) . '../includes/class-plugin-library-server.php';
            $server = new Plugin_Library_Server();
        }
        ?>
    </div>
    <?php
}

// Register the mode setting field
function plugin_library_register_general_settings() {
    register_setting('plugin_library_settings', 'plugin_library_mode');

    add_settings_section(
        'plugin_library_settings_section',
        'Plugin Library Settings',
        null,
        'plugin_library_settings'
    );

    add_settings_field(
        'plugin_library_mode',
        'Mode',
        'plugin_library_mode_option_callback',
        'plugin_library_settings',
        'plugin_library_settings_section'
    );
}
add_action('admin_init', 'plugin_library_register_general_settings');

// Mode option callback
function plugin_library_mode_option_callback() {
    $mode = get_option('plugin_library_mode', 'client');
    echo '<select name="plugin_library_mode">';
    echo '<option value="client"' . selected($mode, 'client', false) . '>Client</option>';
    echo '<option value="server"' . selected($mode, 'server', false) . '>Server</option>';
    echo '</select>';
}