<?php

class WFCO_Mailchimp_Remove_Tags extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'email', 'list_id', 'tags' );
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

		if ( ! is_array( $this->data['tags'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Tags data is invalid' ),
			);
		}

		/** Get or Create Contact */
		$contact_response = WFCO_Mailchimp_Common::get_contact( $this->data['api_key'], $this->data['list_id'], $this->data['email'] );
		if ( 200 !== absint( $contact_response['response'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Unable to Create or Get Contact' ),
			);
		}

		BWFCO_Mailchimp::set_headers( $this->data['api_key'] );

		$params         = array();
		$params['tags'] = array_map( function ( $tag ) {
			return array(
				'name'   => $tag,
				'status' => 'inactive'
			);
		}, $this->data['tags'] );

		return $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mailchimp::get_headers(), BWF_CO::$POST );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		$data_center = BWFCO_Mailchimp::get_data_center( $this->data['api_key'] );

		return BWFCO_Mailchimp::get_endpoint( $data_center ) . 'lists/' . $this->data['list_id'] . '/members/' . md5( $this->data['email'] ) . '/tags';
	}

}

return 'WFCO_Mailchimp_Remove_Tags';
