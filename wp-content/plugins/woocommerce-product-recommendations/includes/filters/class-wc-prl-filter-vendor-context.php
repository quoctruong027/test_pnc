<?php
/**
 * WC_PRL_Filter_Vendor_Context class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.4.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_PRL_Filter_Vendor_Context class for filtering products based on Vendor.
 *
 * @class    WC_PRL_Filter_Vendor_Context
 * @version  1.4.1
 */
class WC_PRL_Filter_Vendor_Context extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                                = 'wc_vendor_context';
		$this->type                              = 'static';
		$this->title                             = __( 'Current Vendor', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'in'         => _x( 'any', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in'     => _x( 'none', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'intersect'  => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'exclude'    => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types            = array( 'product' );
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
			'taxonomy' => WC_PRODUCT_VENDORS_TAXONOMY,
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
			$new_value  = wp_get_post_terms( $product_id, WC_PRODUCT_VENDORS_TAXONOMY, array( 'fields' => 'slugs' ) );
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
		$post_name       = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$product_vendors = ( array ) get_terms( WC_PRODUCT_VENDORS_TAXONOMY, array( 'get' => 'all' ) );
		$modifier        = '';

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		if ( isset( $filter_data[ 'value' ] ) ) {
			$vendors = is_array( $filter_data[ 'value' ] ) ? $filter_data[ 'value' ] : array( $filter_data[ 'value' ] );
		} else {
			$vendors = array();
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
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Limit current vendors to&hellip;', 'woocommerce-product-recommendations' ); ?>">
					<?php
						foreach ( $product_vendors as $vendor ) {
							echo '<option value="' . $vendor->slug . '" ' . selected( in_array( $vendor->slug, $vendors ), true, false ) . '>' . $vendor->name . '</option>';
						}
					?>
				</select>
				</div>
			</div>
		</div>
		<?php
	}
}
