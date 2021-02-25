<?php
/**
 * Main class file for the plugin
 *
 * @author      StoreApps
 * @since       1.0.0
 * @version     1.4.1
 *
 * @package     save-for-later-for-woocommerce/includes/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_Save_For_Later' ) ) {

	/**
	 * Main class
	 */
	class SA_Save_For_Later {

		/**
		 * Variable to hold instance of SA_Save_For_Later
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Saved items
		 *
		 * @var array $saved_items
		 */
		private $saved_items;

		/**
		 * The constructor
		 */
		public function __construct() {

			if ( ! $this->is_wc_gte_25() ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_sa_needs_wc_25_above' ) );
			}

			add_action( 'wp_loaded', array( $this, 'save_for_later_action_handler' ) );
			add_action( 'wp_loaded', array( $this, 'move_to_cart_action_handler' ) );
			add_action( 'wp_loaded', array( $this, 'move_saved_items_from_cookies_to_account' ) );
			add_action( 'wp_loaded', array( $this, 'save_for_later_hooks' ) );

			add_action( 'woocommerce_after_cart_table', array( $this, 'show_saved_items_list' ) );
			add_action( 'woocommerce_cart_is_empty', array( $this, 'show_saved_items_list' ) );

			add_filter( 'sa_saved_items_list_template', array( $this, 'saved_items_list_template_path' ) );

			add_action( 'wp_ajax_sa_delete_saved_item', array( $this, 'sa_delete_saved_item' ) );
			add_action( 'wp_ajax_nopriv_sa_delete_saved_item', array( $this, 'sa_delete_saved_item' ) );

			add_action( 'admin_init', array( $this, 'sa_sfl_activated' ) );
			add_filter( 'sa_is_page_for_notifications', array( $this, 'sa_sfl_is_page_for_notifications' ), 10, 2 );

			if ( $this->is_wc_gte_30() ) {
				add_filter( 'woocommerce_get_sections_products', array( $this, 'sfl_register_section' ) );
				add_filter( 'woocommerce_get_settings_products', array( $this, 'sfl_add_settings' ), 10, 2 );
				// Filter to add Quick Help Widget.
				add_filter( 'sa_active_plugins_for_quick_help', array( $this, 'sfl_active_plugins_for_quick_help' ), 10, 2 );
				// Fitler to add Settings link on plugins page.
				add_filter( 'plugin_action_links_' . plugin_basename( SA_SFL_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );
			}

		}

		/**
		 * Get single instance of SA_Save_For_Later
		 *
		 * @return SA_Save_For_Later Singleton object of SA_Save_For_Later
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * To handle WC compatibility related function call from appropriate class
		 *
		 * @param string $function_name The function name.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 *
		 * @return The result of function call
		 */
		public function __call( $function_name, $arguments = array() ) {

			if ( ! is_callable( 'SA_WC_Compatibility_3_4', $function_name ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_3_4::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_3_4::' . $function_name );
			}

		}

		/**
		 * Function to show admin notice that Save For Later works with WC 2.5+
		 */
		public function admin_notice_sa_needs_wc_25_above() {
			?>
			<div class="updated error">
				<p>
				<?php
					/* translators: 1. Title of message  2. Link to WordPress Upgrade page */
					echo wp_kses_post( sprintf( __( '%1$s Save For Later is active but it will only work with WooCommerce 2.5+. %2$s.', 'save-for-later-for-woocommerce' ), '<strong>' . __( 'Important', 'save-for-later-for-woocommerce' ) . ':</strong>', '<a href="' . admin_url( 'plugins.php?plugin_status=upgrade' ) . '" target="_blank" >' . __( 'Please update WooCommerce to the latest version', 'save-for-later-for-woocommerce' ) . '</a>' ) );
				?>
				</p>
			</div>
			<?php
		}

		/**
		 * To get saved items
		 */
		public function get_saved_items() {

			$user_id = get_current_user_id();

			if ( 0 === $user_id ) {
				$saved_items = $this->get_saved_item_from_cookie();
			} else {
				$saved_items = $this->get_saved_item_from_user_account();
			}

			$cart_items = $this->create_cart_items( $saved_items );

			$this->saved_items = $cart_items;

			return $this->saved_items;

		}

		/**
		 * To handle Save For Later action, when save for later link is clicked
		 */
		public function save_for_later_action_handler() {

			$var_sa_save_for_later = ( ! empty( $_GET['sa_save_for_later'] ) ) ? $_GET['sa_save_for_later'] : ''; // phpcs:ignore
			$var_wpnonce           = ( ! empty( $_GET['_wpnonce'] ) ) ? $_GET['_wpnonce'] : ''; // phpcs:ignore

			if ( ! empty( $var_sa_save_for_later ) && isset( $var_wpnonce ) && wp_verify_nonce( $var_wpnonce, 'sa-save-for-later-cart' ) ) {

				$cart_item_key = sanitize_text_field( $var_sa_save_for_later );

				WC()->cart->get_cart_from_session();

				$cart_item = WC()->cart->get_cart_item( $cart_item_key );

				if ( ! empty( $cart_item ) ) {

					$this->save_for_later( $cart_item_key );

					$product = wc_get_product( $cart_item['product_id'] );

					$item_removed_title = apply_filters( 'sa_sfl_cart_item_removed_title', $product ? $product->get_title() : __( 'Item', 'save-for-later-for-woocommerce' ), $cart_item );

					// Don't show undo link if saved item is out of stock.
					if ( $product->is_in_stock() && $product->has_enough_stock( $cart_item['quantity'] ) ) {
						$undo = $this->get_move_to_cart_url( $cart_item_key );
						/* translators: 1. Removed item's name   2. Anchor tag start for undoing save for later action   3. Anchor tag close for undoing save for later action */
						wc_add_notice( sprintf( __( '%1$s saved for later. %2$sUndo?%3$s', 'save-for-later-for-woocommerce' ), $item_removed_title, '<a href="' . esc_url( $undo ) . '">', '</a>' ) );
					} else {
						/* translators: Removed item's name */
						wc_add_notice( sprintf( __( '%s saved for later.', 'save-for-later-for-woocommerce' ), $item_removed_title ) );
					}
				}

				$referer = wp_get_referer() ? remove_query_arg( array( 'remove_item', 'removed_item', 'add-to-cart', 'added-to-cart', 'sa_save_for_later' ), add_query_arg( 'sa_save_for_later', '1', wp_get_referer() ) ) : wc_get_cart_url();
				wp_safe_redirect( $referer );
				exit;

			}

		}

		/**
		 * To save cart item
		 *
		 * @param string $cart_item_key The cart item key.
		 */
		public function save_for_later( $cart_item_key = null ) {

			if ( empty( $cart_item_key ) ) {
				return;
			}

			$user_id = get_current_user_id();

			if ( 0 === $user_id ) {
				$saved = $this->save_cart_item_in_cookie( $cart_item_key );
			} else {
				$saved = $this->save_cart_item_in_user_account( $cart_item_key );
			}

			if ( $saved ) {
				WC()->cart->set_quantity( $cart_item_key, 0 );
			}

		}

		/**
		 * To save cart item in cookie
		 *
		 * @param string $cart_item_key The cart item key.
		 * @return bool saved or not
		 */
		public function save_cart_item_in_cookie( $cart_item_key = null ) {

			if ( empty( $cart_item_key ) ) {
				return false;
			}

			global $sa_save_for_later;

			WC()->cart->get_cart_from_session();

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			if ( empty( $cart_item ) ) {
				return false;
			}

			$cookie_sa_saved_for_later_profile_id = ( ! empty( $_COOKIE['sa_saved_for_later_profile_id'] ) ) ? $_COOKIE['sa_saved_for_later_profile_id'] : ''; // phpcs:ignore

			if ( empty( $cookie_sa_saved_for_later_profile_id ) ) {
				$unique_id = $this->generate_unique_id();
			} else {
				$unique_id = $cookie_sa_saved_for_later_profile_id;
			}

			$saved_for_later_products = get_option( 'sa_saved_for_later_profile_' . $unique_id, array() );

			$product_id = ( ! empty( $cart_item['variation_id'] ) ) ? $cart_item['variation_id'] : $cart_item['product_id'];

			$update = false;

			if ( $sa_save_for_later->is_wc_gte_30() ) {
				$cart_item_price = $cart_item['data']->get_price();
			} else {
				$cart_item_price = $cart_item['data']->price;
			}

			if ( ! array_key_exists( $product_id, $saved_for_later_products ) ) {
				$saved_for_later_products[ $product_id ] = array(
					'product_id' => $product_id,
					'price'      => $cart_item_price,
				);
				$update                                  = true;
			} else {
				return true;
			}

			if ( $update ) {

				update_option( 'sa_saved_for_later_profile_' . $unique_id, $saved_for_later_products );

				// Save saved for later profile id.
				wc_setcookie( 'sa_saved_for_later_profile_id', $unique_id, $this->get_cookie_life() );

				return true;

			}

			return false;
		}

		/**
		 * To save cart item in user account
		 *
		 * @param string $cart_item_key The cart item key.
		 * @return bool saved or not
		 */
		public function save_cart_item_in_user_account( $cart_item_key = null ) {

			global $sa_save_for_later;

			if ( empty( $cart_item_key ) ) {
				return false;
			}

			$user_id = get_current_user_id();

			WC()->cart->get_cart_from_session();

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			if ( empty( $cart_item ) ) {
				return false;
			}

			if ( function_exists( 'get_user_attribute' ) ) {
				$saved_items = get_user_attribute( $user_id, '_sa_sfl_saved_items' );
			} else {
				$saved_items = get_user_meta( $user_id, '_sa_sfl_saved_items', true ); // phpcs:ignore
			}

			if ( empty( $saved_items ) || ! is_array( $saved_items ) ) {
				$saved_items = array();
			}

			if ( $sa_save_for_later->is_wc_gte_30() ) {
				$cart_item_price = $cart_item['data']->get_price();
			} else {
				$cart_item_price = $cart_item['data']->price;
			}

			$saved_item = array(
				'product_id' => ( ! empty( $cart_item['variation_id'] ) ) ? $cart_item['variation_id'] : $cart_item['product_id'],
				'price'      => $cart_item_price,
			);

			if ( ! empty( $saved_item ) ) {
				if ( ! in_array( $saved_item, $saved_items, true ) ) {
					$saved_items[] = $saved_item;
					if ( function_exists( 'update_user_attribute' ) ) {
						update_user_attribute( $user_id, '_sa_sfl_saved_items', $saved_items );
					} else {
						update_user_meta( $user_id, '_sa_sfl_saved_items', $saved_items ); // phpcs:ignore
					}
				}
				return true;
			}

			return false;
		}

		/**
		 * To handle Move To Cart action, when move to cart link is clicked
		 */
		public function move_to_cart_action_handler() {

			$var_sa_move_to_cart = ( ! empty( $_GET['sa_move_to_cart'] ) ) ? $_GET['sa_move_to_cart'] : ''; // phpcs:ignore
			$var_wpnonce         = ( ! empty( $_GET['_wpnonce'] ) ) ? $_GET['_wpnonce'] : ''; // phpcs:ignore

			if ( ! empty( $var_sa_move_to_cart ) && isset( $var_wpnonce ) && wp_verify_nonce( $var_wpnonce, 'sa-move-to-cart' ) ) {

				$cart_item_key = sanitize_text_field( $var_sa_move_to_cart );

				$moved = $this->move_to_cart( $cart_item_key );

				if ( ! empty( $moved ) ) {
					$_product = $moved['data'];
					/* translators: Product's name */
					wc_add_notice( sprintf( __( 'Moved %s to cart', 'save-for-later-for-woocommerce' ), $_product->get_title() ) );
				}

				$referer = wp_get_referer() ? remove_query_arg( array( 'undo_item', '_wpnonce', 'sa_move_to_cart' ), wp_get_referer() ) : wc_get_cart_url();
				wp_safe_redirect( $referer );
				exit;

			}

		}

		/**
		 * To move saved item back to cart
		 *
		 * @param string $cart_item_key The cart item key.
		 *
		 * @return mixed Will return cart_item, if moved successfully, false otherwise
		 */
		public function move_to_cart( $cart_item_key = null ) {

			global $sa_save_for_later;

			if ( empty( $cart_item_key ) ) {
				return;
			}

			WC()->cart->get_cart_from_session();

			$cart_items = $this->get_saved_items();

			if ( ! empty( $cart_items[ $cart_item_key ] ) ) {
				WC()->cart->cart_contents[ $cart_item_key ] = $cart_items[ $cart_item_key ];
				WC()->cart->set_session();

				if ( $sa_save_for_later->is_wc_gte_30() ) {
					$cart_item_id = $cart_items[ $cart_item_key ]['data']->get_id();
					if ( ( ! empty( $cart_item_id ) ) ) {
						$product_id = $cart_items[ $cart_item_key ]['data']->get_id();
					}
				} else {
					$product_id = ( ! empty( $cart_items[ $cart_item_key ]['data']->variation_id ) ) ? $cart_items[ $cart_item_key ]['data']->variation_id : $cart_items[ $cart_item_key ]['data']->id;
				}

				$deleted = $this->delete_saved_item( $product_id );
				if ( $deleted ) {
					return WC()->cart->cart_contents[ $cart_item_key ];
				}
			}

			return false;

		}

		/**
		 * To delete saved item
		 *
		 * @param int $product_id The product's id.
		 *
		 * @return bool $deleted whether deleted or not
		 */
		public function delete_saved_item( $product_id = null ) {

			if ( empty( $product_id ) ) {
				return;
			}

			$user_id = get_current_user_id();

			if ( 0 === $user_id ) {
				$deleted = $this->delete_saved_item_from_cookie( $product_id );
			} else {
				$deleted = $this->delete_saved_item_from_user_account( $product_id );
			}

			return $deleted;

		}

		/**
		 * To delete saved items from cookies
		 *
		 * @param int $product_id The product's id.
		 *
		 * @return bool $deleted
		 */
		public function delete_saved_item_from_cookie( $product_id = null ) {

			if ( empty( $product_id ) ) {
				return false;
			}

			$cookie_sa_saved_for_later_profile_id = ( ! empty( $_COOKIE['sa_saved_for_later_profile_id'] ) ) ? $_COOKIE['sa_saved_for_later_profile_id'] : ''; // phpcs:ignore

			if ( ! empty( $cookie_sa_saved_for_later_profile_id ) ) {

				$unique_id = $cookie_sa_saved_for_later_profile_id;

				$saved_for_later_products = get_option( 'sa_saved_for_later_profile_' . $unique_id );

				$update = false;

				if ( false !== $saved_for_later_products && is_array( $saved_for_later_products ) ) {

					if ( array_key_exists( $product_id, $saved_for_later_products ) ) {

						unset( $saved_for_later_products[ $product_id ] );
						$update = true;

					}
				}

				if ( $update ) {

					update_option( 'sa_saved_for_later_profile_' . $unique_id, $saved_for_later_products );

					return true;

				}
			}

			return false;

		}

		/**
		 * To delete saved items from user account
		 *
		 * @param int $product_id The product id.
		 *
		 * @return bool $deleted
		 */
		public function delete_saved_item_from_user_account( $product_id = null ) {

			if ( empty( $product_id ) ) {
				return false;
			}

			$user_id = get_current_user_id();

			if ( function_exists( 'get_user_attribute' ) ) {
				$saved_items = get_user_attribute( $user_id, '_sa_sfl_saved_items' );
			} else {
				$saved_items = get_user_meta( $user_id, '_sa_sfl_saved_items', true ); // phpcs:ignore
			}

			if ( ! empty( $saved_items ) ) {

				$update = false;

				foreach ( $saved_items as $index => $saved_item ) {

					if ( intval( $product_id ) === intval( $saved_item['product_id'] ) ) {
						unset( $saved_items[ $index ] );
						$update = true;
					}
				}

				if ( $update ) {
					if ( function_exists( 'update_user_attribute' ) ) {
						update_user_attribute( $user_id, '_sa_sfl_saved_items', $saved_items );
					} else {
						update_user_meta( $user_id, '_sa_sfl_saved_items', $saved_items ); // phpcs:ignore
					}
					return true;
				}
			}

			return false;

		}

		/**
		 * To move saved items from cookies to user account as soon as they logged in
		 */
		public function move_saved_items_from_cookies_to_account() {

			$user_id = get_current_user_id();

			$cookie_sa_saved_for_later_profile_id = ( ! empty( $_COOKIE['sa_saved_for_later_profile_id'] ) ) ? $_COOKIE['sa_saved_for_later_profile_id'] : ''; // phpcs:ignore

			if ( $user_id > 0 && ! empty( $cookie_sa_saved_for_later_profile_id ) ) {

				$unique_id = $cookie_sa_saved_for_later_profile_id;

				$saved_for_later_products = get_option( 'sa_saved_for_later_profile_' . $unique_id );

				if ( false !== $saved_for_later_products && is_array( $saved_for_later_products ) && ! empty( $saved_for_later_products ) ) {

					if ( function_exists( 'get_user_attribute' ) ) {
						$saved_items = get_user_attribute( $user_id, '_sa_sfl_saved_items' );
					} else {
						$saved_items = get_user_meta( $user_id, '_sa_sfl_saved_items', true ); // phpcs:ignore
					}
					if ( empty( $saved_items ) || ! is_array( $saved_items ) ) {
						$saved_items = array();
					}
					$saved_items = array_merge( $saved_items, $saved_for_later_products );
					if ( function_exists( 'update_user_attribute' ) ) {
						update_user_attribute( $user_id, '_sa_sfl_saved_items', $saved_items );
					} else {
						update_user_meta( $user_id, '_sa_sfl_saved_items', $saved_items ); // phpcs:ignore
					}
					wc_setcookie( 'sa_saved_for_later_profile_id', '' );
					delete_option( 'sa_saved_for_later_profile_' . $unique_id );

				}
			}

		}

		/**
		 * Hooks to handle display of save for later actions
		 */
		public function save_for_later_hooks() {

			$show_on_remove = get_option( 'sa_sfl_show_on_remove_cart_item_button', 'yes' );

			if ( $this->is_wc_gte_30() && 'no' === $show_on_remove ) {
				add_action( 'woocommerce_after_cart_item_name', array( $this, 'sfl_action_after_cart_item_name' ), 10, 2 );
				add_action( 'wp_footer', array( $this, 'link_styles_and_scripts' ) );
			} else {
				add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'cart_item_save_for_later_link' ), 10, 2 );
				add_action( 'wp_ajax_get_save_for_later_actions', array( $this, 'get_save_for_later_actions' ) );
				add_action( 'wp_ajax_nopriv_get_save_for_later_actions', array( $this, 'get_save_for_later_actions' ) );
				add_action( 'wp_footer', array( $this, 'tiptip_styles_and_scripts' ) );
			}

		}

		/**
		 * To display saved items
		 */
		public function show_saved_items_list() {

			$user_id = get_current_user_id();

			if ( 0 === $user_id ) {
				$saved_items = $this->get_saved_item_from_cookie();
			} else {
				$saved_items = $this->get_saved_item_from_user_account();
			}

			$cart_items = $this->create_cart_items( $saved_items );

			if ( count( $cart_items ) > 0 ) {

				$js = "
						jQuery('.sa_saved_items_list_wrapper').on('click', '.sa_saved_item_actions a.sa_delete_saved_item', function(){
							var element = jQuery(this);
							element.closest('tr').css('opacity', '0.3');
							var saved_item_count = jQuery('.sa_saved_items_list_container span.sa_saved_item_count').text();
							saved_item_count = parseInt( saved_item_count );
							jQuery.ajax({
								url: '" . admin_url( 'admin-ajax.php' ) . "',
								type: 'post',
								dataType: 'json',
								data: {
									action: 'sa_delete_saved_item',
									product_id: element.data('product_id'),
									security: '" . wp_create_nonce( 'sa-saved-item-list-action' ) . "'
								},
								success: function( response ){
									if ( response.success == 'true' ) {
										element.parent().parent().parent().hide('slow', function(){
											element.parent().parent().parent().remove();
										});
										saved_item_count--;
										if ( saved_item_count == 0 ) {
											jQuery('.sa_saved_items_list_wrapper').remove();
										} else if ( saved_item_count == 1 ) {
											jQuery('.sa_saved_items_list_container h2:first').html('" . __( 'Saved for later', 'save-for-later-for-woocommerce' ) . " (<span class=\"sa_saved_item_count\">1</span> item)');
										} else {
											jQuery('.sa_saved_items_list_container h2:first').html('" . __( 'Saved for later', 'save-for-later-for-woocommerce' ) . " (<span class=\"sa_saved_item_count\">' + saved_item_count + '</span> " . __( 'items', 'save-for-later-for-woocommerce' ) . ")');
										}
									} else {
										console.log('" . __( 'Error', 'save-for-later-for-woocommerce' ) . "');
									}
								}
							});
						});
						";

				wc_enqueue_js( $js );

				include apply_filters( 'sa_saved_items_list_template', 'templates/saved-items-list.php' );

			}

		}

		/**
		 * Allow overridding of Saved Item's List template
		 *
		 * @param string $template The template name.
		 * @return mixed $template
		 */
		public function saved_items_list_template_path( $template ) {

			$template_name = 'saved-items-list.php';

			$template = $this->locate_template( $template_name, $template );

			// Return what we found.
			return $template;

		}

		/**
		 * Locate template for displaying saved items
		 *
		 * @param string $template_name The template name.
		 * @param mixed  $template Found template.
		 * @return mixed $template
		 */
		public function locate_template( $template_name = '', $template = '' ) {

			$default_path = untrailingslashit( dirname( dirname( __FILE__ ) ) ) . '/templates/';

			$plugin_base_dir = trailingslashit( dirname( dirname( __FILE__ ) ) );

			// Look within passed path within the theme - this is priority.
			$template = locate_template(
				array(
					'woocommerce/' . $plugin_base_dir . $template_name,
					$plugin_base_dir . $template_name,
					$template_name,
				)
			);

			// Get default template.
			if ( ! $template ) {
				$template = $default_path . $template_name;
			}

			return $template;
		}

		/**
		 * To create cart items in same format in which it will be added to WooCommerce Cart
		 *
		 * @param array $saved_items The saved items.
		 *
		 * @return array $cart_items
		 */
		public function create_cart_items( $saved_items = null ) {

			global $sa_save_for_later;

			if ( empty( $saved_items ) ) {
				return array();
			}

			$cart_items = array();

			foreach ( $saved_items as $saved_item ) {

				$product = wc_get_product( $saved_item['product_id'] );

				if ( $sa_save_for_later->is_wc_gte_30() ) {
					$product_id = $product->get_id();
				} else {
					$product_id = $product->id;
				}

				if ( empty( $product_id ) ) {
					continue;
				}

				if ( $sa_save_for_later->is_wc_gte_30() ) {
					$variation_parent_id = $product->get_parent_id();
					$variation_id        = $product->get_id();
					$variation           = ( ! empty( $variation_parent_id ) && ( 0 !== $variation_parent_id ) ) ? $product->get_variation_attributes() : array();
				} else {
					$variation_id = ( ! empty( $product->variation_id ) ) ? $product->variation_id : null;
					$variation    = ( ! empty( $variation_id ) ) ? $product->variation_data : array();
				}

				$cart_item_data = array();

				// Load cart item data when adding to cart.
				$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id, 1 );

				// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
				$cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

				// See if this product and its options is already in the cart.
				$cart_item_key = WC()->cart->find_product_in_cart( $cart_id );

				// If cart_item_key is set, the item is already in the cart.
				if ( ! $cart_item_key ) {

					$cart_item_key = $cart_id;

					// Get the product.
					$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

					$cart_item = apply_filters(
						'woocommerce_add_cart_item',
						array_merge(
							$cart_item_data,
							array(
								'product_id'   => $product_id,
								'variation_id' => $variation_id,
								'variation'    => $variation,
								'quantity'     => 1,
								'data'         => $product_data,
							)
						),
						$cart_item_key
					);

					$cart_items[ $cart_item_key ] = $cart_item;

				}
			}

			return $cart_items;

		}

		/**
		 * To get saved items list from cookie
		 */
		public function get_saved_item_from_cookie() {

			$saved_items = array();

			$cookie_sa_saved_for_later_profile_id = ( ! empty( $_COOKIE['sa_saved_for_later_profile_id'] ) ) ? $_COOKIE['sa_saved_for_later_profile_id'] : ''; // phpcs:ignore

			if ( ! empty( $cookie_sa_saved_for_later_profile_id ) ) {

				$unique_id = $cookie_sa_saved_for_later_profile_id;

				$saved_items = get_option( 'sa_saved_for_later_profile_' . $unique_id );

			}

			return $saved_items;

		}

		/**
		 * To get saved items list from user account
		 */
		public function get_saved_item_from_user_account() {

			$user_id = get_current_user_id();

			if ( function_exists( 'get_user_attribute' ) ) {
				$saved_items = get_user_attribute( $user_id, '_sa_sfl_saved_items' );
			} else {
				$saved_items = get_user_meta( $user_id, '_sa_sfl_saved_items', true ); // phpcs:ignore
			}

			if ( empty( $saved_items ) ) {
				$saved_items = array();
			}

			return $saved_items;

		}

		/**
		 * To get Save For Later actions html
		 *
		 * @param string $cart_item_key The cart item key.
		 *
		 * @return string HTML code for Save For Later actions
		 */
		public function get_save_for_later_actions_html( $cart_item_key = null ) {

			if ( empty( $cart_item_key ) ) {
				return null;
			}

			WC()->cart->get_cart_from_session();

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			$_product = $cart_item['data'];

			$product_id = ( ! empty( $cart_item['variation_id'] ) ) ? $cart_item['variation_id'] : $cart_item['product_id'];

			if ( $this->is_wc_gte_33() ) {
				$remove_link_html = sprintf(
					'<a href="%s" title="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
					esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
					esc_html__( 'Delete from cart', 'save-for-later-for-woocommerce' ),
					esc_attr( $product_id ),
					esc_attr( $_product->get_sku() ),
					esc_html__( 'Delete from cart', 'save-for-later-for-woocommerce' )
				);
			} else {
				$remove_link_html = sprintf(
					'<a href="%s" title="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
					esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
					esc_html__( 'Delete from cart', 'save-for-later-for-woocommerce' ),
					esc_attr( $product_id ),
					esc_attr( $_product->get_sku() ),
					esc_html__( 'Delete from cart', 'save-for-later-for-woocommerce' )
				);
			}

			$save_for_later_link_html = sprintf(
				'<a href="%s" class="button sa_save_for_later" title="%s" data-product_id="%s">%s</a>',
				esc_url( $this->get_save_for_later_url( $cart_item_key ) ),
				esc_html__( 'Save for later', 'save-for-later-for-woocommerce' ),
				esc_attr( $product_id ),
				esc_html__( 'Save for later', 'save-for-later-for-woocommerce' )
			);

			if ( ! empty( $save_for_later_link_html ) ) {
				$link_html = $save_for_later_link_html . '&nbsp;or&nbsp;&nbsp;' . $remove_link_html;
			} else {
				$link_html = null;
			}

			return $link_html;

		}

		/**
		 * To display Save For Later link in cart with cart item remove link
		 *
		 * @param string $remove_link HTML code for cart item remove link.
		 * @param string $cart_item_key The cart item key.
		 *
		 * @return string $remove_link including Save For Later link
		 */
		public function cart_item_save_for_later_link( $remove_link = null, $cart_item_key = null ) {

			if ( empty( $cart_item_key ) ) {
				return $remove_link;
			}
			if ( did_action( 'woocommerce_before_cart' ) < 1 ) {
				return $remove_link;
			}

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			if ( ! wp_script_is( 'jquery-tiptip', 'registered' ) ) {
				wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
			}

			if ( ! wp_script_is( 'jquery-tiptip' ) ) {
				wp_enqueue_script( 'jquery-tiptip' );
			}

			if ( ! wp_style_is( 'woocommerce_admin_styles', 'registered' ) ) {
				wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			}

			if ( ! wp_style_is( 'woocommerce_admin_styles' ) ) {
				wp_enqueue_style( 'woocommerce_admin_styles' );
			}

			WC()->cart->get_cart_from_session();

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			$_product = $cart_item['data'];

			$product_id = ( ! empty( $cart_item['variation_id'] ) ) ? $cart_item['variation_id'] : $cart_item['product_id'];

			$link_html = $this->get_save_for_later_actions_html( $cart_item_key );

			if ( ! empty( $link_html ) ) {
				if ( $this->is_wc_gte_33() ) {
					$remove_link = sprintf(
						'<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
						esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
						esc_attr( $link_html ),
						esc_attr( $product_id ),
						esc_attr( $_product->get_sku() )
					);
				} else {
					$remove_link = sprintf(
						'<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
						esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
						esc_attr( $link_html ),
						esc_attr( $product_id ),
						esc_attr( $_product->get_sku() )
					);
				}
			}

			return $remove_link;
		}

		/**
		 * To generate html code for Svae For Later actions
		 */
		public function get_save_for_later_actions() {

			check_ajax_referer( 'sa-save-for-later-actions', 'security' );

			$cart_item_key = ( ! empty( $_POST['cart_item_key'] ) ) ? $_POST['cart_item_key'] : null; // phpcs:ignore

			$link_html = $this->get_save_for_later_actions_html( $cart_item_key );

			echo $link_html; // phpcs:ignore

			die();

		}

		/**
		 * Save for later action after cart item name in the cart
		 *
		 * @param  array  $cart_item     The cart item.
		 * @param  string $cart_item_key The cart item key.
		 */
		public function sfl_action_after_cart_item_name( $cart_item = null, $cart_item_key = '' ) {

			if ( empty( $cart_item ) || empty( $cart_item_key ) ) {
				return;
			}

			$sfl_link = $this->get_save_for_later_url( $cart_item_key );

			$sfl_link = wp_http_validate_url( $sfl_link );

			if ( ! empty( $sfl_link ) ) {
				echo wp_kses_post( sprintf( '<a href="%s" class="sa-sfl-cart-item %s">%s</a>', esc_url( $sfl_link ), apply_filters( 'sa_sfl_action_class', '', $cart_item, $cart_item_key ), apply_filters( 'sa_sfl_action_label', __( 'Save for later', 'save-for-later-for-woocommerce' ), $cart_item, $cart_item_key ) ) );
			}

		}

		/**
		 * Style & script for save for later link in cart
		 */
		public function link_styles_and_scripts() {

			if ( ! is_cart() ) {
				return;
			}

			?>
			<style type="text/css">
				a.sa-sfl-cart-item {
					display: block;
					font-size: 0.8em;
				}
			</style>
			<?php

		}

		/**
		 * To delete saved item via AJAX
		 */
		public function sa_delete_saved_item() {

			check_ajax_referer( 'sa-saved-item-list-action', 'security' );

			$product_id = ( ! empty( $_POST['product_id'] ) ) ? absint( $_POST['product_id'] ) : null; // phpcs:ignore

			if ( empty( $product_id ) ) {
				wp_send_json( array( 'success' => 'false' ) );
			}

			$deleted = $this->delete_saved_item( $product_id );

			if ( $deleted ) {
				$return = array( 'success' => 'true' );
			} else {
				$return = array( 'success' => 'false' );
			}

			wp_send_json( $return );

		}

		/**
		 * To get cookie life
		 */
		public function get_cookie_life() {

			$life = get_option( 'sa_saved_for_later_profile_life', 180 );

			return apply_filters( 'sa_saved_for_later_profile_life', time() + ( 60 * 60 * 24 * $life ) );

		}

		/**
		 * To generate unique id
		 *
		 * Credit: WooCommerce
		 */
		public function generate_unique_id() {

			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher = new PasswordHash( 8, false );
			return md5( $hasher->get_random_bytes( 32 ) );

		}

		/**
		 * To add styles & scripts
		 */
		public function tiptip_styles_and_scripts() {

			if ( ! is_cart() ) {
				return;
			}

			?>
			<style type="text/css">
				#tiptip_content a.button.sa_save_for_later {
					margin-bottom: 1em;
					display: inline-block;
				}
			</style>
			<?php

			$js = "<!-- Save For Later JavaScript Start -->\n
						var window_width = jQuery(window).width();
						var half_window_width = window_width / 2;
						var window_height = jQuery(window).height();
						var half_window_height = window_height / 2;
						var target_position = jQuery('a.remove').offset();
						var target_left_position = 0;
						var target_top_position = 0;
						var tip_position = 'right';
						var activation_method = 'hover';

						if ( target_position != undefined ) {
							target_left_position = target_position.left;
							target_top_position = target_position.top;
						}

						if ( target_left_position > half_window_width ) {
							tip_position = 'left';
						}

						if ( !!( 'ontouchstart' in window ) ) {
							activation_method = 'click';
						}

						var sfl_enable_tip = function( selector ){

							jQuery( selector ).tipTip({
								keepAlive: true,
								activation: activation_method,
								defaultPosition: tip_position,
								edgeOffset: 0,
								delay: 100,
								enter: function(){
									jQuery('#tiptip_content').css('background', '#fff');
									jQuery('#tiptip_content').css('color', '#000');
									jQuery('#tiptip_content').css('border', '1px solid rgba( 128, 128, 128, 0.4 )');
									jQuery('#tiptip_holder').css('z-index', '100');
									jQuery('#tiptip_holder').css('width', '300');
									jQuery('#tiptip_arrow_inner').css('cssText', 'border-'+tip_position+'-color: rgba( 128, 128, 128, 0.6 ) !important');
								}
							});

						};

						if ( jQuery('a.remove').length > 0 ) {
							sfl_enable_tip( 'a.remove' );
						}

						jQuery( document.body).on( 'wc_fragments_refreshed', function( e ){
							jQuery(this).find('#tiptip_holder').hide();
							sfl_enable_tip( 'a.remove' );
						});

					<!-- Save For Later JavaScript End -->";

			wc_enqueue_js( $js );

		}

		/**
		 * Get Save For Later url
		 *
		 * @param string $cart_item_key The cart item key.
		 *
		 * @return string save for later url
		 */
		public function get_save_for_later_url( $cart_item_key = null ) {

			$cart_page_url = wc_get_page_permalink( 'cart' );

			return apply_filters( 'sa_get_save_for_later_url', $cart_page_url ? wp_nonce_url( add_query_arg( 'sa_save_for_later', $cart_item_key, $cart_page_url ), 'sa-save-for-later-cart' ) : '' );

		}

		/**
		 * Get Move To Cart url
		 *
		 * @param string $cart_item_key The cart item key.
		 *
		 * @return string move to cart url
		 */
		public function get_move_to_cart_url( $cart_item_key = null ) {

			$cart_page_url = wc_get_page_permalink( 'cart' );

			return apply_filters( 'sa_get_move_to_cart_url', $cart_page_url ? wp_nonce_url( add_query_arg( 'sa_move_to_cart', $cart_item_key, $cart_page_url ), 'sa-move-to-cart' ) : '' );

		}

		/**
		 * Find latest StoreApps Upgrade file
		 *
		 * @return string classname
		 */
		public function get_latest_upgrade_class() {
			$available_classes         = get_declared_classes();
			$available_upgrade_classes = array_filter(
				$available_classes,
				function ( $class_name ) {
					return strpos( $class_name, 'StoreApps_Upgrade_' ) === 0;
				}
			);
			$latest_class              = 'StoreApps_Upgrade_3_6';
			$latest_version            = 0;
			foreach ( $available_upgrade_classes as $class ) {
				$exploded    = explode( '_', $class );
				$get_numbers = array_filter(
					$exploded,
					function ( $value ) {
						return is_numeric( $value );
					}
				);
				$version     = implode( '.', $get_numbers );
				if ( version_compare( $version, $latest_version, '>' ) ) {
					$latest_version = $version;
					$latest_class   = $class;
				}
			}

			return $latest_class;
		}

		/**
		 * Plugin activated
		 */
		public function sa_sfl_activated() {

			$prefix   = 'save-for-later-for-woocommerce';
			$is_check = get_option( $prefix . '_check_update', 'no' );

			if ( 'no' === $is_check ) {
				if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
					$response = vip_safe_wp_remote_get( 'https://www.storeapps.org/wp-admin/admin-ajax.php?action=check_update&plugin=sfl' );
				} else {
					$response = wp_remote_get( 'https://www.storeapps.org/wp-admin/admin-ajax.php?action=check_update&plugin=sfl' ); // phpcs:ignore
				}
				update_option( $prefix . '_check_update', 'yes' );
			}
		}

		/**
		 * To determine whether to show notification on a page or not
		 *
		 * @param bool  $is_page The page is for notifications.
		 * @param mixed $upgrader The upgrader class.
		 *
		 * @return bool $is_page
		 */
		public function sa_sfl_is_page_for_notifications( $is_page, $upgrader ) {
			$get_page      = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			$get_tab       = ( ! empty( $_GET['tab'] ) ) ? wc_clean( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore
			$get_section   = ( ! empty( $_GET['section'] ) ) ? wc_clean( wp_unslash( $_GET['section'] ) ) : ''; // phpcs:ignore

			if ( ( 'wc-settings' === $get_page ) && ( 'products' === $get_tab ) && ( 'sa_save_for_later' === $get_section ) ) {
				return true;
			}

			return $is_page;
		}

		/**
		 * Function to register section for Save For Later global settings.
		 *
		 * @param array $sections Existing settings.
		 * @return array $sections
		 */
		public function sfl_register_section( $sections ) {
			$sections['sa_save_for_later'] = __( 'Save for later', 'save-for-later-for-woocommerce' );
			return $sections;
		}

		/**
		 * Function to add Save For Later settings for admin
		 *
		 * @param array $settings Existing settings.
		 * @param array $current_section Current section.
		 * @return array $settings
		 */
		public function sfl_add_settings( $settings, $current_section ) {
			if ( 'sa_save_for_later' === $current_section ) {

				$settings = array(
					array(
						'title' => __( 'Settings', 'save-for-later-for-woocommerce' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'sa_sfl_settings',
					),
					array(
						'title'    => __( 'Show "Save For Later" as?', 'save-for-later-for-woocommerce' ),
						'desc'     => __( 'Check to show save for later button on hover of remove on the cart page.', 'save-for-later-for-woocommerce' ),
						'desc_tip' => __( 'Uncheck to show save for later link under each cart item.', 'save-for-later-for-woocommerce' ),
						'id'       => 'sa_sfl_show_on_remove_cart_item_button',
						'default'  => 'yes',
						'type'     => 'checkbox',
						'autoload' => false,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'sa_sfl_settings',
					),
				);

			}

			return $settings;
		}

		/**
		 * Check if the page is valid for the notifications (Eg, Quick Help Widget)
		 *
		 * @param  array    $active_plugins Active plugins.
		 * @param  stdclass $upgrader       The upgrader object.
		 * @return array Active plugins
		 */
		public function sfl_active_plugins_for_quick_help( $active_plugins = array(), $upgrader = null ) {
			$get_page    = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			$get_tab     = ( ! empty( $_GET['tab'] ) ) ? wc_clean( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore
			$get_section = ( ! empty( $_GET['section'] ) ) ? wc_clean( wp_unslash( $_GET['section'] ) ) : ''; // phpcs:ignore

			if ( ( 'wc-settings' === $get_page ) && ( 'products' === $get_tab ) && ( 'sa_save_for_later' === $get_section ) ) {
				$active_plugins['sfl'] = 'save-for-later-for-woocommerce';
			} elseif ( array_key_exists( 'sfl', $active_plugins ) ) {
				unset( $active_plugins['sfl'] );
			}

			return $active_plugins;
		}

		/**
		 * Add link for settings for Save For Later
		 *
		 * @param [array] $links Available actions.
		 * @return [array] $links Array fo plugin manage link with links
		 */
		public function plugin_action_links( $links ) {

			$args         = array(
				'page'    => 'wc-settings',
				'tab'     => 'products',
				'section' => 'sa_save_for_later',
			);
			$settings_url = add_query_arg( $args, admin_url( 'admin.php' ) );

			$action_links = array(
				'settings' => '<a target="_blank" href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'save-for-later-for-woocommerce' ) . '</a>',
			);

			return array_merge( $action_links, $links );

		}

		/**
		 * Get plugin's meta data
		 *
		 * @param  string $file The plguin file.
		 * @return array The plugin info
		 */
		public static function get_plugin_data( $file ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			return get_plugin_data( $file );
		}

	} // End class

} // End class exists condition
