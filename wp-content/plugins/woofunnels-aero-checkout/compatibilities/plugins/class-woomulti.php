<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_WooMulti {

	public $instance = null;

	/**
	 * @var WOOMULTI_CURRENCY_Frontend_Price
	 */

	public function __construct() {

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'action' ] );
		//add_filter( 'wfacp_product_raw_data', [ $this, 'wfacp_product_raw_data' ], 10, 2 );
	}


	public function action() {
		$this->instance = WFACP_Common::remove_actions( 'wp_footer', 'WOOMULTI_CURRENCY_Frontend_Design', 'show_action' );
		if ( ! is_null( $this->instance ) && is_object( $this->instance ) && $this->instance instanceof WOOMULTI_CURRENCY_Frontend_Design ) {
			add_action( 'wfacp_footer_before_print_scripts', array( $this->instance, 'show_action' ) );
		}

	}

	public function is_enable() {
		if ( class_exists( 'WOOMULTI_CURRENCY_Frontend_Design' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $raw_data
	 * @param $product WC_Product;
	 *
	 * @return mixed
	 */
	public function wfacp_product_raw_data( $raw_data, $product ) {

		if ( false == $this->is_enable() ) {

			return $raw_data;
		}

		$settings = new WOOMULTI_CURRENCY_Data();

		$current_currency   = $settings->get_current_currency();
		$product_id         = $product->get_id();
		$regular_price_wmcp = json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true );
		$sale_price_wmcp    = json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true );
		if ( isset( $regular_price_wmcp[ $current_currency ] ) ) {
			if ( $regular_price_wmcp[ $current_currency ] > 0 ) {

				$raw_data['regular_price'] = $regular_price_wmcp[ $current_currency ];
				if ( $raw_data['regular_price'] > 0 ) {
					$sale_price = $sale_price_wmcp[ $current_currency ];
					if ( $sale_price > 0 ) {
						$raw_data['price']      = $sale_price;
						$raw_data['sale_price'] = $sale_price;
					} else {
						$raw_data['price'] = $raw_data['regular_price'];
					}
				}
			}
		}

		return $raw_data;
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WooMulti(), 'WooMulti' );
