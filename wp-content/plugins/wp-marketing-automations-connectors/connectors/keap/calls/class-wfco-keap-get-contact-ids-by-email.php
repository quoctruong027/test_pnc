<?php

class WFCO_Keap_Get_Contact_IDs_By_Email extends WFCO_Keap_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'access_token', 'email', 'create_if_not_exists' );
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

		if ( ! is_email( $this->data['email'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Email is not valid' ),
			);
		}

		BWFCO_Keap::set_headers( $this->data['access_token'] );

		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_Keap::get_headers(), BWF_CO::$GET );

		if ( is_array( $res ) && isset( $res['response'] ) && 200 !== absint( $res['response'] ) ) {
			return $res;
		}

		if ( isset( $res['body']['contacts'] ) && ! empty( $res['body']['contacts'] ) ) {
			return array_map( function ( $contact ) {
				return $contact['id'];
			}, $res['body']['contacts'] );
		}

		/**  Create contact if not exists, only if flag = true */
		if ( false === $this->data['create_if_not_exists'] ) {
			return [];
		}

		$user = get_user_by_email( $this->data['email'] );
		$call = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_keap_create_contact' );
		$call->set_data( array(
			'access_token' => $this->data['access_token'],
			'email'        => $this->data['email'],
			'first_name'   => ! empty( $user ) ? get_user_by_email( $this->data['email'] )->first_name : '',
			'last_name'    => ! empty( $user ) ? get_user_by_email( $this->data['email'] )->last_name : '',
		) );

		$create_result = $call->process();
		if ( is_array( $create_result ) && isset( $create_result['response'] ) && 200 !== absint( $create_result['response'] ) ) {
			return $create_result;
		}

		if ( ! isset( $create_result['body']['id'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Unable to get data on Contact creation' ),
			);
		}

		return array( $create_result['body']['id'] );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Keap::get_endpoint() . 'contacts?email=' . $this->data['email'];
	}

}

return 'WFCO_Keap_Get_Contact_IDs_By_Email';
