<?php

abstract class WFCO_Ontraport_Call extends WFCO_Call {

	private $need_ontraport_authorization = false;

	/**
	 * Checks the required fields for every action & check the validity of Access Token
	 *
	 * @param $data
	 * @param $required_fields
	 *
	 * @return bool
	 */
	public function check_fields( $data, $required_fields ) {
		$check_required_fields = parent::check_fields( $data, $required_fields );

		if ( false === $check_required_fields ) {
			return false;
		}

		if ( isset( $data['connector_initialising'] ) && true === $data['connector_initialising'] ) {
			return true;
		}

		return true;
	}

	/**
	 * Return the error
	 *
	 * @return array
	 */
	public function show_fields_error() {
		if ( true === $this->need_ontraport_authorization ) {
			return array(
				'response' => 502,
				'body'     => array( 'You need to Re-Authorize by "Connect and Update" button on Settings dialog box, under "Ontraport" connector.' ),
			);
		}

		return parent::show_fields_error();
	}
}
