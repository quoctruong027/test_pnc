<?php

class WFCO_GR_Create_Tags extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'api_key', 'tags' );
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

		BWFCO_GetResponse::set_headers( $this->data['api_key'] );

		$created_tags = array();
		$tags_to_get  = array();
		foreach ( $this->data['tags'] as $tag ) {
			$params = array(
				'name' => $tag
			);
			$res    = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_GetResponse::get_headers(), BWF_CO::$POST );
			if ( ! is_array( $res['body'] ) || isset( $res['body']['code'] ) || ( isset( $res['response'] ) && 200 !== absint( $res['response'] ) ) ) {
				$tags_to_get[] = $tag;
			} else {
				$created_tags[] = $res['body']['tagId'];
				do_action( 'wfco_getresponse_tag_created', $res['body']['tagId'], $res['body']['name'] );
			}

		}

		// in case of tag already created in crm so fetching these tags and saved to database
		if ( ! empty( $tags_to_get ) ) {
			$connector = WFCO_Load_Connectors::get_instance();
			$call      = $connector->get_call( 'wfco_gr_search_tags_by_name' );
			$call->set_data( array(
				'api_key'   => $this->data['api_key'],
				'tags_name' => implode( ',', $tags_to_get )
			) );
			$tags_to_get_result = $call->process();
			if ( ! is_array( $tags_to_get_result['body'] ) || isset( $tags_to_get_result['body']['code'] ) || ( isset( $tags_to_get_result['response'] ) && 200 !== absint( $tags_to_get_result['response'] ) ) ) {
				return $res;
			}

			$tags_to_be_added = array();
			$tags_to_get      = array_map( 'strtolower', $tags_to_get );
			foreach ( $tags_to_get_result['body'] as $index => $tags ) {
				/** only use tag if it is strictly equals to an element of tags_to_get */
				if ( in_array( strtolower( $tags['name'] ), $tags_to_get, true ) ) {
					$tags_to_be_added[ $index ] = $tags['tagId'];
					do_action( 'wfco_getresponse_tag_created', $tags['tagId'], $tags['name'] );
				}
			}
			$created_tags = isset( $tags_to_be_added ) && is_array( $tags_to_be_added ) ? array_merge( $created_tags, $tags_to_be_added ) : $created_tags;
		}

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
		return BWFCO_GetResponse::get_endpoint() . 'tags';
	}

}

return 'WFCO_GR_Create_Tags';
