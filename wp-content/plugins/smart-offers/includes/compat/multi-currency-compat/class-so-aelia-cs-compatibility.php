<?php
/**
 * Compatibility class for Aelia Currency Switcher plugin
 *
 * @category	Class
 * @package		compat/multi-currency-compat
 * @author 		StoreApps
 * @version 	1.0.0
 * @since 		Smart Offers 3.9.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Aelia_CS_Compatibility' ) ) {

	class SO_Aelia_CS_Compatibility {
		// Ref: https://aelia.freshdesk.com/support/solutions/articles/3000048680-3rd-party-plugin-integration-guidelines

		/**
		 *
		 * Modifies the amount for the fixed discount given by the admin in the currency selected.
		 *
		 * @param integer|float $price
		 *
		 * @return float
		 */
		function modify_so_contri_amount( $price, $currency = null ) {
			return $this->get_so_total_in_currency( $price, $currency );
		}

		function get_so_total_in_currency( $price, $to_currency = null, $from_currency = null ) {
			// If source currency is not specified, take the shop's base currency as a default
			if ( empty( $from_currency ) ) {
				$from_currency = get_option( 'woocommerce_currency' );
			}

			// If target currency is not specified, take the active currency as a default.
			// The Currency Switcher sets this currency automatically, based on the context. Other
			// plugins can also override it, based on their own custom criteria, by implementing
			// a filter for the "woocommerce_currency" hook.
			//
			// For example, a subscription plugin may decide that the active currency is the one
			// taken from a previous subscription, because it's processing a renewal, and such
			// renewal should keep the original prices, in the original currency.
			if ( empty( $to_currency ) ) {
				$to_currency = get_woocommerce_currency();
			}

			// Call the currency conversion filter. 
			// Credits: Aelia Team
			return apply_filters( 'wc_aelia_cs_convert', $price, $from_currency, $to_currency );
		}

	}

}

new SO_Aelia_CS_Compatibility();