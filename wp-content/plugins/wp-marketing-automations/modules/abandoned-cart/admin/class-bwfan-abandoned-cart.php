<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFAN_Abandoned_Cart_Admin {

	private static $instance = null;
	public $assets_url = '';
	protected $section = 'analytics';

	private function __construct() {
		$this->section    = isset( $_GET['ab_section'] ) ? trim( sanitize_text_field( $_GET['ab_section'] ) ) : 'analytics'; //phpcs:ignore WordPress.Security.NonceVerification
		$this->assets_url = untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/assets';
		$this->register_wp_tables();

		add_action( 'bwfan_abandoned_cart_admin', [ $this, 'page' ] );
		add_action( 'bwfan_global_setting_page', [ $this, 'register_setting_form' ] );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 99 );

		/** Enable cart tracking from carts tab submit */
		add_action( 'admin_init', array( $this, 'enable_cart_tracking' ) );

		/** Rerun Automation AJAX */
		add_action( 'wp_ajax_bwf_rerun_automations', array( $this, 'rerun_automation_for_cart' ) );
	}

	public function rerun_automation_for_cart() {
		BWFAN_Common::check_nonce();

		if ( ! isset( $_POST['cart_id'] ) || empty( $_POST['cart_id'] ) ) {
			wp_send_json( array(
				'msg'    => 'Cart ID not found',
				'status' => false,
			), 400 );
		}

		/** Status 4: Re-Schedule */
		$data_to_update  = array( 'status' => 4 );
		$where_to_update = array( 'id' => absint( $_POST['cart_id'] ) );
		BWFAN_Model_Abandonedcarts::update( $data_to_update, $where_to_update );

		wp_send_json( array(
			'msg'    => 'Success',
			'status' => true,
		), 200 );
	}

	private function register_wp_tables() {
		if ( 'analytics' !== $this->section && isset( $_GET['bwfan_cart_id'] ) && ! empty( sanitize_text_field( $_GET['bwfan_cart_id'] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			include_once __DIR__ . '/class-bwfan-view-tasks-table.php';
		} elseif ( 'analytics' === $this->section ) {
			include_once __DIR__ . '/class-bwfan-abandoned-cart-analytics.php';
		} elseif ( 'recoverable' === $this->section ) {
			include_once __DIR__ . '/class-bwfan-abandoned-table.php';
		} elseif ( 'recovered' === $this->section ) {
			include_once __DIR__ . '/class-bwfan-recovered-carts-table.php';
		} elseif ( 'lost' === $this->section ) {
			include_once __DIR__ . '/class-bwfan-lost-carts-table.php';
		}
	}

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_menu() {
		$menu = [
			'analytics'   => [
				'name'     => __( 'Analytics', 'wp-marketing-automations' ),
				'template' => __DIR__ . '/views/analytics.php',
			],
			'recoverable' => [
				'name'     => __( 'Recoverable Carts', 'wp-marketing-automations' ),
				'template' => __DIR__ . '/views/recoverable.php',
			],
			'recovered'   => [
				'name'     => __( 'Recovered Carts', 'wp-marketing-automations' ),
				'template' => __DIR__ . '/views/recovered.php',
			],
			'lost'        => [
				'name'     => __( 'Lost Carts', 'wp-marketing-automations' ),
				'template' => __DIR__ . '/views/lost.php',
			],
		];

		return $menu;
	}

	public function admin_enqueue_assets() {
		$global_settings = BWFAN_Common::get_global_settings();

		/** Abandonment is disabled */
		if ( empty( $global_settings['bwfan_ab_enable'] ) ) {
			return;
		}

		if ( ! isset( $_GET['tab'] ) || 'carts' !== sanitize_text_field( $_GET['tab'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( 'analytics' === $this->section ) {
			/** CSS */
			wp_enqueue_style( 'bwfan-abandoned-css', $this->assets_url . '/css/admin.css', array(), BWFAN_VERSION_DEV );

			/** JS */
			wp_enqueue_script( 'bwfan-abandoned-chart-js', $this->assets_url . '/js/chart.min.js', array(), BWFAN_VERSION_DEV, true );
		}

		wp_enqueue_script( 'bwfan-abandoned-admin-js', $this->assets_url . '/js/admin.js', array(), BWFAN_VERSION_DEV, true );

		/** Localizing re-run automation vars */
		$data = array(
			'rerun_automation_loading_label'    => __( 'Loading...', 'wp-marketing-automations' ),
			'rerun_automation_loading_text'     => __( 'Please wait', 'wp-marketing-automations' ),
			'rerun_automation_successful_label' => __( 'Automations will re-run on this cart.', 'wp-marketing-automations' ),
		);
		wp_localize_script( 'bwfan-abandoned-admin-js', 'bwfan_ab_carts_data', $data );
	}

	public function page( $section = '' ) {
		if ( '' !== $section ) {
			$this->section = $section;
		}

		if ( 'analytics' !== $this->section && isset( $_GET['bwfan_cart_id'] ) && ! empty( sanitize_text_field( $_GET['bwfan_cart_id'] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			include __DIR__ . '/views/view-tasks.php';
		} else {
			include __DIR__ . '/views/view.php';
		}
	}

	public function register_setting_form( $global_settings ) {
		$user_roles     = array();
		$editable_roles = get_editable_roles();

		if ( $editable_roles ) {
			foreach ( $editable_roles as $role => $details ) {
				$name                = translate_user_role( $details['name'] );
				$user_roles[ $role ] = $name;
			}
		}

		include __DIR__ . '/views/setting.php';
	}

	public function enable_cart_tracking() {
		$nonce = ( isset( $_GET['cart_nonce'] ) ) ? sanitize_text_field( $_GET['cart_nonce'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
		if ( ! isset( $_GET['enable_tracking'] ) || empty( $nonce ) || 'cart' !== $_GET['enable_tracking'] || ! wp_verify_nonce( $nonce, 'bwfan_tab_cart_tracking_enable' ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$redirect_link = add_query_arg( [
			'page' => 'autonami',
			'tab'  => 'settings',
		], admin_url( 'admin.php' ) );

		wp_safe_redirect( $redirect_link, 302 );

		exit();
	}

}

BWFAN_Abandoned_Cart_Admin::get_instance();
