<?php
if (!defined('ABSPATH')) exit("Permission Denied");

if(defined('ENV') AND ENV==true){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require_once dirname(__DIR__).'/../classes/ETCPF_settings.php';

/* Initiating */

Class Etcpf_setting extends ETCPF_settings
{
    public $setter = array();

    private $table = 'etcpf_settings';

    public $db;

    protected $fillable = ['stock_managed', 'default_stock_quantity', 'who_made_it', 'is_supply', 'when_made', 'state', 'title_sync', 'description_sync', 'tags_sync'];

    function __construct($method)
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $this->db->prefix . $this->table;
        add_action('etcpf_settings_hook_' . $method, array($this, $method));
        do_action('etcpf_settings_hook_' . $method);
    }

    public function update_etsy_settings()
    {
        $timestamp = wp_next_scheduled('auto_etsy_order_hook');
        wp_unschedule_event( $timestamp, 'auto_etsy_order_hook' );

        $timestamp = wp_next_scheduled('auto_feed_submission_hook');
        wp_unschedule_event($timestamp, 'auto_feed_submission_hook');

        $timestamp = wp_next_scheduled('update_etsyfeeds_hook');
        wp_unschedule_event($timestamp, 'update_etsyfeeds_hook');

        try {
            foreach ($_POST['formData'] as $key => $item) {
                $this->set_values($item);
                $this->save($item);
            }
        } catch (ErrorException $e) {
            echo json_encode(array('status' => 'failed', 'error' => $e));
        }

        echo json_encode(array('status' => 'ok', 'success' => true));

    }

    protected function set_values($item)
    {
        $this->setter[$item['name']] = $item['value'];
    }

    protected function getter($key)
    {
        if ($key) {
            return $this->setter[$key];
        }
        return $this->setter;
    }

    public function save($item)
    {
        $mKey = $item['name'];
        $data = array(
            '_settings_mkey' => $mKey,
            '_settings_mvalue' => $item['value']
        );
        if ($check = $this->db->get_row($this->db->prepare("SELECT * FROM $this->table WHERE _settings_mkey=%s", array($mKey)))) {
            $this->db->update($this->table, $data, array('id' => $check->id));
        } else {
            $this->db->insert($this->table, $data);
        }
        return true;
    }


    private function post($index)
    {
        if (isset($_POST[$index])) {
            return sanitize_post($_POST);
        }
        return null;
    }
}

if (isset($_POST['perform'])) {
    $OBJECT = new Etcpf_setting($_POST['perform']);
} else {
    wp_send_json_error(array("status" => 'error', 'msg' => 'Method Cannot be empty.'));
}
