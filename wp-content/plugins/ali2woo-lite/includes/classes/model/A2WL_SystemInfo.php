<?php

/**
 * Description of A2WL_SystemInfo
 *
 * @author Andrey
 * 
 * @autoload: a2wl_init
 */

if (!class_exists('A2WL_SystemInfo')) {
    class A2WL_SystemInfo {

        public function __construct() {
            add_action('wp_ajax_a2wl_ping', array($this, 'ajax_ping'));
            add_action('wp_ajax_nopriv_a2wl_ping', array($this, 'ajax_ping'));

            add_action('wp_ajax_a2wl_clear_log_file', array($this, 'ajax_clear_log_file'));
        }

        public function ajax_clear_log_file() {
            A2WL_Logs::getInstance()->delete();
            echo json_encode(array('state'=>'ok'));
            wp_die();
        }

        public function ajax_ping() {
            echo json_encode(array('state'=>'ok'));
            wp_die();
        }

        public static function ping(){
            $result = array();
            $request = wp_remote_post( admin_url('admin-ajax.php')."?action=a2wl_ping");
            if (is_wp_error($request)) {
                $result = A2WL_ResultBuilder::buildError($request->get_error_message());    
            } else if (intval($request['response']['code']) != 200) {
                $result = A2WL_ResultBuilder::buildError($request['response']['code'] . " " . $request['response']['message']);
            } else {
                $result = json_decode($request['body'], true);
            }
            return $result;
        }
       
        
        public static function server_ping(){
            $result = array();
            $ping_url = A2WL_RequestHelper::build_request('ping', array('r' => mt_rand()));
            $request = a2wl_remote_get($ping_url);
            if (is_wp_error($request)) {
                if(file_get_contents($ping_url)){
                    $result = A2WL_ResultBuilder::buildError('a2wl_remote_get error');
                }else{
                    $result = A2WL_ResultBuilder::buildError($request->get_error_message());    
                }
            } else if (intval($request['response']['code']) != 200) {
                $result = A2WL_ResultBuilder::buildError($request['response']['code']." ".$request['response']['message']);
            } else {
                $result = json_decode($request['body'], true);
            }

            return $result;
        }
        
        public static function php_check(){
            return A2WL_ResultBuilder::buildOk();
        }

        public static function php_dom_check(){
            if (class_exists('DOMDocument')) {
                return A2WL_ResultBuilder::buildOk();
            } else{
                return A2WL_ResultBuilder::buildError('PHP DOM is disabled');
            }
        }
    }

}

