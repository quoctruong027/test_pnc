<?php
defined( 'ABSPATH' ) || exit;

$index           = $this->current_index;
$source          = ( ! empty( $this->data->{$index}->img_source ) ) ? $this->data->{$index}->img_source : '';
$full_image_link = ( $this->data->{$index}->img_link ) ? $this->data->{$index}->img_link : 'javascript:void(0)';
if ( $source != '' ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'On', 'thank-you-page-for-woocommerce-nextmove' ) ) );
	?>
    <div class="xlwcty_Box xlwcty_imgBox <?php echo "xlwcty_imgBox_{$index}"; ?>">
		<?php echo $this->data->{$index}->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->{$index}->heading ) . '</div>' : ''; ?>
        <div class="xlwcty_content">
			<?php
			$desc_class = '';
			if ( ! empty( $this->data->{$index}->desc_alignment ) ) {
				$desc_class = ' class="xlwcty_' . $this->data->{$index}->desc_alignment . '"';
			}
			echo $this->data->{$index}->desc ? '<div' . $desc_class . '>' . apply_filters( 'xlwcty_the_content', $this->data->{$index}->desc ) . '</div>' : '';
			?>
            <div class="xlwcty_imgBox_w xlwcty_clearfix">
				<?php echo sprintf( "<p class='xlwcty_center'><a href='%s' class='xlwcty_content_block_image_link'><img src='%s' class='xlwcty_content_block_image'/></a></p>", $full_image_link, $source ); ?>

            </div>
			<?php
			if ( $this->data->{$index}->show_btn == 'yes' && $this->data->{$index}->btn_text != '' ) {
				$btn_link = ! empty( $this->data->{$index}->btn_link ) != '' ? $this->data->{$index}->btn_link : 'javascript:void(0)';
				?>
                <div class="xlwcty_clear_20"></div>
                <div class="xlwcty_clearfix xlwcty_center">
                    <a href="<?php echo XLWCTY_Common::maype_parse_merge_tags( $btn_link ); ?>" class="xlwcty_btn">
						<?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->{$index}->btn_text ); ?>
                    </a>
                </div>
				<?php
			}
			?>
        </div>
    </div>
	<?php
} else {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'Data not set', 'thank-you-page-for-woocommerce-nextmove' ) ) );
}
