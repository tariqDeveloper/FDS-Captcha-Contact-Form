<?php
/**
 * Plugin Name: FDS CAPTCHA Contact Form
 * Plugin URI: https://github.com/tariqDeveloper/FDS-Captcha-Contact-Form
 * Description: A plugin that adds a custom image-based CAPTCHA to your WordPress site.
 * Version: 1.0
 * Author: Muhammad Tariq Khan
 * Author URI: https://github.com/tariqDeveloper
 * License: GPL2
 *
 * Copyright (C) 2021  Your Name  (email : yourname@example.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


//scripts
function fds_captcha_scripts()
{
    wp_enqueue_script('jquery');
    wp_localize_script('jquery', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'fds_captcha_scripts');


function fds_captcha_custom_js()
{
    ?>
    <script>
        (function ($) {
            getCaptcha(1);
            $('#reload_captcha').click(function (e) {
                e.preventDefault();
                getCaptcha(0);
            });

            function getCaptcha(firstTime) {
                $.ajax({
                    url: ajax_object.ajaxurl,  // The URL for the request. This is defined by WordPress and is used to process requests from the front-end.
                    type: 'POST',  // The type of request to make (POST, GET, etc.).
                    data: {
                        action: 'my_action',  // The name of the action to be executed by the WordPress backend.
                    },
                    success: function (response) {  // The function to run if the request is successful.
                        if (firstTime) {
                            $('#captcha-img').attr("src", 'data:image/png;base64,' + response.data.image);
                            $('#captcha-img').show();
                            $('#reload_captcha').show();
                        } else {
                            $("#captcha-img").attr("src", 'data:image/png;base64,' + response.data.image);
                        }

                    },
                    error: function (error) {  // The function to run if the request fails.
                        // Do something with the error here.
                    }
                });
            }

            function fds_check_validation() {
                var nameFalg = 1;
                var emailFalg = 1;
                var msgFalg = 1;
                var captchaFalg = 1;
                if (!$.trim($("#fds_contact_name").val()).length) { // zero-length string AFTER a trim
                    $('#fds_contact_name_status').show();
                    nameFalg = 0;
                } else {
                    $('#fds_contact_name_status').hide();
                }

                if (!$.trim($("#fds_contact_email").val()).length) { // zero-length string AFTER a trim
                    $('#fds_contact_email_status').show();
                    emailFalg = 0;
                } else {
                    $('#fds_contact_email_status').hide();
                }

                if (!$.trim($("#fds_contact_message").val()).length) { // zero-length string AFTER a trim
                    $('#fds_contact_msg_status').show();
                    msgFalg = 0;
                } else {
                    $('#fds_contact_msg_status').hide();
                }

                if (!$.trim($("#fds_captcha_input").val()).length) { // zero-length string AFTER a trim
                    $('#fds_contact_captcha_status').show();
                    captchaFalg = 0;
                } else {
                    $('#fds_contact_captcha_status').hide();
                }

                if (nameFalg && emailFalg && msgFalg && captchaFalg) {
                    return true;
                } else {
                    return false;
                }

            }

            $('#submit_fds_contact').click(function (e) {
                e.preventDefault();
                if (!fds_check_validation()) {
                    return 0;
                }
                var captcha = $('#fds_captcha_input').val();
                var name = $('#fds_contact_name').val();
                var email = $('#fds_contact_email').val();
                var msg = $('#fds_contact_message').val();
                $.ajax({
                    url: ajax_object.ajaxurl,  // The URL for the request. This is defined by WordPress and is used to process requests from the front-end.
                    type: 'POST',  // The type of request to make (POST, GET, etc.).
                    data: {
                        action: 'submit_fds_contact',  // The name of the action to be executed by the WordPress backend.
                        name: name,
                        email: email,
                        msg: msg,
                        captcha: captcha
                    },
                    success: function (response) {  // The function to run if the request is successful.
                        if (response.data.status) {
                            $('#success_msg').html(response.data.msg);
                            $('#success_msg').show();
                            $('#fds_contact_form').trigger('reset');

                        } else {
                            $('#success_msg').html(response.data.msg);
                            $('#success_msg').show();
                        }
                    },
                    error: function (error) {  // The function to run if the request fails.
                        // Do something with the error here.
                    }
                });
            });


        })(jQuery);
    </script>
    <?php
}

add_action('wp_footer', 'fds_captcha_custom_js');

add_action('wp_ajax_my_action', 'my_action_callback');
add_action('wp_ajax_nopriv_my_action', 'my_action_callback');
add_action('wp_ajax_submit_fds_contact', 'submit_fds_contact_callback');
add_action('wp_ajax_nopriv_submit_fds_contact', 'submit_fds_contact_callback');

function my_action_callback()
{
    // Get the data from the request
    generate_captcha_image();
}

function submit_fds_contact_callback()
{
    session_start();
    if (verify_captcha_expiry()) {
        // Read the user's input
        $captcha_input = $_POST['captcha'];

        // Read the correct solution for the CAPTCHA image from a file or database
        $captcha_solution = $_SESSION['captcha_string'];
        // Compare the user's input to the correct solution
        if ($captcha_input == $captcha_solution) {
            $admin_email = get_option('fds_captcha_email_list');
            $to = $admin_email;
            $subject = get_site_url();
            $body = '<p>Name: ' . $_POST['name'] . '</p><p>Email: ' . $_POST['email'] . '</p><p>Message: ' . $_POST['msg'] . '</p><p>Page URL: ' . $_SERVER['HTTP_REFERER'] . '</p><p>IP Address: ' . $_SERVER['REMOTE_ADDR'] . '</p>';
            $headers = array('From: FDS Contact Form' . 'Content-Type: text/html; charset=UTF-8');
            if (wp_mail($to, $subject, $body, $headers)) {
                $location = $_SERVER['HTTP_REFERER'];
                wp_send_json_success(array('status' => true, 'msg' => 'Thanks For contacting Us.'));
            }
        } else {
            // The user's input is incorrect, display an error message and allow them to try again
            wp_send_json_error(array('status' => false, 'msg' => 'CAPTCHA incorrrect. Please reload and try again.'));
        }
    }
    wp_send_json_error(array('status' => false, 'msg' => 'CAPTCHA expired. Please reload and try again.'));
}


function verify_captcha_expiry()
{
    // Get the CAPTCHA string and time from the session
    $captcha_time = $_SESSION['captcha_time'];

    // Check if the CAPTCHA has expired (1 minute = 60 seconds)
    if (time() - $captcha_time > 60) {
        // The CAPTCHA has expired, send an error response
        return 0;
    } else {
        return 1;
    }
}


function generate_captcha_image()
{
    session_start();
    // Set the content type to image/png
    header('Content-Type: image/png');

    // Set the HTTP response code to 200 (OK)
    http_response_code(200);

    // Create a blank image with a white background
    $image = imagecreatetruecolor(200, 50);
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);

    // Generate a random string for the CAPTCHA
    $characters = 'ABCDEFGHIJKLM123456789NOPQRSTUVWXYZ';
    $characters_length = strlen($characters);
    $captcha_string = '';
    for ($i = 0; $i < 6; $i++) {
        $captcha_string .= $characters[rand(0, $characters_length - 1)];
    }

    // Add the CAPTCHA string to the image as text
    $black = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 5, 75, 15, $captcha_string, $black);
    $_SESSION['captcha_string'] = $captcha_string;
    $_SESSION['captcha_time'] = time();

    // Output the image to the browser
    ob_start();
    imagepng($image);
    $image_data = ob_get_clean();

    // Free up memory
    imagedestroy($image);

    // Send the image data as a JSON object
    wp_send_json_success(array('image' => base64_encode($image_data)));
}


// Define the shortcode tag
$shortcode_tag = 'fds-captcha-form';

// Define the shortcode callback function
function fds_captcha_display_captcha_form_shortcode()
{
    // Display the form
    ob_start();
    display_captcha_form();
    return ob_get_clean();
}

// Register the shortcode
add_shortcode($shortcode_tag, 'fds_captcha_display_captcha_form_shortcode');

function display_captcha_form()
{
    ?>
    <form id="fds_contact_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <label for="name">Name:*</label>
        <br>
        <input type="text" id="fds_contact_name" name="name">
        <br>
        <span id="fds_contact_name_status" style="color: red; font-size: 12px; display:none;">Name is required.</span>
        <br>
        <label for="email">Email:</label>
        <br>
        <input type="email" id="fds_contact_email" name="email">
        <br>
        <span id="fds_contact_email_status" style="color: red; font-size: 12px; display:none;">Email is required.</span>
        <br>
        <label for="message">Message:</label>
        <br>
        <textarea id="fds_contact_message" name="message" required></textarea>
        <br>
        <span id="fds_contact_msg_status" style="color: red; font-size: 12px; display:none;">Message is required.</span>
        <br>
        <label for="captcha-input">Enter the text shown in the image:</label><br>
        <input type="text" name="captcha-input" id="fds_captcha_input" required><br>
        <p id="captcha_container"></p><br>
        <span id="fds_contact_captcha_status"
              style="color: red; font-size: 12px; display:none;">Captcha is required.</span>
        <img id="captcha-img" src="" alt="CAPTCHA image" style="display:none;">
        <button id="reload_captcha" style="display:none;">Reload</button>
        <br>
        <input type="submit" id="submit_fds_contact" value="Submit">
        <input type="hidden" name="action" value="verify_captcha">
    </form>
    <p id="success_msg"></p>
    <?php
}

add_action('admin_post_verify_captcha', 'display_captcha_form');

if (file_exists( plugin_dir_path( __FILE__ ) . 'fds_captcha_settings.php' )) {
    require_once( plugin_dir_path( __FILE__ ) . 'fds_captcha_settings.php' );
}
function fds_captcha_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=fds-captcha-settings' ) . '">Settings</a>';
    array_push( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'fds_captcha_settings_link' );
