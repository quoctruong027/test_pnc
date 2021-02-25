<?php

class WFCO_Mautic_Add_Tags extends WFCO_Mautic_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'site_url', 'access_token', 'email', 'tags' );
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
				'body'     => array( 'Tags data is not valid' ),
			);
		}

		//Set Tags with 'Update Contact Fields' WFCO_Call
		/** @var WFCO_Mautic_Update_Contact_Fields $call */
		$call = WFCO_Common::get_call_object( self::get_connector_slug(), 'wfco_mautic_update_contact_fields' );
		$call->set_data( $this->data );
		return $call->process();
	}

}

return 'WFCO_Mautic_Add_Tags';
