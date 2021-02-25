<?php
/**
 * PowerPack WooCommerce Cart widget.
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
 * Class Woo_Cart.
 */
class Woo_Cart extends Powerpack_Widget {

	/**
	 * Retrieve toggle widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Cart' );
	}

	/**
	 * Retrieve toggle widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Cart' );
	}

	/**
	 * Retrieve toggle widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Cart' );
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
		return parent::get_widget_keywords( 'Woo_Cart' );
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
	 * @access protected
	 */
	protected function _register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->register_controls();
	}

	/**
	 * Register Woo Cart widget controls.
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

		/* Style Tab: Headings */
		$this->register_style_heading_controls();

		/* Style Tab: Cart Table */
		$this->register_style_cart_table_controls();

		/* Style Tab: Update Cart Button */
		$this->register_style_update_cart_button_controls();

		/* Style Tab: Coupon */
		$this->register_style_form_coupon_controls();

		/* Style Tab: Cart Total */
		$this->register_style_cart_totals_controls();

		/* Style Tab: Checkout Button */
		$this->register_style_checkout_button_controls();

		/* Style Tab: Cross Sells */
		$this->register_style_cross_sells_controls();
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
			'section_settings',
			array(
				'label' => __( 'Settings', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_coupon',
			array(
				'label'              => __( 'Show Coupon Field', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'show_cross_sells',
			array(
				'label'              => __( 'Show Cross Sells', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'return_value'       => 'yes',
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab
	 */
	/**
	 * Register Layout Controls.
	 *
	 * @access protected
	 */
	protected function register_style_form_coupon_controls() {

		/**
		 * Style Tab: Coupon
		 * -------------------------------------------------
		 */
		$this->start_controls_section(
			'form_coupon_style',
			array(
				'label'     => __( 'Coupon', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_heading',
			array(
				'label'     => __( 'Input', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'form_coupon_input_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'form_coupon_input_width',
			array(
				'label'      => __( 'Input Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 400,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'form_coupon_input_height',
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
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_form_coupon_input_style' );

		$this->start_controls_tab(
			'tab_form_coupon_input_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'form_coupon_input_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text',
				'condition'   => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'form_coupon_input_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_form_coupon_input_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text:hover' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_background_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text:hover' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_form_coupon_input_focus',
			array(
				'label'     => __( 'Focus', 'powerpack' ),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_text_color_focus',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text:focus' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_background_color_focus',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text:focus' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_input_border_color_focus',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .input-text:focus' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'form_coupon_button_label_heading',
			array(
				'label'     => __( 'Coupon Button', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'form_coupon_button_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cart .coupon .button',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'form_coupon_button_spacing',
			array(
				'label'      => __( 'Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .button' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'form_coupon_button_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
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
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .button' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_form_coupon_button_style' );

		$this->start_controls_tab(
			'tab_form_coupon_button_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .button' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .button' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'form_coupon_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-cart .cart .coupon .button',
				'condition'   => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'form_coupon_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'form_coupon_button_box_shadow',
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cart .coupon .button',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_form_coupon_button_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .button:hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .button:hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'form_coupon_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .coupon .button:hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'form_coupon_button_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cart .coupon .button:hover',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Content Tab: Help Docs
	 *
	 * @since 1.4.8
	 * @access protected
	 */
	protected function register_content_help_docs() {

		$help_docs = PP_Config::get_widget_help_links( 'Woo_Cart' );

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
	 * Style Tab: Headings
	 * -------------------------------------------------
	 */
	protected function register_style_heading_controls() {

		$this->start_controls_section(
			'section_headings_style',
			array(
				'label' => __( 'Headings', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'sections_headings_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals > h2, {{WRAPPER}} .pp-woo-cart .cross-sells > h2' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sections_headings_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart_totals > h2, {{WRAPPER}} .pp-woo-cart .cross-sells > h2',
			)
		);

		$this->add_responsive_control(
			'sections_headings_spacing',
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
					'{{WRAPPER}} .pp-woo-cart .cart_totals > h2, {{WRAPPER}} .pp-woo-cart .cross-sells > h2' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Cart Table
	 * -------------------------------------------------
	 */
	protected function register_style_cart_table_controls() {
		$this->start_controls_section(
			'section_cart_table_style',
			array(
				'label' => __( 'Cart Table', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'section_cart_table_box_heading',
			array(
				'label'     => __( 'Box', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_cart_table_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-cart table.cart',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'section_cart_table_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'section_cart_table_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-cart .cart, {{WRAPPER}} .pp-woo-cart .cart th, {{WRAPPER}} .pp-woo-cart .cart td',
			)
		);

		$this->add_control(
			'section_cart_table_border_collapse',
			array(
				'label'     => __( 'Border Collapse', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'collapse',
				'options'   => array(
					'inherit'  => __( 'Theme Default', 'powerpack' ),
					'collapse' => __( 'Collapse', 'powerpack' ),
					'separate' => __( 'Separate', 'powerpack' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart' => 'border-collapse: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_cart_table_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'section_cart_table_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cart',
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

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'section_cart_table_head_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-cart table.cart thead th',
			)
		);

		$this->add_control(
			'section_review_order_table_head_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart table.cart thead th' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_review_order_table_head_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart table.cart thead th' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'section_cart_table_cart_items_heading',
			array(
				'label'     => __( 'Cart Items', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'cart_items_row_separator_type',
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
					'{{WRAPPER}} .pp-woo-cart .woocommerce-cart-form table.cart td' => 'border-top-style: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'cart_items_row_separator_color',
			array(
				'label'     => __( 'Separator Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .woocommerce-cart-form table.cart td' => 'border-top-color: {{VALUE}};',
				),
				'condition' => array(
					'cart_items_row_separator_type!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cart_items_row_separator_size',
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
					'{{WRAPPER}} .pp-woo-cart .woocommerce-cart-form table.cart td' => 'border-top-width: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'cart_items_row_separator_type!' => 'none',
				),
			)
		);

		$this->start_controls_tabs( 'cart_items_rows_tabs_style' );

		$this->start_controls_tab(
			'cart_items_even_row',
			array(
				'label' => __( 'Even Row', 'powerpack' ),
			)
		);

		$this->add_control(
			'cart_items_even_row_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .cart_item:nth-child(2n) td' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'cart_items_even_row_links_color',
			array(
				'label'     => __( 'Links Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .cart_item:nth-child(2n) a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'cart_items_even_row_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .cart_item:nth-child(2n) td' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'cart_items_odd_row',
			array(
				'label' => __( 'Odd Row', 'powerpack' ),
			)
		);

		$this->add_control(
			'cart_items_odd_row_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .cart_item:nth-child(2n+1) td' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'cart_items_odd_row_links_color',
			array(
				'label'     => __( 'Links Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .cart_item:nth-child(2n+1) a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'cart_items_odd_row_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .cart_item:nth-child(2n+1) td' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'cart_items_image_heading',
			array(
				'label'     => __( 'Image', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'cart_items_image_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => 80,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart table.cart .product-thumbnail img' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'cart_items_quantity_input_heading',
			array(
				'label'     => __( 'Quantity Input', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'cart_items_quantity_input_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 20,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .quantity .input-text' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cart_items_quantity_input_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .quantity .input-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_heading',
			array(
				'label'     => __( 'Product Remove Icon', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'cart_items_remove_icon_size',
			array(
				'label'      => __( 'Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'size' => '',
				),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart table.cart .remove' => 'font-size: {{SIZE}}{{UNIT}}; width: calc({{SIZE}}{{UNIT}} + 6px); height: calc({{SIZE}}{{UNIT}} + 6px); line-height: calc({{SIZE}}{{UNIT}} + 3px);',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_cart_items_remove_icon_style' );

		$this->start_controls_tab(
			'tab_cart_items_remove_icon_normal',
			[
				'label'                 => __( 'Normal', 'powerpack' ),
			]
		);

		$this->add_control(
			'cart_items_remove_icon_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart table.cart .remove' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_bg_color',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'selectors'             => [
					'{{WRAPPER}} .pp-woo-cart table.cart .remove' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_items_remove_icon_border_color',
			[
				'label'                 => __( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'selectors'             => [
					'{{WRAPPER}} .pp-woo-cart table.cart .remove' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_cart_items_remove_icon_hover',
            [
                'label'                 => __( 'Hover', 'powerpack' ),
            ]
        );

		$this->add_control(
			'cart_items_remove_icon_color_hover',
			array(
				'label'     => __( 'Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart table.cart .remove:hover' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'cart_items_remove_icon_bg_color_hover',
			[
				'label'                 => __( 'Background Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'selectors'             => [
					'{{WRAPPER}} .pp-woo-cart table.cart .remove:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cart_items_remove_icon_border_color_hover',
			[
				'label'                 => __( 'Border Color', 'powerpack' ),
				'type'                  => Controls_Manager::COLOR,
				'selectors'             => [
					'{{WRAPPER}} .pp-woo-cart table.cart .remove:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

        $this->end_controls_tab();
        $this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Cart Totals
	 * -------------------------------------------------
	 */
	protected function register_style_cart_totals_controls() {
		$this->start_controls_section(
			'section_cart_totals_style',
			array(
				'label' => __( 'Cart Totals', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'cart_totals_box_heading',
			array(
				'label' => __( 'Box', 'powerpack' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'cart_totals_background',
				'types'     => array( 'classic', 'gradient' ),
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cart_totals .shop_table th, {{WRAPPER}} .pp-woo-cart .cart_totals .shop_table td',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'cart_totals_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-cart .cart_totals .shop_table',
			)
		);

		$this->add_control(
			'cart_totals_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .shop_table' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'cart_totals_box_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cart_totals .shop_table',
			)
		);

		$this->add_control(
			'cart_totals_text_heading',
			array(
				'label'     => __( 'Table Text', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'cart_totals_text_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart_totals .shop_table',
			)
		);

		$this->add_control(
			'cart_totals_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .shop_table' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'cart_totals_headings_heading',
			array(
				'label'     => __( 'Table Headings', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'cart_totals_headings_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart_totals .shop_table th',
			)
		);

		$this->add_control(
			'cart_totals_headings_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .shop_table th' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Cart Table Buttons
	 * -------------------------------------------------
	 */
	protected function register_style_update_cart_button_controls() {

		$this->start_controls_section(
			'section_update_cart_button_style',
			array(
				'label' => __( 'Update Cart Button', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'update_cart_button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]',
			)
		);

		$this->add_responsive_control(
			'update_cart_button_width',
			array(
				'label'      => __( 'Width', 'powerpack' ),
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
					'{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'update_cart_button_margin',
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
					'{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_update_cart_button_style' );

		$this->start_controls_tab(
			'tab_update_cart_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'update_cart_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'update_cart_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'update_cart_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]',
			)
		);

		$this->add_control(
			'update_cart_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'update_cart_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'update_cart_button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_update_cart_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'update_cart_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'update_cart_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'update_cart_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'update_cart_button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart .button[name="update_cart"]:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Checkout Button
	 * -------------------------------------------------
	 */
	protected function register_style_checkout_button_controls() {

		$this->start_controls_section(
			'section_checkout_button_style',
			array(
				'label' => __( 'Checkout Button', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'checkout_button_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button',
			)
		);

		$this->add_control(
			'checkout_button_width',
			array(
				'label'        => __( 'Width', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'auto',
				'options'      => array(
					'auto'   => __( 'Auto', 'powerpack' ),
					'full'   => __( 'Full Width', 'powerpack' ),
					'custom' => __( 'Custom', 'powerpack' ),
				),
				'prefix_class' => 'pp-woo-cart-checkout-button-',
			)
		);

		$this->add_responsive_control(
			'checkout_button_custom_width',
			array(
				'label'      => __( 'Custom Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
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
					'{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'checkout_button_width' => 'custom',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_checkout_button_style' );

		$this->start_controls_tab(
			'tab_checkout_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'checkout_button_bg_color_normal',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'checkout_button_text_color_normal',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'checkout_button_border_normal',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button',
			)
		);

		$this->add_control(
			'checkout_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'checkout_button_margin',
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
					'{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'checkout_button_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'checkout_button_box_shadow',
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_checkout_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'checkout_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'checkout_button_text_color_hover',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'checkout_button_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button:hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'checkout_button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .pp-woo-cart .cart_totals .checkout-button:hover',
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style Tab: Cross Sells
	 * -------------------------------------------------
	 */
	protected function register_style_cross_sells_controls() {

		$this->start_controls_section(
			'section_cross_sells_style',
			array(
				'label'     => __( 'Cross Sells', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_title_heading',
			array(
				'label'     => __( 'Title', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_title_color_normal',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cross-sells .woocommerce-loop-product__title' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_title_color_hover',
			array(
				'label'     => __( 'Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cross-sells .woocommerce-loop-product__title:hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cross_sells_title_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cross-sells .woocommerce-loop-product__title',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_price_heading',
			array(
				'label'     => __( 'Price', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_price_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cross-sells .price' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cross_sells_price_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cross-sells .price',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_button_heading',
			array(
				'label'     => __( 'Button', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cross_sells_button_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cross-sells .button',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_cross_sells_button' );

		$this->start_controls_tab(
			'tab_cross_sells_button_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_button_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cross-sells .button' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_button_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cross-sells .button' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_cross_sells_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_button_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cross-sells .button:hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cross-sells .button:hover' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'cross_sells_sale_badge_heading',
			array(
				'label'     => __( 'Sale Badge', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_sale_badge_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cross-sells .onsale' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_control(
			'cross_sells_sale_badge_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-woo-cart .cross-sells .onsale' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'show_cross_sells' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cross_sells_sale_badge_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-woo-cart .cross-sells .onsale',
				'condition' => array(
					'show_coupon' => 'yes',
				),
			)
		);

		$this->end_controls_section();
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
				'pp-woo-cart',
			)
		);
		?>
		<?php do_action( 'pp_woo_before_cart_wrap' ); ?>

		<div <?php echo $this->get_render_attribute_string( 'container' ); ?>>
			<?php
				do_action( 'pp_woo_before_cart_content' );

			if ( '' === $settings['show_cross_sells'] ) {
				// Hide Cross Sell field on cart page
				remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
			}

			if ( '' === $settings['show_coupon'] ) {
				// Hide coupon field on cart page
				add_filter( 'woocommerce_coupons_enabled', '__return_false' );
			}

				echo do_shortcode( '[woocommerce_cart]' );

				do_action( 'pp_woo_after_cart_content' );
			?>
		</div>

		<?php do_action( 'pp_woo_after_cart_wrap' ); ?>
		<?php
	}
}
