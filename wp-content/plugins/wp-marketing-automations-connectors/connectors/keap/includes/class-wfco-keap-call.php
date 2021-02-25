<?php

abstract class WFCO_Keap_Call extends WFCO_Call {

	public $need_wc_active = false;
	private $need_keap_authorization = false;

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

		/** Refresh Access Token if Access Token expired */
		if ( in_array( 'access_token', $required_fields, true ) && 0 !== $data['access_token'] && ! WFCO_Keap_Common::is_access_token_valid() ) {
			$access_token = WFCO_Keap_Common::refresh_access_token();

			if ( false === $access_token ) {
				$this->need_keap_authorization = true;

				return false;
			}

			$this->data['access_token'] = $access_token;

			if ( in_array( 'keap_token_data', $required_fields, true ) ) {
				$this->data['keap_token_data'] = WFCO_Keap_Core()->api->get_keap_token_data();
			}
		}


		return true;
	}

	/**
	 * Return the error
	 *
	 * @return array
	 */
	public function show_fields_error() {
		if ( true === $this->need_keap_authorization ) {
			return array(
				'response' => 502,
				'body'     => array( 'You need to Re-Authorize by "Connect and Update" button on Settings dialog box, under "Keap" connector.' ),
			);
		}

		return parent::show_fields_error();
	}
}