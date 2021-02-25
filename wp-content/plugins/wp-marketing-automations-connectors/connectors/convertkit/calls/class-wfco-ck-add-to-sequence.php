<?php

class WFCO_CK_Add_To_Sequence extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'email', 'course_id' );
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

		return $this->add_subscriber_to_sequence();
	}

	public function add_subscriber_to_sequence() {
		$params = array(
			'api_secret' => $this->data['api_secret'],
			'email'      => $this->data['email'],
		);

		if ( isset( $this->data['first_name'] ) && '' !== $this->data['first_name'] ) {
			$params['first_name'] = $this->data['first_name'];
		}
		if ( isset( $this->data['fields'] ) && is_array( $this->data['fields'] ) && count( $this->data['fields'] ) > 0 ) {
			$params['fields'] = (object) $this->data['fields'];
		}
		if ( isset( $this->data['tags'] ) && is_array( $this->data['tags'] ) && count( $this->data['tags'] ) > 0 ) {
			$params['tags'] = $this->data['tags'];
		}
		if ( isset( $this->data['courses'] ) && is_array( $this->data['courses'] ) && count( $this->data['courses'] ) > 0 ) {
			$params['courses'] = $this->data['courses'];
		}

		$url = $this->get_endpoint() . '/' . $this->data['course_id'] . '/subscribe';
		return $this->make_wp_requests( $url, $params, array(), 2 );
	}

	/**
	 * The forms endpoint to fetch all forms.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'courses';
	}

}

/**
 * Register this call class.
 */
return ( 'WFCO_CK_Add_To_Sequence' );
