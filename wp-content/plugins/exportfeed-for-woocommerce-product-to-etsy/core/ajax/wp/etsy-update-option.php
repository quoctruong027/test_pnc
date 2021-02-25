<?php
if (!defined('ABSPATH')) exit;
if (!is_admin()) die('Permission Denied!');
/* *
 *
 *
 *
 * */
$optionname = sanitize_post($_POST['optionkey']);
$value = sanitize_post($_POST['propertykey']);
$message = '';
if (get_option($optionname) === $value) {
    $message = "Value is unchanged";
} else {
    $message = update_option($optionname, $value) ? 'Successfylly updated' : 'Something went wrong, please try again.';
}
echo json_encode(array(
        'status' => true,
        'data' => array(
            'message' => $message,
            'success' => true
        ))
);
exit;
