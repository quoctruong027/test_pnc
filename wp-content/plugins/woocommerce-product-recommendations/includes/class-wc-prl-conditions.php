<?php
/**
 * WC_PRL_Conditions class
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
 * Visibility Conditions Collection class.
 *
 * @class    WC_PRL_Conditions
 * @version  1.3.0
 */
class WC_PRL_Conditions {

	/**
	 * Conditions.
	 *
	 * @var array
	 */
	protected $conditions;

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
		add_action( 'init', array( $this, 'instantiate_conditions' ), 9 );

		/*---------------------------------------------------*/
		/*  Print condition JS templates in footer.          */
		/*---------------------------------------------------*/

		add_action( 'admin_footer', array( $this, 'print_conditions_field_scripts' ) );

	}

	public function instantiate_conditions() {

		$load_conditions = apply_filters( 'woocommerce_prl_conditions', array(
			'WC_PRL_Condition_Archive_Category',
			'WC_PRL_Condition_Archive_Tag',
			'WC_PRL_Condition_Cart_Item_Value',
			'WC_PRL_Condition_Cart_Total',
			'WC_PRL_Condition_Cart_Category',
			'WC_PRL_Condition_Order_Category',
			'WC_PRL_Condition_Customer',
			'WC_PRL_Condition_Date',
			'WC_PRL_Condition_Geolocate',
			'WC_PRL_Condition_Order_Item_Value',
			'WC_PRL_Condition_Order_Total',
			'WC_PRL_Condition_Cart_Product',
			'WC_PRL_Condition_Product_ID',
			'WC_PRL_Condition_Product_Category',
			'WC_PRL_Condition_Product_Price',
			'WC_PRL_Condition_Product_Stock_Status',
			'WC_PRL_Condition_Product_Tag',
			'WC_PRL_Condition_Recent_Category',
			'WC_PRL_Condition_Recent_Product',
			'WC_PRL_Condition_Recent_Tag',
			'WC_PRL_Condition_Order_Product',
			'WC_PRL_Condition_Cart_Tag',
			'WC_PRL_Condition_Order_Tag',
		) );

		foreach ( $load_conditions as $condition ) {
			$condition = new $condition();
			$this->conditions[ $condition->get_id() ] = $condition;
		}
	}

	/**
	 * Get condition class by id.
	 *
	 * @param  string  $condition_id
	 * @return WC_PRL_Condition|false
	 */
	public function get_condition( $condition_id ) {

		if ( ! empty( $this->conditions[ $condition_id ] ) ) {
			return $this->conditions[ $condition_id ];
		}

		return false;
	}

	/**
	 * Get all supported conditions.
	 *
	 * @param  string $engine_type
	 * @return array
	 */
	public function get_supported_conditions( $engine_type = '' ) {

		$conditions = array();

		foreach ( $this->conditions as $id => $condition ) {
			if ( $engine_type === '' || $condition->has_engine_type( $engine_type ) ) {
				$conditions[ $id ] = $condition;
			}
		}

		return apply_filters( 'woocommerce_prl_get_supported_conditions', $conditions, $engine_type );
	}

	/**
	 * Get condition fields for admin deploy.
	 *
	 * @param  string $engine_type
	 * @param  array  $options
	 * @return str
	 */
	public function get_admin_conditions_html( $engine_type, $options = array() ) {

		$conditions = $this->get_supported_conditions( $engine_type );

		if ( empty( $conditions ) ) {
			return false;
		}

		$post_name   = isset( $options[ 'post_name' ] ) ? $options[ 'post_name' ] : 'prl_deploy';
		$hide_header = isset( $options[ 'hide_header' ] ) ? (bool)$options[ 'hide_header' ] : false;

		if ( ! $hide_header ) { ?>
			<div class="sw-hr-section">
				<?php
				echo __( 'Visibility conditions', 'woocommerce-product-recommendations' );
				echo wc_help_tip( __( 'Some Locations work with multiple Engine Types. The conditions available here may change depending on the Type of the selected Engine.', 'woocommerce-product-recommendations' ) );
				?>
			</div><?php
		}
		$conditions_data  = empty( $options[ 'conditions' ] ) ? array() : $options[ 'conditions' ];
		$conditions_count = count( $conditions_data );
		?><div id="os-container" class="widefat wc-prl-conditions-container" data-os_count="<?php echo $conditions_count; ?>" data-os_post_name="<?php echo $post_name; ?>">
			<div class="os_boarding<?php echo $conditions_count ? '' : ' active'; ?>">
				<div class="icon">
					<i class="prl-icon prl-eye"></i>
				</div>
				<div class="text"><?php _e( 'Add conditions to control the visibility of these product recommendations.', 'woocommerce-product-recommendations' ); ?></div>
				<div class="text no_engine"><?php _e( 'To add visibility conditions, an Engine must be selected.', 'woocommerce-product-recommendations' ); ?></div>
			</div>
			<div id="os-list"<?php echo $conditions_count ? '' : ' class="hidden"'; ?>><?php

				if ( $conditions_count ) {
					foreach ( $conditions_data as $condition_index => $condition_data ) {

						if ( isset( $condition_data[ 'id' ] ) ) {

							$condition_id = $condition_data[ 'id' ];

							if ( array_key_exists( $condition_id, $conditions ) ) {

								?><div class="os_row" data-os_index="<?php echo $condition_index; ?>">

									<div class="os_select">
										<div class="sw-enhanced-select"><?php
											$this->get_conditions_dropdown( $engine_type, $conditions, $condition_id );
										?></div>
									</div>
									<div class="os_content"><?php
										$conditions[ $condition_id ]->get_admin_fields_html( $post_name, $condition_index, $condition_data );
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
			<div class="os_add os_row<?php echo $conditions_count ? '' : ' os_add--boarding'; ?>">
				<div class="os_select">
					<div class="sw-enhanced-select">
						<?php $this->get_conditions_dropdown( $engine_type, $conditions, null, [ 'add' => __( 'Add condition', 'woocommerce-product-recommendations' ) ] ); ?>
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
	 * Admin filters select dropdown.
	 *
	 * @param  string $engine_type
	 * @param  array  $conditions
	 * @param  string $selected_id
	 * @param  array  $additional_options
	 * @return void
	 */
	private function get_conditions_dropdown( $engine_type, $conditions, $selected_id, $additional_options = array() ) {

		?><select class="os_type"><?php

			if ( ! empty( $additional_options ) ) {
				$selected_id = null;
				foreach ( $additional_options as $key => $value ) {
					?><option value="<?php echo $key ?>" selected="selected"><?php echo $value ?></option><?php
				}
			}

			// Hint: Contextual filters need to support in general mode (non-context) the current engine type in order to show up properly.
			foreach ( $conditions as $condition_id => $condition ) {
				?><option value="<?php echo $condition_id ?>" <?php echo $condition_id === $selected_id ? 'selected="selected"' : ''; ?>><?php
					echo $condition->get_title();
				?></option><?php
			}
		?></select><?php
	}

	/**
	 * Print filter JS templates in footer.
	 */
	public function print_conditions_field_scripts() {

		// Get admin screen ID.
		$section   = isset( $_GET[ 'section' ] ) ? $_GET[ 'section' ] : 'locations_overview';
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( in_array( $screen_id, array( 'woocommerce_page_prl_locations' ) ) && 'locations_overview' != $section ) {
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

			$conditions = $this->get_supported_conditions( $type );
			?>

			<script type="text/template" id="tmpl-wc_prl_engine_<?php echo $type ?>_condition_row">
				<div class="os_row" data-os_index="{{{ data.os_index }}}">
					<div class="os_select">
						<div class="sw-enhanced-select"><?php
							$this->get_conditions_dropdown( $type, $conditions, '' );
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

			<script type="text/template" id="tmpl-wc_prl_engine_<?php echo $type ?>_condition_add_content">
				<div class="os_select">
					<div class="sw-enhanced-select">
						<?php $this->get_conditions_dropdown( $type, $conditions, null, [ 'add' => __( 'Add new condition', 'woocommerce-product-recommendations' ) ] ); ?>
					</div>
				</div>
				<div class="os_content">
					<div class="os--placeholder os--disabled"></div>
				</div>
				<div class="os_remove">
				</div>
			</script>

			<?php
			foreach ( $conditions as $condition_id => $condition ) {
				// Generating for {{{ data.os_content }}}.
				?><script type="text/template" id="tmpl-wc_prl_engine_<?php echo $type ?>_condition_<?php echo esc_attr( $condition_id ); ?>_content"><?php

				$condition->get_admin_fields_html( '{{{ data.os_post_name }}}', '{{{ data.os_index }}}', array() );

				?></script><?php
			}
		}
	}
}
