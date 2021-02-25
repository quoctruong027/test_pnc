<?php
/**
 * WC_PRL_Condition_Cart_Product class
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
 * @class    WC_PRL_Condition_Cart_Product
 * @version  1.0.0
 */
class WC_PRL_Condition_Cart_Product extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'cart_product';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Product', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'in'     => _x( 'in cart', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in cart', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'cart' );
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

		// Search.
		$product_ids = array();
		$found_items = false;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_ids[] = $cart_item[ 'product_id' ];
		}

		$data[ 'value' ]   = array_map( 'absint', $data[ 'value' ] );
		$products_matching = 0;

		foreach ( $data[ 'value' ] as $product_id ) {
			if ( in_array( $product_id, $product_ids ) ) {
				$products_matching++;
			}
		}

		$term_relationship = $this->get_default_term_relationship();

		if ( 'or' === $term_relationship && $products_matching ) {
			$found_items = true;
		} elseif ( 'and' === $term_relationship && $products_matching === sizeof( $data[ 'value' ] ) ) {
			$found_items = true;
		}

		if ( $found_items ) {
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
