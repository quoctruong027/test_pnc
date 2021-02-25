<?php
/**
 * Smart Offers Subscription Compat
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.1.0
 * @package     Smart Offers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SO_Subscription' ) ) {

	class SO_Subscription {

		public function __construct() {

			add_filter( 'so_link_args', array( $this, 'add_subscription_args' ), 10, 3 );
			add_filter( 'valid_product_types_for_known_ids', array( $this, 'add_subscription_product_types' ) );
			add_filter( 'valid_product_types_for_unknown_ids', array( $this, 'add_subscription_product_types' ) );

		}

		/**
		* Function to check WooCommerce Subscription version
		* 
		* @param string $version
		* @return bool whether passed version is greater than or equal to current version of WooCommerce Subscription
		*/
		public function is_wcs_gte( $version = null ) {
			if ( $version === null ) return false;
			if ( ! class_exists( 'WC_Subscriptions' ) || empty( WC_Subscriptions::$version ) ) return false;
			return version_compare( WC_Subscriptions::$version, $version, '>=' );
		}

		/**
		* Switch subscription specific
		*/
		public function add_subscription_args( $args, $offer_id, $action ) {
			global $sa_smart_offers;

			if ( empty( $args ) || $action == 'skip' ) return $args;

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id );

			// If switch subscription is disabled on store, return $args (as we don't need further actions)
			$subscriptions_allow_switching = get_option( 'woocommerce_subscriptions_allow_switching', 'no' );
			if( $subscriptions_allow_switching == 'no' ) return $args;

			// If user has bought rule is not set in that offer, return $args
			$offer_rule_has_bought = get_post_meta( $offer_id, 'offer_rule_has_bought', true );
			if( empty( $offer_rule_has_bought ) ) return $args;
			
			$so_offers = new SO_Offers();
			$user_details = $so_offers->get_user_details();
			$user_has_bought = ( !empty( $user_details['offer_rule_has_bought'] ) ) ? explode( ',', $user_details['offer_rule_has_bought'] ) : array();

			if ( $this->is_wcs_gte( '2.0.0' ) ) {
				$target_product_ids = get_post_meta( $offer_id, 'target_product_ids', true );
				$product = wc_get_product($target_product_ids);
				if ( $product instanceof WC_Product ) {
					$product_type = $product->get_type();
				}

				if( $product_type == 'subscription_variation' ) {

					$subscriptions = wcs_get_users_subscriptions();
					$preserve_keys = true;
					$subscriptions = array_reverse( $subscriptions, $preserve_keys );

					foreach ( $subscriptions as $subscription_key => $subscription ) {
						$subscription_items = $subscription->get_items();
						foreach ($subscription_items as $key => $value) {
							$subscription_line_item = $key;
						}
						$order = $subscription->order;
						$product_items = $order->get_items();
						foreach ( $product_items as $item ) {
							$subscription_product_id = $item['product_id'];
							$subscription_status = $subscription->get_status();
							if ( in_array( $subscription_product_id, $user_has_bought ) && 'wc-active' == $subscription_status ) {
								$args['switch-subscription'] = $subscription_key;					// new since Subscription v2.0+ as switch needs all this params
								$args['item'] = $subscription_line_item;
								$wcsnonce = wp_create_nonce( 'wcs_switch_request' );
								$args['_wcsnonce'] = $wcsnonce;
								$args['auto-switch'] = 'true';
								break;
							}
						}
					}
					return $args;
				} else {						// For all other product types, simply return $args
					return $args;
				}
			} else {
				$subscriptions = WC_Subscriptions_Manager::get_users_subscriptions();

				$preserve_keys = true;
				$subscriptions = array_reverse( $subscriptions, $preserve_keys );

				foreach ( $subscriptions as $subscription_key => $subscription ) {
					if ( in_array( $subscription['product_id'], $user_has_bought ) && 'active' == $subscription['status'] ) {
						$args['switch-subscription'] = $subscription_key;
						$args['auto-switch'] = 'true';
						break;
					}
				}
				return $args;
			}
		}

		public function add_subscription_product_types( $product_types = array() ) {

			$current_filter = current_filter();

			if ( $current_filter == 'valid_product_types_for_known_ids' ) {
				$product_types[] = 'subscription_variation';
			} elseif ( $current_filter == 'valid_product_types_for_unknown_ids' ) {
				$product_types[] = 'variable-subscription';
			}

			return $product_types;

		}

	}

}

return new SO_Subscription();
