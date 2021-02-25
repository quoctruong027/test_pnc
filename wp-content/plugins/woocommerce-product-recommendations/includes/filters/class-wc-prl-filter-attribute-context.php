<?php
/**
 * WC_PRL_Filter_Attribute_Context class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_PRL_Filter_Attribute_Context class for filtering products based on category.
 *
 * @class    WC_PRL_Filter_Attribute_Context
 * @version  1.3.0
 */
class WC_PRL_Filter_Attribute_Context extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                                = 'attribute_context';
		$this->type                              = 'static';
		$this->title                             = __( 'Current Attribute', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types            = array( 'product' );
		$this->needs_value                       = false;
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

		if ( empty( $data[ 'value' ] ) || empty( $data[ 'attribute' ] ) ) {
			return $query_args;
		}

		if ( ! is_array( $data[ 'value' ] ) ) {
			$data[ 'value' ] = array( $data[ 'value' ] );
		}

		// Flatten attribute.
		if ( is_array( $data[ 'attribute' ] ) ) {
			$data[ 'attribute' ] = array_shift( $data[ 'attribute' ] );
		}

		if ( ! isset( $query_args[ 'prl_tax_query' ] ) || ! is_array( $query_args[ 'prl_tax_query' ] ) ) {
			$query_args[ 'prl_tax_query' ] = array();
		}

		// If not-in modifier find all terms of the given attribute.
		if ( 'not-in' === $data[ 'modifier' ] ) {
			$all_terms       = get_terms( 'pa_' . $data[ 'attribute' ], array( 'fields' => 'id=>slug' ) );
			$all_terms       = array_values( $all_terms );
			$data[ 'value' ] = array_diff( $all_terms, $data[ 'value' ] );
		}

		$query_args[ 'prl_tax_query' ][] = array(
			'taxonomy' => 'pa_' . $data[ 'attribute' ],
			'field'    => 'slug',
			'terms'    => $data[ 'value' ],
			'operator' => 'IN'
		);

		return $query_args;
	}

	/**
	 * Parse the category value from the source context. -- @see WC_PRL_Filter::get_contextual_value()
	 *
	 * @param  array  $source_data
	 * @param  string $engine_type
	 * @param  double $value
	 * @return mixed|null
	 */
	protected function parse_contextual_value( $source_data, $engine_type, $filter_data ) {

		$new_value = null;

		if ( 'product' === $engine_type ) {
			$product_id = (int) array_pop( $source_data );
			$product    = wc_get_product( $product_id );
			$attributes = $product->get_attributes();
			$terms      = array();

			// Try to find the appropriate attribute from filter's data.
			foreach ( $attributes as $attribute_slug => $attribute ) {
				if ( in_array( str_replace( 'pa_', '', $attribute_slug ), $filter_data[ 'attribute' ] ) ) {
					// If this attribute is not a taxonomy `get_terms` will return an empty set.
					$terms = $attribute->get_terms();
					break;
				}
			}

			// Flatten objects.
			if ( ! empty( $terms ) ) {
				$new_value = array();
				foreach ( $terms as $term ) {
					$new_value[] = $term->slug;
				}
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
	 * @param  int         $filter_index
	 * @param  array       $filter_data
	 * @return void
	 */
	public function get_admin_fields_html( $post_name, $filter_index, $filter_data ) {
		$post_name    = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$modifier     = '';

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		$global_attributes = wc_prl_get_attribute_taxonomies();
		if ( ! empty( $global_attributes ) ) {

			// Attribute.
			$attribute = '';
			if ( ! empty( $filter_data[ 'attribute' ] ) ) {
				$attribute = array_shift( $filter_data[ 'attribute' ] );
			}

			// Is it valid? If not select the first one in the list.
			if ( ! in_array( $attribute, array_keys( $global_attributes ) ) ) {
				$attribute = array_keys( $global_attributes )[ 0 ];
			}
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
				<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][attribute][]" class="prl_attribute_selector sw-select2" data-placeholder="<?php _e( 'Select attribute&hellip;', 'woocommerce-product-recommendations' ); ?>">
					<?php
					if ( ! empty( $global_attributes ) ) {
						foreach ( $global_attributes as $attribute_slug => $attribute_name ) {
							echo '<option value="' . $attribute_slug . '" ' . selected( $attribute_slug === $attribute, true, false ) . '>' . $attribute_name . '</option>';
						}
					}
					?>
				</select>
			</div>
		</div>
		<?php
	}
}
