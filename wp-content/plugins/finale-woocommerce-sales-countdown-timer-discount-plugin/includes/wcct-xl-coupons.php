<?php

class WCCT_XL_Coupons {

	public static $_instance = null;
	public $is_mini_cart = false;
	public $add_to_cart_action = false;
	public $cart_product_id = 0;
	public $cart_product_qty = array();
	public $notices = array();
	public $unique_info_notices = array();


	public function __construct() {

		/**
		 * Controller function to apply coupons
		 */
		add_action( 'woocommerce_add_to_cart', array( $this, 'wcct_maybe_apply_coupons' ), 999, 6 );

		/**
		 * Hook to add HTML markup with the coupons html to add our countdown timer with coupon discount
		 */
		add_filter( 'woocommerce_cart_totals_coupon_html', array( $this, 'wcct_maybe_show_cart_table_coupon_timer' ), 999, 2 );

		/**
		 * Checking if coupon is valid , if the coupon is attached with the respective campaign
		 */
		add_action( 'woocommerce_coupon_is_valid', array( $this, 'woocommerce_coupon_is_valid' ), 999, 2 );

		/**
		 * Show Custom failure message when campaign got expired.
		 */
		add_action( 'woocommerce_coupon_error', array( $this, 'wcct_woocommerce_coupon_error' ), 999, 3 );
		/**
		 * Hide native success message by validation coupon and campaign state
		 */
		add_action( 'woocommerce_coupon_message', array( $this, 'wcct_woocommerce_coupon_message' ), 999, 3 );

		/**
		 * Checking any request to add coupon by the url and try to add discount
		 */
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'maybe_add_coupon_by_url' ), 998 );
		/**
		 * Checking any request to add coupon by the url and try to add discount
		 */
		add_action( 'template_redirect', array( $this, 'wcct_maybe_load_notices_coupons_on_template_redirect' ), 999 );
		add_action( 'woocommerce_check_cart_items', array( $this, 'wcct_maybe_load_notices_coupons_on_cart_item_check' ), 999 );

		/**
		 * Handle WC session to not store our notices in persistent session
		 */
		add_action( 'shutdown', array( $this, 'woocommerce_validate_session_for_notices' ), 19 );
		add_action( 'woocommerce_checkout_process', array( $this, 'prevent_coupon_notice_register' ) );

		/**
		 * Recheck all the coupons we have in the session to make sure that if the coupon valid for the current cart state then gets applied.
		 */
		add_filter( 'woocommerce_update_cart_action_cart_updated', array( $this, 're_apply_coupons' ) );
		/**
		 * restricting success notice visibility using our own filter
		 */
		//      add_filter( 'wcct_restrict_coupon_notice', array( $this, 'remove_coupon_notice_on_cart_and_checkout' ), 1, 2 );
	}

	/**
	 * @return WCCT_XL_Coupons
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Getting all campaigns who have coupons enabled, setting up session for the extracted coupons
	 * Iterate over the coupons that are set for auto application
	 * @hooked over `woocommerce_add_to_cart_validation`
	 *
	 * @param boolean $bool validation state variable
	 * @param int $productID Product ID
	 * @param int $qty Quantity
	 *
	 */
	public function wcct_maybe_apply_coupons( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		/**
		 * Getting data
		 */
		wcct_force_log( 'Product ID: Initializing coupon apply on adding ' );
		WCCT_Core()->public->wcct_get_product_obj( $product_id );
		$data = WCCT_Core()->public->get_single_campaign_pro_data( $product_id, true );

		/**
		 * Filter only running and auto campaigns coupons
		 */
		$coupons_data = $this->wcct_get_all_coupons( $data, true );
		$coupons      = wp_list_pluck( $coupons_data, 'coupons' );
		$coupons      = WCCT_Common::array_flatten( $coupons );

		/**
		 * Check coupon usage count before adding coupon
		 * Sometimes the user email is saved in the database instead of user id
		 * And woocommerce only checks user id before applying coupons.
		 *
		 * So need to check user id and email to avoid this.
		 */
		add_filter( 'woocommerce_coupon_validate_user_usage_limit', array( $this, 'wcct_check_coupon_usage_with_user_id_email' ), 10, 4 );

		/**
		 * Iterate over the coupons to make them applied
		 */
		foreach ( $coupons as $coupon ) {

			$firstdata      = current( $coupons_data );
			$is_hide_errors = $firstdata['properties']['hide_errors'];
			$coupon_obj     = get_post( $coupon );

			if ( ! $coupon_obj instanceof WP_Post ) {
				continue;
			}
			// Sanitize coupon code
			$coupon_name = $coupon_obj->post_title;
			$coupon_code = apply_filters( 'woocommerce_coupon_code', $coupon_name );

			// Get the coupon
			$the_coupon = new WC_Coupon( $coupon_code );

			//checking if coupon is valid and not applied already inside the cart
			if ( $is_hide_errors == 'yes' ) {

				if ( ! WC()->cart->has_discount( $coupon_code ) && $the_coupon->is_valid() ) {
					WC()->cart->add_discount( $coupon_code );
				}
			} else {

				WC()->cart->add_discount( $coupon_code );
			}
		}

		/**
		 * Remove filter to avoid conflict with any other functionality of woocommerce
		 */
		remove_filter( 'woocommerce_coupon_validate_user_usage_limit', array( $this, 'wcct_check_coupon_usage_with_user_id_email' ), 10, 4 );
	}

	/**
	 *
	 * Iterating over all the coupons and setting a session for all the data
	 *
	 * @param array $data
	 * @param bool $is_auto
	 *
	 * @return array Auto coupons array Or Whole set of array
	 */
	private function wcct_get_all_coupons( $data, $is_auto = false ) {
		$coupons_return = array();
		$auto_return    = array();
		if ( $data && is_array( $data ) && count( $data ) > 0 ) {

			foreach ( $data['coupons'] as $campID => $coupons ) {

				$coupons_return[ $campID ] = array(
					'campaign'       => $campID,
					'apply_mode'     => $coupons['apply_mode'],
					'coupons'        => $coupons['coupons'],
					'properties'     => $coupons,
					'running_status' => 'running',
				);

				//if any case the campaign is not running
				//skip this iteration
				if ( true === in_array( $campID, $data['expired'] ) ) {
					$coupons_return[ $campID ]['running_status'] = 'expired';
					continue;
				}
				//if any case the campaign is not running
				//skip this iteration
				if ( true === in_array( $campID, $data['scheduled'] ) ) {
					$coupons_return[ $campID ]['running_status'] = 'scheduled';
					continue;
				}
				if ( $coupons['apply_mode'] == 'auto' ) {

					$date_obj = new DateTime();
					$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

					if ( $data['campaign_meta'][ $campID ]['end_time'] < $date_obj->getTimestamp() ) {
						continue;
					}

					$auto_return[ $campID ] = array(
						'campaign'   => $campID,
						'apply_mode' => $coupons['apply_mode'],
						'coupons'    => $coupons['coupons'],
						'properties' => $coupons,
					);
				}
			}
		}

		if ( ! empty( $coupons_return ) ) {
			$get_prev_session = WC()->session->get( '_wcct_cart_coupons_data_' );

			if ( ! is_array( $get_prev_session ) ) {
				$get_prev_session = array();
			}

			$get_prev_session = array_replace( $get_prev_session, $coupons_return );
			WC()->session->set( '_wcct_cart_coupons_data_', $get_prev_session );
			wcct_force_log( 'Coupon session set is ' . print_r( $get_prev_session, true ) );
		}

		if ( $is_auto === true ) {
			return $auto_return;
		}

		return $coupons_return;
	}

	/**
	 * @param $flag
	 * @param $user_id
	 * @param $coupon
	 * @param $obj
	 *
	 * @return bool
	 * @throws Exception
	 *
	 * Function to check coupon usage count based on user ID and Email
	 */
	public function wcct_check_coupon_usage_with_user_id_email( $flag, $user_id, $coupon, $obj ) {
		if ( ! is_user_logged_in() ) {
			return $flag;
		}

		$usage_limit = $coupon->get_usage_limit_per_user();

		if ( empty( $usage_limit ) ) {
			return $flag;
		}

		global $wpdb;
		$user        = get_user_by( 'ID', $user_id );
		$usage_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( meta_id ) 
							FROM {$wpdb->postmeta} 
							WHERE post_id = %d 
							AND meta_key = '_used_by' 
							AND ( meta_value = %d OR meta_value = %s )", $coupon->get_id(), $user_id, $user->user_email ) );

		if ( $usage_count >= $usage_limit ) {
			throw new Exception( __( 'Coupon usage limit has been reached.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), 106 );
		}

		return false;
	}

	/**
	 * @hooked over `woocommerce_cart_totals_coupon_html`
	 *
	 * @param $text
	 * @param $coupon
	 *
	 * @return string
	 */
	public function wcct_maybe_show_cart_table_coupon_timer( $text, $coupon ) {
		$content = '';
		if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
			$couponID = $coupon->get_id();
		} else {
			$couponID = $coupon->id;
		}

		$get_prev_session = WC()->session->get( '_wcct_cart_coupons_data_' );
		$campID           = 0;
		$campID_final     = 0;
		if ( is_array( $get_prev_session ) ) {
			foreach ( $get_prev_session as $campaigns ) {

				if ( isset( $campaigns['running_status'] ) && 'scheduled' === $campaigns['running_status'] ) {
					continue;
				}

				if ( $couponID == $campaigns['coupons'] ) {
					$campID = $campaigns['campaign'];

					$data     = WCCT_Core()->public->get_single_campaign_instance( $campID, 0, true );
					$date_obj = new DateTime();
					$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

					//checking if its a valid campaign
					if ( isset( $data['campaign_meta'][ $campID ] ) && $data['campaign_meta'][ $campID ]['end_time'] > $date_obj->getTimestamp() ) {
						//assigning campaign ID final
						$campID_final = $campID;
					}

					break;
				}
			}
		}

		if ( $campID_final !== 0 ) {
			$data = WCCT_Core()->public->get_single_campaign_instance( $campID_final, 0, true );

			if ( isset( $data['campaign_meta'][ $campID_final ] ) ) {
				$content    = $campaigns['properties']['cart_message'];
				$timer_data = array(
					'skin'            => 'default',
					'bg_color'        => 'transparent',
					'label_color'     => '#dd3333',
					'timer_font'      => 'inherit',
					'label_font'      => '13',
					'end_timestamp'   => $data['campaign_meta'][ $campID_final ]['end_time'],
					'start_timestamp' => $data['campaign_meta'][ $campID_final ]['start_time'],
					'full_instance'   => $data['coupons'][ $campID_final ],
				);
				$get_timer  = WCCT_Core()->appearance->wcct_maybe_parse_timer( $campID, $timer_data, 'cart_table' );
				$content    = str_replace( '{{countdown_timer}}', $get_timer, $content );
			}
		}

		return $content . $text;
	}

	public function wcct_maybe_load_notices_coupons_on_template_redirect( $current_query ) {
		if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && is_object( WC()->session ) && false == is_cart() && false === is_checkout() ) {
			$get_prev_session = WC()->session->get( '_wcct_cart_coupons_data_' );

			if ( is_array( $get_prev_session ) ) {

				foreach ( $get_prev_session as $campaigns ) {
					if ( ! in_array( $campaigns['coupons'], $this->notices ) ) {
						$this->wcct_register_info_notice( $campaigns['coupons'], $campaigns );
					}
				}
			}
		}
	}

	public function wcct_register_info_notice( $coupon, $campaigns ) {
		global $post, $woocommerce;
		/** manage if there is no product in the cart, do not show timer info bar */

		if ( WC()->cart->is_empty() ) {
			return;
		}

		$content    = '';
		$coupon_obj = get_post( $coupon );

		// Sanitize coupon code
		$coupon_code = '';
		if ( $coupon_obj instanceof WP_Post ) {
			$coupon_name = $coupon_obj->post_title;
			$coupon_code = apply_filters( 'woocommerce_coupon_code', $coupon_name );
		}
		if ( $coupon_code != '' && WC()->cart->has_discount( $coupon_code ) ) {
			// Get the coupon
			$the_coupon = new WC_Coupon( $coupon_code );
			$campID     = $campaigns['campaign'];

			if ( isset( $campaigns['running_status'] ) && 'scheduled' === $campaigns['running_status'] ) {
				return;
			}

			if ( $campID !== 0 ) {

				$data     = WCCT_Core()->public->get_single_campaign_instance( $campID, 0, true );
				$date_obj = new DateTime();
				$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

				if ( ! isset( $data['campaign_meta'][ $campID ] ) ) {
					return;
				}
				if ( $data['campaign_meta'][ $campID ]['end_time'] < $date_obj->getTimestamp() ) {
					return;
				}

				if ( $campaigns['properties']['notice'] === 'all' || ( $campaigns['properties']['notice'] === 'custom' && is_object( $post ) && ( isset( $campaigns['properties']['notice_pages'] ) && is_array( $campaigns['properties']['notice_pages'] ) && in_array( $post->ID, $campaigns['properties']['notice_pages'] ) ) || ( isset( $campaigns['properties']['notice_products'] ) && is_array( $campaigns['properties']['notice_products'] ) && in_array( $post->ID, $campaigns['properties']['notice_products'] ) ) ) ) {

					$content = $campaigns['properties']['success_message'];

					/** Added to counter check if notice already added */
					$success_msg = $content;
					$success_msg = str_replace( '{{countdown_timer}}', '', $success_msg );
					$timer_data  = array(
						'label_color'     => '#dd3333',
						'label_font'      => '13',
						'end_timestamp'   => $data['campaign_meta'][ $campID ]['end_time'],
						'start_timestamp' => $data['campaign_meta'][ $campID ]['start_time'],
						'full_instance'   => $data['coupons'][ $campID ],
					);
					$get_timer   = WCCT_Core()->appearance->wcct_maybe_parse_timer( $campID, $timer_data, 'info_notice' );
					$content     = str_replace( '{{countdown_timer}}', $get_timer, $content );
				}
			}

			if ( $content === '' ) {
				return;
			}
			if ( empty( $campaigns['properties']['is_checkout_button'] ) ) {
				$message = $content;

			} else {
				$message = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( wc_get_page_permalink( 'checkout' ) ), esc_html__( 'Proceed to checkout', 'woocommerce' ), $content );

			}

			/** Checking if notice already added, then return */
			$wc_notices = wc_get_notices();
			if ( is_array( $wc_notices ) && count( $wc_notices ) > 0 && isset( $wc_notices['success'] ) && is_array( $wc_notices['success'] ) && count( $wc_notices['success'] ) > 0 ) {
				foreach ( $wc_notices['success'] as $val ) {
					if ( is_array( $val ) ) {//for woocommerce 3.9
						if ( isset( $val['notice'] ) && strpos( $val['notice'], $success_msg ) !== false ) {
							return;
						}
					} else {
						if ( strpos( $val, $success_msg ) !== false ) {
							return;
						}
					}
				}
			}
			if ( ! in_array( $campID, $this->unique_info_notices ) ) {
				wc_add_notice( $message, 'success' );
			}

			array_push( $this->notices, $coupon );
			array_push( $this->unique_info_notices, $campID );
		}
	}

	public function wcct_maybe_load_notices_coupons_on_cart_item_check( $current_query ) {
		if ( is_object( WC()->session ) ) {
			$get_prev_session = WC()->session->get( '_wcct_cart_coupons_data_' );

			if ( is_array( $get_prev_session ) ) {
				foreach ( $get_prev_session as $campaigns ) {
					if ( ! in_array( $campaigns['coupons'], $this->notices ) ) {
						$this->wcct_register_info_notice( $campaigns['coupons'], $campaigns );
					}
				}
			}
		}
	}

	public function woocommerce_coupon_is_valid( $is_valid, $coupon_obj ) {
		$get_prev_session  = WC()->session->get( '_wcct_cart_coupons_data_' );
		$iterations_result = $is_valid;

		if ( is_array( $get_prev_session ) ) {

			foreach ( $get_prev_session as $campaigns ) {

				if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
					$coupon_id = $coupon_obj->get_id();
				} else {
					$coupon_id = $coupon_obj->id;
				}
				if ( $coupon_id == '' ) {
					$iterations_result = true;
				}

				if ( isset( $campaigns['running_status'] ) && 'scheduled' === $campaigns['running_status'] ) {
					continue;
				}

				if ( $coupon_id == $campaigns['coupons'] ) {
					$iterations_result = $this->validate_and_remove_coupon_after_finish( $coupon_obj, $campaigns );

				} else {
					continue;
				}

				if ( $iterations_result === true ) {
					break;
				}
			}
		}

		return $iterations_result;
	}


	private function validate_and_remove_coupon_after_finish( $coupon_obj, $campaigns ) {
		$campID = $campaigns['campaign'];

		if ( $campaigns['properties']['is_expire'] == 'no' ) {
			return true;
		}

		if ( $campID !== 0 ) {
			$data = WCCT_Core()->public->get_single_campaign_instance( $campID, 0, true );

			$date_obj = new DateTime();
			$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
			if ( isset( $data['campaign_meta'][ $campID ] ) && $data['campaign_meta'][ $campID ]['end_time'] < $date_obj->getTimestamp() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $error
	 * @param $code
	 * @param WC_Coupon $coupon_object
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function wcct_woocommerce_coupon_error( $error, $code, $coupon_object ) {
		if ( is_null( $coupon_object ) ) {
			return $error;
		}

		if ( $code !== $coupon_object::E_WC_COUPON_INVALID_REMOVED && $code !== $coupon_object::E_WC_COUPON_INVALID_FILTERED ) {
			return $error;
		}

		$get_prev_session = WC()->session->get( '_wcct_cart_coupons_data_' );

		if ( is_array( $get_prev_session ) ) {

			foreach ( $get_prev_session as $campaigns ) {

				if ( $campaigns['properties']['is_expire'] == 'no' ) {
					continue;
				}

				if ( isset( $campaigns['running_status'] ) && 'scheduled' === $campaigns['running_status'] ) {
					continue;
				}
				if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
					$coupon_id = $coupon_object->get_id();
				} else {
					$coupon_id = $coupon_object->id;
				}

				if ( $coupon_id == $campaigns['coupons'] ) {
					$campID = $campaigns['campaign'];

					if ( $campID !== 0 ) {

						$data = WCCT_Core()->public->get_single_campaign_instance( $campID, 0, true );

						$date_obj = new DateTime();

						$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

						if ( isset( $data['campaign_meta'][ $campID ] ) && $data['campaign_meta'][ $campID ]['end_time'] < $date_obj->getTimestamp() ) {
							$error = $campaigns['properties']['failure_message'];
						}
					}
				}
			}
		}

		return $error;
	}

	/**
	 * Handling of native success message of wooCommerce while coupon applied successfully.
	 * We need to hide that success message and show our info notice instead.
	 *
	 * @param String $error
	 * @param Integer $code
	 * @param WC_Coupon $coupon_object
	 *
	 * @return string Modified|blank
	 *
	 */
	public function wcct_woocommerce_coupon_message( $error, $code, $coupon_object ) {

		if ( $code !== $coupon_object::WC_COUPON_SUCCESS ) {
			return $error;
		}
		$get_prev_session = WC()->session->get( '_wcct_cart_coupons_data_' );

		if ( is_array( $get_prev_session ) ) {

			foreach ( $get_prev_session as $campaigns ) {

				if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
					$coupon_id = $coupon_object->get_id();
				} else {
					$coupon_id = $coupon_object->id;
				}

				if ( $coupon_id == $campaigns['coupons'] ) {
					$campID = $campaigns['campaign'];
					if ( isset( $campaigns['running_status'] ) && 'scheduled' === $campaigns['running_status'] ) {
						continue;
					}
					if ( $campaigns['properties']['is_expire'] == 'no' ) {
						$error = '';
					} else {
						if ( $campID !== 0 ) {

							$data = WCCT_Core()->public->get_single_campaign_instance( $campID, 0, true );

							$date_obj = new DateTime();

							$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

							if ( isset( $data['campaign_meta'][ $campID ] ) && $data['campaign_meta'][ $campID ]['end_time'] > $date_obj->getTimestamp() ) {
								if ( isset( $campaigns['properties']['notice_after_add_to_cart'] ) && $campaigns['properties']['notice_after_add_to_cart'] === 'yes' ) {
									$error = $this->generate_coupon_message( $campaigns, $campID );

								} else {
									$error = '';
								}
							}
						}
					}
				}
			}
		}

		return $error;
	}

	private function generate_coupon_message( $campaign_data, $campaignID ) {
		if ( $campaignID !== 0 ) {

			$data     = WCCT_Core()->public->get_single_campaign_instance( $campaignID, 0, true );
			$date_obj = new DateTime();
			$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

			if ( ! isset( $data['campaign_meta'][ $campaignID ] ) ) {
				return;
			}
			if ( $data['campaign_meta'][ $campaignID ]['end_time'] < $date_obj->getTimestamp() ) {
				return;
			}

			$content    = $campaign_data['properties']['success_message'];
			$timer_data = array(
				'label_color'     => '#dd3333',
				'label_font'      => '13',
				'end_timestamp'   => $data['campaign_meta'][ $campaignID ]['end_time'],
				'start_timestamp' => $data['campaign_meta'][ $campaignID ]['start_time'],
				'full_instance'   => $data['coupons'][ $campaignID ],
			);
			$get_timer  = WCCT_Core()->appearance->wcct_maybe_parse_timer( $campaignID, $timer_data, 'info_notice' );
			$content    = str_replace( '{{countdown_timer}}', $get_timer, $content );
		}

		if ( $content == '' ) {
			return '';
		}

		if ( empty( $campaign_data['properties']['is_checkout_button'] ) ) {
			$message = $content;

		} else {
			$message = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( wc_get_page_permalink( 'checkout' ) ), esc_html__( 'Proceed to checkout', 'woocommerce' ), $content );

		}
		array_push( $this->unique_info_notices, $campaignID );

		return $message;
	}

	public function maybe_add_coupon_by_url() {
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'wcct-apply_coupon' ) {

			$campID = $_GET['id'];
			$data   = WCCT_Core()->public->get_single_campaign_instance( $campID, 0, true );

			/**
			 * Filter only running
			 */
			$coupons_data = $this->wcct_get_all_coupons( $data );

			//filter running_Campaigns
			foreach ( $coupons_data as $key => $value ) {

				if ( isset( $value['running_status'] ) && 'scheduled' === $value['running_status'] ) {
					unset( $coupons_data[ $key ] );
				}
			}

			$coupons = $original_coupons = wp_list_pluck( $coupons_data, 'coupons' );
			$coupons = WCCT_Common::array_flatten( $coupons );

			/**
			 * Iterate over the coupons to make them applied
			 */
			foreach ( $coupons as $coupon ) {

				$is_hide_errors = $coupons_data[ $campID ]['properties']['hide_errors'];

				$coupon_obj = get_post( $coupon );

				// Sanitize coupon code
				$coupon_name = $coupon_obj->post_title;
				$coupon_code = apply_filters( 'woocommerce_coupon_code', $coupon_name );

				// Get the coupon
				$the_coupon = new WC_Coupon( $coupon_code );

				//checking if errors needs to be shown to user or not

				if ( WC()->cart->cart_contents_total == 0 ) {

					$cookie_name = 'wcct_last_notice';
					setcookie( $cookie_name, $coupons_data[ $campID ]['properties']['empty_cart_message'], time() + ( HOUR_IN_SECONDS * 1 ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
				} else {

					if ( $is_hide_errors == 'yes' ) {
						/**
						 * @todo WC_Coupon::is_valid() is deprecated and need to be replaced with the correct
						 */
						/**
						 * if errors are hidden, checking the coupons first and then safely applying
						 */
						if ( ! WC()->cart->has_discount( $coupon_code ) && $the_coupon->is_valid() ) {
							WC()->cart->add_discount( $coupon_code );
						}
					} else {

						//applying coupon and passing exception checks to WooCommerce
						WC()->cart->add_discount( $coupon_code );
					}
				}
			}

			wp_redirect( apply_filters( 'wcct_redirect_after_coupon_by_url', wc_get_cart_url() ) );
			exit;

		}

	}

	/**
	 * Hooked over `shutdown`
	 * Iterate over all the notices found in session var and unset our notices, so that they don't go to wp session mechanism
	 */
	public function woocommerce_validate_session_for_notices() {
		if ( WC()->session == null ) {
			return;
		}
		$cloned_notices = $all_notices = WC()->session->get( 'wc_notices', array() );
		$is_modified    = false;

		if ( isset( $all_notices['success'] ) ) {

			foreach ( $all_notices['success'] as $key => $notice ) {
				if ( is_array( $notice ) ) {//for woocommerce 3.9
					if ( isset( $notice['notice'] ) && strpos( $notice['notice'], 'wcct_countdown_timer' ) ) {
						unset( $cloned_notices['success'][ $key ] );
						$is_modified = true;
					}
				} else {
					if ( strpos( $notice, 'wcct_countdown_timer' ) ) {
						unset( $cloned_notices['success'][ $key ] );
						$is_modified = true;
					}
				}
			}
		}

		if ( $is_modified ) {
			WC()->session->set( 'wc_notices', $cloned_notices );
		}

	}

	public function remove_coupon_notice_on_cart_and_checkout( $is_restrict, $query ) {
		if ( is_cart() || is_checkout() ) {
			return true;
		}

		return $is_restrict;
	}

	/**
	 * @hooked over `woocommerce_checkout_process`
	 * Unhook __CLASS__(wcct_maybe_load_notices_coupons_on_cart_item_check) to prevent notice to be register while checkout
	 */
	public function prevent_coupon_notice_register() {
		remove_action( 'woocommerce_check_cart_items', array( $this, 'wcct_maybe_load_notices_coupons_on_cart_item_check' ), 999 );
	}


	public function re_apply_coupons( $cart_updated ) {
		if ( false === $cart_updated ) {
			return $cart_updated;
		}

		$get_prev_session = WC()->session->get( '_wcct_cart_coupons_data_' );

		if ( is_array( $get_prev_session ) ) {
			WC()->cart->calculate_totals();
			foreach ( $get_prev_session as $campaigns ) {

				$campID = $campaigns['campaign'];
				if ( isset( $campaigns['running_status'] ) && 'scheduled' === $campaigns['running_status'] ) {
					continue;
				}
				if ( $campaigns['properties']['is_expire'] == 'no' ) {
					$error = '';
				} else {
					if ( $campID !== 0 ) {

						$data     = WCCT_Core()->public->get_single_campaign_instance( $campID, 0, true );
						$date_obj = new DateTime();
						$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

						if ( isset( $data['campaign_meta'][ $campID ] ) && $data['campaign_meta'][ $campID ]['end_time'] > $date_obj->getTimestamp() ) {
							$coupon         = $campaigns['coupons'];
							$is_hide_errors = $campaigns['properties']['hide_errors'];
							$coupon_obj     = get_post( $coupon );

							if ( ! $coupon_obj instanceof WP_Post ) {
								continue;
							}
							// Sanitize coupon code
							$coupon_name = $coupon_obj->post_title;
							$coupon_code = apply_filters( 'woocommerce_coupon_code', $coupon_name );

							// Get the coupon
							$the_coupon = new WC_Coupon( $coupon_code );

							//checking if coupon is valid and not applied already inside the cart
							if ( $is_hide_errors == 'yes' ) {

								if ( ! WC()->cart->has_discount( $coupon_code ) && $the_coupon->is_valid() ) {
									WC()->cart->add_discount( $coupon_code );
								}
							} else {

								WC()->cart->add_discount( $coupon_code );
							}
						}
					}
				}
			}
			WC()->cart->calculate_totals();
		}

		return $cart_updated;
	}

}

if ( class_exists( 'WCCT_XL_Coupons' ) ) {
	WCCT_Core::register( 'coupons', 'WCCT_XL_Coupons' );
}
