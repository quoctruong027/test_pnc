<?php
defined( 'ABSPATH' ) || exit;

$cp_data = $this->get_formated_coupon();
if ( empty( $cp_data['coupon_code'] ) ) {
	return '';
}
do_action( 'xlwcty_dynamic_coupon_generated', $or_id, $cp_data['coupon_code'] );
?>
<div class="xlwcty_Box xlwcty_coupon xlwcty_center">
	<?php echo $this->data->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->heading, $this ) . '</div>' : ''; ?>
    <div class="xlwcty_content c xlwcty_clearfix">
		<?php
		$desc_class = '';
		if ( ! empty( $this->data->desc_alignment ) ) {
			$desc_class = ' class="xlwcty_' . $this->data->desc_alignment . '"';
		}
		echo $this->data->desc ? '<div' . $desc_class . '>' . apply_filters( 'xlwcty_the_content', $this->data->desc, $this ) . '</div><div class="xlwcty_clear_15"></div>' : '';
		?>
        <div class="xlwcty_coupon_code"><?php echo $cp_data['coupon_code']; ?></div>
		<?php
		if ( $this->data->btn_txt != '' ) {
			?>
            <p class="xlwcty_center">
                <a href="<?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->btn_link_immediate_after ); ?>"
                   class="xlwcty_btn"><?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->btn_txt, $this ); ?></a>
            </p>
			<?php
		}
		?>
    </div>
</div>
