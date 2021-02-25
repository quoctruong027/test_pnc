<?php
/**
 * WC_PRL_Condition_Date class
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
 * @class    WC_PRL_Condition_Date
 * @version  1.0.0
 */
class WC_PRL_Condition_Date extends WC_PRL_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'date';
		$this->complexity             = WC_PRL_Condition::ZERO_COMPLEXITY;
		$this->title                  = __( 'Date', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array(
			'min' => _x( '>=', 'prl_modifiers', 'woocommerce-product-recommendations' ),
			'max' => _x( '<', 'prl_modifiers', 'woocommerce-product-recommendations' )
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

		if ( empty( $data[ 'value' ] ) && ! is_array( $data[ 'value' ] ) ) {
			return true;
		}

		$date = strtotime( $data[ 'value' ][ 'year' ] . '/' . $data[ 'value' ][ 'month' ] . '/' . $data[ 'value' ][ 'day' ] );
		$now  = time();

		if ( $this->modifier_is( $data[ 'modifier' ], 'min' ) && $now >= $date ) {
			return true;
		} elseif ( $this->modifier_is( $data[ 'modifier' ], 'max' ) && $now <= $date ) {
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

		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_deploy';
		$modifier  = '';

		$year_now  = absint( date( 'Y' ) );
		$year_span = 3;
		$date      = array(
			'month' => '01',
			'day'   => '1',
			'year'  => $year_now,
		);

		// Default modifier.
		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		} else {
			$modifier = 'max';
		}

		if ( isset( $condition_data[ 'value' ] ) && is_array( $condition_data[ 'value' ] ) ) {
			$date = $condition_data[ 'value' ];
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
				<div class="os_date_picker">
					<div class="sw-enhanced-select">
						<?php
						global $wp_locale;
						$month = '<select class="mm" name="' . $post_name . '[conditions][' . $condition_index . '][value][month]">\n"';
						for ( $i = 1; $i < 13; $i = $i + 1 ) {
							$monthnum  = zeroise( $i, 2 );
							$monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
							$month    .= "\t\t\t" . '<option value="' . $monthnum . '" ' . selected( $monthnum, $date[ 'month' ], false ) . '>';
							/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
							$month .= sprintf( __( '%1$s-%2$s', 'woocommerce-product-recommendations' ), $monthnum, $monthtext ) . "</option>\n";
						}
						$month .= '</select></label>';
						echo $month;
						?>
					</div>
					<div class="sw-enhanced-select">
						<?php
						$day = '<select class="dd" name="' . $post_name . '[conditions][' . $condition_index . '][value][day]">\n"';
						for ( $i = 1; $i < 32; $i = $i + 1 ) {
							$day .= "\t\t\t" . '<option value="' . $i . '" ' . selected( $i, $date[ 'day' ], false ) . '>';
							$day .= $i . "</option>\n";
						}
						$day .= '</select>';
						echo $day;
						?>
					</div>
					<div class="sw-enhanced-select">
						<?php
						$year = '<select class="yy" name="' . $post_name . '[conditions][' . $condition_index . '][value][year]">\n"';
						for ( $i = $year_now; $i < $year_now + $year_span + 1; $i = $i + 1 ) {
							$year .= "\t\t\t" . '<option value="' . $i . '" ' . selected( $i, $date[ 'year' ], false ) . '>';
							$year .= $i . "</option>\n";
						}
						$year .= '</select>';
						echo $year;
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
