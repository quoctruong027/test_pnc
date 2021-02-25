<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;
$attr = sanitize_text_field($_POST['attribute']);
$map = sanitize_text_field($_POST['mapto']);
$service_type = isset($_POST['service_name']) ? sanitize_text_field($_POST['service_name']) : 'Etsy';
update_option('ETCPF_' . $service_type . '_cp_' . $attr, $map);