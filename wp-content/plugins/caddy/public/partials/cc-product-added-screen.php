<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! empty( $product_id ) ) {

	$product      = wc_get_product( $product_id );
	$product_name = $product->get_name();

	$orderby = 'rand';
	$order   = 'desc';
	$upsells = wc_products_array_orderby( array_filter( array_map( 'wc_get_product', $product->get_upsell_ids() ), 'wc_products_array_filter_visible' ), $orderby, $order );

	$cart_item_count = WC()->cart->get_cart_contents_count();
	$checkout_url    = wc_get_checkout_url();

	$cart_total              = floatval( preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_contents_total() ) );
	$cc_free_shipping_amount = get_option( 'cc_free_shipping_amount' );
	$wc_currency_symbol      = get_woocommerce_currency_symbol();
	$total_cart_item_count   = WC()->cart->get_cart_contents_count();
	$cc_free_shipping_bar    = true;

	$free_shipping_remaining_amount = absint( $cc_free_shipping_amount ) - absint( $cart_total );
	$free_shipping_remaining_amount = ! empty( $free_shipping_remaining_amount ) ? abs( $free_shipping_remaining_amount ) : 0;

	// Bar width based off % left
	$cc_bar_amount = 100;
	if ( ! empty( $cc_free_shipping_amount ) && $cart_total <= $cc_free_shipping_amount ) {
		$cc_bar_amount = (float) ( $cart_total * 100 / $cc_free_shipping_amount );
	}

	$cc_product_recommendation = get_option( 'cc_product_recommendation' );
	$cc_shipping_country       = get_option( 'cc_shipping_country' );
	$cc_disable_branding       = get_option( 'cc_disable_branding' ); // Get disable branding
	$cc_disable_branding_class = ( 'disabled' === $cc_disable_branding ) ? ' cc-no-branding' : '';

	// GET BEST SELLING PRODUCTS
	$best_seller_args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => 5,
		'ignore_sticky_posts' => 1,
		'meta_key'            => 'total_sales',
		'orderby'             => 'meta_value_num',
		'order'               => 'DESC',
		'fields'              => 'ids',
		'post__not_in'        => array( $product_id ),
	);
	$best_seller_loop = query_posts( $best_seller_args );

	/* Get up-sells products data */
	$final_upsell_products = array();
	if ( ! empty( $upsells ) ) {
		foreach ( $upsells as $upsell ) {
			$final_upsell_products[] = $upsell->get_id();
		}
	} else {

		foreach ( $best_seller_loop as $best_seller_id ) {
			$final_upsell_products[] = $best_seller_id;
		}

	}
	?>
	<div class="cc-pl-info-header">
		<i class="ccicon-x"></i>
		<div class="cc-inner-container">
			<a href="javascript:void(0);" class="cc_back_to_cart"><i class="ccicon-left-arrow"></i><?php esc_html_e( 'View Your Cart', 'caddy' ); ?></a>
		</div>
	</div>
	<div class="cc-pl-info-wrapper">
		<div class="cc-pl-info cc-row">
			<div class="cc-pl-title">
				<strong><i class="ccicon-check"></i><?php echo esc_html( $product_name ); ?></strong><?php esc_html_e( ' has been added to your cart', 'caddy' ); ?><br>
			</div>
		</div>
		<?php if ( ! empty( $cc_free_shipping_amount ) && $cc_free_shipping_bar ) { ?>
			<div class="cc-fs text-left">
				<?php do_action( 'caddy_free_shipping_title_text' ); // Free shipping title html ?>
			</div>
		<?php } ?>
		<?php if ( ! empty( $final_upsell_products ) && 'disabled' !== $cc_product_recommendation ) { ?>
			<div class="cc-pl-upsells">

				<?php do_action( 'caddy_up_sell_message' ); ?>

				<?php do_action( 'caddy_product_upsells_slider', $product_id ); ?>

				<div class="caddy-prev"><i class="ccicon-circle-left" aria-hidden="true"></i></div>
				<div class="caddy-next"><i class="ccicon-circle-right" aria-hidden="true"></i></div>
			</div>
		<?php } ?>
	</div>

	<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				$( '.cc-pl-upsells-slider' ).slick( {
					infinite: true,
					speed: 300,
					slidesToShow: 2,
					slidesToScroll: 2,
					prevArrow: $( '.caddy-prev' ),
					nextArrow: $( '.caddy-next' ),
					responsive: [
						{
							breakpoint: 1024,
							settings: {
								slidesToShow: 2,
								slidesToScroll: 2,
							}
						},
						{
							breakpoint: 600,
							settings: {
								slidesToShow: 2,
								slidesToScroll: 2
							}
						},
						{
							breakpoint: 480,
							settings: {
								slidesToShow: 1,
								slidesToScroll: 1
							}
						}
						// You can unslick at a given breakpoint now by adding:
						// settings: "unslick"
						// instead of a settings object
					]
				} );
			} );
	</script>

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

	<input type="hidden" name="cc-compass-count-after-remove" class="cc-cart-count-after-product-remove" value="<?php echo esc_attr( $total_cart_item_count ); ?>">
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
	<?php } ?>
	<?php
}
