<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
require_once ETCPF_PATH . '/core/classes/etsyclient.php';
$etsy = new ETCPF_Etsy();
$token = $etsy->doTheAuthorizingThingy();
$res = explode('&', $token);
$output = array();
$output['success'] = false;
if (is_array($res) && count($res)>0) {
    $oauth_token = str_replace('oauth_token=', "", $res[0]);
    $oauth_token_secret = str_replace('oauth_token_secret=', "", $res[1]);
    update_option('etcpf_oauth_token', $oauth_token);
    update_option('etcpf_oauth_token_secret', $oauth_token_secret);
    update_option('etcpf_stage', 4);
    update_option('etcpf_connected_to_shop', 1);
    $output['success'] = true;
}
echo json_encode($output);
