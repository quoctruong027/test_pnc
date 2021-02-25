<?php

class WFCO_AC_Rmv_Tag extends WFCO_Call {

	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'email', 'remove_tags' );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return WFCO_AC_Rmv_Tag|null
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

		BWFCO_ActiveCampaign::set_headers( $this->data['api_key'] );

		return $this->remove_contact_tags();
	}

	/**
	 * Remove tag from a contact.
	 *
	 * @return array|mixed
	 */
	public function remove_contact_tags() {
		/** get_contact_by_email*/
		$connector = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ac_get_contact_tag_list' );
		$connector->set_data( $this->data );
		$response = $connector->process();
		/** contact not exists */
		if ( 200 !== $response['response'] || ! is_array( $response['body'] ) || isset( $response['body']['result_code'] ) || ! count( $response['body'] ) > 0 ) {
			return array(
				'result'  => 4,
				'message' => __( 'Contact with Email ' . $this->data['email'] . 'doesn\'t exists.', 'autonami-automations-connectors' )
			);
		}

		$tags_to_remove       = $this->data['remove_tags'];
		$fetched_tags         = $response['body'];

		$tags_not_present      = array_diff( $tags_to_remove, $fetched_tags );
		$actual_tags_to_remove = array_intersect( $tags_to_remove, $fetched_tags );

		$failed_tags  = array();
		$success_tags = array();
		$h            = 1;

		foreach ( $actual_tags_to_remove as $tag ) {
			$api_action   = 'contact_tag_remove';
			$params_data  = array(
				'api_action' => 'contact_tag_remove',
				'email'      => $this->data['email'],
				'tags'       => $tag,
			);
			$endpoint_url = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );
			$result       = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$POST );
			if ( is_array( $result ) && count( $result ) > 0 && 1 === intval( $result['body']['result_code'] ) ) {
				$success_tags[] = $tag;
			} else {
				$failed_tags[] = $tag;
			}
			$h ++;
		}

		$tags_to_remove_count = count( $tags_to_remove );
		$success_tags_count = count( $success_tags );
		$failed_tags_count  = count( $failed_tags );
		$response           = array( 'status' => 4, 'message' => '' );

		/** Failed Tags response */
		if ( $failed_tags_count > 0 ) {
			$failed_tags_implode  = implode( ',', $failed_tags );
			$single_tag_response  = __( 'Unable to remove tag: ' . $failed_tags[0] );
			$partial_tags_message = __( 'Unable to remove some tags: ' . $failed_tags_implode, 'autonami-automations-connectors' );
			$no_tags_message      = __( 'Unable to remove any tag: ' . $failed_tags_implode, 'autonami-automations-connectors' );
			$response['message']  = 1 === count( $actual_tags_to_remove ) ? $single_tag_response : ( $success_tags_count > 0 ? $partial_tags_message : $no_tags_message );
			return $response;
		}

		/** Tags are not present Response and none Success tags  */
		if ( count( $tags_not_present ) > 0 && 0 === absint($success_tags_count) ) {
			$tags_not_present_implode = implode( ',', $tags_not_present );
			$single_failed_message    = __( 'Tag is not available on contact: ' . $tags_not_present[0], 'autonami-automations-connectors' );
			$multiple_failed_message  = __( 'None of the tag is available on contact to remove: ' . $tags_not_present_implode, 'autonami-automations-connectors' );
			$response['message']      = 1 === $tags_to_remove_count ? $single_failed_message : $multiple_failed_message;
			return $response;
		}

		/** Success Messages */
		$tags_not_present_implode = implode( ',', $tags_not_present );
		$tags_not_present_message = __( 'Tags removed but some tags were not applied to contact: ' . $tags_not_present_implode, 'autonami-automations-connectors' );
		$success_message          = __( 'All tags are removed successfully', 'autonami-automations-connectors' );
		$response['status']       = 3;
		$response['message']      = count( $tags_not_present ) > 0 ? $tags_not_present_message : $success_message;

		return $response;
	}

	/**
	 * Get endpiont for Activecampaign
	 *
	 * @param $api_key
	 * @param $api_url
	 * @param $api_action
	 *
	 * @return array|bool
	 */
	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Rmv_Tag';
