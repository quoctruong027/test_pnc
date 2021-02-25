<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager as Control_Manager;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class El_WFACP_Form_Products extends WFACP_Elementor_HTML_BLOCK {
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
	}

	public function get_name() {
		return 'wfacp_form_products';
	}

	public function get_title() {
		return __( 'Products Switcher', 'woofunnels-aero-checkout' );
	}

	public function get_icon() {
		return 'fab fa-product-hunt';
	}

	public function get_categories() {
		return [ 'woofunnels-aero-checkout' ];
	}


	public function show_in_panel() {

		$wfacp_id     = WFACP_Common::get_id();
		$us_as_widget = get_post_meta( $wfacp_id, '_wfacp_el_product_switcher_us_a_widget', true );
		if ( wc_string_to_bool( $us_as_widget ) ) {
			return true;
		}

		return false;
	}

	protected function _register_controls() {

		//controls for Section
		$this->widget_section();
		// controls for selected item
		$this->selected_item();
		// controls for optional item
		$this->optional_item();

		//controls for you save
		$this->you_save();

		//controls for best value
		$this->best_value();

		//controls for what included
		$this->what_included();

	}

	protected function widget_section() {

		$this->add_tab( __( 'Section', 'woofunnels-aero-checkout' ) );
		$selectors = [
			'{{WRAPPER}} .wfacp-product-switch-title .product-remove',
			'{{WRAPPER}} .wfacp-product-switch-title .product-quantity',
			'{{WRAPPER}} .wfacp-product-switch-title .product-name'
		];
		$this->add_color( 'color', $selectors );
		$this->add_typography( 'typography', '{{WRAPPER}} .wfacp-product-switch-title .product-remove, {{WRAPPER}} .wfacp-product-switch-title .product-quantity, {{WRAPPER}} .wfacp-product-switch-title .product-name' );

		$this->end_tab();
	}


	protected function selected_item() {
		// Controls for selected items
		$this->add_tab( __( 'Selected Items', 'woofunnels-aero-checkout' ) );


		$this->add_heading( __( 'Item', 'woofunnels-aero-checkout' ) );
		$selector = [
			'{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_switcher_item',
			'{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_row_quantity',
		];
		$this->add_color( 'label_color', $selector, '#737373' );
		$this->add_typography( 'item_typo', '{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_switcher_item, {{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_row_quantity, {{WRAPPER}} .shop_table.wfacp-product-switch-panel .product-price' );


		$this->add_heading( __( 'Item Price', 'woofunnels-aero-checkout' ) );
		$selector = [ '{{WRAPPER}} .shop_table.wfacp-product-switch-panel .wfacp-selected-product .product-price' ];
		$this->add_color( 'price_color', $selector, '#737373' );
		$this->add_typography( 'price_typo', '{{WRAPPER}} .shop_table.wfacp-product-switch-panel .wfacp-selected-product .product-price span.woocommerce-Price-amount.amount' );

		$this->add_heading( __( 'Background', 'woofunnels-aero-checkout' ) );
		$this->add_background( 'item_background', '{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product', '#f3f3f3' );
		$this->add_heading( __( 'Border', 'woofunnels-aero-checkout' ) );

		$this->add_border( 'item_border', '{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product' );

		$this->end_tab();
	}

	protected function optional_item() {
		//Controls for unselected item
		$this->add_tab( __( 'Optional Items', 'woofunnels-aero-checkout' ) );
		$this->add_heading( __( 'Item', 'woofunnels-aero-checkout' ) );


		$selectors = [
			'{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_switcher_item',
			'{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_row_quantity'
		];
		$this->add_color( 'optional_label_color', $selectors, '#737373', esc_attr__( 'Label Color', 'woofunnels-aero-checkout' ) );
		$this->add_typography( 'optional_selected_typo', '{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_switcher_item, {{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_row_quantity, {{WRAPPER}} .shop_table.wfacp-product-switch-panel .product-price' );


		$this->add_heading( __( 'Item Price', 'woofunnels-aero-checkout' ) );
		$selector = [ '{{WRAPPER}} .shop_table.wfacp-product-switch-panel .product-price' ];
		$this->add_color( 'optional_price_color', $selector, '#737373' );
		$this->add_typography( 'optional_price_typo', '{{WRAPPER}} .shop_table.wfacp-product-switch-panel .product-price span.woocommerce-Price-amount.amount' );


		$this->add_heading( __( 'Background', 'woofunnels-aero-checkout' ) );
		$this->add_background( 'optional_background', '{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item:not(.wfacp-selected-product)' );

		$this->add_heading( __( 'Background Hover', 'woofunnels-aero-checkout' ) );

		$this->add_background( 'optional_hover_background', '{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item:not(.wfacp-selected-product):hover' );


		$this->add_heading( __( 'Border', 'woofunnels-aero-checkout' ) );
		$this->add_border( 'optional_border', '{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item, {{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item' );

		$this->end_tab();

	}

	protected function best_value() {
		if ( false === WFACP_Common::is_best_value_available() ) {
			return;
		}


		$this->add_tab( __( 'Best Value', 'woofunnels-aero-checkout' ), 1 );
		$selector = '.wfacp_main_form.woocommerce #product_switching_field fieldset .wfacp_best_value_container .wfacp_best_value';
		$this->add_width( 'best_value_width', $selector, '', [ 'width' => 30 ] );
		$this->add_color( 'best_value_text_color', [ '{{WRAPPER}}  .wfacp_main_form.woocommerce #product_switching_field fieldset .wfacp_best_value_container .wfacp_best_value' ] );
		$this->add_typography( 'best_value_typography', '{{WRAPPER}} .wfacp_main_form.woocommerce #product_switching_field fieldset .wfacp_best_value_container .wfacp_best_value' );
		$this->add_text_alignments( 'best_value_alignment', [ '{{WRAPPER}} .wfacp_best_val_wrap .wfacp_best_value' ], '', [], 'center' );


		$this->add_heading( __( 'Padding & Margin', 'woofunnels-aero-checkout' ) );
		$defaults = [ 'top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5, 'unit' => 'px' ];
		$this->add_padding( 'best_value_padding', $selector, $defaults );
		$defaults = [ 'top' => 10, 'right' => 0, 'bottom' => 5, 'left' => 0, 'unit' => 'px' ];
		$this->add_margin( 'best_value_margin', $selector, $defaults );

		$this->add_heading( __( 'Background', 'woofunnels-aero-checkout' ) );
		$this->add_background( 'best_value_background', $selector );
		$this->add_heading( __( 'Border', 'woofunnels-aero-checkout' ) );
		$this->add_border( 'best_value_border', $selector );
		$this->end_tab();

	}


	protected function you_save() {
		$this->add_tab( __( 'You Save Text', 'woofunnels-aero-checkout' ) );

		$this->add_color( 'you_save_color', [ '{{WRAPPER}} .wfacp_row_wrap .wfacp_you_save_text' ], '#b22323' );

		$typography = [
			'{{WRAPPER}} .wfacp_main_form.woocommerce #product_switching_field fieldset .wfacp_best_value_container .wfacp_best_value',
			'{{WRAPPER}}  .wfacp_main_form.woocommerce #product_switching_field fieldset .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button'

		];


		$this->add_typography( 'you_save_typo', implode( ' ', $typography ),[],[] );
		$this->end_tab();
	}

	protected function what_included() {
		$this->add_tab( __( 'What included', 'woofunnels-aero-checkout' ), 2 );
		$this->add_heading( __( 'Section', 'woofunnels-aero-checkout' ) );
		$this->add_background( 'what_included_bg', '{{WRAPPER}} .wfacp_whats_included' );
		$this->add_border( 'what_included_border', '{{WRAPPER}} .wfacp_whats_included' );

		$this->add_heading( __( 'Section Heading', 'woofunnels-aero-checkout' ) );
		$this->add_typography( 'what_included_heading', '{{WRAPPER}} .wfacp_whats_included h3' );
		$this->add_color( 'what_included_heading_color', [ '{{WRAPPER}} .wfacp_whats_included h3' ], "#333333" );


		$this->add_heading( __( 'Product Title', 'woofunnels-aero-checkout' ) );
		$this->add_typography( 'what_included_product_title', '{{WRAPPER}} .wfacp_whats_included .wfacp_product_switcher_description h4' );
		$this->add_color( 'what_included_product_title_color', [ '{{WRAPPER}} .wfacp_whats_included .wfacp_product_switcher_description h4' ], '#666666' );


		$this->add_heading( __( 'Product Description', 'woofunnels-aero-checkout' ) );
		$this->add_typography( 'what_included_product_description', '{{WRAPPER}} .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description p' );
		$this->add_color( 'what_included_product_title_description', [ '{{WRAPPER}} .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description p' ], '#333333' );

		$this->end_tab();
	}

	protected function html() {
		$wfacp_id     = WFACP_Common::get_id();
		$us_as_widget = get_post_meta( $wfacp_id, '_wfacp_el_product_switcher_us_a_widget', true );

		if ( '' == $us_as_widget ) {
			return true;
		}

		echo "<div class='wfacp_main_form'>";
		echo '&nbsp';
		WFACP_Common::get_product_switcher_table();
		echo "</div>";

	}
}

//\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \El_WFACP_Form_Products() );