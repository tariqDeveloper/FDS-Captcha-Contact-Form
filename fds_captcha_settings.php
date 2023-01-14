<?php
// Add a new menu item to the WordPress settings menu
function fds_captcha_register_settings() {
    add_options_page(
        'FDS Captcha Settings',
        'FDS Captcha',
        'manage_options',
        'fds-captcha-settings',
        'fds_captcha_settings_page'
    );
}
add_action( 'admin_menu', 'fds_captcha_register_settings' );

// Callback function that displays the content of the settings page
function fds_captcha_settings_page() {
    ?>
    <div class="wrap">
        <h1>FDS Captcha Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'fds-captcha-settings' );
            do_settings_sections( 'fds-captcha-settings' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="fds_captcha_email_list">Email List</label>
                    </th>
                    <td>
                        <input type="text" id="fds_captcha_email_list" name="fds_captcha_email_list" value="<?php echo esc_attr( get_option( 'fds_captcha_email_list' ) ); ?>" size="40" />
                        <br>
                        <small>Enter multiple email addresses separated by a comma.</small>
                    </td>
                </tr>
            </table>
            <?php
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings, sections, and fields for the settings page
function fds_captcha_register_settings_fields() {
    register_setting( 'fds-captcha-settings', 'fds_captcha_email_list' );
}
add_action( 'admin_init', 'fds_captcha_register_settings_fields' );

