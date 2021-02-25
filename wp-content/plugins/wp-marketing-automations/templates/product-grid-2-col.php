<?php
$n = 1;

if ( is_array( $products ) ) :
	?>

    <style>
        /** don't inline this css - hack for gmail */
        .bwfan-product-grid {
            width: 100%;
        }

        .bwfan-product-grid .bwfan-product-grid-item-2-col img {
            height: auto !important;
        }

        .bwfan-product-grid-item-2-col {
            width: 46%;
            display: inline-block;
            text-align: left;
            padding: 0 0 20px;
            vertical-align: top;
            word-wrap: break-word;
            margin-right: 6%;
            font-size: 14px;
        }

        .bwfan-product-grid .bwfan-product-image {
            width: 100%;
        }

        .bwfan-product-grid img {
            width: 100%;
        }
    </style>

    <table cellspacing="0" cellpadding="0" class="bwfan-product-grid">
        <tbody>
        <tr>
            <td style="padding: 0;">
                <div class="bwfan-product-grid-container">
					<?php
					$disable_product_link      = BWFAN_Common::disable_product_link();
					$disable_product_thumbnail = BWFAN_Common::disable_product_thumbnail();

					if ( false !== $cart ) {
						$tax_display = get_option( 'woocommerce_tax_display_cart' );
						foreach ( $cart as $item ) {
							$product = wc_get_product( $item['data']->get_id() );
							if ( ! $product ) {
								continue; // don't show items if there is no product
							}
							$line_total = $item['line_subtotal'];
							?>
                            <div class="bwfan-product-grid-item-2-col bwfan-product-type-cart" style="<?php echo( $n % 2 ? '' : 'margin-right: 0;' ); ?>">
								<?php echo ( false === $disable_product_thumbnail ) ? wp_kses_post( BWFAN_Common::get_product_image( $product ) ) : ''; //phpcs:ignore WordPress.Security.EscapeOutput ?>
                                <h4><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
                                <p class="price"><strong><?php esc_html_e( $line_total ); //phpcs:ignore WordPress.Security.EscapeOutput ?></strong></p>
                            </div>
							<?php
							$n ++;
						}
					} else {
						foreach ( $products as $product ) {
							?>
                            <div class="bwfan-product-grid-item-2-col bwfan-product-type-product" style="<?php echo( $n % 2 ? '' : 'margin-right: 0;' ); ?>">
								<?php
								if ( true === $disable_product_link ) {
									echo ( false === $disable_product_thumbnail ) ? BWFAN_Common::get_product_image( $product ) : ''; //phpcs:ignore WordPress.Security.EscapeOutput ?>
                                    <h4><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
									<?php
								} else {
									if ( false === $disable_product_thumbnail ) {
										?>
                                        <a href="<?php echo esc_url_raw( $product->get_permalink() ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo wp_kses_post( BWFAN_Common::get_product_image( $product ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?></a>
										<?php
									}
									?>
                                    <h4>
                                        <a href="<?php echo esc_url_raw( $product->get_permalink() ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></a>
                                    </h4>
									<?php
								}
								?>
                                <p class="price"><strong><?php echo wp_kses_post( $product->get_price_html() ); //phpcs:ignore WordPress.Security.EscapeOutput ?></strong></p>
                            </div>
							<?php
							$n ++;
						}
					}
					?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
<?php endif;