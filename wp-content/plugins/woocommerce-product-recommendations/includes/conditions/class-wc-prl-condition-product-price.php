<?php
/**
 * WC_PRL_Condition_Product_Price class
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
 * @class    WC_PRL_Condition_Product_Price
 * @version  1.0.0
 */
class WC_PRL_Condition_Product_Price extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'product_price';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Product price', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'min' => _x( '>=', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'max' => _x( '<', 'prl_modifiers', 'woocommerce-product-recommendations' )
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

		// Empty conditions always apply (not evaluated).
		if ( empty( $data[ 'value' ] ) && '0' != $data[ 'value' ] ) {
			return true;
		}

		global $product;
		$current_product = $product instanceof WC_Product ? $product->get_id() : null;
		if ( ! $current_product ) {
			return false;
		}

		$price = $product->get_price( 'edit' );

		if ( $this->modifier_is( $data[ 'modifier' ], 'min' ) && wc_format_decimal( $data[ 'value' ] ) <= $price ) {
			return true;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'max' ) && wc_format_decimal( $data[ 'value' ] ) > $price ) {
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
	 * @param  int         $condition_index
	 * @param  array       $condition_data
	 * @return void
	 */
	public function get_admin_fields_html( $post_name, $condition_index, $condition_data ) {

		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_deploy';
		$price     = '';

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'min';
		}

		if ( isset( $condition_data[ 'value' ] ) ) {
			$price = wc_format_localized_price( $condition_data[ 'value' ] );
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
				<input type="text" class="wc_input_price" name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value]" value="<?php echo $price; ?>" placeholder="<?php _e( 'Enter product price&hellip;"', 'woocommerce-product-recommendations' ); ?>" step="any" min="0" />
				<span class="os_value--suffix"><?php echo get_woocommerce_currency_symbol() ?></span>
			</div>
		</div>
		<?php
	}
}
