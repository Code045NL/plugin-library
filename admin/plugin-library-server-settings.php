<?php

// Register server settings
function plugin_library_register_server_settings() {
    register_setting('plugin_library_server_settings', 'plugin_library_server_option');

    add_settings_section(
        'plugin_library_server_settings_section',
        'Plugin Library Server Settings',
        null,
        'plugin_library_server_settings'
    );

    add_settings_field(
        'plugin_library_server_option',
        'Server Option',
        'plugin_library_server_option_callback',
        'plugin_library_server_settings',
        'plugin_library_server_settings_section'
    );
}
add_action('admin_init', 'plugin_library_register_server_settings');

// Server settings callbacks
function plugin_library_server_option_callback() {
    $option = get_option('plugin_library_server_option', '');
    echo '<input type="text" name="plugin_library_server_option" value="' . esc_attr($option) . '" class="regular-text">';
}

// Display server settings
function plugin_library_server_settings_page() {
    ?>
    <div class="wrap">
        <h2>Server Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('plugin_library_server_settings');
            do_settings_sections('plugin_library_server_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
plugin_library_server_settings_page();