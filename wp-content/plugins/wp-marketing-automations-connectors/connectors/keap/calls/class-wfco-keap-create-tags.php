<?php

class WFCO_Keap_Create_Tags extends WFCO_Keap_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'access_token', 'tags' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		if ( ! is_array( $this->data['tags'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Tags data is invalid' ),
			);
		}

		BWFCO_Keap::set_headers( $this->data['access_token'] );

		$created_tags = array();
		$tags_to_get  = array();
		foreach ( $this->data['tags'] as $tag ) {
			$params = array(
				'name' => $tag
			);

			$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Keap::get_headers(), BWF_CO::$POST );
			if ( ! is_array( $res['body'] ) || ( isset( $res['response'] ) && 200 !== absint( $res['response'] ) ) ) {
				$tags_to_get[] = $tag;
			} else {
				$created_tags[] = $res['body']['id'];
				do_action( 'wfco_keap_tag_created', $res['body']['id'], $res['body']['name'] );
			}
		}

		/** in case of tag already created in crm so fetching these tags and saved to database */
		$tags_to_be_added = array();
		if ( empty( $tags_to_get ) ) {
			return array(
				'response' => 200,
				'body'     => $created_tags,
			);
		}

		foreach ( $tags_to_get as $tag_to_get ) {
			$connector = WFCO_Load_Connectors::get_instance();
			$call      = $connector->get_call( 'wfco_keap_get_tags' );
			$call->set_data( array(
				'access_token' => $this->data['access_token'],
				'search'       => $tag_to_get
			) );

			$tags_to_get_result = $call->process();
			if ( ! is_array( $tags_to_get_result['body'] ) || ( isset( $tags_to_get_result['response'] ) && 200 !== absint( $tags_to_get_result['response'] ) ) ) {
				continue;
			}

			if ( ! isset( $tags_to_get_result['body']['tags'] ) || ! is_array( $tags_to_get_result['body']['tags'] ) || empty( $tags_to_get_result['body']['tags'] ) ) {
				continue;
			}

			/** Pick the exact tag we have searched for */
			foreach ( $tags_to_get_result['body']['tags'] as $tag ) {
				if ( $tag['name'] !== $tag_to_get ) {
					continue;
				}

				$tag_id             = $tag['id'];
				$tag_name           = $tag['name'];
				$tags_to_be_added[] = $tag_id;
				do_action( 'wfco_keap_tag_created', $tag_id, $tag_name );
				break;
			}
		}

		$created_tags = ! empty( $tags_to_be_added ) ? array_merge( $created_tags, $tags_to_be_added ) : $created_tags;

		return array(
			'response' => 200,
			'body'     => $created_tags,
		);
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Keap::get_endpoint() . 'tags';
	}

}

return 'WFCO_Keap_Create_Tags';
