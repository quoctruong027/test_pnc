<?php

class WFCO_GS_Get_Google_Client extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		//
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function process() {
		$connectors_saved = WFCO_Common::$connectors_saved_data;

		if ( isset( $connectors_saved['bwfco_google_sheets'] ) && ! empty( $connectors_saved['bwfco_google_sheets'] ) ) {
			if ( isset( $connectors_saved['bwfco_google_sheets']['api_data'] ) && isset( $connectors_saved['bwfco_google_sheets']['api_data']['auth_token'] ) ) {

				$auth_token = $connectors_saved['bwfco_google_sheets']['api_data']['auth_token'];
				$id         = $connectors_saved['bwfco_google_sheets']['id'];

				return $this->get_client( $auth_token, $id );
			}
		}

		return false;
	}

	/**
	 * get google client with auth token and updates auth token in integration meta if auth token has expired.
	 *
	 * @param $auth_token
	 * @param int $connector_id
	 *
	 * @return Google_Client
	 * @throws Google_Exception
	 */
	public function get_client( $auth_token, $connector_id = 0 ) {
		$client = new Google_Client();
		$client->setApplicationName( 'Google Sheets API PHP Autobot' );
		$client->setScopes( Google_Service_Sheets::SPREADSHEETS );
		$client->setAuthConfig( WFCO_GOOGLE_SHEETS_PLUGIN_DIR . '/credentials.json' );
		$client->setAccessType( 'offline' );
		$client->setPrompt( 'select_account consent' );

		$accessToken = json_decode( $auth_token, true );
		$client->setAccessToken( $accessToken );

		// If there is no previous token or it's expired.
		if ( $client->isAccessTokenExpired() ) {

			$client->fetchAccessTokenWithRefreshToken( $client->getRefreshToken() );

			/** Update access token Integration meta table */
			$data  = array(
				'meta_value' => wp_json_encode( $client->getAccessToken() ),
			);
			$where = array(
				'connector_id' => $connector_id,
				'meta_key'     => 'auth_token',
			);
			WFCO_Model_ConnectorMeta::update( $data, $where );
		}

		return $client;
	}


}

return 'WFCO_GS_Get_Google_Client';
