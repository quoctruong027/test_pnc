<?php

final class BWFAN_DR_Create_Subscriber extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Create / Update Subscriber', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates or updates a subscriber', 'autonami-automations-connectors' );
		$this->action_priority = 15;
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
		$data_to_set['access_token'] = $integration_object->get_settings( 'access_token' );
		$data_to_set['account_id']   = $integration_object->get_settings( 'account_id' );
		$data_to_set['first_name']   = BWFAN_Common::decode_merge_tags( $task_meta['data']['first_name'] );
		$data_to_set['last_name']    = BWFAN_Common::decode_merge_tags( $task_meta['data']['last_name'] );
		$data_to_set['email']        = BWFAN_Common::decode_merge_tags( $task_meta['data']['email'] );

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		/** handling response in case required field missing **/
		if ( isset( $result['response'] ) && 502 === $result['response'] ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['body']['errors'] ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Error: ' . $result['body']['errors'][0]['message'] . '. Error Code: ' . $result['body']['errors'][0]['code'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['body']['subscribers'] ) ) {
			return array(
				'status'  => 3,
				'message' => __( 'Subscriber created successfully!', 'autonami-automations-connectors' ),
			);
		}

		return array(
			'status'  => 4,
			'message' => __( 'Unknown API Error', 'autonami-automations-connectors' ),
		);

	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_DR_Create_Subscriber';
