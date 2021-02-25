<?php

/**
 * Description of A2WL_DebugPageController
 *
 * @author andrey
 * 
 * @autoload: a2wl_before_admin_menu
 */
if (!class_exists('A2WL_DebugPageController')) {

    class A2WL_DebugPageController extends A2WL_AbstractAdminPage {

        public function __construct() {
            if (a2wl_check_defined('A2WL_DEBUG_PAGE')) {
                parent::__construct(__('Debug', 'ali2woo-lite'), __('Debug', 'ali2woo-lite'), 'edit_plugins', 'a2wl_debug');
            }
        }

        public function render($params = array()) {
            echo "<br/><b>DEBUG</b><br/>";
        }
        
    }
}
