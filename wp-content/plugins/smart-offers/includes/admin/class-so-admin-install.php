<?php
/**
 * Smart Offers Admin Install
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.0.1
 *
 * @package     smart-offers/includes/admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Admin_Install' ) ) {

	/**
	 * SO_Install Class
	 */
	class SO_Admin_Install {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			$this->install();
		}

		/**
		 * Install SO
		 */
		public function install() {

			// Redirect to welcome screen.
			if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) { // phpcs:ignore
				set_transient( '_so_activation_redirect', 1, 30 );
			}

			$this->create_options();

			SO_Admin_Post_Type::register_post_type();

			// Flush rules after install.
			flush_rewrite_rules();
		}

		/**
		 * Default options
		 *
		 * Sets up the default options used on the settings page
		 *
		 * @access public
		 */
		public function create_options() {

			add_option( 'so_show_hidden_items', 'yes', '', 'no' );
			add_option( 'so_if_multiple', 'high_price', '', 'no' );
			add_option( 'so_max_inline_offer', 2, '', 'no' );
			add_option( 'so_update_quantity', 'no', '', 'no' );

			$so_css_for_accept = 'display: block;
									width: 55%;
									text-align: center;
									font-size: 1.618em;
									margin: 0 auto;
									font-weight: 600;
									border-radius: 5px;
									background-color: #4fad43;
									color: #ffffff;
									padding: 0.3em 1em;';

			$so_css_for_skip = 'text-align: center; margin: auto;';

			add_option( 'so_css_for_accept', $so_css_for_accept, '', 'no' );
			add_option( 'so_css_for_skip', $so_css_for_skip, '', 'no' );

			// Persuade.css.
			$button_style_1 = 'background:hsl(0,0%,26%);        
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
								cursor: pointer;';

			// Eternal.css.
			$button_style_2 = 'background: #e74c3c;
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
								cursor: pointer;';

			// Peak.css.
			$button_style_3 = 'background: #936b0c;
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
								cursor: pointer;';

			add_option( 'smart_offers_button_style_1', $button_style_1, '', 'no' );
			add_option( 'smart_offers_button_style_2', $button_style_2, '', 'no' );
			add_option( 'smart_offers_button_style_3', $button_style_3, '', 'no' );

			$so_accept_button_styles = get_option( 'so_accept_button_styles' );

			if ( false === $so_accept_button_styles ) {

				$so_css_for_accept = get_option( 'so_css_for_accept' );

				if ( ! empty( $so_css_for_accept ) ) {
					add_option( 'so_accept_button_styles', 'smart_offers_custom_style_button', '', 'no' );
				} else {
					add_option( 'so_accept_button_styles', 'smart_offers_button_style_1', '', 'no' );
				}
			}

		}

	}

}

return new SO_Admin_Install();
