<?php

class WFCO_GR_Remove_Tags extends WFCO_Call {

	private static $ins = null;
	public $contact_id;

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

		if ( ! is_array( $this->data['tags'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Tags data is invalid' ),
			);
		}

		/** 1: Get Contact ID */
		$contact_id = WFCO_GetResponse_Common::get_contact_id_by_email( $this->data['api_key'], $this->data['list_id'], $this->data['email'], false );
		if ( is_array( $contact_id ) ) {
			return $contact_id;
		}

		if ( empty( $contact_id ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Contact doesn\'t exists.' ),
			);
		}

		$this->contact_id = $contact_id;

		/** 2: Get Contact */
		$connector = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_GR_Get_Contact_By_Id $call */
		$call = $connector->get_call( 'wfco_gr_get_contact_by_id' );
		$call->set_data( array(
			'api_key'    => $this->data['api_key'],
			'contact_id' => $contact_id
		) );

		$contact = $call->process();
		if ( ! is_array( $contact['body'] ) || isset( $contact['body']['code'] ) || ( isset( $contact['response'] ) && 200 !== absint( $contact['response'] ) ) ) {
			return $contact;
		}

		if ( empty( $contact ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Unable to fetch contact' ),
			);
		}

		$contact = $contact['body'];

		/** 3: Filter Tags, remove the selected tags */
		if ( empty( $contact['tags'] ) ) {
			return array(
				'response' => 200,
				'body'     => array( 'Tags not assigned to contact for remove' ),
			);
		}

		$tags_to_preserve = array();
		foreach ( $contact['tags'] as $tag ) {
			if ( ! in_array( $tag['name'], $this->data['tags'], true ) ) {
				$tags_to_preserve[]['tagId'] = $tag['tagId'];
			}
		}

		/** 4: Update contact with preserved tags */
		$params = array(
			'tags'     => $tags_to_preserve,
			'email'    => $this->data['email'],
			'campaign' => array(
				'campaignId' => $this->data['list_id']
			)
		);
		BWFCO_GetResponse::set_headers( $this->data['api_key'] );

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_GetResponse::get_headers(), BWF_CO::$POST );

		return $res;

	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_GetResponse::get_endpoint() . 'contacts/' . $this->contact_id;
	}

}

return 'WFCO_GR_Remove_Tags';
