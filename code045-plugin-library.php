<?php
/**
 * Plugin Name: Code045 Plugin Library
 * Description: Manages WordPress plugins through the Code045 Plugin Management System
 * Version: 1.0.0
 * Author: Code045
 */

add_action('admin_menu', 'code045_add_admin_menu');
add_action('admin_init', 'code045_settings_init');

function code045_add_admin_menu() {
    add_menu_page('Code045 Plugin Library', 'Code045 Plugin Library', 'manage_options', 'code045_plugin_library', 'code045_options_page');
}

function code045_settings_init() {
    register_setting('code045_plugin_library', 'code045_settings');

    add_settings_section(
        'code045_plugin_library_section',
        __('Settings', 'code045'),
        'code045_settings_section_callback',
        'code045_plugin_library'
    );

    add_settings_field(
        'code045_mode',
        __('Mode', 'code045'),
        'code045_mode_render',
        'code045_plugin_library',
        'code045_plugin_library_section'
    );
}

function code045_mode_render() {
    $options = get_option('code045_settings');
    ?>
    <select name='code045_settings[code045_mode]'>
        <option value='server' <?php selected($options['code045_mode'], 'server'); ?>>Server</option>
        <option value='client' <?php selected($options['code045_mode'], 'client'); ?>>Client</option>
    </select>
    <?php
}

function code045_settings_section_callback() {
    echo __('Choose whether this WordPress installation should act as a server or client.', 'code045');
}

function code045_options_page() {
    ?>
    <form action='options.php' method='post'>
        <h2>Code045 Plugin Library</h2>
        <?php
        settings_fields('code045_plugin_library');
        do_settings_sections('code045_plugin_library');
        submit_button();
        ?>
    </form>
    <?php
}

add_action('plugins_loaded', 'code045_load_manager');

function code045_load_manager() {
    $options = get_option('code045_settings');
    $mode = isset($options['code045_mode']) ? $options['code045_mode'] : 'client'; // Default to 'client' if not set

    if ($mode === 'server') {
        require_once plugin_dir_path(__FILE__) . 'includes/code045-server-manager.php';
        new Code045_Server_Manager();
    } else {
        require_once plugin_dir_path(__FILE__) . 'includes/code045-plugin-manager.php';
        new Code045_Plugin_Manager();
    }
}
