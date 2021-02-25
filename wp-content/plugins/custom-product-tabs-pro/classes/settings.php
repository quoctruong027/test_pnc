<?php

/**
 * Class YIKES_Custom_Product_Tabs_Pro_Settings.
 */
class YIKES_Custom_Product_Tabs_Pro_Settings {

	/**
	 * Define hooks.
	 */
	public function __construct() {

		// Add our custom settings page.
		add_action( 'admin_menu', array( $this, 'register_settings_subpage' ), 20 );

		// Enqueue scripts & styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );

		// AJAX call to save settings.
		add_action( 'wp_ajax_cptpro_save_settings', array( $this, 'save_settings' ) );

		// Hide our tab title depending on the value of our `hide_tab_title` settings value.
		add_action( 'init', array( $this, 'maybe_hide_tab_title' ), 10 );

		// Re-order custom product tabs. Make sure to run this after the base plugin adds our tabs via the same filter.
		add_filter( 'woocommerce_product_tabs', array( $this, 'tabs' ), 11 );
	}

	/**
	 * Enqueue our scripts and styes.
	 *
	 * @param string $hook The current screen's base.
	 */
	public function enqueue_scripts( $hook ) {

		if ( $hook === 'custom-product-tabs-pro_page_' . YIKES_Custom_Product_Tabs_Pro_Settings_Page ) {

			wp_enqueue_script( 'icheck', YIKES_Custom_Product_Tabs_Pro_URI . 'js/icheck.min.js', array( 'jquery' ), YIKES_Custom_Product_Tabs_Pro_Version, true );
			wp_enqueue_style( 'icheck-flat-blue-styles', YIKES_Custom_Product_Tabs_Pro_URI . 'css/flat/blue.css', array(), YIKES_Custom_Product_Tabs_Pro_Version );
			wp_enqueue_style( 'cptpro-settings-styles', YIKES_Custom_Product_Tabs_Pro_URI . 'css/settings.min.css', array(), YIKES_Custom_Product_Tabs_Pro_Version );
			wp_enqueue_script( 'cptpro-shared-scripts', YIKES_Custom_Product_Tabs_Pro_URI . 'js/shared.min.js', array( 'jquery' ), YIKES_Custom_Product_Tabs_Pro_Version, true );
			wp_enqueue_script( 'cptpro-settings-scripts', YIKES_Custom_Product_Tabs_Pro_URI . 'js/settings.min.js', array( 'icheck' ), YIKES_Custom_Product_Tabs_Pro_Version, true );
			wp_localize_script(
				'cptpro-settings-scripts',
				'settings_data',
				array(
					'save_settings_nonce'       => wp_create_nonce( 'cptpro_save_settings' ),
					'save_settings_action'      => 'cptpro_save_settings',
					'activate_license_action'   => 'cptpro_activate_license',
					'activate_license_nonce'    => wp_create_nonce( 'cptpro_activate_license' ),
					'deactivate_license_action' => 'cptpro_deactivate_license',
					'deactivate_license_nonce'  => wp_create_nonce( 'cptpro_deactivate_license' ),
					'check_license_action'      => 'cptpro_check_license',
					'check_license_nonce'       => wp_create_nonce( 'cptpro_check_license' ),
				)
			);
		}
	}

	/**
	 * Check our settings to decide whether we need to add our tab-title-hiding filter.
	 */
	public function maybe_hide_tab_title() {

		// Get our option.
		$settings = get_option( 'cptpro_settings', array() );

		// If `hide_tab_title` is true, add our filters.
		if ( isset( $settings['hide_tab_title'] ) && $settings['hide_tab_title'] === true ) {

			// Hide our custom tab's title.
			add_filter( 'yikes_woocommerce_custom_repeatable_product_tabs_heading', '__return_false', 99 );

			// Hide the description tab's title.
			add_filter( 'woocommerce_product_description_heading', '__return_false', 99 );

			// Hide the additional information tab's title.
			add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 99 );
		}
	}

	/**
	 * Run all of our front-end tab maniuplation.
	 *
	 * @param array $tabs The WooCommerce tabs array.
	 */
	public function tabs( $tabs ) {

		// Get our option.
		$settings = get_option( 'cptpro_settings', array() );

		if ( isset( $settings['enable_ordering'] ) && $settings['enable_ordering'] === true ) {
			$tabs = $this->apply_tab_order( $tabs, $settings );
		}

		if ( isset( $settings['disable_description'] ) && $settings['disable_description'] === true ) {
			$tabs = $this->disable_description( $tabs );
		}

		if ( isset( $settings['disable_additional_information'] ) && $settings['disable_additional_information'] === true ) {
			$tabs = $this->disable_additional_information( $tabs );
		}

		if ( isset( $settings['disable_reviews'] ) && $settings['disable_reviews'] === true ) {
			$tabs = $this->disable_reviews( $tabs );
		}

		return $tabs;
	}

	/**
	 * Remove the description tab.
	 *
	 * @param array $tabs The WooCommerce tabs array.
	 */
	private function disable_description( $tabs ) {
		global $post;
		$post_id = is_object( $post ) && isset( $post->ID ) ? $post->ID : '';

		// Allow users to enable the description tab for specific products.
		$excluded_product_ids = apply_filters( 'cptpro_disable_description_excluded_product_ids', array(), $post_id );

		if ( isset( $excluded_product_ids[ $post_id ] ) ) {
			return $tabs;
		}

		if ( isset( $tabs['description'] ) ) {
			unset( $tabs['description'] );
		}

		return $tabs;
	}

	/**
	 * Remove the additional information tab.
	 *
	 * @param array $tabs The WooCommerce tabs array.
	 */
	private function disable_additional_information( $tabs ) {
		global $post;
		$post_id = is_object( $post ) && isset( $post->ID ) ? $post->ID : '';

		// Allow users to enable the additional information tab for specific products.
		$excluded_product_ids = apply_filters( 'cptpro_disable_additional_information_excluded_product_ids', array(), $post_id );

		if ( isset( $excluded_product_ids[ $post_id ] ) ) {
			return $tabs;
		}

		if ( isset( $tabs['additional_information'] ) ) {
			unset( $tabs['additional_information'] );
		}

		return $tabs;
	}

	/**
	 * Remove the reviews tab.
	 *
	 * @param array $tabs The WooCommerce tabs array.
	 */
	private function disable_reviews( $tabs ) {
		global $post;
		$post_id = is_object( $post ) && isset( $post->ID ) ? $post->ID : '';

		// Allow users to enable the reviews tab for specific products.
		$excluded_product_ids = apply_filters( 'cptpro_reviews_excluded_product_ids', array(), $post_id );

		if ( isset( $excluded_product_ids[ $post_id ] ) ) {
			return $tabs;
		}

		if ( isset( $tabs['reviews'] ) ) {
			unset( $tabs['reviews'] );
		}

		return $tabs;
	}

	/**
	 * Apply tab order defined in the Saved Tabs section.
	 *
	 * @param array $tabs     The WooCommerce tabs array.
	 * @param array $settings The plugin's settings.
	 */
	private function apply_tab_order( $tabs, $settings ) {
		global $post;
		$post_id = is_object( $post ) && isset( $post->ID ) ? $post->ID : '';

		// Allow users to remove products from being affected by our tab order logic.
		$excluded_product_ids = apply_filters( 'cptpro_tab_reorder_excluded_product_ids', array(), $post_id );

		if ( isset( $excluded_product_ids[ $post_id ] ) ) {
			return $tabs;
		}

		// Set default tab orders.
		if ( isset( $tabs['description'] ) ) {
			$order                           = isset( $settings['description_order'] ) ? $this->get_default_tab_order( $settings['description_order'] ) : 1;
			$tabs['description']['priority'] = apply_filters( 'cptpro_description_tab_priority', $order, $post_id );
		}

		if ( isset( $tabs['additional_information'] ) ) {
			$order                                      = isset( $settings['additional_information_order'] ) ? $this->get_default_tab_order( $settings['additional_information_order'] ) : 2;
			$tabs['additional_information']['priority'] = apply_filters( 'cptpro_additional_information_tab_priority', $order, $post_id );
		}

		if ( isset( $tabs['reviews'] ) ) {
			$order                       = isset( $settings['reviews_order'] ) ? $this->get_default_tab_order( $settings['reviews_order'] ) : 100;
			$tabs['reviews']['priority'] = apply_filters( 'cptpro_reviews_tab_priority', $order, $post_id );
		}

		// Set custom tab orders.
		$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs', array() );

		// Set a base priority so we don't conflict with the default tabs.
		$base_priority = 2;
		$num_tabs      = count( $saved_tabs );

		foreach ( $saved_tabs as $saved_tab ) {

			if ( isset( $tabs[ $saved_tab['tab_slug'] ] ) ) {
				// Set the order as the base priority + the tab's order. If there is no tab order defined, put it at the end.
				$custom_tab_order                           = isset( $saved_tab['tab_order'] ) ? $saved_tab['tab_order'] + $base_priority : $num_tabs + $base_priority;
				$tabs[ $saved_tab['tab_slug'] ]['priority'] = apply_filters( 'cptpro_saved_tab_priority', $custom_tab_order, $post_id, $saved_tab );
			}
		}

		return apply_filters( 'cptpro_tabs_after_reorder', $tabs, $post_id );
	}

	/**
	 * Get a numeric order based on the chosen order.
	 *
	 * @param string $order The order chosen in the dropdown.
	 *
	 * @return int A numerical representation of the chosen order.
	 */
	private function get_default_tab_order( $order ) {
		switch ( $order ) {
			case 'before':
				return 1;
			break;

			case 'after':
				return 50;
			break;

			default:
			case '':
			case 'last':
				return 100;
			break;
		}
	}

	/**
	 * Create the dropdown options for ordering a default tab.
	 *
	 * @param string $selected The selected option.
	 */
	public static function default_tab_order_dropdown( $selected ) {
		$list    = '';
		$options = array(
			'before' => __( 'Before Custom Tabs', 'custom-product-tabs-pro' ),
			'after'  => __( 'After Custom Tabs', 'custom-product-tabs-pro' ),
			'last'   => __( 'Last', 'custom-product-tabs-pro' ),
		);
		foreach ( $options as $key => $option ) {
			$select = $key === $selected ? 'selected="selected"' : '';
			$list  .= "<option value='{$key}' {$select}>{$option}</option>";
		}
		return $list;
	}

	/**
	 * Save our settings [AJAX].
	 */
	public function save_settings() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'cptpro_save_settings', 'nonce', false ) ) {
			wp_send_json_error();
		}

		// Get our option.
		$settings = get_option( 'cptpro_settings', array() );

		// Handle hide tab title.
		$settings['hide_tab_title'] = isset( $_POST['hide_tab_title'] ) && filter_var( wp_unslash( $_POST['hide_tab_title'] ), FILTER_SANITIZE_STRING ) === 'true';

		// Handle Add Tabs to Search.
		$settings['search_wordpress'] = isset( $_POST['search_wordpress'] ) && filter_var( wp_unslash( $_POST['search_wordpress'] ), FILTER_SANITIZE_STRING ) === 'true';

		// Handle Restricting the Search to Products/WooCommerce.
		$settings['search_woo'] = isset( $_POST['search_woo'] ) && filter_var( wp_unslash( $_POST['search_woo'] ), FILTER_SANITIZE_STRING ) === 'true';

		// Should we apply our plugin's custom tab ordering?
		$settings['enable_ordering'] = isset( $_POST['enable_ordering'] ) && filter_var( wp_unslash( $_POST['enable_ordering'] ), FILTER_SANITIZE_STRING ) === 'true';

		// Description tab order.
		$settings['description_order'] = isset( $_POST['description_order'] ) ? filter_var( wp_unslash( $_POST['description_order'] ), FILTER_SANITIZE_STRING ) : 'before';

		// Additional information tab order.
		$settings['additional_information_order'] = isset( $_POST['additional_information_order'] ) ? filter_var( wp_unslash( $_POST['additional_information_order'] ), FILTER_SANITIZE_STRING ) : 'before';

		// Reviews tab order.
		$settings['reviews_order'] = isset( $_POST['reviews_order'] ) ? filter_var( wp_unslash( $_POST['reviews_order'] ), FILTER_SANITIZE_STRING ) : 'last';

		// Should we disable the description tab?
		$settings['disable_description'] = isset( $_POST['disable_description'] ) && filter_var( wp_unslash( $_POST['disable_description'] ), FILTER_SANITIZE_STRING ) === 'true';

		// Should we disable the additional information tab?
		$settings['disable_additional_information'] = isset( $_POST['disable_additional_information'] ) && filter_var( wp_unslash( $_POST['disable_additional_information'] ), FILTER_SANITIZE_STRING ) === 'true';

		// Should we disable the reviews tab?
		$settings['disable_reviews'] = isset( $_POST['disable_reviews'] ) && filter_var( wp_unslash( $_POST['disable_reviews'] ), FILTER_SANITIZE_STRING ) === 'true';

		// Should we disable ssl verify in updates?
		$settings['disable_sslverify'] = isset( $_POST['disable_sslverify'] ) && filter_var( wp_unslash( $_POST['disable_sslverify'] ), FILTER_SANITIZE_STRING ) === 'true';

		// Should we use the_content filter.
		$disable_the_content = isset( $_POST['disable_the_content'] ) ? sanitize_text_field( $_POST['disable_the_content'] ) : 'false';

		update_option( 'yikes_cpt_use_the_content', $disable_the_content );

		update_option( 'cptpro_settings', $settings );

		wp_send_json_success();
	}

	/**
	 * Register our settings page.
	 */
	public function register_settings_subpage() {

		// Add our custom settings page.
		add_submenu_page(
			YIKES_Custom_Product_Tabs_Settings_Page,                               // Parent menu item slug.
			__( 'Settings', 'custom-product-tabs-pro' ),                           // Tab title name (HTML title).
			__( 'Settings', 'custom-product-tabs-pro' ),                           // Menu page name.
			apply_filters( 'cptpro-pro-settings-capability', 'publish_products' ), // Capability required.
			YIKES_Custom_Product_Tabs_Pro_Settings_Page,                           // Page slug (?page=slug-name).
			array( $this, 'settings_page' )                                        // Function to generate page.
		);
	}

	/**
	 * Include our settings page.
	 */
	public function settings_page() {
		require_once YIKES_Custom_Product_Tabs_Pro_Path . 'partials/page-settings.php';
	}
}

new YIKES_Custom_Product_Tabs_Pro_Settings();
