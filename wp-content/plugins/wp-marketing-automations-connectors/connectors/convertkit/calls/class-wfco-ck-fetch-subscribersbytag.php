<?php

class WFCO_Ck_Fetchsubcribersbytag extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'tag_id' );
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

		return $this->fetch_contacts();
	}

	/**
	 * Fetch contacts by tag_id from the account.
	 *
	 * email, api_key, tag_id are required required.
	 *
	 * @return array|bool
	 * @throws Exception
	 */
	public function fetch_contacts() {
		$params = array(
			'api_secret' => $this->data['api_secret'],
		);

		$url = $this->get_endpoint() . '/' . $this->data['tag_id'] . '/subscriptions';
		$res = $this->make_wp_requests( $url, $params );

		return $res;
	}

	/**
	 * Endpoint for getting contacts.
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
return ( 'WFCO_Ck_Fetchsubcribersbytag' );
