<?php
/**
 * Description of A2WL_ServiceController
 *
 * @author Andrey
 * 
 * @autoload: a2wl_init
 */

if (!class_exists('A2WL_ServiceController')) {

    class A2WL_ServiceController {

        private $system_message_update_period = 3600;

        public function __construct() {
            $system_message_last_update = intval(a2wl_get_setting('plugin_data_last_update'));
            if (!$system_message_last_update || $system_message_last_update < time()) {
                a2wl_set_setting('plugin_data_last_update', time() + $this->system_message_update_period);
                $request_url = A2WL_RequestHelper::build_request('sync_plugin_data');
                $request = a2wl_remote_get($request_url);
                if (!is_wp_error($request) && intval($request['response']['code']) == 200) {
                    $plugin_data = json_decode($request['body'], true);
                    $categories = isset($plugin_data['categories']) && is_array($plugin_data['categories'])?$plugin_data['categories']:array();
                    a2wl_set_setting('system_message', $plugin_data['message']);
                    update_option('a2wl_all_categories', $categories);
                }
            }
        }
    }
}
