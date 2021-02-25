<?php

class wfocu_Input_Html_Rule_Is_Guest {
	public function __construct() {
		// vars
		$this->type = 'Html_Rule_Is_Guest';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => ''
		);
	}

	public function render( $field, $value = null ) {

		_e( 'This Funnel will initiate on guest orders.', 'woofunnels-upstroke-one-click-upsell' );
	}

}