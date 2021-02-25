<?php
/**
 * WC_PRL_Filter_Sales class
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
 * WC_PRL_Filter_Sales class for filtering products based on category.
 *
 * @class    WC_PRL_Filter_Sales
 * @version  1.0.0
 */
class WC_PRL_Filter_Sales extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'on_sale';
		$this->type                   = 'static';
		$this->title                  = __( 'On Sale', 'woocommerce-product-recommendations' );
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

		$product_ids_on_sale = wc_get_product_ids_on_sale();

		if ( strtolower( $data[ 'modifier' ] ) === 'is' ) {

			if ( ! empty( $product_ids_on_sale ) ) {

				if ( ! empty( $query_args[ 'include' ] ) ) {
					$value = array_intersect( $query_args[ 'include' ], $product_ids_on_sale );
				} else {
					$value = $product_ids_on_sale;
				}

				$query_args[ 'include' ] = $value;
			} else {
				// No products on-sale -- Don't include anything.
				$query_args[ 'date_created' ] = 0;
			}

		} elseif ( strtolower( $data[ 'modifier' ] ) === 'is-not' ) {

			if ( ! empty( $product_ids_on_sale ) ) {

				if ( ! empty( $query_args[ 'exclude' ] ) ) {
					$value = array_unique( array_merge( $query_args[ 'exclude' ], $product_ids_on_sale ) );
				} else {
					$value = $product_ids_on_sale;
				}

				$query_args[ 'exclude' ] = $value;
			}
		}

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
