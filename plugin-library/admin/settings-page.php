<?php
function plugin_library_settings_page() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_api_key'])) {
        $client_url = sanitize_text_field($_POST['client_url']);
        $api_key = generate_api_key($client_url);
        echo '<div class="updated"><p>API Key generated: ' . esc_html($api_key) . '</p></div>';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_api_key'])) {
        $api_key = sanitize_text_field($_POST['api_key']);
        remove_api_key($api_key);
        echo '<div class="updated"><p>API Key removed.</p></div>';
    }

    $api_keys = get_option('plugin_library_api_keys', []);

    echo '<div class="wrap">';
    echo '<h1>Plugin Library Settings</h1>';
    echo '<form method="post" style="display: flex; align-items: center; gap: 10px;">';
    echo '<input type="text" name="client_url" placeholder="Client URL" required>';
    echo '<input type="submit" name="generate_api_key" class="button" value="Generate API Key">';
    echo '</form>';
    if (!empty($api_keys)) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>API Key</th><th>Client URL</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($api_keys as $api_key) {
            echo '<tr>';
            echo '<td>' . esc_html($api_key['key']) . '</td>';
            echo '<td>' . esc_html($api_key['client_url']) . '</td>';
            echo '<td>';
            echo '<form method="post" style="display:inline;">';
            echo '<input type="hidden" name="api_key" value="' . esc_attr($api_key['key']) . '">';
            echo '<input type="submit" name="remove_api_key" class="button" value="Remove">';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
    echo '</div>';
}
?>