<?php
defined( 'ABSPATH' ) || exit; //Exit if accessed directly

if ( ! class_exists( 'WooFunnels_UpStroke_Subscriptions' ) ) {
	class WooFunnels_UpStroke_Subscriptions {

		public static $instance;

		public function __construct() {

			$this->init_constants();
			$this->init_hooks();
		}

		public function init_constants() {
			define( 'WFOCU_MIN_WFOCU_VERSION', '2.0.0' );
		}

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function init_hooks() {
			add_action( 'plugins_loaded', array( $this, 'add_files' ) );
		}

		public function add_files() {

			if ( false === wfocu_is_woocommerce_active() ) {
				return;
			}

			if ( ! version_compare( WFOCU_VERSION, WFOCU_MIN_WFOCU_VERSION, '>=' ) ) {
				add_action( 'admin_notices', array( $this, 'wfocu_version_check_notice' ) );

				return false;
			}

			include_once plugin_dir_path( __FILE__ ) . '/gateways/class-upstroke-subscriptions-paypal.php';
			include_once plugin_dir_path( __FILE__ ) . '/gateways/class-upstroke-subscriptions-paypal-checkout.php';
			include_once plugin_dir_path( __FILE__ ) . '/gateways/class-upstroke-subscriptions-ppec.php';
			include_once plugin_dir_path( __FILE__ ) . '/gateways/class-upstroke-subscriptions-stripe.php';
			include_once plugin_dir_path( __FILE__ ) . '/gateways/class-upstroke-subscriptions-authorize-cim.php';
			include_once plugin_dir_path( __FILE__ ) . '/gateways/class-upstroke-subscriptions-braintree-credit-card.php';
			include_once plugin_dir_path( __FILE__ ) . '/gateways/class-upstroke-subscriptions-braintree-paypal.php';
			include_once plugin_dir_path( __FILE__ ) . 'class-upstroke-subscriptions.php';
		}

		public function wfocu_version_check_notice() {
			?>
			<div class="error">
				<p>
					<strong><?php esc_html_e( 'Attention', 'woofunnels-upstroke-power-pack' ); ?></strong>
					<?php
					/* translators: %1$s: Min required upstroke version */
					echo sprintf( esc_html__( 'UpStroke Subscriptions requires  WooFunnels UpStroke: One Click Upsell version %1$s or greater. Kindly update the WooFunnels UpStroke: One Click Upsell plugin.', 'woofunnels-upstroke-power-pack' ), esc_attr( WFOCU_MIN_POWERPACK_VERSION ) );
					?>
				</p>
			</div>
			<?php
		}
	}
}

if ( class_exists( 'WooFunnels_UpStroke_Subscriptions' ) ) {
	WooFunnels_UpStroke_Subscriptions::instance();
}
