<?php
/**
 * WC_PRL_Locations class
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
 * Locations Collection class.
 *
 * @class    WC_PRL_Locations
 * @version  1.1.0
 */
class WC_PRL_Locations {

	/**
	 * Locations.
	 *
	 * @var array
	 */
	protected $locations;

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
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'instantiate_locations' ), 9 );

	}

	public function instantiate_locations() {

		// Define available locations classes.
		$load_locations = apply_filters( 'woocommerce_prl_locations', array(
			'WC_PRL_Location_Shop',
			'WC_PRL_Location_Product_Archive',
			'WC_PRL_Location_Product_Details',
			'WC_PRL_Location_Cart_Page',
			'WC_PRL_Location_Checkout',
			'WC_PRL_Location_Order_Received',
			'WC_PRL_Location_Pay_Page'
		) );

		// Instantiate location objects.
		foreach ( $load_locations as $location ) {
			$location = new $location();
			$this->locations[ $location->get_location_id() ] = $location;
		}
	}

	/**
	 * Get location class by id.
	 *
	 * @param  string  $location_id
	 * @return WC_PRL_Location|false
	 */
	public function get_location( $location_id ) {

		if ( ! empty( $this->locations[ $location_id ] ) ) {
			return $this->locations[ $location_id ];
		}

		return false;
	}

	/**
	 * Get the location object based on the hook.
	 *
	 * @param  string $hook
	 * @return mixed
	 */
	public function get_location_by_hook( $hook ) {

		foreach ( $this->locations as $location ) {
			$hooks = $location->get_hooks();
			// Serial search for active location object.
			if ( isset( $hooks[ $hook ] ) ) {

				// Set action property in the singleton on runtime.
				$location->set_current_hook( $hook );

				return $location;
			}
		}

		return false;
	}

	/**
	 * Get location hooks by engine type.
	 *
	 * @param  string  $engine_type
	 * @return array
	 */
	public function get_hooks_for_deployment( $engine_type ) {

		$hooks = array();

		foreach ( $this->locations as $location ) {

			$hooks[ $location->get_location_id() ]            = array();
			$hooks[ $location->get_location_id() ][ 'title' ] = $location->get_title();

			// Serial search for active location object.
			foreach ( $location->get_hooks_by_engine_type( $engine_type ) as $hook => $data ) {
				$hooks[ $location->get_location_id() ][ 'hooks' ][ $hook ] = $data[ 'label' ];
			}
		}

		return $hooks;
	}

	/**
	 * Get the supported locations.
	 *
	 * @param  string $context
	 * @return array
	 */
	public function get_locations( $context = 'edit' ) {
		return $this->locations;
	}

	/**
	 * Parse the source data.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $engine_type
	 * @param  array  $args
	 * @return array
	 */
	public function parse_source( $engine_type, $args = array() ) {

		$source_data = array();

		switch ( $engine_type ) {

			/*---------------------------------------------------*/
			/*  Type: Product                                    */
			/*  Source data: Product ID                          */
			/*  Source data format: [ current_product_id ]       */
			/*---------------------------------------------------*/
			case 'product':

				global $product;

				if ( isset( $product ) && $product instanceof WC_Product ) {
					$source_data[] = $product->get_id();
				}

				break;

			/*-----------------------------------------------------------*/
			/*  Type: Archive                                            */
			/*  Source data: Complex                                     */
			/*  Source data format: [ [ 'tag' => '%d', 'cat' => '%d' ] ] */
			/*-----------------------------------------------------------*/
			case 'archive':

				$source = get_queried_object();

				if ( $source instanceof WP_Term ) {
					if ( 'product_tag' === $source->taxonomy ) {
						$source_data[] = array(
							'tag' => $source->term_id,
							'cat' => null
						);
					} elseif ( 'product_cat' === $source->taxonomy ) {
						$source_data[] = array(
							'tag' => null,
							'cat' => $source->term_id
						);
					}
				}

				break;

			/*---------------------------------------------------*/
			/*  Type: Cart                                       */
			/*  Source data: Product IDs                         */
			/*  Source data format: [ p1_id, p2_id ... pN_id ]   */
			/*---------------------------------------------------*/
			case 'cart':

				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$source_data[] = $cart_item[ 'product_id' ];
				}

				break;

			/*---------------------------------------------------*/
			/*  Type: Order                                      */
			/*  Source data: Current order id                    */
			/*  Source data format: [ current_order_id ]         */
			/*---------------------------------------------------*/
			case 'order':

				global $wp;

				// Get the order ID
				if ( ! empty( $wp->query_vars[ 'order-received' ] ) ) {
					$source_data[] = absint( $wp->query_vars[ 'order-received' ] );
				} elseif ( ! empty( $wp->query_vars[ 'order-pay' ] ) ) {
					$source_data[] = absint( $wp->query_vars[ 'order-pay' ] );
				}

				break;
		}

		return $source_data;
	}

	/**
	 * Extract environment.
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	public function get_environment() {

		$environment = array();

		// Product.
		$product_source = $this->parse_source( 'product' );
		if ( ! empty( $product_source ) ) {
			$environment[ 'product' ] = array_pop( $product_source );
		}

		// Archive.
		$archive_source = $this->parse_source( 'archive' );
		if ( ! empty( $archive_source ) ) {
			$environment[ 'archive' ] = array_pop( $archive_source );
		}

		// Order.
		$order_source = $this->parse_source( 'order' );
		if ( ! empty( $order_source ) ) {
			$environment[ 'order' ] = array_pop( $order_source );
		}

		return $environment;
	}

	/**
	 * Setup environment.
	 *
	 * @since 1.1.0
	 *
	 * @param  array $posted
	 * @return void
	 */
	public function setup_environment( $posted ) {

		if ( ! empty( $posted[ 'product' ] ) ) {
			$post_object = get_post( (int)$posted[ 'product' ] );
			setup_postdata( $GLOBALS[ 'post' ] =& $post_object );
		}

		if ( ! empty( $posted[ 'order' ] ) ) {

			// Make WC to think it's an `order-received` page.
			global $wp;
			add_filter( 'woocommerce_is_order_received_page', '__return_true' );
			$wp->query_vars[ 'order-received' ] = (int)$posted[ 'order' ];
		}

		if ( ! empty( $posted[ 'archive' ] ) ) {

			if ( ! is_array( $posted[ 'archive' ] ) ) {
				return;
			}

			// Make WC to think it's an archive page.
			$args                = array();
			$args[ 'post_type' ] = 'product';

			if ( ! empty( $posted[ 'archive' ][ 'cat' ] ) ) {
				$args[ 'tax_query' ] = array(
					array (
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => (int)$posted[ 'archive' ][ 'cat' ]
					)
				);
			} else if ( ! empty( $posted[ 'archive' ][ 'tag' ] ) ) {
				$args[ 'tax_query' ] = array(
					array (
						'taxonomy' => 'product_tag',
						'field'    => 'id',
						'terms'    => (int)$posted[ 'archive' ][ 'tag' ]
					)
				);
			}

			$new_query = new WP_Query( $args );
			global $wp_query;
			$wp_query = $new_query;
		}
	}
}
