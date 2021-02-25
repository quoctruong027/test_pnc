<?php

class BWFAN_Abandoned_Cart_Analytics {

	private static $ins = null;

	private $filter_date = 7;
	private $no_of_days = 7;
	private $date_rage_search = false;
	private $start_date = '';
	private $end_date = '';

	private function __construct() {
		$this->end_date   = date( 'Y-m-d', strtotime( "+1 days" ) );
		$this->start_date = date( 'Y-m-d', strtotime( "-{$this->filter_date} days" ) );
		$this->detect_no_days();
		$this->detect_date_range();
	}

	private function detect_no_days() {
		$no_of_days = $this->filter_date;
		if ( isset( $_GET['no_of_days'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$no_of_days = sanitize_text_field( $_GET['no_of_days'] ); //phpcs:ignore WordPress.Security.NonceVerification
		}

		$this->no_of_days = $no_of_days;
		if ( $no_of_days > - 1 ) {
			$this->filter_date = absint( $no_of_days );

			$start_date = date( 'Y-m-d', strtotime( "-{$this->filter_date} days" ) );
			$end_date   = date( 'Y-m-d', strtotime( "+1 days" ) );

			$this->prepare_dates( $start_date, $end_date );
		}
	}

	private function prepare_dates( $first_date, $second_date, $set_max = false ) {
		$first_date_time_stamp  = strtotime( $first_date );
		$second_date_time_stamp = strtotime( $second_date );

		if ( ( $second_date_time_stamp < $first_date_time_stamp ) || ( $second_date_time_stamp === $second_date ) ) {
			$this->calculate_max_days();

			return;
		} else {
			$may_be_return = false;
			try {
				$registered_date      = get_option( 'bwfan_ver_1_0', false );
				$abandoned_start_date = strtotime( $registered_date );

				if ( $first_date_time_stamp < $abandoned_start_date ) {
					$first_date = $registered_date;
				}

				$datetime1 = new DateTime( $first_date );
			} catch ( Exception $e ) {
				$may_be_return = true;
			}
			try {
				$datetime2 = new DateTime( $second_date );
			} catch ( Exception $e ) {
				$may_be_return = true;
			}

			if ( $may_be_return ) {
				// Calculate Max date

				$this->calculate_max_days();

				return;
			}

			$interval      = $datetime1->diff( $datetime2 );
			$interval_date = $interval->format( '%a' );

			if ( $interval_date > 0 ) {
				$this->filter_date = $interval_date;
				$this->start_date  = $first_date;
				$this->end_date    = $second_date;
				if ( $set_max ) {
					$this->date_rage_search = true;
				}
			}
		}
	}

	private function calculate_max_days() {
		if ( false === apply_filters( 'bwfan_ab_delete_inactive_carts', false ) ) {
			return;
		}

		$registered_date = get_option( 'bwfan_ver_1_0', false );
		if ( false === $registered_date ) {
			return;
		}

		$global_settings = BWFAN_Common::get_global_settings();
		if ( isset( $global_settings['bwfan_ab_remove_inactive_cart_time'] ) ) {
			$max_days = absint( $global_settings['bwfan_ab_remove_inactive_cart_time'] );
			if ( $max_days > 0 ) {
				$this->filter_date = $max_days;
			}
		}
	}

	private function detect_date_range() {
		if ( isset( $_REQUEST['bwfanc_date_range_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_REQUEST['bwfanc_date_range_nonce'] ), 'bwfanc_date_range_nonce' ) ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput,WordPress.Security.NonceVerification
			$first_date  = trim( sanitize_text_field( $_REQUEST['date_range_first'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput,WordPress.Security.NonceVerification
			$second_date = trim( sanitize_text_field( $_REQUEST['date_range_second'] ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput,WordPress.Security.NonceVerification
			$this->prepare_dates( $first_date, $second_date, true );
		}
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function get_filter_menu() {
		$menu = [
			'7'  => [
				'name'  => '7 Days',
				'class' => 'bwfanc_default',
				'link'  => add_query_arg( [
					'page' => 'autonami',
					'tab'  => 'carts',
				], admin_url( 'admin.php' ) ),
			],
			'15' => [
				'name'  => '15 Days',
				'class' => 'bwfanc_default_15',
				'link'  => add_query_arg( [
					'page'       => 'autonami',
					'tab'        => 'carts',
					'no_of_days' => 15,
				], admin_url( 'admin.php' ) ),
			],
			'30' => [
				'name'  => '30 Days',
				'class' => 'bwfanc_default_30',
				'link'  => add_query_arg( [
					'page'       => 'autonami',
					'tab'        => 'carts',
					'no_of_days' => 30,
				], admin_url( 'admin.php' ) ),
			],
			'-1' => [
				'name'  => 'Custom',
				'class' => 'bwfanc_default_custom',
				'link'  => add_query_arg( [
					'page'       => 'autonami',
					'tab'        => 'carts',
					'no_of_days' => - 1,
				], admin_url( 'admin.php' ) ),
			],
		];

		if ( isset( $menu[ $this->no_of_days ] ) && ! $this->date_rage_search ) {
			$menu[ $this->no_of_days ]['current'] = true;
		} else {
			$menu['-1']['current'] = true;
		}

		return $menu;
	}

	public function get_captured_cart() {
		$where = "where `status` != 2 AND `last_modified` >= '{$this->start_date}' AND `last_modified` <='{$this->end_date} 23:59:59'";
		$data  = BWFAN_Model_Abandonedcarts::get_abandoned_data( $where );

		if ( ! is_null( $data ) ) {
			$sum = 0;
			foreach ( $data as $d ) {
				$sum += $d->total_base;
			}

			return [
				'data'  => $data,
				'sum'   => $sum,
				'count' => count( $data ),
			];
		}

		return [
			'data'  => [],
			'sum'   => '',
			'count' => 0,
		];
	}

	public function get_lost_cart() {
		$where        = "where `status` = 2 AND `last_modified` >= '{$this->start_date}' AND `last_modified` <='{$this->end_date} 23:59:59'";
		$results      = BWFAN_Model_Abandonedcarts::get_abandoned_data( $where );
		$default_data = $this->get_default_data();
		$found_order  = 0;
		$sum          = 0;
		$dataset      = $default_data[1];
		$revenue_set  = $default_data[1];

		foreach ( $results as $result ) {
			$timestamp    = strtotime( $result->last_modified );
			$created_time = date( 'Y-m-d', $timestamp );

			if ( ! isset( $dataset[ $created_time ] ) ) {
				$dataset[ $created_time ]     = 1;
				$revenue_set[ $created_time ] = $result->total_base;
			} else {
				$dataset[ $created_time ] ++;
				$revenue_set[ $created_time ] += $result->total_base;
			}

			$sum += $result->total_base;
			$found_order ++;
		}

		return [
			'count'   => $found_order,
			'sum'     => $sum,
			'data'    => array_values( $dataset ),
			'revenue' => array_values( $revenue_set ),
		];
	}

	private function get_default_data() {
		$dataset     = [];
		$labels      = [];
		$timestamp   = strtotime( $this->end_date );
		$no_of_loops = absint( $this->filter_date );

		for ( $i = $no_of_loops; $i >= 0; $i -- ) {
			$date             = date( 'Y-m-d', strtotime( "-$i days", $timestamp ) );
			$labels[]         = $date;
			$dataset[ $date ] = 0;
		}

		return [ $labels, $dataset ];
	}

	public function get_recovered_cart() {
		global $wpdb;

		$post_statuses = apply_filters( 'bwfan_recovered_cart_excluded_statuses', array( 'wc-pending', 'wc-failed', 'wc-cancelled', 'wc-refunded', 'trash', 'draft' ) );
		$post_status   = '(';
		foreach ( $post_statuses as $status ) {
			$post_status .= "'" . $status . "',";
		}
		$post_status .= "'')";

		$where        = "AND p.post_date >= '{$this->start_date}' AND p.post_date <='{$this->end_date} 23:59:59'";
		$results      = $wpdb->get_results( $wpdb->prepare( " SELECT p.post_date as date, m.meta_value as total FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id LEFT JOIN {$wpdb->prefix}postmeta as m2 ON p.ID = m2.post_id WHERE p.post_type = %s AND p.post_status NOT IN $post_status AND m.meta_key = %s AND m2.meta_key = %s $where ", 'shop_order', '_bwfan_order_total_base', '_bwfan_ab_cart_recovered_a_id' ) ); //phpcs:ignore WordPress.DB.PreparedSQL
		$default_data = $this->get_default_data();
		$found_order  = 0;
		$sum          = 0;
		$dataset      = $default_data[1];
		$revenue_set  = $default_data[1];

		foreach ( $results as $result ) {
			$timestamp    = strtotime( $result->date );
			$created_time = date( 'Y-m-d', $timestamp );

			if ( ! isset( $dataset[ $created_time ] ) ) {
				$dataset[ $created_time ]     = 1;
				$revenue_set[ $created_time ] = $result->total;
			} else {
				$dataset[ $created_time ] ++;
				$revenue_set[ $created_time ] += $result->total;
			}

			$revenue_set[ $created_time ] = round( $revenue_set[ $created_time ], wc_get_price_decimals() );
			$sum                          += $result->total;
			$found_order ++;
		}

		return [
			'count'   => $found_order,
			'sum'     => $sum,
			'data'    => array_values( $dataset ),
			'revenue' => array_values( $revenue_set ),
		];
	}

	public function get_recovery_rate( $total_abandoned, $total_recovered ) {
		$total_abandoned = intval( $total_abandoned );
		$total_recovered = intval( $total_recovered );

		if ( 0 === $total_recovered ) {
			return 0;
		}

		$total_abandoned += $total_recovered;

		return ( ( $total_recovered / $total_abandoned ) * 100 );
	}

	public function line_chart_data( $captured_cart ) {
		$default_data = $this->get_default_data();
		$dataset      = $default_data[1];
		$revenue_set  = $default_data[1];

		if ( count( $captured_cart['data'] ) > 0 ) {
			$data = $captured_cart['data'];

			foreach ( $data as $item ) {
				$create_time  = $item->last_modified;
				$timestamp    = strtotime( $create_time );
				$created_time = date( 'Y-m-d', $timestamp );

				if ( ! isset( $dataset[ $created_time ] ) ) {
					$dataset[ $created_time ]     = 1;
					$revenue_set[ $created_time ] = $item->total_base;
				} else {
					$dataset[ $created_time ] ++;
					$revenue_set[ $created_time ] += $item->total_base;
				}

				$revenue_set[ $created_time ] = round( $revenue_set[ $created_time ], wc_get_price_decimals() );
			}
		}

		return [
			'labels'  => array_values( $default_data[0] ),
			'data'    => array_values( $dataset ),
			'revenue' => array_values( $revenue_set ),
		];
	}

	/**
	 * Total carts - wc session count - total no of carts made
	 *
	 * @return int
	 */
	public function get_total_cart_generated() {
		$date_query    = "`date` >= '{$this->start_date}' AND `date` <= '{$this->end_date} 23:59:59'";
		$data          = WFCO_Model_Report_views::get_data( $date_query, 0, 1, true );
		$total_session = 0;

		if ( count( $data ) > 0 ) {
			foreach ( $data as $d ) {
				$total_session += $d['no_of_sessions'];
			}
		}

		return intval( $total_session );
	}

	/**
	 * Total orders placed in a particular time period
	 *
	 * @return int
	 */
	public function get_total_orders_placed() {
		global $wpdb;

		$post_statuses = apply_filters( 'bwfan_recovered_cart_excluded_statuses', array( 'wc-pending', 'wc-failed', 'wc-cancelled' ) );
		$count         = count( $post_statuses );
		$i             = 0;
		$post_status   = '(';
		foreach ( $post_statuses as $status ) {
			$i ++;
			if ( $i !== $count ) {
				$post_status .= "'" . $status . "',";
			} else {
				$post_status .= "'" . $status . "'";
			}
		}
		$post_status .= ')';

		$where  = "AND p.post_date >= '{$this->start_date}' AND p.post_date <='{$this->end_date} 23:59:59'";
		$orders = $wpdb->get_var( $wpdb->prepare( " SELECT COUNT(p.ID) FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id WHERE p.post_type = %s AND p.post_status NOT IN $post_status AND m.meta_key = %s $where ", 'shop_order', '_bwfan_order_total_base' ) ); //phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $orders ) ) {
			$orders = 0;
		}

		return intval( $orders );
	}

	public function is_date_rage_set() {

		return $this->date_rage_search;
	}

	public function get_start_date() {
		return $this->start_date;
	}

	public function get_end_date() {
		return $this->end_date;
	}

}
