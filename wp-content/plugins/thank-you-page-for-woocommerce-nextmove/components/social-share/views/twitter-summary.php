<?php
defined( 'ABSPATH' ) || exit;

$product = wc_get_product( $maxs[0] );

if ( $this->data->share_link !== 'custom' ) {
	if ( empty( $opengraph['twitter_description'] ) ) {
		$short_desc                       = XLWCTY_Compatibility::get_short_description( $product );
		$opengraph['twitter_description'] = strip_tags( substr( $short_desc, 0, 300 ) );
	}
	if ( empty( $opengraph['twitter_image'] ) ) {
		if ( $product && has_post_thumbnail( $product->get_id() ) ) {
			$thumbNail                  = get_post_thumbnail_id( $product->get_id() );
			$image                      = wp_get_attachment_image_src( $thumbNail, $this->get_thumbnail_size() );
			$opengraph['twitter_image'] = $image[0];
		}
	}
}

?>
<div class="xlwcty_share_pro xlwcty_clearfix">
	<?php
	if ( isset( $opengraph['twitter_image'] ) && $opengraph['twitter_image'] != '' ) {
		?>
        <div class="xlwcty_pro_img"><img src="<?php echo $opengraph['twitter_image']; ?>" alt=""></div>
		<?php
	}
	?>
    <div class="xlwcty_pro_text">
		<?php if ( isset( $opengraph['title'] ) && ! empty( $opengraph['title'] ) ) { ?>
            <p><?php echo $opengraph['title']; ?></p>
		<?php } ?>
		<?php if ( isset( $opengraph['twitter_description'] ) && ! empty( $opengraph['twitter_description'] ) ) { ?>
            <p class="xlwcty_small_p"><?php echo $opengraph['twitter_description']; ?></p>
		<?php } ?>
    </div>
</div>
