<?php

/**
 * Description of A2WL_RestApi
 *
 * @author Andrey
 * 
 * @autoload: a2wl_init
 */

if (!class_exists('A2WL_RestApi')) {

    class A2WL_RestApi {
        public function __construct() {
            add_action('rest_api_init', array($this, 'register_routes'));
        }
        
        public function register_routes() {
            register_rest_route('a2wl-api/v1', '/info', array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'info'),
                'permission_callback' => '__return_true'
            ));
        }

        public function info($request) {
            $result = array();

            $result['server_ping'] = A2WL_SystemInfo::server_ping();
            $result['plugin_version'] = A2WL()->version;
            
            return rest_ensure_response($result);
        }       
    }
}
