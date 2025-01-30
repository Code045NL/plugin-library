<?php
/**
 * Plugin Name: Code045 Plugin Manager
 * Description: Manages WordPress plugins through the Code045 Plugin Management System
 * Version: 1.0.0
 * Author: Cod045
 */

add_action('admin_menu', 'code045_add_admin_menu');
add_action('admin_init', 'code045_settings_init');

function code045_add_admin_menu() {
    add_menu_page('Code045 Plugin Manager', 'Code045 Plugin Manager', 'manage_options', 'code045_plugin_manager', 'code045_options_page');
}

function code045_settings_init() {
    register_setting('code045_plugin_manager', 'code045_settings');

    add_settings_section(
        'code045_plugin_manager_section',
        __('Settings', 'code045'),
        'code045_settings_section_callback',
        'code045_plugin_manager'
    );

    add_settings_field(
        'code045_mode',
        __('Mode', 'code045'),
        'code045_mode_render',
        'code045_plugin_manager',
        'code045_plugin_manager_section'
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
        <h2>Code045 Plugin Manager</h2>
        <?php
        settings_fields('code045_plugin_manager');
        do_settings_sections('code045_plugin_manager');
        submit_button();
        ?>
    </form>
    <?php
}

add_action('plugins_loaded', 'code045_load_manager');

function code045_load_manager() {
    $options = get_option('code045_settings');
    if ($options['code045_mode'] === 'server') {
        require_once plugin_dir_path(__FILE__) . 'includes/code045-server-manager.php';
    } else {
        require_once plugin_dir_path(__FILE__) . 'includes/code045-plugin-manager.php';
    }
}

