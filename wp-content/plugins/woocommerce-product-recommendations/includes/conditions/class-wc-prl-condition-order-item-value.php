<?php
/**
 * WC_PRL_Condition_Order_Item_Value class
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
 * @class    WC_PRL_Condition_Order_Item_Value
 * @version  1.0.0
 */
class WC_PRL_Condition_Order_Item_Value extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'order_item_value';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Order item value', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'min_min' => _x( 'min item price >=', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'min_max' => _x( 'min item price <', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'max_min' => _x( 'max item price >=', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'max_max' => _x( 'max item price <', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'avg_min' => _x( 'average item price >=', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'avg_max' => _x( 'average item price <', 'prl_modifiers', 'woocommerce-product-recommendations' ),
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

		if ( empty( $data[ 'value' ] ) && '0' != $data[ 'value' ]  ) {
			return true;
		}

		$order_item_prices = array();
		$min               = 9999999;
		$max               = 0;
		$avg               = 0;
		$count             = 0;

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

		foreach ( $order->get_items() as $order_item_key => $order_item ) {

			$product = $order_item->get_product();
			$price   = $product instanceof WC_Product ? $product->get_price( 'edit' ) : 0;

			// Find min.
			if ( $price < $min ) {
				$min = $price;
			}
			// Find max.
			if ( $price > $max ) {
				$max = $price;
			}
			// Add to collection.
			$order_item_prices[] = $price;
			$count++;
		}

		// Set up.
		$value = wc_format_decimal( $data[ 'value' ] );
		if ( $this->modifier_is( $data[ 'modifier' ], array( 'avg_min', 'avg_max' ) ) ) {
			$avg = wc_format_decimal( array_sum( $order_item_prices ) / $count );
		}

		// Check.
		if ( $this->modifier_is( $data[ 'modifier' ], 'min_min' ) ) {
			return $min >= $value;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'min_max' ) ) {
			return $min < $value;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'max_min' ) ) {
			return $max >= $value;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'max_max' ) ) {
			return $max < $value;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'avg_min' ) ) {
			return $avg >= $value;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'avg_max' ) ) {
			return $avg < $value;
		}

		return false;
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
		$modifier  = '';
		$total     = '';

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

		if ( isset( $condition_data[ 'value' ] ) ) {
			$total = wc_format_localized_price( $condition_data[ 'value' ] );
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
				<input type="text" name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value]" value="<?php echo $total; ?>" placeholder="<?php _e( 'Enter amount&hellip;"', 'woocommerce-product-recommendations' ); ?>" step="any" min="0" />
				<span class="os_value--suffix"><?php echo get_woocommerce_currency_symbol() ?></span>
			</div>
		</div>
		<?php
	}
}
