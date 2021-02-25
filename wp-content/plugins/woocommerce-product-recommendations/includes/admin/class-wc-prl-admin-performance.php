<?php
/**
 * WC_PRL_Admin_Performance class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_PRL_Admin_Performance Class.
 * @version 1.0.5
 */
class WC_PRL_Admin_Performance {

	/**
	 * Page home URL.
	 * @const PAGE_URL
	 */
	const PAGE_URL = 'admin.php?page=prl_performance';

	/**
	 * Performance page.
	 *
	 * Handles the display of performance page.
	 */
	public static function output() {

		$range = self::calculate_current_range();

		do_action( 'woocommerce_prl_performance_start', $range );

		$cached_results = get_transient( strtolower( __CLASS__ ) );
		if ( false === $cached_results || wc_prl_should_update_report( 'performance' ) ) {

			$cached_results = array();

			$args             = array(
				'start_date' => $range[ 'start_date' ],
				'end_date'   => $range[ 'end_date' ]
			);

			$conversions      = WC_PRL()->db->tracking->query_conversions( $args );
			$views            = WC_PRL()->db->tracking->query_views( $args );
			$clicks           = WC_PRL()->db->tracking->query_clicks( $args );

			$prev_args        = array(
				'start_date' => $range[ 'prev_start_date' ],
				'end_date'   => $range[ 'prev_end_date' ]
			);
			$prev_conversions = WC_PRL()->db->tracking->query_conversions( $prev_args );
			$prev_views       = WC_PRL()->db->tracking->query_views( $prev_args );
			$prev_clicks      = WC_PRL()->db->tracking->query_clicks( $prev_args );

			// Generate numbers.
			foreach ( $conversions as $index => $conv ) {
				$conversions[ $index ][ 'total_with_tax' ] = $conv[ 'total' ] + $conv[ 'total_tax' ];
			}
			foreach ( $prev_conversions as $index => $conv ) {
				$prev_conversions[ $index ][ 'total_with_tax' ] = $conv[ 'total' ] + $conv[ 'total_tax' ];
			}

			// Init container.
			$glance_data = array();

			// GROSS R.
			$glance_data[ 'gross' ]               = array();
			$glance_data[ 'gross' ][ 'current' ]  = wc_format_decimal( array_sum( wp_list_pluck( $conversions, 'total_with_tax' ) ), 2 );
			$glance_data[ 'gross' ][ 'previous' ] = wc_format_decimal( array_sum( wp_list_pluck( $prev_conversions, 'total_with_tax' ) ), 2 );
			$glance_data[ 'gross' ][ 'data' ]     = array_values( self::prepare( $conversions, 'ordered_time', 'total_with_tax', $range[ 'chart_interval' ], $range[ 'start_date' ], 'day' ) );

			// NET R.
			$glance_data[ 'net' ]               = array();
			$glance_data[ 'net' ][ 'current' ]  = wc_format_decimal( array_sum( wp_list_pluck( $conversions, 'total' ) ), 2 );
			$glance_data[ 'net' ][ 'previous' ] = wc_format_decimal( array_sum( wp_list_pluck( $prev_conversions, 'total' ) ), 2 );
			$glance_data[ 'net' ][ 'data' ]     = array_values( self::prepare( $conversions, 'ordered_time', 'total', $range[ 'chart_interval' ], $range[ 'start_date' ], 'day' ) );

			// Views.
			$glance_data[ 'views' ]               = array();
			$glance_data[ 'views' ][ 'current' ]  = absint( array_sum( wp_list_pluck( $views, 'count' ) ), 2 );
			$glance_data[ 'views' ][ 'previous' ] = absint( array_sum( wp_list_pluck( $prev_views, 'count' ) ), 2 );
			$glance_data[ 'views' ][ 'data' ]     = array_values( self::prepare( $views, 'time_span', 'count', $range[ 'chart_interval' ], $range[ 'start_date' ], 'day' ) );

			// Clicks.
			$glance_data[ 'clicks' ]               = array();
			$glance_data[ 'clicks' ][ 'current' ]  = absint( array_sum( wp_list_pluck( $clicks, 'count' ) ) );
			$glance_data[ 'clicks' ][ 'previous' ] = absint( array_sum( wp_list_pluck( $prev_clicks, 'count' ) ) );
			$glance_data[ 'clicks' ][ 'data' ]     = array_values( self::prepare( $clicks, 'time_span', 'count', $range[ 'chart_interval' ], $range[ 'start_date' ], 'day' ) );

			// Conversions.
			$glance_data[ 'conversions' ]               = array();
			$glance_data[ 'conversions' ][ 'current' ]  = count( $conversions );
			$glance_data[ 'conversions' ][ 'previous' ] = count( $prev_conversions );
			$glance_data[ 'conversions' ][ 'data' ]     = array_values( self::prepare( $conversions, 'ordered_time', false, $range[ 'chart_interval' ], $range[ 'start_date' ], 'day' ) );

			// Clicks per view.
			$glance_data[ 'clicks_per_view' ]               = array();
			$glance_data[ 'clicks_per_view' ][ 'current' ]  = wc_format_decimal( $glance_data[ 'views' ][ 'current' ] > 0 ? $glance_data[ 'clicks' ][ 'current' ] / $glance_data[ 'views' ][ 'current' ]: 0, 2 );
			$glance_data[ 'clicks_per_view' ][ 'previous' ] = wc_format_decimal( $glance_data[ 'views' ][ 'previous' ] > 0 ? $glance_data[ 'clicks' ][ 'previous' ] / $glance_data[ 'views' ][ 'previous' ]: 0, 2 );
			$glance_data[ 'clicks_per_view' ][ 'data' ]     = array_map( array( __CLASS__, 'calc_clicks_per_view' ), array_values( $glance_data[ 'clicks' ][ 'data' ] ), array_values( $glance_data[ 'views' ][ 'data' ] ) );

			// CR.
			$glance_data[ 'cr' ]               = array();
			$glance_data[ 'cr' ][ 'current' ]  = wc_format_decimal( $glance_data[ 'clicks' ][ 'current' ] > 0 ? $glance_data[ 'conversions' ][ 'current' ] / $glance_data[ 'clicks' ][ 'current' ] * 100: 0, 0 );
			$glance_data[ 'cr' ][ 'previous' ] = wc_format_decimal( $glance_data[ 'clicks' ][ 'previous' ] > 0 ? $glance_data[ 'conversions' ][ 'previous' ] / $glance_data[ 'clicks' ][ 'previous' ] * 100: 0, 0 );
			$glance_data[ 'cr' ][ 'data' ]     = array_map( array( __CLASS__, 'calc_conversion_rate' ), array_values( $glance_data[ 'conversions' ][ 'data' ] ), array_values( $glance_data[ 'clicks' ][ 'data' ] ) );

			// Cache results.
			$cached_results[ 'glance_data' ] = $glance_data;

			// Top products data.
			$top_products_args = array(
				'start_date' => $range[ 'start_date' ],
				'end_date'   => $range[ 'end_date' ],
				'group'      => 'products'
			);
			$top_products                      = array();
			$top_products[ 'top_grossing' ]    = WC_PRL()->db->tracking->get_top( array_merge( $top_products_args, array( 'type' => 'revenue' ) ) );
			$top_products[ 'most_clicked' ]    = WC_PRL()->db->tracking->get_top( array_merge( $top_products_args, array( 'type' => 'clicks' ) ) );
			$top_products[ 'best_converting' ] = WC_PRL()->db->tracking->get_top_convertion_rates( $top_products_args );

			// Cache.
			$cached_results[ 'top_products' ]  = $top_products;

			// Top locations data.
			$top_locations_args = array(
				'start_date' => $range[ 'start_date' ],
				'end_date'   => $range[ 'end_date' ],
				'group'      => 'locations'
			);
			$top_locations                      = array();
			$top_locations[ 'top_grossing' ]    = WC_PRL()->db->tracking->get_top( array_merge( $top_locations_args, array( 'type' => 'revenue' ) ) );
			$top_locations[ 'most_clicked' ]    = WC_PRL()->db->tracking->get_top( array_merge( $top_locations_args, array( 'type' => 'clicks' ) ) );
			$top_locations[ 'best_converting' ] = WC_PRL()->db->tracking->get_top_convertion_rates( $top_locations_args );

			// Cache.
			$cached_results[ 'top_locations' ]  = $top_locations;

			// Save transient until the end of the day.
			set_transient( strtolower( __CLASS__ ), $cached_results, strtotime( 'tomorrow' ) - time() );

		} else {
			$glance_data   = $cached_results[ 'glance_data' ];
			$top_products  = $cached_results[ 'top_products' ];
			$top_locations = $cached_results[ 'top_locations' ];
		}

		include dirname( __FILE__ ) . '/views/html-admin-performance.php';
	}

	/**
	 * Get the current range and calculate the start and end dates.
	 *
	 */
	public static function calculate_current_range() {

		// Current & Previous period.
		$end_date        = strtotime( '+1 day 00:00:00', current_time( 'timestamp' ) );
		$start_date      = strtotime( '-7 days', $end_date );
		$prev_end_date   = $start_date;
		$prev_start_date = strtotime( '-14 days', $end_date );

		return array(
			'prev_start_date' => $prev_start_date,
			'prev_end_date'   => $prev_end_date,
			'start_date'      => $start_date,
			'end_date'        => $end_date,
			'chart_interval'  => absint( ceil( max( 0, ( $end_date - $start_date ) / ( 60 * 60 * 24 ) ) ) )
		);
	}

	/**
	 * Prepares data for the report. Bucketing into time periods.
	 */
	public static function prepare( $data, $date_key, $data_key, $interval, $start_date, $group_by ) {

		$prepared_data = array();

		// Ensure all days (or months) have values in this range.
		if ( 'day' === $group_by ) {
			for ( $i = 0; $i < $interval; $i ++ ) {
				$time = strtotime( date( 'Ymd', strtotime( "+{$i} DAY", $start_date ) ) ) . '000';

				if ( ! isset( $prepared_data[ $time ] ) ) {
					$prepared_data[ $time ] = array( esc_js( $time ), 0 );
				}
			}
		} else {
			$current_yearnum  = date( 'Y', $start_date );
			$current_monthnum = date( 'm', $start_date );

			for ( $i = 0; $i < $interval; $i ++ ) {
				$time = strtotime( $current_yearnum . str_pad( $current_monthnum, 2, '0', STR_PAD_LEFT ) . '01' ) . '000';

				if ( ! isset( $prepared_data[ $time ] ) ) {
					$prepared_data[ $time ] = array( esc_js( $time ), 0 );
				}

				$current_monthnum ++;

				if ( $current_monthnum > 12 ) {
					$current_monthnum = 1;
					$current_yearnum  ++;
				}
			}
		}

		foreach ( $data as $d ) {
			switch ( $group_by ) {
				case 'day':
					$time = strtotime( date( 'Ymd', $d[ $date_key ] ) ) . '000';
					break;
				case 'month':
				default:
					$time = strtotime( date( 'Ym', $d[ $date_key ] ) . '01' ) . '000';
					break;
			}

			if ( ! isset( $prepared_data[ $time ] ) ) {
				continue;
			}

			if ( $data_key ) {
				$prepared_data[ $time ][ 1 ] += $d[ $data_key ];
			} else {
				$prepared_data[ $time ][ 1 ] ++;
			}
		}

		return $prepared_data;
	}

	/**
	 * Calculate clicks per view.
	 *
	 * @param array $clicks
	 * @param array $views
	 *
	 * @return array
	 */
	private static function calc_clicks_per_view( $clicks, $views ) {

		$clicks_per_view      = array();
		$clicks_per_view[ 0 ] = $clicks[ 0 ];

		if ( 0 == $views[ 1 ] ) {
			$clicks_per_view[ 1 ] = wc_format_decimal( 0, 2 );
		} else {
			$clicks_per_view[ 1 ] = wc_format_decimal( $clicks[ 1 ] / $views[ 1 ], 2 );
		}

		return $clicks_per_view;
	}

	/**
	 * Calculate conversion rate.
	 *
	 * @param array $conversions
	 * @param array $clicks
	 *
	 * @return array
	 */
	private static function calc_conversion_rate( $conversions, $clicks ) {

		$rate      = array();
		$rate[ 0 ] = $clicks[ 0 ];

		if ( 0 == $clicks[ 1 ] ) {
			$rate[ 1 ] = wc_format_decimal( 0, 2 );
		} else {
			$rate[ 1 ] = wc_format_decimal( ( $conversions[ 1 ] / $clicks[ 1 ] ) * 100, 2 );
		}

		return $rate;
	}

	/**
	 * Prints the diff between 2 values in HTML format.
	 *
	 * @param float $current
	 * @param float $previous
	 *
	 * @return string
	 */
	private static function print_difference( $current, $previous ) {
		if ( $previous > 0 ) {
			$difference = ( (float) $current - (float) $previous ) / abs( (float) $previous ) * 100;
		} else {
			// $difference = (float) $current * 100;
			$difference = __( 'N/A', 'woocommerce-product-recommendations' );
		}
		$class = $difference >= 0 ? 'up' : 'down';
		$class = $difference == 0 ? '' : $class;
		?>
		<span class="difference <?php echo $class ?>">
			<?php echo is_numeric( $difference ) ? wc_format_decimal( abs( $difference ), 0 ) . '%' : $difference; ?>
		</span>
		<?php
	}

	/**
	 * Get a product's object.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	private static function get_product( $product_id ) {

		// Local cache product instances.
		static $products_map;

		if ( empty( $products_map ) ) {
			$products_map = array();
		}

		if ( ! isset( $products_map[ $product_id ] ) ) {
			$products_map[ $product_id ] = wc_get_product( $product_id );
		}

		return $products_map[ $product_id ];
	}

	/**
	 * Get a locations's object by hash.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	private static function get_location_by_hash( $hash ) {

		// Local cache locations.
		static $locations = array();

		if ( empty( $locations ) ) {
			foreach ( WC_PRL()->locations->get_locations() as $location ) {
				foreach ( $location->get_hooks() as $hook => $data ) {
					$key = substr( md5( $hook ), 0, 7 );
					$locations[ $key ] = array(
						'title' => $location->get_title(),
						'hook'  => $hook,
						'id'    => $location->get_location_id(),
						'label' => $data[ 'label' ]
					);
				}
			}
		}

		return isset( $locations[ $hash ] ) ? $locations[ $hash ] : false;
	}
}
