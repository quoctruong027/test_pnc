<?php
defined( 'ABSPATH' ) || exit;

class xlwcty_Input_Html_Rule_Is_Upgrade {
	public function __construct() {
		// vars
		$this->type = 'Html_Rule_Is_Upgrade';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {

		_e( 'This Page will show on orders that have upgraded subscriptions.', 'thank-you-page-for-woocommerce-nextmove' );
	}

}
