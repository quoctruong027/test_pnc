<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class El_WFACP_Form_Widget extends WFACP_Elementor_HTML_BLOCK {

	private $html_fields = [];
	public $typo_default_value = [];
	public $progress_bar = [];
	public $section_fields = [];
	private $current_step = 1;

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
	}


	public function get_name() {

		return 'wfacp_form';
	}

	public function get_title() {
		return __( 'Checkout Form', 'woofunnels-aero-checkout' );
	}

	public function get_icon() {
		return 'wfacp-icon-icon_checkout';
	}

	public function get_categories() {
		return [ 'woofunnels-aero-checkout' ];
	}


	protected function _register_controls() {
		$template = wfacp_template();
		$template->get_fieldsets();

		$this->register_sections();
		$this->register_styles();
	}

	protected function register_sections() {


		$this->breadcrumb_bar();

		$this->register_section_fields();
		$this->payment_method();

		$this->mobile_mini_cart();

	}

	private function register_section_fields() {
		$template = wfacp_template();
		$steps    = $template->get_fieldsets();

		$do_not_show_fields = WFACP_Common::get_html_excluded_field();
		$exclude_fields     = [];
		foreach ( $steps as $step_key => $fieldsets ) {
			foreach ( $fieldsets as $section_key => $section_data ) {
				if ( empty( $section_data['fields'] ) ) {
					continue;
				}
				$count            = count( $section_data['fields'] );
				$html_field_count = 0;


				if ( ! empty( $section_data['html_fields'] ) ) {
					foreach ( $do_not_show_fields as $h_key ) {
						if ( isset( $section_data['html_fields'][ $h_key ] ) ) {
							$html_field_count ++;
							$this->html_fields[ $h_key ] = true;

						}
					}
				}

				if ( $html_field_count == $count ) {
					continue;
				}

				if ( is_array( $section_data['fields'] ) && count( $section_data['fields'] ) > 0 ) {
					foreach ( $section_data['fields'] as $fkey => $fval ) {
						if ( isset( $fval['id'] ) && in_array( $fval['id'], $do_not_show_fields ) ) {
							$exclude_fields[]                 = $fval['id'];
							$this->html_fields[ $fval['id'] ] = true;
							continue;
						}
					}
				}

				if ( count( $exclude_fields ) == count( $section_data['fields'] ) ) {
					continue;
				}


				$this->add_tab( $section_data['name'], 5 );
				$this->register_fields( $section_data['fields'] );
				$this->end_tab();


			}
		}

	}

	private function register_fields( $temp_fields ) {

		$template      = wfacp_template();
		$template_slug = $template->get_template_slug();
		$template_cls  = $template->get_template_fields_class();

		$default_cls        = $template->default_css_class();
		$do_not_show_fields = WFACP_Common::get_html_excluded_field();


		//$this->add_heading( __( 'Field Width', 'woofunnel-aero-checkout' ) );


		$this->section_fields[] = $temp_fields;
		foreach ( $temp_fields as $loop_key => $field ) {

			if ( in_array( $loop_key, [ 'wfacp_start_divider_billing', 'wfacp_start_divider_shipping' ], true ) ) {
				$address_key_group = ( $loop_key == 'wfacp_start_divider_billing' ) ? __( 'Billing Address', 'woocommerce' ) : __( 'Shipping Address', 'woocommerce' );
				$this->add_heading( $address_key_group, 'none' );
			}

			if ( ! isset( $field['id'] ) || ! isset( $field['label'] ) ) {
				continue;
			}

			$field_key = $field['id'];

			if ( isset( $template_cls[ $field_key ] ) ) {
				$field_default_cls = $template_cls[ $field_key ]['class'];
			} else {
				$field_default_cls = $default_cls['class'];
			}

			if ( in_array( $field_key, $do_not_show_fields ) ) {
				$this->html_fields[ $field_key ] = true;
				continue;
			}


			$skipKey = [ 'billing_same_as_shipping', 'shipping_same_as_billing' ];
			if ( in_array( $field_key, $skipKey ) ) {
				continue;
			}

			if ( isset( $field['type'] ) && 'wfacp_html' === $field['type'] ) {
				$options           = [ 'wfacp-col-full' => __( 'Full', 'woofunnel-aero-checkout' ), ];
				$field_default_cls = 'wfacp-col-full';
			} else {
				$options = $this->get_class_options();
			}


			$this->add_select( 'wfacp_' . $template_slug . '_' . $field_key . '_field', $field['label'], $options, $field_default_cls );


		}

	}

	private function form_buttons() {

		$template = wfacp_template();
		$count    = $template->get_step_count();

		$backLinkArr = [];

		$this->add_heading( __( 'Button Text', 'woofunnel-aero-checkout' ), 'none' );
		for ( $i = 1; $i <= $count; $i ++ ) {

			$button_default_text = __( 'NEXT STEP →', 'woofunnels-aero-checkout' );
			$button_key          = 'wfacp_payment_button_' . $i . '_text';
			if ( $i == $count ) {
				$button_key          = 'wfacp_payment_place_order_text';
				$button_default_text = __( 'PLACE ORDER NOW', 'woofunnels-aero-checkout' );
			}
			$this->add_text( $button_key, __( "Step {$i}", 'woofunnel-aero-checkout' ), esc_js( $button_default_text ), [], "wfacp_field_text_wrap" );
			if ( $i > 1 ) {

				$backCount = $i - 1;

				$backLinkArr[ 'payment_button_back_' . $i . '_text' ] = [
					'label'   => __( "Return to Step {$backCount}", 'woofunnel-aero-checkout' ),
					'default' => sprintf( '« Return to Step %s ', $i - 1 )
				];

			}


		}

		if ( is_array( $backLinkArr ) && count( $backLinkArr ) > 0 ) {
			$this->add_heading( __( 'Return Link Text', 'woofunnel-aero-checkout' ), 'none' );
			$cart_name = __( '« Return to Cart', 'woofunnels-aero-checkout' );
			$this->add_text( "return_to_cart_text", 'Return to Cart', $cart_name, [ 'step_cart_link_enable' => 'yes' ], 'wfacp_field_text_wrap' );
			foreach ( $backLinkArr as $i => $val ) {


				$this->add_text( $i, $val['label'], $val['default'], [], 'wfacp_field_text_wrap' );
			}
		}

		$this->add_text( 'text_below_placeorder_btn', __( "Text Below Place Order Button", 'woofunnel-aero-checkout' ), sprintf( 'We Respect Your privacy & Information ', 'woofunnel-aero-checkout' ), [], 'wfacp_field_text_wrap wfacp_bold' );

	}

	private function mobile_mini_cart() {


		$this->add_tab( __( 'Collapsible Order Summary', 'woofunnels-aero-checkout' ), 5 );

		$enable_callapse_order_summary_device = [
			'tablet' => [
				'condition' => [
					'enable_callapse_order_summary_tablet' => [ 'yes' ],
				],
			],
			'mobile' => [
				'condition' => [
					'enable_callapse_order_summary_mobile' => [ 'yes' ],
				],
			],
		];

		$this->add_switcher( 'enable_callapse_order_summary', __( 'Enable', 'woofunnels-aero-checkout' ), '', '', 'no', 'yes', [], 'yes', 'yes' );

		$enable_callapse_order_summary_condition = [
			'enable_callapse_order_summary' => 'yes',
		];


		$condition = [
			'collapse_enable_coupon' => 'true',
		];

		$this->add_text( 'cart_collapse_title', __( 'Collapsed View Text ', 'woofunnels-aero-checkout' ), __( 'Show Order Summary', 'woofunnels-aero-checkout' ) );
		$this->add_text( 'cart_expanded_title', __( 'Expanded View Text', 'woofunnels-aero-checkout' ), __( 'Hide Order Summary', 'woofunnels-aero-checkout' ) );


		$this->add_switcher_without_responsive( 'collapse_enable_coupon', __( 'Enable Coupon', 'woofunnels-aero-checkout' ), '', '', 'false', 'true', [], 'true', 'true', '' );
		$this->add_switcher_without_responsive( 'collapse_enable_coupon_collapsible', __( 'Collapsible Coupon Field', 'woofunnels-aero-checkout' ), '', '', 'false', 'true', $condition, 'true', 'true', '' );

		$this->add_switcher_without_responsive( 'collapse_order_quantity_switcher', __( 'Quantity Switcher', 'woofunnels-aero-checkout' ), '', '', 'true', 'true', [], 'true', 'true', '' );
		$this->add_switcher_without_responsive( 'collapse_order_delete_item', __( 'Allow Deletion', 'woofunnels-aero-checkout' ), '', '', 'true', 'true', [], 'true', 'true', '' );


		$this->end_tab();

	}


	private function collapsible_summary_coupon() {

		$field_key = 'wfacp_collapsible_summary';
		$condition = [ 'collapse_enable_coupon' => 'true' ];
		$this->add_heading( __( 'Coupon', 'woofunnel-aero-checkout' ), '', $condition );
		$this->add_heading( __( 'Link', 'woofunnel-aero-checkout' ), '', $condition );
		$coupon_typography_opt = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info .wfacp_showcoupon',
		];
		$this->add_typography( $field_key . '_coupon_typography', implode( ',', $coupon_typography_opt ), [], $condition );
		$this->add_color( $field_key . '_coupon_text_color', $coupon_typography_opt, '', '', $condition );

		$this->add_heading( __( 'Field', 'woofunnel-aero-checkout' ), '', $condition );
		$form_fields_label_typo = [
			'{{WRAPPER}} #wfacp-e-form form.checkout_coupon.woocommerce-form-coupon .wfacp-col-left-half label.wfacp-form-control-label',
		];
		$fields_options         = [
			'font_weight' => [
				'default' => '400',
			],
		];

		$this->add_typography( $field_key . '_label_typo', implode( ',', $form_fields_label_typo ), $fields_options, $condition, __( 'Label Typography', 'woofunnels-aero-checkout' ) );

		$form_fields_label_color_opt = [
			'{{WRAPPER}} #wfacp-e-form form.checkout_coupon.woocommerce-form-coupon .wfacp-col-left-half label.wfacp-form-control-label',
		];
		$this->add_color( $field_key . '_label_color', $form_fields_label_color_opt, '', __( 'Label Color', 'woofunnels-aero-checkout' ), $condition );


		$fields_options = [
			'{{WRAPPER}} #wfacp-e-form form.checkout_coupon.woocommerce-form-coupon .wfacp-col-left-half .wfacp-form-control',
		];

		$optionString = implode( ',', $fields_options );
		$this->add_typography( $field_key . '_input_typo', $optionString, [], $condition, __( 'Coupon Typography' ) );


		$inputColorOption = [
			'{{WRAPPER}} #wfacp-e-form form.checkout_coupon.woocommerce-form-coupon .wfacp-col-left-half .wfacp-form-control',
		];
		$this->add_color( $field_key . '_input_color', $inputColorOption, '', __( 'Coupon Color', 'woofunnels-aero-checkout' ), $condition );

		$this->add_border_color( $field_key . '_focus_color', [ '{{WRAPPER}} #wfacp-e-form form.checkout_coupon.woocommerce-form-coupon .wfacp-col-left-half .wfacp-form-control:focus' ], '#61bdf7', __( 'Focus Color', 'woofunnel-aero-checkout' ), true, $condition );

		$fields_options = [
			'{{WRAPPER}} #wfacp-e-form form.checkout_coupon.woocommerce-form-coupon .wfacp-col-left-half .wfacp-form-control',
		];
		$default        = [ 'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4, 'unit' => 'px' ];
		$this->add_border( $field_key . '_coupon_border', implode( ',', $fields_options ), $condition, $default );


		$this->add_heading( __( 'Button', 'woofunnel-aero-checkout' ), '', $condition );

		/* Button color setting */
		$btnkey = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_sec_accordion_content form.checkout_coupon.woocommerce-form-coupon .form-row-last.wfacp-col-left-half button.button.wfacp-coupon-btn'
		];

		$btnkey_hover = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_sec_accordion_content form.checkout_coupon.woocommerce-form-coupon .form-row-last.wfacp-col-left-half button.button.wfacp-coupon-btn:hover'
		];
		$this->add_controls_tabs( $field_key . "_tabs", $condition );
		$this->add_controls_tab( $field_key . "_normal_tab", 'Normal' );
		$this->add_background_color( $field_key . '_btn_bg_color', $btnkey, '', __( 'Background', 'woofunnels-aero-checkout' ) );
		$this->add_color( $field_key . '_btn_text_color', $btnkey, '', __( 'Label', 'woofunnels-aero-checkout' ) );
		$this->close_controls_tab();

		$this->add_controls_tab( $field_key . "_hover_tab", 'Hover' );
		$this->add_background_color( $field_key . '_btn_bg_hover_color', $btnkey_hover, '', __( 'Hover', 'woofunnels-aero-checkout' ) );
		$this->add_color( $field_key . '_btn_bg_hover_text_color', $btnkey_hover, '', __( 'Hover Label', 'woofunnels-aero-checkout' ) );
		$this->close_controls_tab();
		$this->close_controls_tabs();
		/* Button color setting End*/

	}

	private function breadcrumb_bar() {
		$template     = wfacp_template();
		$num_of_steps = $template->get_step_count();


		if ( $num_of_steps >= 1 ) {
			$stepsCounter = 1;

			$tab_name              = __( 'Steps', 'woofunnels-aero-checkout' );
			$enable_condition_name = __( 'Enable Steps', 'elementor' );

			$options                    = [
				'tab'       => __( 'Tabs', 'woofunnel-aero-checkout' ),
				'bredcrumb' => __( 'Breadcrumb', 'woofunnel-aero-checkout' ),
			];
			$wfacp_elementor_hide_field = '';

			if ( $num_of_steps == 1 ) {
				$tab_name              = __( 'Header', 'woofunnels-aero-checkout' );
				$enable_condition_name = __( 'Enable', 'elementor' );
				unset( $options['bredcrumb'] );
				$wfacp_elementor_hide_field = 'wfacp_elementor_hide_field';
			}

			$this->add_tab( $tab_name, 5 );
			$this->add_switcher( 'enable_progress_bar', $enable_condition_name, '', '', '', 'yes', [], 'yes', 'yes' );


			$enableOptions = [
				'enable_progress_bar' => 'yes',
			];


			$this->add_select( 'select_type', "Select Type", $options, 'tab', $enableOptions, '', $wfacp_elementor_hide_field );


			$bredcrumb_controls = [
				'select_type'         => [
					'bredcrumb',
				],
				'enable_progress_bar' => "yes"
			];


			$progress_controls = [
				'select_type'         => [
					'progress_bar',
				],
				'enable_progress_bar' => "yes"
			];

			$labels = [
				[
					'heading'     => __( 'SHIPPING', 'woofunnels-aero-checkout' ),
					'sub-heading' => __( 'Where to ship it?', 'woofunnels-aero-checkout' ),
				],
				[
					'heading'     => __( 'PRODUCTS', 'woofunnels-aero-checkout' ),
					'sub-heading' => __( 'Select your product', 'woofunnels-aero-checkout' ),
				],
				[
					'heading'     => __( 'PAYMENT', 'woofunnels-aero-checkout' ),
					'sub-heading' => __( 'Confirm your order', 'woofunnels-aero-checkout' ),
				],

			];

			for ( $bi = 0; $bi < $num_of_steps; $bi ++ ) {
				$heading    = $labels[ $bi ]['heading'];
				$subheading = $labels[ $bi ]['sub-heading'];

				$label = __( 'Step', 'woofunnel-aero-checkout' );


				if ( $num_of_steps > 1 ) {
					$this->add_heading( $label . " " . $stepsCounter, 'none', [ 'enable_progress_bar' => "yes" ] );
				}


				$this->add_text( 'step_' . $bi . '_bredcrumb', "Title", "Step $stepsCounter", $bredcrumb_controls );

				$this->add_text( 'step_' . $bi . '_progress_bar', "Heading", "Step $stepsCounter", $progress_controls );


				$this->add_text( 'step_' . $bi . '_heading', "Heading", $heading, [ 'select_type' => 'tab', 'enable_progress_bar' => "yes" ] );
				$this->add_text( 'step_' . $bi . '_subheading', "Sub Heading", $subheading, [ 'select_type' => 'tab', 'enable_progress_bar' => "yes" ] );
				$stepsCounter ++;
				$heading    = '';
				$subheading = '';
			}

			if ( $num_of_steps > 1 ) {

				$condtion_control = [
					'select_type'         => [
						'bredcrumb',
						'progress_bar',
					],
					'enable_progress_bar' => "yes"
				];

				$cartTitle          = __( 'Title', 'woofunnel-aero-checkout' );
				$progresscartTitle  = __( 'Cart title', 'woofunnel-aero-checkout' );
				$settingDescription = __( 'Note: Cart settings will work for Global Checkout when user navigates from Product > Cart > Checkout', 'woofunnel-aero-checkout' );
				$cartText           = __( 'Cart', 'woocommerce' );

				$options = [
					'yes' => __( 'Yes', 'woofunnel-aero-checkout' ),
					'no'  => __( 'No', 'woofunnel-aero-checkout' ),

				];
				$this->add_heading( 'Cart', 'none', $bredcrumb_controls );

				$this->add_select( 'step_cart_link_enable', "Add to Breadcrumb", $options, 'yes', $condtion_control );
				$this->add_text( 'step_cart_progress_bar_link', $progresscartTitle, $cartText, $progress_controls );
				$this->add_text( 'step_cart_bredcrumb_link', $cartTitle, $cartText, $bredcrumb_controls, '', $settingDescription );

			}

			$this->end_tab();
		}


	}

	protected function register_styles() {

		$this->get_progress_settings();
		$this->get_heading_settings();
		$this->fields_typo_settings();
		$this->section_typo_settings();


		foreach ( $this->html_fields as $key => $v ) {

			$this->generate_html_block( $key );
		}

		$this->payment_buttons_styling();
		$this->payment_method_styling();
		$this->global_typography();
		$this->collapsible_order_summary();
		$this->class_section();
	}

	public function get_progress_settings() {

		$template = wfacp_template();

		$number_of_steps = $template->get_step_count();

		if ( $number_of_steps < 1 ) {
			return;
		}

		$class     = '';
		$step_text = __( 'Steps', 'woofunnel-aero-checkout' );
		if ( $number_of_steps <= 1 ) {
			$class     = 'wfacp_elementor_hide_field';
			$step_text = __( 'Header', 'woofunnel-aero-checkout' );
		}

		$controlsCondition = [
			'select_type' => [
				'bredcrumb',
				'progress_bar',
				'tab',
			],
		];


		$tab_condition          = [ 'select_type' => 'tab' ];
		$breadcrumb_condition   = [ 'select_type' => 'bredcrumb' ];
		$progress_bar_condition = [ 'select_type' => 'progress_bar' ];


		$this->add_tab( __( $step_text, 'woofunnel-aero-checkout' ), 2 );


		$this->add_heading( 'Typography', '', $controlsCondition );
		$this->add_typography( 'tab_heading_typography', '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-order2StepTitle.wfacp-order2StepTitleS1', [], $tab_condition, 'Heading' );
		$this->add_typography( 'tab_subheading_typography', '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-order2StepSubTitle.wfacp-order2StepSubTitleS1', [], $tab_condition, 'Sub Heading' );

		$alignmentOption = [ '{{WRAPPER}} #wfacp-e-form .wfacp-payment-tab-list .wfacp-order2StepHeaderText' ];
		$this->add_text_alignments( 'tab_text_alignment', $alignmentOption, '', [], 'center', [ 'select_type' => 'tab' ] );
		$this->add_typography( 'progress_bar_heading_typography', '{{WRAPPER}} #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li a', [], $progress_bar_condition, 'Heading' );

		/* Breadcrumb */


		$this->add_typography( 'breadcrumb_heading_typography', '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a', [], $breadcrumb_condition, 'Heading' );
		$this->add_heading( 'Colors', '', $controlsCondition );


		/* color setting */
		$this->add_controls_tabs( "wfacp_breadcrumb_style", $breadcrumb_condition );

		$this->add_controls_tab( "wfacp_breadcrumb_normal_tab", 'Normal' );
		$this->add_color( 'breadcrumb_text_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a' ], '', 'Color ', $breadcrumb_condition );
		$this->close_controls_tab();

		$this->add_controls_tab( "wfacp_breadcrumb_hover_tab", 'Hover' );
		$this->add_color( 'breadcrumb_text_hover_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp-form .wfacp_main_form.woocommerce .wfacp_steps_sec ul li a:hover' ], '', 'Color', $breadcrumb_condition );
		$this->close_controls_tab();


		$this->close_controls_tabs();

		/* Back link color setting End*/


		/* Progress Bar */
		$activeColor = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.wfacp_bred_active:before',
			'{{WRAPPER}} #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.wfacp_active_prev:before',
			'{{WRAPPER}} #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.df_cart_link.wfacp_bred_visited:before'
		];


		$this->add_background_color( 'progress_bar_line_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul:before' ], '', 'Line', $progress_bar_condition );
		$this->add_border_color( 'progress_bar_circle_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li:before' ], '', __( 'Circle Border', 'woofunnel-aero-checkout' ), false, $progress_bar_condition );

		$this->add_background_color( 'progress_bar_active_color', $activeColor, '', 'Active Step', $progress_bar_condition );
		$this->add_color( 'progressbar_text_color', [ '{{WRAPPER}}  #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li a' ], '', 'Text ', $progress_bar_condition );
		$this->add_color( 'progressbar_text_hover_color', [ '{{WRAPPER}}  #wfacp-e-form .wfacp_custom_breadcrumb .wfacp_steps_sec ul li a:hover' ], '', 'Text Hover', $progress_bar_condition );

		$this->add_controls_tabs( "wfacp_progress_bar_tabs", $tab_condition, $class );

		$this->add_controls_tab( "wfacp_progress_bar_active_tab", __( 'Active Step', 'woofunnels-aero-checkout' ) );

		$this->add_background_color( 'active_step_bg_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active' ], '', 'Background Color', $tab_condition );
		$this->add_color( 'active_step_text_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp_tcolor' ], '', 'Text Color', $tab_condition );
		$this->add_border_color( 'active_tab_border_bottom_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp-payment-tab-list.wfacp-active' ], '', __( 'Tab Border Color', 'woofunnel-aero-checkout' ), false, $tab_condition );

		if ( $number_of_steps > 1 ) {
			$this->add_background_color( 'active_step_count_bg_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber' ], '', 'Count Background Color', $tab_condition );
			$this->add_border_color( 'active_step_count_border_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber' ], '', __( 'Count Border Color', 'woofunnel-aero-checkout' ), false, $tab_condition );
			$this->add_color( 'active_step_count_text_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list.wfacp-active .wfacp-order2StepNumber' ], '', 'Count Text Color', $tab_condition );
		}

		$this->close_controls_tab();

		$this->add_controls_tab( "wfacp_progress_bar_inactive_tab", __( 'Inactive Step', 'woofunnels-aero-checkout' ) );

		$inactiveBgcolor = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list',

		];
		$this->add_background_color( 'inactive_step_bg_color', $inactiveBgcolor, '', __( 'Background Color', 'woofunnels-aero-checkout' ), $tab_condition );
		$this->add_color( 'inactive_step_text_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp_tcolor' ], '', __( 'Text Color', 'woofunnels-aero-checkout' ), $tab_condition );
		$this->add_border_color( 'inactive_tab_border_bottom_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp-payment-tab-list' ], '', __( 'Tab Border Color', 'woofunnel-aero-checkout' ), false, $tab_condition );
		$this->add_background_color( 'inactive_step_count_bg_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber' ], '', 'Count Background Color', $tab_condition );
		$this->add_border_color( 'inactive_step_count_border_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber' ], '', __( 'Count Border Color', 'woofunnel-aero-checkout' ), false, $tab_condition );
		$this->add_color( 'inactive_step_count_text_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list .wfacp-order2StepNumber' ], '', 'Count Text Color', $tab_condition );


		$this->close_controls_tab();
		$this->close_controls_tabs();

		$this->add_heading( 'Border Radius', '', $tab_condition );

		$label = __( 'Step Bar Border Radius', 'elementor' );
		$this->add_border_radius( 'border_radius_steps', '{{WRAPPER}} #wfacp-e-form .wfacp_form_steps .wfacp-payment-tab-list', $tab_condition, '', '', $label );

		$selector = [
			'{{WRAPPER}} #wfacp-e-form .tab'
		];

		$default = [ 'top' => 0, 'right' => 0, 'bottom' => 15, 'left' => 0, 'unit' => 'px', 'isLinked' => true ];
		$this->add_margin( 'wfacp_tab_margin', implode( ',', $selector ), $default, $default, $tab_condition, $default );

		$this->end_tab();
	}

	private function get_heading_settings() {
		/**
		 * @var $template WFACP_Elementor_Template
		 */


		$this->add_tab( __( 'Heading', 'woofunnel-aero-checkout' ), 2 );
		$this->add_heading( __( 'Heading', 'woofunnel-aero-checkout' ) );


		$sectionTitleOption = [ '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_section_title' ];

		$this->add_typography( 'section_heading_typo', implode( ',', $sectionTitleOption ) );
		$this->add_color( 'form_heading_color', $sectionTitleOption );


		$extra_options = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order'                       => 'font-weight: 700;font-size: 25px;',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-next-btn-wrap button' => 'font-weight: 700;font-size: 25px;',

		];

		$alignment = 'Left';
		if ( is_rtl() ) {
			$alignment = 'Right';
		}
		$this->add_text_alignments( 'form_heading_align', $sectionTitleOption, '', [], $alignment, [], $extra_options );


		$subheadingOption = [ '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-comm-title h4' ];

		//Sub heading start here
		$this->add_heading( __( 'Sub Heading', 'woofunnel-aero-checkout' ) );
		$this->add_typography( 'section_sub_heading_typo', implode( ',', $subheadingOption ) );
		$this->add_color( 'form_sub_heading_color', $subheadingOption );
		$this->add_text_alignments( 'form_sub_heading_align', $subheadingOption );


		//Sub heading end here

		$advanceOption = [ '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-section .wfacp-comm-title' ];
		$this->add_heading( __( 'Advanced', 'woofunnel-aero-checkout' ) );
		$this->add_background_color( 'form_heading_bg_color', $advanceOption, 'transparent' );

		$this->add_padding( 'form_heading_padding', implode( ',', $advanceOption ) );

		$default = [ 'top' => 0, 'right' => 0, 'bottom' => 10, 'left' => 0, 'unit' => 'px' ];
		$this->add_margin( 'form_heading_margin', implode( ',', $advanceOption ), $default, $default, [], $default );
		$this->add_border( 'form_heading_border', implode( ',', $advanceOption ) );

		$this->end_tab();

	}

	private function fields_typo_settings() {
		$this->add_tab( __( 'Fields', 'woofunnel-aero-checkout' ), 2 );

		$this->add_heading( __( 'Label', 'elementor' ) );

		$form_fields_label_typo = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper label.wfacp-form-control-label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_checkbox_field label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .create-account label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .create-account label span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_checkbox_field label span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper.wfacp_custom_field_radio_wrap > label ',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li label',

			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_shipping_options ul li .wfacp_shipping_price',
		];


		$this->add_typography( 'wfacp_form_fields_label_typo', implode( ',', $form_fields_label_typo ) );
		$form_fields_label_color_opt = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-form-control-label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-form-control-label abbr',
		];
		$this->add_color( 'wfacp_form_fields_label_color', $form_fields_label_color_opt );


		$fields_options = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="text"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="email"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="tel"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="password"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="number"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce select',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce textarea',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce number',
			'{{WRAPPER}} #wfacp-e-form .woocommerce-input-wrapper .wfacp-form-control',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',
			'body:not(.wfacp_pre_built) .select2-results__option',
			'body:not(.wfacp_pre_built) .select2-container--default .select2-search--dropdown .select2-search__field',
		];

		$optionString = implode( ',', $fields_options );

		$fields_options = [
			'font_size' => [
				'label'          => _x( 'Size', 'Typography Control', 'elementor' ),
				'default'        => [
					'unit' => 'px',
					'size' => 14,
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => 16,
				],
				'range'          => [
					'px' => [
						'min' => 14,
						'max' => 20,
					],
				],
			],
		];

		$this->add_heading( __( 'Input', 'elementor' ) );
		$this->add_typography( 'wfacp_form_fields_input_typo', $optionString, $fields_options );


		$inputColorOption = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-input-wrapper .wfacp-form-control',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce select',
		];
		$this->add_color( 'wfacp_form_fields_input_color', $inputColorOption );
		$this->add_heading( __( 'Border', 'elementor' ) );


		$fields_options = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="text"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="email"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="tel"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="password"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce select',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce textarea',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="number"].wfacp-form-control',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="text"].wfacp-form-control',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="emal"].wfacp-form-control',
			'{{WRAPPER}} #wfacp-e-form .wfacp_allowed_countries strong',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered',

		];

		$Validation_options = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-error',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-error ul',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-error li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-error li strong',
		];


		$default = [ 'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4, 'unit' => 'px' ];
		$this->add_border( 'wfacp_form_fields_border', implode( ',', $fields_options ), [], $default );

		$fields_options_hover = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="text"]:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="email"]:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="tel"]:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce input[type="password"]:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce select:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce textarea:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="number"].wfacp-form-control:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="text"].wfacp-form-control:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-input-wrapper input[type="emal"].wfacp-form-control:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .select2-container .select2-selection--single .select2-selection__rendered:hover',

		];


		$validation_error = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-required-field .wfacp-form-control',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-email .wfacp-form-control',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_coupon_failed .wfacp_coupon_code',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce p.woocommerce-invalid-required-field:not(.wfacp_select2_country_state):not(.wfacp_state_wrap) .woocommerce-input-wrapper .select2-container .select2-selection--single .select2-selection__rendered',
		];
		$this->add_border_color( 'wfacp_form_fields_focus_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .form-row:not(.woocommerce-invalid-email) .wfacp-form-control:focus' ], '#61bdf7', __( 'Focus Color', 'woofunnel-aero-checkout' ), true );
		$this->add_border_color( 'wfacp_form_fields_validation_color', $validation_error, '#d50000', __( 'Error Validation Color', 'woofunnel-aero-checkout' ), true );
		$this->end_tab();
	}

	private function section_typo_settings() {

		$this->add_tab( __( 'Section', 'woofunnel-aero-checkout' ), 2 );


		$form_section_bg_color = [
			'{{WRAPPER}} #wfacp-e-form .wfacp-section',

		];
		$this->add_background_color( 'form_section_bg_color', $form_section_bg_color, '', __( 'Background Color', 'woofunnels-aero-checkout' ) );
		$this->add_divider( "none" );
		$this->add_border( 'form_section_border', implode( ',', $form_section_bg_color ) );
		$this->add_divider( "none" );
		$this->add_border_shadow( 'form_section_box_shadow', '{{WRAPPER}} #wfacp-e-form .wfacp-section' );
		$this->add_divider( "none" );
		$this->add_padding( 'form_section_padding', '{{WRAPPER}} #wfacp-e-form  .wfacp-section' );
		$default = [ 'top' => 0, 'right' => 0, 'bottom' => 10, 'left' => 0, 'unit' => 'px' ];
		$this->add_margin( 'form_section_margin', '{{WRAPPER}} #wfacp-e-form .wfacp-section', $default, $default, [], $default );
		$this->end_tab();

	}

	private function payment_method() {

		$this->add_tab( __( 'Payment Gateways', 'woofunnel-aero-checkout' ), 5 );
		$this->add_heading( __( 'Section', 'elementor' ) );
		$this->add_text( 'wfacp_payment_method_heading_text', __( 'Heading', 'woofunnel-aero-checkout' ), esc_attr__( 'Payment Information', 'woofunnels-aero-checkout' ), [], 'wfacp_field_text_wrap' );
		$this->add_textArea( 'wfacp_payment_method_subheading', __( 'Sub heading', 'woofunnel-aero-checkout' ), esc_attr__( 'All transactions are secure and encrypted. Credit card information is never stored on our servers.', 'woofunnels-aero-checkout' ) );
		$this->form_buttons();
		$this->end_tab();


	}


	private function payment_buttons_styling() {
		$this->add_tab( __( 'Buttons', 'woofunnel-aero-checkout' ), 2 );

		$selector  = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order',

		];
		$selector1 = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order',
			'#wfacp_qr_model_wrap .wfacp_qr_wrap .wfacp_qv-summary .button'
		];


		$this->add_switcher( 'wfacp_make_button_sticky_on_mobile', __( 'Sticky on Mobile', 'woofunnels-aero-checkout' ), '', '', "no", 'yes', [], '', '', 'wfacp_elementor_device_hide' );

		$tablet_default = [
			'unit' => '%',
			'size' => 100,
		];
		$mobile_default = [
			'unit' => '%',
			'size' => 100,
		];

		$this->add_width( 'wfacp_button_width', implode( ',', $selector ), 'Button Width (in %)', [ 'unit' => '%', 'width' => 100 ], [], [ '%' ], $tablet_default, $mobile_default );

		$alignment1 = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-order-place-btn-wrap',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-next-btn-wrap button',
		];

		$alignment = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-order-place-btn-wrap',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .wfacp-next-btn-wrap',
		];


		$this->add_text_alignments( 'wfacp_form_button_alignment', $alignment, '', [], 'center', [] );

		$btntypo = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout button.button.button-primary.wfacp_next_page_button'
		];

		$fields_options = [
			'font_weight' => [
				'default' => '700',
			],
			'font_size'   => [
				'default' => [
					'unit' => 'px',
					'size' => 25
				]
			],
		];

		$this->add_typography( 'wfacp_form_payment_button_typo', implode( ',', $btntypo ), $fields_options );

		$button_bg_hover_color = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-next-btn-wrap button:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #payment button#place_order:hover',
			'#wfacp_qr_model_wrap .wfacp_qr_wrap .wfacp_qv-summary .button:hover'
		];


		/* Button Background hover tab */
		$this->add_heading( __( 'Color', 'elementor' ) );
		$this->add_controls_tabs( "wfacp_button_style_tab" );
		$this->add_controls_tab( "wfacp_button_style_normal_tab", 'Normal' );
		$this->add_background_color( 'wfacp_button_bg_color', $selector1, "", 'Background' );
		$this->add_color( 'wfacp_button_label_color', $selector1, '', 'Label' );
		$this->close_controls_tab();
		$this->add_controls_tab( "wfacp_button_style_hover_tab", 'Hover' );
		$this->add_background_color( 'wfacp_button_bg_hover_color', $button_bg_hover_color, "", 'Background' );
		$this->add_color( 'wfacp_button_label_hover_color', $button_bg_hover_color, '', 'Label' );
		$this->close_controls_tab();
		$this->close_controls_tabs();


		$this->add_divider( "none" );

		$default   = [ 'top' => "15", 'right' => "25", 'bottom' => "15", 'left' => "25", 'unit' => 'px', 'isLinked' => false ];
		$Mbdefault = [ 'top' => "10", 'right' => "20", 'bottom' => "10", 'left' => "20", 'unit' => 'px', 'isLinked' => false ];
		$this->add_padding( "wfacp_button_padding", implode( ',', $selector ), $default, $Mbdefault );
		$this->add_margin( "wfacp_button_margin", implode( ',', $selector ) );
		$this->add_divider( "none" );


		$this->add_border( "wfacp_button_border", implode( ',', $selector ) );
		$this->add_divider( "none" );

		$stepBackLink = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .btm_btn_sec.wfacp_back_cart_link .wfacp-back-btn-wrap a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a.wfacp_back_page_button',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce form.woocommerce-checkout .place_order_back_btn a'
		];

		$stepBackLinkHover = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .btm_btn_sec.wfacp_back_cart_link .wfacp-back-btn-wrap a:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-back-btn-wrap a.wfacp_back_page_button:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-checkout .place_order_back_btn a:hover'
		];


		$this->add_heading( __( 'Return Link', 'elementor' ), 'none' );


		/* Back Link color setting */
		$this->add_controls_tabs( "wfacp_back_link_style" );

		$this->add_controls_tab( "wfacp_back_link_normal_tab", 'Normal' );
		$this->add_color( 'step_back_link_color', $stepBackLink, '', "Color" );
		$this->close_controls_tab();

		$this->add_controls_tab( "wfacp_back_link_hover_normal_tab", 'Hover' );
		$this->add_color( 'step_back_link_hover_color', $stepBackLinkHover, '', "Color" );
		$this->close_controls_tab();
		$this->close_controls_tabs();

		/* Back link color setting End*/


		$this->add_heading( __( 'Additional Text', 'elementor' ) );
		$this->add_color( 'additional_text_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-payment-dec' ] );
		$this->add_background_color( 'additional_bg_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-payment-dec' ], "", 'Background' );

		$this->end_tab();

	}

	private function payment_method_styling() {
		$this->add_tab( __( 'Payment Methods', 'woofunnel-aero-checkout' ), 2 );

		$btnmethod_typo = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment p a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment ul li input',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment #add_payment_method #payment div.payment_box',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_payment #add_payment_method #payment .payment_box p',

		];

		$fields_options = [
			'font_weight' => [
				'default' => '700',
			],
			'font_size'   => [
				'default' => [
					'unit' => 'px',
					'size' => 25
				]
			],
		];
		$this->add_typography( 'wfacp_form_payment_method_typo', implode( ',', $btnmethod_typo ) );
		$this->add_color( 'wfacp_form_payment_method_color', $btnmethod_typo, '', 'Color' );

		$this->end_tab();
	}

	private function set_typo_default_value( $fontFamily = '' ) {
		$fields_options = [
			'font_size'       => [
				'default' => [
					'unit' => 'px',
					'size' => 14
				]
			],
			'font_weight'     => [
				'default' => '500',
			],
			'font_style'      => [
				'default' => 'normal',
			],
			'text_decoration' => [
				'default' => 'none',
			],
			'text_transform'  => [
				'default' => 'none',
			],

		];
		if ( ! empty( $fontFamily ) ) {
			$fields_options['font_family'] = [ 'default' => $fontFamily ];
		}

		$this->typo_default_value = $fields_options;

		return $this->typo_default_value;
	}

	private function global_typography() {
		$this->add_tab( __( 'Checkout Form', 'woofunnel-aero-checkout' ), 2 );

		$selector = [
			'body:not(.wfacpef_page) {{WRAPPER}} #wfacp-e-form .wfacp-form',
		];


		$default    = [ 'top' => 0, 'right' => 10, 'bottom' => 10, 'left' => 10, 'unit' => 'px', 'isLinked' => true ];
		$mb_default = [ 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0, 'unit' => 'px', 'isLinked' => false ];
		$this->add_padding( 'wfacp_form_border_padding', implode( ',', $selector ), $default, $mb_default, [], $default );

		$globalSettingOptions = [
			'body.wfacp_main_wrapper',
			'body #wfacp-e-form *:not(i)',
			'body .wfacp_qv-main *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_section_heading.wfacp_section_title',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_whats_included h3',
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description a',
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-section h4',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper label.wfacp-form-control-label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form input[type="text"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form input[type="email"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form input[type="tel"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form input[type="number"]',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form select',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form textarea',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form label span a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form button',
			'{{WRAPPER}} #wfacp-e-form #payment button#place_order',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li span',
			'{{WRAPPER}} #wfacp-e-form .woocommerce-form-login-toggle .woocommerce-info ',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-payment-dec',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form label.checkbox',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-title > div',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .select2-container .select2-selection--single .select2-selection__rendered',
			'{{WRAPPER}} #et-boc .et-l span.select2-selection.select2-selection--multiple',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_quantity_selector input',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_price_sec span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_sec *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_quantity_selector input',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_price_sec span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field fieldset .wfacp_best_value',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel .wfacp_product_switcher_col_2 .wfacp_you_save_text',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description h4',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol)',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-coupon-section .wfacp-coupon-page .woocommerce-info > span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info .wfacp_showcoupon',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form label.woocommerce-form__label span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr th',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot .shipping_total_fee td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td span.woocommerce-Price-amount.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_best_value',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_item_name',

			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td small',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th small',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td small',


			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_item_name',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .product-name .product-quantity',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody td.product-total',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container dl',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container dd',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container dt',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_container p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody tr span.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .wfacp_order_summary_item_name',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total small',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .cart_item .product-total span.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody .product-name .product-quantity',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody td.product-total',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table tbody dl',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table tbody dd',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table tbody dt',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table tbody p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table tbody tr span.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol)',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_you_save_text',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_you_save_text span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_msg',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-coupon-page .wfacp_coupon_remove_msg',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-coupon-page .wfacp_coupon_error_msg',
			'body:not(.wfacp_pre_built) .select2-results__option',
			'body:not(.wfacp_pre_built) .select2-container--default .select2-search--dropdown .select2-search__field',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_order_total_field table.wfacp_order_total_wrap tr td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_order_total_field table.wfacp_order_total_wrap tr td span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_order_total .wfacp_order_total_wrap',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form #payment button#place_order',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-checkout button.button.button-primary.wfacp_next_page_button',
			'{{WRAPPER}} #wfacp-e-form .wfacp-order2StepTitle.wfacp-order2StepTitleS1',
			'{{WRAPPER}} #wfacp-e-form .wfacp-order2StepSubTitle.wfacp-order2StepSubTitleS1',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_steps_sec ul li a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_custom_breadcrumb ul li a',
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr td span ',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset .wfacp_you_save_text',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_you_save_text span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_product_subs_details span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper.wfacp_checkbox_field label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .create-account label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .create-account label span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper.wfacp_checkbox_field label span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper.wfacp_custom_field_radio_wrap > label ',

			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td span.woocommerce-Price-amount.amount',


			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_name_inner *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_quantity_selector input',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_price_sec span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_name_inner *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_quantity_selector input',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_price_sec span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .wfacp-coupon-field-btn',
			'{{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_sec_accordion_content form.checkout_coupon button.button.wfacp-coupon-btn',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li .wfacp_shipping_price span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li .wfacp_shipping_price',

			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment p span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment p a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment ul',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment ul li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment ul li input',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment #add_payment_method #payment div.payment_box',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_payment #add_payment_method #payment .payment_box p',


		];

		$this->add_font_family( 'wfacp_font_family', $globalSettingOptions, 'Family', 'Open Sans' );

		$fields_contentColor = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-form-login-toggle .woocommerce-info',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form form.woocommerce-form.woocommerce-form-login.login p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form label.woocommerce-form__label span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_checkbox_field span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table tr.shipping td p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp-product-switch-title div',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-privacy-policy-text p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_options ul li p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .shop_table .wfacp-product-switch-title div',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-info .message-container',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form #wc_checkout_add_ons .description',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form ol li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form ul li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul#shipping_method label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul#shipping_method span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .woocommerce-checkout-review-order h3',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .aw_addon_wrap label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form p:not(.woocommerce-shipping-contents)',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form label:not(.wfacp-form-control-label):not(.wfob_title)not(.wfob_span)',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form label span:not(.optional)',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form',

			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li label span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_shipping_table ul li span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form table.shop_table.woocommerce-checkout-review-order-table tfoot tr.recurring-totals > th',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form p.wfacp-form-control-wrapper:not(.wfacp-anim-wrap) label.wfacp-form-control-label abbr',

		];


		$this->add_color( 'default_text_color', $fields_contentColor, '', "Content Color" );

		$default_link_color_option = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-form-login-toggle .woocommerce-info a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a:not(.wfacp_close_icon):not(.button-social-login)',

			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce label a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce ul li a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table tr td a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a.wfacp_remove_coupon',
		];


		$default_link_hover_color_option = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-form-login-toggle .woocommerce-info a:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a:not(.wfacp_close_icon):not(.button-social-login):hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a span:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce label a:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce ul li a:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table tr td a:hover',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce a.wfacp_remove_coupon:hover',
		];


		/* Button Background hover tab */
		$this->add_heading( __( 'Link Color', 'elementor' ) );
		$this->add_controls_tabs( "wfacp_form_link_color_tab" );
		$this->add_controls_tab( "wfacp_form_link_color_normal_tab", 'Normal' );
		$this->add_color( 'default_link_color', $default_link_color_option, '', 'Color' );
		$this->close_controls_tab();
		$this->add_controls_tab( "wfacp_form_link_color_hover_tab", 'Hover' );
		$this->add_color( 'default_link_hover_color', $default_link_hover_color_option, '', 'Color' );
		$this->close_controls_tab();
		$this->close_controls_tabs();


		$this->end_tab();

	}

	private function collapsible_order_summary() {
		$this->add_tab( __( 'Collapsible Order Summary', 'woofunnel-aero-checkout' ), 2 );
		$advanceOption = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_sec_accordion_content',
		];

		$this->add_switcher( 'order_summary_enable_product_image_collapsed', __( 'Enable Image', 'woofunnels-aero-checkout' ), '', '', "yes", 'yes', [], '', '', 'wfacp_elementor_device_hide' );
		$this->add_background_color( 'collapsible_order_summary_bg_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_mb_mini_cart_wrap .wfacp_mb_cart_accordian' ], '#f7f7f7', 'Collapsed Background' );
		$this->add_background_color( 'expanded_order_summary_bg_color', $advanceOption, '#f7f7f7', 'Expanded Background' );
		$this->add_color( 'expanded_order_summary_link_color', [
			'{{WRAPPER}} #wfacp-e-form .wfacp_show_icon_wrap a span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_show_price_wrap span'
		], '#323232', __( 'Text Color', 'woofunnels-aero-checkout' ) );

		$selector = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_collapsible_order_summary_wrap'
		];

		$default = [ 'top' => 0, 'right' => 0, 'bottom' => 15, 'left' => 0, 'unit' => 'px', 'isLinked' => true ];
		$this->add_margin( 'wfacp_collapsible_margin', implode( ',', $selector ), $default, $default, [], $default );

		$label = __( 'Border Radius', 'elementor' );
		$this->add_border_radius( 'wfacp_collapsible_border_radius', '{{WRAPPER}} #wfacp-e-form .wfacp_show_icon_wrap a span', [], '', '', $label );

		//$this->collapsible_summary_coupon();
		do_action( 'wfacp_elementor_collapsible_fields_settings', $this );
		$this->end_tab();

	}

	private function class_section() {
		$template           = wfacp_template();
		$template_slug      = $template->get_template_slug();
		$do_not_show_fields = WFACP_Common::get_html_excluded_field();
		$this->add_tab( __( 'Field Classes', 'woofunnel-aero-checkout' ), 3 );


		$sections = $this->section_fields;
		foreach ( $sections as $keys => $val ) {
			foreach ( $val as $loop_key => $field ) {
				if ( in_array( $loop_key, [ 'wfacp_start_divider_billing', 'wfacp_start_divider_shipping' ], true ) ) {
					$address_key_group = ( $loop_key == 'wfacp_start_divider_billing' ) ? __( 'Billing Address', 'woocommerce' ) : __( 'Shipping Address', 'woocommerce' );
					$this->add_heading( $address_key_group, 'none' );
				}

				if ( ! isset( $field['id'] ) || ! isset( $field['label'] ) ) {
					continue;
				}

				$field_key = $field['id'];

				if ( in_array( $field_key, $do_not_show_fields ) ) {
					$this->html_fields[ $field_key ] = true;
					continue;
				}


				$skipKey = [ 'billing_same_as_shipping', 'shipping_same_as_billing' ];
				if ( in_array( $field_key, $skipKey ) ) {
					continue;
				}
				$this->add_text( 'wfacp_' . $template_slug . '_' . $field_key . '_field_class', __( $field['label'], 'woofunnel-aero-checkout' ), '', [], '', '', 'Custom Class' );

			}
		}


		$this->end_tab();
	}


	protected function get_class_options() {
		return [
			'wfacp-col-full'       => __( 'Full', 'woofunnel-aero-checkout' ),
			'wfacp-col-left-half'  => __( 'One Half', 'woofunnel-aero-checkout' ),
			'wfacp-col-left-third' => __( 'One Third', 'woofunnel-aero-checkout' ),
			'wfacp-col-two-third'  => __( 'Two Third', 'woofunnel-aero-checkout' ),
		];
	}

	protected function html() {

		$template = wfacp_template();
		$id       = $this->get_id();
		if ( WFACP_Common::is_theme_builder() ) {
			do_action( 'wfacp_form_widgets_elementor_editor', $this );
		}
		$setting = WFACP_Common::get_session( $id );

		$template->set_form_data( $setting );

		/**
		 * @var $template WFACP_Elementor_template;
		 */
		if ( isset( $_COOKIE['wfacp_elementor_open_page'] ) && wp_doing_ajax() ) {
			$cookie             = $_COOKIE['wfacp_elementor_open_page'];
			$parts              = explode( '@', $cookie );
			$this->current_step = $parts[1];
			if ( ! empty( $this->current_step ) && 'single_step' !== $this->current_step ) {
				$template->set_current_open_step( $this->current_step );
				add_filter( 'wfacp_el_bread_crumb_active_class_key', [ $this, 'set_breadcrumb' ], 10, 2 );
			}


		}


		include $template->wfacp_get_form();


	}

	public function set_breadcrumb( $active, $instance ) {
		if ( ! empty( $this->current_step ) && 'single_step' !== $this->current_step ) {
			if ( 'two_step' == $this->current_step ) {
				$active = 1;
			} else if ( 'third_step' == $this->current_step ) {
				$active = 2;
			} else {
				$active = 0;
			}
		}

		return $active;
	}
}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \El_WFACP_Form_Widget() );