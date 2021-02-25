<?php
/**
 * Plugin Name: Finale WooCommerce Deal Pages
 * Plugin URI: https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/
 * Description: Now list all your deals, special offers, enticing sales on a single page. Visitors can access it via a single click on the navigation bar/sticky header. They can browse Deals of the day/Exclusives/Stock Clearance sale/Christmas specials, all curated on a single page. Browsing and shopping during a sale has never been so hassle-free.
 * Version: 1.4.1
 * Author: XLPlugins
 * Author URI: http://xlplugins.com/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: finale-woocommerce-deal-pages
 * Domain Path: languages
 * XL: true
 * Requires at least: 4.2.1
 * Tested up to: 5.2.4
 * WC requires at least: 2.6.0
 * WC tested up to: 3.7.1
 *
 * Finale WooCommerce Deal Pages is free software.
 * You can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Finale WooCommerce Deal Pages is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Finale WooCommerce Deal Pages. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Finale
 * @Category Add-on
 * @author XLPlugins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'WCCT_DEAL_PAGES_BASENAME', plugin_basename( __FILE__ ) );

if ( ! function_exists( 'wcct_finale_dependency' ) ) {

	/**
	 * Function to check if wcct_finale pro version is loaded and activated or not?
	 * @return bool True|False
	 */
	function wcct_finale_dependency() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		if ( false === file_exists( plugin_dir_path( __DIR__ ) . 'finale-woocommerce-sales-countdown-timer-discount-plugin/finale-woocommerce-sales-countdown-timer-discount-plugin.php' ) ) {
			return false;
		}

		return in_array( 'finale-woocommerce-sales-countdown-timer-discount-plugin/finale-woocommerce-sales-countdown-timer-discount-plugin.php', $active_plugins, true ) || array_key_exists( 'finale-woocommerce-sales-countdown-timer-discount-plugin/finale-woocommerce-sales-countdown-timer-discount-plugin.php', $active_plugins );
	}
}

/**
 * Plugin class to handle setting constants and loading files and static helper methods.
 */
final class Finale_deal_pages {

	/**
	 * Admin Dashboard.
	 */
	public function loaded() {
		if ( is_admin() ) {
			do_action( 'finale_deal_locomotive_init' );
		}
	}

	public function run() {
		$batch_process = '';
		$step          = 0;
		$errors        = array();

		check_ajax_referer( 'wcct-deal-run-batch-process', 'nonce' );

		if ( empty( $_POST['batch_process'] ) ) {
			$errors[] = __( 'Batch process not specified.', 'locomotive' );
		} else {
			$batch_process = sanitize_text_field( wp_unslash( $_POST['batch_process'] ) );
		}

		if ( empty( $_POST['step'] ) ) {
			$errors[] = __( 'Step must be defined.', 'locomotive' );
		} else {
			$step = absint( $_POST['step'] );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json( array(
				'success' => false,
				'errors'  => $errors,
			) );
		}

		do_action( 'wcct_deal_batch_' . $batch_process, $step );
	}

	/**
	 * AJAX handler for running a batch.
	 *
	 * @todo Move this to it's own AJAX class.
	 */
	public function reset() {
		$batch_process = '';
		$errors        = array();

		check_ajax_referer( 'wcct-deal-run-batch-process', 'nonce' );

		if ( empty( $_POST['batch_process'] ) ) {
			$errors[] = __( 'Batch process not specified.', 'locomotive' );
		} else {
			$batch_process = sanitize_text_field( wp_unslash( $_POST['batch_process'] ) );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json( array(
				'success' => false,
				'errors'  => $errors,
			) );
		}

		do_action( 'wcct_deal_batch_' . $batch_process . '_reset' );

		wp_send_json( array( 'success' => true ) );
	}

	public function wcct_settings_redirect( $plugin ) {
		if ( wcct_finale_dependency() && class_exists( 'WooCommerce' ) ) {
			if ( $plugin === plugin_basename( __FILE__ ) ) {
				wp_safe_redirect( add_query_arg( array(
					'page'    => 'wc-settings',
					'tab'     => WCCT_Common::get_wc_settings_tab_slug(),
					'section' => 'deal_pages',
				), admin_url( 'admin.php' ) ) );
				exit;
			}
		}
	}

	/**
	 * Init.
	 */
	public function init() {
		$this->define_constants();
		$this->load_includes();
		$this->attach_hooks();
	}

	/**
	 * Define all the constants we need
	 */
	public function define_constants() {
		define( 'WCCT_DEAL_PAGE_VERSION', '1.4.1' );
		define( 'WCCT_DEAL_PAGE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'WCCT_DEAL_PAGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'WCCT_DEAL_PAGE_PLUGIN_FILE', __FILE__ );
		define( 'WCCT_DEAL_PAGE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WCCT_DEAL_FULL_NAME', __( 'Finale WooCommerce Deal Pages', 'finale-woocommerce-deal-pages' ) );
	}

	/**
	 * Load in all the files we need.
	 */
	public function load_includes() {

		include_once WCCT_DEAL_PAGE_PLUGIN_DIR . 'woo-includes/woo-functions.php';
		if ( wcct_deal_is_woocommerce_active() ) {
			include_once plugin_dir_path( WCCT_DEAL_PAGE_PLUGIN_FILE ) . 'class-wcct-deal-license-handler.php';
			include_once plugin_dir_path( WCCT_DEAL_PAGE_PLUGIN_FILE ) . 'includes/class-wcct-deal-xl-support.php';
			require_once( WCCT_DEAL_PAGE_PLUGIN_DIR . 'includes/abstracts/abstract-batch.php' );
			require_once( WCCT_DEAL_PAGE_PLUGIN_DIR . 'includes/xl-finale-batch-json.php' );
			require_once( WCCT_DEAL_PAGE_PLUGIN_DIR . 'includes/batches/class-batch-product.php' );
			require_once( WCCT_DEAL_PAGE_PLUGIN_DIR . 'includes/functions.php' );
			require_once( WCCT_DEAL_PAGE_PLUGIN_DIR . 'includes/xl-finale-batch-admin.php' );
			require_once( WCCT_DEAL_PAGE_PLUGIN_DIR . 'includes/xl-finale-batch-process.php' );
		}
	}

	/**
	 * Handle hooks.
	 */
	public function attach_hooks() {
		add_action( 'admin_init', array( $this, 'loaded' ) );

		add_action( 'wp_ajax_run_batch', array( $this, 'run' ) );

		add_action( 'wp_ajax_reset_batch', array( $this, 'reset' ) );

		add_action( 'activated_plugin', array( $this, 'wcct_settings_redirect' ) );
	}

}

if ( wcct_finale_dependency() ) {
	$batch_processing = new Finale_deal_pages();
	$batch_processing->init();
}
