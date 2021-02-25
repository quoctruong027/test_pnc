<?php
/**
 * WC_PRL_Condition_Customer class
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
 * @class    WC_PRL_Condition_Customer
 * @version  1.0.0
 */
class WC_PRL_Condition_Customer extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'customer';
		$this->complexity             = WC_PRL_Condition::LOW_COMPLEXITY;
		$this->title                  = __( 'Customer', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'is-guest'     => _x( 'is guest', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'is-not-guest' => _x( 'is not guest', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'cart', 'product', 'order', 'archive' );
	}

	/**
	 * Check the condition to the current request.
	 *
	 * @param  array  $data
	 * @param  WC_PRL_deployment  $deployment
	 * @return bool
	 */
	public function check( $data, $deployment ) {

		if ( $this->modifier_is( $data[ 'modifier' ], 'is-guest' ) && ! is_user_logged_in() ) {
			return true;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'is-not-guest' ) && is_user_logged_in() ) {
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

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'max';
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
				<div class="os--disabled"></div>
			</div>
		</div>
		<?php
	}
}
