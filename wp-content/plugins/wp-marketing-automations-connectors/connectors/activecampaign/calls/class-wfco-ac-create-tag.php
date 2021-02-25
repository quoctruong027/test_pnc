<?php

class WFCO_AC_Create_Tag extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'email' );
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

		BWFCO_ActiveCampaign::set_headers( $this->data['api_key'] );

		return $this->create_tag();
	}

	/**
	 * Create a single new tag.
	 *
	 * @param string $api_key
	 * @param string $api_url
	 * @param string $email
	 *
	 * @return array|mixed
	 */
	public function create_tag() {
		$new_tags    = $this->data['new_tags'];
		$create_tags = [];
		$api_action  = 'tags';

		foreach ( $new_tags as $tag_name ) {
			$params_data = array(
				'tag' => array(
					'tag'     => $tag_name,
					'tagType' => 'contact',
				),
			);

			$params_data  = wp_json_encode( $params_data );
			$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
			$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$POST );

			if ( isset( $result['response'] ) && 200 === $result['response'] ) { // new tag created
				$created_tag_id                 = $result['body']['tag']['id'];
				$create_tags[ $created_tag_id ] = $tag_name;
			} else { // fetch the tag details
				$endpoint_url = $endpoint_url . '?filters[tag]=' . $tag_name;
				$params_data  = [];
				$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$GET );
				if ( isset( $result['response'] ) && 200 === $result['response'] && isset( $result['body']['tags'][0] ) ) {
					$tag_id                 = $result['body']['tags'][0]['id'];
					$create_tags[ $tag_id ] = $tag_name;
				}
			}
		}

		if ( 0 === count( $create_tags ) ) { // no tags can be created
			return false;
		}

		return $create_tags;
	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Create_Tag';
