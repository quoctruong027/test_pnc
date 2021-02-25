<?php
/**
 * WC_PRL_Condition_Order_Tag class
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
 * @class    WC_PRL_Condition_Order_Tag
 * @version  1.0.0
 */
class WC_PRL_Condition_Order_Tag extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'order_tag';
		$this->complexity             = WC_PRL_Condition::MEDIUM_COMPLEXITY;
		$this->title                  = __( 'Tag', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'in'         => _x( 'in order', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'all-in'     => _x( 'all in order', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in'     => _x( 'not in order', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-all-in' => _x( 'not all in order', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'order' );
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

		global $wp;

		$order_id = 0;
		if ( is_checkout_pay_page() ) {
			$order_id = absint( $wp->query_vars[ 'order-pay' ] );
		} elseif ( is_order_received_page() ) {
			$order_id = absint( $wp->query_vars[ 'order-received' ] );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		// Shorthands.
		$modifier = $data[ 'modifier' ];
		$tag_ids  = $data[ 'value' ];

		// Search.
		$found_items = $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) );

		foreach ( $order->get_items() as $order_item_key => $order_item ) {

			$product_tag_ids = array();

			$product         = $order_item->get_product();
			$product_tag_ids = $product instanceof WC_Product ? $product->get_tag_ids() : array();

			if ( ! empty( $product_tag_ids ) ) {

				$tags_matching = 0;

				foreach ( $product_tag_ids as $product_tag_id ) {
					if ( in_array( $product_tag_id, $tag_ids ) ) {
						$tags_matching++;
					}
				}

				$term_relationship = $this->get_default_term_relationship();

				if ( $this->modifier_is( $modifier, array( 'in', 'not-in' ) ) ) {

					if ( 'or' === $term_relationship && $tags_matching ) {
						$found_items = true;
					} elseif ( 'and' === $term_relationship && $tags_matching === sizeof( $data[ 'value' ] ) ) {
						$found_items = true;
					}

					if ( $found_items ) {
						break;
					}

				} elseif ( $this->modifier_is( $modifier, array( 'all-in', 'not-all-in' ) ) ) {

					if ( 'or' === $term_relationship && ! $tags_matching ) {
						$found_items = false;
					} elseif ( 'and' === $term_relationship && $tags_matching !== sizeof( $data[ 'value' ] ) ) {
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

		$post_name    = ! is_null( $post_name ) ? $post_name : 'prl_deploy';
		$product_tags = ( array ) get_terms( 'product_tag', array( 'get' => 'all' ) );
		$modifier     = '';
		$tags         = array();

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

		if ( isset( $condition_data[ 'value' ] ) ) {
			$tags = is_array( $condition_data[ 'value' ] ) ? $condition_data[ 'value' ] : array( $condition_data[ 'value' ] );
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
				<select name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Select tags&hellip;', 'woocommerce-product-recommendations' ); ?>">
					<?php
						foreach ( $product_tags as $product_tag )
							echo '<option value="' . $product_tag->term_id . '" ' . selected( in_array( $product_tag->term_id, $tags ), true, false ) . '>' . $product_tag->name . '</option>';
					?>
				</select>
			</div>
		</div>
		<?php
	}
}
