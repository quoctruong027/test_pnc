<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
if (defined('ENV') && ENV == 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

define('XMLRPC_REQUEST', true);
$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check) {
    return;
}
global $wpdb;

require_once dirname(__FILE__) . '/../../classes/etsyclient.php';
require_once dirname(__FILE__) . '/../../classes/etsy-upload.php';

$feed_id = intval($_REQUEST['feed_id']);
$resubmit = isset($_REQUEST['resubmit']) ? boolval($_REQUEST['resubmit']) : false;
$etsyUploader = new ETCPF_EtsyUpload();
$essentials = array(
    'uploadType' => sanitize_text_field($_POST['uploadType']),
    'variation_profile' => sanitize_text_field($_POST['uploadType'])==='single' ? $_POST['variation_profile'] : 0
);
$currentcount = get_option('etsy_current_uploading_' . $feed_id);
update_option('etsy_current_uploading_' . $feed_id, (intval($currentcount) - 1));

if (isset($_REQUEST['uploadfailed']) && $_REQUEST['uploadfailed'] == true) {
    $task = 3;
} elseif ($resubmit == true) {
    $task = 7;
} else {
    $task = 0;
}


if (isset($_POST['status']) && ($_POST['status'] == 'CONTINUE' || $_POST['status'] == 'START')) {
    $itemid = $_REQUEST['item_id'];
    $result = $etsyUploader->submit_listing_to_etsy($itemid, $task,$feed_id, $essentials);
} elseif (isset($_POST['status']) && $_POST['status'] == 'FINISHED') {
    $uploadfailedResult = $etsyUploader->checkFailedListing($feed_id, 3);
    $result = $etsyUploader->submit_listing_to_etsy($feed_id, 3,$feed_id, $essentials);
    if ($result['status'] == 'FINISHED') {
        $result['status'] = 'CONFIRM_FINISHED';
    }
} elseif (isset($_POST['status']) && $_POST['status'] == 'CONFIRM_FINISHED') {
    $remaining = $etsyUploader->get_remaining_listting($feed_id, 0);
    if ($remaining == false) {
        $result = array('status' => 'FINISH_CONFIRMED');
        update_option('currently_uploading_feed_id',0);
    } else {
        $result = array('status' => 'CONTINUE', 'type' => 0);
    }
} elseif (isset($_POST['status']) && $_POST['status'] == 'REUPLOAD') {
    $id = isset($_POST['itemId']) ? $_POST['itemId'] : null;
    if ($id == null) {
        echo json_encode(array('status' => 'failed', 'message' => 'Item id must be defined'));
        exit;
    }
    $result = $etsyUploader->UpoadListingByID($id);
} elseif (isset($_POST['status']) && $_POST['status'] == 'DELETE') {
    $listing_id = isset($_POST['itemId']) ? $_POST['itemId'] : null;
    if ($listing_id) {
        $etsyClient = new ETCPF_Etsy();
        $result = $etsyClient->deleteListing($listing_id);
    } else {
        $result = array('status' => 'FAILED', 'message' => 'Empty Listing Id');
    }
} elseif(isset($_POST['status'])&& $_POST['status']=='FetchAllItemIds' && $resubmit === true){
    $table = $wpdb->prefix.'etcpf_listings';
    $feedIds = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$table} WHERE feed_id= %d ORDER BY id DESC", array($feed_id)));
    $result = array('status'=>true,'data'=>$feedIds);
}elseif( isset($_POST['status'])&& $_POST['status']=='FetchAllItemIds' && $resubmit === false) {
    $table = $wpdb->prefix.'etcpf_listings';
    $feedIds = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$table} WHERE feed_id= %d AND listing_id IS NULL ORDER BY id DESC", array($feed_id)));
    $result = array('status'=>true,'data'=>$feedIds);
}else{
    if(isset($_POST['status'])){
        $result=array('status'=>'FAILED',"Could not find the specified action {$_POST['status']}");
    }else{
        $result=array('status'=>'FAILED',"Action Not specified");
    }
}

echo json_encode($result);
exit;
