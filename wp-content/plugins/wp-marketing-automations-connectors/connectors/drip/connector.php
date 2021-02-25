<?php

class BWFCO_Drip extends BWF_CO {
	public static $api_end_point = 'https://api.getdrip.com/';
	public static $account_id = null;
	public static $headers = null;
	private static $ins = null;

	public function __construct() {
		$this->keys_to_track = [
			'access_token',
			'account_id',
			'tags',
			'custom_fields',
			'campaigns',
			'workflows',
		];
		$this->form_req_keys = [
			'access_token',
			'account_id',
		];

		$this->sync             = true;
		$this->connector_url    = WFCO_AUTONAMI_CONNECTORS_PLUGIN_URL . '/connectors/drip';
		$this->dir              = __DIR__;
		$this->nice_name        = __( 'Drip', 'autonami-automations-connectors' );
		$this->autonami_int_slug = 'BWFAN_Drip_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function get_endpoint( $account_id = null, $version = 'v2' ) {
		$endpoint = self::$api_end_point . $version . '/';
		if ( ! is_null( $account_id ) ) {
			$endpoint = $endpoint . $account_id . '/';
		}

		return $endpoint;
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers( $access_token ) {
		$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( $access_token ),
		);

		self::$headers = $headers;
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {
		$api_token          = $posted_data['access_token'];
		$account_id         = $posted_data['account_id'];
		$connector_instance = WFCO_Load_Connectors::get_instance();

		$params     = array(
			'account_id'   => $account_id,
			'access_token' => $api_token,
		);
		$resp_array = array(
			'status' => 'success',
		);

		/** Fetch tags & also verifying if details are correct */
		$tag_cal = $connector_instance->get_call( 'wfco_dr_fetch_tags' );
		$tag_cal->set_data( $params );
		$tags = $tag_cal->process();

		/** Failure checking */
		if ( ! is_array( $tags ) || 200 !== $tags['response'] ) {
			$resp_array = array(
				'status'  => 'failed',
				'message' => __( 'There was problem authenticating your account. Confirm entered details.', 'autonami-automations-connectors' ),
			);
			wp_send_json( $resp_array );
		}

		$tags                           = $tags['body']['tags'];
		$resp_array['api_data']['tags'] = $tags;

		/** Fetch Custom Fields */
		$custom_field = $connector_instance->get_call( 'wfco_dr_fetch_custom_fields' );
		$custom_field->set_data( $params );
		$custom_tags = $custom_field->process();

		if ( is_array( $custom_tags ) && 200 === $custom_tags['response'] ) {
			$custom_tags                             = $custom_tags['body']['custom_field_identifiers'];
			$resp_array['api_data']['custom_fields'] = $custom_tags;
		}

		/** Fetch Campaigns */
		$campaign_call = $connector_instance->get_call( 'wfco_dr_fetch_campaigns' );
		$campaign_call->set_data( $params );
		$campaigns = $campaign_call->process();

		if ( is_array( $campaigns ) && 200 === $campaigns['response'] ) {
			$campaigns = $campaigns['body']['campaigns'];
			if ( is_array( $campaigns ) && count( $campaigns ) > 0 ) {
				$temp_campaigns = array();
				foreach ( $campaigns as $campaign_details ) {
					if ( 'active' === $campaign_details['status'] ) {
						$temp_campaigns[ $campaign_details['id'] ] = $campaign_details['name'];
					}
				}

				$campaigns                           = $temp_campaigns;
				$resp_array['api_data']['campaigns'] = $campaigns;
			}
		}

		/** Fetch All Workflows */
		$workflows_call = $connector_instance->get_call( 'wfco_dr_fetch_workflows' );
		$workflows_call->set_data( $params );
		$workflows = $workflows_call->process();

		if ( is_array( $workflows ) && 200 === $workflows['response'] ) {
			$workflows = $workflows['body']['workflows'];
			if ( is_array( $workflows ) && count( $workflows ) > 0 ) {
				$temp_workflows = array();
				foreach ( $workflows as $workflow_details ) {
					if ( 'active' === $workflow_details['status'] ) {
						$temp_workflows[ $workflow_details['id'] ] = $workflow_details['name'];
					}
				}

				$workflows                           = $temp_workflows;
				$resp_array['api_data']['workflows'] = $workflows;
			}
		}

		$resp_array['api_data']['account_id']   = $account_id;
		$resp_array['api_data']['access_token'] = $api_token;

		return $resp_array;
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_drip'] = array(
			'name'            => 'Drip',
			'desc'            => __( 'Add or Remove tags, Add or Remove contact(s) to a campaign or a workflow, Update contact custom fields, Add eCommerce Orders, Add Cart and much more.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Drip',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}


}

WFCO_Load_Connectors::register( 'BWFCO_Drip' );
