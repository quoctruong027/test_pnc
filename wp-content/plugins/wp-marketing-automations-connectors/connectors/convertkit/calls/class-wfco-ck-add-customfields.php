<?php

class WFCO_CK_Add_CustomFields extends WFCO_Call {

	private static $ins = null;
	public $default_fields = array(
		'first_name' => 'First Name',
	);

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'custom_fields', 'email' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		return $this->update_customfields_subscriber();
	}

	public function update_customfields_subscriber() {
		$params = array(
			'api_secret'    => $this->data['api_secret'],
			'email_address' => $this->data['email'],
			'fields'        => $this->data['custom_fields'],
		);

		/** check if default fields are present in the set data */
		$default_fields = $this->default_fields;
		foreach ( $this->data['custom_fields'] as $key1 => $value1 ) {
			if ( isset( $default_fields[ $key1 ] ) ) {
				$params[ $key1 ] = $value1;
			}
		}

		$connector      = WFCO_Load_Connectors::get_instance();
		$get_subscriber = $connector->get_call( 'wfco_ck_get_subscriber' );

		$get_subscriber->set_data( $this->data );
		$subscriber_id = $get_subscriber->process();
		if ( is_array( $subscriber_id ) ) {
			return $subscriber_id;
		}

		$url = $this->get_endpoint() . 'subscribers/' . $subscriber_id;
		$res = $this->make_wp_requests( $url, $params, BWFCO_ConvertKit::get_headers(), BWF_CO::$PUT );

		return $res;
	}

	/**
	 * The sequences endpoint to insert a contact into sequence.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint();
	}

}

/**
 * Register this call class.
 */
return ( 'WFCO_CK_Add_CustomFields' );
