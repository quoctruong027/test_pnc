<ul class="wcct_custom_pro_grid wcct_pro_row">
	<?php
	do_action( 'wcct_deal_pages_before_loop' );
	while ( $r->have_posts() ) {
		$r->the_post();
		global $product;

		$passed = false;

		/** Validating product against a campaign, if valid then show otherwise continue */
		add_action( 'wcct_before_apply_rules', array( $this, 'deal_products_exclude_rules_add' ), 9999, 2 );
		add_action( 'wcct_after_apply_rules', array( $this, 'deal_products_exclude_rules_remove' ), 9999, 2 );
		foreach ( $this->passed_campaigns as $camp_id ) {
			$rule_result = WCCT_Common::match_groups( $camp_id, get_the_ID() );
			if ( true === $rule_result ) {
				$passed = true;
				break;
			}
		}
		remove_action( 'wcct_before_apply_rules', array( $this, 'deal_products_exclude_rules_add' ), 9999, 2 );
		remove_action( 'wcct_after_apply_rules', array( $this, 'deal_products_exclude_rules_remove' ), 9999, 2 );

		if ( false === $passed ) {
			continue;
		}

		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( get_the_ID() );
		}
		$add_to_cart_text_single = ! empty( $add_to_cart_text ) ? $add_to_cart_text : esc_html( $product->add_to_cart_text() );

		$you_save_raw = wcct_get_product_savings( $product, $yousave_value );
		$product_link = apply_filters( 'wcct_deal_page_template_external_product_link', $product->get_permalink(), $product );
		$is_in_stock  = $product->is_in_stock();
		?>
        <li class="wcct_pro_col wcct_single_row">
            <div class="wcct_pro_col_left">
                <a href="<?php echo $product_link; ?>" class="wcct_pro_link">
                    <div class="wcct_pro_imgBox">
						<?php
						echo $this->get_thumbnail( $product->get_id() );
						?>
						<?php
						if ( 'on' !== $hide_sale_badge ) {
							if ( $product->is_on_sale() ) {
								?>
                                <span class="wcct_pro_sale"><?php echo $this->_wcct_deal_sale_badge_text; ?></span>
								<?php
							}
						}
						?>
                    </div>
                </a>
            </div>
            <div class="wcct_pro_col_right">
                <div class="wcct_pro_table">
                    <div class="wcct_pro_tableCell wcct_contentBox">
                        <div class="wcct_innerBox">
                            <h2 class="wcct_pro_title">
                                <a href="<?php echo $product_link; ?>"><?php echo $product->get_title(); ?></a>
                            </h2>
							<?php
							if ( 'on' !== $hide_rating ) {
								$average = $product->get_average_rating();
								if ( $average > 0 ) {
									?>
                                    <div class="wcct_pro_rating_wrap">
										<span class="wcct_pro_star_rating">
											<span class="wcct_pro_fill"
                                                  style="width:<?php echo( ( $average / 5 ) * 100 ); ?>%"></span>
										</span>
                                    </div>
									<?php
								}
							}
							?>
                            <div class="wcct_hide_tab">
								<?php
								if ( 1 === intval( $is_show_bar ) && $is_in_stock ) {
									?>
                                    <div class="wcct_pro_batch_bar">
										<?php do_action( 'wcct_batch_bar_woocommerce_after_shop_loop_item', $product, $campaign_id, $parsed_meta ); ?>
                                    </div>
									<?php
								}
								?>
                            </div>
							<?php
							if ( 'on' !== $hide_desc ) {
								$desc = strip_shortcodes( get_the_excerpt() );
								if ( '' !== $desc ) {
									?>
                                    <div class="wcct_text">
										<?php
										$content_without_line_breaks = preg_replace( '/(^|[^\n\r])[\r\n](?![\n\r])/', '$1 ', $desc );
										$content_without_line_breaks = strip_tags( $content_without_line_breaks );

										if ( strlen( $content_without_line_breaks ) > $this->_wcct_deal_product_desc_length ) {
											$content_without_line_breaks = substr( $content_without_line_breaks, 0, $this->_wcct_deal_product_desc_length ) . '....';
										}
										?>
										<?php echo $content_without_line_breaks; ?>
                                    </div>
									<?php
								}
							}
							?>
                        </div>
                    </div>
                    <div class="wcct_pro_tableCell wcct_sidebar">
                        <div class="wcct_sidebarArea">
                            <div class="wcct_tab_col2">
                                <div class="wcct_table_50">
                                    <div class="wcct_tableCell_50 wcct_cell_left">
                                        <div class="wcct_hide_desktop">
											<?php
											if ( 1 === intval( $is_show_bar ) && $is_in_stock ) {
												ob_start();
												do_action( 'wcct_batch_bar_woocommerce_after_shop_loop_item', $product, $campaign_id, $parsed_meta );
												$bar_output = ob_get_clean();
												if ( ! empty( $bar_output ) ) {
													?>
                                                    <div class="wcct_pro_batch_bar">
														<?php echo $bar_output; ?>
                                                    </div>
													<?php
												}
											}
											?>
                                        </div>
                                        <div class="wcct_pro_batch_time">
											<?php
											if ( 1 === intval( $is_show_timer ) && $is_in_stock ) {
												ob_start();
												do_action( 'wcct_batch_timer_woocommerce_after_shop_loop_item', $product, $campaign_id, $parsed_meta );
												$timer_output = ob_get_clean();
												if ( ! empty( $timer_output ) ) {
													?>
                                                    <div class="wcct_pro_batch_time">
                                                        <span class="wcct_pro_time_text"><?php echo nl2br( $deal_timer_text_before ); ?> </span>
														<?php echo $timer_output; ?>
                                                        <span class="wcct_pro_time_text"><?php echo nl2br( $deal_timer_text_after ); ?> </span>
                                                    </div>
													<?php
												}
											}
											?>
                                        </div>
                                    </div>
                                    <div class="wcct_tableCell_50 wcct_cell_right">
                                        <div class="wcct_pro_price_wrap">
											<span class="wcct_pro_price">
												<?php
												echo $product->get_price_html();
												?>
											</span>
											<?php if ( false !== $you_save_raw ) { ?>
                                                <span class="wcct_pro_save">
											   <span class="wcct_save_text"><?php printf( '%s', nl2br( $yousave_text ) ); ?></span>
											   <span class="wcct_save_percent"><?php echo $you_save_raw; ?></span>
											</span>
											<?php } ?>
                                        </div>
                                        <div class="wcct_pro_cart_btn">
											<?php
											if ( $is_in_stock ) {
												if ( isset( $this->_wcct_deal_pages_add_to_cart_exclude ) && is_array( $this->_wcct_deal_pages_add_to_cart_exclude ) && in_array( $product->get_type(), $this->_wcct_deal_pages_add_to_cart_exclude ) ) {
													$add_to_cart_text_single = $product->add_to_cart_text();
												}
												echo apply_filters( 'woocommerce_loop_add_to_cart_link', sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>', esc_url( $product->add_to_cart_url() ), esc_attr( isset( $quantity ) ? $quantity : 1 ), esc_attr( $product->get_id() ), esc_attr( $product->get_sku() ), esc_attr( isset( $class ) ? $class : 'wcct_pro_add_to_cart' ), $add_to_cart_text_single ), $product, [] );
											} else {
												$availability = $product->get_availability();
												echo $availability['availability'];
											}
											?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
		<?php
		unset( $add_to_cart_text_single );
	}
	do_action( 'wcct_deal_pages_after_loop' );
	wp_reset_query();
	?>
</ul>
<?php
wcct_maybe_show_pagination( $r, $atts );
