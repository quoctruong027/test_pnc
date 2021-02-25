<?php

final class BWFAN_AFFWP_Change_Referral_Status extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Change Referral Status', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action changes affiliate\'s status', 'autonami-automations-pro' );
		$this->required_fields = array( 'referral_id', 'referral_status' );

		// Excluded events which this action does not supports.
		$this->included_events = array(
			'affwp_makes_sale',
			'affwp_referral_rejected',
			'affwp_report',
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'referral_status_options', $data );
		}
	}

	public function get_view_data() {
		$statuses = array(
			'paid'     => __( 'Paid', 'affiliate-wp' ),
			'unpaid'   => __( 'Unpaid', 'affiliate-wp' ),
			'rejected' => __( 'Rejected', 'affiliate-wp' ),
			'pending'  => __( 'Pending', 'affiliate-wp' ),
		);

		return $statuses;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            selected_referral_status = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'referral_status')) ? data.actionSavedData.data.referral_status : '';
            #>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Status', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][referral_status]">
                    <option value=""><?php echo esc_html__( 'Choose Status', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.actionFieldsOptions, 'referral_status_options') && _.isObject(data.actionFieldsOptions.referral_status_options) ) {
                    _.each( data.actionFieldsOptions.referral_status_options, function( value, key ){
                    selected = (key == selected_referral_status) ? 'selected' : '';
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
		$data_to_set['referral_status'] = $task_meta['data']['referral_status'];
		$data_to_set['referral_id']     = $task_meta['global']['referral_id'];

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
				'status' => 3,
			);
		}

		return array(
			'status'  => 4,
			'message' => __( 'Referral status couldn\'t be changed', 'autonami-automations-pro' ),
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

		return affwp_set_referral_status( $this->data['referral_id'], $this->data['referral_status'] );
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_AFFWP_Change_Referral_Status';
