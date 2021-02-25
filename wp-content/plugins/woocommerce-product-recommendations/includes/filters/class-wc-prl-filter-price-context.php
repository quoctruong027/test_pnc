<?php
/**
 * WC_PRL_Filter_Price_Context class
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
 * WC_PRL_Filter_Price_Context class for filtering products based on their price.
 *
 * @class    WC_PRL_Filter_Price_Context
 * @version  1.3.5
 */
class WC_PRL_Filter_Price_Context extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                                = 'price_context';
		$this->type                              = 'static';
		$this->title                             = __( 'Relative Price', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'min' => _x( '>=', 'prl_modifiers', 'woocommerce-product-recommendations' ), // <=
			'max' => _x( '<', 'prl_modifiers', 'woocommerce-product-recommendations' ) // >
		);
		$this->supported_engine_types            = array( 'product' );
		$this->needs_value                       = true;
	}

	/**
	 * Apply the filter to the query args array. -- @see WC_PRL_Filter::run()
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

		if ( ! isset( $query_args[ 'prl_meta_query' ] ) || ! is_array( $query_args[ 'prl_meta_query' ] ) ) {
			$query_args[ 'prl_meta_query' ] = array();
		}

		$query_args[ 'prl_meta_query' ][] = array(
			'key'     => '_price',
			'value'   => floatval( $data[ 'value' ] ),
			'compare' => strtolower( $data[ 'modifier' ] ) == 'min' ? '>=' : '<',
			'type'    => 'NUMERIC'
		);

		return $query_args;
	}

	/**
	 * Parse the percentage price value from the source context. -- @see WC_PRL_Filter::get_contextual_value()
	 *
	 * @param  array  $source_data
	 * @param  string $engine_type
	 * @param  array  $data
	 * @return mixed|null
	 */
	protected function parse_contextual_value( $source_data, $engine_type, $data ) {

		$new_value = null;

		if ( 'product' === $engine_type ) {

			// Consider the value as a percentage of the current product.
			$product = wc_get_product( absint( array_pop( $source_data ) ) );

			if ( $product && $product instanceof WC_Product ) {
				$value     = absint( $data[ 'value' ] );
				$new_value = floatval( $product->get_price( 'edit' ) );
				$new_value = $new_value * ( $value / 100 );
			}
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
		$post_name  = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$modifier   = '';
		$percentage = '';

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

		// Price format.
		if ( isset( $filter_data[ 'value' ] ) ) {
			$percentage = $filter_data[ 'value' ];
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
				<input type="number" class="wc_input_price" name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value]" value="<?php echo $percentage; ?>" placeholder="<?php _e( 'Enter a value&hellip;"', 'woocommerce-product-recommendations' ); ?>" step="1" min="0" />
				<span class="os_value--suffix"><?php _e( '% of current product', 'woocommerce-product-recommendations' ); ?></span>
			</div>
		</div>
		<?php
	}
}
