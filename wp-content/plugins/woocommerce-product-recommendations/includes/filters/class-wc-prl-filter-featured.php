<?php
/**
 * WC_PRL_Filter_Featured class
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
 * WC_PRL_Filter_Featured class for filtering products based on category.
 *
 * @class    WC_PRL_Filter_Featured
 * @version  1.0.0
 */
class WC_PRL_Filter_Featured extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'featured';
		$this->type                   = 'static';
		$this->title                  = __( 'Featured', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'is'     => _x( 'is', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'is-not' => _x( 'is not', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'cart', 'product', 'order', 'archive' );
	}

	/**
	 * Apply the filter to the query args array.
	 *
	 * @param  array $query_args
	 * @param  WC_PRL_Deployment $deployment
	 * @param  array $data
	 * @return array
	 */
	public function filter( $query_args, $deployment, $data ) {

		$query_args[ 'featured' ] = ( 'is' === $data[ 'modifier' ] ) ? true : false;

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

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'is';
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
				<div class="os--disabled"></div>
			</div>
		</div>
		<?php
	}
}
