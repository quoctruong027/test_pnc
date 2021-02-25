<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$cc_empty_class = ( WC()->cart->is_empty() ) ? ' cc-empty' : '';

$cart_total              = floatval( preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_contents_total() ) );
$cc_free_shipping_amount = get_option( 'cc_free_shipping_amount' );
$wc_currency_symbol      = get_woocommerce_currency_symbol();
$total_cart_item_count   = WC()->cart->get_cart_contents_count();
$cc_free_shipping_bar    = true;

$free_shipping_remaining_amount = absint( $cc_free_shipping_amount ) - absint( $cart_total );
$free_shipping_remaining_amount = ! empty( $free_shipping_remaining_amount ) ? $free_shipping_remaining_amount : 0;

// Bar width based off % left
$cc_bar_amount = 100;
if ( ! empty( $cc_free_shipping_amount ) && $cart_total <= $cc_free_shipping_amount ) {
	$cc_bar_amount = ( $cart_total * 100 / $cc_free_shipping_amount );
}

$current_user_id    = get_current_user_id();
$cc_sfl_items_array = get_user_meta( $current_user_id, 'cc_save_for_later_items', true );
if ( ! is_array( $cc_sfl_items_array ) ) {
	$cc_sfl_items_array = array();
}
$cc_sfl_items = array_reverse( array_unique( $cc_sfl_items_array ) );

$cc_shipping_country       = get_option( 'cc_shipping_country' );
$cc_disable_branding       = get_option( 'cc_disable_branding' ); // Get disable branding
$cc_disable_branding_class = ( 'disabled' === $cc_disable_branding ) ? ' cc-no-branding' : '';

$currency_symbol = get_woocommerce_currency_symbol();
$cart_items      = WC()->cart->get_cart();
$cart_items_data = array_reverse( $cart_items );

$cc_bar_active = ( $cart_total >= $cc_free_shipping_amount ) ? ' cc-bar-active' : '';

?>
	<div class="cc-body<?php echo esc_attr( $cc_empty_class ); ?>">

		<?php do_action( 'caddy_display_registration_message' ); ?>

		<?php if ( ! WC()->cart->is_empty() ) { ?>

			<?php if ( ! empty( $cc_free_shipping_amount ) && $cc_free_shipping_bar ) { ?>
				<div class="cc-fs text-left">
					<?php do_action( 'caddy_free_shipping_title_text' ); // Free shipping title html ?>
				</div>
			<?php } ?>
			<div class="cc-row cc-cart-items text-center">
				<?php
				foreach ( $cart_items_data as $cart_item_key => $cart_item ) {
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = $_product->get_id();
					?>
					<div class="cc-cart-product-list">
						<?php
						if ( $_product && $_product->exists() && $cart_item['quantity'] > 0
						     && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key )
						) {
							$product_name  = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
							$product_image = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

							$product_regular_price = get_post_meta( $product_id, '_regular_price', true );
							$product_sale_price    = get_post_meta( $product_id, '_sale_price', true );
							if ( ! empty( $product_sale_price ) ) {
								$percentage = ( ( $product_regular_price - $product_sale_price ) * 100 ) / $product_regular_price;
							}
							$product_price = $_product->get_price_html();
							//$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
							$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
							?>
							<div class="cc-cart-product">
								<?php
								echo sprintf(
									'<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_name="%s">&times;</a>',
									'javascript:void(0);',
									esc_html__( 'Remove this item', 'caddy' ),
									esc_attr( $product_id ),
									esc_attr( $cart_item_key ),
									esc_attr( $product_name )
								);
								?>
								<a href="<?php echo esc_url( $product_permalink ); ?>" class="cc-product-link cc-product-thumb"
								   data-title="<?php echo esc_attr( $product_name ); ?>">
									<?php echo $product_image; ?>
								</a>
								<div class="cc_item_content">
									<div class="cc_item_title">
										<a href="<?php echo esc_url( $product_permalink ); ?>" class="cc-product-link"
										   data-title="<?php echo esc_attr( $product_name ); ?>"><?php echo esc_html( $product_name ); ?></a>
									</div>
									<?php if ( ! empty( $product_price ) ) { ?>
										<div class="cc_item_total_price">
											<div class="price"><?php echo $product_price; ?></div>
											<?php if ( ! empty( $product_sale_price ) ) { ?>
												<div class="cc_saved_amount"><?php echo '(Save ' . round( $percentage ) . '%)'; ?></div>
											<?php } ?>
										</div>
									<?php } ?>
									<div class="cc_item_quantity_wrap">
										<div class="cc_item_quantity_update cc_item_quantity_minus" data-type="minus">-</div>
										<input type="text" readonly class="cc_item_quantity" data-key="<?php echo esc_attr( $cart_item_key ); ?>"
										       value="<?php echo $cart_item['quantity']; ?>">
										<div class="cc_item_quantity_update cc_item_quantity_plus" data-type="plus">+</div>
									</div>
									<?php if ( is_user_logged_in() ) { ?>
										<div class="cc_sfl_btn">
											<?php
											echo sprintf(
												'<a href="%s" class="button save_for_later_btn" aria-label="%s" data-product_id="%s" data-cart_item_key="%s">%s</a>',
												'javascript:void(0);',
												esc_html__( 'Save for later', 'caddy' ),
												esc_attr( $product_id ),
												esc_attr( $cart_item_key ),
												esc_html__( 'Save for later', 'caddy' )
											);
											?>
											<div class="cc-loader" style="display: none;"></div>
										</div>
									<?php } ?>
								</div>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
			<?php
			if ( wc_coupons_enabled() ) {
				$applied_coupons = WC()->cart->get_applied_coupons();
				?>
				<div class="cc-coupon">
					<div class="woocommerce-notices-wrapper"><?php wc_print_notices(); ?></div>
					<?php
					// Coupon form will only display when there is no coupon code applied.
					if ( empty( $applied_coupons ) ) {
						?>
						<div class="cc-coupon-title"><?php esc_html_e( 'Apply a promo code:', 'caddy' ); ?></div>
					<?php } ?>
					<div class="cc-coupon-form">
						<?php
						// Coupon form will only display when there is no coupon code applied.
						if ( empty( $applied_coupons ) ) {
							?>
							<div class="coupon">
								<form name="apply_coupon_form" id="apply_coupon_form" method="post">
									<input type="text" name="cc_coupon_code" id="cc_coupon_code" placeholder="<?php echo esc_html__( 'Promo code', 'caddy' ); ?>" />
									<input type="submit" class="cc-coupon-btn" name="cc_apply_coupon" value="<?php echo esc_html__( 'Apply', 'caddy' ); ?>">
								</form>
							</div>
						<?php } ?>

						<?php
						// Check if there is any coupon code is applied.
						if ( ! empty( $applied_coupons ) ) {
							foreach ( $applied_coupons as $code ) {
								$coupon_detail   = new WC_Coupon( $code );
								$coupon_data     = $coupon_detail->get_data();
								$discount_amount = $coupon_data['amount'];
								$discount_type   = $coupon_data['discount_type'];

								if ( 'percent' == $discount_type ) {
									$coupon_amount_text = $discount_amount . '%';
								} else {
									$coupon_amount_text = $currency_symbol . $discount_amount;
								}
								?>
								<div class="cc-applied-coupon">
									<span class="cc_applied_code"><?php echo esc_html( $code ); ?></span><?php echo esc_html( __( ' promo code ( ', 'caddy' ) . $coupon_amount_text . __( ' off ) applied.', 'caddy' ) ); ?>
									<a href="javascript:void(0);" class="cc-remove-coupon"><?php esc_html_e( 'Remove', 'caddy' ); ?></a>
								</div>
								<?php
							}
						} ?>

					</div>
				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="cc-empty-msg">
				<i class="ccicon-cart-empty"></i>
				<span class="cc-title"><?php esc_html_e( 'Your Cart is empty!', 'caddy' ); ?></span>

				<?php if ( ! empty( $cc_sfl_items ) ) { ?>
					<p><?php esc_html_e( 'You haven\'t added any items to your cart yet, but you do have products in your saved list.', 'caddy' ); ?></p>
					<a href="javascript:void(0);" class="cc-button cc-view-saved-items"><?php esc_html_e( 'View Saved Items', 'caddy' ); ?></a>
				<?php } else { ?>
					<p><?php esc_html_e( 'It looks like you haven\'t added any items to your cart yet.', 'caddy' ); ?></p>
					<a href="<?php echo get_permalink( wc_get_page_id( 'shop' ) ); ?>" class="cc-button"><?php esc_html_e( 'Browse Products', 'caddy' ); ?></a>
				<?php } ?>
			</div>
		<?php } ?>

	</div>

<?php if ( ! WC()->cart->is_empty() ) { ?>
	<div class="cc-cart-actions<?php echo $cc_disable_branding_class; ?>">
		<div class="cc-totals">
			<?php if ( $total_cart_item_count > 1 ) { ?>
				<span class="cc-total-text"><?php echo esc_html__( 'Estimated Total - ', 'caddy' ) . $total_cart_item_count . esc_html__( ' items', 'caddy' ); ?></span>
			<?php } else { ?>
				<span class="cc-total-text"><?php echo esc_html__( 'Estimated Total - ', 'caddy' ) . $total_cart_item_count . esc_html__( ' item', 'caddy' ); ?></span>
			<?php } ?>
			<span class="cc-total-amount"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
		</div>
		<div class="cc-ship-tax-notice"><?php esc_html_e( '*Shipping &amp; taxes calculated at checkout.', 'caddy' ); ?></div>
		<a href="<?php echo wc_get_checkout_url(); ?>" class="cc-button cc-button-primary"><?php esc_html_e( 'Checkout Now', 'caddy' ); ?></a>
	</div>
<?php } ?>
	<input type="hidden" name="cc-compass-count-after-remove" class="cc-cart-count-after-product-remove" value="<?php echo $total_cart_item_count; ?>">
<?php if ( 'disabled' !== $cc_disable_branding ) { ?>
	<div class="cc-poweredby text-center">
		<?php
		echo sprintf(
			'%1$s <img src="%2$s" alt="Voltage Emoji"> %3$s <a href="%4$s" target="_blank">%5$s</a>',
			__( 'Powered', 'caddy' ),
			plugin_dir_url( __DIR__ ) . 'img/voltage-emoji.png',
			__( 'by', 'caddy' ),
			esc_url( 'https://www.usecaddy.com' ),
			__( 'Caddy', 'caddy' )
		);
		?>
	</div>
	<?php
}
