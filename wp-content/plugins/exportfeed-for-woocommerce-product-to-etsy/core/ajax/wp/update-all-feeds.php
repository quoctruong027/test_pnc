<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
/*$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;*/
$feed_ids = isset($_POST['feed_id']) ? $_POST['feed_id'] : array();
etcpf_update_all(false, $feed_ids);
echo 'Update successful';
