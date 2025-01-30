<?php
// This file contains the HTML and PHP code for the settings page where users can enter the remote WordPress installation's URL, login name, and password.

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<div class="wrap">
    <h1><?php esc_html_e( 'Remote WordPress Installation Settings', 'code045-plugin-library' ); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields( 'code045_plugin_library_options_group' );
        do_settings_sections( 'code045_plugin_library' );
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Remote URL', 'code045-plugin-library' ); ?></th>
                <td><input type="text" name="remote_url" value="<?php echo esc_attr( get_option('remote_url') ); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Login Name', 'code045-plugin-library' ); ?></th>
                <td><input type="text" name="login_name" value="<?php echo esc_attr( get_option('login_name') ); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Password', 'code045-plugin-library' ); ?></th>
                <td><input type="password" name="password" value="<?php echo esc_attr( get_option('password') ); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>