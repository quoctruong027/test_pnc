<?php

class WFCO_CK_Rmv_Tags extends WFCO_Call {

	private static $ins = null;
	public $show = true;
	public $subscriber_data = [];

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'tags', 'email' );
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

		if ( is_array( $result ) ) {
			return $result;
		}

		$subscriber_id = $result;

		return $this->remove_tag_from_subscriber( $subscriber_id );
	}

	/**
	 * Remove a single tag from the subscriber
	 *
	 * $subscriber_id and $tag_id are required.
	 *
	 * @return array|mixed|null|object|string
	 */
	public function remove_tag_from_subscriber( $subscriber_id ) {
		/** get tag list of subscriber **/
		$connector_contact_tag_list = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ck_get_subscriber_tag_list' );
		$contact_tag_list_params    = array(
			'api_secret'    => $this->data['api_secret'],
			'subscriber_id' => $subscriber_id,
		);
		$connector_contact_tag_list->set_data( $contact_tag_list_params );
		$connector_contact_tag_response = $connector_contact_tag_list->process();
		/** contact not exists */
		if ( 200 !== $connector_contact_tag_response['response'] || ! is_array( $connector_contact_tag_response['body'] ) || ! count( $connector_contact_tag_response['body'] ) > 0 ) {
			return array(
				'result'  => 4,
				'message' => __( 'Contact with Email ' . $this->data['email'] . ' doesn\'t exists.', 'autonami-automations-connectors' )
			);
		}

		$tags_to_remove = $this->data['tags'];
		$fetch_tags     = array();

		foreach ( $connector_contact_tag_response["body"] as $tags => $fetch ) {
			$fetch_tags[ $fetch['id'] ] = $fetch['name'];
		}
		$tags_not_present      = array_diff( $tags_to_remove, $fetch_tags );
		$actual_tags_to_remove = array_intersect( $tags_to_remove, $fetch_tags );

		$failed_tags  = array();
		$success_tags = array();

		$h = 1;
		foreach ( $actual_tags_to_remove as $tag ) {
			$params = array(
				'api_secret' => $this->data['api_secret'],
			);
			$tag_id = array_search( $tag, $fetch_tags );
			$url    = $this->get_endpoint() . '/' . $subscriber_id . '/tags/' . $tag_id;
			$result = $this->make_wp_requests( $url, $params, array(), 3 );
			if ( is_array( $result ) && count( $result ) > 0 && 200 === $result['response'] ) {
				$success_tags[] = $tag;
			} else {
				$failed_tags[] = $tag;
			}
			$h ++;
		}

		$tags_to_remove_count = count( $tags_to_remove );
		$success_tags_count   = count( $success_tags );
		$failed_tags_count    = count( $failed_tags );
		$response             = array( 'status' => 4, 'message' => '' );

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
		if ( count( $tags_not_present ) > 0 && 0 === absint( $success_tags_count ) ) {
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
	 * The endpoint to remove tag from subscriber.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'subscribers';
	}

}

/**
 * Register this call class.
 */
return ( 'WFCO_CK_Rmv_Tags' );
