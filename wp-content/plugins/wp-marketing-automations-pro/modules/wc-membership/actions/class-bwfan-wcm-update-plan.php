<?php

final class BWFAN_WCM_Update_Plan extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Assign / Update Membership Plan', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action assigns/ update the plan of a user\'s active membership. If no active membership exists a new membership will be created', 'autonami-automations-pro' );
		$this->required_fields = array( 'user_id', 'to_plan', 'from_plan' );

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
            selected_plan = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'to_plan')) ? data.actionSavedData.data.plan : '';
            existing_plan = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'from_plan')) ? data.actionSavedData.data.plan : '';
            #>
            <div class="bwfan-<?php echo esc_html__( $this->get_slug() ); ?>">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'New Membership Plan', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][to_plan]">
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
                <div class="clearfix bwfan_field_desc">
                    If selected membership plan is already active on one of the user's memberships, then this action will be ignored.
                </div>
            </div>
            <hr/>
            <div class="bwfan-<?php echo esc_html__( $this->get_slug() ); ?>">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Existing Membership Plan', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][from_plan]">
                    <option value=""><?php echo esc_html__( 'Choose Existing Plan', 'autonami-automations-pro' ); ?></option>
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
                <div class="clearfix bwfan_field_desc">
                    Leave this blank if you want to create new user membership
                </div>
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
		$data_to_set              = array();
		$data_to_set['to_plan']   = $task_meta['data']['to_plan'];
		$data_to_set['from_plan'] = $task_meta['data']['from_plan'];
		$data_to_set['user_id']   = $task_meta['global']['user_id'];

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
				'message' => 'Membership plan is assigned / updated to user\'s membership',
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

		$new_plan_id      = absint( $this->data['to_plan'] );
		$existing_plan_id = absint( $this->data['from_plan'] );

		$user = get_userdata( $this->data['user_id'] );
		if ( false === $user ) {
			return array(
				'status'  => 4,
				'message' => __( 'User doesn\'t exists', 'autonami-automations-pro' ),
			);
		}

		$selected_plan = $new_plan_id ? wc_memberships_get_membership_plan( $this->data['to_plan'] ) : false;
		//Selected plan must exists in order to execute this action
		if ( false === $selected_plan ) {
			return array(
				'status'  => 4,
				'message' => __( 'Membership Plan doesn\'t exists', 'autonami-automations-pro' ),
			);
		}

		$new_membership_by_plan = wc_memberships_get_user_membership( $user->ID, $selected_plan );
		//Plan is already included in one of the user's membership
		if ( $new_membership_by_plan ) {
			return array(
				'status'  => 4,
				'message' => __( 'Membership plan is already included in user\'s membership', 'autonami-automations-pro' ),
			);
		}

		$existing_membership = $existing_plan_id ? wc_memberships_get_user_membership( $user->ID, $existing_plan_id ) : false;
		if ( $existing_membership ) {
			$membership_updated = $this->change_membership_plan( $existing_membership, $selected_plan );

			if ( ! $membership_updated ) {
				return array(
					'status'  => 4,
					'message' => __( 'Unable to update plan to user\'s membership', 'autonami-automations-pro' ),
				);
			}

			return true;
		} else {
			$membership_created = $this->create_new_membership( $user, $selected_plan );
			if ( ! $membership_created ) {
				return array(
					'status'  => 4,
					'message' => __( 'Unable to assign plan to user', 'autonami-automations-pro' ),
				);
			}

			return true;
		}
	}

	/**
	 * Change membership plan of User Membership
	 *
	 * @param WC_Memberships_User_Membership $membership
	 * @param WC_Memberships_Membership_Plan $plan
	 *
	 * @return bool
	 */
	private function change_membership_plan( $membership, $plan ) {
		$updated = wp_update_post( array(
			'ID'          => $membership->get_id(),
			'post_parent' => $plan->get_id(),
		) );

		if ( $updated ) {
			return true;
		}

		return false;
	}

	/**
	 * Create new User Membership
	 *
	 * @param WP_User $user
	 * @param WC_Memberships_Membership_Plan $plan
	 *
	 * @return bool
	 */
	private function create_new_membership( $user, $plan ) {
		try {
			wc_memberships_create_user_membership( [
				'user_id' => $user->ID,
				'plan_id' => $plan->get_id(),
			] );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_WCM_Update_Plan';
