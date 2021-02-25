<?php
/**
 * Smart Offers
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.1.4
 * @package     Smart Offers
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists('SO_Offers') ) {

	Class SO_Offers {

		/**
		 * Return Order details
		 */
		function get_current_order_details($where) {
			global $sa_smart_offers, $wp;

			$order_containing_ids = $current_order_details = array();
			$found_categories_ids = $found_categories_ids_total = $order = array();

			if ( ! empty( $wp->query_vars['order-received'] ) ) {
				$order_id = absint($wp->query_vars['order-received']);
			} elseif ( 'thankyou' === $where && true === wp_doing_ajax() ) {
				$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
				if ( ! empty( $order_key ) && function_exists( 'wc_get_order_id_by_order_key' ) ) {
					$order_id = wc_get_order_id_by_order_key( $order_key );
				}
			}

			if ( $order_id && $order_id > 0 ) {
				$order = wc_get_order( $order_id );

				$valid_order_status = get_option( 'smart_offers_valid_order_status_to_show_offer', 'completed,processing' );
				$valid_order_statuses = array_map( 'trim', explode( ',', $valid_order_status ) );

				$order_status = $order->get_status();

				if ( in_array( $order_status, $valid_order_statuses ) ) {
					$order_items = $order->get_items();
					$order_total = 0;

					if (!empty($order_items)) {

						foreach ($order_items as $item_details) {
							$order_containing_ids[] = $item_details['product_id'];

							if ($item_details['variation_id'] != '') {
								$order_containing_ids[] = $item_details['variation_id'];
							}

							$order_total += $item_details ['line_subtotal'];

							$get_prod_category_ids = wp_get_post_terms($item_details ['product_id'], 'product_cat', array("fields" => "ids"));

							if ( count( $get_prod_category_ids ) > 0 ) {
								$get_prod_category_ids = array_fill_keys($get_prod_category_ids, $item_details ['line_subtotal']);
								$found_categories_ids[] = $get_prod_category_ids;
							}
						}
					}

					foreach ($found_categories_ids as $found_categories_id) {
						foreach ($found_categories_id as $cat_id => $cat_price) {
							if ( ! isset($found_categories_ids_total[$cat_id] ) ) {
								$found_categories_ids_total[$cat_id] = $cat_price;
							} else {
								$found_categories_ids_total[$cat_id] += $cat_price;
							}
						}
					}

					$order_containing_ids = array_unique($order_containing_ids);
					$order_grand_total = $order->get_total();

					// Get count of items from order. Quantity gets counted as another count.
					$order_item_count = $order->get_item_count();

					if ( count( $order_containing_ids ) ) {
						$current_order_contains_products = implode(',', $order_containing_ids);
					}

					$current_order_details = array(
													'offer_rule_cart_contains' 			=> $current_order_contains_products,
													'offer_rule_total' 					=> $order_total,
													'offer_rule_grand_total' 			=> $order_grand_total,
													'offer_rule_cart_category_details'  => $found_categories_ids_total,
													'offer_rule_cart_products_count' 	=> $order_item_count,
												);
				}
			}

			return array($order_containing_ids, $current_order_details, $order);
		}

		/**
		 * Return page details
		 */
		function get_page_details() {
			global $sa_smart_offers, $wp;

			if( wp_doing_ajax() ) {
				// Page flag will come 'any' if url contains any param. Need to handle that case.
				$requesting_url = wp_get_referer();
				if ( get_home_url() === rtrim( $requesting_url, '/' ) || get_site_url() === rtrim( $requesting_url, '/' ) ) {
					$where = "home";
				} elseif ( wc_get_cart_url() === $requesting_url ) {
					$where = "cart";
				} elseif ( get_permalink( wc_get_page_id('myaccount') ) === $requesting_url ) {
					$where = "myaccount";
				} elseif( strpos( $requesting_url, 'order-received' ) ) {
					$where = "thankyou";
				} elseif( wc_get_checkout_url() === $requesting_url ) {
					$where = "checkout";
				} else {
					$where = "any";
				}
				$where_url = $requesting_url;
			} else {
				if ( is_home() || is_front_page() ) {
					$where = "home";
					$where_url = home_url();
				} elseif ( is_cart() ) {
					$where = "cart";
					$where_url = wc_get_cart_url();
				} elseif ( is_account_page() ) {
					$where = "myaccount";
					$where_url = get_permalink(wc_get_page_id('myaccount'));
				} else {
					$where = "any";
				}
				
				if ( is_checkout() ) {
					if (isset($wp->query_vars['order-received'])) {
						$where = "thankyou";
					} else {
						$where = "checkout";
						$where_url = wc_get_checkout_url();
					}
				}
				if ($where == "thankyou" || $where == "any") {

					$where_url = (isset($_SERVER ["HTTPS"]) && $_SERVER ["HTTPS"] == "on") ? "https://" : "http://";
					if ($_SERVER ["SERVER_PORT"] != "80") {
						$where_url .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"] . $_SERVER ["REQUEST_URI"];
					} else {
						$where_url .= $_SERVER ["SERVER_NAME"] . $_SERVER ["REQUEST_URI"];
					}
				}
			}

			return array($where, $where_url);
		}

		/**
		 * Return accepted offer ids in the session
		 */
		function get_accepted_offer_ids_from_session() {

			$accepted_ids_in_session = array();

			// Check whether 'sa_smart_offers_accepted_offer_ids' session variable is set or not.
			$accepted_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_accepted_offer_ids');

			if ($accepted_session_variable) {
				$accepted_ids_in_session = SO_Session_Handler::so_get_session_value('sa_smart_offers_accepted_offer_ids');
			}

			return array($accepted_session_variable, $accepted_ids_in_session);
		}

		/**
		 * Return skipped offer in the session
		 */
		function get_skipped_offer_ids_from_session() {

			$skipped_ids_in_session = array();

			// Check whether 'sa_smart_offers_skipped_offer_ids' session variable is set or not.
			$skipped_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_skipped_offer_ids');

			if ($skipped_session_variable) {
				$skipped_ids_in_session = SO_Session_Handler::so_get_session_value('sa_smart_offers_skipped_offer_ids');
			} else {
				$skipped_ids_in_session = '';
			}

			return array($skipped_session_variable, $skipped_ids_in_session);
		}

		/**
		 * Return offer id value set after offer skipped
		 */
		function get_offer_id_on_skipping($skip_offer_id_variable) {

			$skipped_offer_id_variable = SO_Session_Handler::check_session_set_or_not($skip_offer_id_variable);
			$offer_id_on_skipping = '';
			if ($skipped_offer_id_variable) {
				$offer_id_on_skipping = SO_Session_Handler::so_get_session_value($skip_offer_id_variable);
			}

			return array($offer_id_on_skipping, $skipped_offer_id_variable);
		}

		/**
		 * Return valid offers on a page
		 */
		function get_valid_offer_ids($data) {

			if(empty($data)) {
				return;
			}

			extract($data);

			$valid_offer_ids = array();

			if ( !empty($offer_id_on_skipping) ) {
				$unset_id = false;
				$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id_on_skipping );

				if( ! is_array( $skipped_ids_in_session ) ) {
					$skipped_ids_in_session = explode(',', $skipped_ids_in_session);
				}

				if ( !empty($skipped_ids_in_session) || !empty($accepted_ids_in_session) ) {
					if ( in_array($offer_id_on_skipping, $skipped_ids_in_session) || in_array($offer_id_on_skipping, $accepted_ids_in_session) ) {
						$unset_id = true;
					}
				}

				if ( $unset_id == true ) {
					SO_Session_Handler::so_delete_session($skip_offer_id_variable);
				} else {
					$so_offer = new SO_Offer();
					//$offer_price = get_post_meta( $offer_id, 'offer_price', true ); // Need to fetch price based on variation id, variation data, prod_id
					$offer_price = $so_offer->get_offer_price(array('offer_id' => $offer_id));

					$valid_offer_ids [$offer_id] = $offer_price;
				}
			} else {
				$parent_offer_id_variable = ( $where == "any" ) ? str_replace(array('/', '-', '&', '=', ':'), '', $where_url) . '_parent_offer_id' : $where . '_parent_offer_id';
				$check_parent_offer_id = SO_Session_Handler::check_session_set_or_not($parent_offer_id_variable);
				if ($check_parent_offer_id) {
					SO_Session_Handler::so_delete_session($parent_offer_id_variable);
				}

				if ( !empty($offer_ids) ) {
					// get offers based on ids for future
					$offer_ids = explode(',', $offer_ids);
					$offer_ids = $this->get_page_offers($page, $offer_ids);
				} else {
					$offer_ids = $this->get_page_offers($page);
				}

				if ( !empty( $offer_ids ) ) {
					//Get user's details
					$user_details = ( $where == "thankyou" ) ? $this->get_user_details($page, $order) : $this->get_user_details($page, '');
					// Get Cart/Order details
					$cart_order_details = ( $where == "thankyou" ) ? $current_order_details : $this->get_cart_contents();
					$details = array_merge($user_details, $cart_order_details);
					$valid_offer_ids = $this->validate_offers($page, $offer_ids, $details);
				} else {
					return;
				}
			}

			if ( empty($valid_offer_ids) ) {
				return;
			}

			return $valid_offer_ids;
		}

		/**
		 * Return valid offer after validating offer aganist SO settings
		 */
		function get_offers($offer_ids = null) {

			$offer_ids = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_ids );

			list($where, $where_url) = $this->get_page_details();

			$current_action = current_action();
			if( 'woocommerce_review_order_before_submit' === $current_action ) {
				$page = 'before_checkout_submit_page';
			} else if( 'woocommerce_review_order_after_submit' === $current_action ) {
				$page = 'after_checkout_submit_page';
			} else {
				$page = $where . '_page';
			}

			$order_containing_ids = $current_order_details = array();
			$order = null;
			if ( $where == 'thankyou' ) {
				list($order_containing_ids, $current_order_details, $order) = $this->get_current_order_details($where);
			}

			list($accepted_session_variable, $accepted_ids_in_session) = $this->get_accepted_offer_ids_from_session();
			list($skipped_session_variable, $skipped_ids_in_session) = $this->get_skipped_offer_ids_from_session();

			$skip_offer_id_variable = ( $where == "any" ) ? str_replace(array('/', '-', '&', '=', ':'), '', $where_url) . '_skip_offer_id' : $where . '_skip_offer_id';
			list($offer_id_on_skipping, $skipped_offer_id_variable) = $this->get_offer_id_on_skipping($skip_offer_id_variable);

			$data = array(
				'page' => $page,
				'where' => $where,
				'where_url' => $where_url,
				'accepted_ids_in_session' => $accepted_ids_in_session,
				'skipped_ids_in_session' => $skipped_ids_in_session,
				'skip_offer_id_variable' => $skip_offer_id_variable,
				'offer_id_on_skipping' => $offer_id_on_skipping,
				'current_order_details' => $current_order_details,
				'offer_ids' => $offer_ids,
				'skipped_offer_id_variable' => $skipped_offer_id_variable,
				'order' => $order,
			);

			$valid_offer_ids = $this->get_valid_offer_ids($data);

			// TODO: Define settings class and fetch value from it.
			$get_option_for_hidden = get_option( 'so_show_hidden_items' );
			$get_option_for_price  = get_option( 'so_if_multiple' );
			// Pick a single offer from available offers

			if( empty($valid_offer_ids) ) {
				return;
			}

			$offer_data = $this->process_offers($get_option_for_hidden, $get_option_for_price, $valid_offer_ids, $where, $order_containing_ids);
			$data['offer_data'] = $offer_data;
			return $data;
		}

		/**
		 * Return offers details after processing the settings saved and checking offered prod is_in stock or not.
		 */
		function process_offers($get_option_for_hidden, $get_option_for_price, $offer, $where, $order_containing_ids = array()) {
			global $sa_smart_offers, $wpdb;

			$offer_ids = array_keys($offer);

			$query = "SELECT offer_priority,
					  (CASE WHEN unpriortized_offer_ids = 0 THEN priortized_offer_ids
							ELSE unpriortized_offer_ids
					  END) AS offer_ids
					  FROM(
					  SELECT
					  (CASE WHEN (menu_order = 0) THEN menu_order
			    			ELSE menu_order
					  END) AS offer_priority,
					  (CASE WHEN (menu_order > 0) THEN ID
			 				ELSE ''
					  END) AS priortized_offer_ids,
					  (CASE WHEN (menu_order = 0) THEN GROUP_CONCAT(ID)
			    			ELSE 0
					  END) AS unpriortized_offer_ids
					FROM {$wpdb->prefix}posts
					WHERE ID IN ('".implode("','", $offer_ids)."')
					GROUP BY offer_priority, priortized_offer_ids
					ORDER BY offer_priority DESC, priortized_offer_ids DESC) as temp";
			$offer_ids_priority = $wpdb->get_results($query, 'ARRAY_A');

			$priority_sorted_offer_ids = $sort_offer_ids_on_global = $offers_set_1 = $offers_set_2 = $sorted_offers = array();
			if ( is_array( $offer_ids_priority ) ) {
				foreach ( $offer_ids_priority as $offer_id_priority ) {
					if ( $offer_id_priority['offer_priority'] == 0 ) {
						$global_sort_offer_ids = $offer_id_priority['offer_ids'];
					} else {
						$priority_sorted_offer_ids[] = $offer_id_priority['offer_ids'];
					}
				}
			}

			if ( isset( $priority_sorted_offer_ids ) ) {
				foreach ( $priority_sorted_offer_ids as $key => $value ) {
					$offers_set_1[$value] = $offer[$value];
				}
			}

			if ( !empty( $global_sort_offer_ids ) ) {
				if ( strpos($global_sort_offer_ids, ',') ) {
					$sort_offer_ids_on_global = explode(',', $global_sort_offer_ids);
				} else {
					$sort_offer_ids_on_global[0] = $global_sort_offer_ids;
				}
			}

			if ( isset( $sort_offer_ids_on_global ) ) {
				foreach ( $sort_offer_ids_on_global as $key => $value ) {
					$offers_set_2[$value] = $offer[$value];
				}
				if ( $get_option_for_price == 'high_price' ) {
					arsort($offers_set_2);
				} elseif ( $get_option_for_price == 'low_price' ) {
					asort($offers_set_2);
				} elseif ( $get_option_for_price == 'random' ) {
					$shuffled_offer_array = array();

					$shuffled_keys = array_keys($offers_set_2);
					shuffle($shuffled_keys);

					foreach ($shuffled_keys as $shuffled_key) {
						$shuffled_offer_array[$shuffled_key] = $offers_set_2[$shuffled_key];
					}

					$offers_set_2 = $shuffled_offer_array;
				}
			}

			$sorted_offers = $offers_set_1 + $offers_set_2;

			$max_inline_offer = get_option('so_max_inline_offer') ? get_option('so_max_inline_offer') : 2;
			$offer_details = array();
			$i = 0;

			foreach ( $sorted_offers as $post_id => $sale_price ) {
				$post_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post_id );

				if ( $where == "home" && $i == 1 ) {
					continue;
				}

				$offered_prod_id = get_post_meta($post_id, 'target_product_ids', true);

				if ( $where == "thankyou" ) {
					foreach ( $order_containing_ids as $id ) {
						if ( $id == $offered_prod_id ) {
							continue 2;
						}
					}
				}

				if ( !empty( $offered_prod_id ) ) {
					$offered_product_instance = wc_get_product($offered_prod_id);
					if ( !( $offered_product_instance instanceof WC_Product ) ) {
						continue;
					}

					$stock = ( $offered_product_instance->is_in_stock() == 1 ) ? 1 : 0;
					if ( $stock == 1 ) {
						$offered_prods_visibility = $offered_product_instance->is_visible();
						if ( $get_option_for_hidden == "no" && empty( $offered_prods_visibility ) ) {
							$show_offer = 0;
						} else {
							$show_offer = 1;
						}
					} else {
						$show_offer = 0;
					}
				} else {
					$show_offer = 1;		// in case offered_product is empty but offer is of free shipping type
				}

				if ( $show_offer == 1 ) {
					if ( $i < $max_inline_offer ) {
						$offer_details [$i]['post_id'] = $post_id;
						$offer_details [$i]['id'] = $offered_prod_id;
						$offer_details [$i]['offer_price'] = $sale_price;
					}
				}
				$i++;
			}

			return $offer_details;
		}

		/**
		 * Return postmeta values of all offers of a particular page.
		 */
		function get_page_offers($page, $offer_ids = array()) {

			global $wpdb, $current_user;

			$offer_ids = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_ids ); 				// ?

//                              ===== Calculating ids to skipped =======================
			$offers_to_skip = array();

			//Checking whehter session is set or not.
			$skipped_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_skipped_offer_ids');
			$accepted_session_variable = SO_Session_Handler::check_session_set_or_not('sa_smart_offers_accepted_offer_ids');

			// Getting skipped/accepted ids of session.
			$skipped_ids_in_session = ( $skipped_session_variable ) ? SO_Session_Handler::so_get_session_value('sa_smart_offers_skipped_offer_ids') : array();
			$accepted_ids_in_session = ( $accepted_session_variable ) ? SO_Session_Handler::so_get_session_value('sa_smart_offers_accepted_offer_ids') : array();

			if (!empty($skipped_ids_in_session)) {
				$offers_to_skip = array_merge($offers_to_skip, $skipped_ids_in_session);
			}

			if (!empty($accepted_session_variable)) {
				$offers_to_skip = array_merge($offers_to_skip, $accepted_ids_in_session);
			}

			if ($current_user->ID != 0) {
				$offers_skipped_by_user = get_user_meta($current_user->ID, 'customer_skipped_offers', true);
				if (!empty($offers_skipped_by_user)) {
					$offers_to_skip = array_merge($offers_to_skip, $offers_skipped_by_user);
				}
			}

			if( is_array( $offers_to_skip ) ) {
				$offers_to_skip = array_unique($offers_to_skip, SORT_REGULAR);
			}

//                              =======================================================

			$results_for_fetching_offers = array();

			$wpdb->query("SET SESSION group_concat_max_len=999999");

			if ( !empty( $offer_ids ) ) {
				$smart_offers_ids_args = array(
												'post_type' => 'smart_offers',
												'post_status' => 'publish',
												'post__in' => $offer_ids,
												'nopaging' => true,
												'fields' => 'ids'
											);
			} else {
				$smart_offers_ids_args = array(
												'post_type' => 'smart_offers',
												'post_status' => 'publish',
												'nopaging' => true,
												'fields' => 'ids',
												'meta_query' => array(
																		array(
																				'key' => 'offer_rule_page_options',
																				'value' => $page,
																				'compare' => 'LIKE'
																			)
																	)
											);
			}

			if ( !empty( $offers_to_skip ) ) {
				$smart_offers_ids_args = array_merge( $smart_offers_ids_args, array( 'post__not_in' => $offers_to_skip ) );
			}

			$smart_offers_ids_result = new WP_Query( $smart_offers_ids_args );

			$results_for_fetching_offers = ( $smart_offers_ids_result->post_count > 0 ) ? $smart_offers_ids_result->posts : array();

			if (count($results_for_fetching_offers) > 0) {
				$offers = $this->get_all_offer_rules_meta($results_for_fetching_offers);
			} else {
				$offers = array();
			}

			return $offers;
		}

		/**
		 * Return offer rules of all offer ids
		 * Updated @since 3.8.0
		 */
		function get_all_offer_rules_meta($offer_ids) {

			global $wpdb, $current_user;

			$offers = array();
			$offer_rules = array();

			if ( !empty($offer_ids) && is_array($offer_ids) ) {

				$offer_ids = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_ids ); 				// ?

				$get_all_meta_args = array(
											'post_type' => 'smart_offers',
											'post__in' => $offer_ids,
											'fields' => 'ids',
											'nopaging' => true
										);
				$get_all_meta_results = new WP_Query( $get_all_meta_args );

				if ( $get_all_meta_results->post_count > 0 ) {

					$query_to_get_serialized_rules = "SELECT * FROM {$wpdb->prefix}postmeta
										WHERE meta_key = '_offer_rules'
											AND post_id IN ( " . implode( ',', $get_all_meta_results->posts ) . " )";
					$results_for_fetching_offers = $wpdb->get_results($query_to_get_serialized_rules);

					if ( count( $results_for_fetching_offers ) > 0 ) {
						foreach ( $results_for_fetching_offers as $result ) {
							$offer_rules = maybe_unserialize( $result->meta_value );

							// Following code checks if the rules data is of new or old format.
							// If new rules structure, then if ::else else
							// This is done to avoid migration on plugin update
							if ( isset( $offer_rules[0][0] ) ) {
								foreach ( $offer_rules as $key => $offer_rules_set ) {
									$offers[$result->post_id.'_group_'.$key] = $this->flatten_offer_rules($offer_rules_set);
									$offers[$result->post_id.'_group_'.$key]['offer_id'] = $result->post_id;
									$offers[$result->post_id.'_group_'.$key]['default_rule_show_offer'] = true;
								}
							} else {
								$offers[$result->post_id.'_group_0'] = $this->flatten_offer_rules($offer_rules);
								$offers[$result->post_id.'_group_0']['offer_id'] = $result->post_id;
								$offers[$result->post_id.'_group_0']['default_rule_show_offer'] = true;
							}
						}
					}

				}

			}

			return $offers;
		}

		/*
		 * This function will convert serialized offer rules into individual meta format so validate function can use it to validate rules
		 * @since 3.5.1
		 */
		function flatten_offer_rules( $offers ) {

			$offer_rules = array();
			$offer_rules_for_each_offer = array();

			foreach ( $offers as $offer ) {

				$offer_rule_action_and_value = array( 'offer_rule_' . $offer['offer_action'] => $offer['offer_rule_value'] );

				if ( $offer['offer_type'] == 'cartorder' && $offer['offer_action'] == 'cart_contains' ) {
					// To handle quantity & operator in case where multiple products are added in same cart/order contains product rule
					$count_of_products = substr_count($offer['offer_rule_value'], ",");
					if ( $count_of_products > 0 ) {
						$separator = ",";
						$cart_order_contains_extra_meta = array(
													'offer_rule_quantity_total' => implode($separator, array_fill(0, $count_of_products+1, $offer['quantity_total'])),
													'offer_rule_cart_quantity' => implode($separator, array_fill(0, $count_of_products+1, $offer['cart_quantity']))
												);
					} else {
						$cart_order_contains_extra_meta = array(
														'offer_rule_quantity_total' => $offer['quantity_total'],
														'offer_rule_cart_quantity' => $offer['cart_quantity']
													);						
					}
					$offer_rule_action_and_value = array_merge( $offer_rule_action_and_value, $cart_order_contains_extra_meta );
				} elseif ( $offer['offer_type'] == 'cartorder' && $offer['offer_action'] == 'cart_prod_categories_is' ) {
					$category_extra_meta = array(
													'offer_rule_category_total' => $offer['category_total'],
													'offer_rule_category_amount' => $offer['category_amount']
												);
					$offer_rule_action_and_value = array_merge( $offer_rule_action_and_value, $category_extra_meta );
				} elseif ( $offer['offer_type'] == 'cartorder' && ( $offer['offer_action'] == 'cart_prod_attribute_is' || $offer['offer_action'] == 'cart_prod_attribute_not_is' ) ) {
					$product_attribute_term_extra_meta = array(
																'offer_rule_product_attribute_term' => $offer['cart_prod_attribute_term']
															);
					$offer_rule_action_and_value = array_merge( $offer_rule_action_and_value, $product_attribute_term_extra_meta );
				}

				$offer_rules[] = $offer_rule_action_and_value;

				unset($offer_rules['offer_type']);
				unset($offer_rules['offer_action']);
				unset($offer_rules['offer_rule_value']);
				if ( array_key_exists('quantity_total', $offer_rules) ) {
					unset($offer_rules['quantity_total']);
				}
				if ( array_key_exists('cart_quantity', $offer_rules) ) {
					unset($offer_rules['cart_quantity']);
				}
				if ( array_key_exists('category_total', $offer_rules) ) {
					unset($offer_rules['category_total']);
				}
				if ( array_key_exists('category_amount', $offer_rules) ) {
					unset($offer_rules['category_amount']);
				}

			}

			foreach ($offer_rules as $offers_rule) {
				foreach ($offers_rule as $key => $value) {
					$offer_rules_for_each_offer[$key][] = $value;
				}
			}

			foreach ($offer_rules_for_each_offer as $key => $value) {
				if ( $key != 'offer_rule_offer_valid_between' ) {
					$offer_rules_for_each_offer[$key] = implode(",", $value);
				} else {
					$offer_rules_for_each_offer[$key] = array_merge(...$value);
				}
			}

			return $offer_rules_for_each_offer;

		}

		/**
		 * Function to find individual product quantity in cart
		 */
		function get_product_id_to_quantity($cart) {

			global $sa_smart_offers;

			if ( empty( $cart ) ) return array();

			$product_and_its_quantity = array();

			foreach( $cart as $cart_key => $value ) {
				$product_and_its_quantity [$value['data']->get_id()] = $value['quantity'];
			}

			return $product_and_its_quantity;
		}

		/**
		 * Return valid offers after validating aganist rules based on cart/order details and user details
		 * Updated @since 3.8.0
		 *
		 * @param $page current page
		 * @param $page_offers_id current offer's rule group with rules
		 * @param $details pre-fetched user's details to validate rules against
		 *
		 * @return $validated_offers_id key value pair of valid offers id with their offered_price
		 */
		function validate_offers($page, $page_offers_id, $details) {
			$user_cart_contains = ( isset($details ['offer_rule_cart_contains']) ) ? explode(",", $details ['offer_rule_cart_contains']) : array();
			$user_has_bought = ( isset($details ['offer_rule_has_bought']) ) ? explode(",", $details ['offer_rule_has_bought']) : array();
			$cart_category_details = ( isset($details['offer_rule_cart_category_details']) ) ? $details['offer_rule_cart_category_details'] : array();

			$valid_offers_id = array();
			$all_page_offers = array();

			foreach ( $page_offers_id as $group_id => $rules ) {
				$exploded_group_id = explode( '_group_', $group_id );
				if ( count( $exploded_group_id ) < 2 ) {
					continue;
				}
				if ( empty( $all_page_offers[ $exploded_group_id[0] ] ) || ! is_array( $all_page_offers[ $exploded_group_id[0] ] ) ) {
					$all_page_offers[ $exploded_group_id[0] ] = array();
				}
				$all_page_offers[ $exploded_group_id[0] ][ $exploded_group_id[1] ] = $rules;
			}

			foreach ( $all_page_offers as $offer_id => $offer_groups ) {

				$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id );

				$is_valid = $this->validate_or_rule( $page, $offer_groups, $details );

				if ( true === $is_valid ) {
					$valid_offers_id [] = $offer_id;
				}

			}

			$validated_offers_id = array();

			foreach ( $valid_offers_id as $id ) {
				if ( $id != '' ) {
					$so_offer = new SO_Offer();
					$offer_price = $so_offer->get_offer_price( array( 'offer_id' => $id ) );
					$validated_offers_id [$id] = $offer_price;
				}
			}

			return $validated_offers_id;
		}

		/**
		 * Validate offer with And rule
		 * @since 3.8.0
		 */
		function validate_and_rule( $page, $offer_rule, $details ) {
			$user_cart_contains = ( isset($details ['offer_rule_cart_contains']) ) ? explode(",", $details ['offer_rule_cart_contains']) : array();
			$user_has_bought = ( isset($details ['offer_rule_has_bought']) ) ? explode(",", $details ['offer_rule_has_bought']) : array();
			$user_has_bought_categories = ( isset($details ['offer_rule_user_prod_categories']) ) ? explode(",", $details ['offer_rule_user_prod_categories']) : array();
			$cart_category_details = ( isset($details['offer_rule_cart_category_details']) ) ? $details['offer_rule_cart_category_details'] : array();
			$cart_product_attribute_details = ( isset( $details['offer_rule_product_attributes'] ) ) ? $details['offer_rule_product_attributes'] : array();

			foreach ( $offer_rule as $rule_key => $rule_value ) {

				if ( $rule_key == "offer_rule_category_amount" || $rule_key == "offer_rule_category_total" || $rule_key == "offer_rule_cart_quantity" || $rule_key == "offer_rule_quantity_total"  || $rule_key == "offer_rule_product_attribute_term" ) {
					continue;
				}

				$bool = false;

				switch ($rule_key) {

					case "default_rule_show_offer" :
					case "offer_id" :

						$bool = true;

						break;

					case "offer_rule_cart_contains" :

						$rule_cart_contains = explode(",", $rule_value);

						if ( is_array( $rule_cart_contains ) ) {

							$quantity = explode(",", $offer_rule['offer_rule_cart_quantity']);
							$operator = explode(",", $offer_rule['offer_rule_quantity_total']);

							for ( $i = 0; $i < count($rule_cart_contains); $i++ ) {

								if ( in_array( $rule_cart_contains[$i], $user_cart_contains ) ) {

									if ( !empty( $quantity[$i] ) ) {

										$cart = WC()->cart->get_cart();
										$product_and_quantity = $this->get_product_id_to_quantity($cart);
										$cart_quantity = $product_and_quantity[$rule_cart_contains[$i]];

										if ( $operator[$i] == "quantity_total_less" ) {
											if ( $cart_quantity <= $quantity[$i] ) {
												$bool = true;
											} else {
												$bool = false;
												break;
											}
										} elseif ( $operator[$i] == "quantity_total_more" ) {
											if ( $cart_quantity >= $quantity[$i] ) {
												$bool = true;
											} else {
												$bool = false;
												break;
											}
										}

									} else {
										$bool = true;
									}

								} else {
									$bool = false;
									break;
								}

							}

						}

						break;

					case "offer_rule_cart_doesnot_contains" :

						$rule_cart_doesnot_contains = explode(",", $rule_value);

						$cart_doesnot_contain_val = (count(array_intersect($rule_cart_doesnot_contains, $user_cart_contains)) == 0 ) ? 1 : 0;
						if ( $cart_doesnot_contain_val == 1 ) {
							$bool = true;
						}

						break;

					case "offer_rule_cart_total_less" :	// Cannot have same multiple rules, hence not handling

						if (isset($details ['offer_rule_total']) && $details ['offer_rule_total'] <= $rule_value) {
							$bool = true;
						}

						break;

					case "offer_rule_cart_total_more" :	// Cannot have same multiple rules, hence not handling

						if (isset($details ['offer_rule_total']) && $details ['offer_rule_total'] >= $rule_value) {
							$bool = true;
						}

						break;

					case "offer_rule_cart_grand_total_less" :	// Cannot have same multiple rules, hence not handling

						if (isset($details ['offer_rule_grand_total']) && $details ['offer_rule_grand_total'] <= $rule_value) {
							$bool = true;
						}

						break;

					case "offer_rule_cart_grand_total_more" :	// Cannot have same multiple rules, hence not handling

						if (isset($details ['offer_rule_grand_total']) && $details ['offer_rule_grand_total'] >= $rule_value) {
							$bool = true;
						}

						break;

					case "offer_rule_cart_product_count_less" :

						if ( isset( $details['offer_rule_cart_products_count'] ) && $details['offer_rule_cart_products_count'] <= $rule_value ) {
							$bool = true;
						}

						break;

					case "offer_rule_cart_product_count_more" :

						if ( isset( $details['offer_rule_cart_products_count'] ) && $details['offer_rule_cart_products_count'] >= $rule_value ) {
							$bool = true;
						}

						break;

					case "offer_rule_cart_prod_categories_is" :

						$rule_contains_categories = explode(",", $rule_value);

						if( is_array( $rule_contains_categories ) ) {

							$operator = explode(",", $offer_rule['offer_rule_category_total']);
							$amt = explode(",", $offer_rule['offer_rule_category_amount']);

							for ( $i=0; $i < count($rule_contains_categories) ; $i++ ) {

								if ( array_key_exists($rule_contains_categories[$i], $cart_category_details) ) {

									if (!empty($amt[$i])) {

										$cart_amount = $cart_category_details[$rule_contains_categories[$i]];

										if ($operator[$i] == "category_total_less") {
											if ($cart_amount <= $amt[$i]) {
												$bool = true;
											} else {
												$bool = false;
												break;
											}
										} elseif ($operator[$i] == "category_total_more") {
											if ($cart_amount >= $amt[$i]) {
												$bool = true;
											} else {
												$bool = false;
												break;
											}
										}
									} else {
										$bool = true;
									}
								} else {
									$bool = false;
									break;
								}

							}

						}

						break;

					case "offer_rule_cart_prod_categories_not_is" :

						$rule_not_contains_categories = explode(",", $rule_value);

						$cart_category_ids = array();
						foreach ( $cart_category_details as $keys => $values ) {
							$cart_category_ids[] = $keys;
						}

						$rule_not_contains_categories_val = (count(array_intersect($rule_not_contains_categories, $cart_category_ids)) == 0 ) ? 1 : 0;
						if ( $rule_not_contains_categories_val == 1 ) {
							$bool = true;
						}

						break;

					case "offer_rule_cart_prod_attribute_is" :

						$rule_contains_prod_attributes = explode(",", $rule_value);

						if ( is_array( $rule_contains_prod_attributes ) ) {

							$rule_attribute_term = explode(",", $offer_rule['offer_rule_product_attribute_term']);

							for ( $i=0; $i < count($rule_contains_prod_attributes) ; $i++ ) {

								if ( array_key_exists($rule_contains_prod_attributes[$i], $cart_product_attribute_details) ) {

									if ( !empty($rule_attribute_term[$i]) ) {

										$cart_prod_attribute_term = $cart_product_attribute_details[$rule_contains_prod_attributes[$i]];
										$cart_product_attribute_terms = array_unique(explode(",",$cart_prod_attribute_term));
										if ( in_array($rule_attribute_term[$i], $cart_product_attribute_terms) ) {
											$bool = true;
										} else {
											$bool = false;
										}

									} else {
										$bool = false;
									}
								} else {
									$bool = false;
									break;
								}

							}

						}

						break;

					case "offer_rule_cart_prod_attribute_not_is" :

						$rule_not_contains_prod_attributes = explode(",", $rule_value);

						if ( is_array( $rule_not_contains_prod_attributes ) ) {

							$rule_attribute_term = explode(",", $offer_rule['offer_rule_product_attribute_term']);

							for ( $i=0; $i < count($rule_not_contains_prod_attributes) ; $i++ ) {

								if ( array_key_exists($rule_not_contains_prod_attributes[$i], $cart_product_attribute_details) ) {

									if ( !empty($rule_attribute_term[$i]) ) {

										$cart_prod_attribute_term = $cart_product_attribute_details[$rule_not_contains_prod_attributes[$i]];
										$cart_product_attribute_terms = array_unique(explode(",",$cart_prod_attribute_term));
										if ( in_array($rule_attribute_term[$i], $cart_product_attribute_terms) ) {
											$bool = false;
										} else {
											$bool = true;
										}

									} else {
										$bool = false;
									}
								} else {
									$bool = true;
									break;
								}

							}

						}

						break;

					case "offer_rule_has_bought" :

						$rule_has_bought = explode(",", $rule_value);

						$user_bought_val = (count(array_intersect($rule_has_bought, $user_has_bought)) == count($rule_has_bought)) ? 1 : 0;
						if ($user_bought_val == 1) {
							$bool = true;
						}

						break;

					case "offer_rule_not_bought" :

						$rule_not_bought = explode(",", $rule_value);

						$user_not_bought_val = (count(array_intersect($rule_not_bought, $user_has_bought)) == 0) ? 1 : 0;
						if ($user_not_bought_val == 1) {
							$bool = true;
						}

						break;

					case "offer_rule_registered_user" :	// Cannot have same multiple rules, hence not handling

						if ( isset( $details['offer_rule_registered_user'] ) && $details['offer_rule_registered_user'] == $rule_value ) {
							$bool = true;
						}

						break;

					case "offer_rule_user_role" :

						$rule_user_role = explode(",", $rule_value);

						if ( isset( $details['offer_rule_user_role'] ) ) {
							$user_rule_val = ( count(array_intersect($rule_user_role, $details['offer_rule_user_role'])) == count($rule_user_role) ) ? 1 : 0;
							if ( $user_rule_val == 1 ) {
								$bool = true;
							}
						}

						break;

					case "offer_rule_user_role_not" :

						$rule_user_role_not = explode(",", $rule_value);

						if ( isset( $details['offer_rule_user_role'] ) ) {
							$user_rule_not_val = ( count(array_intersect($rule_user_role_not, $details['offer_rule_user_role'])) == 0 ) ? 1 : 0;
							if ( $user_rule_not_val == 1 ) {
								$bool = true;
							}
						}

						break;

					case "offer_rule_registered_period" :	// Cannot have same multiple rules, hence not handling

						switch ($rule_value) {
							case "one_month" :

								if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] < 1) {
									$bool = true;
								}
								break;
							case "three_month" :

								if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] < 3) {
									$bool = true;
								}
								break;
							case "six_month" :

								if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] < 6) {
									$bool = true;
								}
								break;
							case "less_than_1_year" :

								if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] < 12) {
									$bool = true;
								}
								break;
							case "more_than_1_year" :

								if (isset($details ['offer_rule_registered_period']) && $details ['offer_rule_registered_period'] > 12) {
									$bool = true;
								}
								break;
						}

						break;

					case "offer_rule_total_ordered_less" :	// Cannot have same multiple rules, hence not handling

						if (isset($details ['offer_rule_order_total']) && $details ['offer_rule_order_total'] <= $rule_value) {
							$bool = true;
						}

						break;

					case "offer_rule_total_ordered_more" :	// Cannot have same multiple rules, hence not handling

						if (isset($details ['offer_rule_order_total']) && $details ['offer_rule_order_total'] >= $rule_value) {
							$bool = true;
						}

						break;

					case "offer_rule_has_bought_product_categories" :

						$rule_bought_categories = explode(",", $rule_value);

						$user_prod_category_ids = array();
						foreach ( $user_has_bought_categories as $keys => $values ) {
							$user_prod_category_ids[] = $values;
						}


						$rule_contains_categories_val = (count(array_intersect($rule_bought_categories, $user_prod_category_ids)) == count($rule_bought_categories) ) ? 1 : 0;
						if ( $rule_contains_categories_val == 1 ) {
							$bool = true;
						}

						break;

					case "offer_rule_has_not_bought_product_categories" :

						$rule_not_bought_categories = explode(",", $rule_value);

						$user_prod_category_ids = array();
						foreach ( $user_has_bought_categories as $keys => $values ) {
							$user_prod_category_ids[] = $values;
						}

						$rule_not_contains_categories_val = ( count( array_intersect( $rule_not_bought_categories, $user_prod_category_ids ) ) == 0 ) ? 1 : 0;
						if ( $rule_not_contains_categories_val == 1 ) {
							$bool = true;
						}

						break;

					case "offer_rule_has_placed_num_orders_less" :

						if ( isset( $details['offer_rule_user_orders_count'] ) && $details['offer_rule_user_orders_count'] <= $rule_value ) {
							$bool = true;
						}

						break;

					case "offer_rule_has_placed_num_orders_more" :

						if ( isset( $details['offer_rule_user_orders_count'] ) && $details['offer_rule_user_orders_count'] >= $rule_value ) {
							$bool = true;
						}

						break;

					case "offer_rule_offer_valid_between" :	// Cannot have same multiple rules, hence not handling

						if ( isset($rule_value['offer_valid_from']) && !empty($rule_value['offer_valid_from']) ) {

							if (current_time('timestamp') >= $rule_value['offer_valid_from']) {

								$bool = true;

								if (isset($rule_value['offer_valid_till']) && !empty($rule_value['offer_valid_till'])) {

									if (current_time('timestamp') <= $rule_value['offer_valid_till']) {
										$bool = true;
									} else {
										$bool = false;
									}
								}
							}
						}

						break;

				}

				if ( $bool === false ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Validate offer with OR rule
		 * @since 3.8.0
		 */
		function validate_or_rule( $page, $offer_groups, $details ) {
			$is_valid = false;
			foreach ( $offer_groups as $group ) {
				$is_valid = $is_valid || $this->validate_and_rule( $page, $group, $details );
			}
			return $is_valid;
		}

		/**
		 * Return details of the current user.
		 */
		function get_user_details($page = null, $order = null) {

			global $current_user, $wpdb, $sa_smart_offers;

			$registered_user = ( $current_user->ID != 0 ) ? 'yes' : 'no';
			$user_role = ( $current_user->ID != 0 ) ? (array) $current_user->roles : array();

			if ( !empty( $order ) ) {
				$order_id = $order->get_id();
			}

			$current_order_id = ( $page == 'thankyou_page' && !empty( $order_id ) ) ? $order_id : '';

			$order_post_query_args = array(
											'post_type' => 'shop_order',
											'post_status' => array_keys( wc_get_order_statuses() ),
											'nopaging' => true,
											'fields' => 'ids'
										);

			if ( $current_user->ID != 0 ) {

				$today = date("Y-m-d");
				$registered_date = $current_user->data->user_registered;
				$registered_date = date("Y-m-d", strtotime($registered_date));

				$start_date = strtotime($registered_date);
				$end_date = strtotime($today);

				$year_1 = date('Y', $start_date);
				$year_2 = date('Y', $end_date);

				$month_1 = date('m', $start_date);
				$month_2 = date('m', $end_date);

				$date_1 = date('d', $start_date);
				$date_2 = date('d', $end_date);

				if ( $date_2 < $date_1 ) {
					$registered_period = (($year_2 - $year_1) * 12) + ($month_2 - $month_1) - 1;
				} else {
					$registered_period = (($year_2 - $year_1) * 12) + ($month_2 - $month_1);
				}

				$user_email = $current_user->data->user_email;

				$get_all_orders_id_of_customers_args = array(
																'meta_query' => array(
																						array(
																								'key' => '_customer_user',
																								'value' => $current_user->ID
																							)
																					)
															);

			} else {

				$registered_period = '';

				if ( $page == "thankyou_page" ) {

					$order_billing_email = $order->get_billing_email();
					$user_email = ( !empty( $order_billing_email ) ) ? $order_billing_email : '';

					if ( !empty( $user_email ) ) {
						$get_all_orders_id_of_customers_args = array(
																	'meta_query' => array(
																							array(
																									'key' => '_billing_email',
																									'value' => $user_email
																								)
																						)
																);
					}

				}
			}

			$current_order_id_args = array();

			if ( !empty( $current_order_id ) && $current_order_id != '' ) {

				$current_order_id_args = array(
												'post__not_in' => array( $current_order_id )
											);
			}

			// CODE CHANGES IN QUERY TO MAKE IT COMPATIBLE WITH WC 2.0
			if ( ( $current_user->ID != 0 ) || ( $current_user->ID == 0 && $page == 'thankyou_page' ) ) {
				$get_all_orders_id_of_customers_args = array_merge( $get_all_orders_id_of_customers_args, $order_post_query_args, $current_order_id_args );

				$get_all_orders_id_of_customers_results = new WP_Query( $get_all_orders_id_of_customers_args );

				$get_all_orders_id_of_customers = ( $get_all_orders_id_of_customers_results->post_count > 0 ) ? $get_all_orders_id_of_customers_results->posts : array();

				if ( count( $get_all_orders_id_of_customers ) > 0 ) {
					$valid_order_status = array( 'wc-completed', 'wc-processing', 'wc-on-hold' );
					$get_valid_order_statuses = get_option( 'so_valid_order_statuses_for_earning', $valid_order_status );

					$get_valid_orders_args = array(
													'post_type' => 'shop_order',
													'nopaging' => true,
													'fields' => 'ids',
													'post_status' => $get_valid_order_statuses,
													'post__in' => $get_all_orders_id_of_customers
												);
					$get_valid_orders_results = new WP_Query( $get_valid_orders_args );

					$query_to_get_valid_orders = ( $get_valid_orders_results->post_count > 0 ) ? $get_valid_orders_results->posts : array();
					$valid_order_ids = count($query_to_get_valid_orders);

					if ( count( $query_to_get_valid_orders ) > 0 ) {

						$products_id = $orders_total = array();

						// WPML compat: Not converting further query because data for post_id are already extracted from WP_Query only
						$query_to_all_order_items = "SELECT pm.meta_value as order_total,
															GROUP_CONCAT(order_item_meta.meta_value ORDER BY order_item_meta.meta_key SEPARATOR '###') AS order_items_meta_value
															FROM {$wpdb->prefix}postmeta as pm
															JOIN {$wpdb->prefix}woocommerce_order_items as order_items ON (pm.post_id = order_items.order_id)
															JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON (order_items.order_item_id = order_item_meta.order_item_id )
															WHERE pm.meta_key IN ('_order_total')
															AND order_item_meta.meta_key IN ('_product_id','_variation_id')
															AND order_items.order_item_type LIKE 'line_item'
															AND pm.post_id IN ( " . implode(',', $query_to_get_valid_orders) . " )
															GROUP BY pm.post_id
													";
						$results = $wpdb->get_results($query_to_all_order_items, ARRAY_A);

						foreach ($results as $result) {

							$ids = explode('###', $result ['order_items_meta_value']);

							foreach ($ids as $id) {
								if ($id == '')
									continue;
								$products_id[] = $id;
							}

							$orders_total [] = $result ['order_total'];
						}

						$products_id = array_unique($products_id);
						sort($products_id);
						$products_id = implode(',', $products_id);

						$total_ordered = array_sum($orders_total);
					} else {
						$products_id = '';
						$total_ordered = '';
						$valid_order_ids = '';
					}
				} else {
					$products_id = '';
					$total_ordered = '';
					$valid_order_ids = '';
				}
			} else {
				$products_id = '';
				$total_ordered = '';
				$valid_order_ids = '';
			}

			$prod_ids = array();
			$product_category_ids = array();
			$prod_ids = explode(",", $products_id);
			foreach ( $prod_ids as $key => $value ) {

				// Category_id for variation product comes empty. Following logic will fetch variation parent's category and assign it to the variation.
				$product_instance = wc_get_product( $value );
				if ( $product_instance instanceof WC_Product ) {
					$parent_id = $product_instance->get_parent_id();
					if ( $parent_id == 0 ) {
						$product_category_ids[] = wp_get_post_terms( $value, 'product_cat', array('fields' => 'ids') );
					} else {
						$product_category_ids[] = wp_get_post_terms( $parent_id, 'product_cat', array('fields' => 'ids') );
					}
				}

			}

			if ( is_array( $product_category_ids ) ) {
				$product_category_ids = implode( ',', $this->so_map_array( $product_category_ids ) );
				$product_category_ids = implode( ',', array_unique( explode( ',', $product_category_ids ) ) );
			}

			$user_details = array(
									'offer_rule_registered_user' 		=> $registered_user,
									'offer_rule_registered_period' 		=> $registered_period,
									'offer_rule_has_bought' 			=> $products_id,
									'offer_rule_order_total' 			=> $total_ordered,
									'offer_rule_user_role' 				=> $user_role,
									'offer_rule_user_prod_categories' 	=> $product_category_ids,
									'offer_rule_user_orders_count'	 	=> $valid_order_ids
								);

			return $user_details;
		}

		function so_map_array( $product_category_ids ) {
			$mapped_array = array();
			foreach ( $product_category_ids as $key => $value ) {
				if ( is_array( $value ) && isset( $value[0] ) ) {
					$mapped_array[$key] = "$value[0]";
				} else {
					$mapped_array[$key] = "0";
				}
			}

			return $mapped_array;
		}

		/**
		 * Return cart details
		 */
		function get_cart_contents() {

			global $wp;

			$cart_order_contents = $cart_contains_products = $found_categories_ids = $found_categories_ids_total = $simple_attribute = $variation_attribute = $cart_products_attributes = array();

			if ( isset( WC()->cart ) ) {
				$cart_order_contents = WC()->cart->cart_contents;
			}

			if ( !empty( $wp->query_vars['order-received'] ) ) {
				$order_id = absint( $wp->query_vars['order-received'] );
				$order = wc_get_order( $order_id );
				$order_items = $order->get_items();
				if ( !empty( $order_items ) ) {
					$cart_order_contents = array_merge( $cart_order_contents, $order_items );
				}
			}

			foreach ( $cart_order_contents as $cart_item ) {

				if ( $cart_item ['variation_id'] != '' ) {
					$cart_contains_products[] = $cart_item ['variation_id'];
					$cart_contains_products [] = $cart_item ['product_id'];

					$cart_variation_product = wc_get_product( $cart_item ['variation_id'] );
					if( ( $cart_variation_product instanceof WC_Product ) ) {
						$variation_attribute[] = $cart_variation_product->get_attributes();
					}

					if ( !empty( $variation_attribute ) ) {
						foreach ($variation_attribute as $v_value) {
							foreach ($v_value as $attribute_name => $attribute_value) {
								if ( empty( $cart_products_attributes[ $attribute_name ] ) ) {
									$cart_products_attributes[ $attribute_name ] = $attribute_value;
								} else {
									$cart_products_attributes[ $attribute_name ] .= ',' . $attribute_value;
								}
							}
							
						}
					}
				} else {
					$cart_contains_products [] = $cart_item ['product_id'];

					$cart_product = wc_get_product( $cart_item ['product_id'] );
					if( ( $cart_product instanceof WC_Product ) ) {
						$simple_attribute[] = $cart_product->get_attributes();
					}

					if ( !empty( $simple_attribute ) ) {
						foreach ($simple_attribute as $value) {
							foreach ( $value as $attribute_name => $attribute ) {
								if ( $attribute->is_taxonomy() ) {
									$attribute_values = wc_get_product_terms( $cart_product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );
									foreach ( $attribute_values as $attribute_value ) {
										if ( empty( $cart_products_attributes[ $attribute->get_name() ] ) ) {
											$cart_products_attributes[ $attribute->get_name() ] = $attribute_value->slug;
										} else {
											$cart_products_attributes[ $attribute->get_name() ] .= ',' . $attribute_value->slug;
										}
									}
								} else {
									$cart_products_attributes[] = $attribute->get_options();
								}
							}
						}
					}
				}

				$get_prod_category_ids = wp_get_post_terms($cart_item ['product_id'], 'product_cat', array('fields' => 'ids'));

				if ( count($get_prod_category_ids) > 0 ) {
					$line_subtotal = (isset($cart_item['line_subtotal'])) ? $cart_item['line_subtotal'] : 0;
					$get_prod_category_ids = array_fill_keys($get_prod_category_ids, $line_subtotal);
					$found_categories_ids[] = $get_prod_category_ids;
				}
			}

			foreach ( $found_categories_ids as $found_categories_id ) {
				foreach ( $found_categories_id as $cat_id => $cat_price ) {
					if ( isset( $found_categories_ids_total[$cat_id] ) ) {
						$found_categories_ids_total[$cat_id] += $cat_price;
					} else {
						$found_categories_ids_total[$cat_id] = $cat_price;
					}
				}
			}

			$cart_contains_products = array_unique($cart_contains_products);
			asort($cart_contains_products);
			$cart_contains_products = implode(',', $cart_contains_products);

			$cart_total = $cart_grand_total = $cart_products_count = 0;
			if ( isset( WC()->cart ) ) {
				$cart_total = WC()->cart->cart_contents_total;
				$cart_grand_total = WC()->cart->total;
				$cart_products_count = WC()->cart->get_cart_contents_count();	// Get count of items from cart. Quantity gets counted as another product in count.
			}

			$cart_details = array(
									'offer_rule_cart_contains' 			=> $cart_contains_products,
									'offer_rule_total' 					=> $cart_total,
									'offer_rule_grand_total' 			=> $cart_grand_total,
									'offer_rule_cart_category_details' 	=> $found_categories_ids_total,
									'offer_rule_cart_products_count' 	=> $cart_products_count,
									'offer_rule_product_attributes'		=> $cart_products_attributes,
								);

			return $cart_details;
		}

	}
}
