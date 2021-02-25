<?php

final class BWFAN_WCM_Delete_Membership extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Remove From Membership Plan', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action removes the membership plan from the user', 'autonami-automations-pro' );
		$this->required_fields = array( 'user_id', 'plan_id' );

		// Excluded events which this action does not supports.
		$this->included_events = array(
			'wcm_membership_created',
			'wcm_status_changed',
			'elementor_form_submit',
			'gf_form_submit',
			'ac_webhook_received',
			'drip_webhook_received',
		);
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'membership_plans', $data );
		}
	}

	public function get_view_data() {
		$plans       = wc_memberships_get_membership_plans();
		$plans_array = [];
		foreach ( $plans as $plan ) {
			$plans_array[ $plan->get_id() ] = $plan->get_formatted_name();
		}

		return $plans_array;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            selected_plan = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'plan')) ? data.actionSavedData.data.plan : '';
            #>
            <div class="bwfan-<?php echo esc_html__( $this->get_slug() ); ?>">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Membership Plan', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][plan]">
                    <option value=""><?php echo esc_html__( 'Choose New Plan', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.actionFieldsOptions, 'membership_plans') && _.isObject(data.actionFieldsOptions.membership_plans) ) {
                    _.each( data.actionFieldsOptions.membership_plans, function( value, key ){
                    selected = (key == selected_plan) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
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
		$data_to_set            = array();
		$data_to_set['user_id'] = $task_meta['global']['user_id'];
		$data_to_set['plan_id'] = $task_meta['data']['plan'];

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
		$this->set_data( $action_data['processed_data'] );
		$result = $this->process();

		if ( true === $result ) {
			return array(
				'status'  => 3,
				'message' => 'User Membership deleted',
			);
		}

		return $result;
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

		$plan_id = absint( $this->data['plan_id'] );

		$user = get_userdata( $this->data['user_id'] );
		if ( false === $user ) {
			return array(
				'status'  => 4,
				'message' => __( 'User doesn\'t exists', 'autonami-automations-pro' ),
			);
		}

		$membership = $plan_id ? wc_memberships_get_user_membership( $user->ID, $plan_id ) : false;
		//To execute this action user's current membership must exists
		if ( ! $membership ) {
			return array(
				'status'  => 4,
				'message' => __( 'Membership doesn\'t exists with the selected plan', 'autonami-automations-pro' ),
			);
		}

		$membership_id = $membership->get_id();

		$success = wp_delete_post( $membership_id, true );

		if ( $success ) {
			return true;
		}

		return array(
			'status'  => 4,
			'message' => __( 'Unable to delete user membership', 'autonami-automations-pro' ),
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_WCM_Delete_Membership';
