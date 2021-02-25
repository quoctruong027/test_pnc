<?php
if ( ! defined( 'WFACP_TEMPLATE_DIR' ) ) {
	return '';
}
/**
 * @var $pro WC_Product;
 * @var $product_data []
 */
if ( ! $pro instanceof WC_Product ) {
	return;
}
if ( isset( WC()->cart->removed_cart_contents[ $cart_item_key ] ) ) {
	return;
}

// check if product is not added in cart then we check status of product
if ( '' == $cart_item_key ) {
	$manage_stock = WFACP_Common::check_manage_stock( $pro, $product_data['org_quantity'] );
	if ( false == $manage_stock || false == $pro->is_purchasable() ) {
		return;
	}
}


$hide_quick_view        = wc_string_to_bool( $switcher_settings['settings']['hide_quick_view'] );
$hide_quantity_switcher = wc_string_to_bool( $switcher_settings['settings']['hide_quantity_switcher'] );
$enable_delete_item     = false;
$you_save_text          = isset( $product_data['you_save_text'] ) ? $product_data['you_save_text'] : '';
$input_class            = 'wfacp_product_choosen';
if ( 'radio' === $type ) {
	$input_class = 'wfacp_product_switch';
} elseif ( 'hidden' == $type ) {
	$enable_delete_item = wc_string_to_bool( $switcher_settings['settings']['enable_delete_item'] );
}
$force_all_setting = false;
if ( isset( $switcher_settings['product_settings']['add_to_cart_setting'] ) && 1 == $switcher_settings['product_settings']['add_to_cart_setting'] ) {
	$force_all_setting = true;

}
$cart_variation_id = 0;
if ( ! is_null( $cart_item ) ) {
	if ( isset( $cart_item['variation_id'] ) ) {
		$cart_variation_id = $cart_item['variation_id'];
	}
}
$product_is_hide_cls = 'wfacp_without_qty';
if ( true != $hide_quantity_switcher ) {
	$product_is_hide_cls = 'wfacp_with_qty';
}

list( $product_attributes, $is_variation_error, $attributes_keys, $variation_attributes ) = WFACP_Common::get_cart_item_attributes( $cart_item, $pro, $product_data, $cart_variation_id );

$checked_cls = 'wfacp_ps_checked';
if ( '' == $product_data['is_checked'] ) {
	$checked_cls = 'wfacp_ps_not_checked';
}
$ps_cls     = 'ps_' . $type;
$cart_count = count( WC()->cart->get_cart_contents() );

$product_selected_class = '';
if ( ! isset( WC()->cart->removed_cart_contents[ $cart_item_key ] ) ) {
	if ( 'radio' === $type && '' !== $product_data['is_checked'] && '' !== $cart_item_key ) {
		$product_selected_class = 'wfacp-selected-product';
	} else {
		if ( '' !== $cart_item_key ) {
			$product_selected_class = 'wfacp-selected-product';
		}
	}
}
$is_sold_individually = false;
$item_key             = isset( $product_data['item_key'] ) ? $product_data['item_key'] : '';
$product_title        = WFACP_Common::get_product_switcher_item_title( $cart_item, $cart_item_key, $pro, $switcher_settings, $product_data, $variation_attributes );

$best_value          = isset( $product_data['best_value_text'] ) ? $product_data['best_value_text'] : '';
$best_value_position = isset( $product_data['best_value_position'] ) ? $product_data['best_value_position'] : null;
$quick_preview       = '';
$eye_icon_url        = WFACP_PLUGIN_URL . '/assets/img/show_popup.svg';
$choose_label        = '';

if ( in_array( $product_data['type'], WFACP_Common::get_variable_product_type() ) ) {

	/**
	 * @var $pro WC_Product_Variable;
	 */
	$choose_label = sprintf( "<a href='#' class='wfacp_qv-button var_product $is_variation_error' qv-id='%d' qv-var-id='%d'>%s</a>", $product_data['id'], $cart_variation_id, apply_filters( 'wfacp_choose_option_text', __( 'Choose an option', 'woocommerce' ) ) );
	if ( true != $hide_quick_view ) {
		$quick_preview = sprintf( "<a class='wfacp_qv-button' qv-id='%d'  qv-var-id='%d'><img src='%s'></a>", $product_data['id'], $cart_variation_id, $eye_icon_url );
	}
} else {
	if ( true != $hide_quick_view ) {
		$quick_preview = sprintf( "<a class='wfacp_qv-button' qv-id='%d'><img src='%s'></a>", $pro->get_id(), $eye_icon_url );
	}
}

list( $subscription_product_string, $you_save_text_html ) = WFACP_Common::get_product_switcher_item_you_save( $you_save_text, $price_data, $pro, $product_data, $cart_item, $cart_item_key );

if ( $pro->is_sold_individually() ) {
	$is_sold_individually = true;

}
$best_val_class = '';
if ( '' != $best_value ) {
	$best_val_class = 'wfacp_best_val_wrap';
}


$enable_delete_options = WFACP_Common::delete_option_enable_in_product_switcher();
if ( false == $enable_delete_options ) {
	$product_data['enable_delete'] = true;
}
$enable_hide_img = '';
if ( ( isset( $product_data['hide_product_image'] ) && true === $product_data['hide_product_image'] ) && true === $force_all_setting ) {
	$enable_hide_img = 'wfacp_ps_enable_hideImg1';
} else {
	$enable_hide_img = 'wfacp_ps_disable_hideImg1';
}
$wfacp_ps_active_radio_checkbox = '';
if ( false === $force_all_setting ) {
	$wfacp_ps_active_radio_checkbox = 'wfacp_ps_active_radio_checkbox';
}

$hide_qty_switcher_cls = '';
if ( $hide_quantity_switcher == true ) {
	$hide_qty_switcher_cls = 'wfacp_hide_qty_switcher';
}

if ( 'hidden' == $type && true === $enable_delete_item && ! is_null( WFACP_Common::get_cart_item_from_removed_items( $item_key ) ) ) {
	return;
}


$wfacp_you_save_text_html = 'wfacp_you_save_text_blank';
if ( '' !== $you_save_text_html ) {
	$wfacp_you_save_text_html = '';
}

$inner_class        = [ $best_val_class, $best_value_position, $product_is_hide_cls, $product_selected_class ];
$inner_class_string = implode( ' ', $inner_class );

add_filter( 'wp_get_attachment_image_attributes', 'WFACP_Common::remove_src_set' );
?>
    <fieldset class="woocommerce-cart-form__cart-item cart_item wfacp_product_row <?php echo trim( $inner_class_string ); ?>" data-item-key="<?php echo $item_key; ?>" cart_key="<?php echo $cart_item_key; ?>" data-id="<?php echo $pro->get_id(); ?>">
		<?php
		if ( '' != $best_value && ( 'top_left_corner' == $best_value_position || 'top_right_corner' == $best_value_position ) ) {
			printf( "<legend class='wfacp_best_value wfacp_%s'>%s</legend>", $best_value_position, $best_value );
		}
		?>
        <div class="wfacp_row_wrap <?php echo $ps_cls . ' ' . $enable_hide_img . ' ' . $wfacp_you_save_text_html; ?>">
            <div class="wfacp_ps_title_wrap">
                <div class="wfacp_product_switcher_col wfacp_product_switcher_col_1 ">
                    <input id="wfacp_product_choosen_<?php echo $item_key; ?>" type="<?php echo $type; ?>" name="wfacp_product_choosen" class="<?php echo $input_class; ?> wfacp_switcher_checkbox input-checkbox" data-item-key="<?php echo $item_key; ?>" <?php echo $product_data['is_checked']; ?> cart_key="<?php echo $cart_item_key; ?>">
					<?php
					if ( 'hidden' == $type && true === $enable_delete_item && true === wc_string_to_bool( $product_data['enable_delete'] ) && isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
						$item_class = 'wfacp_remove_item_from_cart';
						$item_icon  = 'x';
						?>
                        <div class="wfacp_product_switcher_remove_product wfacp_delete_item">
                            <a href="javascript:void(0)" class="<?php echo $item_class; ?>" data-cart_key="<?php echo $cart_item_key; ?>" data-item_key="<?php echo $item_key; ?>"><?php echo $item_icon; ?></a>
                        </div>
						<?php
					}

					$merge_tag_quantity = WFACP_Common::product_switcher_merge_tags( "{{quantity}}", $price_data, $pro, $product_data, $cart_item, $cart_item_key );
					if ( false == $product_data['hide_product_image'] ) {
						$qtyHtml   = sprintf( '<div class="wfacp-qty-ball"><span class="wfacp-qty-count wfacp_product_switcher_quantity"><span class="wfacp-pro-count">%s</span></span></div>', $merge_tag_quantity );
						$thumbnail = $pro->get_image( [ 100, 100 ], [ 'srcset' => false ] );
						echo sprintf( '<div class="product-image"><div class="wfacp-pro-thumb">%s</div>%s</div>', $thumbnail, $qtyHtml );
					}
					?>
                </div>
                <div class="wfacp_product_switcher_col wfacp_product_switcher_col_2">
					<?php
					echo "<div class='wfacp_product_switcher_description'>";
					$variation_class = count( $variation_attributes ) > 0 ? 'wfacp_variation_product_title' : '';
					$best_value_html = '';
					if ( '' != $best_value ) {
						$best_value_html = sprintf( "<span class='wfacp_best_value wfacp_best_value_below'>%s</span>", $best_value );
					}
					if ( '' != $best_value && ( 'above' == $best_value_position ) ) {
						echo "<div class='wfacp_best_value_container'>" . $best_value_html . "</div>";
					}
					$best_value_default = ( '' != $best_value && '' == $best_value_position ) ? $best_value_html : '';
					if ( true == $product_data['hide_product_image'] ) {


						$product_title .= '<span class="wfacp_product_row_quantity wfacp_product_switcher_quantity"> x' . $merge_tag_quantity . "</span>";
					}


					?>
                    <div class="product-name product_name">
                        <div class="wfacp_product_sec">
                            <div class="wfacp_product_name_inner">
                            <span class="wfacp_product_choosen_label_wrap <?php echo $variation_class ?>">
                                <span class="wfacp_product_choosen_label" for="<?php echo "wfacp_product_{$item_key}" ?>"><?php echo $product_title . " " . $best_value_default; ?></span>
                            </span>
								<?php echo $quick_preview ?>
                            </div>
                            <div class="wfacp_product_attributes">
								<?php
								$attribute_html = WFACP_Common::get_attribute_html( $cart_item, $cart_item_key, $pro, $switcher_settings, $product_data );
								if ( ! is_null( $attribute_html ) && isset( $attribute_html['selected'] ) ) {
									echo $attribute_html['selected'];
								}
								?>
                                <div class="wfacp_product_select_options">
									<?php
									echo ! empty( $attribute_html['not_selected'] ) ? $attribute_html['not_selected'] : $choose_label;
									?>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php
					if ( '' != $best_value && 'below' == $best_value_position ) {
						echo "<div class='wfacp_best_value_container'>" . $best_value_html . "</div>";
					}

					echo '<div class="wfacp_ps_div_row">';
					if ( $you_save_text_html != '' ) {
						echo $you_save_text_html;
					}
					echo $subscription_product_string;
					echo '</div>';
					echo '</div>';
					$cls_sold_individually = '';
					if ( isset( $is_sold_individually ) && $is_sold_individually == 1 ) {
						$cls_sold_individually = 'wfacp_sold_indi';
					}
					?>
                </div>
            </div>
            <div class="wfacp_product_sec_start ">

				<?php
				$hide_qty_switcher_cls = '';
				if ( true == $hide_quantity_switcher ) {
					$hide_qty_switcher_cls = 'wfacp_hide_qty_switcher1';
				}
				?>
                <div class="wfacp_product_switcher_col wfacp_product_switcher_col_3 <?php echo $cls_sold_individually . " " . $hide_qty_switcher_cls; ?>">
                    <div class="wfacp_product_quantity_container">
						<?php
						if ( ! $pro->is_sold_individually() ) {
							$rqty       = 1;
							$disableQty = '';

							$qty_step = 1;
							if ( '' !== $cart_item_key ) {
								$qty_step = 0;
								$rqty     = $product_data['quantity'];
							} else {
								if ( $type == 'radio' ) {
									$disableQty = 'disabled';
								}
							}
							$minMax = apply_filters( 'wfacp_product_item_min_max_quantity', [ 'min' => $qty_step, 'max' => '' ], $pro, $item_key, $cart_item_key );
							?>
                            <div class="wfacp_quantity_selector" style="<?php echo ( true != $hide_quantity_switcher ) ? 'display:flex' : 'display:none;pointer-events:none;'; ?>">

                                <div class="wfacp_quantity q_h">
                                    <div class="wfacp_qty_wrap">
                                        <div class="value-button wfacp_decrease_item" data-item-key='<?php echo $item_key; ?>' onclick="decreaseItmQty(this,'')" value="Decrease Value">-</div>
                                        <input type="number" step="1" min="<?php echo $minMax['min']; ?>" max="<?php echo $minMax['max']; ?>" value="<?php echo $rqty; ?>" data-value="<?php echo $rqty; ?>" name="wfacp_product_switcher_quantity_<?php echo $item_key; ?>" class="wfacp_product_switcher_quantity wfacp_product_quantity_number_field" onfocusout="this.value = (Math.abs(this.value)<0?0:Math.abs(this.value))" <?php echo $disableQty; ?>>
                                        <div class="value-button wfacp_increase_item" data-item-key='<?php echo $item_key; ?>' onclick="increaseItmQty(this,'')" value="Increase Value">+</div>
                                    </div>
                                </div>

                            </div>

							<?php
						} elseif ( $is_sold_individually ) {
							?>
                            <span class="wfacp_sold_individually">1</span>
							<?php
						}
						?>
                    </div>
                    <div class="wfacp_product_price_container product-price">
                        <div class="wfacp_product_price_sec">
							<?php


							if ( apply_filters( 'wfacp_show_product_price', true, $pro, $cart_item_key, $price_data ) ) {
								$price_html = '';

								if ( in_array( $pro->get_type(), WFACP_Common::get_subscription_product_type() ) ) {

									if ( '' !== $cart_item_key ) {
										$price_html = wc_price( $price_data['price'] );
									} else {
										$price_html = wc_price( WFACP_Common::get_subscription_price( $pro, $price_data ) );
									}
								} else {
									if ( $price_data['regular_org'] == 0 ) {
										echo $pro->get_price_html();
									} else {
										if ( $price_data['price'] > 0 && $price_data['regular_org'] > 0 && ( round( $price_data['price'], 2 ) !== round( $price_data['regular_org'], 2 ) ) ) {
											if ( $price_data['price'] > $price_data['regular_org'] ) {
												$price_html = wc_price( $price_data['price'] );
											} else {
												$price_html = wc_format_sale_price( $price_data['regular_org'], $price_data['price'] );
											}
										} else {
											$price_html = wc_price( $price_data['price'] );
										}
									}
								}
								echo apply_filters( 'wfacp_product_switcher_price_text', $price_html, $pro, $price_data, $product_data );
							} else {
								do_action( 'wfacp_show_product_price_placeholder', $pro, $cart_item_key, $price_data );
							}
							?>


                        </div>


                    </div>
					<?php
					if ( 'hidden' == $type && true === $enable_delete_item && true === wc_string_to_bool( $product_data['enable_delete'] ) && isset( $cart_item_key ) && '' != $cart_item_key ) {
						$item_class = 'wfacp_remove_item_from_cart';
						$item_icon  = 'x';
						?>
                        <div class="wfacp_crossicon_for_mb">
                            <div class="wfacp_product_switcher_remove_product wfacp_delete_item">
                                <a href="javascript:void(0)" class="<?php echo $item_class; ?>" data-cart_key="<?php echo $cart_item_key; ?>" data-item_key="<?php echo $item_key; ?>"><?php echo $item_icon; ?></a>
                            </div>
                        </div>
						<?php
					}
					?>


                </div>
            </div>

        </div>


    </fieldset>
<?php
remove_filter( 'wp_get_attachment_image_attributes', 'WFACP_Common::remove_src_set' );
?>