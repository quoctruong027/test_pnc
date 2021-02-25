<?php
/**
 * WC_PRL_Location_Cart_Page class
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
 * @class    WC_PRL_Location_Cart_Page
 * @version  1.3.3
 */
class WC_PRL_Location_Cart_Page extends WC_PRL_Location {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id        = 'cart_page';
		$this->title     = __( 'Cart', 'woocommerce-product-recommendations' );
		$this->cacheable = false;

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
		return is_cart();
	}

	/**
	 * Setup all supported hooks based on the location id.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		$this->hooks = (array) apply_filters( 'woocommerce_prl_location_cart_actions', array(

			'woocommerce_before_cart'         => array(
				'id'              => 'before_cart',
				'label'           => __( 'Before Cart Table', 'woocommerce-product-recommendations' ),
				'priority'        => 10,
			),

			'woocommerce_after_cart_table'     => array(
				'id'              => 'before_cart',
				'label'           => __( 'After Cart Table', 'woocommerce-product-recommendations' ),
			),

			'woocommerce_cart_collaterals'     => array(
				'id'              => 'cart_collaterals',
				'label'           => __( 'Cart Collaterals', 'woocommerce-product-recommendations' ),
				'class'           => array( 'cross-sells', 'wc-prl-recommendations--no-clear' ),
			),

			'woocommerce_after_cart'           => array(
				'id'              => 'after_cart',
				'label'           => __( 'After Cart Totals', 'woocommerce-product-recommendations' ),
			),

			'woocommerce_cart_is_empty'         => array(
				'id'              => 'empty_cart',
				'label'           => __( 'Empty Cart', 'woocommerce-product-recommendations' ),
				'priority'        => 20,
			)

		), $this );
	}
}
