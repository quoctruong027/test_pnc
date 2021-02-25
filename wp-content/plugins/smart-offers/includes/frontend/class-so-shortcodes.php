<?php
/**
 * Smart Offers
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.1.0
 * @package     Smart Offers
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('SO_Shortcodes')) {

	Class SO_Shortcodes {

		function __construct() {
			global $sa_smart_offers;

			// Add shortcodes on different Wordpress & Woocommerce hooks
			add_action( 'wp_head', array( $this, 'show_offer_on_home_page' ) );

			add_action( 'woocommerce_before_cart', array( $this, 'to_show_offer_on_cart' ) );

			add_action( 'woocommerce_cart_is_empty', array( $this, 'so_cart_empty' ) );
			add_action( 'woocommerce_before_checkout_form', array( $this, 'to_show_offer_on_checkout' ) );
			add_action( 'woocommerce_before_my_account', array( $this, 'to_show_offer_on_account' ) );
			add_action( 'woocommerce_thankyou', array( $this, 'to_show_offer_on_thankyou' ), 9 );
			add_action( 'woocommerce_review_order_before_submit', array( $this, 'to_show_order_bump' ) );
			add_action( 'woocommerce_review_order_after_submit', array( $this, 'to_show_order_bump' ) );

			add_shortcode( 'so_show_offers', array( $this, 'shortcode_for_showing_offers' ) );
			add_shortcode( 'so_acceptlink', array( $this, 'shortcode_for_accept_link' ) );
			add_shortcode( 'so_skiplink', array( $this, 'shortcode_for_skip_link' ) );
			add_shortcode( 'so_product_variants', array( $this, 'shortcode_for_showing_product_variants' ) );
			add_shortcode( 'so_quantity', array( $this, 'shortcode_for_showing_quantity' ) );
			add_shortcode( 'so_product_image', array( $this, 'shortcode_for_showing_product_image' ) );
			add_shortcode( 'so_price', array( $this, 'shortcode_for_showing_price' ) );
			add_shortcode( 'so_product_name', array( $this, 'shortcode_for_showing_product_name' ) );
			add_shortcode( 'so_product_short_description', array( $this, 'shortcode_for_showing_product_short_description' ) );
		}

		/**
		 * Process and show offer on Home page as popup
		 */
		function show_offer_on_home_page() {
			if (is_home() || is_front_page()) {
				do_shortcode("[so_show_offers display_as='popup']");
			}
		}

		/**
		 * Process and show offer on cart page
		 */
		function to_show_offer_on_cart() {
			do_shortcode("[so_show_offers]");
		}

		/**
		 * Process and show offer on Cart empty template
		 */
		function so_cart_empty() {
			$this->to_show_offer_on_cart();
		}

		/**
		 * Process and show offer on Checkout page as popup
		 */
		function to_show_offer_on_checkout() {
			do_shortcode("[so_show_offers]");
		}

		/**
		 * Process and show offer on account page as popup
		 */
		function to_show_offer_on_account() {
			do_shortcode("[so_show_offers]");
		}

		/**
		 * Process and show offer on order received page as popup
		 */
		function to_show_offer_on_thankyou($order_id) {
			do_shortcode("[so_show_offers]");
		}

		/**
		 * Process and show offer as order bump
		 */
		function to_show_order_bump() {
			$offer_shortcode = '[so_show_offers]';
			if( wp_doing_ajax() ) {
				echo do_shortcode($offer_shortcode);
			} else {
				do_shortcode($offer_shortcode);
			}
		}

		/**
		 * Shortcode function for accept button.
		 */
		function shortcode_for_accept_link($atts) {
			return $this->get_link($atts, 'accept');
		}

		/**
		 * Shortcode function for skip button.
		 */
		function shortcode_for_skip_link($atts) {
			return $this->get_link($atts, 'skip');
		}

		/**
		 * return accept/skip link
		 */
		function get_link($atts, $action) {

			// To return home_url in case accept and skip link when previewing an offer
			if ( ( isset($_GET ['preview']) && $_GET ['preview'] == 'true' ) || ( isset($_REQUEST ['preview']) && $_REQUEST ['preview'] == 'true' ) ) {
				return home_url();
			}

			if (empty($atts)) {
				return;
			}

			extract(shortcode_atts(array(
				'offer_id' => '',
				'page_url' => '',
				'source'   => ''
							), $atts));

			$page_url = urldecode($page_url);

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id );

			$args = array( 'so_action' => $action, 'so_offer_id' => $offer_id );

			if ( !empty( $source ) ) {
				$new_args = array( 'source' => $source );
				$args = array_merge( $args, $new_args );
			}

			$query_args = apply_filters( 'so_link_args', $args, $offer_id, $action );

			$skip_url = add_query_arg( $query_args, $page_url );

			return $skip_url;
		}

		/**
		 * Shortcode to show product variants in Offer description
		 */
		function shortcode_for_showing_product_variants($atts) {

			global $sa_smart_offers;

			if ( empty($atts) ) {
				return;
			}

			global $sa_smart_offers;

			extract(shortcode_atts(array(
				'prod_id' => '',
				'offer_id' => '',
				'page' => '',
				'where_url' => '',
				'image' => 'yes'
							), $atts));

			if ( $page == 'post_checkout_page' ) {
				$source = 'so_post_checkout';
			} elseif ( $page == 'checkout_page' ) {
				$source = 'so_pre_checkout';
			} else {
				$source = '';
			}

			wp_enqueue_script('wc-add-to-cart-variation');

			$product = wc_get_product($prod_id);
			$available_variations = $product->get_available_variations();
			$selected_attributes = $product->get_default_attributes();

			foreach ( $available_variations as $key => $value ) {

				if ( !empty( $value['attributes'] ) ) {
					$found = 0;
					foreach ( $value['attributes'] as $attr_key => $attr_value ) {
						$attr_key = str_replace( 'attribute_', '', $attr_key );
						if ( ! empty( $selected_attributes[ $attr_key ] ) && $selected_attributes[ $attr_key ] == $attr_value ) {
							$found++;
						}
					}
				}

				$variation_id = $value['variation_id'];
				$prod_instance = wc_get_product($variation_id);
				$sale_price = $prod_instance->get_sale_price();
				$price = $prod_instance->get_price();

				$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id );

				$so_offer = new SO_Offer();
				$offer_price = $so_offer->get_offer_price(array('offer_id' => $offer_id, 'prod_id' => $variation_id));
				if ( $sale_price != $offer_price ) {
					$so_display_price_html = '<del>' . $prod_instance->get_price_html() . '</del> <ins>' . wc_price($offer_price) . '</ins>';
				} else {
					$so_display_price_html = $prod_instance->get_price_html();
				}
				$available_variations[$key]['price_html'] = '<div class="so-show-offer-price"><p class="price"> ' . $so_display_price_html . '</p></div>';
			}

			$attributes = $product->get_variation_attributes();

			$accept_link = do_shortcode("[so_acceptlink offer_id=" . $offer_id . " page_url=" . urlencode($where_url . "/") . " source=" . $source . "]");
			$accept_link = untrailingslashit( str_replace( "#038;", "&", $accept_link ) );

			$return_string = '<form action="' . $accept_link . '" class="variations_form cart" method="POST" id="so_addtocart_' . $offer_id . '" enctype="multipart/form-data" data-product_id="' . $prod_id . '" data-product_variations="' . esc_attr(json_encode($available_variations)) . '">';
			if ( $image == 'yes' ) {
				$return_string .= do_shortcode("[so_product_image]");
			}

			$return_string .= '<table class="variations" cellspacing="0"><tbody>';
			$loop = 1;
			foreach ($attributes as $name => $options) {

				$return_string .= '<tr>';
				$return_string .= '<td class="label"><label for="' . sanitize_title($name) . '">' . wc_attribute_label($name) . '</label></td>';
				$return_string .= '<td class="value"><select class="attribute_' . $loop . '" id="' . esc_attr(sanitize_title($name)) . '" name="attribute_' . sanitize_title($name) . '">';
				$return_string .= '<option value="">' . __( 'Choose an option', 'smart-offers' ) . '</option>';

				if (is_array($options)) {

					$selected_value = ( isset($selected_attributes[sanitize_title($name)]) ) ? $selected_attributes[sanitize_title($name)] : '';

					if (taxonomy_exists($name)) {

						$orderby = wc_attribute_orderby($name);

						$args = array();
						switch ($orderby) {
							case 'name' :
								$args = array('orderby' => 'name', 'hide_empty' => false, 'menu_order' => false);
								break;
							case 'id' :
								$args = array('orderby' => 'id', 'order' => 'ASC', 'menu_order' => false);
								break;
							case 'menu_order' :
								$args = array('menu_order' => 'ASC');
								break;
						}

						$terms = get_terms($name, $args);

						foreach ($terms as $term) {
							if (!in_array($term->slug, $options))
								continue;

							$return_string .= '<option value="' . esc_attr($term->slug) . '" ' . selected($selected_value, $term->slug, false) . '>' . apply_filters('woocommerce_variation_option_name', $term->name) . '</option>';
						}
					} else {

						foreach ($options as $option) {
							$return_string .= '<option value="' . esc_attr(sanitize_title($option)) . '" ' . selected(sanitize_title($selected_value), sanitize_title($option), false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option)) . '</option>';
						}
					}
				}

				$return_string .= '</select></td>';
				$return_string .= '</tr>';
				$loop++;
			}

			$return_string .= '</tbody></table>';
			$return_string .= '<input type="hidden" id="parent_prod_id" name="parent_prod_id" value="' . $prod_id . '">';
			$return_string .= '<input type="hidden" name="variation_id" value="" />';

			$return_string .= '<div class="single_variation_wrap" style="display:none;"><div class="single_variation"></div></div></form>';

			return $return_string;
		}

		/**
		 * Shortcode to allow changing product quantity in the offer
		 */
		function shortcode_for_showing_quantity($atts) {

			extract(shortcode_atts(array(
				'value' => 1,
				'allow_change' => 'false',
				'min' => 1,
				'max' => '',
				'prod_id' => '',
				'offer_id' => '',
				'page' => '',
				'where_url' => ''
							), $atts));

			if ( $allow_change == 'false' ) {
				$style = "display: none";
			}

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id );

			$accept_link = do_shortcode("[so_acceptlink offer_id=" . $offer_id . " page_url=" . urlencode($where_url . "/") . "]");
			$accept_link = untrailingslashit( str_replace( "#038;", "&", $accept_link ) );

			$target_product_id = get_post_meta( $offer_id, 'target_product_ids', true );
			$product = wc_get_product( $target_product_id );
			if ( !( $product instanceof WC_Product ) ) {
				return;
			}

			$html = '<form action="' . $accept_link . '" method="POST" id="so_qty_' . $offer_id . '"';
			if ( !empty($style) ) {
				$html .= 'style="' . $style . '"';
			}
			$html .= '>';

			$qty_params = array('input_value' => $value,
				'max_value' => $max,
				'min_value' => $min);

			$html .= woocommerce_quantity_input($qty_params, $product, false);
			$html .= '</form>';

			return $html;
		}

		/**
		 * Shortcode to show offer
		 */
		function shortcode_for_showing_offers($atts) {

	 		extract(shortcode_atts(array(
				'display_as' => '',
				'offer_ids' => ''
							), $atts));

			$so_offers = new SO_Offers();
			$offers_data = $so_offers->get_offers( $offer_ids );

			if ( empty($offers_data) ) {
				return;
			}

			if ( $offers_data['page'] == 'any_page' || wp_doing_ajax() ) {				// separated from other pages to show offer at exact place where shortcode is placed
				ob_start();

				$so_offer = new SO_Offer();
				$so_offer->prepare_offer($display_as, $offers_data);

				return ob_get_clean();
			} else {
				$so_offer = new SO_Offer();
				$so_offer->prepare_offer($display_as, $offers_data);
			}

		}

		/**
		 * Shortcode to display product image
		 */
		function shortcode_for_showing_product_image() {

			ob_start();

			global $post, $product, $sa_smart_offers;

			if ( $post->post_type != 'smart_offers' ) {
				return;
			}

			$current_post = $post;
			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );
			$target_product_id = get_post_meta( $offer_id, 'target_product_ids', true );

			$product = wc_get_product( $target_product_id );
			if ( !( $product instanceof WC_Product ) ) {
				ob_clean();
				return;
			}
			$product_id = $product->get_id();
			$post = get_post( $product_id );

			query_posts( array( 'post_type' => $post->post_type, 'p' => $offer_id ) );
			wc_get_template( 'single-product/product-image.php', array( 'post' => $post, 'product' => $product ) );
			wp_reset_query();

			$post = $current_post;

			return ob_get_clean();

		}

		/**
		 * Shortcode to show price in the offer description (Simple Products)
		 */
		function shortcode_for_showing_price() {

			global $post, $product, $sa_smart_offers;

			if ( $post->post_type != 'smart_offers' ) {
				return;
			}

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

			$target_product_id = get_post_meta( $offer_id, 'target_product_ids', true );

			$product = wc_get_product( $target_product_id );
			if( !( $product instanceof WC_Product ) ) {
				return;
			}

			$product_id = $product->get_id();
			$sale_price = $product->get_sale_price();
			
			$price = $product->get_price();
			$so_offer = new SO_Offer();
			$offer_price = $so_offer->get_offer_price( array( 'offer_id' => $offer_id, 'prod_id' => $target_product_id ) );

			if ( $sale_price != $offer_price ) {
				$so_display_price_html = '<del>' . $product->get_price_html() . '</del> <ins>' . wc_price($offer_price) . '</ins>';
			} else {
				$so_display_price_html = $product->get_price_html();
			}

			$price_content = '<p class="price"> ' . $so_display_price_html . '</p>';

			return $price_content;
		}

		/**
		 * Shortcode to product name in the offer description
		 */
		function shortcode_for_showing_product_name() {

			global $post;

			if ( $post->post_type != 'smart_offers' ) {
				return;
			}

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );
			$target_product_id = get_post_meta( $offer_id, 'target_product_ids', true );

			$product = wc_get_product( $target_product_id );
			if( !( $product instanceof WC_Product ) ) {
				return;
			}

			$product_name = $product->get_title();
			$product_full_name = $product_name;

			return $product_full_name;

		}

		/**
		 * Shortcode to show short description in the offer description
		 */
		function shortcode_for_showing_product_short_description() {

			global $post;

			if ( $post->post_type != 'smart_offers' ) {
				return;
			}

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );
			$target_product_id = get_post_meta( $offer_id, 'target_product_ids', true );

			$offered_product = wc_get_product( $target_product_id );
			if( !( $offered_product instanceof WC_Product ) ) {
				return;
			}

			$product_short_description = $offered_product->get_short_description();

			return $product_short_description;

		}

	}

}

return new SO_Shortcodes();
