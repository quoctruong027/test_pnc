<?php
/**
 * WC_PRL_Condition_Product_Stock_Status class
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
 * @class    WC_PRL_Condition_Product_Stock_Status
 * @version  1.0.0
 */
class WC_PRL_Condition_Product_Stock_Status extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'product_stock_status';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Product stock status', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'is'     => _x( 'is', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'is-not' => _x( 'is not', 'prl_modifiers', 'woocommerce-product-recommendations' )
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

		if ( is_array( $data[ 'value' ] ) ) {
			$data[ 'value' ] = end( $data[ 'value' ] );
		}

		global $product;
		$found           = false;
		$current_product = $product instanceof WC_Product ? $product : null;
		if ( ! $current_product ) {
			return false;
		}

		if ( $current_product->get_stock_status() === $data[ 'value' ] ) {
			$found = true;
		}

		if ( $found ) {
			return $this->modifier_is( $data[ 'modifier' ], 'is' );
		} else {
			return $this->modifier_is( $data[ 'modifier' ], 'is-not' );
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

		$post_name  = ! is_null( $post_name ) ? $post_name : 'prl_deploy';
		$options    = wc_get_product_stock_status_options();
		$modifier   = '';
		$selected   = '';

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'is';
		}

		if ( isset( $condition_data[ 'value' ] ) ) {
			$selected = $condition_data[ 'value' ];
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
				<div class="sw-enhanced-select">
					<select name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value]">
						<?php
						foreach ( $options as $option_value => $option_label ) {
							echo '<option value="' . $option_value . '" ' . selected( $option_value === $selected, true, false ) . '>' . $option_label . '</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<?php
	}
}
