<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$subtotal     = 0;
$subtotal_tax = 0;
$total        = 0;

$disable_product_thumbnail = BWFAN_Common::disable_product_thumbnail();

$colspan      = ' colspan="2"';
$colspan_foot = ' colspan="3"';
if ( true === $disable_product_thumbnail ) {
	$colspan      = '';
	$colspan_foot = ' colspan="2"';
}
?>
<style>
    #template_header {
        width: 100%;
    }

    .bwfan-order-table {
        border: 2px solid #e5e5e5;
        border-collapse: collapse;
    }

    .bwfan-order-table img {
        max-width: 75px;
    }

    .bwfan-order-table tr th, .bwfan-order-table tr td {
        border: 2px solid #e5e5e5;
    }
</style>

<table cellspacing="0" cellpadding="6" border="1" class="bwfan-order-table" width="100%">
    <thead>
    <tr>
        <th class="td" scope="col" <?php echo $colspan ?> style="<?php echo is_rtl() ? 'text-align:right;' : 'text-align:left;'; ?>"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
        <th class="td" scope="col" style="<?php echo is_rtl() ? 'text-align:right;' : 'text-align:left;'; ?>"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
        <th class="td" scope="col" style="<?php echo is_rtl() ? 'text-align:right;' : 'text-align:left;'; ?>"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
    </tr>
    </thead>
    <tbody>

	<?php
	if ( false !== $cart ) {
		$tax_display = get_option( 'woocommerce_tax_display_cart' );
		foreach ( $cart as $item ) :
			$product = wc_get_product( $item['data']->get_id() );
			if ( ! $product ) {
				continue; // don't show items if there is no product
			}

			if ( false === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
				$subtotal     += BWFAN_Common::get_line_subtotal( $item );
				$subtotal_tax += BWFAN_Common::get_line_subtotal_tax( $item );
				$line_total   = ( 'excl' === $tax_display ) ? BWFAN_Common::get_line_subtotal( $item ) : BWFAN_Common::get_line_subtotal( $item ) + BWFAN_Common::get_line_subtotal_tax( $item );
				$total        += $line_total;
			} else {
				$line_total = $product->get_price();
			}
			?>
            <tr>
				<?php
				if ( false === $disable_product_thumbnail ) {
					?>
                    <td class="image" width="100">
						<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product, 'thumbnail' ) ); ?>
                    </td>
					<?php
				}
				?>
                <td>
                    <h4>
						<?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?>
                    </h4>
                </td>
                <td>
					<?php
					if ( false === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
						esc_html_e( BWFAN_Common::get_quantity( $item ) );
					} else {
						esc_html_e( 1 );
					}
					?>
                </td>
                <td>
					<?php echo wp_kses_post( BWFAN_Common::price( $line_total ) ); ?>
                </td>
            </tr>

		<?php
		endforeach;
	} else {
		foreach ( $products as $product ) {
			?>
            <tr>
				<?php
				if ( false === $disable_product_thumbnail ) {
					?>
                    <td width="100">
						<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product, 'thumbnail' ) ); ?>
                    </td>
					<?php
				}
				?>
                <td>
					<?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?>
                </td>
                <td>1</td>
                <td><?php echo wp_kses_post( BWFAN_Common::price( 12 ) ); ?></td>
            </tr>
			<?php
		}
	}
	?>
    </tbody>
    <tfoot>
	<?php if ( is_array( $data ) && isset( $data['shipping_total'] ) && ! empty( $data['shipping_total'] ) && '0.00' !== $data['shipping_total'] ): ?>
        <tr>
            <th scope="row" <?php echo $colspan_foot ?> style="<?php echo is_rtl() ? 'text-align:right' : 'text-align:left'; ?>"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?>
				<?php if ( wc_tax_enabled() && $tax_display !== 'excl' ): ?>
                    <small><?php echo wp_kses_post( sprintf( __( '(includes %s tax)', 'woocommerce' ), BWFAN_Common::price( esc_attr( $data['shipping_tax_total'] ) ) ) ) ?></small>
				<?php endif; ?>
            </th>
            <td><?php echo BWFAN_Common::price( esc_attr( $data['shipping_total'] ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?></td>
        </tr>
	<?php endif; ?>

	<?php if ( is_array( $data ) && isset( $data['coupons'] ) && ! empty( $data['coupons'] ) ): ?>
        <tr>
			<?php
			$discount     = 0;
			$coupon_names = array();
			foreach ( $data['coupons'] as $coupon_name => $coupon ) {
				$discount       += $coupon['discount_incl_tax'];
				$coupon_names[] = $coupon_name;
			}
			$coupon_names = implode( ', ', $coupon_names );
			$coupon_names = apply_filters( 'bwfan_modify_coupon_names', $coupon_names, $data['coupons'] );
			?>
            <th scope="row" <?php echo $colspan_foot ?> style="<?php echo is_rtl() ? 'text-align:right' : 'text-align:left'; ?>">
				<?php esc_html_e( 'Discount:', 'woocommerce' ); ?>
				<?php if ( ! empty( $coupon_names ) ) { ?>
                    <small><?php echo wp_kses_post( $coupon_names ) ?></small>
				<?php } ?>
            </th>
            <td><?php echo '-' . BWFAN_Common::price( esc_attr( $discount ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?></td>
        </tr>
	<?php endif; ?>

	<?php if ( wc_tax_enabled() && $tax_display === 'excl' && $subtotal_tax ): ?>
        <tr>
            <th scope="row" <?php echo $colspan_foot ?> style="<?php echo is_rtl() ? 'text-align:right' : 'text-align:left'; ?>"><?php esc_html_e( 'Tax', 'woocommerce' ); ?></th>
            <td><?php echo wp_kses_post( BWFAN_Common::price( $subtotal_tax ) ); ?></td>
        </tr>
	<?php endif; ?>

    <tr>
        <th scope="row" <?php echo $colspan_foot ?> style="<?php echo is_rtl() ? "text-align:right;" : "text-align:-internal-center;" ?>">
			<?php esc_html_e( 'Total', 'woocommerce' ); ?>
			<?php if ( wc_tax_enabled() && $tax_display !== 'excl' ): ?>
                <small><?php echo wp_kses_post( sprintf( __( '(includes %s tax)', 'woocommerce' ), BWFAN_Common::price( esc_attr( $subtotal_tax ) ) ) ) ?></small>
			<?php endif; ?>
        </th>
        <td><?php echo wp_kses_post( BWFAN_Common::price( $data['total'] ) ); ?></td>
    </tr>
    </tfoot>

</table>
