<?php

class WFOCU_Compatibility_With_WC_Memberships {

	public function __construct() {
		add_action( 'plugins_loaded', function () {
			if ( $this->is_enable() ) {
				/**
				 * Create New Membership when offer is accepted by guest user
				 */
				add_action( 'wfocu_offer_accepted_and_processed', array( $this, 'maybe_create_new_membership' ), 999, 5 );


				/**
				 * It is important to remove this action to make sure that while creating new order wc-membership  only processes the order when subscription got attached with the order.
				 */
				add_action( 'wfocu_front_create_new_order_on_success', function () {
					$plans = wc_memberships()->get_plans_instance();
					remove_action( 'woocommerce_order_status_completed', [ $plans, 'grant_access_to_membership_from_order' ], 9 );
					remove_action( 'woocommerce_order_status_processing', [ $plans, 'grant_access_to_membership_from_order' ], 9 );
				}, 1 );
				add_action( 'wfocu_front_create_new_order_on_success', function () {
					$plans = wc_memberships()->get_plans_instance();
					add_action( 'woocommerce_order_status_completed', [ $plans, 'grant_access_to_membership_from_order' ], 9 );
					add_action( 'woocommerce_order_status_processing', [ $plans, 'grant_access_to_membership_from_order' ], 9 );
				}, 20 );


				/**
				 * Handle when primary order gets cancelled the subscription dates should not update the membership
				 */
				add_action( 'wfocu_front_primary_order_cancelled', function () {
					$integration = wc_memberships()->get_integrations_instance()->get_subscriptions_instance();
					add_action( 'woocommerce_subscription_date_updated', array( $integration, 'update_related_membership_dates' ), 10, 3 );
				}, 1 );
				add_action( 'wfocu_front_primary_order_cancelled', function () {
					$integration = wc_memberships()->get_integrations_instance()->get_subscriptions_instance();

					add_action( 'woocommerce_subscription_date_updated', array( $integration, 'update_related_membership_dates' ), 10, 3 );
				}, 20 );


				/**
				 * prevent WC membership to proess any access to the order already waiting to refund in WooCommerce.
				 */
				add_filter( 'wc_memberships_grant_access_from_new_purchase', function ( $should_grant, $args ) {
					if ( ! isset( $args['order_id'] ) ) {
						return $should_grant;
					}

					$get_order = wc_get_order( $args['order_id'] );

					$if_pending_refund = $get_order->get_meta( '_wfocu_pending_refund', true );

					if ( 'yes' === $if_pending_refund ) {
						return false;
					}

					return $should_grant;
				}, 999, 2 );
			}
		} );

	}

	public function is_enable() {

		return class_exists( 'WC_Memberships' ) && ! class_exists( 'UpStroke_Memeberships' ) && version_compare( WC_Memberships::VERSION, '1.9.0', '>=' );
	}


	/**
	 * @param $get_offer_id
	 * @param $get_package
	 * @param $get_parent_order
	 * @param $new_order
	 * @param $get_transaction_id
	 *
	 * @throws WC_Data_Exception
	 */
	public function maybe_create_new_membership( $get_offer_id, $get_package, $get_parent_order, $new_order, $get_transaction_id ) {

		/**
		 * creation of a new order
		 */
		$membership_order = $get_parent_order;
		if ( $new_order instanceof WC_Order ) {
			$membership_order = $new_order;
		}


		foreach ( $get_package['products'] as $product ) {

			$get_product = $product['data'];

			if ( ! is_user_logged_in() ) {

				$has_membership_product = $this->offer_contains_membership_product( $get_product );


				if ( $has_membership_product ) {

					$user_id = $this->create_new_customer( WFOCU_WC_Compatibility::get_order_data( $membership_order, 'billing_email' ) );
					$membership_order->set_customer_id( $user_id );
					$membership_order->save();

					WFOCU_Core()->log->log( "A new user is created and logged in for offer id: $get_offer_id with product_id: " . print_r( $get_product->get_id(), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
					break;
				}
			}
		}
		if ( class_exists( 'WC_Memberships_Membership_Plans' ) ) {
			$plans = wc_memberships()->get_plans_instance();


			if ( $new_order instanceof WC_Order ) {
				$plans->grant_access_to_membership_from_order( $new_order );
			} else {
				$plans->grant_access_to_membership_from_order( $get_parent_order );
			}
		}
	}

	/**
	 * Iterate over the offer products & check if the offer contains any subscription products
	 *
	 * @param $offer_build_products
	 *
	 * @return boolean
	 */
	public function offer_contains_membership_product( $offer_build_product ) {

		if ( class_exists( 'WC_Memberships_Membership_Plans' ) ) {
			$plans = wc_memberships()->get_plans_instance();

			$membership_plans = $plans->get_available_membership_plans();

			// loop over all available membership plans
			foreach ( $membership_plans as $plan ) {

				// skip if no products grant access to this plan
				if ( ! $plan->has_products() ) {
					continue;
				}

				if ( $plan->has_product( $offer_build_product->get_id() ) ) {
					return true;
				}
			}
		}

		return false;
	}

	public function create_new_customer( $email ) {

		if ( empty( $email ) ) {
			return false;
		}

		/**
		 * Try to get the user by the email provided, if present then process as user ID exists.
		 */
		$maybe_user = get_user_by( 'email', $email );
		if ( $maybe_user instanceof WP_User ) {
			return $maybe_user->ID;
		}
		$username = sanitize_user( current( explode( '@', $email ) ), true );

		// username has to be unique
		$append     = 1;
		$o_username = $username;

		while ( username_exists( $username ) ) {
			$username = $o_username . $append;

			++ $append;
		}

		$password = wp_generate_password();


		// Use WP_Error to handle registration errors.
		$errors = new WP_Error();

		do_action( 'woocommerce_register_post', $username, $email, $errors );

		$errors = apply_filters( 'woocommerce_registration_errors', $errors, $username, $email );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		$new_customer_data = apply_filters( 'woocommerce_new_customer_data', array(
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => $email,
			'role'       => 'customer',
		) );

		$customer_id = wp_insert_user( $new_customer_data );

		if ( is_wp_error( $customer_id ) ) {
			return $customer_id;
		}

		do_action( 'woocommerce_created_customer', $customer_id, $new_customer_data, true );

		if ( ! empty( $customer_id ) ) {
			wp_set_current_user( $customer_id, $username );

			wc_set_customer_auth_cookie( $customer_id );
		}

		return $customer_id;

	}


}


WFOCU_Plugin_Compatibilities::register( new WFOCU_Compatibility_With_WC_Memberships(), 'memberships' );
