<?php
/**
 * WC_PRL_Tracking class
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
 * Tracking class.
 *
 * @class    WC_PRL_Tracking
 * @version  1.4.4
 */
class WC_PRL_Tracking {

	/**
	 * Init.
	 *
	 * @return void
	 */
	public static function init() {

		// Actions.
		add_action( 'init', array( __CLASS__, 'track_clicks' ), 1 );

		// Filters.
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'add_long_term_conversion' ) );
		add_filter( 'woocommerce_post_class', array( __CLASS__, 'add_taxonomy_classes' ), 10, 2 );
	}

	/**
	 * Tracks the clicks of a product based on the GET params.
	 *
	 * @return void
	 */
	public static function track_clicks() {

		if ( ! wc_prl_tracking_enabled() ) {
			return;
		}

		if ( isset( $_REQUEST[ 'prl_track' ] ) && ! empty( $_REQUEST[ 'prl_track' ] ) ) {

			$track_param = wc_clean( $_REQUEST[ 'prl_track' ] );

			// Send click event to db.
			try {
				self::maybe_add_click_event( $track_param );
			} catch ( Exception $e ) {
				if ( $e->getMessage() ) {
					WC_PRL()->log( 'Click Event: ' . $e->getMessage(), 'notice', 'wc_prl' );
				}
			}

			// Do a redirect only when a "normal" click occured.
			if ( ! defined( 'DOING_AJAX' ) && ! isset( $_REQUEST[ 'add-to-cart' ] ) ) {

				// Remove tracking arg and redirect anyway.
				$url = remove_query_arg( 'prl_track' );
				wp_redirect( $url );
				exit;
			}
		}
	}

	/**
	 * Checks if a click event exists in the current session, if not writes in the db.
	 *
	 * @since 1.1.0
	 *
	 * @param  string  $track_param
	 * @return void
	 *
	 * @throws Exception
	 */
	public static function maybe_add_click_event( $track_param ) {

		// Extract information from track param.
		$track = explode( '_', $track_param );

		// If click does not exist in the current session, write db.
		if ( self::update_clicks_cookie( $track[ 0 ], $track[ 3 ], isset( $track[ 4 ] ) ? $track[ 4 ] : '' ) ) {

			$args = array(
				'deployment_id' => $track[ 0 ],
				'engine_id'     => $track[ 1 ],
				'location_hash' => $track[ 2 ],
				'product_id'    => $track[ 3 ]
			);

			if ( isset( $track[ 4 ] ) ) {
				$args[ 'source_hash' ] = $track[ 4 ];
			}

			WC_PRL()->db->tracking->add_click_event( $args );
		}
	}

	/**
	 * Handles the long-term conversion after inserting into cart from a deployment.
	 *
	 * @param  array  $cart_item_data
	 * @return array
	 */
	public static function add_long_term_conversion( $cart_item_data ) {

		if ( ! wc_prl_tracking_enabled() ) {
			return $cart_item_data;
		}

		// If the add-to-cart action is from a deployment, mark it as a long term contruct.
		if ( isset( $_REQUEST[ 'prl_track' ] ) && ! empty( $_REQUEST[ 'prl_track' ] ) ) {

			$track                                    = explode( '_', wc_clean( $_REQUEST[ 'prl_track' ] ) );
			$cart_item_data[ '_prl_conversion' ]      = absint( $track[ 0 ] ); // Deployment ID.
			$cart_item_data[ '_prl_conversion_time' ] = time();

			if ( isset( $track[ 4 ] ) ) {
				$cart_item_data[ '_prl_conversion_source_hash' ] = $track[ 4 ];
			}

		} else {

			// Get product id.
			$product_id          = $cart_item_data[ 'product_id' ];

			// Search in local cookie.
			$cookie              = 'wc_prl_deployments_clicked';
			$product_ids_clicked = isset( $_COOKIE[ $cookie ] ) && ! empty( $_COOKIE[ $cookie ] ) ? explode( ',', $_COOKIE[ $cookie ] ) : array();

			foreach ( $product_ids_clicked as $event ) {
				$attrs = explode( '_', $event );

				if ( isset( $attrs[ 1 ] ) && $product_id === absint( $attrs[ 1 ] ) ) {
					$cart_item_data[ '_prl_conversion' ]      = absint( $attrs[ 0 ] ); // Deployment ID.
					$cart_item_data[ '_prl_conversion_time' ] = time();

					if ( isset( $attrs[ 2 ] ) ) {
						$cart_item_data[ '_prl_conversion_source_hash' ] = $attrs[ 2 ];
					}

					break;
				}
			}
		}

		return $cart_item_data;
	}

	/**
	 * Updates the cookie value for customer-related clicks. Returns true if the cookie has been updated.
	 *
	 * @param  int  $deployment_id
	 * @param  int  $product_id
	 * @param  string  $source_hash
	 * @return bool
	 */
	private static function update_clicks_cookie( $deployment_id, $product_id, $source_hash ) {

		if ( ! wc_prl_tracking_enabled() ) {
			return;
		}

		if ( empty( $deployment_id ) || empty( $product_id ) ) {
			return;
		}

		// Cookie info.
		$cookie              = 'wc_prl_deployments_clicked';
		$shopping_session    = wc_prl_get_shopping_session_interval();
		$updated             = false;

		// Get all customer clicks.
		$product_ids_clicked = isset( $_COOKIE[ $cookie ] ) && ! empty( $_COOKIE[ $cookie ] ) ? explode( ',', $_COOKIE[ $cookie ] ) : array();

		// Set new event value.
		$value               = $deployment_id . '_' . $product_id;
		if ( ! empty( $source_hash ) ) {
			$value .= '_' . $source_hash;
		}

		// Did customer has already clicked this product?
		if ( ! in_array( $value, $product_ids_clicked ) ) {

			$max_num_allowed = wc_prl_get_clicks_max_cookie_num();

			if ( count( $product_ids_clicked ) > $max_num_allowed - 1 ) {
				array_shift( $product_ids_clicked );
			}

			$product_ids_clicked[] = $value;
			setcookie( $cookie, implode( ',', $product_ids_clicked ), time() + $shopping_session, '/' );
			$updated = true;
		}

		return $updated;
	}

	/**
	 * Add taxonomy specific classes on the product dom container.
	 *
	 * @since  1.2.0
	 *
	 * @param  array       $classes
     * @param  WC_Product  $product
	 * @return void
	 */
	public static function add_taxonomy_classes( $classes, $product ) {

		if ( ! ( $product instanceof WC_Product ) ) {
			return;
		}

		$categories = $product->get_category_ids();
		if ( ! empty( $categories ) ) {
			$classes[] = 'wc-prl-cat-' . implode( '-', $categories );
		}

		$tags = $product->get_tag_ids();
		if ( ! empty( $tags ) ) {
			$classes[] = 'wc-prl-tag-' . implode( '-', $tags );
		}

		return $classes;
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public static function track_product_views( $product_id ) {
		_deprecated_function( __METHOD__ . '()', '1.2.0' );
	}

	public static function update_recently_viewed_cookie( $product_id ) {
		_deprecated_function( __METHOD__ . '()', '1.2.0' );
	}
}

WC_PRL_Tracking::init();
