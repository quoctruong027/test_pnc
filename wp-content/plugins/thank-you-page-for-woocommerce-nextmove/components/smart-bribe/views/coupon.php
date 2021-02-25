<?php
defined( 'ABSPATH' ) || exit;

if ( ! empty( $coupon_code ) ) {
	do_action( 'xlwcty_smart_coupon_generated', $or_id, $coupon_code );
	?>
    <div class="xlwcty_coupon xlwcty_socialBox">
        <div class="xlwcty_content xlwcty_clearfix">
			<?php echo isset( $this->data->desc_after ) ? apply_filters( 'xlwcty_the_content', $this->data->desc_after, $this ) : ''; ?>
            <div class="xlwcty_coupon_code"><?php echo $coupon_code; ?></div>
        </div>
    </div>
	<?php
}
