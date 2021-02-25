<?php

final class BWFAN_ConvertKit_Integration extends BWFAN_Integration {

	private static $ins = null;
	protected $connector_slug = 'bwfco_convertkit';
	protected $need_connector = true;

	private function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'Convertkit', 'autonami-automations-connectors' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 *
	 *
	 * /**
	 * Handle the responses for all the actions of this connector.
	 * Return 0 for try again, 2 for action halt, 3 for successful execution , 4 for permanent failure.
	 *
	 * @param $result
	 * @param $connector_slug
	 * @param $action_call_class_slug
	 * @param $action_data
	 *
	 * @return array
	 */
	public function handle_response( $result, $connector_slug, $action_call_class_slug, $action_data = null ) {
		if ( isset( $result['response'] ) ) {
			if ( 400 === $result['response'] && isset( $result['body']['error'] ) ) {
				return array(
					'status'  => 4,
					'message' => $result['body']['error'] . ' (' . $result['body']['message'] . ')',
				);

			} elseif ( isset( $result['response'] ) && 500 === $result['response'] ) {
				return array(
					'status'  => 0,
					'message' => $result['body'],
				);
			}
		}

		if ( isset( $result['bwfan_response'] ) ) {
			// Required field missing error
			return array(
				'status'  => 4,
				'message' => $result['bwfan_response'],
			);
		}

		// Curl error
		if ( isset( $result['body']['subscription']['id'] ) && '' !== $result['body']['subscription']['id'] ) {
			return array(
				'status' => 3,
			);
		}
		if ( isset( $result['body']['total_subscribers'] ) && 0 === intval( $result['body']['total_subscribers'] ) ) {
			// as convertkit does not give any message if subscriber is not present in sequence, thats why custom message.
			return array(
				'status'  => 4,
				'message' => __( 'Subscriber not present', 'autonami-automations-connectors' ),
			);
		}

		return $result;
	}

	protected function do_after_action_registration( BWFAN_Action $action_object ) {
		$action_object->connector = $this->connector_slug;
	}

}

/**
 * Register this class as an integration.
 */
BWFAN_Load_Integrations::register( 'BWFAN_ConvertKit_Integration' );
