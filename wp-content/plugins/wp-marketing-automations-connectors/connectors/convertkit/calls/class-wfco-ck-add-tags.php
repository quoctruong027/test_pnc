<?php

class WFCO_CK_Add_Tags extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'email', 'tags' );
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

		return $this->add_tag_to_subscriber();
	}

	public function add_tag_to_subscriber() {
		$params       = array(
			'api_secret' => $this->data['api_secret'],
			'email'      => $this->data['email'],
		);
		$final_result = array();
		foreach ( $this->data['tags'] as $tag_id ) {
			$url                     = $this->get_endpoint() . '/' . $tag_id . '/subscribe';
			$res                     = $this->make_wp_requests( $url, $params, array(), BWF_CO::$POST );
			$final_result[ $tag_id ] = $res;
		}

		return $final_result;
	}

	/**
	 * The endpoint to add tag to subscriber.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'tags';
	}

}

/**
 * Register this call class.
 */
return ( 'WFCO_CK_Add_Tags' );
