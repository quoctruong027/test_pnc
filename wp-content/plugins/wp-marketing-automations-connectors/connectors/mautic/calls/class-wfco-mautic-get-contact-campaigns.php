<?php

class WFCO_Mautic_Get_Contact_Campaigns extends WFCO_Mautic_Call {

	private static $ins = null;

	private $contact_id = null;
	private $points = 0;

	public function __construct() {

		$this->required_fields = array( 'site_url', 'access_token', 'email' );
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

		$contact = WFCO_Mautic_Common::get_contact_id_by_email( $this->data['access_token'], $this->data['site_url'], $this->data['email'], false );

		// Error in getting contact
		if ( is_array( $contact ) && isset( $contact['response'] ) && 200 !== absint( $contact['response'] ) ) {
			return $contact;
		}

		//If no contact found
		if ( empty( $contact ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Contact not found' ),
			);
		}

		$this->contact_id = $contact;

		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_Mautic::get_headers(), BWF_CO::$GET );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->data['site_url'] . '/api/contacts/' . $this->contact_id . '/campaigns?access_token=' . $this->data['access_token'];
	}

}

return 'WFCO_Mautic_Get_Contact_Campaigns';
