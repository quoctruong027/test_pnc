<?php

final class BWFAN_GR_Create_Contact extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Create Contact', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates a contact', 'autonami-automations-connectors' );
		$this->action_priority = 10;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
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

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_list = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'list_id')) ? data.actionSavedData.data.list_id : '';
            selected_first_name = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'first_name')) ? data.actionSavedData.data.first_name : '';
            selected_last_name = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'last_name')) ? data.actionSavedData.data.last_name : '';
            selected_email = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'email')) ? data.actionSavedData.data.email : '';
            #>

            <label for="" class="bwfan-label-title">
				<?php esc_html_e( 'Email', 'wp-marketing-automations' ); ?>
				<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </label>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                <input required type="text" class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][email]" placeholder="Email" value="{{selected_email}}"/>
            </div>
            <label for="" class="bwfan-label-title">
				<?php esc_html_e( 'First Name (optional)', 'wp-marketing-automations' ); ?>
				<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </label>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                <input required type="text" class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][first_name]" placeholder="First Name" value="{{selected_first_name}}"/>
            </div>
            <label for="" class="bwfan-label-title">
				<?php esc_html_e( 'Last Name (optional)', 'wp-marketing-automations' ); ?>
				<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </label>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                <input required type="text" class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][last_name]" placeholder="Last Name" value="{{selected_last_name}}"/>
            </div>
            <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-mb-15">
                <label class="bwfan-label-title">Select List</label>
                <div>
                    <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][list_id]">
                        <option value=""><?php echo esc_html__( 'Default List', 'autonami-automations-connectors' ); ?></option>
                        <#
                        if(_.has(data.actionFieldsOptions, 'list_options') && _.isObject(data.actionFieldsOptions.list_options) ) {
                        _.each( data.actionFieldsOptions.list_options, function( value, key ){
                        selected = (key == selected_list) ? 'selected' : '';
                        #>
                        <option value="{{key}}" {{selected}}>{{value}}</option>
                        <# })
                        }
                        #>
                    </select>
                </div>
                <div class="bwfan_field_desc bwfan-mb10">Select the list where contact should be created.</div>
            </div>
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
		$data_to_set['list_id'] = empty( $task_meta['data']['list_id'] ) ? $integration_object->get_settings( 'default_list' ) : $task_meta['data']['list_id'];
		if ( ! empty( $task_meta['data']['first_name'] ) || ! empty( $task_meta['data']['last_name'] ) ) {
			$data_to_set['name'] = ( isset( $task_meta['data']['first_name'] ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['first_name'] ) : '' ) . ' ' . ( $task_meta['data']['last_name'] ? BWFAN_Common::decode_merge_tags( $task_meta['data']['last_name'] ) : '' );
		}
		$data_to_set['email'] = BWFAN_Common::decode_merge_tags( $task_meta['data']['email'] );
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

		if ( isset( $result['response'] ) && 200 !== absint( $result['response'] ) ) {
			$message = ( 502 === absint( $result['response'] ) ) ? $result['body'][0] : __('Unknown Error Occurred','autonami-automations-connectors')
			;

			return array(
				'status'  => 4,
				'message' => __( $message, 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['response'] ) && 200 === absint( $result['response'] ) ) {
			return array(
				'status'  => 3,
				'message' => isset( $result['body']['result_message'] ) ? $result['body']['result_message'] : __( 'Contact created successfully!', 'autonami-automations-connectors' ),
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
return 'BWFAN_GR_Create_Contact';
