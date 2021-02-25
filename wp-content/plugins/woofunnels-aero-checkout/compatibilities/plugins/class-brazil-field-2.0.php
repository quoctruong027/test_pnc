<?php

class WFACP_Brazil_Field_2 {
	private static $instance = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'after_setup_theme', [ $this, 'setup_fields_billing' ] );
		add_action( 'after_setup_theme', [ $this, 'setup_fields_shipping' ] );
	}

	private function is_enabled() {
		$page_version = WFACP_Common::get_checkout_page_version();
		if ( version_compare( $page_version, '2.0.0', '<' ) && ! is_admin() ) {
			return false;
		}
		if ( class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) ) {
			return true;
		}

	}

	public function setup_fields_billing() {
		if ( false == $this->is_enabled() ) {
			return;
		}
		$settings    = get_option( 'wcbcf_settings' );
		$person_type = intval( $settings['person_type'] );
		if ( 0 !== $person_type ) {
			if ( 1 === $person_type ) {
				new WFACP_Add_Address_Field( 'persontype', [
					'type'        => 'select',
					'label'       => __( 'Person type', 'woocommerce-extra-checkout-fields-for-brazil' ),
					'class'       => [ 'form-row-wide', 'person-type-field' ],
					'cssready'    => [ 'wfacp-col-full' ],
					'input_class' => [ 'wc-ecfb-select' ],
					'required'    => false,
					'options'     => [
						'1' => __( 'Individuals', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'2' => __( 'Legal Person', 'woocommerce-extra-checkout-fields-for-brazil' ),
					],
					'priority'    => 22,
				] );
			}

			if ( 1 === $person_type || 2 === $person_type ) {
				if ( isset( $settings['rg'] ) ) {

					new WFACP_Add_Address_Field( 'cpf', [
						'label'    => __( 'CPF', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'class'    => [ 'form-row-first', 'person-type-field' ],
						'cssready' => [ 'wfacp-col-left-half' ],
						'required' => false,
						'type'     => 'tel',
						'priority' => 23,
					] );


					new WFACP_Add_Address_Field( 'rg', [
						'label'    => __( 'RG', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'class'    => [ 'form-row-last', 'person-type-field' ],
						'cssready' => [ 'wfacp-col-left-half' ],
						'required' => false,
						'priority' => 24,
					] );


				} else {
					new WFACP_Add_Address_Field( 'cpf', [
						'label'    => __( 'CPF', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'class'    => [ 'form-row-first', 'person-type-field' ],
						'cssready' => [ 'wfacp-col-left-half' ],
						'required' => false,
						'type'     => 'tel',
						'priority' => 23,
					] );

				}
			}

			if ( 1 === $person_type || 3 === $person_type ) {

				if ( isset( $settings['ie'] ) ) {

					new WFACP_Add_Address_Field( 'cnpj', [
						'label'    => __( 'CNPJ', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'class'    => [ 'form-row-first', 'person-type-field' ],
						'cssready' => [ 'wfacp-col-left-half' ],
						'required' => false,
						'type'     => 'tel',
						'priority' => 26,
					] );

					new WFACP_Add_Address_Field( 'ie', [
						'label'    => __( 'State Registration', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'class'    => [ 'form-row-last', 'person-type-field' ],
						'cssready' => [ 'wfacp-col-left-half' ],
						'required' => false,
						'priority' => 27,
					] );


				} else {


					new WFACP_Add_Address_Field( 'cnpj', [
						'label'    => __( 'CNPJ', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'class'    => [ 'form-row-wide', 'person-type-field' ],
						'cssready' => [ 'wfacp-col-full' ],
						'required' => false,
						'type'     => 'tel',
						'priority' => 26,
					] );
				}
			}
		}

		if ( isset( $settings['birthdate_sex'] ) ) {
			new WFACP_Add_Address_Field( 'birthdate', [
				'label'    => __( 'Birthdate', 'woocommerce-extra-checkout-fields-for-brazil' ),
				'class'    => [ 'form-row-first', 'person-type-field' ],
				'cssready' => [ 'wfacp-col-left-half' ],
				'clear'    => false,
				'required' => true,
				'priority' => 31,
			] );
			new WFACP_Add_Address_Field( 'sex', [
				'type'        => 'select',
				'label'       => __( 'Sex', 'woocommerce-extra-checkout-fields-for-brazil' ),
				'class'       => [ 'form-row-last', 'person-type-field wfacp_drop_list' ],
				'cssready'    => [ 'wfacp-col-left-half' ],
				'input_class' => [ 'wc-ecfb-select' ],
				'clear'       => true,
				'required'    => true,
				'options'     => [
					''                                                             => __( 'Select', 'woocommerce-extra-checkout-fields-for-brazil' ),
					__( 'Female', 'woocommerce-extra-checkout-fields-for-brazil' ) => __( 'Female', 'woocommerce-extra-checkout-fields-for-brazil' ),
					__( 'Male', 'woocommerce-extra-checkout-fields-for-brazil' )   => __( 'Male', 'woocommerce-extra-checkout-fields-for-brazil' ),
				],
				'priority'    => 32,
			] );
		}

		new WFACP_Add_Address_Field( 'number', array(
			'label'    => __( 'Number', 'woocommerce-extra-checkout-fields-for-brazil' ),
			'class'    => [ 'form-row-first', 'address-field' ],
			'cssready' => [ 'wfacp-col-left-half' ],
			'clear'    => true,
			'required' => true,
			'priority' => 55,
		) );


		new WFACP_Add_Address_Field( 'neighborhood', array(
			'label'    => __( 'Neighborhood', 'woocommerce-extra-checkout-fields-for-brazil' ),
			'class'    => [ 'form-row-first', 'address-field' ],
			'cssready' => [ 'wfacp-col-left-half' ],
			'clear'    => true,
			'priority' => 65,
		) );


		if ( isset( $settings['cell_phone'] ) ) {
			new WFACP_Add_Address_Field( 'cellphone', array(
				'label'    => __( 'Cell Phone', 'woocommerce-extra-checkout-fields-for-brazil' ),
				'class'    => [ 'form-row-last' ],
				'cssready' => [ 'wfacp-col-full' ],
				'clear'    => true,
				'priority' => 105,
			) );
		}
	}

	public function setup_fields_shipping() {
		if ( false == $this->is_enabled() ) {
			return;
		}
		new WFACP_Add_Address_Field( 'number', array(
			'label'    => __( 'Number', 'woocommerce-extra-checkout-fields-for-brazil' ),
			'class'    => [ 'form-row-first', 'address-field' ],
			'cssready' => [ 'wfacp-col-left-half' ],
			'clear'    => true,
			'required' => true,
			'priority' => 55,
		), 'shipping' );

		new WFACP_Add_Address_Field( 'neighborhood', array(
			'label'    => __( 'Neighborhood', 'woocommerce-extra-checkout-fields-for-brazil' ),
			'class'    => [ 'form-row-first', 'address-field' ],
			'cssready' => [ 'wfacp-col-left-half' ],
			'clear'    => true,
			'priority' => 65,
		), 'shipping' );
	}
}

WFACP_Brazil_Field_2::get_instance();