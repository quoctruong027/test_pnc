<?php
/**
 * WC_PRL_Amplifier_Popularity class
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
 * WC_PRL_Amplifier_Popularity class for amplifying products based on their order number in a given time span.
 *
 * @class    WC_PRL_Amplifier_Popularity
 * @version  1.4.0
 */
class WC_PRL_Amplifier_Popularity extends WC_PRL_Amplifier {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'popularity';
		$this->title                  = __( 'Popularity', 'woocommerce-product-recommendations' );
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

		$time_spans = $this->get_amplifier_values();

		if ( ! in_array( $data[ 'value' ], array_keys( $time_spans ) ) ) {
			return $query_args;
		}

		$now = date( 'Y-m-d' );
		if ( $time_spans[ $data[ 'value' ] ][ 'time_span' ] != 0 ) {
			$start = date( 'Y-m-d', strtotime( $time_spans[ $data[ 'value' ] ][ 'time_span' ] ) );
		} else {
			$start = false;
		}

		$posts_clauses                        = array();
		// If `all-time` modifier selected and lookup tables enabled, use them.
		$posts_clauses[ 'use_lookup_tables' ] = ! $start && wc_prl_lookup_tables_enabled();

		if ( $posts_clauses[ 'use_lookup_tables' ] ) {

			$posts_clauses[ 'join' ]    = " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
			$posts_clauses[ 'where' ]   = " AND wc_product_meta_lookup.total_sales > 0 ";
			$posts_clauses[ 'orderby' ] = " wc_product_meta_lookup.total_sales {$data[ 'modifier' ]}, wc_product_meta_lookup.product_id ASC ";

		} else {

			if ( wc_prl_lookup_tables_enabled( 'order' ) ) {

				$where_start               = $start ? "WHERE d.date_created >= '{$start}'" : '';
				$posts_clauses[ 'join' ]   = "
					INNER JOIN (
						SELECT product_id, count(*) as orders_count
						FROM `{$wpdb->prefix}wc_order_product_lookup` as d
						{$where_start}
						GROUP BY d.product_id
					) as prl_popularity ON ($wpdb->posts.ID = prl_popularity.product_id)
				";
				$posts_clauses[ 'orderby' ] = "prl_popularity.orders_count {$data[ 'modifier' ]}, $wpdb->posts.post_date DESC";
				$posts_clauses[ 'groupby' ] = "$wpdb->posts.ID";

			} else {

				$where_start               = $start ? "AND $wpdb->posts.post_date >= '{$start}'" : '';
				$posts_clauses[ 'join' ]   = "
					INNER JOIN (
						SELECT product_id, count(*) as orders_count
						FROM ( SELECT meta_value as product_id, post_date
							FROM $wpdb->order_itemmeta
							INNER JOIN {$wpdb->prefix}woocommerce_order_items ON($wpdb->order_itemmeta.order_item_id = {$wpdb->prefix}woocommerce_order_items.order_item_id)
							INNER JOIN {$wpdb->posts} ON({$wpdb->prefix}woocommerce_order_items.order_id = $wpdb->posts.ID)
							WHERE $wpdb->order_itemmeta.meta_key = '_product_id'
							AND {$wpdb->prefix}woocommerce_order_items.order_item_type = 'line_item'
							{$where_start}
							AND $wpdb->posts.post_date <= '{$now}'
							AND $wpdb->posts.post_type = 'shop_order' ) as d
						GROUP BY d.product_id
					) as prl_popularity ON ($wpdb->posts.ID = prl_popularity.product_id)
				";
				$posts_clauses[ 'orderby' ] = "prl_popularity.orders_count {$data[ 'modifier' ]}, $wpdb->posts.post_date DESC";
				$posts_clauses[ 'groupby' ] = "$wpdb->posts.ID";
			}
		}

		WC_PRL()->db->set_shared_posts_clauses( $posts_clauses );

		add_filter( 'posts_clauses', array( $this, 'add_order_clauses' ) );

		return $query_args;
	}

	/**
	 * Alters the raw query in order to add popularity order support.
	 *
	 * @param  array $args
	 * @return array
	 */
	public function add_order_clauses( $args ) {
		global $wpdb;

		$posts_clauses = WC_PRL()->db->get_shared_posts_clauses();
		// De-allocate.
		WC_PRL()->db->set_shared_posts_clauses( null );

		if ( $posts_clauses[ 'use_lookup_tables' ] ) {
			$args[ 'join' ]   .= $posts_clauses[ 'join' ];
			$args[ 'where' ]  .= $posts_clauses[ 'where' ];
			$args[ 'orderby' ] = $posts_clauses[ 'orderby' ];
		} else {
			$args[ 'join' ]   .= $posts_clauses[ 'join' ];
			$args[ 'orderby' ] = $posts_clauses[ 'orderby' ];
			$args[ 'groupby' ] = $posts_clauses[ 'groupby' ];
		}

		return $args;
	}

	/**
	 * Get all time-spans available for this amp.
	 *
	 * @return array
	 */
	public function get_amplifier_values() {

		$time_spans = array(
			'day' => array(
				'label'      => 'last 24 hours',
				'time_span'  => '-1 day'
			),
			'week' => array(
				'label'      => 'last 7 days',
				'time_span'  => '-7 day'
			),
			'month' => array(
				'label'      => 'last 30 days',
				'time_span'  => '-1 month'
			),
			'quarter' => array(
				'label'      => 'last 3 months',
				'time_span'  => '-3 month'
			),
			'all-time' => array(
				'label'      => 'all time',
				'time_span'  => 0
			)
		);

		/**
		 * 'woocommerce_prl_popularity_amplifier_values' filter.
		 *
		 * Modify popularity time spans.
		 *
		 * @param  array  $time_spans
		 */
		return apply_filters( 'woocommerce_prl_popularity_amplifier_values', $time_spans );
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
		$options   = $this->get_amplifier_values();
		$selected  = '';

		// Default modifier.
		if ( ! empty( $amplifier_data[ 'modifier' ] ) ) {
			$modifier = $amplifier_data[ 'modifier' ];
		} else {
			$modifier = 'DESC';
		}

		if ( isset( $amplifier_data[ 'value' ] ) ) {
			$selected = $amplifier_data[ 'value' ];
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
				<div class="sw-enhanced-select">
					<select name="<?php echo $post_name; ?>[amplifiers][<?php echo $amplifier_index; ?>][value]">
						<?php
						foreach ( $options as $option_value => $option_data ) {
							echo '<option value="' . $option_value . '" ' . selected( $option_value === $selected, true, false ) . '>' . $option_data[ 'label' ] . '</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div class="os_slider column-wc_actions">
				<?php wc_prl_print_weight_select( $weight, $post_name . '[amplifiers][' . $amplifier_index . '][weight]' ) ?>
			</div>
		</div><?php
	}
}
