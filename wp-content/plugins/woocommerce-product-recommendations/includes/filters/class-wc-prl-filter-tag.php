<?php
/**
 * WC_PRL_Filter_Tag class
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
 * WC_PRL_Filter_Tag class for filtering products based on category.
 *
 * @class    WC_PRL_Filter_Tag
 * @version  1.4.6
 */
class WC_PRL_Filter_Tag extends WC_PRL_Filter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                                = 'tag';
		$this->type                              = 'static';
		$this->title                             = __( 'Tag', 'woocommerce-product-recommendations' );
		$this->supported_modifiers               = array(
			'in'     => _x( 'in', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'not-in' => _x( 'not in', 'prl_modifiers', 'woocommerce-product-recommendations' )
		);
		$this->supported_engine_types            = array( 'cart', 'product', 'order', 'archive' );
		$this->needs_value                       = true;
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

		if ( empty( $data[ 'value' ] ) ) {
			return $query_args;
		}

		if ( ! is_array( $data[ 'value' ] ) ) {
			$data[ 'value' ] = array( $data[ 'value' ] );
		}

		if ( ! isset( $query_args[ 'prl_tax_query' ] ) || ! is_array( $query_args[ 'prl_tax_query' ] ) ) {
			$query_args[ 'prl_tax_query' ] = array();
		}

		$query_args[ 'prl_tax_query' ][] = array(
			'taxonomy' => 'product_tag',
			'field'    => 'slug',
			'terms'    => $data[ 'value' ],
			'operator' => strtolower( $data[ 'modifier' ] ) === 'in' ? 'IN' : 'NOT IN'
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
		$post_name    = ! is_null( $post_name ) ? $post_name : 'prl_engine';
		$product_tags = ( array ) get_terms( 'product_tag', array( 'get' => 'all' ) );
		$modifier     = '';
		$tags         = array();

		// Default modifier.
		if ( ! empty( $filter_data[ 'modifier' ] ) ) {
			$modifier = $filter_data[ 'modifier' ];
		} else {
			$modifier = 'in';
		}

		// Price format.
		if ( isset( $filter_data[ 'value' ] ) ) {
			$tags = is_array( $filter_data[ 'value' ] ) ? $filter_data[ 'value' ] : array( $filter_data[ 'value' ] );
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
			<div class="os_value select-field">
				<select name="<?php echo $post_name; ?>[filters][<?php echo $filter_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Select tags&hellip;', 'woocommerce-product-recommendations' ); ?>">
					<?php
						foreach ( $product_tags as $product_tag ) {
							echo '<option value="' . $product_tag->slug . '" ' . selected( in_array( $product_tag->slug, $tags ), true, false ) . '>' . $product_tag->name . '</option>';
						}
					?>
				</select>
				<span class="os_form_row">
					<a class="os_select_all button" href="#"><?php _e( 'All', 'woocommerce' ); ?></a>
					<a class="os_select_none button" href="#"><?php _e( 'None', 'woocommerce' ); ?></a>
				</span>
			</div>
		</div>
		<?php
	}
}
