<?php

class WFCO_Ontraport_Rmv_Tag extends WFCO_Call {

	private static $instance = null;
  private $contact_id = null;
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->required_fields = array( 'app_id', 'api_key', 'email', 'remove_tags' );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return WFCO_Ontraport_Rmv_Tag|null
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

		BWFCO_Ontraport::set_headers( $this->data);

		return $this->remove_contact_tags();
	}

	/**
	 * Remove tag from a contact.
	 *
	 * @return array|mixed
	 */
	public function remove_contact_tags() {
		/** get_contact_by_email*/
		$connector = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ontraport_get_contact_id_by_email' );
		$connector->set_data( $this->data );
		$response = $connector->process();

		/** contact not exists */
		if ( isset( $response['response'] ) && 200 === $response['response'] && 0 === count( $response['body']['contacts'] ) ) {
			$response['bwfan_custom_message'] = __( 'Contact Not Present', 'autonami-automations-connectors' );

			return $response;
		}
    $this->contact_id = $response;
		$tags       = $this->data['remove_tags'];
		$result_arr = array();
		$h          = 1;

		foreach ( $tags as $tag ) {
			$params_data  = array(
				'objectID'          => 0,
				'remove_list'       => $tag,
        'ids'               =>    $this->contact_id,
			);

			$result       = $this->make_wp_requests( $this->get_endpoint(), $params_data, BWFCO_Ontraport::get_headers(), BWF_CO::$DELETE );
			if ( is_array( $result ) && count( $result ) > 0 && isset( $result['body']['data'] ) ) {
				$result_arr[ $h ]['response']               = 200;
				$result_arr[ $h ]['body']['data'] = $result['body']['data'];
			}
			$h ++;
		}
		return ( is_array( $result_arr ) && count( $result_arr ) > 0 ) ? array(
			'bwfan_success_message' => 1,
		) : array(
			'bwfan_custom_message' => __( 'One or more tags cannot be removed', 'autonami-automations-connectors' ),
		);
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
	public function get_endpoint( ) {
		return BWFCO_Ontraport::get_endpoint( ).'/objects/tag';
	}

}

return 'WFCO_Ontraport_Rmv_Tag';
