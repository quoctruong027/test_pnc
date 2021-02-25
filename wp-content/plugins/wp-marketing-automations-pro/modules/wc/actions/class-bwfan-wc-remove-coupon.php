<?php

final class BWFAN_WC_Remove_Coupon extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Delete Coupon', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action deletes a WooCommerce coupon', 'autonami-automations-pro' );
		$this->required_fields = array( 'coupon_name' );

		$this->action_priority = 10;
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            entered_coupon_name = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'coupon_name')) ? data.actionSavedData.data.coupon_name : '';
            #>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php echo esc_html__( 'Coupon Name', 'autonami-automations-pro' ); ?>
					<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                </label>
                <input required type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][coupon_name]" value="{{entered_coupon_name}}"/>
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
		$this->set_data_for_merge_tags( $task_meta );
		$data_to_set                = array();
		$data_to_set['email']       = $task_meta['global']['email'];
		$data_to_set['coupon_name'] = BWFAN_Common::decode_merge_tags( $task_meta['data']['coupon_name'] );

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

		if ( $this->fields_missing ) {
			return array(
				'status'  => 4,
				'message' => $result['body'][0],
			);
		}

		if ( is_array( $result ) && count( $result ) > 0 ) { // Error in coupon deletion, coupon does not exists.
			$status = array(
				'status'  => 4,
				'message' => $result['err_msg'],
			);

			return $status;
		}

		return array(
			'status'  => 3,
			'message' => "Coupon {$this->data['coupon_name']} deleted.",
		);
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			$this->fields_missing = true;

			return $this->show_fields_error();
		}

		$data        = $this->data;
		$coupon_name = $data['coupon_name'];
		$coupon      = new WC_Coupon( $coupon_name );
		$coupon_id   = $coupon->get_id();

		if ( 0 === $coupon_id ) { // Coupon does not exists
			return [
				'err_msg' => __( 'Coupon does not exists', 'autonami-automations-pro' )
			];
		}

		$coupons_email = $coupon->get_email_restrictions();

		if ( is_array( $coupons_email ) && count( $coupons_email ) > 0 ) {
			$index = array_search( $data['email'], $coupons_email, true );
			if ( false !== $index ) {
				unset( $coupons_email[ $index ] );
			}
		}

		if ( is_array( $coupons_email ) && count( $coupons_email ) > 0 ) {
			$coupon->set_email_restrictions( $coupons_email );
			$coupon->save();
		} else {
			wp_delete_post( $coupon_id, true );
		}

		return $coupon_id;
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_WC_Remove_Coupon';
