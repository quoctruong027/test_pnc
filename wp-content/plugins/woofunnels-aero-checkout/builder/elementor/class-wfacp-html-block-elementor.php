<?php

abstract class WFACP_Elementor_HTML_BLOCK extends WFACP_EL_Fields {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
	}


	final protected function render() {
		WFACP_Elementor::set_locals( $this->get_name(), $this->get_id() );
		if ( ! wp_doing_ajax() && is_admin() ) {
			return;
		}
		if ( apply_filters( 'wfacp_print_elementor_widget', true, $this->get_id(), $this ) ) {
			$setting = $this->get_settings();
			$id      = $this->get_id();
			WFACP_Common::set_session( $id, $setting );
			$this->html();
		}
	}

	protected function html() {

	}


	protected function available_html_block() {
		$block = [ 'product_switching', 'order_total' ];

		return apply_filters( 'wfacp_html_block_elements', $block );
	}


	protected function order_summary( $field_key ) {


		$this->add_tab( __( 'Order Summary', 'woofunnel-aero-checkout' ), 2 );
		$this->add_heading( 'Product' );

		$this->add_switcher( 'order_summary_enable_product_image', __( 'Enable Image', 'woofunnels-aero-checkout' ), '', '', "yes", 'yes', [], '', '', 'wfacp_elementor_device_hide' );

		$cart_item_color = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_item_name',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .product-name .product-quantity',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody td.product-total',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .cart_item .product-total span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .cart_item .product-total span.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .cart_item .product-total small',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_container dl',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_container dd',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_container dt',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_container p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody tr span.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody dl',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody dd',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody dt',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody tr td span:not(.wfacp-pro-count)',
		];

		$this->add_typography( $field_key . '_cart_item_typo', implode( ',', $cart_item_color ) );

		$this->add_color( $field_key . '_cart_item_color', $cart_item_color, '#666666' );


		$this->add_border_color( 'mini_product_image_border_color', [ '{{WRAPPER}} #wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table tr.cart_item .product-image img' ], '', __( 'Image Border Color', 'woofunnel-aero-checkout' ), false, [ 'order_summary_enable_product_image' => 'yes' ] );

		$this->add_heading( __( 'Subtotal', 'woocommerce' ) );


		$cart_subtotal_color_option = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal th',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot .shipping_total_fee td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td span.woocommerce-Price-amount.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.shipping_total_fee td span.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.shipping_total_fee td span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount th',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount td span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount td span.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.cart-discount td p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total)',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td small',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th small',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) ul li label',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td span.woocommerce-Price-amount.amount',
		];


		$fields_options = [
			'font_weight' => [
				'default' => '400',
			],
		];

		$this->add_typography( 'order_summary_product_meta_typo', implode( ',', $cart_subtotal_color_option ) );
		$this->add_color( 'order_summary_product_meta_color', $cart_subtotal_color_option );

		$cart_total_color_option = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td span.woocommerce-Price-amount.amount',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td span',

			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td small',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td a',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total td p',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th small',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table.woocommerce-checkout-review-order-table tfoot tr.order-total th a',


		];

		$this->add_heading( 'Total' );
		$this->add_typography( $field_key . '_cart_subtotal_heading_typo', implode( ',', $cart_total_color_option ), $fields_options );
		$this->add_color( $field_key . '_cart_subtotal_heading_color', $cart_total_color_option, '' );

		$this->add_heading( __( 'Divider', 'woocommerce' ) );
		$divider_line_color = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce table.shop_table tbody .wfacp_order_summary_item_name',
			'{{WRAPPER}} #wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table tr.cart_item',
			'{{WRAPPER}} #wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table tr.cart-subtotal',
			'{{WRAPPER}} #wfacp-e-form table.shop_table.woocommerce-checkout-review-order-table tr.order-total',
		];


		$this->add_border_color( $field_key . '_divider_line_color', $divider_line_color, '' );
		$this->end_tab();

	}


	/**
	 * @param $field STring
	 * @param \Elementor\Widget_Base
	 */
	protected function product_switching( $field_key ) {
		$this->add_tab( __( 'Product Switcher', 'woofunnel-aero-checkout' ), 2 );

		/*  Selected Items Setting */

		$this->add_controls_tabs( "wfacp_selected_item_tabs" );
		$this->add_controls_tab( "wfacp_selected_item_tab", __( 'Selected Items', 'woofunnels-aero-checkout' ) );

		/* Typography  */

		$product_switcher_typo_option = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_name_inner *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_quantity_selector input',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_price_sec span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_subs_details *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset.wfacp-selected-product .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
		];
		$product_switcher_typo_string = implode( ',', $product_switcher_typo_option );
		$this->add_typography( 'selected_item_typography', $product_switcher_typo_string );


		/* Items Color */
		$selector = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_switcher_item',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_row_quantity',
		];

		$this->add_color( $field_key . '_label_color', $selector, '', 'Item Color' );

		/* Items Price Color */

		$itemPriceColorOpt = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .shop_table.wfacp-product-switch-panel .wfacp-selected-product .product-price',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .shop_table.wfacp-product-switch-panel .wfacp-selected-product .product-price span',
		];
		$this->add_color( $field_key . '_price_color', $itemPriceColorOpt, '', "Item Price Color" );

		$variant_color = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_selected_attributes .wfacp_pro_attr_single span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_selected_attributes .wfacp_pro_attr_single span:last-child',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details',
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details span',
		];
		$this->add_color( $field_key . '_variant_color', $variant_color, '#666666', 'Variant Color' );


		/* Background Color */
		$itemBgColor = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product',
		];
		$this->add_background_color( $field_key . '_item_background', $itemBgColor, "", 'Background Color' );


		$fields_options = [
			'border' => [
				'default' => 'solid'
			],
			'width'  => [
				'default' => [
					'top'    => 1,
					'bottom' => 1,
					'left'   => 1,
					'right'  => 1,
				]
			],
			'color'  => [
				'default' => '#dddddd'
			],

		];
		/* Border */
		$this->add_border( $field_key . '_item_border', '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item.wfacp-selected-product', [], [], $fields_options );

		$this->close_controls_tab();

		/* Optional Item Setting */


		$this->add_controls_tab( "wfacp_non_selected_item_tab", __( 'Non-selected Items', 'woofunnels-aero-checkout' ) );


		$product_switcher_typo_optional = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_name_inner *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_attributes .wfacp_selected_attributes  *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_quantity_selector input',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_price_sec span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_subs_details *',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset:not(.wfacp-selected-product) .wfacp_product_sec .wfacp_product_select_options .wfacp_qv-button',
		];

		$product_switcher_typo_optional_string = implode( ',', $product_switcher_typo_optional );
		$this->add_typography( $field_key . '_optional_item_typography', $product_switcher_typo_optional_string );


		/* Label Color Setting */
		$optionalLabelColorOpt = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_switcher_item',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item .wfacp_row_wrap .wfacp_product_choosen_label .wfacp_product_row_quantity'
		];

		$this->add_color( $field_key . '_optional_label_color', $optionalLabelColorOpt, '', esc_attr__( 'Item Color', 'woofunnels-aero-checkout' ) );


		/* Items Price Color */

		$optional_price_color_option = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .shop_table.wfacp-product-switch-panel .product-price',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .shop_table.wfacp-product-switch-panel .wfacp_product_price_sec span',
		];
		$this->add_color( $field_key . '_optional_price_color', $optional_price_color_option, '', "Item Price Color" );


		/* Background Color */

		$this->add_background_color( $field_key . '_optional_background', [ '{{WRAPPER}} .woocommerce-cart-form__cart-item.cart_item:not(.wfacp-selected-product)' ], "#ffffff", 'Background Color' );

		$this->add_background_color( $field_key . '_optional_background_hover', [ '{{WRAPPER}} .wfacp-product-switch-panel .woocommerce-cart-form__cart-item.cart_item:not(.wfacp-selected-product):hover' ], "#fbfbfb", 'Background Hover Color' );


		/* Border */
		$this->add_border( $field_key . '_optional_border', '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .woocommerce-cart-form__cart-item.cart_item:not(.wfacp-selected-product)', [], [], $fields_options );

		$this->close_controls_tab();
		$this->close_controls_tabs();
		$this->add_heading( __( 'Saving Text', 'woofunnels-aero-checkout' ) );
		$saveTextColorOption = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset .wfacp_you_save_text',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #wfacp-e-form .wfacp_main_form .wfacp_row_wrap .wfacp_you_save_text span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_row_wrap .wfacp_you_save_text span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details > span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol)',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details lebel',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form #product_switching_field .wfacp_product_switcher_col_2 .wfacp_product_subs_details span:not(.subscription-details):not(.woocommerce-Price-amount):not(.woocommerce-Price-currencySymbol)'
		];


		$typography = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-product-switch-panel fieldset .wfacp_you_save_text',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_row_wrap .wfacp_you_save_text span',


		];

		$this->add_typography( $field_key . '_you_save_typo', implode( ',', $typography ) );
		$this->add_color( $field_key . '_you_save_color', $saveTextColorOption, '#b22323' );


		//Best value Controls

		if ( true === WFACP_Common::is_best_value_available() ) {
			$this->add_heading( __( 'Best Value', 'woofunnels-aero-checkout' ) );
			$selector = [
				'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #product_switching_field fieldset .wfacp_best_value_container .wfacp_best_value',
				'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_best_value.wfacp_top_left_corner',
				'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_best_value.wfacp_top_right_corner',
			];

			/* Best Value: Color Setting */
			$this->add_typography( $field_key . '_best_value_typography', '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce #product_switching_field fieldset .wfacp_best_value' );
			$this->add_color( $field_key . '_best_value_text_color', $selector );
			$this->add_background_color( $field_key . '_best_value_bg_color', $selector, "", 'Background Color' );

			$this->add_border_color( '_best_value_border_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_main_form .shop_table.wfacp-product-switch-panel .woocommerce-cart-form__cart-item.cart_item.wfacp_best_val_wrap' ], '', __( 'Best Value Item Border Color', 'woofunnel-aero-checkout' ) );


			/* Typography */
			$this->add_border( $field_key . '_best_value_border', implode( ',', $selector ) );


		}


		if ( true === WFACP_Common::is_what_included_available() ) {


			$this->add_heading( __( "Custom Product Description", 'woofunnels-aero-checkout' ) );


			/* Section Heading Setting */
			$what_included_heading_opt = [
				'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included h3'
			];
			$this->add_heading( __( 'Heading', 'woofunnels-aero-checkout' ), 'none' );
			$this->add_typography( $field_key . '_what_included_heading', implode( ',', $what_included_heading_opt ) );
			$this->add_color( $field_key . '_what_included_heading_color', $what_included_heading_opt );


			/* Product Title Setting */
			$this->add_heading( __( 'Title', 'woofunnels-aero-checkout' ), 'none' );
			$this->add_typography( $field_key . '_what_included_product_title', '{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included .wfacp_product_switcher_description h4' );
			$this->add_color( $field_key . '_what_included_product_title_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_whats_included .wfacp_product_switcher_description h4' ], '#666666' );


			/* Product Description Setting */
			$this->add_heading( __( 'Description', 'woofunnels-aero-checkout' ), 'none' );
			$fields_options = [
				'font_weight' => [
					'default' => '400',
				],
			];

			$description_typo = [
				'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description p',
				'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description a',
				'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included .wfacp_product_switcher_description .wfacp_description',
			];
			$this->add_typography( $field_key . '_what_included_product_description', implode( ',', $description_typo ), $fields_options );
			$this->add_color( $field_key . '_what_included_product_title_description', $description_typo, '#6c6c6c' );


			$this->add_heading( __( 'Advanced', 'woofunnels-aero-checkout' ), 'none' );
			$advance_typo = [
				'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included',
			];
			$this->add_background_color( $field_key . '_what_included_bg', $advance_typo, "", 'Background Color' );

			$default        = [ 'top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1, 'unit' => 'px' ];
			$fields_options = [
				'border' => [
					'default' => 'solid'
				],
				'width'  => [
					'default' => [
						'top'    => 1,
						'bottom' => 1,
						'left'   => 1,
						'right'  => 1,
					]
				],
				'color'  => [
					'default' => '#efefef'
				],

			];
			$this->add_border( $field_key . '_what_included_border', '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_whats_included', [], $default, $fields_options );

			$description = __( 'Note: Add this CSS class <strong>"wfacp_for_mb_style"</strong> here if your checkout page width is less than 375px on desktop browser', 'woofunnels-aero-checkout' );
			$this->add_text( 'product_switcher_mobile_style', __( 'CSS Class', 'woofunnels-aero-checkout' ), '', [], '', $description );

		}
		$this->end_tab();
	}

	/**
	 * @param $field STring
	 * @param $this \Elementor\Widget_Base
	 */
	protected function order_total( $field_key ) {


		$this->add_tab( __( 'Order Total', 'woofunnel-aero-checkout' ), 2 );

		$order_total_text_color_opt = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table.wfacp_order_total_wrap tr td',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table.wfacp_order_total_wrap tr td strong > span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total_field table.wfacp_order_total_wrap tr td strong > span span.woocommerce-Price-currencySymbol',

		];
		$this->add_typography( $field_key . '_typography', implode( ',', $order_total_text_color_opt ) );
		$this->add_color( $field_key . '_order_total_text_color', $order_total_text_color_opt );

		$order_total_bg_color_opt = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_order_total .wfacp_order_total_wrap'
		];

		$this->add_background_color( $field_key . '_order_total_bg_color', $order_total_bg_color_opt, "", 'Background Color' );
		$this->add_border( $field_key . '_order_total_border_sec', implode( ',', $order_total_bg_color_opt ) );
		$this->end_tab();

	}

	protected function coupon_field_settings( $field_key ) {
		$this->add_tab( __( 'Coupon', 'woocommerce' ), 2 );
		$this->add_heading( __( 'Link', 'woofunnel-aero-checkout' ), '' );
		$coupon_typography_opt = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-coupon-section .wfacp-coupon-page .woocommerce-info > span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info .wfacp_showcoupon',
		];
		$this->add_typography( $field_key . '_coupon_typography', implode( ',', $coupon_typography_opt ) );
		$this->add_color( $field_key . '_coupon_text_color', $coupon_typography_opt );


		$this->add_heading( __( 'Field', 'woofunnel-aero-checkout' ) );
		$form_fields_label_typo = [
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper label.wfacp-form-control-label',
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper.wfacp-anim-wrap label.wfacp-form-control-label',
		];
		$fields_options         = [
			'font_weight' => [
				'default' => '400',
			],
		];

		$this->add_typography( $field_key . '_label_typo', implode( ',', $form_fields_label_typo ), $fields_options, [], __( 'Label Typography', 'woofunnels-aero-checkout' ) );

		$form_fields_label_color_opt = [
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper label.wfacp-form-control-label',
		];
		$this->add_color( $field_key . '_label_color', $form_fields_label_color_opt, '', __( 'Label Color', 'woofunnels-aero-checkout' ) );


		$fields_options = [
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper .wfacp-form-control',
		];

		$optionString = implode( ',', $fields_options );
		$this->add_typography( $field_key . '_input_typo', $optionString, [], [], __( 'Coupon Typography' ) );


		$inputColorOption = [
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper .wfacp-form-control',
		];
		$this->add_color( $field_key . '_input_color', $inputColorOption, '', __( 'Coupon Color', 'woofunnels-aero-checkout' ) );


		$this->add_border_color( $field_key . '_focus_color', [ '{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_coupon_field_box p.wfacp-form-control-wrapper .wfacp-form-control:focus' ], '#61bdf7', __( 'Focus Color', 'woofunnel-aero-checkout' ), true );

		$fields_options = [
			'{{WRAPPER}}  #wfacp-e-form .wfacp_main_form .wfacp_coupon_field_box p.wfacp-form-control-wrapper .wfacp-form-control',
		];
		$default        = [ 'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4, 'unit' => 'px' ];
		$this->add_border( $field_key . '_coupon_border', implode( ',', $fields_options ), [], $default );


		$this->add_heading( __( 'Button', 'woofunnel-aero-checkout' ) );

		/* Button color setting */
		$btnkey = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .wfacp_coupon_field_box .wfacp-coupon-field-btn'
		];

		$btnkey_hover = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .wfacp_coupon_field_box .wfacp-coupon-field-btn:hover'
		];
		$this->add_controls_tabs( $field_key . "_tabs" );
		$this->add_controls_tab( $field_key . "_normal_tab", 'Normal' );
		$this->add_background_color( $field_key . '_btn_bg_color', $btnkey, '', __( 'Background', 'woofunnels-aero-checkout' ) );
		$this->add_color( $field_key . '_btn_text_color', $btnkey, '', __( 'Label', 'woofunnels-aero-checkout' ) );
		$this->close_controls_tab();

		$this->add_controls_tab( $field_key . "_hover_tab", 'Hover' );
		$this->add_background_color( $field_key . '_btn_bg_hover_color', $btnkey_hover, '', __( 'Background', 'woofunnels-aero-checkout' ) );
		$this->add_color( $field_key . '_btn_bg_hover_text_color', $btnkey_hover, '', __( 'Label', 'woofunnels-aero-checkout' ) );
		$this->close_controls_tab();
		$this->close_controls_tabs();

		$this->add_typography( $field_key . '_btn_typo', implode( ',', $btnkey ), [], [], __( 'Button Typography' ) );
		/* Button color setting End*/
		$this->end_tab();

	}

	protected function order_coupon( $field_key ) {

		$this->coupon_field_settings( $field_key );

		return;

		$this->add_tab( __( 'Coupon', 'woofunnel-aero-checkout' ), 2 );

		$coupon_typography_opt = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-coupon-section .wfacp-coupon-page .woocommerce-info > span',
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form.woocommerce .wfacp_woocommerce_form_coupon .wfacp-coupon-section .woocommerce-info .wfacp_showcoupon',
		];
		$this->add_typography( $field_key . '_coupon_typography', implode( ',', $coupon_typography_opt ) );
		$this->add_color( $field_key . '_coupon_text_color', $coupon_typography_opt );

		/* Button color setting */
		$btnkey = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .wfacp_coupon_field_box .wfacp-coupon-field-btn'
		];

		$btnkey_hover = [
			'{{WRAPPER}} #wfacp-e-form .wfacp_main_form .wfacp_woocommerce_form_coupon .wfacp-coupon-section .wfacp_coupon_field_box .wfacp-coupon-field-btn:hover'
		];
		$this->add_controls_tabs( $field_key . "_tabs" );
		$this->add_controls_tab( $field_key . "_normal_tab", 'Normal' );
		$this->add_background_color( $field_key . '_btn_bg_color', $btnkey, '', __( 'Background', 'woofunnels-aero-checkout' ) );
		$this->add_color( $field_key . '_btn_text_color', $btnkey, '', __( 'Label', 'woofunnels-aero-checkout' ) );
		$this->close_controls_tab();

		$this->add_controls_tab( $field_key . "_hover_tab", 'Hover' );
		$this->add_background_color( $field_key . '_btn_bg_hover_color', $btnkey_hover, '', __( 'Background', 'woofunnels-aero-checkout' ) );
		$this->add_color( $field_key . '_btn_bg_hover_text_color', $btnkey_hover, '', __( 'Label', 'woofunnels-aero-checkout' ) );
		$this->close_controls_tab();
		$this->close_controls_tabs();
		/* Button color setting End*/


		$this->end_tab();
	}


	/**
	 * @param $field STring
	 * @param $this \Elementor\Widget_Base
	 */
	protected function generate_html_block( $field_key ) {
		if ( method_exists( $this, $field_key ) ) {
			$this->{$field_key}( $field_key );
		}
	}

}