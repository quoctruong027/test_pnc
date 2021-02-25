<?php

class WFCO_Ontraport_Search_Tag_By_Name extends WFCO_Call {

	private static $instance = null;
  private $contact_id = null;
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->required_fields = array( 'app_id', 'api_key', 'email', 'tag' );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return WFCO_Ontraport_Search_Tag_By_Name|null
	 */
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

		BWFCO_Ontraport::set_headers( $this->data );

		return $this->search_tag();
	}

	/**
	 * Remove tag from a contact.
	 *
	 * @return array|mixed
	 */
	public function search_tag() {
		$result       = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_Ontraport::get_headers(), BWF_CO::$GET );
		if( 200 !== $result['response'] || ! isset( $result['body']['data'] ) || ! isset( $result['body']['data'][0]['tag_id'] ) ){
			return false;
		}

		/** Check if the same tag is getting searched */
		$fetched_tag = false;
		foreach ( $result['body']['data'] as $tag ) {
			if ( $this->data['tag'] === $tag['tag_name'] ) {
				$fetched_tag = $tag;
				break;
			}
		}

		return false !== $fetched_tag ? $fetched_tag['tag_id'] : false;
	}

	/**
	 * Get endpiont for Ontraport
	 *
	 * @param $api_key
	 * @param $api_url
	 * @param $api_action
	 *
	 * @return array|bool
	 */
	public function get_endpoint() {
		return BWFCO_Ontraport::get_endpoint().'/objects?objectID=14&search=' . $this->data['tag'];
	}

}

return 'WFCO_Ontraport_Search_Tag_By_Name';
