<?php
/**
 * Template Functions
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'woocommerce_prl_add_link_track_param' ) ) {
	/**
	 * Insert GET params in order to keep track of the clicks.
	 */
	function woocommerce_prl_add_link_track_param( $link ) {

		if ( ! wc_prl_tracking_enabled() ) {
			return $link;
		}

		global $product;

		$deployment = WC_PRL()->templates->get_current_deployment();

		if ( ! is_null( $deployment ) ) {

			$product_id = $product->get_id();

			// If the $product_id is a variation ID.
			if ( $product->is_type( 'variation' ) ) {
				$product_id = $product->get_parent_id();
			}

			// Create a concatenated param value.
			// eg. `[deployment_id] _ [engine_id] _ [location_hash] _ [product_id] _ [source_hash?]`

			$value = $deployment->get_id() . '_' . $deployment->get_engine_id() . '_' . substr( md5( $deployment->get_hook() ), 0, 7 ) . '_' . $product_id;

			// Add source hash if applicable.
			$source_hash = $deployment->get_tracking_source_hash();
			if ( $source_hash ) {
				$value .= '_' . $source_hash;
			}

			$link = add_query_arg( 'prl_track', $value, $link );
		}

		return $link;
	}
}

if ( ! function_exists( 'woocommerce_prl_add_data_track_param' ) ) {
	/**
	 * Insert data track param for add-to-cart AJAX.
	 */
	function woocommerce_prl_add_data_track_param( $args, $product ) {

		if ( ! wc_prl_tracking_enabled() ) {
			return $args;
		}

		$deployment = WC_PRL()->templates->get_current_deployment();

		if ( ! is_null( $deployment ) ) {

			$product_id = $product->get_id();

			// If the $product_id is a variation ID.
			if ( $product->is_type( 'variation' ) ) {
				$product_id = $product->get_parent_id();
			}

			// Create a concatenated param value.
			// eg. `[deployment_id] _ [engine_id] _ [location_hash] _ [product_id] _ [source_hash?]`

			$value = $deployment->get_id() . '_' . $deployment->get_engine_id() . '_' . substr( md5( $deployment->get_hook() ), 0, 7 ) . '_' . $product_id;

			// Add source hash if applicable.
			$source_hash = $deployment->get_tracking_source_hash();
			if ( $source_hash ) {
				$value .= '_' . $source_hash;
			}

			$args[ 'attributes' ][ 'data-prl_track' ] = $value;
		}

		return $args;
	}
}

function wc_prl_print_weight_select( $weight, $input_name ) {
	?><div class="sw-enhanced-weight">
		<button type="button" class="button dec"></button>
		<div class="points">
			<input type="hidden" value="<?php echo $weight ?>" name="<?php echo $input_name; ?>">
			<?php for ( $i = 0; $i < $weight; $i++ ) { ?>
				<span class="active"></span>
			<?php } ?>

			<?php
			if ( $weight < 5 ) {
				for ( $i = 0; $i < 5 - $weight; $i++ ) { ?>
				<span></span>
				<?php } ?>
			<?php } ?>
		</div>
		<button type="button" class="button inc"></button>
	</div><?php
}

function wc_prl_print_currency_amount( $amount ) {

	$amount = wc_format_decimal( $amount, 2 );
	$return = $amount;

	switch ( get_option( 'woocommerce_currency_pos' ) ) {
		case 'right':
			$return = $amount . get_woocommerce_currency_symbol();
			break;
		case 'right_space':
			$return = $amount . ' ' . get_woocommerce_currency_symbol();
			break;
		case 'left':
			$return = get_woocommerce_currency_symbol() . $amount;
			break;
		case 'left_space':
			$return = get_woocommerce_currency_symbol() . ' ' . $amount;
		default:
			break;
	}

	return $return;
}
