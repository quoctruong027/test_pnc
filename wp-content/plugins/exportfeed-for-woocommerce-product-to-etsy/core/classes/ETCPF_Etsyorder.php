<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
require_once "OAuth.php";
include_once "etsy-upload.php";

Class Etsyorder extends ETCPF_EtsyUpload
{
    private $totalOrders = 0;
    
    private $table;
    
    private $db;
    
    private $dbaction = array();
    
    private $response_order_data = null;
    
    function __construct()
    {
        parent::__construct();
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $this->db->prefix . 'etcpf_orders';
    }
    
    public function index()
    {
        $data = array();
        $this->view('etsy-orders', $data);
    }
    
    public function fetch_etsy_orders($days, $nextoffset)
    {
        $recurring = false;
        $nextpage = 0;
        $ordersDetails = null;
        $failedInsertion = array();
        $page = ($nextoffset+25)/25;
    
        $dir = wp_upload_dir();
        $upload_die = $dir['basedir'];
        $myfile = fopen($upload_die . "/order.log", "w") or die("Unable to open file!");
        
        $latestreceipents = $this->getLatestReceipents($days, $nextoffset, $page);
        $txt = $days ."\n". $page . "\n". json_encode($latestreceipents);
        fwrite($myfile, $txt);
        fclose($myfile);
        if ($data = json_decode($latestreceipents)) {
            
            if(isset($data->pagination->next_page) && $data->pagination->next_page !== null){
                $recurring = true;
                $nextoffset = $data->pagination->next_offset;
                update_option('etsy_order_offset',$nextoffset);
                $nextpage = $data->pagination->next_page;
            }else{
                update_option('etsy_order_offset',0);
            }
            
            foreach ($data->results as $key => $datum) {
                /*if($datum->receipt_id === 1533890306){
                    $orders = wp_remote_retrieve_body($this->getOrders($datum));
                    echo "<pre>";
                    print_r($datum);
                    echo "</pre>";
                    exit;
                }*/
                $orders = wp_remote_retrieve_body($this->getOrders($datum));
                $buyersInfo = array(
                    'name' => $datum->name,
                    'first_line' => $datum->first_line,
                    'second_line' => $datum->second_line,
                    'city' => $datum->city,
                    'state' => $datum->state,
                    'zip' => $datum->zip,
                    'formatted_address' => $datum->formatted_address,
                    'country_id' => $datum->country_id,
                    'payment_method' => $datum->payment_method,
                    'payment_email' => $datum->payment_email,
                    'buyer_email' => $datum->buyer_email,
                    'total_shipping_cost' => $datum->total_shipping_cost,
                    'is_dead' => !empty($datum->is_dead)
                );
                if ($ordersDetails = json_decode($orders)) {
                    $transaction = true;
                    
                    /**
                     * @Info: Ignore this variables for now, may need to use in future implementation.
                     * $total_orders = $ordersDetails->count;
                     * $newoffset = isset($ordersDetails->pagination->next_offset) ? $ordersDetails->pagination->next_offset : 0;
                     * */
                    
                    /**==============================================================================================
                     * if (!empty($newoffset)) {
                     * return array(
                     * 'newoffset' => $newoffset,
                     * 'data' => $orders
                     * );
                     * } else {
                     * return array(
                     * 'newoffset' => 0,
                     * 'data' => $orders
                     * );
                     * }
                     * exit();
                     * ==============================================================================================*/
                    if (isset($ordersDetails->results) && is_array($ordersDetails->results)) {
                        foreach ($ordersDetails->results as $item) {
                            /* $buyersInfo = wp_remote_retrieve_body($this->getBuyersInfo($item->receipt_id)); */
                            
                            if (!$this->save($item, $buyersInfo)) {
                                $transaction = false;
                                $failedInsertion[] = array(
                                    'title' => $item->title,
                                    'transaction_id' => $item->transaction_id,
                                );
                            }
                            $this->totalOrders++;
                            $this->response_order_data['count'] = $this->totalOrders;
                            $this->response_order_data['results'][] = $item;
                        }
                        if ($transaction == false) {
                            return $this->SendResponse(array(
                                'status' => 'failed',
                                'orders' => true,
                                'receipts' => true,
                                'order_data' => array('count' => $ordersDetails->count, 'results' => $ordersDetails->results),
                                'receipts_data' => array('count' => $data->count, 'results' => $data->results),
                                'failed_transaction' => $failedInsertion,
                                'dbactions' => $this->dbaction,
                                'dberror' => isset($this->db->last_error) ? $this->db->last_error : null,
                                'messaage' => 'Database Insertion Failed'
                            ));
                        } /*else {
                            return $this->SendResponse(array(
                                'status' => 'success',
                                'orders' => true,
                                'receipts' => true,
                                'order_data' => array('count' => $ordersDetails->count, 'results' => $ordersDetails->results),
                                'receipts_data' => array('count' => $data->count, 'results' => $data->results),
                                'failed_transaction' => false,
                                'dbactions' => $this->dbaction,
                                'messaage' => 'Everything went okay'
                            ));
                        }*/
                    } else {
                        return $this->SendResponse(
                            array(
                                'status' => 'success',
                                'orders' => false,
                                'receipts' => true,
                                'receipts_data' => array('count' => $data->count, 'results' => $data->results),
                                'messaage' => 'Empty Order Details'
                            ));
                    }
                } else {
                    return $this->SendResponse(
                        array(
                            'status' => 'success',
                            'orders' => false,
                            'receipts' => true,
                            'receipts_data' => array('count' => $data->count, 'results' => $data->results),
                            'messaage' => 'Empty Order Details'
                        ));
                }
            }
            
            /* @Info: Loop is competed and nothing went wrong fetching and inserting data, If not execution would not reach this level. */
            
            return $this->SendResponse(array(
                'status' => 'success',
                'orders' => true,
                'receipts' => true,
                'order_data' => $this->response_order_data,
                'receipts_data' => array('count' => $data->count, 'results' => $data->results),
                'failed_transaction' => false,
                'dbactions' => $this->dbaction,
                'messaage' => 'Everything went okay',
                'recurring' => $recurring,
                'offset' => $nextoffset,
                'next_page' => $nextpage,
                'pagination_result' => $data->pagination
            ));
            
        } else {
            /* @TODO : Perform Handling */
            return $this->SendResponse(
                array(
                    'status' => 'success',
                    'orders' => false,
                    'receipts' => false
                ));
        }
    }
    
    public function countEtsyProduct(){
        global $wpdb;
        $countResult = parent::countEtsyProduct();
        $table_count = $wpdb->prefix . 'etcpf_etsy_product_count';
        $count_data = array(
            'active'=>$countResult['active'],
            'draft'=>$countResult['draft'],
            'inactive'=>$countResult['inactive']
        );
        $check = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_count WHERE id=%d",1)
        );
        if($check){
            $wpdb->update($table_count,$count_data,array('id'=> 1));
        }else{
            $wpdb->insert($table_count,$count_data);
        }
        wp_send_json_success($count_data);
    }
    
    public function fetchEtsyProducts($active,$draft,$inactive,$start_value,$type){
        global $wpdb;
        $limit =100;
        $table_etsy_sync = $wpdb->prefix . 'etcpf_etsy_sync';
        if($type == 'active'){
            if($active > 0){
                $start_value = $wpdb->get_var("SELECT COUNT(*) FROM $table_etsy_sync where state='active'");
                parent::findAllShopListingsActive($start_value,$this->shop_id,$limit);
                // $start_value+=$limit;
                $new_active = $active-100;
                if($new_active > 0){
                    $response = array(
                        'active'=>$new_active,
                        'draft'=>$draft,'inactive'=>$inactive,
                        'type'=> 'active','status'=>false,
                        'message' => 'Fetching remaining '.$new_active.' active products...',
                        'start_value'=>$start_value
                    );
                    wp_send_json_success($response);
                }else{
                    $response = array('active'=>0,
                        'draft'=>$draft,
                        'inactive'=>$inactive,
                        'type'=> 'draft',
                        'status'=>false,
                        'message' => 'Active product fetched. Now fetching draft products...',
                        'start_value'=>0
                    );
                    wp_send_json_success($response);
                }
            }else{
                $response = array(
                    'active'=>0,
                    'draft'=>$draft,
                    'inactive'=>$inactive,
                    'type'=> 'draft',
                    'status'=>false,
                    'message' => 'Fetching draft products...',
                    'start_value'=>0
                );
                wp_send_json_success($response);
            }
        }else if($type == 'draft'){
            if($draft > 0){
                $start_value = $wpdb->get_var("SELECT COUNT(*) FROM $table_etsy_sync where state='draft'");
                parent::findAllShopListingsDraft($start_value,$this->shop_id,$limit);
                // $start_value+=$limit;
                $new_draft = $draft-100;
                if($new_draft > 0){
                    $response = array(
                        'active'=>0,
                        'draft'=>$new_draft,
                        'inactive'=>$inactive,
                        'type'=> 'draft',
                        'status'=>false,
                        'message' => 'Fetching remaining '.$new_draft.' draft products...',
                        'start_value'=>$start_value
                    );
                    wp_send_json_success($response);
                }else{
                    $response = array(
                        'active'=>0,
                        'draft'=>0,
                        'inactive'=>$inactive,
                        'type'=> 'inactive',
                        'status'=>false,
                        'message' => 'Draft product fetched. Now fetching inactive products...',
                        'start_value'=>0
                    );
                    wp_send_json_success($response);
                }
            }else{
                $response = array(
                    'active'=>0,
                    'draft'=>0,
                    'inactive'=>$inactive,
                    'type'=> 'inactive',
                    'status'=>false,
                    'message' => 'Fetching inactive products...',
                    'start_value'=>0
                );
                wp_send_json_success($response);
            }
        }else if($type == 'inactive'){
            if($inactive > 0){
                $start_value = $wpdb->get_var("SELECT COUNT(*) FROM $table_etsy_sync where state='inactive'");
                parent::findAllShopListingsInactive($start_value,$this->shop_id,$limit);
                // $start_value+=$limit;
                $new_inactive = $inactive-100;
                if($new_inactive > 0){
                    $response = array(
                        'active'=>0,
                        'draft'=>0,
                        'inactive'=>$new_inactive,
                        'type'=> 'inactive',
                        'status'=>false,
                        'message' => 'Fetching remaining'.$new_inactive.'inactive products...',
                        'start_value'=>$start_value
                    );
                    wp_send_json_success($response);
                }else{
                    $response = array(
                        'active'=>0,
                        'draft'=>0,
                        'inactive'=>0,
                        'type'=> 0,
                        'status'=>true,
                        'message' => 'Inactive product fetched.',
                        'start_value'=>0
                    );
                    wp_send_json_success($response);
                }
            }else{
                $response = array(
                    'active'=>0,
                    'draft'=>0,
                    'inactive'=>0,
                    'type'=> 0,
                    'status'=>true,
                    'message' => 'Completed successfully.',
                    'start_value'=>0
                );
                wp_send_json_success($response);
            }
        }else{
            $response = array(
                'active'=>0,
                'draft'=>0,
                'inactive'=>0,
                'type'=> 0,
                'status'=>true,
                'message' => 'Mapping your products. Please wait...',
                'start_value'=>0
            );
            wp_send_json_success($response);
        }
    }
    
    public function mapEtsyProducts(){
        $map = parent::mapEtsyProducts();
        return $map;
    }
    
    public function getLatestReceipents($days, $offset, $page)
    {
        if(intval($days)<=0)
            $days_ago = get_option('etcpf_last_sync_date');
        else
            $days_ago = date('Y-m-d h:i:s', strtotime('-' . $days . ' days'));
        
        update_option('etcpf_last_sync_date', $days_ago);
        
        $dt = new DateTime(date($days_ago));
        $unix_timestamp = $dt->getTimestamp();
        $shop_id = $this->shop_id;
        $url = "https://openapi.etsy.com/v2/shops/" . $shop_id . "/receipts";
        $params = array(
            'scopes' => 'transactions_r', // not necessary for now
            /* 'was_paid' => 1,
             'was_shipped' => 1,*/
            'min_last_modified' => $unix_timestamp,
            'limit' => 25,
            'offset' => isset($offset) ? $offset : 0,
            'page' => $page
        );
        $acc_req = $this->prepareHash($url, 'GET', false, $params);
        $response = wp_remote_get($acc_req,
            array(
                'timeout' => 120,
                'redirection' => 5,
                'blocking' => true,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Expect' => '',
                )
            )
        );
        
        if (wp_remote_retrieve_response_code($response) == 200) {
            return wp_remote_retrieve_body($response);
        }
        return wp_remote_retrieve_body($response);
    }
    
    public function save($item, $buyersInfo)
    {
        $data = array(
            'title' => $item->title,
            'transaction_id' => $item->transaction_id,
            'seller_user_id' => $item->seller_user_id,
            'buyer_user_id' => $item->buyer_user_id,
            'creation_tsz' => date('y-m-d h:i:s', intval($item->creation_tsz)),
            'paid_tsz' => date('y-m-d h:i:s', intval($item->paid_tsz)),
            'shipped_tsz' => date('y-m-d h:i:s', intval($item->shipped_tsz)),
            'price' => $item->price,
            'currency_code' => $item->currency_code,
            'quantity' => $item->quantity,
            'tags' => json_encode($item->tags),
            'materials' => json_encode($item->materials),
            'image_listing_id' => $item->image_listing_id,
            'receipt_id' => $item->receipt_id,
            'shipping_cost' => $buyersInfo['total_shipping_cost'],
            'listing_id' => $item->listing_id,
            'transaction_type' => $item->transaction_type,
            'url' => $item->url,
            'variations' => json_encode($item->variations),
            'product_data' => json_encode($item->product_data),
            'product_id' => $item->product_data->product_id,
            'parent_sku' => $item->product_data->sku,
            'offerings' => json_encode($item->product_data->offerings),
            'buyers_info' => json_encode($buyersInfo),
            'state' => isset($item->shipped_tsz) ? 'shipped' : (boolval($buyersInfo['is_dead'])===true ? 'cancelled' : 'pending' )
        );
     
        if ($item->transaction_id) {
            $check = $this->checkOrdersExistence($item->transaction_id);
            if (empty($check)) {
                $privateSku = (strlen($item->product_data->sku) > 0) ? $item->product_data->sku : false;
                if(!$privateSku)
                    return false;
                if ($this->db->insert($this->table, $data)) {
                    $this->stockManagement($privateSku, $item->quantity);
                    /*if (!empty($item->shipped_tsz) || !empty($item->paid_tsz)) {
                        $msc = $this->stockManagement($privateSku, $item->quantity);
                        if ($msc == true || $msc == 'manage_stock_no') {
                            $this->updateVariables($item->transaction_id, array('state' => 'shipped'));
                        } // Exception is handled there itself @stockManagement
                    }*/
                    $this->dbaction[$item->transaction_id] = 'inserted';
                    return true;
                }
                return false;
                
            } else {
                
                try{
                    if ( ($this->getDesiredVariables('state', $item->transaction_id) == 'pending') ) {
                        $this->db->update($this->table, $data, array('transaction_id' => $item->transaction_id));
                        $this->dbaction[$item->transaction_id] = 'updated';
                        if ($buyersInfo['is_dead']) {
                            if(!$item->product_data->sku){
                                $this->dbaction[$item->transaction_id]['error'] = $item->product_data->sku .' is not available in you woo product';
                                return false;
                            }
                            $this->stockManagement($item->product_data->sku, -$item->quantity);
                        }
                        return true;
                    }else{
                        $this->db->update($this->table, $data, array('transaction_id' => $item->transaction_id));
                        $this->dbaction[$item->transaction_id] = 'updated';
                        return  true;
                    }
                }catch (Exception $e){
                    echo "<pre>";
                    print_r($e->getMessage());
                    echo "</pre>";
                    exit;
                }
            }
        }
        return false;
        
    }
    
    
    /**
     * @Param: transaction Id from etsy orders
     * @return: either id of given transaction or false
     */
    public function checkOrdersExistence($trans_id)
    {
        $qry = $this->db->get_var($this->db->prepare("SELECT id FROM {$this->table} WHERE transaction_id=%d", array($trans_id)));
        if ($qry) {
            return $qry; /* id of order with supplied transaction id*/
        }
        return false;
    }
    
    private function getOrders($receiptdata)
    {
        $shop_id = $this->shop_id;
        //$url = "https://openapi.etsy.com/v2/shops/{$shop_id}/transactions?limit={$limit}&offset={$offset}";
        $url = "https://openapi.etsy.com/v2/receipts/{$receiptdata->receipt_id}/transactions";
        $acc_req = $this->prepareHash($url, 'GET', false);
        $response = wp_remote_get($acc_req,
            array(
                'timeout' => 120,
                'redirection' => 5,
                'blocking' => true,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Expect' => '',
                )
            )
        );
        return $response;
    }
    
    private function sendResponse(array $data)
    {
        return $data;
    }
    
    public function stockManagement($sku, $quantity)
    {
        //$sku = 'EXPORTFEED_123';
        if (function_exists('wc_get_product_id_by_sku')) {
            $productID = wc_get_product_id_by_sku($sku);
            if(!$productID && $sku){
                $productID = wc_get_product_id_by_sku($sku);
            }
        } else {
            $productID = $this->db->get_var($this->db->prepare("SELECT post_id FROM $this->db->prefix.'posts' WHERE meta_value = %s", array($sku)));
            if(!$productID && $sku){
                $productID = $this->db->get_var($this->db->prepare("SELECT post_id FROM $this->db->prefix.'posts' WHERE meta_value = %s", array($sku)));
            }
        }
        $is_stock_managed = get_post_meta($productID, '_manage_stock');
        if ($is_stock_managed[0] == 'yes') {
            $stock = get_post_meta($productID, '_stock');
            $qty = $stock[0] - $quantity;
            if (!update_post_meta($productID, '_stock', $qty)) {
                error_log("Etsy: Stock management failed for product with id-->{$productID} for quantity-->{$quantity}");
                return false;
            }
            return true;
        }
        return 'manage_stock_no';
    }
    
    private function getDesiredVariables($var, $transactionID)
    {
        $qry = $this->db->get_var($this->db->prepare("SELECT $var FROM {$this->table} WHERE transaction_id=%d", array($transactionID)));
        if ($qry) {
            return $qry; /* $var of order with supplied transaction id*/
        }
        return false;
    }
    
    private function updateVariables($transactionID, $data)
    {
        $this->db->update($this->table, $data, array('transaction_id' => $transactionID));
        if (empty($this->db->last_error)) {
            return true;
        }
        return $this->db->last_error;
    }
    
    public function view($viewfile, $data)
    {
        $dir = dirname(__FILE__);
        $viewDir = $dir . '/../etsy-views/order-views/' . $viewfile . '.php';
        if (file_exists($viewDir)) {
            $realFile = realpath($viewDir);
            include_once $realFile;
        } else {
            echo "<pre>";
            print_r($viewfile . ' could not be found.');
            echo "</pre>";
            exit();
        }
    }
}
