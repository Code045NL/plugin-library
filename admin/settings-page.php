<?php

function remote_library_settings_page() {
    $mode = get_option('plugin_library_mode', 'client'); // Default to client mode
    $debug = get_option('plugin_library_client_debug', false); // Get the debug option
    $api_results = null;

    if ($debug) {
        // Perform the API GET request if the debug option is enabled
        $remote_url = get_option('plugin_library_client_remote_url');
        $username = get_option('plugin_library_client_username');
        $password = get_option('plugin_library_client_password');

        if (!empty($remote_url) && !empty($username) && !empty($password)) {
            $response = wp_remote_get($remote_url . '/wp-json/plugin-library/v1/plugins', array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
                ),
            ));

            if (!is_wp_error($response)) {
                $api_results = wp_remote_retrieve_body($response);
            } else {
                $api_results = $response->get_error_message();
            }
        }
    }
    ?>
    <div class="wrap">
        <h1>Remote Library Settings</h1>
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
            $server->display_settings();
        }

        // Show debug information if debug option is enabled
        if ($debug) {
            echo '<h2>Debug Information</h2>';
            echo '<pre>';
            var_dump($mode);
            var_dump($debug);
            var_dump($client ?? null);
            var_dump($server ?? null);
            var_dump($api_results);
            echo '</pre>';
        }
        ?>
    </div>
    <?php
}

// Register the mode setting field
function plugin_library_register_general_settings() {
    register_setting('plugin_library_settings', 'plugin_library_mode');
    register_setting('plugin_library_settings', 'plugin_library_client_debug'); // Register the debug option

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

    add_settings_field(
        'plugin_library_client_debug',
        'Enable Debug Options',
        'plugin_library_debug_option_callback',
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

// Debug option callback
function plugin_library_debug_option_callback() {
    $debug = get_option('plugin_library_client_debug', false);
    echo '<input type="checkbox" name="plugin_library_client_debug" value="1"' . checked(1, $debug, false) . '>';
}