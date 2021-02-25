<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
class ETCPF_FeedCore
{

    public $banner = true; //In Createfeed page
    public $callSuffix = ''; //So getList() can map into getListJ() and getListW() depending on the cms
    public $form_method = 'GET';
    public $hide_outofstock = false;
    public $isJoomla = false;
    public $isWordPress = false;
    public $siteHost; //eg: www.mysite.com
    public $shopID = 0; //Used by RapidCart Source
    public $feedType; //1 custom feed type 0 Categories feed type
    public $feedLimit = '';
    public $listing = '';
    public $pluginName = 'WooCommerce';

    function __construct()
    {
        /********************************************************************
         * Wordpress init
         ********************************************************************/

        //check what plugin is available, assuming WooCommerce
        $pluginName = 'WooCommerce';
        $all_plugins = get_plugins();
        foreach ($all_plugins as $index => $this_plugin) {
            if (is_plugin_active('wp-e-commerce/wp-shopping-cart.php')) {
                if ($this_plugin['Name'] == 'WP e-Commerce'
                    || $this_plugin['Name'] == 'WP eCommerce'
                ) {
                    $pluginName = 'WP e-Commerce';
                    break;
                }
            }
        }

        switch ($pluginName) {
            case 'WooCommerce':
                global $woocommerce;
                $this->callSuffix = 'W';
                $this->cmsName = 'WordPress';
                $this->cmsPluginName = 'Woocommerce';
                if (function_exists('get_woocommerce_currency')) {
                    $this->currency = get_woocommerce_currency();
                } else {
                    $this->currency = '$';
                } //!Should not be hard-coded
                $this->isWordPress = true;
                $this->siteHost = site_url();
                $this->siteHostAdmin = admin_url();
                $this->weight_unit = esc_attr(
                    get_option('woocommerce_weight_unit')
                );
                $this->dimension_unit = esc_attr(
                    get_option('woocommerce_dimension_unit')
                ); //cm
                $this->manage_stock = strtolower(
                        get_option('woocommerce_manage_stock')
                    ) == 'yes';
                $this->hide_outofstock = strtolower(
                        get_option('woocommerce_hide_out_of_stock_items')
                    ) == 'yes';

                $this->listing = get_option('listing_etsy_et');
                break;
            case 'WP e-Commerce':
                $this->callSuffix = 'We';
                $this->cmsName = 'WordPress';
                $this->cmsPluginName = 'WP e-Commerce';
                $this->currency = '$'; //!Should not be hard-coded
                $this->isWordPress = true;
                $this->siteHost = site_url();
                $this->siteHostAdmin = admin_url();
                $this->weight_unit = 'kg'; //!Should not be hard-coded
                break;
        }
    }

    public function loadRequires($name)
    {

        $name = 'google';
        //Allow external plugins to load a particular Feed object (Intended for WordPress)
        require_once dirname(__FILE__) . '/../classes/dialogbasefeed.php';
        require_once dirname(__FILE__) . '/../feeds/' . $name . '/feed.php';
        require_once dirname(__FILE__) . '/../feeds/' . $name . '/dialognew.php';

    }

    function localizedDate($format, $data)
    {
        $getListCall = 'localizedDate' . $this->callSuffix;

        return $this->$getListCall($format, $data);
    }

    function localizedDateW($format, $data)
    {
        return date_i18n($format, $data);
    }

    function localizedDateWE($format, $data)
    {
        return date_i18n($format, $data);
    }

    function settingDelete($settingName)
    {
        $getListCall = 'settingDelete' . $this->callSuffix;

        return $this->$getListCall($settingName);
    }

    function settingDeleteW($settingName)
    {
        return delete_option($settingName);
    }

    function settingDeleteWe($settingName)
    {
        return delete_option($settingName);
    }

    function settingGet($settingName)
    {
        $getListCall = 'settingGet' . $this->callSuffix;

        return $this->$getListCall($settingName);
    }

    function getVersion()
    {
        return 2;
    }

    function settingGetW($settingName)
    {
        return get_option($settingName);
    }

    function settingGetWe($settingName)
    {
        return get_option($settingName);
    }

    function settingSet($settingName, $value)
    {
        $getListCall = 'settingSet' . $this->callSuffix;
        $this->$getListCall($settingName, $value);
    }

    function settingSetW($settingName, $value)
    {
        update_option($settingName, $value);
    }

    function settingSetWe($settingName, $value)
    {
        update_option($settingName, $value);
    }

    public function trigger($eventname)
    {
        $getListCall = 'trigger' . $this->callSuffix;
        $this->$getListCall($eventname);
    }

    private function triggerW($eventname)
    {
        do_action($eventname);
    }

    private function triggerWE($eventname)
    {
        do_action($eventname);
    }

    public function triggerFilter($eventname, $param1 = null, $param2 = null, $param3 = null)
    {
        $getListCall = 'triggerFilter' . $this->callSuffix;

        return $this->$getListCall($eventname, $param1, $param2, $param3);
    }

    private function triggerFilterW($eventname, $param1, $param2, $param3)
    {
        return apply_filters($eventname, $param1, $param2, $param3);
    }

    private function triggerFilterWE($eventname, $param1, $param2, $param3)
    {
        return apply_filters($eventname, $param1, $param2, $param3);
    }

    function get_gif_loader($class)
    {
        if (!strlen($class) > 0)
            return;
        echo '<img class="' . $class . '" style="display:none" src="' . ETCPF_URL . '/images/loading_balls.gif" height="25" width="30" />';
    }

	public function checkLicense($licenseKey){
		global $wpdb;
		$option_id = $wpdb->get_var($wpdb->prepare( "SELECT option_id from $wpdb->options where option_value = %s", $licenseKey));
		return $option_id;
	}

}

global $etcore;
$etcore = new ETCPF_FeedCore();