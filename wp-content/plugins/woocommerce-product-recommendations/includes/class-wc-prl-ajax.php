<?php
/**
 * WC_PRL_Ajax class
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
 * Front-end AJAX filters.
 *
 * @class    WC_PRL_Ajax
 * @version  1.4.6
 */
class WC_PRL_Ajax {

	/**
	 * Current URL that is retrieving deployments.
	 *
	 * @var string
	 */
	private static $current_url;

	/**
	 * Hook in.
	 */
	public static function init() {
		// Front end.
		add_action( 'wc_ajax_woocommerce_prl_log_view_event', array( __CLASS__ , 'prl_log_view_event' ) );
		add_action( 'wc_ajax_woocommerce_prl_log_click_event', array( __CLASS__ , 'prl_log_click_event' ) );
		add_action( 'wc_ajax_woocommerce_prl_print_location', array( __CLASS__ , 'prl_print_location' ) );
	}

	/**
	 * Add View Event to database.
	 *
	 * @return void
	 */
	public static function prl_log_view_event() {
		ob_start();

		check_ajax_referer( 'write-view-event', 'security' );

		if ( empty( $_POST[ 'id' ] ) || empty( $_POST[ 'engine_id' ] ) || empty( $_POST[ 'location_hash' ] ) ) {
			wp_send_json( array(
				'result'  => 'failure',
				'message' => __( 'Event missing attributes', 'woocommerce-product-recommendations' )
			) );
		}

		try {

			WC_PRL()->db->tracking->add_view_event( array(
				'deployment_id' => $_POST[ 'id' ],
				'engine_id'     => $_POST[ 'engine_id' ],
				'location_hash' => $_POST[ 'location_hash' ],
				'source_hash'   => $_POST[ 'source_hash' ]
			) );

			wp_send_json( array(
				'result'  => 'success'
			) );

		} catch ( Exception $e ) {
			wp_send_json( array(
				'result'  => 'failure',
				'message' => $e->getMessage()
			) );
		}
	}

	/**
	 * Add Click Event to database.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public static function prl_log_click_event() {
		ob_start();

		check_ajax_referer( 'write-click-event', 'security' );

		if ( empty( $_POST[ 'clicks' ] ) ) {
			wp_send_json( array(
				'result'  => 'failure',
				'message' => __( 'Event missing attributes', 'woocommerce-product-recommendations' )
			) );
		}

		try {

			if ( ! empty( $_POST[ 'clicks' ] ) ) {

				// Sanitize.
				$clicks = array_map( 'wc_clean', $_POST[ 'clicks' ] );

				foreach ( $clicks as $track_param ) {
					// Send click event to db.
					WC_PRL_Tracking::maybe_add_click_event( $track_param );
				}
			}

		} catch ( Exception $e ) {
			wp_send_json( array(
				'result'  => 'failure',
				'message' => $e->getMessage()
			) );
		}
	}

	/**
	 * Render deployments to bypass HTML cache.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public static function prl_print_location() {

		// Validation.
		if ( empty( $_POST[ 'locations' ] ) ) {

			wp_send_json( array(
				'result'  => 'failure',
				'message' => __( 'Locations not found', 'woocommerce-product-recommendations' )
			) );
		}

		// Filter current request url.
		self::$current_url = esc_url( $_POST[ 'current_url' ], null, 'edit' );
		add_filter( 'woocommerce_product_add_to_cart_url', array( __CLASS__, 'fix_add_to_cart_url' ), -9999, 2 );

		// Setup GLOBALS.
		WC_PRL()->locations->setup_environment( $_POST );

		$locations = explode( ',', wc_clean( $_POST[ 'locations' ] ) );
		$output    = array();

		foreach ( $locations as $hook ) {

			ob_start();

			// Hint: All native arguments passed
			//       from the current action are missing in this context.
			WC_PRL()->templates->process_hook( $hook, true );

			// Save output.
			$html = ob_get_clean();
			if ( $html ) {
				$output[ $hook ] = $html;
			}
		}

		// Remove url filter.
		self::$current_url = null;
		remove_filter( 'woocommerce_product_add_to_cart_url', array( __CLASS__, 'fix_add_to_cart_url' ), -9999, 2 );

		wp_send_json( array(
			'result'  => 'success',
			'html'    => $output
		) );
	}

	/**
	 * Fix url add-to-cart button.
	 *
	 * @since 1.1.0
	 *
	 * @param  string     $url
	 * @param  WC_Product $product
	 * @return string
	 */
	public static function fix_add_to_cart_url( $url, $product ) {

		if ( $product->has_options() || ( method_exists( $product, 'requires_input' ) && $product->requires_input() ) ) {
			return $url;
		}

		$url = remove_query_arg( array( 'wc-ajax', 'prl_track' ), add_query_arg( 'add-to-cart', $product->get_id(), self::$current_url ) );
		return $url;
	}
}

WC_PRL_Ajax::init();
