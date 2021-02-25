<?php

class WFCO_GR_Remove_From_List extends WFCO_Call {

	private static $ins = null;
	private $contact_id = false;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'email', 'list_id' );
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

		$contacts = WFCO_GetResponse_Common::get_contacts_by_email( $this->data['api_key'], $this->data['email'] );
		if ( ! is_array( $contacts['body'] ) || isset( $contacts['body']['code'] ) || ( isset( $contacts['response'] ) && 200 !== absint( $contacts['response'] ) ) ) {
			return $contacts;
		}

		foreach ( $contacts['body'] as $contact ) {
			if ( strval( $contact['campaign']['campaignId'] ) !== strval( $this->data['list_id'] ) ) {
				continue;
			}

			$this->contact_id = $contact['contactId'];
			break;
		}

		if ( false === $this->contact_id ) {
			return array(
				'response' => 502,
				'body'     => array( 'Contact doesn\'t exists in selected list.' ),
			);
		}

		if ( count( $contacts['body'] ) === 1 ) {
			return array(
				'response' => 502,
				'body'     => array( 'Autonami will not delete contact, as it belongs to only one list' ),
			);
		}

		BWFCO_GetResponse::set_headers( $this->data['api_key'] );
		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_GetResponse::get_headers(), BWF_CO::$DELETE );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_GetResponse::get_endpoint() . 'contacts/' . $this->contact_id;
	}

}

return 'WFCO_GR_Remove_From_List';
