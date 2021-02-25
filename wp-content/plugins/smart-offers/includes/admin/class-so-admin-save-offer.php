<?php
/**
 * Smart Offers Admin Save offer
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.1.1
 * @package     Smart Offers
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Admin_Save_Offer' ) ) {

	class SO_Admin_Save_Offer {

		function __construct() {
			add_action( 'save_post', array( $this, 'on_process_offers_meta' ), 10, 2 );
		}

		/**
		 * Save meta data for Smart Offers
		 */
		function on_process_offers_meta( $post_id, $post ) {
			global $wpdb, $sa_smart_offers;

			if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
				return;
			}
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			if ( is_int( wp_is_post_revision( $post ) ) ) {
				return;
			}
			if ( is_int( wp_is_post_autosave( $post ) ) ) {
				return;
			}
			if ( empty( $_POST ['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( $_POST ['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
				return;
			}
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
			if ( $post->post_type != 'smart_offers' ) {
				return;
			}

			$offer_rules = array(); // array to store data in serialized format
			// Delete product rules, but not the pages they need to be shown on...
			$delete_query = "DELETE FROM {$wpdb->prefix}postmeta where meta_key like 'offer_rule_%' and meta_key not like 'offer_rule_%_page' and meta_key != 'offer_rule_page_options' and post_id = $post_id ";
			$wpdb->query( $delete_query );

			clean_post_cache( $post_id );

			// To update so_offer_priority in wp_posts->menu_order field
			// remove_action => add_action => remove_action is added to prevent infinite loop
			if ( isset( $_POST['so_offer_priority'] ) ) {
				remove_action( 'save_post', array( $this, 'on_process_offers_meta' ), 10, 2 );
				wp_update_post(
					array(
						'ID'         => $_POST['post_ID'],
						'menu_order' => $_POST['so_offer_priority'],
					)
				);
				add_action( 'save_post', array( $this, 'on_process_offers_meta' ), 10, 2 );
			}

			// if any rules set in offer then enter
			if ( isset( $_POST['offer_type'] ) ) {
				$offer_type    = $_POST['offer_type'];
				$offer_action  = $_POST['offer_action'];
				$price         = $_POST['price'];
				$product_count = $_POST['product_count'];
				$orders_count  = $_POST['orders_count'];

				$i = 0;
				foreach ( $offer_type as $offer_key => $value ) {

					$offer_rules [ $i ] ['offer_type'] = $offer_type [ $offer_key ];

					if ( $offer_rules [ $i ] ['offer_type'] == 'offer_valid_between' ) {
						$offer_rules [ $i ] ['offer_action'] = $offer_rules [ $i ] ['offer_type'];

						$offer_valid_from = $_POST[ '_offer_valid_from_' . $offer_key ];
						$offer_valid_till = $_POST[ '_offer_valid_till_' . $offer_key ];

						// Dates
						if ( $offer_valid_from ) {
							$date_from = strtotime( $offer_valid_from );
						} else {
							$date_from = strtotime( date( 'Y-m-d' ) );
						}

						if ( $offer_valid_till ) {
							$date_to = strtotime( $offer_valid_till );
						} else {
							$date_to = '';
						}

						if ( $offer_valid_till && ! $offer_valid_from ) {
							$date_from = strtotime( 'NOW', current_time( 'timestamp' ) );
						}

						if ( $offer_valid_till && strtotime( $offer_valid_till ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
							$date_from = '';
							$date_to   = '';
						}

						$offer_valid_between                     = array();
						$offer_valid_between['offer_valid_from'] = $date_from;
						$offer_valid_between['offer_valid_till'] = $date_to;

						$offer_rules [ $i ] ['offer_rule_value'] = $offer_valid_between;
					} else {
						$offer_rules [ $i ] ['offer_action'] = $offer_action [ $offer_key ];

						if ( $offer_action [ $offer_key ] == 'cart_total_less' || $offer_action [ $offer_key ] == 'cart_total_more' || $offer_action [ $offer_key ] == 'cart_grand_total_less' || $offer_action [ $offer_key ] == 'cart_grand_total_more' || $offer_action [ $offer_key ] == 'total_ordered_less' || $offer_action [ $offer_key ] == 'total_ordered_more' ) {

							$offer_rules [ $i ] ['offer_rule_value'] = $price [ $offer_key ];

						} elseif ( $offer_action [ $offer_key ] == 'has_bought' || $offer_action [ $offer_key ] == 'not_bought' || $offer_action [ $offer_key ] == 'cart_doesnot_contains' ) {

							$key                                     = 'search_product_ids_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = ( ! empty( $_POST[ $key ] ) ) ? implode( ',', $_POST [ $key ] ) : '';

						} elseif ( $offer_action [ $offer_key ] == 'cart_contains' ) {

							$key                                     = 'search_product_ids_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = ( ! empty( $_POST[ $key ] ) ) ? implode( ',', $_POST [ $key ] ) : '';
							$offer_rules [ $i ] ['quantity_total']   = ( ! empty( $_POST [ 'quantity_total_' . $i ] ) ) ? $_POST [ 'quantity_total_' . $i ] : '';
							$offer_rules [ $i ] ['cart_quantity']    = ( ! empty( $_POST [ 'cart_quantity_' . $i ] ) ) ? $_POST [ 'cart_quantity_' . $i ] : '';

						} elseif ( $offer_action [ $offer_key ] == 'registered_user' ) {

							$key                                     = 'registered_user_action_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = $_POST [ $key ];

						} elseif ( $offer_action [ $offer_key ] == 'registered_period' ) {

							$key                                     = 'registered_period_action_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = $_POST [ $key ];

						} elseif ( $offer_action [ $offer_key ] == 'user_role' ) {

							$key                                     = 'user_role_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = $_POST [ $key ];

						} elseif ( $offer_action [ $offer_key ] == 'user_role_not' ) {

							$key                                     = 'user_role_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = $_POST [ $key ];

						} elseif ( $offer_action[ $offer_key ] == 'cart_prod_categories_is' ) {

							$key                                     = 'search_category_ids_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = ( ! empty( $_POST[ $key ] ) ) ? implode( ',', $_POST [ $key ] ) : '';
							$offer_rules [ $i ] ['category_total']   = ( ! empty( $_POST [ 'category_total_' . $i ] ) ) ? $_POST [ 'category_total_' . $i ] : '';
							$offer_rules [ $i ] ['category_amount']  = ( ! empty( $_POST [ 'category_amount_' . $i ] ) ) ? $_POST [ 'category_amount_' . $i ] : '';

						} elseif ( $offer_action[ $offer_key ] == 'cart_prod_categories_not_is' ) {

							$key                                     = 'search_category_ids_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = ( ! empty( $_POST[ $key ] ) ) ? implode( ',', $_POST [ $key ] ) : '';

						} elseif ( $offer_action[ $offer_key ] == 'cart_product_count_less' || $offer_action[ $offer_key ] == 'cart_product_count_more' ) {

							$offer_rules [ $i ] ['offer_rule_value'] = $product_count [ $offer_key ];

						} elseif ( $offer_action[ $offer_key ] == 'has_bought_product_categories' || $offer_action[ $offer_key ] == 'has_not_bought_product_categories' ) {

							$key                                     = 'search_category_ids_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = implode( ',', $_POST [ $key ] );

						} elseif ( $offer_action[ $offer_key ] == 'has_placed_num_orders_less' || $offer_action[ $offer_key ] == 'has_placed_num_orders_more' ) {

							$offer_rules [ $i ] ['offer_rule_value'] = $orders_count [ $offer_key ];

						} elseif ( $offer_action[ $offer_key ] == 'cart_prod_attribute_is' || $offer_action[ $offer_key ] == 'cart_prod_attribute_not_is' ) {

							$key                                     = 'cart_prod_attribute_' . $offer_key;
							$offer_rules [ $i ] ['offer_rule_value'] = $_POST [ $key ];
							$term_id [ $i ]                          = ( ! empty( $_POST [ 'cart_prod_attribute_term_' . $i ] ) ) ? implode( ',', $_POST [ 'cart_prod_attribute_term_' . $i ] ) : '';

							// We are saving term slug instead of id, hence fetching slug using following method
							if ( is_numeric( $term_id [ $i ] ) ) {
								$attribute_term [ $i ] = get_term_by( 'id', $term_id [ $i ], $offer_rules [ $i ] ['offer_rule_value'] );
							} else {
								$attribute_term [ $i ] = get_term_by( 'slug', $term_id [ $i ], $offer_rules [ $i ] ['offer_rule_value'] );
							}
							$offer_rules [ $i ] ['cart_prod_attribute_term'] = ( ! empty( $_POST [ 'cart_prod_attribute_term_' . $i ] ) && ! empty( $attribute_term [ $i ]->slug ) ) ? $attribute_term [ $i ]->slug : '';

						}
					}

					$i++;
				}
			}

			foreach ( $offer_rules as $j ) {
				if ( array_key_exists( 'offer_action', $j ) && array_key_exists( 'offer_rule_value', $j ) ) {
					$meta_key = 'offer_rule_' . $j ['offer_action'];
					update_post_meta( $post_id, $meta_key, $j ['offer_rule_value'] );
					if ( $meta_key == 'offer_rule_cart_prod_categories_is' ) {
						update_post_meta( $post_id, 'offer_rule_category_total', $j ['category_total'] );
						update_post_meta( $post_id, 'offer_rule_category_amount', $j ['category_amount'] );
					} elseif ( $meta_key == 'offer_rule_cart_contains' ) {
						update_post_meta( $post_id, 'offer_rule_quantity_total', $j ['quantity_total'] );
						update_post_meta( $post_id, 'offer_rule_cart_quantity', $j ['cart_quantity'] );
					}
				}
			}

			if ( ! empty( $_POST ['and_or'] ) ) {
				$and_or          = $_POST ['and_or'];
				$old_offer_rules = get_post_meta( $post_id, '_offer_rules', true );
				$new_offer_rules = array();
				$k               = -1;
				$index           = 0;
				foreach ( $and_or as $ar ) {
					if ( 'or' === $ar ) {
						$k++;
					}
					if ( empty( $new_offer_rules[ $k ] ) || ! is_array( $new_offer_rules[ $k ] ) ) {
						$new_offer_rules[ $k ] = array();
					}
					if ( ! empty( $offer_rules[ $index ] ) ) {
						$new_offer_rules[ $k ][] = $offer_rules[ $index ];
					}
					$index++;
				}
				$offer_rules = $new_offer_rules;
				if ( ! self::is_new_offer_rules( $old_offer_rules ) ) {
					update_post_meta( $post_id, '_offer_rules_old', $old_offer_rules );
				}
			}

			update_post_meta( $post_id, '_offer_rules', $offer_rules );

			if ( isset( $_POST ['post_title'] ) ) {
				update_post_meta( $post_id, 'offer_title', $_POST ['post_title'] );
			} else {
				delete_post_meta( $post_id, 'offer_title', array() );
			}

			$so_offer_type = isset( $_POST ['so_offer_type'] ) ? $_POST ['so_offer_type'] : '';
			if ( ! empty( $so_offer_type ) ) {
				update_post_meta( $post_id, 'so_offer_type', $so_offer_type );
			} else {
				delete_post_meta( $post_id, 'so_offer_type' );
			}

			if ( isset( $_POST ['target_product_ids'] ) ) {
				$target_products = array_values( $_POST ['target_product_ids'] );
				update_post_meta( $post_id, 'target_product_ids', implode( ',', $target_products ) );
			} else {
				update_post_meta( $post_id, 'target_product_ids', '' );
			}

			if ( isset( $_POST ['offer_price'] ) ) {
				update_post_meta( $post_id, 'offer_price', $_POST ['offer_price'] );
			} else {
				delete_post_meta( $post_id, 'offer_price' );
			}

			if ( isset( $_POST ['so_custom_css'] ) && ! empty( $_POST ['so_custom_css'] ) ) {
				update_post_meta( $post_id, 'so_custom_css', $_POST ['so_custom_css'] );
			} else {
				delete_post_meta( $post_id, 'so_custom_css' );
			}

			if ( isset( $_POST ['discount_type'] ) ) {
				update_post_meta( $post_id, 'discount_type', $_POST ['discount_type'] );
			} else {
				delete_post_meta( $post_id, 'discount_type' );
			}

			$offer_rule_page_options = array();
			if ( 'order_bump' === $so_offer_type ) {
				if ( isset( $_POST ['so_offer_position'] ) ) {
					$offer_position = $_POST['so_offer_position'];
					if ( 'before_checkout_submit' === $offer_position ) {
						update_post_meta( $post_id, 'offer_rule_before_checkout_submit_page', 'yes' );
						delete_post_meta( $post_id, 'offer_rule_after_checkout_submit_page' ); // Delete after submit option
						$offer_rule_page_options [] = 'before_checkout_submit_page';
					} elseif ( 'after_checkout_submit' === $offer_position ) {
						update_post_meta( $post_id, 'offer_rule_after_checkout_submit_page', 'yes' );
						delete_post_meta( $post_id, 'offer_rule_before_checkout_submit_page' ); // Delete before submit option
						$offer_rule_page_options [] = 'after_checkout_submit_page';
					}
					update_post_meta( $post_id, 'so_offer_position', $offer_position );
				} else {
					delete_post_meta( $post_id, 'so_offer_position' );
				}

				// Offer Content tab's field
				if ( ! empty( $_POST ['so_order_bump_lead_text'] ) ) {
					update_post_meta( $post_id, 'so_order_bump_lead_text', $_POST ['so_order_bump_lead_text'] );
				} else {
					delete_post_meta( $post_id, 'so_order_bump_lead_text' );
				}

				if ( ! empty( $_POST ['so_order_bump_intro_text'] ) ) {
					update_post_meta( $post_id, 'so_order_bump_intro_text', $_POST ['so_order_bump_intro_text'] );
				} else {
					delete_post_meta( $post_id, 'so_order_bump_intro_text' );
				}

				if ( ! empty( $_POST ['so_order_bump_body_text'] ) ) {
					update_post_meta( $post_id, 'so_order_bump_body_text', $_POST ['so_order_bump_body_text'] );
				} else {
					delete_post_meta( $post_id, 'so_order_bump_body_text' );
				}

				if ( ! empty( $_POST['so_order_bump_attachment_id'] ) ) {
					update_post_meta( $post_id, 'so_order_bump_attachment_id', $_POST ['so_order_bump_attachment_id'] );
				} else {
					delete_post_meta( $post_id, 'so_order_bump_attachment_id' );
				}

				if ( ! empty( $_POST['so_order_bump_style'] ) ) {
					update_post_meta( $post_id, 'so_order_bump_style', $_POST ['so_order_bump_style'] );
				} else {
					delete_post_meta( $post_id, 'so_order_bump_style' );
				}
			} else {
				if ( isset( $_POST ['offer_rule_home_page'] ) ) {
					update_post_meta( $post_id, 'offer_rule_home_page', $_POST ['offer_rule_home_page'] );
					$offer_rule_page_options [] = 'home_page';
				} else {
					delete_post_meta( $post_id, 'offer_rule_home_page' );
				}

				if ( isset( $_POST ['offer_rule_cart_page'] ) ) {
					$offer_rule_page_options [] = 'cart_page';
					update_post_meta( $post_id, 'offer_rule_cart_page', $_POST ['offer_rule_cart_page'] );
				} else {
					delete_post_meta( $post_id, 'offer_rule_cart_page' );
				}

				if ( isset( $_POST ['offer_rule_checkout_page'] ) ) {
					$offer_rule_page_options [] = 'checkout_page';
					update_post_meta( $post_id, 'offer_rule_checkout_page', $_POST ['offer_rule_checkout_page'] );
				} else {
					delete_post_meta( $post_id, 'offer_rule_checkout_page' );
				}

				if ( isset( $_POST ['offer_rule_post_checkout_page'] ) ) {
					$offer_rule_page_options [] = 'post_checkout_page';
					update_post_meta( $post_id, 'offer_rule_post_checkout_page', $_POST ['offer_rule_post_checkout_page'] );
				} else {
					delete_post_meta( $post_id, 'offer_rule_post_checkout_page' );
				}

				if ( isset( $_POST ['offer_rule_thankyou_page'] ) ) {
					update_post_meta( $post_id, 'offer_rule_thankyou_page', $_POST ['offer_rule_thankyou_page'] );
					$offer_rule_page_options [] = 'thankyou_page';
				} else {
					delete_post_meta( $post_id, 'offer_rule_thankyou_page' );
				}

				if ( isset( $_POST ['offer_rule_myaccount_page'] ) ) {
					update_post_meta( $post_id, 'offer_rule_myaccount_page', $_POST ['offer_rule_myaccount_page'] );
					$offer_rule_page_options [] = 'myaccount_page';
				} else {
					delete_post_meta( $post_id, 'offer_rule_myaccount_page' );
				}

				if ( isset( $_POST ['offer_rule_any_page'] ) ) {
					update_post_meta( $post_id, 'offer_rule_any_page', $_POST ['offer_rule_any_page'] );
					$offer_rule_page_options [] = 'any_page';
				} else {
					delete_post_meta( $post_id, 'offer_rule_any_page' );
				}
			}

			if ( $offer_rule_page_options ) {
				$page_options_value = implode( ',', $offer_rule_page_options );
				update_post_meta( $post_id, 'offer_rule_page_options', $page_options_value );
			} else {
				delete_post_meta( $post_id, 'offer_rule_page_options' );
			}

			if ( 'order_bump' === $so_offer_type ) {
				update_post_meta( $post_id, 'so_show_offer_as', 'offer_as_inline' ); // Order bump offers are always inline.
			} else {
				if ( isset( $_POST ['so_show_offer_as'] ) ) {
					update_post_meta( $post_id, 'so_show_offer_as', $_POST ['so_show_offer_as'] );
				} else {
					delete_post_meta( $post_id, 'so_show_offer_as' );
				}
			}

			$actions_on_accept = array();

			if ( 'order_bump' === $so_offer_type ) {
				$actions_on_accept['add_to_cart'] = 'yes';
			} else {
				// If checked, add the offered product to the cart
				if ( isset( $_POST['sa_add_to_cart'] ) ) {
					$actions_on_accept['add_to_cart'] = 'yes';
				} else {
					$actions_on_accept['add_to_cart'] = 'no';
				}
				// Remove products from cart if specified
				if ( isset( $_POST['sa_remove_prods_from_cart'] ) ) {
					$prods_ids_to_remove = array();
					$prods_ids_to_remove = $_POST ['remove_prods_from_cart'];

					if ( in_array( 'all', $prods_ids_to_remove ) ) {
						$actions_on_accept[ $_POST ['sa_remove_prods_from_cart'] ] = 'all';
					} else {
						$prods_ids_to_remove = array();
						$prods_ids_to_remove = $_POST ['remove_prods_from_cart'];

						if ( count( $prods_ids_to_remove ) > 0 ) {
							$prod_ids = implode( ',', $prods_ids_to_remove );
							$actions_on_accept[ $_POST ['sa_remove_prods_from_cart'] ] = $prod_ids;
						}
					}
				}

				// Apply coupons if specifed
				if ( isset( $_POST['sa_apply_coupon'] ) ) {
					if ( ! empty( $_POST ['sa_coupon_title'] ) ) {
						$apply_coupons = array();
						$apply_coupons = $_POST ['sa_coupon_title'];
					}

					if ( count( $apply_coupons ) > 0 ) {
						$coupons = implode( ',', $apply_coupons );
						$actions_on_accept[ $_POST ['sa_apply_coupon'] ] = $coupons;
					}
				}

				// Do not apply any coupon to the offered product of this offer if checked
				if ( isset( $_POST['sa_no_coupon'] ) ) {
					$actions_on_accept['sa_no_coupon'] = 'yes';
				} else {
					$actions_on_accept['sa_no_coupon'] = 'no';
				}

				// Show another offer
				if ( isset( $_POST['accepted_offer_ids'] ) ) {
					$offer_ids_on_accept = array();
					if ( ! empty( $_POST ['accept_offer_ids'] ) ) {
						$so_offer_ids_on_accept = $_POST ['accept_offer_ids'];
						foreach ( $so_offer_ids_on_accept as $so_offer_id_on_accept ) {
							$offer_status = get_post_status( $so_offer_id_on_accept );
							if ( $offer_status == 'publish' ) {
								$offer_ids_on_accept [] = $so_offer_id_on_accept;
							}
						}
					}

					if ( count( $offer_ids_on_accept ) > 0 ) {
						$accept_ids = implode( ',', $offer_ids_on_accept );
						$actions_on_accept[ $_POST ['accepted_offer_ids'] ] = $accept_ids;
					}
				}

				// Redirect to another url
				if ( isset( $_POST['sa_redirect_to_url'] ) ) {
					if ( isset( $_POST ['accept_redirect_url'] ) && ! empty( $_POST ['accept_redirect_url'] ) ) {
						$actions_on_accept[ $_POST ['sa_redirect_to_url'] ] = $_POST ['accept_redirect_url'];
					}
				}

				// Checkout with Buy Now plugin
				if ( isset( $_POST['sa_buy_now'] ) ) {
					$actions_on_accept['buy_now'] = true;
				}
			}

			// Update accept actions in serialized format
			if ( $actions_on_accept ) {
				update_post_meta( $post_id, 'so_actions_on_accept', $actions_on_accept );
			} else {
				delete_post_meta( $post_id, 'so_actions_on_accept' );
			}

			if ( 'order_bump' === $so_offer_type ) {
				update_post_meta( $post_id, 'sa_smart_offer_if_denied', 'order_page' );
			} else {
				if ( isset( $_POST ['sa_smart_offer_if_denied'] ) ) {
					update_post_meta( $post_id, 'sa_smart_offer_if_denied', $_POST ['sa_smart_offer_if_denied'] );
					if ( $_POST ['sa_smart_offer_if_denied'] == 'url' ) {
						$text_option = 'text_' . $_POST ['sa_smart_offer_if_denied'];
						update_post_meta( $post_id, 'url', $_POST [ $text_option ] );
					} elseif ( $_POST ['sa_smart_offer_if_denied'] == 'offer_page' ) {
						if ( ! empty( $_POST ['offer_ids'] ) ) {
							$offers = array();
							$ids    = $_POST ['offer_ids'];
							foreach ( $ids as $id ) {
								$offer_status = get_post_status( $id );
								if ( $id && $id > 0 && $offer_status == 'publish' ) {
									$offers [] = $id;
								}
							}
							update_post_meta( $post_id, 'url', implode( ',', $offers ) );
						}
					} elseif ( $_POST ['sa_smart_offer_if_denied'] == 'particular_page' ) {
						update_post_meta( $post_id, 'url', $_POST ['page_id'] );
					} else {
						delete_post_meta( $post_id, 'url' );
					}
				} else {
					update_post_meta( $post_id, 'sa_smart_offer_if_denied', 'order_page' );
					// if its "order_page", then do not save url
					delete_post_meta( $post_id, 'url' );
				}

				// NEWLY ADDED CODE TO REMOVE SKIPPED IDS FROM CUSTOMERS RECORD IF IT IS UNCHECKED.
				$skip_permanently = get_post_meta( $post_id, 'sa_smart_offer_if_denied_skip_permanently', true );

				if ( $skip_permanently && ! isset( $_POST['sa_smart_offer_if_denied_skip_permanently'] ) ) {

					$users_skipped_ids_args = array(
						'meta_query' => array(
							array(
								'key' => 'customer_skipped_offers',
							),
						),
						'fields'     => 'ID',
					);

					// The User Query
					$users_skipped_ids = new WP_User_Query( $users_skipped_ids_args );

					$new_skipped_ids = array();

					if ( $users_skipped_ids->total_users > 0 ) {

						foreach ( $users_skipped_ids->results as $user_id ) {

							$skipped_ids = get_user_meta( $user_id, 'customer_skipped_offers', true );

							if ( in_array( $post_id, $skipped_ids ) ) {
								$key = array_search( $post_id, $skipped_ids );
								unset( $skipped_ids [ $key ] );
								$new_skipped_ids[ $user_id ] = $skipped_ids;
							}
						}
					}

					$query_case = array();
					$user_ids   = array();

					if ( count( $new_skipped_ids > 0 ) ) {

						$wpdb->query( 'SET SESSION group_concat_max_len=999999' );
						foreach ( $new_skipped_ids as $id => $meta_value ) {

							$user_ids[]   = $id;
							$query_case[] = 'WHEN ' . $id . " THEN '" . $wpdb->_real_escape( maybe_serialize( $meta_value ) ) . "'";
						}
						$update_query_for_customer_skipped_ids = "UPDATE {$wpdb->prefix}usermeta  
																						SET meta_value = CASE user_id " . implode( "\n", $query_case ) . ' 
																						END 
																						WHERE user_id IN (' . implode( ',', $user_ids ) . ")
																						AND meta_key = 'customer_skipped_offers'
																						";
					}

					$wpdb->query( $update_query_for_customer_skipped_ids );
				}

				if ( isset( $_POST ['sa_smart_offer_if_denied_skip_permanently'] ) ) {
					update_post_meta( $post_id, 'sa_smart_offer_if_denied_skip_permanently', $_POST ['sa_smart_offer_if_denied_skip_permanently'] );
				} else {
					delete_post_meta( $post_id, 'sa_smart_offer_if_denied_skip_permanently' );
				}

				// If we don't find accept / skip link in Offer Description
				$position_accept = strpos( $_POST ['content'], '[so_acceptlink' );
				$position_skip   = strpos( $_POST ['content'], '[so_skiplink' );
				$sc_position     = strpos( $_POST ['content'], '[so_product_variants' );

				if ( ! $position_accept || ! $position_skip ) {
					$url                   = admin_url( 'post.php?action=edit&message=2&post=' . $post_id );
					$offered_prod_instance = wc_get_product( implode( ',', $target_products ) );
					if ( ( $offered_prod_instance instanceof WC_Product ) ) {
						if ( $sc_position === false && ( $offered_prod_instance->is_type( 'variable' ) || $offered_prod_instance->is_type( 'variable-subscription' ) ) ) {
							$url = add_query_arg( 'show_sc_msg', true, $url );
						}
					}
					wp_safe_redirect( $url );
					exit();
				}
			}
		}

		static function is_new_offer_rules( $offer_rules = array() ) {
			if ( isset( $offer_rules[0][0] ) ) {
				return true;
			}
			return false;
		}

		static function get_offer_rules( $offer_id = 0 ) {
			if ( empty( $offer_id ) ) {
				return array();
			}
			$offer_rules = get_post_meta( $offer_id, '_offer_rules', true );
			if ( self::is_new_offer_rules( $offer_rules ) ) {
				return $offer_rules;
			}
			return array( $offer_rules );
		}

	}

	return new SO_Admin_Save_Offer();
}
