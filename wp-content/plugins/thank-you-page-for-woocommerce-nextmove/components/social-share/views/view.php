<?php
defined( 'ABSPATH' ) || exit;

if ( 'yes' !== $this->data->fb && 'yes' !== $this->data->tw ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'Data not set', 'thank-you-page-for-woocommerce-nextmove' ) ) );

	return;
}

$opengraph = array();
$maxs      = $this->get_highest_order_product();
$permalink = false;

if ( is_array( $maxs ) && count( $maxs ) > 0 ) {
	if ( 'custom' === $this->data->share_link && '' !== $this->data->share_custom_link ) {
		$permalink = $this->data->share_custom_link;
	} else {
		$permalink = get_the_permalink( $maxs[0] );
	}
	$opengraph = $this->get_opengraph( $permalink );
}

if ( ! isset( $opengraph['twitter_image'] ) ) {
	if ( isset( $opengraph['image'] ) && '' !== $opengraph['image'] ) {
		$opengraph['twitter_image'] = $opengraph['image'];
	}
}
if ( false === $permalink ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'On but No Product available for Share', 'thank-you-page-for-woocommerce-nextmove' ) ) );
}

XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'On', 'thank-you-page-for-woocommerce-nextmove' ) ) );
?>
    <div class="xlwcty_Box xlwcty_share xlwcty_center">
        <div class="xlwcty_title"><?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->heading ); ?></div>
		<?php
		$desc_class = 'xlwcty_desc_div';
		if ( ! empty( $this->data->desc_alignment ) ) {
			$desc_class .= ' xlwcty_' . $this->data->desc_alignment;
		}
		echo $this->data->desc ? '<div class="' . $desc_class . '">' . apply_filters( 'xlwcty_the_content', $this->data->desc ) . '</div>' : '';
		?>
        <div class="xlwcty_shareTab">
            <ul>
				<?php
				if ( 'yes' === $this->data->fb ) {
					?>
                    <li class="xlwcty_fb">
                        <a href="javascript:void(0);" class="xlwcty_active" data-tab="xlwcty_fb_tab"><i class="xlwcty-fa xlwcty-fa-facebook"></i>Facebook</a>
                    </li>
					<?php
				}
				if ( 'yes' === $this->data->tw ) {
					$tw_class = '';
					if ( 'yes' !== $this->data->fb ) {
						$tw_class = ' class="xlwcty_active"';
					}
					?>
                    <li class="xlwcty_tw">
                        <a href="javascript:void(0);" <?php echo $tw_class; ?> data-tab="xlwcty_tw_tab" class=""><i class="xlwcty-fa xlwcty-fa-twitter"></i>Twitter</a>
                    </li>
					<?php
				}
				?>
            </ul>
            <div class="xlwcty_tab_content">
				<?php
				if ( 'yes' === $this->data->fb ) {
					?>
                    <div class="xlwcty_tabArea xlwcty_openTab" id="xlwcty_fb_tab">
                        <div class="xlwcty_text">
                            <textarea class="xlwcty_share_facebook_text"><?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->fb_message ); ?></textarea>
                        </div>
						<?php include __DIR__ . '/twitter-summary.php'; ?>
                        <p class="xlwcty_fb_share_btn xlwcty_center" href="<?php echo site_url(); ?>"
                           data-text="<?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->fb_message ); ?>"
                           data-url="<?php echo $permalink; ?>"
                           data-related="facebook">
                            <a class="xlwcty_btn wcxlty_fb_order_share"
                               href="<?php echo site_url(); ?>"
                               data-text="<?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->fb_message ); ?>"
                               data-url="<?php echo $permalink; ?>"
                               data-related="facebook"
                            >
								<?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->btn_text ); ?>
                            </a>
                        </p>
                    </div>
					<?php
				}
				if ( $this->data->tw == 'yes' ) {
					$tw_class = '';
					if ( $this->data->fb != 'yes' ) {
						$tw_class = 'xlwcty_openTab';
					}
					?>
                    <div class="xlwcty_tabArea <?php echo $tw_class; ?>" id="xlwcty_tw_tab">
                        <div class="xlwcty_text">
                            <textarea class="xlwcty_share_twitter_text"><?php echo XLWCTY_Common::maype_parse_merge_tags( $this->data->tw_message ); ?></textarea>
                        </div>
						<?php include __DIR__ . '/twitter-summary.php'; ?>
						<?php
						if ( $this->data->btn_text != '' ) {
							?>
                            <p class="xlwcty_tw_share_btn xlwcty_center" href="<?php echo site_url(); ?>"
                               data-text="
							   <?php
							   echo XLWCTY_Common::maype_parse_merge_tags( $this->data->tw_message );
							   ?>
							   "
                               data-url="<?php echo $permalink; ?>"
                               data-related="twitterapi,twitter">
                                <a class="xl-twitter-share-button xlwcty_btn"
                                   href="<?php echo site_url(); ?>"
                                   data-text="
								   <?php
								   echo XLWCTY_Common::maype_parse_merge_tags( $this->data->tw_message );
								   ?>
								   "
                                   data-url="<?php echo $permalink; ?>"
                                   data-related="twitterapi,twitter">
									<?php
									echo XLWCTY_Common::maype_parse_merge_tags( $this->data->btn_text );
									?>
                                </a>
                            </p>
							<?php
						}
						?>
                    </div>
					<?php
				}
				?>
            </div>
        </div>
    </div>
<?php
