<?php
/*
  Plugin Name: Aliexpress Dropship for Woocommerce (Lite version)
  Plugin URI: https://codecanyon.net/user/ma-group/portfolio
  Description: Aliexpress Dropship for Woocommerce (Lite version) is a WordPress plugin created for AliExpress Drop Shipping and Affiliate marketing
  Text Domain: ali2woo-lite
  Domain Path: /languages
  Version: 2.0.1
  Author: MA-Group
  Author URI: https://codecanyon.net/user/ma-group
  License: GPLv2+
  Tested up to: 5.6
  WC tested up to: 4.9
  WC requires at least: 2.6
 */

if (!defined('A2WL_PLUGIN_FILE')) {
    define('A2WL_PLUGIN_FILE', __FILE__);
}

if (!class_exists('A2WL_Main')) {

    class A2WL_Main {

        /**
         * @var The single instance of the class		 
         */
        protected static $_instance = null;

        /**
         * @var string Ali2Woo Lite plugin version
         */
        public $version;

        /**
         * @var string Ali2Woo Lite plugin version
         */
        public $plugin_name;

        /**
         * @var string chrome extension url
         */
        public $chrome_url = 'https://chrome.google.com/webstore/detail/faieahckjkcpljkaedbjidlhhcigddal';

        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        private function __construct() {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $plugin_data = get_plugin_data(A2WL_PLUGIN_FILE);

            $this->version = $plugin_data['Version'];
            $this->plugin_name = plugin_basename(A2WL_PLUGIN_FILE);

            require_once($this->plugin_path() . "/includes/libs/taobao-sdk/TopSdk.php");

            require_once($this->plugin_path() . '/includes/libs/wp-background-processing/wp-background-processing.php');

            include_once($this->plugin_path() . '/includes/settings.php');
            include_once($this->plugin_path() . '/includes/functions.php');

            include_once($this->plugin_path() . '/includes/init.php');
            A2WL_Init::init_classes($this->plugin_path() . '/includes/classes', 'a2wl_init');
            A2WL_Init::init_addons($this->plugin_path() . '/addons');


            include_once($this->plugin_path() . "/includes/libs/a2wl_json_api/a2wl_json_api.php");
            A2WL_Json_Api_Configurator::init('a2wl_dashboard');

            A2WL_ImportProcess::instance();

            if (!class_exists('Requests')) {
                include_once ($this->plugin_path() . '/includes/libs/Requests/Requests.php');
                Requests::register_autoloader();
            }

            register_activation_hook(A2WL_PLUGIN_FILE, array($this, 'install'));
            register_deactivation_hook(A2WL_PLUGIN_FILE, array($this, 'uninstall'));

            add_action('admin_menu', array($this, 'admin_menu'));

            add_action('admin_enqueue_scripts', array($this, 'admin_assets'));

            add_action('wp_enqueue_scripts', array($this, 'assets'));
        }

        /**
         * Path to Ali2Woo Lite plugin root url
         */
        public function plugin_url() {
            return untrailingslashit(plugins_url('/', A2WL_PLUGIN_FILE));
        }

        /**
         * Path to Ali2Woo Lite plugin root dir
         */
        public function plugin_path() {
            return untrailingslashit(plugin_dir_path(A2WL_PLUGIN_FILE));
        }

        function install() {
            do_action('a2wl_install');
        }

        function uninstall() {
            do_action('a2wl_uninstall');
        }

        public function assets($page) {
            do_action('a2wl_assets', $page);
        }

        public function admin_assets($page) {
            do_action('a2wl_admin_assets', $page);
        }

        public function admin_menu() {
            do_action('a2wl_before_admin_menu');

            add_menu_page(__('Ali2Woo Lite', 'ali2woo-lite'), __('Ali2Woo Lite', 'ali2woo-lite'), 'import', 'a2wl_dashboard', '', plugins_url('assets/img/icon.png', A2WL_PLUGIN_FILE));

            do_action('a2wl_init_admin_menu', 'a2wl_dashboard');
        }

    }

}

/**
 * Returns the main instance of A2WL_Main to prevent the need to use globals.
 *
 * @return A2WL_Main
 */
if (!function_exists('A2WL')) {

    function A2WL() {
        return A2WL_Main::instance();
    }

}

$ali2woo_lite = A2WL();

/**
 * Ali2Woo Lite global init action
 */
do_action('a2wl_init');
