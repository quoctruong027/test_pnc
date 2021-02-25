<?php

final class BWFAN_ActiveCampaign_Integration extends BWFAN_Integration {

	private static $instance = null;
	protected $connector_slug = 'bwfco_activecampaign';
	protected $need_connector = true;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'ActiveCampaign', 'autonami-automations-connectors' );

		add_filter( 'bwfan_get_deal_id_wc', array( $this, 'get_deal_id_from_order_meta' ), 10, 2 );
	}

	/**
	 * Return class instance
	 *
	 * @return BWFAN_ActiveCampaign_Integration
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_deal_id_from_order_meta( $bool, $action_data ) {
		$order_id = BWFAN_Merge_Tag_Loader::get_data( 'wc_order_id' );
		$deal_id  = BWFAN_Woocommerce_Compatibility::get_order_meta( $order_id, '_bwfan_ac_deal_id' );
		if ( ! empty( $deal_id ) ) {
			return $deal_id;
		}

		return $bool;
	}

	/**
	 * Handle the responses for all the actions of this connector.
	 *
	 * @param $result
	 * @param $connector_slug
	 * @param $action_call_class_slug
	 *
	 * @return array
	 *
	 */
	public function handle_response( $result, $connector_slug, $action_call_class_slug, $action_data = null ) {
		if ( isset( $result['body']['errors'] ) && isset( $result['body']['errors'][0] ) ) {
			if ( isset( $result['body']['errors'][0]['message'] ) ) {
				return array(
					'status'  => 4,
					'message' => $result['body']['errors'][0]['message'],
				);
			}
			if ( isset( $result['body']['errors'][0]['title'] ) ) {
				return array(
					'status'  => 4,
					'message' => $result['body']['errors'][0]['title'],
				);
			}
		}
		// Required field missing error
		if ( isset( $result['bwfan_response'] ) ) {
			return array(
				'status'  => 4,
				'message' => $result['bwfan_response'],
			);
		}
		// Curl error
		if ( isset( $result['response'] ) && 500 === intval( $result['response'] ) ) {
			return array(
				'status'  => 0,
				'message' => $result['body'],
			);
		}
		if ( isset( $result['body']['result_message'] ) && 'Failed: Nothing is returned' === $result['body']['result_message'] ) {
			return array(
				'status'  => 4,
				'message' => $result['body']['result_message'],
			);
		}
		if ( isset( $result['bwfan_custom_message'] ) ) {
			return array(
				'status'  => 4,
				'message' => $result['bwfan_custom_message'],
			);
		}
		if ( isset( $result['bwfan_success_message'] ) ) {
			return array(
				'status' => 3,
			);
		}

		return $result;
	}

	protected function do_after_action_registration( BWFAN_Action $action_object ) {

		$action_object->connector = $this->connector_slug;
	}

}

BWFAN_Load_Integrations::register( 'BWFAN_ActiveCampaign_Integration' );
