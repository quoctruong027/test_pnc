<?php
/**
 * PowerPack WooCommerce Checkout widget.
 *
 * @package PowerPack
 */

namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Config;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Class Woo_Categories.
 */
class Woo_Checkout extends Powerpack_Widget {

	/**
	 * Retrieve toggle widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Checkout' );
	}

	/**
	 * Retrieve toggle widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Checkout' );
	}

	/**
	 * Retrieve toggle widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Checkout' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.4.13.1
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Checkout' );
	}

	/**
	 * Retrieve the list of scripts the toggle widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'pp-woocommerce',
			'powerpack-frontend',
		);
	}

	/**
	 * Retrieve the list of styles the Woo - Categories depended on.
	 *
	 * Used to set style dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_style_depends() {
		return array(
			'pp-woocommerce',
		);
	}

	/**
	 * Register controls.
	 *
	 * @since 1.3.3
	 * @access protected
	 */
	protected function _register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->register_controls();
	}

	/**
	 * Register woo checkout widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/* Product Control */
		$this->register_content_general_controls();

		/* Help Docs */
		$this->register_content_help_docs();

		/* Style: Sections */
		$this->register_style_controls_sections();

		/* Style: Sections */
		$this->register_style_controls_columns();

		/* Style: Inputs */
		$this->register_style_controls_inputs();

		/* Style: Returning Customer */
		$this->register_style_controls_returning_customer();

		/* Style: Coupon Bar */
		$this->register_style_controls_coupon_bar();

		/* Style: Headings */
		$this->register_style_controls_headings();

		/* Style: Billing Details */
		$this->register_style_controls_billing_details();
	}

	/**
	 * Register toggle widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_content_general_controls() {

		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '1',
				'options' => array(
					'1' => __( 'One Column', 'powerpack' ),
					'2' => __( 'Two Columns', 'powerpack' ),
				),
			)
		);

		$this->add_control(
			'columns_stack',
			array(
				'label'              => __( 'Stack On', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'tablet',
				'options'            => array(
					'tablet' => __( 'Tablet', 'powerpack' ),
					'mobile' => __( 'Mobile', 'powerpack' ),
				),
				'prefix_class'       => 'pp-woo-cols-stack-',
				'frontend_available' => true,
				'condition'          => array(
					'layout' => '2',
				),
			)
		);

		$this->add_responsive_control(
			'column_1_width',
			array(
				'label'      => __( 'First Column Width', 'powerpack' ) . ' (%)',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'devices'    => array( 'desktop', 'tablet' ),
				'default'    => array(
					'size' => 50,
				),
				'range'      => array(
					'%' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout-col-2 .woocommerce .col2-set' => 'width: calc( {{SIZE}}% - ({{column_gap.size}}px / 2) );',
					'{{WRAPPER}} .pp-woo-checkout-col-2 #order_review_heading, {{WRAPPER}} .pp-woo-checkout-col-2 .woocommerce-checkout-review-order' => 'width: calc( ( 100% - {{SIZE}}% ) - ({{column_gap.size}}px / 2) );',
				),
				'condition'  => array(
					'layout' => '2',
				),
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => __( 'Columns Gap', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'devices'    => array( 'desktop', 'tablet' ),
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => 30,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout-col-2 .woocommerce .col2-set' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => '2',
				),
			)
		);

		$this->add_control(
			'hide_additional_info',
			array(
				'label'        => __( 'Hide Additonal Information Box', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	protected function register_content_help_docs() {

		$help_docs = PP_Config::get_widget_help_links( 'Woo_Checkout' );

		if ( ! empty( $help_docs ) ) {

			/**
			 * Content Tab: Help Docs
			 *
			 * @since 1.4.8
			 * @access protected
			 */
			$this->start_controls_section(
				'section_help_docs',
				array(
					'label' => __( 'Help Docs', 'powerpack' ),
				)
			);

			$hd_counter = 1;
			foreach ( $help_docs as $hd_title => $hd_link ) {
				$this->add_control(
					'help_doc_' . $hd_counter,
					array(
						'type'            => Controls_Manager::RAW_HTML,
						'raw'             => sprintf( '%1$s ' . $hd_title . ' %2$s', '<a href="' . $hd_link . '" target="_blank" rel="noopener">', '</a>' ),
						'content_classes' => 'pp-editor-doc-links',
					)
				);

				$hd_counter++;
			}

			$this->end_controls_section();
		}
	}

	/**
	 * Style Tab: Section
	 * -------------------------------------------------
	 */
	protected function register_style_controls_columns() {

		$this->start_controls_section(
			'section_columns_style',
			array(
				'label' => __( 'Columns', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'column_1_style_heading',
			array(
				'label' => __( 'Column 1', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'column_1_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '.pp-woo-checkout.pp-woo-checkout-col-1 .woocommerce-checkout, .pp-woo-checkout.pp-woo-checkout-col-2 #customer_details',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'column_1_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '.pp-woo-checkout.pp-woo-checkout-col-1 .woocommerce-checkout, .pp-woo-checkout.pp-woo-checkout-col-2 #customer_details',
			)
		);

		$this->add_responsive_control(
			'column_1_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'.pp-woo-checkout.pp-woo-checkout-col-1 .woocommerce-checkout, .pp-woo-checkout.pp-woo-checkout-col-2 #customer_details' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'column_1_box_shadow',
				'selector' => '.pp-woo-checkout.pp-woo-checkout-col-1 .woocommerce-checkout, .pp-woo-checkout.pp-woo-checkout-col-2 #customer_details',
			)
		);

		$this->add_responsive_control(
			'column_1_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'.pp-woo-checkout.pp-woo-checkout-col-1 .woocommerce-checkout, .pp-woo-checkout.pp-woo-checkout-col-2 #customer_details' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'column_2_style_heading',
			array(
				'label'     => __( 'Column 2', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout' => '2',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'column_2_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '.pp-woo-checkout.pp-woo-checkout-col-2 .woocommerce-checkout #order_review',
				'condition' => array(
					'layout' => '2',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'column_2_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '.pp-woo-checkout.pp-woo-checkout-col-2 .woocommerce-checkout #order_review',
				'condition'   => array(
					'layout' => '2',
				),
			)
		);

		$this->add_responsive_control(
			'column_2_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'.pp-woo-checkout.pp-woo-checkout-col-2 .woocommerce-checkout #order_review' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => '2',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'column_2_box_shadow',
				'selector'  => '.pp-woo-checkout.pp-woo-checkout-col-2 .woocommerce-checkout #order_review',
				'condition' => array(
					'layout' => '2',
				),
			)
		);

		$this->add_responsive_control(
			'column_2_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'.pp-woo-checkout.pp-woo-checkout-col-2 .woocommerce-checkout #order_review' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => '2',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Section
	 * -------------------------------------------------
	 */
	protected function register_style_controls_sections() {

		$this->start_controls_section(
			'section_sections_style',
			array(
				'label' => __( 'Sections', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'sections_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-payment',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'sections_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'separator'   => 'before',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-payment',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'sections_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-payment',
			)
		);

		$this->add_responsive_control(
			'sections_gap',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 35,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout-col-2 .woocommerce .col2-set .col-1, {{WRAPPER}} .pp-woo-checkout-col-2 .woocommerce-checkout-review-order-table' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'sections_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table, {{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-payment' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Inputs
	 * -------------------------------------------------
	 */
	protected function register_style_controls_inputs() {

		$this->start_controls_section(
			'section_inputs_style',
			array(
				'label' => __( 'Inputs', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'inputs_text_align',
			array(
				'label'       => __( 'Text Alignment', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'default'     => 'left',
				'selectors'   => array(
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select' => 'text-align: {{VALUE}};',
				),
				'separator'   => 'after',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'inputs_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select',
			)
		);

		$this->add_control(
			'input_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'inputs_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'separator'   => 'before',
				'selector'    => '{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select',
			)
		);

		$this->add_responsive_control(
			'inputs_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'inputs_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select',
			)
		);

		$this->add_responsive_control(
			'inputs_gap',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce form .input-text, {{WRAPPER}} .woocommerce form  select' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'inputs_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce form .input-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'inputs_height',
			array(
				'label'     => __( 'Input Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 35,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .woocommerce .form-row input.input-text, {{WRAPPER}} .woocommerce .form-row select' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'textarea_height',
			array(
				'label'     => __( 'Textarea Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .woocommerce form .form-row textarea' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Returning Customer Box
	 * -------------------------------------------------
	 */
	protected function register_style_controls_returning_customer() {
		$this->start_controls_section(
			'section_returning_customer_style',
			array(
				'label' => __( 'Returning Customer Box', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'returning_customer_toggle_heading',
			array(
				'label' => __( 'Returning Customer Toggle', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'returning_customer_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_icon_color',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info:before' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_links_color',
			array(
				'label'     => __( 'Links Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_links_color_hover',
			array(
				'label'     => __( 'Links Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_toggle_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'returning_customer_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'returning_customer_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info',
			)
		);

		$this->add_responsive_control(
			'returning_customer_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'returning_customer_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login-toggle .woocommerce-info',
			)
		);

		$this->add_control(
			'returning_customer_form_heading',
			array(
				'label'     => __( 'Login Form', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'returning_customer_form_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_form_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'returning_customer_form_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'returning_customer_form_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_input_heading',
			array(
				'label'     => __( 'Login Form Input', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'returning_customer_form_input_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_input_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'returning_customer_form_input_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_input_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_form_input_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_input_height',
			array(
				'label'     => __( 'Input Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_input_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login input.input-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_input_label_heading',
			array(
				'label'     => __( 'Login Form Input Label', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'returning_customer_form_input_label_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_form_input_label_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login label',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_input_label_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 5,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_button_heading',
			array(
				'label'     => __( 'Login Form Button', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'returning_customer_form_button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button',
			)
		);

		$this->start_controls_tabs( 'tabs_returning_customer_form_button_style' );

		$this->start_controls_tab(
			'tab_returning_customer_form_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'returning_customer_form_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'returning_customer_form_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button',
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'returning_customer_form_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'returning_customer_form_button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_returning_customer_form_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'returning_customer_form_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'returning_customer_form_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'returning_customer_form_button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-login .button:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Coupon Bar
	 * -------------------------------------------------
	 */
	protected function register_style_controls_coupon_bar() {
		$this->start_controls_section(
			'section_coupon_bar_style',
			array(
				'label' => __( 'Coupon Bar', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'coupon_bar_toggle_heading',
			array(
				'label' => __( 'Coupon Toggle', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'coupon_bar_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_icon_color',
			array(
				'label'     => __( 'Icon Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info:before' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_links_color',
			array(
				'label'     => __( 'Links Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_links_color_hover',
			array(
				'label'     => __( 'Links Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'coupon_bar_toggle_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'coupon_bar_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'coupon_bar_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info',
			)
		);

		$this->add_responsive_control(
			'coupon_bar_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'coupon_bar_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon-toggle .woocommerce-info',
			)
		);

		$this->add_control(
			'coupon_bar_form_heading',
			array(
				'label'     => __( 'Coupon Form', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'coupon_form_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'coupon_form_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'coupon_form_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon',
			)
		);

		$this->add_responsive_control(
			'coupon_form_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'coupon_form_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon',
			)
		);

		$this->add_responsive_control(
			'coupon_form_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_form_input_heading',
			array(
				'label'     => __( 'Coupon Form Input', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'coupon_bar_form_input_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_form_input_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'coupon_bar_form_input_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code',
			)
		);

		$this->add_responsive_control(
			'coupon_bar_form_input_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'coupon_bar_form_input_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code',
			)
		);

		$this->add_responsive_control(
			'coupon_bar_form_input_height',
			array(
				'label'     => __( 'Input Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'coupon_bar_form_input_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon #coupon_code' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'coupon_bar_form_button_heading',
			array(
				'label'     => __( 'Coupon Form Button', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'coupon_bar_form_button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button',
			)
		);

		$this->start_controls_tabs( 'tabs_coupon_form_button_style' );

		$this->start_controls_tab(
			'tab_coupon_form_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'coupon_form_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'coupon_form_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'coupon_form_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button',
			)
		);

		$this->add_responsive_control(
			'coupon_form_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'coupon_form_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'coupon_form_button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_coupon_form_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'coupon_form_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'coupon_form_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'coupon_form_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'coupon_form_button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-form-coupon .button:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Headings
	 * -------------------------------------------------
	 */
	protected function register_style_controls_headings() {
		$this->start_controls_section(
			'section_headings_style',
			array(
				'label' => __( 'Headings', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'headings_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout #customer_details .woocommerce-billing-fields > h3, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields > h3, {{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields > h3, {{WRAPPER}} .pp-woo-checkout #order_review_heading' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'headings_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout #customer_details .woocommerce-billing-fields > h3, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields > h3, {{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields > h3, {{WRAPPER}} .pp-woo-checkout #order_review_heading',
			)
		);

		$this->add_responsive_control(
			'headings_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout #customer_details .woocommerce-billing-fields > h3, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields > h3, {{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields > h3, {{WRAPPER}} .pp-woo-checkout #order_review_heading' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Billing Details
	 * -------------------------------------------------
	 */
	protected function register_style_controls_billing_details() {
		$this->start_controls_section(
			'section_billing_details_style',
			array(
				'label' => __( 'Billing Details', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_billing_details_heading',
			array(
				'label' => __( 'Section', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'section_billing_details_background',
				'types'     => array( 'classic', 'gradient' ),
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_billing_details_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper',
			)
		);

		$this->add_responsive_control(
			'section_billing_details_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_billing_details_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper',
			)
		);

		$this->add_control(
			'section_billing_details_inputs_heading',
			array(
				'label'     => __( 'Inputs', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_billing_details_inputs_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select',
			)
		);

		$this->add_responsive_control(
			'section_billing_details_inputs_height',
			array(
				'label'     => __( 'Input Height', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 35,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'section_billing_details_inputs_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_billing_details_inputs_style' );

		$this->start_controls_tab(
			'tab_billing_details_inputs_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_billing_details_inputs_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select',
			)
		);

		$this->add_responsive_control(
			'section_billing_details_inputs_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_billing_details_inputs_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_billing_details_inputs_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_billing_details_inputs_box_shadow_hover',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text:hover, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select:hover',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_billing_details_inputs_focus',
			array(
				'label' => __( 'Focus', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_text_color_focus',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select:focus' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_background_color_focus',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select:focus' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_billing_details_inputs_border_color_focus',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select:focus' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_billing_details_inputs_box_shadow_focus',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper select:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper input.input-text:focus, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper select:focus',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'section_billing_details_inputs_label_heading',
			array(
				'label'     => __( 'Input Label', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_billing_details_inputs_label_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper label, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_billing_details_inputs_label_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper label, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper label',
			)
		);

		$this->add_responsive_control(
			'section_billing_details_inputs_label_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 5,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-billing-fields__field-wrapper label, {{WRAPPER}} .pp-woo-checkout .woocommerce-shipping-fields__field-wrapper label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Additional Information
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_additional_fields_style',
			array(
				'label' => __( 'Additional Information', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_additional_fields_heading',
			array(
				'label' => __( 'Section', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'section_additional_fields_background',
				'types'     => array( 'classic', 'gradient' ),
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_additional_fields_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper',
			)
		);

		$this->add_responsive_control(
			'section_additional_fields_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_additional_fields_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper',
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_heading',
			array(
				'label'     => __( 'Textarea', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_additional_fields_textarea_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea',
			)
		);

		$this->start_controls_tabs( 'tabs_additional_fields_textarea_style' );

		$this->start_controls_tab(
			'tab_additional_fields_textarea_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_additional_fields_textarea_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea',
			)
		);

		$this->add_responsive_control(
			'section_additional_fields_textarea_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_additional_fields_textarea_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_additional_fields_textarea_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_additional_fields_textarea_box_shadow_hover',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea:hover',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_additional_fields_textarea_focus',
			array(
				'label' => __( 'Focus', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_text_color_focus',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea:focus' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_background_color_focus',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea:focus' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_border_color_focus',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea:focus' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_additional_fields_textarea_box_shadow_focus',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper textarea:focus',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'section_additional_fields_textarea_label_heading',
			array(
				'label'     => __( 'Textarea Label', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_additional_fields_textarea_label_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_additional_fields_textarea_label_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper label',
			)
		);

		$this->add_responsive_control(
			'section_additional_fields_textarea_label_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 5,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-additional-fields__field-wrapper label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Review Order
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_review_order_style',
			array(
				'label' => __( 'Review Order', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_review_order_table_heading',
			array(
				'label' => __( 'Table', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_review_order_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'section_review_order_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_review_order_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table, {{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table th, {{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table td',
			)
		);

		$this->add_responsive_control(
			'section_review_order_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_review_order_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table',
			)
		);

		$this->add_responsive_control(
			'section_review_order_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_cell_heading',
			array(
				'label'     => __( 'Table Cell', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'section_review_order_cell_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table th, {{WRAPPER}} .pp-woocommerce.pp-woo-checkout .woocommerce-checkout #order_review .shop_table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_head_heading',
			array(
				'label'     => __( 'Table Head', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_review_order_table_head_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table thead th' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_head_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table thead th' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_foot_heading',
			array(
				'label'     => __( 'Table Footer', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_review_order_table_foot_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table tfoot tr' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_foot_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table tfoot tr' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_body_heading',
			array(
				'label'     => __( 'Table Body', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->start_controls_tabs( 'section_review_order_tbody_rows_tabs_style' );

		$this->start_controls_tab(
			'tab_section_review_order_even_row',
			array(
				'label' => __( 'Even Row', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_review_order_even_row_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table .cart_item:nth-child(2n) td' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_even_row_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table .cart_item:nth-child(2n) td' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_section_review_order_odd_row',
			array(
				'label' => __( 'Odd Row', 'powerpack' ),
			)
		);

		$this->add_control(
			'section_review_order_odd_row_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table .cart_item:nth-child(2n+1) td' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_odd_row_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout-review-order-table .cart_item:nth-child(2n+1) td' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'section_review_order_row_separator_heading',
			array(
				'label'     => __( 'Row Separator', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'section_review_order_row_separator_type',
			array(
				'label'     => __( 'Separator Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'none'   => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
					'dashed' => __( 'Dashed', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table td, {{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table tfoot th' => 'border-top-style: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_row_separator_color',
			array(
				'label'     => __( 'Separator Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table td, {{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table tfoot th' => 'border-top-color: {{VALUE}};',
				),
				'condition' => array(
					'section_review_order_row_separator_type!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'section_review_order_row_separator_size',
			array(
				'label'     => __( 'Separator Size', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table td, {{WRAPPER}} .pp-woo-checkout .woocommerce table.shop_table tfoot th' => 'border-top-width: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'section_review_order_row_separator_type!' => 'none',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Payment Method
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_payment_method_style',
			array(
				'label' => __( 'Payment Method', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_payment_method_heading',
			array(
				'label' => __( 'Section', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'section_payment_method_background',
				'types'     => array( 'classic', 'gradient' ),
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_payment_method_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment',
			)
		);

		$this->add_responsive_control(
			'section_payment_method_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_payment_method_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment',
			)
		);

		$this->add_control(
			'section_payment_method_label_heading',
			array(
				'label'     => __( 'Label', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'payment_method_label_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout .payment_methods label',
			)
		);

		$this->add_control(
			'payment_method_label_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout .payment_methods label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_payment_method_message_heading',
			array(
				'label'     => __( 'Message', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'payment_method_message_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment .payment_box',
			)
		);

		$this->add_control(
			'payment_method_message_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment .payment_box' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'payment_method_message_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment .payment_box' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #payment .payment_box:before' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Privacy Policy
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_privacy_policy_style',
			array(
				'label' => __( 'Privacy Policy', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'privacy_policy_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-terms-and-conditions-wrapper .woocommerce-privacy-policy-text' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'privacy_policy_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-terms-and-conditions-wrapper .woocommerce-privacy-policy-text',
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Button
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'section_checkout_button_style',
			array(
				'label' => __( 'Button', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order',
			)
		);

		$this->add_control(
			'button_width',
			array(
				'label'        => __( 'Width', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'auto',
				'options'      => array(
					'auto'   => __( 'Auto', 'powerpack' ),
					'full'   => __( 'Full Width', 'powerpack' ),
					'custom' => __( 'Custom', 'powerpack' ),
				),
				'prefix_class' => 'pp-woo-checkout-button-',
			)
		);

		$this->add_responsive_control(
			'button_custom_width',
			array(
				'label'      => __( 'Custom Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 50,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'button_width' => 'custom',
				),
			)
		);

		$this->add_responsive_control(
			'button_margin',
			array(
				'label'              => __( 'Margin', 'powerpack' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', 'em', '%' ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => array(
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				),
				'selectors'          => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order',
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-checkout .woocommerce-checkout #place_order:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function get_shortcode() {

		$shortcode = sprintf( '[%s %s]', 'woocommerce_checkout', $this->get_render_attribute_string( 'shortcode' ) );

		return $shortcode;
	}

	/**
	 * Render output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings();

		$this->add_render_attribute(
			'container',
			'class',
			array(
				'pp-woocommerce',
				'pp-woo-checkout',
				'pp-woo-checkout-col-' . $settings['layout'],
				'clearfix',
			)
		);

		if ( $settings['hide_additional_info'] == 'yes' ) {
			add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'container' ); ?>>
			<?php do_action( 'pp_woo_before_checkout_wrap' ); ?>

			<div class="woopack-product-checkout">
				<?php do_action( 'pp_woo_before_checkout_content' ); ?>
				<?php echo do_shortcode( '[woocommerce_checkout]' ); ?>
				<?php do_action( 'pp_woo_after_checkout_content' ); ?>
			</div>

			<?php do_action( 'pp_woo_after_checkout_wrap' ); ?>
		</div>
		<?php
	}

	public function render_plain_content() {
		echo $this->get_shortcode();
	}
}
