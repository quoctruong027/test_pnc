<?php

final class BWFAN_CK_Rmv_From_Sequence extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Remove Subscriber from Sequence', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action removes a subscriber from the selected sequence', 'autonami-automations-connectors' );
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'sequence_id_options', $data );
		}
	}

	public function get_view_data() {
		$sequences = WFCO_Common::get_single_connector_data( $this->connector, 'sequences' );

		return $sequences;
	}


	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_sequence_id = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'sequence_id')) ? data.actionSavedData.data.sequence_id : '';
            #>
            <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Select Sequence', 'autonami-automations-connectors' ); ?></label>
            <select required id="" class="bwfan-input-wrapper bwfan-single-select" name="bwfan[{{data.action_id}}][data][sequence_id]">
                <option value=""><?php echo esc_html__( 'Choose A Sequence', 'autonami-automations-connectors' ); ?></option>
                <#
                if(_.has(data.actionFieldsOptions, 'sequence_id_options') && _.isObject(data.actionFieldsOptions.sequence_id_options) ) {
                _.each( data.actionFieldsOptions.sequence_id_options, function( value, key ){
                selected = (key == selected_sequence_id) ? 'selected' : '';
                #>
                <option value="{{key}}" {{selected}}>{{value}}</option>
                <# })
                }
                #>
            </select>
        </script>
		<?php
	}

	public function make_data( $integration_object, $task_meta ) {
		$data_to_set               = array();
		$data_to_set['api_secret'] = $integration_object->get_settings( 'api_secret' );
		$data_to_set['course_id']  = $task_meta['data']['sequence_id'];
		$data_to_set['email']      = $task_meta['global']['email'];

		return $data_to_set;
	}

	protected function handle_response( $response, $call_object = null ) {

		if(isset($response['status'])){
			return $response;
		}

		if ( isset( $response['response'] ) && 200 === $response['response'] ) {
			return array(
				'status'  => 3,
				'message' => __( 'Subscriber removed from sequence', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $response['response'] ) && is_array( $response['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $response['body'][0] ) ? $response['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $response['response'];
		$result_message  = ( is_array( $response['body'] ) && isset( $response['body']['error'] ) ) ? $response['body']['error'] : false;
		$message         = ( is_array( $response['body'] ) && isset( $response['body']['message'] ) ) ? $response['body']['message'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $result_message ? $result_message : ( false !== $message ? $message : $unknown_message ) ) . $response_code,
		);

	}

}

/** Deprecated this action, as it is removing subscriber from all the sequences, not from the specified one */
//return 'BWFAN_CK_Rmv_From_Sequence';
