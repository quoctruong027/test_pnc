<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_WECFB {

	public $user_id = 0;
	private $fields_added = false;
	private $shipping_fields_added = false;
	private $configs = [];
	private $instance = null;
	private $brazil_field_keys = [];

	public function __construct() {

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions' ] );
		add_filter( 'wfacp_form_section', [ $this, 'checkout_billing_sections' ] );
		add_filter( 'wfacp_form_section', [ $this, 'checkout_shipping_sections' ], 12 );
		add_action( 'wfacp_before_process_checkout_template_loader', [ $this, 'remove_hooks_for_brazil' ] );
		add_filter( 'wfacp_forms_field', [ $this, 'check_wc_validations_billing' ], 11 );
		add_filter( 'wfacp_global_dependency_messages', [ $this, 'add_dependency_messages' ] );

		add_filter( 'wfacp_default_values', [ $this, 'change_default_value' ], 11, 3 );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );


	}

	function change_default_value( $field_value, $key, $field ) {


		if ( class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) && is_user_logged_in() && $key == 'billing_sex' ) {
			$userID = get_current_user_id();

			if ( $userID != '' && get_user_meta( $userID, $key, true ) != '' ) {
				$field_value = get_user_meta( $userID, $key, true );
			}
		}

		return $field_value;
	}

	private function is_enabled() {
		if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) ) {
			return false;
		}

		$id = WFACP_Common::get_checkout_page_version();
		if ( version_compare( $id, '1.9.3.3', '<=' ) ) {

			return true;
		}

		return false;
	}

	public function check_wc_validations_billing( $fields ) {

		if ( false == $this->is_enabled() ) {
			return $fields;
		}
		if ( isset( $fields['id'] ) && $fields['id'] == 'billing_company' ) {
			$fields['required'] = true;
		}

		return $fields;
	}

	public function remove_hooks_for_brazil() {

		if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) || ! class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) ) {
			return;
		}

		WFACP_Common::remove_actions( 'woocommerce_billing_fields', 'Extra_Checkout_Fields_For_Brazil_Front_End', 'checkout_billing_fields' );
		$this->instance = WFACP_Common::remove_actions( 'woocommerce_shipping_fields', 'Extra_Checkout_Fields_For_Brazil_Front_End', 'checkout_shipping_fields' );

		if ( $this->instance instanceof Extra_Checkout_Fields_For_Brazil_Front_End ) {
			add_filter( 'wfacp_checkout_fields', function ( $fields ) {

				if ( isset( $fields['billing'] ) ) {
					$fields['billing'] = $this->instance->checkout_billing_fields( $fields['billing'] );
				}
				if ( isset( $fields['shipping'] ) ) {
					$fields['shipping'] = $this->instance->checkout_shipping_fields( $fields['shipping'] );
				}

				return $fields;
			}, 15 );
		}
	}

	public function actions() {

		add_action( 'wp_footer', [ $this, 'add_js' ] );
	}

	public function checkout_shipping_sections( $sections ) {
		if ( false == $this->is_enabled() ) {
			return $sections;
		}
		if ( $this->shipping_fields_added ) {

			return $sections;
		}
		if ( count( $sections ) == 0 ) {
			return $sections;
		}
		if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) || ! class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) ) {
			return $sections;
		}

		if ( isset( $sections['fields']['wfacp_end_divider_shipping'] ) ) {
			try {

				$this->shipping_fields_added = true;

				$end_address_found     = false;
				$end_address_closser   = $sections['fields']['wfacp_end_divider_shipping'];
				$after_address_element = [];
				$is_hidedable          = false;

				foreach ( $sections['fields'] as $index => $field ) {
					if ( $end_address_found ) {
						$after_address_element[] = $field;
						unset( $sections['fields'][ $index ] );
					}
					if ( isset( $field['class'] ) && in_array( 'wfacp_shipping_fields', $field['class'] ) ) {
						$is_hidedable = true;
					}
					if ( 'wfacp_end_divider_shipping' === $index ) {
						unset( $sections['fields'][ $index ] );
						$end_address_found = true;
					}
				}

				if ( false == $end_address_found ) {
					return $sections;
				}

				$new_fields = array();

				$new_fields['shipping_number'] = [
					'label'    => __( 'Number', 'woocommerce-extra-checkout-fields-for-brazil' ),
					'class'    => [ 'form-row-first', 'address-field', 'wfacp-form-control-wrapper' ],
					'cssready' => [ 'wfacp-col-left-half' ],
					'clear'    => true,
					'required' => true,
					'priority' => 55,
				];

				$new_fields['shipping_neighborhood'] = [
					'label'    => __( 'Neighborhood', 'woocommerce-extra-checkout-fields-for-brazil' ),
					'class'    => [ 'form-row-first', 'address-field', 'wfacp-form-control-wrapper' ],
					'cssready' => [ 'wfacp-col-left-half' ],
					'clear'    => true,
				];

				$this->brazil_field_keys[] = $new_fields;

				if ( is_array( $new_fields ) && count( $new_fields ) > 0 ) {
					foreach ( $new_fields as $fkey => $fvalue ) {
						$fvalue['id'] = $fkey;

						if ( $is_hidedable ) {
							$fvalue['class'][] = 'wfacp_shipping_fields';
							$fvalue['class'][] = 'wfacp_shipping_field_hide';
						}

						$sections['fields'][] = $fvalue;
					}
				}

				$sections['fields']['wfacp_end_divider_shipping'] = $end_address_closser;

				if ( count( $after_address_element ) > 0 ) {
					$last_field_type = '';
					foreach ( $after_address_element as $element ) {
						if ( isset( $element['type'] ) && $element['type'] === 'wfacp_start_divider' ) {
							if ( false !== strpos( $element['id'], '_shipping' ) ) {
								$last_field_type            = 'shipping';
								$tid                        = 'wfacp_start_divider_shipping';
								$sections['fields'][ $tid ] = WFACP_Common::get_start_divider_field( 'shipping' );
							} elseif ( false !== strpos( $element['id'], '_billing' ) ) {
								$last_field_type            = 'billing';
								$tid                        = 'wfacp_start_divider_billing';
								$sections['fields'][ $tid ] = WFACP_Common::get_start_divider_field( 'billing' );
							}
						} elseif ( isset( $element['type'] ) && $element['type'] === 'wfacp_end_divider' ) {
							$tid                        = 'wfacp_end_divider_' . $last_field_type;
							$sections['fields'][ $tid ] = WFACP_Common::get_end_divider_field();
						} else {
							$sections['fields'][] = $element;
						}
					}
				}
			} catch ( Exception $e ) {

			}
		}

		return $sections;
	}

	public function checkout_billing_sections( $sections ) {
		if ( false == $this->is_enabled() ) {
			return $sections;
		}

		if ( $this->fields_added ) {
			return $sections;
		}
		if ( count( $sections ) == 0 ) {
			return $sections;
		}
		if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) || ! class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) ) {
			return $sections;
		}

		if ( isset( $sections['fields']['wfacp_end_divider_billing'] ) ) {
			try {

				$this->fields_added = true;

				$end_address_found     = false;
				$end_address_closser   = $sections['fields']['wfacp_end_divider_billing'];
				$after_address_element = [];
				$is_hidedable          = false;
				foreach ( $sections['fields'] as $index => $field ) {
					if ( $end_address_found ) {
						$after_address_element[] = $field;
						unset( $sections['fields'][ $index ] );
					}
					if ( isset( $field['class'] ) && in_array( 'wfacp_billing_fields', $field['class'] ) ) {
						$is_hidedable = true;
					}
					if ( 'wfacp_end_divider_billing' === $index ) {
						unset( $sections['fields'][ $index ] );
						$end_address_found = true;
					}
				}

				if ( false == $end_address_found ) {
					return $sections;
				}

				$new_fields = array();

				// Get plugin settings.
				$settings    = get_option( 'wcbcf_settings' );
				$person_type = intval( $settings['person_type'] );

				if ( 0 !== $person_type ) {
					if ( 1 === $person_type ) {
						$new_fields['billing_persontype'] = [
							'type'        => 'select',
							'label'       => __( 'Person type', 'woocommerce-extra-checkout-fields-for-brazil' ),
							'class'       => [ 'form-row-wide', 'person-type-field', 'wfacp-form-control-wrapper' ],
							'cssready'    => [ 'wfacp-col-full' ],
							'input_class' => [ 'wc-ecfb-select' ],
							'required'    => false,
							'options'     => [
								'1' => __( 'Individuals', 'woocommerce-extra-checkout-fields-for-brazil' ),
								'2' => __( 'Legal Person', 'woocommerce-extra-checkout-fields-for-brazil' ),
							],
							'priority'    => 22,
						];
					}

					if ( 1 === $person_type || 2 === $person_type ) {
						if ( isset( $settings['rg'] ) ) {
							$new_fields['billing_cpf'] = [
								'label'    => __( 'CPF', 'woocommerce-extra-checkout-fields-for-brazil' ),
								'class'    => [ 'form-row-first', 'person-type-field', 'wfacp-form-control-wrapper' ],
								'cssready' => [ 'wfacp-col-left-half' ],
								'required' => false,
								'type'     => 'tel',
								'priority' => 23,
							];

							$new_fields['billing_rg'] = [
								'label'    => __( 'RG', 'woocommerce-extra-checkout-fields-for-brazil' ),
								'class'    => [ 'form-row-last', 'person-type-field', 'wfacp-form-control-wrapper' ],
								'cssready' => [ 'wfacp-col-left-half' ],
								'required' => false,
								'priority' => 24,
							];
						} else {
							$new_fields['billing_cpf'] = [
								'label'    => __( 'CPF', 'woocommerce-extra-checkout-fields-for-brazil' ),
								'class'    => [ 'form-row-wide', 'person-type-field', 'wfacp-form-control-wrapper', 'wfacp-col-full' ],
								'cssready' => [ 'wfacp-col-full' ],
								'required' => false,
								'type'     => 'tel',
								'priority' => 23,
							];
						}
					}

					if ( 1 === $person_type || 3 === $person_type ) {

						if ( isset( $settings['ie'] ) ) {
							$new_fields['billing_cnpj'] = [
								'label'    => __( 'CNPJ', 'woocommerce-extra-checkout-fields-for-brazil' ),
								'class'    => [ 'form-row-first', 'person-type-field', 'wfacp-form-control-wrapper' ],
								'cssready' => [ 'wfacp-col-left-half' ],
								'required' => false,
								'type'     => 'tel',
								'priority' => 26,
							];

							$new_fields['billing_ie'] = [
								'label'    => __( 'State Registration', 'woocommerce-extra-checkout-fields-for-brazil' ),
								'class'    => [ 'form-row-last', 'person-type-field', 'wfacp-form-control-wrapper' ],
								'cssready' => [ 'wfacp-col-left-half' ],
								'required' => false,
								'priority' => 27,
							];

						} else {
							$new_fields['billing_cnpj'] = [
								'label'    => __( 'CNPJ', 'woocommerce-extra-checkout-fields-for-brazil' ),
								'class'    => [ 'form-row-wide', 'person-type-field', 'wfacp-form-control-wrapper' ],
								'cssready' => [ 'wfacp-col-full' ],
								'required' => false,
								'type'     => 'tel',
								'priority' => 26,
							];
						}
					}
				}

				if ( isset( $settings['birthdate_sex'] ) ) {
					$new_fields['billing_birthdate'] = [
						'label'    => __( 'Birthdate', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'class'    => [ 'form-row-first', 'person-type-field', 'wfacp-form-control-wrapper' ],
						'cssready' => [ 'wfacp-col-left-half' ],
						'clear'    => false,
						'required' => true,
						'priority' => 31,
					];

					$new_fields['billing_sex'] = [
						'type'        => 'select',
						'label'       => __( 'Sex', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'class'       => [ 'form-row-last', 'person-type-field', 'wfacp-form-control-wrapper' ],
						'cssready'    => [ 'wfacp-col-left-half wfacp_drop_list' ],
						'input_class' => [ 'wc-ecfb-select' ],
						'clear'       => true,
						'required'    => true,
						'options'     => [
							''                                                             => __( 'Select', 'woocommerce-extra-checkout-fields-for-brazil' ),
							__( 'Female', 'woocommerce-extra-checkout-fields-for-brazil' ) => __( 'Female', 'woocommerce-extra-checkout-fields-for-brazil' ),
							__( 'Male', 'woocommerce-extra-checkout-fields-for-brazil' )   => __( 'Male', 'woocommerce-extra-checkout-fields-for-brazil' ),
						],
						'priority'    => 32,
					];
				}

				$new_fields['billing_number'] = array(
					'label'    => __( 'Number', 'woocommerce-extra-checkout-fields-for-brazil' ),
					'class'    => [ 'form-row-first', 'address-field', 'wfacp-form-control-wrapper' ],
					'cssready' => [ 'wfacp-col-left-half' ],
					'clear'    => true,
					'required' => true,
					'priority' => 55,
				);

				$new_fields['billing_neighborhood'] = array(
					'label'    => __( 'Neighborhood', 'woocommerce-extra-checkout-fields-for-brazil' ),
					'class'    => [ 'form-row-first', 'address-field', 'wfacp-form-control-wrapper' ],
					'cssready' => [ 'wfacp-col-left-half' ],
					'clear'    => true,
					'priority' => 65,
				);

				if ( isset( $settings['cell_phone'] ) ) {

					$new_fields['billing_cellphone'] = array(
						'label'    => __( 'Cell Phone', 'woocommerce-extra-checkout-fields-for-brazil' ),
						'class'    => [ 'form-row-last', 'wfacp-form-control-wrapper' ],
						'cssready' => [ 'wfacp-col-full' ],
						'clear'    => true,
						'priority' => 105,
					);

				}

				$this->brazil_field_keys = array_merge( $this->brazil_field_keys, $new_fields );

				if ( is_array( $new_fields ) && count( $new_fields ) > 0 ) {
					foreach ( $new_fields as $fkey => $fvalue ) {
						$fvalue['id'] = $fkey;

						if ( $is_hidedable ) {
							$fvalue['class'][] = 'wfacp_billing_fields';
							$fvalue['class'][] = 'wfacp_billing_field_hide';
						}

						$sections['fields'][] = $fvalue;
					}
				}

				$sections['fields']['wfacp_end_divider_billing'] = $end_address_closser;
				if ( count( $after_address_element ) > 0 ) {

					$last_field_type = '';
					foreach ( $after_address_element as $element ) {

						if ( $element['type'] === 'wfacp_start_divider' ) {
							if ( false !== strpos( $element['id'], '_shipping' ) ) {
								$last_field_type            = 'shipping';
								$tid                        = 'wfacp_start_divider_shipping';
								$sections['fields'][ $tid ] = WFACP_Common::get_start_divider_field( 'shipping' );
							} elseif ( false !== strpos( $element['id'], '_billing' ) ) {
								$last_field_type            = 'billing';
								$tid                        = 'wfacp_start_divider_billing';
								$sections['fields'][ $tid ] = WFACP_Common::get_start_divider_field( 'billing' );
							}
						} elseif ( $element['type'] === 'wfacp_end_divider' ) {
							$tid                        = 'wfacp_end_divider_' . $last_field_type;
							$sections['fields'][ $tid ] = WFACP_Common::get_end_divider_field();
						} else {
							$sections['fields'][] = $element;
						}
					}
				}
			} catch ( Exception $e ) {

			}
		}

		return $sections;
	}


	public function add_js() {

		if ( ! $this->fields_added ) {
			return;
		}
		?>
        <script>
            window.addEventListener('load', function () {
                (function ($) {

                    function select2_reintiate() {
                        let wc_ecfb_select = $('.wc-ecfb-select');
                        if ($().select2 && wc_ecfb_select.length > 0) {
                            wc_ecfb_select.select2('destroy');
                            setTimeout(function () {
                                $('.wc-ecfb-select').select2();
                            }, 800);
                        }

                    }

                    $(".wfacp_steps_wrap a").on('click', function (e) {
                        select2_reintiate();
                        validate_required();
                    });

                    $(document).ready(function () {

                        validate_required();
                    });

                    $(document.body).on('wfacp_step_switching', function () {

                        select2_reintiate();
                        validate_required();

                    });


                    function validate_required() {

                        var $ele = $("#billing_persontype");
                        var $parent = $("#billing_company_field");

                        if ($ele.val() == "2" || $ele.val() == 2) {
                            console.log("if");

                            var billing_company = $parent.find("input[name=billing_company]").val();
                            if (billing_company.trim() === '' || billing_company == null) {

                                $parent.removeClass('woocommerce-validated').addClass('validate-required');
                            } else {
                                $parent.removeClass('woocommerce-invalid').addClass('woocommerce-validated');
                            }

                        } else {
                            $parent.removeClass('woocommerce-invalid').addClass('woocommerce-validated');
                        }

                    }

                    $(document.body).on("change", "#billing_persontype", function (e) {
                        validate_required()
                    });
                    $(document).on('focus', '#billing_company', function () {
                        validate_required()
                    });


                    $(document).on('change', '#billing_same_as_shipping,#shipping_same_as_billing', function () {
                        if ($(this).is(":checked")) {
                            select2_reintiate();
                        }
                    });
                })(jQuery);
            });
        </script>
		<?php

	}

	public function add_dependency_messages( $messages ) {

		if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) ) {
			return $messages;
		}

		$messages[] = [
			'message'     => __( '"WooCommerce Extra Checkout Fields for Brazil" is activated. Learn about the right away to configure it with AeroCheckout.<a target="_blank" href="//buildwoofunnels.com/docs/aerocheckout/compatibility/woocommerce-extra-checkout-fields-for-Brazil"> Know more</a>', 'woofunnels-aero-checkout' ),
			'id'          => '',
			'show'        => 'yes',
			'dismissible' => true,
			'is_global'   => true,
			'type'        => 'wfacp_error',
		];

		return $messages;
	}


	public function internal_css() {
		if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil_Front_End' ) ) {
			return;
		}

		?>
        <style>
            body #wfacp-e-form .wfacp_main_form.woocommerce #billing_persontype_field .optional,
            body #wfacp-e-form .wfacp_main_form.woocommerce #billing_cnpj_field .optional,
            body #wfacp-e-form .wfacp_main_form.woocommerce #billing_ie_field .optional,
            body #wfacp-e-form .wfacp_main_form.woocommerce #billing_cpf_field .optional,
            body #wfacp-e-form .wfacp_main_form.woocommerce #billing_rg_field .optional {
                display: none !important;
            }

        </style>
		<?php
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WECFB(), 'wfcfb' );
