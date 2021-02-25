<?php

final class BWFAN_AFFWP_Change_Affiliate_Rate extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Change Affiliate Rate', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action changes the affiliate\'s referral rate', 'autonami-automations-pro' );
		$this->required_fields = array( 'affiliate_id', 'rate_type', 'rate' );

		// Excluded events which this action does not supports.
		$this->included_events = array(
			'affwp_makes_sale',
			'affwp_referral_rejected',
			'affwp_report',
			'affwp_application_approved',
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'rate_types_options', $data );
		}
	}

	public function get_view_data() {
		$types = affwp_get_affiliate_rate_types();

		return $types;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            selected_rate_type = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'rate_type')) ? data.actionSavedData.data.rate_type : '';
            rate = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'rate')) ? data.actionSavedData.data.rate : '';
            #>
            <div class="bwfan-<?php echo esc_html__( $this->get_slug() ); ?>">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Rate Type', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][rate_type]">
                    <option value=""><?php echo esc_html__( 'Choose Type', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.actionFieldsOptions, 'rate_types_options') && _.isObject(data.actionFieldsOptions.rate_types_options) ) {
                    _.each( data.actionFieldsOptions.rate_types_options, function( value, key ){
                    selected = (key == selected_rate_type) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
            </div>
            <div class="bwfan-<?php echo esc_html__( $this->get_slug() ); ?>">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Rate', 'autonami-automations-pro' ); ?></label>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0">
                    <input type="text" required class="bwfan-input-wrapper" placeholder="<?php echo esc_html__( 'Affiliate new rate', 'autonami-automations-pro' ); ?>" name="bwfan[{{data.action_id}}][data][rate]" value="{{rate}}">
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
		$data_to_set                 = array();
		$data_to_set['rate_type']    = $task_meta['data']['rate_type'];
		$data_to_set['rate']         = $task_meta['data']['rate'];
		$data_to_set['affiliate_id'] = $task_meta['global']['affiliate_id'];

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
			'message' => __( 'Affiliate rate couldn\'t be changed', 'autonami-automations-pro' ),
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

		$affiliate = affwp_get_affiliate( $this->data['affiliate_id'] );
		if ( false === $affiliate ) {
			return false;
		}

		affiliate_wp()->affiliates->update( $affiliate->ID, array(
			'rate_type' => $this->data['rate_type'],
			'rate'      => $this->data['rate'],
		), '', 'affiliate' );

		return true;
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_AFFWP_Change_Affiliate_Rate';
