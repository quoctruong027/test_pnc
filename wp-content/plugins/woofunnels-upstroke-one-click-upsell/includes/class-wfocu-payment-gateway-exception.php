<?php


/**
 * Class WFOCU_Payment_Gateway_Exception handles exceptions thrown by the gateway integration class
 */
class WFOCU_Payment_Gateway_Exception extends Exception {

	/**
	 * WFOCU_Payment_Gateway_Exception constructor.
	 * Initiates the Exception
	 *
	 * @param $error_message message to be thrown
	 * @param $error_code Error code associated with the exception
	 * @param string $gateway gateway ID inside the exception thrown
	 */
	public function __construct( $error_message, $error_code, $gateway = '' ) {
		parent::__construct( $error_message, $error_code );
	}


}
