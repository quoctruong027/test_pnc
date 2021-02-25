<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * WFOCU_Gateway_Integration_WFOCU_Test class.
 *
 * @extends WFOCU_Gateway
 */
class WFOCU_Gateway_Integration_Bacs extends WFOCU_Gateway {


	protected static $ins = null;
	public $key = 'bacs';
	public $token = false;

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * Try and get the payment token saved by the gateway
	 *
	 * @param WC_Order $order
	 *
	 * @return true on success false otherwise
	 */
	public function has_token( $order ) {

		return true;

	}

	public function process_charge( $order ) {

		$is_successful = true;

		return $this->handle_result( $is_successful, '' );
	}


}


WFOCU_Gateway_Integration_Bacs::get_instance();
