<?php
/**
 * GDPR Privacy
 *
 * @author      StoreApps
 * @since       3.3.6
 * @version     1.1.0
 * @package     Smart Offers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Abstract_Privacy' ) ) {
	return;
}

if ( ! class_exists( 'SO_Privacy' ) ) {

	/**
	 * Class for showing GDPR content.
	 */
	class SO_Privacy extends WC_Abstract_Privacy {

		/**
		 * Constructor
		 */
		public function __construct() {
			// To show this plugin's privacy message in Privacy Policy Guide page on your admin dashboard.
			parent::__construct( __( 'Smart Offers', 'smart-offers' ) );
		}

		/**
		 * Gets the message of the privacy to display.
		 */
		public function get_privacy_message() {
			$content = '<strong>' . __( 'What we store?', 'smart-offers' ) . '</strong>
							<p>' . __( 'If an offer is shown and accepted or skipped, we save the offer ids.', 'smart-offers' ) . '</p>
							<strong>' . __( 'Where we store?', 'smart-offers' ) . '</strong>
							<ul>
								<li>' . __( 'If you are logged in: We store it in your WooCommerce session.', 'smart-offers' ) . '</li>
								<li>' . __( 'If you are a visitor: We add a cookie in your browser.', 'smart-offers' ) . '</li>
							</ul>
							<strong>' . __( 'Delete Personal Data', 'smart-offers' ) . '</strong>
							<ul>
								<li>' . __( 'For deleting personal data for logged in, logout from your WooCommerce account and close your browser.', 'smart-offers' ) . '</li>
								<li>' . __( 'For deleting personal data of a visitor, a visitor can simply delete cookies from their browser.', 'smart-offers' ) . '</li>
							</ul>';

			return $content;
		}

	}

	return new SO_Privacy();

}
