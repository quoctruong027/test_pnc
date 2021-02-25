<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;

/**
 * Class Elementor_WFOCU_Product_Title_Widget
 */
class Elementor_WFOCU_Product_Title_Widget extends \Elementor\Widget_Heading {

	/**
	 * Get widget name.
	 *
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wfocu-offer-product-title';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Product Title', 'woofunnels-upstroke-one-click-upsell' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'wfocu-icon-offer_title';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the widget belongs to.
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'upstroke' ];
	}


	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function _register_controls() {
		$offer_id = WFOCU_Core()->template_loader->get_offer_id();

		if ( empty( $offer_id ) ) {
			return;
		}

		$products        = array();
		$product_options = array( '0' => '--No Product--' );
		if ( ! empty( $offer_id ) ) {
			$products        = WFOCU_Core()->template_loader->product_data->products;
			$product_options = array();
		}

		$this->start_controls_section( 'section_product_title', [
			'label' => __( 'Product Title', 'woofunnels-upstroke-one-click-upsell' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		foreach ( $products as $key => $product ) {
			$product_options[ $key ] = $product->data->get_name();
		}

		$this->add_control( 'selected_product', [
			'label'   => __( 'Product', 'woofunnels-upstroke-one-click-upsell' ),
			'type'    => Controls_Manager::SELECT,
			'default' => key( $product_options ),
			'options' => $product_options,
		] );

		do_action( 'wfocu_add_elementor_controls', $this, $offer_id, $products );

		$this->add_control( 'header_size', [
			'label'     => __( 'HTML Tag', 'elementor' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => [
				'h1'  => 'H1',
				'h2'  => 'H2',
				'h3'  => 'H3',
				'h4'  => 'H4',
				'h5'  => 'H5',
				'h6'  => 'H6',
				'div' => 'div',
				'p'   => 'p',
			],
			'default'   => 'h2',
			'selectors' => [
				'{{WRAPPER}} .elementor-product-title-wrapper .elementor-wfocu-product-title' => 'margin-bottom: 0; font-size: 20px; font-family:  "Open Sans",sans-serif;',
			],
		] );

		$this->add_responsive_control( 'text_align', [
			'label'     => __( 'Alignment', 'elementor-pro' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => [
				'left'   => [
					'title' => __( 'Left', 'elementor-pro' ),
					'icon'  => 'fa fa-align-left',
				],
				'center' => [
					'title' => __( 'Center', 'elementor-pro' ),
					'icon'  => 'fa fa-align-center',
				],
				'right'  => [
					'title' => __( 'Right', 'elementor-pro' ),
					'icon'  => 'fa fa-align-right',
				],
			],
			'selectors' => [
				'{{WRAPPER}} .elementor-product-title-wrapper .elementor-wfocu-product-title' => 'text-align: {{VALUE}}',
			],
		] );

		$this->end_controls_section();
		$this->start_controls_section( 'section_price_style', [
			'label' => __( 'Product Title', 'elementor-pro' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Title Color', 'elementor-pro' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#414349',
			'scheme'    => [
				'type'  => Scheme_Color::get_type(),
				'value' => Scheme_Color::COLOR_2,
			],
			'selectors' => [
				'{{WRAPPER}} .elementor-product-title-wrapper .elementor-wfocu-product-title' => 'color: {{VALUE}}',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'typography',
			'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
			'selector' => '{{WRAPPER}} .elementor-product-title-wrapper, {{WRAPPER}} .elementor-product-title-wrapper .elementor-wfocu-product-title',
		] );

		$this->add_group_control( \Elementor\Group_Control_Text_Shadow::get_type(), [
			'name'     => 'text_shadow',
			'selector' => '{{WRAPPER}} .elementor-product-title-wrapper .elementor-wfocu-product-title',
		] );

		$this->end_controls_section();

	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'elementor-product-title-wrapper' );
		$this->add_render_attribute( 'header_size', 'class', 'elementor-wfocu-product-title' ); ?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php

			$title = __( 'Product Title', 'woofunnels-upstroke-one-click-upsell' );
			if ( isset( $settings['selected_product'] ) && ! empty( $settings['selected_product'] ) ) {
				if ( ! isset( WFOCU_Core()->template_loader->product_data->products ) ) {
					return;
				}

				$product_data = WFOCU_Core()->template_loader->product_data->products;
				$product_key  = $this->get_settings( 'selected_product' );

				$product = '';
				if ( isset( $product_data->{$product_key} ) ) {
					$product = $product_data->{$product_key}->data;
				}
				if ( ! $product instanceof WC_Product ) {
					return;
				}

				if ( $product instanceof WC_Product ) {
					$title = $product->get_title();
				}

				if ( empty( $title ) ) {
					return;
				}
			}

			$title_html = sprintf( '<%1$s %2$s>%3$s</%1$s>', $settings['header_size'], $this->get_render_attribute_string( 'header_size' ), $title );

			echo $title_html;

			?>
		</div>
		<?php
	}

	protected function _content_template() {
	}
}
