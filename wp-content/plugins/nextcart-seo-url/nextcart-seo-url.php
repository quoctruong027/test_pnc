<?php

/** 
 * Plugin Name: Next-Cart SEO URLs
 * Description: Redirects (301) old URLs to new URLs in WooCommerce store.
 * Version: 1.0.0
 * Author: Next-Cart
 * Author URI: https://next-cart.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * WC requires at least: 3.0
 * WC tested up to: 3.7
 */

defined('ABSPATH') or die();
if (!defined('NCSU_PLUGIN_URL')) {
    define('NCSU_PLUGIN_URL', plugins_url() . '/' . trim(dirname(plugin_basename(__FILE__)), '/'));
}
if (!defined('NCSU_PLUGIN_PATH')) {
    define('NCSU_PLUGIN_PATH', dirname(__FILE__) . '/');
}

class NcSeoUrl {
    
    protected static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        if (is_admin()) {
            include_once NCSU_PLUGIN_PATH . 'includes/class-nc-admin.php';
        }
        $this->init_hooks();
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array(&$this, 'install'));
        
        add_filter('request', function ($query) {
            if (empty($query['product']) || empty($query['category']) || empty($query['page']) || empty($query['post'])) {
                global $wpdb;
                $url = parse_url(get_bloginfo('url'));
                $url = isset($url['path']) ? $url['path'] : '';
                $request = trim(substr($_SERVER['REQUEST_URI'], strlen($url)), '/');
                $request = parse_url($request, PHP_URL_PATH);
                if (!$request) {
                    return $query;
                }
                $paths = explode('?', $request);
                $sql = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}nextcart_seo_url` WHERE `request_path` = '" . $paths[0] . "'", ARRAY_A);
                $wpUrl = false;
                if ($sql) {
                    $sql = end($sql);
                    if ($sql && $sql['target_type'] == 'category') {
                        $wpUrl = get_term_link((int) $sql['target_id'], 'product_cat');
                    } elseif ($sql) {
                        $wpUrl = get_permalink((int) $sql['target_id']);
                    }
                }
                if ($wpUrl) {
                    //wp_redirect($wpUrl, 301);
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . $wpUrl);
                    exit;
                }
            }
            return $query;
        }, 'edit_files', 1);
    }
    
    public function install() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        require_once(ABSPATH . 'wp-admin/includes/schema.php');
        global $wpdb;
        $query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nextcart_seo_url` (`redirect_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, `request_path` VARCHAR(255), `target_id` INT(11), `target_type` VARCHAR(255)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        dbDelta($query);
    }
}

NcSeoUrl::instance();