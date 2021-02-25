<?php
if (!defined('ABSPATH')) {
	exit;
}
// Exit if accessed directly
include_once dirname(__FILE__) . '/../data/productcategories.php';
include_once dirname(__FILE__) . '/../data/attributesfound.php';
include_once dirname(__FILE__) . '/../data/feedfolders.php';
include_once dirname(__FILE__) . '/../classes/etsyclient.php';
include_once dirname(__FILE__) . '/../data/savedfeed.php';
include_once dirname(__FILE__) . '/../classes/dialogbasefeed.php';

Class Errorreport {
	function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $this->db->prefix . "etcpf_feedproducts";
	}

	public function index() {
		global $wpdb;
        $table = $this->db->prefix . "etcpf_feedproducts";
		$link = $_REQUEST['feed_id'];
		$data['total_products'] = $wpdb->get_var("SELECT COUNT(DISTINCT p_id) FROM {$table} WHERE feed_id = {$link} AND status='active'");
		$data['success_products'] = $wpdb->get_var("SELECT COUNT(p_id) FROM {$table} WHERE feed_id = {$link} AND status='active' AND (error_status = '2' OR error_status = '1')");
		$data['error_product_count'] = $wpdb->get_var("SELECT COUNT(p_id) FROM {$table} WHERE feed_id = {$link} AND status='active' AND error_code <> 5000 AND (error_status ='0' OR error_status ='-1') ");
		$data['errortypes'] = $wpdb->get_results("SELECT * FROM {$table} WHERE feed_id = {$link} AND status='active' AND (error_status <> '2' AND error_status <> '1') GROUP BY error_code");
        $data['all_products'] = $this->getAllProducts($link);
		$data['successproducts'] = $wpdb->get_results("SELECT * FROM {$table} WHERE feed_id = {$link} AND status='active' AND (error_status = '2' OR error_status = '1') GROUP BY p_id");

		if($data['error_product_count']>0){
            update_option('ETCPF_RESOLVED','no');
        }

		foreach ($data['errortypes'] as $key => $value) {
			$data[$value->error_code . '_pcount'][] = $wpdb->get_var("SELECT COUNT(p_id) FROM {$table} WHERE feed_id = {$link} AND status='active' AND error_code = $value->error_code AND error_status <> '2'");
		}
		return $this->views($data);
	}

	public function getAllProducts($feedID){
	    $data = array();
	    $result = $this->db->get_results("SELECT DISTINCT p_id FROM {$this->table} WHERE feed_id={$feedID} AND status='active' ORDER BY error_status");
	    if($result){
	        foreach ($result as $key=>$value){
	            $relatedData = $this->db->get_results($this->db->prepare("SELECT * FROM $this->table WHERE p_id=%d AND feed_id = {$feedID}",array($value->p_id)));
	            $data[$value->p_id]['data'] = $relatedData;
                $data[$value->p_id]['contains_errors'] = false;
	            foreach($relatedData as $k=>$val){
	                if($val->error_status=='-1'||$val->error_status=='0'){
	                    $data[$value->p_id]['contains_errors'] = true;
                    }
                }
            }
            return $data;
        }
        return null;
    }

	public function getproductbyErrorCode($feed_id, $errorcode) {
		global $wpdb;
		$table = $wpdb->prefix . "etcpf_feedproducts";
		$productdata = $wpdb->get_results("SELECT * FROM {$table} WHERE feed_id = {$feed_id} AND status='active' AND error_code={$errorcode} AND (error_status<>'2' AND error_status <> '1')");
		return $productdata;
	}

	function views($data = array()) {
		include_once 'errorreport-view-page.php';
	}

	function getAttributes($mapTocode) {
		$mapTo = $this->getAttributeNameByCode($mapTocode);
		$found_attributes = new ETCPF_FoundAttribute();
		$object = new ETCPF_PBaseFeedDialog();
		$attributes = $object->createDropdownAttr($found_attributes, '', $mapto = $mapTo);
		return $attributes;
	}

	function regenerateFeed($feedid) {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'etcpf_feeds';
        $providerList = new ETCPF_ProviderList();
        $categoryMappingTable = $wpdb->prefix . 'etcpf_category_mappings';
        $sql_feeds = ("SELECT f.*, GROUP_CONCAT(CM.local_category_slug) as mappedlocalcategory,GROUP_CONCAT(CM.showValue) as mappedremotecategory FROM $feed_table f LEFT JOIN $categoryMappingTable CM on f.id=CM.feed_id WHERE f.id={$feedid}  GROUP BY f.id");
        $list_of_feeds = $wpdb->get_row($sql_feeds);
        if($list_of_feeds){
            return $list_of_feeds;
        }
        return null;

        /*$saved_feed = new ETCPF_SavedFeed($feedid);
		if (!is_null($saved_feed)) {
			return $saved_feed;
		} else {
			return null;
		}*/

	}

	public function getProductAttributes($pid,$type=null) {
		global $woocommerce;
		$productData = wc_get_product($pid);
		if($type=='url'){
            if ($productData->get_parent_id()) {
                return admin_url() . 'post.php?post=' . $productData->get_parent_id() . '&action=edit';
            } else {
                return admin_url() . 'post.php?post=' . $pid . '&action=edit';
            }
        }else{
		    return $productData;
        }
	}

	function getAttributeNameByCode($code) {
		switch ($code) {
		case 5201:
			$attributename = "sku";
			break;
		case 5202:
			$attributename = "regular_price";
			break;
		case 5203:
			$attributename = "price";
			break;
		case 5204:
			$attributename = "stock_quantity";
			break;
		case 5205:
			$attributename = "brand";
			break;
		case 5206:
			$attributename = "category";
			break;
		case 5207:
			$attributename = "default1";
			break;
		case 5208:
			$attributename = "default2";
			break;
		default:
			$attributename = "default1";
			break;
		}
		return $attributename;
	}
}
