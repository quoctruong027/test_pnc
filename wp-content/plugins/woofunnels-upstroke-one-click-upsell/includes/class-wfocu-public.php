<?php


/**
 * FrontEnd flow controller class
 * WFOCU_Public class
 */
class  WFOCU_Public {

	private static $ins = null;
	public $is_preview = false;
	public $initiate_funnel = false;
	public $new_order = null;
	public $failed_order = null;
	private $is_offer = null;
	private $porder = null;
	private $items_added = null;
	private $is_order_behavior_switched = false;

	public function __construct() {

		/**
		 * WooCommerce `woocommerce_pre_payment_complete` hook is the best hook to check if we have any upsells to show at this moment.
		 * As we only tries to show upsells on the successfully paid offer [keeping the gateways support in mind]
		 */
		add_action( 'woocommerce_pre_payment_complete', array( $this, 'maybe_setup_upsell' ), 99, 1 );

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'maybe_setup_upsell_on_cod_or_bacs' ), 100, 2 );

		/**
		 * Actions and filters to initiate when funnel
		 */
		add_action( 'wfocu_front_init_funnel_hooks', array( $this, 'maybe_setup_funnel_options' ), - 1 );
		add_action( 'wfocu_front_init_funnel_hooks', array( $this, 'modify_order_received_url' ), 1 );

		add_action( 'wfocu_front_init_funnel_hooks', array( $this, 'update_primary_order_meta' ), 1 );

		add_action( 'wfocu_front_init_funnel_hooks', array( $this, 'schedule_pending_emails_event' ), 10, 1 );

		add_action( 'wfocu_front_primary_order_status_change', array( $this, 'save_schedule_meta' ), 10, 3 );
		add_action( 'wfocu_front_primary_order_status_change', array( $this, 'sustain_order_status' ), 10, 3 );

		add_action( 'wfocu_before_template_load', array( $this, 'maybe_enqueue_assets' ), 20 );

		/**
		 * Checks if it is an offer page request for the front end
		 * Validate the offer against the user funnel state
		 * Take respective action based on the result
		 */
		add_action( 'template_redirect', array( $this, 'if_is_offer' ) );

		/**
		 * Tell the class that this request is for showing preview
		 */
		add_action( 'init', array( $this, 'if_is_preview' ), 1 );

		/**
		 * Make system ready to initiate a funnel.
		 * Until this hook we are good to run our rules over the active funnels in the system to find the best suitable funnel
		 */
		add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'maybe_decide_funnel_on_fragments' ), 10, 1 );
		add_action( 'woocommerce_applied_coupon', array( $this, 'maybe_decide_funnel' ), 99 );
		add_action( 'woocommerce_removed_coupon', array( $this, 'maybe_decide_funnel' ), 99 );

		/**
		 * Fallback to match rules when we have any rules left to be match in the session in the hunt of the funnel
		 */
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'maybe_decide_funnel_on_order' ), 99, 2 );

		add_action( 'woocommerce_thankyou', array( $this, 'maybe_destroy_session' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'maybe_log_thankyou_visited' ), 999, 1 );
		add_action( 'wp_footer', array( $this, 'print_session_data_js' ) );

		add_action( 'wfocu_offer_accepted_and_processed', array( $this, 'maybe_save_event_offer_accepted' ), 10, 6 );

		add_action( 'wfocu_session_loaded', array( $this, 'maybe_set_offer' ) );

		/**
		 * load specific assets during the single page request and custom upsell page request
		 */
		add_action( 'wfocu_front_before_custom_offer_page', array( $this, 'maybe_initiate_hooks_for_assets_load' ) );
		add_action( 'wfocu_front_before_customizer_page_load', array( $this, 'maybe_initiate_hooks_for_assets_load' ) );
		add_action( 'wfocu_front_before_single_page_load', array( $this, 'maybe_initiate_hooks_for_assets_load' ) );

		/**
		 * Clear users local storage for our variables
		 */
		add_action( 'woocommerce_thankyou', array( $this, 'remove_localstorage' ) );

		add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'maybe_add_wfocu_session_param' ), 999 );

		add_action( 'wfocu_front_create_new_order_on_success', array( $this, 'handle_new_order_creation_on_success' ), 10, 4 );
		add_action( 'wfocu_front_create_new_order_on_success', array( $this, 'maybe_save_new_order_id_in_parent' ), 12, 4 );
		add_action( 'wfocu_front_batch_items_on_success', array( $this, 'handle_batching_on_success' ), 10, 3 );

		add_action( 'wfocu_front_create_new_order_on_failure', array( $this, 'handle_new_order_creation_on_failure' ), 10, 1 );

		add_action( 'woocommerce_before_pay_action', [ $this, 'maybe_decide_funnel' ], 10, 2 );
		add_action( 'wfocu_get_funnel_option', [ $this, 'maybe_alter_funnel_order_behaviour' ], 10, 2 );
		add_action( 'wfocu_get_funnel_option', [ $this, 'maybe_alter_funnel_order_behaviour_is_cancel' ], 12, 2 );

		add_action( 'wfocu_session_loaded', [ $this, 'maybe_remove_all_taxes_from_offers_when_excempt' ] );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * @hooked into `woocommerce_update_order_review_fragments`
	 * At this place we have the posted data that we will going to contain in our data object so it could be further used by the matching groups
	 *
	 * @param $fragments
	 *
	 * @return mixed
	 */
	public function maybe_decide_funnel_on_fragments( $fragments = array() ) {
		$arr = array();
		if ( ! isset( $_POST['post_data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $fragments;
		}
		wp_parse_str( $_POST['post_data'], $arr );    // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		WFOCU_Core()->data->setup_posted( $arr );
		WFOCU_Core()->data->setup_funnel();

		return $fragments;
	}

	/**
	 * @hooked into `woocommerce_removed_coupon` || `woocommerce_applied_coupon`
	 */
	public function maybe_decide_funnel() {

		WFOCU_Core()->data->setup_funnel();
	}

	/**
	 * @hooked over `woocommerce_checkout_order_processed`
	 * Set up necessary environment and proceed with the rule check to find out best funnel for the order.
	 *
	 * @param $order_id
	 */
	public function maybe_decide_funnel_on_order( $order_id_or_order, $posted_data = array() ) {

		if ( $order_id_or_order instanceof WC_Order ) {
			$order_id_or_order = $order_id_or_order->get_id();
		}
		WFOCU_Core()->rules->set_environment_var( 'order', $order_id_or_order );
		WFOCU_Core()->data->setup_posted( $posted_data );
		WFOCU_Core()->data->setup_funnel( false, 'order' );
	}

	/**
	 * @hooked into `woocommerce_get_checkout_order_received_url` conditionally
	 * Responsible to change order received url in case we have funnel to initiate
	 *
	 * @param $url Existing order-received url
	 * @param WC_Order $order Order Getting processed
	 *
	 * @return string Modified order-received url on success
	 * @see WFOCU_Public::maybe_setup_upsell()
	 *
	 */
	public function maybe_redirect_to_upsell( $url, $order ) {

		$get_payment_gateway = WFOCU_WC_Compatibility::get_payment_gateway_from_order( $order );

		$get_integration = WFOCU_Core()->gateways->get_integration( $get_payment_gateway );
		$this->porder    = WFOCU_WC_Compatibility::get_order_id( $order );

		$get_compatibility_class = WFOCU_Plugin_Compatibilities::get_compatibility_class( 'subscriptions' );
		remove_filter( 'wfocu_front_payment_gateway_integration_enabled', array( $get_compatibility_class, 'maybe_disable_integration_when_subscription_in_cart' ), 10 );

		if ( WFOCU_Core()->data->is_funnel_exists() && $get_integration instanceof WFOCU_Gateway && $get_integration->is_enabled( $order ) && ( $get_integration->has_token( $order ) || $get_integration->is_run_without_token() ) ) {

			$get_offer = WFOCU_Core()->offers->get_the_first_offer();

			if ( 0 === absint( $get_offer ) ) { //integer check done
				WFOCU_Core()->log->log( 'Order #' . $this->porder . ': Skipping funnel, no offer /enabled' . $get_offer );
				/**
				 * At that time we possibly modified the order status, since we are not returning offer url we need to maybe normalize here to handle order status keeping in primary order
				 */
				WFOCU_Core()->orders->maybe_normalize_order_statuses( WFOCU_Core()->data->get_funnel_id(), WFOCU_WC_Compatibility::get_order_id( $order ) );

				return $url;
			}

			WFOCU_Core()->data->set( 'porder', $order, '_orders' );
			WFOCU_Core()->data->set( 'porder', WFOCU_WC_Compatibility::get_order_id( $order ), 'orders' );

			WFOCU_Core()->data->set( 'current_offer', $get_offer );
			WFOCU_Core()->data->set( 'useremail', WFOCU_WC_Compatibility::get_order_data( $order, 'billing_email' ) );
			WFOCU_Core()->data->save( 'orders' );
			WFOCU_Core()->data->save();

			do_action( 'wfocu_funnel_init_event', WFOCU_Core()->data->get_funnel_id(), WFOCU_WC_Compatibility::get_order_id( $order ), WFOCU_Core()->data->get( 'useremail' ), $order->get_payment_method(), $order->get_meta( '_woofunnel_cid' ) );
			$offer_url = $this->get_the_upsell_url( $get_offer );

			WFOCU_Core()->log->log( 'Order #' . $this->porder . ': Showing offer: ' . $get_offer . ' ( ' . $offer_url . ')' );

			return $offer_url;

		} else {

			WFOCU_Core()->log->log( 'Order #' . $this->porder . ': By passing upsell' );

		}

		return $url;
	}

	public function get_the_upsell_url( $offer ) {

		if ( empty( $offer ) ) {
			return $this->get_clean_order_received_url( true );
		}

		$offer_data = WFOCU_Core()->offers->get_offer( $offer );
		$link       = WFOCU_Core()->offers->get_the_link( $offer );
		if ( 'custom-page' === $offer_data->template ) {
			$custom_page_id = get_post_meta( $offer, '_wfocu_custom_page', true );
			if ( ! empty( $custom_page_id ) ) {
				$get_custom_page_post = get_post( $custom_page_id );

				if ( null === $get_custom_page_post || ( is_object( $get_custom_page_post ) && 'publish' !== $get_custom_page_post->post_status ) ) {
					WFOCU_Core()->log->log( 'Order #' . $this->porder . ':: Skipping this offer# ' . $offer . ' as page ' . $custom_page_id . ' no longer exists.' );

					$get_offer = WFOCU_Core()->offers->get_the_next_offer( 'yes' );

					$link = $this->get_the_upsell_url( $get_offer );
				}
			}
		}

		return add_query_arg( array(
			'wfocu-key' => WFOCU_Core()->data->get_funnel_key(),
			'wfocu-si'  => WFOCU_Core()->data->get_transient_key(),
		), $link );
	}

	/**
	 * Getting order received URL which is not affected by any offers
	 * @return string
	 */

	public function get_clean_order_received_url( $end_funnel = true, $append_failure_params = false ) {

		$order = WFOCU_Core()->data->get_current_order();

		if ( ! $order instanceof WC_Order ) {
			return '';
		}
		/**
		 * Prevent this code to reset the checkout received url, as we do not need to reattach the filter for the current session
		 */
		remove_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'maybe_redirect_to_upsell' ), 99, 2 );
		$get_received_url = $order->get_checkout_order_received_url();

		if ( true === $end_funnel ) {
			do_action( 'wfocu_funnel_ended_event', WFOCU_Core()->data->get_funnel_id(), WFOCU_WC_Compatibility::get_order_id( $order ), WFOCU_Core()->data->get( 'useremail' ) );

		}

		return ( $append_failure_params === false ) ? $get_received_url : add_query_arg( array( '_wfocu_process' => 'no' ), $get_received_url );

	}

	/**
	 * @hooked over `woocommerce_checkout_order_processed`
	 * In this case we check if the order payment method is bacs||cheque, then we need to setup funnel in order to run further
	 * As there in these payment methods we do not have 'woocommerce_pre_payment_complete' hook to initiate the funnels.
	 *
	 * @param $order_id
	 * @param $posted_data
	 */
	public function maybe_setup_upsell_on_cod_or_bacs( $order_id, $posted_data = array() ) {

		if ( ( WC()->cart instanceof WC_Cart ) && WC()->cart->needs_payment() && is_array( $posted_data ) && isset( $posted_data['payment_method'] ) && in_array( $posted_data['payment_method'], array(
				'cheque',
				'bacs'
			), true ) ) {
			$this->maybe_setup_upsell( $order_id );
		}
	}

	/**
	 * @hooked into `woocommerce_pre_payment_complete`
	 * In this method we validates if we have any funnel to initiate and checking for the gateway support and tokenization
	 * If we have any funnel and checks are validated then registering required filters to continue with the funnel.
	 *
	 * @param $order_id
	 *
	 * @return null
	 */
	public function maybe_setup_upsell( $order_id = '' ) {

		if ( empty( $order_id ) ) {
			return;
		}
		$wc_get_order = wc_get_order( $order_id );

		if ( false === is_a( $wc_get_order, 'WC_Order' ) ) {
			WFOCU_Core()->log->log( 'No valid order' );

			return;
		}

		/**create_new_order
		 * Parent Order Has subscription in it and our addon for subscription is not installed and activated, then discard the funnel setup
		 */

		if ( $this->is_parent_contains_subscriptions( $wc_get_order ) && false === class_exists( 'UpStroke_Subscriptions' ) ) {
			WFOCU_Core()->log->log( 'Order #' . $order_id . ': Funnel Initiation Skipped: Reason UpStroke Subscription is not Activated.' );

			return false;
		}

		$skip_funnel_init = apply_filters( 'wfocu_front_skip_funnel', false, $wc_get_order );

		if ( true === $skip_funnel_init ) {
			WFOCU_Core()->log->log( 'Funnel Running Skipped by filter' );

			return false;
		}
		WFOCU_Core()->log->log( 'Order #' . $order_id . ': Entering: ' . __FUNCTION__ ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		WFOCU_Core()->log->log( 'Order #' . $order_id . ': Backtrace for maybe_setup_upsell::' . wp_debug_backtrace_summary() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
		do_action( 'wfocu_front_pre_init_funnel_hooks', $wc_get_order );
		$get_payment_gateway = WFOCU_WC_Compatibility::get_payment_gateway_from_order( $wc_get_order );

		$get_integration = WFOCU_Core()->gateways->get_integration( $get_payment_gateway );

		$this->porder = WFOCU_WC_Compatibility::get_order_id( $wc_get_order );

		$get_compatibility_class = WFOCU_Plugin_Compatibilities::get_compatibility_class( 'subscriptions' );

		$get_current_offer = WFOCU_Core()->data->get_current_offer();

		remove_filter( 'wfocu_front_payment_gateway_integration_enabled', array( $get_compatibility_class, 'maybe_disable_integration_when_subscription_in_cart' ), 10 );

		if ( false === $get_current_offer && WFOCU_Core()->data->is_funnel_exists() && $get_integration instanceof WFOCU_Gateway && $get_integration->is_enabled( $wc_get_order ) && ( $get_integration->has_token( $wc_get_order ) || $get_integration->is_run_without_token() ) ) {

			WFOCU_Core()->log->log( 'Order #' . $this->porder . ' Initiating funnel' );

			$this->initiate_funnel = true;

			add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'maybe_redirect_to_upsell' ), 99, 2 );

			do_action( 'wfocu_front_init_funnel_hooks', $wc_get_order );

		} else {

			WFOCU_Core()->log->log( 'Order #' . $this->porder . ' Details for skip given below ' . print_r( array( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
					'have_funnel'          => WFOCU_Core()->data->is_funnel_exists(),
					'have_gateway'         => ( $get_integration instanceof WFOCU_Gateway ),
					'get_current_offer'    => $get_current_offer,
					'have_enabled_gateway' => ( ( $get_integration instanceof WFOCU_Gateway ) && $get_integration->is_enabled( $wc_get_order ) ),
					'has_token'            => ( ( $get_integration instanceof WFOCU_Gateway ) && $get_integration->has_token( $wc_get_order ) ),
					'run_wihtout_token'    => ( ( $get_integration instanceof WFOCU_Gateway ) && $get_integration->is_run_without_token() ),
				), true ) );
		}

	}

	/**
	 * Checking if parent order contains subscriptions
	 *
	 * @param $wc_order
	 */
	public function is_parent_contains_subscriptions( $wc_order, $order_type = array( 'parent', 'resubscribe', 'switch' ) ) {
		// Accept either an array or string (to make it more convenient for singular types, like 'parent' or 'any')

		$contains_subscription = false;

		if ( false === function_exists( 'wcs_get_subscriptions_for_order' ) || false === function_exists( 'wcs_get_objects_property' ) || false === function_exists( 'wcs_order_contains_renewal' ) || false === function_exists( 'wcs_order_contains_resubscribe' ) || false === function_exists( 'wcs_order_contains_switch' ) ) {
			return $contains_subscription;
		}
		if ( ! is_array( $order_type ) ) {
			$order_type = array( $order_type );
		}

		if ( ! is_a( $wc_order, 'WC_Abstract_Order' ) ) {
			$wc_order = wc_get_order( $wc_order );
		}

		$get_all = ( in_array( 'any', $order_type, true ) ) ? true : false;

		if ( ( in_array( 'parent', $order_type, true ) || $get_all ) && count( wcs_get_subscriptions_for_order( wcs_get_objects_property( $wc_order, 'id' ), array( 'order_type' => 'parent' ) ) ) > 0 ) {
			$contains_subscription = true;

		} elseif ( ( in_array( 'renewal', $order_type, true ) || $get_all ) && wcs_order_contains_renewal( $wc_order ) ) {
			$contains_subscription = true;

		} elseif ( ( in_array( 'resubscribe', $order_type, true ) || $get_all ) && wcs_order_contains_resubscribe( $wc_order ) ) {
			$contains_subscription = true;

		} elseif ( ( in_array( 'switch', $order_type, true ) || $get_all ) && wcs_order_contains_switch( $wc_order ) ) {
			$contains_subscription = true;

		}

		return $contains_subscription;
	}

	public function schedule_pending_emails_event( $order ) {
		if ( false === is_a( $order, 'WC_Order' ) ) {
			WFOCU_Core()->log->log( 'Order #' . WFOCU_WC_Compatibility::get_order_id( $order ) . ': No valid order' . __FUNCTION__ );

			return;
		}

		$order_behavior = WFOCU_Core()->funnels->get_funnel_option( 'order_behavior' );

		if ( 'create_order' === $order_behavior ) {
			$args = array(
				WFOCU_WC_Compatibility::get_order_id( $order ),
				WFOCU_Core()->funnels->get_funnel_option( 'order_behavior' ),
				WFOCU_Core()->funnels->get_funnel_option( 'is_cancel_order' ),
				WFOCU_Core()->data->get_option( 'send_processing_mail_on_no_batch' ),
				WFOCU_Core()->data->get_option( 'send_processing_mail_on_no_batch_cancel' ),
				time(),
			);
			$order->update_meta_data( '_wfocu_pending_mails', $args );
			$order->save_meta_data();
			do_action( 'wfocu_schedule_email_data_stored', $order, $args );
		}

	}

	/**
	 * @hooked into `wfocu_front_init_funnel_hooks`
	 * Handles setting up the single schedule event to normalize the order status after the duration provided.
	 *
	 * @param WC_Order $order
	 */
	public function save_schedule_meta( $new_status, $old_status, $order ) {

		if ( false === is_a( $order, 'WC_Order' ) ) {
			WFOCU_Core()->log->log( 'Order #' . WFOCU_WC_Compatibility::get_order_id( $order ) . ': No valid order' . __FUNCTION__ );

			return;
		}
		if ( 'wc-wfocu-pri-order' !== $new_status ) {
			WFOCU_Core()->log->log( 'Order #' . WFOCU_WC_Compatibility::get_order_id( $order ) . ': No valid order status' . __FUNCTION__ );

			return;
		}
		$args = array(
			'order_status'  => $old_status,
			'source_status' => $order->get_status(),
			'time'          => time(),
		);

		$order->update_meta_data( '_wfocu_schedule_status', $args );
		$order->save_meta_data();

	}

	/**
	 * @hooked over `wfocu_front_primary_order_status_change`
	 * This method is to store order Statuses in the transition in the cookie to manage order Statuses further
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param WC_Order $order
	 */
	public function sustain_order_status( $new_status, $old_status, $order ) {
		if ( false === is_a( $order, 'WC_Order' ) ) {
			WFOCU_Core()->log->log( 'Order #' . WFOCU_WC_Compatibility::get_order_id( $order ) . ': No valid order' . __FUNCTION__ );

			return;
		}
		if ( 'wc-wfocu-pri-order' !== $new_status ) {
			WFOCU_Core()->log->log( 'Order #' . WFOCU_WC_Compatibility::get_order_id( $order ) . ': No valid order status' . __FUNCTION__ );

			return;
		}

		WFOCU_Core()->data->set( 'porder_status', $old_status );
		WFOCU_Core()->data->set( 'sorder_status', $order->get_status() );
		WFOCU_Core()->data->save();

	}

	public function modify_order_received_url() {
		add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'maybe_redirect_to_upsell' ), 99, 2 );

	}

	public function maybe_destroy_session() {
		WFOCU_Core()->data->destroy_session();
	}

	public function if_is_offer() {

		global $post;

		if ( true === $this->is_offer ) {
			return $this->is_offer;
		}

		$maybe_offer = WFOCU_Core()->offers->get_offer_from_post( $post );

		if ( false !== $maybe_offer ) {

			if ( false === $this->is_preview && false === WFOCU_Core()->offers->validate( $maybe_offer ) ) {

				/**
				 * There could be multiple reasons why the validation returns false.
				 * 1. Funnel is not setup, not have a cookie set for this funnel
				 * 2. This is not the expected offer to come on this funnel
				 */
				$this->handle_failed_validation_for_the_offer();
				wp_die( esc_attr__( 'Sorry, you are not allowed to access this page.' ) );
			}
			$this->is_offer = true;

			return true;
		}
		$this->is_offer = false;

		return false;
	}

	/**
	 * Handle validation failure when request for offer page came in.
	 *
	 * @see WFOCU_Public::if_is_offer()
	 */
	public function handle_failed_validation_for_the_offer() {
		$url_to_redirect = $this->get_clean_order_received_url( true );

		if ( '' !== $url_to_redirect ) {
			wp_redirect( $url_to_redirect );
			exit;

		}

		if ( isset( $_GET['wfocu-key'] ) && ! empty( $_GET['wfocu-key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$maybe_get_order = get_posts( array( //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
				'post_type'   => 'shop_order',
				'post_status' => 'any',
				'meta_key'    => '_wfocu_funnel_key', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'  => wc_clean( $_GET['wfocu-key'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'fields'      => 'ids',
			) );

			if ( is_array( $maybe_get_order ) && count( $maybe_get_order ) > 0 ) {
				$order_id = $maybe_get_order[0];
				/**
				 * Prevent this code to reset the checkout received url, as we do not need to reattach the filter for the current session
				 */
				remove_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'maybe_redirect_to_upsell' ), 99 );
				$get_received_url = wc_get_order( $order_id )->get_checkout_order_received_url();
				if ( '' !== $get_received_url ) {
					wp_redirect( $get_received_url );
					exit;
				}
			}
		}
	}

	/**
	 * prevent checks to validate page.
	 * @return bool
	 */
	public function can_show_upsell() {

		if ( null !== filter_input( INPUT_GET, 'elementor-preview', FILTER_SANITIZE_STRING ) && '' !== filter_input( INPUT_GET, 'elementor-preview', FILTER_SANITIZE_STRING ) ) {

			return false;
		}
		if ( 'loaded' === filter_input( INPUT_GET, 'wfocu_customize', FILTER_SANITIZE_STRING ) ) {
			return false;
		}

		return true;
	}

	public function charge_upsell() {

		$order               = WFOCU_Core()->data->get( 'porder', false, '_orders' );
		$get_payment_gateway = WFOCU_WC_Compatibility::get_payment_gateway_from_order( $order );

		$get_integration         = WFOCU_Core()->gateways->get_integration( $get_payment_gateway );
		$get_compatibility_class = WFOCU_Plugin_Compatibilities::get_compatibility_class( 'subscriptions' );
		remove_filter( 'wfocu_front_payment_gateway_integration_enabled', array( $get_compatibility_class, 'maybe_disable_integration_when_subscription_in_cart' ), 10 );

		$get_package = WFOCU_Core()->data->get( '_upsell_package' );
		if ( $get_package && 0 === WFOCU_Common::get_amount_for_comparisons( $get_package['total'] ) && true === apply_filters( 'wfocu_allow_free_upsells', true ) ) {
			return true;
		}
		if ( $get_integration instanceof WFOCU_Gateway && $get_integration->is_enabled( $order ) && ( $get_integration->has_token( $order ) || $get_integration->is_run_without_token() ) ) {

			do_action( 'wfocu_offer_before_charge_complete', $get_package );

			$charge_result = $get_integration->process_charge( $order );

			do_action( 'wfocu_offer_charge_complete', $charge_result, $get_package );

			return $charge_result;
		}

		return false;
	}

	public function set_parent_order( $order_id ) {
		$this->porder = $order_id;
	}

	/**
	 * @return bool|WC_Order|WC_Refund
	 */
	public function get_parent_order() {
		return wc_get_order( $this->porder );
	}

	/**
	 * A controller method to handle successful upsell request
	 * It handles the order processing behavior,
	 * Create new Orders, Cancel the Old ones and batch if required.
	 * Handles stock management
	 * Handles marking order as completed payment
	 *
	 */
	public function handle_success_upsell() {
		$order_behavior = WFOCU_Core()->funnels->get_funnel_option( 'order_behavior' );

		$is_batching_on     = ( 'batching' === $order_behavior ) ? true : false;
		$get_parent_order   = WFOCU_Core()->data->get( 'porder', false, '_orders' );
		$get_package        = WFOCU_Core()->data->get( '_upsell_package' );
		$get_funnel_id      = WFOCU_Core()->data->get_funnel_id();
		$get_offer_id       = WFOCU_Core()->data->get_current_offer();
		$get_transaction_id = WFOCU_Core()->data->get( '_transaction_id' );

		if ( $is_batching_on ) {

			do_action( 'wfocu_front_batch_items_on_success', $get_transaction_id, $get_funnel_id, $get_offer_id );

		} else {

			do_action( 'wfocu_front_create_new_order_on_success', $get_transaction_id, $get_funnel_id, $get_offer_id, true );
		}

		do_action( 'wfocu_offer_accepted_and_processed', $get_offer_id, $get_package, $get_parent_order, $this->new_order, $get_transaction_id, $this->items_added );
	}

	public function handle_failed_upsell() {

		$get_parent_order = WFOCU_Core()->data->get( 'porder', false, '_orders' );
		$get_package      = WFOCU_Core()->data->get( '_upsell_package' );

		$get_funnel_id = WFOCU_Core()->data->get_funnel_id();

		$get_current_offer = WFOCU_Core()->data->get_current_offer();

		do_action( 'wfocu_front_create_new_order_on_failure', $get_funnel_id, $get_current_offer );

		$args = array(
			'order_id'      => WFOCU_WC_Compatibility::get_order_id( $get_parent_order ),
			'funnel_id'     => WFOCU_Core()->data->get_funnel_id(),
			'offer_id'      => $get_current_offer,
			'offer_type'    => WFOCU_Core()->data->get( '_current_offer_type' ),
			'offer_index'   => WFOCU_Core()->data->get( '_current_offer_type_index' ),
			'_failed_order' => $this->failed_order,
			'email'         => WFOCU_Core()->data->get( 'useremail' ),
		);

		$args['value'] = WFOCU_Plugin_Compatibilities::get_fixed_currency_price_reverse( $get_package['total'], WFOCU_WC_Compatibility::get_order_currency( $get_parent_order ) );
		do_action( 'wfocu_offer_payment_failed_event', $args );

	}


	public function print_session_data_js() {

		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			$data   = WFOCU_Core()->data->get_all();
			$script = 'var wfocu_info = ' . wp_json_encode( WFOCU_Common::unserialize_recursive( $data ) ) . ';';
			?>
			<script type="text/javascript">
				<?php echo $script;   //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</script>
			<?php
		}

	}

	public function maybe_set_offer() {
		if ( $this->is_front() || WFOCU_AJAX_Controller::is_wfocu_front_ajax() ) {

			WFOCU_Core()->template_loader->set_offer_id( WFOCU_Core()->data->get_current_offer() );
		}
	}

	public function is_front() {
		$offer = WFOCU_Core()->data->get_current_offer();
		if ( $offer ) {
			return ( null === filter_input( INPUT_GET, 'wfocu-key', FILTER_SANITIZE_STRING ) ) ? false : true;
		}
	}

	public function maybe_save_event_offer_accepted( $get_current_offer, $get_package, $get_parent_order, $new_order, $get_transaction_id, $items_added = [] ) {

		$args = array(
			'order_id'    => WFOCU_WC_Compatibility::get_order_id( $get_parent_order ),
			'funnel_id'   => WFOCU_Core()->data->get_funnel_id(),
			'offer_id'    => $get_current_offer,
			'offer_type'  => WFOCU_Core()->data->get( '_current_offer_type' ),
			'offer_index' => WFOCU_Core()->data->get( '_current_offer_type_index' ),
			'email'       => WFOCU_Core()->data->get( 'useremail' ),
		);

		$products   = array();
		$item_total = 0;
		foreach ( $get_package['products'] as $product ) {

			$products[] = array(
				'id'    => $product['id'],
				'price' => $product['args']['total'],
				'name'  => $product['_offer_data']->name,
				'hash'  => $product['hash'],
			);

			$args_products = array_merge( $args, array(
				'value'            => WFOCU_Plugin_Compatibilities::get_fixed_currency_price_reverse( $product['args']['total'], WFOCU_WC_Compatibility::get_order_currency( $get_parent_order ) ),
				'raw_value'        => $product['args']['total'],
				'product_id'       => $product['id'],
				'offer_product_id' => $product['hash'],
				'product_title'    => $product['_offer_data']->name,
				'qty'              => $product['qty'],
			) );
			$item_total    = $product['args']['total'] + $item_total;
			do_action( 'wfocu_product_accepted_event', $args_products );

		}

		$args['payment_data'] = array(
			'items'           => $products,
			'total'           => WFOCU_Plugin_Compatibilities::get_fixed_currency_price_reverse( $get_package['total'], WFOCU_WC_Compatibility::get_order_currency( $get_parent_order ) ),
			'_total_charged'  => $get_package['total'],
			'_total_shipping' => wp_json_encode( WFOCU_Core()->shipping->get_shipping_cost_from_package( $get_package ) ),
			'_total_items'    => $item_total,
			'_total_tax'      => $get_package['taxes'],
			'_currency'       => $get_parent_order->get_currency(),
		);

		$args['value']          = WFOCU_Plugin_Compatibilities::get_fixed_currency_price_reverse( $get_package['total'], WFOCU_WC_Compatibility::get_order_currency( $get_parent_order ) );
		$args['new_order']      = ( is_a( $new_order, 'WC_Order' ) ) ? WFOCU_WC_Compatibility::get_order_id( $new_order ) : '';
		$args['transaction_id'] = $get_transaction_id;
		$args['items_added']    = $items_added;

		do_action( 'wfocu_offer_accepted_event', $args );
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function update_primary_order_meta( $order ) {
		if ( false === is_a( $order, 'WC_Order' ) ) {
			WFOCU_Core()->log->log( 'Order #' . WFOCU_WC_Compatibility::get_order_id( $order ) . ': No valid order' . __FUNCTION__ );

			return;
		}

		$order->update_meta_data( '_wfocu_funnel_id', WFOCU_Core()->data->get_funnel_id() );
		$order->update_meta_data( '_wfocu_funnel_key', WFOCU_Core()->data->get_funnel_key() );
		$order->update_meta_data( '_wfocu_version', WFOCU_VERSION );
		$order->save();
	}

	public function maybe_initiate_hooks_for_assets_load() {

		/**
		 * Loads which group of assets to load when offer is loading
		 */
		if ( 'wfocu_front_before_customizer_page_load' === current_action() ) {

			WFOCU_Core()->assets->setup_assets( 'offer' );

		} elseif ( 'wfocu_front_before_single_page_load' === current_action() ) {
			WFOCU_Core()->assets->setup_assets( 'offer-single' );

		} else {
			WFOCU_Core()->assets->setup_assets( 'offer-page' );

		}

		add_action( 'wp_head', array( $this, 'load_header_script_for_custom_page' ) );
		add_action( 'wp_footer', array( $this, 'load_confirmation_page_ui' ) );
		add_action( 'wp_footer', array( $this, 'load_footer_script_for_custom_page' ) );
	}

	public function load_header_script_for_custom_page() {

		$this->maybe_enqueue_assets();
		WFOCU_Core()->assets->print_styles( true );
		WFOCU_Core()->assets->print_scripts( true );
	}

	public function maybe_enqueue_assets() {
		if ( $this->is_offer || $this->if_is_preview() ) {

			WFOCU_Core()->assets->localize_script( 'accounting', 'wfocu_wc_params', array(
				'currency_format_num_decimals' => wc_get_price_decimals(),
				'currency_format_symbol'       => get_woocommerce_currency_symbol(),
				'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
				'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
				'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
			) );

			WFOCU_Core()->assets->localize_script( 'wfocu-global', 'wfocu_vars', apply_filters( 'wfocu_localized_data', array(
				'nonces'                 => array(
					'wfocu_front_offer_skipped' => wp_create_nonce( 'wfocu_front_offer_skipped' ),
					'wfocu_charge'              => wp_create_nonce( 'wfocu_front_charge' ),
					'wfocu_calculate_shipping'  => wp_create_nonce( 'wfocu_front_calculate_shipping' ),
					'wfocu_register_views'      => wp_create_nonce( 'wfocu_front_register_views' ),
					'wfocu_offer_expired'       => wp_create_nonce( 'wfocu_front_offer_expired' ),
					'wfocu_front_catch_error'   => wp_create_nonce( 'wfocu_front_catch_error' ),
				),
				'offer'                  => WFOCU_Core()->data->get( 'current_offer' ),
				'offer_type'             => WFOCU_Core()->data->get( '_current_offer_type' ),
				'offer_type_index'       => WFOCU_Core()->data->get( '_current_offer_type_index' ),
				'show_variation_default' => apply_filters( 'wfocu_show_default_variation_on_load', true ),
				'no_variation_text'      => __( 'Choose an option', 'woocommerce' ),
				'offer_data'             => WFOCU_Core()->data->get( '_current_offer_data' ),
				'messages'               => array(

					'offer_success_message_pop'        => WFOCU_Core()->funnels->get_funnel_option( 'offer_success_message_pop' ),
					'offer_msg_pop_failure'            => WFOCU_Core()->funnels->get_funnel_option( 'offer_failure_message_pop' ),
					'i18n_no_matching_variations_text' => esc_attr__( 'Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce' ),
				),
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'wc_ajax_url'            => WC_AJAX::get_endpoint( '%%endpoint%%' ),

				'loader'                 => sprintf( '<img src="%s" />', admin_url( 'images/spinner.gif' ) ),
				'loading_text'           => WFOCU_Core()->funnels->get_funnel_option( 'offer_wait_message_pop' ),
				'global'                 => array(
					'flat_shipping_label' => WFOCU_Core()->data->get_option( 'flat_shipping_label' ),
					'include_taxes'       => WFOCU_Core()->offers->show_tax_info_in_confirmation(),
				),
				'is_preview'             => $this->is_preview,
				'tax_nice_name'          => WFOCU_Core()->data->get_tax_name(),
				'is_show_price_with_tax' => WFOCU_Core()->funnels->show_prices_including_tax(),
				'session_id'             => ( true === $this->is_preview ) ? '' : WFOCU_Core()->data->get_transient_key(),
				'order_received_url'     => ( true === $this->is_preview ) ? '' : $this->get_clean_order_received_url( false, true ),
				'is_free_shipping'       => ( true === $this->is_preview ) ? false : $this->is_free_shipping_in_parent(),
			) ) );
		}
	}

	public function if_is_preview() {
		if ( 'loaded' === filter_input( INPUT_GET, 'wfocu_customize', FILTER_SANITIZE_STRING ) || ( isset( $_POST['action'] ) && $_POST['action'] === 'customize_save' ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->is_preview = true;
		}

		return apply_filters( 'wfocu_is_template_preview', $this->is_preview );
	}

	/**
	 * Getting if parent order contains free shipping
	 */
	public function is_free_shipping_in_parent() {
		$order = WFOCU_Core()->data->get( 'porder', false, '_orders' );
		/**
		 * @var WC_Order $order
		 */
		$methods = $order->get_shipping_methods();

		foreach ( $methods as $method ) {
			if ( true === WFOCU_Core()->shipping->is_free_shipping( $method->get_name() ) ) {
				return true;
			}
		}

		return false;
	}

	public function load_footer_script_for_custom_page() {

		WFOCU_Core()->assets->print_styles();

		do_action( 'footer_before_print_scripts' );
		do_action( 'wfocu_footer_before_print_scripts' );
		WFOCU_Core()->assets->print_scripts();
		do_action( 'footer_after_print_scripts' );
		do_action( 'wfocu_footer_after_print_scripts' );

	}

	public function load_confirmation_page_ui() {
		if ( true === WFOCU_Core()->public->is_preview ) {
			return;
		}
		$offer_data = WFOCU_Core()->data->get( '_current_offer_data', 'funnel' );
		/** Sidebar Bucket */
		WFOCU_Core()->template_loader->get_template_part( 'offer-confirmations', $offer_data->products );

	}

	public function remove_localstorage() {
		?>
		<script type="text/javascript">
            if (!String.prototype.startsWith) {
                String.prototype.startsWith = function (searchString, position) {
                    position = position || 0;
                    return this.indexOf(searchString, position) === position;
                };
            }

            if (localStorage.length > 0) {
                var len = localStorage.length;
                var wfocuRemoveLS = [];
                for (var i = 0; i < len; ++i) {
                    var storage_key = localStorage.key(i);
                    if (storage_key.startsWith("wfocu_") === true) {
                        wfocuRemoveLS.push(storage_key);
                    }
                }
                for (var eachLS in wfocuRemoveLS) {
                    localStorage.removeItem(wfocuRemoveLS[eachLS]);
                }

            }
		</script>
		<?php
	}

	/**
	 * @hooked over `wfocu_front_create_new_order_on_success`
	 * Performs New Order creation on success of upsell offer
	 *
	 * @param string $get_transaction_id
	 * @param $get_funnel_id
	 * @param $get_offer_id
	 * @param bool $should_complete whether to mark the order complete or not
	 *
	 * @throws WC_Data_Exception
	 */
	public function handle_new_order_creation_on_success( $get_transaction_id = '', $get_funnel_id, $get_offer_id, $should_complete = true ) {

		$get_parent_order = WFOCU_Core()->data->get( 'porder', false, '_orders' );

		$get_package = WFOCU_Core()->data->get( '_upsell_package' );

		$new_order       = WFOCU_Core()->orders->create_new_order( $get_package, $get_parent_order );
		$this->new_order = $new_order;
		WFOCU_Core()->orders->maybe_handle_shipping_new_order( $get_package, $get_parent_order, $new_order );
		do_action( 'wfocu_offer_new_order_created_before_complete', $new_order, $get_transaction_id );
		if ( true === $should_complete ) {
			/**
			 * Removing our action so that the funnel initiation will not trigger once WC_Order::payment_complete() hits
			 */
			remove_action( 'woocommerce_pre_payment_complete', array( $this, 'maybe_setup_upsell' ), 99, 1 );

			WFOCU_Core()->orders->payment_complete( $get_transaction_id, $new_order );

			$new_order->set_transaction_id( $get_transaction_id );
			$new_order->save();

		}
		$transaction_id_note = '';
		if ( ! empty( $get_transaction_id ) ) {
			$transaction_id_note = sprintf( ' (Transaction ID: %s)', $get_transaction_id );

		}
		$new_order->add_order_note( 'A New Order Created &  Offer Accepted | Funnel ID ' . $get_funnel_id . '  | Offer ID ' . $get_offer_id . $transaction_id_note );

		$funnel_settings = get_post_meta( $get_funnel_id, '_wfocu_settings', true );
		if ( isset( $funnel_settings['order_behavior'] ) && 'batching' === $funnel_settings['order_behavior'] ) {
			$get_parent_order->add_order_note( 'Customer accepted the upsell offer after the order is normalized hence a new order is created. Order ID:' . $new_order->get_id() );

		}

		$get_payment_gateway = WFOCU_WC_Compatibility::get_payment_gateway_from_order( $new_order );

		$cancel_original = WFOCU_Core()->funnels->get_funnel_option( 'is_cancel_order' );
		do_action( 'wfocu_offer_new_order_created', $new_order, $get_transaction_id );
		do_action( 'wfocu_offer_new_order_created_' . $get_payment_gateway, $new_order, $get_transaction_id );

		$get_if_cancelled = WFOCU_Core()->data->get( 'cancelled', false );

		if ( 'yes' === $cancel_original && ( $get_parent_order->get_id() !== $get_if_cancelled ) ) {
			WFOCU_Core()->orders->process_cancellation();

			WFOCU_Core()->data->set( 'corder', WFOCU_WC_Compatibility::get_order_id( $new_order ), 'orders' );
			WFOCU_Core()->data->set( 'corder', wc_get_order(), '_orders' );
			WFOCU_Core()->data->set( 'cancelled', $get_parent_order->get_id() );

			WFOCU_Core()->data->save( 'funnel' );
			WFOCU_Core()->data->save( 'orders' );
		}
	}

	/**
	 * @hooked `wfocu_front_create_new_order_on_success`
	 * Lets keep track of new orders in the current order in the system, it will be used to show relevant order data on thankyou page
	 */
	public function maybe_save_new_order_id_in_parent() {
		$current_order_replaced = WFOCU_Core()->data->get_current_order();
		$current_order          = $this->new_order;

		$parent_order = WFOCU_Core()->data->get( 'porder', null, 'orders' );

		if ( $current_order->get_id() !== $parent_order && ( ( $current_order instanceof WC_Order ) && $current_order->get_id() !== $current_order_replaced->get_id() ) ) {
			$current_order_replaced->add_meta_data( '_wfocu_sibling_order', $current_order->get_id(), false );
			$current_order_replaced->save_meta_data();
		}
	}

	/**
	 * @hooked over `wfocu_front_batch_items_on_success`
	 * Controller function to perform order batching process on successfull upsell
	 *
	 * @param string $get_transaction_id
	 * @param $get_funnel_id
	 * @param $get_offer_id
	 */
	public function handle_batching_on_success( $get_transaction_id = '', $get_funnel_id, $get_offer_id ) {
		$get_parent_order = WFOCU_Core()->data->get( 'porder', false, '_orders' );

		$get_package       = WFOCU_Core()->data->get( '_upsell_package' );
		$items_added       = WFOCU_Core()->orders->add_products_to_order( $get_package, $get_parent_order );
		$this->items_added = $items_added;
		WFOCU_Core()->orders->maybe_handle_shipping( $get_package, $get_parent_order );
		wc_reduce_stock_levels( $get_parent_order );
		$get_parent_order->save();
		$transaction_id_note = '';
		if ( ! empty( $get_transaction_id ) ) {
			$transaction_id_note = sprintf( ' (Transaction ID: %s)', $get_transaction_id );

		}
		$get_parent_order->add_order_note( sprintf( 'Upsell Offer Accepted | Funnel ID %s  | Offer ID %s %s', $get_funnel_id, $get_offer_id, $transaction_id_note ) );

	}

	/**
	 * @hooked over `wfocu_front_create_new_order_on_failure`
	 * Perform New Order creation on the failure of upsell offer charge request
	 *
	 * @param $get_funnel_id ID of the funnel running
	 * @param $get_offer_id Id of the offer that has failed
	 */
	public function handle_new_order_creation_on_failure( $get_funnel_id ) {
		$get_parent_order = WFOCU_Core()->data->get( 'porder', false, '_orders' );
		$get_package      = WFOCU_Core()->data->get( '_upsell_package' );

		$failed_order = WFOCU_Core()->orders->create_failed_order( $get_package, $get_parent_order );

		$failed_order->add_order_note( 'Order Payment Failed| Funnel ID ' . $get_funnel_id );
		$this->failed_order = $failed_order;
	}

	/**
	 * @hooked over `woocommerce_get_checkout_order_received_url`
	 * Sets Our session ID in the checkout url to detect with session user is in & which data belongs to the session.
	 *
	 * @param string $url Order Received URL
	 * @param WC_Order $order Order
	 *
	 * @return string
	 */
	public function maybe_add_wfocu_session_param( $url ) {
		$get_transient_key = WFOCU_Core()->data->get_transient_key();

		if ( is_null( $get_transient_key ) ) {
			return $url;
		}

		return add_query_arg( array( 'wfocu-si' => $get_transient_key ), $url );
	}

	public function maybe_log_thankyou_visited( $order_id ) {
		WFOCU_Core()->log->log( 'Order #' . $order_id . ': Order received page viewed successfully' );

		if ( isset( $_GET['_wfocu_process'] ) && 'no' === $_GET['_wfocu_process'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			WFOCU_Core()->log->log( 'Order #' . $order_id . ': Error confirmation logged during upsell process--' . print_r( isset( $_GET['ec'] ) ? wc_clean( $_GET['ec'] ) : '', true ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.PHP.DevelopmentFunctions.error_log_print_r

		}
	}

	public function maybe_setup_funnel_options() {
		WFOCU_Core()->funnels->setup_funnel_options( WFOCU_Core()->data->get_funnel_id() );
	}

	public function maybe_alter_funnel_order_behaviour( $value, $key ) {

		if ( 'order_behavior' !== $key ) {
			return $value;
		}
		$get_parent_order       = WFOCU_Core()->data->get_parent_order();
		$should_alter_behaviour = apply_filters( 'wfocu_alter_order_behaviour_on_batching', false, $get_parent_order );

		if ( $get_parent_order instanceof WC_Order && 'batching' === $value && in_array( $get_parent_order->get_status(), wc_get_is_paid_statuses(), true ) && true === $should_alter_behaviour ) {
			$this->is_order_behavior_switched = true;
			$value                            = 'create_order';
		}

		return $value;
	}

	public function maybe_alter_funnel_order_behaviour_is_cancel( $value, $key ) {

		if ( 'is_cancel_order' !== $key ) {
			return $value;
		}
		if ( $this->is_order_behavior_switched ) {
			return 'no';
		}

		return $value;
	}


	public function maybe_remove_all_taxes_from_offers_when_excempt() {
		$order = WFOCU_Core()->data->get_current_order();

		if ( ! $order instanceof WC_Order ) {
			return false;
		}

		$is_tax_exempt = $order->get_meta( 'is_vat_exempt' );

		if ( 'yes' !== $is_tax_exempt ) {
			return false;
		}

		add_filter( 'wc_tax_enabled', function () {
			return false;
		} );
	}
}

if ( class_exists( 'WFOCU_Core' ) ) {
	WFOCU_Core::register( 'public', 'WFOCU_Public' );
}