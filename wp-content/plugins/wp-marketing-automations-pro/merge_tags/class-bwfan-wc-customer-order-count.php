<?php

class BWFAN_WC_Customer_Order_Count extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'customer_order_count';
		$this->need_order_sync = true;
		$this->tag_description = __( 'Customer Order Count', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_customer_order_count', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		$get_data = BWFAN_Merge_Tag_Loader::get_data();
		if ( true === $get_data['is_preview'] ) {
			return $this->get_dummy_preview();
		}

		$user_id     = 0;
		$email       = '';
		$order_count = 0;

		// Get user ID and Email
		if ( isset( $get_data['user_id'] ) ) {
			$user_id = $get_data['user_id'];
		}
		if ( isset( $get_data['email'] ) ) {
			$email = $get_data['email'];
		}
		if ( ! $user_id || ! $email ) {
			$order = null;
			if ( isset( $get_data['wc_order'] ) ) {
				$order = $get_data['wc_order'];
			}
			if ( ! $order instanceof WC_Order && isset( $get_data['order_id'] ) ) {
				$order = wc_get_order( $get_data['order_id'] );
			}
			if ( $order instanceof WC_Order ) {
				if ( ! $user_id ) {
					$user_id = $order->get_user_id();
				}
				if ( ! $email ) {
					$email = $order->get_billing_email();
				}
			}
		}

		$customer = BWFAN_Common::get_bwf_customer( $email, $user_id );

		if ( $customer ) {
			$order_count = $customer->get_total_order_count();
		}

		return $this->parse_shortcode_output( $order_count, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 4;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_Order_Count' );
}
