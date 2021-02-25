<?php
/**
 * Description of A2WL_Settings
 *
 * @author andrey
 */

if (!class_exists('A2WL_Settings')) {

    class A2WL_Settings {
        private $settings;
        private $auto_commit = true;

        private $static_settings = array(
            'api_endpoint'=>'https://api.ali2woo.com/v1/',
            'image_editor_srickers'=> array(
                '/assets/img/stickers/stick-001.png',
                '/assets/img/stickers/stick-002.png',
                '/assets/img/stickers/stick-003.png',
                '/assets/img/stickers/stick-004.png',
                '/assets/img/stickers/stick-005.png',
                '/assets/img/stickers/stick-006.png',
                '/assets/img/stickers/stick-007.png',
                '/assets/img/stickers/stick-008.png',
                '/assets/img/stickers/stick-009.png',
                '/assets/img/stickers/stick-010.png',
                '/assets/img/stickers/stick-011.png',
                '/assets/img/stickers/stick-012.png',
                '/assets/img/stickers/stick-013.png',
                '/assets/img/stickers/stick-014.png',
                '/assets/img/stickers/stick-015.png',
                '/assets/img/stickers/stick-016.png',
                '/assets/img/stickers/stick-017.png',
                '/assets/img/stickers/stick-018.png',
                '/assets/img/stickers/stick-019.png',
                '/assets/img/stickers/stick-020.png',
                '/assets/img/stickers/stick-021.png',
                '/assets/img/stickers/stick-022.png')
        );
        
        private $default_settings = array(
            'item_purchase_code'=>'',
            'account_type'=> 'aliexpress',
            'use_custom_account'=>false,
            'account_data'=> array('aliexpress'=>array('appkey'=>'', 'trackingid'=>''), 'admitad'=>array('cashback_url'=>'')),
            
            'import_language'=> 'en',
            'local_currency'=> 'USD',
            'default_product_type'=> 'simple',
            'default_product_status'=> 'publish',
            'not_import_attributes'=> false,
            'not_import_description'=> false,
            'not_import_description_images'=> false,
            'import_extended_attribute'=> false,
            'import_product_images_limit'=> 0,
            'use_external_image_urls'=> true,
            'use_cdn'=> false,
            'use_random_stock'=> false,
            'use_random_stock_min'=> 5,
            'use_random_stock_max'=> 15,
            'split_attribute_values'=> true,
            'attribute_values_separator'=> ',',
            'currency_conversion_factor'=> 1,
            'background_import'=> true,

            'auto_update'=> false,
            'on_not_available_product'=> 'trash', // nothing, trash, zero
            'on_not_available_variation'=> 'trash',// nothing, trash, zero
            'on_new_variation_appearance'=> 'add',// nothing, add
            'on_price_changes'=> 'update', // nothing, update
            'on_stock_changes'=> 'update', // nothing, update
            'email_alerts'=> false,
            'email_alerts_email'=> '',

            'fulfillment_prefship'=> 'EMS_ZX_ZX_US',
            'fulfillment_phone_code'=> '',
            'fulfillment_phone_number'=> '',
            'fulfillment_custom_note'=> '',
            'order_translitirate' => false,
            'order_third_name' => false,
            'order_autopay' => false,
            'order_awaiting_payment' => true,
            'pricing_rules_type'=> 'sale_price_as_base',
            'use_extended_price_markup'=> false,
            'use_compared_price_markup'=> false,
            'price_cents'=> -1,
            'price_compared_cents'=> -1,
            'default_formula'=> false,
            'formula_list'=> array(),
            
            'phrase_list'=> array(),
            
            'load_review'=> false,
            'review_status'=> false,
            'review_translated'=> false,
            'review_avatar_import'=> false,
            'review_max_per_product'=> 20,
            'review_raiting_from'=> 1,
            'review_raiting_to'=> 5,
            'review_noavatar_photo'=>null,
            'review_load_attributes'=> false,
            'review_show_image_list'=> false,
            'moderation_reviews'=> false,
            'review_allow_country'=> '',
            
            'aliship_frontend'=> false,
            'aliship_shipto'=> 'US',
            'default_shipping_class'=> false,
            
            
            'json_api_base'=> 'a2wl_api',
            'json_api_controllers'=> 'core,auth',
            
            'system_message_last_update'=> 0,
            
            'api_keys'=> array(),

            'chrome_ext_import'=> false,

            'write_info_log'=> false,
        );

        private static $_instance = null;

        protected function __construct() {
            $this->load();
        }

        protected function __clone() {
            
        }

        static public function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }
        
        public function auto_commit($auto_commit = true){
            $this->auto_commit = $auto_commit;
        }
        
        public function load(){
            $static_settings = $this->static_settings;

            if(a2wl_check_defined("A2WL_API_ENDPOINT")){ 
                $static_settings['api_endpoint'] = A2WL_API_ENDPOINT;
            }

            if(a2wl_check_defined("A2WL_DO_NOT_USE_HTTPS")){
                $static_settings['api_endpoint'] = str_replace("https", "http", $static_settings['api_endpoint']);
            }
            $this->settings = array_merge(
                $this->default_settings, get_option('a2wl_settings', array()), $static_settings);
        }
        
        public function commit(){
            update_option('a2wl_settings', $this->settings);
        }
        
        public function to_string() { }
        
        public function from_string($str) { }


        public function get($setting, $default=''){
            return isset($this->settings[$setting])?$this->settings[$setting]:$default;
        }
        
        public function set($setting, $value){
            $old_value = isset($this->settings[$setting])?$this->settings[$setting]:'';
            
            do_action('a2wl_pre_set_setting_'.$setting, $old_value, $value, $setting);
            
            $this->settings[$setting] = $value;
            
            if($this->auto_commit){
                $this->commit();
            }
            
            do_action('a2wl_set_setting_'.$setting, $old_value, $value, $setting);
        }
        
        public function del($setting){
            if(isset($this->settings[$setting])){
                unset($this->settings[$setting]);
                
                if($this->auto_commit){
                    $this->commit();
                }
            }
        }
    }
}

if (!function_exists('a2wl_settings')) {
    function a2wl_settings() {
        return A2WL_Settings::instance();
    }
}

if (!function_exists('a2wl_get_setting')) {
    function a2wl_get_setting($setting, $default='') {
        return a2wl_settings()->get($setting, $default);
    }
}

if (!function_exists('a2wl_set_setting')) {
    function a2wl_set_setting($setting, $value) {
        if (a2wl_check_defined('A2WL_DEMO_MODE') && in_array($setting, array('use_external_image_urls'))) {
            return;
        }
        
        return a2wl_settings()->set($setting, $value);
    }
}

if (!function_exists('a2wl_del_setting')) {
    function a2wl_del_setting($setting) {
        return a2wl_settings()->del($setting);
    }
}
