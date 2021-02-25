<?php

class WFCO_KEAP_Update_Tags_By_Id extends WFCO_Keap_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'access_token', 'contact_ids', 'tags' );
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

		if ( ! is_array( $this->data['tags'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Tags data is invalid' ),
			);
		}

		if ( ! is_array( $this->data['contact_ids'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Contacts data is invalid' ),
			);
		}

		$params = array( 'tagIds' => $this->data['tags'] );

		BWFCO_Keap::set_headers( $this->data['access_token'] );

		foreach ( $this->data['contact_ids'] as $contact_id ) {
			$res = $this->make_wp_requests( $this->get_endpoint( $contact_id ), $params, BWFCO_Keap::get_headers(), BWF_CO::$POST );
			if ( ! is_array( $res['body'] ) || ( isset( $res['response'] ) && 200 !== absint( $res['response'] ) ) ) {
				return array(
					'response' => 502,
					'body'     => array( 'Unable to apply tags to: "' . $contact_id . '" (Keap Contact ID)' . ( isset( $res['body']['fault'] ) && isset( $res['body']['fault']['faultstring'] ) ? '. Error: ' . $res['body']['fault']['faultstring'] : '' ) ),
				);
			}
		}

		return array(
			'response' => 200,
			'body'     => 'Tags Applied to all given Contacts',
		);
	}

	/**
	 * Return the endpoint.
	 *
	 * @param $contact_id int
	 *
	 * @return string
	 */
	public function get_endpoint( $contact_id ) {
		return BWFCO_Keap::get_endpoint() . 'contacts/' . absint( $contact_id ) . '/tags';
	}

}

return 'WFCO_KEAP_Update_Tags_By_Id';
