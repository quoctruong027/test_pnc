<?php
/**
 * WC_PRL_Location_Product_Details class
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
 * @class    WC_PRL_Location_Product_Details
 * @version  1.0.0
 */
class WC_PRL_Location_Product_Details extends WC_PRL_Location {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'product_details';
		$this->title = __( 'Product', 'woocommerce-product-recommendations' );

		$this->defaults = array(
			'engine_type' => array( 'product', 'cart' ),
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
		return is_product();
	}

	/**
	 * Setup all supported hooks based on the location id.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		$this->hooks = (array) apply_filters( 'woocommerce_prl_location_product_details_actions', array(

			'woocommerce_after_add_to_cart_form'       => array(
				'id'              => 'after_add_to_cart_form',
				'label'           => __( 'After Add-To-Cart Button', 'woocommerce-product-recommendations' ),
				'priority'        => 1000
			),

			'woocommerce_single_product_summary'       => array(
				'id'              => 'after_summary',
				'label'           => __( 'After Product Meta', 'woocommerce-product-recommendations' ),
				'priority'        => 1000
			),

			'woocommerce_after_single_product_summary' => array(
				'id'              => 'before_tabs',
				'label'           => __( 'Before Tabs', 'woocommerce-product-recommendations' ),
				'priority'        => 0
			),

			'woocommerce_after_single_product'         => array(
				'id'              => 'after_tabs',
				'label'           => __( 'After Tabs', 'woocommerce-product-recommendations' ),
				'priority'        => 0
			),

		), $this );
	}
}
