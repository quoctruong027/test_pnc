<?php

final class BWFAN_WCS_Change_Subscription_Status extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Change Subscription Status', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action changes the WooCommerce Subscription status', 'autonami-automations-pro' );
		$this->required_fields = array( 'subscription_id', 'subs_status' );
		$this->action_priority = 1;

		// Excluded events which this action does not supports.
		$this->included_events = array(
			'wcs_before_end',
			'wcs_before_renewal',
			'wcs_card_expiry',
			'wcs_created',
			'wcs_renewal_payment_complete',
			'wcs_renewal_payment_failed',
			'wcs_status_changed',
			'wcs_trial_end',
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'subscription_status_options', $data );
		}
	}

	public function get_view_data() {
		$all_status = wcs_get_subscription_statuses();

		return $all_status;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            selected_subscription_status = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'subscription_status')) ? data.actionSavedData.data.subscription_status : '';
            #>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Status', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][subscription_status]">
                    <option value=""><?php echo esc_html__( 'Choose Status', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.actionFieldsOptions, 'subscription_status_options') && _.isObject(data.actionFieldsOptions.subscription_status_options) ) {
                    _.each( data.actionFieldsOptions.subscription_status_options, function( value, key ){
                    selected = (key == selected_subscription_status) ? 'selected' : '';
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
		$data_to_set                    = array();
		$data_to_set['subs_status']     = $task_meta['data']['subscription_status'];
		$data_to_set['subscription_id'] = $task_meta['global']['wc_subscription_id'];

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

		if ( $result ) {
			return array(
				'status' => 3,
			);
		}

		return array(
			'status'  => $result['status'],
			'message' => $result['msg'],
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

		return $this->change_status();
	}

	/**
	 * Change subscription status.
	 *
	 * subscription_id, status are required.
	 *
	 * @return array|bool
	 */
	public function change_status() {
		$result = [];

		$subscription = wcs_get_subscription( $this->data['subscription_id'] );
		if ( ! $subscription ) {
			$result['msg']    = __( 'Subscription does not exists', 'autonami-automations-pro' );
			$result['status'] = 4;

			return $result;
		}

		try {
			$subscription->update_status( $this->data['subs_status'], sprintf( __( 'Subscription status changed by Autonami automation #%s.', 'autonami-automations-pro' ), $this->data['automation_id'] ) );
			$result = true;
		} catch ( Exception $error ) {
			$result['msg']    = $error->getMessage();
			$result['status'] = 4;
		}

		return $result;
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_WCS_Change_Subscription_Status';
