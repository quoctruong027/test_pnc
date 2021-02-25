<?php
/***********************************************************
 * Plugin Name: ExportFeed for WooCommerce Product To Etsy
 * Plugin URI: www.exportfeed.com
 * Description: Etsy's Feed of WooCommerce Product Feed Export :: <a target="_blank" href="http://www.exportfeed.com/tos/">How-To Click Here</a>
 * Author: ExportFeed.com
 * Version: 3.2.1.1
 * Author URI: www.exportfeed.com
 * License:  GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: etsy-exportfeed-strings
 * Authors: sabinthapa8, roshanbh, tekrajstha
 * WC requires at least: 3.0.0
 * WC tested up to: 5.5
 * Note: The "core" folder is shared to the Joomla component.
 * Changes to the core, especially /core/data, should be considered carefully
 * license GNU General Public License version 3 or later; see GPLv3.txt
 ***********************************************************/
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
require_once ABSPATH . '/wp-admin/includes/plugin.php';
$plugin_version_data = get_plugin_data(__FILE__);
// current version: used to show version throughout plugin pages.
define('ETCPF_PLUGIN_VERSION', $plugin_version_data['Version']);
define('ETCPF_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('ETCPF_PATH', realpath(dirname(__FILE__)));
define('ETCPF_URL', plugins_url() . '/' . basename(dirname(__FILE__)) . '/');
// functions to display google_merchant_feeds version and checks for updates.
include_once 'etcpf_functions.php';
include_once 'etsy-export-feeds-information.php';
require_once 'etsy-export-feed-setup.php';

// action hook for plugin activation.
register_activation_hook(__FILE__, 'etcpf_activate_plugin');
register_deactivation_hook(__FILE__, 'etcpf_deactivate_plugin');

add_action('etcpf_plugins_loaded', 'etcpf_activate_plugin');
$ETCPF_DBVERSION = get_option('ETCPF_DBVERSION');

if ($ETCPF_DBVERSION !== ETCPF_PLUGIN_VERSION) {
    do_action('etcpf_plugins_loaded');
    update_option('ETCPF_DBVERSION', ETCPF_PLUGIN_VERSION);
}

global $cp_feed_order, $cp_feed_order_reverse;

// need to know what this cron activity will do.
require_once 'core/classes/cron.php';
require_once 'core/data/feedfolders.php';

if (get_option('et_cpf_feed_order_reverse') == '') {
    add_option('et_cpf_feed_order_reverse', false);
}

if (get_option('et_cp_feed_order') == '') {
    add_option('et_cp_feed_order', "id");
}

if (get_option('et_cp_localkey') == '') {
    add_option('et_cp_localkey', "none");
}

if (get_option('et_cp_feed_limit') == '') {
    add_option('et_cp_feed_limit', '0');
}

//seting setup stage phase
if (get_option('etcpf_stage') == '') {
    add_option('etcpf_stage', 1);
}

if (get_option('et_cp_feed_count') == '') {
    add_option('et_cp_feed_count', '0');
}

if (get_option('listing_etsy_et') == '') {
    add_option('listing_etsy_et', 0);
}
if (get_option('etsy_order_offset') == '') {
    add_option('etsy_order_offset', 0);
}
add_option('etcpf_license_message', 0);
//***********************************************************
// cron schedules for Feed Updates
//***********************************************************
$cron = new ETCPF_Cron();
$cron->scheduleetsyFetchProduct();
if (get_etsy_settings('feed_update_interval')) {
    $cron->etsyFeedUpdateCron();
}
if (get_etsy_settings('order_fetch_interval')) {
    $cron->scheduleetsyOrder();
}
if (get_etsy_settings('feed_submission_interval')) {
    $cron->scheduleetsyUpload();
}
/*ETCPF_Auto_Feedsubmission::doSetup();*/


// ETCPF_Auto_Feedsubmission::scheduleetsyUpload();
// ETCPF_Order_Management::scheduleetsyOrder();
// ETCPF_Fetch_Product::scheduleetsyFetchProduct();
// ETCPF_Mapping_Product::scheduleetsyMappingProduct();
/*function etcpf_cron_schedules($schedules)
{
    if (!isset($schedules["Etsy Five Minute XML Refresh"])) {
        $schedules["Etsy Five Minute XML Refresh"] = array(
            'interval' => 5 * 60,
            'display'  => __('Once every 5 minutes'));
    }
    return $schedules;
}*/

// ETCPF_Auto_Feedsubmission::scheduleetsyUpload();
// ETCPF_Order_Management::scheduleetsyOrder();


//***********************************************************
// Update Feeds (Cron)
//   2014-05-09 Changed to now update all feeds... not just Etsy Feeds
//***********************************************************

add_action('update_etsyfeeds_hook', 'etcpf_update_all_abstract');
add_filter('auto_feed_submission_hook', 'etcpf_auto_feed_submission');
add_filter('auto_fetch_product_hook', 'auto_fetch_product');
// add_filter('auto_mapping_product_hook', 'auto_mapping_product');
add_filter('auto_etsy_order_hook', 'auto_etsy_order');
//do_action('auto_feed_submission_hook');
function etcpf_get_gif_loader($class, $style = array(), $gif = false, $original = false, $ripple = 'ripple.gif')
{
    $css = '';
    if (count($style) > 0) {
        foreach ($style as $key => $value) {
            $css .= $key . ':' . $value . ';';
        }
    }
    $dimension = ' height="25" wdth="30" ';
    if ($original) {
        $dimension = "";
    }

    if ($gif) {
        $ripple = 'ripple2.gif';
    }

    if (!strlen($class) > 0) {
        return;
    }

    echo '<img class="' . $class . '" style="display:none;' . $css . '" src="' . ETCPF_URL . '/images/' . $ripple . '"' . $dimension . '/>';
}

function etcpf_get_biggif_loader($class, $css = array())
{
    $style = "";
    if (count($css) > 0) {
        foreach ($css as $key => $value) {
            $style .= $key . ':' . $value . ';';
        }
    }
    if (!strlen($class) > 0) {
        return;
    }

    echo '<img class="' . $class . '" style="' . $style . '" src="' . ETCPF_URL . '/images/bigload.gif' . '"/>';
}

function etcpf_update_all_abstract(){
    etcpf_update_all(true, false);
}

function etcpf_update_all($doRegCheck = true, $feedids)
{
    require_once 'etsy-export-feed-wpincludes.php'; //The rest of the required-files moved here
    require_once 'core/data/savedfeed.php';

    $reg = new ETCPF_EtsyValidation();

    if ($doRegCheck == true ) {
        if(is_object($reg->results) && $reg->results->status != "Active"){
            $dir = wp_upload_dir();
            $upload_die = $dir['basedir'];
            $myfile = fopen($upload_die . "/etsy.txt", "w") or die("Unable to open file!");
            $txt = json_encode($reg);
            fwrite($myfile, $txt);
            fclose($myfile);
            return;
        }
        
        if(is_array($reg->results) && $reg->results['status'] != "Active"){
            $dir = wp_upload_dir();
            $upload_die = $dir['basedir'];
            $myfile = fopen($upload_die . "/etsy.txt", "w") or die("Unable to open file!");
            $txt = json_encode($reg);
            fwrite($myfile, $txt);
            fclose($myfile);
            return;
        }
    }
    do_action('load_etcpf_modifiers');
    add_action('etcpf_feed_main_hook', 'etcpf_update_all_step_2');
    do_action('etcpf_feed_main_hook', $feedids);

    // to upload images
    add_action('etcpf_mutipl_images_upload', 'etcpf_doImageThingy');
    do_action('etcpf_multipl_images_upload');
}

function etcpf_doImageThingy()
{
    require_once 'etsy-export-feed-wpincludes.php';
    $etsy = new ETCPF_Etsy();
    $etsy->upload_additional_images();
}

function etcpf_update_all_step_2($feedIds)
{
    global $wpdb;
    $feed_table = $wpdb->prefix . 'etcpf_feeds';
    $where = '';
    if ($feedIds) {
        $where = ' WHERE id ='.$feedIds;
        $limit = '';
    } else {
        $limit = ' ORDER BY updated_at ASC limit 2';
    }
    $sql = 'SELECT id, type, filename,product_count FROM ' . $feed_table . $where . $limit;
    $feed_ids = $wpdb->get_results($sql);
    $savedProductList = null;
    //***********************************************************
    //Build stack of aggregate providers
    //***********************************************************
    $aggregateProviders = array();
    //***********************************************************
    //Main
    //***********************************************************
    foreach ($feed_ids as $index => $this_feed_id) {
        $saved_feed = new ETCPF_SavedFeed($this_feed_id->id);
        //Make sure someone exists in the core who can provide the feed
        $providerFile = 'core/feeds/etsy/feed.php';
        if (!file_exists(dirname(__FILE__) . '/' . $providerFile)) {
            continue;
        }
        require_once $providerFile;
        //Initialize provider data
        $x = new ETCPF_EtsyFeed();
        $x->savedFeedID = $saved_feed->id;

        $catpath = $saved_feed->category_path;
        $taxonomy_path = $saved_feed->texonomy_path;
        $x->productList = $savedProductList;

        if ($saved_feed->feed_type <= 0) {
            $x->getFeedData($saved_feed->category_id, $saved_feed->remote_category, $saved_feed->filename, $saved_feed, $catpath, $taxonomy_path, true);
            $savedProductList = $x->productList;
            $x->products = null;
        } else {
            include_once 'core/classes/ETCPF_Customfeed.php';
            $CustomObj = new ETCPF_Customfeed(1);
            $params = array(
                'filename' => $saved_feed->filename,
                'categories' => $CustomObj->getMappedCategoryOfParticularFeed($this_feed_id->id),
                'products' => $CustomObj->getProductsOfParticularFeed($this_feed_id->id),
                'feedtype' => 1
            );
            $x->updateCustomFeed($this_feed_id->id, $params, $saved_feed);
        }

        $listingtable = $wpdb->prefix . 'etcpf_listings';
        $check = $wpdb->get_results("SELECT count(id) as count FROM $listingtable WHERE uploaded=0 AND feed_id={$this_feed_id->id}");
        if (intval($check[0]->count) > 0) {
            //error_log("came to count. count is:".json_encode($check));
            continue;
        } else {
            include_once 'core/classes/etsy-upload.php';
            //error_log("came to preparing listing for id {$this_feed_id->id}");
            $prepareData = new ETCPF_EtsyUpload();
            $prepareData->prepare_the_list_from_feed($this_feed_id->id, true);
            update_option('currently_uploading_feed_id', 0);
        }

    }
    /*foreach ($aggregateProviders as $thisAggregateProvider)
      $thisAggregateProvider->finalizeAggregateFeed();*/

}

//***********************************************************
// Links From the Install Plugins Page (WordPress)
//***********************************************************

if (is_admin()) {
    require_once 'etsy-export-feed-admin.php';
    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_" . $plugin, 'etcpf_manage_feed');
}
//***********************************************************
//Function to create feed generation link  in installed plugin page
//***********************************************************
function etcpf_manage_feed($links)
{
    $settings_link = '<a href="admin.php?page=etsy-export-feed-manage">Manage Feeds</a>';
    array_unshift($links, $settings_link);
    return $links;
}


function etcpf_auto_feed_submission()
{
    include_once 'core/classes/Etsy_auto_uploader.php';
    $updater = new Etsyupdater();
    $updater->uploadtoetsy();
}

function auto_fetch_product()
{
    include_once 'core/classes/ETCPF_Etsyorder.php';
    $updater = new Etsyorder();
    $updater->countEtsyProduct();
}

// function auto_mapping_product()
// {
//     include_once 'core/classes/ETCPF_Etsyorder.php';
//     $updater = new Etsyorder();
//     $updater->mapEtsyProducts();
// }


function auto_etsy_order()
{
    include_once 'core/classes/ETCPF_Etsyorder.php';
    $offset = get_option('etsy_order_offset', $default = 0);
    $updater = new Etsyorder();
    $updater->fetch_etsy_orders(get_etsy_settings('order_fetching_time_interval'),$offset);
    do_action('etcpf_update_all_step_2');
}

add_action('woocommerce_update_product', 'mp_sync_on_product_save', 10, 1);
function mp_sync_on_product_save($product_id)
{
    if (!$product_id) return false;
    require_once 'core/classes/product_uploadhook.php';
    $invoker = new Product_uploadhoook($product_id);
    $invoker->Invoker();
    // do something with this product
}


/*function my_cron_schedules($schedules)
{
    $current_delay = get_option('et_cp_feed_delay');
    if (!isset($schedules["custom_etsy_interval"])) {
        $schedules["custom_etsy_interval"] = array(
            'interval' => strtotime($current_delay . ' seconds'),
            'display' => __('Etsy Custom Interval')
        );
    }
    return $schedules;
}*/
//add_filter('cron_schedules','my_cron_schedules');

