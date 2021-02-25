<?php

class wfocu_Input_Funnel_OneTime {
	public function __construct() {
		// vars
		$this->type = 'Funnel_OneTime';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => ''
		);
	}

	public function render( $field, $value = null ) {

		_e( 'Run this funnel only if the user hasn\'t visited it yet.', 'woofunnels-upstroke-one-click-upsell' );
	}

}