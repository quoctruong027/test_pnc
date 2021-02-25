<?php

class WFCO_Mailchimp_Common {

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
		$data = self::get_mailchimp_settings();

		return isset( $data['api_key'] ) && ! empty( $data['api_key'] ) ? $data['api_key'] : false;
	}

	/**
	 * Get Default list selected if Valid, otherwise return false
	 *
	 * @return bool|string
	 */
	public static function get_default_list() {
		$data = self::get_mailchimp_settings();

		return isset( $data['default_list'] ) && ! empty( $data['default_list'] ) ? $data['default_list'] : false;
	}

	/**
	 * Get Default Store selected if Valid, otherwise return false
	 *
	 * @return bool|string
	 */
	public static function get_default_store() {
		$data = self::get_mailchimp_settings();

		return isset( $data['default_store'] ) && ! empty( $data['default_store'] ) ? $data['default_store'] : false;
	}

	/**
	 * Get Mailchimp Saved Settings
	 *
	 * @return array
	 */
	public static function get_mailchimp_settings() {
		if ( false === WFCO_Common::$saved_data ) {
			WFCO_Common::get_connectors_data();
		}
		$data = WFCO_Common::$connectors_saved_data;
		$slug = self::get_connector_slug();
		$data = ( isset( $data[ $slug ] ) && is_array( $data[ $slug ] ) ) ? $data[ $slug ] : array();

		return $data;
	}

	public static function get_connector_slug() {
		return sanitize_title( BWFCO_Mailchimp::class );
	}

	public static function update_settings( $settings = array() ) {
		if ( empty( $settings ) ) {
			return false;
		}

		$old_settings = self::get_mailchimp_settings();
		$settings     = array_merge( $old_settings, $settings );

		$active_connectors = WFCO_Load_Connectors::get_active_connectors();
		/** @var BWF_CO $connector_ins */
		$connector_ins = $active_connectors[ self::get_connector_slug() ];
		$response      = $connector_ins->handle_settings_form( $settings, 'update' );

		return is_array( $response ) && $response['status'] === 'success' ? true : false;
	}

	public static function upsert_contact( $api_key, $list_id, $email, $merge_fields = array(), $interests = array() ) {
		//Get contact by Email
		$call   = WFCO_Common::get_call_object( self::get_connector_slug(), 'wfco_mailchimp_upsert_contact' );
		$params = array(
			'api_key' => $api_key,
			'email'   => $email,
			'list_id' => $list_id,
		);

		if ( is_array( $merge_fields ) && ! empty( $merge_fields ) ) {
			$params['merge_fields'] = $merge_fields;
		}

		if ( is_array( $interests ) && ! empty( $interests ) ) {
			$params['interests'] = $interests;
		}
		$call->set_data( $params );

		/** @var string $contact (It is supposed to be contact ID, not object or array) */
		return $call->process();
	}

	public static function get_contact( $api_key, $list_id, $email ) {
		return self::upsert_contact( $api_key, $list_id, $email );
	}
}

WFCO_Mailchimp_Common::get_instance();