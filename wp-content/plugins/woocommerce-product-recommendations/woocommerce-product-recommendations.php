<?php
/**
* Plugin Name: WooCommerce Product Recommendations
* Plugin URI: https://woocommerce.com/products/product-recommendations/
* Description: Create smarter up-sells and cross-sells, place them anywhere, and measure their impact with in-depth analytics.
* Version: 1.4.7
* Author: SomewhereWarm
* Author URI: https://somewherewarm.com/
*
* Woo: 4486128:9732a1cdebd38f7eb1f58bb712f7fb0e
*
* Text Domain: woocommerce-product-recommendations
* Domain Path: /languages/
*
* Requires at least: 4.4
* Tested up to: 5.5
*
* WC requires at least: 3.3
* WC tested up to: 4.6
*
* Copyright: © 2017-2020 SomewhereWarm SMPC.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @class    WC_Product_Recommendations
 * @version  1.4.7
 */
class WC_Product_Recommendations {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version = '1.4.7';

	/**
	 * Min required WC version.
	 *
	 * @var string
	 */
	private $wc_min_version = '3.3.0';

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Product_Recommendations
	 */
	protected static $_instance = null;

	/**
	 * Loaded locations.
	 *
	 * @var WC_PRL_Locations
	 */
	public $locations;

	/**
	 * Deployments factory.
	 *
	 * @var WC_PRL_Deployments
	 */
	public $deployments;

	/**
	 * DB Helper.
	 *
	 * @var WC_PRL_DB
	 */
	public $db;

	/**
	 * Templates functions.
	 *
	 * @var WC_PRL_Templates
	 */
	public $templates;

	/**
	 * Main WC_Product_Recommendations instance. Ensures only one instance is loaded or can be loaded - @see 'WC_PRL()'.
	 *
	 * @static
	 * @return  WC_Product_Recommendations
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Make stuff.
	 */
	protected function __construct() {
		// Entry point.
		add_action( 'plugins_loaded', array( $this, 'initialize_plugin' ), 9 );
	}

	/**
	 * Plugin URL getter.
	 *
	 * @return string
	 */
	public function get_plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Plugin path getter.
	 *
	 * @return string
	 */
	public function get_plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @return string
	 */
	public function get_plugin_basename() {
		return plugin_basename( __FILE__ );
	}

	/**
	 * Plugin version getter.
	 *
	 * @param  boolean  $base
	 * @param  string   $version
	 * @return string
	 */
	public function get_plugin_version( $base = false, $version = '' ) {

		$version = $version ? $version : $this->version;

		if ( $base ) {
			$version_parts = explode( '-', $version );
			$version       = sizeof( $version_parts ) > 1 ? $version_parts[ 0 ] : $version;
		}

		return $version;
	}

	/**
	 * Define constants if not present.
	 *
	 * @since  1.2.4
	 *
	 * @return boolean
	 */
	protected function maybe_define_constant( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Indicates whether the plugin is fully initialized.
	 *
	 * @since  1.2.4
	 *
	 * @return boolean
	 */
	public function is_plugin_initialized() {
		return isset( WC_PRL()->locations );
	}

	/**
	 * Fire in the hole!
	 */
	public function initialize_plugin() {

		$this->define_constants();
		$this->maybe_create_store();

		// WC version sanity check.
		if ( ! function_exists( 'WC' ) || version_compare( WC()->version, $this->wc_min_version ) < 0 ) {
			$notice = sprintf( __( 'WooCommerce Product Recommendations requires at least WooCommerce <strong>%s</strong>.', 'woocommerce-product-recommendations' ), $this->wc_min_version );
			require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-notices.php' );
			WC_PRL_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		// PHP version check.
		if ( ! function_exists( 'phpversion' ) || version_compare( phpversion(), '5.6.20', '<' ) ) {
			$notice = sprintf( __( 'WooCommerce Product Recommendations requires at least PHP <strong>%1$s</strong>. Learn <a href="%2$s">how to update PHP</a>.', 'woocommerce-product-recommendations' ), '5.6.20', $this->get_resource_url( 'update-php' ) );
			require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-notices.php' );
			WC_PRL_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		$this->includes();

		// Instantiate global singletons.
		$this->locations   = new WC_PRL_Locations();
		$this->deployments = new WC_PRL_Deployments();
		$this->filters     = new WC_PRL_Filters();
		$this->amplifiers  = new WC_PRL_Amplifiers();
		$this->conditions  = new WC_PRL_Conditions();
		$this->db          = new WC_PRL_DB();
		$this->templates   = new WC_PRL_Templates();

		// Load translations hook.
		add_action( 'init', array( $this, 'load_translation' ) );
	}

	/**
	 * Constants.
	 */
	public function define_constants() {
			$this->maybe_define_constant( 'WC_PRL_VERSION', $this->version );
			$this->maybe_define_constant( 'WC_PRL_SUPPORT_URL', 'https://woocommerce.com/my-account/marketplace-ticket-form/' );
			$this->maybe_define_constant( 'WC_PRL_ABSPATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
	}

	/**
	 * A simple dumb datastore for sharing information accross our plugins.
	 *
	 * @since  1.3.0
	 *
	 * @return void
	 */
	private function maybe_create_store() {
		if ( ! isset( $GLOBALS[ 'sw_store' ] ) ) {
			$GLOBALS[ 'sw_store' ] = array();
		}
	}

	/**
	 * Includes.
	 */
	public function includes() {

		// Install.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-install.php' );
		require_once( WC_PRL_ABSPATH . 'includes/db/class-wc-prl-frequencies-db.php' );
		require_once( WC_PRL_ABSPATH . 'includes/db/class-wc-prl-tracking-db.php' );
		require_once( WC_PRL_ABSPATH . 'includes/db/class-wc-prl-deployment-db.php' );
		require_once( WC_PRL_ABSPATH . 'includes/db/class-wc-prl-db.php' );

		// Compatibility.
		require_once( WC_PRL_ABSPATH . 'includes/compatibility/class-wc-prl-compatibility.php' );

		// Functions.
		require_once( WC_PRL_ABSPATH . 'includes/wc-prl-functions.php' );

		// Templates.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-templates.php' );

		// Post types.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-post-types.php' );
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-post-data.php' );

		// Engines.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-engine.php' );
		require_once( WC_PRL_ABSPATH . 'includes/data-stores/class-wc-prl-engine-data-store-cpt.php' );

		// Locations.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-locations.php' );
		require_once( WC_PRL_ABSPATH . 'includes/abstracts/class-wc-prl-abstract-location.php' );
		require_once( WC_PRL_ABSPATH . 'includes/locations/class-wc-prl-location-cart-page.php' );
		require_once( WC_PRL_ABSPATH . 'includes/locations/class-wc-prl-location-product-details.php' );
		require_once( WC_PRL_ABSPATH . 'includes/locations/class-wc-prl-location-product-archive.php' );
		require_once( WC_PRL_ABSPATH . 'includes/locations/class-wc-prl-location-order-received.php' );
		require_once( WC_PRL_ABSPATH . 'includes/locations/class-wc-prl-location-pay-page.php' );
		require_once( WC_PRL_ABSPATH . 'includes/locations/class-wc-prl-location-checkout.php' );
		require_once( WC_PRL_ABSPATH . 'includes/locations/class-wc-prl-location-shop.php' );

		// Deployments.
		require_once( WC_PRL_ABSPATH . 'includes/data-stores/class-wc-prl-deployment-data.php' );
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-deployment.php' );
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-deployments.php' );
		// - Background generator for deployments.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-background-generator.php' );

		// Conditions.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-conditions.php' );
		require_once( WC_PRL_ABSPATH . 'includes/abstracts/class-wc-prl-abstract-condition.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-date.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-guest.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-geolocate.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-recent-category.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-recent-tag.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-recent-product.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-cart-category.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-cart-tag.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-cart-product.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-cart-total.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-cart-item-value.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-product-id.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-product-price.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-product-category.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-product-tag.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-product-stock-status.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-archive-category.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-archive-tag.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-order-total.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-order-category.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-order-tag.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-order-product.php' );
		require_once( WC_PRL_ABSPATH . 'includes/conditions/class-wc-prl-condition-order-item-value.php' );

		// Engine Filters.
		require_once( WC_PRL_ABSPATH . 'includes/abstracts/class-wc-prl-abstract-filter.php' );
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-filters.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-attribute.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-attribute-context.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-category.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-category-context.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-tag.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-tag-context.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-price.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-price-context.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-stock-status.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-recently-viewed.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-product.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-sales.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-featured.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-freshness.php' );

		// Engine Amplifiers.
		require_once( WC_PRL_ABSPATH . 'includes/abstracts/class-wc-prl-abstract-amplifier.php' );
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-amplifiers.php' );
		require_once( WC_PRL_ABSPATH . 'includes/amplifiers/class-wc-prl-amplifier-freshness.php' );
		require_once( WC_PRL_ABSPATH . 'includes/amplifiers/class-wc-prl-amplifier-frequently-bought-together.php' );
		require_once( WC_PRL_ABSPATH . 'includes/amplifiers/class-wc-prl-amplifier-others-also-bought.php' );
		require_once( WC_PRL_ABSPATH . 'includes/amplifiers/class-wc-prl-amplifier-price.php' );
		require_once( WC_PRL_ABSPATH . 'includes/amplifiers/class-wc-prl-amplifier-rating.php' );
		require_once( WC_PRL_ABSPATH . 'includes/amplifiers/class-wc-prl-amplifier-popularity.php' );
		require_once( WC_PRL_ABSPATH . 'includes/amplifiers/class-wc-prl-amplifier-conversion-rate.php' );
		require_once( WC_PRL_ABSPATH . 'includes/amplifiers/class-wc-prl-amplifier-random.php' );

		// Front-end AJAX handlers.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-ajax.php' );

		// Tracking.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-tracking.php' );

		// Order hooks.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-order.php' );

		// Notices.
		require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-notices.php' );

		// Admin includes.
		if ( is_admin() ) {
			$this->admin_includes();
		}

		// WP-CLI includes.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-cli.php' );
		}
	}

	/**
	 * Admin & AJAX functions and hooks.
	 */
	public function admin_includes() {

		// Admin notices handling.
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-notices.php' );

		// Admin post type settings.
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-post-types.php' );
		require_once( WC_PRL_ABSPATH . 'includes/admin/list-tables/class-wc-prl-admin-list-engines.php' );
		require_once( WC_PRL_ABSPATH . 'includes/admin/list-tables/class-wc-prl-admin-list-deployments.php' );

		// Admin functions and hooks.
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin.php' );
		// Pages.
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-locations.php' );
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-deploy.php' );
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-hooks.php' );
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-performance.php' );
	}

	/**
	 * Load textdomain.
	 */
	public function load_translation() {
		load_plugin_textdomain( 'woocommerce-product-recommendations', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Returns URL to a doc or support resource.
	 *
	 * @since  1.3.0
	 *
	 * @param  string  $handle
	 * @return string
	 */
	public function get_resource_url( $handle ) {

		$resource = false;

		if ( 'update-php' === $handle ) {
			$resource = 'https://docs.woocommerce.com/document/how-to-update-your-php-version/';
		} elseif ( 'docs-contents' === $handle ) {
			$resource = 'https://docs.woocommerce.com/document/product-recommendations/';
		} elseif ( 'page-caching' === $handle ) {
			$resource = 'https://docs.woocommerce.com/document/product-recommendations/frequently-asked-questions/#faq_missing_recommendations';
		} elseif ( 'updating' === $handle ) {
			$resource = 'https://docs.woocommerce.com/document/how-to-update-woocommerce/';
		} elseif ( 'ticket-form' === $handle ) {
			$resource = WC_PRL_SUPPORT_URL;
		} elseif ( 'whats-new-1-4' === $handle ) {
			$resource = 'https://somewherewarm.com/a-pinch-of-machine-learning-for-your-woocommerce-store/';
		}

		return $resource;
	}

	/**
	 * Log using 'WC_Logger' class.
	 *
	 * @param  string  $message
	 * @param  string  $level
	 * @param  string  $context
	 */
	public function log( $message, $level, $context ) {
		$logger = wc_get_logger();
		$logger->log( $level, $message, array( 'source' => $context ) );
	}

	/**
	 * Checks if the current admin screen belongs to PRL extension.
	 *
	 * @return  bool
	 */
	public function is_current_screen() {
		global $current_screen;
		$screen_id = $current_screen ? $current_screen->id : '';

		if ( in_array( $screen_id, $this->get_screen_ids(), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the current admin screen is the legacy reports tab.
	 *
	 * @return  bool
	 */
	public function is_legacy_reports_screen() {
		global $current_screen;
		$screen_id = $current_screen ? $current_screen->id : '';

		if ( 'woocommerce_page_wc-reports' === $screen_id && isset( $_GET[ 'tab' ] ) && 'prl_recommendations' === $_GET[ 'tab' ] ) {
			return true;
		}

		return false;
	}

	/**
	 * Get PRL screen ids.
	 */
	public function get_screen_ids() {
		$screens   = array();
		$screens[] = 'prl_engine';
		$screens[] = 'edit-prl_engine';
		$screens[] = 'woocommerce_page_prl_locations';
		$screens[] = 'woocommerce_page_prl_performance';

		return $screens;
	}
}

/**
 * Returns the main instance of WC_Product_Recommendations to prevent the need to use globals.
 *
 * @return  WC_Product_Recommendations
 */
function WC_PRL() {
	return WC_Product_Recommendations::instance();
}

WC_PRL();
