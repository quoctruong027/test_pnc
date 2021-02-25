<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Class WFOCU_Product_Short_Description_Widget
 */
class WFOCU_Product_Short_Description_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'wfocu-short-description';
	}

	public function get_title() {
		return __( 'Product Short Description', 'elementor-pro' );
	}

	public function get_icon() {
		return 'wfocu-icon-product_description';
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


	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'image', 'product', 'gallery', 'lightbox' ];
	}

	protected function _register_controls() {

		$offer_id = WFOCU_Core()->template_loader->get_offer_id();

		$products        = array();
		$product_options = array( '0' => '--No Product--' );
		if ( ! empty( $offer_id ) ) {
			$products        = WFOCU_Core()->template_loader->product_data->products;
			$product_options = array();
		}

		$this->start_controls_section( 'section_product_desc', [
			'label' => __( 'Offer Product Description', 'woofunnels-upstroke-one-click-upsell' ),
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

		$this->end_controls_section();

		$this->start_controls_section( 'section_product_description_style', [
			'label' => __( 'Style', 'elementor-pro' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'text_align', [
			'label'     => __( 'Alignment', 'elementor-pro' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => [
				'left'    => [
					'title' => __( 'Left', 'elementor-pro' ),
					'icon'  => 'fa fa-align-left',
				],
				'center'  => [
					'title' => __( 'Center', 'elementor-pro' ),
					'icon'  => 'fa fa-align-center',
				],
				'right'   => [
					'title' => __( 'Right', 'elementor-pro' ),
					'icon'  => 'fa fa-align-right',
				],
				'justify' => [
					'title' => __( 'Justified', 'elementor-pro' ),
					'icon'  => 'fa fa-align-justify',
				],
			],
			'selectors' => [
				'{{WRAPPER}}' => 'text-align: {{VALUE}}',
			],
		] );

		$this->add_control( 'text_color', [
			'label'     => __( 'Text Color', 'elementor-pro' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#414349',
			'selectors' => [
				'{{WRAPPER}} .elementor-widget-container' => 'color: {{VALUE}}',
			],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'text_typography',
			'label'    => __( 'Typography', 'elementor-pro' ),
			'selector' => '{{WRAPPER}}',
		] );

	}

	/**
	 * Render output
	 */
	public function render() {

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
		$post_object       = get_post( $product->get_id() );
		$short_description = apply_filters( 'woocommerce_short_description', $post_object->post_excerpt );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

			<?php echo $short_description; // WPCS: XSS ok. ?>
		</div>
		<?php
	}
}
