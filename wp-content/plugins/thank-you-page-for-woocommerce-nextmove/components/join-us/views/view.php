<?php
defined( 'ABSPATH' ) || exit;

XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), 'On' ) );
?>
    <div class="xlwcty_Box xlwcty_socialBox xlwcty_joinus">
		<?php
		echo $this->data->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->heading ) . '</div>' : '';
		?>
        <div class="xlwcty_content xlwcty_clearfix">
			<?php
			$desc_class = '';
			if ( ! empty( $this->data->desc_alignment ) ) {
				$desc_class = ' class="xlwcty_' . $this->data->desc_alignment . '"';
			}
			echo $this->data->desc ? '<div' . $desc_class . '>' . apply_filters( 'xlwcty_the_content', $this->data->desc ) . '</div>' : '';
			?>
            <div class="xlwcty_social_icon xlwcty_center <?php echo 'circle' === $this->data->icon_style ? 'xlwcty_circle_icon' : ''; ?>">
                <ul>
					<?php
					if ( ! empty( $this->data->fb ) ) {
						?>
                        <li class="xlwcty_facebook">
                            <a href="<?php echo $this->data->fb; ?>" target="_blank" class="<?php echo $this->get_slug() . '_fb'; ?>"><i class="xlwcty-fa xlwcty-fa-facebook"></i></a></li>
						<?php
					}
					if ( ! empty( $this->data->tw ) ) {
						?>
                        <li class="xlwcty_twitter"><a href="<?php echo $this->data->tw; ?>" target="_blank" class="<?php echo $this->get_slug() . '_tw'; ?>"><i class="xlwcty-fa xlwcty-fa-twitter"></i></a>
                        </li>
						<?php
					}
					if ( ! empty( $this->data->pin ) ) {
						?>
                        <li class="xlwcty_pinterest">
                            <a href="<?php echo $this->data->pin; ?>" target="_blank" class="<?php echo $this->get_slug() . '_pin'; ?>"><i class="xlwcty-fa xlwcty-fa-pinterest-p"></i></a></li>
						<?php
					}
					if ( ! empty( $this->data->gp ) ) {
						?>
                        <li class="xlwcty_google_plus">
                            <a href="<?php echo $this->data->gp; ?>" target="_blank" class="<?php echo $this->get_slug() . '_gp'; ?>"><i class="xlwcty-fa xlwcty-fa-google-plus"></i></a></li>
						<?php
					}
					if ( ! empty( $this->data->insta ) ) {
						?>
                        <li class="xlwcty_google_plus">
                            <a href="<?php echo $this->data->insta; ?>" target="_blank" class="<?php echo $this->get_slug() . '_insta'; ?>"><i class="xlwcty-fa xlwcty-fa-instagram"></i></a></li>
						<?php
					}

					if ( ! empty( $this->data->linkedin ) ) {
						?>
                        <li class="xlwcty_linkedin">
                            <a href="<?php echo $this->data->linkedin; ?>" target="_blank" class="<?php echo $this->get_slug() . '_linkedin'; ?>"><i class="xlwcty-fa xlwcty-fa-linkedin"></i></a></li>
						<?php
					}

					if ( ! empty( $this->data->youtube ) ) {
						?>
                        <li class="xlwcty_youtube">
                            <a href="<?php echo $this->data->youtube; ?>" target="_blank" class="<?php echo $this->get_slug() . '_youtube'; ?>"><i class="xlwcty-fa xlwcty-fa-youtube"></i></a></li>
						<?php
					}
					?>
                </ul>
            </div>
        </div>
    </div>
<?php
