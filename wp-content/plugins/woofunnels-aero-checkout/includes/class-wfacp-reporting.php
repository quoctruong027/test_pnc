<?php
defined( 'ABSPATH' ) || exit; //Exit if accessed directly

class WFACP_Reporting {

	private static $ins = null;
	private $is_cart_restored = false;

	private function __construct() {
		global $wpdb;
		$wpdb->wfacp_stats = $wpdb->prefix . 'wfacp_stats';
		add_action( 'admin_init', [ $this, 'create_table' ] );
		add_action( 'woocommerce_payment_complete', [ $this, 'update_order_total_in_order_meta' ] );
		add_action( 'woocommerce_order_status_changed', array( $this, 'insert_row_for_ipn_based_gateways' ), 10, 3 );

		add_filter( 'woocommerce_cod_process_payment_order_status', [ $this, 'cod_payment' ], 99, 2 );
		add_filter( 'woocommerce_cheque_process_payment_order_status', [ $this, 'cod_payment' ], 99, 2 );
		add_filter( 'woocommerce_bacs_process_payment_order_status', [ $this, 'cod_payment' ], 99, 2 );

		add_action( 'delete_post', [ $this, 'delete_report_for_order' ] );

		add_action( 'wfab_pre_abandoned_cart_restored', [ $this, 'check_if_autobot_cart_restored' ] );
		add_action( 'woocommerce_thankyou', [ $this, 'wfacp_clear_view_session' ], 10, 1 );

		add_action( 'woocommerce_thankyou', [ $this, 'updating_reports_from_orders' ] );
		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'update_order_review' ] );
	}

	public static function get_instance() {
		if ( is_null( self::$ins ) ) {
			self::$ins = new self();
		}

		return self::$ins;
	}


	public function create_table() {
		/** create table in ver 1.0 */
		if ( false !== get_option( 'wfacp_db_ver_2_0', false ) ) {
			return;
		}
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$creationSQL = "CREATE TABLE {$wpdb->prefix}wfacp_stats (
 		  ID bigint(20) unsigned NOT NULL auto_increment,
 		  order_id bigint(20) unsigned NOT NULL,
 		  wfacp_id bigint(20) unsigned NOT NULL,
 		  total_revenue varchar(255) not null default 0,
 		  cid int(11) unsigned NOT NULL DEFAULT 0,
 		  fid int(11) unsigned NOT NULL DEFAULT 0, 		 		  
 		  date datetime NOT NULL, 		  
		  PRIMARY KEY  (ID),
		  KEY ID (ID),
		  KEY oid (order_id),
		  KEY bid (wfacp_id),
		  KEY date (date)
		) $collate;";
		dbDelta( $creationSQL );
		update_option( 'wfacp_db_ver_1_0', date( 'Y-m-d' ) );
	}

	/**
	 * @param $status
	 * @param $order WC_Order
	 *
	 * @return mixed
	 */
	public function cod_payment( $status, $order ) {
		$this->update_order_total_in_order_meta( $order->get_id() );

		return $status;
	}

	/**
	 * hooked @ 'woocommerce_payment_complete'
	 *
	 * @param $order_id
	 */
	public function update_order_total_in_order_meta( $order_id ) {
		$wfacp_report_data = get_post_meta( $order_id, '_wfacp_report_data', true );
		$wfacp_total       = ( is_array( $wfacp_report_data ) && isset( $wfacp_report_data['wfacp_total'] ) ) ? $wfacp_report_data['wfacp_total'] : '';
		if ( empty( $wfacp_total ) && '0.00' !== $wfacp_total ) {
			$this->update_reporting_data_in_meta( $order_id );
		}
	}

	/**
	 * hooked @ 'woocommerce_order_status_changed'
	 *
	 * @param $order_id
	 * @param $from
	 * @param $to
	 */
	public function insert_row_for_ipn_based_gateways( $order_id, $from, $to ) {
		$order          = wc_get_order( $order_id );
		$payment_method = $order->get_payment_method();

		$ipn_gateayws = apply_filters( 'wfacp_ipn_gateways_list', array( 'paypal', 'mollie_wc_gateway_ideal', 'mollie_wc_gateway_bancontact', 'mollie_wc_gateway_sofort' ) );
		if ( ! in_array( $payment_method, $ipn_gateayws, true ) ) {
			return false;
		}
		if ( $order_id > 0 && 'pending' === $from && in_array( $to, wc_get_is_paid_statuses(), true ) ) {
			$this->updating_reports_from_orders( $order_id );
		}
	}

	public function update_reporting_data_in_meta( $order_id ) {
		$order    = wc_get_order( $order_id );
		$wfacp_id = get_post_meta( $order_id, '_wfacp_post_id', true );
		if ( empty( $wfacp_id ) || $wfacp_id < 1 ) {
			return;
		}
		$wfacp_used_total = $order->get_total();
		$funnel_id        = 0;
		if ( class_exists( 'WFFN_Core' ) ) {
			$funnel = WFFN_Core()->data->get_session_funnel();
			if ( $funnel instanceof WFFN_Funnel ) {
				$funnel_id = $funnel->get_id();
			}
		}
		update_post_meta( $order_id, '_wfacp_report_data', array( 'wfacp_total' => $wfacp_used_total, 'funnel_id' => $funnel_id ) );
	}

	/**
	 * hooked @woocommerce_thankyou
	 *
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function updating_reports_from_orders( $order_id ) {
		$order        = wc_get_order( $order_id );
		$order_status = $order->get_status();
		if ( ! in_array( $order_status, wc_get_is_paid_statuses(), true ) ) {
			return false;
		}
		$wfacp_report_data = get_post_meta( $order_id, '_wfacp_report_data', true );
		$wfacp_total       = ( is_array( $wfacp_report_data ) && isset( $wfacp_report_data['wfacp_total'] ) ) ? $wfacp_report_data['wfacp_total'] : '';

		if ( empty( $wfacp_total ) || '0.00' === $wfacp_total ) {
			return false;
		}
		$wfacp_id  = get_post_meta( $order_id, '_wfacp_post_id', true );
		$paid_date = get_post_meta( $order_id, '_paid_date', true );
		$cid       = get_post_meta( $order_id, '_woofunnel_cid', true );
		$funnel_id = ( is_array( $wfacp_report_data ) && isset( $wfacp_report_data['funnel_id'] ) ) ? $wfacp_report_data['funnel_id'] : 0;
		$paid_date = empty( $paid_date ) ? $order->get_date_created() :  $paid_date;

		$wfacp_data = [
			'order_id'      => absint( $order_id ),
			'wfacp_id'      => absint( $wfacp_id ),
			'total_revenue' => abs( $wfacp_total ),
			'date'          => $paid_date,
			'cid'           => $cid,
			'fid'           => $funnel_id
		];
		$report_id  = $this->insert_data( $wfacp_data );
		update_post_meta( $order_id, '_wfacp_report_data', array( 'wfacp_total' => '0.00' ) );
	}

	public function delete_report_for_order( $post_id ) {
		$get_post_type = get_post_type( $post_id );

		/**
		 * @todo keep eye on WC upgrades so that we can handle this when Orders are no longer be posts.
		 */
		if ( 'shop_order' === $get_post_type ) {

			global $wpdb;
			$wpdb->delete( $wpdb->wfacp_stats, [ 'order_id' => $post_id ], [ '%d' ] );
		}

	}

	private function insert_data( $data ) {
		global $wpdb;
		$status = $wpdb->insert( $wpdb->wfacp_stats, $data, [ '%d', '%d', '%s', '%s', '%d', '%d' ] );
		if ( false !== $status ) {
			return $status;
		}

		return null;
	}

	private function update_data( $data, $where ) {
		global $wpdb;
		$status = $wpdb->update( $wpdb->wfacp_stats, $data, $where, [ '%s' ], [ '%d', '%d' ] );
		if ( false !== $status ) {
			return true;
		}

		return null;
	}

	public function get_session_key( $aero_id ) {
		return WC()->session->get( 'wfacp_view_session_' . $aero_id, false );
	}

	public function update_session_key( $aero_id ) {
		WC()->session->set( 'wfacp_view_session_' . $aero_id, true );
	}


	public function check_if_autobot_cart_restored() {
		$this->is_cart_restored = true;
	}

	public function wfacp_clear_view_session( $order_id ) {
		$aero_id = ( $order_id > 0 ) ? get_post_meta( $order_id, '_wfacp_post_id', true ) : 0;
		if ( $aero_id > 0 && ! is_null( WC()->session ) && WC()->session->has_session() ) {
			WC()->session->set( 'wfacp_view_session_' . $aero_id, false );
		}
	}

	public function update_order_review( $postdata ) {
		$post_data = [];
		parse_str( $postdata, $post_data );
		$wfacp_id = isset( $post_data['_wfacp_post_id'] ) ? $post_data['_wfacp_post_id'] : 0;

		if ( $wfacp_id < 1 ) {
			return;
		}

		$status = $this->get_session_key( $wfacp_id );

		/** Already captured */
		if ( true === $status ) {
			return;
		}
		/** Check if AutoBot installed and cart tracking in enabled and Cart is restored, don't require cart initiate increment */
		if ( true === $this->is_cart_restored ) {
			$this->update_session_key( $wfacp_id );

			return;
		}

		if ( ! class_exists( 'WFCO_Model_Report_views' ) ) {
			$bwf_configuration = WooFunnel_Loader::get_the_latest();
			require $bwf_configuration['plugin_path'] . '/woofunnels/connector/db/class-wfco-model-report-views.php'; //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}
		WFCO_Model_Report_views::update_data( date( 'Y-m-d' ), $wfacp_id, 4 );
		$this->update_session_key( $wfacp_id );

	}
}

if ( class_exists( 'WFACP_Core' ) && ! WFACP_Common::is_disabled() ) {
	WFACP_Core::register( 'reporting', 'WFACP_Reporting' );
}
