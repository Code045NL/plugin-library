<?php
// filepath: /workspaces/plugin-library/admin/server-plugins-list-page.php

function plugin_library_server_plugins_list_page() {
    $plugins = get_plugins();
    $backup_dir = ABSPATH . 'plugin-library';

    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['create_zip']) || isset($_POST['update_zip']))) {
        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
        $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;

        // Delete older version zip files
        $old_zip_files = glob($backup_dir . '/' . $plugin_slug . '*.zip');
        foreach ($old_zip_files as $old_zip_file) {
            unlink($old_zip_file);
        }

        create_plugin_zip($plugin_dir, $backup_dir, $plugin_slug);
        echo '<div class="updated"><p>Zip file created/updated for ' . esc_html($plugin_slug) . '.</p></div>';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_zip'])) {
        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
        $zip_file = $backup_dir . '/' . $plugin_slug . '.zip';
        if (file_exists($zip_file)) {
            unlink($zip_file);
            echo '<div class="updated"><p>Zip file removed for ' . esc_html($plugin_slug) . '.</p></div>';
        } else {
            echo '<div class="error"><p>Zip file not found for ' . esc_html($plugin_slug) . '.</p></div>';
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Remote Plugin Manager</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th style="width: 30%;">Plugin Name</th><th>Version</th><th>Zip Version</th><th>Actions</th><th>Status</th></tr></thead>';
    echo '<tbody>';
    foreach ($plugins as $plugin_file => $plugin_data) {
        $plugin_slug = dirname($plugin_file);
        if ($plugin_slug === 'plugin-library') {
            continue; // Skip the plugin-library plugin
        }
        $plugin_version = $plugin_data['Version'];
        $zip_file = $backup_dir . '/' . $plugin_slug . '.zip';
        $zip_exists = file_exists($zip_file);
        $zip_version = $zip_exists ? $plugin_version : 'N/A';

        echo '<tr>';
        echo '<td>' . esc_html($plugin_data['Name']) . '</td>';
        echo '<td>' . esc_html($plugin_version) . '</td>';
        echo '<td>' . esc_html($zip_version) . '</td>';
        echo '<td>';
        if ($zip_exists) {
            echo '<form method="post" style="display:inline;">';
            echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($plugin_slug) . '">';
            echo '<button type="submit" name="update_zip" class="button">Update Zip</button>';
            echo '<button type="submit" name="remove_zip" class="button">Remove Zip</button>';
            echo '</form>';
        } else {
            echo '<form method="post" style="display:inline;">';
            echo '<input type="hidden" name="plugin_slug" value="' . esc_attr($plugin_slug) . '">';
            echo '<button type="submit" name="create_zip" class="button">Create Zip</button>';
            echo '</form>';
        }
        echo '</td>';
        echo '<td>';
        if ($zip_exists) {
            echo '<span style="color: green;">&#x25CF; Zip Available</span>';
        } else {
            echo '<span style="color: red;">&#x25CF; No Zip</span>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
?>