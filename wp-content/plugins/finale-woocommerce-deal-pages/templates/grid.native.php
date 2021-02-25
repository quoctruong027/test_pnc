<?php woocommerce_product_loop_start(); ?>
<?php
do_action( 'wcct_deal_pages_before_loop' );
$core = Finale_deal_batch_processing::instance();
add_filter( 'woocommerce_product_add_to_cart_text', array( $core, 'change_add_to_cart_text_for_native' ), 10, 2 );

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
	$add_to_cart_text = ! empty( $add_to_cart_text ) ? $add_to_cart_text : esc_html( $product->add_to_cart_text() );
	wc_get_template_part( 'content', 'product' );
}
do_action( 'wcct_deal_pages_after_loop' );
woocommerce_product_loop_end();

wp_reset_query();
wcct_maybe_show_pagination( $r, $atts );
