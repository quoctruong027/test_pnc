<?php
/**
 * Smart Offers Admin Notifications
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.1.3
 *
 * @package     smart-offers/includes/admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Admin_Notifications' ) ) {

	/**
	 * Class for handling admin notifications of Smart Offers
	 */
	class SO_Admin_Notifications {

		/**
		 * Constructor
		 */
		public function __construct() {

			add_filter( 'plugin_action_links_' . plugin_basename( SO_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );

			// Update footer text.
			add_filter( 'admin_footer_text', array( $this, 'so_footer_text' ) );
			add_filter( 'update_footer', array( $this, 'so_update_footer_text' ), 99 );

			// Filter to add Quick Help Widget.
			add_filter( 'sa_active_plugins_for_quick_help', array( $this, 'so_active_plugins_for_quick_help' ), 10, 2 );

			add_filter( 'sa_is_page_for_notifications', array( $this, 'is_page_for_notifications' ), 10, 2 );

			add_action( 'admin_notices', array( $this, 'so_bn_upsell' ) );
			add_action( 'admin_init', array( $this, 'so_dismiss_admin_notice' ) );

			$this->may_be_show_sa_in_app_offer();

		}

		/**
		 * Function to add more action on plugins page
		 *
		 * @param array $links Existing links.
		 * @return array $links
		 */
		public function plugin_action_links( $links ) {

			$action_links = array(
				'settings'  => '<a href="' . admin_url( 'edit.php?post_type=smart_offers&page=so-settings' ) . '" title="' . esc_attr( __( 'View Smart Offers Settings', 'smart-offers' ) ) . '">' . __( 'Settings', 'smart-offers' ) . '</a>',
				'need_help' => '<a href="' . esc_url( add_query_arg( array( 'page' => 'so-faqs' ), 'edit.php?post_type=smart_offers' ) ) . '" title="' . __( 'FAQ\'s', 'smart-offers' ) . '">' . __( 'FAQ\'s', 'smart-offers' ) . '</a>',
			);

			return array_merge( $action_links, $links );

		}

		/**
		 * Function to change footer text on Smart Offers pages in admin
		 *
		 * @param  string $so_footer_text Text in footer (left).
		 * @return string $so_footer_text
		 */
		public function so_footer_text( $so_footer_text ) {

			global $post, $pagenow;
			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore

			if ( ! empty( $pagenow ) ) {
				if (
					( is_object( $post ) && 'smart_offers' === $post->post_type )
					||
					( ! empty( $get_page ) && ( 'so-settings' === $get_page || 'so-about' === $get_page || 'so-shortcode' === $get_page || 'so-faqs' === $get_page ) )
				) {
					$so_footer_text = __( 'Thank you for using <span style="color: #5850EC;">Smart Offers</span>. A huge thank you from <span style="color: #5850EC;">StoreApps</span>!', 'smart-offers' );
				}
			}

			return $so_footer_text;

		}

		/**
		 * Function to change footer text on Smart Offers pages in admin
		 *
		 * @param  string $so_text Text in footer (right).
		 * @return string $so_text
		 */
		public function so_update_footer_text( $so_text ) {

			global $post, $pagenow;

			$so_plugin_data     = get_plugin_data( WP_PLUGIN_DIR . '/smart-offers/smart-offers.php' );
			$so_current_version = $so_plugin_data['Version'];
			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore

			if ( ! empty( $pagenow ) ) {
				if (
					( is_object( $post ) && 'smart_offers' === $post->post_type )
					||
					( ! empty( $get_page ) && ( 'so-settings' === $get_page || 'so-about' === $get_page || 'so-shortcode' === $get_page || 'so-faqs' === $get_page ) )
				) {
					/* translators: %s: Current version of Smart Offers */
					$so_text = sprintf( __( 'Smart Offers version: <span style="color: #5850EC;">%s</span></strong>', 'smart-offers' ), $so_current_version );

				}
			}

			return $so_text;

		}

		/**
		 * Check if the page is valid for the notifications (Eg, Quick Help Widget)
		 *
		 * @param  array    $active_plugins Active plugins.
		 * @param  stdclass $upgrader       The upgrader object.
		 * @return array Active plugins
		 */
		public function so_active_plugins_for_quick_help( $active_plugins = array(), $upgrader = null ) {

			global $post;
			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			$get_post_type = ( ! empty( $_GET['post_type'] ) ) ? wc_clean( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore

			if ( ( is_object( $post ) && 'smart_offers' === $post->post_type ) || ( ! empty( $get_post_type ) && ( 'smart_offers' === $get_post_type ) )
				||
				( ! empty( $get_page ) && ( 'so-settings' === $get_page || 'so-about' === $get_page || 'so-shortcode' === $get_page || 'so-faqs' === $get_page ) )
			) {
				$active_plugins['so'] = 'smart-offers';
			} elseif ( array_key_exists( 'so', $active_plugins ) ) {
				unset( $active_plugins['so'] );
			}

			return $active_plugins;
		}

		/**
		 * To determine whether to show notification on a page or not
		 *
		 * @param bool  $bool Whether to show on current page.
		 * @param mixed $upgrader The upgrader object.
		 *
		 * @return bool $bool
		 */
		public function is_page_for_notifications( $bool = false, $upgrader = null ) {

			$active_plugins = apply_filters( 'sa_active_plugins_for_quick_help', array(), $upgrader );
			if ( array_key_exists( $upgrader->sku, $active_plugins ) ) {
				return true;
			}

			return $bool;

		}

		/**
		 * Function to show admin notice on Smart Offers pages in admin
		 */
		public function so_bn_upsell() {
			$current_user = wp_get_current_user();
			if ( ! $current_user->exists() ) {
				return;
			}

			$active_plugins = (array) get_option( 'active_plugins', array() );
			if ( is_multisite() ) {
				$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
			}
			if ( ( in_array( 'woocommerce-buy-now/woocommerce-buy-now.php', $active_plugins, true ) || array_key_exists( 'woocommerce-buy-now/woocommerce-buy-now.php', $active_plugins ) ) ) {
				return;
			}

			$bn_notice_status = get_option( 'so_bn_notice_smart_offers' );
			if ( 'no' === $bn_notice_status ) {
				return;
			}

			global $post, $pagenow;
			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore

			if ( ! empty( $pagenow ) ) {
				if (
					( is_object( $post ) && 'smart_offers' === $post->post_type )
					||
					( ! empty( $get_page ) && ( 'so-settings' === $get_page ) )
				) {
					$so_current_user_display_name = $current_user->display_name;
					if ( empty( $so_current_user_display_name ) ) {
						$so_current_user_display_name = 'there';
					}
					?>
					<style type="text/css" class="so-bn-upsell">
						.so-design-notice {
							width: 58%;
							background-color: #FFF !important;
							padding: 1.25em;
							box-shadow: 0 0 7px 0 rgba(0, 0, 0, .2);
							font-size: 1.1em;
							border: 0.25em solid #5850EC;
							margin: 1em auto 0 auto;
							text-align: center;
						}
						.so-main-headline {
							font-size: 1.7em;
							font-weight: bold;
							padding-bottom: 0.4em;
							color: #5850EC;
						}
						.so-bn-feature {
							font-size: 1.1em;
							line-height: 1em;
							padding: 1em 0;
						}
						.so-sub-content {
							padding-bottom: 1em;
							font-size: 1.2em;
							color: #2d3e50;
							line-height: 1.3em;
						}
						a.so-admin-btn-secondary {
							text-decoration: none;
							color: #aeb3b5;
							float: right;
							font-size: small;
						}
					</style>

					<div class="so-design-notice">
						<div class="so-main-headline"><?php echo esc_html__( 'Do one-click upsells, one-click checkout and boost conversions' ); ?></div>
						<div class="so-bn-feature"><?php echo sprintf( esc_html__( 'Using our %1$1sBuy Now%2$2s plugin!', 'smart-offers' ), '<strong>', '</strong>' ); ?></div>
						<div class="so-sub-content"><?php echo sprintf( esc_html__( 'One-time offer - %1$1s', 'smart-offers' ), '<a href="https://www.storeapps.org/product/woocommerce-buy-now/?coupon-code=bn-annual&utm_source=so&utm_medium=in_app&utm_campaign=bn_pricing_so_notice" target="_blank">' . esc_html__( 'Flat 50% off on Annual License', 'smart-offers' ) . '</a>' ); ?></div>
						<a class="so-admin-btn-secondary" href="?so_dismiss_admin_notice=1&option_name=so_bn_notice"><?php echo esc_html__( 'Hide', 'smart-offers' ); ?></a>
					</div>
					<?php
				}
			}
		}

		/**
		 * Function to dismiss any admin notice in Smart Offers
		 */
		public function so_dismiss_admin_notice() {

			$so_dismiss_admin_notice = ( ! empty( $_GET['so_dismiss_admin_notice'] ) ) ? wc_clean( wp_unslash( $_GET['so_dismiss_admin_notice'] ) ) : ''; // phpcs:ignore
			$so_option_name          = ( ! empty( $_GET['option_name'] ) ) ? wc_clean( wp_unslash( $_GET['option_name'] ) ) : ''; // phpcs:ignore

			if ( ! empty( $so_dismiss_admin_notice ) && '1' === $so_dismiss_admin_notice && ! empty( $so_option_name ) ) {
				if ( strpos( $so_option_name, 'smart_offers_ready_designs_imported' ) !== false ) {
					update_option( $so_option_name, 'no', 'no' );
				} else {
					update_option( $so_option_name . '_smart_offers', 'no', 'no' );
				}
				$referer = wp_get_referer();
				wp_safe_redirect( $referer );
				exit();
			}

		}

		/**
		 * Function to show SA in app offer if any.
		 * Added @since: 3.4.2. Start Date: 26.10.2018
		 */
		public function may_be_show_sa_in_app_offer() {

			$get_post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore

			if ( ! class_exists( 'SA_In_App_Offer' ) ) {
				include_once SA_SO_PLUGIN_DIRPATH . '/sa-includes/class-sa-in-app-offer.php';

				$args = array(
					'file'           => SO_PLUGIN_FILE,
					'prefix'         => 'so',
					'option_name'    => 'sa_offer_halloween_2018',
					'campaign'       => 'sa_halloween_2018',
					'start'          => '2018-10-30',
					'end'            => '2018-11-02',
					'is_plugin_page' => ( 'smart_offers' === $get_post_type ) ? true : false, // WPCS: CSRF ok.
				);

				SA_In_App_Offer::get_instance( $args );
			}
		}

	}

	return new SO_Admin_Notifications();

}
