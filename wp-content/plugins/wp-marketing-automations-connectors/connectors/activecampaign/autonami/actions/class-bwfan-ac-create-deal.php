<?php

final class BWFAN_AC_Create_Deal extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		// Nice name of the action which will shown in UI
		$this->action_name     = __( 'Create Deal', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates a deal. Works with WooCommerce order related events', 'autonami-automations-connectors' );
		$this->included_events = array(
			'wc_new_order',
			'wc_order_note_added',
			'wc_order_status_change',
			'wc_product_purchased',
			'wc_product_refunded',
			'wc_product_stock_reduced',
		);
		$this->action_priority = 45;
	}

	public function load_hooks() {
		// this action localizes the data for script template which is then used to create the UI for the current action
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'pipeline_options', $data['pipelines'] );
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'stage_options', $data['stages'] );
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'pipelines_stages_options', $data['pipelines_stages'] );
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'owner_id_options', $data['owner_ids'] );
		}
	}

	public function get_view_data() {
		$pipelines        = WFCO_Common::get_single_connector_data( $this->connector, 'pipelines' );
		$stages           = WFCO_Common::get_single_connector_data( $this->connector, 'stages' );
		$pipelines_stages = WFCO_Common::get_single_connector_data( $this->connector, 'pipelines_stages' );
		$owner_ids        = WFCO_Common::get_single_connector_data( $this->connector, 'owner_ids' );

		return array(
			'pipelines'        => $pipelines,
			'stages'           => $stages,
			'pipelines_stages' => $pipelines_stages,
			'owner_ids'        => $owner_ids,
		);
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#

            selected_event = BWFAN_Auto.uiDataDetail.trigger.event;
            default_deal_value = '';
            default_deal_currency = '';

            if(selected_event=='wc_new_order' || selected_event=='wc_order_status_change' || selected_event=='wc_product_purchased'){
            default_deal_value = '{{order_total}}';
            default_deal_currency = '{{order_currency}}';
            }

            selected_stage_id = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'stage_id')) ? data.actionSavedData.data.stage_id : '';
            selected_owner_id = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'owner_id')) ? data.actionSavedData.data.owner_id : '';
            entered_deal_name = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'deal_name')) ? data.actionSavedData.data.deal_name : '';
            entered_deal_value = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'deal_value')) ? data.actionSavedData.data.deal_value : default_deal_value;
            entered_deal_currency = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'deal_currency')) ? data.actionSavedData.data.deal_currency : default_deal_currency;
            entered_deal_description = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'deal_description')) ? data.actionSavedData.data.deal_description : '';

            if(_.has(data.actionFieldsOptions, 'pipeline_options') && _.isObject(data.actionFieldsOptions.pipeline_options) ) {
            pipeline_options = data.actionFieldsOptions.pipeline_options;
            stage_options = data.actionFieldsOptions.stage_options;
            pipelines_stages_options = data.actionFieldsOptions.pipelines_stages_options;
            #>

            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Deal Title', 'autonami-automations-connectors' );
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <input required type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][deal_name]" value="{{entered_deal_name}}"/>
            </div>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Deal Description', 'autonami-automations-connectors' );
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <textarea required class="bwfan-input-wrapper" rows="4" placeholder="<?php echo esc_attr__( 'Message', 'autonami-automations-connectors' ); ?>" name="bwfan[{{data.action_id}}][data][deal_description]">{{entered_deal_description}}</textarea>
            </div>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Deal Value', 'autonami-automations-connectors' );
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <input required type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][deal_value]" value="{{entered_deal_value}}"/>
            </div>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Deal Currency', 'autonami-automations-connectors' );
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <input required type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][deal_currency]" value="{{entered_deal_currency}}"/>
            </div>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Select Deal Stage', 'autonami-automations-connectors' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][stage_id]">
                    <option value=""><?php echo esc_html__( 'Select a stage', 'autonami-automations-connectors' ); ?></option>
                    <# _.each( pipelines_stages_options, function( value, key ){ #>
                    <optgroup label="{{pipeline_options[key]}}">
                        <# _.each( value, function( value1, key1 ){
                        select_value = key+'_'+value1;

                        selected = (selected_stage_id == select_value) ? 'selected' : '';
                        #>
                        <option value="{{select_value}}" {{selected}}>{{stage_options[value1]}}</option>
                        <# }) #>
                    </optgroup>
                    <# }) #>
                </select>
            </div>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Select Deal Owner', 'autonami-automations-connectors' );
					$message = __( 'Select an owner and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
					echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][owner_id]">
                    <option value=""><?php echo esc_html__( 'Choose Owner', 'autonami-automations-connectors' ); ?></option>
                    <#
                    if(_.has(data.actionFieldsOptions, 'owner_id_options') && _.isObject(data.actionFieldsOptions.owner_id_options) ) {
                    _.each( data.actionFieldsOptions.owner_id_options, function( value, key ){
                    selected = (key == selected_owner_id) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
            </div>
            <#
            }
            #>

        </script>
		<?php
	}

	/**
	 * Make all the data which is required by the current action.
	 * This data will be used while executing the task of this action.
	 *
	 * @param $integration_object BWFAN_Integration
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data( $integration_object, $task_meta ) {
		$data_to_set                = array();
		$data_to_set['api_key']     = $integration_object->get_settings( 'api_key' );
		$data_to_set['api_url']     = $integration_object->get_settings( 'api_url' );
		$data_to_set['email']       = $task_meta['global']['email'];
		$data_to_set['title']       = BWFAN_Common::decode_merge_tags( $task_meta['data']['deal_name'] );
		$stage_details              = explode( '_', $task_meta['data']['stage_id'] );
		$pipeline_id                = $stage_details[0];
		$stage_id                   = $stage_details[1];
		$data_to_set['pipeline_id'] = $pipeline_id;
		$data_to_set['order_id']    = $task_meta['global']['order_id'];
		$data_to_set['stage_id']    = $stage_id;
		$data_to_set['owner_id']    = $task_meta['data']['owner_id'];
		$data_to_set['deal_value']  = intval( BWFAN_Common::decode_merge_tags( $task_meta['data']['deal_value'] ) ) * 100;
		$data_to_set['currency']    = strtolower( BWFAN_Common::decode_merge_tags( $task_meta['data']['deal_currency'] ) );
		$data_to_set['description'] = ( ! empty( $task_meta['data']['deal_description'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['deal_description'] ) : '';

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['deal'] ) && is_array( $result['body']['deal'] ) && count( $result['body']['deal'] ) > 0 ) {

			$deal_id  = $result['body']['deal']['id'];
			$order_id = BWFAN_Merge_Tag_Loader::get_data( 'wc_order_id' );
			if ( ! empty( $order_id ) && is_numeric( $order_id ) ) {
				update_post_meta( $order_id, '_bwfan_ac_deal_id', $deal_id );
			}

			return array(
				'status'  => 3,
				'message' => isset( $result['body']['result_message'] ) ? $result['body']['result_message'] : __( 'Deal successfully created', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$result_message  = ( is_array( $result['body'] ) && isset( $result['body']['result_message'] ) ) ? $result['body']['result_message'] : false;
		$message         = ( is_array( $result['body'] ) && isset( $result['body']['message'] ) ) ? $result['body']['message'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $result_message ? $result_message : ( false !== $message ? $message : $unknown_message ) ) . $response_code,
		);
	}

}

return 'BWFAN_AC_Create_Deal';
