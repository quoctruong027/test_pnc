<?php
/**
 * WC_PRL_Filter_Tag_Context class
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
 * WC_PRL_Filter_Tag_Context class for filtering products based on category.
 *
 * @class    WC_PRL_Filter_Tag_Context
 * @version  1.4.6
 */
class WC_PRL_Filter_Tag_Context extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                                = 'tag_context';
		$this->type                              = 'static';
		$this->title                             = __( 'Current Tag', 'woocommerce-product-recommendations' );
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
			'taxonomy' => 'product_tag',
			'field'    => 'slug',
			'terms'    => $data[ 'value' ],
			'operator' => in_array( strtolower( $data[ 'modifier' ] ), array( 'not-in', 'exclude' ) ) ? 'NOT IN' : 'IN'
		);

		return $query_args;
	}

	/**
	 * Parse the tag value from the source context. -- @see WC_PRL_Filter::get_contextual_value()
	 *
	 * @param  array  $source_data
	 * @param  string $engine_type
	 * @param  array  $filter_data
	 * @return mixed|null
	 */
	protected function parse_contextual_value( $source_data, $engine_type, $filter_data ) {

		$new_value = null;

		if ( 'product' === $engine_type ) {
			$product_id = (int) array_pop( $source_data );
			$new_value  = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'slugs' ) );
		}

		if ( 'archive' === $engine_type ) {

			$context = array_pop( $source_data );
			if ( ! is_array( $context ) || is_null( $context[ 'tag' ] ) ) {
				return null;
			}

			$term      = get_term_by( 'id', absint( $context[ 'tag' ] ), 'product_tag' );
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
		$post_name    = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$product_tags = ( array ) get_terms( 'product_tag', array( 'get' => 'all' ) );
		$modifier     = '';

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		if ( isset( $filter_data[ 'value' ] ) ) {
			$tags = is_array( $filter_data[ 'value' ] ) ? $filter_data[ 'value' ] : array( $filter_data[ 'value' ] );
		} else {
			$tags = array();
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
				<div class="select-field" data-modifiers="intersect,exclude"<?php echo in_array( $modifier, array( 'intersect', 'exclude' ) ) ? '' : ' style="display:none;"'; ?>>
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Limit current tags to&hellip;', 'woocommerce-product-recommendations' ); ?>">
						<?php
							foreach ( $product_tags as $product_tag ) {
								echo '<option value="' . $product_tag->slug . '" ' . selected( in_array( $product_tag->slug, $tags ), true, false ) . '>' . $product_tag->name . '</option>';
							}
						?>
					</select>
					<span class="os_form_row">
						<a class="os_select_all button" href="#"><?php _e( 'All', 'woocommerce' ); ?></a>
						<a class="os_select_none button" href="#"><?php _e( 'None', 'woocommerce' ); ?></a>
					</span>
				</div>
			</div>
		</div>
		<?php
	}
}
