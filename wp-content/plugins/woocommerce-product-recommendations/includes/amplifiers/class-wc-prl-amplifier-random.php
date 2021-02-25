<?php
/**
 * WC_PRL_Amplifier_Random class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.2.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_PRL_Amplifier_Random class for amplifying products based on their freshness.
 *
 * @class    WC_PRL_Amplifier_Random
 * @version  1.2.3
 */
class WC_PRL_Amplifier_Random extends WC_PRL_Amplifier {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'random';
		$this->title                  = __( 'Random', 'woocommerce-product-recommendations' );
		$this->supported_engine_types = array( 'cart', 'product', 'order', 'archive' );
	}

	/**
	 * Apply the amplifier to the query args array.
	 *
	 * @param  array $query_args
	 * @param  WC_PRL_Deployment $deployment
	 * @param  array $data
	 * @return array
	 */
	public function amp( $query_args, $deployment, $data ) {

		$query_args[ 'orderby' ] = 'rand';

		return $query_args;
	}

	/*---------------------------------------------------*/
	/*  Force methods.                                   */
	/*---------------------------------------------------*/

	/**
	 * Get admin html for filter inputs.
	 *
	 * @param  string|null $post_name
	 * @param  int      $amplifier_index
	 * @param  array    $amplifier_data
	 * @return void
	 */
	public function get_admin_fields_html( $post_name, $amplifier_index, $amplifier_data ) {

		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_engine';

		// Default weight.
		if ( ! empty( $amplifier_data[ 'weight' ] ) ) {
			$weight = absint( $amplifier_data[ 'weight' ] );
		} else {
			$weight = 4;
		}

		?>
		<input type="hidden" name="<?php echo $post_name; ?>[amplifiers][<?php echo $amplifier_index; ?>][id]" value="<?php echo $this->id; ?>" />
		<div class="os_row_inner">
			<div class="os_modifier">
				<div class="os--disabled"></div>
			</div>
			<div class="os_semi_value">
				<div class="os--disabled"></div>
			</div>
			<div class="os_slider column-wc_actions">
				<?php wc_prl_print_weight_select( $weight, $post_name . '[amplifiers][' . $amplifier_index . '][weight]' ) ?>
			</div>
		</div><?php
	}
}
