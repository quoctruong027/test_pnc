<?php

class WCCT_Input_Time extends WCCT_Input_Text {

	public function __construct() {
		$this->type = 'Time';

		parent::__construct();
	}

	public function render( $field, $value = null ) {
		$field = array_merge( $this->defaults, $field );
		if ( ! isset( $field['id'] ) ) {
			$field['id'] = sanitize_title( $field['id'] );
		}

		echo '<input name="' . $field['name'] . '" type="text" id="' . esc_attr( $field['id'] ) . '" class="wcct-time-picker-field' . esc_attr( $field['class'] ) . '" placeholder="For Eg: 23:59" value="' . $value . '" />';
	}

}


