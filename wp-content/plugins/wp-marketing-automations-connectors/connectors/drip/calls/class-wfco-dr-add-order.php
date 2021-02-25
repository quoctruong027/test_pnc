<?php

class WFCO_DR_Add_Order extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'email', 'account_id', 'access_token', 'items', 'billing_address', 'shipping_address' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		BWFCO_Drip::set_headers( $this->data['access_token'] );
		$connector = WFCO_Load_Connectors::get_instance();

		// First add email address drip contact
		$call_instance = $connector->get_call( 'wfco_dr_addsubscribertoaccount' );
		if ( ! is_null( $call_instance ) ) {
			$call_instance->set_data( $this->data );
			$result = $call_instance->process();
			if ( is_array( $result ) && isset( $result['response'] ) && 200 === $result['response'] ) {
				$result = $this->create_order();
			}
		}

		return $result;
	}

	/**
	 * Creates a new order in the drip account and associate with the subscriber.
	 *
	 * subscriber_email is required.
	 * order_id is required.
	 * amount (in cents) is optional.
	 * provider (woocommerce, shopify, etc) is optional.
	 * financial_state (pending, authorized, partially_paid, paid, partially_refunded, refunded, voided) is optional.
	 * order_permalink is optional.
	 *
	 * @return array|mixed|object|string
	 */
	public function create_order() {
		$url = $this->get_endpoint();
		unset( $this->data['access_token'] );
		unset( $this->data['account_id'] );
		unset( $this->data['automation_id'] );
		unset( $this->data['automation_id'] );
		unset( $this->data['occurred_at'] );

		$req_params = wp_json_encode( $this->data );
		$res        = $this->make_wp_requests( $url, $req_params, BWFCO_Drip::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Return the endpoint for creating / updating an order.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Drip::get_endpoint( $this->data['account_id'], 'v3' ) . 'shopper_activity/order';
	}

}

return 'WFCO_DR_Add_Order';
