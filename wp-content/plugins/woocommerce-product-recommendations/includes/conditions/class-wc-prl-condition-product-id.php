<?php
/**
 * WC_PRL_Condition_Product_ID class
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
 * @class    WC_PRL_Condition_Product_ID
 * @version  1.0.0
 */
class WC_PRL_Condition_Product_ID extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'product_id';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Product', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'product' );
		$this->needs_value            = true;
	}

	/**
	 * Check the condition to the current request.
	 *
	 * @param  array  $data
	 * @param  WC_PRL_deployment  $deployment
	 * @return bool
	 */
	public function check( $data, $deployment ) {

		if ( empty( $data[ 'value' ] ) ) {
			return true;
		}

		if ( ! is_array( $data[ 'value' ] ) ) {
			$data[ 'value' ] = array( $data[ 'value' ] );
		}

		global $product;
		$found           = false;
		$current_product = $product instanceof WC_Product ? $product->get_id() : null;
		if ( ! $current_product ) {
			return false;
		}

		$data[ 'value' ] = array_map( 'absint', $data[ 'value' ] );

		foreach ( $data[ 'value' ] as $product_id ) {
			if ( $product_id === $current_product ) {
				$found = true;
				break;
			}
		}

		if ( $found ) {
			return $this->modifier_is( $data[ 'modifier' ], 'in' );
		} else {
			return $this->modifier_is( $data[ 'modifier' ], 'not-in' );
		}
	}

	/*---------------------------------------------------*/
	/*  Force methods.                                   */
	/*---------------------------------------------------*/

	/**
	 * Get admin html for filter inputs.
	 *
	 * @param  string|null $post_name
	 * @param  int      $condition_index
	 * @param  array    $condition_data
	 * @return void
	 */
	public function get_admin_fields_html( $post_name, $condition_index, $condition_data ) {

		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_deploy';
		$products  = array();
		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		if ( isset( $condition_data[ 'value' ] ) ) {
			$products = is_array( $condition_data[ 'value' ] ) ? $condition_data[ 'value' ] : array( $condition_data[ 'value' ] );
			// Init products.
			$products = array_map( 'wc_get_product', $products );
		}

		?>
		<input type="hidden" name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][id]" value="<?php echo $this->id; ?>" />
		<div class="os_row_inner">
			<div class="os_modifier">
				<div class="sw-enhanced-select">
					<select name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][modifier]">
						<?php $this->get_modifiers_select_options( $modifier ); ?>
					</select>
				</div>
			</div>
			<div class="os_value">
				<select class="sw-select2-search--products" name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value][]" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-product-recommendations' ); ?>" data-action="woocommerce_json_search_products" multiple="multiple" data-limit="100" data-sortable="true">
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
