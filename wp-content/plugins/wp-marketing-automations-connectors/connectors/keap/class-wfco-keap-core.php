<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WFCO_Keap {

	/**
	 * @var WFCO_Keap
	 */
	public static $_instance = null;

	/**
	 * @var WFCO_Keap_API
	 */
	public $api = null;

	/**
	 * @var WFCO_Keap_WC_Mapper
	 */
	public $wc_mapper = null;

	private function __construct() {
		$this->sync = true;

		/**
		 * Load important variables and constants
		 */
		$this->define_plugin_properties();

		/**
		 * Loads common file
		 */
		$this->load_commons();
	}

	/**
	 * Defining constants
	 */
	public function define_plugin_properties() {
		define( 'WFCO_KEAP_VERSION', '1.0.1' );
		define( 'WFCO_KEAP_FULL_NAME', 'Autonami Marketing Automations Connectors: Keap Addon' );
		define( 'WFCO_KEAP_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_KEAP_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_KEAP_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_KEAP_PLUGIN_FILE ) ) );
		define( 'WFCO_KEAP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WFCO_KEAP_MAIN', 'autonami-automations-connectors' );
		define( 'WFCO_KEAP_ENCODE', sha1( WFCO_KEAP_PLUGIN_BASENAME ) );
	}

	/**
	 * Load common hooks
	 */
	public function load_commons() {
		$this->init_keap();

		$saved_connectors = WFCO_Common::$connectors_saved_data;

		if ( ! array_key_exists( 'bwfco_keap', $saved_connectors ) ) {
			return;
		}

		/**  adding product meta setting for mapping keap product */
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'bwfan_autonami_product_mapping_tab' ], 999, 1 );
		add_filter( 'woocommerce_product_data_panels', [ $this, 'bwfan_autonami_product_tab_content' ] );
		add_action( 'woocommerce_process_product_meta_simple', [ $this, 'save_bwfan_autonami_fields' ] );
		add_action( 'woocommerce_process_product_meta_variable', [ $this, 'save_bwfan_autonami_fields' ] );
	}

	public function init_keap() {
		require WFCO_KEAP_PLUGIN_DIR . '/includes/class-wfco-keap-call.php';
		require WFCO_KEAP_PLUGIN_DIR . '/includes/class-wfco-keap-common.php';
	}

	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function load_keap_api() {
		$this->api = WFCO_Keap_API::get_instance();
	}

	public function load_keap_mapper() {
		$this->wc_mapper = WFCO_Keap_WC_Mapper::get_instance();
	}

	/** create custom product tab for mapping keap product
	 *
	 * @param $product_tab
	 *
	 * @return mixed
	 */
	public function bwfan_autonami_product_mapping_tab( $product_tab ) {
		$product_tab['bwfan_autonami'] = array(
			'label'  => __( 'Autonami', 'woocommerce' ),
			'target' => 'bwfan_autonami',
			'class'  => array(),
		);

		return $product_tab;
	}

	/**
	 * creating autonami product settings
	 */
	public function bwfan_autonami_product_tab_content() {
		global $post;
		$settings    = WFCO_Keap_Common::get_keap_settings();
		$products    = isset( $settings['products'] ) ? $settings['products'] : array();
		$value       = get_post_meta( $post->ID, 'bwfan_keap_product_id', true );
		$products[0] = __( 'Choose Product', 'wp-marketing-automations' );
		ksort( $products );
		?>
        <div id='bwfan_autonami' class='panel woocommerce_options_panel'><?php

		?>
        <div class='options_group'><?php

			woocommerce_wp_select( array(
				'id'      => 'bwfan_keap_product_id',
				'label'   => __( 'Keap Product', 'woocommerce' ),
				'options' => $products,
				'value'   => $value,
			) );

			?></div>

        </div><?php
	}

	/**
	 * Save the custom fields.
	 */
	function save_bwfan_autonami_fields( $post_id ) {
		if ( isset( $_POST['bwfan_keap_product_id'] ) && ! empty( $_POST['bwfan_keap_product_id'] ) ) {
			update_post_meta( $post_id, 'bwfan_keap_product_id', $_POST['bwfan_keap_product_id'] );
		}
	}

}

if ( ! function_exists( 'WFCO_Keap_Core' ) ) {

	/**
	 * Global Common function to load all the classes
	 * @return WFCO_Keap
	 */
	function WFCO_Keap_Core() {  //@codingStandardsIgnoreLine
		return WFCO_Keap::get_instance();
	}
}

WFCO_Keap_Core();
