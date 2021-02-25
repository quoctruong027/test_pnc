<?php

final class BWFAN_DR_Rmv_From_Campaign extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Remove Subscriber from Campaign', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action removes the subscriber from the selected campaign', 'autonami-automations-connectors' );
		$this->action_priority = 20;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'campaign_id_options', $data );
		}
	}

	public function get_view_data() {
		$campaigns = WFCO_Common::get_single_connector_data( $this->connector, 'campaigns' );

		return $campaigns;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_campaign_id = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'campaign_id')) ? data.actionSavedData.data.campaign_id : '';
            #>
            <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Select Campaign', 'autonami-automations-connectors' ); ?></label>
            <select required id="" class="bwfan-input-wrapper bwfan-single-select" name="bwfan[{{data.action_id}}][data][campaign_id]">
                <option value=""><?php echo esc_html__( 'Choose A Campaign', 'autonami-automations-connectors' ); ?></option>
                <#
                if(_.has(data.actionFieldsOptions, 'campaign_id_options') && _.isObject(data.actionFieldsOptions.campaign_id_options) ) {
                _.each( data.actionFieldsOptions.campaign_id_options, function( value, key ){
                selected = (key == selected_campaign_id) ? 'selected' : '';
                #>
                <option value="{{key}}" {{selected}}>{{value}}</option>
                <# })
                }
                #>
            </select>
        </script>
		<?php
	}

	/**
	 * Make all the data which is required by the current action.
	 * This data will be used while executing the task of this action.
	 *
	 * @param $integration_object
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data( $integration_object, $task_meta ) {
		$data_to_set                 = array();
		$data_to_set['access_token'] = $integration_object->get_settings( 'access_token' );
		$data_to_set['account_id']   = $integration_object->get_settings( 'account_id' );
		$data_to_set['email']        = $task_meta['global']['email'];
		$data_to_set['campaign_id']  = $task_meta['data']['campaign_id'];

		return $data_to_set;
	}

	/**
	 * Execute the current action.
	 * Return 3 for successful execution , 4 for permanent failure.
	 *
	 * @param $action_data
	 *
	 * @return array
	 */
	public function execute_action( $action_data ) {
		$result = parent::execute_action( $action_data );
		if ( isset( $result['body']['subscribers'] ) && isset( $result['body']['subscribers'][0] ) && isset( $result['body']['subscribers'][0]['id'] ) && ! empty( $result['body']['subscribers'][0]['id'] ) ) {
			return array(
				'status'  => 3,
				'message' => '',
			);
		}

		/** handling response in case required field missing **/
		if ( isset( $result['response'] ) && 502 === $result['response'] ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['body']['errors'] ) && isset( $result['body']['errors'][0] ) ) {
			if ( isset( $result['body']['errors'][0]['code'] ) && 'not_found_error' === $result['body']['errors'][0]['code'] ) {
				return array(
					'status'  => 4,
					'message' => ( isset( $result['body']['errors'][0]['message'] ) ) ? $result['body']['errors'][0]['message'] : WFCO_Common::get_call_object( $this->connector, $this->call )->get_random_api_error(),
				);
			}

			return array(
				'status'  => '',
				'message' => ( isset( $result['body']['errors'][0]['message'] ) ) ? $result['body']['errors'][0]['message'] : WFCO_Common::get_call_object( $this->connector, $this->call )->get_random_api_error(),
			);

		}

		return array(
			'status'  => '',
			'message' => WFCO_Common::get_call_object( $this->connector, $this->call )->get_random_api_error(),
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_DR_Rmv_From_Campaign';
