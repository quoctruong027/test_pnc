<?php

class WFCO_GetResponse_Common {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Get API Key if Valid, otherwise return false
	 *
	 * @return bool|string
	 */
	public static function get_api_key() {
		$data = self::get_gr_settings();

		return isset( $data['api_key'] ) && ! empty( $data['api_key'] ) ? $data['api_key'] : false;
	}

	/**
	 * Get Default list selected if Valid, otherwise return false
	 *
	 * @return bool|string
	 */
	public static function get_default_list() {
		$data = self::get_gr_settings();

		return isset( $data['default_list'] ) && ! empty( $data['default_list'] ) ? $data['default_list'] : false;
	}

	/**
	 * Get GR Saved Settings
	 *
	 * @return array
	 */
	public static function get_gr_settings() {
		if ( false === WFCO_Common::$saved_data ) {
			WFCO_Common::get_connectors_data();
		}
		$data = WFCO_Common::$connectors_saved_data;
		$slug = self::get_connector_slug();
		$data = ( isset( $data[ $slug ] ) && is_array( $data[ $slug ] ) ) ? $data[ $slug ] : array();

		return $data;
	}

	public static function get_connector_slug() {
		return sanitize_title( BWFCO_GetResponse::class );
	}

	public static function update_settings( $settings = array() ) {
		if ( empty( $settings ) ) {
			return false;
		}

		$old_settings = self::get_gr_settings();
		$settings     = array_merge( $old_settings, $settings );

		$active_connectors = WFCO_Load_Connectors::get_active_connectors();
		/** @var BWF_CO $connector_ins */
		$connector_ins = $active_connectors[ self::get_connector_slug() ];
		$response      = $connector_ins->handle_settings_form( $settings, 'update' );

		return is_array( $response ) && $response['status'] === 'success' ? true : false;
	}

	/**
	 * Get contact ID by Email (using GR's WFCO_GR_Get_Contact_ID_By_Email call)
	 *
	 * @param $api_key
	 * @param $list_id
	 * @param $email
	 * @param $create_if_not_exists
	 *
	 * @return array|string|false
	 */
	public static function get_contact_id_by_email( $api_key, $list_id, $email, $create_if_not_exists = false ) {
		//Get contact by Email
		$call = WFCO_Common::get_call_object( self::get_connector_slug(), 'wfco_gr_get_contact_id_by_email' );
		$call->set_data( array(
			'api_key'              => $api_key,
			'email'                => $email,
			'list_id'              => $list_id,
			'create_if_not_exists' => $create_if_not_exists
		) );

		/** @var string $contact (It is supposed to be contact ID, not object or array) */
		return $call->process();
	}

	/**
	 * Get all contacts by Email (using GR's WFCO_GR_Search_Contacts_By_Email call)
	 *
	 * @param $api_key
	 * @param $email
	 *
	 * @return array
	 */
	public static function get_contacts_by_email( $api_key, $email ) {
		//Get contact by Email
		$call = WFCO_Common::get_call_object( self::get_connector_slug(), 'wfco_gr_search_contacts_by_email' );
		$call->set_data( array(
			'api_key'              => $api_key,
			'email'                => $email
		) );

		/** @var string $contact (It is supposed to be contact ID, not object or array) */
		return $call->process();
	}
}

WFCO_GetResponse_Common::get_instance();