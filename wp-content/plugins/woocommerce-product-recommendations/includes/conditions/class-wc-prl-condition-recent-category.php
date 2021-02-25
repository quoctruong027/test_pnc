<?php
/**
 * WC_PRL_Condition_Recent_Category class
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
 * @class    WC_PRL_Condition_Recent_Category
 * @version  1.2.5
 */
class WC_PRL_Condition_Recent_Category extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'recent_category';
		$this->complexity             = WC_PRL_Condition::QUERY_JOIN_COMPLEXITY;
		$this->title                  = __( 'Recently viewed category', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'cart', 'product', 'order', 'archive' );
		$this->needs_value            = true;
	}

	/**
	 * Enables the session if there is not enabled yet.
	 *
	 * @return void
	 */
	public function start_session() {

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

		if ( ! is_array( $data[ 'value' ] ) ) {
			$data[ 'value' ] = array( $data[ 'value' ] );
		}

		if ( ! isset( $_COOKIE[ 'wc_prl_recently_viewed' ] ) ) {
			return false;
		}

		// Get products part.
		$parts = ( array ) explode( ',', wp_unslash( $_COOKIE[ 'wc_prl_recently_viewed' ] ) );
		if ( count( $parts ) < 2 ) {
			return false;
		}

		$viewed_terms = wp_parse_id_list( ( array ) explode( '|', $parts[ 1 ] ) );

		if ( empty( $viewed_terms ) ) {
			return false;
		}

		$categories_matching = 0;
		$found_items         = false;

		foreach ( $data[ 'value' ] as $product_category_id ) {
			if ( in_array( $product_category_id, $viewed_terms ) ) {
				$categories_matching++;
			}
		}

		$term_relationship = $this->get_default_term_relationship();

		if ( 'or' === $term_relationship && $categories_matching ) {
			$found_items = true;
		} elseif ( 'and' === $term_relationship && $categories_matching === sizeof( $data[ 'value' ] ) ) {
			$found_items = true;
		}

		if ( $found_items ) {
			return $this->modifier_is( $data[ 'modifier' ], 'in' );
		} else {
			return $this->modifier_is( $data[ 'modifier' ], 'not-in' );
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

		$post_name          = ! is_null( $post_name ) ? $post_name : 'prl_deploy';
		$modifier           = '';
		$categories         = array();

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

		if ( isset( $condition_data[ 'value' ] ) ) {
			$categories = is_array( $condition_data[ 'value' ] ) ? $condition_data[ 'value' ] : array( $condition_data[ 'value' ] );
		}

		if ( is_null( self::$product_categories_tree ) ) {
			$product_categories = ( array ) get_terms( 'product_cat', array( 'get' => 'all' ) );
			self::$product_categories_tree = wc_prl_build_taxonomy_tree( $product_categories );
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
				<select name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Select categories&hellip;', 'woocommerce-product-recommendations' ); ?>"><?php
					wc_prl_print_taxonomy_tree_options( self::$product_categories_tree, $categories, apply_filters( 'woocommerce_prl_condition_dropdown_options', array(), $this ) );
				?></select>
			</div>
		</div>
		<?php
	}
}
