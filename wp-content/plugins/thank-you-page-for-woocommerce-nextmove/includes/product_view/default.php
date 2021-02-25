<?php
defined( 'ABSPATH' ) || exit;

echo $this->data->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->heading ) . '</div>' : '';
$desc_class = 'xlwcty_desc_div';
if ( ! empty( $this->data->desc_alignment ) ) {
	$desc_class .= ' xlwcty_' . $this->data->desc_alignment;
}
echo $this->data->desc ? '<div class="' . $desc_class . '">' . apply_filters( 'xlwcty_the_content', $this->data->desc ) . '</div>' : '';
?>
<div class="woocommerce xlwcty_clearfix">
	<?php
	woocommerce_product_loop_start();
	while ( $r->have_posts() ) {
		$r->the_post();
		global $product;
		$product = wc_get_product( get_the_ID() );

		wc_get_template_part( 'content', 'product' );
		unset( $product );
	}
	woocommerce_product_loop_end();
	?>
</div>
