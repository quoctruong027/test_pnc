<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;
if ( $price_html = $product->get_price_html() ) : ?>
	<div class="woopb-product-price"><?php echo $price_html; ?></div>
<?php endif;
