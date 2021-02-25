<?php

class WFACP_Address_choosen_new_ui {
	private static $self = null;
	private $address_new_ui = false;
	private $checkbox_field_key = '';
	private $address_new_start_ui = false;
	private $address_new_end_ui = false;

	private function __construct() {
		add_filter( 'wfacp_forms_field', [ $this, 'change_type_of_checkbox' ], 15, 2 );
	}


	public static function get_instance() {
		if ( is_null( self::$self ) ) {
			self::$self = new self();
		}

		return self::$self;
	}

	public function change_type_of_checkbox( $field, $key ) {
		if ( in_array( $key, [ 'billing_same_as_shipping', 'shipping_same_as_billing' ] ) ) {


			if ( isset( $field['radio_options'] ) && 'yes' == $field['radio_options'] && isset( $field['label_2'] ) && '' !== $field['label_2'] ) {
				$field['type']          = 'wfacp_radio';
				$field['options']       = [
					'option_1' => $field['label'],
					'option_2' => $field['label_2'],
				];
				$field['input_class'][] = $key;
				$field['default']       = 'option_1';
				unset( $field['label'] );
				$this->checkbox_field_key = $key;
				$this->address_new_ui     = true;
				add_action( 'wfacp_after_' . $key . '_field', [ $this, 'start_div_wrapper' ], 1 );
				add_action( 'wfacp_after_wfacp_divider_billing_end_field', [ $this, 'end_div_wrapper' ], 1 );
				add_action( 'wfacp_after_wfacp_divider_shipping_end_field', [ $this, 'end_div_wrapper' ], 1 );

			}

		}

		return $field;
	}

	public function start_div_wrapper() {
		if ( true == $this->address_new_ui && false == $this->address_new_start_ui ) {
			echo "<div class='wfacp_address_new_ui'>";
			$this->address_new_start_ui = true;
		}

	}

	public function end_div_wrapper() {
		if ( true == $this->address_new_ui && true == $this->address_new_start_ui && false == $this->address_new_end_ui ) {
			$this->address_new_end_ui = true;

			echo "</div>";
		}
	}

}

//WFACP_Address_choosen_new_ui::get_instance();