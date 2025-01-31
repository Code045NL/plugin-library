<?php
class Code045_Plugin_Library {

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_pages'));
        add_action('wp_ajax_code045_install_plugin', array($this, 'install_plugin'));
    }

    public function add_admin_pages() {
        add_menu_page(
            'Code045 Plugin Library Settings',
            'Code045 Plugin Library',
            'manage_options',
            'code045-plugin-library-settings',
            array($this, 'settings_page_html'),
            '',
            100
        );

        add_submenu_page(
            'code045-plugin-library-settings',
            'Installed Plugins',
            'Installed Plugins',
            'manage_options',
            'code045-plugin-library-plugins',
            array($this, 'plugins_page_html')
        );
    }

    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>Code045 Plugin Library Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('code045_plugin_library_settings');
                do_settings_sections('code045_plugin_library_settings');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Remote URL</th>
                        <td><input type="text" name="code045_remote_url" value="<?php echo esc_attr(get_option('code045_remote_url')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Username</th>
                        <td><input type="text" name="code045_remote_username" value="<?php echo esc_attr(get_option('code045_remote_username')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Password</th>
                        <td><input type="password" name="code045_remote_password" value="<?php echo esc_attr(get_option('code045_remote_password')); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function plugins_page_html() {
        $remote_url = get_option('code045_remote_url');
        $username = get_option('code045_remote_username');
        $password = get_option('code045_remote_password');

        if (empty($remote_url) || empty($username) || empty($password)) {
            echo '<p>Please set the remote URL, username, and password in the settings page.</p>';
            return;
        }

        $remote_connection = new Code045_Remote_Connection($remote_url, $username, $password);
        $plugins = $remote_connection->get_installed_plugins();

        if (empty($plugins)) {
            echo '<p>No plugins found on the remote WordPress install.</p>';
            return;
        }

        echo '<h1>Installed Plugins</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Plugin Name</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($plugins as $plugin) {
            echo '<tr>';
            echo '<td>' . esc_html($plugin['name']) . '</td>';
            echo '<td><a href="#" class="button code045-install-plugin" data-plugin-slug="' . esc_attr($plugin['slug']) . '">Install</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.code045-install-plugin').on('click', function(e) {
                e.preventDefault();
                var pluginSlug = $(this).data('plugin-slug');
                $.post(ajaxurl, {
                    action: 'code045_install_plugin',
                    plugin_slug: pluginSlug
                }, function(response) {
                    alert(response.data);
                });
            });
        });
        </script>
        <?php
    }

    public function install_plugin() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action.');
        }

        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
        $remote_url = get_option('code045_remote_url');
        $username = get_option('code045_remote_username');
        $password = get_option('code045_remote_password');

        $remote_connection = new Code045_Remote_Connection($remote_url, $username, $password);
        $result = $remote_connection->copy_plugin($plugin_slug);

        if ($result) {
            wp_send_json_success('Plugin installed successfully.');
        } else {
            wp_send_json_error('Failed to install plugin.');
        }
    }
}
?>