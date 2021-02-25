<?php

class WFCO_Ontraport_Create_Tag extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'app_id', 'api_key', 'new_tags' );
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

		BWFCO_Ontraport::set_headers( $this->data );

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

		foreach ( $new_tags as $tag_name ) {
			$params_data = array(
				'tag_name' => $tag_name,
			);

			$result = $this->make_wp_requests( $this->get_endpoint(), $params_data, BWFCO_Ontraport::get_headers(), BWF_CO::$POST );
			if ( isset( $result['response'] ) && 200 === $result['response'] && isset( $result['body']['data'] ) ) { // new tag created
				$created_tag_id                 = $result['body']['data']['tag_id'];
				$create_tags[ $created_tag_id ] = $tag_name;
				do_action( 'wfco_ontraport_tag_created', $created_tag_id, $tag_name );
				continue;
			}

			/** Fetch Tag Details */
			$endpoint_url = BWFCO_Ontraport::get_endpoint() . '/objects?objectID=14&search=' . $tag_name;
			$params_data  = [];
			$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_Ontraport::get_headers(), BWF_CO::$GET );
			if ( 200 !== absint( $result['response'] ) || ! isset( $result['body']['data'] ) || empty( $result['body']['data'] ) ) {
				continue;
			}

			/** Check if the same tag is getting searched */
			$fetched_tag = false;
			foreach ( $result['body']['data'] as $tag ) {
				if ( $tag_name === $tag['tag_name'] ) {
					$fetched_tag = $tag;
					break;
				}
			}

			if ( false === $fetched_tag ) {
				continue;
			}

			$tag_id                 = $fetched_tag['tag_id'];
			$create_tags[ $tag_id ] = $fetched_tag['tag_name'];
			do_action( 'wfco_ontraport_tag_created', $tag_id, $tag_name );
		}

		if ( 0 === count( $create_tags ) ) { // no tags can be created
			return false;
		}

		return $create_tags;
	}

	public function get_endpoint() {
		return BWFCO_Ontraport::get_endpoint() . '/Tags';
	}

}

return 'WFCO_Ontraport_Create_Tag';
