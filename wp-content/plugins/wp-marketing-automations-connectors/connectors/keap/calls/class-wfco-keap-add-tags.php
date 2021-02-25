<?php

class WFCO_Keap_Add_Tags extends WFCO_Keap_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'access_token', 'email', 'tags' );
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

		$contact_id = WFCO_Keap_Common::get_contact_ids_by_email( $this->data['access_token'], $this->data['email'], true );
		if ( isset( $contact_id['response'] ) && ( 200 !== $contact_id['response'] || 201 !== $contact_id['response'] ) ) {
			return $contact_id;
		}

		if ( ! is_array( $contact_id ) || empty( $contact_id ) ) {
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
			$call = $connector->get_call( 'wfco_keap_create_tags' );
			$call->set_data( array(
				'access_token' => $this->data['access_token'],
				'tags'         => $tags_to_create
			) );
			$create_tags_result = $call->process();

			if ( ! is_array( $create_tags_result['body'] ) || isset( $create_tags_result['body']['fault'] ) || ( isset( $create_tags_result['response'] ) && 200 !== absint( $create_tags_result['response'] ) ) ) {
				return $create_tags_result;
			}

			/** Assign tags */
			$tags_to_assign = array_merge( $tags_to_assign, $create_tags_result['body'] );
		}

		/** Assign tags */
		$call = $connector->get_call( 'wfco_keap_update_tags_by_id' );
		$call->set_data( array(
			'access_token' => $this->data['access_token'],
			'contact_ids'  => $contact_id,
			'tags'         => $tags_to_assign
		) );

		return $call->process();
	}

}

return 'WFCO_Keap_Add_Tags';
