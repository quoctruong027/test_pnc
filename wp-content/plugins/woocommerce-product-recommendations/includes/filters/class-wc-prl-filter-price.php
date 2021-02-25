<?php
/**
 * WC_PRL_Filter_Price class
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
 * WC_PRL_Filter_Price class for filtering products based on their price.
 *
 * @class    WC_PRL_Filter_Price
 * @version  1.1.2
 */
class WC_PRL_Filter_Price extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                                = 'price';
		$this->type                              = 'static';
		$this->title                             = __( 'Price', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'min' => _x( '>=', 'prl_modifiers', 'woocommerce-product-recommendations' ), // <=
			'max' => _x( '<', 'prl_modifiers', 'woocommerce-product-recommendations' ) // >
		);
		$this->supported_engine_types            = array( 'cart', 'product', 'order', 'archive' );
		$this->needs_value                       = true;
	}

	/**
	 * Apply the filter to the query args array. -- @see WC_PRL_Filter::run()
	 *
	 * @param  array $query_args
	 * @param  WC_PRL_Deployment $deployment
	 * @param  array $data
	 * @return array
	 */
	public function filter( $query_args, $deployment, $data ) {

		if ( empty( $data[ 'value' ] ) ) {
			return $query_args;
		}

		if ( ! isset( $query_args[ 'prl_meta_query' ] ) || ! is_array( $query_args[ 'prl_meta_query' ] ) ) {
			$query_args[ 'prl_meta_query' ] = array();
		}

		$query_args[ 'prl_meta_query' ][] = array(
			'key'     => '_price',
			'value'   => floatval( $data[ 'value' ] ),
			'compare' => strtolower( $data[ 'modifier' ] ) == 'min' ? '>=' : '<',
			'type'    => 'NUMERIC'
		);

		return $query_args;
	}

	/*---------------------------------------------------*/
	/*  Force methods.                                   */
	/*---------------------------------------------------*/

	/**
	 * Get admin html for filter inputs.
	 *
	 * @param  string|null $post_name
	 * @param  int      $filter_index
	 * @param  array    $filter_data
	 * @return void
	 */
	public function get_admin_fields_html( $post_name, $filter_index, $filter_data ) {
		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$modifier  = '';
		$price     = '';

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

		// Price format.
		if ( isset( $filter_data[ 'value' ] ) ) {
			$price = wc_format_localized_price( $filter_data[ 'value' ] );
		}

		?>
		<input type="hidden" name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][id]" value="<?php echo $this->id; ?>" />
		<div class="os_row_inner">
			<div class="os_modifier">
				<div class="sw-enhanced-select">
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][modifier]">
						<?php $this->get_modifiers_select_options( $modifier ); ?>
					</select>
				</div>
			</div>
			<div class="os_value">
				<input type="text" class="wc_input_price" name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value]" value="<?php echo $price; ?>" placeholder="<?php _e( 'Enter a price&hellip;"', 'woocommerce-product-recommendations' ); ?>" step="any" min="0" />
				<span class="os_value--suffix"><?php echo get_woocommerce_currency_symbol() ?></span>
			</div>
		</div>
		<?php
	}
}
