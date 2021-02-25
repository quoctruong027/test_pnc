<?php
/**
 * WC_PRL_Filter_Stock_Status class
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
 * WC_PRL_Filter_Stock_Status class for filtering products based on stock status.
 *
 * @class    WC_PRL_Filter_Stock_Status
 * @version  1.4.5
 */
class WC_PRL_Filter_Stock_Status extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'stock_status';
		$this->type                   = 'dynamic';
		$this->title                  = __( 'Stock Status', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'is'     => _x( 'is', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'is-not' => _x( 'is not', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'cart', 'product', 'order', 'archive' );
		$this->needs_value            = true;
	}

	/**
	 * Apply dynamic filter process.
	 *
	 * @param  array          $products
	 * @param  int            $max_products
	 * @param  array          $filter_data
	 * @param  WC_PRL_Engine  $engine
	 * @return void
	 */
	public function run( &$products, $max_products, $filter_data, $engine ) {
		global $wpdb;

		if ( empty( $products ) ) {
			return;
		}

		// Sanity check.
		$accepted_values = wc_get_product_stock_status_options();
		$stock_value     = $filter_data[ 'value' ];
		if ( ! in_array( $stock_value, array_keys( $accepted_values ) ) ) {
			return;
		}

		if ( 'is-not' === strtolower( $filter_data[ 'modifier' ] ) ) {
			$stock_value = array_diff( array_keys( $accepted_values ), array( $stock_value ) );
		}

		if ( wc_prl_lookup_tables_enabled() ) {

			$args = array(
				'return'       => 'ids',
				'limit'        => count( $products ),
				'include'      => $products,
				'orderby'      => 'post__in'
			);

			if ( ! is_array( $stock_value ) ) {
				$stock_value = array( $stock_value );
			}

			$posts_clauses            = array();
			$posts_clauses[ 'join' ]  = " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
			$posts_clauses[ 'where' ] = " AND wc_product_meta_lookup.stock_status IN ( '" . implode( '\',\'', $stock_value ) . "' )";

			WC_PRL()->db->set_shared_posts_clauses( $posts_clauses );

			add_filter( 'posts_clauses', array( $this, 'add_order_clauses' ) );
			$products = $engine->query( $args );
			remove_filter( 'posts_clauses', array( $this, 'add_order_clauses' ) );

		} else {

			// `stock_status` argument in `wc_get_products` expects a string enum ( 'instock', 'outofstock' ).
			if ( is_array( $stock_value ) ) {
				if ( in_array( 'instock', $stock_value ) ) {
					$stock_value = 'instock';
				} elseif ( in_array( 'outofstock', $stock_value ) ) {
					$stock_value = 'outofstock';
				}
			}

			// Bulk approach.
			$args = array(
				'return'       => 'ids',
				'limit'        => count( $products ),
				'include'      => $products,
				'orderby'      => 'post__in'
			);

			if ( ! is_array( $stock_value ) && $stock_value ) {
				$args[ 'stock_status' ] = $stock_value;
				$products = $engine->query( $args );
			}
		}
	}

	/**
	 * Alters the raw query to add sorting tables support.
	 *
	 * @param  array $args
	 * @return array
	 */
	public function add_order_clauses( $args ) {

		$posts_clauses = WC_PRL()->db->get_shared_posts_clauses();
		// De-allocate.
		WC_PRL()->db->set_shared_posts_clauses( null );

		$args[ 'join' ]  .= $posts_clauses[ 'join' ];
		$args[ 'where' ] .= $posts_clauses[ 'where' ];

		return $args;
	}

	/**
	 * Removes any addition in the query.
	 *
	 * @return void
	 */
	public function remove_amp() {
		remove_filter( 'posts_clauses', array( $this, 'add_order_clauses' ) );
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
		$post_name  = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$options    = wc_get_product_stock_status_options();
		$modifier   = '';
		$selected   = '';

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		if ( isset( $filter_data[ 'value' ] ) ) {
			$selected = $filter_data[ 'value' ];
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
				<div class="sw-enhanced-select">
					<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value]">
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
