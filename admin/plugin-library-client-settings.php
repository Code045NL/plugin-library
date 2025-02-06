<?php

// Register client settings
function plugin_library_register_client_settings() {
    register_setting('plugin_library_client_settings', 'plugin_library_client_debug');
    register_setting('plugin_library_client_settings', 'plugin_library_client_remote_url');
    register_setting('plugin_library_client_settings', 'plugin_library_client_username');
    register_setting('plugin_library_client_settings', 'plugin_library_client_password');

    add_settings_section(
        'plugin_library_client_settings_section',
        'Plugin Library Client Settings',
        null,
        'plugin_library_client_settings'
    );

    add_settings_field(
        'plugin_library_client_debug',
        'Enable Debug Options',
        'plugin_library_client_debug_option_callback',
        'plugin_library_client_settings',
        'plugin_library_client_settings_section'
    );

    add_settings_field(
        'plugin_library_client_remote_url',
        'Remote URL',
        'plugin_library_client_remote_url_callback',
        'plugin_library_client_settings',
        'plugin_library_client_settings_section'
    );

    add_settings_field(
        'plugin_library_client_username',
        'Username',
        'plugin_library_client_username_callback',
        'plugin_library_client_settings',
        'plugin_library_client_settings_section'
    );

    add_settings_field(
        'plugin_library_client_password',
        'Password',
        'plugin_library_client_password_callback',
        'plugin_library_client_settings',
        'plugin_library_client_settings_section'
    );
}
add_action('admin_init', 'plugin_library_register_client_settings');

// Client settings callbacks
function plugin_library_client_debug_option_callback() {
    $debug = get_option('plugin_library_client_debug', false);
    echo '<input type="checkbox" name="plugin_library_client_debug" value="1"' . checked(1, $debug, false) . '>';
}

function plugin_library_client_remote_url_callback() {
    $remote_url = get_option('plugin_library_client_remote_url', '');
    echo '<input type="text" name="plugin_library_client_remote_url" value="' . esc_attr($remote_url) . '" class="regular-text">';
}

function plugin_library_client_username_callback() {
    $username = get_option('plugin_library_client_username', '');
    echo '<input type="text" name="plugin_library_client_username" value="' . esc_attr($username) . '" class="regular-text">';
}

function plugin_library_client_password_callback() {
    $password = get_option('plugin_library_client_password', '');
    echo '<input type="password" name="plugin_library_client_password" value="' . esc_attr($password) . '" class="regular-text">';
}

// Display client settings
function plugin_library_client_settings_page() {
    ?>
    <div class="wrap">
        <h2>Client Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('plugin_library_client_settings');
            do_settings_sections('plugin_library_client_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
plugin_library_client_settings_page();