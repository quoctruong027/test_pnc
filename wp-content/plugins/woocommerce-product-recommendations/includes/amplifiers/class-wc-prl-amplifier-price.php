<?php
/**
 * WC_PRL_Amplifier_Price class
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
 * WC_PRL_Amplifier_Price class for amplifying products based on their price.
 *
 * @class    WC_PRL_Amplifier_Price
 * @version  1.0.0
 */
class WC_PRL_Amplifier_Price extends WC_PRL_Amplifier {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'price';
		$this->title                  = __( 'Price', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'ASC'  => _x( 'low to high', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'DESC' => _x( 'high to low', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
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
		global $wpdb;

		$posts_clauses = array();

		if ( wc_prl_lookup_tables_enabled() ) {
			$posts_clauses[ 'join' ]    = " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
			$posts_clauses[ 'orderby' ] = " wc_product_meta_lookup.min_price {$data[ 'modifier' ]}, wc_product_meta_lookup.product_id ASC ";
		} else {
			$posts_clauses[ 'join' ]    = " INNER JOIN ( SELECT post_id, min( meta_value+0 ) price FROM $wpdb->postmeta WHERE meta_key='_price' GROUP BY post_id ) as price_query ON $wpdb->posts.ID = price_query.post_id ";
			$posts_clauses[ 'orderby' ] = " price_query.price {$data[ 'modifier' ]}, $wpdb->posts.ID ASC ";
		}

		WC_PRL()->db->set_shared_posts_clauses( $posts_clauses );

		add_filter( 'posts_clauses', array( $this, 'add_order_clauses' ) );

		return $query_args;
	}

	/**
	 * Alters the raw query in order to add rating order support.
	 *
	 * @param  array $args
	 * @return array
	 */
	public function add_order_clauses( $args ) {

		$posts_clauses = WC_PRL()->db->get_shared_posts_clauses();
		// De-allocate.
		WC_PRL()->db->set_shared_posts_clauses( null );

		$args[ 'join' ]   .= $posts_clauses[ 'join' ];
		$args[ 'orderby' ] = $posts_clauses[ 'orderby' ];

		return $args;
	}

	/**
	 * Removes any global amp settings.
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
	 * @param  int      $amplifier_index
	 * @param  array    $amplifier_data
	 * @return void
	 */
	public function get_admin_fields_html( $post_name, $amplifier_index, $amplifier_data ) {

		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_engine';

		// Default modifier.
		if ( ! empty( $amplifier_data[ 'modifier' ] ) ) {
			$modifier = $amplifier_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

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
				<div class="sw-enhanced-select">
					<select name="<?php echo $post_name; ?>[amplifiers][<?php echo $amplifier_index; ?>][modifier]">
						<?php $this->get_modifiers_select_options( $modifier ); ?>
					</select>
				</div>
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
