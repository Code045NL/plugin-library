<?php

function plugin_library_dashboard_page() {
    $current_page = isset($_GET['page']) ? $_GET['page'] : 'plugin-library-settings';
    ?>
    <div class="wrap">
        <h1>Plugin Library Dashboard</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=plugin-library-settings" class="nav-tab <?php echo $current_page === 'plugin-library-settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
            <a href="?page=client-plugin-library" class="nav-tab <?php echo $current_page === 'client-plugin-library' ? 'nav-tab-active' : ''; ?>">Client Plugins</a>
            <a href="?page=server-plugin-library" class="nav-tab <?php echo $current_page === 'server-plugin-library' ? 'nav-tab-active' : ''; ?>">Server Plugins</a>
        </h2>
        <div class="tab-content">
            <?php
            if ($current_page === 'client-plugin-library') {
                require_once plugin_dir_path(__FILE__) . 'plugin-library-client-page.php';
            } elseif ($current_page === 'server-plugin-library') {
                require_once plugin_dir_path(__FILE__) . 'plugin-library-server-page.php';
            } else {
                require_once plugin_dir_path(__FILE__) . 'settings-page.php';
            }
            ?>
        </div>
    </div>
    <?php
}