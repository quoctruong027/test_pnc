<?php
defined( 'ABSPATH' ) || exit;

echo $this->data->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->heading ) . '</div>' : '';
$desc_class = 'xlwcty_desc_div';
if ( ! empty( $this->data->desc_alignment ) ) {
	$desc_class .= ' xlwcty_' . $this->data->desc_alignment;
}
echo $this->data->desc ? '<div class="' . $desc_class . '">' . apply_filters( 'xlwcty_the_content', $this->data->desc ) . '</div>' : '';
?>
<div class="xlwcty_pro_grid xlwcty_center xlwcty_clearfix">
    <ul class="xlwcty_products">
		<?php
		while ( $r->have_posts() ) {
			$r->the_post();
			$product = wc_get_product( get_the_ID() );
			?>
            <li>
                <div class="xlwcty_pro_inner">
                    <a href="<?php echo get_the_permalink(); ?>">
						<?php
						if ( has_post_thumbnail( $product->get_id() ) ) {
							$thumbNail = get_post_thumbnail_id( $product->get_id() );
							$image     = wp_get_attachment_image_src( $thumbNail, $this->get_thumbnail_size() );
							?>
                            <img src="<?php echo $image[0]; ?>" class="xlwcty_product">
							<?php
						} elseif ( ( $parent_id = wp_get_post_parent_id( $product->get_id() ) ) && has_post_thumbnail( $parent_id ) ) {

							$thumbNail = get_post_thumbnail_id( $parent_id );
							$image     = wp_get_attachment_image_src( $thumbNail, $this->get_thumbnail_size() );
							?>
                            <img src="<?php echo $image[0]; ?>" class="xlwcty_product">
							<?php
						} else {
							echo $this->wc_placeholder_img( $this->get_thumbnail_size() );
						}
						?>
                    </a>
                    <p class="xlwcty_p_title"><a href="<?php echo get_the_permalink(); ?>"><?php echo $product->get_title(); ?></a></p>
					<?php if ( $this->data->display_rating == 'yes' ) { ?>
                        <div class="xlwcty_star_rating">
							<span style="width:<?php echo $product->get_average_rating() * 20; ?>%">Rated <strong
                                        class="xlwcty_rating"><?php echo $product->get_average_rating(); ?></strong> out of 5</span>
                        </div>
						<?Php
					}
					?>
                    <span class="xlwcty_price"><span class="xlwcty_price_amount"><span class="xlwcty_price_currencySymbol"><?php echo $product->get_price_html(); ?></span></span></span>
					<?php woocommerce_show_product_loop_sale_flash(); ?>
                </div>
				<?php
				echo '<div class="xlwcty_clearfix"></div>';
				echo "<div class='xlwcty_add_to_cart_ajax'>";
				$ajax_add_to_cart = '';
				if ( 'simple' === $product->get_type() && ( $product->is_purchasable() && $product->is_in_stock() ) ) {
					$ajax_add_to_cart = 'ajax_add_to_cart';
				}
				echo apply_filters( 'woocommerce_loop_add_to_cart_link', sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s  product_type_%s add_to_cart_button %s xlwcty_add_cart">%s</a>', esc_url( $product->add_to_cart_url() ), esc_attr( isset( $quantity ) ? $quantity : 1 ), esc_attr( $product->get_id() ), esc_attr( $product->get_sku() ), esc_attr( isset( $class ) ? $class : 'button' ), esc_attr( $product->get_type() ), $ajax_add_to_cart, $product->add_to_cart_text() ), $product, [] );
				echo '</div>';
				?>
            </li>
			<?php
		}
		?>
    </ul>
</div>           
