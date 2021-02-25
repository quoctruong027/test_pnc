<?php

final class BWFAN_AC_Add_To_Automation extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Add Contact To Automation', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds a contact to the selected automation', 'autonami-automations-connectors' );
		$this->action_priority = 25;
	}

	public function load_hooks() {
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'automation_id_options', $data );
		}
	}

	public function get_view_data() {
		$automations = WFCO_Common::get_single_connector_data( $this->connector, 'automations' );

		return $automations;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_automation_id = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'automation_id')) ? data.actionSavedData.data.automation_id : '';
            #>
            <label for="" class="bwfan-label-title">
				<?php
				echo esc_html__( 'Select Automation', 'autonami-automations-connectors' );
				$message = __( 'Select an automation to add contact to and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
				echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
            </label>
            <select required id="" class="bwfan-input-wrapper bwfan-single-select" name="bwfan[{{data.action_id}}][data][automation_id]">
                <option value=""><?php echo esc_html__( 'Choose An Automation', 'autonami-automations-connectors' ); ?></option>
                <#
                if(_.has(data.actionFieldsOptions, 'automation_id_options') && _.isObject(data.actionFieldsOptions.automation_id_options) ) {
                _.each( data.actionFieldsOptions.automation_id_options, function( value, key ){
                selected = (key == selected_automation_id) ? 'selected' : '';
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
	 * @param $integration_object BWFAN_Integration
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data( $integration_object, $task_meta ) {
		$data_to_set            = array();
		$data_to_set['api_key'] = $integration_object->get_settings( 'api_key' );
		$data_to_set['api_url'] = $integration_object->get_settings( 'api_url' );
		$data_to_set['a_id']    = $task_meta['data']['automation_id'];
		$data_to_set['email']   = $task_meta['global']['email'];

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['result_code'] ) && 1 === intval( $result['body']['result_code'] ) ) {
			return array(
				'status'  => 3,
				'message' => isset( $result['body']['result_message'] ) ? $result['body']['result_message'] : __( 'Contact successfully added to automation', 'autonami-automations-connectors' ),
			);
		}

		/** in case required field missing */
		if ( ( isset( $result['response'] ) && 502 === absint( $result['response'] ) ) && is_array( $result['body'] ) ) {

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

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_AC_Add_To_Automation';
