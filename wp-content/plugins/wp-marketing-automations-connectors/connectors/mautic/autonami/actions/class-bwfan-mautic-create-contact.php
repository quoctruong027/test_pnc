<?php

final class BWFAN_Mautic_Create_Contact extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Create Contact', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates a contact', 'autonami-automations-connectors' );
		$this->action_priority = 10;
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
			selected_event_src = BWFAN_Auto.uiDataDetail.trigger.source;
			selected_event = BWFAN_Auto.uiDataDetail.trigger.event;

            ae = bwfan_automation_data.all_triggers_events;
            email_merge_tag ='';
            if(_.has(ae, selected_event_src) &&
            _.has(ae[selected_event_src], selected_event) &&
            _.has(ae[selected_event_src][selected_event], 'customer_email_tag')) {
            email_merge_tag = ae[selected_event_src][selected_event].customer_email_tag;
            }

            selected_first_name = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'first_name')) ? data.actionSavedData.data.first_name : '';
            selected_last_name = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'last_name')) ? data.actionSavedData.data.last_name : '';
            selected_email = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'email')) ? data.actionSavedData.data.email : email_merge_tag;
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
		$data_to_set                 = array();
		$data_to_set['site_url']     = $integration_object->get_settings( 'site_url' );
		$data_to_set['access_token'] = $integration_object->get_settings( 'access_token' );
		$data_to_set['first_name']   = BWFAN_Common::decode_merge_tags( $task_meta['data']['first_name'] );
		$data_to_set['last_name']    = BWFAN_Common::decode_merge_tags( $task_meta['data']['last_name'] );
		$data_to_set['email']        = BWFAN_Common::decode_merge_tags( $task_meta['data']['email'] );

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['contact'] ) && isset( $result['body']['contact']['id'] ) && ! empty( $result['body']['contact']['id'] ) ) {
			return array(
				'status'  => 3,
				'message' => __( 'Contact created successfully!', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$result_message  = ( is_array( $result['body'] ) && isset( $result['body']['errors'] ) ) ? $result['body']['errors'][0]['message'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $result_message ? $result_message : $unknown_message ) . $response_code,
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Mautic_Create_Contact';
