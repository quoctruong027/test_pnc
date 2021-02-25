<?php

class WFCO_DR_Create_Subscriber extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'account_id', 'access_token', 'email' );
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

		return $this->create_subscriber();
	}

	/**
	 * Create a Subscriber
	 *
	 * @return array|mixed|object|string
	 */
	public function create_subscriber() {
		$url = $this->get_endpoint();

		if ( ! is_email( $this->data['email'] ) ) {
			return [
				'status'  => 4,
				'message' => __( 'Email is not valid', 'wp-marketing-automations' ),
			];
		}

		$params = array(
			'email' => $this->data['email'],
		);

		if ( isset( $this->data['first_name'] ) && ! empty( $this->data['first_name'] ) ) {
			$params['first_name'] = $this->data['first_name'];
		} else if ( isset( $this->data['last_name'] ) && ! empty( $this->data['last_name'] ) ) {
			$params['last_name'] = $this->data['last_name'];
		}

		$req_params = array(
			'subscribers' => array( $params ),
		);
		$res        = $this->make_wp_requests( $url, wp_json_encode( $req_params ), BWFCO_Drip::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Return the endpoint for creating / updating an subscriber.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Drip::get_endpoint( $this->data['account_id'] ) . 'subscribers';
	}

}

return 'WFCO_DR_Create_Subscriber';
