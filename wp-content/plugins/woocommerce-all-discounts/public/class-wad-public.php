<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.orionorigin.com/
 * @since      0.1
 *
 * @package    Wad
 * @subpackage Wad/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wad
 * @subpackage Wad/public
 * @author     ORION <support@orionorigin.com>
 */
class Wad_Public {

    /**
     * The ID of this plugin.
     *
     * @since    0.1
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.1
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    0.1
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wad_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wad_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wad-public.css', array(), $this->version, 'all');
        wp_enqueue_style("wad-tooltip", WAD_URL . 'public/css/tooltip.min.css', array(), $this->version, 'all');
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    0.1
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wad_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wad_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wad-public.js', array('jquery'), $this->version, false);
        wp_enqueue_script("wad-tooltip", WAD_URL . 'public/js/tooltip.min.js', array('jquery'), $this->version, false);
    }

    function init_globals() {
        global $wad_settings;
        $cart_count = '';
        $wad_settings = get_option("wad-options");
        if(class_exists('WooCommerce')){
            if(is_admin() && !is_ajax())
                return;
            global $wad_discounts;        
            global $wad_products_lists;
            global $mailchimp_user_lists;
            global $sendinblue_user_lists;
            global $wad_free_gifts;
            global $wad_cart_discounts;
            global $wad_user_role;
            global $wad_user_groups;
            global $wad_ignore_product_prices_calculations;
            global $wad_reviewed_products_by_customer;
            global $wad_last_products_fetch;
            global $wad_cart_total_without_taxes;
            global $wad_cart_total_inc_taxes;
            global $wad_free_gifts_total;
            global $wad_active_discounts;
            global $wad_shipping_taxes;
    //        $wad_cart_total_without_taxes=FALSE;
            if(function_exists( 'WC') && WC()->cart){
                // WC()->cart->calculate_totals();
                $cart_count = WC()->cart->get_cart_contents_count();
            }
            if($cart_count>0){
                $wad_cart_total_without_taxes=wad_get_cart_total(false);
                $wad_cart_total_inc_taxes=wad_get_cart_total(true);
            }

            $wad_cart_discounts=0;
            $wad_free_gifts_total=0;
            $wad_reviewed_products_by_customer=false;
            $wad_ignore_product_prices_calculations=false;
            $wad_last_products_fetch=false;
            $wad_free_gifts=array();
            $wad_shipping_taxes=array();
            $wad_user_groups=false;

            $current_user = wp_get_current_user();
            $email = $current_user->user_email;
            $wad_user_role=wad_get_user_role();

            $mailchimp_user_lists  = $this->get_mailchimp_user_lists($email);
            $sendinblue_user_lists = $this->get_sendinblue_user_lists($email);        
            $all_discounts         = wad_get_active_discounts(true);
            $wad_products_lists    = array();
            $wad_active_discounts  = array();


            //We don't want to risk a memory error for variables that are not used admin side
    //        if(is_admin())
    //            return $all_discounts;

            foreach ($all_discounts as $discount_type => $discounts) {
                $wad_discounts[$discount_type] = array();
                foreach ( $discounts as $discount_id ) {
                    $wad_discounts[ $discount_type ][ $discount_id ] = new WAD_Discount( $discount_id );
                }
            }

            define('WAD_INITIALIZED', true);
        }
    }

    function get_mailchimp_user_lists($email) {
        global $wad_settings;
        $list = array();
        
        $api_key_mc = get_proper_value($wad_settings, "mailchimp-api-key", false);
        if (isset($api_key_mc) AND ! empty($api_key_mc)) {
            $MailChimp = new \Drewm\MailChimp($api_key_mc);
            $DATA_MC = array("email" => array("email" => $email));
            $user_lists = $MailChimp->call("/helper/lists-for-email", $DATA_MC);
            if (empty($user_lists)) 
                return $list;
            foreach ($user_lists as $to_list) {
                if (isset($to_list['id']) AND ! empty($to_list['id'])) {
                    $list[] = $to_list['id'];
                }
            }
        }
        return $list;
    }
    
    function get_sendinblue_user_lists($email) {
        global $wad_settings;
        $list = array();
        
        $api_key_sb = get_proper_value($wad_settings, "sendinblue-api-key", false);
        if (isset($api_key_sb) AND ! empty($api_key_sb)) {
            $mailin = new Mailin('https://api.sendinblue.com/v2.0', $api_key_sb);
            $DATA_SB = array("email" => $email);
            $content = $mailin->get_user($DATA_SB);
            if (isset($content['data']['listid']) AND !empty($content['data']['listid'])) {
            $list = $content['data']['listid'];
        }
            
        }
        return $list;
    }

}
