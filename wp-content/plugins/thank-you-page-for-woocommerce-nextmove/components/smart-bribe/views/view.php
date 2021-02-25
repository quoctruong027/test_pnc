<?php
defined( 'ABSPATH' ) || exit;

if ( 'yes' === $this->data->hide_for_repeat_customers ) {
	$user_id       = $order_data->get_user_id();
	$billing_email = XLWCTY_Compatibility::get_order_data( $order_data, 'billing_email' );

	$orders = wc_get_orders( array(
		'customer' => $user_id ? $user_id : $billing_email,
		'limit'    => 1,
		'return'   => 'ids',
		'exclude'  => array( $order_data->id ),
	) );

	if ( ! empty( $orders ) ) {
		return false;
	}
}

if ( 'yes' !== $this->data->fb_like && 'yes' !== $this->data->fb_share ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'Data not set', 'thank-you-page-for-woocommerce-nextmove' ) ) );

	return false;
}
XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'On', 'thank-you-page-for-woocommerce-nextmove' ) ) );

if ( 'custom' === $this->data->fb_share_link && '' !== $this->data->fb_share_cust_link ) {
	$permalink = XLWCTY_Static_Merge_Tags::maybe_parse_merge_tags( $this->data->fb_share_cust_link );
} else {
	$maxs      = $this->get_highest_order_product();
	$permalink = get_the_permalink( $maxs[0] );
}
?>
    <div class="xlwcty_Box xlwcty_socialBox ">
        <div style="display: none" class="xlwcty_component_load"><i class="xlwcty-fa xlwcty-fa-spinner xlwcty-fa-pulse xlwcty-fa-2x xlwcty-fa-fw"></i></div>
		<?php
		echo $this->data->heading ? '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->heading, $this ) . '</div>' : '';
		$or_id       = XLWCTY_Compatibility::get_order_id( $order_data );
		$cookie_data = ( isset( $_COOKIE["xlwcty_smart_bribe_cookies_displayed_{$or_id}"] ) ? $_COOKIE["xlwcty_smart_bribe_cookies_displayed_{$or_id}"] : '' );
		if ( isset( $cookie_data ) && '' !== $cookie_data ) {
			$data          = json_decode( stripslashes( $cookie_data ), true );
			$data_order_id = (int) $data['or'];
			if ( $data_order_id == $order_data->get_id() ) {
				$coupon_data = $this->generate_new_coupons( $data_order_id );
				extract( $coupon_data );
				if ( '' !== $coupon_data['coupon_code'] ) {
					include __DIR__ . '/coupon.php';
				}
			}
		} else {
			?>
            <div class="xlwcty_smart_bribe_coupon">
                <div class="xlwcty_content xlwcty_clearfix">
					<?php
					$desc_class = '';
					if ( ! empty( $this->data->desc_alignment ) ) {
						$desc_class = ' class="xlwcty_' . $this->data->desc_alignment . '"';
					}
					echo $this->data->desc ? '<div' . $desc_class . '>' . apply_filters( 'xlwcty_the_content', $this->data->desc, $this ) . '</div>' : '';

					if ( 'yes' === $this->data->locked_coupon && $this->data->selected_coupon > 0 ) {
						if ( '' !== $this->data->selected_coupon ) {
							?>
                            <div class="xlwcty_clear_15"></div>
                            <div class="xlwcty_coupon_area xlwcty_smart_bribe_show_hidden_coupon">
                                <div class="xlwcty_coupon_inner">
                                    <div class="xlwcty_overlay"></div>
                                    <div class="xlwcty_sc_icon xlwcty_lock"><i class="xlwcty-fa xlwcty-fa-lock"></i></div>
                                    <div class="xlwcty_cou_text">
										<?php
										if ( 'yes' === $this->data->personalize && '' !== $this->data->format ) {
											echo sanitize_title( XLWCTY_Common::maype_parse_merge_tags( $this->data->format, $this ) );
										} else {
											echo get_the_title( $this->data->selected_coupon );
										}
										?>
                                    </div>
                                    <div class="xlwcty_sc_icon xlwcty_r_icon xlwcty_lock"><i class="xlwcty-fa xlwcty-fa-lock"></i></div>
                                </div>
                            </div>
							<?php
						}
					}
					?>
                </div>
            </div>
            <div class="xlwcty_social_icon xlwcty_smart_bribe_icons xlwcty_btn_style xlwcty_full_style xlwcty_center">
                <ul>
					<?php
					if ( 'yes' === $this->data->fb_like && '' !== $this->data->fb ) {
						?>
                        <li class="xlwcty_facebook">
                            <div class="xlwcty_btn_wrap">
                                <div class="xlwcty_btn_icon"><i class="xlwcty-fa xlwcty-fa-facebook"></i></div>
                                <div class="xlwcty_btn_title">
									<?php
									echo XLWCTY_Common::maype_parse_merge_tags( $this->data->like_btn_text != '' ? $this->data->like_btn_text : __( 'Like', 'thank-you-page-for-woocommerce-nextmove' ), $this );
									?>
                                </div>
                                <div class="xlwcty_social_control_wrap">
                                    <div class="xlwcty_btn_text_h">
										<?php
										echo XLWCTY_Common::maype_parse_merge_tags( $this->data->like_btn_text != '' ? $this->data->like_btn_text : __( 'Like', 'thank-you-page-for-woocommerce-nextmove' ), $this );
										?>
                                    </div>
                                    <div class="fb-like" data-href="<?php echo $this->data->fb; ?>" data-layout="button" data-action="like" data-size="large" data-show-faces="true"
                                         data-share="false"></div>
                                </div>
                            </div>
                        </li>
						<?php
					}
					if ( 'yes' === $this->data->fb_share ) {
						$fb_share_text     = $this->data->fb_share_text ? XLWCTY_Common::maype_parse_merge_tags( $this->data->fb_share_text, $this ) : '';
						$fb_share_btn_text = $this->data->share_btn_text ? XLWCTY_Common::maype_parse_merge_tags( $this->data->share_btn_text, $this ) : __( 'Share', 'thank-you-page-for-woocommerce-nextmove' );
						?>
                        <li class="xlwcty_facebook">
                            <div class="xlwcty_btn_wrap" data-href="<?php echo $permalink; ?>">
                                <div class="xlwcty_btn_icon"><i class="xlwcty-fa xlwcty-fa-facebook"></i></div>
                                <div class="xlwcty_btn_title">
									<?php echo $fb_share_btn_text; ?>
                                </div>
                                <div class="xlwcty_social_control_wrap">
                                    <div class="xlwcty_btn_text_h">
										<?php echo $fb_share_btn_text; ?>
                                    </div>
                                    <div class="xlwcty_share_btn wcxlty_fb_order_smart_bribe" data-href="<?php echo $permalink; ?>" data-text="<?php echo $fb_share_text; ?>">
										<?php echo $fb_share_btn_text; ?>
                                    </div>
                                </div>
                            </div>
                        </li>
						<?php
					}
					?>
                </ul>
            </div>
			<?php
		}
		?>
    </div>
<?php
