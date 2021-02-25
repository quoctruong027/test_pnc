<?php

class WFCO_Keap_Remove_Tags extends WFCO_Keap_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'access_token', 'email', 'tags', 'maybe_remove_remote_tags' );
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

		if ( ! is_array( $this->data['tags'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Tags data is invalid' ),
			);
		}

		$tags_to_remove = $this->data['tags'];
		$tags_to_get    = $this->data['maybe_remove_remote_tags'];
		if ( ! empty( $tags_to_get ) ) {
			$remote_tags_to_remove = [];
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

				$tag_id                  = $tags_to_get_result['body']['tags'][0]['id'];
				$tag_name                = $tags_to_get_result['body']['tags'][0]['name'];
				$remote_tags_to_remove[] = $tag_id;
				do_action( 'wfco_keap_tag_created', $tag_id, $tag_name );
			}
			$tags_to_remove = ! empty( $remote_tags_to_remove ) ? array_merge( $tags_to_remove, $remote_tags_to_remove ) : $tags_to_remove;
		}

		$contact_ids = WFCO_Keap_Common::get_contact_ids_by_email( $this->data['access_token'], $this->data['email'], false );
		if ( isset( $contact_ids['response'] ) && ( 200 !== absint( $contact_ids['response'] ) || 201 !== absint( $contact_ids['response'] ) ) ) {
			return $contact_ids;
		}

		if ( ! is_array( $contact_ids ) || empty( $contact_ids ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Contact doesn\'t exists.' ),
			);
		}

		BWFCO_Keap::set_headers( $this->data['access_token'] );

		foreach ( $contact_ids as $contact_id ) {
			$res = $this->make_wp_requests( $this->get_endpoint( $contact_id, $tags_to_remove ), array(), BWFCO_Keap::get_headers(), BWF_CO::$DELETE );
			if ( isset( $res['response'] ) && 204 !== absint( $res['response'] ) ) {
				return array(
					'response' => 502,
					'body'     => array( 'Unable to remove tags from: "' . $contact_id . '" (Keap Contact ID)' . ( isset( $res['body']['fault'] ) && isset( $res['body']['fault']['faultstring'] ) ? '. Error: ' . $res['body']['fault']['faultstring'] : '' ) ),
				);
			}
		}

		return array(
			'response' => 200,
			'body'     => 'Tags Removed from all given Contacts',
		);

	}

	/**
	 * Return the endpoint.
	 *
	 * @param $contact_id int
	 *
	 * @return string
	 */
	public function get_endpoint( $contact_id, $tags ) {
		return BWFCO_Keap::get_endpoint() . 'contacts/' . $contact_id . '/tags?ids=' . implode( ',', $tags );
	}

}

return 'WFCO_Keap_Remove_Tags';
