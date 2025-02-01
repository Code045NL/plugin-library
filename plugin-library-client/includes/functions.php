<?php
// Register settings
function code045_register_settings() {
    register_setting('code045_plugin_library_settings', 'code045_remote_url');
    register_setting('code045_plugin_library_settings', 'code045_api_key');
}
?>