<?php
/**
 * WC_PRL_Admin_Report class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Report Class for PRL.
 *
 * @class    WC_PRL_Admin_Report
 * @version  1.0.0
 */
class WC_PRL_Admin_Report extends WC_Admin_Report {

	/**
	 * Deployments ids.
	 *
	 * @var array
	 */
	public $show_deployments = array();

	/**
	 * Locations ids.
	 *
	 * @var array
	 */
	public $show_locations = array();

	/**
	 * Products ids.
	 *
	 * @var array
	 */
	public $show_products = array();

	/**
	 * Deployments cache.
	 *
	 * @var array
	 */
	public $deployments_per_location = array();

	/**
	 * Constructor.
	 */
	public function __construct() {

		if ( isset( $_GET[ 'show_deployments' ] ) && isset( $_GET[ 'show_deployments' ][ 0 ] ) && $_GET[ 'show_deployments' ][ 0 ]  ) {
			$this->show_deployments = is_array( $_GET[ 'show_deployments' ] ) ? array_map( 'absint', $_GET[ 'show_deployments' ] ) : array( absint( $_GET[ 'show_deployments' ] ) );
		}

		if ( isset( $_GET[ 'show_locations' ] ) && isset( $_GET[ 'show_locations' ][ 0 ] ) && $_GET[ 'show_locations' ][ 0 ] ) {
			$this->show_locations = is_array( $_GET[ 'show_locations' ] ) ? array_map( 'wc_clean', $_GET[ 'show_locations' ] ) : array( wc_clean( $_GET[ 'show_locations' ] ) );
		}

		if ( isset( $_GET[ 'show_products' ] ) ) {
			$this->show_products = is_array( $_GET[ 'show_products' ] ) ? array_map( 'absint', $_GET[ 'show_products' ] ) : array( absint( $_GET[ 'show_products' ] ) );
		}

		// Load deployments.
		$this->load_deployments();
	}

	/**
	 * Output an export link.
	 */
	public function get_export_button() {
		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';
		?>
		<a
			href="#"
			download="report-prl-<?php echo esc_attr( $current_range ); ?>-<?php echo date_i18n( 'Y-m-d', current_time( 'timestamp' ) ); ?>.csv"
			class="export_csv"
			data-export="chart"
			data-xaxes="<?php esc_attr_e( 'Date', 'woocommerce' ); ?>"
			data-exclude_series="2"
			data-groupby="<?php echo $this->chart_groupby; ?>"
		>
			<?php _e( 'Export CSV', 'woocommerce' ); ?>
		</a>
		<?php
	}

	/**
	 * Builds query args based on current filters.
	 *
	 * @return array
	 */
	protected function build_filters_query_args() {

		// Query.
		$args = array(
			'start_date' => $this->start_date,
			'end_date'   => strtotime( '+1 day', $this->end_date )
		);

		// Check for filters.
		if ( ! empty( $this->show_locations ) ) {
			$location_hashes = array();
			foreach ( $this->show_locations as $hook ) {
				$location_hashes[] = substr( md5( $hook ), 0, 7 );
			}
			$args[ 'location_hash' ] = $location_hashes;
		}
		if ( ! empty( $this->show_deployments ) ) {
			$deployments_filter = array();
			foreach ( $this->show_deployments as $id ) {
				$deployments_filter[] = absint( $id );
			}
			$args[ 'deployment_id' ] = $deployments_filter;
		}
		if ( ! empty( $this->show_products ) ) {
			$products_filter = array();
			foreach ( $this->show_products as $product_id ) {
				$products_filter[] = absint( $product_id );
			}
			$args[ 'product_id' ] = $products_filter;
		}

		return $args;
	}

	/**
	 * Loads deployment data.
	 *
	 * @return void
	 */
	private function load_deployments() {
		$all_deployments                = WC_PRL()->db->deployment->query( array() );
		$this->deployments_per_location = array();

		foreach ( $all_deployments as $data ) {

			if ( ! isset( $this->deployments_per_location[ $data[ 'hook' ] ] ) ) {
				$this->deployments_per_location[ $data[ 'hook' ] ] = array();
			}

			$this->deployments_per_location[ $data[ 'hook' ] ][] = array(
				'id' => $data[ 'id' ],
				'title' => $data[ 'title' ] ? $data[ 'title' ] : __( '(no title)', 'woocommerce-product-recommendations' )
			);
		}
	}

	/**
	 * Returns current deployments data in id=>title format.
	 *
	 * @return array
	 */
	public function fetch_current_deployment_data() {
		$deployments = array();

		if ( ! empty( $this->show_locations ) ) {
			foreach ( $this->show_locations as $hook ) {
				if ( isset( $this->deployments_per_location[ $hook ] ) ) {
					foreach ( $this->deployments_per_location[ $hook ] as $data ) {
						$deployments[ $data[ 'id' ] ] = $data[ 'title' ];
					}
				}
			}
		}

		return $deployments;
	}

	/**
	 * Get chart widgets.
	 *
	 * @return array
	 */
	public function get_chart_widgets() {

		$widgets = array();

		if ( ! empty( $this->show_products ) || ! empty( $this->show_locations ) || ! empty( $this->show_deployments ) ) {
			$widgets[] = array(
				'title'    => __( 'Showing reports for:', 'woocommerce' ),
				'callback' => array( $this, 'current_filters' ),
			);
		}

		$widgets[] = array(
			'title'    => '',
			'callback' => array( $this, 'filters_widget' ),
		);

		return $widgets;
	}

	/**
	 * Output filters widget.
	 */
	public function current_filters() {

		if ( ! empty( $this->show_locations ) ) {
			$hooks = array();

			foreach ( $this->show_locations as $hook ) {

				$location = WC_PRL()->locations->get_location_by_hook( $hook );

				if ( $location ) {
					$data = $location->get_hook_data();
					$hooks[] = $location->get_title() . ' - ' . $data[ 'label' ];
				}

			}

			echo '<p>' . esc_html__( 'Location', 'woocommerce-product-recommendations' ) . ': <strong>' . wp_kses_post( implode( ', ', $hooks ) ) . '</strong></p>';
		}

		if ( ! empty( $this->show_deployments ) ) {

			$titles      = array();
			$deployments = $this->fetch_current_deployment_data();

			foreach ( $deployments as $id => $title ) {
				if ( in_array( $id, $this->show_deployments ) ) {
					$titles[] = $title . ' (#' . $id . ')';
				}
			}

			echo '<p>' . esc_html__( 'Engine Deployments', 'woocommerce-product-recommendations' ) . ': <strong>' . wp_kses_post( implode( ', ', $titles ) ) . '</strong></p>';
		}

		if ( ! empty( $this->show_products ) ) {

			$product_ids_titles = array();

			foreach ( $this->show_products as $product_id ) {
				$product = wc_get_product( $product_id );

				if ( $product ) {
					$product_ids_titles[] = $product->get_formatted_name();
				} else {
					$product_ids_titles[] = '#' . $product_id;
				}
			}

			echo '<p>' . esc_html__( 'Products', 'woocommerce-product-recommendations' ) . ': <strong>' . wp_kses_post( implode( ', ', $product_ids_titles ) ) . '</strong></p>';
		}

		// Reset URL.
		$reset_url = esc_url( remove_query_arg( array( 'show_products', 'show_locations', 'show_deployments' ) ) );
		echo '<p><a class="button" href="' . $reset_url . '">' . esc_html__( 'Reset', 'woocommerce' ) . '</a></p>';

	}

	/**
	 * Output filters widget.
	 */
	public function filters_widget() {

		$current_report = isset( $_GET['report'] ) ? sanitize_title( $_GET['report'] ) : current( array_keys( WC_PRL_Admin_Reports::$reports[ 'reports' ] ) );

		?>
		<h4><span><?php esc_html_e( 'Filters', 'woocommerce-product-recommendations' ); ?></span></h4>
		<div class="section sw-select2-autoinit">
			<form method="GET">
				<div style="margin-bottom: 10px">
					<span style="color: #555;display: block;padding: 5px 0;font-size: 12px;"><?php esc_html_e( 'Location', 'woocommerce-product-recommendations' ); ?></span>
					<select name="show_locations[]" class="prl-show-locations sw-select2" style="width: 100%" data-placeholder="<?php _e( 'Select Location&hellip;', 'woocommerce-product-recommendations' ); ?>" data-allow_clear="true">
						<option value=""></option>
						<?php
						foreach ( WC_PRL()->locations->get_locations() as $location ) {
							echo '<optgroup label="' . $location->get_title() . '">';
								foreach ( $location->get_hooks() as $hook => $data ) {
									$selected = in_array( $hook, $this->show_locations ) ? ' selected="selected"' : '';
									echo '<option value="' . $hook . '"' . $selected . '>' . $data[ 'label' ] . '</option>';
								}
							echo '</optgroup>';
						}
						?>
					</select>
				</div>
				<?php
				$deployment_options = '';
				if ( ! empty( $this->show_locations ) ) {
					foreach ( $this->show_locations as $hook ) {
						if ( isset( $this->deployments_per_location[ $hook ] ) ) {
							foreach ( $this->deployments_per_location[ $hook ] as $data ) {
								$selected = in_array( $data[ 'id' ], $this->show_deployments ) ? ' selected="selected"' : '';
								$deployment_options .= '<option value="' . $data[ 'id' ] . '"' . $selected . '>' . $data[ 'title' ] . ' (#' . $data[ 'id' ] . ')</option>';
							}
						}
					}
				}
				?>
				<div style="margin-bottom: 10px;<?php echo ! strlen( $deployment_options ) ? 'display:none;' : ''; ?>">
					<span style="color: #555;display: block;padding: 5px 0;font-size: 12px;"><?php esc_html_e( 'Engine Deployments', 'woocommerce-product-recommendations' ); ?></span>
					<select name="show_deployments[]" class="prl-show-deployments sw-select2" style="width: 100%;" data-placeholder="<?php _e( 'Select deployments&hellip;', 'woocommerce-product-recommendations' ); ?>" data-allow_clear="true">
						<option value=""></option>
						<?php echo $deployment_options; ?>
					</select>
				</div>
				<div>
					<span style="color: #555;display: block;padding: 5px 0;font-size: 12px;"><?php esc_html_e( 'Products', 'woocommerce-product-recommendations' ); ?></span>
					<select name="show_products[]" multiple="multiple" class="sw-select2-search--products" style="width: 100%" data-placeholder="<?php _e( 'Search for products&hellip;', 'woocommerce-product-recommendations' ); ?>" data-limit="20">
						<?php
						if ( ! empty( $this->show_products ) ) {
							$products = array_map( 'wc_get_product', $this->show_products );
							foreach ( $products as $product ) {
								$product_extra = $product->get_sku() ? $product->get_sku() : '#' . $product->get_id();
								echo '<option value="' . $product->get_id() . '" selected="selected">' . $product->get_name() . ' (' . $product_extra . ')' . '</option>';
							}
						}
						?>
					</select>

					<button type="submit" class="submit button" value="<?php esc_attr_e( 'Show', 'woocommerce' ); ?>"><?php esc_html_e( 'Show', 'woocommerce' ); ?></button>
					<input type="hidden" name="range" value="<?php echo ( ! empty( $_GET[ 'range' ] ) ) ? esc_attr( wp_unslash( $_GET[ 'range' ] ) ) : ''; ?>" />
					<input type="hidden" name="start_date" value="<?php echo ( ! empty( $_GET[ 'start_date' ] ) ) ? esc_attr( wp_unslash( $_GET[ 'start_date' ] ) ) : ''; ?>" />
					<input type="hidden" name="end_date" value="<?php echo ( ! empty( $_GET[ 'end_date' ] ) ) ? esc_attr( wp_unslash( $_GET[ 'end_date' ] ) ) : ''; ?>" />
					<input type="hidden" name="page" value="<?php echo ( ! empty( $_GET[ 'page' ] ) ) ? esc_attr( wp_unslash( $_GET[ 'page' ] ) ) : ''; ?>" />
					<input type="hidden" name="tab" value="<?php echo ( ! empty( $_GET[ 'tab' ] ) ) ? esc_attr( wp_unslash( $_GET[ 'tab' ] ) ) : ''; ?>" />
					<input type="hidden" name="report" value="<?php echo esc_attr( $current_report ); ?>" />
				</div>

			</form>
		</div>
		<?php
		$deployments_select_data = wp_json_encode( $this->deployments_per_location );
		?>
		<script type="text/javascript">

			var deployments                   = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( $deployments_select_data ); ?>' ) ),
				$locations_select             = jQuery( '.prl-show-locations' ),
				$deployments_select           = jQuery( '.prl-show-deployments' ),
				$deployments_select_container = $deployments_select.parent();

			$locations_select.on( 'change', function() {
				var selected = $locations_select.val(),
					options  = deployments[ selected ],
					$options = '<option value=""></option>',
					i;

				if ( typeof options === 'object' && options.length ) {

					$deployments_select_container.show();

					for ( i in options ) {
						$options += '<option value="' + options[i].id + '">' + options[i].title + ' (#' + options[i].id +')</option>';
					}

					$deployments_select.html( $options );
					$deployments_select.trigger( 'change' );

				} else {
					$deployments_select_container.hide();
				}
			} );
		</script>
		<?php
	}

	/**
	 * Prepares data for the report. Bucketing into time periods.
	 * Hints: This function is re-written because we use unix timestamps in the database.
	 */
	public function prepare( $data, $date_key, $data_key, $interval, $start_date, $group_by ) {

		$prepared_data = array();

		// Ensure all days (or months) have values in this range.
		if ( 'day' === $group_by ) {
			for ( $i = 0; $i <= $interval; $i ++ ) {
				$time = strtotime( date( 'Ymd', strtotime( "+{$i} DAY", $start_date ) ) ) . '000';

				if ( ! isset( $prepared_data[ $time ] ) ) {
					$prepared_data[ $time ] = array( esc_js( $time ), 0 );
				}
			}
		} else {
			$current_yearnum  = date( 'Y', $start_date );
			$current_monthnum = date( 'm', $start_date );

			for ( $i = 0; $i <= $interval; $i ++ ) {
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
}
