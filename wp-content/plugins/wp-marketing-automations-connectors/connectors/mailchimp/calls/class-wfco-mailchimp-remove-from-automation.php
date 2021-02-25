<?php

class WFCO_Mailchimp_Remove_From_Automation extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'email', 'workflow_id' );
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

		BWFCO_Mailchimp::set_headers( $this->data['api_key'] );
		$params = array( 'email_address' => $this->data['email'] );

		return $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mailchimp::get_headers(), BWF_CO::$POST );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		$data_center = BWFCO_Mailchimp::get_data_center( $this->data['api_key'] );

		return BWFCO_Mailchimp::get_endpoint( $data_center ) . "automations/" . $this->data['workflow_id'] . "/removed-subscribers";
	}

}

return 'WFCO_Mailchimp_Remove_From_Automation';
