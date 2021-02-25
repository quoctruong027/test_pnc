<?php

class WFCO_CK_Create_tags extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'tags' );
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

		BWFCO_ConvertKit::set_headers();

		return $this->add_tags();
	}

	/**
	 * Add tags to account
	 *
	 * @param $tags
	 *
	 * @return array|mixed|object|string
	 */
	public function add_tags() {
		$tags        = $this->data['tags'];
		$final_array = array();

		foreach ( $tags as $single_tag ) {
			$params        = '{ "api_secret": "' . $this->data['api_secret'] . '","tag": {"name": "' . $single_tag . '"} }';
			$url           = $this->get_endpoint();
			$res           = $this->make_wp_requests( $url, $params, BWFCO_ConvertKit::get_headers(), BWF_CO::$POST );
			$final_array[] = $res;
		}

		return $final_array;
	}

	/**
	 * The Tags endpoint.
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
return ( 'WFCO_CK_Create_tags' );
