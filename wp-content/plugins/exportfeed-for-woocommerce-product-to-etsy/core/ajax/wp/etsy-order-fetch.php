<?php

if (!defined('ABSPATH')) exit("Permission Denied");
include_once dirname(__FILE__) . '/../../classes/ETCPF_Etsyorder.php';

Class Etcpf_etsy_orders extends Etsyorder
{
    public $days;
    public $offset;

    function __construct($method)
    {
        parent::__construct();
        add_action('etcpf_custom_main_feed_hook_' . $method, array($this, $method));
        do_action('etcpf_custom_main_feed_hook_' . $method);
    }

    public function fetch_etsy_orders($limit = 25, $offset = 0)
    {
        if ($days = $this->post('days')) {
            $this->days = $days;
            $this->offset = $this->post('offset');
        }
        $transaction = parent::fetch_etsy_orders($this->days, $this->offset);
        wp_send_json_success($transaction);
        exit;
    }

    public function getMappingDetails(){
        global $wpdb;
        $table_etsy_sync = $wpdb->prefix . 'etcpf_etsy_sync';
        $result = $wpdb->get_row("SELECT
            COUNT(CASE WHEN mapped_status = 1 THEN 1 END) AS successful,
            COUNT(CASE WHEN mapped_status = 2 THEN 1 END) AS failed,
            COUNT(CASE WHEN mapped_status = 0 THEN 1 END) AS remaining FROM {$table_etsy_sync}");
        if($result){
            wp_send_json_success($result);
            die();
        }
        wp_send_json_error($result);
        die();
    }

    public function countEtsyProduct(){
        parent::countEtsyProduct();
    }

    public function fetch_etsy_products(){
        $active = $this->post('active');
        $draft = $this->post('draft');
        $inactive = $this->post('inactive');
        $start_value = $this->post('start_value');
        $type = $this->post('type');
        $etsy_product = parent::fetchEtsyProducts($active,$draft,$inactive,$start_value,$type);
        if($etsy_product){
            wp_send_json_success($etsy_product);
        }else{
            wp_send_json_error($etsy_product);
        }
    }
    public function map_products(){
        $map_product = parent::mapEtsyProducts();
        if($map_product){
            wp_send_json_success($map_product);
        }else{
            wp_send_json_error($map_product);
        }
    }

    private function post($index)
    {
        if (isset($_POST[$index])) {
            return $_POST[$index];
        }
        return null;
    }
}

if (isset($_POST['perform'])) {
    $OBJECT = new Etcpf_etsy_orders($_POST['perform']);
} else {
    wp_send_json_error(array("status" => 'error', 'msg' => 'Method Cannot be empty.'));exit;
}
