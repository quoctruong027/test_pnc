<?php
/**
 * Description of A2WL_HelpController
 *
 * @author Andrey
 * 
 * @autoload: a2wl_init
 */
if (!class_exists('A2WL_HelpController')) {

    class A2WL_HelpController {

        public $tab_class = '';
        public $tab_id = '';
        public $tab_title = '';
        public $tab_icon = '';

        public function __construct() {
            if(is_admin()){
                add_action('a2wl_init_admin_menu', array($this, 'add_submenu_page'), 200);  
            }
        }

        public function add_submenu_page($parent_slug) {
            $page_id = add_submenu_page($parent_slug, '', 'Help', 'manage_options', 'https://ali2woo.com/codex/');            
        }
    }

}    
