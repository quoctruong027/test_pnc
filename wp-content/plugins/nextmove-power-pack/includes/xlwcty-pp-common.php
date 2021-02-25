<?php

class XLWCTY_PP_Common {

	public function __construct() {

	}

	/**
	 * @return array
	 * Function to get all woocommerce order statuses
	 */
	public static function get_wc_order_statuses() {
		$wc_order_statuses = wc_get_order_statuses();
		$order_statuses    = array();
		foreach ( $wc_order_statuses as $status_key => $status_value ) {
			$key                    = str_replace( 'wc-', '', $status_key );
			$order_statuses[ $key ] = $status_value;
		}

		return $order_statuses;
	}

	/**
	 * @return array
	 * Get all woocommerce customer emails
	 */
	public static function get_wc_customer_emails() {
		$emails          = WC()->mailer()->get_emails();
		$customer_emails = array();
		$excluded_emails = self::get_excluded_customer_emails();

		foreach ( $emails as $email_key => $email ) {
			if ( $email->is_customer_email() && ! in_array( $email_key, $excluded_emails ) ) {
				$customer_emails[ $email_key ] = $email->get_title();
			}
		}

		return $customer_emails;
	}

	/**
	 * @return array
	 * Add excluded customer emails here
	 */
	public static function get_excluded_customer_emails() {
		return apply_filters( 'xlwcty_get_excluded_customer_emails', array(
			'WC_Email_Customer_Reset_Password',
			'WC_Email_Customer_New_Account',
		) );
	}

	/**
	 * @return array|mixed|void
	 * Function to get all power pack options
	 */
	public static function get_power_pack_options() {
		$options = get_option( 'xlwcty_power_pack_settings' );
		$options = wp_parse_args( $options, self::get_options_defaults() );

		return $options;
	}

	/**
	 * @return array
	 * Function to set all default values of power pack settings
	 */
	public static function get_options_defaults() {
		return array(
			'append_user_track_order_email'                    => 'no',
			'append_coupons_in_email'                          => 'no',
			'append_user_track_order_email_button_text'        => __( 'Click Here to Track Your Order', 'nextmove-power-pack' ),
			'append_user_track_order_email_section_bg_color'   => '#f2f2f2',
			'append_user_track_order_email_bg_color'           => '#f64c3f',
			'append_user_track_order_email_text_color'         => '#FFFFFF',
			'append_coupons_in_email_section_bg_color'         => '#f2f2f2',
			'append_coupons_in_email_heading_color'            => '#000',
			'append_coupons_in_email_content_color'            => '#777',
			'append_coupons_in_email_coupon_border_color'      => '#f64c3f',
			'append_coupons_in_email_coupon_bg_color'          => '#fff',
			'append_coupons_in_email_coupon_text_color'        => '#777',
			'append_coupons_in_email_coupon_button_bg_color'   => '#f64c3f',
			'append_coupons_in_email_coupon_button_text_color' => '#FFFFFF',
			'append_coupons_in_email_heading'                  => __( 'You unlocked a new coupon.', 'nextmove-power-pack' ),
			'append_coupons_in_email_desc'                     => __( 'Hi {{customer_first_name}}, as a way of saying thanks for shopping with {{shop_title}} today, here is a coupon for {{coupon_value}} off your next purchase in the next month. <b>Expires on: {{coupon_expiry_date}}</b>', 'nextmove-power-pack' ),
			'append_coupons_in_email_btn_text'                 => __( 'Shop Now', 'nextmove-power-pack' ),
			'append_coupons_in_email_btn_link'                 => '{{shop_url}}',
			'append_coupons_in_email_heading_lock'             => __( 'Unlock a new coupon.', 'nextmove-power-pack' ),
			'append_coupons_in_email_desc_lock'                => __( 'Hi {{customer_first_name}}, as a way of saying thanks for shopping with {{shop_title}} today, you can unlock a coupon for {{coupon_value}} off your next purchase in the next month.', 'nextmove-power-pack' ),
			'append_coupons_in_email_btn_text_lock'            => __( 'Shop Now', 'nextmove-power-pack' ),
		);
	}

	/**
	 * Return Track link html for admin settings
	 * @return string
	 */
	public static function get_preview_link_html() {
		ob_start();
		?>
        <div class="xlwcty-power-pack-preview-wrap xlwcty-power-pack-link-wrap">
            <div class="xlwcty-power-pack-track-link-preview-section">
                <h2 class="wp-heading-inline"><?php _e( 'Preview', 'nextmove-power-pack' ); ?></h2>
                <div class="xlwcty-power-pack-track-link-preview"><img src="<?php echo includes_url() . 'images/spinner.gif'; ?>"></div>
            </div>
        </div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Return Track link script for admin settings
	 * @return string
	 */
	public static function get_preview_link_script() {
		ob_start();
		include XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/xlwcty-track-link-preview.php';
		$track_link_html = ob_get_clean();

		return '<script type="text/html" id="tmpl-track-link-template">' . $track_link_html . '</script>';
	}

	/**
	 * Return Coupon html for admin settings
	 * @return string
	 */
	public static function get_preview_coupon_html() {
		ob_start();
		?>
        <div class="xlwcty-power-pack-preview-wrap xlwcty-power-pack-coupon-wrap">
            <div class="xlwcty-power-pack-coupon-preview-section">
                <h2 class="wp-heading-inline"><?php _e( 'Preview', 'nextmove-power-pack' ); ?></h2>
                <div class="xlwcty-pp-preview-desc">Preview when coupon is unlocked.</div>
                <div class="xlwcty-power-pack-immediate-coupon-preview"><img src="<?php echo includes_url() . 'images/spinner.gif'; ?>"></div>
            </div>
            <div class="xlwcty-power-pack-coupon-preview-section">
                <div class="xlwcty-pp-preview-desc">Preview when coupon is locked.</div>
                <div class="xlwcty-power-pack-lock-coupon-preview"><img src="<?php echo includes_url() . 'images/spinner.gif'; ?>"></div>
            </div>
        </div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Return Coupon script for admin settings
	 * @return string
	 */
	public static function get_preview_coupon_script() {
		ob_start();
		include XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/xlwcty-immediate-coupon-preview.php';
		$immediate_coupon_html = ob_get_clean();

		ob_start();
		include XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/xlwcty-lock-coupon-preview.php';
		$lock_coupon_html = ob_get_clean();

		return '<script type="text/html" id="tmpl-immediate-coupon-template">' . $immediate_coupon_html . '</script><script type="text/html" id="tmpl-lock-coupon-template">' . $lock_coupon_html . '</script>';
	}

}
