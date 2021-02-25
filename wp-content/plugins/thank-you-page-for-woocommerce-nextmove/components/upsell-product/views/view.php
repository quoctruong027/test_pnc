<?php
defined( 'ABSPATH' ) || exit;

if ( empty( $this->upsell_product ) ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'Data not set', 'thank-you-page-for-woocommerce-nextmove' ) ) );

	return;
}
XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'On', 'thank-you-page-for-woocommerce-nextmove' ) ) );
$query_args = array(
	'posts_per_page' => ( $this->data->display_count ? $this->data->display_count : 4 ),
	'no_found_rows'  => 1,
	'post_status'    => 'publish',
	'post_type'      => 'product',
	'post__in'       => $this->upsell_product,
	'orderby'        => 'rand',
	'meta_query'     => array(
		array(
			'key'     => '_stock_status',
			'value'   => array( 'instock', 'onbackorder' ),
			'compare' => 'IN',
		),
	),
);
$r          = new WP_Query( $query_args );
if ( $r->have_posts() ) {
	$grid_type = $this->data->grid_type;
	if ( '2c' === $grid_type ) {
		include __DIR__ . '/2c.php';
	} elseif ( '3c' === $grid_type ) {
		include __DIR__ . '/3c.php';
	} elseif ( 'list' === $grid_type ) {
		include __DIR__ . '/list.php';
	} else {
		include __DIR__ . '/default.php';
	}
}
wp_reset_postdata();
