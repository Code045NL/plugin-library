<?php
// This file contains the HTML and PHP code for the plugins list page that displays the installed plugins from the remote installation along with install and update buttons.

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Fetch the list of plugins from the remote installation
$remote_plugins = get_remote_plugins(); // Assume this function is defined elsewhere

if ($remote_plugins) {
    echo '<div class="wrap">';
    echo '<h1>Installed Plugins from Remote Installation</h1>';
    echo '<table class="widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">Plugin Name</th>';
    echo '<th scope="col">Version</th>';
    echo '<th scope="col">Action</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($remote_plugins as $plugin) {
        echo '<tr>';
        echo '<td>' . esc_html($plugin['name']) . '</td>';
        echo '<td>' . esc_html($plugin['version']) . '</td>';
        echo '<td>';
        echo '<button class="button install-plugin" data-plugin="' . esc_attr($plugin['slug']) . '">Install</button>';
        echo '<button class="button update-plugin" data-plugin="' . esc_attr($plugin['slug']) . '">Update</button>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else {
    echo '<div class="wrap">';
    echo '<h1>No Plugins Found</h1>';
    echo '<p>Please check your remote installation settings.</p>';
    echo '</div>';
}
?>