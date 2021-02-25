<?php
defined( 'ABSPATH' ) || exit;

$index = $this->current_index;
if ( '' !== $this->data->{$this->current_index}->html_content || '' !== $this->data->{$this->current_index}->heading ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s %s', $this->get_component_property( 'title' ), $index, __( 'On', 'thank-you-page-for-woocommerce-nextmove' ) ) );
	?>
    <div class="xlwcty_Box xlwcty_textBox <?php echo "xlwcty_textBox_{$index}"; ?>">
		<?php
		echo $this->data->{$index}->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->{$index}->heading ) . '</div>' : '';
		echo $this->data->{$index}->html_content ? '<div class="xlwcty_content">' . apply_filters( 'xlwcty_the_content', $this->data->{$index}->html_content ) . '</div>' : '';
		?>
    </div>
	<?php
} else {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'Data not set', 'thank-you-page-for-woocommerce-nextmove' ) ) );
}
