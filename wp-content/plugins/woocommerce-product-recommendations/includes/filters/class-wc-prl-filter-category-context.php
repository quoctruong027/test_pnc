<?php
/**
 * WC_PRL_Filter_Category_Context class
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
 * WC_PRL_Filter_Category_Context class for filtering products based on category.
 *
 * @class    WC_PRL_Filter_Category_Context
 * @version  1.3.0
 */
class WC_PRL_Filter_Category_Context extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                                = 'category_context';
		$this->type                              = 'static';
		$this->title                             = __( 'Current Category', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'in'         => _x( 'any', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in'     => _x( 'none', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'intersect'  => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'exclude'    => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types            = array( 'product', 'archive' );
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

		if ( ! is_array( $data[ 'value' ] ) ) {
			$data[ 'value' ] = array( $data[ 'value' ] );
		}

		if ( ! isset( $query_args[ 'prl_tax_query' ] ) || ! is_array( $query_args[ 'prl_tax_query' ] ) ) {
			$query_args[ 'prl_tax_query' ] = array();
		}

		$query_args[ 'prl_tax_query' ][] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $data[ 'value' ],
			'operator' => in_array( strtolower( $data[ 'modifier' ] ), array( 'not-in', 'exclude' ) ) ? 'NOT IN' : 'IN'
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
			$new_value  = wp_get_post_terms( $product_id, 'product_cat', apply_filters( 'woocommerce_prl_filter_category_context_value_args', array( 'fields' => 'slugs' ) ) );
		}

		if ( 'archive' === $engine_type ) {

			$context = array_pop( $source_data );
			if ( ! is_array( $context ) || is_null( $context[ 'cat' ] ) ) {
				return null;
			}

			$term      = get_term_by( 'id', absint( $context[ 'cat' ] ), 'product_cat' );
			$new_value = $term instanceof WP_Term ? array( $term->slug ) : null;
		}

		// Intersect or diff new value with existing set if nessesary.
		if ( is_array( $filter_data[ 'value' ] ) && ! empty( $filter_data[ 'value' ] ) ) {

			if ( in_array( strtolower( $filter_data[ 'modifier' ] ), array( 'exclude', 'intersect' ) ) ) {
				$new_value = array_intersect( $new_value, $filter_data[ 'value' ] );
			}

			if ( empty( $new_value ) ) {
				$new_value = null;
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
		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$modifier  = '';

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		if ( isset( $filter_data[ 'value' ] ) ) {
			$categories = is_array( $filter_data[ 'value' ] ) ? $filter_data[ 'value' ] : array( $filter_data[ 'value' ] );
		} else {
			$categories = array();
		}

		if ( is_null( self::$product_categories_tree ) ) {
			$product_categories = ( array ) get_terms( 'product_cat', array( 'get' => 'all' ) );
			self::$product_categories_tree = wc_prl_build_taxonomy_tree( $product_categories );
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
				<div class="os--disabled" data-modifiers="in,not-in"<?php echo in_array( $modifier, array( 'in', 'not-in' ) ) ? '' : ' style="display:none;"'; ?>></div>
				<div data-modifiers="intersect,exclude"<?php echo in_array( $modifier, array( 'intersect', 'exclude' ) ) ? '' : ' style="display:none;"'; ?>>
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Limit current categories to&hellip;', 'woocommerce-product-recommendations' ); ?>"><?php
						wc_prl_print_taxonomy_tree_options( self::$product_categories_tree, $categories, apply_filters( 'woocommerce_prl_filter_dropdown_options', array( 'key' => 'slug' ), $this ) );
					?></select>
				</div>
			</div>
		</div>
		<?php
	}
}
