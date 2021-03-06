<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_array( $products ) ) : ?>

    <style>
        /** don't inline this css - hack for gmail */
        .bwfan-product-rows {
            width: 100%;
        }

        .bwfan-product-rows img {
            max-width: 75px;
        }
    </style>

    <table cellspacing="0" cellpadding="0" style="width: 100%;" class="bwfan-product-rows">
        <tbody>
		<?php
		$disable_product_link      = BWFAN_Common::disable_product_link();
		$disable_product_thumbnail = BWFAN_Common::disable_product_thumbnail();
		if ( false !== $cart ) {
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
			foreach ( $cart as $item ) :
				$product = wc_get_product( $item['data']->get_id() );
				if ( ! $product ) {
					continue; // don't show items if there is no product
				}
				$line_total = $item['line_subtotal'];
				?>
                <tr>
					<?php
					if ( false === $disable_product_thumbnail ) {
						?>
                        <td class="image" width="100">
							<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                        </td>
						<?php
					} ?>
                    <td width="">
                        <h4><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
                    </td>
                    <td align="right" class="last" width="">
						<?php echo wp_kses_post( BWFAN_Common::price( $line_total ) ); ?>
                    </td>
                </tr>

			<?php endforeach;
		} else {
			foreach ( $products as $product ) {
				?>
                <tr>
					<?php
					if ( true === $disable_product_link ) {
						if ( false === $disable_product_thumbnail ) {
							?>
                            <td class="image" width="100">
								<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                            </td>
							<?php
						} ?>
                        <td width="">
                            <h4><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
                        </td>
						<?php
					} else {
						if ( false === $disable_product_thumbnail ) {
							?>
                            <td class="image" width="100">
                                <a href="<?php echo esc_url_raw( $product->get_permalink() ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo wp_kses_post( BWFAN_Common::get_product_image( $product ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?></a>
                            </td>
							<?php
						}
						?>
                        <td width="">
                            <h4>
                                <a href="<?php echo esc_url_raw( $product->get_permalink() ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></a>
                            </h4>
                        </td>
						<?php
					}
					?>
                    <td align="right" class="last" width="">
                        <p class="price" style="margin: 18px 0 8px;"><?php echo wp_kses_post( $product->get_price_html() ); //phpcs:ignore WordPress.Security.EscapeOutput ?></p>
                    </td>
                </tr>
				<?php
			}
		}
		?>
        </tbody>
    </table>

<?php endif;
