<?php
/**
 * WC_PRL_Location_Order_Received class
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
 * @class    WC_PRL_Location_Order_Received
 * @version  1.3.3
 */
class WC_PRL_Location_Order_Received extends WC_PRL_Location {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id        = 'order_received';
		$this->title     = __( 'Order Received', 'woocommerce-product-recommendations' );
		$this->cacheable = false;

		$this->defaults = array(
			'engine_type' => array( 'order' ),
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
		return is_order_received_page();
	}

	/**
	 * Setup all supported hooks based on the location id.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		$this->hooks = (array) apply_filters( 'woocommerce_prl_location_order_received_actions', array(

			'woocommerce_order_details_before_order_table'     => array(
				'id'              => 'before_order_details',
				'label'           => __( 'Before Order Details', 'woocommerce-product-recommendations' ),
				'priority'        => 10
			),

			'woocommerce_order_details_after_order_table'      => array(
				'id'              => 'after_order_details',
				'label'           => __( 'After Order Details', 'woocommerce-product-recommendations' ),
				'priority'        => 10
			),

			'woocommerce_order_details_after_customer_details' => array(
				'id'              => 'after_customer_details',
				'label'           => __( 'After Customer Details', 'woocommerce-product-recommendations' ),
				'priority'        => 10
			),

		), $this );
	}
}
