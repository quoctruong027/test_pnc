<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
require_once ETCPF_PATH . '/core/classes/etsyclient.php';

$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;

global $wpdb;

$data = array();
$res = array();
$res['success'] = false;
$res['btn'] = '';
$listing_id = sanitize_text_field($_REQUEST['listing_id']);
$id = sanitize_text_field($_REQUEST['id']);
$data['who_made'] = sanitize_text_field($_REQUEST['who_made']);
$data['when_made'] = sanitize_text_field($_REQUEST['when_made']);
$data['state'] = sanitize_text_field($_REQUEST['state']);
$data['shipping_template_id'] = sanitize_text_field($_REQUEST['shipping_template_id']);
$data['uploaded'] = 0;

update_option('etcpf_update_status', 'Initializing');

$tbl = $wpdb->prefix . "etcpf_tmp_etsy_listing";
$update = $wpdb->update($tbl, $data, ['listing_id' => $listing_id]);

if ($update) {
    $etsy = new ETCPF_Etsy();

    $res['success'] = true;
    update_option('etcpf_update_status', 'Connect to etsy...');

    $sql = $wpdb->prepare("SELECT * FROM $tbl WHERE id = %d", [$id]);
    $item = $wpdb->get_row($sql, ARRAY_A);

    update_option('etcpf_update_status', 'Uploading to Etsy ... ');
    $upload = $etsy->startUpload($item);
    if ((is_string($upload) && (is_object(json_decode($upload)) || is_array(json_decode($upload))))) {
        $upload = json_decode($upload);
        $uploaded_data = $upload->results[0];
        $data = array();
        $data['listing_id'] = $uploaded_data->listing_id;
        $data['shop_id'] = $uploaded_data->user_id;
        $data['shipping_template_id'] = $uploaded_data->shipping_template_id;
        $data['uploaded'] = 1;
        $data['result'] = '';
        $data['last_updated'] = date('Y-m-d H:i:s');

        $etsy->updateUploadData($data, $item['id']);
        $etsy->uploadImage($data['listing_id'], $item['image']);
        update_option('etcpf_update_status', 'Completed.');

        $res['msg'] = '<span style="color:green">' . $item['title'] . ' is updated in Etsy.</span>';
    } else {
        $res['msg'] = '<span style="color:red">' . $upload . '</span>';
        $res['success'] = false;
    }

}
echo json_encode($res);
die;