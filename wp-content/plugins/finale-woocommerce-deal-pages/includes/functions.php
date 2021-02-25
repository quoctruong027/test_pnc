<?php

function wcct_register_deal_batch( $args ) {
	if ( empty( $args['type'] ) ) {
		$args['type'] = '';
	}
	$batch_processor = wcct_deal_page_get_batch( $args['type'] );
	$batch_processor = apply_filters( 'wcct_deal_register_batch_process', $batch_processor, $args['type'], $args );
	$batch_processor->register( $args );
}

function wcct_deal_page_get_batch( $type ) {
	switch ( $type ) {
		case 'product':
			return new Finale_products_batch_handler();
			break;
	}

	return null;
}

function wcct_deal_get_all_batches() {
	global $currently_registered;

	return $currently_registered;
}

function wcct_deals_admin_handle_shortcode_meta_visibility( $cmb ) {
	$screen = get_current_screen();

	if ( $screen && is_object( $screen ) && 'wcct-deal-shortcode' === $screen->post_type && null !== filter_input( INPUT_GET, 'post' ) ) {
		return true;
	}

	return false;
}

function wcct_deal_time_ago( $time ) {
	return sprintf( _x( '%s ago', 'amount of time that has passed', 'locomotive' ), human_time_diff( $time, current_time( 'timestamp' ) ) );
}

function wcct_get_product_savings( $product, $label = '{{savings_percentage}}' ) {
	$decimal = apply_filters( 'wcct_deal_product_saving_decimal_values', 2 );
	if ( in_array( $product->get_type(), array( 'variable' ) ) ) {

		if ( ! $product->is_on_sale() ) {
			return false;
		}

		$min_regular_price = $product->get_variation_regular_price( 'min', true );
		$min_sale_price    = $product->get_variation_sale_price( 'min', true );
		$max_regular_price = $product->get_variation_regular_price( 'max', true );
		$max_sale_price    = $product->get_variation_sale_price( 'max', true );

		if ( empty( $min_regular_price ) || empty( $min_sale_price ) || 0 == $min_regular_price || 0 == $min_sale_price ) {
			return false;
		}

		$max_diff      = ( ( $max_regular_price - $max_sale_price ) / $max_regular_price ) * 100;
		$max_diff      = number_format( $max_diff, $decimal );
		$min_diff      = ( ( $min_regular_price - $min_sale_price ) / $min_regular_price ) * 100;
		$min_diff      = number_format( $min_diff, $decimal );
		$final_val     = array( ( $min_regular_price - $min_sale_price ), ( $max_regular_price - $max_sale_price ) );
		$final_percent = array( $min_diff, $max_diff );
		$you_save_html = $label;

		if ( min( $final_val ) == max( $final_val ) ) {
			$you_save_html = str_replace( '{{savings_value}}', '' . wc_price( min( $final_val ) ) . '', $you_save_html );
			$you_save_html = str_replace( '{{savings_percentage}}', '' . min( $final_percent ) . '%', $you_save_html );
			$you_save_html = str_replace( '{{savings_value_percentage}}', '' . wc_price( min( $final_val ) ) . ' (' . min( $final_percent ) . '%)', $you_save_html );
		} elseif ( min( $final_percent ) == max( $final_percent ) ) {
			$you_save_html = str_replace( '{{savings_value}}', '' . ( wc_price( min( $final_val ) ) . '–' . wc_price( max( $final_val ) ) ) . '', $you_save_html );
			$you_save_html = str_replace( '{{savings_percentage}}', '' . min( $final_percent ) . '%', $you_save_html );
			$you_save_html = str_replace( '{{savings_value_percentage}}', '' . ( wc_price( min( $final_val ) ) . '–' . wc_price( max( $final_val ) ) ) . ' (' . min( $final_percent ) . '%)', $you_save_html );
		} else {
			$you_save_html = str_replace( '{{savings_value}}', '' . ( wc_price( min( $final_val ) ) . '–' . wc_price( max( $final_val ) ) ) . '', $you_save_html );
			$you_save_html = str_replace( '{{savings_percentage}}', '' . ( min( $final_percent ) . '%-' . max( $final_percent ) ) . '%', $you_save_html );
			$you_save_html = str_replace( '{{savings_value_percentage}}', '' . ( wc_price( min( $final_val ) ) . '–' . wc_price( max( $final_val ) ) ) . ' (' . ( min( $final_percent ) . '%-' . max( $final_percent ) ) . '%)', $you_save_html );
		}

		$get_min_regular = $product->get_variation_regular_price( 'min', true );
		$get_max_regular = $product->get_variation_regular_price( 'max', true );
		$get_min_sale    = $product->get_variation_sale_price( 'min', true );
		$get_max_sale    = $product->get_variation_sale_price( 'max', true );
		$you_save_html   = str_replace( '{{regular_price}}', wc_price( $get_min_regular ) . ' - ' . wc_price( $get_max_regular ), $you_save_html );
		$you_save_html   = str_replace( '{{sale_price}}', wc_price( $get_min_sale ) . ' - ' . wc_price( $get_max_sale ), $you_save_html );

		if ( min( $final_val ) > 0 ) {
			$wcst_you_save_html = '' . $you_save_html . '';

			return $wcst_you_save_html;
		}
	} else {
		if ( ! $product->is_on_sale() || ! $product->is_in_stock() ) {
			return false;
		}

		$regular_price = $product->get_regular_price();
		$sale_price    = $product->get_sale_price();
		$sale_price    = empty( $sale_price ) ? 0 : $sale_price;

		if ( 0 === intval( $regular_price ) && 0 === $sale_price ) {
			return false;
		}
		if ( $sale_price !== $regular_price && $sale_price >= 0 ) {
			// sale price must have a value for price difference
			$diff               = ( ( $regular_price - $sale_price ) / $regular_price ) * 100;
			$diff               = number_format( $diff, $decimal );
			$you_save_html      = $label;
			$price_difference   = ( $regular_price - $sale_price );
			$you_save_html      = str_replace( '{{savings_value}}', '' . ( wc_price( $price_difference ) ) . '', $you_save_html );
			$you_save_html      = str_replace( '{{savings_percentage}}', '' . $diff . '%', $you_save_html );
			$you_save_html      = str_replace( '{{savings_value_percentage}}', '' . ( wc_price( $price_difference ) ) . ' (' . $diff . '%)', $you_save_html );
			$you_save_html      = str_replace( '{{regular_price}}', wc_price( $regular_price ), $you_save_html );
			$you_save_html      = str_replace( '{{sale_price}}', wc_price( $sale_price ), $you_save_html );
			$wcst_you_save_html = $you_save_html;

			return $wcst_you_save_html;
		}
	}

	return false;
}

function wcct_get_default_fields_value() {
	return array(
		'wcct_finale_deal_shortcode_campaign'           => '0',
		'_wcct_finale_deal_choose_campaign'             => 'multiple',
		'_wcct_action_after_campaign_expired'           => 'hide_products',
		'_wcct_action_after_campaign_expired_text'      => __( 'Sorry! This Campaign is not running at the moment.', 'finale-woocommerce-deal-pages' ),
		'_wcct_location_timer_show_single'              => '1',
		'_wcct_location_bar_show_single'                => '1',
		'_wcct_deal_template_layout'                    => 'grid',
		'_wcct_deal_grid_size'                          => '3',
		'_wcct_deal_layout_grid'                        => 'grid_layout2',
		'_wcct_deal_layout_list'                        => 'list_layout1',
		'_wcct_appearance_timer_single_skin'            => 'highlight_1',
		'_wcct_appearance_timer_single_bg_color'        => '#ffffff',
		'_wcct_appearance_timer_single_text_color'      => '#dd3333',
		'_wcct_appearance_timer_single_font_size_timer' => '18',
		'_wcct_appearance_timer_single_font_size'       => '10',
		'_wcct_appearance_timer_single_label_days'      => 'days',
		'_wcct_appearance_timer_single_label_hrs'       => 'hrs',
		'_wcct_appearance_timer_single_label_mins'      => 'mins',
		'_wcct_appearance_timer_single_label_secs'      => 'secs',
		'_wcct_deal_view_deal_timer_text_before'        => __( 'Deal Ends In', 'finale-woocommerce-deal-pages' ),
		'_wcct_deal_view_deal_timer_text_after'         => '',
		'_wcct_appearance_bar_single_skin'              => 'stripe_animate',
		'_wcct_appearance_bar_single_edges'             => 'smooth',
		'_wcct_appearance_bar_single_orientation'       => 'rtl',
		'_wcct_appearance_bar_single_bg_color'          => '#dddddd',
		'_wcct_appearance_bar_single_active_color'      => '#dd3333',
		'_wcct_appearance_bar_single_height'            => '12',
		'_wcct_appearance_bar_single_display_before'    => __( 'Hurry! only {{remaining_units}} at this price.' ),
		'_wcct_appearance_bar_single_display_after'     => '',
		'_wcct_add_to_cart_btn_text'                    => __( 'Add to cart', 'woocommerce' ),
		'_wcct_add_to_cart_btn_text_bg_color'           => '#ffa200',
		'_wcct_add_to_cart_btn_text_bg_color_hover'     => '#ffa900',
		'_wcct_add_to_cart_btn_text_color'              => '#fff',
		'_wcct_add_to_cart_btn_width'                   => 'full',
		'_wcct_sale_badge_color'                        => '#d26e4b',
		'_wcct_add_to_cart_btn_text_font_size'          => '18',
		'_wcct_deal_pages_add_to_cart_exclude'          => '',
		'_wcct_deal_you_save_text'                      => 'You Save',
		'_wcct_deal_you_save_value'                     => '{{savings_percentage}}',
		'_wcct_deal_pagination_style'                   => 'rounded',
		'_wcct_deal_pagination_active_bg_color'         => '#446084',
		'_wcct_deal_pagination_active_border_color'     => '#446084',
		'_wcct_deal_pagination_active_color'            => '#fff',
		'_wcct_deal_pagination_default_bg_color'        => 'transparent',
		'_wcct_deal_pagination_default_border_color'    => '#777777',
		'_wcct_deal_pagination_default_color'           => '#777777',
		'_wcct_deal_hide_description'                   => '',
		'_wcct_deal_hide_rating'                        => '',
		'_wcct_deal_hide_sale_badge'                    => '',
		'_wcct_deal_sale_badge_text'                    => __( 'Sale!', 'finale-woocommerce-deal-pages' ),
		'_wcct_sale_badge_text_color'                   => '#fff',
		'_wcct_deal_product_desc_length'                => '200',
		'_wcct_deal_shop_thumbnail_size'                => 'shop_catalog',
	);
}

function wcct_maybe_show_pagination( $query, $atts ) {
	if ( isset( $query->query['showposts'] ) ) {
		return;
	}

	$core         = Finale_deal_batch_processing::instance();
	$design       = $core->_wcct_deal_pagination_style;
	$shortcode_id = $core->shortcode_id;
	$class        = '';

	if ( 'square' === $design ) {
		$class = 'wcct_squre_style';
	}

	$class .= ' wcct_deal_sh_' . $shortcode_id;
	$css   = '.wcct_custom_paginations.wcct_deal_sh_' . $shortcode_id . ' .page-numbers>li>a ';
	$css   .= '{border-color: ' . $core->_wcct_deal_pagination_default_border_color . '; color: ' . $core->_wcct_deal_pagination_default_color . '; background-color: ' . $core->_wcct_deal_pagination_default_bg_color . ';}';
	$css   .= '.wcct_custom_paginations.wcct_deal_sh_' . $shortcode_id . ' .page-numbers>li>span.current ';
	$css   .= '{border-color: ' . $core->_wcct_deal_pagination_active_border_color . '; color: ' . $core->_wcct_deal_pagination_active_color . '; background-color: ' . $core->_wcct_deal_pagination_active_bg_color . ';}';
	$css   .= '.wcct_custom_paginations.wcct_deal_sh_' . $shortcode_id . ' .page-numbers>li>.current, .wcct_custom_paginations.wcct_deal_sh_' . $shortcode_id . ' .page-numbers>li>span:hover, .wcct_custom_paginations.wcct_deal_sh_' . $shortcode_id . ' .page-numbers>li>a:hover ';
	$css   .= '{border-color: ' . $core->_wcct_deal_pagination_active_border_color . '; color: ' . $core->_wcct_deal_pagination_active_color . '; background-color: ' . $core->_wcct_deal_pagination_active_bg_color . ';}';

	$core->add_css( $css );
	echo '<nav class="wcct_custom_paginations ' . $class . '">';

	$cur_page = get_query_var( 'paged' );
	$cur_page = empty( $cur_page ) ? get_query_var( 'page' ) : $cur_page;
	$cur_page = max( 1, $cur_page );

	echo paginate_links( apply_filters( 'wcct_deal_page_pagination_args', array(
		'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
		'format'    => '',
		'add_args'  => false,
		'current'   => $cur_page,
		'total'     => $query->max_num_pages,
		'prev_text' => '&larr;',
		'next_text' => '&rarr;',
		'type'      => 'list',
		'end_size'  => 3,
		'mid_size'  => 3,
	) ) );
	echo '</nav>';
}

function wcct_deals_modify_image_sizes_options() {
	$sizes     = get_intermediate_image_sizes();
	$new_array = array();
	if ( is_array( $sizes ) && count( $sizes ) > 0 ) {
		foreach ( $sizes as $key => $val ) {
			$new_array[ $val ] = ucwords( str_replace( '_', ' ', $val ) );
		}
	}

	return $new_array;
}
