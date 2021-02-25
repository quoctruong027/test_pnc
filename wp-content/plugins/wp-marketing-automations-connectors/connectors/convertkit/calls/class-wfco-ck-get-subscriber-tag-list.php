<?php

class WFCO_CK_Get_Subscriber_Tag_List extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'subscriber_id' );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
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

		return $this->get_contact_tag_list();
	}

	/**
	 * View tags associated with contact.
	 *
	 * @return array|mixed
	 */
	public function get_contact_tag_list() {
		$endpoint_url = $this->get_endpoint( $this->data['api_secret'], $this->data['subscriber_id']);
		$result       = $this->make_wp_requests( $endpoint_url, array(), array(), BWF_CO::$GET );
		if ( is_array( $result['body']['tags'] ) && count( $result['body']['tags']) > 0 ) {
			$result['body'] = $result['body']['tags'];
		}

		return $result;
	}

	public function get_endpoint( $api_secret,$subscriber_id) {
		return BWFCO_ConvertKit::get_endpoint().'subscribers/'.$subscriber_id.'/tags?api_secret='.$api_secret;
	}

}

return 'WFCO_CK_Get_Subscriber_Tag_List';
