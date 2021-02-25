<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;
$map_string = get_option('etcpf_attribute_user_map_Etsy');

if (strlen($map_string) == 0) {
    $map = array();
} else {
    $map = json_decode($map_string);
    $map = get_object_vars($map);
}

$attr = sanitize_text_field($_POST['attribute']);
$mapto = sanitize_text_field($_POST['mapto']);
$map[$mapto] = $attr;

if ($attr == '(Reset)') {
    $new_map = array();
    foreach ($map as $index => $item) {
        if ($index != $mapto) {
            $new_map[$index] = $item;
        }
    }
    $map = $new_map;
}

update_option('etcpf_attribute_user_map_Etsy', wp_json_encode($map));