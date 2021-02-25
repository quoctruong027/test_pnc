<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.madebytribe.com
 * @since      1.0.0
 *
 * @package    Caddy
 * @subpackage Caddy/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Caddy
 * @subpackage Caddy/public
 * @author     Tribe Interactive <success@madebytribe.co>
 */
class Caddy_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Caddy_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Caddy_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( ! is_checkout() ) { // Not load on the checkout page
			wp_enqueue_style( 'cc-fontawesome', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css', array(), '5.6.3', 'all' );
			wp_enqueue_style( 'cc-slick', plugin_dir_url( __FILE__ ) . 'css/slick.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/caddy-public.css', array( 'cc-fontawesome' ), $this->version, 'all' );
			wp_enqueue_style( 'cc-icons', plugin_dir_url( __FILE__ ) . 'css/caddy-icons.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Caddy_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Caddy_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( ! is_checkout() ) { // Not load on the checkout page
			//wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_script( 'cc-tabby-js', plugin_dir_url( __FILE__ ) . 'js/tabby.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'cc-tabby-polyfills-js', plugin_dir_url( __FILE__ ) . 'js/tabby.polyfills.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'cc-slick-js', plugin_dir_url( __FILE__ ) . 'js/slick.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/caddy-public.js', array( 'jquery' ), null, true );

			// make the ajaxurl var available to the above script
			$params = array(
				'ajaxurl'            => admin_url( 'admin-ajax.php' ),
				'wc_currency_symbol' => get_woocommerce_currency_symbol(),
				'nonce'              => wp_create_nonce( 'caddy' ),
			);
			wp_localize_script( $this->plugin_name, 'cc_ajax_script', $params );
		}
	}

	/**
	 * Load the cc widget
	 */
	public function cc_load_widget() {
		if ( ! is_checkout() ) { // Not load on the checkout page
			require_once( plugin_dir_path( __FILE__ ) . 'partials/caddy-public-display.php' );
		}
	}

	/**
	 * Ajaxify cart count.
	 *
	 * @param $fragments
	 *
	 * @return mixed
	 */
	public function cc_compass_cart_count_fragments( $fragments ) {
		ob_start();
		$cart_count   = WC()->cart->get_cart_contents_count();
		$cc_cart_zero = ( $cart_count == 0 ) ? ' cc-cart-zero' : '';
		?>
		<span class="cc-compass-count<?php echo $cc_cart_zero; ?>">
			<?php echo sprintf( _n( '%d', '%d', $cart_count ), $cart_count ); ?>
		</span>
		<?php
		$fragments['.cc-compass-count'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Ajaxify cart window total amount.
	 *
	 * @param $fragments
	 *
	 * @return mixed
	 */
	public function cc_compass_cart_window_totals_fragments( $fragments ) {

		$applied_coupons = WC()->cart->get_applied_coupons();
		if ( empty( $applied_coupons ) ) {
			$cart_total = WC()->cart->get_cart_subtotal();
		} else {
			$cart_total = WC()->cart->get_cart_total();
		}
		ob_start();
		?>
		<span class="cc-total-amount"><?php echo wp_kses_post( $cart_total ); ?></span>
		<?php
		$fragments['span.cc-total-amount'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Ajaxify short-code cart count.
	 *
	 * @param $fragments
	 *
	 * @return mixed
	 */
	public function cc_shortcode_cart_count_fragments( $fragments ) {
		ob_start();
		$cart_count   = WC()->cart->get_cart_contents_count();
		$cc_cart_zero = ( $cart_count == 0 ) ? ' cc_cart_zero' : '';
		?>
		<span class="cc_cart_count<?php echo $cc_cart_zero; ?>"><?php echo sprintf( _n( '%d', '%d', $cart_count ), $cart_count ); ?></span>
		<?php
		$fragments['.cc_cart_count'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Window screen template.
	 */
	public function cc_window_screen() {
		include( plugin_dir_path( __FILE__ ) . 'partials/cc-window-screen.php' );
	}

	/**
	 * Cart screen template.
	 */
	public function cc_cart_screen() {
		include( plugin_dir_path( __FILE__ ) . 'partials/cc-cart-screen.php' );
	}

	/**
	 * Save for later template.
	 */
	public function cc_sfl_screen() {
		include( plugin_dir_path( __FILE__ ) . 'partials/cc-sfl-screen.php' );
	}

	/**
	 * Caddy add item to the cart.
	 */
	public function cc_add_to_cart() {

		//Check nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'caddy' ) ) {
			wp_send_json_error( esc_html__( 'Error, please reload page.', 'caddy' ) );
		}

		WC_AJAX::get_refreshed_fragments();
		wp_die();
	}

	/**
	 * Caddy update window data.
	 */
	public function update_window_data() {

		// Get window screen
		ob_start();
		$this->cc_window_screen();
		$window_output = ob_get_clean();

		// Fragments and mini cart are returned
		$data = array(
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
					'div.cc-window-wrapper' => '<div class="cc-window-wrapper">' . $window_output . '</div>',
				)
			),
			'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
		);
		wp_send_json( $data );

		wp_die();
	}

	/**
	 * Display product added information
	 */
	public function cc_product_added_info_html() {

		//Check nonce
		if ( is_user_logged_in() ) {
			$post_nonce        = filter_input( INPUT_POST, 'security', FILTER_SANITIZE_STRING );
			$cc_ajax_condition = ( wp_verify_nonce( $post_nonce, 'caddy' ) && isset( $_POST['product_id'] ) );
		} else {
			$cc_ajax_condition = ( isset( $_POST['product_id'] ) );
		}

		if ( $cc_ajax_condition ) {
			$product_id = filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT );

			// Get window screen
			ob_start();
			$this->cc_product_added_screen( $product_id );
			$product_added_screen = ob_get_clean();

			// Fragments and mini cart are returned
			$data = array(
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
						'div.cc-pl-info-container' => '<div class="cc-pl-info-container">' . $product_added_screen . '</div>',
					)
				),
				'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			);
			wp_send_json( $data );

		}
		wp_die();
	}

	/**
	 * Remove product from the cart
	 */
	public function cc_remove_item_from_cart() {

		//Check nonce
		if ( is_user_logged_in() ) {
			$condition = ( wp_verify_nonce( $_POST['nonce'], 'caddy' ) && isset( $_POST['cart_item_key'] ) );
		} else {
			$condition = ( isset( $_POST['cart_item_key'] ) );
		}

		if ( $condition ) {

			$cart_item_key = wc_clean( isset( $_POST['cart_item_key'] ) ? wp_unslash( $_POST['cart_item_key'] ) : '' );
			if ( ! empty( $cart_item_key ) ) {
				WC()->cart->remove_cart_item( $cart_item_key );
			}

			// Get window screen
			ob_start();
			$this->cc_window_screen();
			$window_output = ob_get_clean();

			// Fragments and mini cart are returned
			$data = array(
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
						'div.cc-window-wrapper' => '<div class="cc-window-wrapper">' . $window_output . '</div>',
					)
				),
				'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			);
			wp_send_json( $data );

		}
		wp_die();
	}

	/**
	 * Cart item quantity update
	 */
	public function cc_cart_item_quantity_update() {

		$key    = sanitize_text_field( $_POST['key'] );
		$number = intval( sanitize_text_field( $_POST['number'] ) );

		if ( is_user_logged_in() ) {
			$condition = ( $key && $number > 0 && wp_verify_nonce( $_POST['security'], 'caddy' ) );
		} else {
			$condition = ( $key && $number > 0 );
		}

		if ( $condition ) {

			WC()->cart->set_quantity( $key, $number );

			// Get window screen
			ob_start();
			$this->cc_window_screen();
			$window_output = ob_get_clean();

			// Fragments and mini cart are returned
			$data = array(
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
						'div.cc-window-wrapper' => '<div class="cc-window-wrapper">' . $window_output . '</div>',
					)
				),
				'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			);
			wp_send_json( $data );

		}
		wp_die();
	}

	/**
	 * Add cart item to wishlist
	 */
	public function cc_save_for_later_item() {

		//Check nonce
		if ( wp_verify_nonce( $_POST['security'], 'caddy' ) &&
		     isset( $_POST['product_id'] ) ) {

			$product_id      = filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT );
			$post_item_key   = filter_input( INPUT_POST, 'cart_item_key', FILTER_SANITIZE_STRING );
			$current_user_id = get_current_user_id();

			$cc_sfl_items = get_user_meta( $current_user_id, 'cc_save_for_later_items', true );
			if ( ! is_array( $cc_sfl_items ) ) {
				$cc_sfl_items = array();
			}
			$cc_sfl_items[] = $product_id;
			update_user_meta( $current_user_id, 'cc_save_for_later_items', $cc_sfl_items );

			// Remove item from the cart
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( $cart_item_key == $post_item_key ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}
			WC()->cart->calculate_totals();
			WC()->cart->maybe_set_cart_cookies();

			// Get window screen
			ob_start();
			$this->cc_window_screen();
			$window_output = ob_get_clean();

			// Fragments and mini cart are returned
			$data = array(
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
						'div.cc-window-wrapper' => '<div class="cc-window-wrapper">' . $window_output . '</div>',
					)
				),
				'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			);
			wp_send_json( $data );

		}
		wp_die();
	}

	/**
	 * Add item to cart from wishlist
	 */
	public function cc_move_to_cart_item() {

		//Check nonce
		if ( wp_verify_nonce( $_POST['security'], 'caddy' ) &&
		     isset( $_POST['product_id'] ) ) {

			$product_id        = filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT );
			$product_data      = wc_get_product( $product_id );
			$product_type      = $product_data->get_type();
			$variation_id      = ( 'variation' == $product_type ) ? $product_id : 0;
			$quantity          = 1;
			$current_user_id   = get_current_user_id();
			$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
			$product_status    = get_post_status( $product_id );

			if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id ) && 'publish' === $product_status ) {

				do_action( 'woocommerce_ajax_added_to_cart', $product_id );

				if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
					wc_add_to_cart_message( array( $product_id => $quantity ), true );
				}

				// Get save for later items
				$cc_sfl_items_array = get_user_meta( $current_user_id, 'cc_save_for_later_items', true );
				if ( ! is_array( $cc_sfl_items_array ) ) {
					$cc_sfl_items_array = array();
				}
				// Search and remove from items array
				$key_pos = array_search( $product_id, $cc_sfl_items_array );
				unset( $cc_sfl_items_array[ $key_pos ] );
				update_user_meta( $current_user_id, 'cc_save_for_later_items', $cc_sfl_items_array );

				WC_AJAX::get_refreshed_fragments();

			} else {

				$data = array(
					'error'       => true,
					'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
				);

				wp_send_json( $data );
			}

			wp_die();
		}
	}

	/**
	 * Remove item from save for later
	 */
	public function cc_remove_item_from_sfl() {

		//Check nonce
		if ( wp_verify_nonce( $_POST['nonce'], 'caddy' ) &&
		     isset( $_POST['product_id'] ) ) {

			$product_id         = filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT );
			$current_user_id    = get_current_user_id();
			$cc_sfl_items_array = get_user_meta( $current_user_id, 'cc_save_for_later_items', true );
			if ( ! is_array( $cc_sfl_items_array ) ) {
				$cc_sfl_items_array = array();
			}

			if ( ( $key = array_search( $product_id, $cc_sfl_items_array ) ) !== false ) {
				unset( $cc_sfl_items_array[ $key ] );
			}
			update_user_meta( $current_user_id, 'cc_save_for_later_items', $cc_sfl_items_array );

			// Get window screen
			ob_start();
			$this->cc_window_screen();
			$window_output = ob_get_clean();

			// Fragments and mini cart are returned
			$data = array(
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
						'div.cc-window-wrapper' => '<div class="cc-window-wrapper">' . $window_output . '</div>',
					)
				),
				'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			);
			wp_send_json( $data );

		}
		wp_die();
	}

	/**
	 * Apply coupon code to the cart
	 */
	public function cc_apply_coupon_to_cart() {

		if ( is_user_logged_in() ) {
			//Check nonce
			$post_nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
			$condition  = ( wp_verify_nonce( $post_nonce, 'caddy' ) && isset( $_POST['coupon_code'] ) );
		} else {
			$condition = ( isset( $_POST['coupon_code'] ) );
		}

		if ( $condition ) {

			global $woocommerce;
			$coupon_code = filter_input( INPUT_POST, 'coupon_code', FILTER_SANITIZE_STRING );
			$woocommerce->cart->add_discount( sanitize_text_field( $coupon_code ) );

			// Get window screen
			ob_start();
			$this->cc_window_screen();
			$window_output = ob_get_clean();

			// Fragments and mini cart are returned
			$data = array(
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
						'div.cc-window-wrapper' => '<div class="cc-window-wrapper">' . $window_output . '</div>',
					)
				),
				'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			);
			wp_send_json( $data );

		} else {
			wc_add_notice( WC_Coupon::get_generic_coupon_error( WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
		}

		wc_print_notices();
		wp_die();
	}

	/**
	 * Remove coupon code to the cart
	 */
	public function cc_remove_coupon_code() {

		if ( is_user_logged_in() ) {
			//Check nonce
			$post_nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
			$condition  = ( wp_verify_nonce( $post_nonce, 'caddy' ) && isset( $_POST['coupon_code_to_remove'] ) );
		} else {
			$condition = ( isset( $_POST['coupon_code_to_remove'] ) );
		}

		if ( $condition ) {

			global $woocommerce;
			$coupon_code_to_remove = filter_input( INPUT_POST, 'coupon_code_to_remove', FILTER_SANITIZE_STRING );
			WC()->cart->remove_coupon( $coupon_code_to_remove );

			// Get window screen
			ob_start();
			$this->cc_window_screen();
			$window_output = ob_get_clean();

			/* Calculate free shipping remaining amount and bar amount */
			$wc_cart_obj             = WC()->cart->get_totals();
			$cart_total              = $wc_cart_obj['subtotal'];
			$cc_free_shipping_amount = get_option( 'cc_free_shipping_amount' );
			$cc_free_shipping_bar    = true;
			$final_cart_subtotal     = $cart_total;

			$free_shipping_remaining_amount = absint( $cc_free_shipping_amount ) - absint( $final_cart_subtotal );
			$free_shipping_remaining_amount = ! empty( $free_shipping_remaining_amount ) ? $free_shipping_remaining_amount : 0;

			// Bar width based off % left
			$cc_bar_amount = 100;
			if ( ! empty( $cc_free_shipping_amount ) && $final_cart_subtotal <= $cc_free_shipping_amount ) {
				$cc_bar_amount = $final_cart_subtotal * 100 / $cc_free_shipping_amount;
			}

			// Fragments and mini cart are returned
			$data = array(
				'fragments'           => apply_filters( 'woocommerce_add_to_cart_fragments', array(
						'div.cc-window-wrapper' => '<div class="cc-window-wrapper">' . $window_output . '</div>',
					)
				),
				'fs_remaining_amount' => $free_shipping_remaining_amount,
				'fs_bar_amount'       => $cc_bar_amount,
				'cart_hash'           => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			);
			wp_send_json( $data );

		}
		wp_die();
	}

	/**
	 * Product added to the cart screen.
	 *
	 * @param string $product_id
	 */
	public function cc_product_added_screen( $product_id = '' ) {
		if ( ! empty( $product_id ) ) {
			include( plugin_dir_path( __FILE__ ) . 'partials/cc-product-added-screen.php' );
		}
	}

	/**
	 * Hide shipping rates when free shipping amount matched.
	 * Updated to support WooCommerce 2.6 Shipping Zones.
	 *
	 * @param array $rates Array of rates found for the package.
	 *
	 * @return array
	 */
	public function cc_shipping_when_free_is_available( $rates ) {
		$shipping_array       = array();
		$coupon_free_shipping = false;

		$applied_coupons = WC()->cart->get_applied_coupons();
		if ( ! empty( $applied_coupons ) ) {
			foreach ( $applied_coupons as $coupon_code ) {
				$coupon = new WC_Coupon( $coupon_code );
				if ( $coupon->get_free_shipping() ) {
					$coupon_free_shipping = true;
				}
			}
		}

		$cart_total              = floatval( preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_contents_total() ) );
		$subcart_total           = (int) number_format( $cart_total, 2 );
		$cc_free_shipping_amount = (int) get_option( 'cc_free_shipping_amount' );

		if ( ! empty( $cc_free_shipping_amount ) ) {
			if ( $cc_free_shipping_amount <= $subcart_total ) {
				foreach ( $rates as $rate_id => $rate ) {
					if ( 'free_shipping' === $rate->method_id ) {
						$shipping_array[ $rate_id ] = $rate;
						break;
					}
				}
			} else {
				foreach ( $rates as $rate_id => $rate ) {
					if ( 'free_shipping' !== $rate->method_id ) {
						$shipping_array[ $rate_id ] = $rate;
					}
				}
			}
		}

		if ( ! empty( $shipping_array ) && ! $coupon_free_shipping ) {
			$return_array = $shipping_array;
		} else {
			$return_array = $rates;
		}

		return $return_array;
	}

	/**
	 * Saved items short-code.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function cc_saved_items_shortcode( $atts ) {

		$default = array(
			'text' => '',
			'icon' => '',
		);

		$attributes         = shortcode_atts( $default, $atts );
		$attributes['text'] = ! empty( $attributes['text'] ) ? $attributes['text'] : $default['text'];

		$saved_items_link = sprintf(
			'<a href="%1$s" class="cc_saved_items_list" aria-label="%2$s">%3$s %4$s</a>',
			'javascript:void(0);',
			esc_html__( 'Saved Items', 'caddy' ),
			( 'yes' === $attributes['icon'] ) ? '<i class="ccicon-heart-empty"></i>' : '',
			esc_html( $attributes['text'] )
		);

		return $saved_items_link;
	}

	/**
	 * Cart items short-code.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function cc_cart_items_shortcode( $atts ) {

		$default = array(
			'text' => '',
			'icon' => '',
		);

		$cart_items_link    = '';
		$attributes         = shortcode_atts( $default, $atts );
		$attributes['text'] = ! empty( $attributes['text'] ) ? $attributes['text'] : $default['text'];

		$cart_count    = '';
		$cc_cart_class = '';
		if ( ! is_admin() ) {
			$cart_count    = WC()->cart->get_cart_contents_count();
			$cc_cart_class = ( $cart_count == 0 ) ? 'cc_cart_count cc_cart_zero' : 'cc_cart_count';
		}

		$cart_items_link = sprintf(
			'<a href="%1$s" class="cc_cart_items_list" aria-label="%2$s">%3$s %4$s <span class="%5$s">%6$s</span></a>',
			'javascript:void(0);',
			esc_html__( 'Cart Items', 'caddy' ),
			( 'yes' === $attributes['icon'] ) ? '<i class="ccicon-cart"></i>' : '',
			esc_html( $attributes['text'] ),
			$cc_cart_class,
			esc_html( $cart_count )
		);

		return $cart_items_link;
	}

	/**
	 * Add product to save for later button.
	 */
	public function cc_add_product_to_sfl() {

		$cc_sfl_btn_on_product = get_option( 'cc_sfl_btn_on_product' );
		$current_user_id       = get_current_user_id();
		$cc_sfl_items_array    = get_user_meta( $current_user_id, 'cc_save_for_later_items', true ); // phpcs:ignore
		$cc_sfl_items_array    = ! empty( $cc_sfl_items_array ) ? $cc_sfl_items_array : array();

		if ( is_user_logged_in() && 'enabled' === $cc_sfl_btn_on_product ) {
			global $product;
			$product_id   = $product->get_id();
			$product_type = $product->get_type();

			if ( in_array( $product_id, $cc_sfl_items_array ) ) {
				echo sprintf(
					'<a href="%1$s" class="button remove_from_sfl_button" data-product_id="' . $product_id . '" data-product_type="' . $product_type . '"><i class="ccicon-heart-filled"></i> <span>%2$s</span></a>',
					'javascript:void(0);',
					esc_html__( 'Saved', 'caddy' )
				);
			} else {
				echo sprintf(
					'<a href="%1$s" class="button cc_add_product_to_sfl" data-product_id="' . $product_id . '" data-product_type="' . $product_type . '"><i class="ccicon-heart-empty"></i> <span>%2$s</span></a>',
					'javascript:void(0);',
					esc_html__( 'Save for later', 'caddy' )
				);
			}
		}
	}

	/**
	 * Add product to save for later directly via button.
	 */
	public function cc_add_product_to_sfl_action() {

		//Check nonce
		if ( wp_verify_nonce( $_POST['nonce'], 'caddy' ) &&
		     isset( $_POST['product_id'] ) ) {

			$product_id      = filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT );
			$current_user_id = get_current_user_id();

			$cc_sfl_items = get_user_meta( $current_user_id, 'cc_save_for_later_items', true );
			if ( ! is_array( $cc_sfl_items ) ) {
				$cc_sfl_items = array();
			}

			if ( ! in_array( $product_id, $cc_sfl_items ) ) {
				$cc_sfl_items[] = $product_id;
				update_user_meta( $current_user_id, 'cc_save_for_later_items', $cc_sfl_items );
			}

			// Get window screen
			ob_start();
			$this->cc_window_screen();
			$window_output = ob_get_clean();

			// Fragments and mini cart are returned
			$data = array(
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
						'div.cc-window-wrapper' => '<div class="cc-window-wrapper">' . $window_output . '</div>',
					)
				),
				'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			);
			wp_send_json( $data );

		}
		wp_die();
	}

	/**
	 * Hide 'Added to Cart' message.
	 *
	 * @param $message
	 * @param $products
	 *
	 * @return string
	 */
	public function cc_empty_wc_add_to_cart_message( $message, $products ) {
		return '';
	}

	/**
	 * Caddy load Custom CSS added to custom css box into footer.
	 */
	public function cc_load_custom_css() {

		$cc_custom_css = get_option( 'cc_custom_css' );
		if ( ! empty( $cc_custom_css ) ) {
			echo '<style type="text/css">' . stripslashes( $cc_custom_css ) . '</style>';
		}

	}

	/**
	 * Load window content when page loads.
	 */
	public function cc_load_window_content() {

		if ( is_user_logged_in() ) {
			//Check nonce
			$condition = ( wp_verify_nonce( $_POST['nonce'], 'caddy' ) );
		} else {
			$condition = ( isset( $_POST['cart_item_key'] ) );
		}

		if ( $condition ) {

			// Get window screen
			ob_start();
			$this->cc_window_screen();
			$window_output = ob_get_clean();

			// Fragments and mini cart are returned
			$data = array(
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
						'div.cc-window-wrapper' => '<div class="cc-window-wrapper">' . $window_output . '</div>',
					)
				),
				'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
			);
			wp_send_json( $data );

		}
		wp_die();
	}

	/**
	 * CC load nav tab items
	 */
	public function cc_load_nav_tabs() {

		// Check if premium plugin is active or not
		if ( ! class_exists( 'Caddy_Premium' ) ) {
			?>
			<ul data-tabs>
				<li><a data-tabby-default href="#cc-cart" class="cc-cart-nav" data-id="cc-saves"><?php esc_html_e( 'Your Cart', 'caddy' ); ?></a></li>
				<?php if ( is_user_logged_in() ) { ?>
					<li><a href="#cc-saves" class="cc-save-nav" data-id="cc-saves"><?php esc_html_e( 'Saved Items', 'caddy' ); ?></a></li>
				<?php } ?>
			</ul>
			<?php
		}
	}

	/**
	 * Display up-sells message
	 */
	public function cc_display_up_sell_message() {

		// Check if premium plugin is active or not
		if ( ! class_exists( 'Caddy_Premium' ) ) {
			?>
			<label><?php esc_html_e( 'We think you\'ll also love these products...', 'caddy' ); ?></label>
			<?php
		}
	}

	/**
	 * Display compass icon
	 */
	public function cc_display_compass_icon() {

		$cart_count   = WC()->cart->get_cart_contents_count();
		$cc_cart_zero = ( $cart_count == 0 ) ? ' cc-cart-zero' : '';

		// Check if premium plugin is active or not
		if ( ! class_exists( 'Caddy_Premium' ) ) {
			?>
			<!-- The floating icon -->
			<div class="cc-compass">
				<span class="licon"></span>
				<div class="cc-loader" style="display: none;"></div>
				<span class="cc-compass-count<?php echo esc_attr( $cc_cart_zero ); ?>"><?php echo sprintf( _n( '%d', '%d', $cart_count ), $cart_count ); ?></span>
			</div>
			<?php
		}
	}

	/**
	 * Display up-sells slider in product added screen
	 *
	 * @param $product_id
	 */
	public function cc_display_product_upsells_slider( $product_id ) {

		// Check if premium plugin is active or not
		if ( ! class_exists( 'Caddy_Premium' ) ) {
			$product = wc_get_product( $product_id );
			$orderby = 'rand';
			$order   = 'desc';
			$upsells = wc_products_array_orderby( array_filter( array_map( 'wc_get_product', $product->get_upsell_ids() ), 'wc_products_array_filter_visible' ), $orderby, $order );

			// GET BEST SELLING PRODUCTS
			$best_seller_args = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'posts_per_page'      => 5,
				'ignore_sticky_posts' => 1,
				'meta_key'            => 'total_sales',
				'orderby'             => 'meta_value_num',
				'order'               => 'DESC',
				'fields'              => 'ids',
				'post__not_in'        => array( $product_id ),
			);
			$best_seller_loop = query_posts( $best_seller_args );

			/* Get up-sells products data */
			$final_upsell_products = array();
			if ( ! empty( $upsells ) ) {
				foreach ( $upsells as $upsell ) {
					$final_upsell_products[] = $upsell->get_id();
				}
			} else {
				foreach ( $best_seller_loop as $best_seller_id ) {
					$final_upsell_products[] = $best_seller_id;
				}
			}
			?>
			<div class="cc-pl-upsells-slider">
				<?php
				foreach ( $final_upsell_products as $upsells_product_id ) {

					$product          = wc_get_product( $upsells_product_id );
					$product_image    = $product->get_image();
					$product_name     = $product->get_name();
					$product_price    = $product->get_price_html();
					$product_link     = get_permalink( $upsells_product_id );
					$add_to_cart_text = $product->add_to_cart_text();
					?>
					<div class="slide">
						<a class="up-sells-product" href="<?php echo esc_url( $product_link ); ?>">
							<?php echo $product_image; ?>
							<p class="title"><?php echo $product_name; ?></p>
							<div class="cc_item_total_price">
								<span class="price"><?php echo $product_price; ?></span>
							</div>
						</a>
						<?php
						if ( $product->is_type( 'simple' ) ) {
							?>
							<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
							      method="post" enctype='multipart/form-data'>
								<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
								        class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
							</form>
						<?php } else { ?>
							<a class="button" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"><?php echo esc_html( $add_to_cart_text ); ?></a>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Free shipping bar html
	 */
	public function cc_free_shipping_bar_html() {
		if ( ! class_exists( 'Caddy_Premium' ) ) {
			$cart_total              = floatval( preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_contents_total() ) );
			$subcart_total           = number_format( $cart_total, 2 );
			$cc_free_shipping_amount = get_option( 'cc_free_shipping_amount' );
			$final_cart_subtotal     = $subcart_total;

			$free_shipping_remaining_ammount = absint( $cc_free_shipping_amount ) - absint( $final_cart_subtotal );
			$free_shipping_remaining_ammount = ! empty( $free_shipping_remaining_ammount ) ? $free_shipping_remaining_ammount : 0;

			// Bar width based off % left
			$cc_bar_amount = 100;
			if ( ! empty( $cc_free_shipping_amount ) && $final_cart_subtotal <= $cc_free_shipping_amount ) {
				$cc_bar_amount = $final_cart_subtotal * 100 / $cc_free_shipping_amount;
			}

			$cc_shipping_country = get_option( 'cc_shipping_country' );
			$wc_currency_symbol  = get_woocommerce_currency_symbol();

			$cc_bar_active = ( $final_cart_subtotal >= $cc_free_shipping_amount ) ? ' cc-bar-active' : '';
			?>
			<span class="cc-fs-title">
				<?php
				if ( $final_cart_subtotal >= $cc_free_shipping_amount ) {
					echo sprintf(
						'<span class="cc-fs-icon"><img src="%1$s"></span>%2$s<strong> %3$s %4$s %5$s</strong>!',
						esc_url( plugin_dir_url( __DIR__ ) . 'public/img/sparkles-emoji.png' ),
						esc_html( __( 'Congrats, you\'ve activated', 'caddy' ) ),
						esc_html( __( 'free', 'caddy' ) ),
						esc_html( $cc_shipping_country ),
						esc_html( __( 'shipping', 'caddy' ) )
					);
				} else {
					echo sprintf(
						'<span class="cc-fs-icon"><img src="%1$s"></span>%2$s<strong> <span class="cc-fs-amount">%3$s</span> %4$s</strong> %5$s <strong>%6$s %7$s %8$s</strong>',
						esc_url( plugin_dir_url( __DIR__ ) . 'public/img/box-emoji.png' ),
						esc_html( __( 'Spend', 'caddy' ) ),
						$wc_currency_symbol . $free_shipping_remaining_ammount,
						esc_html( __( 'more', 'caddy' ) ),
						esc_html( __( 'to get', 'caddy' ) ),
						esc_html( __( 'free', 'caddy' ) ),
						esc_html( $cc_shipping_country ),
						esc_html( __( 'shipping', 'caddy' ) )
					);
				}
				?>
			</span>
			<div class="cc-fs-meter">
				<span class="cc-fs-meter-used<?php echo esc_attr( $cc_bar_active ); ?>" style="width: <?php echo esc_attr( $cc_bar_amount ); ?>%"></span>
			</div>
			<?php
		}
	}

}
