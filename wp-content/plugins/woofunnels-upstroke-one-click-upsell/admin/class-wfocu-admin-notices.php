<?php

class WFOCU_Admin_Notices {

	private static $ins = null;
	public $admin_path;
	public $admin_url;
	public $should_show_shortcodes = null;

	public function __construct() {

		$this->admin_path = WFOCU_PLUGIN_DIR . '/admin';
		$this->admin_url  = WFOCU_PLUGIN_URL . '/admin';

		add_action( 'admin_notices', array( $this, 'maybe_show_notice_for_no_gateways' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_notice_for_paypal_missing_creds' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_notice_on_memory_usage_and_php_version' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_notice_on_pixel_your_site_pro' ) );
		add_action( 'admin_init', array( $this, 'maybe_dismiss_notice' ) );
		add_action( 'admin_init', array( $this, 'maybe_show_notice_on_google_enhanced_pixel_plugin' ) );
		add_action( 'admin_init', array( $this, 'maybe_show_notice_on_wc_membership_below_1_9' ) );
	}

	public function maybe_show_notice_for_no_gateways() {

		if ( WFOCU_Core()->admin->is_upstroke_page() ) {

			$get_gateway_list = WFOCU_Core()->gateways->get_gateways_list();

			if ( empty( $get_gateway_list ) ) {
				$this->no_gateway_notice();
			}
		}

	}

	public function maybe_show_notice_for_paypal_missing_creds() {
		$get_enabled_gateways = WFOCU_Core()->data->get_option( 'gateways' );

		$get_paypal_settings = get_option( 'woocommerce_paypal_settings', [] );

		if ( isset( $get_paypal_settings['enabled'] ) && 'yes' === $get_paypal_settings['enabled'] && is_array( $get_enabled_gateways ) && ( in_array( 'paypal', $get_enabled_gateways ) ) ) {

			$get_integration = WFOCU_Core()->gateways->get_integration( 'paypal' );
			if ( false === $get_integration->has_api_credentials_set() ) {
				$this->paypal_creds_missing_notice();
			}
		}
	}

	public function maybe_show_notice_on_memory_usage_and_php_version() {

		$this->memory = $this->get_system_memory();
		/**
		 * Show notice as memory needs to be greater or equal to 256 mb
		 */
		if ( 268430000 > $this->memory ) {
			?>


            <div class="wfocu-notice notice notice-error">
                <p><?php _e( 'UpStroke Notice: Your PHP memory is running low. We recommend setting memory to at least 256MB. <a target="_blank" href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP">Learn how to increase php memory limit</a>', 'woofunnels-upstroke-one-click-upsell' ); ?>
                </p>

            </div>
			<?php
		}
	}




	public function paypal_creds_missing_notice() {
		?>
        <div class="wfocu-notice notice notice-error">
            <p><?php _e( 'UpStroke Notice: PayPal is inactive and upsell offers won\'t trigger. Please Add Username,Password and Signature in API Credentials section. Go to WooCommerce > Payments > Checkout > PayPal to add these credentials.', 'woofunnels-upstroke-one-click-upsell' ); ?> </p>
            <p>
                <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paypal' ); ?>" class="button"><?php _e( 'Go to PayPal settings', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
            </p>
        </div>
		<?php
	}

	public function no_gateway_notice() {
		?>
        <div class="wfocu-notice notice notice-error">
            <p><?php _e( 'UpStroke Notice: You do not have any gateway(s) enabled in your settings for UpStroke.', 'woofunnels-upstroke-one-click-upsell' ); ?>
                <a target="_blank" href="https://buildwoofunnels.com/docs/upstroke/supported-payment-methods/">Learn more about compatibility with gateways.</a></p>
            <p><a href="<?php echo admin_url( 'admin.php?page=upstroke&tab=settings' ); ?>" class="button"><?php _e( 'Go to settings', 'woofunnels-upstroke-one-click-upsell' ); ?></a></p>
        </div>
		<?php
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function get_system_memory() {
		$memory = wc_let_to_num( WP_MEMORY_LIMIT );
		if ( function_exists( 'memory_get_usage' ) ) {
			$system_memory = wc_let_to_num( @ini_get( 'memory_limit' ) );

			$memory = max( $memory, $system_memory );
		}

		return $memory;
	}

	public function maybe_show_notice_on_pixel_your_site_pro() {

		if ( ( true === WFOCU_Common::plugin_active_check( 'pixelyoursite-pro/pixelyoursite-pro.php' ) || true === WFOCU_Common::plugin_active_check( 'pixelyoursite/facebook-pixel-master.php' ) ) && '' === get_option( 'wfocu_notice_pys_dismissed', '' ) ) {
			$this->pys_notice();
		}
	}

	public function pys_notice() {
		?>
        <div class="wfocu-notice notice notice-error">
            <p><?php _e( 'UpStroke Notice: We notice that you are using PixelYourSite. To avoid duplication of purchase events, <strong>disable the Purchase Event </strong> from PixelYourSite and enable it from UpStroke. Go to Settings > Tracking & Analytics.', 'woofunnels-upstroke-one-click-upsell' ); ?>
                <a target="_blank" href="https://buildwoofunnels.com/docs/upstroke/global-settings/tracking-analytics/"><?php _e( 'Learn more about setting up Facebook pixel tracking.', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
            </p>
            <p>
                <a href="<?php echo admin_url( 'admin.php?page=upstroke&tab=settings' ); ?>" class="button button-primary"><?php _e( 'Go to settings', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
                <a style="padding-left: 10px;" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=upstroke&tab=settings&nid=pys' ), 'wfocu_dismissed_notice' ); ?>" class="button"><?php _e( 'I\'ve already done this', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
            </p>
        </div>
		<?php
	}


	public function maybe_show_notice_on_wc_membership_below_1_9() {
		if ( ( true === WFOCU_Common::plugin_active_check( 'woocommerce-memberships/woocommerce-memberships.php' ) && ( class_exists( 'WC_Memberships' ) && ! class_exists( 'UpStroke_Memeberships' ) && version_compare( WC_Memberships::VERSION, '1.9.0', '<' ) ) ) ) {
			add_action( 'admin_notices', array( $this, 'wc_memberships_notice' ) );

		}
	}


	public function wc_memberships_notice() {
		?>
        <div class="wfocu-notice notice notice-error">
            <p><?php _e( 'UpStroke Notice: We notice that you are using WooCommerce Memberships plugin which is not up to date. To avoid any issues kindly update WooCommerce Memberships to version 1.9.0 or greater. ', 'woofunnels-upstroke-one-click-upsell' ); ?>
            </p>
        </div>
		<?php
	}

	public function maybe_show_notice_on_google_enhanced_pixel_plugin() {
		if ( ( true === WFOCU_Common::plugin_active_check( 'enhanced-e-commerce-for-woocommerce-store/enhanced-ecommerce-google-analytics.php' ) && '' === get_option( 'wfocu_notice_enhancedga_dismissed', '' ) ) ) {
			add_action( 'admin_notices', array( $this, 'enhanced_ga_notice' ) );
		}
	}

	public function enhanced_ga_notice() {
		?>
        <div class="wfocu-notice notice notice-error">
            <p><?php _e( 'UpStroke Notice: We notice that you are using "Enhanced E-commerce for Woocommerce store". To avoid duplication of purchase events, Follow our detailed guide & complete the setup. ', 'woofunnels-upstroke-one-click-upsell' ); ?>
            </p>
            <p>
                <a href="https://buildwoofunnels.com/docs/upstroke/compatibilities/enhanced-ecommerce-google-analytics-plugin/" class="button" target="_blank"><?php _e( 'Read documentation', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
                <a style="padding-left: 10px;" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=upstroke&nid=enhancedga' ), 'wfocu_dismissed_notice' ); ?>" class=""><?php _e( 'Ignore this message', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
            </p>
        </div>
		<?php
	}

	public function maybe_show_notice_on_fb_wooocommerce_plugin() {


		if ( ( true === WFOCU_Common::plugin_active_check( 'facebook-for-woocommerce/facebook-for-woocommerce.php' ) && '' === get_option( 'wfocu_notice_fbwoo_dismissed', '' ) ) ) {
			add_action( 'admin_notices', array( $this, 'fbwooo_notice' ) );

		}
	}

	/**
	 * @todo replace the link with the valid fbwoo link for buildwoofunnels
	 */
	public function fbwooo_notice() {
		?>
        <div class="wfocu-notice notice notice-error">
            <p><?php _e( 'UpStroke Notice: We notice that you are using "Facebook for WooCommerce". To avoid duplication of purchase events, Follow our detailed guide & complete the setup. ', 'woofunnels-upstroke-one-click-upsell' ); ?>
            </p>
            <p>
                <a href="https://buildwoofunnels.com/docs/upstroke/compatibilities/enhanced-ecommerce-google-analytics-plugin/" class="button" target="_blank"><?php _e( 'Read documentation', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
                <a style="padding-left: 10px;" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=upstroke&nid=fbwoo' ), 'wfocu_dismissed_notice' ); ?>" class=""><?php _e( 'Ignore this message', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
            </p>
        </div>
		<?php
	}

	public function maybe_dismiss_notice() {
		if ( isset( $_GET['_wpnonce'] ) && isset( $_GET['nid'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'wfocu_dismissed_notice' ) ) {
			update_option( 'wfocu_notice_' . $_GET['nid'] . '_dismissed', 'yes' );
		}
	}

}

if ( class_exists( 'WFOCU_Core' ) ) {
	WFOCU_Core::register( 'admin_notices', 'WFOCU_Admin_Notices' );
}