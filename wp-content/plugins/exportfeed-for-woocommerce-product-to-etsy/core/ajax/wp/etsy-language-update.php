<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
require_once dirname(__FILE__) . '/../../classes/etsyclient.php';
$etsy = new ETCPF_Etsy(null);
$data = array(
    'status' => $etsy->getEtsyShopLang(),
    'success' => true
);
wp_send_json_success($data);exit();
