<?php

class WFCO_GR_Add_Tags extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'email', 'list_id', 'tags' );
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

		if ( ! is_email( $this->data['email'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Email is not valid' ),
			);
		}

		if ( ! is_array( $this->data['tags'] ) || ( ! isset( $this->data['tags']['new'] ) || ! isset( $this->data['tags']['existing'] ) ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Tags data is invalid' ),
			);
		}

		$contact_id = WFCO_GetResponse_Common::get_contact_id_by_email( $this->data['api_key'], $this->data['list_id'], $this->data['email'], true );
		if ( is_array( $contact_id ) ) {
			return $contact_id;
		}

		if ( empty( $contact_id ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Unable to Create or Get Contact' ),
			);
		}

		/** Filter tags and create the unavailable tags */
		$tags_to_create = $this->data['tags']['new'];
		$tags_to_assign = $this->data['tags']['existing'];

		$connector = WFCO_Load_Connectors::get_instance();
		if ( ! empty( $tags_to_create ) ) {
			/** Create Tags */
			$call = $connector->get_call( 'wfco_gr_create_tags' );
			$call->set_data( array(
				'api_key' => $this->data['api_key'],
				'tags'    => $tags_to_create
			) );
			$create_tags_result = $call->process();

			if ( ! is_array( $create_tags_result['body'] ) || isset( $create_tags_result['body']['code'] ) || ( isset( $create_tags_result['response'] ) && 200 !== absint( $create_tags_result['response'] ) ) ) {
				return $create_tags_result;
			}

			/** Assign tags */
			$tags_to_assign = array_merge( $tags_to_assign, $create_tags_result['body'] );
		}

		/** Assign tags */
		$call = $connector->get_call( 'wfco_gr_update_tags_by_id' );
		$call->set_data( array(
			'api_key'    => $this->data['api_key'],
			'contact_id' => $contact_id,
			'tags'       => $tags_to_assign
		) );

		return $call->process();
	}

}

return 'WFCO_GR_Add_Tags';
