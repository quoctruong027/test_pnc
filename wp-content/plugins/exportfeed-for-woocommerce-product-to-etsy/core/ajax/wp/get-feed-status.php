<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);

$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;

ob_start(null);
function etcpf_safe_get_status_data($index)
{
    if (isset($_POST[$index])) {
        return $_POST[$index];
    } else {
        return '';
    }
}

$feedIdentifier = intval(etcpf_safe_get_status_data('feed_identifier'));

ob_clean();
echo get_option('etcpf_etsyfeedActivity_' . $feedIdentifier);