<?php
/**
 * WC_PRL_Report_Sales class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Parent Class.
if ( ! class_exists( 'WC_PRL_Admin_Report' ) ) {
	require_once( WC_PRL_ABSPATH . 'includes/admin/reports/class-wc-prl-report.php' );
}


/**
 * Sales Reports class.
 *
 * @class    WC_PRL_Report_Sales
 * @version  1.0.5
 */
class WC_PRL_Report_Sales extends WC_PRL_Admin_Report {

	/**
	 * Chart colors.
	 *
	 * @var array
	 */
	public $chart_colours = array();

	/**
	 * The report data.
	 *
	 * @var stdClass
	 */
	private $report_data;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get report data.
	 *
	 * @return stdClass
	 */
	public function get_report_data() {
		if ( empty( $this->report_data ) ) {
			$this->query_report_data();
		}
		return $this->report_data;
	}

	/**
	 * Get all data needed for this report and store in the class.
	 */
	private function query_report_data() {

		// Init container.
		$this->report_data = new stdClass();

		$args = $this->build_filters_query_args();

		$query_hash     = md5( json_encode( $args ) );
		$cached_results = get_transient( strtolower( get_class( $this ) ) );

		if ( false === $cached_results || ! isset( $cached_results[ $query_hash ] ) || wc_prl_should_update_report( 'sales' )  ) {

			$this->report_data->conversions = WC_PRL()->db->tracking->query_conversions( $args );

			// Generate numbers.
			foreach ( $this->report_data->conversions as $index => $conv ) {
				$this->report_data->conversions[ $index ][ 'total_with_tax' ] = $conv[ 'total' ] + $conv[ 'total_tax' ];
			}

			// Calculate.
			$this->report_data->total_sales = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->conversions, 'total' ) ) + array_sum( wp_list_pluck( $this->report_data->conversions, 'total_tax' ) ), 2 );
			$this->report_data->net_sales   = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->conversions, 'total' ) ), 2 );

			// Calculate average based on net
			$this->report_data->average_sales       = wc_format_decimal( $this->report_data->net_sales / ( $this->chart_interval + 1 ), 2 );
			$this->report_data->average_total_sales = wc_format_decimal( $this->report_data->total_sales / ( $this->chart_interval + 1 ), 2 );


			if ( ! is_array( $cached_results ) ) {
				$cached_results = array();
			}

			$cached_results[ $query_hash ] = $this->report_data;
			set_transient( strtolower( get_class( $this ) ), $cached_results, strtotime( 'tomorrow' ) - time() );

		} else {
			$this->report_data = $cached_results[ $query_hash ];
		}
	}

	/**
	 * Get the legend for the main chart sidebar.
	 *
	 * @return array
	 */
	public function get_chart_legend() {
		$legend = array();
		$data   = $this->get_report_data();

		switch ( $this->chart_groupby ) {
			case 'day':
				/* translators: %s: average total sales */
				$average_total_sales_title = sprintf(
					__( '%s average gross daily sales', 'woocommerce' ),
					'<strong>' . wc_price( $data->average_total_sales ) . '</strong>'
				);
				/* translators: %s: average sales */
				$average_sales_title = sprintf(
					__( '%s average net daily sales', 'woocommerce' ),
					'<strong>' . wc_price( $data->average_sales ) . '</strong>'
				);
				break;
			case 'month':
			default:
				/* translators: %s: average total sales */
				$average_total_sales_title = sprintf(
					__( '%s average gross monthly sales', 'woocommerce' ),
					'<strong>' . wc_price( $data->average_total_sales ) . '</strong>'
				);
				/* translators: %s: average sales */
				$average_sales_title = sprintf(
					__( '%s average net monthly sales', 'woocommerce' ),
					'<strong>' . wc_price( $data->average_sales ) . '</strong>'
				);
				break;
		}

		$legend[] = array(
			/* translators: %s: total sales */
			'title'            => sprintf(
				__( '%s gross sales in this period', 'woocommerce' ),
				'<strong>' . wc_price( $data->total_sales ) . '</strong>'
			),
			'placeholder'      => __( 'This is the sum of the order totals after any refunds and including shipping and taxes.', 'woocommerce' ),
			'color'            => $this->chart_colours[ 'sales_amount' ],
			'highlight_series' => 0,
		);
		if ( $data->average_total_sales > 0 ) {
			$legend[] = array(
				'title'            => $average_total_sales_title,
				'color'            => $this->chart_colours[ 'average' ],
				'highlight_series' => 1,
			);
		}

		$legend[] = array(
			/* translators: %s: net sales */
			'title'            => sprintf(
				__( '%s net sales in this period', 'woocommerce' ),
				'<strong>' . wc_price( $data->net_sales ) . '</strong>'
			),
			'placeholder'      => __( 'This is the sum of the order totals after any refunds and excluding shipping and taxes.', 'woocommerce' ),
			'color'            => $this->chart_colours[ 'net_sales_amount' ],
			'highlight_series' => 2,
		);
		if ( $data->average_sales > 0 ) {
			$legend[] = array(
				'title'            => $average_sales_title,
				'color'            => $this->chart_colours[ 'net_average' ],
				'highlight_series' => 3,
			);
		}

		return $legend;
	}

	/**
	 * Output the report.
	 */
	public function output_report() {

		$ranges = array(
			'year'       => __( 'Year', 'woocommerce' ),
			'last_month' => __( 'Last month', 'woocommerce' ),
			'month'      => __( 'This month', 'woocommerce' ),
			'7day'       => __( 'Last 7 days', 'woocommerce' ),
		);

		$this->chart_colours = array(
			'sales_amount'     => '#b1d4ea',
			'net_sales_amount' => '#3498db',
			'average'          => '#b1d4ea',
			'net_average'      => '#3498db'
		);

		$current_range = ! empty( $_GET[ 'range' ] ) ? sanitize_text_field( $_GET[ 'range' ] ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) ) {
			$current_range = '7day';
		}

		$this->check_current_range_nonce( $current_range );
		$this->calculate_current_range( $current_range );

		include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
	}

	/**
	 * Round our totals correctly.
	 *
	 * @param array|string $amount
	 *
	 * @return array|string
	 */
	private function round_chart_totals( $amount ) {
		if ( is_array( $amount ) ) {
			return array( $amount[0], wc_format_decimal( $amount[1], wc_get_price_decimals() ) );
		} else {
			return wc_format_decimal( $amount, wc_get_price_decimals() );
		}
	}

	/**
	 * Get the main chart.
	 */
	public function get_main_chart() {
		global $wp_locale;

		// Prepare data for report
		$data = array(
			'total_sales' => $this->prepare( $this->report_data->conversions, 'ordered_time', 'total_with_tax', $this->chart_interval, $this->start_date, $this->chart_groupby ),
			'net_sales'   => $this->prepare( $this->report_data->conversions, 'ordered_time', 'total', $this->chart_interval, $this->start_date, $this->chart_groupby ),
		);

		$chart_data = wp_json_encode( array(
			'total_sales' => array_map( array( $this, 'round_chart_totals' ), array_values( $data[ 'total_sales' ] ) ),
			'net_sales'   => array_map( array( $this, 'round_chart_totals' ), array_values( $data[ 'net_sales' ] ) ),
		) );
		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">

			var main_chart;

			jQuery(function(){
				var order_data = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( $chart_data ); ?>' ) );
				var drawGraph = function( highlight ) {
					var series = [
						{
							label: "<?php echo esc_js( __( 'Gross sales amount', 'woocommerce' ) ); ?>",
							data: order_data.total_sales,
							yaxis: 1,
							color: '<?php echo $this->chart_colours['sales_amount']; ?>',
							points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); ?>
						},
						{
							label: "<?php echo esc_js( __( 'Average gross sales amount', 'woocommerce' ) ); ?>",
							data: [ [ <?php echo min( array_keys( $data['total_sales'] ) ); ?>, <?php echo $this->report_data->average_total_sales; ?> ], [ <?php echo max( array_keys( $data['total_sales'] ) ); ?>, <?php echo $this->report_data->average_total_sales; ?> ] ],
							yaxis: 1,
							color: '<?php echo $this->chart_colours['average']; ?>',
							points: { show: false },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Net sales amount', 'woocommerce' ) ); ?>",
							data: order_data.net_sales,
							yaxis: 1,
							color: '<?php echo $this->chart_colours['net_sales_amount']; ?>',
							points: { show: true, radius: 6, lineWidth: 4, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 5, fill: false },
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); ?>
						},
						{
							label: "<?php echo esc_js( __( 'Average net sales amount', 'woocommerce' ) ); ?>",
							data: [ [ <?php echo min( array_keys( $data['net_sales'] ) ); ?>, <?php echo $this->report_data->average_sales; ?> ], [ <?php echo max( array_keys( $data['net_sales'] ) ); ?>, <?php echo $this->report_data->average_sales; ?> ] ],
							yaxis: 1,
							color: '<?php echo $this->chart_colours['net_average']; ?>',
							points: { show: false },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							hoverable: false
						}
					];

					if ( highlight !== 'undefined' && series[ highlight ] ) {
						highlight_series = series[ highlight ];

						highlight_series.color = '#9c5d90';

						if ( highlight_series.bars ) {
							highlight_series.bars.fillColor = '#9c5d90';
						}

						if ( highlight_series.lines ) {
							highlight_series.lines.lineWidth = 5;
						}
					}

					main_chart = jQuery.plot(
						jQuery('.chart-placeholder.main'),
						series,
						{
							legend: {
								show: false
							},
							grid: {
								color: '#aaa',
								borderColor: 'transparent',
								borderWidth: 0,
								hoverable: true
							},
							xaxes: [ {
								color: '#aaa',
								position: "bottom",
								tickColor: 'transparent',
								mode: "time",
								timeformat: "<?php echo ( 'day' === $this->chart_groupby ) ? '%d %b' : '%b'; ?>",
								monthNames: JSON.parse( decodeURIComponent( '<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->month_abbrev ) ) ); ?>' ) ),
								tickLength: 1,
								minTickSize: [1, "<?php echo $this->chart_groupby; ?>"],
								font: {
									color: "#aaa"
								}
							} ],
							yaxes: [
								{
									min: 0,
									alignTicksWithAxis: 1,
									tickDecimals: 2,
									color: '#d4d9dc',
									font: { color: "#aaa" }
								}
							],
						}
					);

					jQuery('.chart-placeholder').resize();
				}

				drawGraph();

				jQuery('.highlight_series').hover(
					function() {
						drawGraph( jQuery(this).data('series') );
					},
					function() {
						drawGraph();
					}
				);
			});
		</script>
		<?php
	}
}
