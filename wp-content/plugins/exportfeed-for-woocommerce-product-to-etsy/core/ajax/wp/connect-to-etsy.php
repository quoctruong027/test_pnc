<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
require_once dirname(__FILE__) . '/../../classes/etsyclient.php';
$etsy = new ETCPF_Etsy();
$etsy->requestToken();