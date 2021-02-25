<?php
/**
 * WC_PRL_Location_Pay_Page class
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
 * @class    WC_PRL_Location_Pay_Page
 * @version  1.3.3
 */
class WC_PRL_Location_Pay_Page extends WC_PRL_Location {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id        = 'pay_page';
		$this->title     = __( 'Order Pay', 'woocommerce-product-recommendations' );
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
		return is_checkout_pay_page();
	}

	/**
	 * Setup all supported hooks based on the location id.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		$this->hooks = (array) apply_filters( 'woocommerce_prl_location_pay_page_actions', array(

			'woocommerce_pay_order_after_submit'      => array(
				'id'              => 'after_pay_button',
				'label'           => __( 'After Pay Button', 'woocommerce-product-recommendations' ),
				'priority'        => 10
			),

		), $this );
	}
}
