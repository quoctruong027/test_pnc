<?php
/**
 * WC_PRL_Filter_Category class
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
 * WC_PRL_Filter_Category class for filtering products based on category.
 *
 * @class    WC_PRL_Filter_Category
 * @version  1.1.2
 */
class WC_PRL_Filter_Category extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                                = 'category';
		$this->type                              = 'static';
		$this->title                             = __( 'Category', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types            = array( 'cart', 'product', 'order', 'archive' );
		$this->needs_value                       = true;
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
			'operator' => strtolower( $data[ 'modifier' ] ) === 'in' ? 'IN' : 'NOT IN'
		);

		return $query_args;
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
		$post_name          = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$modifier           = '';
		$categories         = array();

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		if ( isset( $filter_data[ 'value' ] ) ) {
			$categories = is_array( $filter_data[ 'value' ] ) ? $filter_data[ 'value' ] : array( $filter_data[ 'value' ] );
		}

		if ( is_null( self::$product_categories_tree ) ) {
			$product_categories = ( array ) get_terms( 'product_cat', array( 'get' => 'all' ) );
			self::$product_categories_tree = wc_prl_build_taxonomy_tree( $product_categories );
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
				<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Select categories&hellip;', 'woocommerce-product-recommendations' ); ?>"><?php
					wc_prl_print_taxonomy_tree_options( self::$product_categories_tree, $categories, apply_filters( 'woocommerce_prl_filter_dropdown_options', array( 'key' => 'slug' ), $this ) );
				?></select>
			</div>
		</div>
		<?php
	}
}
