<?php

class WFCO_DR_Deletewebhook extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'webhook_id', 'account_id', 'access_token' );
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

		return $this->delete_webhook();
	}

	/**
	 * Remove a webhook from drip account.
	 *
	 * @param $webhook_id
	 *
	 * @return array|mixed|object|string
	 */
	public function delete_webhook() {
		$params = array();
		$url    = $this->get_endpoint() . '/' . $this->data['webhook_id'];
		$res    = $this->make_wp_requests( $url, $params, BWFCO_Drip::get_headers(), BWF_CO::$DELETE );

		if ( is_array( $res ) && isset( $res['response'] ) && 204 === $res['response'] ) {
			$data = array(
				'response' => $res['response'],
				'message'  => 'Webhook Successfully Deleted',
			);
		} else {
			$data = $res;
		}

		return $data;
	}

	/**
	 * Return the endpoint for creating / updating an order.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Drip::get_endpoint( $this->data['account_id'] ) . 'webhooks';
	}

}

return 'WFCO_DR_Deletewebhook';
