<?php
/**
 * WC_PRL_Condition_Cart_Total class
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
 * @class    WC_PRL_Condition_Cart_Total
 * @version  1.0.0
 */
class WC_PRL_Condition_Cart_Total extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'cart_total';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Cart total', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'min' => _x( '>=', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'max' => _x( '<', 'prl_modifiers', 'woocommerce-product-recommendations' )
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

		// Empty conditions always apply (not evaluated).
		if ( empty( $data[ 'value' ] ) && '0' != $data[ 'value' ] ) {
			return true;
		}

		$include_taxes = apply_filters( 'woocommerce_prl_cart_total_condition_incl_tax', true, $data, $deployment );

		if ( apply_filters( 'woocommerce_prl_cart_total_contents_only', true, $data, $deployment ) ) {

			$cart_contents_total = WC()->cart->get_cart_contents_total();
			$cart_contents_taxes = WC()->cart->get_cart_contents_taxes();

			$cart_contents_tax   = $include_taxes ? array_sum( $cart_contents_taxes ) : 0.0;
			$cart_total          = $cart_contents_total + $cart_contents_tax;

		} else {

			$full_cart_total     = WC()->cart->get_total( 'edit' );
			$full_cart_taxes     = WC()->cart->get_total_tax();

			$cart_total          = $include_taxes ? $full_cart_total : $full_cart_total - $full_cart_taxes;
		}

		if ( $this->modifier_is( $data[ 'modifier' ], 'min' ) && wc_format_decimal( $data[ 'value' ] ) <= $cart_total ) {
			return true;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'max' ) && wc_format_decimal( $data[ 'value' ] ) > $cart_total ) {
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
