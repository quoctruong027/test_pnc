<?php
/**
 * WC_PRL_Settings class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_PRL_Settings' ) ) :

/**
 * WooCommerce Product Recommendations Settings.
 *
 * @class    WC_PRL_Settings
 * @version  1.1.0
 */
class WC_PRL_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'prl_settings';
		$this->label = __( 'Recommendations', 'woocommerce-product-recommendations' );

		// Add settings page.
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		// Output sections.
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		// Output content.
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		// Process + save data.
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		return apply_filters( 'woocommerce_prl_settings', array(

			array(
				'title' => __( 'Settings', 'woocommerce-product-recommendations' ),
				'type'  => 'title',
				'id'    => 'prl_settings_options'
			),

			array(
				'title'         => __( 'Tracking session (hours)', 'woocommerce-product-recommendations' ),
				'desc'          => __( 'Period of visitor inactivity that must elapse before tracking cookies expire. Used by Product Recommendations to record Unique Views for recommendation blocks.', 'woocommerce-product-recommendations' ),
				'id'            => 'wc_prl_shopping_session_interval',
				'default'       => 12,
				'type'          => 'number',
				'custom_attributes' => array(
					'min'  => 1,
					'max'  => 48,
					'step' => 1
				)
			),

			array(
				'title'         => __( 'View tracking threshold (seconds)', 'woocommerce-product-recommendations' ),
				'desc'          => __( 'Controls the time that a recommendation block must remain within the browser\'s viewport before a &quot;view&quot; event will be recorded.', 'woocommerce-product-recommendations' ),
				'id'            => 'wc_prl_view_tracking_threshold',
				'default'       => 2,
				'type'          => 'number',
				'custom_attributes' => array(
					'min'  => 1,
					'max'  => 12,
					'step' => 1
				)
			),

			array(
				'title'         => __( 'Cache regeneration period (hours)', 'woocommerce-product-recommendations' ),
				'desc'          => __( 'Time that must elapse before Product Recommendations will invalidate the content of a cached recommendation block.', 'woocommerce-product-recommendations' ),
				'id'            => 'wc_prl_cache_regeneration_threshold',
				'default'       => 24,
				'type'          => 'number',
				'custom_attributes' => array(
					'min'  => 1,
					'max'  => 168,
					'step' => 1
				)
			),

			array(
				'title'         => __( 'Visible Deployments (per Location)', 'woocommerce-product-recommendations' ),
				'desc'          => __( 'Use this setting to control the maximum number of recommendation blocks that a visitor may see in a single Location.', 'woocommerce-product-recommendations' ),
				'id'            => 'wc_prl_max_location_deployments',
				'default'       => 3,
				'type'          => 'number',
				'custom_attributes' => array(
					'min'  => 1,
					'max'  => 12,
					'step' => 1
				)
			),

			array(
				'title'    => __( 'Deployments rendering', 'woocommerce-product-recommendations' ),
				'desc'     => __( 'Use AJAX', 'woocommerce-product-recommendations' ),
				'id'       => 'wc_prl_render_using_ajax',
				'default'  => 'no',
				'type'     => 'checkbox',
				'desc_tip' => __( 'Recommendation blocks will be rendered asynchronously using AJAX. Must be enabled in order to bypass HTML caching.', 'woocommerce-product-recommendations' ),
			),

			array( 'type' => 'sectionend', 'id' => 'prl_settings_options' ),

		) );
	}
}

endif;

return new WC_PRL_Settings();
