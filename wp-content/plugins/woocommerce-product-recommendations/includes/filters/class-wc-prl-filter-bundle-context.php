<?php
/**
 * WC_PRL_Filter_Bundle_Context class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_PRL_Filter_Bundle_Context class for including specific products.
 *
 * @class    WC_PRL_Filter_Bundle_Context
 * @version  1.0.6
 */
class WC_PRL_Filter_Bundle_Context extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'bundled_by';
		$this->type                   = 'static';
		$this->title                  = __( 'Product Bundle', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'contains' => _x( 'containing current', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'product' );
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

		$bundles = wc_pb_get_bundled_product_map( $data[ 'value' ], true );

		if ( ! empty( $bundles ) ) {

			if ( ! empty( $query_args[ 'include' ] ) ) {
				$value = array_unique( array_merge( $query_args[ 'include' ], $bundles ) );
			} else {
				$value = $bundles;
			}

			$query_args[ 'include' ] = $value;

		} else {
			$query_args[ 'force_empty_set' ] = true;
		}

		return $query_args;
	}

	/**
	 * Parse the percentage price value from the source context. -- @see WC_PRL_Filter::get_contextual_value()
	 *
	 * @param  array  $source_data
	 * @param  string $engine_type
	 * @param  double $value
	 * @return mixed|null
	 */
	protected function parse_contextual_value( $source_data, $engine_type, $value ) {

		$new_value = null;

		if ( 'product' === $engine_type ) {
			$new_value = absint( array_pop( $source_data ) );
		}

		return $new_value;
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

		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$modifier  = '';

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'contains';
		}
		?>
		<input type="hidden" name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][id]" value="<?php echo $this->id; ?>" />
		<input type="hidden" name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][context]" value="yes" />
		<div class="os_row_inner">
			<div class="os_modifier">
				<div class="sw-enhanced-select">
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][modifier]">
						<?php $this->get_modifiers_select_options( $modifier ); ?>
					</select>
				</div>
			</div>
			<div class="os_value">
				<div class="os--disabled"></div>
			</div>
		</div>
		<?php
	}
}
