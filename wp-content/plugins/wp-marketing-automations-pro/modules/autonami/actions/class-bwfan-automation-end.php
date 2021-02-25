<?php

final class BWFAN_Automation_End extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'End Automation', 'autonami-automations-pro' );
		$this->action_desc     = __( 'End an Autonami Automation', 'autonami-automations-pro' );
		$this->required_fields = array( 'email' );

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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'automations_options', $data );
		}
	}

	public function get_view_data() {
		$automations = BWFAN_Core()->automations->get_active_automations();
		if ( empty( $automations ) ) {
			return array();
		}

		$automations_to_return = array();
		foreach ( $automations as $automation ) {
			$automations_to_return[ $automation['id'] ] = $automation['meta']['title'];
		}

		return $automations_to_return;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_automation = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'selected_automation')) ? data.actionSavedData.data.selected_automation : '';
            #>
            <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Select Automation', 'autonami-automations-pro' ); ?></label>
            <select required id="" class="bwfan-input-wrapper bwfan-single-select" name="bwfan[{{data.action_id}}][data][selected_automation]">
                <#
                if(_.has(data.actionFieldsOptions, 'automations_options') && _.isObject(data.actionFieldsOptions.automations_options) ) {
                _.each( data.actionFieldsOptions.automations_options, function( value, key ){
                selected = (key == selected_automation) ? 'selected' : '';
                #>
                <option value="{{key}}" {{selected}}>{{value}} (#{{key}})</option>
                <# })
                }
                #>
            </select>
            <div class="clearfix bwfan_field_desc bwfan-mb10">
                Any scheduled tasks for the selected automation will be removed for the user.
            </div>
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
		$data_to_set                   = array();
		$data_to_set['end_automation'] = $task_meta['data']['selected_automation'];

		if ( isset( $task_meta['global']['phone'] ) && ! empty( $task_meta['global']['phone'] ) ) {
			$data_to_set['phone'] = $task_meta['global']['phone'];
		}

		$data_to_set['email'] = $task_meta['global']['email'];
		if ( ! is_email( $data_to_set['email'] ) && isset( $task_meta['global']['user_id'] ) ) {
			$data_to_set['email'] = ( get_user_by( 'ID', $task_meta['global']['user_id'] ) )->user_email;
		}

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
		$action_data['processed_data']['task_id'] = absint( $action_data['task_id'] );
		$this->set_data( $action_data['processed_data'] );
		$result = $this->process();

		if ( ! is_array( $result ) ) {
			$email   = is_email( $this->data['email'] ) ? ' Email: ' . $this->data['email'] : '';
			$phone   = isset( $this->data['phone'] ) && ! empty( $this->data['phone'] ) ? ' Phone: ' . $this->data['phone'] : '';
			$message = __( $result . ' tasks having were deleted successfully, for' . $email . $phone, 'autonami-automation-pro' );

			return array(
				'status'  => 3,
				'message' => $message
			);
		}

		return array(
			'status'  => 4,
			'message' => __( is_array( $result ) && isset( $result['message'] ) ? $result['message'] : 'Something went wrong while ending automation: ' . $this->data['automation_id'], 'autonami-automations-pro' ),
		);
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

		$automation_id = absint( $this->data['end_automation'] );
		$tasks         = array();
		if ( isset( $this->data['email'] ) && is_email( $this->data['email'] ) ) {
			$email_tasks = BWFAN_Common::get_schedule_task_by_email( array( $automation_id ), $this->data['email'] );
			$tasks       = isset( $email_tasks[ $automation_id ] ) && ! empty( $email_tasks[ $automation_id ] ) ? $email_tasks[ $automation_id ] : $tasks;
		}

		if ( isset( $this->data['phone'] ) && ! empty( $this->data['phone'] ) ) {
			$phone_tasks = BWFAN_Common::get_schedule_task_by_phone( array( $this->data['end_automation'] ), $this->data['phone'] );
			$tasks       = isset( $phone_tasks[ $automation_id ] ) && ! empty( $phone_tasks[ $automation_id ] ) ? array_replace( $tasks, $phone_tasks[ $automation_id ] ) : $tasks;
		}

		if ( ! is_array( $tasks ) || 0 === count( $tasks ) ) {
			return array( 'message' => 'No tasks found' );
		}

		$delete_tasks = array();
		foreach ( $tasks as $task ) {
			$delete_tasks[] = absint( $task['ID'] );
		}

		/** Unset Current task */
		if ( absint( $this->data['end_automation'] ) === absint( $this->data['automation_id'] ) && false !== ( $key = array_search( $this->data['task_id'], $delete_tasks, true ) ) ) {
			unset( $delete_tasks[ $key ] );
		}

		if ( 0 === count( $delete_tasks ) ) {
			return array( 'message' => 'No tasks found' );
		}

		BWFAN_Core()->tasks->delete_tasks( $delete_tasks );

		return count( $delete_tasks );
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Automation_End';
