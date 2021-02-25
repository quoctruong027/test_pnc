<?php
/**
 * WC_PRL_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles compatibility with other WC extensions.
 *
 * @class    WC_PRL_Compatibility
 * @version  1.4.1
 */
class WC_PRL_Compatibility {

	/**
	 * Min required plugin versions to check.
	 * @var array
	 */
	private static $required = array();

	/**
	 * Setup compatibility class.
	 */
	public static function init() {

		// Define dependencies.
		self::$required = array(
			'pb' => '5.11.0',
			'cp' => '4.1.0'
		);

		// Initialize.
		self::load_modules();
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	protected static function load_modules() {

		if ( is_admin() ) {
			// Check plugin min versions.
			add_action( 'admin_init', array( __CLASS__, 'add_compatibility_notices' ) );
		}

		// Include core compatibility class.
		self::core_includes();

		// Load modules.
		add_action( 'plugins_loaded', array( __CLASS__, 'module_includes' ), 100 );

		// Prevent initialization of deprecated mini-extensions usually loaded on 'plugins_loaded' -- 10.
		self::unload_modules();
	}

	/**
	 * Core compatibility functions.
	 *
	 * @return void
	 */
	public static function core_includes() {
		require_once( WC_PRL_ABSPATH . 'includes/compatibility/core/class-wc-prl-core-compatibility.php' );
	}

	/**
	 * Prevent deprecated mini-extensions from initializing.
	 *
	 * @return void
	 */
	protected static function unload_modules() {
		// Silence.
	}

	/**
	 * Load compatibility classes.
	 *
	 * @return void
	 */
	public static function module_includes() {

		$module_paths = array();

		// Product Bundles support.
		if ( function_exists( 'WC_PB' ) ) {
			$module_paths[ 'product_bundles' ] = WC_PRL_ABSPATH . '/includes/compatibility/modules/class-wc-prl-pb-compatibility.php';
		}

		// Composite Products support.
		if ( function_exists( 'WC_CP' ) ) {
			$module_paths[ 'composite_products' ] = WC_PRL_ABSPATH . '/includes/compatibility/modules/class-wc-prl-cp-compatibility.php';
		}

		// Elementor support.
		if ( did_action( 'elementor/loaded' ) ) {
			$module_paths[ 'elementor' ] = WC_PRL_ABSPATH . '/includes/compatibility/modules/class-wc-prl-elementor-compatibility.php';
		}

		// Product Vendors support.
		if ( class_exists( 'WC_Product_Vendors' ) && defined( 'WC_PRODUCT_VENDORS_VERSION' ) ) {
			$module_paths[ 'product_vendors' ] = WC_PRL_ABSPATH . '/includes/compatibility/modules/class-wc-prl-vendors-compatibility.php';
		}

		/**
		 * 'woocommerce_prl_compatibility_modules' filter.
		 *
		 * Use this to filter the required compatibility modules.
		 *
		 * @since  1.0.6
		 * @param  array $module_paths
		 */
		$module_paths = apply_filters( 'woocommerce_prl_compatibility_modules', $module_paths );

		foreach ( $module_paths as $name => $path ) {
			require_once( $path );
		}
	}

	/**
	 * Checks versions of compatible/integrated/deprecated extensions.
	 *
	 * @return void
	 */
	public static function add_compatibility_notices() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// PB version check.
		if ( function_exists( 'WC_PB' ) ) {
			$required_version = self::$required[ 'pb' ];
			if ( version_compare( WC_PRL()->get_plugin_version( true, WC_PB()->version ), $required_version ) < 0 ) {

				$extension      = __( 'Product Bundles', 'woocommerce-product-recommendations' );
				$extension_full = __( 'WooCommerce Product Bundles', 'woocommerce-product-recommendations' );
				$extension_url  = 'https://woocommerce.com/products/product-bundles/';
				$notice         = sprintf( __( 'The installed version of <strong>%1$s</strong> is not supported by <strong>Product Recommendations</strong>. Please update <a href="%2$s" target="_blank">%3$s</a> to version <strong>%4$s</strong> or higher.', 'woocommerce-product-recommendations' ), $extension, $extension_url, $extension_full, $required_version );

				WC_PRL_Admin_Notices::add_dismissible_notice( $notice, array( 'dismiss_class' => 'pb_lt_' . $required_version, 'type' => 'warning' ) );
			}
		}

		// CP version check.
		if ( function_exists( 'WC_CP' ) ) {
			$required_version = self::$required[ 'cp' ];
			if ( version_compare( WC_PRL()->get_plugin_version( true, WC_CP()->version ), $required_version ) < 0 ) {

				$extension      = __( 'Composite Products', 'woocommerce-product-recommendations' );
				$extension_full = __( 'WooCommerce Composite Products', 'woocommerce-product-recommendations' );
				$extension_url  = 'https://woocommerce.com/products/composite-products/';
				$notice         = sprintf( __( 'The installed version of <strong>%1$s</strong> is not supported by <strong>Product Recommendations</strong>. Please update <a href="%2$s" target="_blank">%3$s</a> to version <strong>%4$s</strong> or higher.', 'woocommerce-product-recommendations' ), $extension, $extension_url, $extension_full, $required_version );

				WC_PRL_Admin_Notices::add_dismissible_notice( $notice, array( 'dismiss_class' => 'cp_lt_' . $required_version, 'type' => 'warning' ) );
			}
		}
	}
}

WC_PRL_Compatibility::init();
