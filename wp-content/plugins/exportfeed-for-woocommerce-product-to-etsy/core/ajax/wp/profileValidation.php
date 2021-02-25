<?php
if(!defined(ABSPATH)) die("Access Denied");
if(!is_admin()) die("Unauthorized Access");

require_once ETCPF_PATH.'core/classes/Profiling.php';

Class profileValidation extends Profiling{

    function __construct()
    {
        parent::__construct();
    }

    public function validate($field, $value){
        $value = getsafePostData('profile_name');
        $validation = parent::validate('profile_name', $value);
        if(!$validation['status']){
            wp_send_json_error($validation);
        }
    }

    public function _Initiate()
    {
        $method = array_key_exists('perform', $_POST) ? $_POST['perform'] : null;
        $arguments = array_key_exists('params', $_POST)  ? $_POST['params'] : $_POST;
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

$object = new profileValidation();
$object->_Initiate();
