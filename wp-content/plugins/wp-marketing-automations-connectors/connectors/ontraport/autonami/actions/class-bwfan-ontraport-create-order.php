<?php

final class BWFAN_Ontraport_Create_Order extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Add Order', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds the order', 'autonami-automations-connectors' );
		$this->action_priority = 70;
		$this->included_events = array(
			'wc_new_order',
			'wc_product_purchased',
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
	 * @param $integration_object BWFAN_Integration
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data( $integration_object, $task_meta ) {

		$data_to_set            = array();
		$data_to_set['app_id']  = $integration_object->get_settings( 'app_id' );
		$data_to_set['api_key'] = $integration_object->get_settings( 'api_key' );

		/** Set Email if global email is empty */
		$data_to_set['email'] = $task_meta['global']['email'];
		if ( empty( $data_to_set['email'] ) ) {
			$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

			$data_to_set['email'] = $user instanceof WP_User ? $user->user_email : '';
		}

		$data_to_set['order_id'] = $task_meta['global']['order_id'];

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['response'] ) && 502 === absint( $result['response'] ) ) {
			return array(
				'status'  => 4,
				'message' => __( $result['body'][0], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['body']['fault'] ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Error: ' . $result['body']['fault']['faultstring'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['response'] ) && 200 !== absint( $result['response'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? __( 'Error: ' . $result['body'][0], 'autonami-automations-connectors' ) : __( 'Unable to update Custom Fields', 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['response'] ) && ( 200 === absint( $result['response'] ) || 201 === absint( $result['response'] ) ) ) {
			return array(
				'status'  => 3,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Order added successfully!', 'autonami-automations-connectors' ),
			);
		}


		return array(
			'status'  => 4,
			'message' => __( 'Unknown Error: Check log failed-' . $this->get_slug() . '-action', 'autonami-automations-connectors' ),
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Ontraport_Create_Order';
