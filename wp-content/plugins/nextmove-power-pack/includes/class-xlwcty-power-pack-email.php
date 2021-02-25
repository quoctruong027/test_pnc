<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XLWCTY_PP_Email {

	private static $ins = null;

	public function __construct() {
		add_action( 'woocommerce_email_order_meta', array( $this, 'xlwcty_send_tracking_url_coupon_in_wc_email' ), 10, 4 );
	}

	public static function instance() {
		if ( self::$ins == null ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * @param $order
	 * Function to send tracking link and coupon in the woocommerce email
	 */
	public function xlwcty_send_tracking_url_coupon_in_wc_email( $order, $sent_to_admin, $plain_text, $email ) {
		$settings = XLWCTY_PP_Common::get_power_pack_options();
		$key      = get_class( $email );

		/**
		 * Send coupon in the email
		 */
		if ( isset( $settings['append_coupons_in_email'] ) && 'yes' == $settings['append_coupons_in_email'] && isset( $settings['append_coupon_order_status_email'] ) && is_array( $settings['append_coupon_order_status_email'] ) && ! empty( $settings['append_coupon_order_status_email'] ) && in_array( $key, $settings['append_coupon_order_status_email'] ) ) {
			echo $this->xlwcty_generate_order_coupon( $order, $settings );
		}

		/**
		 * Send track link in the email
		 */
		if ( isset( $settings['append_user_track_order_email'] ) && 'yes' == $settings['append_user_track_order_email'] && isset( $settings['append_user_track_order_status_email'] ) && is_array( $settings['append_user_track_order_status_email'] ) && ! empty( $settings['append_user_track_order_status_email'] ) && in_array( $key, $settings['append_user_track_order_status_email'] ) ) {
			echo $this->xlwcty_generate_order_track_link( $order, $settings );
		}
	}

	/**
	 * @param $order
	 *
	 * @return false|string
	 * Function to generate coupon for the order and send it in the email
	 */
	public function xlwcty_generate_order_coupon( $order, $settings ) {
		$order_id           = $order->get_id();
		$coupon_id          = get_post_meta( $order_id, '_xlwcty_coupon', true );
		$coupon_code        = '';
		$coupon_expiry_date = '';

		/**
		 * get coupon metas / Content to be shown in email
		 */
		if ( empty( $coupon_id ) ) {

			/** Get thankyou page */
			$thankyou_id = get_post_meta( $order_id, '_xlwcty_thankyou_page', true );
			if ( empty( $thankyou_id ) ) {
				return '';
			}

			/** Get Thankyou page data */
			$data = XLWCTY_Common::get_item_data( $thankyou_id );
			if ( empty( $data ) || 1 != $data['coupon_enable'] || empty( $data['coupon_select'] ) ) {
				return '';
			}

			/**
			 * Load order if not loaded already
			 */
			$load_order = XLWCTY_Core()->data->get_order();
			if ( ! $load_order instanceof WC_Order ) {
				XLWCTY_Core()->data->load_order( $order_id );
			}

			if ( 'immediate' == $data['coupon_display'] ) {

				if ( 'yes' == $data['coupon_personalize'] ) {
					/** Generate coupon if immediately */
					$coupon_id = $this->xlwcty_save_coupon_data( $data, $order, $order_id );
					if ( empty( $coupon_id ) ) {
						return '';
					}

					$coupon_code        = get_post_meta( $coupon_id, '_xlwcty_coupon_code', true );
					$coupon_expiry_date = get_post_meta( $coupon_id, '_xlwcty_expiry_date', true );
				} else {
					$coupon_id = $data['coupon_select'];
				}

				$content  = $settings['append_coupons_in_email_desc'];
				$heading  = $settings['append_coupons_in_email_heading'];
				$btn_txt  = $settings['append_coupons_in_email_btn_text'];
				$btn_link = $settings['append_coupons_in_email_btn_link'];
			} else {

				$coupon_id = $data['coupon_select'];

				if ( 'yes' == $data['coupon_personalize'] ) {
					$coupon_code = XLWCTY_Common::maype_parse_merge_tags( $data['coupon_format'] );
				}

				$content  = $settings['append_coupons_in_email_desc_lock'];
				$heading  = $settings['append_coupons_in_email_heading_lock'];
				$btn_txt  = $settings['append_coupons_in_email_btn_text_lock'];
				$btn_link = get_permalink( $thankyou_id );
				$btn_link = XLWCTY_Common::prepare_single_post_url( $btn_link, $order );
			}
		} else {
			/**
			 * Get coupon data from coupon meta settings
			 */
			$coupon_code        = get_post_meta( $coupon_id, '_xlwcty_coupon_code', true );
			$coupon_expiry_date = get_post_meta( $coupon_id, '_xlwcty_expiry_date', true );
			$content            = $settings['append_coupons_in_email_desc'];
			$heading            = $settings['append_coupons_in_email_heading'];
			$btn_txt            = $settings['append_coupons_in_email_btn_text'];
			$btn_link           = $settings['append_coupons_in_email_btn_link'];
		}

		/** Get coupon data */
		$coupon_type  = get_post_meta( $coupon_id, 'discount_type', true );
		$coupon_value = get_post_meta( $coupon_id, 'coupon_amount', true );

		if ( empty( $coupon_code ) ) {
			$coupon_code = get_the_title( $coupon_id );
		}

		if ( empty( $coupon_expiry_date ) && isset( $data['coupon_expiry'] ) && ! empty( $data['coupon_expiry'] ) ) {
			$dbj       = new DateTime();
			$timestamp = strtotime( '+1 days' );
			$dbj->setTimestamp( $timestamp );
			$coupon_expiry_date = $dbj->format( 'Y-m-d' );
			$noOfdays           = (int) $data['coupon_expiry'];

			if ( $noOfdays > 0 ) {
				$noOfdaysPl = $noOfdays;
				$exptime    = strtotime( "+{$noOfdaysPl} days" );
				$dbj->setTimestamp( $exptime );
				$coupon_expiry_date = $dbj->format( 'Y-m-d' );
			}
		}

		/**
		 * generate coupon value
		 */
		if ( 'percent' == $coupon_type ) {
			$coupon_value .= '%';
		} else {
			$coupon_value = wc_price( $coupon_value );
		}

		/**
		 * generate time for coupon
		 */
		$time_format  = get_option( 'time_format', false );
		$time_display = ' 23:59:59';
		if ( $time_format ) {
			$cur_date     = new DateTime( date( 'Y-m-d' ) . ' 23:59:59', new DateTimeZone( 'UTC' ) );
			$time_display = ' ' . date( $time_format, $cur_date->getTimestamp() );
		}

		/**
		 * generate coupon expiry date
		 */
		$coupon_expiry = null;
		if ( isset( $coupon_expiry_date ) && ! empty( $coupon_expiry_date ) ) {
			$coupon_expiry = XLWCTY_Common::get_formatted_date_from_date( $coupon_expiry_date, get_option( 'date_format', 'Y-m-d' ) ) . $time_display;
		} else {
			$coupon_expiry_date = get_post_meta( $coupon_id, 'expiry_date', true );
			if ( isset( $coupon_expiry_date ) && ! empty( $coupon_expiry_date ) ) {
				$coupon_expiry = XLWCTY_Common::get_formatted_date_from_date( $coupon_expiry_date, get_option( 'date_format', 'Y-m-d' ) ) . $time_display;
			}
		}

		/**
		 *  Filter coupon content and replace the dynamic tags and other tags from the content
		 */
		XLWCTY_Core()->data->load_order( $order_id );
		$content = XLWCTY_ShortCode_Merge_Tags::maybe_parse_merge_tags( $content );
		$content = XLWCTY_Dynamic_Merge_Tags::maybe_parse_merge_tags( $content );
		$content = XLWCTY_Static_Merge_Tags::maybe_parse_merge_tags( $content );
		$heading = XLWCTY_Common::maype_parse_merge_tags( $heading );

		/**
		 *  Replace tags in the content
		 */
		if ( false !== strpos( $content, '{{coupon_expiry_date}}' ) ) {
			$content = str_replace( '{{coupon_expiry_date}}', $coupon_expiry, $content );
			$heading = str_replace( '{{coupon_expiry_date}}', $coupon_expiry, $heading );
		}
		if ( false !== strpos( $content, '{{coupon_value}}' ) ) {
			$content = str_replace( '{{coupon_value}}', $coupon_value, $content );
			$heading = str_replace( '{{coupon_value}}', $coupon_value, $heading );
		}

		/**
		 * Get coupon colors to be shown in the email
		 */
		$section_color       = $settings['append_coupons_in_email_section_bg_color'];
		$heading_color       = $settings['append_coupons_in_email_heading_color'];
		$content_color       = $settings['append_coupons_in_email_content_color'];
		$coupon_border_color = $settings['append_coupons_in_email_coupon_border_color'];
		$coupon_bg_color     = $settings['append_coupons_in_email_coupon_bg_color'];
		$coupon_text_color   = $settings['append_coupons_in_email_coupon_text_color'];
		$button_bg_color     = $settings['append_coupons_in_email_coupon_button_bg_color'];
		$button_text_color   = $settings['append_coupons_in_email_coupon_button_text_color'];

		ob_start();
		include XLWCTY_POWER_PACK_PLUGIN_DIR . 'components/track-order/views/coupon-email.php';
		$coupon_email = ob_get_clean();

		return $coupon_email;
	}

	public function xlwcty_save_coupon_data( $data, $order, $order_id ) {
		$coupon_id = (int) $data['coupon_select'];

		if ( '' != $data['coupon_format'] ) {
			$coupon_meta     = array();
			$formated_coupon = XLWCTY_Common::maype_parse_merge_tags( $data['coupon_format'] );

			if ( empty( $formated_coupon ) ) {
				return '';
			}

			/**
			 *  Get coupon data and generate new one for this order.
			 */
			$coupon_meta = $this->xlwcty_get_coupon_data( $coupon_id );

			if ( is_array( $coupon_meta ) && count( $coupon_meta ) > 0 ) {
				$expiry_date                        = 0;
				$coupon_meta['_xlwcty_coupon_code'] = $formated_coupon;
				$billing_email                      = XLWCTY_Compatibility::get_order_data( $order, 'billing_email' );

				if ( '' != $billing_email ) {
					$coupon_meta['customer_email'] = array( $billing_email );
				}

				if ( ! empty( $data['coupon_expiry'] ) ) {
					$expiry_date                        = (int) $data['coupon_expiry'];
					$expiry                             = $this->xlwcty_get_expiry_dates( (int) $expiry_date );
					$coupon_meta['expiry_date']         = $expiry['expire_on'];
					$coupon_meta['date_expires']        = $expiry['expiry_timestamped'];
					$coupon_meta['_xlwcty_expiry_date'] = $expiry['expiry'];
				} else {
					$coupon_meta['_xlwcty_expiry_date'] = $coupon_meta['expiry_date'];
					$coupon_meta['expiry_date']         = $coupon_meta['expiry_date'];
					$coupon_meta['date_expires']        = ( isset( $coupon_meta['date_expires'] ) ? $coupon_meta['date_expires'] : $coupon_meta['expiry_date'] );
				}

				if ( ! empty( $data['coupon_desc'] ) ) {
					$coupon_meta['_xlwcty_coupon_description'] = $data['coupon_desc'];
				}

				$is_coupon_exists = $this->xlwcty_create_new_coupon( $formated_coupon, $coupon_meta );
				$coupon_id        = $is_coupon_exists;

				if ( ! empty( $is_coupon_exists ) ) {
					update_post_meta( $order_id, '_xlwcty_coupon', $is_coupon_exists );
				}
			}
		} else {
			if ( $coupon_id > 0 ) {
				$coupon_meta = $this->xlwcty_get_coupon_data( $coupon_id );
				$expiry_date = $coupon_meta['expiry_date'];

				/** saving coupon in order meta */
				$check_coupon = get_post_meta( $order_id, '_xlwcty_coupon', true );
				if ( empty( $check_coupon ) ) {
					update_post_meta( $order_id, '_xlwcty_coupon', $coupon_id );
				}
			}
		}

		return $coupon_id;
	}

	public function xlwcty_get_coupon_data( $coupon_id ) {
		$meta = get_post_meta( $coupon_id );
		if ( is_array( $meta ) && count( $meta ) > 0 ) {
			foreach ( $meta as $key => $val ) {
				if ( $key != '_edit_lock' && $key != '_edit_last' ) {
					$coupon_meta[ $key ] = maybe_serialize( $val[0] ) ? maybe_unserialize( $val[0] ) : $val[0];
				}
			}
		}

		return $coupon_meta;
	}

	public function xlwcty_get_expiry_dates( $noOfdays = 0 ) {
		$dbj       = new DateTime();
		$timestamp = strtotime( '+1 days' );
		$dbj->setTimestamp( $timestamp );
		$expDate = $dbj->format( 'Y-m-d' );

		$timestamp = time();
		$dbj->setTimestamp( $timestamp );
		$expDateEmail = $dbj->format( 'Y-m-d h:i:s' );
		$noOfdays     = (int) $noOfdays;

		if ( $noOfdays > 0 ) {
			$noOfdaysPl = $noOfdays;
			$exptime    = strtotime( "+{$noOfdaysPl} days" );
			$dbj->setTimestamp( $exptime );
			$expDate          = $dbj->format( 'Y-m-d' );
			$noOfdays         += 1;
			$exptime          = strtotime( "+{$noOfdays} days" );
			$expDateEmail     = date( 'Y-m-d', $exptime );
			$expiry_timestamp = strtotime( $expDateEmail );
		}

		$date = array(
			'expiry'             => $expDate,
			'expire_on'          => $expDateEmail,
			'expiry_timestamped' => $expiry_timestamp,
		);

		return $date;
	}

	public function xlwcty_create_new_coupon( $coupon_name, $meta_data ) {
		$new_coupon       = null;
		$is_coupon_exists = $this->xlwcty_check_coupon_exist( $coupon_name );

		if ( is_null( $is_coupon_exists ) ) {
			$args      = array(
				'post_type'   => 'shop_coupon',
				'post_status' => 'publish',
				'post_title'  => $coupon_name,
			);
			$coupon_id = wp_insert_post( $args );
		} else {
			$coupon_id = $is_coupon_exists;
		}
		if ( ! is_wp_error( $coupon_id ) ) {
			$meta_data['usage_count'] = 0;
			update_post_meta( $coupon_id, 'is_xlwcty_coupon', $coupon_id );
			if ( is_array( $meta_data ) && count( $meta_data ) > 0 ) {
				foreach ( $meta_data as $key => $val ) {

					update_post_meta( $coupon_id, $key, $val );
				}
			}
			$new_coupon = $coupon_id;
		}

		return $new_coupon;
	}

	public function xlwcty_check_coupon_exist( $coupon_code ) {
		global $wpdb;
		$coupon_code  = str_replace( "\n", '', $coupon_code );
		$coupon_found = $wpdb->get_var( $wpdb->prepare( "
				SELECT $wpdb->posts.ID
				FROM $wpdb->posts
				WHERE $wpdb->posts.post_type = 'shop_coupon'				
				AND $wpdb->posts.post_title = '%s'
			 ", $coupon_code ) );

		return $coupon_found;
	}

	/**
	 * @param $order WC_Order
	 *
	 * @return false|string
	 * Function to generate order tracking link and send it in the email
	 */
	public function xlwcty_generate_order_track_link( $order, $settings ) {
		$order_id    = $order->get_id();
		$thankyou_id = get_post_meta( $order_id, '_xlwcty_thankyou_page', true );

		if ( empty( $thankyou_id ) ) {
			return '';
		}

		$get_link         = get_permalink( $thankyou_id );
		$url              = XLWCTY_Common::prepare_single_post_url( $get_link, $order );
		$url              = add_query_arg( array(
			'ts' => 1,
		), $url );
		$button_text      = $settings['append_user_track_order_email_button_text'];
		$section_bg_color = $settings['append_user_track_order_email_section_bg_color'];
		$bg_color         = $settings['append_user_track_order_email_bg_color'];
		$text_color       = $settings['append_user_track_order_email_text_color'];

		ob_start();
		include XLWCTY_POWER_PACK_PLUGIN_DIR . 'components/track-order/views/track_link.php';
		$track_button = ob_get_clean();

		return $track_button;
	}

}

XLWCTY_PP_Email::instance();
