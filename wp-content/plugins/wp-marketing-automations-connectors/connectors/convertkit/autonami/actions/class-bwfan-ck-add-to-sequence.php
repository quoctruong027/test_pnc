<?php

final class BWFAN_CK_Add_To_Sequence extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Add Subscriber To Sequence', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds a subscriber to the selected sequence', 'autonami-automations-connectors' );
		$this->action_priority = 15;
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

	/**
	 * Show the html fields for the current action.
	 */
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

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		return $this->add_subscriber_to_sequence();
	}

	public function add_subscriber_to_sequence() {
		$params = array(
			'api_secret' => $this->data['api_secret'],
			'email'      => $this->data['email'],
		);

		if ( isset( $this->data['first_name'] ) && '' !== $this->data['first_name'] ) {
			$params['first_name'] = $this->data['first_name'];
		}
		if ( isset( $this->data['fields'] ) && is_array( $this->data['fields'] ) && count( $this->data['fields'] ) > 0 ) {
			$params['fields'] = (object) $this->data['fields'];
		}
		if ( isset( $this->data['tags'] ) && is_array( $this->data['tags'] ) && count( $this->data['tags'] ) > 0 ) {
			$params['tags'] = $this->data['tags'];
		}
		if ( isset( $this->data['courses'] ) && is_array( $this->data['courses'] ) && count( $this->data['courses'] ) > 0 ) {
			$params['courses'] = $this->data['courses'];
		}

		$url = $this->get_endpoint() . '/' . $this->data['course_id'] . '/subscribe';
		$res = $this->make_wp_requests( $url, $params, array(), BWFAN_Load_Integrations::$POST );

		return $res;
	}

	/**
	 * The forms endpoint to fetch all forms.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'courses';
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
		$data_to_set               = array();
		$data_to_set['api_secret'] = $integration_object->get_settings( 'api_secret' );
		$data_to_set['course_id']  = $task_meta['data']['sequence_id'];
		$data_to_set['email']      = $task_meta['global']['email'];

		return $data_to_set;
	}

	protected function handle_response( $response, $call_object = null ) {
		if ( isset( $response['status'] ) ) {
			if ( 3 === $response['status'] ) {
				return $response;
			}

			$response['status'] = 4;
			if ( is_array( $response['message'] ) && isset( $response['message']['error'] ) ) {
				$response['message'] = isset( $response['message']['message'] ) ? $response['message']['error'] . ' : ' . $response['message']['message'] : '';
			}

			return $response;
		}
		if ( isset( $response['response'] ) && 200 === $response['response'] && isset( $response['body']['subscriber'] ) ) {
			$response = [
				'status'  => 3,
				'message' => __( 'Added Subscriber to Sequence', 'autonami-automations-connectors' ),
			];
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

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_CK_Add_To_Sequence';
