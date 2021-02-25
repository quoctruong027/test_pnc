<?php

final class BWFAN_Slack_Integration extends BWFAN_Integration {
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	private function __construct() {
		$this->connector_slug = 'bwfco_slack';
		$this->need_connector = true;
		$this->action_dir     = __DIR__;
		$this->nice_name      = __( 'Slack', 'autonami-automations-connectors' );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_Slack_Integration|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Handle the responses for all the actions of this connector.
	 * Return 0 for try again, 2 for action halt, 3 for successful execution , 4 for permanent failure.
	 *
	 * @param $result
	 * @param $connector_slug
	 * @param $action_call_class_slug
	 *
	 * @return array
	 */
	public function handle_response( $result, $connector_slug, $action_call_class_slug, $action_data = null ) {
		// Required field missing error
		if ( isset( $result['bwfan_response'] ) ) {
			return array(
				'status'  => 4,
				'message' => $result['bwfan_response'],
			);
		}

		// Curl error
		if ( isset( $result['response'] ) && 500 === $result['response'] ) {
			return array(
				'status'  => 0,
				'message' => $result['body'],
			);
		}
		if ( isset( $result['response'] ) && 200 === $result['response'] && isset( $result['body']['ok'] ) && 1 === intval( $result['body']['ok'] ) ) {
			return array(
				'status' => 3,
			);
		}

		return array(
			'status'  => 4,
			'message' => $result['body']['error'],
		);
	}

	protected function do_after_action_registration( BWFAN_Action $action_object ) {
		$action_object->connector = $this->connector_slug;
	}

}

/**
 * Register this class as an integration.
 */
BWFAN_Load_Integrations::register( 'BWFAN_Slack_Integration' );
