<?php
// This file contains functions specifically related to API interactions

/**
 * Fetch installed plugins from a remote WordPress installation.
 *
 * @param string $remote_url The URL of the remote WordPress installation.
 * @param string $username The username for authentication.
 * @param string $password The password for authentication.
 * @return array|WP_Error An array of installed plugins or a WP_Error object on failure.
 */
function fetch_remote_plugins($remote_url, $username, $password) {
    $response = wp_remote_get($remote_url . '/wp-json/plugins/v1/all', [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
        ],
    ]);

    if (is_wp_error($response)) {
        return $response; // Return the error object
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true); // Return the decoded JSON response
}

/**
 * Install a plugin from a remote WordPress installation.
 *
 * @param string $remote_url The URL of the remote WordPress installation.
 * @param string $username The username for authentication.
 * @param string $password The password for authentication.
 * @param string $plugin_slug The slug of the plugin to install.
 * @return WP_Error|null A WP_Error object on failure, or null on success.
 */
function install_remote_plugin($remote_url, $username, $password, $plugin_slug) {
    $response = wp_remote_post($remote_url . '/wp-json/plugins/v1/install', [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
        ],
        'body' => [
            'plugin_slug' => $plugin_slug,
        ],
    ]);

    if (is_wp_error($response)) {
        return $response; // Return the error object
    }

    return null; // Return null on success
}

/**
 * Update a plugin from a remote WordPress installation.
 *
 * @param string $remote_url The URL of the remote WordPress installation.
 * @param string $username The username for authentication.
 * @param string $password The password for authentication.
 * @param string $plugin_slug The slug of the plugin to update.
 * @return WP_Error|null A WP_Error object on failure, or null on success.
 */
function update_remote_plugin($remote_url, $username, $password, $plugin_slug) {
    $response = wp_remote_post($remote_url . '/wp-json/plugins/v1/update', [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
        ],
        'body' => [
            'plugin_slug' => $plugin_slug,
        ],
    ]);

    if (is_wp_error($response)) {
        return $response; // Return the error object
    }

    return null; // Return null on success
}
?>