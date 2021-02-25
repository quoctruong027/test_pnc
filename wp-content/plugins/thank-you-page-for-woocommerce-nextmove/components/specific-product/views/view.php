<?php
defined( 'ABSPATH' ) || exit;

if ( empty( $this->data->products ) ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'Data not set', 'thank-you-page-for-woocommerce-nextmove' ) ) );

	return;
}
XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'On', 'thank-you-page-for-woocommerce-nextmove' ) ) );
$query_args = array(
	'post_status'    => 'publish',
	'post_type'      => 'product',
	'posts_per_page' => count( $this->data->products ),
	'post__in'       => $this->data->products,
	'orderby'        => 'rand',
);
$r          = new WP_Query( $query_args );
$output     = '';
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
$content = ob_get_clean();
echo $output . $content;
