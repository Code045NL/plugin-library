<?php

// Register settings
function plugin_library_register_settings() {
    register_setting('plugin_library_settings', 'plugin_library_mode');
}
add_action('admin_init', 'plugin_library_register_settings');

// Add general settings page
function plugin_library_add_general_settings_page() {
    add_menu_page(
        'Plugin Library Settings',
        'Plugin Library',
        'manage_options',
        'plugin-library-settings',
        'plugin_library_settings_page'
    );
}
add_action('admin_menu', 'plugin_library_add_general_settings_page');

// General settings page callback
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
            require_once plugin_dir_path(__FILE__) . 'plugin-library-client-settings.php';
        } elseif ($mode === 'server') {
            require_once plugin_dir_path(__FILE__) . 'plugin-library-server-settings.php';
        }
        ?>
    </div>
    <?php
}

// Mode option callback
function plugin_library_mode_option_callback() {
    $mode = get_option('plugin_library_mode', 'client');
    echo '<select name="plugin_library_mode">';
    echo '<option value="client"' . selected($mode, 'client', false) . '>Client</option>';
    echo '<option value="server"' . selected($mode, 'server', false) . '>Server</option>';
    echo '</select>';
}

// Register the mode setting field
add_action('admin_init', function() {
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
});