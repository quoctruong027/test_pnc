<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
//ob_start(null, 0, PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_CLEANABLE);
$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;
ob_start(null);

if(defined('ENV')&&ENV=='development'){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

}

function etcpf_safe_post_data($index)
{
    if (isset($_POST[$index])) {
        return $_POST[$index];
    } else {
        return '';
    }
}

function etcpf_output($output)
{
    ob_clean();
    echo wp_json_encode($output);
}

require_once dirname(__FILE__) . '/../../../etsy-export-feed-wpincludes.php';

do_action('load_etcpf_modifiers');
global $etcore;
$etcore->trigger('etcpf_init_feeds');

add_action('etcpf_feed_main_hook', 'etcpf_feed_main');
do_action('etcpf_feed_main_hook');

function etcpf_feed_main()
{
    $requestCode = sanitize_text_field(etcpf_safe_post_data('provider'));
    $local_category = sanitize_text_field(etcpf_safe_post_data('local_category'));
    $remote_category = sanitize_text_field(etcpf_safe_post_data('remote_category'));
    $file_name = sanitize_text_field(etcpf_safe_post_data('file_name'));
    $feedIdentifier = intval(etcpf_safe_post_data('feed_identifier'));
    $saved_feed_id = intval(etcpf_safe_post_data('feed_id'));
    $feed_list = sanitize_text_field(etcpf_safe_post_data('feed_ids')); //For Aggregate Feed Provider
    $feed_type = 0;
    $remote_category_path = sanitize_text_field(etcpf_safe_post_data('category_path'));
    $full_taxonomy_path = sanitize_text_field(etcpf_safe_post_data('full_taxonomy_path'));
    $regenerate = sanitize_text_field(etcpf_safe_post_data('regenerate'));
    if($regenerate==true){
        update_option('ETCPF_RESOLVED','resolved');
    }
    global $etcore;
    $etcore->feedType = $feed_type;

    $output = new stdClass();
    $output->url = '';

    if (strlen($requestCode) * strlen($local_category) == 0) {
        $output->errors = 'Error: error in AJAX request. Insufficient data or categories supplied.';
        etcpf_output($output);

        return;
    }

    if (strlen($remote_category) == 0) {
        $output->errors = 'Error: Insufficient data. Please fill in "' . $requestCode . ' category"';
        etcpf_output($output);
        return;
    }

    // Check if form was posted and select task accordingly
    $dir = ETCPF_FeedFolder::uploadRoot();
    if (!is_writable($dir)) {
        $output->errors = "Error: $dir should be writeable";
        etcpf_output($output);

        return;
    }
    $dir = ETCPF_FeedFolder::uploadFolder();
    if (!is_dir($dir)) {
        mkdir($dir);
    }
    if (!is_writable($dir)) {
        $output->errors = "Error: $dir should be writeable";
        etcpf_output($output);

        return;
    }

    $providerFile = 'feeds/'.strtolower($requestCode).'/feed.php';
    if (!file_exists(dirname(__FILE__) . '/../../' . $providerFile)) {
        if (!class_exists('ETCPF_'.$requestCode.'Feed')) {
            $output->errors = 'Error: Provider file not found.';
            etcpf_output($output);
            return;
        }
    }

    $providerFileFull = dirname(__FILE__) . '/../../' . $providerFile;
    if (file_exists($providerFileFull)) {
        require_once $providerFileFull;
    }

    //Load form data
    $file_name = sanitize_title_with_dashes($file_name);
    if ($file_name == '') {
        $file_name = 'feed' . rand(10, 1000);
    }

    $saved_feed = null;
    if ((strlen($saved_feed_id) > 0) && ($saved_feed_id > -1)) {
        require_once dirname(__FILE__) . '/../../data/savedfeed.php';
        $saved_feed = new ETCPF_SavedFeed($saved_feed_id);
    }

    $providerClass = 'ETCPF_'.$requestCode.'Feed';
    $x = new $providerClass;
    $x->feed_list = $feed_list; //For Aggregate Provider only
    if (strlen($feedIdentifier) > 0) {
        $x->activityLogger = new ETCPF_FeedActivityLog($feedIdentifier);
    }
    $x->getFeedData($local_category, $remote_category, $file_name, $saved_feed,$remote_category_path,$full_taxonomy_path,false);

    if ($x->success) {
        $feed_id = ETCPF_FeedActivityLog::feedDataToID($file_name,$x->providerName);
        $output->url = ETCPF_FeedFolder::uploadURL() . $x->providerName . '/' . $file_name . '.' . $x->fileformat;
        $errorOutput = $x->getErrorReportList($file_name,$feed_id);
        $output->errorsreport = null;
        $output->errorUrl = null;
        if($errorOutput['error']==true){
            $csvlink = $errorOutput['filelink'];
            $output->errorUrl = admin_url().'admin.php?page=etsy-export-feed-manage&option=errorreportpage&feed_id='.$feed_id;
            $output->errorsreport="error exixts";
        }else{
            $output->errorsreport="error exixts";
            $output->errorUrl = admin_url().'admin.php?page=etsy-export-feed-manage&option=errorreportpage&feed_id='.$feed_id;
        }
    }
    //$output->errors = $x->getErrorMessages();
    $output->errors = false;
    etcpf_output($output);
}
