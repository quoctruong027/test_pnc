<?php

class BWFCO_ActiveCampaign extends BWF_CO {

	public static $headers = null;
	public static $api_version = '/api/3/';
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->keys_to_track = [
			'connections',
			'tags',
			'custom_fields',
			'deal_custom_fields',
			'lists',
			'automations',
			'pipelines',
			'stages',
			'pipelines_stages',
			'owner_ids',
		];
		$this->form_req_keys = [
			'api_url',
			'api_key',
		];
		$this->sync          = true;
		$this->connector_url = WFCO_AUTONAMI_CONNECTORS_PLUGIN_URL . '/connectors/activecampaign';

		/** @var: Autonami integration class name needs to pass */
		$this->autonami_int_slug = 'BWFAN_ActiveCampaign_Integration';
		$this->dir               = __DIR__;

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFCO_ActiveCampaign|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers( $api_token ) {
		$headers       = array(
			'Api-Token' => $api_token,
		);
		self::$headers = $headers;
	}

	public static function get_endpoint_url( $api_url, $api_action ) {
		return self::get_new_api_url( $api_url ) . $api_action;
	}

	public static function get_new_api_url( $api_url ) {
		return $api_url . self::$api_version;
	}

	/**
	 * Get endpoint url.
	 *
	 * @param string $api_key
	 * @param string $api_url
	 * @param string $api_action
	 *
	 * @return array|bool
	 */
	public static function endpoint( $api_key = '', $api_url = '', $api_action = '' ) {
		$base = '';
		if ( ! preg_match( '/https:\/\/www.activecampaign.com/', $api_url ) ) {
			$base = '/admin';
		}
		if ( preg_match( '/\/$/', $api_url ) ) {
			// remove trailing slash
			$api_url = substr( $api_url, 0, strlen( $api_url ) - 1 );
		}
		if ( $api_key ) {
			$api_url = "{$api_url}{$base}/api.php?api_key={$api_key}";
		}
		$endpoint_url = "{$api_url}&api_action={$api_action}&api_output=serialize";

		return $endpoint_url;
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {
		$api_key = isset( $posted_data['api_key'] ) ? $posted_data['api_key'] : '';
		$api_url = isset( $posted_data['api_url'] ) ? $posted_data['api_url'] : '';

		$connectors = WFCO_Load_Connectors::get_instance();
		$params     = array(
			'api_url' => $api_url,
			'api_key' => $api_key,
		);

		$wfco_ac_oauth_check = $connectors->get_call( 'wfco_ac_oauth_check' );
		$wfco_ac_oauth_check->set_data( $params );
		$response   = $wfco_ac_oauth_check->process();
		$resp_array = array(
			'status' => 'success',
		);

		/** Failure */
		if ( ! is_array( $response ) || 200 !== intval( $response['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => [],
				'message'  => __( 'There was problem authenticating your account. Confirm entered details.', 'autonami-automations-connectors' ),
			);
		}

		$resp_array['api_data']['api_url'] = $api_url;
		$resp_array['api_data']['api_key'] = $api_key;

		$params['limit']  = 100;
		$params['offset'] = 0;

		/** Fetch connections */
		$final_connections       = [];
		$wfco_ac_get_connections = $connectors->get_call( 'wfco_ac_get_connections' );
		$wfco_ac_get_connections->set_data( $params );
		$connections = $wfco_ac_get_connections->process();

		if ( is_array( $connections ) && isset( $connections['response'] ) && 200 === $connections['response'] && isset( $connections['body']['connections'] ) && is_array( $connections['body']['connections'] ) && count( $connections['body']['connections'] ) > 0 ) {
			// Current activecampaign account have deep data integration
			$temp_connections = $connections['body']['connections'];
			$connections      = [];
			foreach ( $temp_connections as $details ) {
				$connections[ $details['id'] ] = $details['service'];
			}
			$final_connections = $connections;
		}

		/** Create Connection for Autonami */
		if ( ! in_array( 'Autonami', $final_connections ) ) {
			$autonami_connection = $this->create_connection( $params );
			if ( 200 === $autonami_connection['response'] ) {
				$final_connections[] = $autonami_connection['body']['connection']['service'];
			}
		}
		$resp_array['api_data']['connections'] = $final_connections;

		/** Fetch tags */
		$tags_result = $this->fetch_tags( [], $params );
		if ( is_array( $tags_result ) && count( $tags_result ) > 0 ) {
			$resp_array['api_data']['tags'] = $tags_result;
		}

		/** Fetch Custom Fields */
		$custom_fields_result = $this->fetch_customfields( [], $params );
		if ( is_array( $custom_fields_result ) && count( $custom_fields_result ) > 0 ) {
			$resp_array['api_data']['custom_fields'] = $custom_fields_result;
		}

		/** Fetch Deal Custom Fields */
		$deal_custom_fields_result = $this->fetch_deal_custom_fields( [], $params );
		if ( is_array( $deal_custom_fields_result ) && count( $deal_custom_fields_result ) > 0 ) {
			$resp_array['api_data']['deal_custom_fields'] = $deal_custom_fields_result;
		}

		/** Fetch lists */
		$lists_result = $this->fetch_lists( [], $params );
		if ( is_array( $lists_result ) && count( $lists_result ) > 0 ) {
			$resp_array['api_data']['lists'] = $lists_result;
		}

		/** Fetch Automations */
		$automation_results = $this->fetch_automations( [], $params );
		if ( is_array( $automation_results ) && count( $automation_results ) > 0 ) {
			$resp_array['api_data']['automations'] = $automation_results;
		}

		/** Fetch all Pipelines, all users if account support deep data integration*/
		$pipelines_result = $this->fetch_pipelines( [], [], [], $params );
		if ( isset( $pipelines_result['pipelines'] ) && isset( $pipelines_result['stages'] ) && is_array( $pipelines_result['pipelines'] ) && is_array( $pipelines_result['stages'] ) ) {
			$resp_array['api_data']['pipelines']        = $pipelines_result['pipelines'];
			$resp_array['api_data']['stages']           = $pipelines_result['stages'];
			$resp_array['api_data']['pipelines_stages'] = $pipelines_result['pipelines_stages'];
		}

		$user_results = $this->fetch_users( [], $params );
		if ( is_array( $user_results ) && count( $user_results ) > 0 ) {
			$resp_array['api_data']['owner_ids'] = $user_results;
		}

		return $resp_array;
	}

	public function create_connection( $params ) {
		$connectors             = WFCO_Load_Connectors::get_instance();
		$create_connection_call = $connectors->get_call( 'wfco_ac_create_connection' );

		$params = array_replace( $params, array(
			'service'    => 'Autonami',
			'externalid' => $_SERVER['SERVER_NAME'],
			'name'       => get_bloginfo(),
			'logoUrl'    => 'https://buildwoofunnels.com/wp-content/uploads/2020/04/autonami-128x128.jpg',
			'linkUrl'    => admin_url( 'admin.php?page=autonami' ),
		) );
		$create_connection_call->set_data( $params );

		return $create_connection_call->process();
	}

	public function fetch_tags( $captured_tags, $params ) {
		$connectors            = WFCO_Load_Connectors::get_instance();
		$all_tags              = [];
		$wfco_ac_get_tags_list = $connectors->get_call( 'wfco_ac_get_tags_list' );
		$wfco_ac_get_tags_list->set_data( $params );
		$tags_list = $wfco_ac_get_tags_list->process();

		if ( ! is_array( $tags_list ) || 200 !== $tags_list['response'] || ! isset( $tags_list['body']['tags'] ) || 0 === count( $tags_list['body']['tags'] ) ) {
			return $all_tags;
		}

		$total_tags_count = intval( $tags_list['body']['meta']['total'] );
		$tags             = $tags_list['body']['tags'];
		foreach ( $tags as $tags_details ) {
			$captured_tags[ $tags_details['id'] ] = $tags_details['tag'];
		}

		$all_tags = $captured_tags;
		$offset   = '';
		if ( $total_tags_count > count( $all_tags ) ) {
			$offset = count( $all_tags );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;
			$inner_tags       = $this->fetch_tags( $all_tags, $params );
			if ( is_array( $inner_tags ) && count( $inner_tags ) > 0 ) {
				foreach ( $inner_tags as $id => $name ) {
					$all_tags[ $id ] = $name;
				}
			}
		}

		return $all_tags;
	}

	public function fetch_customfields( $captured_custom_fields, $params ) {
		$connectors                = WFCO_Load_Connectors::get_instance();
		$all_custom_fields         = [];
		$wfco_ac_get_custom_fields = $connectors->get_call( 'wfco_ac_get_custom_fields' );
		$wfco_ac_get_custom_fields->set_data( $params );
		$response = $wfco_ac_get_custom_fields->process();

		if ( ! is_array( $response ) || 200 !== $response['response'] || ! isset( $response['body']['fields'] ) || ! is_array( $response['body']['fields'] ) || 0 === count( $response['body']['fields'] ) ) {
			return $all_custom_fields;
		}

		$total_cf_count = intval( $response['body']['meta']['total'] );
		$customfields   = $response['body']['fields'];
		foreach ( $customfields as $field_details ) {
			$custom_field_id                                       = $field_details['id'];
			$captured_custom_fields[ $custom_field_id ]['title']   = $field_details['title'];
			$captured_custom_fields[ $custom_field_id ]['type']    = $field_details['type'];
			$captured_custom_fields[ $custom_field_id ]['options'] = $field_details['options'];
			$captured_custom_fields[ $custom_field_id ]['defval']  = $field_details['defval'];

		}

		$all_custom_fields = $captured_custom_fields;
		$offset            = '';
		if ( $total_cf_count > count( $all_custom_fields ) ) {
			$offset = count( $all_custom_fields );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_customfields( $all_custom_fields, $params );
		}

		return $all_custom_fields;
	}

	public function fetch_deal_custom_fields( $captured_custom_fields, $params ) {
		$connectors                = WFCO_Load_Connectors::get_instance();
		$all_custom_fields         = [];
		$wfco_ac_get_custom_fields = $connectors->get_call( 'wfco_ac_get_deal_custom_fields' );
		$wfco_ac_get_custom_fields->set_data( $params );
		$response = $wfco_ac_get_custom_fields->process();

		if ( ! is_array( $response ) || 200 !== $response['response'] || ! isset( $response['body']['dealCustomFieldMeta'] ) || ! is_array( $response['body']['dealCustomFieldMeta'] ) || 0 === count( $response['body']['dealCustomFieldMeta'] ) ) {
			return $all_custom_fields;
		}

		$customfields = $response['body']['dealCustomFieldMeta'];
		foreach ( $customfields as $field_details ) {
			$custom_field_id                                     = $field_details['id'];
			$captured_custom_fields[ $custom_field_id ]['title'] = $field_details['fieldLabel'];
			$captured_custom_fields[ $custom_field_id ]['type']  = $field_details['fieldType'];
		}

		if ( ! isset( $response['body']['meta'] ) || ! isset( $response['body']['meta']['total'] ) ) {
			return $captured_custom_fields;
		}

		$total_cf_count    = intval( $response['body']['meta']['total'] );
		$all_custom_fields = $captured_custom_fields;
		$offset            = '';
		if ( $total_cf_count > count( $all_custom_fields ) ) {
			$offset = count( $all_custom_fields );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_deal_custom_fields( $all_custom_fields, $params );
		}

		return $all_custom_fields;
	}

	public function fetch_lists( $captured_lists, $params ) {
		$all_lists         = [];
		$connectors        = WFCO_Load_Connectors::get_instance();
		$wfco_ac_get_lists = $connectors->get_call( 'wfco_ac_get_lists' );
		$wfco_ac_get_lists->set_data( $params );
		$response = $wfco_ac_get_lists->process();

		if ( ! is_array( $response ) || 200 !== $response['response'] || ! isset( $response['body']['lists'] ) || 0 === count( $response['body']['lists'] ) ) {
			return $all_lists;
		}

		$total_lists_count = intval( $response['body']['meta']['total'] );
		$lts               = $response['body']['lists'];
		foreach ( $lts as $list_details ) {
			$captured_lists[ $list_details['id'] ] = $list_details['name'];
		}

		$all_lists = $captured_lists;
		$offset    = '';
		if ( $total_lists_count > count( $all_lists ) ) {
			$offset = count( $all_lists );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;
			$inner_lists      = $this->fetch_lists( $all_lists, $params );
			if ( is_array( $inner_lists ) && count( $inner_lists ) > 0 ) {
				foreach ( $inner_lists as $id => $name ) {
					$all_lists[ $id ] = $name;
				}
			}
		}

		return $all_lists;
	}

	public function fetch_automations( $captured_automations, $params ) {
		$all_automations         = [];
		$connectors              = WFCO_Load_Connectors::get_instance();
		$wfco_ac_get_automations = $connectors->get_call( 'wfco_ac_get_automations' );
		$wfco_ac_get_automations->set_data( $params );
		$response = $wfco_ac_get_automations->process();

		if ( ! is_array( $response ) || 200 !== $response['response'] || ! isset( $response['body']['automations'] ) || 0 === count( $response['body']['automations'] ) ) {
			return $all_automations;
		}

		$total_au_count = intval( $response['body']['meta']['total'] );
		$automations    = $response['body']['automations'];
		foreach ( $automations as $au_details ) {
			$captured_automations[ $au_details['id'] ] = $au_details['name'];
		}

		$all_automations = $captured_automations;
		$offset          = '';
		if ( $total_au_count > count( $all_automations ) ) {
			$offset = count( $all_automations );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;
			$inner_atmns      = $this->fetch_automations( $all_automations, $params );
			if ( is_array( $inner_atmns ) && count( $inner_atmns ) > 0 ) {
				foreach ( $inner_atmns as $id => $name ) {
					$all_automations[ $id ] = $name;
				}
			}
		}

		return $all_automations;
	}

	public function fetch_pipelines( $captured_pipelines, $captured_stages, $captured_relation, $params ) {
		$connectors            = WFCO_Load_Connectors::get_instance();
		$all_pipelines         = [];
		$wfco_ac_get_pipelines = $connectors->get_call( 'wfco_ac_get_pipelines' );
		$wfco_ac_get_pipelines->set_data( $params );
		$pipelines = $wfco_ac_get_pipelines->process();

		if ( ! is_array( $pipelines ) || 200 !== $pipelines['response'] || ! isset( $pipelines['body']['dealGroups'] ) || 0 === count( $pipelines['body']['dealGroups'] ) ) {
			return $all_pipelines;
		}

		$total_pipeline_count = intval( $pipelines['body']['meta']['total'] );
		$pips                 = isset( $pipelines['body']['dealGroups'] ) ? $pipelines['body']['dealGroups'] : array();
		$stages               = isset( $pipelines['body']['dealStages'] ) ? $pipelines['body']['dealStages'] : array();

		foreach ( $pips as $pipeline_details ) {
			$captured_pipelines[ $pipeline_details['id'] ] = $pipeline_details['title'];
			$captured_relation[ $pipeline_details['id'] ]  = $pipeline_details['stages'];
		}
		$all_pipelines        = $captured_pipelines;
		$all_pipelines_stages = $captured_relation;

		foreach ( $stages as $stage_details ) {
			$captured_stages[ $stage_details['id'] ] = $stage_details['title'];
		}
		$all_stages = $captured_stages;
		$offset     = '';

		if ( $total_pipeline_count > count( $all_pipelines ) ) {
			$offset = count( $all_pipelines );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;
			$inner_pips       = $this->fetch_pipelines( $all_pipelines, $all_stages, $all_pipelines_stages, $params );
			if ( is_array( $inner_pips ) && isset( $inner_pips['pipelines'] ) ) {
				$captured_pipelines = array_replace( $captured_pipelines, $inner_pips['pipelines'] );
				$captured_relation  = array_replace( $captured_relation, $inner_pips['pipelines_stages'] );
				$captured_stages    = array_replace( $captured_stages, $inner_pips['stages'] );
			} else {
				/** Error or Empty Array returned */
				return $inner_pips;
			}
		}

		return array(
			'pipelines'        => $captured_pipelines,
			'stages'           => $captured_stages,
			'pipelines_stages' => $captured_relation,
		);
	}

	public function fetch_users( $captured_users, $params ) {
		$all_users         = [];
		$connectors        = WFCO_Load_Connectors::get_instance();
		$wfco_ac_get_users = $connectors->get_call( 'wfco_ac_get_users' );
		$wfco_ac_get_users->set_data( $params );
		$response = $wfco_ac_get_users->process();

		if ( ! is_array( $response ) || 200 !== $response['response'] || ! isset( $response['body']['users'] ) || 0 === count( $response['body']['users'] ) ) {
			return $all_users;
		}

		$total_users_count = intval( $response['body']['meta']['total'] );
		$users             = $response['body']['users'];
		foreach ( $users as $user_details ) {
			$captured_users[ $user_details['id'] ] = $user_details['firstName'] . ' ' . $user_details['lastName'];
		}

		$all_users = $captured_users;
		$offset    = '';
		if ( $total_users_count > count( $all_users ) ) {
			$offset = count( $all_users );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;
			$inner_atmns      = $this->fetch_users( $all_users, $params );
			if ( is_array( $inner_atmns ) && count( $inner_atmns ) > 0 ) {
				foreach ( $inner_atmns as $id => $name ) {
					$all_users[ $id ] = $name;
				}
			}
		}

		return $all_users;
	}

	/** Check if there is any difference in old and new api data.
	 *
	 * @param $new_data
	 * @param $old_data
	 *
	 * @return bool
	 */
	public function track_sync_changes( $new_data, $old_data ) {
		if ( ! isset( $old_data['api_url'] ) || ! isset( $new_data['api_url'] ) ) {
			return parent::track_sync_changes( $new_data, $old_data );
		}

		if ( $old_data['api_url'] !== $new_data['api_url'] || $old_data['api_key'] !== $new_data['api_key'] ) {
			$has_changes = true;

			return $has_changes;
		}

		return parent::track_sync_changes( $new_data, $old_data );
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_activecampaign'] = array(
			'name'            => 'ActiveCampaign',
			'desc'            => __( 'Add or Remove tags, Add or Remove contact(s) to an automation or a list, Update contact custom fields, Add eCommerce Orders/ Deals, Create abandonment cart and much more.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_ActiveCampaign',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}


}

WFCO_Load_Connectors::register( 'BWFCO_ActiveCampaign' );
