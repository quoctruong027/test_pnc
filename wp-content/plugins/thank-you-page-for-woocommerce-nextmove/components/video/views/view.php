<?php
defined( 'ABSPATH' ) || exit;

$index = $this->current_index;
if ( '' !== $this->data->{$index}->url || '' !== $this->data->{$index}->embed ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'On', 'thank-you-page-for-woocommerce-nextmove' ) ) );
	?>
    <div class="xlwcty_Box xlwcty_videoBox <?php echo "xlwcty_videoBox_{$index}"; ?>">
		<?php
		echo $this->data->{$index}->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->{$index}->heading ) . '</div>' : '';
		$desc_class = '';
		if ( ! empty( $this->data->{$index}->desc_alignment ) ) {
			$desc_class = ' class="xlwcty_' . $this->data->{$index}->desc_alignment . '"';
		}
		echo $this->data->{$index}->desc ? '<div' . $desc_class . '>' . apply_filters( 'xlwcty_the_content', $this->data->{$index}->desc ) . '</div>' : '';
		?>
        <div class="xlwcty_embed_video">
            <div class="xlwcty_16by9">
				<?php
				if ( 'video_url' === $this->data->{$index}->source ) {
					if ( strpos( $this->data->{$index}->url, 'youtu' ) !== false ) {
						$youtube_id = XLWCTY_Common::get_video_id( $this->data->{$index}->url );
						if ( ! empty( $youtube_id ) ) {
							$youtube_url   = "https://www.youtube.com/embed/{$youtube_id}";
							$autoplay_attr = '';
							if ( strpos( $this->data->{$index}->url, 'autoplay' ) !== false ) {
								$youtube_url   = add_query_arg( array(
									'autoplay' => '1',
								), $youtube_url );
								$autoplay_attr = ' allow="autoplay"';
							}
							if ( strpos( $this->data->{$index}->url, 'showinfo' ) !== false ) {
								$youtube_url = add_query_arg( array(
									'showinfo' => '0',
								), $youtube_url );
							}
							if ( strpos( $this->data->{$index}->url, 'rel' ) !== false ) {
								$youtube_url = add_query_arg( array(
									'rel' => '0',
								), $youtube_url );
							}
							if ( strpos( $this->data->{$index}->url, 'controls' ) !== false ) {
								$youtube_url = add_query_arg( array(
									'controls' => '0',
								), $youtube_url );
							}
							echo sprintf( '<iframe src="%s" width="1020" height="574" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen %s></iframe>', $youtube_url, $autoplay_attr );
						}
					} elseif ( strpos( $this->data->{$index}->url, 'vimeo' ) !== false ) {
						$vimeo_id = XLWCTY_Common::get_video_id( $this->data->{$index}->url, 'vimeo' );
						if ( ! empty( $vimeo_id ) ) {
							$vimeo_url = "https://player.vimeo.com/video/{$vimeo_id}";
							if ( strpos( $this->data->{$index}->url, 'autoplay' ) !== false ) {
								$vimeo_url = add_query_arg( array(
									'autoplay' => '1',
								), $vimeo_url );
							}
							if ( strpos( $this->data->{$index}->url, 'loop' ) !== false ) {
								$vimeo_url = add_query_arg( array(
									'loop' => '1',
								), $vimeo_url );
							}
							echo sprintf( '<iframe src="%s" width="1020" height="574" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>', $vimeo_url );
						}
					} else {
						echo apply_filters( 'the_content', sprintf( '[embed]%s[/embed]', $this->data->{$index}->url ) );
					}
				}
				if ( 'embed' === $this->data->{$index}->source ) {
					echo $this->data->{$index}->embed;
				}
				?>
            </div>
			<?php
			if ( 'yes' === $this->data->{$index}->show_btn && '' !== $this->data->{$index}->btn_text ) {
				$btn_link = ! empty( $this->data->{$index}->btn_link ) !== '' ? $this->data->{$index}->btn_link : 'javascript:void(0)';
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
