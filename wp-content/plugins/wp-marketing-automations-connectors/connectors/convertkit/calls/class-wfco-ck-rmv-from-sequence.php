<?php

class WFCO_CK_Rmv_From_Sequence extends WFCO_Call {

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
		$connectors = WFCO_Load_Connectors::get_instance();
		// First check if subscriber is created by fetching subscriber details by email.
		$get_subscriber = $connectors->get_call( 'wfco_ck_get_subscriber' );
		$get_subscriber->set_data( $this->data );
		$result = $get_subscriber->process();

		if ( ! is_array( $result ) ) {
			return $this->remove_subscriber();
		}

		return $result;
	}

	/**
	 * Remove the subscriber
	 *
	 * subscriber email.
	 *
	 * @return array|mixed|null|object|string
	 */
	public function remove_subscriber() {
		$params = array(
			'api_secret' => $this->data['api_secret'],
			'email'      => $this->data['email'],
		);

		$url = $this->get_endpoint();
		$res = $this->make_wp_requests( $url, $params, array(), BWF_CO::$PUT );

		return $res;
	}

	/**
	 * The endpoint to remove subscriber.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'unsubscribe';
	}

}

/**
 * Register this call class.
 */
return ( 'WFCO_CK_Rmv_From_Sequence' );
