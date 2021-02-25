<?php
if (!defined('ABSPATH')) {
    exit;
}
if (defined('ENV') && ENV == 'development') {
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

Class SingleVariationPreparator
{
    protected $variationupload_preparation;
    private $db;
    private $table;
    public $attributes;
    private $feedTable;
    private $preparationTable;

    public function __construct()
    {
        //parent::__construct();
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $this->db->prefix . 'etcpf_variationupload_preparation';
        $this->feedTable = $this->db->prefix . 'etcpf_feeds';
        $this->preparationTable = $this->db->prefix . 'etcpf_variationupload_preparation';
        $this->profileTable = $this->db->prefix . 'etcpf_profiles';
    }

    public function set()
    {
        parse_str($_POST['data'], $this->attributes);
        exit;
    }

    public function save()
    {

    }

    public function updateProfile()
    {
        $data = array();
        parse_str($_POST['data'], $data);
        $tobeUpdated = array(
            'variation_upload_profile' => $data['variation_upload_profile'],
            'variation_upload_type' => $data['choose']
        );
        $this->db->update($this->feedTable, $tobeUpdated, array('id' => $data['feed_id']));
        $profileData = $this->db->get_row("SELECT * FROM $this->profileTable WHERE id={$data['variation_upload_profile']}");
        $variationData = $this->db->get_results("SELECT * FROM $this->preparationTable WHERE profile_id={$data['variation_upload_profile']}", ARRAY_A);
        $resulthtml = '';
        $variations = '';
        foreach ($variationData as $key => $variationDatum) {
            $resulthtml .= $variationDatum['prefix'] . $variationDatum['variation_attribute'] . $variationDatum['suffix'];
            $variations .= $variationDatum['variation_attribute'];
            if (count($variationData) - 1 > $key) {
                $resulthtml .= $profileData->attribute_seperator;
                $variations .= $profileData->attribute_seperator;
            }
        }

        wp_send_json(array('success' => true,
            'data' => array('variation_data' => $variations,
                'variation_result' => $resulthtml,
                'variation_type' => $data['choose'],
                'id' => $data['variation_upload_profile'])
        ));
        exit();
    }

    public function _Initiate()
    {
        $method = array_key_exists('perform', $_POST) ? $_POST['perform'] : null;
        $arguments = array_key_exists('params', $_POST) ? $_POST['params'] : $_POST;
        if (!is_array($arguments)) {
            $arguments = array($arguments);
        }
        if (is_null($method)) {
            echo json_encode(array('success' => false, 'msg' => "Methods was null"));
        } elseif (!method_exists($this, $method)) {
            echo json_encode(array('success' => false, 'msg' => "Methods {$method} does not exists."));
        } else {
            call_user_func_array(array($this, $method), array($arguments));
        }
    }

}

$OBJECT = New SingleVariationPreparator();
$OBJECT->_Initiate();
