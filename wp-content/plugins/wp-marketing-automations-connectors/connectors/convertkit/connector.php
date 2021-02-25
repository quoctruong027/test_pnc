<?php

class BWFCO_ConvertKit extends BWF_CO {

	public static $api_end_point = 'https://api.convertkit.com/v3/';
	public static $headers = null;
	private static $ins = null;

	public function __construct() {
		$this->keys_to_track = [
			'api_secret',
			'tags',
			'custom_fields',
			'sequences',
		];

		$this->sync              = true;
		$this->is_setting        = true;
		$this->connector_url     = WFCO_AUTONAMI_CONNECTORS_PLUGIN_URL . '/connectors/convertkit';
		$this->dir               = __DIR__;
		$this->nice_name         = __( 'ConvertKit', 'autonami-automations-connectors' );
		$this->autonami_int_slug = 'BWFAN_ConvertKit_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function get_endpoint( $account_id = null ) {
		$endpoint = self::$api_end_point;
		if ( ! is_null( $account_id ) ) {
			$endpoint = $endpoint . $account_id . '/';
		}

		return $endpoint;
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers() {
		$headers       = array(
			'Content-Type' => 'application/json',
		);
		self::$headers = $headers;
	}

	/**
	 * The account endpoint to fetch the account details.
	 *
	 * @return string
	 */
	public function get_accounts_endpoint() {
		return $this->get_initial_endpoint() . '/account';
	}

	/**
	 * The initial common endpoint for convertkit.
	 *
	 * @return string
	 */
	public function get_initial_endpoint() {
		$endpoint = $this->api_end_point;

		return $endpoint;
	}

	/**
	 * The sequences endpoint to fetch all sequences.
	 *
	 * @return string
	 */
	public function get_sequences_endpoint() {
		return $this->get_initial_endpoint() . '/sequences';
	}

	/**
	 * Endpoint for adding a subscriber to a sequence
	 *
	 * @return string
	 */
	public function get_add_subscriber_to_sequence_endpoint() {
		return $this->get_initial_endpoint() . '/sequences/' . $this->sequence_id . '/subscribe';
	}

	/**
	 * Return the endpoint for creating / deleting a webhook.
	 *
	 * @return string
	 */
	public function get_webhook_endpoint() {
		$endpoint = $this->get_initial_endpoint() . '/automations/hooks';

		return $endpoint;
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {

		if ( empty( $posted_data['api_secret'] ) ) {
			return array(
				'status'   => 'failed',
				'message'  => __( 'Access Token Expire', 'woofunnels-convertkit' ),
				'api_data' => [],
			);
		}

		$api_secret = $posted_data['api_secret'];

		$resp_array = array(
			'status'   => 'success',
			'api_data' => [],
		);

		$params = array(
			'api_secret' => $api_secret,
		);

		$connector           = WFCO_Load_Connectors::get_instance();
		$wfco_ck_check_oauth = $connector->get_call( 'wfco_ck_check_oauth' );
		$wfco_ck_check_oauth->set_data( $params );
		$response = $wfco_ck_check_oauth->process();

		if ( isset( $response['body']['error'] ) ) {
			return array(
				'status'   => 'failed',
				'message'  => $response['body']['message'],
				'api_data' => [],
			);
		}

		if ( 200 !== $response['response'] || '' === $response['body']['primary_email_address'] ) {
			return array(
				'status'   => 'failed',
				'message'  => __( 'There was problem authenticating your account.Confirm entered details.', 'autonami-automations-connectors' ),
				'api_data' => [],
			);
		}

		$resp_array['api_data']['api_secret'] = $api_secret;
		/** Fetch tags */
		$wfco_ck_fetch_tags = $connector->get_call( 'wfco_ck_fetch_tags' );
		$wfco_ck_fetch_tags->set_data( $params );
		$tags = $wfco_ck_fetch_tags->process();

		if ( is_array( $tags ) && 200 === $tags['response'] ) {
			$tags = $tags['body']['tags'];
			if ( is_array( $tags ) && count( $tags ) > 0 ) {
				$temp_tags = array();
				foreach ( $tags as $tag_details ) {
					$temp_tags[ $tag_details['id'] ] = $tag_details['name'];
				}
				$tags                           = $temp_tags;
				$resp_array['api_data']['tags'] = $tags;
			}
		}

		/** Fetch Custom Fields */
		$wfco_ck_fetch_custom_fields = $connector->get_call( 'wfco_ck_fetch_custom_fields' );
		$wfco_ck_fetch_custom_fields->set_data( $params );
		$custom_tags = $wfco_ck_fetch_custom_fields->process();

		if ( is_array( $custom_tags ) && 200 === $custom_tags['response'] ) {
			if ( isset( $custom_tags['body']['custom_fields'] ) ) {
				$custom_tags               = $custom_tags['body']['custom_fields'];
				$temp_fields               = array();
				$temp_fields['first_name'] = 'First Name';

				if ( is_array( $custom_tags ) && count( $custom_tags ) > 0 ) {
					foreach ( $custom_tags as $field_details ) {
						$temp_fields[ $field_details['key'] ] = $field_details['label'];
					}
					$custom_tags                             = $temp_fields;
					$resp_array['api_data']['custom_fields'] = $custom_tags;
				} else {
					$resp_array['api_data']['custom_fields'] = $temp_fields;
				}
			}
		}

		/** Fetch Sequences */
		$wfco_ck_fetch_sequences = $connector->get_call( 'wfco_ck_fetch_sequences' );
		$wfco_ck_fetch_sequences->set_data( $params );
		$sequences = $wfco_ck_fetch_sequences->process();

		if ( is_array( $sequences ) && 200 === $sequences['response'] ) {
			$sequences = $sequences['body']['courses'];
			if ( is_array( $sequences ) && count( $sequences ) > 0 ) {
				$temp_sequences = array();
				foreach ( $sequences as $sequences_details ) {
					$temp_sequences[ $sequences_details['id'] ] = $sequences_details['name'];
				}
				$resp_array['api_data']['sequences'] = $temp_sequences;
			}
		}

		return $resp_array;
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_convertkit'] = array(
			'name'            => 'ConvertKit',
			'desc'            => __( 'Add or Remove tags, Add or Remove contact(s) to a sequence, Update contact custom fields & Add eCommerce Orders and much more.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_ConvertKit',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

	protected function validate_settings_fields( $data, $type = 'save' ) {
		if ( 'save' === $type ) {
			return true;
		}
		if ( 'update' === $type && isset( $data['id'] ) && (int) $data['id'] > 0 && isset( $data['api_secret'] ) && ! empty( $data['api_secret'] ) ) {
			return true;
		}
		if ( 'sync' === $type && isset( $data['id'] ) && (int) $data['id'] > 0 ) {
			return true;
		}

		return false;
	}

}

WFCO_Load_Connectors::register( 'BWFCO_ConvertKit' );
