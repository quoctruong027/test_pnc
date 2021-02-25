<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

Class ResolveFeedProducts
{

    function __construct()
    {

    }

    public function saveresolveddata()
    {
        global $wpdb;
        $table = $wpdb->prefix.'etcpf_resolved_product_data';
        $tablefeedproducts = $wpdb->prefix.'etcpf_feedproducts';
        $product_ids = $_POST['product_ids'];
        $productIDarray = join("','",$product_ids);
        $feed_id = $_POST['feed_id'];
        $attribute = $_POST['attribute_code'];
        $value = $_POST['attribute_value'];
        $html = '';
        foreach ($product_ids as $key => $product_id) {
            $data = array(
                'feed_id' => $feed_id,
                'product_id' => $product_id,
                'attribute' => $attribute,
                'value' => $value,
                'error_code' => $attribute
            );
            $check = $wpdb->get_row( "SELECT * FROM {$table} WHERE product_id={$product_id} AND error_code={$attribute}", OBJECT );
            if(is_object($check) && !empty($check->id)){
                $wpdb->update($table,$data,array('id'=>$check->id));
            }else{
                $wpdb->insert($table, $data);
            }
            $where = array('p_id'=>$product_id,'error_code'=>$attribute);
            $updatedata = array('error_status'=>2,'message'=>'Resolved Product');
            if($wpdb->update($tablefeedproducts,$updatedata,$where)){
                $trans = true;
            }else{
                $trans = false;
            }
        }
        if($trans==true){
            $allProductResolved = $this->checkIfAllProductResolved($feed_id);
            $remainingErrors = $this->getproductbyErrorCode($feed_id,$attribute);
            $resolvedData = $wpdb->get_results("SELECT * FROM {$tablefeedproducts} WHERE p_id IN ('$productIDarray') GROUP BY p_id" );
            $returndata=array('status'=>'success','result'=>true,'html'=>$resolvedData,'remaining_errors'=>$remainingErrors);
            echo json_encode($returndata);
            exit;
        }
        $returndata=array('status'=>'success','result'=>false);
        echo json_encode($returndata);
        exit;
    }

    public function getproductbyErrorCode($feed_id, $errorcode) {
        global $wpdb;
        $table = $wpdb->prefix . "etcpf_feedproducts";
        $productdata = $wpdb->get_results("SELECT * FROM {$table} WHERE FIND_IN_SET($feed_id,`feed_id`) AND error_code={$errorcode} AND (error_status<>'2' AND error_status != '1')");
        return $productdata;
    }

    public function checkIfAllProductResolved($feed_id)
    {
        global $wpdb;
        $table = $wpdb->prefix.'etcpf_feedproducts';
        $qry = $wpdb->prepare("SELECT id FROM $table WHERE FIND_IN_SET(%d,`feed_id`) AND (error_status=%s OR error_status=%s)", array($feed_id, '-1', '0'));
        $result = $wpdb->get_results($qry);
        if (is_array($result) && count($result) > 0) {
            return false;
        }
        update_option('ETCPF_RESOLVED','yes');
        return true;
    }
}

$cObject = new ResolveFeedProducts();
$cObject->saveresolveddata();