<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$current_user        = wp_get_current_user();
$display_name        = ! empty( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;
$cart_contents_count = WC()->cart->get_cart_contents_count();
$shop_page_url       = get_permalink( wc_get_page_id( 'shop' ) );
?>
<div class="cc-header text-left">
	<i class="ccicon-x"></i>
	<div class="cc-inner-container">
		<div class="cc-nav">
			<?php do_action( 'caddy_nav_tabs' ); ?>
		</div>

	</div>
</div>

<!-- Cart Screen -->
<div id="cc-cart" class="cc-cart cc-screen-tab">
	<?php Caddy_Public::cc_cart_screen(); ?>
</div>

<!-- Save for later screen -->

<?php if ( is_user_logged_in() ) { ?>
	<div id="cc-saves" class="cc-saves cc-screen-tab">
		<?php Caddy_Public::cc_sfl_screen(); ?>
	</div>
<?php } ?>

<?php do_action( 'caddy_after_screen_tabs' ); ?>
