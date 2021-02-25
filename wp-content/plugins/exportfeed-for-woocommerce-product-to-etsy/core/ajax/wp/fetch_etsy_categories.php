<?php
if (!defined('ABSPATH')) {
	exit;
}
// Exit if accessed directly
if (!is_admin()) {
	die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check) {
	return;
}

require_once dirname(__FILE__) . '/../../data/feedcore.php';
require_once dirname(__FILE__) . '/../../classes/etsyclient.php';
// echo plugins_url('../classes/etsyclient.php',dirname(__FILE__));
global $wpdb;
$service_name = array_key_exists('service_name', $_POST) ? sanitize_text_field($_POST['service_name']) : null;

$data = '';
if (class_exists('CPF_Taxonomy')) {
	$data = CPF_Taxonomy::onLoadTaxonomy(strtolower($_POST['service_name']));
}

/*if (strlen($data) == 0)
$data = file_get_contents(dirname(__FILE__) . '/../../feeds/' . strtolower($service_name) . '/categories.txt');

$data = explode("\n", $data);*/

$searchTerm = array_key_exists('partial_data', $_POST) ? sanitize_text_field($_POST['partial_data']) : null;
$count = 0;
$canDisplay = true;
$id = get_current_user_id();
if ($id == 0) {
    $id = array_key_exists('service_status', $_POST) ? sanitize_text_field($_POST['service_status']) :null ;
}

$etsy = new ETCPF_Etsy($id);
$level = sanitize_text_field($_POST['level']);
switch ($level) {

case '1':
	$etsy->fetchEtsyCategories();
	break;

case '2':
	$etsy->shippingTemplate();
	break;

case '3':
	$etsy->timeToUpload();
	break;

case '4':
	$etsy->deleteShipping();
	break;

case '5':
	$shipping_data = $_POST['shipping_details'];
	$flag = sanitize_text_field($_POST['flag']);
	if (!is_array($shipping_data)) {
		return '';
	}

	$data = $etsy->createShippingTemplate($shipping_data);
	$result = json_decode($data);

	if (!is_object($result)) {
		die('Unable to create shipping template. Please try once again after few moments.');
	}

	global $wpdb;
	$tbl = $wpdb->prefix . "etcpf_shipping_template";
	foreach ($result->results as $res) {
		$insertData = array();
		$insertData['shipping_template_id'] = $res->shipping_template_id;
		$insertData['title'] = $res->title;
		$insertData['processing_days_display_label'] = $res->processing_days_display_label;
		$insertData['country'] = $etsy->countryByID($res->origin_country_id);
		$wpdb->insert($tbl, $insertData);
		if ($flag == 1) {
			$etsy->makeDefaultShipping($res->shipping_template_id);
		}
		die('Shipping template created');
	}

	break;

case '6':
	$id = intval($_POST['shipping_id']);
	$etsy->makeDefaultShipping($id);
	echo 'Updated!';
	die;
	break;

case '7':
	$etsy->makeDefaultShop();
	break;

case '8':
	$etsy->shippingTemplate();
	break;

case '9':
	$etsy->changeConfigurations();
	break;
case '10':
	$etsy->deleteAccount();
	break;
case '11':
    $etsy->getEtsyShopLang();
	$etsy->get_shipping_info();
	break;

case 'fetch_products':

	$sql = $wpdb->prepare("SELECT url FROM " . $wpdb->prefix . "etcpf_feeds WHERE id = %d", [intval($_POST['feed_id'])]);
	$f_path = $wpdb->get_row($sql);

	$feed = file_get_contents($f_path->url);
	$xml = simplexml_load_string($feed, 'SimpleXMLElement', LIBXML_NOCDATA);
	$items = array();
	$i = 0;

	foreach ($xml->channel->item as $entry) {

		$item = $entry->children();
		$item = json_encode($item);
		$item = json_decode($item);
		$thisItem = new stdClass();
		$thisItem->id = $item->id;
		$thisItem->title = $item->title;
		$thisItem->stock = $item->quantity;
		$thisItem->price = $item->price[0];
		$thisItem->remote_category = $item->etsy_category;

		// get the listing details if the product is already uploaded
		$upload_details = $etsy->get_uploaded_details($item->id, intval($_POST['feed_id']));
		$thisItem->upload_details = $upload_details;

		$items[$i] = $thisItem;
		unset($thisItem);
		$i++;
	}

	$etsy->view('upload-listing-product-tab', [
		'products' => $items,
		'feed_id' => intval($_POST['feed_id']),
	]);
	wp_die();
	break;

case 'upload_to_etsy':

	$data = array();
	$data['category'] = sanitize_text_field($_POST['remote_category']);
	$data['product_id'] = $_POST['item_id'] > 0 ? intval($_POST['item_id']) : 0;
	$etsy->upload_listing($data);
	die();

	break;
case 'update_upload_message':
	global $message;
	echo $message;
	break;

case 'get_etsy_category_tree':
	$etsy->get_remote_category();
	break;

case 'prepare_from_product':
	$etsy->prepare_from_product();
	break;

case 'upload_from_product':
	$etsy->upload_from_product();
	break;
case 'save_credentials':
	$etsy->saveCredential();
	break;
case 'remove_queue_list':
	$table = $wpdb->prefix . "etcpf_listings";
	$wpdb->delete($table, ['uploaded' => 3]);
	break;
case 'reconnect_etsy':
	$etsy->deleteAccount();
	break;
default:
	# code...
	break;
}
