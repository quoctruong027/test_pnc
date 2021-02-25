<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
require_once ETCPF_PATH.'/core/classes/Profiling.php';
Class Profiling_Ajax_Handler extends Profiling{

    function __construct()
    {
        parent::__construct();
    }

    public function deleteattributes($params){
        if($id = getsafePostData('id')){
            $this->db->delete($this->preparationTable, array('id' => $id));
            if(!$this->db->last_error){
                wp_send_json_success(array($params));
                exit();
            }
            wp_send_json_error($params);
            exit();
        }
        wp_send_json_error('Id must be provided');
    }

    public function validateprofile(){
        $value = getsafeajaxPostData('profile_name');
        $validation = parent::validate('profile_name', $value);
        if(!$validation['success']){
            wp_send_json_error($validation);
        }else{
            wp_send_json_success();
        }
    }

    public function invoker(){
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

$OBJECT = New Profiling_Ajax_Handler();
$OBJECT->invoker();
