<?php

/**
 * Description of A2WL_RequestHelper
 *
 * @author Andrey
 */
if (!class_exists('A2WL_RequestHelper')) {

    class A2WL_RequestHelper {
        public static function build_request($function, $params=array()){
            $request_url = a2wl_get_setting('api_endpoint').$function.'.php?' . A2WL_Account::getInstance()->build_params() . A2WL_AliexpressLocalizator::getInstance()->build_params(isset($params['lang']))."&su=".  urlencode(site_url());
            if(!empty($params) && is_array($params)){
                foreach($params as $key=>$val){
                    $request_url .= "&".str_replace("%7E", "~", rawurlencode($key))."=".str_replace("%7E", "~", rawurlencode($val));
                }    
            }
            return $request_url;
        }
    }
}
