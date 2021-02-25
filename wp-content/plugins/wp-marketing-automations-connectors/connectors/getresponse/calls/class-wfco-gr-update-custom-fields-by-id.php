<?php

class WFCO_GR_Update_Custom_Fields_By_Id extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'api_key', 'contact_id', 'custom_fields' );
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

		if ( ! is_array( $this->data['custom_fields'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Custom Fields data is invalid' ),
			);
		}

		$params = array( 'customFieldValues' => array() );
		foreach ( $this->data['custom_fields'] as $field => $value ) {
			$params['customFieldValues'][] = array(
				'customFieldId' => $field,
				'value'         => [ $value ]
			);
		}

		BWFCO_GetResponse::set_headers( $this->data['api_key'] );

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_GetResponse::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_GetResponse::get_endpoint() . 'contacts/' . $this->data['contact_id'] . '/custom-fields';
	}

}

return 'WFCO_GR_Update_Custom_Fields_By_Id';
