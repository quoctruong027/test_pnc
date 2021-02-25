<?php
defined( 'ABSPATH' ) || exit;

$index            = $this->current_index;
$source_left      = ! empty( $this->data->{$index}->img_l_source ) ? $this->data->{$index}->img_l_source : '';
$source_right     = ! empty( $this->data->{$index}->img_r_source ) ? $this->data->{$index}->img_r_source : '';
$left_image_link  = ! empty( $this->data->{$index}->img_l_link ) ? $this->data->{$index}->img_l_link : 'javascript:void(0)';
$right_image_link = ! empty( $this->data->{$index}->img_r_link ) ? $this->data->{$index}->img_r_link : 'javascript:void(0)';

if ( $source_left == '' && $source_right == '' ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s %s', $this->get_component_property( 'title' ), $index, __( 'Data not set', 'thank-you-page-for-woocommerce-nextmove' ) ) );

	return '';
}
XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s %s', $this->get_component_property( 'title' ), $index, __( 'On', 'thank-you-page-for-woocommerce-nextmove' ) ) );
?>
<div class="xlwcty_Box xlwcty_imgBox <?php echo "xlwcty_imgBox_{$index}"; ?>">
    <div class="xlwcty_title"><?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->{$index}->heading ); ?></div>
	<?php
	$desc_class = '';
	if ( ! empty( $this->data->{$index}->desc_alignment ) ) {
		$desc_class = ' class="xlwcty_' . $this->data->{$index}->desc_alignment . '"';
	}
	echo $this->data->{$index}->desc ? '<div' . $desc_class . '>' . apply_filters( 'xlwcty_the_content', $this->data->{$index}->desc ) . '</div>' : '';
	?>
    <div class="xlwcty_imgBox_w xlwcty_imgBox_2cw xlwcty_clearfix">
		<?php
		if ( $source_left != '' ) {
			?>
            <div class="xlwcty_content xlwcty_center xlwcty_50" data-style="left">
				<?php
				echo sprintf( "<a href='%s' class='xlwcty_content_block_image_link'><img src='%s' class='xlwcty_content_block_image'/></a>", $left_image_link, $source_left );
				?>
            </div>
			<?php
		}
		if ( $source_right != '' ) {
			?>
            <div class="xlwcty_content xlwcty_center xlwcty_50" data-style="right">
				<?php
				echo sprintf( "<a href='%s' class='xlwcty_content_block_image_link'><img src='%s' class='xlwcty_content_block_image'/></a>", $right_image_link, $source_right );
				?>
            </div>
			<?php
		}
		?>
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
