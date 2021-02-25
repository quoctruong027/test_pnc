<?php
/**
 * WC_PRL_Condition_Recent_Tag class
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
 * @class    WC_PRL_Condition_Recent_Tag
 * @version  1.2.0
 */
class WC_PRL_Condition_Recent_Tag extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'recent_tag';
		$this->complexity             = WC_PRL_Condition::QUERY_JOIN_COMPLEXITY;
		$this->title                  = __( 'Recently viewed tag', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types = array( 'cart', 'product', 'order', 'archive' );
		$this->needs_value            = true;
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
		if ( count( $parts ) < 3 ) {
			return false;
		}

		$viewed_terms = wp_parse_id_list( ( array ) explode( '|', $parts[ 2 ] ) );

		if ( empty( $viewed_terms ) ) {
			return false;
		}

		$tags_matching = 0;
		$found_items   = false;

		foreach ( $data[ 'value' ] as $product_tag_id ) {
			if ( in_array( $product_tag_id, $viewed_terms ) ) {
				$tags_matching++;
			}
		}

		$term_relationship = $this->get_default_term_relationship();

		if ( 'or' === $term_relationship && $tags_matching ) {
			$found_items = true;
		} elseif ( 'and' === $term_relationship && $tags_matching === sizeof( $data[ 'value' ] ) ) {
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

		$post_name    = ! is_null( $post_name ) ? $post_name : 'prl_deploy';
		$product_tags = ( array ) get_terms( 'product_tag', array( 'get' => 'all' ) );
		$modifier     = '';
		$tags         = array();

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

		if ( isset( $condition_data[ 'value' ] ) ) {
			$tags = is_array( $condition_data[ 'value' ] ) ? $condition_data[ 'value' ] : array( $condition_data[ 'value' ] );
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
				<select name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Select tags&hellip;', 'woocommerce-product-recommendations' ); ?>">
					<?php
						foreach ( $product_tags as $product_tag )
							echo '<option value="' . $product_tag->term_id . '" ' . selected( in_array( $product_tag->term_id, $tags ), true, false ) . '>' . $product_tag->name . '</option>';
					?>
				</select>
			</div>
		</div>
		<?php
	}
}
