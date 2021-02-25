<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFAN_WC_Cart_AB_Email_Series_Without_Coupon extends BWFAN_Recipes {
	private static $instance = null;

	public function __construct() {
		$settings                             = $this->get_settings();
		$this->data['name']                   = __( 'Cart abandonment email series without coupon', 'wp-marketing-automations' );
		$this->data['description']            = __( 'A 2-part email sequence to make prospects return to the store to complete their purchase.', 'wp-marketing-automations' );
		$this->data['data-dependencies']      = array(
			array(
				'operator'      => '=',
				'current_value' => isset( $settings['bwfan_ab_enable'] ) ? $settings['bwfan_ab_enable'] : '',
				'check_value'   => '1',
				'message'       => __( 'Cart tracking is not enabled.', 'wp-marketing-automations' ),
			),
		);
		$this->data['plugin-dependencies']    = array( 'woocommerce' );
		$this->data['connector-dependencies'] = array();
		$this->data['json']                   = array( 'cart_abandonment_email_series_without_coupon' );
		$this->data['connector-filter']       = array();
		$this->data['plugin-filter']          = array( 'wc' );
		$this->data['priority']               = 100;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

/**
 * Register this merge tag to a group.
 */
BWFAN_Recipe_Loader::register( 'BWFAN_WC_Cart_AB_Email_Series_Without_Coupon' );
