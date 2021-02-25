<?php
/**
 * Admin related functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WooFunnels_DB_Updater
 *
 */
class WooFunnels_DB_Updater {
	/**
	 * @var $ins
	 */
	public static $ins;

	/**
	 * @var WooFunnels_Background_Updater $updater
	 */
	public $updater;

	/**
	 * @var WooFunnels_Background_Updater $updater
	 */
	public $order_id_in_process;

	/**
	 * WooFunnels_DB_Updater constructor.
	 */
	public function __construct() {

		/** Showing notice to admin to allow upgrading tokens */
		add_action( 'admin_notices', array( $this, 'woofunnels_show_contact_processing_notice' ) );

		add_action( 'admin_init', array( $this, 'woofunnels_handle_db_upgrade_actions' ), 100 );

		/** Initiate Background Database tables customer and customer on clicking 'Allow' button from tools */
		add_action( 'init', array( $this, 'woofunnels_init_background_updater' ), 110 );

		add_action( 'admin_init', array( $this, 'woofunnels_maybe_update_customer_database' ), 120 );

		/** Creating contact for new orders */
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'woofunnels_wc_order_create_contact' ), 10, 3 );

		/** Creating updating customer on order statuses paid */
		add_action( 'woocommerce_order_status_changed', array( $this, 'woofunnels_status_change_create_update_contact_customer' ), 10, 3 );

		/** Updating customer and customer meta on accepting offer */
		add_action( 'wfocu_offer_accepted_and_processed', array( $this, 'woofunnels_offer_accept_create_update_customer' ), 1, 4 );

		/** Attempt to update customer during single order edit page*/
		add_action( 'dbx_post_sidebar', [ $this, 'bwf_edit_order_changes_to_customer' ], 10, 1 );

		/** Attempt to update customer on WP profile update*/
		add_action( 'profile_update', [ $this, 'bwf_update_contact_on_user_update' ], 10, 1 );
		add_action( 'woocommerce_save_account_details', [ $this, 'bwf_update_contact_on_user_update' ], 10, 1 );

		/** Attempt to update customer On WP user profile login*/
		add_action( 'wp_login', [ $this, 'bwf_index_orders_on_login' ], 10, 2 );

		add_action( 'bwf_order_index_completed', [ $this, 'maybe_change_state_on_success' ] );

		add_action( 'woocommerce_refund_created', [ $this, 'bwf_update_refunded_amount' ], 10, 2 );

		add_action( 'woofunnels_woocommerce_thankyou', [ $this, 'bwf_contact_changes_to_customer' ], 10, 1 );

		add_action( 'rest_api_init', [ $this, 'rest_init_register_async_request' ] );

		add_action( 'woofunnels_tools_add_tables_row_start', [ $this, 'bwf_add_indexing_consent_button' ], 10, 1 );

		add_action( 'shutdown', [ $this, 'maybe_clean_indexing' ] );

		add_action( 'admin_footer', [ $this, 'maybe_re_dispatch_background_process' ] );
	}

	/**
	 * @return WooFunnels_DB_Updater
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * Creating/updating contacts and  customers on offer accepted
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 */
	public static function capture_offer_accepted_event( $request ) {
		$posted_data = $request->get_body_params();
		$order_id    = isset( $posted_data['order_id'] ) ? $posted_data['order_id'] : 0;
		$products    = ( isset( $posted_data['products'] ) && count( $posted_data['products'] ) > 0 ) ? $posted_data['products'] : array();
		$total       = isset( $posted_data['total'] ) ? $posted_data['total'] : 0;

		try {
			bwf_create_update_contact( $order_id, $products, $total, false );
		} catch ( Error $r ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( print_R( $r->getMessage(), true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/** Creating/updating contacts and  customers on order status change */
	public static function capture_order_status_change_event( $request ) {
		$posted_data = $request->get_body_params();
		$order_id    = isset( $posted_data['order_id'] ) ? $posted_data['order_id'] : 0;
		try {
			bwf_create_update_contact( $order_id, array(), 0, false );
		} catch ( Error $r ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( print_R( $r->getMessage(), true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * @param $request
	 */
	public static function capture_wp_user_login_event( $request ) {

		$posted_data = $request->get_body_params();
		$user_id     = isset( $posted_data['user_id'] ) ? $posted_data['user_id'] : 0;
		try {
			bwf_update_contact_on_login( $user_id );
		} catch ( Error $r ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( print_R( $r->getMessage(), true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	public function needs_upgrade() {
		return apply_filters( 'bwf_init_db_upgrade', false );
	}

	public function woofunnels_handle_db_upgrade_actions() {
		if ( isset( $_GET['_bwf_remove_updated_db_notice'] ) && isset( $_GET['_bwf_updated_nonce'] ) ) { // WPCS: input var ok, CSRF ok.
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_bwf_updated_nonce'] ) ), '_bwf_hide_updated_nonce' ) ) { // WPCS: input var ok, CSRF ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woofunnels' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash( $_GET['_bwf_remove_updated_db_notice'] ) ); // WPCS: input var ok, CSRF ok.

			if ( 'yes' === $hide_notice ) {
				$this->set_upgrade_state( '5' );
			}
		}

		if ( isset( $_GET['bwf_update_db'] ) && isset( $_GET['_bwf_update_nonce'] ) ) { // WPCS: input var ok, CSRF ok.
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_bwf_update_nonce'] ) ), '_bwf_start_update_nonce' ) ) { // WPCS: input var ok, CSRF ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woofunnels' ) );
			}

			$update_db = sanitize_text_field( wp_unslash( $_GET['bwf_update_db'] ) ); // WPCS: input var ok, CSRF ok.

			if ( 'yes' === $update_db && '0' === $this->get_upgrade_state() ) {
				$this->set_upgrade_state( '2' );
			}
		}

		if ( isset( $_GET['_bwf_remove_declined_notice'] ) && isset( $_GET['_bwf_declined_nonce'] ) ) { // WPCS: input var ok, CSRF ok.
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_bwf_declined_nonce'] ) ), '_bwf_hide_declined_nonce' ) ) { // WPCS: input var ok, CSRF ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woofunnels' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash( $_GET['_bwf_remove_declined_notice'] ) ); // WPCS: input var ok, CSRF ok.

			if ( 'yes' === $hide_notice ) {
				$this->set_upgrade_state( '6' );
			}
		}
	}

	public function set_upgrade_state( $stage ) {
		update_option( '_bwf_db_upgrade', $stage, true );
	}

	public function get_upgrade_state() {

		/**
		 * 0: upgrade is allowed, optin message should show
		 * 1: Upgrade is declined.
		 * 2: Upgrade is accepted but not dispatched
		 * 3: Upgrade is accepted & dispatched (show notice)
		 * 4: Upgrade is completed (show notice)
		 * 5: Upgrade is completed and notice dismissed
		 * 6: Upgrade is declined and dismissed
		 */
		return get_option( '_bwf_db_upgrade', '0' );
	}

	/**
	 * Contact processing notice to notify admin about the state
	 */
	public function woofunnels_show_contact_processing_notice() {

		$db_state = $this->get_upgrade_state();

		if ( '3' === $db_state ) { ?>
            <div class="bwf-notice notice notice-success">
                <div class="bwf-logo-wrapper">
                    <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) ) . 'img/buildwoofunnels.png'; ?>">
                </div>

                <div class="bwf-message-content">
                    <strong><?php esc_html_e( 'Indexing of orders has started', 'woofunnels' ); ?></strong>
                    <p><?php esc_html_e( 'It may take sometime to finish the process. We will update this notice once the process completes.', 'woofunnels' ); ?>
                </div>
            </div>
			<?php
		} elseif ( '4' === $db_state ) {
			?>
            <div class="bwf-notice notice notice-success">
                <div class="bwf-logo-wrapper">
                    <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) ) . 'img/buildwoofunnels.png'; ?>">
                </div>

                <div class="bwf-message-content">
                    <strong><?php esc_html_e( 'Success', 'woofunnels' ); ?></strong>
                    <p><?php esc_html_e( 'Order indexing completed successfully.', 'woofunnels' ); ?></p>
                </div>

                <div class="bwf-message-action">
                    <a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( '_bwf_remove_updated_db_notice', 'yes' ), '_bwf_hide_updated_nonce', '_bwf_updated_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'woofunnels' ); ?></a>
                </div>
            </div>
			<?php
		} elseif ( '1' === $db_state ) {
			?>

            <div class="bwf-notice notice notice-error">
                <div class="bwf-logo-wrapper">
                    <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) ) . 'img/buildwoofunnels.png'; ?>">
                </div>

                <div class="bwf-message-content">
                    <strong><?php esc_html_e( 'WooFunnels Notice', 'woofunnels' ); ?></strong>
                    <p><?php echo sprintf( wp_kses_post( __( 'Unable to complete indexing of orders. Please <a target="_blank" href="%s">contact support</a> to get the issue resolved.', 'woofunnels' ) ), esc_url( 'https://buildwoofunnels.com/support/' ) ); ?></p>
                </div>

                <div class="bwf-message-action">
                    <a class="button-secondary" target="_blank" href="<?php echo esc_url( 'https://buildwoofunnels.com/support/' ); ?>"><?php esc_html_e( 'Contact Support', 'woofunnels' ); ?></a>
                    <a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( '_bwf_remove_declined_notice', 'yes' ), '_bwf_hide_declined_nonce', '_bwf_declined_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'woofunnels' ); ?></a>
                </div>
            </div>
			<?php
		}
		?>
        <style>

            .wp-admin .bwf-notice.notice, .wp-admin.toplevel_page_woofunnels .bwf-notice.notice, .wp-admin.woofunnels_page_upstroke .bwf-notice.notice {
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex !important;
                -webkit-box-align: center;
                -webkit-align-items: center;
                -ms-flex-align: center;
                align-items: center;
                padding: 15px;;
            }

            .wp-admin .bwf-message-content, .wp-admin.toplevel_page_woofunnels .bwf-message-content, .wp-admin.woofunnels_page_upstroke .bwf-message-content {
                padding: 0 13px;
            }

            .wp-admin .bwf-message-action, .wp-admin.toplevel_page_woofunnels .bwf-message-action, .wp-admin.woofunnels_page_upstroke .bwf-message-action {
                text-align: center;
                display: -webkit-box;
                display: -webkit-inline-flex;
                display: -ms-flexbox;
                display: inline;
                -webkit-box-orient: vertical;
                -webkit-box-direction: normal;
                -webkit-flex-direction: column;
                -ms-flex-direction: column;
                flex-direction: column;
                margin-left: auto;
            }

            .wp-admin .bwf-message-content p, .wp-admin.toplevel_page_woofunnels .bwf-message-content p, .wp-admin.woofunnels_page_upstroke .bwf-message-content p {
                margin: 0;
                padding: 0;
            }

            .wp-admin .bwf-logo-wrapper, .wp-admin.toplevel_page_woofunnels .bwf-logo-wrapper, .wp-admin.woofunnels_page_upstroke .bwf-logo-wrapper {
                height: 51px;
            }
        </style>
		<?php
	}

	// Register offer accepted and processed

	/**
	 * Initiate WooFunnels_Background_Updater class
	 * @see woofunnels_maybe_update_customer_database()
	 */
	public function woofunnels_init_background_updater() {
		if ( class_exists( 'WooFunnels_Background_Updater' ) ) {
			$this->updater = new WooFunnels_Background_Updater();
		}
	}

	/**
	 * Creating BWF contact if not exist on WC new order
	 * sync call
	 *
	 * @param $order_id
	 * @param $posted_data
	 * @param $order WC_Order
	 */
	public function woofunnels_wc_order_create_contact( $order_id, $posted_data, $order ) {
		$wp_id = $order->get_customer_id();
		$email = $order->get_billing_email();

		/** If no email then return */
		if ( empty( $email ) ) {
			return;
		}

		/** Assigning wp id 0 if not available */
		if ( empty( $wp_id ) ) {
			$wp_id = 0;
		}

		$bwf_contact = bwf_get_contact( $wp_id, $email );

		/** If contact exists then directly add meta */
		if ( $bwf_contact->get_id() > 0 ) {
			/** Just update the temp meta */
			$order_total = bwf_get_order_total( $order );
			$product_ids = bwf_get_order_product_ids( $order );

			$meta_key   = 'new_order_temp';
			$meta_value = $bwf_contact->get_meta( $meta_key );

			if ( ! is_array( $meta_value ) ) {
				$meta_value = [];
			}
			$meta_value[ $order_id ] = array(
				'total'    => $order_total,
				'products' => $product_ids,
			);

			$bwf_contact->set_meta( $meta_key, $meta_value );
			$bwf_contact->save_meta();
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Order #".$order->get_id().": Processed against contact iD".$bwf_contact->get_id(), 'woofunnels_indexing' );

			$order->update_meta_data('_woofunnel_cid',$bwf_contact->get_id());
			$order->save_meta_data();
			return;
		}

		/** Need to create a contact */
		$wp_f_name = $wp_l_name = '';

		if ( $wp_id > 0 ) {
			$wp_user   = get_user_by( 'id', $wp_id );
			$email     = $wp_user->user_email;
			$wp_f_name = $wp_user->user_firstname;
			$wp_l_name = $wp_user->user_lastname;
		}
		$bwf_contact->set_email( $email );

		$f_name = empty( $wp_f_name ) ? $order->get_billing_first_name() : $wp_f_name;
		$l_name = empty( $wp_l_name ) ? $order->get_billing_last_name() : $wp_l_name;

		$bwf_contact->set_f_name( $f_name );
		$bwf_contact->set_l_name( $l_name );

		$order_total = bwf_get_order_total( $order );
		$product_ids = bwf_get_order_product_ids( $order );

		$meta_key   = 'new_order_temp';
		$meta_value = [];

		$meta_value[ $order_id ] = array(
			'total'    => $order_total,
			'products' => $product_ids,
		);

		$bwf_contact->set_meta( $meta_key, $meta_value );

		bwf_contact_maybe_update_creation_date( $bwf_contact, $order );

		$bwf_contact->save( false );
		$bwf_contact->save_meta();
		WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Order #".$order->get_id().": Processed against contact iD".$bwf_contact->get_id(), 'woofunnels_indexing' );

		$order->update_meta_data('_woofunnel_cid',$bwf_contact->get_id());
		$order->save_meta_data();
		return;
	}

	/**
	 * Creating or updating contact and customer on order status changed to paid statuses
	 *
	 * @param $order_id
	 * @param $from
	 * @param $to
	 */
	public function woofunnels_status_change_create_update_contact_customer( $order_id, $from, $to ) {

		$order         = wc_get_order( $order_id );
		$paid_status   = $order->has_status( wc_get_is_paid_statuses() );
		$woofunnel_custid = $order->get_meta( '_woofunnel_custid' );
		if ( $paid_status && empty( $woofunnel_custid ) ) {
			$data = array( 'order_id' => $order_id );
			$url  = home_url() . '/?rest_route=/woofunnel_customer/v1/order_status_changed';
			$args = bwf_get_remote_rest_args( $data );

			wp_remote_post( $url, $args );
		}

		//Reducing total_value with remaining order total (if partial earlier refund made)
		if ( 'cancelled' === $to ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Order status changes from $from to $to for order id: $order_id", 'woofunnels_indexing' );
			bwf_reduce_customer_total_on_cancel( $order_id );
		}
	}

	public function rest_init_register_async_request() {
		//Posting data to aysnc request for processing package product and total for indexing
		register_rest_route( 'woofunnel_customer/v1', '/offer_accpeted', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( __CLASS__, 'capture_offer_accepted_event' ),
			'permission_callback' => '__return_true',
		) );

		/** Posting data to async request for processing new order product on order status change */
		register_rest_route( 'woofunnel_customer/v1', '/order_status_changed', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( __CLASS__, 'capture_order_status_change_event' ),
			'permission_callback' => '__return_true',
		) );

		/** Posting data to async request for indexing on user login */
		register_rest_route( 'woofunnel_customer/v1', '/wp_user_login', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( __CLASS__, 'capture_wp_user_login_event' ),
			'permission_callback' => '__return_true',
		) );

		/** Posting data to async request for saving contact data on WC thank you order */
		register_rest_route( 'woofunnel_customer/v1', '/wc_thank_you', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( __CLASS__, 'capture_wc_thank_you_event' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Updating contact and customer on accepting offer
	 *
	 * @param $get_offer_id
	 * @param $get_package
	 * @param $get_parent_order
	 * @param $new_order
	 */
	public function woofunnels_offer_accept_create_update_customer( $get_offer_id, $get_package, $get_parent_order, $new_order ) {
		$order_id     = 0;
		$new_order_id = 0;

		if ( $get_parent_order instanceof WC_Order ) {
			$order_id = $get_parent_order->get_id();
		}
		if ( $new_order instanceof WC_Order ) {
			$new_order_id = $new_order->get_id();
		}

		/**
		 *  Updating contact and customer in async REST API request if parent order is already indexed otherwise customer will be updated during parent order status change
		 * If batching is off then customer will be updated during child order status change
		 */
		if ( $order_id && ! empty( get_post_meta( $order_id, '_woofunnel_custid', false ) ) && $new_order_id === 0 ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Creating/Updating contact and customer in async request for batching order_id: $order_id and offer id: $get_offer_id ", 'woofunnels_indexing' );
			if ( is_array( $get_package ) && isset( $get_package['products'] ) && is_array( $get_package['products'] ) ) {
				$product_ids = array();
				foreach ( $get_package['products'] as $product_data ) {
					if ( isset( $product_data['id'] ) ) {
						array_push( $product_ids, $product_data['id'] );
					}
					if ( isset( $product_data['_offer_data'] ) && isset( $product_data['_offer_data']->id ) && isset( $product_data['id'] ) && $product_data['id'] !== $product_data['_offer_data']->id ) {
						array_push( $product_ids, $product_data['_offer_data']->id );
					}
				}
			}

			$total       = isset( $get_package['total'] ) ? $get_package['total'] : 0;
			$product_ids = array_unique( $product_ids );

			$data = array(
				'products' => $product_ids,
				'total'    => $total,
				'order_id' => $order_id,
			);
			$url  = home_url() . '/?rest_route=/woofunnel_customer/v1/offer_accpeted';
			$args = bwf_get_remote_rest_args( $data );

			wp_remote_post( $url, $args );
		}
	}

	public static function capture_wc_thank_you_event( $request ) {
		$posted_data = $request->get_body_params();
		if ( ! isset( $posted_data['order_id'] ) || empty( $posted_data['order_id'] ) ) {
			return;
		}

		$order_id = $posted_data['order_id'];
		bwf_update_contact_changes_to_customer( $order_id );

	}

	/**
	 * Updating refunded amount in order meta
	 *
	 * @param $refund_id
	 * @param $args
	 */
	public function bwf_update_refunded_amount( $refund_id, $args ) {
		$order_id = isset( $args['order_id'] ) ? $args['order_id'] : 0;
		$amount   = isset( $args['amount'] ) ? $args['amount'] : 0;

		bwf_update_customer_refunded( $order_id, $amount );
	}

	public function maybe_change_state_on_success() {
		delete_option( '_bwf_last_offsets' );
		$this->set_upgrade_state( '4' );
	}

	/**
	 * Updated changes in customer and removing 'changes' meta key on thank you
	 * Async call
	 *
	 * @hooked woocommerce_thankyou
	 *
	 * @param $order_id
	 */
	public function bwf_contact_changes_to_customer( $order_id ) {
		$url  = site_url() . '/?rest_route=/woofunnel_customer/v1/wc_thank_you';
		$data = array( 'order_id' => $order_id );
		$args = bwf_get_remote_rest_args( $data );

		wp_remote_post( $url, $args );
	}

	/**
	 * Updated changes in customer and removing 'changes' meta key on order edit
	 *
	 * @param $order_id
	 * @param $post
	 *
	 * @hooked edit_post
	 */
	public function bwf_edit_order_changes_to_customer( $post ) {
		if ( $post instanceof WP_Post && 'shop_order' === get_post_type( $post ) ) {
			$order_id = $post->ID;
			bwf_update_contact_changes_to_customer( $order_id );

		}
	}

	/**
	 * Adding allow button for db upgrade inside tools
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function bwf_add_indexing_consent_button() {
		$get_threshold_order = get_option( '_bwf_order_threshold', BWF_THRESHOLD_ORDERS );
		$bwf_db_upgrade      = $this->get_upgrade_state();
		global $wpdb;

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( '3' !== $bwf_db_upgrade || $get_threshold_order < 1 ) {
			$get_threshold_order = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(order_post.ID) FROM {$wpdb->posts} as `order_post` LEFT JOIN {$wpdb->postmeta} as `order_meta` ON order_post.ID = order_meta.post_id AND order_meta.meta_key = %s WHERE order_post.post_type = %s AND (order_post.post_status = %s OR order_post.post_status = %s)", '_woofunnel_cid', 'shop_order', 'wc-processing', 'wc-completed' ) );
		}
		$remaining_text = '';

		if ( 0 === $get_threshold_order && 0 === absint( $bwf_db_upgrade ) ) {
			$this->set_upgrade_state( '5' );
			$bwf_db_upgrade = '5';
		}
		if ( '5' !== $bwf_db_upgrade && '4' !== $bwf_db_upgrade &&  $get_threshold_order > 0) {
			$remaining_text = sprintf( __( 'This store has <strong>%s orders</strong> to index.' ), $get_threshold_order );
		}

		if ( true === apply_filters( 'bwf_needs_order_indexing', false ) ) {
			?>
            <tr>
                <th>
                    <strong class="name"><?php esc_html_e( 'Index Past Orders', 'woofunnels' ); ?></strong>
                    <p class="description"><?php echo sprintf( __( 'This tool will scan all the previous orders and create an optimized index to run efficient queries. %s', 'woofunnels' ), $remaining_text ); ?></p>
					<?php if ( '1' === $bwf_db_upgrade || '6' === $bwf_db_upgrade ) { ?>
                        <span style="width:100%; color: red;"><?php esc_html_e( 'Unable to complete indexing of orders.', 'woofunnels' ); ?></span><br/>
						<?php esc_html_e( 'Please', 'woofunnels' ); ?>
                        <a target="_blank" href="<?php echo esc_url( 'https://buildwoofunnels.com/support/' ); ?>"><?php esc_html_e( 'contact support', 'woofunnels' ); ?></a><?php esc_html_e( ' to get the issue resolved.', 'woofunnels' ); ?>
                        <br/><br/>
					<?php } ?>
                    <a href="https://buildwoofunnels.com/docs/upstroke/miscellaneous/index-past-order/"><?php esc_html_e( 'Learn more about this process', 'woofunnels' ); ?></a>
                </th>
                <td class="run-tool">
					<?php if ( '3' === $bwf_db_upgrade ) { ?>
                        <a href="javascript:void(0);" class="button button-large disabled"><?php esc_html_e( 'Running', 'woofunnels' ); ?></a>
					<?php } elseif ( '4' === $bwf_db_upgrade || '5' === $bwf_db_upgrade ) { ?>
                        <a href="javascript:void(0);" class="button button-large disabled"><?php esc_html_e( 'Completed', 'woofunnels' ); ?></a>

					<?php } elseif ( '1' === $bwf_db_upgrade || '6' === $bwf_db_upgrade ) { ?>
                        <a href="javascript:void(0);" class="button button-large disabled"><?php esc_html_e( 'Start', 'woofunnels' ); ?></a>
						<?php
					} else {
						$start_url = esc_url( wp_nonce_url( add_query_arg( 'bwf_update_db', 'yes' ), '_bwf_start_update_nonce', '_bwf_update_nonce' ) );
						?>
                        <a class="button button-large <?php echo ( $get_threshold_order > 0 ) ? '' : 'disabled'; ?>" href="<?php echo ( $get_threshold_order > 0 ) ? $start_url : 'javascript:void(0);'; ?>"><?php esc_html_e( 'Start', 'woofunnels' ); ?></a>
					<?php } ?>
                </td>
            </tr>
			<?php
		}
	}

	/**
	 * @param $user_id
	 * @param $old_user_data
	 *
	 * @hooked on profile_update
	 */
	public function bwf_update_contact_on_user_update( $user_id ) {

		if ( 'profile_update' === current_action() && ( did_action( 'personal_options_update' ) || did_action( 'edit_user_profile_update' ) ) ) {
			bwf_update_contact_on_profile_update( $user_id );
		}

		if ( 'woocommerce_save_account_details' === current_action() ) {
			bwf_update_contact_on_profile_update( $user_id );
		}


	}

	/**
	 * Indexing orders and create/update contacts and customers on user login
	 *
	 * @param $user_login
	 * @param $user
	 *
	 * @hooked on wp_login
	 */
	public function bwf_index_orders_on_login( $user_login, $user ) {
		$data = array( 'user_id' => $user->ID );
		$url  = site_url() . '/?rest_route=/woofunnel_customer/v1/wp_user_login';
		$args = bwf_get_remote_rest_args( $data );

		wp_remote_post( $url, $args );
	}

	/**
	 *
	 */
	public function maybe_clean_indexing() {
		if ( 1 === did_action( 'admin_head' ) && current_user_can( 'manage_options' ) && 'yes' === filter_input( INPUT_GET, 'bwf_index_clean', FILTER_SANITIZE_STRING ) ) {
			global $wpdb;

			$tables = array(
				'bwf_contact',
				'bwf_contact_meta',
				'bwf_wc_customers',
			);

			foreach ( $tables as &$table ) {
				$bwf_table = $wpdb->prefix . $table;
				$wpdb->query( "DROP TABLE IF EXISTS $bwf_table" );  //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
			}

			delete_option( '_bwf_created_tables' );
			delete_option( '_bwf_db_upgrade' );
			delete_option( '_bwf_order_threshold' );
			delete_option( '_bwf_offset' );
			delete_option( '_bwf_last_offsets' );

			$table = $wpdb->prefix . 'postmeta';
			$wpdb->delete( $table, array( 'meta_key' => '_woofunnel_cid' ) );  //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$wpdb->delete( $table, array( 'meta_key' => '_woofunnel_custid' ) );  //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$this->updater->kill_process_safe();
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'Indexing was cleaned manually.', 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
		$this->bwf_maybe_restart_indexing();
	}

	/**
	 * Restart indexing when it is stop due to any reason like cron disabled, server stopped etc
	 */
	public function bwf_maybe_restart_indexing() {
		if ( 1 === did_action( 'admin_head' ) && current_user_can( 'manage_options' ) && 'yes' === filter_input( INPUT_GET, 'bwf_restart_indexing', FILTER_SANITIZE_STRING ) ) {
			$this->set_upgrade_state( '2' );
			$this->woofunnels_maybe_update_customer_database();
		}
	}

	/**
	 * @hooked over `admin_head`
	 * This method takes care of database updating process.
	 * Checks whether there is a need to update the database
	 * Iterates over define callbacks and passes it to background updater class
	 * Update bwf_customer and bwf_customer_meta tables with new token from different tables
	 */
	public function woofunnels_maybe_update_customer_database() {

		if ( is_null( $this->updater ) ) {
			return;
		}

		if ( isset( $_GET['bwf_update_db'] ) && isset( $_GET['_bwf_update_nonce'] ) ) { // WPCS: input var ok, CSRF ok.
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_bwf_update_nonce'] ) ), '_bwf_start_update_nonce' ) ) { // WPCS: input var ok, CSRF ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woofunnels' ) );
			}

			$bwf_update_db = sanitize_text_field( wp_unslash( $_GET['bwf_update_db'] ) ); // WPCS: input var ok, CSRF ok.

			$get_state = $this->get_upgrade_state();
			if ( 'yes' === $bwf_update_db && '2' === $get_state ) {
				$this->bwf_start_indexing();
			}
		}
	}

	public function bwf_start_indexing() {
		$task = 'bwf_create_update_contact_customer';  //Scanning order table and updating customer tables
		$this->updater->push_to_queue( $task );
		WooFunnels_Dashboard::$classes['BWF_Logger']->log( '**************START INDEXING************', 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$this->set_upgrade_state( '3' );
		$this->updater->save()->dispatch();
		WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'First Dispatch completed', 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
	}

	public function capture_fatal_error() {
		$error = error_get_last();
		if ( ! empty( $error ) ) {
			if ( is_array( $error ) && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {

				if ( $this->is_ignorable_error( $error['message'] ) ) {
					return;
				}
				WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'Error logged during the process' . print_r( $error, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

				$current_offset = get_option( '_bwf_offset', 0 );
				$current_offset ++;
				update_option( '_bwf_offset', $current_offset );

				$order_id = WooFunnels_Dashboard::$classes['WooFunnels_DB_Updater']->get_order_id_process();
				$order    = wc_get_order( $order_id );
				if ( $order instanceof WC_Order ) {

					$order->update_meta_data( '_woofunnel_cid', 0 );
					$order->save_meta_data();
				}
			}
		}
	}

	private function is_ignorable_error( $str ) {
		$get_all_ingorable_regex = $this->ignorable_errors();

		foreach ( $get_all_ingorable_regex as $re ) {
			$matches = [];
			preg_match_all( $re, $str, $matches, PREG_SET_ORDER, 0 );
			if ( ! empty( $matches ) ) {
				return true;
			}
		}

		return false;
	}

	private function ignorable_errors() {
		return [ '/Maximum execution time of/m', '/Allowed memory size of/m' ];
	}

	public function set_order_id_in_process( $order_id ) {
		$this->order_id_in_process = $order_id;
	}

	public function get_order_id_process() {
		return $this->order_id_in_process;
	}

	public function maybe_re_dispatch_background_process() {
		$this->updater->maybe_re_dispatch_background_process();
	}
}
