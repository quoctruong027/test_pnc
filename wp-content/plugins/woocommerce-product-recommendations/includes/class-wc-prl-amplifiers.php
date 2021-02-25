<?php
/**
 * WC_PRL_Amplifiers class
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
 * Amplifiers Collection class.
 *
 * @class    WC_PRL_Amplifiers
 * @version  1.4.0
 */
class WC_PRL_Amplifiers {

	/**
	 * Amplifiers.
	 *
	 * @var array
	 */
	protected $amplifiers;

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'instantiate_amplifiers' ), 9 );

		/*---------------------------------------------------*/
		/*  Print amplifiers JS templates in footer.         */
		/*---------------------------------------------------*/

		add_action( 'admin_footer', array( $this, 'print_amplifiers_field_scripts' ) );

	}

	public function instantiate_amplifiers() {

		$load_amplifiers = array(
			'WC_PRL_Amplifier_Conversion_Rate',
			'WC_PRL_Amplifier_Freshness',
			'WC_PRL_Amplifier_Popularity',
			'WC_PRL_Amplifier_Price',
			'WC_PRL_Amplifier_Random',
			'WC_PRL_Amplifier_Rating'
		);

		if ( wc_prl_lookup_tables_enabled( 'order' ) ) {
			$load_amplifiers[] = 'WC_PRL_Amplifier_Frequently_Bought_Together';
			$load_amplifiers[] = 'WC_PRL_Amplifier_Others_Also_Bought';
		}

		$load_amplifiers = (array) apply_filters( 'woocommerce_prl_amplifiers', $load_amplifiers );
		sort( $load_amplifiers );

		foreach ( $load_amplifiers as $amplifier ) {
			$amplifier = new $amplifier();
			$this->amplifiers[ $amplifier->get_id() ] = $amplifier;
		}
	}

	/**
	 * Get amplifier class by id.
	 *
	 * @param  string  $amplifier_id
	 * @return WC_PRL_Amplifier|false
	 */
	public function get_amplifier( $amplifier_id ) {

		if ( ! empty( $this->amplifiers[ $amplifier_id ] ) ) {
			return $this->amplifiers[ $amplifier_id ];
		}

		return false;
	}

	/**
	 * Get all supported amplifiers.
	 *
	 * @param  string $engine_type
	 * @return array
	 */
	public function get_supported_amplifiers( $engine_type = '' ) {

		$amplifiers = array();

		foreach ( $this->amplifiers as $id => $amplifier ) {
			if ( $engine_type === '' || $amplifier->has_engine_type( $engine_type ) ) {
				$amplifiers[ $id ] = $amplifier;
			}
		}

		return apply_filters( 'woocommerce_prl_get_supported_amplifiers', $amplifiers, $engine_type );
	}

	/**
	 * Get amplifiers fields for admin engine metabox.
	 *
	 * @param  WC_PRL_Engine $engine
	 * @param  int    $index
	 * @param  array  $options
	 * @return str
	 */
	public function get_admin_filters_html( $engine, $options = array() ) {

		$amplifiers = $this->get_supported_amplifiers( $engine->get_type() );

		if ( empty( $amplifiers ) ) {
			return false;
		}

		?>
		<div class="sw-hr-section">
			<?php
			echo __( 'Amplifiers', 'woocommerce-product-recommendations' );
			echo wc_help_tip( __( 'Use amplifiers to adjust the display order of filtered recommendations. Add multiple weighted amplifiers to perform advanced sort operations.', 'woocommerce-product-recommendations' ) );
			?>
		</div>
		<?php
		$amplifiers_data  = empty( $options[ 'amplifiers' ] ) ? $engine->get_amplifiers_data() : $options[ 'amplifiers' ];
		$amplifiers_count = count( $amplifiers_data );
		?><div id="os-container" class="widefat wc-prl-amplifiers-container" data-os_count="<?php echo $amplifiers_count; ?>">
			<div class="os_boarding<?php echo $amplifiers_count ? '' : ' active'; ?>">
				<div class="icon">
					<i class="prl-icon prl-amps"></i>
				</div>
				<div class="text"><?php _e( 'No amplifiers found. Add one now?', 'woocommerce-product-recommendations' ); ?></div>
			</div>
			<div id="os-list"<?php echo $amplifiers_count ? '' : ' class="hidden"'; ?>><?php

				if ( $amplifiers_count ) {
					foreach ( $amplifiers_data as $amplifier_index => $amplifier_data ) {

						if ( isset( $amplifier_data[ 'id' ] ) ) {

							$amplifier_id = $amplifier_data[ 'id' ];

							if ( array_key_exists( $amplifier_id, $amplifiers ) ) {

								?><div class="os_row" data-os_index="<?php echo $amplifier_index; ?>">

									<div class="os_select">
										<div class="sw-enhanced-select"><?php
											$this->get_amplifiers_dropdown( $engine->get_type(), $amplifiers, $amplifier_id );
										?></div>
									</div>
									<div class="os_content"><?php
										$amplifiers[ $amplifier_id ]->get_admin_fields_html( null, $amplifier_index, $amplifier_data );
									?></div>
									<div class="os_remove column-wc_actions">
										<a href="#" data-tip="<?php echo __( 'Remove', 'woocommerce-product-recommendations' ) ?>" class="button wc-action-button trash help_tip"></a>
									</div>
								</div><?php
							}
						}
					}
				}
			?>
			</div>
			<div class="os_add os_row<?php echo $amplifiers_count ? '' : ' os_add--boarding'; ?>">
				<div class="os_select">
					<div class="sw-enhanced-select">
						<?php $this->get_amplifiers_dropdown( $engine->get_type(), $amplifiers, null, [ 'add' => __( 'Add amplifier', 'woocommerce-product-recommendations' ) ] ); ?>
					</div>
				</div>
				<div class="os_content">
					<div class="os--placeholder os--disabled"></div>
				</div>
				<div class="os_remove">
				</div>
			</div>
		</div><?php
	}

	/**
	 * Admin amplifiers select dropdown.
	 *
	 * @param  string $engine_type
	 * @param  array  $amplifiers
	 * @param  string $selected_id
	 * @param  array  $additional_options
	 * @return void
	 */
	private function get_amplifiers_dropdown( $engine_type, $amplifiers, $selected_id, $additional_options = array() ) {

		?><select class="os_type"><?php

			if ( ! empty( $additional_options ) ) {
				$selected_id = null;
				foreach ( $additional_options as $key => $value ) {
					?><option value="<?php echo $key ?>" selected="selected"><?php echo $value ?></option><?php
				}
			}

			// Hint: Contextual amplifiers need to support in general mode (non-context) the current engine type in order to show up properly.
			foreach ( $amplifiers as $amplifier_id => $amplifier ) {
				?><option value="<?php echo $amplifier_id ?>" <?php echo $amplifier_id === $selected_id ? 'selected="selected"' : ''; ?>><?php
					echo $amplifier->get_title();
				?></option><?php
			}
		?></select><?php
	}

	/**
	 * Print amplifiers JS templates in footer.
	 */
	public function print_amplifiers_field_scripts() {

		// Get admin screen ID.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( in_array( $screen_id, array( 'edit-prl_engine', 'prl_engine' ) ) ) {
			$this->print_js_templates();
		}
	}

	/**
	 * Prints JS condition templates in footer.
	 *
	 * @param  string $scope
	 * @return void
	 */
	private function print_js_templates() {

		$types = wc_prl_get_engine_types();

		foreach ( $types as $type => $label ) {

			$amplifiers = $this->get_supported_amplifiers( $type );
			?>

			<script type="text/template" id="tmpl-wc_prl_engine_<?php echo $type ?>_amplifier_row">
				<div class="os_row" data-os_index="{{{ data.os_index }}}">
					<div class="os_select">
						<div class="sw-enhanced-select"><?php
							$this->get_amplifiers_dropdown( $type, $amplifiers, '' );
						?></div>
					</div>
					<div class="os_content">
						{{{ data.os_content }}}
					</div>
					<div class="os_remove column-wc_actions">
						<a href="#" class="button wc-action-button trash help_tip" data-tip="<?php echo __( 'Remove', 'woocommerce-product-recommendations' ) ?>"></a>
					</div>
				</div>
			</script>

			<script type="text/template" id="tmpl-wc_prl_engine_<?php echo $type ?>_amplifier_add_content">
				<div class="os_select">
					<div class="sw-enhanced-select">
						<?php $this->get_amplifiers_dropdown( $type, $amplifiers, null, [ 'add' => __( 'Add amplifier', 'woocommerce-product-recommendations' ) ] ); ?>
					</div>
				</div>
				<div class="os_content">
					<div class="os--placeholder os--disabled"></div>
				</div>
				<div class="os_remove">
				</div>
			</script>

			<?php
			foreach ( $amplifiers as $amp_id => $amplifier ) {
				// Generating for {{{ data.os_content }}}.
				?><script type="text/template" id="tmpl-wc_prl_engine_<?php echo $type ?>_amplifier_<?php echo esc_attr( $amp_id ); ?>_content"><?php

				$amplifier->get_admin_fields_html( null, '{{{ data.os_index }}}', array() );

				?></script><?php
			}
		}
	}

	/**
	 * Compare amplifiers alphabetically.
	 *
	 * @since 1.4.0
	 *
	 * @param  WC_PRL_Amplifier $a
	 * @param  WC_PRL_Amplifier $b
	 * @return bool
	 */
	private function compare_alphabetically( $a, $b ) {
		return strcmp( $a->get_title(), $b->get_title() );
	}
}
