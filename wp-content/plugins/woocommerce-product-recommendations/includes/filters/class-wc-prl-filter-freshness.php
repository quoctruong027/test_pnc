<?php
/**
 * WC_PRL_Filter_Freshness class
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
 * WC_PRL_Filter_Freshness class for filtering products based on creation date.
 *
 * @class    WC_PRL_Filter_Freshness
 * @version  1.0.0
 */
class WC_PRL_Filter_Freshness extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'freshness';
		$this->type                   = 'static';
		$this->title                  = __( 'Created', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'cart', 'product', 'order', 'archive' );
		$this->needs_value            = true;

	}

	/**
	 * Apply the filter to the query args array.
	 *
	 * @param  array $query_args
	 * @param  WC_PRL_Deployment $deployment
	 * @param  array $data
	 * @return array
	 */
	public function filter( $query_args, $deployment, $data ) {

		if ( empty( $data[ 'value' ] ) ) {
			return $query_args;
		}

		$time_spans = $this->get_filter_values();

		if ( ! in_array( $data[ 'value' ], array_keys( $time_spans ) ) ) {
			return $query_args;
		}

		$now   = date( 'Y-m-d' );
		$start = date( 'Y-m-d', strtotime( $time_spans[ $data[ 'value' ] ][ 'time_span' ] ) );

		$query_args[ 'date_created' ] = $start . '...' . $now;

		return $query_args;
	}

	/**
	 * Get all time-spans available for this filter.
	 *
	 * @return array
	 */
	public function get_filter_values() {

		$time_spans = array(
			'day' => array(
				'label'      => 'last 24 hours',
				'time_span'  => '-1 day'
			),
			'week' => array(
				'label'      => 'last 7 days',
				'time_span'  => '-7 day'
			),
			'month' => array(
				'label'      => 'last 30 days',
				'time_span'  => '-1 month'
			),
			'quarter' => array(
				'label'      => 'last 3 months',
				'time_span'  => '-3 month'
			)
		);

		/**
		 * 'woocommerce_prl_freshness_filter_values' filter.
		 *
		 * Modify Freshness time spans.
		 *
		 * @param  array  $time_spans
		 */
		return apply_filters( 'woocommerce_prl_freshness_filter_values', $time_spans );
	}

	/*---------------------------------------------------*/
	/*  Force methods.                                   */
	/*---------------------------------------------------*/

	/**
	 * Get admin html for filter inputs.
	 *
	 * @param  string|null $post_name
	 * @param  int      $filter_index
	 * @param  array    $filter_data
	 * @return void
	 */
	public function get_admin_fields_html( $post_name, $filter_index, $filter_data ) {
		$post_name  = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$options    = $this->get_filter_values();
		$modifier   = '';
		$selected   = '';

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		if ( isset( $filter_data[ 'value' ] ) ) {
			$selected = $filter_data[ 'value' ];
		}

		?>
		<input type="hidden" name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][id]" value="<?php echo $this->id; ?>" />
		<div class="os_row_inner">
			<div class="os_modifier">
				<div class="sw-enhanced-select">
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][modifier]">
						<?php $this->get_modifiers_select_options( $modifier ); ?>
					</select>
				</div>
			</div>
			<div class="os_value">
				<div class="sw-enhanced-select">
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value]">
						<?php
						foreach ( $options as $option_value => $option_data ) {
							echo '<option value="' . $option_value . '" ' . selected( $option_value === $selected, true, false ) . '>' . $option_data[ 'label' ] . '</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<?php
	}
}
