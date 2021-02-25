<?php
/**
 * WC_PRL_Filter_Product class
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
 * WC_PRL_Filter_Product class for including specific products.
 *
 * @class    WC_PRL_Filter_Product
 * @version  1.0.0
 */
class WC_PRL_Filter_Product extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'product';
		$this->type                   = 'static';
		$this->title                  = __( 'Product', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
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

		if ( ! is_array( $data[ 'value' ] ) ) {
			$data[ 'value' ] = array( $data[ 'value' ] );
		}

		switch ( $data[ 'modifier' ] ) {
			case 'in':
				if ( ! empty( $query_args[ 'include' ] ) ) {
					$value = array_unique( array_merge( $query_args[ 'include' ], $data[ 'value' ] ) );
				} else {
					$value = $data[ 'value' ];
				}
				$query_args[ 'include' ] = $value;
				break;
			case 'not-in':
				if ( ! empty( $query_args[ 'exclude' ] ) ) {
					$value = array_unique( array_merge( $query_args[ 'exclude' ], $data[ 'value' ] ) );
				} else {
					$value = $data[ 'value' ];
				}
				$query_args[ 'exclude' ] = $value;
				break;
		}

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
		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$products  = array();
		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		// Price format.
		if ( isset( $filter_data[ 'value' ] ) ) {
			$products = is_array( $filter_data[ 'value' ] ) ? $filter_data[ 'value' ] : array( $filter_data[ 'value' ] );
			// Init products.
			$products = array_map( 'wc_get_product', $products );
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
				<select class="sw-select2-search--products" name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value][]" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-product-recommendations' ); ?>" data-action="woocommerce_json_search_products" multiple="multiple" data-limit="100" data-sortable="true">
					<?php
					foreach ( $products as $product ) {
						$product_extra = $product->get_sku() ? $product->get_sku() : '#' . $product->get_id();
						echo '<option value="' . $product->get_id() . '" selected="selected">' . $product->get_name() . ' (' . $product_extra . ')' . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<?php
	}
}
