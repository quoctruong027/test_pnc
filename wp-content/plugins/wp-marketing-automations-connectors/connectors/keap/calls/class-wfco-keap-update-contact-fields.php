<?php

class WFCO_Keap_Update_Contact_Fields extends WFCO_Keap_Call {

	private static $ins = null;

	private $contact_id = null;
	private $site_url = null;

	public function __construct() {

		$this->required_fields = array( 'access_token', 'email' );
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

		if ( ! is_array( $this->data['optional_fields'] ) || ! is_array( $this->data['custom_fields'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Custom fields data is invalid' ),
			);
		}

		$contact_ids = WFCO_Keap_Common::get_contact_ids_by_email( $this->data['access_token'], $this->data['email'], true );
		if ( isset( $contact_ids['response'] ) && ( 200 !== $contact_ids['response'] || 201 !== $contact_ids['response'] ) ) {
			return $contact_ids;
		}

		if ( ! is_array( $contact_ids ) || empty( $contact_ids ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Unable to Create or Get Contact' ),
			);
		}

		BWFCO_Keap::set_headers( $this->data['access_token'] );

		$params = array();

		isset( $this->data['optional_fields'] ) && ! empty( $this->data['optional_fields'] ) ? $params = $this->data['optional_fields'] : false;
		isset( $this->data['custom_fields'] ) && ! empty( $this->data['custom_fields'] ) ? $params['custom_fields'] = $this->data['custom_fields'] : false;

		if ( empty( $params ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'No Custom fields are provided to update' ),
			);
		}

		foreach ( $contact_ids as $id ) {
			$res = $this->make_wp_requests( $this->get_endpoint( $id ), $params, BWFCO_Keap::get_headers(), BWF_CO::$PATCH );
			if ( ! is_array( $res['body'] ) || ( isset( $res['response'] ) && 200 !== absint( $res['response'] ) ) ) {

				$error_message = ( isset( $res['body']['fault'] ) && isset( $res['body']['fault']['faultstring'] ) ) ? '. Error: ' . $res['body']['fault']['faultstring'] : '';
				$error_message = ( empty( $error_message ) && isset( $res['body']['message'] ) ) ? '. Error: ' . $res['body']['message'] : '';

				return array(
					'response' => 502,
					'body'     => array( 'Unable to update custom fields for: "' . $id . '" (Keap Contact ID)' . $error_message ),
				);
			}
		}

		return array(
			'response' => 200,
			'body'     => 'Custom fields updated for all given Contacts',
		);
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint( $contact_id ) {
		return BWFCO_Keap::get_endpoint() . 'contacts/' . absint( $contact_id );
	}

}

return 'WFCO_Keap_Update_Contact_Fields';
