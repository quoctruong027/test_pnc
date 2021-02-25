<?php
/**
 * WC_PRL_Report_Events class
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
 * Events Reports class.
 *
 * @class    WC_PRL_Report_Events
 * @version  1.0.6
 */
class WC_PRL_Report_Events extends WC_PRL_Admin_Report {

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
	 * Is converted product selected.
	 *
	 * @var bool
	 */
	private $is_product_filter = false;

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

		if ( isset( $args[ 'product_id' ] ) ) {
			$this->is_product_filter = true;
		}

		$query_hash     = md5( json_encode( $args ) );
		$cached_results = get_transient( strtolower( get_class( $this ) ) );

		if ( false === $cached_results || ! isset( $cached_results[ $query_hash ] ) || wc_prl_should_update_report( 'events' )  ) {

			$this->report_data->conversions = WC_PRL()->db->tracking->query_conversions( $args );
			$this->report_data->clicks      = WC_PRL()->db->tracking->query_clicks( $args );

			// Totals.
			$this->report_data->total_clicks      = absint( array_sum( wp_list_pluck( $this->report_data->clicks, 'count' ) ) );
			$this->report_data->total_conversions = absint( count( $this->report_data->conversions ) );

			if ( ! $this->is_product_filter ) {
				$this->report_data->views       = WC_PRL()->db->tracking->query_views( $args );
				$this->report_data->total_views = absint( array_sum( wp_list_pluck( $this->report_data->views, 'count' ) ) );
			}

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

		if ( ! $this->is_product_filter ) {
			$legend[] = array(
				/* translators: %s: views */
				'title'            => sprintf(
					__( '%s unique views', 'woocommerce-product-recommendations' ),
					'<strong>' . $data->total_views . '</strong>'
				),
				'color'            => $this->chart_colours[ 'views' ],
				'highlight_series' => 0,
			);
		}

		$current_series = $this->is_product_filter ? 0 : 1;

		$legend[] = array(
			/* translators: %s: total orders */
			'title'            => sprintf(
				__( '%s unique clicks', 'woocommerce-product-recommendations' ),
				'<strong>' . $data->total_clicks . '</strong>'
			),
			'color'            => $this->chart_colours[ 'clicks' ],
			'highlight_series' => $current_series++
		);

		$legend[] = array(
			/* translators: %s: total orders */
			'title'            => sprintf(
				__( '%s conversions', 'woocommerce-product-recommendations' ),
				'<strong>' . $data->total_conversions . '</strong>'
			),
			'color'            => $this->chart_colours[ 'conversions' ],
			'highlight_series' => $current_series++
		);

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
			'views'       => '#b1d4ea',
			'clicks'      => '#3498db',
			'conversions' => '#5cc488'
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
	 * Get the main chart.
	 */
	public function get_main_chart() {
		global $wp_locale;

		// Prepare data for report
		$buckets = array(
			'clicks'      => $this->prepare( $this->report_data->clicks, 'time_span', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby ),
			'conversions' => $this->prepare( $this->report_data->conversions, 'ordered_time', false, $this->chart_interval, $this->start_date, $this->chart_groupby )
		);

		$data = array(
			'clicks'      => array_values( $buckets[ 'clicks' ] ),
			'conversions' => array_values( $buckets[ 'conversions' ] )
		);

		if ( ! $this->is_product_filter ) {
			$buckets[ 'views' ] = $this->prepare( $this->report_data->views, 'time_span', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$data[ 'views' ]    = array_values( $buckets[ 'views' ] );
		}

		$chart_data = wp_json_encode( $data );
		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">

			var main_chart;

			jQuery(function(){
				var chart_data = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( $chart_data ); ?>' ) );
				var drawGraph = function( highlight ) {
					var series = [
						<?php if ( ! $this->is_product_filter ) { ?>
							{
								label: "<?php echo esc_js( __( 'Unique Views', 'woocommerce-product-recommendations' ) ); ?>",
								data: chart_data.views,
								yaxis: 1,
								color: '<?php echo $this->chart_colours[ 'views' ]; ?>',
								points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
								lines: { show: true, lineWidth: 2, fill: false },
								shadowSize: 0,
								append_tooltip: "&nbsp;<?php esc_html_e( 'unique views', 'woocommerce-product-recommendations' );?>"
							},
						<?php } ?>
						{
							label: "<?php echo esc_js( __( 'Unique Clicks', 'woocommerce-product-recommendations' ) ); ?>",
							data: chart_data.clicks,
							yaxis: 1,
							color: '<?php echo $this->chart_colours[ 'clicks' ]; ?>',
							points: { show: true, radius: 6, lineWidth: 4, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 5, fill: false },
							shadowSize: 0,
							append_tooltip: "&nbsp;<?php esc_html_e( 'unique clicks', 'woocommerce-product-recommendations' );?>"
						},
						{
							label: "<?php echo esc_js( __( 'Conversions', 'woocommerce-product-recommendations' ) ); ?>",
							data: chart_data.conversions,
							yaxis: 1,
							color: '<?php echo $this->chart_colours[ 'conversions' ]; ?>',
							bars: { fillColor: '<?php echo $this->chart_colours[ 'conversions' ]; ?>', fill: true, show: true, lineWidth: 0, barWidth: <?php echo $this->barwidth; ?> * 0.5, align: 'center' },
							shadowSize: 0,
							enable_tooltip: true,
							append_tooltip: "&nbsp;<?php esc_html_e( 'conversions', 'woocommerce-product-recommendations' );?>"
						},
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
									minTickSize: 1,
									tickDecimals: 0,
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
