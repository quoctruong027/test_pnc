<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;
require_once dirname(__FILE__) . '/../../data/feedcore.php';

global $wpdb;
$provider_name = 'Etsy';

$sql = $wpdb->prepare("
			SELECT * FROM $wpdb->options
			WHERE $wpdb->options.option_name LIKE '%s'", like_escape($provider_name) . '_cpf_%');

$mappings = $wpdb->get_results($sql);
foreach ($mappings as $this_option) {
    delete_option($this_option->option_name);
}

echo '1';