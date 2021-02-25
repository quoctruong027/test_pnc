<?php
/**
 * WC_PRL_Condition_Cart_Category class
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
 * @class    WC_PRL_Condition_Cart_Category
 * @version  1.2.5
 */
class WC_PRL_Condition_Cart_Category extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'cart_category';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Category', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'in'         => _x( 'in cart', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'all-in'     => _x( 'all in cart', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in'     => _x( 'not in cart', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-all-in' => _x( 'not all in cart', 'prl_modifiers', 'woocommerce-product-recommendations' )
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

		// Shorthands.
		$modifier     = $data[ 'modifier' ];
		$category_ids = $data[ 'value' ];

		// Search.
		$found_items = $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$product_category_ids = array();

			$product              = $cart_item[ 'variation_id' ] ? wc_get_product( $cart_item[ 'product_id' ] ) : $cart_item[ 'data' ];
			$product_category_ids = $product instanceof WC_Product ? $product->get_category_ids() : array();

			if ( ! empty( $product_category_ids ) ) {

				$categories_matching = 0;

				foreach ( $product_category_ids as $product_category_id ) {
					if ( in_array( $product_category_id, $category_ids ) ) {
						$categories_matching++;
					}
				}

				$term_relationship = $this->get_default_term_relationship();

				if ( $this->modifier_is( $modifier, array( 'in', 'not-in' ) ) ) {

					if ( 'or' === $term_relationship && $categories_matching ) {
						$found_items = true;
					} elseif ( 'and' === $term_relationship && $categories_matching === sizeof( $data[ 'value' ] ) ) {
						$found_items = true;
					}

					if ( $found_items ) {
						break;
					}

				} elseif ( $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) ) ) {

					if ( 'or' === $term_relationship && ! $categories_matching ) {
						$found_items = false;
					} elseif ( 'and' === $term_relationship && $categories_matching !== sizeof( $data[ 'value' ] ) ) {
						$found_items = false;
					}

					if ( ! $found_items ) {
						break;
					}
				}
			}
		}

		if ( $found_items ) {
			return $this->modifier_is( $modifier, array( 'in', 'all-in' ) );
		} else {
			return $this->modifier_is( $modifier, array( 'not-in', 'not-all-in' ) );
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

		$post_name          = ! is_null( $post_name ) ? $post_name : 'prl_deploy';
		$modifier           = '';
		$categories         = array();

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

		if ( isset( $condition_data[ 'value' ] ) ) {
			$categories = is_array( $condition_data[ 'value' ] ) ? $condition_data[ 'value' ] : array( $condition_data[ 'value' ] );
		}

		if ( is_null( self::$product_categories_tree ) ) {
			$product_categories = ( array ) get_terms( 'product_cat', array( 'get' => 'all' ) );
			self::$product_categories_tree = wc_prl_build_taxonomy_tree( $product_categories );
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
				<select name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Select categories&hellip;', 'woocommerce-product-recommendations' ); ?>"><?php
					wc_prl_print_taxonomy_tree_options( self::$product_categories_tree, $categories, apply_filters( 'woocommerce_prl_condition_dropdown_options', array(), $this ) );
				?></select>
			</div>
		</div>
		<?php
	}
}
