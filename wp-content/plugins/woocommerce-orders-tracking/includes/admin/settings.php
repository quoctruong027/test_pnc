<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_SETTINGS {
	private $settings;
	private $schedule_send_emails;
	private $shipping_countries;
	protected $language;
	protected $languages;
	protected $default_language;
	protected $languages_data;

	public function __construct() {
		$this->settings         = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
		$this->languages        = array();
		$this->languages_data   = array();
		$this->default_language = '';
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
		add_action( 'admin_init', array( $this, 'check_update' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ) );
		add_action( 'wp_ajax_wotv_admin_add_new_shipping_carrier', array(
			$this,
			'wotv_admin_add_new_shipping_carrier'
		) );
		add_action( 'wp_ajax_wotv_admin_edit_shipping_carrier', array( $this, 'wotv_admin_edit_shipping_carrier' ) );
		add_action( 'wp_ajax_wotv_admin_delete_shipping_carrier', array(
			$this,
			'wotv_admin_delete_shipping_carrier'
		) );
		add_action( 'wp_ajax_wotv_admin_choose_default_shipping_carrier', array(
			$this,
			'wotv_admin_choose_default_shipping_carrier'
		) );
		add_action( 'media_buttons', array( $this, 'preview_emails_button' ) );
		add_action( 'wp_ajax_wot_preview_emails', array( $this, 'wot_preview_emails' ) );
		add_action( 'wp_ajax_wot_test_connection_paypal', array( $this, 'wot_test_connection_paypal' ) );
		add_action( 'wp_ajax_woo_orders_tracking_search_page', array( $this, 'search_page' ) );
		add_action( 'wp_ajax_woo_orders_tracking_send_test_sms', array( $this, 'send_test_sms' ) );
	}

	public static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::set( $name, $set_name );
	}

	public function send_test_sms() {
		$text               = isset( $_POST['text'] ) ? sanitize_text_field( $_POST['text'] ) : '';
		$provider           = isset( $_POST['provider'] ) ? sanitize_text_field( $_POST['provider'] ) : '';
		$from_number        = isset( $_POST['from_number'] ) ? sanitize_text_field( $_POST['from_number'] ) : '';
		$app_id             = isset( $_POST['app_id'] ) ? sanitize_text_field( $_POST['app_id'] ) : '';
		$app_token          = isset( $_POST['app_token'] ) ? sanitize_text_field( $_POST['app_token'] ) : '';
		$powerpack          = isset( $_POST['powerpack'] ) ? sanitize_text_field( $_POST['powerpack'] ) : '';
		$bitly_access_token = isset( $_POST['bitly_access_token'] ) ? sanitize_text_field( $_POST['bitly_access_token'] ) : '';
		$send_test_sms      = isset( $_POST['send_test_sms'] ) ? sanitize_text_field( $_POST['send_test_sms'] ) : '';
		$response           = array(
			'status'        => 'success',
			'message'       => '',
			'message_title' => '',
		);
		if ( $text ) {
			$shortlink = get_permalink( $this->settings->get_params( 'service_tracking_page' ) );
			if ( ! $shortlink ) {
				$shortlink = get_site_url();
			}
			if ( $bitly_access_token ) {
				$bitly             = new VI_WOOCOMMERCE_ORDERS_TRACKING_BITLY( $bitly_access_token );
				$shortlink_request = $bitly->get_link( $shortlink );
				if ( $shortlink_request['status'] === 'success' ) {
					$shortlink = $shortlink_request['data']['link'];
				}
			}
			$user = wp_get_current_user();
			$text = str_replace( array(
				'{tracking_number}',
				'{tracking_url}',
				'{carrier_name}',
				'{order_id}',
				'{billing_first_name}',
				'{billing_last_name}'
			), array(
				'Test_tracking_number',
				$shortlink,
				'UPS',
				'12345',
				empty( $user->display_name ) ? 'John' : $user->display_name,
				empty( $user->display_name ) ? 'John' : $user->display_name
			), $text );
			switch ( $provider ) {
				case 'twilio':
					$sms_object             = new VI_WOOCOMMERCE_ORDERS_TRACKING_TWILIO( $app_id, $app_token );
					$sms_response           = $sms_object->send( $from_number, $send_test_sms, $text );
					$response['sms_status'] = $sms_response['status'];
					if ( $sms_response['status'] === 'error' ) {
						$response['message']       = $sms_response['data'];
						$response['message_title'] = esc_html__( 'Failed sending SMS message', 'woocommerce-orders-tracking' );
					} elseif ( in_array( $sms_response['data']['status'], array( 'failed', 'undelivered' ) ) ) {
						$response['sms_status']    = 'error';
						$response['message']       = isset( $sms_response['data']['error_message'] ) ? $sms_response['data']['error_message'] : '';
						$response['message_title'] = esc_html__( 'Failed sending SMS message', 'woocommerce-orders-tracking' );
					} else {
						$response['message_title'] = esc_html__( 'Send SMS message successfully', 'woocommerce-orders-tracking' );
						$response['message']       = empty( $sms_response['body'] ) ? $text : $sms_response['body'];
					}
					break;
				case 'nexmo':
					$sms_object             = new VI_WOOCOMMERCE_ORDERS_TRACKING_NEXMO( $app_id, $app_token );
					$sms_response           = $sms_object->send( $from_number, $send_test_sms, $text );
					$response['sms_status'] = $sms_response['status'];
					if ( $sms_response['status'] === 'error' ) {
						$response['message_title'] = esc_html__( 'Failed sending SMS message', 'woocommerce-orders-tracking' );
						$response['message']       = $sms_response['data'];
					} else {
						$response['message_title'] = esc_html__( 'Send SMS message successfully', 'woocommerce-orders-tracking' );
						$response['message']       = $text;
					}
					break;
				case 'plivo':
					$sms_object             = new VI_WOOCOMMERCE_ORDERS_TRACKING_PLIVO( $app_id, $app_token );
					$sms_response           = $sms_object->send( $powerpack, $send_test_sms, $text );
					$response['sms_status'] = $sms_response['status'];
					if ( $sms_response['status'] === 'error' ) {
						$response['message_title'] = esc_html__( 'Failed sending SMS message', 'woocommerce-orders-tracking' );
						$response['message']       = $sms_response['data'];
					} else {
						$response['message_title'] = esc_html__( 'Send SMS message successfully', 'woocommerce-orders-tracking' );
						$response['message']       = $text;
					}
					break;
				default:

			}
		} else {
			$response['status']  = 'error';
			$response['message'] = esc_html__( 'Empty message', 'woocommerce-orders-tracking' );
		}
		wp_send_json( $response );
	}

	public function check_update() {
		if ( class_exists( 'VillaTheme_Plugin_Check_Update' ) ) {
			$setting_url = admin_url( 'admin.php?page=woocommerce-orders-tracking' );
			$key         = $this->settings->get_params( 'key' );
			new VillaTheme_Plugin_Check_Update (
				VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION,                    // current version
				'https://villatheme.com/wp-json/downloads/v3',  // update path
				'woocommerce-orders-tracking/woocommerce-orders-tracking.php',                  // plugin file slug
				'woocommerce-orders-tracking', '25799', $key, $setting_url
			);
			new VillaTheme_Plugin_Updater( 'woocommerce-orders-tracking/woocommerce-orders-tracking.php', 'woocommerce-orders-tracking', $setting_url );
		}
	}

	public function admin_menu() {
		add_menu_page(
			esc_html__( 'WooCommerce Orders Tracking settings', 'woocommerce-orders-tracking' ),
			esc_html__( 'Orders Tracking', 'woocommerce-orders-tracking' ),
			'manage_options',
			'woocommerce-orders-tracking',
			array( $this, 'settings_callback' ),
			'dashicons-location',
			'2'
		);
	}


	public function save_settings() {
		global $pagenow;
		global $woo_orders_tracking_settings;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-orders-tracking' ) {
			/*wpml*/
			if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
				global $sitepress;
				$default_lang           = $sitepress->get_default_language();
				$this->default_language = $default_lang;
				$languages              = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
				$this->languages_data   = $languages;
				if ( count( $languages ) ) {
					foreach ( $languages as $key => $language ) {
						if ( $key != $default_lang ) {
							$this->languages[] = $key;
						}
					}
				}
			} elseif ( class_exists( 'Polylang' ) ) {
				/*Polylang*/
				$languages    = pll_languages_list();
				$default_lang = pll_default_language( 'slug' );
				foreach ( $languages as $language ) {
					if ( $language == $default_lang ) {
						continue;
					}
					$this->languages[] = $language;
				}
			}
			if ( isset( $_POST['woo_orders_tracking_check_key'] ) ) {
				delete_transient( '_site_transient_update_plugins' );
				delete_transient( 'villatheme_item_25799' );
				delete_option( 'woocommerce-orders-tracking_messages' );
			}
			if ( isset( $_POST['_vi_wot_setting_nonce'] ) && wp_verify_nonce( $_POST['_vi_wot_setting_nonce'], 'vi_wot_setting_action_nonce' ) ) {
				$args                                             = $woo_orders_tracking_settings;
				$args['service_carrier_enable']                   = isset( $_POST['woo-orders-tracking-settings']['service_carrier']['service_carrier_enable'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['service_carrier']['service_carrier_enable'] ) : '';
				$args['service_carrier_type']                     = isset( $_POST['woo-orders-tracking-settings']['service_carrier']['service_carrier_type'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['service_carrier']['service_carrier_type'] ) : '';
				$args['service_tracking_page']                    = isset( $_POST['woo-orders-tracking-settings']['service_carrier']['service_tracking_page'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['service_carrier']['service_tracking_page'] ) : '';
				$args['service_cache_request']                    = isset( $_POST['woo-orders-tracking-settings']['service_carrier']['service_cache_request'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['service_carrier']['service_cache_request'] ) : '';
				$args['service_carrier_api_key']                  = isset( $_POST['woo-orders-tracking-settings']['service_carrier']['service_carrier_api_key'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['service_carrier']['service_carrier_api_key'] ) : '';
				$args['service_add_tracking_if_not_exist']        = isset( $_POST['woo-orders-tracking-settings']['service_carrier']['service_add_tracking_if_not_exist'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['service_carrier']['service_add_tracking_if_not_exist'] ) : '';
				$args['email_woo_enable']                         = isset( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_enable'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_enable'] ) : '';
				$args['email_woo_status']                         = isset( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_status'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_status'] ) : array();
				$args['email_woo_position']                       = isset( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_position'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_position'] ) : 'after_order_table';
				$args['email_woo_html']                           = isset( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_html'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_html'] ) : '';
				$args['email_woo_tracking_list_html']             = isset( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_tracking_list_html'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_tracking_list_html'] ) : '';
				$args['email_woo_tracking_number_html']           = isset( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_tracking_number_html'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_tracking_number_html'] ) : '';
				$args['email_woo_tracking_carrier_html']          = isset( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_tracking_carrier_html'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email_woo']['email_woo_tracking_carrier_html'] ) : '';
				$args['email_send_all_order_items']               = isset( $_POST['woo-orders-tracking-settings']['email']['email_send_all_order_items'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email']['email_send_all_order_items'] ) : '';
				$args['email_send_after_aliexpress_order_synced'] = isset( $_POST['woo-orders-tracking-settings']['email']['email_send_after_aliexpress_order_synced'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email']['email_send_after_aliexpress_order_synced'] ) : '';
				$args['email_column_tracking_number']             = isset( $_POST['woo-orders-tracking-settings']['email']['email_column_tracking_number'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email']['email_column_tracking_number'] ) : '';
				$args['email_column_carrier_name']                = isset( $_POST['woo-orders-tracking-settings']['email']['email_column_carrier_name'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email']['email_column_carrier_name'] ) : '';
				$args['email_column_tracking_url']                = isset( $_POST['woo-orders-tracking-settings']['email']['email_column_tracking_url'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email']['email_column_tracking_url'] ) : '';
				$args['email_time_send']                          = isset( $_POST['woo-orders-tracking-settings']['email']['email_time_send'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email']['email_time_send'] ) : '';
				$args['email_time_send_type']                     = isset( $_POST['woo-orders-tracking-settings']['email']['email_time_send_type'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email']['email_time_send_type'] ) : '';
				$args['email_number_send']                        = isset( $_POST['woo-orders-tracking-settings']['email']['email_number_send'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email']['email_number_send'] ) : '';
				$args['email_subject']                            = isset( $_POST['woo-orders-tracking-settings']['email']['email_subject'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email']['email_subject'] ) : '';
				$args['email_template']                           = isset( $_POST['woo-orders-tracking-settings']['email_template'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email_template'] ) : '';
				$args['email_heading']                            = isset( $_POST['woo-orders-tracking-settings']['email']['email_heading'] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email']['email_heading'] ) : '';
				$args['email_content']                            = isset( $_POST['woo-orders-tracking-settings']['email']['email_content'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email']['email_content'] ) : '';
				$args['paypal_sandbox_enable']                    = isset( $_POST['woo-orders-tracking-settings']['paypal']['paypal_sandbox_enable'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['paypal']['paypal_sandbox_enable'] ) : array();
				$args['paypal_method']                            = isset( $_POST['woo-orders-tracking-settings']['paypal']['paypal_method'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['paypal']['paypal_method'] ) : array();
				$args['paypal_client_id_live']                    = isset( $_POST['woo-orders-tracking-settings']['paypal']['paypal_client_id_live'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['paypal']['paypal_client_id_live'] ) : array();
				$args['paypal_client_id_sandbox']                 = isset( $_POST['woo-orders-tracking-settings']['paypal']['paypal_client_id_sandbox'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['paypal']['paypal_client_id_sandbox'] ) : array();
				$args['paypal_secret_live']                       = isset( $_POST['woo-orders-tracking-settings']['paypal']['paypal_secret_live'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['paypal']['paypal_secret_live'] ) : array();
				$args['paypal_secret_sandbox']                    = isset( $_POST['woo-orders-tracking-settings']['paypal']['paypal_secret_sandbox'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['paypal']['paypal_secret_sandbox'] ) : array();
				$args['sms_from_number']                          = isset( $_POST['woo-orders-tracking-settings']['sms_from_number'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_from_number'] ) : '';
				$args['sms_text']                                 = isset( $_POST['woo-orders-tracking-settings']['sms_text'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_text'] ) : '';
				$args['sms_text_new']                             = isset( $_POST['woo-orders-tracking-settings']['sms_text_new'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_text_new'] ) : '';
				$args['sms_provider']                             = isset( $_POST['woo-orders-tracking-settings']['sms_provider'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_provider'] ) : '';
				$args['sms_twilio_app_id']                        = isset( $_POST['woo-orders-tracking-settings']['sms_twilio_app_id'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_twilio_app_id'] ) : '';
				$args['sms_twilio_app_token']                     = isset( $_POST['woo-orders-tracking-settings']['sms_twilio_app_token'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_twilio_app_token'] ) : '';
				$args['sms_nexmo_app_id']                         = isset( $_POST['woo-orders-tracking-settings']['sms_nexmo_app_id'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_nexmo_app_id'] ) : '';
				$args['sms_nexmo_app_token']                      = isset( $_POST['woo-orders-tracking-settings']['sms_nexmo_app_token'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_nexmo_app_token'] ) : '';
				$args['sms_nexmo_unicode']                        = isset( $_POST['woo-orders-tracking-settings']['sms_nexmo_unicode'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_nexmo_unicode'] ) : '';
				$args['sms_plivo_app_id']                         = isset( $_POST['woo-orders-tracking-settings']['sms_plivo_app_id'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_plivo_app_id'] ) : '';
				$args['sms_plivo_app_token']                      = isset( $_POST['woo-orders-tracking-settings']['sms_plivo_app_token'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_plivo_app_token'] ) : '';
				$args['sms_plivo_powerpack_uuid']                 = isset( $_POST['woo-orders-tracking-settings']['sms_plivo_powerpack_uuid'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['sms_plivo_powerpack_uuid'] ) : '';
				$args['bitly_access_token']                       = isset( $_POST['woo-orders-tracking-settings']['bitly_access_token'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['bitly_access_token'] ) : '';
				$args['send_test_sms']                            = isset( $_POST['woo-orders-tracking-settings']['send_test_sms'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['send_test_sms'] ) : '';

				$args['tracking_form_recaptcha_enable']     = isset( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_enable'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_enable'] ) : '';
				$args['tracking_form_recaptcha_version']    = isset( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_version'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_version'] ) : '';
				$args['tracking_form_recaptcha_site_key']   = isset( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_site_key'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_site_key'] ) : '';
				$args['tracking_form_recaptcha_secret_key'] = isset( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_secret_key'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_secret_key'] ) : '';
				$args['tracking_form_recaptcha_theme']      = isset( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_theme'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['tracking_form_recaptcha_theme'] ) : '';
				$args['change_order_status']                = isset( $_POST['woo_orders_tracking_change_order_status'] ) ? self::stripslashes_deep( $_POST['woo_orders_tracking_change_order_status'] ) : '';
				$args['key']                                = isset( $_POST['woo-orders-tracking-settings']['key'] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['key'] ) : '';
				if ( count( $this->languages ) ) {
					foreach ( $this->languages as $key => $value ) {
						$args[ 'email_template_' . $value ]                  = isset( $_POST['woo-orders-tracking-settings'][ 'email_template_' . $value ] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings'][ 'email_template_' . $value ] ) : '';
						$args[ 'email_subject_' . $value ]                   = isset( $_POST['woo-orders-tracking-settings']['email'][ 'email_subject_' . $value ] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email'][ 'email_subject_' . $value ] ) : '';
						$args[ 'email_heading_' . $value ]                   = isset( $_POST['woo-orders-tracking-settings']['email'][ 'email_heading_' . $value ] ) ? self::stripslashes( $_POST['woo-orders-tracking-settings']['email'][ 'email_heading_' . $value ] ) : '';
						$args[ 'email_content_' . $value ]                   = isset( $_POST['woo-orders-tracking-settings']['email'][ 'email_content_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email'][ 'email_content_' . $value ] ) : '';
						$args[ 'email_column_tracking_number_' . $value ]    = isset( $_POST['woo-orders-tracking-settings']['email'][ 'email_column_tracking_number_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email'][ 'email_column_tracking_number_' . $value ] ) : '';
						$args[ 'email_column_carrier_name_' . $value ]       = isset( $_POST['woo-orders-tracking-settings']['email'][ 'email_column_carrier_name_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email'][ 'email_column_carrier_name_' . $value ] ) : '';
						$args[ 'email_column_tracking_url_' . $value ]       = isset( $_POST['woo-orders-tracking-settings']['email'][ 'email_column_tracking_url_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email'][ 'email_column_tracking_url_' . $value ] ) : '';
						$args[ 'sms_from_number_' . $value ]                 = isset( $_POST['woo-orders-tracking-settings'][ 'sms_from_number_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings'][ 'sms_from_number_' . $value ] ) : '';
						$args[ 'sms_text_' . $value ]                        = isset( $_POST['woo-orders-tracking-settings'][ 'sms_text_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings'][ 'sms_text_' . $value ] ) : '';
						$args[ 'sms_text_new_' . $value ]                    = isset( $_POST['woo-orders-tracking-settings'][ 'sms_text_new_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings'][ 'sms_text_new_' . $value ] ) : '';
						$args[ 'email_woo_html_' . $value ]                  = isset( $_POST['woo-orders-tracking-settings']['email_woo'][ 'email_woo_html_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email_woo'][ 'email_woo_html_' . $value ] ) : '';
						$args[ 'email_woo_tracking_list_html_' . $value ]    = isset( $_POST['woo-orders-tracking-settings']['email_woo'][ 'email_woo_tracking_list_html_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email_woo'][ 'email_woo_tracking_list_html_' . $value ] ) : '';
						$args[ 'email_woo_tracking_number_html_' . $value ]  = isset( $_POST['woo-orders-tracking-settings']['email_woo'][ 'email_woo_tracking_number_html_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email_woo'][ 'email_woo_tracking_number_html_' . $value ] ) : '';
						$args[ 'email_woo_tracking_carrier_html_' . $value ] = isset( $_POST['woo-orders-tracking-settings']['email_woo'][ 'email_woo_tracking_carrier_html_' . $value ] ) ? self::stripslashes_deep( $_POST['woo-orders-tracking-settings']['email_woo'][ 'email_woo_tracking_carrier_html_' . $value ] ) : '';
					}
				}
				update_option( 'woo_orders_tracking_settings', $args );
				$woo_orders_tracking_settings = $args;
				$this->settings               = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance( true );
			}
		}
	}


	private static function stripslashes( $value ) {
		return sanitize_text_field( stripslashes( $value ) );
	}

	private static function stripslashes_deep( $value ) {
		if ( is_array( $value ) ) {
			$value = array_map( 'stripslashes_deep', $value );
		} else {
			$value = wp_kses_post( stripslashes( $value ) );
		}

		return $value;
	}


	public function settings_callback() {
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'WooCommerce Orders Tracking settings', 'woocommerce-orders-tracking' ); ?></h2>
            <div class="vi-ui raised">
                <form action="" class="vi-ui form" method="post">
					<?php
					wp_nonce_field( 'vi_wot_setting_action_nonce', '_vi_wot_setting_nonce' );
					?>
                    <div class="vi-ui vi-ui-main top tabular attached menu ">
                        <a class="item active" data-tab="shipping_carriers">
							<?php esc_html_e( 'Shipping Carriers', 'woocommerce-orders-tracking' ) ?>
                        </a>
                        <a class="item " data-tab="email">
							<?php esc_html_e( 'Email', 'woocommerce-orders-tracking' ) ?>
                        </a>
                        <a class="item " data-tab="email_woo">
							<?php esc_html_e( 'WooCommerce Email', 'woocommerce-orders-tracking' ) ?>
                        </a>
                        <a class="item " data-tab="sms">
							<?php esc_html_e( 'SMS', 'woocommerce-orders-tracking' ) ?>
                        </a>
                        <a class="item " data-tab="paypal">
							<?php esc_html_e( 'PayPal', 'woocommerce-orders-tracking' ) ?>
                        </a>
                        <a class="item" data-tab="tracking_service">
							<?php esc_html_e( 'Tracking Service', 'woocommerce-orders-tracking' ) ?>
                        </a>
                        <a class="item" data-tab="update">
							<?php esc_html_e( 'Update', 'woocommerce-orders-tracking' ) ?>
                        </a>
                    </div>
                    <div class="vi-ui bottom attached tab segment active" data-tab="shipping_carriers">
						<?php
						$this->shipping_carriers_settings();
						?>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="email">
						<?php
						$this->email_settings();
						?>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="email_woo">
						<?php
						$this->email_woo_settings();
						?>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="sms">
						<?php
						$this->sms_settings();
						?>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="paypal">
						<?php
						$this->paypal_settings();
						?>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="tracking_service">
						<?php
						$this->tracking_service_settings();
						?>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="update">
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="auto-update-key"><?php esc_html_e( 'Auto Update Key', 'woocommerce-orders-tracking' ) ?></label>
                                </th>
                                <td>
                                    <div class="fields">
                                        <div class="ten wide field">
                                            <input type="text"
                                                   name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[key]"
                                                   id="auto-update-key"
                                                   class="villatheme-autoupdate-key-field"
                                                   value="<?php echo esc_attr( $this->settings->get_params( 'key' ) ); ?>">
                                        </div>
                                        <div class="six wide field">
                                        <span class="vi-ui button green villatheme-get-key-button"
                                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                                              data-id="26062993"><?php esc_html_e( 'Get Key', 'woocommerce-orders-tracking' ) ?></span>
                                        </div>
                                    </div>
									<?php do_action( 'woocommerce-orders-tracking_key' ) ?>
                                    <p><?php echo wp_kses_post( __( 'Please enter the key that you get from <a target="_blank" href="https://villatheme.com/my-download">https://villatheme.com/my-download</a> to enable auto update for WooCommerce Orders Tracking plugin. Please read <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">guide</a>', 'woocommerce-orders-tracking' ) ) ?></p>
                                </td>
                            </tr>

                        </table>
                    </div>
                    <p class="<?php echo esc_attr( self::set( 'button-save-settings-container' ) ) ?>">
                        <button type="submit"
                                name="<?php echo esc_attr( self::set( 'settings-save-button' ) ) ?>"
                                class="<?php echo esc_attr( self::set( 'settings-save-button' ) ) ?> vi-ui button primary labeled icon">
                            <i class="icon save"></i>
							<?php esc_html_e( 'Save', 'woocommerce-orders-tracking' ); ?>
                        </button>
                        <button class="vi-ui button labeled icon" type="submit"
                                name="<?php echo esc_attr( self::set( 'check_key', true ) ) ?>"><i
                                    class="icon save"></i>
							<?php esc_html_e( 'Save & Check Key', 'woocommerce-orders-tracking' ); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
		<?php
		do_action( 'villatheme_support_woocommerce-orders-tracking' );
	}

	private function shipping_carriers_settings() {
		$countries = new WC_Countries();
		$countries = $countries->get_countries();
		?>
        <div class="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-overlay', 'hidden' ) ) ) ?>">
        </div>
        <div class="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-header' ) ) ) ?>">
            <div class="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-filter-wrap' ) ) ) ?>">
                <div class="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-filter-type-wrap' ) ) ) ?>">
                    <select name=""
                            id="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-filter-type' ) ) ) ?>"
                            class="vi-ui dropdown fluid <?php echo esc_attr( self::set( array( 'setting-shipping-carriers-filter-type' ) ) ) ?>">
                        <option value="all"><?php esc_html_e( 'All Carriers', 'woocommerce-orders-tracking' ) ?></option>
                        <option value="custom"><?php esc_html_e( 'Custom Carriers ', 'woocommerce-orders-tracking' ) ?></option>
                    </select>
                </div>
                <div class="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-filter-country-wrap' ) ) ) ?>">
                    <select name=""
                            id="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-filter-country' ) ) ) ?>"
                            class="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-filter-country' ) ) ) ?>">
                        <option value="all_country"
                                selected><?php esc_html_e( 'All Countries ', 'woocommerce-orders-tracking' ) ?></option>
                        <option value="Global"><?php esc_html_e( 'Global', 'woocommerce-orders-tracking' ) ?></option>
						<?php
						foreach ( $countries as $country_code => $country_name ) {
							?>
                            <option value="<?php echo esc_attr( $country_code ) ?>"><?php esc_html_e( $country_name ) ?></option>
							<?php
						}
						?>
                    </select>
                </div>
            </div>
            <div class="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-search-wrap' ) ) ) ?>">
                <span class="vi-ui button olive <?php echo esc_attr( self::set( array( 'setting-shipping-carriers-add-new-carrier' ) ) ) ?>"><?php esc_html_e( 'Add Carriers ', 'woocommerce-orders-tracking' ) ?></span>
                <input type="text"
                       placeholder="<?php echo esc_attr__( 'Search carrier name', 'woocommerce-orders-tracking' ) ?>"
                       class="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-filter-search' ) ) ) ?>">
            </div>
        </div>
        <div class="<?php echo esc_attr( self::set( array( 'setting-shipping-carriers-list-wrap' ) ) ) ?>">
        </div>
        <div class="<?php echo esc_attr( self::set( array(
			'setting-shipping-carriers-list-search-wrap',
			'hidden'
		) ) ) ?>">
        </div>
		<?php
	}

	private function email_settings() {
		if ( $this->schedule_send_emails ) {
			$orders = get_option( 'vi_wot_send_mails_for_import_csv_function_orders' );
			if ( $orders ) {
				$orders = vi_wot_json_decode( $orders );
				if ( count( $orders ) ) {
					$gmt_offset = intval( get_option( 'gmt_offset' ) );
					?>
                    <div class="vi-ui positive message">
                        <div class="header">
							<?php printf( wp_kses_post( __( 'Next schedule: <strong>%s</strong>', 'woocommerce-orders-tracking' ) ), date_i18n( 'F j, Y g:i:s A', ( $this->schedule_send_emails + HOUR_IN_SECONDS * $gmt_offset ) ) ); ?>
                        </div>
                        <p><?php printf( esc_html__( 'Order(s) to send next: %s', 'woocommerce-orders-tracking' ), implode( ',', array_splice( $orders, 0, $this->settings->get_params( 'email_number_send' ) ) ) ); ?></p>
                    </div>
					<?php
				}
			}
		}
		?>
        <div class="vi-ui positive message">
            <div>
				<?php esc_html_e( 'Settings for sending individual email if you check the send email checkbox(when editing order tracking/importing tracking/Webhooks)', 'woocommerce-orders-tracking' ) ?>
            </div>
        </div>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'email_send_all_order_items' ) ) ?>"><?php esc_html_e( 'Send tracking of whole order', 'woocommerce-orders-tracking' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox">
                        <input type="checkbox"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email][email_send_all_order_items]"
                               id="<?php echo esc_attr( self::set( 'email_send_all_order_items' ) ) ?>"
                               value="1" <?php checked( $this->settings->get_params( 'email_send_all_order_items' ), '1' ) ?>><label></label>
                    </div>
                    <p class="description"><?php esc_html_e( '{tracking_table} will include tracking of all items of an order instead of only changed one. Helpful when you add tracking number for single item of an order.', 'woocommerce-orders-tracking' ) ?></p>
                </td>
            </tr>
			<?php
			if ( class_exists( 'VI_WOOCOMMERCE_ALIDROPSHIP' ) && version_compare( VI_WOOCOMMERCE_ALIDROPSHIP_VERSION, '1.0.0.6', '>=' ) ) {
				?>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( self::set( 'email_send_after_aliexpress_order_synced' ) ) ?>"><?php esc_html_e( 'Send email when syncing AliExpress orders', 'woocommerce-orders-tracking' ) ?></label>
                    </th>
                    <td>
                        <div class="vi-ui toggle checkbox">
                            <input type="checkbox"
                                   name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email][email_send_after_aliexpress_order_synced]"
                                   id="<?php echo esc_attr( self::set( 'email_send_after_aliexpress_order_synced' ) ) ?>"
                                   value="1" <?php checked( $this->settings->get_params( 'email_send_after_aliexpress_order_synced' ), '1' ) ?>><label></label>
                        </div>
                        <p class="description"><?php esc_html_e( 'When syncing AliExpress orders, send tracking info email to customers if tracking number updated or tracking status switches to delivered', 'woocommerce-orders-tracking' ) ?></p>
                    </td>
                </tr>
				<?php
			}
			$email_template = $this->settings->get_params( 'email_template' );
			?>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'email_template' ) ) ?>"><?php esc_html_e( 'Email template', 'woocommerce-orders-tracking' ) ?></label>
                </th>
                <td>
					<?php
					$this->default_language_flag_html( 'email_template' );
					$email_templates = self::get_email_templates();
					?>
                    <select class="vi-ui dropdown fluid" id="<?php echo esc_attr( self::set( 'email_template' ) ) ?>"
                            type="text"
                            name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email_template]">
                        <option value=""><?php esc_html_e( 'None', 'woocommerce-orders-tracking' ) ?></option>
						<?php
						if ( count( $email_templates ) ) {
							foreach ( $email_templates as $email_template_k => $email_template_v ) {
								?>
                                <option value="<?php echo esc_attr( $email_template_v->ID ); ?>" <?php selected( $email_template_v->ID, $email_template ); ?>><?php echo esc_html( "(#{$email_template_v->ID}){$email_template_v->post_title}" ); ?></option>
								<?php
							}
						}
						?>
                    </select>
					<?php
					if ( count( $this->languages ) ) {
						foreach ( $this->languages as $key => $value ) {
							$email_template_lang = $this->settings->get_params( 'email_template', '', $value );
							$this->print_other_country_flag( 'email_template', $value );
							?>
                            <select class="vi-ui dropdown fluid"
                                    id="<?php echo esc_attr( self::set( "email_template_{$value}" ) ) ?>" type="text"
                                    name="<?php echo esc_attr( self::set( "settings[email_template_{$value}]" ) ) ?>">
                                <option value=""><?php esc_html_e( 'None', 'woocommerce-orders-tracking' ) ?></option>
								<?php
								if ( count( $email_templates ) ) {
									foreach ( $email_templates as $email_template_k => $email_template_v ) {
										?>
                                        <option value="<?php echo esc_attr( $email_template_v->ID ); ?>" <?php selected( $email_template_v->ID, $email_template_lang ); ?>><?php echo esc_html( "(#{$email_template_v->ID}){$email_template_v->post_title}" ); ?></option>
										<?php
									}
								}
								?>
                            </select>
							<?php
						}
					}
					?>
                    <p><?php _e( 'You can use <a href="https://1.envato.market/BZZv1" target="_blank">WooCommerce Email Template Customizer</a> or <a href="http://bit.ly/woo-email-template-customizer" target="_blank">Email Template Customizer for WooCommerce</a> to create and customize your own email template. If no email template is selected, below email will be used.', 'woocommerce-orders-tracking' ) ?></p>
					<?php
					if ( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::is_email_template_customizer_active() ) {
						?>
                        <p>
                            <a href="edit.php?post_type=viwec_template"
                               target="_blank"><?php esc_html_e( 'View all Email templates', 'woocommerce-orders-tracking' ) ?></a>
							<?php esc_html_e( 'or', 'woocommerce-orders-tracking' ) ?>
                            <a href="post-new.php?post_type=viwec_template&sample=wot_email&style=basic"
                               target="_blank"><?php esc_html_e( 'Create a new email template', 'woocommerce-orders-tracking' ) ?></a>
                        </p>
						<?php
					}
					?>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="vi-ui segment">
            <div class="vi-ui message"><?php esc_html_e( 'This email is used when no Email template is selected', 'woocommerce-orders-tracking' ) ?></div>
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( self::set( 'setting-email-subject' ) ) ?>">
							<?php esc_html_e( 'Email subject', 'woocommerce-orders-tracking' ) ?>
                        </label>
                    </th>
                    <td>
						<?php
						$this->default_language_flag_html( 'setting-email-subject' );
						?>
                        <input type="text"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email][email_subject]"
                               id="<?php echo esc_attr( self::set( 'setting-email-subject' ) ) ?>"
                               value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'email_subject' ) ) ) ?>">
						<?php
						if ( count( $this->languages ) ) {
							foreach ( $this->languages as $key => $value ) {
								?>
                                <p>
                                    <label for="<?php echo esc_attr( self::set( "setting-email-subject_{$value}" ) ) ?>"><?php
										if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
											?>
                                            <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
											<?php
										}
										echo $value;
										if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
											echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
										}
										?>:</label>
                                </p>
                                <input id="<?php echo esc_attr( self::set( "setting-email-subject_{$value}" ) ) ?>"
                                       type="text"
                                       name="<?php echo esc_attr( self::set( "settings[email][email_subject_{$value}]" ) ) ?>"
                                       value="<?php echo stripslashes( $this->settings->get_params( 'email_subject', '', $value ) ); ?>">
								<?php
							}
						}
						?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( self::set( 'setting-email-heading' ) ) ?>">
							<?php esc_html_e( 'Email heading', 'woocommerce-orders-tracking' ) ?>
                        </label>
                    </th>
                    <td>
						<?php
						$this->default_language_flag_html( 'setting-email-heading' );
						?>
                        <input type="text"

                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email][email_heading]"

                               id="<?php echo esc_attr( self::set( 'setting-email-heading' ) ) ?>"

                               value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'email_heading' ) ) ) ?>">
						<?php
						if ( count( $this->languages ) ) {
							foreach ( $this->languages as $key => $value ) {
								?>
                                <p>
                                    <label for="<?php echo esc_attr( self::set( "setting-email-heading_{$value}" ) ) ?>"><?php
										if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
											?>
                                            <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
											<?php
										}
										echo $value;
										if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
											echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
										}
										?>:</label>
                                </p>
                                <input id="<?php echo esc_attr( self::set( "setting-email-heading_{$value}" ) ) ?>"
                                       type="text"
                                       name="<?php echo esc_attr( self::set( "settings[email][email_heading_{$value}]" ) ) ?>"
                                       value="<?php echo stripslashes( $this->settings->get_params( 'email_heading', '', $value ) ); ?>">
								<?php
							}
						}
						?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( self::set( 'setting-email-content' ) ) ?>">
							<?php esc_html_e( 'Email content', 'woocommerce-orders-tracking' ) ?>
                        </label>
                    </th>
                    <td>
						<?php
						$this->default_language_flag_html( 'email_content' );
						wp_editor( stripslashes( $this->settings->get_params( 'email_content' ) ), 'wot-email-content', array(

							'editor_height' => 300,

							'textarea_name' => 'woo-orders-tracking-settings[email][email_content]'

						) );
						if ( count( $this->languages ) ) {
							foreach ( $this->languages as $key => $value ) {
								?>
                                <p>
                                    <label for="<?php echo esc_attr( "wot-email-content_{$value}" ) ?>"><?php
										if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
											?>
                                            <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
											<?php
										}
										echo $value;
										if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
											echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
										}
										?>:</label>
                                </p>
								<?php
								wp_editor( stripslashes( $this->settings->get_params( 'email_content', '', $value ) ), "wot-email-content_{$value}", array(
									'editor_height' => 300,
									'textarea_name' => "woo-orders-tracking-settings[email][email_content_{$value}]"
								) );
							}
						}
						self::table_of_placeholders( array(
								'tracking_table'     => esc_html__( 'Table of order items and their respective tracking info', 'woocommerce-orders-tracking' ),
								'order_id'           => esc_html__( 'ID of current order', 'woocommerce-orders-tracking' ),
								'billing_first_name' => esc_html__( 'Billing first name', 'woocommerce-orders-tracking' ),
								'billing_last_name'  => esc_html__( 'Billing last name', 'woocommerce-orders-tracking' ),
							)
						);
						?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( self::set( 'email_column_tracking_number' ) ) ?>">
							<?php esc_html_e( 'Tracking number column', 'woocommerce-orders-tracking' ) ?>
                        </label>
                    </th>
                    <td>
						<?php
						$this->default_language_flag_html( 'email_column_tracking_number' );
						wp_editor( stripslashes( $this->settings->get_params( 'email_column_tracking_number' ) ), 'wot-email_column_tracking_number', array(
							'editor_height' => 50,
							'textarea_name' => 'woo-orders-tracking-settings[email][email_column_tracking_number]',
						) );
						if ( count( $this->languages ) ) {
							foreach ( $this->languages as $key => $value ) {
								?>
                                <p>
                                    <label for="<?php echo esc_attr( "wot-email_column_tracking_number_{$value}" ) ?>"><?php
										if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
											?>
                                            <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
											<?php
										}
										echo $value;
										if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
											echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
										}
										?>:</label>
                                </p>
								<?php
								wp_editor( stripslashes( $this->settings->get_params( 'email_column_tracking_number', '', $value ) ), "wot-email_column_tracking_number_{$value}", array(
									'editor_height' => 50,
									'textarea_name' => "woo-orders-tracking-settings[email][email_column_tracking_number_{$value}]"
								) );
							}
						}
						?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( self::set( 'email_column_carrier_name' ) ) ?>">
							<?php esc_html_e( 'Carrier name column', 'woocommerce-orders-tracking' ) ?>
                        </label>
                    </th>
                    <td>
						<?php
						$this->default_language_flag_html( 'email_column_carrier_name' );
						wp_editor( stripslashes( $this->settings->get_params( 'email_column_carrier_name' ) ), 'wot-email_column_carrier_name', array(
							'editor_height' => 50,
							'textarea_name' => 'woo-orders-tracking-settings[email][email_column_carrier_name]',
						) );
						if ( count( $this->languages ) ) {
							foreach ( $this->languages as $key => $value ) {
								?>
                                <p>
                                    <label for="<?php echo esc_attr( "wot-email_column_carrier_name_{$value}" ) ?>"><?php
										if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
											?>
                                            <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
											<?php
										}
										echo $value;
										if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
											echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
										}
										?>:</label>
                                </p>
								<?php
								wp_editor( stripslashes( $this->settings->get_params( 'email_column_carrier_name', '', $value ) ), "wot-email_column_carrier_name_{$value}", array(
									'editor_height' => 50,
									'textarea_name' => "woo-orders-tracking-settings[email][email_column_carrier_name_{$value}]"
								) );
							}
						}
						?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( self::set( 'email_column_tracking_url' ) ) ?>">
							<?php esc_html_e( 'Tracking url column', 'woocommerce-orders-tracking' ) ?>
                        </label>
                    </th>
                    <td>
						<?php
						$this->default_language_flag_html( 'email_column_tracking_url' );
						wp_editor( stripslashes( $this->settings->get_params( 'email_column_tracking_url' ) ), 'wot-email_column_tracking_url', array(
							'editor_height' => 50,
							'textarea_name' => 'woo-orders-tracking-settings[email][email_column_tracking_url]',
						) );
						if ( count( $this->languages ) ) {
							foreach ( $this->languages as $key => $value ) {
								?>
                                <p>
                                    <label for="<?php echo esc_attr( "wot-email_column_tracking_url_{$value}" ) ?>"><?php
										if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
											?>
                                            <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
											<?php
										}
										echo $value;
										if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
											echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
										}
										?>:</label>
                                </p>
								<?php
								wp_editor( stripslashes( $this->settings->get_params( 'email_column_tracking_url', '', $value ) ), "wot-email_column_tracking_url_{$value}", array(
									'editor_height' => 50,
									'textarea_name' => "woo-orders-tracking-settings[email][email_column_tracking_url_{$value}]"
								) );
							}
						}
						?>
                        <p><?php esc_html_e( '{tracking_table} contains 4 columns and you can customize 3 of them, the first column is Product title and it\'s mandatory.', 'woocommerce-orders-tracking' ) ?></p>
                        <p><?php esc_html_e( 'You can leave column content blank to remove it from {tracking_table}.', 'woocommerce-orders-tracking' ) ?></p>
                        <p><?php esc_html_e( 'Below placeholders can be used in both 3 columns of {tracking_table}', 'woocommerce-orders-tracking' ) ?></p>
						<?php
						self::table_of_placeholders( array(
								'tracking_number' => esc_html__( 'Tracking number', 'woocommerce-orders-tracking' ),
								'tracking_url'    => esc_html__( 'Tracking url', 'woocommerce-orders-tracking' ),
								'carrier_name'    => esc_html__( 'Carrier name', 'woocommerce-orders-tracking' ),
								'carrier_url'     => esc_html__( 'Carrier url', 'woocommerce-orders-tracking' ),
							)
						);
						?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <table class="form-table">
            <tbody>
            <tr>
                <td colspan="2">
                    <div class="vi-ui positive message">
                        <div class="header">
							<?php esc_html_e( 'Settings for sending emails when importing tracking numbers', 'woocommerce-orders-tracking' ) ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'email_number_send' ) ) ?>">
						<?php esc_html_e( 'Number of emails sent per time', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="number" min="1"
                           class="<?php echo esc_attr( self::set( 'email_number_send' ) ) ?>"
                           id="<?php echo esc_attr( self::set( 'email_number_send' ) ) ?>"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email][email_number_send]"
                           value="<?php echo esc_attr( $this->settings->get_params( 'email_number_send' ) ) ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'email_time_send' ) ) ?>">
						<?php esc_html_e( 'Delay between each time', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <div class="vi-ui right labeled input">
                        <input type="number" min="0"
                               class="<?php echo esc_attr( self::set( 'email_time_send' ) ) ?>"
                               id="<?php echo esc_attr( self::set( 'email_time_send' ) ) ?>"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email][email_time_send]"
                               value="<?php echo esc_attr( $this->settings->get_params( 'email_time_send' ) ) ?>">
                        <label for="amount"
                               class="vi-ui label">
                            <select name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email][email_time_send_type]"
                                    id="<?php echo esc_attr( self::set( 'email_time_send_type' ) ) ?>"
                                    class="vi-ui dropdown <?php echo esc_attr( self::set( 'email_time_send_type' ) ) ?>">
								<?php
								$delay_time_type = array(
									'day'    => esc_html__( 'Day', 'woocommerce-orders-tracking' ),
									'hour'   => esc_html__( 'Hour', 'woocommerce-orders-tracking' ),
									'minute' => esc_html__( 'Minute', 'woocommerce-orders-tracking' ),
								);
								foreach ( $delay_time_type as $key => $value ) {
									$selected = '';
									if ( $this->settings->get_params( 'email_time_send_type' ) == $key ) {
										$selected = 'selected="selected"';
									}
									?>
                                    <option value="<?php echo esc_attr( $key ) ?>" <?php echo esc_attr( $selected ) ?>><?php esc_html_e( $value ) ?></option>
									<?php
								}
								?>
                            </select>
                        </label>
                    </div>
                    <p class="description"><?php esc_html_e( 'If you import tracking numbers for 100 orders and all 100 orders have tracking numbers updated, not all 100 emails will be sent at a time.', 'woocommerce-orders-tracking' ) ?></p>
                    <p class="description"><?php echo wp_kses_post( __( 'If you set <strong>"Number of emails sent per time"</strong> to 10 and <strong>"Delay between each time"</strong> to 10 minutes, by the time the import completes, it will send 10 first email and wait 10 minutes to send next 10 emails and continue this until all emails are sent.', 'woocommerce-orders-tracking' ) ) ?></p>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	private function sms_settings() {
		$sms_provider  = $this->settings->get_params( 'sms_provider' );
		$sms_providers = array(
			'twilio' => 'Twilio',
			'nexmo'  => 'Nexmo',
			'plivo'  => 'Plivo'
		)
		?>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_text_new' ) ) ?>">
						<?php esc_html_e( 'Message text when new tracking is added', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
					<?php
					$this->default_language_flag_html( 'sms_text_new' );
					?>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_text_new]"
                           id="<?php echo esc_attr( self::set( 'sms_text_new' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_text_new' ) ) ) ?>">
					<?php
					if ( count( $this->languages ) ) {
						foreach ( $this->languages as $key => $value ) {
							?>
                            <p>
                                <label for="<?php echo esc_attr( self::set( "setting-email-sms_text_new_{$value}" ) ) ?>"><?php
									if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
										?>
                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
										<?php
									}
									echo $value;
									if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
										echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
									}
									?>:</label>
                            </p>
                            <input id="<?php echo esc_attr( self::set( "setting-email-sms_text_new_{$value}" ) ) ?>"
                                   type="text"
                                   name="<?php echo esc_attr( self::set( "settings[sms_text_new_{$value}]" ) ) ?>"
                                   value="<?php echo stripslashes( $this->settings->get_params( 'sms_text_new', '', $value ) ); ?>">
							<?php
						}
					}
					?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_text' ) ) ?>">
						<?php esc_html_e( 'Message text when tracking changes', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
					<?php
					$this->default_language_flag_html( 'sms_text' );
					?>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_text]"
                           id="<?php echo esc_attr( self::set( 'sms_text' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_text' ) ) ) ?>">
					<?php
					if ( count( $this->languages ) ) {
						foreach ( $this->languages as $key => $value ) {
							?>
                            <p>
                                <label for="<?php echo esc_attr( self::set( "setting-email-sms_text_{$value}" ) ) ?>"><?php
									if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
										?>
                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
										<?php
									}
									echo $value;
									if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
										echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
									}
									?>:</label>
                            </p>
                            <input id="<?php echo esc_attr( self::set( "setting-email-sms_text_{$value}" ) ) ?>"
                                   type="text"
                                   name="<?php echo esc_attr( self::set( "settings[sms_text_{$value}]" ) ) ?>"
                                   value="<?php echo stripslashes( $this->settings->get_params( 'sms_text', '', $value ) ); ?>">
							<?php
						}
					}
					self::table_of_placeholders( array(
							'order_id'           => esc_html__( 'ID of current order', 'woocommerce-orders-tracking' ),
							'billing_first_name' => esc_html__( 'Billing first name', 'woocommerce-orders-tracking' ),
							'billing_last_name'  => esc_html__( 'Billing last name', 'woocommerce-orders-tracking' ),
							'tracking_number'    => esc_html__( 'The tracking number', 'woocommerce-orders-tracking' ),
							'tracking_url'       => esc_html__( 'The tracking URL', 'woocommerce-orders-tracking' ),
							'carrier_name'       => esc_html__( 'Carrier name', 'woocommerce-orders-tracking' ),
						)
					);
					?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_provider' ) ) ?>">
						<?php esc_html_e( 'SMS provider', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <select class="vi-ui dropdown"
                            name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_provider]"
                            id="<?php echo esc_attr( self::set( 'sms_provider' ) ) ?>">
						<?php
						foreach ( $sms_providers as $sms_provider_k => $sms_provider_v ) {
							?>
                            <option value="<?php echo esc_attr( $sms_provider_k ) ?>" <?php selected( $sms_provider_k, $sms_provider ) ?>><?php esc_html_e( $sms_provider_v ) ?></option>
							<?php
						}
						?>
                    </select>
                </td>
            </tr>
            <tr class="<?php echo esc_attr( $sms_provider === 'plivo' ? self::set( 'hidden' ) : '' ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_from_number' ) ) ?>">
						<?php esc_html_e( 'From number', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
					<?php
					$this->default_language_flag_html( 'sms_from_number' );
					?>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_from_number]"
                           id="<?php echo esc_attr( self::set( 'sms_from_number' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_from_number' ) ) ) ?>">
					<?php
					if ( count( $this->languages ) ) {
						foreach ( $this->languages as $key => $value ) {
							?>
                            <p>
                                <label for="<?php echo esc_attr( self::set( "setting-email-sms_from_number_{$value}" ) ) ?>"><?php
									if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
										?>
                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
										<?php
									}
									echo $value;
									if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
										echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
									}
									?>:</label>
                            </p>
                            <input id="<?php echo esc_attr( self::set( "setting-email-sms_from_number_{$value}" ) ) ?>"
                                   type="text"
                                   name="<?php echo esc_attr( self::set( "settings[sms_from_number_{$value}]" ) ) ?>"
                                   value="<?php echo stripslashes( $this->settings->get_params( 'sms_from_number', '', $value ) ); ?>">
							<?php
						}
					}
					?>
                </td>
            </tr>
			<?php
			$sms_twilio_app = array( 'sms_twilio_app' );
			$sms_nexmo_app  = array( 'sms_nexmo_app' );
			$sms_plivo_app  = array( 'sms_plivo_app' );
			switch ( $sms_provider ) {
				case 'twilio':
					$sms_nexmo_app[] = 'hidden';
					$sms_plivo_app[] = 'hidden';
					break;
				case 'nexmo':
					$sms_twilio_app[] = 'hidden';
					$sms_plivo_app[]  = 'hidden';
					break;
				case 'plivo':
					$sms_nexmo_app[]  = 'hidden';
					$sms_twilio_app[] = 'hidden';
					break;
				default:
			}
			?>
            <tr class="<?php echo esc_attr( self::set( $sms_twilio_app ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_twilio_app_id' ) ) ?>">
						<?php esc_html_e( 'ACCOUNT SID', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_twilio_app_id]"
                           id="<?php echo esc_attr( self::set( 'sms_twilio_app_id' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_twilio_app_id' ) ) ) ?>">
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $sms_twilio_app ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_twilio_app_token' ) ) ?>">
						<?php esc_html_e( 'AUTH TOKEN', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_twilio_app_token]"
                           id="<?php echo esc_attr( self::set( 'sms_twilio_app_token' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_twilio_app_token' ) ) ) ?>">
                    <p class="description"><?php echo wp_kses_post( __( 'To get Twilio Access Token, please read <a href="https://www.twilio.com/docs/iam/access-tokens" target="_blank">Twilio API: Access Tokens</a>', 'woocommerce-orders-tracking' ) ) ?></p>
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $sms_nexmo_app ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_nexmo_app_id' ) ) ?>">
						<?php esc_html_e( 'API Key', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_nexmo_app_id]"
                           id="<?php echo esc_attr( self::set( 'sms_nexmo_app_id' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_nexmo_app_id' ) ) ) ?>">
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $sms_nexmo_app ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_nexmo_app_token' ) ) ?>">
						<?php esc_html_e( 'API Secret', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_nexmo_app_token]"
                           id="<?php echo esc_attr( self::set( 'sms_nexmo_app_token' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_nexmo_app_token' ) ) ) ?>">
                    <p class="description"><?php echo wp_kses_post( __( 'To get Nexmo API key and token, please read <a href="https://help.nexmo.com/hc/en-us/articles/204014493-Where-can-I-find-my-API-key-and-API-secret-" target="_blank">Where can I find my API key and API secret?</a>', 'woocommerce-orders-tracking' ) ) ?></p>
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $sms_nexmo_app ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_nexmo_unicode' ) ) ?>">
						<?php esc_html_e( 'Enable unicode', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox">
                        <input type="checkbox"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_nexmo_unicode]"
                               id="<?php echo esc_attr( self::set( 'setting-email-woo-enable' ) ) ?>"
                               value="1" <?php checked( $this->settings->get_params( 'sms_nexmo_unicode' ), '1' ) ?>><label
                                for="<?php echo esc_attr( self::set( 'setting-email-woo-enable' ) ) ?>"><?php esc_html_e( 'Yes', 'woocommerce-orders-tracking' ) ?></label>
                    </div>
                    <p class="description"><?php echo wp_kses_post( __( 'Only enable this option if your message contains Unicode characters because Unicode messages can only contain 70 characters, rather than the usual 160. There\'s more information about this <a href="https://help.nexmo.com/hc/en-us/articles/204076866-How-long-is-a-single-SMS-body-" target="_blank">on the help page</a>', 'woocommerce-orders-tracking' ) ) ?></p>
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $sms_plivo_app ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_plivo_app_id' ) ) ?>">
						<?php esc_html_e( 'Auth ID', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_plivo_app_id]"
                           id="<?php echo esc_attr( self::set( 'sms_plivo_app_id' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_plivo_app_id' ) ) ) ?>">
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $sms_plivo_app ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_plivo_app_token' ) ) ?>">
						<?php esc_html_e( 'Auth Token', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_plivo_app_token]"
                           id="<?php echo esc_attr( self::set( 'sms_plivo_app_token' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_plivo_app_token' ) ) ) ?>">
                    <p class="description"><?php echo wp_kses_post( __( 'To get Plivo Access Token, please read <a href="https://support.plivo.com/support/solutions/articles/17000089755-auth-id-and-auth-token" target="_blank">Auth ID and Auth Token</a>', 'woocommerce-orders-tracking' ) ) ?></p>
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $sms_plivo_app ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'sms_plivo_powerpack_uuid' ) ) ?>">
						<?php esc_html_e( 'Powerpack UUID', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[sms_plivo_powerpack_uuid]"
                           id="<?php echo esc_attr( self::set( 'sms_plivo_powerpack_uuid' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'sms_plivo_powerpack_uuid' ) ) ) ?>">
                    <p class="description"><?php echo wp_kses_post( __( 'To create power pack, please read <a href="https://www.plivo.com/docs/sms/powerpack" target="_blank">Getting started with Powerpack</a>', 'woocommerce-orders-tracking' ) ) ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'bitly_access_token' ) ) ?>">
						<?php esc_html_e( 'Bitly access token', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[bitly_access_token]"
                           id="<?php echo esc_attr( self::set( 'bitly_access_token' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'bitly_access_token' ) ) ) ?>">
                    <p class="description"><?php echo wp_kses_post( __( 'Using Bitly to shorten your tracking url helps reduce message characters. Please read <a href="https://support.bitly.com/hc/en-us/articles/230647907-How-do-I-find-my-OAuth-access-token-" target="_blank">How to get Access Token</a>', 'woocommerce-orders-tracking' ) ) ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'send_test_sms' ) ) ?>">
						<?php esc_html_e( 'Send test SMS', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <div class="vi-ui right labeled input">
                        <input type="text"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[send_test_sms]"
                               placeholder="<?php echo esc_attr__( 'Your phone number with area code', 'woocommerce-orders-tracking' ) ?>"
                               id="<?php echo esc_attr( self::set( 'send_test_sms' ) ) ?>"
                               class="<?php echo esc_attr( self::set( 'send_test_sms' ) ) ?>">
                        <label class="vi-ui label">
                            <span class="vi-ui positive button <?php echo esc_attr( self::set( 'button-send-test-sms' ) ) ?>"><?php esc_html_e( 'Send', 'woocommerce-orders-tracking' ) ?></span>
                        </label>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	private function email_woo_settings() {
		?>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'setting-email-woo-enable' ) ) ?>">
						<?php esc_html_e( 'Include tracking in WooCommerce email', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox">
                        <input type="checkbox"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email_woo][email_woo_enable]"
                               id="<?php echo esc_attr( self::set( 'setting-email-woo-enable' ) ) ?>"
                               value="1" <?php checked( $this->settings->get_params( 'email_woo_enable' ), '1' ) ?>><label
                                for="<?php echo esc_attr( self::set( 'setting-email-woo-enable' ) ) ?>"><?php esc_html_e( 'Yes', 'woocommerce-orders-tracking' ) ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'Tracking information will be included in selected emails below no matter you check the send email checkbox(when editing order tracking/importing tracking) or not', 'woocommerce-orders-tracking' ) ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'setting-email-woo-status' ) ) ?>">
						<?php esc_html_e( 'Order status email', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
					<?php
					$email_woo_status   = $this->settings->get_params( 'email_woo_status' );
					$email_woo_statuses = apply_filters( 'woocommerce_orders_tracking_email_woo_statuses', array(
						'cancelled_order'           => esc_html__( 'Cancelled', 'woocommerce-orders-tracking' ),
						'customer_completed_order'  => esc_html__( 'Completed', 'woocommerce-orders-tracking' ),
						'customer_invoice'          => esc_html__( 'Customer Invoice', 'woocommerce-orders-tracking' ),
						'customer_note'             => esc_html__( 'Customer Note', 'woocommerce-orders-tracking' ),
						'failed_order'              => esc_html__( 'Failed', 'woocommerce-orders-tracking' ),
						'customer_on_hold_order'    => esc_html__( 'On Hold', 'woocommerce-orders-tracking' ),
						'customer_processing_order' => esc_html__( 'Processing', 'woocommerce-orders-tracking' ),
						'customer_refunded_order'   => esc_html__( 'Refunded', 'woocommerce-orders-tracking' ),
					) );
					?>
                    <select name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email_woo][email_woo_status][]"
                            id="<?php echo esc_attr( self::set( 'setting-email-woo-status' ) ) ?>"
                            class="vi-ui fluid dropdown <?php echo esc_attr( self::set( 'setting-email-woo-status' ) ) ?>"
                            multiple>
						<?php
						foreach ( $email_woo_statuses as $email_woo_statuses_k => $email_woo_statuses_v ) {
							?>
                            <option value="<?php echo esc_attr( $email_woo_statuses_k ) ?>" <?php echo esc_attr( in_array( $email_woo_statuses_k, $email_woo_status ) ? 'selected' : "" ); ?>><?php esc_html_e( $email_woo_statuses_v ) ?></option>
							<?php
						}
						?>
                    </select>
                    <p class="description"><?php esc_html_e( 'Select orders status email to include the tracking information.', 'woocommerce-orders-tracking' ) ?></p>
                    <p class="description"><?php _e( '<strong>*Note:</strong> If you use an email customizer plugin to send email, this option will be skipped. Tracking info will be included in all emails that <strong>contain order table</strong>.', 'woocommerce-orders-tracking' ) ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'setting-email-woo-position' ) ) ?>">
						<?php esc_html_e( 'Tracking info position', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
					<?php
					$email_woo_position  = $this->settings->get_params( 'email_woo_position' );
					$email_woo_positions = array(
						'before_order_table' => esc_html__( 'Before order table', 'woocommerce-orders-tracking' ),
						'after_order_item'   => esc_html__( 'After each order item', 'woocommerce-orders-tracking' ),
						'after_order_table'  => esc_html__( 'After order table', 'woocommerce-orders-tracking' ),
					);
					?>
                    <select name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[email_woo][email_woo_position]"
                            id="<?php echo esc_attr( self::set( 'setting-email-woo-position' ) ) ?>"
                            class="vi-ui fluid dropdown <?php echo esc_attr( self::set( 'setting-email-woo-position' ) ) ?>">
						<?php
						foreach ( $email_woo_positions as $email_woo_position_k => $email_woo_position_v ) {
							?>
                            <option value="<?php echo esc_attr( $email_woo_position_k ) ?>" <?php selected( $email_woo_position, $email_woo_position_k ) ?>><?php esc_html_e( $email_woo_position_v ) ?></option>
							<?php
						}
						?>
                    </select>
                    <p class="description"><?php esc_html_e( 'Where in the email to place tracking information?', 'woocommerce-orders-tracking' ) ?></p>
					<?php
					if ( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::is_email_template_customizer_active() ) {
						?>
                        <p class="<?php echo esc_attr( self::set( 'setting-email-woo-position-before_order_table' ) ) ?> <?php if ( $email_woo_position !== 'before_order_table' ) {
							echo esc_attr( self::set( 'hidden' ) );
						} ?>"><?php _e( '<strong>*Note: </strong>You have to add <strong>WC Hook</strong> and select hook "woocommerce_email_before_order_table" in the WooCommerce email that you want to include tracking info.', 'woocommerce-orders-tracking' ) ?></p>
                        <p class="<?php echo esc_attr( self::set( 'setting-email-woo-position-after_order_table' ) ) ?> <?php if ( $email_woo_position !== 'after_order_table' ) {
							echo esc_attr( self::set( 'hidden' ) );
						} ?>"><?php _e( '<strong>*Note: </strong>You have to add <strong>WC Hook</strong> and select hook "woocommerce_email_after_order_table" in the WooCommerce email that you want to include tracking info.', 'woocommerce-orders-tracking' ) ?></p>
						<?php
					}
					?>
                </td>
            </tr>
			<?php
			$not_after_order_item_class = array( 'not_after_order_item' );
			if ( $email_woo_position === 'after_order_item' ) {
				$not_after_order_item_class[] = 'hidden';
			}
			?>
            <tr class="<?php echo esc_attr( self::set( $not_after_order_item_class ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'email_woo_html' ) ) ?>">
						<?php esc_html_e( 'Tracking content', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
					<?php
					$this->default_language_flag_html( 'email_woo_html' );
					wp_editor( stripslashes( $this->settings->get_params( 'email_woo_html' ) ), 'wot-email_woo_html', array(
						'editor_height' => 100,
						'textarea_name' => 'woo-orders-tracking-settings[email_woo][email_woo_html]',
					) );
					if ( count( $this->languages ) ) {
						foreach ( $this->languages as $key => $value ) {
							?>
                            <p>
                                <label for="<?php echo esc_attr( "wot-email_woo_html_{$value}" ) ?>"><?php
									if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
										?>
                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
										<?php
									}
									echo $value;
									if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
										echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
									}
									?>:</label>
                            </p>
							<?php
							wp_editor( stripslashes( $this->settings->get_params( 'email_woo_html', '', $value ) ), "wot-email_woo_html_{$value}", array(
								'editor_height' => 100,
								'textarea_name' => "woo-orders-tracking-settings[email_woo][email_woo_html_{$value}]"
							) );
						}
					}
					self::table_of_placeholders( array(
							'tracking_list' => esc_html__( 'List of tracking info of an order', 'woocommerce-orders-tracking' ),
						)
					);
					?>
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $not_after_order_item_class ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'email_woo_tracking_list_html' ) ) ?>">
						<?php esc_html_e( 'Tracking list item', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
					<?php
					$this->default_language_flag_html( 'email_woo_tracking_list_html' );
					wp_editor( stripslashes( $this->settings->get_params( 'email_woo_tracking_list_html' ) ), 'wot-email_woo_tracking_list_html', array(
						'editor_height' => 50,
						'textarea_name' => 'woo-orders-tracking-settings[email_woo][email_woo_tracking_list_html]',
					) );
					if ( count( $this->languages ) ) {
						foreach ( $this->languages as $key => $value ) {
							?>
                            <p>
                                <label for="<?php echo esc_attr( "wot-email_woo_tracking_list_html_{$value}" ) ?>"><?php
									if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
										?>
                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
										<?php
									}
									echo $value;
									if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
										echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
									}
									?>:</label>
                            </p>
							<?php
							wp_editor( stripslashes( $this->settings->get_params( 'email_woo_tracking_list_html', '', $value ) ), "wot-email_woo_tracking_list_html_{$value}", array(
								'editor_height' => 50,
								'textarea_name' => "woo-orders-tracking-settings[email_woo][email_woo_tracking_list_html_{$value}]"
							) );
						}
					}
					self::table_of_placeholders( array(
							'tracking_number' => esc_html__( 'Tracking number', 'woocommerce-orders-tracking' ),
							'tracking_url'    => esc_html__( 'Tracking url', 'woocommerce-orders-tracking' ),
							'carrier_name'    => esc_html__( 'Carrier name', 'woocommerce-orders-tracking' ),
							'carrier_url'     => esc_html__( 'Carrier url', 'woocommerce-orders-tracking' ),
						)
					);
					?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="vi-ui positive small message">
                        <div>
							<?php _e( 'You can customize tracking number and carrier html which are displayed <strong>after every order item</strong> in email(if selected) or on <strong>My account/Order details</strong> page', 'woocommerce-orders-tracking' ) ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'email_woo_tracking_number_html' ) ) ?>">
						<?php esc_html_e( 'Tracking Number', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
					<?php
					$this->default_language_flag_html( 'email_woo_tracking_number_html' );
					wp_editor( stripslashes( $this->settings->get_params( 'email_woo_tracking_number_html' ) ), 'wot-email_woo_tracking_number_html', array(
						'editor_height' => 50,
						'textarea_name' => 'woo-orders-tracking-settings[email_woo][email_woo_tracking_number_html]',
					) );
					if ( count( $this->languages ) ) {
						foreach ( $this->languages as $key => $value ) {
							?>
                            <p>
                                <label for="<?php echo esc_attr( "wot-email_woo_tracking_number_html_{$value}" ) ?>"><?php
									if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
										?>
                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
										<?php
									}
									echo $value;
									if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
										echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
									}
									?>:</label>
                            </p>
							<?php
							wp_editor( stripslashes( $this->settings->get_params( 'email_woo_tracking_number_html', '', $value ) ), "wot-email_woo_tracking_number_html_{$value}", array(
								'editor_height' => 50,
								'textarea_name' => "woo-orders-tracking-settings[email_woo][email_woo_tracking_number_html_{$value}]"
							) );
						}
					}
					?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'email_woo_tracking_carrier_html' ) ) ?>">
						<?php esc_html_e( 'Tracking Carrier', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
					<?php
					$this->default_language_flag_html( 'email_woo_tracking_carrier_html' );
					wp_editor( stripslashes( $this->settings->get_params( 'email_woo_tracking_carrier_html' ) ), 'wot-email_woo_tracking_carrier_html', array(
						'editor_height' => 50,
						'textarea_name' => 'woo-orders-tracking-settings[email_woo][email_woo_tracking_carrier_html]',
					) );
					if ( count( $this->languages ) ) {
						foreach ( $this->languages as $key => $value ) {
							?>
                            <p>
                                <label for="<?php echo esc_attr( "wot-email_woo_tracking_carrier_html_{$value}" ) ?>"><?php
									if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
										?>
                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
										<?php
									}
									echo $value;
									if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
										echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
									}
									?>:</label>
                            </p>
							<?php
							wp_editor( stripslashes( $this->settings->get_params( 'email_woo_tracking_carrier_html', '', $value ) ), "wot-email_woo_tracking_carrier_html_{$value}", array(
								'editor_height' => 50,
								'textarea_name' => "woo-orders-tracking-settings[email_woo][email_woo_tracking_carrier_html_{$value}]"
							) );
						}
					}
					self::table_of_placeholders( array(
							'tracking_number' => esc_html__( 'Tracking number', 'woocommerce-orders-tracking' ),
							'tracking_url'    => esc_html__( 'Tracking url', 'woocommerce-orders-tracking' ),
							'carrier_name'    => esc_html__( 'Carrier name', 'woocommerce-orders-tracking' ),
							'carrier_url'     => esc_html__( 'Carrier url', 'woocommerce-orders-tracking' ),
						)
					);
					?>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	private function paypal_settings() {
		$available_gateways = WC()->payment_gateways()->payment_gateways();

		$available_paypal_methods  = array();
		$supported_paypal_gateways = $this->settings->get_params( 'supported_paypal_gateways' );
		foreach ( $available_gateways as $method_id => $method ) {
			if ( in_array( $method_id, $supported_paypal_gateways ) ) {
				$available_paypal_methods[] = $method;
			}
		}
		if ( is_array( $available_paypal_methods ) && count( $available_paypal_methods ) ) {
			?>
            <div class="vi-ui positive message">
                <div class="header"><?php esc_html_e( 'Please follow these steps to get PayPal API Credentials', 'woocommerce-orders-tracking' ) ?></div>
                <ul class="list">
                    <li><?php printf( wp_kses_post( __( 'Go to %s and login with your PayPal account', 'woocommerce-orders-tracking' ) ), '<strong><a href="https://developer.paypal.com/developer/applications/"
                           target="_blank">PayPal Developer</a></strong>' ); ?></li>
                    <li><?php echo wp_kses_post( __( 'Go to My Apps & Credentials and select the <strong>Live</strong> tab', 'woocommerce-orders-tracking' ) ) ?></li>
                    <li><?php esc_html_e( 'Click on Create App button', 'woocommerce-orders-tracking' ) ?></li>
                    <li><?php esc_html_e( 'Enter the name of your application and click Create App button', 'woocommerce-orders-tracking' ); ?></li>
                    <li><?php esc_html_e( 'Copy your Client ID and Secret and paste them to Client Id and Client Secret fields', 'woocommerce-orders-tracking' ); ?></li>
                </ul>
            </div>
            <table class="form-table wot-paypal-app-table">
                <tbody>
                <tr class="wot-paypal-app-table-header">
                    <th><?php esc_html_e( 'Payment Method', 'woocommerce-orders-tracking' ) ?></th>
                    <th><?php esc_html_e( 'Client ID', 'woocommerce-orders-tracking' ) ?></th>
                    <th><?php esc_html_e( 'Client Secret', 'woocommerce-orders-tracking' ) ?></th>
                    <th><?php esc_html_e( 'PayPal sandbox', 'woocommerce-orders-tracking' ) ?></th>
                    <th><?php esc_html_e( 'Actions', 'woocommerce-orders-tracking' ) ?></th>
                </tr>
                </tbody>
                <tbody>
				<?php
				$paypal_method = $this->settings->get_params( 'paypal_method' );
				foreach ( $available_paypal_methods as $item ) {
					$i              = array_search( $item->id, $paypal_method );
					$live_client_id = $live_client_secret = $sandbox_client_id = $sandbox_client_secret = $sandbox_enable = '';
					if ( is_numeric( $i ) ) {
						$live_client_id        = $this->settings->get_params( 'paypal_client_id_live' )[ $i ];
						$live_client_secret    = $this->settings->get_params( 'paypal_secret_live' )[ $i ];
						$sandbox_client_id     = $this->settings->get_params( 'paypal_client_id_sandbox' )[ $i ];
						$sandbox_client_secret = $this->settings->get_params( 'paypal_secret_sandbox' )[ $i ];
						$sandbox_enable        = $this->settings->get_params( 'paypal_sandbox_enable' )[ $i ];
					}
					?>
                    <tr class="wot-paypal-app-content">
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[paypal][paypal_method][]"
                                   value="<?php echo esc_attr( $item->id ) ?>">
                            <input type="text" title="<?php echo esc_attr( $item->id ) ?>"
                                   value="<?php echo esc_attr( $item->method_title ) ?>" readonly>
                        </td>
                        <td>
                            <div class="field">
                                <div class="field  woo-orders-tracking-setting-paypal-live-wrap">
                                    <div class="vi-ui input"
                                         data-tooltip="<?php echo esc_attr( 'Live Client ID', 'woocommerce-orders-tracking' ) ?>">
                                        <input type="text"
                                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[paypal][paypal_client_id_live][]"
                                               class="woo-orders-tracking-setting-paypal-client-id-live"
                                               value="<?php echo esc_attr( $live_client_id ) ?>">
                                    </div>
                                </div>
                                <div class="field woo-orders-tracking-setting-paypal-sandbox-wrap">
                                    <div class="vi-ui input"
                                         data-tooltip="<?php echo esc_attr( 'Sandbox Client ID', 'woocommerce-orders-tracking' ) ?>">
                                        <input type="text"
                                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[paypal][paypal_client_id_sandbox][]"
                                               class="woo-orders-tracking-setting-paypal-client-id-sandbox"
                                               value="<?php echo esc_attr( $sandbox_client_id ) ?>">
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="field ">
                                <div class="field  woo-orders-tracking-setting-paypal-live-wrap">
                                    <div class="vi-ui input"
                                         data-tooltip="<?php echo esc_attr( 'Live Client Secret', 'woocommerce-orders-tracking' ) ?>">
                                        <input type="text"
                                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[paypal][paypal_secret_live][]"
                                               class="woo-orders-tracking-setting-paypal-secret-live"
                                               value="<?php echo esc_attr( $live_client_secret ) ?>">
                                    </div>
                                </div>
                                <div class="field woo-orders-tracking-setting-paypal-sandbox-wrap">
                                    <div class="vi-ui input"
                                         data-tooltip="<?php echo esc_attr( 'Sandbox Client Secret', 'woocommerce-orders-tracking' ) ?>">
                                        <input type="text"
                                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[paypal][paypal_secret_sandbox][]"
                                               class="woo-orders-tracking-setting-paypal-secret-sandbox"
                                               value="<?php echo esc_attr( $sandbox_client_secret ) ?>">
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[paypal][paypal_sandbox_enable][]"
                                   class="<?php echo esc_attr( self::set( 'setting-paypal-sandbox-enable' ) ) ?>"
                                   value="<?php echo esc_attr( $sandbox_enable ); ?>">
                            <div class="vi-ui toggle checkbox">
                                <input type="checkbox"
                                       id="<?php echo esc_attr( self::set( 'setting-paypal-sandbox-enable' ) ) ?>"
                                       value="<?php echo esc_attr( $sandbox_enable ); ?>" <?php checked( $sandbox_enable, '1' ) ?> >
                            </div>
                        </td>
                        <td>
                            <div class="field">
                                <div class="field">
                                        <span class="wot-paypal-app-content-action-test-api wot-paypal-app-content-action-btn vi-ui button positive ">
                                    <?php esc_html_e( 'Test Connection', 'woocommerce-orders-tracking' ) ?>
                                </span>
                                </div>
                                <div class="field">
                                    <div class="<?php echo esc_attr( self::set( 'setting-paypal-btn-check-api-text' ) ) ?>">
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
					<?php
				}
				?>
                </tbody>
            </table>
			<?php
		} else {
			?>
            <div class="vi-ui negative message">
                <div class="header">
					<?php esc_html_e( 'This option will only be available if a PayPal payment method is activated', 'woocommerce-orders-tracking' ) ?>
                </div>
            </div>
			<?php
		}
	}

	private function tracking_service_settings() {
		?>
        <div class="vi-ui positive small message">
			<?php echo wp_kses_post( __( 'TrackingMore tracking form shortcode <strong>[vi_wot_tracking_more_form]</strong>. You can still use this shortcode even if you do not use tracking service. More details about this at <a target="_blank" href="https://www.trackingmore.com/embed_box_float-en.html">Track Button</a>', 'woocommerce-orders-tracking' ) ) ?>
        </div>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'setting-service-carrier-enable' ) ) ?>">
						<?php esc_html_e( 'Enable', 'woocommerce-orders-tracking' ); ?>
                    </label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox">
                        <input type="checkbox"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[service_carrier][service_carrier_enable]"
                               id="<?php echo esc_attr( self::set( 'setting-service-carrier-enable' ) ) ?>"
                               value="1" <?php checked( $this->settings->get_params( 'service_carrier_enable' ), '1' ) ?>><label
                                for="<?php echo esc_attr( self::set( 'setting-service-carrier-enable' ) ) ?>"><?php esc_html_e( 'Yes', 'woocommerce-orders-tracking' ); ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'Check it if you use the 3rd party service to track shipment info', 'woocommerce-orders-tracking' ) ?></p>
                </td>
            </tr>
			<?php
			$api_key_class        = array( 'tracking-service-api' );
			$service_carrier_type = $this->settings->get_params( 'service_carrier_type' );
			if ( $service_carrier_type === 'cainiao' ) {
				$api_key_class[] = 'hidden';
			}
			?>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'setting-service-carrier-type' ) ) ?>"><?php esc_html_e( 'Service', 'woocommerce-orders-tracking' ); ?>
                    </label>
                </th>
                <td>
                    <select name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[service_carrier][service_carrier_type]"
                            id="<?php echo esc_attr( self::set( 'setting-service-carrier-type' ) ) ?>"
                            class="vi-ui fluid dropdown <?php echo esc_attr( self::set( 'setting-service-carrier-type' ) ) ?>">
						<?php
						foreach ( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::service_carriers_list() as $item_slug => $item_name ) {
							?>
                            <option value="<?php echo esc_attr( $item_slug ) ?>" <?php selected( $service_carrier_type, $item_slug ) ?>><?php esc_html_e( $item_name ); ?></option>
							<?php
						}
						?>
                    </select>
                    <p class="description <?php echo esc_attr( self::set( $api_key_class ) ) ?>"><?php esc_html_e( 'If Cainiao is not selected, it\'s still be used when customers search for a tracking that exists in your orders but can not be found with your tracking service API', 'woocommerce-orders-tracking' ) ?></p>
                </td>
            </tr>

            <tr class="<?php echo esc_attr( self::set( $api_key_class ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'setting-service-carrier-api-key' ) ) ?>">
						<?php
						esc_html_e( 'API key', 'woocommerce-orders-tracking' );
						?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[service_carrier][service_carrier_api_key]"
                           id="<?php echo esc_attr( self::set( 'setting-service-carrier-api-key' ) ) ?>"
                           value="<?php echo esc_attr( $this->settings->get_params( 'service_carrier_api_key' ) ) ?>">
                    <p class="description <?php echo esc_attr( self::set( array(
						'setting-service-carrier-api-key-trackingmore',
						'setting-service-carrier-api-key',
						'hidden'
					) ) ) ?>">
						<?php
						echo wp_kses_post( __( 'Please enter your TrackingMore api key. If you don\'t have an account, <a href="https://my.trackingmore.com/get_apikey.php" target="_blank"><strong>click here</strong></a> to create account and generate api key', 'woocommerce-orders-tracking' ) );
						?>
                    </p>
                    <p class="description <?php echo esc_attr( self::set( array(
						'setting-service-carrier-api-key-aftership',
						'setting-service-carrier-api-key',
						'hidden'
					) ) ) ?>">
						<?php echo wp_kses_post( __( 'Please enter your AfterShip api key. If you don\'t have a account, <a href="https://help.aftership.com/hc/en-us/articles/115008353227-How-to-generate-AfterShip-API-Key-" target="_blank"><strong>click here</strong></a> to create account and generate api key', 'woocommerce-orders-tracking' ) ) ?>
                    </p>
                    <p class="description <?php echo esc_attr( self::set( array(
						'setting-service-carrier-api-key-easypost',
						'setting-service-carrier-api-key',
						'hidden'
					) ) ) ?>">
						<?php echo wp_kses_post( __( 'Please enter your EasyPost api key. If you don\'t have a account, <a href="https://www.easypost.com/signup" target="_blank"><strong>click here</strong></a> to create account and generate api key', 'woocommerce-orders-tracking' ) ) ?>
                    </p>
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $api_key_class ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'setting-service-add-tracking-if-not-exist' ) ) ?>">
						<?php esc_html_e( 'Add tracking if not exist', 'woocommerce-orders-tracking' ); ?>
                    </label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox">
                        <input type="checkbox"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[service_carrier][service_add_tracking_if_not_exist]"
                               id="<?php echo esc_attr( self::set( 'setting-service-add-tracking-if-not-exist' ) ) ?>"
                               value="1" <?php checked( $this->settings->get_params( 'service_add_tracking_if_not_exist' ), '1' ) ?>><label
                                for="<?php echo esc_attr( self::set( 'setting-service-add-tracking-if-not-exist' ) ) ?>"><?php esc_html_e( 'Yes', 'woocommerce-orders-tracking' ); ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'When customers search for a tracking number that exists in your current orders, add it to your tracking API if it does not exist in your API tracking list', 'woocommerce-orders-tracking' ) ?></p>
                </td>
            </tr>
			<?php
			$service_tracking_page = $this->settings->get_params( 'service_tracking_page' );
			?>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'setting-service-tracking-page' ) ) ?>">
						<?php
						esc_html_e( 'Tracking page', 'woocommerce-orders-tracking' );
						?>
                    </label>
                </th>
                <td>
                    <select name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[service_carrier][service_tracking_page]"
                            id="<?php echo esc_attr( self::set( 'setting-service-tracking-page' ) ) ?>"
                            class="search-page <?php echo esc_attr( self::set( 'setting-service-tracking-page' ) ) ?>">
						<?php
						if ( $service_tracking_page ) {
							?>
                            <option value="<?php echo esc_attr( $service_tracking_page ) ?>"
                                    selected><?php esc_html_e( get_the_title( $service_tracking_page ) ) ?></option>
							<?php
						}
						?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'service_cache_request' ) ) ?>">
						<?php
						esc_html_e( 'Cache request', 'woocommerce-orders-tracking' );
						?>
                    </label>
                </th>
                <td>
                    <div class="vi-ui right labeled input">
                        <input type="number" min="0" step="0.5"
                               class="<?php echo esc_attr( self::set( 'service_cache_request' ) ) ?>"
                               id="<?php echo esc_attr( self::set( 'service_cache_request' ) ) ?>"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[service_carrier][service_cache_request]"
                               value="<?php echo esc_attr( $this->settings->get_params( 'service_cache_request' ) ) ?>">
                        <label for="<?php echo esc_attr( self::set( 'service_cache_request' ) ) ?>"
                               class="vi-ui label"><?php esc_html_e( 'Hour(s)', 'woocommerce-orders-tracking' ) ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'When customers search for a tracking number on tracking page, the result will be saved to use for same searches for this tracking number within this cache time', 'woocommerce-orders-tracking' ) ?></p>
                </td>
            </tr>
            <tr class="<?php echo esc_attr( self::set( $api_key_class ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'change_order_status' ) ) ?>"><?php esc_html_e( 'Change Order Status', 'woocommerce-orders-tracking' ) ?></label>
                </th>
                <td>
					<?php
					$all_order_statuses  = wc_get_order_statuses();
					$change_order_status = $this->settings->get_params( 'change_order_status' );
					?>
                    <select id="<?php echo esc_attr( self::set( 'change_order_status' ) ) ?>"
                            class="vi-ui fluid dropdown"
                            name="<?php echo esc_attr( self::set( 'change_order_status', true ) ) ?>">
                        <option value=""><?php esc_html_e( 'Not change', 'woocommerce-orders-tracking' ) ?></option>
						<?php
						foreach ( $all_order_statuses as $all_option_k => $all_option_v ) {
							?>
                            <option value="<?php echo esc_attr( $all_option_k ) ?>" <?php selected( $all_option_k, $change_order_status ) ?>><?php echo esc_html( $all_option_v ) ?></option>
							<?php
						}
						?>
                    </select>
                    <div class="description"><?php esc_html_e( 'Select order status to change to when Shipment status changes to Delivered. Leave it blank if you don\'t want to change order status', 'woocommerce-orders-tracking' ) ?></div>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="setting-service-carrier-default">
						<?php esc_html_e( 'Customize Tracking page', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <div>
						<?php
						if ( $service_tracking_page && $service_tracking_page_url = get_the_permalink( $service_tracking_page ) ) {
							$href = 'customize.php?url=' . urlencode( $service_tracking_page_url ) . '&autofocus[panel]=vi_wot_orders_tracking_design';
							?>
                            <a href="<?php echo esc_url( $href ) ?>"
                               target="_blank">
								<?php esc_html_e( 'Click to customize your tracking page', 'woocommerce-orders-tracking' ) ?>
                            </a>
							<?php
						} else {
							?>
                            <label for="<?php echo esc_attr( self::set( 'setting-service-tracking-page' ) ) ?>"><?php esc_html_e( 'Please select a Tracking page and save settings to use this feature', 'woocommerce-orders-tracking' ); ?></label>
							<?php
						}
						?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="vi-ui positive small message">
            <div class="header">
				<?php esc_html_e( 'Google reCAPTCHA for tracking form', 'woocommerce-orders-tracking' ) ?>
            </div>
            <ul class="list">
                <li><?php echo wp_kses_post( __( 'Visit <a target="_blank" href="http://www.google.com/recaptcha/admin">Google reCAPTCHA page</a> to sign up for an API key pair with your Gmail account', 'woocommerce-orders-tracking' ) ) ?></li>
                <li><?php esc_html_e( 'Select the reCAPTCHA version that you want to use', 'woocommerce-orders-tracking' ) ?></li>
                <li><?php esc_html_e( 'Fill in authorized domains', 'woocommerce-orders-tracking' ) ?></li>
                <li><?php esc_html_e( 'Accept terms of service and click Register button', 'woocommerce-orders-tracking' ) ?></li>
                <li><?php esc_html_e( 'Copy and paste the site key and secret key into respective fields', 'woocommerce-orders-tracking' ) ?></li>
            </ul>
        </div>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_enable' ) ) ?>">
						<?php esc_html_e( 'Enable reCAPTCHA', 'woocommerce-orders-tracking' ); ?>
                    </label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox">
                        <input type="checkbox"
                               name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[tracking_form_recaptcha_enable]"
                               id="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_enable' ) ) ?>"
                               value="1" <?php checked( $this->settings->get_params( 'tracking_form_recaptcha_enable' ), '1' ) ?>>
                    </div>
                    <p class="description"><?php esc_html_e( 'Use Google reCAPTCHA for tracking form', 'woocommerce-orders-tracking' ) ?></p>
                </td>
            </tr>
			<?php
			$recaptcha_version = $this->settings->get_params( 'tracking_form_recaptcha_version' );
			?>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_version' ) ) ?>">
						<?php esc_html_e( 'reCAPTCHA version', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <select class="vi-ui dropdown"
                            name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[tracking_form_recaptcha_version]"
                            id="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_version' ) ) ?>">
                        <option value="2" <?php selected( $recaptcha_version, 2 ) ?>><?php esc_html_e( 'reCAPTCHA V2', 'woocommerce-orders-tracking' ) ?></option>
                        <option value="3" <?php selected( $recaptcha_version, 3 ) ?>><?php esc_html_e( 'reCAPTCHA V3', 'woocommerce-orders-tracking' ) ?></option>
                    </select>
                </td>
            </tr>

            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_site_key' ) ) ?>">
						<?php esc_html_e( 'Site key', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[tracking_form_recaptcha_site_key]"
                           id="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_site_key' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'tracking_form_recaptcha_site_key' ) ) ) ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_secret_key' ) ) ?>">
						<?php esc_html_e( 'Secret key', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[tracking_form_recaptcha_secret_key]"
                           id="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_secret_key' ) ) ?>"
                           value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'tracking_form_recaptcha_secret_key' ) ) ) ?>">
                </td>
            </tr>
			<?php
			$recaptcha_theme = $this->settings->get_params( 'tracking_form_recaptcha_theme' );
			?>
            <tr class="<?php echo esc_attr( $recaptcha_version == 2 ? '' : self::set( 'hidden' ) ) ?>">
                <th>
                    <label for="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_theme' ) ) ?>">
						<?php esc_html_e( 'Theme', 'woocommerce-orders-tracking' ) ?>
                    </label>
                </th>
                <td>
                    <select class="vi-ui dropdown"
                            name="<?php echo esc_attr( self::set( 'settings' ) ) ?>[tracking_form_recaptcha_theme]"
                            id="<?php echo esc_attr( self::set( 'tracking_form_recaptcha_theme' ) ) ?>">
                        <option value="dark" <?php selected( $recaptcha_theme, 'dark' ) ?>><?php esc_html_e( 'Dark', 'woocommerce-orders-tracking' ) ?></option>
                        <option value="light" <?php selected( $recaptcha_theme, 'light' ) ?>><?php esc_html_e( 'Light', 'woocommerce-orders-tracking' ) ?></option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	public function default_language_flag_html( $name = '' ) {
		if ( $this->default_language ) {
			?>
            <p>
                <label for="<?php echo esc_attr( self::set( $name ) ) ?>"><?php
					if ( isset( $this->languages_data[ $this->default_language ]['country_flag_url'] ) && $this->languages_data[ $this->default_language ]['country_flag_url'] ) {
						?>
                        <img src="<?php echo esc_url( $this->languages_data[ $this->default_language ]['country_flag_url'] ); ?>">
						<?php
					}
					echo $this->default_language;
					if ( isset( $this->languages_data[ $this->default_language ]['translated_name'] ) ) {
						echo '(' . $this->languages_data[ $this->default_language ]['translated_name'] . '):';
					}
					?></label>
            </p>
			<?php
		}
	}

	public function wotv_admin_choose_default_shipping_carrier() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['action_nonce'] ) || ! wp_verify_nonce( $_POST['action_nonce'], 'vi_wot_setting_action_nonce' ) ) {
			return;
		}
		$carrier_slug = isset( $_POST['carrier_slug'] ) ? sanitize_text_field( $_POST['carrier_slug'] ) : '';
		if ( $carrier_slug ) {
			$args                             = $this->settings->get_params();
			$args['shipping_carrier_default'] = $carrier_slug;
			update_option( 'woo_orders_tracking_settings', $args );
			wp_send_json(
				array(
					'status'  => 'success',
					'default' => $carrier_slug,
				)
			);

		} else {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'not enough info',
					'details' => array( 'slug' => $carrier_slug )
				)
			);
		}
	}

	public function wotv_admin_delete_shipping_carrier() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['action_nonce'] ) || ! wp_verify_nonce( $_POST['action_nonce'], 'vi_wot_setting_action_nonce' ) ) {
			return;
		}
		$carrier_slug = isset( $_POST['carrier_slug'] ) ? sanitize_text_field( $_POST['carrier_slug'] ) : '';
		if ( $carrier_slug ) {
			$args     = $this->settings->get_params();
			$position = '';
			$carriers = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_custom_carriers();
			if ( count( $carriers ) ) {
				foreach ( $carriers as $shipping_carrier ) {
					if ( $shipping_carrier["slug"] === $carrier_slug ) {
						$position = array_search( $shipping_carrier, $carriers );
						break;
					} else {
						continue;
					}
				}
				array_splice( $carriers, $position, 1 );
				$args['custom_carriers_list'] = json_encode( $carriers );
				update_option( 'woo_orders_tracking_settings', $args );
				wp_send_json(
					array(
						'status'   => 'success',
						'position' => $position,
					)
				);
			} else {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => 'can\'t delete carrier',
						'details' => array( 'custom_carriers_list' => $carriers )
					)
				);
			}
		} else {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'not enough info',
					'details' => array( 'slug' => $carrier_slug )
				)
			);
		}
	}

	public function wotv_admin_edit_shipping_carrier() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['action_nonce'] ) || ! wp_verify_nonce( $_POST['action_nonce'], 'vi_wot_setting_action_nonce' ) ) {
			return;
		}
		$carrier_slug     = isset( $_POST['carrier_slug'] ) ? sanitize_text_field( $_POST['carrier_slug'] ) : '';
		$carrier_name     = isset( $_POST['carrier_name'] ) ? sanitize_text_field( $_POST['carrier_name'] ) : '';
		$display_name     = empty( $_POST['display_name'] ) ? $carrier_name : sanitize_text_field( $_POST['display_name'] );
		$shipping_country = isset( $_POST['shipping_country'] ) ? sanitize_text_field( $_POST['shipping_country'] ) : '';
		$tracking_url     = isset( $_POST['tracking_url'] ) ? sanitize_text_field( $_POST['tracking_url'] ) : '';
		$digital_delivery = isset( $_POST['digital_delivery'] ) ? sanitize_text_field( $_POST['digital_delivery'] ) : '';
		if ( $carrier_slug && $carrier_name && $shipping_country && $tracking_url ) {
			$args     = array();
			$carriers = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_custom_carriers();
			if ( count( $carriers ) ) {
				foreach ( $carriers as $key => $shipping_carrier ) {
					if ( $shipping_carrier['slug'] === $carrier_slug ) {
						$shipping_carrier['name']             = $carrier_name;
						$shipping_carrier['display_name']     = $display_name;
						$shipping_carrier['country']          = $shipping_country;
						$shipping_carrier['url']              = $tracking_url;
						$shipping_carrier['digital_delivery'] = $digital_delivery;
						$carriers[ $key ]                     = $shipping_carrier;
						$args['custom_carriers_list']         = json_encode( $carriers );
						$args                                 = wp_parse_args( $args, $this->settings->get_params() );
						update_option( 'woo_orders_tracking_settings', $args );
						wp_send_json(
							array(
								'status'           => 'success',
								'carrier_name'     => $carrier_name,
								'display_name'     => $display_name,
								'shipping_country' => $shipping_country,
								'tracking_url'     => $tracking_url,
								'digital_delivery' => $digital_delivery,
							)
						);
					}
				}
			}
			$defined_carriers = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_defined_carriers();
			foreach ( $defined_carriers as $key => $shipping_carrier ) {
				if ( $shipping_carrier['slug'] === $carrier_slug ) {
					$shipping_carrier['display_name']      = $display_name;
					$defined_carriers[ $key ]              = $shipping_carrier;
					$args['shipping_carriers_define_list'] = json_encode( $defined_carriers );
					$args                                  = wp_parse_args( $args, $this->settings->get_params() );
					update_option( 'woo_orders_tracking_settings', $args );
					wp_send_json(
						array(
							'status'           => 'success',
							'carrier_name'     => $carrier_name,
							'display_name'     => $display_name,
							'shipping_country' => $shipping_country,
							'tracking_url'     => $tracking_url,
							'digital_delivery' => $digital_delivery,
						)
					);
				}
			}
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'can\'t edit carrier',
					'details' => array( 'custom_carriers_list' => $carriers )
				)
			);
		} else {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'not enough info',
					'details' => array(
						'name'    => $carrier_name,
						'slug'    => $carrier_slug,
						'country' => $shipping_country,
						'url'     => $tracking_url
					)

				)
			);
		}
	}

	public function wotv_admin_add_new_shipping_carrier() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['action_nonce'] ) || ! wp_verify_nonce( $_POST['action_nonce'], 'vi_wot_setting_action_nonce' ) ) {
			return;
		}
		$carrier_name     = isset( $_POST['carrier_name'] ) ? sanitize_text_field( $_POST['carrier_name'] ) : '';
		$display_name     = empty( $_POST['display_name'] ) ? $carrier_name : sanitize_text_field( $_POST['display_name'] );
		$tracking_url     = isset( $_POST['tracking_url'] ) ? sanitize_text_field( $_POST['tracking_url'] ) : '';
		$shipping_country = isset( $_POST['shipping_country'] ) ? sanitize_text_field( $_POST['shipping_country'] ) : '';
		$digital_delivery = isset( $_POST['digital_delivery'] ) ? sanitize_text_field( $_POST['digital_delivery'] ) : '';
		if ( $carrier_name && $tracking_url && $shipping_country ) {
			$args                         = $this->settings->get_params();
			$custom_carriers_list         = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_custom_carriers();
			$custom_carrier               = array(
				'name'             => $carrier_name,
				'display_name'     => $display_name,
				'slug'             => 'custom_' . time(),
				'url'              => $tracking_url,
				'country'          => $shipping_country,
				'type'             => 'custom',
				'digital_delivery' => $digital_delivery,
			);
			$custom_carriers_list[]       = $custom_carrier;
			$args['custom_carriers_list'] = json_encode( $custom_carriers_list );
			update_option( 'woo_orders_tracking_settings', $args );
			wp_send_json(
				array(
					'status'  => 'success',
					'carrier' => $custom_carrier,
				)
			);
		} else {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'not enough info',
					'details' => array(
						'carrier_name'     => $carrier_name,
						'tracking_url'     => $tracking_url,
						'shipping_country' => $shipping_country
					)
				)
			);
		}
	}

	public function preview_emails_button( $editor_id ) {
		global $pagenow;
		if ( $pagenow == 'admin.php' && isset( $_REQUEST['page'] ) && sanitize_text_field( $_REQUEST['page'] ) == 'woocommerce-orders-tracking' ) {

			$editor_ids = array( 'wot-email-content' );
			if ( count( $this->languages ) ) {
				foreach ( $this->languages as $key => $value ) {
					$editor_ids[] = 'wot-email-content_' . $value;
				}
			}
			if ( in_array( $editor_id, $editor_ids ) ) {

				?>

                <span class="<?php echo esc_attr( self::set( 'preview-emails-button' ) ) ?> button"

                      data-wot_language="<?php echo esc_attr( str_replace( 'wot-email-content', '', $editor_id ) ) ?>"><?php esc_html_e( 'Preview emails', 'woocommerce-orders-tracking' ) ?></span>

				<?php

			}

		}

	}


	public function wot_preview_emails() {
		$shortcodes = array(
			'order_number'                => 2020,
			'order_status'                => 'processing',
			'order_date'                  => date_i18n( 'F d, Y', strtotime( 'today' ) ),
			'order_total'                 => 999,
			'order_subtotal'              => 990,
			'items_count'                 => 1,
			'payment_method'              => 'Cash on delivery',
			'shipping_method'             => 'Free shipping',
			'shipping_address'            => 'Thainguyen City',
			'formatted_shipping_address'  => 'Thainguyen City, Vietnam',
			'billing_address'             => 'Thainguyen City',
			'formatted_billing_address'   => 'Thainguyen City, Vietnam',
			'billing_country'             => 'VN',
			'billing_city'                => 'Thainguyen',
			'billing_first_name'          => 'John',
			'billing_last_name'           => 'Doe',
			'formatted_billing_full_name' => 'John Doe',
			'billing_email'               => 'support@villatheme.com',
			'shop_title'                  => get_bloginfo(),
			'home_url'                    => home_url(),
			'shop_url'                    => get_option( 'woocommerce_shop_page_id', '' ) ? get_page_link( get_option( 'woocommerce_shop_page_id' ) ) : '',
		);

		$content                      = isset( $_GET['content'] ) ? wp_kses_post( stripslashes( $_GET['content'] ) ) : '';
		$email_column_tracking_number = isset( $_GET['email_column_tracking_number'] ) ? wp_kses_post( stripslashes( $_GET['email_column_tracking_number'] ) ) : '';
		$email_column_carrier_name    = isset( $_GET['email_column_carrier_name'] ) ? wp_kses_post( stripslashes( $_GET['email_column_carrier_name'] ) ) : '';
		$email_column_tracking_url    = isset( $_GET['email_column_tracking_url'] ) ? wp_kses_post( stripslashes( $_GET['email_column_tracking_url'] ) ) : '';
		$heading                      = isset( $_GET['heading'] ) ? ( stripslashes( $_GET['heading'] ) ) : '';
		$heading                      = str_replace( array(
			'{order_id}',
			'{billing_first_name}',
			'{billing_last_name}'
		), array(
			$shortcodes['order_number'],
			$shortcodes['billing_first_name'],
			$shortcodes['billing_last_name']
		), $heading );
		$service_tracking_page        = $this->settings->get_params( 'service_tracking_page' );
		$imported                     = array(
			array(
				'order_item_name' => "T-shirt",
				'tracking_number' => "LTxxxxxxxxxCN",
				'carrier_name'    => "ePacket",
				'tracking_url'    => $service_tracking_page ? get_page_link( $service_tracking_page ) : home_url(),
			),
			array(
				'order_item_name' => "Legging",
				'tracking_number' => "LTyyyyyyyyyCN",
				'carrier_name'    => "UPS",
				'tracking_url'    => $service_tracking_page ? get_page_link( $service_tracking_page ) : home_url(),
			),
		);
		ob_start();
		?>
        <table class="<?php echo esc_attr( self::set( 'preview-email-table' ) ) ?>" cellspacing="0" cellpadding="6"
               border="1">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Product title', 'woocommerce-orders-tracking' ) ?></th>
				<?php
				if ( $email_column_tracking_number ) {
					?>
                    <th><?php esc_html_e( 'Tracking number', 'woocommerce-orders-tracking' ) ?></th>
					<?php
				}
				if ( $email_column_carrier_name ) {
					?>
                    <th><?php esc_html_e( 'Carrier name', 'woocommerce-orders-tracking' ) ?></th>
					<?php
				}
				if ( $email_column_tracking_url ) {
					?>
                    <th><?php esc_html_e( 'Tracking link', 'woocommerce-orders-tracking' ) ?></th>
					<?php
				}
				?>
            </tr>
            </thead>
            <tbody>
			<?php
			foreach ( $imported as $item ) {
				?>
                <tr>
                    <td><?php esc_html_e( $item['order_item_name'] ); ?></td>
					<?php
					if ( $email_column_tracking_number ) {
						?>
                        <td><?php echo str_replace( array(
								'{tracking_number}',
								'{carrier_name}',
								'{tracking_url}',
							), array(
								$item['tracking_number'],
								$item['carrier_name'],
								$item['tracking_url'],
							), $email_column_tracking_number ); ?></td>
						<?php
					}
					if ( $email_column_carrier_name ) {
						?>
                        <td><?php echo str_replace( array(
								'{tracking_number}',
								'{carrier_name}',
								'{tracking_url}',
							), array(
								$item['tracking_number'],
								$item['carrier_name'],
								$item['tracking_url'],
							), $email_column_carrier_name ); ?></td>
						<?php
					}
					if ( $email_column_tracking_url ) {
						?>
                        <td><?php echo str_replace( array(
								'{tracking_number}',
								'{carrier_name}',
								'{tracking_url}',
							), array(
								$item['tracking_number'],
								$item['carrier_name'],
								$item['tracking_url'],
							), $email_column_tracking_url ); ?></td>
						<?php
					}
					?>
                </tr>
				<?php
			}
			?>
            </tbody>
        </table>
		<?php
		$tracking_table = ob_get_clean();
		$content        = str_replace( array(
			'{order_id}',
			'{billing_first_name}',
			'{billing_last_name}',
			'{tracking_table}'
		), array(
			$shortcodes['order_number'],
			$shortcodes['billing_first_name'],
			$shortcodes['billing_last_name'],
			$tracking_table
		), $content );
		$mailer         = WC()->mailer();
		$email          = new WC_Email();
		$content        = $email->style_inline( $mailer->wrap_message( $heading, $content ) );
		wp_send_json(
			array(
				'html' => $content,
			)
		);
	}

	public function wot_test_connection_paypal() {
		$client_id = isset( $_POST['client_id'] ) ? sanitize_text_field( $_POST['client_id'] ) : '';
		$secret    = isset( $_POST['secret'] ) ? sanitize_text_field( $_POST['secret'] ) : '';
		$sandbox   = isset( $_POST['sandbox'] ) ? sanitize_text_field( $_POST['sandbox'] ) : '';
		if ( $secret && $sandbox && $client_id ) {
			if ( $sandbox === 'no' ) {
				$sandbox = false;
			}
			$url         = VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_PAYPAL::get_request_url( $sandbox );
			$check_token = VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_PAYPAL::get_access_token( $client_id, $secret, $url );
			if ( $check_token['status'] === 'success' ) {
				$message = '<p class="' . esc_attr( self::set( 'success' ) ) . '">' . esc_html__( 'Successfully!', 'woocommerce-orders-tracking' ) . '</p>';
			} else {
				$message = '<p class="' . esc_attr( self::set( 'error' ) ) . '">' . $check_token['data'] . '</p>';
			}
			wp_send_json(
				array(
					'message' => $message
				)
			);
		}
	}

	public function orders_tracking_admin_footer() {
		$countries = new WC_Countries();
		$countries = $countries->get_countries();
		?>
        <div class="preview-emails-html-container woo-orders-tracking-footer-container woo-orders-tracking-hidden">
            <div class="preview-emails-html-overlay woo-orders-tracking-overlay"></div>
            <div class="preview-emails-html woo-orders-tracking-footer-content"></div>
        </div>
        <div class="edit-shipping-carrier-html-container woo-orders-tracking-footer-container woo-orders-tracking-hidden">
            <div class="edit-shipping-carrier-html-overlay woo-orders-tracking-overlay"></div>
            <div class="edit-shipping-carrier-html-content woo-orders-tracking-footer-content">
                <div class="edit-shipping-carrier-html-content-header">
                    <h2><?php esc_html_e( 'Edit shipping carrier', 'woocommerce-orders-tracking' ) ?></h2>
                    <i class="close icon edit-shipping-carrier-html-content-close"></i>
                </div>
                <div class="edit-shipping-carrier-html-content-body">
                    <div class="edit-shipping-carrier-html-content-body-row">
                        <div class="edit-shipping-carrier-html-content-body-carrier-name-wrap">
                            <label for="edit-shipping-carrier-html-content-body-carrier-name"><?php esc_html_e( 'Carrier Name', 'woocommerce-orders-tracking' ) ?></label>
                            <input type="text" id="edit-shipping-carrier-html-content-body-carrier-name">
                        </div>
                        <div class="edit-shipping-carrier-html-content-body-carrier-display-name-wrap">
                            <label for="edit-shipping-carrier-html-content-body-carrier-display-name"><?php esc_html_e( 'Display Name', 'woocommerce-orders-tracking' ) ?></label>
                            <input type="text" id="edit-shipping-carrier-html-content-body-carrier-display-name">
                        </div>
                        <div class="edit-shipping-carrier-html-content-body-country-wrap">
                            <label for="edit-shipping-carrier-html-content-body-country"><?php esc_html_e( 'Shipping Country', 'woocommerce-orders-tracking' ) ?></label>
                            <select name="" id="edit-shipping-carrier-html-content-body-country"
                                    class="edit-shipping-carrier-html-content-body-country">
                                <option value=""></option>
                                <option value="Global"><?php esc_html_e( 'Global', 'woocommerce-orders-tracking' ) ?></option>
								<?php
								foreach ( $countries as $country_code => $country_name ) {
									?>
                                    <option value="<?php echo esc_attr( $country_code ) ?>"><?php esc_html_e( $country_name ) ?></option>
									<?php
								}
								?>
                            </select>
                        </div>
                    </div>
                    <div class="edit-shipping-carrier-html-content-body-row">
                        <div>
                            <input type="checkbox"
                                   id="edit-shipping-carrier-is-digital-delivery"
                                   class="edit-shipping-carrier-is-digital-delivery">
                            <label for="edit-shipping-carrier-is-digital-delivery"><?php esc_html_e( 'Check if this is a Digital Delivery carrier. Tracking number is not required for this kind of carrier', 'woocommerce-orders-tracking' ) ?></label>
                        </div>
                    </div>
                    <div class="edit-shipping-carrier-html-content-body-row">
                        <div class="edit-shipping-carrier-html-content-body-carrier-url-wrap">
                            <label for="edit-shipping-carrier-html-content-body-carrier-url"><?php esc_html_e( 'Carrier URL', 'woocommerce-orders-tracking' ) ?></label>
                            <input type="text" id="edit-shipping-carrier-html-content-body-carrier-url"
                                   placeholder="http://yourcarrier.com/{tracking_number}">
                            <p class="description">
                                <strong>{tracking_number}</strong>: <?php esc_html_e( 'The placeholder for tracking number of an item', 'woocommerce-orders-tracking' ) ?>
                            </p>
                            <p class="description">
                                <strong>{postal_code}</strong>:<?php esc_html_e( 'The placeholder for postal code of an order', 'woocommerce-orders-tracking' ) ?>
                            </p>
                            <p class="description"><?php esc_html_e( 'eg: https://www.dhl.com/en/express/tracking.html?AWB={tracking_number}&brand=DHL', 'woocommerce-orders-tracking' ); ?></p>
                            <p class="description wotv-error-tracking-url"><?php esc_html_e( 'The tracking url will not include tracking number if message does not include {tracking_number}', 'woocommerce-orders-tracking' ) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="edit-shipping-carrier-html-content-footer">
                    <button type="button"
                            class="vi-ui button primary edit-shipping-carrier-html-btn-save">
						<?php esc_html_e( 'Save', 'woocommerce-orders-tracking' ) ?>
                    </button>
                    <button type="button"
                            class="vi-ui button edit-shipping-carrier-html-btn-cancel">
						<?php esc_html_e( 'Cancel', 'woocommerce-orders-tracking' ) ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="add-new-shipping-carrier-html-container woo-orders-tracking-footer-container woo-orders-tracking-hidden">
            <div class="add-new-shipping-carrier-html-overlay woo-orders-tracking-overlay"></div>
            <div class="add-new-shipping-carrier-html-content woo-orders-tracking-footer-content">
                <div class="add-new-shipping-carrier-html-content-header">
                    <h2><?php esc_html_e( 'Add custom shipping carrier', 'woocommerce-orders-tracking' ) ?></h2>
                    <i class="close icon add-new-shipping-carrier-html-content-close"></i>
                </div>
                <div class="add-new-shipping-carrier-html-content-body">
                    <div class="add-new-shipping-carrier-html-content-body-row">
                        <div class="add-new-shipping-carrier-html-content-body-carrier-name-wrap">
                            <label for="add-new-shipping-carrier-html-content-body-carrier-name"><?php esc_html_e( 'Carrier Name', 'woocommerce-orders-tracking' ) ?></label>
                            <input type="text" id="add-new-shipping-carrier-html-content-body-carrier-name">
                        </div>
                        <div class="add-new-shipping-carrier-html-content-body-carrier-display-name-wrap">
                            <label for="add-new-shipping-carrier-html-content-body-carrier-display-name"><?php esc_html_e( 'Display Name', 'woocommerce-orders-tracking' ) ?></label>
                            <input type="text" id="add-new-shipping-carrier-html-content-body-carrier-display-name">
                        </div>
                        <div class="add-new-shipping-carrier-html-content-body-country-wrap">
                            <label for="add-new-shipping-carrier-html-content-body-country"><?php esc_html_e( 'Shipping Country', 'woocommerce-orders-tracking' ) ?></label>
                            <select name="" id="add-new-shipping-carrier-html-content-body-country"
                                    class="add-new-shipping-carrier-html-content-body-country">
                                <option value="Global"
                                        selected><?php esc_html_e( 'Global', 'woocommerce-orders-tracking' ) ?></option>
								<?php
								foreach ( $countries as $country_code => $country_name ) {
									?>
                                    <option value="<?php echo esc_attr( $country_code ) ?>"><?php esc_html_e( $country_name ) ?></option>
									<?php
								}
								?>
                            </select>
                        </div>
                    </div>
                    <div class="add-new-shipping-carrier-html-content-body-row">
                        <div>
                            <input type="checkbox"
                                   id="add-new-shipping-carrier-is-digital-delivery"
                                   class="add-new-shipping-carrier-is-digital-delivery">
                            <label for="add-new-shipping-carrier-is-digital-delivery"><?php esc_html_e( 'Check if this is a Digital Delivery carrier. Tracking number is not required for this kind of carrier', 'woocommerce-orders-tracking' ) ?></label>
                        </div>
                    </div>
                    <div class="add-new-shipping-carrier-html-content-body-row">
                        <div class="add-new-shipping-carrier-html-content-body-carrier-url-wrap">
                            <label for="add-new-shipping-carrier-html-content-body-carrier-url"><?php esc_html_e( 'Tracking URL', 'woocommerce-orders-tracking' ) ?></label>
                            <input type="text" id="add-new-shipping-carrier-html-content-body-carrier-url"
                                   placeholder="http://yourcarrier.com/{tracking_number}">
                            <p class="description">
                                <strong>{tracking_number}</strong>: <?php esc_html_e( 'The placeholder for tracking number of an item', 'woocommerce-orders-tracking' ) ?>
                            </p>
                            <p class="description">
                                <strong>{postal_code}</strong>:<?php esc_html_e( 'The placeholder for postal code of an order', 'woocommerce-orders-tracking' ) ?>
                            </p>
                            <p class="description"><?php esc_html_e( 'eg: https://www.dhl.com/en/express/tracking.html?AWB={tracking_number}&brand=DHL', 'woocommerce-orders-tracking' ); ?></p>
                            <p class="description wotv-error-tracking-url"><?php esc_html_e( 'The tracking url will not include tracking number if message does not include {tracking_number}', 'woocommerce-orders-tracking' ) ?></p>
                        </div>
                    </div>
                </div>
                <div class="add-new-shipping-carrier-html-content-footer">
                    <button type="button"
                            class="vi-ui button primary add-new-shipping-carrier-html-btn-save">
						<?php esc_html_e( 'Add New', 'woocommerce-orders-tracking' ) ?>
                    </button>
                    <button type="button"
                            class="vi-ui button add-new-shipping-carrier-html-btn-cancel">
						<?php esc_html_e( 'Cancel', 'woocommerce-orders-tracking' ) ?>
                    </button>
                </div>
            </div>
        </div>
		<?php
	}

	public static function admin_enqueue_semantic() {
		wp_enqueue_style( 'semantic-ui-message', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'message.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-input', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'input.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-label', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'label.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-accordion', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'accordion.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-button', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'button.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-checkbox', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'checkbox.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-dropdown', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'dropdown.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-form', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'form.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-input', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'input.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-popup', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'popup.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-icon', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'icon.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-menu', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'menu.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-segment', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'segment.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-tab', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'tab.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_style( 'semantic-ui-table', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'table.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_script( 'semantic-ui-accordion', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'accordion.min.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_script( 'semantic-ui-address', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'address.min.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_script( 'semantic-ui-checkbox', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'checkbox.min.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_script( 'semantic-ui-dropdown', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'dropdown.min.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_script( 'semantic-ui-form', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'form.min.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
		wp_enqueue_script( 'semantic-ui-tab', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'tab.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
	}

	public function admin_enqueue_script() {
		global $pagenow;
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-orders-tracking' ) {
			self::admin_enqueue_semantic();
			add_action( 'admin_footer', array( $this, 'orders_tracking_admin_footer' ) );
			$this->schedule_send_emails = wp_next_scheduled( 'vi_wot_send_mails_for_import_csv_function' );
			wp_enqueue_style( 'vi-wot-admin-setting-css', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'admin-setting.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			wp_enqueue_style( 'vi-wot-admin-setting-support', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'villatheme-support.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array(
				'jquery-ui-draggable',
				'jquery-ui-slider',
				'jquery-touch-punch'
			), false, 1 );

			wp_enqueue_script( 'vi-wot-admin-setting-carrier-functions-js', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . '/carrier-functions.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			if ( ! wp_script_is( 'transition' ) ) {
				wp_enqueue_style( 'transition', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'transition.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
				wp_enqueue_script( 'transition', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'transition.min.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			}
			if ( ! wp_script_is( 'select2' ) ) {
				wp_enqueue_style( 'select2', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'select2.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
				wp_enqueue_script( 'select2', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'select2.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			}
			wp_enqueue_script( 'vi-wot-admin-setting-js', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'admin-setting.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			$countries                = new WC_Countries();
			$this->shipping_countries = $countries->get_countries();
			wp_localize_script(
				'vi-wot-admin-setting-js',
				'vi_wot_admin_settings',
				array(
					'ajax_url'                      => admin_url( 'admin-ajax.php' ),
					'shipping_carrier_default'      => $this->settings->get_params( 'shipping_carrier_default' ),
					'custom_carriers_list'          => $this->settings->get_params( 'custom_carriers_list' ),
					'shipping_carriers_define_list' => $this->settings->get_params( 'shipping_carriers_define_list' ),
					'shipping_countries'            => $this->shipping_countries,
					'service_carriers_list'         => array_keys( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::service_carriers_list() ),
					'select_default_carrier_text'   => esc_html__( 'Set Default', 'woocommerce-orders-tracking' ),
					'add_new_error_empty_field'     => esc_html__( 'Please fill full information for carrier', 'woocommerce-orders-tracking' ),
					'confirm_delete_carrier_custom' => esc_html__( 'Are you sure you want to delete this carrier?', 'woocommerce-orders-tracking' ),
					'confirm_delete_string_replace' => esc_html__( 'Remove this item?', 'woocommerce-orders-tracking' ),
					'display_name_title'            => esc_html__( 'Display name: your customers see this instead of real carrier name', 'woocommerce-orders-tracking' ),
				)
			);
		}
	}

	public static function table_of_placeholders( $args ) {
		if ( count( $args ) ) {
			?>
            <table class="vi-ui celled table <?php echo esc_attr( self::set( 'table-of-placeholders' ) ) ?>">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Placeholder', 'woocommerce-orders-tracking' ) ?></th>
                    <th><?php esc_html_e( 'Explanation', 'woocommerce-orders-tracking' ) ?></th>
                </tr>
                </thead>
                <tbody>
				<?php
				foreach ( $args as $key => $value ) {
					?>
                    <tr>
                        <td class="<?php echo esc_attr( self::set( 'placeholder-value-container' ) ) ?>"><input
                                    class="<?php echo esc_attr( self::set( 'placeholder-value' ) ) ?>" type="text"
                                    readonly value="<?php echo esc_attr( "{{$key}}" ); ?>"><i
                                    class="vi-ui icon copy <?php echo esc_attr( self::set( 'placeholder-value-copy' ) ) ?>"
                                    title="<?php esc_attr_e( 'Copy', 'woocommerce-orders-tracking' ) ?>"></i></td>
                        <td><?php echo esc_html( "{$value}" ); ?></td>
                    </tr>
					<?php
				}
				?>
                </tbody>
            </table>
			<?php
		}
	}

	public function search_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$keyword = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );
		if ( ! $keyword ) {
			$keyword = filter_input( INPUT_POST, 'keyword', FILTER_SANITIZE_STRING );
		}
		if ( empty( $keyword ) ) {
			die();
		}
		$args      = array(
			'post_status'    => 'any',
			'post_type'      => 'page',
			'posts_per_page' => 50,
			's'              => $keyword
		);
		$the_query = new WP_Query( $args );
		$items     = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$items[] = array( 'id' => get_the_ID(), 'text' => get_the_title() );
			}
		}
		wp_reset_postdata();
		wp_send_json( $items );
	}

	public function print_other_country_flag( $param, $lang ) {
		?>
        <p>
            <label for="<?php echo esc_attr( self::set( "{$param}_{$lang}" ) ); ?>"><?php
				if ( isset( $this->languages_data[ $lang ]['country_flag_url'] ) && $this->languages_data[ $lang ]['country_flag_url'] ) {
					?>
                    <img src="<?php echo esc_url( $this->languages_data[ $lang ]['country_flag_url'] ); ?>">
					<?php
				}
				echo $lang;
				if ( isset( $this->languages_data[ $lang ]['translated_name'] ) ) {
					echo '(' . $this->languages_data[ $lang ]['translated_name'] . ')';
				}
				?>:</label>
        </p>
		<?php
	}

	public static function get_email_templates( $type = 'wot_email' ) {
		$email_templates = array();
		if ( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::is_email_template_customizer_active() ) {
			$email_templates = viwec_get_emails_list( $type );
		}

		return $email_templates;
	}

}