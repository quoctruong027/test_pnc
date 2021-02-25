<?php
/**
 * WC_PRL_Admin class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class.
 *
 * Loads admin scripts, includes admin classes and adds admin hooks.
 *
 * @class    WC_PRL_Admin
 * @version  1.4.7
 */
class WC_PRL_Admin {

	/**
	 * Bundled selectSW library version.
	 *
	 * @var string
	 */
	private static $bundled_selectsw_version = '1.1.3';

	/**
	 * Setup Admin class.
	 */
	public static function init() {

		// Admin initializations.
		add_action( 'init', array( __CLASS__, 'admin_init' ) );

		// selectSW scripts.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'maybe_register_selectsw' ), 0 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'maybe_load_selectsw' ), 1 );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_display_selectsw_notice' ), 1 );

		// Init meta boxes.
		add_action( 'current_screen', array( __CLASS__, 'init_meta_boxes' ) );

		// Add a message in the WP Privacy Policy Guide page.
		add_action( 'admin_init', array( __CLASS__, 'add_privacy_policy_guide_content' ) );

		// Settings.
		add_filter( 'woocommerce_get_settings_pages', array( __CLASS__, 'add_settings_page' ) );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_resources' ), 11 );
		add_filter( 'woocommerce_screen_ids', array( __CLASS__, 'add_wc_screens' ) );

		// Add body class for WP 5.3 compatibility.
		add_filter( 'admin_body_class', array( __CLASS__, 'include_admin_body_class' ) );

		if ( method_exists( WC(), 'queue' ) ) {
			// Add status tool to re-schedule page cache test.
			add_filter( 'woocommerce_debug_tools', array( __CLASS__, 'add_page_cache_reschedule_tool' ) );
			// Add status tool to clear the PRL's queue.
			add_filter( 'woocommerce_debug_tools', array( __CLASS__, 'add_clear_queue_tool' ) );

		}
	}

	/**
	 * Admin init.
	 */
	public static function admin_init() {
		self::includes();
	}

	/**
	 * Inclusions.
	 */
	protected static function includes() {

		// Admin Menus.
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-menus.php' );

		// Abstract Metabox.
		require_once( WC_PRL_ABSPATH . 'includes/abstracts/class-wc-prl-abstract-meta-box.php' );

		// Reports.
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-reports.php' );

		// Status.
		require_once( WC_PRL_ABSPATH . 'includes/admin/status/class-wc-prl-admin-status.php' );

		// Admin AJAX.
		require_once( WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-ajax.php' );
	}

	/**
	 * Register own version of select2 library.
	 *
	 * @since 1.2.0
	 */
	public static function maybe_register_selectsw() {

		$is_registered      = wp_script_is( 'sw-admin-select-init', $list = 'registered' );
		$registered_version = $is_registered ? wp_scripts()->registered[ 'sw-admin-select-init' ]->ver : '';
		$register           = ! $is_registered || version_compare( self::$bundled_selectsw_version, $registered_version, '>' );

		if ( $register ) {

			if ( $is_registered ) {
				wp_deregister_script( 'sw-admin-select-init' );
			}

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Register own select2 initialization library.
			wp_register_script( 'sw-admin-select-init', WC_PRL()->get_plugin_url() . '/assets/js/admin/select2-init' . $suffix . '.js', array( 'jquery', 'sw-admin-select' ), self::$bundled_selectsw_version );
		}
	}

	/**
	 * Load own version of select2 library.
	 *
	 * @since 1.2.0
	 */
	public static function maybe_load_selectsw() {

		// Responsible for loading selectsw?
		if ( self::load_selectsw() ) {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Register selectSW library.
			wp_register_script( 'sw-admin-select', WC_PRL()->get_plugin_url() . '/assets/js/admin/select2' . $suffix . '.js', array( 'jquery' ), self::$bundled_selectsw_version );

			// Enqueue selectSW styles.
			wp_register_style( 'sw-admin-css-select', WC_PRL()->get_plugin_url() . '/assets/css/admin/select2.css', array(), self::$bundled_selectsw_version );
			wp_style_add_data( 'sw-admin-css-select', 'rtl', 'replace' );
		}
	}

	/**
	 * Display notice when selectSW library is unsupported.
	 *
	 * @since 1.2.0
	 */
	public static function maybe_display_selectsw_notice() {

		if ( ! wp_scripts()->query( 'sw-admin-select-init' ) ) {
			return;
		}

		$registered_version       = wp_scripts()->registered[ 'sw-admin-select-init' ]->ver;
		$registered_version_major = strstr( $registered_version, '.', true );
		$bundled_version_major    = strstr( self::$bundled_selectsw_version, '.', true );

		if ( version_compare( $bundled_version_major, $registered_version_major, '<' ) ) {

			$notice = __( 'The installed version of <strong>Product Recommendations</strong> is not compatible with the <code>selectSW</code> library found on your system. Please update Product Recommendations to the latest version.', 'woocommerce-product-recommendations' );
			WC_PRL_Admin_Notices::add_notice( $notice, 'error' );
		}
	}

	/**
	 * Whether to load own version of select2 library or not.
	 *
	 * @since 1.2.0
	 *
	 * @return boolean
	 */
	private static function load_selectsw() {
		$load_selectsw_from = wp_scripts()->registered[ 'sw-admin-select-init' ]->src;
		return strpos( $load_selectsw_from, WC_PRL()->get_plugin_url() ) === 0;
	}

	/**
	 * Include admin classes.
	 *
	 * @since 1.2.0
	 *
	 * @param  String  $classes
	 * @return String
	 */
	public static function include_admin_body_class( $classes ) {

		$classes .= ' wc-prl';

		if ( ! empty( $_GET[ 'post_status' ] ) && 'trash' === $_GET[ 'post_status' ] ) {
			$classes .= ' wc-prl-trash';
		}

		if ( strpos( $classes, 'sw-wp-version-gte-53' ) !== false ) {
			return $classes;
		}

		if ( WC_PRL_Core_Compatibility::is_wp_version_gte( '5.3' ) ) {
			$classes .= ' sw-wp-version-gte-53';
		}

		return $classes;
	}

	/**
	 * Init meta-boxes.
	 */
	public static function init_meta_boxes() {

		$load_metaboxes = (array) apply_filters( 'woocommerce_prl_metaboxes', array(
			'WC_PRL_Meta_Box_Engine_Configuration',
		) );

		if ( ! WC_PRL()->is_current_screen() ) {
			return;
		}

		foreach ( $load_metaboxes as $metabox ) {
			require_once( WC_PRL_ABSPATH . 'includes/admin/meta-boxes/class-' . strtolower( str_replace('_', '-', $metabox ) ) . '.php' );
			// Instantiate.
			new $metabox();
		}
	}

	/**
	 * Add a message in the WP Privacy Policy Guide page.
	 */
	public static function add_privacy_policy_guide_content() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content( 'WooCommerce Product Recommendations', self::get_privacy_policy_guide_message() );
		}
	}

	/**
	 * Message to add in the WP Privacy Policy Guide page.
	 * @return string
	 */
	protected static function get_privacy_policy_guide_message() {

		$content = '
			<div class="wp-suggested-text">' .
				'<p class="privacy-policy-tutorial">' .
					__( 'WooCommerce Product Recommendations uses cookies to track the following visitor activity:', 'woocommerce-product-recommendations' ) .
				'</p>' .
				'<ul style="list-style:lower-roman;margin-left:20px;">' .
					'<li>' . __( 'Viewed products.', 'woocommerce-product-recommendations' ) . '</li>' .
					'<li>' . __( 'Viewed recommendations.', 'woocommerce-product-recommendations' ) . '</li>' .
					'<li>' . __( 'Clicked products in recommendations.', 'woocommerce-product-recommendations' ) . '</li>' .
				'</ul>' .
				'<p class="privacy-policy-tutorial">' .
					__( 'The extension also stores aggregate information based on the recommendations that visitors view and click while browsing your site. This information cannot be used to personally identify any visitor.', 'woocommerce-product-recommendations' ) .
				'</p>' .
				'<p class="privacy-policy-tutorial">' . __( 'Please make sure that your cookie and analytics policies are updated to reflect this information.', 'woocommerce-product-recommendations' ) . '</p>' .
				'<h3>' . __( 'Cookies', 'woocommerce-product-recommendations' ) . '</h3>' .
				'<p>' . __( '<strong>Suggested text:</strong> While browsing our site, we will set temporary cookies to track:', 'woocommerce-product-recommendations' ) . '</p>' .
				'<ul style="font-style:italic;list-style:lower-roman;margin-left:20px;">' .
					'<li>' . __( 'Recommendations you have viewed.', 'woocommerce-product-recommendations' ) . '</li>' .
					'<li>' . __( 'Product recommendations you have clicked.', 'woocommerce-product-recommendations' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'These cookies contain no personal data and expire after 1 day.', 'woocommerce-product-recommendations' ) . '</p>' .
				'<h3>' . __( 'Analytics', 'woocommerce-product-recommendations' ) . '</h3>' .
				'<p>' . __( '<strong>Suggested text:</strong> We store aggregate information based on the recommendations you view and click while browsing our site. This information cannot be used to personally identify you.', 'woocommerce-product-recommendations' ) . '</p>' .
			'</div>';

		return $content;
	}

	/**
	 * Admin scripts.
	 */
	public static function admin_resources() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_style( 'wc-prl-admin-css', WC_PRL()->get_plugin_url() . '/assets/css/admin/admin.css', array(), WC_PRL()->get_plugin_version() );
		wp_style_add_data( 'wc-prl-admin-css', 'rtl', 'replace' );

		wp_register_style( 'wc-prl-admin-metaboxes-css', WC_PRL()->get_plugin_url() . '/assets/css/admin/meta-boxes.css', array( 'sw-admin-css-select' ), WC_PRL()->get_plugin_version() );
		wp_style_add_data( 'wc-prl-admin-metaboxes-css', 'rtl', 'replace' );

		wp_register_script( 'wc-prl-writepanel', WC_PRL()->get_plugin_url() . '/assets/js/admin/wc-prl-admin' . $suffix . '.js', array( 'jquery', 'wp-util', 'sw-admin-select-init' ), WC_PRL()->get_plugin_version() );

		$params = array(
			'wc_ajax_url'                         => admin_url( 'admin-ajax.php' ),
			'attributes_form_nonce'               => wp_create_nonce( 'wc_prl_attributes_form' ),
			'i18n_attributes_form_session_expired' => _x( 'Something went wrong. Please refresh your browser and try again.', 'attributes_form', 'woocommerce-product-recommendations' ),
			'regenerate_deployment_nonce'         => wp_create_nonce( 'wc_prl_regenerate_deployment' ),
			'add_deployment_nonce'                => wp_create_nonce( 'wc_prl_add_deployment' ),
			'delete_deployment_nonce'             => wp_create_nonce( 'wc_prl_delete_deployment' ),
			'toggle_deployment_nonce'             => wp_create_nonce( 'wc_prl_toggle_deployment' ),
			'search_engine_nonce'                 => wp_create_nonce( 'wc_prl_search_engine' ),
			'regenerate_engine_nonce'             => wp_create_nonce( 'wc_prl_regenerate_engine' ),
			'i18n_engine_regeneration'            => __( 'Engine cache cleared. Recommendations will be regenerated shortly after the first time they are requested.', 'woocommerce-product-recommendations' ),
			'i18n_deployment_regeneration'        => __( 'Cache cleared. Recommendations will be regenerated shortly after the first time they are requested.', 'woocommerce-product-recommendations' ),
			'i18n_toggle_session_expired'         => _x( 'Something went wrong. Please refresh your browser and try again.', 'active toggler', 'woocommerce-product-recommendations' ),
			'i18n_change_type_conditions_warning' => __( 'The conditions you have added will be deleted. Are you sure?', 'woocommerce-product-recommendations' ),
			'i18n_change_type_warning'            => __( 'The filters and amplifiers you have added will be deleted. Are you sure?', 'woocommerce-product-recommendations' ),
			'i18n_delete_deployment_warning'      => __( 'This deployment will be permanently deleted from your system. Are you sure?', 'woocommerce-product-recommendations' ),
		);

		/*
		 * Enqueue global css.
		 */
		wp_enqueue_style( 'wc-prl-admin-css' );

		/*
		 * Enqueue specific styles & scripts.
		 */
		if ( WC_PRL()->is_current_screen() ) {
			wp_enqueue_script( 'wc-prl-writepanel' );
			wp_localize_script( 'wc-prl-writepanel', 'wc_prl_admin_params', $params );
			wp_enqueue_style( 'wc-prl-admin-metaboxes-css' );
		}

		/*
		 * Enqueue scripts in Reports tab.
		 */
		if ( WC_PRL()->is_legacy_reports_screen() ) {
			wp_enqueue_script( 'wc-prl-writepanel' );
			wp_localize_script( 'wc-prl-writepanel', 'wc_prl_admin_params', $params );
			wp_enqueue_style( 'wc-prl-admin-metaboxes-css' );
		}
	}

	/**
	 * Add a RPL screen ids.
	 */
	public static function add_wc_screens( $screens ) {
		$screens = array_merge( $screens, WC_PRL()->get_screen_ids() );
		return $screens;
	}

	/**
	 * Add 'Recommendations' tab to WooCommerce Settings tabs.
	 *
	 * @param  array $settings
	 * @return array $settings
	 */
	public static function add_settings_page( $settings ) {

		$settings[] = include( 'settings/class-wc-prl-admin-settings.php' );

		return $settings;
	}

	/**
	 * Adds status tool to re-schedule page cache test.
	 *
	 * @since  1.3.3
	 *
	 * @param  array
	 * @return array
	 */
	public static function add_page_cache_reschedule_tool( $tools ) {

		$tools[ 'prl_reschedule_page_cache_test' ] = array(
			'name'     => __( 'Reschedule page cache test', 'woocommerce-product-recommendations' ),
			'button'   => __( 'Reschedule', 'woocommerce-product-recommendations' ),
			'desc'     => __( 'This will reschedule the page cache test routine in Product Recommendations and update notices as needed. Useful if you have just made changes to your server and don\'t want to wait for the next scheduled test run.', 'woocommerce-product-recommendations' ),
			'callback' => array( __CLASS__, 'reschedule_page_cache_test' )
		);

		return $tools;
	}

	/**
	 * Rescedules page cache test.
	 *
	 * @since  1.3.3
	 *
	 * @return string
	 */
	public static function reschedule_page_cache_test() {

		$result = WC_PRL_Notices::schedule_page_cache_test( true );

		if ( is_wp_error( $result ) ) {

			if ( did_action( 'admin_head' ) && ! empty( $_GET[ 'action' ] ) && 'prl_reschedule_page_cache_test' === $_GET[ 'action' ] ) {
				echo '<div class="error inline"><p>' . sprintf( __( 'Could not reschedule page cache test: %s', 'woocommerce-product-recommendations' ), $result->get_error_message() ) . '</p></div>';
			}

			return 1;
		}

		return __( 'Page cache test rescheduled successfully.', 'woocommerce-product-recommendations' );
	}

	/**
	 * Adds status tool to clear the queue.
	 *
	 * @since  1.4.7
	 *
	 * @param  array
	 * @return array
	 */
	public static function add_clear_queue_tool( $tools ) {

		$tools[ 'prl_clear_queue' ] = array(
			'name'     => __( 'Clear Product Recommendations queue', 'woocommerce-product-recommendations' ),
			'button'   => __( 'Clear queue', 'woocommerce-product-recommendations' ),
			'desc'     => __( 'This will clear the regeneration queue of Product Recommendations. Useful if the queue appears to be stuck due to server errors or conflicts.', 'woocommerce-product-recommendations' ),
			'callback' => array( __CLASS__, 'clear_prl_queue' )
		);

		return $tools;
	}

	/**
	 * Clears the PRL queue.
	 *
	 * @since  1.4.7
	 *
	 * @param  array
	 * @return array
	 */
	public static function clear_prl_queue() {
		global $wpdb;

		$affected_rows = $wpdb->query( $wpdb->prepare("DELETE FROM `{$wpdb->prefix}options` where `option_name` LIKE %s", '%wc_prl_generator_batch%') );

		if ( false !== $affected_rows ) {
			return __( 'Product Recommendations queue cleared successfully.', 'woocommerce-product-recommendations' );
		} else {
			return false;
		}
	}
}

WC_PRL_Admin::init();
