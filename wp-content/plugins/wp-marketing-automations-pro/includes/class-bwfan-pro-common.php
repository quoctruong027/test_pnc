<?php

class BWFAN_PRO_Common {

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'schedule_cron' ) );

		register_deactivation_hook( BWFAN_PRO_PLUGIN_FILE, array( __CLASS__, 'deactivation' ) );

		add_action( 'bwfan_run_midnight_cron', array( __CLASS__, 'run_midnight_cron' ) );
		add_action( 'bwfan_automation_saved', array( __CLASS__, 'check_if_event_is_independent' ) );
		add_action( 'bwfan_run_independent_automation', array( __CLASS__, 'make_independent_events_tasks' ) );
		add_action( 'bwfan_send_affiliate_insights', array( __CLASS__, 'send_affiliate_insights' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'bwfan_add_plugin_endpoint_pro' ) );

		add_filter( 'bwfan_select2_ajax_callable', array( __CLASS__, 'get_callable_object' ), 1, 2 );

		add_filter( 'bwfan_send_async_call_data', array( __CLASS__, 'passing_event_language' ), 10, 1 );

		/** Send test data to zapier zap */
		add_action( 'wp_ajax_bwf_test_zap', array( __CLASS__, 'test_zap' ) );

		/** Send test data to zapier zap */
		add_action( 'wp_ajax_bwf_send_test_http_post', array( __CLASS__, 'send_test_http_post' ) );

		/** Delete automation tasks after paid order */
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'delete_automation_tasks' ), 11, 4 );

		/** 5 min action callback */
		add_action( 'bwfan_5_minute_worker', array( __CLASS__, 'five_minute_worker_cb' ) );

		/** populate cart from order **/
		add_action( 'wp', array( __CLASS__, 'bwfan_populate_cart_from_order' ), 999 );

		/** additional data in case of handle_utm_grabber plugin is active for abandoned cart **/

		add_filter( 'bwfan_ab_change_checkout_data_for_external_use', array( __CLASS__, 'bwfan_populate_utm_grabber_data_cart' ), 999, 1 );
	}

	public static function send_test_http_post() {
		BWFAN_Common::check_nonce(); // phpcs:disable WordPress.Security.NonceVerification

		$response = [];
		try {
			$post = $_POST;
			$url  = $post['data']['url'];

			BWFAN_Merge_Tag_Loader::set_data( array(
				'is_preview' => true,
			) );

			$fields        = $post['data']['custom_fields']['field'];
			$fields_value  = $post['data']['custom_fields']['field_value'];
			$custom_fields = array();
			foreach ( $fields as $key1 => $field_id ) {
				$custom_fields[ $field_id ] = BWFAN_Common::decode_merge_tags( $fields_value[ $key1 ] );
			}

			$fields       = $post['data']['headers']['field'];
			$fields_value = $post['data']['headers']['field_value'];
			$headers      = array();
			foreach ( $fields as $key1 => $field_id ) {
				$headers[ $field_id ] = BWFAN_Common::decode_merge_tags( $fields_value[ $key1 ] );
			}

			$action_object             = BWFAN_Core()->integration->get_action( 'wp_http_post' );
			$action_object->is_preview = true;
			$action_object->set_data( array(
				'headers'       => $headers,
				'custom_fields' => $custom_fields,
				'url'           => $url
			) );
			$response = $action_object->process();
		} catch ( Exception $e ) {
			wp_send_json( array(
				'status' => false,
				'msg'    => $e->getMessage(),
			) );
		}

		if ( empty( $response ) || 200 !== $response['response'] ) {
			wp_send_json( array(
				'status' => false,
				'msg'    => __( 'Error: Response code is not 200', 'autonami-automations-pro' ),
			) );
		}

		wp_send_json( array(
			'status' => true,
			'msg'    => __( 'Test Data posted successfully!', 'autonami-automations-pro' ),
		) );
	}

	/**
	 *
	 * @param $order_id
	 * @param $from
	 * @param $to
	 * @param $order
	 */
	public static function delete_automation_tasks( $order_id, $from, $to, $order ) {

		$failed_statuses = [ 'pending', 'failed', 'cancelled' ];

		if ( ! in_array( $from, $failed_statuses, true ) || in_array( $to, $failed_statuses, true ) ) {
			return;
		}

		/** Order status verified, we can proceed to delete scheduled tasks of the order email or phone */
		$url  = home_url();
		$url  = add_query_arg( array(
			'rest_route' => '/autonami/v1/delete-automation-tasks',
		), $url );
		$args = [
			'method'    => 'POST',
			'body'      => array(
				'unique_key' => get_option( 'bwfan_u_key', false ),
				'order_id'   => $order_id,
			),
			'timeout'   => 0.1,
			'sslverify' => false,
		];
		wp_remote_post( $url, $args );
	}

	/**
	 * Make a new endpoint which will receive the event data
	 */
	public static function bwfan_add_plugin_endpoint_pro() {

		register_rest_route( 'autonami/v1', '/delete-automation-tasks', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'delete_automation_tasks_by_automation_ids' ),
			'permission_callback' => '__return_true',
		) );
	}


	/**
	 * passing grabber utm data to cart abandoned
	 */
	public static function bwfan_populate_utm_grabber_data_cart( $data ) {

		if ( ! bwfan_is_handle_utm_grabber_active() && ! is_plugin_active( 'handl-utm-grabber-v3/handl-utm-grabber-v3.php' ) ) {
			return $data;
		}

		$handle_utm_grabber_data = array();

		if ( isset( $_COOKIE['utm_campaign'] ) && ! empty( $_COOKIE['utm_campaign'] ) ) {
			$handle_utm_grabber_data['utm_campaign'] = $_COOKIE['utm_campaign'];
		}

		if ( isset( $_COOKIE['utm_source'] ) && ! empty( $_COOKIE['utm_source'] ) ) {
			$handle_utm_grabber_data['utm_source'] = $_COOKIE['utm_source'];
		}

		if ( isset( $_COOKIE['utm_term'] ) && ! empty( $_COOKIE['utm_term'] ) ) {
			$handle_utm_grabber_data['utm_term'] = $_COOKIE['utm_term'];
		}

		if ( isset( $_COOKIE['utm_medium'] ) && ! empty( $_COOKIE['utm_medium'] ) ) {
			$handle_utm_grabber_data['utm_medium'] = $_COOKIE['utm_medium'];
		}

		if ( isset( $_COOKIE['utm_content'] ) && ! empty( $_COOKIE['utm_content'] ) ) {
			$handle_utm_grabber_data['utm_content'] = $_COOKIE['utm_content'];
		}

		if ( isset( $_COOKIE['gclid'] ) && ! empty( $_COOKIE['gclid'] ) ) {
			$handle_utm_grabber_data['gclid'] = $_COOKIE['gclid'];
		}

		if ( isset( $_COOKIE['handl_original_ref'] ) && ! empty( $_COOKIE['handl_original_ref'] ) ) {
			$handle_utm_grabber_data['handl_original_ref'] = $_COOKIE['handl_original_ref'];
		}

		if ( isset( $_COOKIE['handl_landing_page'] ) && ! empty( $_COOKIE['handl_landing_page'] ) ) {
			$handle_utm_grabber_data['handl_landing_page'] = $_COOKIE['handl_landing_page'];
		}

		if ( isset( $_COOKIE['handl_ip'] ) && ! empty( $_COOKIE['handl_ip'] ) ) {
			$handle_utm_grabber_data['handl_ip'] = $_COOKIE['handl_ip'];
		}

		if ( isset( $_COOKIE['handl_ref'] ) && ! empty( $_COOKIE['handl_ref'] ) ) {
			$handle_utm_grabber_data['handl_ref'] = $_COOKIE['handl_ref'];
		}

		if ( isset( $_COOKIE['handl_url'] ) && ! empty( $_COOKIE['handl_url'] ) ) {
			$handle_utm_grabber_data['handl_url'] = $_COOKIE['handl_url'];
		}

		$data['handle_utm_grabber'] = $handle_utm_grabber_data;

		return apply_filters( 'bwfan_external_handl_utm_grabber_data', $data );
	}

	/**
	 * Capture all the action ids of an automation and delete all its tasks except for completed tasks.
	 *
	 * @param WP_REST_Request $request
	 */
	public static function delete_automation_tasks_by_automation_ids( WP_REST_Request $request ) {
		$post_parameters = $request->get_body_params();

		if ( false === is_array( $post_parameters ) || 0 === count( $post_parameters ) ) {
			return;
		}

		if ( ! isset( $post_parameters['order_id'] ) ) {
			return;
		}

		/**
		 * Check Unique key security
		 */
		$unique_key = get_option( 'bwfan_u_key', false );
		if ( false === $unique_key || ! isset( $post_parameters['unique_key'] ) || $post_parameters['unique_key'] !== $unique_key ) {
			return;
		}

		$order_id = $post_parameters['order_id'];
		$order    = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$email = $order->get_billing_email();
		$phone = $order->get_billing_phone();

		do_action( 'bwfan_sync_call_delete_tasks', ( $email ? $email : '' ), ( $phone ? $phone : '' ), $order_id );

	}


	/**
	 * Schedule a wp event which will run once a day.
	 * @throws Exception
	 */
	public static function schedule_cron() {
		if ( ! bwf_has_action_scheduled( 'bwfan_run_midnight_cron' ) ) {
			$date = new DateTime();
			$date->modify( '+1 days' );
			BWFAN_Common::convert_from_gmt( $date ); // convert to site time
			$date->setTime( 0, 0, 0 );
			BWFAN_Common::convert_to_gmt( $date );

			bwf_schedule_recurring_action( $date->getTimestamp(), DAY_IN_SECONDS, 'bwfan_run_midnight_cron' );
		}

		/** Scheduling a 5 min worker */
		if ( ! bwf_has_action_scheduled( 'bwfan_5_minute_worker' ) ) {
			bwf_schedule_recurring_action( time(), MINUTE_IN_SECONDS * 5, 'bwfan_5_minute_worker' );
		}
	}

	/**
	 * Remove autonami events on plugin deactivation.
	 */
	public static function deactivation() {
		bwf_unschedule_actions( 'bwfan_run_midnight_cron' );
	}

	/**
	 * Run once in a day and check all the active automations for events which are time independent.
	 * @throws Exception
	 */
	public static function run_midnight_cron() {
		$active_automations = BWFAN_Core()->automations->get_active_automations();

		if ( ! is_array( $active_automations ) || 0 === count( $active_automations ) ) {
			return;
		}
		do_action( 'bwfan_midnight_cron', $active_automations );
		foreach ( $active_automations as $automation_details ) {
			self::check_if_event_is_independent( $automation_details['id'] );
		}
	}

	/**
	 * Check if the event selected in the automation is time independent or not.
	 *
	 * @param $automation_id
	 *
	 * @throws Exception
	 */
	public static function check_if_event_is_independent( $automation_id ) {
		if ( class_exists( 'WooFunnels_Cache' ) ) {
			$WooFunnels_Cache_obj = WooFunnels_Cache::get_instance();
			$WooFunnels_Cache_obj->set_cache( 'bwfan_automations_meta_' . $automation_id, false, 'autonami' );
		}

		$automation      = BWFAN_Model_Automations::get_automation_with_data( $automation_id );
		$source_instance = BWFAN_Core()->sources->get_source( $automation['source'] );
		$event_instance  = BWFAN_Core()->sources->get_event( $automation['event'] );

		if ( ! $source_instance instanceof BWFAN_Source || ! $event_instance instanceof BWFAN_Event ) {
			return;
		}

		$event_instance->load_hooks();
		$is_event_time_independent = $event_instance->is_time_independent();
		if ( false === $is_event_time_independent ) {
			return;
		}

		$hours   = isset( $automation['meta']['event_meta']['hours'] ) ? $automation['meta']['event_meta']['hours'] : '';
		$minutes = isset( $automation['meta']['event_meta']['minutes'] ) ? $automation['meta']['event_meta']['minutes'] : '';
		if ( empty( $hours ) ) {
			$hours = 00;
		}
		if ( empty( $minutes ) ) {
			$minutes = 00;
		}

		if ( bwf_has_action_scheduled( 'bwfan_run_independent_automation', array( $automation_id ) ) ) {
			bwf_unschedule_actions( 'bwfan_run_independent_automation', array( $automation_id ) );
		}

		bwf_schedule_single_action( BWFAN_Core()->automations->get_automation_execution_time( $hours, $minutes ), 'bwfan_run_independent_automation', array( $automation_id ) );
	}

	/**
	 * Make tasks for those events which are time independent.
	 * Like WC subscription before end or before renewal
	 *
	 * @param $automation_id
	 */
	public static function make_independent_events_tasks( $automation_id ) {
		$automation_details = BWFAN_Model_Automations::get_automation_with_data( $automation_id );

		/** Check if automation is active */
		if ( 1 !== intval( $automation_details['status'] ) ) {
			return;
		}

		$event_instance = BWFAN_Core()->sources->get_event( $automation_details['event'] );

		/** Validate if event exist */
		if ( ! $event_instance instanceof BWFAN_Event ) {
			return;
		}

		/** Halt if not time independent event */
		if ( false === $event_instance->is_time_independent() ) {
			return;
		}

		$event_instance->load_hooks();
		$last_run    = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'last_run' );
		$date_time   = new DateTime();
		$current_day = $date_time->format( 'Y-m-d' );

		if ( empty( $last_run ) || $current_day !== $last_run ) {
			BWFAN_Core()->automations->current_lifecycle_automation_id = $automation_id;
			$event_instance->make_task_data( $automation_id, $automation_details );
		}

	}

	/**
	 * Return funnels of upstroke
	 *
	 * @return array
	 */
	public static function get_upstroke_funnels() {
		$args = array(
			'post_type'      => WFOCU_Common::get_funnel_post_type_slug(),
			'post_status'    => array( 'publish' ),
			'posts_per_page' => - 1,
		);

		$q     = new WP_Query( $args );
		$items = array();
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {

				$q->the_post();
				global $post;

				$items[] = array(
					'id'         => $post->ID,
					'post_title' => $post->post_title,
				);
			}
		}

		return $items;
	}

	/**
	 * Return offers of upstroke
	 *
	 * @return array
	 */
	public static function get_upstroke_offers() {
		$args = array(
			'post_type'      => WFOCU_Common::get_offer_post_type_slug(),
			'post_status'    => array( 'publish' ),
			'posts_per_page' => - 1,
		);

		$q     = new WP_Query( $args );
		$items = array();
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {

				$q->the_post();
				global $post;

				$items[] = array(
					'id'         => $post->ID,
					'post_title' => $post->post_title,
				);
			}
		}

		return $items;
	}

	public static function get_upstroke_funnel_nice_name( $funnel_ids ) {
		$result = [];
		$args   = array(
			'post_type'   => WFOCU_Common::get_funnel_post_type_slug(),
			'numberposts' => - 1,
			'post__in'    => $funnel_ids,
		);

		$funnels = get_posts( $args );
		if ( false === is_array( $funnels ) || 0 === count( $funnels ) ) {
			return $result;
		}

		foreach ( $funnels as $single_funnel ) {
			$result[ $single_funnel->ID ] = $single_funnel->post_title . ' (#' . $single_funnel->ID . ')';
		}

		return $result;
	}

	public static function get_upstroke_offer_nice_name( $offer_ids ) {
		$result = [];
		$args   = array(
			'post_type'   => WFOCU_Common::get_offer_post_type_slug(),
			'numberposts' => - 1,
			'post__in'    => $offer_ids,
		);

		$offers = get_posts( $args );
		if ( false === is_array( $offers ) || 0 === count( $offers ) ) {
			return $result;
		}

		foreach ( $offers as $single_offer ) {
			$result[ $single_offer->ID ] = $single_offer->post_title;
		}

		return $result;
	}

	public static function get_all_active_affiliates() {
		global $wpdb;

		$affiliates = $wpdb->get_results( $wpdb->prepare( "
										SELECT affiliate_id
										FROM {$wpdb->prefix}affiliate_wp_affiliates
										WHERE status = %s
										", 'active' ), ARRAY_A );

		if ( count( $affiliates ) > 0 ) {
			return array_column( $affiliates, 'affiliate_id' );
		}

		return $affiliates;
	}

	public static function send_affiliate_insights( $affiliates ) {
		foreach ( $affiliates['ids'] as $affiliate_id ) {
			$referrals_count = self::get_referrals_count_from_period( $affiliate_id, $affiliates['from'], $affiliates['to'] );
			$visits          = self::get_visits_from_period( $affiliate_id, $affiliates['from'], $affiliates['to'] );
			$commissions     = self::get_commissions_from_period( $affiliate_id, $affiliates['from'], $affiliates['to'] );

			do_action( 'bwfan_trigger_affiliate_report_event', $affiliate_id, $referrals_count, $visits, $commissions, $affiliates['from'], $affiliates['to'] );
		}
	}

	public static function get_referrals_count_from_period( $affiliate_id, $from, $to ) {
		global $wpdb;

		$start_date = $from . ' 00:00:00';
		$end_date   = $to . ' 23:59:59';

		$referrals = $wpdb->get_var( $wpdb->prepare( "
			                                        SELECT COUNT(referral_id)
			                                        FROM {$wpdb->prefix}affiliate_wp_referrals
			                                        WHERE affiliate_id = %d
			                                        AND status = %s
			                                        AND date >= %s
			                                        AND date <= %s
			                                        ", $affiliate_id, 'unpaid', $start_date, $end_date ) );

		return ! empty( $referrals ) ? $referrals : 0;
	}

	public static function get_visits_from_period( $affiliate_id, $from, $to ) {
		global $wpdb;

		$start_date = $from . ' 00:00:00';
		$end_date   = $to . ' 23:59:59';

		$visits = $wpdb->get_var( $wpdb->prepare( "
			                                        SELECT COUNT(visit_id)
			                                        FROM {$wpdb->prefix}affiliate_wp_visits
			                                        WHERE affiliate_id = %d
			                                        AND date >= %s
			                                        AND date <= %s
			                                        ", $affiliate_id, $start_date, $end_date ) );

		return ! empty( $visits ) ? $visits : 0;
	}

	public static function get_commissions_from_period( $affiliate_id, $from, $to ) {
		global $wpdb;

		$start_date = $from . ' 00:00:00';
		$end_date   = $to . ' 23:59:59';

		$earnings = $wpdb->get_var( $wpdb->prepare( "
			                                        SELECT SUM(amount)
			                                        FROM {$wpdb->prefix}affiliate_wp_referrals
			                                        WHERE affiliate_id = %d
			                                        AND status = %s
			                                        AND date >= %s
			                                        AND date <= %s
			                                        ", $affiliate_id, 'unpaid', $start_date, $end_date ) );

		$total_earnings = 0;
		if ( ! empty( $earnings ) ) {
			$decimal        = apply_filters( 'bwfan_get_decimal_values', 2 );
			$total_earnings = round( $earnings, $decimal );
		}

		return $total_earnings;
	}

	public static function get_callable_object( $is_empty, $data ) {
		if ( 'sfwd-courses' === $data['type'] ) {
			return [ __CLASS__, 'get_learndash_courses' ];
		}

		return $is_empty;
	}

	public static function get_learndash_courses( $searched_term ) {
		$courses      = array();
		$results      = array();
		$query_params = array(
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		if ( '' !== $searched_term ) {
			$query_params['s'] = $searched_term;
		}

		$query = new WP_Query( $query_params );

		if ( $query->found_posts > 0 ) {
			foreach ( $query->posts as $post ) {
				$results[] = array(
					'id'   => $post->ID,
					'text' => $post->post_title,
				);
			}
		}

		$courses['results'] = $results;

		return $courses;
	}

	public static function get_wc_membership_active_statuses() {
		if ( ! function_exists( 'wc_memberships' ) ) {
			return [];
		}

		$statuses = wc_memberships()->get_user_memberships_instance()->get_active_access_membership_statuses();
		$statuses = BWFAN_PRO_Common::get_wc_membership_statuses_with_prefix( $statuses );

		return $statuses;
	}

	public static function get_wc_membership_statuses_with_prefix( $statuses ) {
		if ( ! function_exists( 'wc_memberships' ) ) {
			return [];
		}

		$statuses_to_return = [];
		foreach ( (array) $statuses as $index => $status ) {

			if ( 'any' !== $status ) {
				$statuses_to_return[ $index ] = 'wcm-' . $status;
			}
		}

		return $statuses_to_return;
	}


	public static function test_zap() {
		BWFAN_Common::check_nonce();
		// phpcs:disable WordPress.Security.NonceVerification
		$result = array(
			'status' => false,
			'msg'    => __( 'Error', 'autonami-automations-pro' ),
		);

		$post = $_POST;
		if ( ! isset( $post['data']['url'] ) ) {
			$result['msg'] = __( 'Webhook URL cannot be blank', 'autonami-automations-pro' );
			wp_send_json( $result );

		}

		if ( isset( $post['data']['custom_fields'] ) && isset( $post['data']['custom_fields']['field'] ) && isset( $post['data']['custom_fields']['field_value'] ) ) {
			reset( $post['data']['custom_fields']['field'] );
			$first_key = key( $post['data']['custom_fields']['field'] );
			if ( empty( $post['data']['custom_fields']['field'][ $first_key ] ) || empty( $post['data']['custom_fields']['field'][ $first_key ] ) ) {
				$result['msg'] = __( 'Atleast one key / value pair required', 'autonami-automations-pro' );
				wp_send_json( $result );
			}
		} else {
			$result['msg'] = __( 'Atleast one key / value pair required', 'autonami-automations-pro' );
			wp_send_json( $result );
		}

		update_option( 'bwfan_show_preview', $post );
		BWFAN_Merge_Tag_Loader::set_data( array(
			'is_preview' => true,
		) );

		$post['event_data']['event_slug'] = $post['event'];
		$action_object                    = BWFAN_Core()->integration->get_action( 'za_send_data' );
		$action_object->is_preview        = true;
		$data_to_set                      = array();

		$fields       = array_map( 'stripslashes', $post['data']['custom_fields']['field'] );
		$fields_value = array_map( 'stripslashes', $post['data']['custom_fields']['field_value'] );

		$custom_fields = array();

		foreach ( $fields as $key1 => $field_id ) {
			$custom_fields[ $field_id ] = BWFAN_Common::decode_merge_tags( $fields_value[ $key1 ] );
		}
		$data_to_set['custom_fields'] = $custom_fields;
		$data_to_set['url']           = BWFAN_Common::decode_merge_tags( $post['data']['url'] );
		$data_to_set['test']          = true;

		$action_object->set_data( $data_to_set );
		$response = $action_object->process();

		if ( false !== $response ) {
			$result['msg']    = __( 'Test Data sent to Zap.', 'autonami-automations-pro' );
			$result['status'] = true;
		}
		//phpcs:enable WordPress.Security.NonceVerification
		wp_send_json( $result );
	}

	public static function five_minute_worker_cb() {
		$active_automations = BWFAN_Core()->automations->get_active_automations();
		if ( ! is_array( $active_automations ) || 0 === count( $active_automations ) ) {
			return;
		}
		do_action( 'bwfan_5_min_action', $active_automations );
	}

	/**
	 * Passing language as a param in sync call for every event
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function passing_event_language( $data ) {
		/** checking for wpml plugin **/
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {

			$data['language'] = ICL_LANGUAGE_CODE;
		}

		/** checking for polylang plugin **/
		if ( function_exists( 'pll_current_language' ) ) {
			$data['language'] = pll_current_language();
		}

		/** checking for translate press **/
		if ( bwfan_is_translatepress_active() ) {
			$data['language'] = get_locale();
		}

		return $data;
	}

	/**
	 * Populate cart from order id
	 *
	 * @throws Exception
	 */
	public static function bwfan_populate_cart_from_order() {
		global $woocommerce;

		if ( ! isset( $_GET['bwfan-order-again'] ) || empty( $_GET['bwfan-order-again'] ) ) {
			return;
		}

		$order_id = $_GET['bwfan-order-again'];
		$order    = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order || ! $order->has_status( apply_filters( 'woocommerce_valid_order_statuses_for_order_again', array( 'completed', 'processing' ) ) ) ) {
			return;
		}
		/** empty cart **/
		$woocommerce->cart->empty_cart();
		$order_items = $order->get_items();

		if ( ! is_array( $order_items ) || 0 === count( $order_items ) ) {
			return;
		}

		foreach ( $order_items as $item ) {

			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}

			$product_id     = (int) apply_filters( 'woocommerce_add_to_cart_product_id', $item->get_product_id() );
			$quantity       = $item->get_quantity();
			$variation_id   = (int) $item->get_variation_id();
			$variations     = array();
			$cart_item_data = apply_filters( 'woocommerce_order_again_cart_item_data', array(), $item, $order );
			$product        = $item->get_product();

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			/** Prevent reordering variable products if no selected variation */
			if ( ! $variation_id && $product->is_type( 'variable' ) ) {
				continue;
			}

			/** Prevent reordering items specifically out of stock */
			if ( ! $product->is_in_stock() ) {
				continue;
			}

			foreach ( $item->get_meta_data() as $meta ) {
				if ( taxonomy_is_product_attribute( $meta->key ) ) {
					$term                     = get_term_by( 'slug', $meta->value, $meta->key );
					$variations[ $meta->key ] = $term ? $term->name : $meta->value;
				} elseif ( meta_is_product_attribute( $meta->key, $meta->value, $product_id ) ) {
					$variations[ $meta->key ] = $meta->value;
				}
			}

			if ( ! apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations, $cart_item_data ) ) {
				continue;
			}

			/** Add to cart directly */
			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations, $cart_item_data );
		}

		/** Clear show notices for added coupons or products */
		if ( ! is_null( WC()->session ) ) {
			WC()->session->set( 'wc_notices', array() );
		}
	}

	public static function get_contact_by_email( $email ) {
		if ( ! is_email( $email ) || ! class_exists( 'WooFunnels_DB_Operations' ) ) {
			return false;
		}

		$contact_db  = WooFunnels_DB_Operations::get_instance();
		$contact_obj = $contact_db->get_contact_by_email( $email );

		if ( ! isset( $contact_obj->id ) || ! $contact_obj->id > 0 ) {
			return false;
		}

		return $contact_obj;
	}

	public static function get_bwf_contact_by_email( $email ) {
		$contact = self::get_contact_by_email( $email );
		if ( ! isset( $contact->wpid ) || ! absint( $contact->wpid ) > 0 ) {
			return false;
		}

		$bwf_contact = bwf_get_contact( $contact->wpid, $email );
		if ( $bwf_contact instanceof WooFunnels_Customer ) {
			return $bwf_contact->contact;
		}

		if ( $bwf_contact instanceof WooFunnels_Contact ) {
			return $bwf_contact;
		}

		return false;
	}

	public static function get_bwf_customer_by_email( $email ) {
		$bwf_contact = self::get_bwf_contact_by_email( $email );
		if ( ! $bwf_contact instanceof $bwf_contact ) {
			return false;
		}

		$customer = new WooFunnels_Customer( $bwf_contact );

		if ( ! isset( $customer->id ) || ! $customer->id > 0 ) {
			return false;
		}

		return $customer;
	}

	public static function get_contact_meta( $contact_id, $key = '' ) {
		$contact_id = absint( $contact_id );
		if ( ! $contact_id > 0 ) {
			return false;
		}

		$contact_db   = WooFunnels_DB_Operations::get_instance();
		$contact_meta = $contact_db->get_contact_metadata( $contact_id );
		if ( ! is_array( $contact_meta ) || empty( $contact_meta ) ) {
			return false;
		}

		$contact_meta_array = array();
		foreach ( $contact_meta as $meta ) {
			$contact_meta_array[ $meta->meta_key ] = maybe_unserialize( $meta->meta_value );
		}

		if ( empty( $key ) ) {
			return $contact_meta_array;
		}

		if ( ! isset( $contact_meta_array[ $key ] ) ) {
			return false;
		}

		return $contact_meta_array[ $key ];
	}

}
