<?php

final class BWFAN_GR_Add_To_List extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Add Contact to List', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds the contact to the selected list', 'autonami-automations-connectors' );
		$this->action_priority = 50;
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'list_options', $data );
		}
	}

	public function get_view_data() {
		$campaigns = WFCO_Common::get_single_connector_data( $this->connector, 'lists' );

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
			selected_list_id = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'list_id')) ? data.actionSavedData.data.list_id : '';
			#>
            <label for="" class="bwfan-label-title">
				<?php
				echo esc_html__( 'Select List', 'autonami-automations-connectors' );
				$message = __( 'Select list to add contact to and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
				echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
            </label>
			<select required id="" class="bwfan-input-wrapper bwfan-single-select" name="bwfan[{{data.action_id}}][data][list_id]">
				<option value=""><?php echo esc_html__( 'Choose a Segment', 'autonami-automations-connectors' ); ?></option>
				<#
				if(_.has(data.actionFieldsOptions, 'list_options') && _.isObject(data.actionFieldsOptions.list_options) ) {
				_.each( data.actionFieldsOptions.list_options, function( value, key ){
				selected = (key == selected_list_id) ? 'selected' : '';
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
		$data_to_set['api_key'] = $integration_object->get_settings( 'api_key' );
		$data_to_set['list_id']        = empty( $task_meta['data']['list_id'] ) ? $integration_object->get_settings( 'default_list' ) : $task_meta['data']['list_id'];

		/** Set Email if global email is empty */
		$data_to_set['email'] = $task_meta['global']['email'];
		if ( empty( $data_to_set['email'] ) ) {
			$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

			$data_to_set['email'] = $user instanceof WP_User ? $user->user_email : '';
		}

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['code'] ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Error: ' . $result['body']['message'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['response'] ) && ( 200 !== absint( $result['response'] ) ) ) {
			$message = ( 502 === absint( $result['response'] ) ) ? $result['body'][0] : 'Unknown Error Occurred';

			return array(
				'status'  => 4,
				'message' => __( $message, 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['response'] ) && 200 === absint( $result['response'] ) ) {
			return array(
				'status'  => 3,
				'message' => isset( $result['body']['result_message'] ) ? $result['body']['result_message'] : __( 'Added to list successfully!', 'autonami-automations-connectors' ),
			);
		}

		BWFAN_Core()->logger->log( $result, 'failed-' . $this->get_slug() . '-action' );

		return array(
			'status'  => 4,
			'message' => __( 'Unknown Error: Check log failed-' . $this->get_slug() . '-action', 'autonami-automations-connectors' ),
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_GR_Add_To_List';
