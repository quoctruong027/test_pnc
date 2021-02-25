<?php

/**
 * Description of A2WL_PluginUpdateController
 *
 * @author Andrey
 * 
 * @autoload: a2wl_init
 */
if (!class_exists('A2WL_PluginUpdateController')) {

    class A2WL_PluginUpdateController extends A2WL_AbstractController {

        private $update;

        public function __construct() {

            $this->update = new A2WL_Update(A2WL()->version, a2wl_get_setting('api_endpoint').'update.php', A2WL()->plugin_name, '19821022', a2wl_get_setting('item_purchase_code'));

            //add_action('in_plugin_update_message-ali2woo-lite/ali2woo-lite.php', array($this, 'plugin_update_message'), 10, 3);
        }
        
        public function plugin_update_message($plugin_file, $plugin_data='', $status=''){
            echo ' <em><a href="'.admin_url( 'admin.php?page=a2wl_setting').'">Register</a> your copy of plugin to receive access to automatic upgrades and support.</em>';
        }

    }

}
