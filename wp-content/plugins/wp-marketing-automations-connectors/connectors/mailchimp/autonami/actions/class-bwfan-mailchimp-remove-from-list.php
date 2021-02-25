<?php

final class BWFAN_Mailchimp_Remove_From_List extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Remove Contact from List', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action removes the contact from the selected list', 'autonami-automations-connectors' );
		$this->action_priority = 40;
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
		$lists = WFCO_Common::get_single_connector_data( $this->connector, 'lists' );

		return $lists;
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
	            $message = __( 'Select list to remove contact from and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
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
		$data_to_set            = array();
		$data_to_set['api_key'] = $integration_object->get_settings( 'api_key' );
		$data_to_set['list_id'] = empty( $task_meta['data']['list_id'] ) ? $integration_object->get_settings( 'default_list' ) : $task_meta['data']['list_id'];

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

		if ( isset( $result['response'] ) && 204 === absint( $result['response'] ) ) {
			return array(
				'status'  => 3,
				'message' => __( 'Removed from list successfully!', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$error_title  = ( is_array( $result['body'] ) && isset( $result['body']['title'] ) ) ? $result['body']['title'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $error_title ? $error_title : $unknown_message ) . $response_code,
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Mailchimp_Remove_From_List';
