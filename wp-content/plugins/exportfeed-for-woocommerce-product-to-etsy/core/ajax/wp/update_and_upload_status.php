<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
$msg = get_option('etcpf_update_status');
echo $msg;
die;