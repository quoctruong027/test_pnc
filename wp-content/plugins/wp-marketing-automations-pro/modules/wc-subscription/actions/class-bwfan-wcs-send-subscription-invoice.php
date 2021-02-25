<?php

final class BWFAN_WCS_Send_Subscription_Invoice extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Send Subscription Invoice', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action sends the subscription invoice email to the user', 'autonami-automations-pro' );
		$this->required_fields = array( 'subscription_id' );
		$this->action_priority = 2;

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
		$status = array(
			'status'  => $result['status'],
			'message' => $result['msg'],
		);

		return $status;
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

		return $this->send_invoice();
	}

	/**
	 * Change subscription status.
	 *
	 * subscription_id, status are required.
	 *
	 * @return array|bool
	 */
	public function send_invoice() {
		$result       = [];
		$subscription = wcs_get_subscription( $this->data['subscription_id'] );

		if ( ! $subscription ) {
			$result['msg']    = __( 'Subscription does not exists', 'autonami-automations-pro' );
			$result['status'] = 4;

			return $result;
		}

		do_action( 'woocommerce_before_resend_order_emails', $subscription, 'customer_invoice' );

		WC()->payment_gateways();
		WC()->shipping();
		WC()->mailer()->customer_invoice( $subscription );

		do_action( 'woocommerce_after_resend_order_email', $subscription, 'customer_invoice' );

		return true;
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_WCS_Send_Subscription_Invoice';
