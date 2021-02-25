<?php
/**
 * Main class for Update Variations In Cart
 *
 * @category    Class
 * @package     WooCommerce Update Variations In Cart
 * @author      StoreApps
 * @version     1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Update_Variations_In_Cart' ) ) {

	/**
	 * Main class for Update Variations In Cart
	 */
	class WC_Update_Variations_In_Cart {

		/**
		 * Variable to hold instance of Update Variation in Cart
		 *
		 * @var $instance
		 * @since 1.8.1
		 */
		private static $instance = null;

		/**
		 * Update Variation in Cart plugin data.
		 *
		 * @var array
		 * @since 1.8.1
		 */
		public $plugin_data = array();

		/**
		 * Get single instance of Update Variation in Cart.
		 *
		 * @return WC_Update_Variations_In_Cart Singleton object of WC_Update_Variations_In_Cart
		 * @since 1.8.1
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.8.1
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-update-variations-in-cart' ), '1.8.1' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.8.1
		 */
		private function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-update-variations-in-cart' ), '1.8.1' );
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->plugin_data = self::get_uvc_plugin_data();

			if ( ! $this->is_wc_gte_25() ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_sa_needs_wc_25_above' ) );
			}

			add_filter( 'woocommerce_cart_item_name', array( $this, 'link_to_update_variation_form' ), 10, 3 );
			add_action( 'wp_enqueue_scripts', array( $this, 'uvc_enqueue_scripts_styles' ) );
			add_action( 'wp_ajax_show_update_variations_in_cart_form', array( $this, 'show_update_variations_in_cart_form' ) );
			add_action( 'wp_ajax_nopriv_show_update_variations_in_cart_form', array( $this, 'show_update_variations_in_cart_form' ) );
			add_action( 'wp_ajax_update_variations_in_cart', array( $this, 'update_variations_in_cart' ) );
			add_action( 'wp_ajax_nopriv_update_variations_in_cart', array( $this, 'update_variations_in_cart' ) );

			add_action( 'admin_init', array( $this, 'sa_uvc_activated' ) );
			add_filter( 'sa_is_page_for_notifications', array( $this, 'sa_uvc_is_page_for_notifications' ), 10, 2 );

			$this->may_be_show_sa_in_app_offer();
		}

		/**
		 * Function to handle WC compatibility related function call from appropriate class
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return mixed Result of function call.
		 */
		public function __call( $function_name, $arguments = array() ) {
			if ( ! is_callable( 'SA_WC_Compatibility_3_0', $function_name ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_3_0::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_3_0::' . $function_name );
			}
		}

		/**
		 * Function to show admin notice that Update Variations in Cart works with WC 2.5+
		 */
		public function admin_notice_sa_needs_wc_25_above() {
			?>
			<div class="updated error">
				<p>
				<?php
					/* translators: 1: Notice severity  2: Link to plugins menu */
					echo sprintf( esc_html__( '%1$s Update Variations In Cart is active but it will only work with WooCommerce 2.5+. %2$s.', 'woocommerce-update-variations-in-cart' ), '<strong>' . esc_html__( 'Important', 'woocommerce-update-variations-in-cart' ) . ':</strong>', '<a href="' . esc_url( admin_url( 'plugins.php?plugin_status=upgrade' ) ) . '" target="_blank" >' . esc_html__( 'Please update WooCommerce to the latest version', 'woocommerce-update-variations-in-cart' ) . '</a>' );
				?>
				</p>
			</div>
			<?php
		}

		/**
		 * Function to enqueue style and scripts
		 */
		public function uvc_enqueue_scripts_styles() {

			if ( is_cart() ) {
				if ( ! wp_script_is( 'jquery' ) ) {
					wp_enqueue_script( 'jquery' );
				}

				wp_register_script( 'update-variations-js', UVC_URL . 'assets/js/update-variations.js', array( 'jquery' ), $this->plugin_data['Version'], true );
				wp_register_style( 'update-variations-css', UVC_URL . 'assets/css/update-variations.css', array(), $this->plugin_data['Version'], false );

				wp_localize_script(
					'update-variations-js',
					'update_variation_params',
					array(
						'ajax_url'          => admin_url( 'admin-ajax.php' ),
						'update_text'       => __( 'Update', 'woocommerce-update-variations-in-cart' ),
						'cart_updated_text' => __( 'Cart updated.', 'woocommerce-update-variations-in-cart' ),
						'form_security'     => wp_create_nonce( 'uvc-form-display' ),
						'update_cart'       => wp_create_nonce( 'uvc-update-cart' ),
					)
				);

				if ( ! wp_script_is( 'update-variations-js' ) ) {
					wp_enqueue_script( 'update-variations-js' );
				}

				if ( ! wp_script_is( 'wc-add-to-cart-variation' ) ) {
					wp_enqueue_script( 'wc-add-to-cart-variation' );
				}

				if ( ! wp_style_is( 'update-variations-css' ) ) {
					wp_enqueue_style( 'update-variations-css' );
				}
			}

		}

		/**
		 * Loads woocommerce variable product template and the contents inside the templates
		 */
		public function show_update_variations_in_cart_form() {
			check_ajax_referer( 'uvc-form-display', 'security' );

			global $product;

			$form_value = ( ! empty( $_POST['form_value'] ) ) ? $_POST['form_value'] : ''; // phpcs:ignore

			if ( ! empty( $form_value ) ) {

				$cart_item_key                    = $form_value;
				$cart_item                        = WC()->cart->cart_contents[ $cart_item_key ];
				$product                          = wc_get_product( $cart_item['product_id'] );
				$selected_product                 = $cart_item['data'];
				$selected_product->variation_data = $cart_item['variation'];

				foreach ( $selected_product->variation_data as $attribute_name => $attribute_value ) {
					$_REQUEST[ $attribute_name ] = $attribute_value;
				}

				$thumbnail = apply_filters( 'woocommerce_in_cart_product_thumbnail', $selected_product->get_image(), $cart_item, $cart_item_key );

				$_POST['previous_cart_item_key'] = $cart_item_key;
				$_POST['uvc_is_wc_gte_26']       = ( $this->is_wc_gte_26() ) ? 'yes' : 'no';

				if ( ! empty( $thumbnail ) ) {
					$_POST['product_thumbnail'] = $thumbnail;
				}

				add_action( 'woocommerce_after_variations_form', array( $this, 'add_thumbnail_and_cancel_button' ) );

				echo '<script src="'.plugins_url().'/woocommerce/assets/js/frontend/add-to-cart-variation.js"></script>'; // phpcs:ignore

				$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

				// Load the template.
				$product_attributes = ( $this->is_wc_gte_30() ) ? $product->get_default_attributes() : $product->get_variation_default_attributes;

				if ( 'variable-subscription' === $product->get_type() ) {
					$template_name = 'variable-subscription.php';
					$template_path = WP_PLUGIN_DIR . '/woocommerce-subscriptions/templates/';
				} else {
					$template_name = 'variable.php';
					$template_path = '';
				}

				wc_get_template(
					'single-product/add-to-cart/' . $template_name,
					array(
						'available_variations' => $get_variations ? $product->get_available_variations() : false,
						'attributes'           => $product->get_variation_attributes(),
						'selected_attributes'  => $product_attributes,
					),
					'',
					$template_path
				);
			}

			exit;
		}

		/**
		 * To add thumbnail and cancel button in the form.
		 */
		public function add_thumbnail_and_cancel_button() {
			check_ajax_referer( 'uvc-form-display', 'security' );

			$product_thumbnail      = ( ! empty( $_POST['product_thumbnail'] ) ) ? $_POST['product_thumbnail'] : ''; // phpcs:ignore
			$uvc_is_wc_gte_26       = ( ! empty( $_POST['uvc_is_wc_gte_26'] ) ) ? sanitize_text_field( wp_unslash( $_POST['uvc_is_wc_gte_26'] ) ) : ''; // WPCS: input var ok.
			$previous_cart_item_key = ( ! empty( $_POST['previous_cart_item_key'] ) ) ? wc_clean( $_POST['previous_cart_item_key'] ) : ''; // phpcs:ignore

			?>
			<input type='hidden' id='product_thumbnail' value='<?php echo esc_attr( $product_thumbnail ); ?>'>
			<input type='hidden' id='uvc_is_wc_gte_26' value='<?php echo esc_attr( $uvc_is_wc_gte_26 ); ?>'>
			<span id="cancel" onclick="cancel_update_variations('<?php echo esc_attr( $previous_cart_item_key ); ?>');" title="<?php echo esc_html__( 'Close', 'woocommerce-update-variations-in-cart' ); ?>" style="cursor: pointer; "><u><?php echo esc_html__( 'Cancel', 'woocommerce-update-variations-in-cart' ); ?></u></span>
			<input name="previous_cart_item_key" type="hidden" value="<?php echo esc_attr( $previous_cart_item_key ); ?>">
			<?php
		}

		/**
		 * To update the product and delete the product on basis of the variation changed and submitted.
		 */
		public function update_variations_in_cart() {
			check_ajax_referer( 'uvc-update-cart', 'security' );

			if ( ! class_exists( 'WC_Form_Handler' ) ) {
				include_once WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-form-handler.php';
			}

			$form_data = ( ! empty( $_POST['form_data'] ) ) ? $_POST['form_data'] : ''; // phpcs:ignore

			if ( ! empty( $form_data ) ) {

				parse_str( $form_data, $_REQUEST ); // WPCS: input var ok, sanitization ok.

				$previous_cart_item_key = ( ! empty( $_REQUEST['previous_cart_item_key'] ) ) ? wc_clean( $_REQUEST['previous_cart_item_key'] ) : ''; // phpcs:ignore

				if ( ! empty( $previous_cart_item_key ) ) {
					WC()->cart->set_quantity( $previous_cart_item_key, 0 );

					$url = wc_get_page_permalink( 'cart' );
					WC_Form_Handler::add_to_cart_action( $url );
				}
			}
			die();
		}

		/**
		 * To show edit link in cart and passing of the data from it to the jquery slidedown pop up modal of variation
		 *
		 * @param string $product_title Product title.
		 * @param array  $values Cart item data.
		 * @param string $cart_item_key Cart item key.
		 * @return string $product_title
		 */
		public function link_to_update_variation_form( $product_title, $values, $cart_item_key ) {
			$post_id  = $values['product_id'];
			$_product = wc_get_product( $post_id );

			if ( $_product->is_type( 'variable' ) ) {
				// Allow 3rd party plugins to control showing of 'Change' link.
				$is_show_change_link = apply_filters( 'uvc_show_change_link', true, $product_title, $values, $cart_item_key );

				if ( ! $is_show_change_link ) {
					return $product_title;
				}

				if ( ! is_cart() ) {
					return $product_title;
				}

				$cart_item_key_variation_id = WC()->cart->cart_contents[ $cart_item_key ]['variation_id'];

				$label = get_option( 'uvc_label_for_change', __( 'Change', 'woocommerce-update-variations-in-cart' ) );

				if ( ! empty( $cart_item_key_variation_id ) ) {
					$attributes     = array_map( array( $this, 'attribute_label' ), array_keys( $values['variation'] ) );
					$product_title .= '</a><span class="btnshow uvc_change_button" id="' . $cart_item_key . '" title="' . $label . ' ' . ucwords( implode( ', ', $attributes ) ) . '">' . $label . '</span>';
					$product_title .= '<input type="hidden" value="' . $cart_item_key . '" id="' . $cart_item_key . '" name="item_' . $cart_item_key . '">';
				}
			}

			return $product_title;
		}

		/**
		 * Function to get label
		 *
		 * @param string $attribute Product attribute.
		 * @return string $attribute product attribute label.
		 */
		public function attribute_label( $attribute ) {
			return wc_attribute_label( str_replace( 'attribute_', '', $attribute ) );
		}

		/**
		 * Function to check update
		 */
		public function sa_uvc_activated() {
			$prefix   = 'update_variations_in_cart';
			$is_check = get_option( $prefix . '_check_update', 'no' );

			if ( 'no' === $is_check ) {
				if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
					$response = vip_safe_wp_remote_get( 'https://www.storeapps.org/wp-admin/admin-ajax.php?action=check_update&plugin=uvc' );
				} else {
					$response = wp_remote_get( 'https://www.storeapps.org/wp-admin/admin-ajax.php?action=check_update&plugin=uvc' ); // phpcs:ignore
				}
				update_option( $prefix . '_check_update', 'yes' );
			}
		}

		/**
		 * To determine whether to show notification on a page or not
		 *
		 * @param bool  $bool True/false.
		 * @param mixed $upgrader StoreApps Upgrader object.
		 * @return bool $bool True/false.
		 */
		public function sa_uvc_is_page_for_notifications( $bool = false, $upgrader = null ) {
			$screen    = get_current_screen();
			$post_type = isset( $screen, $screen->post_type ) ? $screen->post_type : '';

			if ( 'product' === $post_type ) {
				return true;
			}

			return $bool;
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
			$latest_class              = 'StoreApps_Upgrade_3_7';
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
		 * Function to dismiss admin notice.
		 */
		public function uvc_dismiss_admin_notice() {
			if ( isset( $_GET['uvc_dismiss_admin_notice'] ) && '1' === $_GET['uvc_dismiss_admin_notice'] && isset( $_GET['option_name'] ) ) { // phpcs:ignore
				$option_name = sanitize_text_field( wp_unslash( $_GET['option_name'] ) ); // phpcs:ignore

				update_option( $option_name, 'no', 'no' );

				$referer = wp_get_referer();
				wp_safe_redirect( $referer );
				exit();
			}
		}

		/**
		 * Function to fetch plugin's data
		 */
		public function get_uvc_plugin_data() {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return get_plugin_data( UVC_PLUGIN_FILE );
		}

		/**
		 * Function to show SA in app offer if any.
		 */
		public function may_be_show_sa_in_app_offer() {
			if ( ! class_exists( 'SA_In_App_Offer' ) ) {
				include_once UVC_PLUGIN_DIRPATH . '/sa-includes/class-sa-in-app-offer.php';

				$args = array(
					'file'        => UVC_PLUGIN_FILE,
					'prefix'      => 'uvc',
					'option_name' => 'sa_offer_halloween_2018',
					'campaign'    => 'sa_halloween_2018',
					'start'       => '2018-10-30',
					'end'         => '2018-11-02',
				);

				SA_In_App_Offer::get_instance( $args );
			}
		}
	}
}
