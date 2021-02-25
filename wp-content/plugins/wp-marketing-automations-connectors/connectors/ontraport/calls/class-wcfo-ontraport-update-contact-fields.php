<?php

class WFCO_Ontraport_Update_Contact_Fields extends WFCO_Call {

	private static $ins = null;

	private $contact_id = null;
	private $site_url = null;

	public function __construct() {

		$this->required_fields = array( 'app_id', 'api_key', 'email' );
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

		$contact = WFCO_Ontraport_Common::get_contact_id_by_email( $this->data['app_id'], $this->data['api_key'], $this->data['email'], true );

		// Error in getting contact
		if ( is_array( $contact ) && isset( $contact['response'] ) && 200 !== absint( $contact['response'] ) ) {
			return $contact;
		}

		$this->contact_id = $contact;
		BWFCO_Ontraport::set_headers( $this->data );
		return $this->make_wp_requests( $this->get_endpoint(), $this->data, BWFCO_Ontraport::get_headers(), BWF_CO::$POST );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Ontraport::get_endpoint() . '/Contacts?id=' . $this->contact_id;
	}

}

return 'WFCO_Ontraport_Update_Contact_Fields';
