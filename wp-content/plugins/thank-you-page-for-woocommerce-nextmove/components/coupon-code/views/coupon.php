<?php
defined( 'ABSPATH' ) || exit;

if ( empty( $coupon_code ) ) {
	return '';
}
do_action( 'xlwcty_dynamic_coupon_generated', $or_id, $coupon_code );

echo $this->data->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->heading, $this ) . '</div>' : '';
?>
<div class="xlwcty_content xlwcty_clearfix xlwcty_main_coupons">
	<?php
	$desc_class = '';
	if ( ! empty( $this->data->desc_alignment ) ) {
		$desc_class = ' class="xlwcty_' . $this->data->desc_alignment . '"';
	}
	echo $this->data->desc_after ? '<div' . $desc_class . '>' . apply_filters( 'xlwcty_the_content', $this->data->desc_after, $this ) . '</div><div class="xlwcty_clear_15"></div>' : '';
	?>
    <div class="xlwcty_coupon_code"><?php echo $coupon_code; ?></div>
	<?php
	if ( $this->data->btn_link_after != '' ) {
		?>
        <p class="xlwcty_center">
            <a href="<?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->btn_link_after ); ?>"
               class="xlwcty_btn xlwcty_generate_new_coupons"><?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->btn_txt_after, $this ); ?></a>
        </p>
		<?php
	}
	?>
</div>
