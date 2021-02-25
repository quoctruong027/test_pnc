<?php
/**
 * WC_PRL_Condition_Order_Total class
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
 * @class    WC_PRL_Condition_Order_Total
 * @version  1.0.0
 */
class WC_PRL_Condition_Order_Total extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'order_total';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Order total', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'min' => _x( '>=', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'max' => _x( '<', 'prl_modifiers', 'woocommerce-product-recommendations' )
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

		// Empty conditions always apply (not evaluated).
		if ( empty( $data[ 'value' ] ) && '0' != $data[ 'value' ]  ) {
			return true;
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

		$include_taxes = apply_filters( 'woocommerce_prl_order_total_condition_incl_tax', true, $data, $order, $deployment );

		if ( ! apply_filters( 'woocommerce_prl_order_total_contents_only', false, $data, $order, $deployment ) ) {

			$order_total_amount = $order->get_total();
			$order_total_tax    = $order->get_total_tax();
			$order_total        = $include_taxes ? $order_total_amount : $order_total_amount - $order_total_tax;

		} else {

			$order_contents_total = $order->get_subtotal() - $order->get_discount_total();
			$order_contents_tax   = $order->get_cart_tax();
			$order_total          = $include_taxes ? $order_contents_total + $order_contents_tax : $order_contents_total;
		}

		if ( $this->modifier_is( $data[ 'modifier' ], 'min' ) && wc_format_decimal( $data[ 'value' ] ) <= $order_total ) {
			return true;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'max' ) && wc_format_decimal( $data[ 'value' ] ) > $order_total ) {
			return true;
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
				<input type="text" class="wc_input_price" name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value]" value="<?php echo $total; ?>" placeholder="<?php _e( 'Enter cart total&hellip;"', 'woocommerce-product-recommendations' ); ?>" step="any" min="0" />
				<span class="os_value--suffix"><?php echo get_woocommerce_currency_symbol() ?></span>
			</div>
		</div>
		<?php
	}
}
