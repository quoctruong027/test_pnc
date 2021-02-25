<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);

$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;

require_once dirname(__FILE__) . '/../../data/feedcore.php';
$service_name = 'Etsy';

$data = '';
if (strlen($data) == 0) {
    $data = file_get_contents(dirname(__FILE__) . '/../../feeds/etsy/categories.txt');
}

$data = explode("\n", $data);
$partial_data = sanitize_text_field($_POST['partial_data']);
$search_term = strtolower($partial_data);
$count = 0;
$can_display = true;
foreach ($data as $this_item) {

    if (strlen($this_item) * strlen($search_term) == 0) {
        continue;
    }

    if (strpos(strtolower($this_item), $search_term) !== false) {

        // Transform item from chicken-scratch into something the system can recognize later.
        $option = str_replace(' & ', '.and.', str_replace(' / ', '.in.', trim($this_item)));
        $option = str_replace("'", '', $option);

        // Transform a category from chicken-scratch into something the user can read.
        $text = htmlentities(trim($this_item));

        if ($can_display) {
            echo '<div class="categoryItem" onclick="googleDoSelectCategory_default(this, \'' . $option . '\', \'' . $service_name . '\')">' . $text . '</div>';
        }
        $count++;
        if ((strlen($search_term) < 3) && ($count > 15)) {
            $can_display = false;
        }
    }
}

if ($count == 0) {
    // echo 'No matching categories found'
}

if (!$can_display) {
    echo '<div class="categoryItem">( ' . esc_html($count) . ' results )</div>';
}