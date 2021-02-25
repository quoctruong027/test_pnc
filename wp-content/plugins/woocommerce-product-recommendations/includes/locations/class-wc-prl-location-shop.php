<?php
/**
 * WC_PRL_Location_Shop class
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
 * Locations that are used only in cart context.
 *
 * @class    WC_PRL_Location_Shop
 * @version  1.0.0
 */
class WC_PRL_Location_Shop extends WC_PRL_Location {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'shop';
		$this->title = __( 'Shop', 'woocommerce-product-recommendations' );

		$this->defaults = array(
			'engine_type' => array( 'cart' ),
			'priority'    => 10,
			'args_number' => 0
		);

		parent::__construct();
	}

	/**
	 * Check if the current location page is active.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return is_shop();
	}

	/**
	 * @override
	 * Get the supported actions.
	 *
	 * @param  string $context
	 * @return array
	 */
	public function get_hooks( $context = 'edit' ) {

		if ( 'view' === $context ) {

			if ( ! $this->is_active() ) {
				return array();
			}

			$transformed_hooks = array();

			foreach ( $this->hooks as $hook => $data ) {
				// Remove suffix for hooking in the right place.
				$hook = str_replace( '_generic', '', $hook );
				$transformed_hooks[ $hook ] = $data;
			}

			return $transformed_hooks;
		}

		return $this->hooks;
	}

	/**
	 * Setup all supported hooks based on the location id.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		$this->hooks = (array) apply_filters( 'woocommerce_prl_location_shop_actions', array(

			'woocommerce_before_shop_loop_generic'  => array(
				'id'              => 'before_shop_loop_generic',
				'label'           => __( 'Before Products', 'woocommerce-product-recommendations' ),
				'priority'        => 9
			),

			'woocommerce_after_shop_loop_generic'   => array(
				'id'              => 'after_shop_loop_generic',
				'label'           => __( 'After Products', 'woocommerce-product-recommendations' ),
				'priority'        => 100
			),

			'woocommerce_no_products_found_generic' => array(
				'id'              => 'no_products_found_generic',
				'label'           => __( 'No Products Found', 'woocommerce-product-recommendations' ),
				'priority'        => 100
			),

		), $this );
	}
}
