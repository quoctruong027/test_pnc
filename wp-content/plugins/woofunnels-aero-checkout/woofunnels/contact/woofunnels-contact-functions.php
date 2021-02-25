<?php
/**
 * Contact functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Providing a contact object
 *
 * @param $email
 * @param $wp_id
 *
 * @return WooFunnels_Contact|WooFunnels_Customer
 */
if ( ! function_exists( 'bwf_get_contact' ) ) {
	function bwf_get_contact( $wp_id, $email ) {
		$uid          = md5( $email . $wp_id );
		$bwf_contacts = BWF_Contacts::get_instance();

		if ( isset( $bwf_contacts->contact_objs[ $uid ] ) ) {
			return $bwf_contacts->contact_objs[ $uid ];
		}

		$woofunnel_contact = new WooFunnels_Contact( $wp_id, $email );

		if ( ! empty( $uid ) && ! isset( $bwf_contacts->contact_objs[ $uid ] ) ) {
			$bwf_contacts->contact_objs[ $uid ] = $woofunnel_contact;
		}

		return $woofunnel_contact;
	}
}

/**
 * Creating updating contact and customer table
 *
 * @param $order_id
 * @param array $products
 * @param int $total
 * @param bool $force
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @return int
 *
 */
if ( ! function_exists( 'bwf_create_update_contact' ) ) {
	function bwf_create_update_contact( $order_id, $products, $total, $force ) {
		$order = wc_get_order( $order_id );

		$wp_id    = $order->get_customer_id();
		$wp_email = $wp_fname = $wp_lname = '';

		if ( $wp_id > 0 ) {
			$wp_user  = get_user_by( 'id', $wp_id );
			$wp_email = $wp_user->user_email;
			$wp_fname = $wp_user->user_firstname;
			$wp_lname = $wp_user->user_lastname;
		}

		$email       = empty( $wp_email ) ? $order->get_billing_email() : $wp_email;
		$bwf_contact = bwf_get_contact( $wp_id, $email );

		/**
		 * Check if any new temp is stored in the meta, if it is then destroy this meta right away.
		 */
		$new_order_temp = $bwf_contact->get_meta( 'new_order_temp' );
		if ( $bwf_contact instanceof WooFunnels_Contact && ! empty( $new_order_temp ) && is_array( $new_order_temp ) && isset( $new_order_temp[ $order_id ] ) ) {
			unset( $new_order_temp[ $order_id ] );

			if ( count( $new_order_temp ) > 0 ) {
				$bwf_contact->update_meta( 'new_order_temp', $new_order_temp );
			} else {
				$bwf_contact->delete_meta( 'new_order_temp' );
			}
			$bwf_contacts = BWF_Contacts::get_instance();
			$bwf_contacts->destroy_object( $bwf_contact );
			$bwf_contact = bwf_get_contact( $wp_id, $email );
		}

		$bwf_email = isset( $bwf_contact->db_contact->email ) ? $bwf_contact->db_contact->email : '';
		$bwf_wpid  = isset( $bwf_contact->db_contact->wpid ) ? $bwf_contact->db_contact->wpid : 0;

		if ( $wp_id > 0 && ( $bwf_wpid !== $wp_id ) ) {
			$bwf_contact->set_wpid( $wp_id );
		}

		if ( ( empty( $bwf_email ) && ! empty( $email ) ) || ( ! empty( $wp_email ) && ( $bwf_email !== $email ) ) ) {
			$bwf_contact->set_email( $email );
		}

		$subscription_renewal = BWF_WC_Compatibility::get_order_data( $order, '_subscription_renewal' );

		/**
		 * Setting contact data if no products recieved or no upsell new order i.e. no batching in upsell or checkout or indexing or not a new order created by upstroke
		 */
		if ( ( 'upstroke' !== $order->get_created_via() && empty( $subscription_renewal ) ) && ( ( is_array( $products ) && count( $products ) < 1 ) || empty( $products ) ) ) {

			$fname = empty( $wp_fname ) ? $order->get_billing_first_name() : $wp_fname;
			$lname = empty( $wp_lname ) ? $order->get_billing_last_name() : $wp_lname;

			$bwf_fname = $bwf_contact->get_f_name();
			$bwf_lname = $bwf_contact->get_l_name();

			if ( ( empty( $bwf_fname ) && ! empty( $fname ) ) || ( ! empty( $wp_fname ) && $bwf_fname !== $fname ) ) {
				$bwf_contact->set_f_name( $fname );
			}

			if ( ( empty( $bwf_lname ) && ! empty( $lname ) ) || ( ! empty( $wp_lname ) && $bwf_lname !== $lname ) ) {
				$bwf_contact->set_l_name( $lname );
			}

			if ( empty( $bwf_contact->get_marketing_status() ) ) {
				$bwf_contact->set_marketing_status( 1 );
			}

			$first_order_date   = $bwf_contact->get_contact_meta( 'first_order_date' );
			$order_created_date = $order->get_date_created()->date( 'Y-m-d H:i:s' );

			if ( empty( $first_order_date ) || $first_order_date > $order_created_date ) {
				$bwf_contact->set_first_order_date( $order_created_date );
			}

			$bwf_country   = $bwf_contact->get_country();
			$order_country = $order->get_billing_country();

			if ( empty( $bwf_country ) || $bwf_country !== $order_country ) {
				$bwf_contact->set_country( $order_country );
			}

			$bwf_city   = $bwf_contact->get_city();
			$order_city = $order->get_billing_city();

			if ( empty( $bwf_city ) || $bwf_city !== $order_city ) {
				$bwf_contact->set_city( $order_city );
			}

			$bwf_state   = $bwf_contact->get_state();
			$order_state = $order->get_billing_state();

			if ( empty( $bwf_state ) || $bwf_state !== $order_state ) {
				$bwf_contact->set_state( $order_state );
			}
		}

		bwf_contact_maybe_update_creation_date( $bwf_contact, $order );
		bwf_create_update_customer( $bwf_contact, $order, $order_id, $products, $total );

		$bwf_contact->save( $force );
		$bwf_contact->save_meta();

		$cid = $bwf_contact->get_id();
		update_post_meta( $order_id, '_woofunnel_cid', $cid );


		return $cid;
	}
}

/**
 * Updating changes in child entities from contact meta 'changes' on thank you and order edit
 *
 * @param $order_id
 *
 * @hooked order_edit and woocommerce_thankyou
 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
 */
if ( ! function_exists( 'bwf_update_contact_changes_to_customer' ) ) {
	function bwf_update_contact_changes_to_customer( $order_id ) {
		if ( empty( $order_id ) ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		$wp_id = $order->get_customer_id();
		$email = $order->get_billing_email();

		$bwf_contact = bwf_get_contact( $wp_id, $email );
		$cid         = $bwf_contact->get_id();

		$changes     = $bwf_contact->get_contact_meta( 'changes' );
		$changes_arr = ! empty( $changes ) ? json_decode( $changes, true ) : array();

		WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Updating changes in customer for cid: $cid, on thank you/order-edit: " . print_r( count( $changes_arr ), true ), 'woofunnels_indexing' ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		$changes_exits = false;

		foreach ( $changes_arr as $change_key => $change_value ) {
			$changes_exits = true;
			if ( 'contact' === $change_key ) {
				continue;
			}
			$bwf_contact->{'set_' . $change_key . '_child'}();
			foreach ( $change_value as $key => $change ) {
				$bwf_contact->{'set_' . $change_key . '_' . $key}( $change );
			}
		}
		$new_order_temp = $bwf_contact->get_meta( 'new_order_temp' );

		if ( ! empty( $new_order_temp ) && is_array( $new_order_temp ) && isset( $new_order_temp[ $order_id ] ) ) {
			unset( $new_order_temp[ $order_id ] );

			if ( count( $new_order_temp ) > 0 ) {
				$bwf_contact->update_meta( 'new_order_temp', $new_order_temp );
			} else {
				$bwf_contact->delete_meta( 'new_order_temp' );
			}
		}

		if ( $changes_exits ) {
			$bwf_contact->save( true );
		}
	}
}
/**
 * Updating contact email, f_name and l_name on profile update
 *
 * @param $user_id
 */
if ( ! function_exists( 'bwf_update_contact_on_profile_update' ) ) {
	function bwf_update_contact_on_profile_update( $wp_id ) {
		$user_data       = get_userdata( $wp_id );
		$wp_email        = $user_data->user_email;
		$wp_fname        = $user_data->first_name;
		$wp_lname        = $user_data->last_name;

		$bwf_contact = bwf_get_contact( $wp_id, $wp_email );
		$cid         = $bwf_contact->get_id();

		WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Updating contact on profile update WPID: $wp_id, CID: $cid ", 'woofunnels_indexing' );

		if ( $cid > 0 ) {

			$bwf_wpid  = ( isset( $bwf_contact->db_contact ) && isset( $bwf_contact->db_contact->wpid ) && ! empty( $bwf_contact->db_contact->wpid ) ) ? $bwf_contact->db_contact->email : $bwf_contact->get_wpid();
			$bwf_email = ( isset( $bwf_contact->db_contact ) && isset( $bwf_contact->db_contact->email ) && ! empty( $bwf_contact->db_contact->email ) ) ? $bwf_contact->db_contact->email : $bwf_contact->get_email();
			$bwf_fname = $bwf_contact->get_f_name();
			$bwf_lname = $bwf_contact->get_l_name();

			$changed = false;

			if ( 0 < $wp_id && 0 === $bwf_wpid ) {
				$bwf_contact->set_wpid( $wp_id );
			}

			if ( ! empty( $wp_email ) && ( $wp_email !== $bwf_email ) ) {
				$bwf_contact->set_email( $wp_email );
				$changed = true;
			}

			if ( ! empty( $wp_fname ) && ( $wp_fname !== $bwf_fname ) ) {
				$bwf_contact->set_f_name( $wp_fname );
				$changed = true;
			}

			if ( ! empty( $wp_lname ) && ( $wp_lname !== $bwf_lname ) ) {
				$bwf_contact->set_l_name( $wp_lname );
				$changed = true;
			}
			if ( $changed ) {
				$bwf_contact->save( $changed );
			}
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Updating Customer. WPID: $wp_id,  CID: {$bwf_contact->get_id()}", 'woofunnels_indexing' );
		}
	}
}
/**
 * Indexing orders and create/update contacts and customers on user login
 *
 * @param $user_login
 * @param $user
 *
 * @hooked wp_login
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
 */
if ( ! function_exists( 'bwf_update_contact_on_login' ) ) {
	function bwf_update_contact_on_login( $user_id ) {
		$wp_id    = $user_id;
		$wp_user  = get_user_by( 'id', $user_id );
		$wp_email = $wp_user->user_email;
		$wp_fname = $wp_user->user_firstname;
		$wp_lname = $wp_user->user_lastname;

		$force       = true;
		$numberposts = 200;

		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'woofunnels_handle_indexed_orders', 10, 2 );

		// Get all orders of this login user which are not indexed yet
		$customer_orders_ids = wc_get_orders( array(
			'return'      => 'ids',
			'meta_key'    => '_customer_user', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => $wp_id, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'numberposts' => $numberposts,
			'post_type'   => 'shop_order',
			'offset'      => null,
			'orderby'     => 'ID',
			'order'       => 'DESC',
			'status'      => wc_get_is_paid_statuses(),
		) );
		wp_reset_query();

		$order_count = count( $customer_orders_ids );

		remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'woofunnels_handle_indexed_orders', 10, 2 );

		$bwf_contact = bwf_get_contact( $wp_id, $wp_email );
		$cid         = $bwf_contact->get_id();
		$bwf_wpid    = isset( $bwf_contact->db_contact->wpid ) ? $bwf_contact->db_contact->wpid : '';
		$bwf_email   = isset( $bwf_contact->db_contact->email ) ? $bwf_contact->db_contact->email : '';
		$bwf_fname   = $bwf_contact->get_f_name();
		$bwf_lname   = $bwf_contact->get_l_name();

		$email = $fname = $lname = '';

		$last_order_id = end( $customer_orders_ids );
		if ( $last_order_id > 0 ) {
			$last_order = wc_get_order( $last_order_id );

			$email = empty( $wp_email ) ? $last_order->get_billing_email() : $wp_email;
			$fname = empty( $wp_fname ) ? $last_order->get_billing_first_name() : $wp_fname;
			$lname = empty( $wp_lname ) ? $last_order->get_billing_last_name() : $wp_lname;

			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Last order id:$last_order_id, CID: $cid, WPID: $wp_id", 'woofunnels_indexing' );
		}

		if ( $wp_id > 0 && ( $bwf_wpid !== $wp_id ) ) {
			$bwf_contact->set_wpid( $wp_id );
		}

		if ( ( empty( $bwf_email ) && ! empty( $email ) ) || ( ! empty( $email ) && ( $bwf_email !== $email ) ) ) {
			$bwf_contact->set_email( $email );
		}

		if ( ( empty( $bwf_fname ) && ! empty( $fname ) ) || ( ! empty( $fname ) && $bwf_fname !== $fname ) ) {
			$bwf_contact->set_f_name( $fname );
		}

		if ( ( empty( $bwf_lname ) && ! empty( $lname ) ) || ( ! empty( $lname ) && $bwf_lname !== $lname ) ) {
			$bwf_contact->set_l_name( $lname );
		}

		WooFunnels_Dashboard::$classes['BWF_Logger']->log( "These $order_count orders for user: $wp_id, CID: $cid are retrieved: " . print_r( $customer_orders_ids, true ), 'woofunnels_indexing' ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		foreach ( $customer_orders_ids as $order_id ) {
			$order                = wc_get_order( $order_id );
			$subscription_renewal = BWF_WC_Compatibility::get_order_data( $order, '_subscription_renewal', true );
			if ( empty( $subscription_renewal ) && 'upstroke' !== $order->get_created_via() ) {
				bwf_create_update_contact_object( $bwf_contact, $order );
			}
			bwf_contact_maybe_update_creation_date( $bwf_contact, $order );
			bwf_create_update_customer_object( $bwf_contact, $order );

		}

		$bwf_contact->save( $force );
		$bwf_contact->save_meta();

		$cid = $bwf_contact->get_id();
		foreach ( $customer_orders_ids as $order_id ) {
			update_post_meta( $order_id, '_woofunnel_cid', $cid );
			update_post_meta( $order_id, '_woofunnel_custid', $cid );
		}

		return $cid;
	}
}

/**
 * @param WooFunnels_Contact $bwf_contact
 * @param WC_order $order
 */
if ( ! function_exists( 'bwf_contact_maybe_update_creation_date' ) ) {
	function bwf_contact_maybe_update_creation_date( $bwf_contact, $order ) {
		$get_creation_date = $bwf_contact->get_creation_date();

		if ( empty( $get_creation_date ) || $get_creation_date === '0000-00-00' || ( ! empty( $get_creation_date ) && $order->get_date_created() instanceof DateTime && ( strtotime( $get_creation_date ) > $order->get_date_created()->getTimestamp() ) ) ) {
			$bwf_contact->set_creation_date( $order->get_date_created()->format( 'Y-m-d H:i:s' ) );
		}
	}
}
/**
 *
 * @param $bwf_contact WooFunnels_Contact
 * @param $order_id
 */
if ( ! function_exists( 'bwf_create_update_contact_object' ) ) {
	function bwf_create_update_contact_object( $bwf_contact, $order ) {
		if ( empty( $bwf_contact->get_marketing_status() ) ) {
			$bwf_contact->set_marketing_status( 1 );
		}

		$first_order_date   = $bwf_contact->get_contact_meta( 'first_order_date' );
		$order_created_date = $order->get_date_created()->date( 'Y-m-d H:i:s' );

		if ( empty( $first_order_date ) || $first_order_date > $order_created_date ) {
			$bwf_contact->set_first_order_date( $order_created_date );
		}

		$bwf_country   = $bwf_contact->get_country();
		$order_country = $order->get_billing_country();

		if ( empty( $bwf_country ) || $bwf_country !== $order_country ) {
			$bwf_contact->set_country( $order_country );
		}

		$bwf_city   = $bwf_contact->get_city();
		$order_city = $order->get_billing_city();

		if ( empty( $bwf_city ) || $bwf_city !== $order_city ) {
			$bwf_contact->set_city( $order_city );
		}

		$bwf_state   = $bwf_contact->get_state();
		$order_state = $order->get_billing_state();

		if ( empty( $bwf_state ) || $bwf_state !== $order_state ) {
			$bwf_contact->set_state( $order_state );
		}
	}
}
