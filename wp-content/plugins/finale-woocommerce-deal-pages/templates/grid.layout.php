<ul class="wcct_custom_pro_grid wcct_custom_pro_grid_<?php echo $template_grid_cols; ?>">
	<?php
	do_action( 'wcct_deal_pages_before_loop', $parsed_meta );
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
		$you_save_raw            = wcct_get_product_savings( $product, $yousave_value );

		$product_link = apply_filters( 'wcct_deal_page_template_external_product_link', $product->get_permalink(), $product );

		?>
        <li class="wcct_pro_col">
            <a href="<?php echo $product_link; ?>" class="wcct_pro_link">
                <div class="wcct_pro_imgBox">
					<?php
					echo $this->get_thumbnail( $product->get_id() );
					if ( 'on' !== $hide_sale_badge ) {
						if ( $product->is_on_sale() ) {
							?>
                            <span class="wcct_pro_sale"><?php echo $this->_wcct_deal_sale_badge_text; ?></span>
							<?php
						}
					}
					?>
                </div>
                <h2 class="wcct_pro_title"><?php echo $product->get_title(); ?></h2>
            </a>
			<?php
			if ( 'on' !== $hide_rating ) {
				$average = $product->get_average_rating();
				if ( $average > 0 ) {
					?>
                    <div class="wcct_pro_rating_wrap">
						<span class="wcct_pro_star_rating">
							<span class="wcct_pro_fill" style="width:<?php echo( ( $average / 5 ) * 100 ); ?>%"></span>
						</span>
                    </div>
					<?php
				}
			}
			?>
            <div class="wcct_pro_price_wrap <?php echo ( false !== $you_save_raw ) ? 'wcct_price_left_right' : ''; ?>">
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
			<?php
			if ( $product->is_in_stock() ) {
				if ( 1 === intval( $is_show_bar ) ) {
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
				if ( 1 === intval( $is_show_timer ) ) {
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
                <div class="wcct_pro_cart_btn">
					<?php
					if ( isset( $this->_wcct_deal_pages_add_to_cart_exclude ) && is_array( $this->_wcct_deal_pages_add_to_cart_exclude ) && in_array( $product->get_type(), $this->_wcct_deal_pages_add_to_cart_exclude, true ) ) {
						$add_to_cart_text_single = $product->add_to_cart_text();
					}
					echo apply_filters( 'woocommerce_loop_add_to_cart_link', sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>', esc_url( $product->add_to_cart_url() ), esc_attr( isset( $quantity ) ? $quantity : 1 ), esc_attr( $product->get_id() ), esc_attr( $product->get_sku() ), esc_attr( isset( $class ) ? $class : 'wcct_pro_add_to_cart' ), $add_to_cart_text_single ), $product, [] );
					?>
                </div>
				<?php
			} else {
				echo '<div class="wcct_pro_cart_btn">';
				$availability = $product->get_availability();
				echo $availability['availability'];
				echo '</div>';
			}
			?>
        </li>
		<?php
		unset( $add_to_cart_text_single );
	}
	do_action( 'wcct_deal_pages_after_loop', $parsed_meta );
	wp_reset_query();
	?>
</ul>
<?php
wcct_maybe_show_pagination( $r, $atts );
