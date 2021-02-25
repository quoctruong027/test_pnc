<?php
/**
 * WC_PRL_Condition_Recent_Product class
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
 * @class    WC_PRL_Condition_Recent_Product
 * @version  1.2.0
 */
class WC_PRL_Condition_Recent_Product extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'recent_product';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Recently viewed product', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'cart', 'product', 'order', 'archive' );
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

		if ( ! isset( $_COOKIE[ 'wc_prl_recently_viewed' ] ) ) {
			return false;
		}

		// Get products part.
		$parts = ( array ) explode( ',', wp_unslash( $_COOKIE[ 'wc_prl_recently_viewed' ] ) );
		if ( empty( $parts ) ) {
			return false;
		}

		$viewed_products = wp_parse_id_list( ( array ) explode( '|', $parts[ 0 ] ) );

		if ( empty( $viewed_products ) ) {
			return false;
		}

		$data[ 'value' ]   = array_map( 'absint', $data[ 'value' ] );
		$products_matching = 0;
		$found_items       = false;

		foreach ( $data[ 'value' ] as $product_id ) {
			if ( in_array( $product_id, $viewed_products ) ) {
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
