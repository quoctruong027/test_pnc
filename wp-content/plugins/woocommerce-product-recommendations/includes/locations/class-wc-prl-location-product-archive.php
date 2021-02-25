<?php
/**
 * WC_PRL_Location_Product_Archive class
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
 * @class    WC_PRL_Location_Product_Archive
 * @version  1.0.0
 */
class WC_PRL_Location_Product_Archive extends WC_PRL_Location {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'product_archive';
		$this->title = __( 'Product Archive', 'woocommerce-product-recommendations' );

		$this->defaults = array(
			'engine_type' => array( 'archive', 'cart' ),
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
		return is_product_category() || is_product_tag();
	}

	/**
	 * Setup all supported hooks based on the location id.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		$this->hooks = (array) apply_filters( 'woocommerce_prl_location_product_archive_actions', array(

			'woocommerce_before_shop_loop'  => array(
				'id'              => 'before_shop_loop',
				'label'           => __( 'Before Products', 'woocommerce-product-recommendations' ),
				'priority'        => 9
			),

			'woocommerce_after_shop_loop'   => array(
				'id'              => 'after_shop_loop',
				'label'           => __( 'After Products', 'woocommerce-product-recommendations' ),
				'priority'        => 100
			),

			'woocommerce_no_products_found' => array(
				'id'              => 'no_products_found',
				'label'           => __( 'No Products Found', 'woocommerce-product-recommendations' ),
				'priority'        => 100
			),

		), $this );
	}
}
