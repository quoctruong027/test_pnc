<?php
/**
 * WC_PRL_Filter_Attribute class
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
 * WC_PRL_Filter_Attribute class for filtering products based on category.
 *
 * @class    WC_PRL_Filter_Attribute
 * @version  1.3.0
 */
class WC_PRL_Filter_Attribute extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                                = 'attribute';
		$this->type                              = 'static';
		$this->title                             = __( 'Attribute', 'woocommerce-product-recommendations' );
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

			// Get children.
			$all_terms      = get_terms( 'pa_' . $attribute, array( 'hide_empty' => false ) );
			$selected_terms = array();

			if ( ! empty( $filter_data[ 'value' ] ) ) {
				$selected_terms = is_array( $filter_data[ 'value' ] ) ? $filter_data[ 'value' ] : array( $filter_data[ 'value' ] );
			}
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
				<div class="os_value--attribute">
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][attribute][]" class="prl_attribute_selector sw-select2" data-placeholder="<?php _e( 'Select attribute&hellip;', 'woocommerce-product-recommendations' ); ?>">
						<?php
						if ( ! empty( $global_attributes ) ) {
							foreach ( $global_attributes as $attribute_slug => $attribute_name ) {
								echo '<option value="' . $attribute_slug . '" ' . selected( $attribute_slug === $attribute, true, false ) . '>' . $attribute_name . '</option>';
							}
						}
						?>
					</select>
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Select terms&hellip;', 'woocommerce-product-recommendations' ); ?>">
						<?php
						if ( ! empty( $all_terms ) ) {
							foreach ( $all_terms as $term ) {
								echo '<option value="' . $term->slug . '" ' . selected( in_array( $term->slug, $selected_terms ), true, false ) . '>' . $term->name . '</option>';
							}
						}
						?>
					</select>
					<div class="os_value--tools">
						<a class="select_all button" href="#"><?php _e( 'All', 'woocommerce' ); ?></a>
						<a class="select_none button" href="#"><?php _e( 'None', 'woocommerce' ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
