<?php
/**
 * Created by PhpStorm.
 * User: suzan
 * Date: 4/2/19
 * Time: 12:08 PM
 */

if (!defined('ABSPATH')) {
    exit;
}

include_once "etsy-upload.php";
require_once 'product_uploadhook.php';
Class Etsyupdater extends ETCPF_EtsyUpload
{
    private $db;

    private $table;

    private $feed_table;

    public function __construct()
    {
        parent::__construct();
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $this->db->prefix . 'etcpf_listings';
        $this->feed_table = $this->db->prefix . 'etcpf_feeds';

    }

    public function uploadtoetsy()
    {
        /*$feed_id = $this->db->get_var("SELECT id FROM $this->feed_table ORDER BY updated_at desc limit 1");
        update_option('current_on_update_feed_etsy',$feed_id);
        $onprocessingFeed = get_option('current_on_update_feed_etsy');
        if()*/
        $datasets = $this->db->get_results($this->db->prepare("SELECT item_id, listing_id FROM $this->table WHERE uploaded=%d OR uploaded=%d ORDER BY uploaded_at ASC LIMIT 7", array(0, 7)));
        if ($datasets) {
            foreach ($datasets as $key => $dataset) {
                $ids[] = $dataset->item_id;
                if ($dataset->listing_id == NULL) continue;
                $invoker = new Product_uploadhoook($dataset->item_id);
                $invoker->Invoker();
            }
            
            //$this->db->query("UPDATE $this->table set uploaded=1 WHERE item_id in (implode(','$$ids))");
        }

    }
}
