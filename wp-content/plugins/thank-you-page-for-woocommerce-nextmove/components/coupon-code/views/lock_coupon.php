<?php
defined( 'ABSPATH' ) || exit;

if ( empty( $this->data->fadeCouponText ) ) {
	return '';
}
?>
<div class="xlwcty_Box xlwcty_coupon">
    <div style="display: none" class="xlwcty_component_load"><i class="xlwcty-fa xlwcty-fa-spinner xlwcty-fa-pulse xlwcty-fa-2x xlwcty-fa-fw"></i></div>
	<?php echo $this->data->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->heading, $this ) . '</div>' : ''; ?>
    <div class="xlwcty_content c xlwcty_clearfix xlwcty_main_coupons xlwcty_center">
		<?php
		$desc_class = '';
		if ( ! empty( $this->data->desc_alignment ) ) {
			$desc_class = ' class="xlwcty_' . $this->data->desc_alignment . '"';
		}
		echo $this->data->desc ? '<div' . $desc_class . '>' . apply_filters( 'xlwcty_the_content', $this->data->desc, $this ) . '</div><div class="xlwcty_clear_15"></div>' : '';
		?>
        <div class="xlwcty_show_hide_coupon">
            <div class="xlwcty_coupon_area">
                <div class="xlwcty_coupon_inner">
                    <div class="xlwcty_overlay"></div>
                    <div class="xlwcty_sc_icon xlwcty_lock"><i class="fa fa-lock"></i></div>
                    <!--<div class="xlwcty_cou_text"><?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->fadeCouponText ); ?></div>-->
                    <div class="xlwcty_cou_text">
						<?php
						if ( $this->data->personalize == 'yes' && $this->data->format != '' ) {
							echo sanitize_title( XLWCTY_Common::maype_parse_merge_tags( $this->data->fadeCouponText ) );
						} else {
							echo get_the_title( $this->data->selected_coupon );
						}
						?>
                    </div>
                    <div class="xlwcty_sc_icon xlwcty_r_icon xlwcty_lock"><i class="fa fa-lock"></i></div>
                </div>
            </div>
        </div>
		<?php
		if ( $this->data->btn_txt != '' ) {
			?>
            <p class="xlwcty_center">
                <a href="javascript:void(0);" class="xlwcty_btn xlwcty_generate_new_coupons"><?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->btn_txt, $this ); ?></a></p>
			<?php
		}
		?>
    </div>
</div>
