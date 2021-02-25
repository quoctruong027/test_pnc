<?php
/**
 * Class for ugrading Database for Smart Offers
 *
 * @since       3.13.0
 * @version     1.0.0
 * @package     Smart Offers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_DB_Update' ) ) {

	/**
	 * Class for ugrading Ddatabase of Affiliate For WooCommerce
	 */
	class SO_DB_Update {

		/**
		 * Variable to hold instance of this class
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of this class
		 *
		 * @return SO_DB_Update Singleton object of this class
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'sa_so_db_update' ) );
		}

		/**
		 * Inititalize database upgrades.
		 * Will only have one entry point to run all upgrades.
		 * Version 3.2.4 will have to be updated with the last DB version set.
		 */
		public function sa_so_db_update() {

			$current_db_version = get_option( '_current_smart_offers_db_version' );
			if ( version_compare( $current_db_version, '3.2.4', '<' ) || empty( $current_db_version ) ) {
				$this->do_db_upgrade();
			}

		}

		/**
		 * Do the database upgrade
		 */
		public function do_db_upgrade() {
			global $wpdb, $blog_id;

			// For multisite table prefix.
			if ( is_multisite() ) {
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}", 0 );
			} else {
				$blog_ids = array( $blog_id );
			}

			foreach ( $blog_ids as $id ) {
				if ( is_multisite() ) {
					switch_to_blog( $id );
				}

				// All the DB update functions should be called from here since they should run for each blog id.
				if ( false === get_option( '_current_smart_offers_db_version' ) || '' === get_option( '_current_smart_offers_db_version' ) ) {
					$this->upgrade_to_3_1_2();
				}

				if ( '3.1.2' === get_option( '_current_smart_offers_db_version' ) ) {
					$this->upgrade_to_3_2_3();
				}

				if ( '3.2.3' === get_option( '_current_smart_offers_db_version' ) ) {
					$this->upgrade_to_3_2_4();
				}

				if ( is_multisite() ) {
					restore_current_blog();
				}
			}

		}

		/**
		 * Database updation for version 3.1.2 for merging Before Checkout & Checkout.
		 */
		public function upgrade_to_3_1_2() {
			global $wpdb;

			$pre_checkout_page_rule = 'offer_rule_pre_checkout_page';
			$so_page_options        = 'offer_rule_page_options';

			$smart_offers_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key IN ( %s, %s )", $pre_checkout_page_rule, $so_page_options ) );

			if ( empty( $smart_offers_ids ) ) {
				return;
			}

			add_option( 'smart_offers_ids_pre_checkout', $smart_offers_ids, '', 'no' );

			$update_page_result         = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_key = 'offer_rule_checkout_page' WHERE meta_key = %s", $pre_checkout_page_rule ) );
			$update_page_options_result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, 'pre_checkout_page', 'checkout_page') WHERE meta_key = %s", $so_page_options ) );

			if ( $update_page_result !== false && $update_page_options_result !== false ) {
				delete_option( 'smart_offers_ids_pre_checkout' );
			}

			update_option( '_current_smart_offers_db_version', '3.1.2', 'no' );
		}

		/**
		 * Added since v3.2.3 to make buttons compatible for mobile devices.
		 */
		public function upgrade_to_3_2_3() {
			$so_css_for_custom_button = "display: block;
										border-style: groove;
										border-color: #ffab23;
										border-width: 3px 4px 4px 3px;
										height: 50px;
										width: 320px;
										background: #ffec64;
										color: #333;
										line-height: 2;
										text-align: center;
										font-size: 25px;
										margin: auto;
										text-decoration: none;
										font-family: Myriad Pro, Impact, Helvetica, sans-serif;
										font-weight: 800;
										text-shadow: 1px 1px 0px #ffee66;
										border-radius: 9px;";
			$so_css_for_custom_button_default = get_option( 'so_css_for_accept' );

			$so_css_for_custom_button_without_spaces         = preg_replace( '/\s+/', '', $so_css_for_custom_button );
			$so_css_for_custom_button_default_without_spaces = preg_replace( '/\s+/', '', $so_css_for_custom_button_default );

			// updating default button style
			if( $so_css_for_custom_button_without_spaces === $so_css_for_custom_button_default_without_spaces ) {
				$so_new_css_for_custom_button = "display: block;
												border-style: groove;
												border-color: #ffab23;
												border-width: 3px 4px 4px 3px;
												width: 55%;
												background: #ffec64;
												color: #333;
												line-height: 2;
												text-align: center;
												font-size: 1em;
												margin: auto;
												text-decoration: none;
												font-family: Myriad Pro, Impact, Helvetica, sans-serif;
												font-weight: 800;
												text-shadow: 1px 1px 0px #ffee66;
												border-radius: 9px;";
				update_option( 'so_css_for_accept', $so_new_css_for_custom_button, 'no' );
			}

			// updating button style 1 - Persuade.css
			$button_style_1 = "background:hsl(0,0%,26%);
								color:hsl(0,100%,100%);
								text-decoration:none;
								font-weight:400;
								width:55%;
								font-size: 1em;
								border:none;
								-moz-border-radius:.6em;
								-webkit-border-radius:.6em;
								border-radius:.6em;
								border-bottom:.3em solid hsl(0,0%,20%);
								-moz-box-shadow:0 .3em 1.5em rgba(0,0,0,0.6)!important;
								-webkit-box-shadow:0 .3em 1.5em rgba(0,0,0,0.6)!important;
								box-shadow:0 .3em 1.5em rgba(0,0,0,0.6)!important;
								text-align:center;
								margin:.2em auto .5em auto;
								padding:0.4em;
								cursor: pointer;";
			update_option( 'smart_offers_button_style_1', $button_style_1, 'no' );

			// updating button style 2 - Eternal.css
			$button_style_2 = "background: #e74c3c;
								color: hsl(0, 33%, 98%);
								font-weight: 700;
								text-decoration: none;
								font-size: 1em;
								width: 50%;
								text-align: center;
								-moz-box-sizing: content-box;
								box-sizing: content-box;
								margin: 0.5em auto 0.5em auto;
								vertical-align: top;
								padding: 0.8em 0.1em;
								text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
								border: 0;
								border-bottom: 4px solid #BE3427;
								cursor: pointer;";
			update_option( 'smart_offers_button_style_2', $button_style_2, 'no' );

			// updating button style 3 - Peak.css
			$button_style_3 = "background: #936b0c;
								color: hsl(0, 100%, 100%);
								font-size: 1em;
								vertical-align: top;
								font-weight: 700;
								text-align: center;
								border-bottom: 3px solid rgba(0, 0, 0, 0.45);
								-moz-border-radius: 3px;
								-webkit-border-radius: 3px;
								border-radius: 3px;
								text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.5);
								margin: 0.5em auto 0.5em auto;
								width: 40%;
								padding: 0.3em 0.6em;
								cursor: pointer;";
			update_option( 'smart_offers_button_style_3', $button_style_3, 'no' );

			update_option( '_current_smart_offers_db_version', '3.2.3', 'no' );
		}

		/**
		 * Added since v3.13.0 to rename sm options to so.
		 */
		public function upgrade_to_3_2_4() {
			global $wpdb;

			$option_result_show_hidden_items = $wpdb->get_col(
													$wpdb->prepare(
																	"SELECT option_name
																	FROM {$wpdb->prefix}options
																	WHERE option_name = %s",
																	'woo_sm_offer_show_hidden_items'
																)
												);
			if ( ! empty( $option_result_show_hidden_items ) ) {
				$update_option_1_result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}options SET option_name = %s WHERE option_name = %s", 'so_show_hidden_items', 'woo_sm_offer_show_hidden_items' ) );
			}
			$option_result_if_multiple = $wpdb->get_col(
											$wpdb->prepare(
															"SELECT option_name
															FROM {$wpdb->prefix}options
															WHERE option_name = %s",
															'woo_sm_offers_if_multiple'
														)
										);
			if ( ! empty( $option_result_if_multiple ) ) {
				$update_option_2_result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}options SET option_name = %s WHERE option_name = %s", 'so_if_multiple', 'woo_sm_offers_if_multiple' ) );
			}

			update_option( '_current_smart_offers_db_version', '3.2.4', 'no' );
		}
	}
}

SO_DB_Update::get_instance();
