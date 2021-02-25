<?php
/**
 * WC_PRL_Location_Checkout class
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
 * @class    WC_PRL_Location_Checkout
 * @version  1.3.3
 */
class WC_PRL_Location_Checkout extends WC_PRL_Location {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id        = 'checkout';
		$this->title     = __( 'Checkout', 'woocommerce-product-recommendations' );
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
		return is_checkout();
	}

	/**
	 * Setup all supported hooks based on the location id.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		$this->hooks = (array) apply_filters( 'woocommerce_prl_location_checkout_actions', array(

			'woocommerce_before_checkout_form'         => array(
				'id'              => 'before_checkout',
				'label'           => __( 'Before Checkout Form', 'woocommerce-product-recommendations' )
			),

			'woocommerce_checkout_order_review'        => array(
				'id'              => 'order_review',
				'label'           => __( 'After Place-Order Button', 'woocommerce-product-recommendations' ),
				'priority'        => 1000
			),

			'woocommerce_after_checkout_form'          => array(
				'id'              => 'after_checkout_form',
				'label'           => __( 'After Checkout Form', 'woocommerce-product-recommendations' ),
				'priority'        => 0
			),

		), $this );
	}
}
