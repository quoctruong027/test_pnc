<?php

class WCCT_Input_Html_Always {
	public function __construct() {
		// vars
		$this->type = 'Html_Always';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		_e( 'Campaign would render on complete site.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
	}

}


class WCCT_Input_Html_General_All_Products {
	public function __construct() {
		// vars
		$this->type = 'Html_General_All_Products';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		_e( 'Campaign would render on All Single Product Iterations (ex: single product page, product grid etc).', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
	}

}


class WCCT_Input_Html_General_Front {
	public function __construct() {
		// vars
		$this->type = 'Html_General_Front';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		_e( 'Campaign would render on Home Page.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
	}

}

class WCCT_Input_Html_General_All_Pages {
	public function __construct() {
		// vars
		$this->type = 'Html_General_All_Pages';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		_e( 'Campaign would render on All pages.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
	}

}

class WCCT_Input_Html_General_All_Product_cats {
	public function __construct() {
		// vars
		$this->type = 'Html_General_All_Product_cats';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		_e( 'Campaign would render on All Product Category Pages.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
	}

}

class WCCT_Input_Html_General_All_Product_Tags {
	public function __construct() {
		// vars
		$this->type = 'Html_General_All_Product_Tags';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		_e( 'Campaign would render on All Product Tag Pages.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
	}

}

class WCCT_Input_Html_Guests {
	public function __construct() {
		// vars
		$this->type = 'Html_Guests';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		_e( 'Campaign would render on Guest users.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
	}

}
