<?php
/**
 * WC_PRL_Filter_Recently_Viewed class
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
 * WC_PRL_Filter_Recently_Viewed class for filtering products based on customer view history.
 *
 * @class    WC_PRL_Filter_Recently_Viewed
 * @version  1.0.0
 */
class WC_PRL_Filter_Recently_Viewed extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'recently_viewed';
		$this->type                   = 'dynamic';
		$this->title                  = __( 'Recently Viewed', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'cart', 'product', 'order', 'archive' );
	}

	/**
	 * Apply dynamic filter process.
	 *
	 * @param  array $products
	 * @param  int   $max_products
	 * @param  array $filter_data
	 * @param  WC_PRL_Engine $engine
	 * @return void
	 */
	public function run( &$products, $max_products, $filter_data, $engine ) {

		if ( ! isset( $_COOKIE[ 'wc_prl_recently_viewed' ] ) ) {
			if ( strtolower( $filter_data[ 'modifier' ] ) === 'in' ) {
				$products = array();
			}
			return;
		}

		// Get products part.
		$parts = ( array ) explode( ',', wp_unslash( $_COOKIE[ 'wc_prl_recently_viewed' ] ) );

		if ( empty( $parts ) || empty( $parts[ 0 ] ) ) {
			if ( strtolower( $filter_data[ 'modifier' ] ) === 'in' ) {
				$products = array();
			}
			return;
		}

		$viewed_products = wp_parse_id_list( ( array ) explode( '|', $parts[ 0 ] ) );

		if ( empty( $viewed_products ) ) {
			return;
		}

		if ( strtolower( $filter_data[ 'modifier' ] ) === 'in' ) {

			// If there are ONLY dynamic filters, return pure cookie value.
			if ( count( $engine->get_filters_data() ) === count( $engine->get_dynamic_filters_data() ) && empty( $engine->get_amplifiers_data() ) ) {
				$products = array_reverse( $viewed_products );
			} else {
				$products = array_intersect( $products, $viewed_products );
			}

		} elseif ( strtolower( $filter_data[ 'modifier' ] ) === 'not-in' ) {
			$products = array_diff( $products, $viewed_products );
		}
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
			$modifier = 'in';
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
