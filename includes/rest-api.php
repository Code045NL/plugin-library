<?php
function plugin_library_rest_api_init() {
    register_rest_route('plugin-library/v1', '/plugins', array(
        'methods' => 'GET',
        'callback' => 'plugin_library_get_plugins',
    ));
}

function plugin_library_get_plugins() {
    $plugins = get_plugins();
    $plugin_data = array();

    foreach ($plugins as $plugin_file => $plugin_info) {
        $slug = dirname($plugin_file);
        $plugin_data[] = array(
            'name' => $plugin_info['Name'],
            'version' => $plugin_info['Version'],
            'slug' => $slug,
            'zip_url' => home_url('/plugin-library/' . $slug . '-' . $plugin_info['Version'] . '.zip')
        );
    }

    return new WP_REST_Response($plugin_data, 200);
}
?>