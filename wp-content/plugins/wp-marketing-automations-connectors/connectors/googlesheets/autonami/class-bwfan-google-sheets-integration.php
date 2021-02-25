<?php

if ( ! class_exists( 'BWFAN_Google_Sheets_Integration' ) ) {
	class BWFAN_Google_Sheets_Integration extends BWFAN_Integration {

		private static $ins = null;
		protected $connector_slug = 'bwfco_google_sheets';
		protected $need_connector = true;

		public function __construct() {
			$this->action_dir = __DIR__;
			$this->nice_name  = __( 'Google Sheets', 'autonami-automations-connectors' );

			add_action( 'wp_ajax_wfco_gs_get_worksheets', array( $this, 'wfco_gs_get_worksheets' ) );
		}

		/**
		 * @return BWFAN_Google_Sheets_Integration|null
		 */
		public static function get_instance() {
			if ( null === self::$ins ) {
				self::$ins = new self();
			}

			return self::$ins;
		}

		public static function get_permanent_failure_error_codes() {
			return array( 400, 401, 403, 404, 500 );
		}

		protected function do_after_action_registration( BWFAN_Action $action_object ) {
			$action_object->connector = $this->connector_slug;
		}

		public function wfco_gs_get_worksheets() {
			BWFAN_Common::check_nonce();

			if ( ! isset( $_POST['id'] ) ) {
				wp_send_json( array(
					'success' => 0,
					'result'  => __( 'Security check failed', 'autonami-automations-connectors' ),
				) );
			}

			$worksheets = BWFCO_Google_Sheets::get_google_worksheets( $_POST['id'] );
			if ( false === $worksheets ) {
				wp_send_json( array(
					'success' => 0,
					'result'  => __( 'Worksheet call failed', 'autonami-automations-connectors' ),
				) );
			}

			if ( isset( $worksheets[3] ) && false === $worksheets[3] ) {
				wp_send_json( array(
					'success' => 0,
					'result'  => $worksheets[1],
				) );
			}

			wp_send_json( array(
				'success' => 1,
				'result'  => $worksheets,
			) );
		}


	}

	BWFAN_Load_Integrations::register( 'BWFAN_Google_Sheets_Integration' );
}
