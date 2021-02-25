<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_Smart_Bribes extends xlwcty_component implements xlwcty_coupon {

	private static $instance = null;
	public $viewpath = '';
	public $is_disable = true;
	public $coupon_expiry = '';
	public $coupon_value = '';
	public $time_display = '';

	public function __construct( $order = false ) {
		parent::__construct();
		$this->viewpath = __DIR__ . '/views/view.php';

		$this->show_locked_coupon = 1;
		$this->show_ad            = true;
		add_action( 'xlwcty_after_component_data_setup_xlwcty_social_coupons', array( $this, 'setup_style' ) );
		add_action( 'wp_ajax_xlwcty_smart_bribe_coupons', array( $this, 'get_locked_coupon' ) );
		add_action( 'wp_ajax_nopriv_xlwcty_smart_bribe_coupons', array( $this, 'get_locked_coupon' ) );
		add_action( 'xlwcty_after_components_loaded', array( $this, 'setup_fields' ) );
		add_filter( 'xlwcty_decode_coupon_merge_tags', array( $this, 'decode_coupon_merge_tag' ), 10, 2 );
	}

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function setup_fields() {
		$this->fields = array(
			'heading'                   => $this->get_slug() . '_heading',
			'heading_font_size'         => $this->get_slug() . '_heading_font_size',
			'heading_alignment'         => $this->get_slug() . '_heading_alignment',
			'desc'                      => $this->get_slug() . '_desc',
			'desc_alignment'            => $this->get_slug() . '_desc_alignment',
			'fb_like'                   => $this->get_slug() . '_fb_like',
			'fb'                        => $this->get_slug() . '_fb',
			'like_btn_text'             => $this->get_slug() . '_like_btn_text',
			'fb_share'                  => $this->get_slug() . '_fb_btn',
			'fb_share_text'             => $this->get_slug() . '_share_text',
			'fb_share_link'             => $this->get_slug() . '_share_link',
			'fb_share_cust_link'        => $this->get_slug() . '_share_custom_link',
			'share_btn_text'            => $this->get_slug() . '_share_btn_text',
			'btn_font_size'             => $this->get_slug() . '_btn_font_size',
			'btn_color'                 => $this->get_slug() . '_btn_color',
			'btn_bg_color'              => $this->get_slug() . '_btn_bg_color',
			'locked_coupon'             => $this->get_slug() . '_locked_coupon',
			'selected_coupon'           => $this->get_slug() . '_select',
			'personalize'               => $this->get_slug() . '_personalize',
			'format'                    => $this->get_slug() . '_format',
			'exp_days'                  => $this->get_slug() . '_expiry',
			'format_font'               => $this->get_slug() . '_font_size',
			'format_color'              => $this->get_slug() . '_color',
			'desc_after'                => $this->get_slug() . '_desc_after_click',
			'border_style'              => $this->get_slug() . '_border_style',
			'border_width'              => $this->get_slug() . '_border_width',
			'border_color'              => $this->get_slug() . '_border_color',
			'component_bg_color'        => $this->get_slug() . '_component_bg',
			'hide_for_repeat_customers' => $this->get_slug() . '_hide_for_repeat_customers',
		);
	}

	public function prepare_out_put_data() {
		parent::prepare_out_put_data();

		// assigning coupon expiry date and value
		$time_format        = get_option( 'time_format', false );
		$this->time_display = ' 23:59:59';
		if ( $time_format ) {
			$cur_date           = new DateTime( date( 'Y-m-d' ) . ' 23:59:59', new DateTimeZone( 'UTC' ) );
			$this->time_display = ' ' . date( $time_format, $cur_date->getTimestamp() );
		}
		if ( $this->data->locked_coupon == 'yes' && $this->data->selected_coupon != '' ) {
			$coupon_meta = $this->get_coupon_data( $this->data->selected_coupon );
			if ( is_array( $coupon_meta ) ) {
				if ( ! empty( $this->data->exp_days ) && (int) $this->data->exp_days > 0 ) {
					$expiry_date                        = (int) $this->data->exp_days;
					$expiry                             = $this->get_expiry_dates( (int) $expiry_date );
					$coupon_meta['_xlwcty_expiry_date'] = $expiry['expiry'];
					$this->coupon_expiry                = XLWCTY_Common::get_formatted_date_from_date( $coupon_meta['_xlwcty_expiry_date'], $this->get_wp_date_format() ) . $this->time_display;
				} elseif ( isset( $coupon_meta['expiry_date'] ) && $coupon_meta['expiry_date'] != '' ) {
					$formatted_date      = XLWCTY_Common::get_formatted_date_from_date( $coupon_meta['expiry_date'], $this->get_wp_date_format() );
					$this->coupon_expiry = $formatted_date . ' ' . $this->time_display;
				}
				$this->coupon_value = $coupon_meta['coupon_amount'];
				if ( $coupon_meta['discount_type'] == 'percent' ) {
					$this->coupon_value .= '%';
				} else {
					$this->coupon_value = wc_price( $this->coupon_value );
				}
			}
		}
	}

	public function get_coupon_data( $coupon_id, $force = false ) {
		$coupon_meta = array();
		if ( ! $coupon_id ) {
			return $coupon_meta;
		}
		if ( empty( $this->campaign_data[ $coupon_id ] ) || $force == true ) {
			$meta = get_post_meta( $coupon_id );
			if ( is_array( $meta ) && count( $meta ) > 0 ) {
				foreach ( $meta as $key => $val ) {
					if ( $key != '_edit_lock' && $key != '_edit_last' ) {
						$coupon_meta[ $key ] = maybe_serialize( $val[0] ) ? maybe_unserialize( $val[0] ) : $val[0];
					}
				}
				$this->campaign_data[ $coupon_id ] = $coupon_meta;
			}
		} else {
			$coupon_meta = $this->campaign_data[ $coupon_id ];
		}

		return $coupon_meta;
	}

	public function get_expiry_dates( $noOfdays = 0 ) {
		if ( empty( $noOfdays ) ) {
			$coupon_id   = (int) $this->data->selected_coupon;
			$expiry_date = get_post_meta( $coupon_id, 'expiry_date', true );
			$expiry_time = get_post_meta( $coupon_id, 'date_expires', true );

			return array(
				'expiry'             => $expiry_date,
				'expire_on'          => $expiry_time,
				'expiry_timestamped' => $expiry_time,
			);
		}

		$dbj       = new DateTime();
		$timestamp = strtotime( '+1 days' );
		$dbj->setTimestamp( $timestamp );
		$expDate   = $dbj->format( 'Y-m-d' );
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

	public function get_locked_coupon() {
		extract( $_POST );
		$output = array(
			'cp_html' => '',
			'type'    => '1',
		);
		xlwcty_Core()->data->load_order( $_POST['or_id'] );
		xlwcty_Core()->data->set_page( $_POST['cp_id'] );
		xlwcty_Core()->data->load_thankyou_metadata();
		if ( $this->data->locked_coupon == 'yes' ) {
			$coupon_data       = $this->generate_new_coupons( $_POST['or_id'] );
			$output['cp_html'] = '';
			if ( $coupon_data['coupon_code'] != '' ) {
				ob_start();
				extract( $coupon_data );
				include __DIR__ . '/views/coupon.php';
				$coupon_html       = ob_get_clean();
				$output['cp_html'] = $coupon_html;
				do_action( 'xlwcty_get_locked_coupon', $_POST['or_id'], $coupon_code );
			}
		}
		exit( json_encode( $output ) );
	}

	public function generate_new_coupons( $or_id = 0 ) {

		if ( isset( $_POST['or_id'] ) && $_POST['or_id'] > 0 ) {
			$or_id = $_POST['or_id'];
		}
		$or_id = (int) $or_id;
		if ( isset( $or_id ) && $or_id > 0 ) {
			try {
				$cp_data = $this->get_formated_coupon();

				return array(
					'coupon_code' => $cp_data['coupon_code'],
					'exp'         => $cp_data['expiry_date'],
				);
			} catch ( Exception $e ) {
				echo $e->getMessage();
			}
		}
	}

	public function get_formated_coupon() {
		$coupon_code      = $this->save_coupon_data();
		$formated_coupon  = get_the_title( $coupon_code );
		$expiry_date      = get_post_meta( $coupon_code, '_xlwcty_expiry_date', true );
		$expiry_date_time = '';
		if ( $this->data->selected_coupon > 0 ) {
			if ( $expiry_date != '' ) {
				$time = strtotime( $expiry_date );
				$date = new DateTime();
				$date->setTimestamp( $time );
				$expiry_date_time = $date->format( $this->get_wp_date_format() );
			}
		}

		return array(
			'coupon_code' => $formated_coupon,
			'expiry_date' => $expiry_date_time,
		);
	}

	public function save_coupon_data( $is_ajax = false ) {
		$coupon_id = 0;
		if ( is_numeric( $this->data->selected_coupon ) && $this->data->selected_coupon > 0 ) {
			$coupon_id    = (int) $this->data->selected_coupon;
			$order_data   = XLWCTY_Core()->data->get_order();
			$time_format  = get_option( 'time_format', false );
			$time_display = ' 23:59:59';
			if ( $time_format ) {
				$cur_date     = new DateTime( date( 'Y-m-d' ) . ' 23:59:59', new DateTimeZone( 'UTC' ) );
				$time_display = ' ' . date( $time_format, $cur_date->getTimestamp() );
			}
			if ( $this->data->personalize == 'yes' && $this->data->format != '' ) {
				$coupon_meta     = array();
				$formated_coupon = XLWCTY_Common::maype_parse_merge_tags( $this->data->format );

				if ( empty( $formated_coupon ) ) {
					return '';
				}

				/**
				 *  Check if coupon is already generated for this order.
				 */
				$check_coupon = get_post_meta( XLWCTY_Compatibility::get_order_id( $order_data ), '_xlwcty_coupon', true );

				if ( ! empty( $check_coupon ) ) {
					$is_coupon_exists = $this->check_coupon_exist( $formated_coupon );

					if ( $check_coupon == $is_coupon_exists ) {
						$coupon_id   = $is_coupon_exists;
						$coupon_meta = $this->get_coupon_data( $coupon_id );

						if ( isset( $coupon_meta['_xlwcty_expiry_date'] ) && ! empty( $coupon_meta['_xlwcty_expiry_date'] ) ) {
							$this->coupon_expiry = XLWCTY_Common::get_formatted_date_from_date( $coupon_meta['_xlwcty_expiry_date'], $this->get_wp_date_format() ) . $this->time_display;

							$this->coupon_value = $coupon_meta['coupon_amount'];
							if ( $coupon_meta['discount_type'] == 'percent' ) {
								$this->coupon_value .= '%';
							} else {
								$this->coupon_value = wc_price( $this->coupon_value );
							}

							return $coupon_id;
						}
					}
				}

				/**
				 *  Get coupon data and generate new one for this order.
				 */
				$coupon_meta = $this->get_coupon_data( $coupon_id );

				if ( $formated_coupon != '' && is_array( $coupon_meta ) && count( $coupon_meta ) > 0 ) {
					$expiry_date        = 0;
					$this->coupon_value = $coupon_meta['coupon_amount'];
					if ( $coupon_meta['discount_type'] == 'percent' ) {
						$this->coupon_value .= '%';
					} else {
						$this->coupon_value = wc_price( $this->coupon_value );
					}

					$billing_email = XLWCTY_Compatibility::get_order_data( $order_data, 'billing_email' );
					if ( $billing_email != '' ) {
						$coupon_meta['customer_email'] = array( $billing_email );
					}
					if ( ! empty( $this->data->exp_days ) && $this->data->exp_days > 0 ) {
						$expiry_date                        = (int) $this->data->exp_days;
						$expiry                             = $this->get_expiry_dates( (int) $expiry_date );
						$coupon_meta['expiry_date']         = $expiry['expire_on'];
						$coupon_meta['date_expires']        = $expiry['expiry_timestamped'];
						$coupon_meta['_xlwcty_expiry_date'] = $expiry['expiry'];
						$this->coupon_expiry                = XLWCTY_Common::get_formatted_date_from_date( $coupon_meta['_xlwcty_expiry_date'], $this->get_wp_date_format() ) . $time_display;
					} else {
						$coupon_meta['_xlwcty_expiry_date'] = $coupon_meta['expiry_date'];
						$coupon_meta['expiry_date']         = $coupon_meta['expiry_date'];

						$coupon_meta['date_expires'] = ( isset( $coupon_meta['date_expires'] ) ? $coupon_meta['date_expires'] : $coupon_meta['expiry_date'] );
						$this->coupon_expiry         = XLWCTY_Common::get_formatted_date_from_date( $coupon_meta['_xlwcty_expiry_date'], $this->get_wp_date_format() ) . $time_display;
					}
					if ( ! empty( $formated_coupon ) ) {
						$coupon_meta['_xlwcty_coupon_code'] = $formated_coupon;
					}
					if ( ! empty( $this->data->desc ) ) {
						$coupon_meta['_xlwcty_coupon_description'] = $this->data->desc;
					}

					$coupon_meta      = apply_filters( 'xlwcty_coupon_meta_for_thankyou_page', $coupon_meta, get_the_ID(), $this->data, false );
					$is_coupon_exists = $this->create_new_coupon( $formated_coupon, $coupon_meta );
					$coupon_id        = $is_coupon_exists;
					if ( empty( $check_coupon ) && ! empty( $is_coupon_exists ) ) {
						update_post_meta( XLWCTY_Compatibility::get_order_id( $order_data ), '_xlwcty_coupon', $is_coupon_exists );
					}
				}
			} else {
				if ( $coupon_id > 0 ) {
					$coupon_meta        = $this->get_coupon_data( $coupon_id );
					$expiry_date        = $coupon_meta['expiry_date'];
					$this->coupon_value = $coupon_meta['coupon_amount'];
					if ( $coupon_meta['discount_type'] == 'percent' ) {
						$this->coupon_value .= '%';
					} else {
						$this->coupon_value = wc_price( $this->coupon_value );
					}
					if ( isset( $coupon_meta['expiry_date'] ) && $coupon_meta['expiry_date'] != '' ) {
						$formatted_date      = XLWCTY_Common::get_formatted_date_from_date( $coupon_meta['expiry_date'], $this->get_wp_date_format() );
						$this->coupon_expiry = $formatted_date . ' ' . $this->time_display;
					}

					/** saving coupon in order meta */
					$check_coupon = get_post_meta( XLWCTY_Compatibility::get_order_id( $order_data ), '_xlwcty_coupon', true );
					if ( empty( $check_coupon ) ) {
						update_post_meta( XLWCTY_Compatibility::get_order_id( $order_data ), '_xlwcty_coupon', $coupon_id );
					}
				}
			}
		}

		return $coupon_id;
	}

	public function check_coupon_exist( $coupon_code ) {
		if ( ! $coupon_code ) {
			return '';
		}
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

	public function create_new_coupon( $coupon_name, $meta_data ) {
		$new_coupon = null;
		if ( empty( $coupon_name ) ) {
			return $new_coupon;
		}

		$is_coupon_exists = $this->check_coupon_exist( $coupon_name );
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

	public function setup_style( $slug ) {
		if ( $this->is_enable() ) {
			if ( $this->data->heading_font_size != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_title']['font-size']   = $this->data->heading_font_size . 'px';
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_title']['line-height'] = ( $this->data->heading_font_size + 4 ) . 'px';
			}
			if ( $this->data->heading_alignment != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_title']['text-align'] = $this->data->heading_alignment;
			}
			if ( $this->data->format_font != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_coupon_code']['font-size'] = $this->data->format_font . 'px';
			}
			if ( $this->data->format_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_coupon_code']['color']        = $this->data->format_color;
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_coupon_code']['border-color'] = $this->data->format_color;
			}
			if ( $this->data->btn_font_size != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_btn']['font-size'] = $this->data->btn_font_size . 'px';
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_btn']['font-size'] = $this->data->btn_font_size . 'px';
			}
			if ( $this->data->btn_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_btn']['color'] = $this->data->btn_color;
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_btn']['color'] = $this->data->btn_color;
				//                $style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_coupon_area .xlwcty_coupon_inner']['border-color'] = $this->data->format_color;
				//                $style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_coupon_area .xlwcty_sc_icon']['color'] = $this->data->format_color;
			}
			if ( $this->data->btn_bg_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_btn']['background'] = $this->data->btn_bg_color;
				$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_btn']['background'] = $this->data->btn_bg_color;

				$rgba = XLWCTY_Common::hex2rgb( $this->data->btn_bg_color, true );
				if ( $rgba != '' ) {
					$style['.xlwcty_wrap .xlwcty_socialBox .xlwcty_btn:hover']['background'] = "rgba({$rgba},0.70)";
				}
			}
			if ( $this->data->border_style != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_socialBox']['border-style'] = $this->data->border_style;
			}
			if ( (int) $this->data->border_width >= 0 ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_socialBox']['border-width'] = (int) $this->data->border_width . 'px';
			}
			if ( $this->data->border_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_socialBox']['border-color'] = $this->data->border_color;
			}
			if ( $this->data->component_bg_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_socialBox']['background-color'] = $this->data->component_bg_color;
			}
			parent::push_css( $slug, $style );
		}
	}

	public function decode_coupon_merge_tag( $content, $obj = false ) {
		if ( is_object( $obj ) ) {
			if ( $this->get_slug() == $obj->get_slug() ) {
				if ( strpos( $content, '{{coupon_expiry_date}}' ) !== false ) {
					$content = str_replace( '{{coupon_expiry_date}}', $this->coupon_expiry, $content );
				}
				if ( strpos( $content, '{{coupon_value}}' ) !== false ) {
					$content = str_replace( '{{coupon_value}}', $this->coupon_value, $content );
				}
			}
		}

		return $content;
	}

}

return XLWCTY_Smart_Bribes::get_instance();
