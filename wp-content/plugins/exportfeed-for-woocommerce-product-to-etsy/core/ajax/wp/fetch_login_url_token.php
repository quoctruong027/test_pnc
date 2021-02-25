<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
require_once ETCPF_PATH . '/core/classes/etsyclient.php';

$etsy = new ETCPF_Etsy();
//$etsy->checkInformation(); // will added later
$url = $etsy->fetchLoginToken();
$output = [];

$output['success'] = false;
$output['url'] = $url;
$out['stage'] = 1;
if (strlen($url) > 0) {
    $pos = strpos($url, '=');
    $url = substr($url, $pos + 1);
    $params = explode("&", $url);
    #echo '<pre>';print_r($params);die;
    $oauth_token_secret = str_replace('oauth_token_secret=', "", $params[4]);
    $output['url'] = $url;
    $output['success'] = true;
    update_option('etcpf_oauth_token_secret', $oauth_token_secret);
    update_option('etcpf_login_url', $url);
    update_option('etcpf_stage', 2);
}
echo json_encode($output);
die;