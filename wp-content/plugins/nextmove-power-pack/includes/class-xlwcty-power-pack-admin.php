<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XLWCTY_PP_Admin {

	private static $ins = null;

	public function __construct() {
		add_filter( 'XLWCTY_Component_fields', array( $this, 'xlwcty_add_field_to_hide_component_on_order_status' ) );

		/** Add option tab for power pack */
		add_filter( 'xlwcty_setting_option_tabs', array( $this, 'xlwcty_add_setting_menu' ) );
		add_filter( 'xlwcty_section_pages', array( $this, 'xlwcty_add_seting_page' ) );
		add_action( 'xlwcty_add_on_setting-power_pack', array( $this, 'xlwcty_add_global_power_pack_settings' ) );

		add_filter( 'cmb2_init', array( $this, 'xlwcty_add_options_power_pack_metabox' ) );

		add_filter( 'xlwcty_skip_field_for_default_values', array( $this, 'xlwcty_skip_the_field_default_values_for_hidding_order_status' ), 10, 3 );
		add_filter( 'xlwcty_enqueue_scripts', array( $this, 'xlwcty_load_admin_scripts_on_power_pack_settings_page' ), 10, 2 );

		add_filter( 'admin_notices', array( $this, 'maybe_show_advanced_update_notification' ), 999 );
	}

	public static function instance() {
		if ( self::$ins == null ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * @param $components_fields
	 *
	 * @return mixed
	 * Function to add field to hide each component according to the order status
	 */
	public function xlwcty_add_field_to_hide_component_on_order_status( $components_fields ) {

		$order_statuses = XLWCTY_PP_Common::get_wc_order_statuses();

		foreach ( $components_fields as $slug => $component ) {
			foreach ( $component['fields'] as $key => $value ) {
				if ( $slug . '_hide_mobile' == $value['id'] || $slug . '_hide_mobile_{{index}}' == $value['id'] ) {
					if ( isset( $value['after_row'] ) ) {
						unset( $components_fields[ $slug ]['fields'][ $key ]['after_row'] );

						$id        = ( $slug . '_hide_mobile' == $value['id'] ) ? $slug . '_hide_order_status' : $slug . '_hide_order_status_{{index}}';
						$enable_id = ( $slug . '_hide_mobile' == $value['id'] ) ? $slug . '_enable' : $slug . '_enable_{{index}}';

						$components_fields[ $slug ]['fields'][] = array(
							'name'              => __( 'Hide for following Order Status', 'nextmove-power-pack' ),
							'desc'              => __( 'Check order statuses where you want to hide this component.', 'nextmove-power-pack' ),
							'id'                => $id,
							'type'              => 'multicheck_inline',
							'options'           => $order_statuses,
							'row_classes'       => array( 'xlwcty_border_top' ),
							'select_all_button' => false,
							'attributes'        => array(
								'data-conditional-id'    => $enable_id,
								'data-conditional-value' => '1',
							),
							'after_row'         => array( 'XLWCTY_Admin_CMB2_Support', 'cmb_after_row_cb' ),
						);
					}
				}
			}
		}

		return $components_fields;
	}

	/**
	 * @param $setting_tabs
	 *
	 * @return array
	 * Function to add power pack setting menu
	 */
	public function xlwcty_add_setting_menu( $setting_tabs ) {
		$setting_tabs[] = array(
			'link'  => admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you&section=power_pack' ),
			'title' => __( 'Power Pack', 'nextmove-power-pack' ),
			'class' => array( 'xlwcty-a-blue' ),
		);

		return $setting_tabs;
	}

	/**
	 * @param $setting_page
	 *
	 * @return mixed
	 * Function to set power pack setting page
	 */
	public function xlwcty_add_seting_page( $setting_page ) {
		$setting_page['power_pack'] = 'power_pack';

		return $setting_page;
	}

	/**
	 * Function to display power pack settings
	 */
	public function xlwcty_add_global_power_pack_settings() {
		include __DIR__ . '/class-xlwcty-power-pack-table.php';
	}

	/**
	 * Function to setup new power pack cmb2 metabox
	 */
	public function xlwcty_add_options_power_pack_metabox() {
		$box_options_global         = array(
			'id'      => 'xlwcty_power_pack_settings',
			'title'   => __( 'Power Pack Settings', 'nextmove-power-pack' ),
			'classes' => 'xlwcty_options_common',
			'hookup'  => false,
			'show_on' => array(
				'key'   => 'options-page',
				'value' => array( 'xlwcty' ),
			),
		);
		$cmb2_builder_fields_global = new_cmb2_box( $box_options_global );

		$get_fields = include XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/cmb2-settings-config.php';
		foreach ( $get_fields as $field ) {
			$cmb2_builder_fields_global->add_field( $this->settings_add_default_value( $field ) );
		}
	}

	/**
	 * @param $field
	 *
	 * @return mixed
	 * Set default values for the given power pack fields
	 */
	public function settings_add_default_value( $field ) {
		$get_defaults = XLWCTY_PP_Common::get_options_defaults();
		if ( array_key_exists( $field['id'], $get_defaults ) ) {
			$field['default'] = $get_defaults[ $field['id'] ];
		}

		return $field;
	}

	/**
	 * @param $flag
	 * @param $slug
	 * @param $field
	 *
	 * @return bool
	 * Function to skip the default value for hiding the component according to order status
	 */
	public function xlwcty_skip_the_field_default_values_for_hidding_order_status( $flag, $slug, $field ) {
		if ( $slug . '_hide_order_status' == $field['id'] ) {
			return true;
		}

		return $flag;
	}

	/**
	 * @param $flag
	 * @param $cur_screen
	 *
	 * @return bool
	 * Function to add power pack settings page in the load admin script function in nextmove thank you page
	 */
	public function xlwcty_load_admin_scripts_on_power_pack_settings_page( $flag, $cur_screen ) {
		if ( 'power_pack_settings' == $cur_screen ) {
			$screen       = get_current_screen();
			$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );

			if ( is_object( $screen ) && ( $screen->base == $wc_screen_id . '_page_wc-settings' && isset( $_GET['tab'] ) && 'xl-thank-you' == $_GET['tab'] ) && filter_input( INPUT_GET, 'section' ) == 'power_pack' ) {
				return true;
			}
		}

		return $flag;
	}

	/**
	 * Check the screen and check if plugins update available to show notification to the admin to update the plugin
	 */
	public function maybe_show_advanced_update_notification() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && ( 'plugins.php' == $screen->parent_file || 'index.php' == $screen->parent_file || 'xl-thank-you' == filter_input( INPUT_GET, 'tab' ) ) ) {
			$plugins = get_site_transient( 'update_plugins' );
			if ( isset( $plugins->response ) && is_array( $plugins->response ) ) {
				$plugins = array_keys( $plugins->response );
				if ( is_array( $plugins ) && count( $plugins ) > 0 && in_array( XLWCTY_POWER_PACK_PLUGIN_BASENAME, $plugins ) ) {
					?>
                    <div class="notice notice-warning is-dismissible">
                        <p>
							<?php
							_e( sprintf( 'Attention: There is an update available of <strong>%s</strong> plugin. &nbsp;<a href="%s" class="">Go to updates</a>', XLWCTY_POWER_PACK_FULL_NAME, admin_url( 'plugins.php?s=nextmove&plugin_status=all' ) ), XLWCTY_TEXTDOMAIN );
							?>
                        </p>
                    </div>
					<?php
				}
			}
		}
	}

}

XLWCTY_PP_Admin::instance();
