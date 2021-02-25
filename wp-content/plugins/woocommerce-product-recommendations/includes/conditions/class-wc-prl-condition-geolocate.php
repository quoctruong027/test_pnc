<?php
/**
 * WC_PRL_Condition_Geolocate class
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
 * @class    WC_PRL_Condition_Geolocate
 * @version  1.0.0
 */
class WC_PRL_Condition_Geolocate extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'geolocate';
		$this->complexity             = WC_PRL_Condition::MEDIUM_COMPLEXITY;
		$this->title                  = __( 'Geolocated country', 'woocommerce-product-recommendations' );
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
	 * @param  array              $data
	 * @param  WC_PRL_Deployment  $deployment
	 * @return bool
	 */
	public function check( $data, $deployment ) {

		if ( empty( $data[ 'value' ] ) ) {
			return true;
		}

		if ( ! is_array( $data[ 'value' ] ) ) {
			$data[ 'value' ] = array( $data[ 'value' ] );
		}

		$default_location = wc_get_customer_default_location();
		$found            = in_array( $default_location[ 'country' ], $data[ 'value' ] );

		if ( $this->modifier_is( $data[ 'modifier' ], 'in' ) && $found ) {
			return true;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'not-in' ) && ! $found ) {
			return true;
		}

		return false;
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

		$post_name      = ! is_null( $post_name ) ? $post_name : 'prl_deploy';
		$modifier       = '';
		$list_countries = WC()->countries->get_allowed_countries();
		$countries      = array();

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

		if ( ! empty( $condition_data[ 'value' ] ) ) {
			$countries = (array) $condition_data[ 'value' ];
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
			<div class="os_value select-field">
				<select name="<?php echo $post_name; ?>[conditions][<?php echo $condition_index; ?>][value][]" class="multiselect sw-select2" multiple="multiple" data-placeholder="<?php _e( 'Select countries&hellip;', 'woocommerce-product-recommendations' ); ?>">
					<?php
						foreach ( $list_countries as $key => $val ) {
							echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $countries ), true, false ).'>' . $val . '</option>';
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
