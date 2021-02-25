<?php

class WFCO_GR_Update_Custom_Fields extends WFCO_Call {

	private static $ins = null;
	private $contact_id = false;

	public function __construct() {
		/** Optional: list_id (in case only one contact related to the list needs to be updated */
		$this->required_fields = array( 'api_key', 'email', 'custom_fields' );
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

		if ( ! is_array( $this->data['custom_fields'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Custom Fields data is invalid' ),
			);
		}

		$selected_contacts = array();
		if ( ! isset( $this->data['list_id'] ) || empty( $this->data['list_id'] ) ) {
			$contacts = WFCO_GetResponse_Common::get_contacts_by_email( $this->data['api_key'], $this->data['email'] );
			/** Error is in the form of Array */
			if ( ! is_array( $contacts['body'] ) || isset( $contacts['body']['code'] ) || ( isset( $contacts['response'] ) && 200 !== absint( $contacts['response'] ) ) ) {
				return $contacts;
			}

			if ( ! empty( $contacts['body'] ) ) {
				$selected_contacts = array_map( function ( $contact ) {
					return $contact['contactId'];
				}, $contacts['body'] );
			}
		}

		if ( empty( $selected_contacts ) ) {
			if ( ! isset( $this->data['list_id'] ) ) {
				$this->data['list_id'] = WFCO_GetResponse_Common::get_default_list();
			}

			if ( false === $this->data['list_id'] ) {
				return array(
					'response' => 502,
					'body'     => array( 'Neither any List selected, nor Default List found!' ),
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

			$selected_contacts = [ $contact_id ];
		}

		foreach ( $selected_contacts as $contact ) {
			/** @var WFCO_GR_Update_Custom_Fields_By_Id $call */
			$call = WFCO_Common::get_call_object( self::get_connector_slug(), 'wfco_gr_update_custom_fields_by_id' );
			$call->set_data( array(
				'api_key'       => $this->data['api_key'],
				'contact_id'    => $contact,
				'custom_fields' => $this->data['custom_fields']
			) );
			$result = $call->process();
			if ( ! is_array( $result['body'] ) || isset( $result['body']['code'] ) || ( isset( $result['response'] ) && 200 !== absint( $result['response'] ) ) ) {
				return $result;
			}
		}

		return array(
			'response' => 200,
			'body'     => array( 'Custom Fields updated successfully!' ),
		);
	}

}

return 'WFCO_GR_Update_Custom_Fields';
