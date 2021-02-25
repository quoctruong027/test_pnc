<?php

abstract class WFACP_Common_Helper {
	private static $order_bumps = [];
	protected static $wfacp_publish_posts = [];
	protected static $get_saved_pages = [];
	protected static $ip_data = [];

	public static function get_geo_ip() {
		if ( isset( self::$ip_data ) ) {
			self::$ip_data = WC_Geolocation::geolocate_ip();
		}

		return self::$ip_data;
	}

	public static function allow_svg_mime_type( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';

		return $mimes;
	}

	public static function set_session( $key, $data ) {

		if ( empty( $data ) ) {
			$data = [];
		}

		if ( ! is_null( WC()->session ) ) {

			WC()->session->set( 'wfacp_' . $key . '_' . WFACP_Common::get_id(), $data );
		}
	}

	public static function get_session( $key ) {

		if ( ! is_null( WC()->session ) ) {
			return WC()->session->get( 'wfacp_' . $key . '_' . WFACP_Common::get_id(), [] );
		}

		return [];
	}

	public static function default_design_data() {
		return [
			'selected'        => 'shopcheckout',
			'selected_type'   => 'pre_built',
			'template_active' => 'no',
		];
	}

	public static function pc( $data ) {
		if ( class_exists( 'PC' ) && method_exists( 'PC', 'debug' ) && ( true == apply_filters( 'wfacp_show_debug_logs', false ) ) ) {
			PC::debug( $data );
		}
	}

	public static function is_disabled() {
		if ( isset( $_REQUEST['wfacp_disabled'] ) ) {
			return true;
		}

		return false;
	}

	public static function pr( $arr ) {
		echo '<br /><pre>';
		print_r( $arr );
		echo '</pre><br />';
	}

	public static function dump( $arr ) {
		echo '<pre>';
		var_dump( $arr );
		echo '</pre>';
	}

	public static function export( $arr ) {
		echo '<pre>';
		var_export( $arr );
		echo '</pre>';
	}

	/**
	 * Check our customizer page is open or not
	 * @return bool
	 */
	public static function is_customizer() {
		if ( isset( $_REQUEST['wfacp_customize'] ) && $_REQUEST['wfacp_customize'] == 'loaded' && isset( $_REQUEST['wfacp_id'] ) && $_REQUEST['wfacp_id'] > 0 ) {
			return true;
		}

		return false;
	}


	public static function get_checkout_page_version() {
		$version = WFACP_Common::get_post_meta_data( WFACP_Common::get_id(), '_wfacp_version', true );
		if ( '' == $version ) {
			$version = '1.0.0';
		}

		return $version;
	}

	public static function maybe_convert_html_tag( $val ) {
		//      new WP_Customize_Manager();
		if ( false === is_string( $val ) ) {
			return $val;
		}
		$val = str_replace( '&lt;', '<', $val );
		$val = str_replace( '&gt;', '>', $val );

		return $val;
	}

	public static function date_i18n( $timestamp = '' ) {
		if ( '' == $timestamp ) {
			$timestamp = time();
		}

		return date_i18n( apply_filters( 'wfacp_date_i18n_format', get_option( 'date_format', 'M jS, Y' ) ), $timestamp );
	}

	public static function include_notification_class( $get_global_path ) {

		require_once $get_global_path . 'includes/class-woofunnels-notifications.php';
	}

	/**
	 * Get default global setting Error Messages
	 * @return array
	 */
	public static function get_error_message() {

		$msg = [
			'required' => __( 'is required field', 'woofunnels-aero-checkout' ),
			'invalid'  => __( 'is not a valid', 'woofunnels-aero-checkout' ),

		];

		return $msg;

	}

	/**
	 * Check cart all product is boolean
	 * @return bool
	 */
	public static function is_cart_is_virtual() {
		if ( is_null( WC()->cart ) ) {
			return false;
		}
		$cart_items      = WC()->cart->get_cart_contents();
		$virtual_product = 0;
		if ( ! empty( $cart_items ) ) {
			foreach ( $cart_items as $key => $cart_item ) {
				$pro = $cart_item['data'];
				if ( $pro instanceof WC_Product && $pro->is_virtual() ) {
					$virtual_product ++;
				}
			}
		}
		if ( count( $cart_items ) == $virtual_product ) {
			return true;
		}

		return false;
	}

	public static function get_saved_pages() {
		if ( ! empty( self::$get_saved_pages ) ) {
			return self::$get_saved_pages;
		}
		global $wpdb;

		self::$get_saved_pages = $wpdb->get_results( "SELECT `ID`, `post_title`, `post_type` FROM `{$wpdb->prefix}posts` WHERE `post_type` = '" . WFACP_Common::get_post_type_slug() . "' AND `post_title` != '' AND `post_status` = 'publish' ORDER BY `post_title` ASC", ARRAY_A );


		return self::$get_saved_pages;
	}

	public static function get_class_path( $class = 'WFACP_Core' ) {
		$reflector = new ReflectionClass( $class );
		$fn        = $reflector->getFileName();

		return dirname( $fn );
	}

	public static function get_function_path( $class = 'WFACP_Core()' ) {
		$reflector = new ReflectionFunction( $class );
		$fn        = $reflector->getFileName();

		return dirname( $fn );
	}


	/**
	 * Detect builder page is open
	 * @return bool
	 */

	public static function is_builder() {
		if ( is_admin() && isset( $_GET['page'] ) && 'wfacp' == $_GET['page'] ) {
			return true;
		}

		return false;

	}


	public static function is_theme_builder() {
		$status = false;
		if ( self::is_customizer() ) {
			$status = true;
		}

		return apply_filters( 'wfacp_is_theme_builder', $status );
	}

	public static function is_edit_screen_open() {
		$status = false;
		if ( isset( $_REQUEST['wfacp_customize'] ) || isset( $_REQUEST['wfacp_id'] ) ) {
			$status = true;
		}

		return apply_filters( 'wfacp_is_edit_screen_open', $status );
	}

	public static function get_date_format() {
		return get_option( 'date_format', '' ) . ' ' . get_option( 'time_format', '' );
	}

	public static function posts_per_page() {
		return apply_filters( 'wfacp_post_per_page', 10 );
	}


	/**
	 * Checkout Placeorder button pressed and checout process started
	 * @return bool
	 */
	public static function is_checkout_process() {
		if ( isset( $_REQUEST['_wfacp_post_id'] ) && $_REQUEST['_wfacp_post_id'] > 0 ) {
			return true;
		}

		return false;
	}

	public static function unset_blank_keys_old( $data_array ) {

		foreach ( $data_array as $key => $value ) {
			if ( $value == '' ) {
				unset( $data_array[ $key ] );
			}
		}

		return $data_array;
	}

	public static function unset_blank_keys( $array_for_check ) {
		if ( is_array( $array_for_check ) && count( $array_for_check ) > 0 ) {
			foreach ( $array_for_check as $key => $value ) {
				if ( is_array( $value ) && count( $value ) > 0 ) {
					continue;
				}
				if ( $value == '' ) {
					unset( $array_for_check[ $key ] );
				}
			}
		}

		return $array_for_check;

	}


	public static function get_default_you_save_text() {
		return apply_filters( 'wfacp_default_you_text', __( 'Buy {{quantity}} and Save {{saving_value}} ({{saving_percentage}})', 'woofunnels-aero-checkout' ) );
	}

	public static function get_default_additional_information_title() {
		return __( "WHAT'S INCLUDED IN YOUR PLAN?", 'woofunnels-aero-checkout' );
	}

	public static function default_shipping_placeholder_text() {
		return __( 'Enter your address to view shipping options.', 'woocommerce' );
	}

	public static function delete_option_enable_in_product_switcher() {
		return apply_filters( 'wfacp_enable_product_switcher_deletion_item', false );
	}

	/**
	 * Disabled finale execution on our discounting
	 */
	public static function disable_wcct_pricing() {

		if ( function_exists( 'WCCT_Core' ) && class_exists( 'WCCT_discount' ) ) {

			add_filter( 'wcct_force_do_not_run_campaign', function ( $status, $instance ) {
				$products = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id() );
				if ( is_array( $products ) && count( $products ) > 0 ) {

					foreach ( $products as $index => $data ) {
						$product_id = absint( $data['id'] );
						if ( $data['parent_product_id'] && $data['parent_product_id'] > 0 ) {
							$product_id = absint( $data['parent_product_id'] );
						}
						unset( $instance->single_campaign[ $product_id ] );
						$status = false;
					}
				}

				return $status;

			}, 10, 2 );
		}
	}

	/**
	 * Restrict discount apply on these our ajax action
	 *
	 * @param $actions
	 *
	 * @return array
	 */
	public static function wcct_get_restricted_action( $actions ) {
		$actions[] = 'wfacp_add_product';
		$actions[] = 'wfacp_remove_product';
		$actions[] = 'wfacp_save_products';

		$actions[] = 'wfacp_addon_product';
		$actions[] = 'wfacp_remove_addon_product';
		$actions[] = 'wfacp_switch_product_addon';
		$actions[] = 'wfacp_update_product_qty';
		$actions[] = 'wfacp_quick_view_ajax';

		return $actions;
	}

	public static function handling_post_data( $post_data ) {
		if ( isset( $post_data['ship_to_different_address'] ) && isset( $post_data['wfacp_billing_same_as_shipping'] ) && $post_data['wfacp_billing_same_as_shipping'] == 0 ) {
			$address_fields = [ 'address_1', 'address_2', 'city', 'postcode', 'country', 'state' ];
			foreach ( $address_fields as $key => $val ) {
				if ( isset( $_POST[ 's_' . $val ] ) ) {
					$_POST[ $val ] = $_POST[ 's_' . $val ];
				}
			}
		}
	}

	public static function merge_page_product_settings( $settings ) {
		if ( ! isset( $settings['settings']['product_switcher_template'] ) ) {
			$settings['settings']['product_switcher_template'] = 'default';
		}

		return $settings;
	}


	public static function wcs_cart_totals_shipping_calculator_html() {
		include WFACP_TEMPLATE_COMMON . '/checkout/wcs_cart_totals_shipping_calculator_html.php';
	}


	public static function wcs_cart_totals_shipping_html() {
		include WFACP_TEMPLATE_COMMON . '/checkout/wcs_cart_totals_shipping_html.php';
	}


	public static function print_custom_field_at_thankyou( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		include WFACP_TEMPLATE_COMMON . '/thankyou-custom-field.php';
	}


	public static function print_custom_field_at_email( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		include WFACP_TEMPLATE_COMMON . '/email-custom-field.php';
	}

	public static function check_wc_validations_billing( $address_fields, $type ) {

		$woocommerce_checkout_address_2_field = get_option( 'woocommerce_checkout_address_2_field', 'optional' );
		$woocommerce_checkout_company_field   = get_option( 'woocommerce_checkout_company_field', 'optional' );
		$requiredFor                          = false;
		$requiredForCompany                   = false;
		if ( 'required' === $woocommerce_checkout_address_2_field ) {
			$requiredFor = true;
		}
		if ( 'required' === $woocommerce_checkout_company_field ) {
			$requiredForCompany = true;
		}

		if ( isset( $address_fields['billing_address_2'] ) ) {
			if ( ( isset( $address_fields['billing_address_2']['required'] ) && false === $requiredFor ) ) {
				unset( $address_fields['billing_address_2']['required'] );
			}
		}

		if ( isset( $address_fields['billing_company'] ) ) {
			if ( ( isset( $address_fields['billing_company']['required'] ) && false === $requiredForCompany ) ) {
				unset( $address_fields['billing_company']['required'] );
			}
		}

		return $address_fields;
	}

	public static function check_wc_validations_shipping( $address_fields, $type ) {

		$woocommerce_checkout_address_2_field = get_option( 'woocommerce_checkout_address_2_field', 'optional' );
		$woocommerce_checkout_company_field   = get_option( 'woocommerce_checkout_company_field', 'optional' );

		$requiredFor        = false;
		$requiredForCompany = false;
		if ( 'required' === $woocommerce_checkout_address_2_field ) {
			$requiredFor = true;
		}

		if ( 'required' === $woocommerce_checkout_company_field ) {
			$requiredForCompany = true;
		}

		if ( isset( $address_fields['shipping_address_2'] ) ) {
			if ( ( isset( $address_fields['shipping_address_2']['required'] ) && false === $requiredFor ) ) {
				unset( $address_fields['shipping_address_2']['required'] );
			}
		}
		if ( isset( $address_fields['shipping_company'] ) ) {
			if ( ( isset( $address_fields['shipping_company']['required'] ) && false === $requiredForCompany ) ) {
				unset( $address_fields['shipping_company']['required'] );
			}
		}

		return $address_fields;
	}

	/** Do not sustain deleted item in remove_cart_item_object
	 *
	 * @param $cart_item_key
	 * @param $cart WC_Cart
	 */
	public static function remove_item_deleted_items( $cart_item_key, $cart ) {
		unset( $cart->removed_cart_contents[ $cart_item_key ] );
	}

	public static function remove_src_set( $attr ) {
		if ( isset( $attr['srcset'] ) ) {
			unset( $attr['srcset'] );
		}

		return $attr;
	}

	/**
	 * Re apply aero checkout product settings when payment failed for subscription and user click on pay now button from  order list at my-account
	 *All discount and other setting automatically applied
	 *
	 * @param $cart_item_data
	 *
	 * @return mixed
	 */
	public static function re_apply_aero_checkout_settings( $cart_item_data ) {
		if ( isset( $cart_item_data['subscription_initial_payment'] ) && isset( $cart_item_data['subscription_initial_payment']['custom_line_item_meta'] ) ) {

			$line_data = $cart_item_data['subscription_initial_payment']['custom_line_item_meta'];
			if ( isset( $line_data['_wfacp_product'] ) ) {
				$cart_item_data['_wfacp_product']     = $line_data['_wfacp_product'];
				$cart_item_data['_wfacp_product_key'] = $line_data['_wfacp_product_key'];
				$cart_item_data['_wfacp_options']     = $line_data['_wfacp_options'];

			}
		}

		return $cart_item_data;

	}


	public static function get_product_image( $product_obj, $size = 'woocommerce_thumbnail', $cart_item = [], $cart_item_key = '' ) {
		$image = '';
		if ( ! $product_obj instanceof WC_Product ) {
			return $image;
		}
		if ( $product_obj->get_image_id() ) {
			$image = wp_get_attachment_image_src( $product_obj->get_image_id(), $size, false );
		} elseif ( $product_obj->get_parent_id() ) {
			$parent_product = wc_get_product( $product_obj->get_parent_id() );
			$image          = self::get_product_image( $parent_product, $size, $cart_item, $cart_item_key );
		}

		if ( is_array( $image ) && isset( $image[0] ) ) {

			$image_src = apply_filters( 'wfacp_cart_item_thumbnail', $image[0], $cart_item, $cart_item_key );

			$image_html = '<img src="' . esc_attr( $image_src ) . '" alt="' . esc_html( $product_obj->get_name() ) . '" width="' . esc_attr( $image[1] ) . '" height="' . esc_attr( $image[2] ) . '" />';

			return $image_html;
		}

		$image = wc_placeholder_img( $size );

		return $image;
	}

	public static function array_insert_after( array $array, $key, array $new ) {
		$keys  = array_keys( $array );
		$index = array_search( $key, $keys );

		$pos = false === $index ? count( $array ) : $index + 1;

		return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
	}


	public static function sort_shipping( $available_methods ) {

		$global_settings = get_option( '_wfacp_global_settings', [] );
		if ( isset( $global_settings['wfacp_set_shipping_method'] ) && false === wc_string_to_bool( $global_settings['wfacp_set_shipping_method'] ) ) {
			if ( true === apply_filters( 'wfacp_disable_shipping_sorting', true ) ) {
				return $available_methods;
			}
		}

		uasort( $available_methods, [ __CLASS__, 'short_shipping_method' ] );

		return $available_methods;
	}

	/**
	 * Short shipping method low to high Cost
	 *
	 * @param $p1
	 * @param $p2
	 */
	public static function short_shipping_method( $p1, $p2 ) {
		if ( $p1 instanceof WC_Shipping_Rate && $p2 instanceof WC_Shipping_Rate ) {
			if ( $p1->get_cost() == $p2->get_cost() ) {
				return 0;
			}

			return ( $p1->get_cost() < $p2->get_cost() ) ? - 1 : 1;
		}

		return 0;
	}


	public static function assign_minimum_value_sipping_method( $default, $rates, $chosen_method ) {

		if ( true === apply_filters( 'wfacp_disable_minimum_value_shipping', true ) ) {
			return $default;
		}
		if ( is_array( $rates ) && count( $rates ) > 0 ) {
			//	uasort( $rates, 'WFACP_Common::short_shipping_method' );
			$rates   = WFACP_Common::sort_shipping( $rates );
			$default = current( array_keys( $rates ) );

		}

		return $default;
	}


	/**
	 * @param $cart_item
	 * @param $pro WC_Product
	 * @param $product_data
	 * @param $cart_variation_id String
	 *
	 * @return array
	 */
	public final static function get_cart_item_attributes( $cart_item, $pro, $product_data, $cart_variation_id ) {
		global $wfacp_products_attributes_data;
		$product_attributes   = [];
		$variation_attributes = [];
		if ( ! is_null( $cart_item ) && isset( $cart_item['variation_id'] ) ) {

			if ( is_array( $cart_item['variation'] ) && count( $cart_item['variation'] ) ) {
				$product_attributes = $cart_item['variation'];
			} elseif ( 'variation' == $cart_item['data']->get_type() ) {
				$product_attributes = $cart_item['data']->get_attributes();
			}
		} elseif ( 'variation' == $pro->get_type() ) {
			$product_attributes = $pro->get_attributes();
		}

		$is_variation_error = '';
		$attributes_keys    = [];
		if ( count( $product_attributes ) > 0 ) {
			$wfacp_products_attributes_data[ $pro->get_id() ]['attributes'] = $product_attributes;
			foreach ( $product_attributes as $a_key => $a_val ) {
				if ( '' != $a_val ) {
					$variation_attributes[] = $a_val;
				} else {
					if ( in_array( $product_data['type'], WFACP_Common::get_variable_product_type() ) && $cart_variation_id > 0 ) {
						$is_variation_error = 'wfacp_incomplete_variation';
						$attributes_keys[]  = ucwords( str_replace( [ 'attribute_', 'attribute_pa_' ], '', $a_key ) );
					}
				}
			}
		}

		return [ $product_attributes, $is_variation_error, $attributes_keys, $variation_attributes ];
	}


	/**
	 * Get a shipping methods full label including price.
	 *
	 * @param WC_Shipping_Rate $method Shipping method rate data.
	 *
	 * @return string
	 */
	public static function wc_cart_totals_shipping_method_cost( $method ) {
		$output    = __( 'Free', 'woocommerce' );
		$has_cost  = 0 < $method->cost;
		$hide_cost = ! $has_cost && in_array( $method->get_method_id(), array( 'free_shipping', 'local_pickup' ), true );

		if ( $has_cost && ! $hide_cost ) {
			$output = '';
			if ( WC()->cart->display_prices_including_tax() ) {
				$output .= wc_price( $method->cost + $method->get_shipping_tax() );
				if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
					$output .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			} else {
				$output .= wc_price( $method->cost );
				if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
					$output .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			}
		}

		return apply_filters( 'wc_cart_totals_shipping_method_cost', $output, $method );
	}

	/**
	 * Get a shipping methods full label including price.
	 *
	 * @param WC_Shipping_Rate $method Shipping method rate data.
	 *
	 * @return string
	 */
	public static function shipping_method_label( $method ) {

		$status = apply_filters( 'wfacp_show_shipping_method_label_without_tax_string', true, $method );
		if ( true == $status ) {
			$output = $method->get_label();
		} else {
			$output = wc_cart_totals_shipping_method_label( $method );
		}

		return apply_filters( 'woocommerce_cart_shipping_method_full_label', $output, $method );
	}


	public static function get_cart_count( $items ) {
		$count = 0;
		if ( is_array( $items ) && count( $items ) > 0 ) {
			foreach ( $items as $item ) {
				if ( isset( $item['_wfob_product'] ) || apply_filters( 'wfacp_exclude_product_cart_count', false, $item ) ) {
					continue;
				}
				$count ++;
			}
		}

		return $count;

	}

	public static function wc_cart_totals_shipping_html( $colspan_attr_1 = '', $colspan_attr_2 = '' ) {
		$packages = WC()->shipping->get_packages();
		$first    = true;

		foreach ( $packages as $i => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
			$product_names = array();
			if ( count( $packages ) > 1 ) {
				foreach ( $package['contents'] as $item_id => $values ) {
					$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
				}
				$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
			}

			wc_get_template( 'wfacp/checkout/cart-shipping.php', array(
				'package'                  => $package,
				'available_methods'        => $package['rates'],
				'show_package_details'     => count( $packages ) > 1,
				'show_shipping_calculator' => is_cart() && $first,
				'package_details'          => implode( ', ', $product_names ),
				'package_name'             => apply_filters( 'woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping %d', 'shipping packages', 'woocommerce' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'woocommerce' ), $i, $package ),
				'index'                    => $i,
				'chosen_method'            => $chosen_method,
				'formatted_destination'    => WC()->countries->get_formatted_address( $package['destination'], ', ' ),
				'has_calculated_shipping'  => WC()->customer->has_calculated_shipping(),
				'colspan_attr_1'           => $colspan_attr_1,
				'colspan_attr_2'           => $colspan_attr_2,
			) );

			$first = false;
		}
	}

	/**
	 * Remove action for without instance method  class found and return object of class
	 *
	 * @param $hook
	 * @param $cls string
	 * @param string $function
	 *
	 * @return |null
	 */
	public static function remove_actions( $hook, $cls, $function = '' ) {

		global $wp_filter;
		$object = null;
		if ( class_exists( $cls ) && isset( $wp_filter[ $hook ] ) && ( $wp_filter[ $hook ] instanceof WP_Hook ) ) {
			$hooks = $wp_filter[ $hook ]->callbacks;
			foreach ( $hooks as $priority => $reference ) {
				if ( is_array( $reference ) && count( $reference ) > 0 ) {
					foreach ( $reference as $index => $calls ) {
						if ( isset( $calls['function'] ) && is_array( $calls['function'] ) && count( $calls['function'] ) > 0 ) {
							if ( is_object( $calls['function'][0] ) ) {
								$cls_name = get_class( $calls['function'][0] );
								if ( $cls_name == $cls && $calls['function'][1] == $function ) {
									$object = $calls['function'][0];
									unset( $wp_filter[ $hook ]->callbacks[ $priority ][ $index ] );
								}
							} elseif ( $index == $cls . '::' . $function ) {
								// For Static Classess
								$object = $cls;
								unset( $wp_filter[ $hook ]->callbacks[ $priority ][ $cls . '::' . $function ] );
							}
						}
					}
				}
			}
		} elseif ( function_exists( $cls ) && isset( $wp_filter[ $hook ] ) && ( $wp_filter[ $hook ] instanceof WP_Hook ) ) {

			$hooks = $wp_filter[ $hook ]->callbacks;
			foreach ( $hooks as $priority => $reference ) {
				if ( is_array( $reference ) && count( $reference ) > 0 ) {
					foreach ( $reference as $index => $calls ) {
						$remove = false;
						if ( $index == $cls ) {
							$remove = true;
						} elseif ( isset( $calls['function'] ) && $cls == $calls['function'] ) {
							$remove = true;
						}
						if ( true == $remove ) {
							unset( $wp_filter[ $hook ]->callbacks[ $priority ][ $cls ] );
						}
					}
				}
			}
		}

		return $object;

	}


	/**
	 * @param $cart_item
	 * @param $cart_item_key
	 * @param $pro WC_Product
	 * @param $switcher_settings
	 * @param $product_data
	 *
	 * @return mixed|void
	 */
	public static function get_product_switcher_item_title( $cart_item, $cart_item_key, $pro, $switcher_settings, $product_data ) {


		$item_name = '';
		$item_key  = isset( $product_data['item_key'] ) ? $product_data['item_key'] : '';
		if ( isset( $switcher_settings['products'][ $item_key ] ) ) {
			$title     = $switcher_settings['products'][ $item_key ]['title'];
			$old_title = $switcher_settings['products'][ $item_key ]['old_title'];
			if ( '' !== $title && $title !== $old_title ) {
				$item_name = $title;

			}
		}
		if ( '' == $item_name ) {
			if ( isset( $product_data['variable'] ) && 'yes' == $product_data['variable'] ) {
				$item_name = $pro->get_title();
			} else {
				$item_name = $pro->get_name();
			}
		}

		return "<span class='wfacp_product_switcher_item'>" . apply_filters( 'wfacp_get_product_switcher_item_title', $item_name, $cart_item, $cart_item_key, $pro, $switcher_settings, $product_data ) . '</span>';
	}

	/**
	 * Filter callback for finding variation attributes.
	 *
	 * @param WC_Product_Attribute $attribute Product attribute.
	 *
	 * @return bool
	 */
	private static function filter_variation_attributes( $attribute ) {
		return true === $attribute->get_variation();
	}

	/**
	 * @param $cart_item
	 * @param $cart_item_key
	 * @param $pro WC_Product
	 * @param $switcher_settings
	 * @param $product_data
	 *
	 * @return mixed|void
	 */
	public final static function get_attribute_html( $cart_item, $cart_item_key, $pro, $switcher_settings, $product_data ) {

		if ( apply_filters( 'wfacp_hide_product_switcher_attributes', ( ! isset( $product_data['variable'] ) || 'yes' !== $product_data['variable'] ), $cart_item, $cart_item_key, $pro, $switcher_settings, $product_data ) ) {
			return;
		}
		if ( ! in_array( $pro->get_type(), WFACP_Common::get_variation_product_type() ) ) {
			return;
		}

		$is_product_is_variable = ( isset( $product_data['variable'] ) && 'yes' == $product_data['variable'] );

		$parent_id   = $pro->get_parent_id();
		$product_obj = WFACP_Common::wc_get_product( $parent_id, $product_data['item_key'] );

		$variation_attributes = array_filter( $product_obj->get_attributes(), array( __CLASS__, 'filter_variation_attributes' ) );

		if ( empty( $variation_attributes ) ) {
			return;
		}

		$item_in_cart            = false;
		$cart_product_attributes = [];
		if ( ! empty( $cart_item ) && isset( $cart_item['data'] ) ) {
			/**
			 * @var $cart_product_object
			 */
			$item_in_cart            = true;
			$cart_product_attributes = $cart_item['variation'];
		}

		$attribute_string     = '';
		$incomplete_variation = '';
		$only_attribute       = [];
		$cart_variation_id    = 0;
		if ( ! is_null( $cart_item ) ) {
			if ( isset( $cart_item['variation_id'] ) ) {
				$cart_variation_id = $cart_item['variation_id'];
			}
		}

		$output = [
			'selected'     => '',
			'not_selected' => '',
		];
		/**
		 * @var $attribute WC_Product_Attribute
		 */

		foreach ( $variation_attributes as $slug => $attribute ) {

			$only_attribute[] = wc_attribute_label( $attribute->get_name() );
			if ( false == $item_in_cart && true == $is_product_is_variable ) {

				continue;
			}

			$temp_terms = [];
			$terms      = $attribute->get_terms();
			if ( ! is_null( $terms ) ) {
				foreach ( $terms as $term ) {
					$temp_terms[ $term->slug ] = $term->name;
				}
			}
			$attr_value          = ( $is_product_is_variable ) ? __( 'Select', 'woocommerce' ) : '';
			$temp_slug           = 'attribute_' . $slug;
			$value_not_available = '';
			if ( ! empty( $cart_product_attributes ) && isset( $cart_product_attributes[ $temp_slug ] ) && '' !== $cart_product_attributes[ $temp_slug ] ) {
				$attr_value = $cart_product_attributes[ $temp_slug ];
				if ( isset( $temp_terms[ $attr_value ] ) ) {
					$attr_value = $temp_terms[ $attr_value ];
				}
			} else {
				if ( $is_product_is_variable ) {
					$value_not_available  = 'wfacp_attr_value_not_available';
					$incomplete_variation = 'wfacp_incomplete_variation';
				}
			}

			if ( '' !== $attr_value ) {

				$attribute_string .= sprintf( '<div class="wfacp_pro_attr_single"><span class="wfacp_attribute_id">%s</span><span class="wfacp_attributes_sep">: </span><span class="wfacp_attribute_value %s">%s</span><span>, </span></div>', wc_attribute_label( $attribute->get_name() ), $value_not_available, $attr_value );

			}
		}

		if ( '' != $attribute_string && true == $item_in_cart ) {

			$output['selected'] = sprintf( '<div class="wfacp_selected_attributes %s">%s</div>', $incomplete_variation, $attribute_string );

		}

		if ( true == $is_product_is_variable && ! empty( $only_attribute ) && self::display_not_selected_attribute( $product_data, $pro ) ) {

			$not_selected = __( 'Select', 'woofunnels-aero-checkout' );
			if ( count( $only_attribute ) > 1 ) {
				$last = end( $only_attribute );
				$size = count( $only_attribute );
				unset( $only_attribute[ $size - 1 ] );
				$not_selected .= ' ' . implode( ', ', $only_attribute ) . ' &amp; ' . $last;
			} else {
				$not_selected .= ' ' . $only_attribute[0];
			}

			$choose_label           = sprintf( "<a href='#' class='wfacp_qv-button var_product' qv-id='%d' qv-var-id='%d'>%s</a>", $product_data['id'], $cart_variation_id, apply_filters( 'wfacp_choose_option_text', $not_selected ) );
			$output['not_selected'] = sprintf( '<div class="wfacp_not_selected_attributes">%s</div>', $choose_label );

		} else {
			do_action( 'wfacp_display_not_selected_attribute_placeholder', $only_attribute, $product_data, $pro );
		}

		return $output;

	}


	/**
	 * @param $you_save_text
	 * @param $price_data
	 * @param $pro WC_Product
	 * @param $product_data
	 * @param $cart_item
	 * @param $cart_item_key
	 *
	 * @return array
	 */
	public final static function get_product_switcher_item_you_save( $you_save_text, $price_data, $pro, $product_data, $cart_item, $cart_item_key ) {
		$you_save_text_html = '';
		if ( '' !== $you_save_text ) {
			$subscription_tryl   = 0;
			$subscription_signup = 0;
			if ( in_array( $pro->get_type(), WFACP_Common::get_subscription_product_type() ) ) {
				$subscription_tryl   = WC_Subscriptions_Product::get_trial_length( $pro );
				$subscription_signup = WC_Subscriptions_Product::get_sign_up_fee( $pro );
			}

			$have_saving_value_merge_tag      = strpos( $you_save_text, '{{saving_value}}' );
			$have_saving_percentage_merge_tag = strpos( $you_save_text, '{{saving_percentage}}' );
			if ( ( false !== $have_saving_value_merge_tag || false !== $have_saving_percentage_merge_tag ) ) {
				if ( $subscription_tryl > 0 || $subscription_signup > 0 ) {
					//available  for future updates
				} else {
					$save_html = WFACP_Common::product_switcher_merge_tags( $you_save_text, $price_data, $pro, $product_data, $cart_item, $cart_item_key );
					if ( '' !== $save_html ) {
						$you_save_text_html = sprintf( '<div class="wfacp_you_save_text">%s</div>', $save_html );
					}
				}
			} else {
				// do not have merge tag Or Static you save text
				$save_html = WFACP_Common::product_switcher_merge_tags( $you_save_text, $price_data, $pro, $product_data, $cart_item, $cart_item_key );
				if ( '' !== $save_html ) {
					$you_save_text_html = sprintf( '<div class="wfacp_you_save_text">%s</div>', $save_html );
				}
			}
		}
		$subscription_product_string = '';
		if ( in_array( $pro->get_type(), WFACP_Common::get_subscription_product_type() ) ) {
			$subscription_product_string = sprintf( "<div class='wfacp_product_subs_details'>%s</div>", WFACP_Common::subscription_product_string( $pro, $product_data, $cart_item, $cart_item_key ) );
		}

		return [ $you_save_text_html, $subscription_product_string ];
	}


	public static final function display_not_selected_attribute( $product_data, $pro ) {
		return apply_filters( 'wfacp_display_not_selected_attribute', false, $product_data, $pro );
	}

	public static function order_summary_html( $args = [] ) {
		if ( ! empty( $args ) ) {
			WC()->session->set( 'wfacp_order_summary_' . WFACP_Common::get_id(), $args );
		}
		$path = WFACP_TEMPLATE_COMMON . '/order-summary.php';
		$path = apply_filters( 'wfacp_order_summary_template', $path );
		include $path;

	}

	public static function is_best_value_available() {
		$wfacp_id = WFACP_Common::get_id();
		$data     = WFACP_Common::get_post_meta_data( $wfacp_id, '_wfacp_product_switcher_setting' );
		if ( empty( $data ) || wc_string_to_bool( $data['settings']['hide_best_value'] ) || ! isset( $data['settings']['best_value_product'] ) || '' == $data['settings']['best_value_product'] ) {
			// we do not display best value setting when no product is assign in product switcher
			return false;
		}

		return true;
	}

	public static function is_what_included_available() {
		$wfacp_id = WFACP_Common::get_id();
		$data     = WFACP_Common::get_post_meta_data( $wfacp_id, '_wfacp_product_switcher_setting' );

		if ( empty( $data ) ) {
			return true;
		}
		if ( ! isset( $data['settings']['is_hide_additional_information'] ) || false == wc_string_to_bool( $data['settings']['is_hide_additional_information'] ) ) {
			return true;
		}

		return false;
	}


	public static function replace_css_classes() {
		$is_migrated = get_option( 'wfacp_css_migrated', 'no' );
		if ( 'yes' == $is_migrated ) {
			return;
		}


		$default = [
			'wfacp-col-full'         => 'wfacp-col-full',
			'wfacp-col-left-half'    => 'wfacp-col-left-half',
			'wfacp-col-right-half'   => 'wfacp-col-left-half',
			'col-right-half'         => 'wfacp-col-left-half',
			'wfacp-col-left-third'   => 'wfacp-col-left-third',
			'wfacp-col-middle-third' => 'wfacp-col-left-third',
			'wfacp-col-right-third'  => 'wfacp-col-left-third',

		];

		$old_slugs = [
			'classic'      => 'layout_1',
			'salesletter'  => 'layout_2',
			'marketer'     => 'layout_4',
			'shopcheckout' => 'layout_9',
		];

		global $wpdb;
		$post_type = WFACP_Common::get_post_type_slug();
		$all_posts = $wpdb->get_results( "select ID from {$wpdb->posts} where post_type='{$post_type}'", ARRAY_A );
		if ( empty( $all_posts ) ) {
			return;
		}


		foreach ( $all_posts as $index => $post ) {
			$id          = $post['ID'];
			$page_design = WFACP_Common::get_page_design( $id );

			if ( 'embed_forms' == $page_design['selected_type'] ) {
				wp_update_post( [ 'ID' => $id, 'post_content' => "[wfacp_forms]" ] );
				update_post_meta( $id, '_wp_page_template', 'wfacp-full-width.php' );


				$page_layout = get_post_meta( $id, '_wfacp_page_layout', true );
				if ( $page_layout['current_step'] == 'third_step' ) {
					$third_step = array(
						'selected'        => 'embed_forms_3',
						'selected_type'   => 'embed_forms',
						'template_active' => 'yes',
					);
					WFACP_Common::update_page_design( $id, $third_step );
				} else if ( $page_layout['current_step'] == 'two_step' ) {
					$two_step = array(
						'selected'        => 'embed_forms_2',
						'selected_type'   => 'embed_forms',
						'template_active' => 'yes',
					);
					WFACP_Common::update_page_design( $id, $two_step );
				}
				update_post_meta( $id, 'ct_other_template', '-1' );
			}

			$options = get_option( 'wfacp_c_' . $id, [] );
			if ( empty( $options ) ) {
				continue;
			}

			$selected_templated = '';
			if ( isset( $page_design['selected'] ) ) {
				$selected_templated = $page_design['selected'];
			}

			if ( isset( $old_slugs[ $selected_templated ] ) ) {
				$template = $old_slugs[ $selected_templated ];
			}
			$field_key = 'wfacp_form_form_fields_1_' . $template;
			//product switcher keys
			$switcher_keys = [
				'wfacp_form_section_' . $template . '_product_switcher_bg_color'   => 'wfacp_form_product_switcher_section_' . $template . '_product_switcher_bg_color',
				'wfacp_form_section_' . $template . '_product_switcher_text_color' => 'wfacp_form_product_switcher_section_' . $template . '_product_switcher_text_color',
				'wfacp_form_section_' . $template . '_you_save_text_color'         => 'wfacp_form_product_switcher_section_' . $template . '_you_save_text_color',
				'wfacp_form_section_' . $template . '_best_value_text_color'       => 'wfacp_form_product_switcher_section_' . $template . '_best_value_text_color',
				'wfacp_form_section_' . $template . '_best_value_bg_color'         => 'wfacp_form_product_switcher_section_' . $template . '_best_value_bg_color'
			];


			foreach ( $options as $o_key => $o_val ) {
				if ( false !== strpos( $o_key, $field_key ) ) {
					$classes         = explode( ' ', $o_val );
					$first_wfacp_key = '';
					$other_css_class = [];
					foreach ( $classes as $class ) {
						$class = trim( $class );
						if ( empty( $class ) ) {
							continue;
						}
						if ( isset( $default[ $class ] ) ) {
							$first_wfacp_key = $default[ $class ];
						} else {
							$other_css_class[] = $class;
						}
					}
					if ( '' !== $first_wfacp_key ) {
						$options[ $o_key ] = $first_wfacp_key;
					}
					if ( count( $other_css_class ) > 0 ) {
						$other_css_key             = $o_key . '_other_classes';
						$options[ $other_css_key ] = implode( ' ', $other_css_class );
					}
				}
				if ( isset( $switcher_keys[ $o_key ] ) ) {
					$temp_data = $options[ $o_key ];
					unset( $options[ $o_key ] );
					$new_key             = $switcher_keys[ $o_key ];
					$options[ $new_key ] = $temp_data;
				}

			}

			if ( ! empty( $options ) ) {
				update_option( 'wfacp_c_' . $id, $options, 'no' );
			}

		}
		update_option( 'wfacp_css_migrated', 'yes', 'yes' );
	}


	public static function get_builder_localization() {
		$data  = [];
		$links = [];

		$links[] = "<a target='_blank' href='//buildwoofunnels.com/docs/aerocheckout/optimizations/smart-buttons-for-express-checkout/'>Stripe Apple Pay</a>";
		$links[] = "<a target='_blank' href='//buildwoofunnels.com/docs/aerocheckout/optimizations/smart-buttons-for-express-checkout/'>Stripe Google Pay</a>";
		$links[] = "<a target='_blank' href='//buildwoofunnels.com/docs/aerocheckout/optimizations/smart-buttons-for-express-checkout/'>PayPal Express</a>";

		$amazonelink = "<a target='_blank' href='//buildwoofunnels.com/docs/aerocheckout/optimizations/how-to-configure-amazon-pay/'>Amazon Pay</a>";

		$links_string                     = implode( ', ', $links );
		$data['global']                   = [
			'form_has_changes'         => [
				'title'             => __( 'Changes have been made!', 'woofunnels-aero-checkout' ),
				'text'              => __( 'You need to save changes before generating preview.', 'woofunnels-aero-checkout' ),
				'confirmButtonText' => __( 'Yes, Save it!', 'woofunnels-aero-checkout' ),
				'cancelText'        => __( 'Cancel', 'woofunnels-aero-checkout' ),
			],
			'no_products'              => __( 'No product associated with this checkout. You need to add minimum one product to generate preview', 'woofunnels-aero-checkout' ),
			'remove_product'           => [
				'title'             => __( 'Want to delete this product from checkout?', 'woofunnels-aero-checkout' ),
				'text'              => __( "You won't be able to revert this!", 'woofunnels-aero-checkout' ),
				'confirmButtonText' => __( 'Delete', 'woofunnels-aero-checkout' ),
			],
			'active'                   => __( 'Active', 'woofunnels-aero-checkout' ),
			'inactive'                 => __( 'Inactive', 'woofunnels-aero-checkout' ),
			'add_checkout'             => [
				'heading'           => __( 'Title', 'woofunnels-aero-checkout' ),
				'post_content'      => __( 'Description', 'woofunnels-aero-checkout' ),
				'checkout_url_slug' => __( 'Permalink', 'wordpress' ),
			],
			'delete_checkout_page_btn' => __( 'Yes, delete this checkout page', 'woofunnels-aero-checkout' ),
			'confirm_button_text'      => __( 'Yes, remove this section!', 'woofunnels-aero-checkout' ),

			'confirm_button_text_ok'                => __( 'Ok', 'woofunnels-aero-checkout' ),
			'billing_email_present_only_first_step' => __( 'Billing Email field must be on step 1 for the form', 'woofunnels-aero-checkout' ),
			'cncel_button_text'                     => __( 'Cancel', 'woofunnels-aero-checkout' ),
			'delete_checkout_page_head'             => __( 'Are you sure you want to delete this checkout page?', 'woofunnels-aero-checkout' ),
			'delete_checkout_page'                  => __( 'Are you sure, you want to delete this permanently? This can`t be undone', 'woofunnels-aero-checkout' ),
			'add_checkout_page'                     => __( 'Add New ', 'woofunnels-aero-checkout' ),
			'edit_checkout_page'                    => __( 'Edit Checkout Page', 'woofunnels-aero-checkout' ),
			'add_checkout_btn'                      => __( 'Create a Checkout', 'woofunnels-aero-checkout' ),
			'update_btn'                            => __( 'Update', 'woofunnels-aero-checkout' ),
			'data_saving'                           => __( 'Data Saving...', 'woofunnels-aero-checkout' ),
			'shortcode_copy_message'                => __( 'Shortcode Copied!', 'woofunnels-aero-checkout' ),
			'enable'                                => __( 'Enable', 'woofunnels-aero-checkout' ),
			'add_product_popup'                     => __( 'Add Product', 'woofunnels-aero-checkout' ),
		];
		$data['error']                    = [
			400 => array(
				'title'             => __( 'Oops! Unable to save this form', 'woofunnels-aero-checkout' ),
				'text'              => __( 'This Forms contains extremely large options. Please increase server\'s max_input_vars limit. Not sure? Contact support.', 'woofunnels-aero-checkout' ),
				'confirmButtonText' => __( 'Okay! Got it', 'woofunnels-aero-checkout' ),
				'type'              => 'error',
			),
			500 => array(
				'title'             => __( 'Oops! Internal Server Error', 'woofunnels-aero-checkout' ),
				'text'              => '',
				'confirmButtonText' => __( 'Okay! Got it', 'woofunnels-aero-checkout' ),
				'type'              => 'error',
			),
			502 => array(
				'title'             => __( 'Oops! Bad Gateway', 'woofunnels-aero-checkout' ),
				'text'              => '',
				'confirmButtonText' => __( 'Okay! Got it', 'woofunnels-aero-checkout' ),
				'type'              => 'error',
			)
		];
		$data['importer']                 = [
			'activate_template' => [
				'heading'     => __( 'Are you sure you want to apply this template?', 'woofunnels-aero-checkout' ),
				'sub_heading' => '',
				'button_text' => __( 'Yes, apply this template!', 'woofunnels-aero-checkout' ),
			],

			'add_template'      => [
				'heading'     => __( 'Are you sure you want to import this template?', 'woofunnels-aero-checkout' ),
				'sub_heading' => '',
				'button_text' => __( 'Yes, Import this template!', 'woofunnels-aero-checkout' ),
			],
			'remove_template'   => [
				'heading'     => __( 'Are you sure you want to remove this template?', 'woofunnels-aero-checkout' ),
				'sub_heading' => __( 'Any changes done to the current template will be lost. This action can\'t be undone.', 'woofunnels-aero-checkout' ),
				'button_text' => __( 'Yes, remove this template!', 'woofunnels-aero-checkout' ),
			],
			'failed_import'     => __( 'Oops! Something went wrong. Try again or contact support.', 'woofunnels-aero-checkout' ),
			'close_prompt_text' => __( 'Close', 'woofunnels-aero-checkout' ),
		];
		$data['fields']                   = [
			'field_id_slug'                               => __( 'Field ID', 'woofunnels-aero-checkout' ),
			'inputs'                                      => [
				'active'   => __( 'Active', 'woofunnels-aero-checkout' ),
				'inactive' => __( 'Inactive', 'woofunnels-aero-checkout' ),
			],
			'section'                                     => [
				'default_sub_heading' => __( 'Example: Fields marked with * are mandatory', 'woofunnels-aero-checkout' ),
				'default_classes'     => '',
				'add_heading'         => __( 'Add Section', 'woofunnels-aero-checkout' ),
				'update_heading'      => __( 'Update', 'woofunnels-aero-checkout' ),
				'delete'              => __( 'Aero you sure you want to remove {{section_name}} Section?', 'woofunnels-aero-checkout' ),
				'fields'              => [
					'heading'     => __( 'Section Name', 'woofunnels-aero-checkout' ),
					'sub_heading' => __( 'Sub Heading', 'woofunnels-aero-checkout' ),
					'classes'     => __( 'Classes', 'woofunnels-aero-checkout' ),
				],
			],
			'steps_error_msgs'                            => [
				'single_step' => __( 'Step 1', 'woofunnels-aero-checkout' ),
				'two_step'    => __( 'Step 2', 'woofunnels-aero-checkout' ),
				'third_step'  => __( 'Step 3', 'woofunnels-aero-checkout' ),
			],
			'empty_step_error'                            => __( 'can\'t be blank. Add a few fields or remove the step and save again.', 'woofunnels-aero-checkout' ),
			'input_field_error'                           => [
				'billing_email' => __( 'Billing Email is required for processing payment', 'woofunnels-aero-checkout' ),
			],
			'same_as_billing'                             => __( 'Enable checkbox to show above fields', 'woofunnels-aero-checkout' ),
			'same_as_billing_label_hint'                  => __( 'This will make shipping address an optional checkbox when billing address is present in the form', 'woofunnels-aero-checkout' ),
			'same_as_shipping'                            => __( 'Different from shipping address', 'woofunnels-aero-checkout' ),
			'same_as_shipping_label_hint'                 => __( 'This will make shipping address an optional checkbox when billing address is present in the form', 'woofunnels-aero-checkout' ),
			'add_new_btn'                                 => __( 'Add Section', 'woofunnels-aero-checkout' ),
			'update_btn'                                  => __( 'Update', 'woofunnels-aero-checkout' ),
			'show_field_label1'                           => __( 'Status', 'woofunnels-aero-checkout' ),
			'show_field_label2'                           => __( 'Label', 'woofunnels-aero-checkout' ),
			'show_field_label3'                           => __( 'Placeholder', 'woofunnels-aero-checkout' ),
			'product_you_save_merge_tags'                 => __( 'Merge Tags: {{quantity}},{{saving_value}} or {{saving_percentage}}', 'woofunnels-aero-checkout' ),
			'field_types_label'                           => __( 'Field Type', 'woofunnels-aero-checkout' ),
			'field_types'                                 => [
				[
					'id'   => 'text',
					'name' => __( 'Single Line Text', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'checkbox',
					'name' => __( 'Checkbox', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'wfacp_radio',
					'name' => __( 'Radio', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'wfacp_wysiwyg',
					'name' => __( 'HTML', 'woofunnels-aero-checkout' ),
				],

				[
					'id'   => 'select',
					'name' => __( 'Dropdown', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'select2',
					'name' => __( 'Select2', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'multiselect',
					'name' => __( 'Multi Select', 'woofunnels-aero-checkout' ),
				],

				[
					'id'   => 'textarea',
					'name' => __( 'Paragraph Text', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'number',
					'name' => __( 'Number', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'hidden',
					'name' => __( 'Hidden', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'password',
					'name' => __( 'Password', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'email',
					'name' => __( 'Email', 'woofunnels-aero-checkout' ),
				],
			],
			'name_field_label'                            => __( 'Field ID (Order Meta Key)', 'woofunnels-aero-checkout' ),
			'name_field_label_hint'                       => __( "Field ID (Order Meta Key) where value of this field gets stored. Use '_' to seperate in case of multiple words. Example: date_of_birth", 'woofunnels-aero-checkout' ),
			'label_field_label'                           => __( 'Label', 'woofunnels-aero-checkout' ),
			'options_field_label'                         => __( 'Options (|) separated', 'woofunnels-aero-checkout' ),
			'default_field_label'                         => __( 'Default', 'woofunnels-aero-checkout' ),
			'multiselect_maximum_selection'               => __( 'Max number of selection', 'woofunnels-aero-checkout' ),
			'multiselect_maximum_error_field_label'       => __( 'Error Message', 'woofunnels-aero-checkout' ),
			'multiselect_maximum_error'                   => __( 'You can only select {maximum_number} items', 'woofunnels-aero-checkout' ),
			'multiselect_maximum_selection_default_count' => '',
			'shipping_field_placeholder'                  => __( 'Placeholder', 'woofunnels-aero-checkout' ),
			'shipping_field_placeholder_hint'             => __( 'Enter the default text for shipping method', 'woofunnels-aero-checkout' ),
			'default_field_placeholder'                   => __( 'Default Value', 'woofunnels-aero-checkout' ),
			'order_total_breakup_label'                   => __( 'Detailed Summary', 'woofunnels-aero-checkout' ),
			'order_total_breakup_hint'                    => __( 'Enable this to show detailed summary including Subtotal, Coupon, Fees, Shipping and Taxes whichever are applicable.', 'woofunnels-aero-checkout' ),
			'default_field_checkbox_options'              => [
				[
					'id'   => '1',
					'name' => __( 'True', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => '0',
					'name' => __( 'False', 'woofunnels-aero-checkout' ),
				],
			],
			'placeholder_field_label'                     => __( 'Placeholder', 'woofunnels-aero-checkout' ),
			'required_field_label'                        => __( 'Required', 'woofunnels-aero-checkout' ),
			'address'                                     => [
				'billing_address_first_name_hint' => __( 'Please keep this field turned OFF, if you are using First name separate field in the form', 'woofunnels-aero-checkout' ),
				'billing_address_last_name_hint'  => __( 'Please keep this field turned OFF, if you are using First name separate field in the form', 'woofunnels-aero-checkout' ),
				'first_name'                      => __( 'First Name', 'woocommerce' ),
				'last_name'                       => __( 'Last Name', 'woocommerce' ),
				'label'                           => __( 'Label', 'woocommerce' ),
				'placeholder'                     => __( 'Placeholder', 'woocommerce' ),
				'street_address1'                 => __( 'Street Address', 'woocommerce' ),
				'street_address2'                 => __( 'Street Address 2', 'woocommerce' ),
				'company'                         => __( 'Company', 'woocommerce' ),
				'city'                            => __( 'City', 'woocommerce' ),
				'state'                           => __( 'State', 'woocommerce' ),
				'zip'                             => __( 'Zip/Postcode', 'woocommerce' ),
				'country'                         => __( 'Country', 'woocommerce' ),
			],
			'add_field'                                   => __( 'Add Field', 'woofunnels-aero-checkout' ),
			'edit_field'                                  => __( 'Edit Field', 'woofunnels-aero-checkout' ),
			'shipping_address_message'                    => WFACP_Common::default_shipping_placeholder_text(),

			'show_on_thankyou'           => __( 'Show On Thank You Page', 'woofunnels-aero-checkout' ),
			'show_in_email'              => __( 'Show In Order Email', 'woofunnels-aero-checkout' ),
			'enable_time_date'           => __( 'Enable Time', 'woofunnels-aero-checkout' ),
			'time_format_label'          => __( 'Time Format', 'woofunnels-aero-checkout' ),
			'time_format_options'        => [
				[
					'value' => '12',
					'name'  => __( '12 Hours', 'woofunnels-aero-checkout' ),
				],
				[
					'value' => '24',
					'name'  => __( '24 Hours', 'woofunnels-aero-checkout' ),
				],
			],
			'validation_error'           => __( 'Validation Error', 'woofunnels-aero-checkout' ),
			'delete_c_field'             => __( 'Are you sure you want to delete field', 'woofunnels-aero-checkout' ),
			'delete_c_field_sub_heading' => __( 'This action can be undone.', 'woofunnels-aero-checkout' ),
			'yes_delete_the_field'       => __( 'Yes, Delete the field', 'woofunnels-aero-checkout' ),

		];
		$data['design']['preview_step']   = [
			'single_step' => __( 'One Step', 'wordpress' ),
			'two_step'    => __( 'Two Step', 'wordpress' ),
			'third_step'  => __( 'Three Step', 'wordpress' ),
		];
		$data['design']['section']        = [];
		$data['design']['settings']       = [];
		$data['settings']['radio_fields'] = [
			[
				'value' => 'true',
				'name'  => __( 'Yes', 'woofunnels-aero-checkout' ),
			],
			[
				'value' => 'false',
				'name'  => __( 'No', 'woofunnels-aero-checkout' ),
			],
		];

		$data['settings']['preview_section_heading']          = __( 'Heading (optional)', 'woofunnels-aero-checkout' );
		$data['settings']['preview_section_subheading']       = __( 'Subheading (optional)', 'woofunnels-aero-checkout' );
		$data['settings']['preview_field_admin_heading']      = __( 'Multistep Field Preview', 'woofunnels-aero-checkout' );
		$data['settings']['preview_field_preview_text']       = __( 'Preview Link Text', 'woofunnels-aero-checkout' );
		$data['settings']['preview_field_admin_heading_hint'] = __( 'Enable this on multistep form to help user preview entered values at next steps. It helps user recap the information and prevent inadvertent errors.', 'woofunnels-aero-checkout' );
		$data['settings']['preview_field_admin_note']         = __( 'This Feature is available only for multistep form', 'woofunnels-aero-checkout' );

		$data['settings']['empty_cart_heading_area']       = __( 'Empty cart', 'woofunnels-aero-checkout' );
		$data['settings']['empty_cart_heading_subheading'] = __( 'Message when cart is empty', 'woofunnels-aero-checkout' );
		$data['settings']['empty_cart_heading']            = __( 'Empty cart message', 'woofunnels-aero-checkout' );
		$data['settings']['empty_cart_heading_hint']       = __( 'Message when no product added to cart', 'woofunnels-aero-checkout' );
		$data['settings']['scripts']                       = [
			'heading'                   => __( 'Embed Script', 'woofunnels-aero-checkout' ),
			'sub_heading'               => __( 'Add custom scripts on checkout page', 'woofunnels-aero-checkout' ),
			'header_heading'            => __( 'Header', 'woofunnels-aero-checkout' ),
			'header_script_placeholder' => __( 'Paste your code here', 'woofunnels-aero-checkout' ),
			'footer_heading'            => __( 'Footer', 'woofunnels-aero-checkout' ),
			'footer_script_placeholder' => __( 'Paste your code here', 'woofunnels-aero-checkout' ),
		];
		$data['settings']['style']                         = [
			'heading'                  => __( 'Custom CSS', 'woofunnels-aero-checkout' ),
			'sub_heading'              => __( 'Add custom CSS on checkout page', 'woofunnels-aero-checkout' ),
			'header_heading'           => __( 'CSS', 'woofunnels-aero-checkout' ),
			'header_style_placeholder' => __( 'Paste your CSS code here', 'woofunnels-aero-checkout' ),

		];
		$data['google_autocomplete']                       = [
			'heading'       => __( 'Google Address Autocompletion', 'woofunnels-aerocheckout-powerpack' ),
			'sub_heading'   => __( 'Enable this to provide address suggestions and let buyers quickly fill up form as they enter billing and shipping address.', 'woofunnels-aerocheckout-powerpack' ),
			'country_label' => __( 'Disallow Countries (Optional)', 'woofunnels-aerocheckout-powerpack' ),
		];
		$couponText                                        = __( 'Enable this to surprise your buyers with special auto applied coupon. Reduces cart abandonment rate and discourages buyers from hunting coupons else where.', 'woofunnels-aero-checkout' );

		$data['settings']['coupons']                = [
			'heading'                 => __( 'Auto Apply Coupons', 'woofunnels-aero-checkout' ),
			'sub_heading'             => $couponText,
			'auto_add_coupon_heading' => __( 'Auto Apply Coupon', 'woofunnels-aero-checkout' ),
			'coupon_heading'          => __( 'Coupon Code', 'woofunnels-aero-checkout' ),
			'search_placeholder'      => __( 'Enter coupon code here', 'woofunnels-aero-checkout' ),
			'select_coupon'           => __( 'Choose Coupon', 'woofunnels-aero-checkout' ),
			'disable_coupon'          => __( 'Disable Coupon Field', 'woofunnels-aero-checkout' ),
			'active'                  => __( 'Active', 'woofunnels-aero-checkout' ),
			'inactive'                => __( 'Inactive', 'woofunnels-aero-checkout' ),
		];
		$data['optimizations']['google']            = [
			'heading'             => __( 'Google Autocomplete', 'woofunnels-aero-checkout' ),
			'sub_heading'         => __( '', 'woofunnels-aero-checkout' ),
			'enable'              => __( 'Enable google autocomplete', 'woofunnels-aero-checkout' ),
			'api_key'             => __( 'Enter Api Key', 'woofunnels-aero-checkout' ),
			'api_key_placeholder' => 'AIzaSyCJZg_lvlTS7-2BXb5fZPEAekBs3bjOW-o',
			'api_key_hint'        => __( 'Api Key', 'woofunnels-aero-checkout' ) . ' (https://developers.google.com/maps/documentation/javascript/get-api-key#key)',
		];
		$data['optimizations']['preferred_country'] = [
			'heading'     => __( 'Preferred Countries', 'woofunnels-aero-checkout' ),
			'sub_heading' => __( 'By default, WooCommerce shows countries in alphabetical order. Enable this option to re-arrange the list such that your top selling countries are always on top', 'woofunnels-aero-checkout' ),
			'label'       => __( 'Select Countries', 'woofunnels-aero-checkout' ),
			'placeholder' => ' ',
			'hint'        => 'US=United States, GB=United Kingdom,CA=CANADA',
		];


		$data['optimizations']['smart_buttons'] = [
			'heading'          => __( 'Smart Buttons for Express Checkout', 'woofunnels-aero-checkout' ),
			'sub_heading'      => __( "Enable this to show smart buttons for $links_string and $amazonelink for express checkout. For Stripe, Payment Request Buttons should be enabled and configured.", 'woofunnels-aero-checkout' ),
			'position_heading' => __( 'Choose Position', 'woofunnels-aero-checkout' ),
			'positions'        => self::smart_buttons_positions(),
		];

		$data['settings']['product_switching'] = [
			'heading'                => __( 'Product Selection', 'woofunnels-aero-checkout' ),
			'sub_heading'            => __( 'You can manage the quantity increment, quick view provision from here', 'woofunnels-aero-checkout' ),
			'you_save_text'          => __( 'You Save text', 'woofunnels-aero-checkout' ),
			'hide_quantity_switcher' => __( 'Hide Quantity Incrementor', 'woofunnels-aero-checkout' ),
			'hide_quick_view'        => __( 'Hide Quick View', 'woofunnels-aero-checkout' ),
			'hide_product_image'     => __( 'Hide Product Image', 'woofunnels-aero-checkout' ),
		];
		$data['settings']['coupon']            = [
			'success_message_heading'      => __( 'Success message', 'woofunnels-aero-checkout' ),
			'success_message_heading_hint' => __( 'Use merge tags to display Coupon Code: {{coupon_code}} & Coupon Value: {{coupon_value}} in success message', 'woofunnels-aero-checkout' ),
			'remove_message_heading'       => __( 'Failure message', 'woofunnels-aero-checkout' ),
			'style_heading'                => __( 'Collapsible', 'woofunnels-aero-checkout' ),
			'style_options'                => [
				[
					'value' => 'true',
					'name'  => __( 'yes', 'woofunnels-aero-checkout' ),
				],
				[
					'value' => 'false',
					'name'  => __( 'no', 'woofunnels-aero-checkout' ),
				],
			],
			'sub_heading'                  => __( 'You can manage the quantity increment, quick view provision from here', 'woofunnels-aero-checkout' ),
		];

		$timezone_heading             = __( 'Enable this to set expiry of checkout page after certain sales or at a particular date. Used for generating scarcity during time sensitive campaigns.', 'woofunnels-aero-checkout' );
		$timezone_text                = __( '<p>Note: The settings are only applicable for product specific checkout pages or order forms</p>', 'woofunnels-aero-checkout' );
		$data['settings']['advanced'] = [
			'heading'                           => __( 'Time Checkout Expiry', 'woofunnels-aero-checkout' ),
			'sub_heading'                       => $timezone_heading . $timezone_text,
			'close_after'                       => __( 'Close This checkout Page After # of Orders', 'woofunnels-aero-checkout' ),
			'close_checkout_after_date'         => __( 'Close Checkout After Date', 'woofunnels-aero-checkout' ),
			'total_purchased_allowed'           => __( 'Total Orders Allowed', 'woofunnels-aero-checkout' ),
			'total_purchased_allowed_hint'      => __( 'After given number of order made, disable this checkout page and redirect buyer to a specified URL', 'woofunnels-aero-checkout' ),
			'total_purchased_redirect_url'      => __( 'Redirect URL', 'woofunnels-aero-checkout' ),
			'total_purchased_redirect_url_hint' => __( 'Buyer will be redirect to given URL here', 'woofunnels-aero-checkout' ),
			'close_checkout_on'                 => __( 'Close Checkout On', 'woofunnels-aero-checkout' ),
			'close_checkout_on_hint'            => __( 'Set the date to close this checkout page', 'woofunnels-aero-checkout' ),
			'close_checkout_redirect_url'       => __( 'Closed Checkout Redirect URL', 'woofunnels-aero-checkout' ),
			'close_checkout_redirect_url_hint'  => __( 'Buyer will be redirect to given URL here', 'woofunnels-aero-checkout' ),
			'note_for_global_checkout'          => __( 'Note: These settings are only applicable for dedicated checkout page', 'woofunnels-aero-checkout' ),

		];

		$data['settings']['autopopulate_fields'] = [
			'heading'     => __( 'Prefill Form for Abandoned Users', 'woofunnels-aero-checkout' ),
			'sub_heading' => __( 'Enable this to populate previously entered values as abandoned users return back to checkout.', 'woofunnels-aero-checkout' ),
		];
		$data['settings']['autopopulate_state']  = [
			'heading'         => __( 'Auto fill State from Zip Code and Country', 'woofunnels-aero-checkout' ),
			'sub_heading'     => __( 'Enable this to auto fill State from combination of Zip code and Country', 'woofunnels-aero-checkout' ),
			'service_heading' => __( 'Choose service', 'woofunnels-aero-checkout' ),
			'services'        => [
				[
					'value' => 'zippopotamus',
					'name'  => __( 'By Zippopotamus', 'woofunnels-aero-checkout' ),
				],
			]
		];

		$data['settings']['auto_fill_url'] = [
			'heading'                => __( 'Generate URL to populate checkout', 'woofunnels-aero-checkout' ),
			'sub_heading'            => __( 'Use these settings to pre-populate checkout with URLs parameters', 'woofunnels-aero-checkout' ),
			'product_ids'            => __( 'Product', 'woofunnels-aero-checkout' ),
			'product_ids_hint'       => __( 'Tip: Enter Comma Separated Product IDs for multiple products', 'woofunnels-aero-checkout' ),
			'quantity'               => __( 'Quantity', 'woofunnels-aero-checkout' ),
			'quantity_hint'          => __( 'Tip: Enter Comma Separated quantity value for multiple products', 'woofunnels-aero-checkout' ),
			'fields_label'           => __( 'Fields', 'woofunnels-aero-checkout' ),
			'fields_options'         => [
				[
					'value' => 'billing_email',
					'name'  => __( 'Email', 'woocommerce' ),
				],
				[
					'value' => 'billing_first_name',
					'name'  => __( 'First Name', 'woocommerce' ),
				],
				[
					'value' => 'billing_last_name',
					'name'  => __( 'Last Name', 'woocommerce' )
				]
			],
			'auto_responder_label'   => __( 'Email Service', 'woofunnels-aero-checkout' ),
			'auto_responder_options' => self::auto_responder_options(),
			'perfill_url'            => __( 'Checkout URL', 'woofunnels-aero-checkout' )
		];

		$data['settings']['analytics'] = [
			'heading'             => __( 'Tracking and Analytics', 'woofunnels-aero-checkout' ),
			'hint'                => __( 'Use this to adjust the tracking events for one-page checkouts', 'woofunnels-aero-checkout' ),
			'sub_heading'         => __( 'Enable this to auto fill State from combination of Zip code and Country', 'woofunnels-aero-checkout' ),
			'service_heading'     => __( 'Choose service', 'woofunnels-aero-checkout' ),
			'pixel'               => [
				'heading' => __( 'Facebook Pixel', 'woofunnels-aero-checkout' ),
			],
			'google'              => [
				'heading' => __( 'Google Analytics', 'woofunnels-aero-checkout' ),
			],
			'events'              => [
				'add_to_cart' => __( 'Enable AddtoCart Event', 'woofunnels-aero-checkout' ),
				'page_view' => __( 'Enable PageView Event', 'woofunnels-aero-checkout' ),
				'checkout'    => __( 'Enable BeginCheckout Event', 'woofunnels-aero-checkout' ),
				'payment'     => __( 'Enable AddPaymentInfo Event', 'woofunnels-aero-checkout' )
			],
			'options_label'       => __( 'Trigger Event', 'woofunnels-aero-checkout' ),
			'override'            => __( 'Override Global Settings', 'woofunnels-aero-checkout' ),
			'track_event_options' => self::track_events_options()
		];
		$data['settings']['tracking']  = [
			'heading' => __( 'Tracking and analytics', 'woofunnels-aero-checkout' ),
			'label'   => __( 'Override global settings', 'woofunnels-aero-checkout' )

		];
		$shipping_address_options      = WFACP_Common::get_single_address_fields( 'shipping' );
		$address_options               = WFACP_Common::get_single_address_fields();
		$data['shipping-address']      = $shipping_address_options['fields_options'];
		$data['address']               = $address_options['fields_options'];

		$shipping_address_options = WFACP_Common::get_single_address_fields( 'shipping' );
		$address_options          = WFACP_Common::get_single_address_fields();
		$data['shipping-address'] = $shipping_address_options['fields_options'];
		$data['address']          = $address_options['fields_options'];
		$data                     = apply_filters( 'wfacp_builder_default_localization', $data );

		return apply_filters( 'wfacp_global_localization_texts', $data );
	}

	public static function auto_responder_options() {
		$options = [
			'select_email_provider' => [
				'id'         => 'select_email_provider',
				'name'       => __( 'Select Email Service Provider', 'woocommerce' ),
				'merge_tags' => []
			],
			'activecampaign'        => [
				'id'         => 'activecampaign',
				'name'       => __( 'ActiveCampaign', 'woocommerce' ),
				'merge_tags' => [
					'billing_email'      => '%EMAIL%',
					'billing_first_name' => '%FIRSTNAME%',
					'billing_last_name'  => '%LASTNAME%',
				]
			],
			'convertkit'            => [
				'id'         => 'convertkit',
				'name'       => __( 'Convertkit', 'woocommerce' ),
				'merge_tags' => [
					'billing_email'      => '{{subscriber.email }}',
					'billing_first_name' => '{{subscriber.first_name}}',
					'billing_last_name'  => '{{subscriber.last_name}}',
				]
			],
			'drip'                  => [
				'id'         => 'drip',
				'name'       => __( 'Drip', 'woocommerce' ),
				'merge_tags' => [
					'billing_email'      => '{{subscriber.email}}',
					'billing_first_name' => '{{subscriber.first_name}}',
					'billing_last_name'  => '{{subscriber.last_name}}',
				]
			],
			'infusionsoft'          => [
				'id'         => 'infusionsoft',
				'name'       => __( 'InfusionSoft', 'woocommerce' ),
				'merge_tags' => [
					'billing_email'      => '~Contact.Email~',
					'billing_first_name' => '~Contact.FirstName~',
					'billing_last_name'  => '~Contact.LastName~',
				]
			],
			'mailchimp'             => [
				'id'         => 'mailchimp',
				'name'       => __( 'Mailchimp', 'woocommerce' ),
				'merge_tags' => [
					'billing_email'      => '*|EMAIL|*',
					'billing_first_name' => '*|FNAME|*',
					'billing_last_name'  => '*|LNAME|*',
				]
			],
			'other'                 => [
				'id'         => 'other',
				'name'       => __( 'Other', 'woocommerce' ),
				'merge_tags' => [
					'billing_email'      => 'xxx',
					'billing_first_name' => 'xxx',
					'billing_last_name'  => 'xxx',
				]
			],
			'getresponse'           => [
				'id'         => 'getresponse',
				'name'       => __( 'GetRepsonse', 'woocommerce' ),
				'merge_tags' => [
					'billing_email'      => '[[email]]',
					'billing_first_name' => '[[firstname]]',
					'billing_last_name'  => '[[lastname]]',
				]
			]
		];

		return apply_filters( 'wfacp_auto_responders_settings', $options );
	}

	public static function smart_buttons_positions() {

		$positions = [
			//''
			[
				'id'   => 'wfacp_form_single_step_start',
				'name' => __( 'At top of checkout Page', 'woofunnels-aero-checkout' ),
			],
			[
				'id'   => 'wfacp_before_product_switching_field',
				'name' => __( 'Before product switcher', 'woofunnels-aero-checkout' ),
			],
			[
				'id'   => 'wfacp_after_product_switching_field',
				'name' => __( 'After product switcher', 'woofunnels-aero-checkout' ),
			],
			[
				'id'   => 'wfacp_before_order_summary_field',
				'name' => __( 'Before order summary', 'woofunnels-aero-checkout' ),
			],
			[
				'id'   => 'wfacp_after_order_summary_field',
				'name' => __( 'After order summary', 'woofunnels-aero-checkout' ),
			],
			[
				'id'   => 'wfacp_before_payment_section',
				'name' => __( 'Above the payment gateways', 'woofunnels-aero-checkout' ),
			],
		];

		return apply_filters( 'wfacp_smart_buttons_positions', $positions );
	}


	public static function get_html_excluded_field() {
		return [ 'order_summary', 'order_total', 'order_coupon', 'product_switching', 'shipping_calculator' ];

	}

	public static function is_mobile_device() {
		$detect = new WFACP_Mobile_Detect();
		if ( $detect->isMobile() && ! $detect->istablet() ) {
			return true;
		}

		return false;
	}

	public static function get_current_user_role() {
		if ( is_user_logged_in() ) {
			if ( is_super_admin() ) {
				return 'administrator';
			} else {
				return 'customer';
			}
		}

		return 'guest';
	}


	/**
	 * Save all publish checkout pages into transient
	 */
	public static function save_publish_checkout_pages_in_transient( $force = true, $count = '-1' ) {
		if ( ! empty( self::$wfacp_publish_posts ) ) {
			return self::$wfacp_publish_posts;
		}

		$output   = [];
		$output[] = [
			'id'   => 0,
			'name' => __( 'Default WooCommerce Checkout Page', 'woofunnels-aero-checkout' ),
			'type' => 'default',
		];
		$data     = WFACP_Common::get_saved_pages();
		if ( is_array( $data ) && count( $data ) > 0 ) {

			foreach ( $data as $v ) {
				$output[] = [
					'id'   => $v['ID'],
					'name' => $v['post_title'],
					'type' => 'wfacp',
				];
			}
		}

		$output = apply_filters( 'wfacp_checkout_post_list', $output );

		if ( count( $output ) == 0 ) {

			return [];
		}

		/**
		 * @var $Woofunnel_cache_obj WooFunnels_Cache
		 */
		$Woofunnel_cache_obj     = WooFunnels_Cache::get_instance();
		$Woofunnel_transient_obj = WooFunnels_Transient::get_instance();

		$cache_key = 'wfacp_publish_posts';
		/** $force = true */
		if ( true === $force ) {
			$Woofunnel_transient_obj->set_transient( $cache_key, $output, DAY_IN_SECONDS, WFACP_SLUG );
			$Woofunnel_cache_obj->set_cache( $cache_key, $output, 'free-gift' );

			return $output;
		}

		$cache_data = $Woofunnel_cache_obj->get_cache( $cache_key, WFACP_SLUG );
		if ( false !== $cache_data ) {
			$wfacp_publish_posts = $cache_data;
		} else {
			$transient_data = $Woofunnel_transient_obj->get_transient( $cache_key, WFACP_SLUG );

			if ( false !== $transient_data ) {
				$wfacp_publish_posts = $transient_data;
			} else {

				$Woofunnel_transient_obj->set_transient( $cache_key, $output, DAY_IN_SECONDS, WFACP_SLUG );
			}
			$Woofunnel_cache_obj->set_cache( $cache_key, $output, WFACP_SLUG );
		}

		self::$wfacp_publish_posts = $wfacp_publish_posts;

		return self::$wfacp_publish_posts;
	}

	/**
	 * Return WFACP Post id if user override default checkout from global settings
	 */
	public static function get_checkout_page_id() {
		$checkout_page_id = 0;
		$global_settings  = get_option( '_wfacp_global_settings', [] );

		if ( isset( $global_settings['override_checkout_page_id'] ) ) {
			$checkout_page_id = absint( $global_settings['override_checkout_page_id'] );
		}

		return apply_filters( 'wfacp_global_checkout_page_id', $checkout_page_id );
	}

	public static function is_global_checkout( $id = 0 ) {
		if ( $id == 0 ) {
			return false;
		}

		if ( $id == self::get_checkout_page_id() ) {
			return true;
		}

		return false;

	}

	public static function make_cart_empty() {
		$items = WC()->cart->get_cart();
		if ( ! empty( $items ) ) {
			foreach ( $items as $key => $item ) {
				if ( isset( $item['_wfob_options'] ) ) {
					continue;
				}
				WC()->cart->remove_cart_item( $key );
			}
		}
	}

	/**
	 * Stored Order bump Item in variable when product switcher radio options triggered
	 */
	public static function order_bump_restored_start() {
		$items = WC()->cart->get_cart();
		if ( ! empty( $items ) ) {
			foreach ( $items as $key => $item ) {
				if ( isset( $item['_wfob_options'] ) ) {
					self::$order_bumps[ $key ] = $item;
					continue;
				}
			}
		}
	}

	/**
	 * Restore a Order bump cart item when product switcher add a product in cart for Radio Option.
	 *
	 * @param string $cart_item_key Cart item key to restore to the cart.
	 *
	 * @return bool
	 */
	public static function order_bump_restored_end() {
		if ( count( self::$order_bumps ) > 0 ) {
			foreach ( self::$order_bumps as $item_key => $item ) {
				do_action( 'woocommerce_restore_cart_item', $item_key, WC()->cart );
				WC()->cart->cart_contents[ $item_key ] = $item;
				do_action( 'woocommerce_cart_item_restored', $item_key, WC()->cart );
			}
		}
	}

	/**
	 * Make Proper table layout in Mini cart  for shipping columns
	 *
	 * @param $spans
	 *
	 * @return mixed
	 */
	public static function order_review_shipping_colspan( $spans ) {
		global $wfacp_colspan_attr_1, $wfacp_colspan_attr_2;
		if ( ! is_null( $wfacp_colspan_attr_1 ) ) {
			$spans['first'] = $wfacp_colspan_attr_1;
		}
		if ( ! is_null( $wfacp_colspan_attr_2 ) ) {
			$spans['second'] = $wfacp_colspan_attr_2;
		}

		return $spans;
	}


	public static function get_product_switcher_templates() {
		$templates = [
			'default' => array(
				'slug'      => 'default',
				'path'      => WFACP_TEMPLATE_COMMON . '/layout_9/template.php',
				'name'      => __( 'Default', 'woofunnels-aero-checkout' ),
				'thumbnail' => WFACP_PLUGIN_URL . '/public/template-common/images/defualt_product_switcher.jpg',
			),
		];

		return $templates;
	}

	public static function woocommerce_locate_template( $template ) {
		$wfacp_dir = strpos( $template, 'wfacp/checkout/cart-shipping.php' );
		if ( false !== $wfacp_dir ) {
			return WFACP_TEMPLATE_COMMON . '/checkout/cart-shipping.php';
		}

		$wfacp_dir = strpos( $template, 'wfacp/checkout/cart-recurring-shipping.php' );
		if ( false !== $wfacp_dir ) {
			return WFACP_TEMPLATE_COMMON . '/checkout/cart-recurring-shipping.php';
		}

		$wfacp_dir = strpos( $template, 'wfacp/checkout/cart-recurring-shipping-calculate.php' );
		if ( false !== $wfacp_dir ) {
			return WFACP_TEMPLATE_COMMON . '/checkout/cart-recurring-shipping-calculate.php';
		}

		return $template;

	}

	public static function track_events_content_id_options() {

		$events = [
			[ 'id' => '0', 'name' => __( 'Select content id parameter', 'woofunnels-aero-checkout' ) ],
			[ 'id' => 'product_id', 'name' => __( 'Product ID', 'woofunnels-aero-checkout' ) ],
			[ 'id' => 'product_sku', 'name' => __( 'Product Sku', 'woofunnels-aero-checkout' ) ],
		];

		return apply_filters( 'wfacp_track_events_content_id_options', $events );
	}

	public static function track_events_options() {

		$events = [
			[ 'id' => 'load', 'name' => __( 'On Page Load', 'woofunnels-aero-checkout' ) ],
			[ 'id' => 'button', 'name' => __( 'On Button Click', 'woofunnels-aero-checkout' ) ],
			[ 'id' => 'email', 'name' => __( 'On Email Capture', 'woofunnels-aero-checkout' ) ]
		];

		return apply_filters( 'wfacp_track_event_options', $events );
	}

	public static function get_default_global_settings() {
		$data                  = [];
		$global_template_pages = WFACP_Common::save_publish_checkout_pages_in_transient();
		$wfacp_global_checkout = [
			'fields'     => [

				[
					'type'          => 'select',
					'styleClasses'  => 'group-one-class',
					'label'         => __( 'Override Default Checkout', 'woofunnels-aero-checkout' ),
					'hint'          => __( 'Set Aero Checkout page as  default page for your all products', 'woofunnels-aero-checkout' ),
					'default'       => '0',
					'values'        => $global_template_pages,
					'model'         => 'override_checkout_page_id',
					'selectOptions' => [
						'hideNoneSelectedText' => true,
					],
				],

			],
			'legend'     => __( 'Global Checkout', 'woofunnels-aero-checkout' ),
			'wfacp_data' => [ 'id' => 'wfacp-global-checkout', 'class' => 'wfacp_global_checkout', 'title' => __( 'Global Checkout', 'woofunnels-aero-checkout' ) ]
		];

		$wfacp_tracking_analytics = [
			'fields'     => [
				//Facebook
				[
					'type'         => 'label',
					'hint'         => sprintf( __( 'These settings are now moved to <a href="%s">WooFunnels > Settings > General  </a>' ), BWF_Admin_General_Settings::get_instance()->get_settings_link() ),
					'styleClasses' => [ 'bwf_tracing_general_text' ],
					'model'        => 'custom_html_tracking_general_',
				],
			],
			'legend'     => __( 'Tracking & Analytics', 'woofunnels-aero-checkout' ),
			'wfacp_data' => [ 'id' => 'wfacp-tracking-analytics', 'class' => 'wfacp_tracking_analytics', 'title' => __( 'Tracking & Analytics', 'woofunnels-aero-checkout' ) ]
		];


		$wfacp_miscellaneous_analytics = [
			'fields'     => [

				[
					'type'         => 'checkbox',
					'inputType'    => 'text',
					'label'        => __( 'Set Shipping Method Prices in Ascending Order', 'woofunnels-aero-checkout' ),
					'default'      => '',
					'styleClasses' => 'group-one-class wfacp_set_shipping_method_wrap wfacp_checkbox_wrap',
					'model'        => 'wfacp_set_shipping_method',
					'is_bool'      => false,
				],

			],
			'legend'     => __( 'Advanced', 'woofunnels-aero-checkout' ),
			'wfacp_data' => [ 'id' => 'wfacp-miscellaneous', 'class' => 'wfacp_miscellaneous', 'title' => __( 'Advanced', 'woofunnels-aero-checkout' ) ]
		];

		$wfacp_appearance = [
			'fields'     => [

				[
					'type'         => 'textArea',
					'inputType'    => 'text',
					'label'        => __( 'Custom CSS Tweaks', 'woofunnels-aero-checkout' ),
					'styleClasses' => 'wfacp_global_css_wrap_field',
					'model'        => 'wfacp_checkout_global_css',
					'hint'         => __( 'Add your custom CSS to apply on all AeroCheckout pages', 'woofunnels-aero-checkout' ),

				],

			],
			'legend'     => __( 'Global Custom CSS', 'woofunnels-aero-checkout' ),
			'wfacp_data' => [ 'id' => 'wfacp-global_css', 'class' => 'wfacp_global_css', 'title' => __( 'Custom CSS', 'woofunnels-aero-checkout' ) ]
		];

		$wfacp_external_script = [
			'fields'     => [

				[
					'type'         => 'textArea',
					'inputType'    => 'text',
					'label'        => __( 'External JS Scripts', 'woofunnels-aero-checkout' ),
					'styleClasses' => 'wfacp_global_external_script_field',
					'model'        => 'wfacp_global_external_script',
					'hint'         => __( 'These scripts will be globally embedded in all the AeroCheckout Pages.', 'woofunnels-aero-checkout' ),

				],

			],
			'legend'     => __( 'External Scripts', 'woofunnels-aero-checkout' ),
			'wfacp_data' => [ 'id' => 'wfacp-global_external_script', 'class' => 'wfacp_global_external_script', 'title' => __( 'External Scripts', 'woofunnels-aero-checkout' ) ]
		];


		$url              = 'https://buildwoofunnels.com/docs/aerocheckout/global-settings/googlemap-api-key/';
		$google_maps_hint = "<a href='{$url}' target='_blank'>" . __( 'Click here for more info', 'woofunnels-aero-checkout' ) . "</a>";
		$autocomplete     = [
			'fields'     => [
				[
					'type'         => "input",
					'inputType'    => 'text',
					'label'        => __( 'Google Map API Key', 'woofunnels-aerocheckout-powerpack' ),
					'default'      => '',
					'hint'         => __( 'Looking for Google Map API key?', 'woofunnels-aero-checkout' ) . ' ' . $google_maps_hint,
					'styleClasses' => 'group-one-class wfacp_checkbox_wrap',
					'model'        => 'wfacp_google_address_key',
				]
			],
			'legend'     => __( 'Address Autocomplete', 'woofunnels-aerocheckout-powerpack' ),
			'wfacp_data' => [ 'id' => 'wfacp-powerpack', 'class' => 'wfacp_powerpack', 'title' => __( 'Address Autocomplete', 'woofunnels-aerocheckout-powerpack' ) ]
		];

		$data['groups'][] = $wfacp_global_checkout;
		$data['groups'][] = $wfacp_tracking_analytics;
		$data['groups'][] = $autocomplete;

		$data['groups'][] = $wfacp_appearance;
		$data['groups'][] = $wfacp_external_script;
		$data['groups'][] = $wfacp_miscellaneous_analytics;


		return apply_filters( 'wfacp_global_setting_fields', $data );
	}

	/*************** Page layout section start ***************/
	public static function get_page_layout_multistep() {
		$product_field  = self::get_product_field();
		$advanced_field = self::get_advanced_fields();
		$data           = array(
			'steps'                       => self::get_default_steps_fields( true ),
			'fieldsets'                   => array(
				'single_step' => array(
					array(
						'name'        => __( 'Customer Information', 'woofunnels-aero-checkout' ),
						'class'       => '',
						'sub_heading' => '',
						'fields'      => array(
							array(
								'label'        => __( 'Email', 'woocommerce' ),
								'required'     => 'true',
								'type'         => 'email',
								'class'        => array(
									0 => 'form-row-wide',
								),
								'validate'     => array(
									0 => 'email',
								),
								'autocomplete' => 'email username',
								'priority'     => '110',
								'id'           => 'billing_email',
								'field_type'   => 'billing',
								'placeholder'  => __( 'john.doe@example.com ', 'woofunnels-aero-checkout' ),
							),
						),

					),
					[
						'name'        => __( 'Shipping Information', 'woofunnels-aero-checkout' ),
						'class'       => '',
						'is_default'  => 'yes',
						'sub_heading' => '',
						'fields'      => array(
							array(
								'label'        => __( 'First name', 'woocommerce' ),
								'required'     => 'true',
								'class'        => [
									0 => 'form-row-first',
								],
								'autocomplete' => 'given-name',
								'priority'     => '10',
								'type'         => 'text',
								'id'           => 'billing_first_name',
								'field_type'   => 'billing',
								'placeholder'  => __( 'John', 'woofunnels-aero-checkout' ),

							),
							array(
								'label'        => __( 'Last name', 'woocommerce' ),
								'required'     => 'true',
								'class'        => array(
									0 => 'form-row-last',
								),
								'autocomplete' => 'family-name',
								'priority'     => '20',
								'type'         => 'text',
								'id'           => 'billing_last_name',
								'field_type'   => 'billing',
								'placeholder'  => __( 'Doe', 'woofunnels-aero-checkout' ),
							),

							self::get_single_address_fields(),
							self::get_single_address_fields( 'shipping' ),

						),
					],
				),
				'two_step'    => [
					[
						'name'        => __( 'Your Products', 'woofunnels-aero-checkout' ),
						'class'       => '',
						'sub_heading' => '',
						'html_fields' => [ 'product_switching' => true ],
						'fields'      => [
							$product_field['product_switching'],
						],
					],
				],
				'third_step'  => [

					[
						'name'        => __( 'Order Summary', 'woofunnels-aero-checkout' ),
						'class'       => '',
						'sub_heading' => '',
						'html_fields' => [ 'order_summary' => true ],
						'fields'      => [
							$advanced_field['order_coupon'],
							$advanced_field['order_summary'],
						],
					],
				],
			),
			'enabled_product_switching'   => 'yes',
			'have_billing_address'        => true,
			'have_shipping_address'       => true,
			'have_billing_address_index'  => 4,
			'have_shipping_address_index' => 5,
			'have_coupon_field'           => false,
			'have_shipping_method'        => true,
			'current_step'                => 'third_step',
		);

		$advanced_field = self::get_advanced_fields();

		if ( isset( $advanced_field['shipping_calculator'] ) ) {
			$data['fieldsets']['two_step'][] = array(
				'name'        => __( 'Shipping Method', 'woocommerce' ),
				'class'       => '',
				'html_fields' => [ 'shipping_calculator' => true ],
				'sub_heading' => '',
				'fields'      => array(
					$advanced_field['shipping_calculator'],
				),
			);
		}

		return $data;
	}

	/**
	 * Get page layout data
	 *
	 * @param $page_id
	 *
	 * @return array|mixed
	 */
	public static function get_page_layout( $page_id ) {

		$data           = WFACP_Common::get_post_meta_data( $page_id, '_wfacp_page_layout' );
		$_wfacp_version = WFACP_Common::get_post_meta_data( $page_id, '_wfacp_version' );
		$stepone_title  = __( 'Shipping Information', 'woofunnels-aero-checkout' );

		if ( $_wfacp_version == WFACP_VERSION ) {
			$stepone_title = __( 'Customer Information', 'woofunnels-aero-checkout' );
		}

		if ( empty( $data ) ) {

			$data = array(
				'steps'     => self::get_default_steps_fields(),
				'fieldsets' => array(
					'single_step' => [],
				),

				'current_step'                => 'single_step',
				'have_billing_address'        => 'true',
				'have_shipping_address'       => 'true',
				'have_billing_address_index'  => 5,
				'have_shipping_address_index' => 4,
				'enabled_product_switching'   => "yes",
				'have_coupon_field'           => true,
				'have_shipping_method'        => true,
			);


			$current_version = WFACP_Common::get_checkout_page_version();


			$sub_heading_text = '';

			if ( version_compare( $current_version, '1.9.3', '>' ) ) {
				$sub_heading_text = '';
			}


			$data['fieldsets']['single_step'][] = array(
				'name'        => $stepone_title,
				'class'       => '',
				'is_default'  => 'yes',
				'sub_heading' => $sub_heading_text,
				'fields'      => array(
					array(
						'label'        => __( 'Email', 'woocommerce' ),
						'required'     => 'true',
						'type'         => 'email',
						'class'        => array(
							0 => 'form-row-wide',
						),
						'validate'     => array(
							0 => 'email',
						),
						'autocomplete' => 'email username',
						'priority'     => '110',
						'id'           => 'billing_email',
						'field_type'   => 'billing',
						'placeholder'  => __( 'john.doe@example.com ', 'woofunnels-aero-checkout' ),
					),
					array(
						'label'        => __( 'First name', 'woocommerce' ),
						'required'     => 'true',
						'class'        => array(
							0 => 'form-row-first',
						),
						'autocomplete' => 'given-name',
						'priority'     => '10',
						'type'         => 'text',
						'id'           => 'billing_first_name',
						'field_type'   => 'billing',
						'placeholder'  => __( 'John', 'woofunnels-aero-checkout' ),

					),
					array(
						'label'        => __( 'Last name', 'woocommerce' ),
						'required'     => 'true',
						'class'        => array(
							0 => 'form-row-last',
						),
						'autocomplete' => 'family-name',
						'priority'     => '20',
						'type'         => 'text',
						'id'           => 'billing_last_name',
						'field_type'   => 'billing',
						'placeholder'  => __( 'Doe', 'woofunnels-aero-checkout' ),
					),
					self::get_single_address_fields( 'shipping' ),
					self::get_single_address_fields(),
					array(
						'label'        => __( 'Phone', 'woocommerce' ),
						'type'         => 'tel',
						'class'        => array( 'form-row-wide' ),
						'id'           => 'billing_phone',
						'field_type'   => 'billing',
						'validate'     => array( 'phone' ),
						'placeholder'  => '999-999-9999',
						'autocomplete' => 'tel',
						'priority'     => 100,
					),

				),
			);

			$advanced_field = self::get_advanced_fields();

			if ( isset( $advanced_field['shipping_calculator'] ) ) {
				$data['fieldsets']['single_step'][] = array(

					'name'        => __( 'Shipping Method', 'woocommerce' ),
					'class'       => '',
					'sub_heading' => '',
					'html_fields' => [ 'shipping_calculator' => true ],
					'fields'      => array(
						$advanced_field['shipping_calculator'],
					),
				);
			}
			$product_field                      = self::get_product_field();
			$data['fieldsets']['single_step'][] = array(
				'name'        => __( 'Your Products', 'woofunnels-aero-checkout' ),
				'class'       => '',
				'sub_heading' => '',
				'html_fields' => [ 'product_switching' => true ],
				'fields'      => array(
					$product_field['product_switching'],
				),
			);
			$data['fieldsets']['single_step'][] = array(
				'name'        => __( 'Order Summary', 'woofunnels-aero-checkout' ),
				'class'       => '',
				'sub_heading' => '',
				'html_fields' => [
					'order_coupon'  => true,
					'order_summary' => true,
				],
				'fields'      => array(
					$advanced_field['order_coupon'],
					$advanced_field['order_summary'],
				),
			);
			$data                               = apply_filters( 'wfacp_default_form_fieldset', $data );
		}

		return $data;
	}


	public static function get_single_address_fields( $type = 'billing' ) {

		$address_field = array(
			'required'   => '1',
			'class'      => [ 'wfacp-col-half' ],
			'cssready'   => [ 'wfacp-col-half' ],
			'id'         => 'address',
			'field_type' => 'billing',
		);

		if ( 'billing' == $type ) {
			$address_field['label'] = __( 'Billing Address', 'woocommerce' );
		} else {
			$address_field['label'] = __( 'Shipping Address', 'woocommerce' );
			unset( $address_field['required'] );
		}

		if ( 'shipping' === $type ) {
			$address_field['id']                                = 'shipping-address';
			$address_field['fields_options']['same_as_billing'] = array(
				'same_as_billing'         => 'true',
				'same_as_billing_label'   => __( 'Use a different shipping address', 'woofunnels-aero-checkout' ),
				'same_as_billing_label_2' => '',
			);
		} else {
			$address_field['fields_options']['same_as_shipping'] = array(
				'same_as_shipping'         => 'true',
				'same_as_shipping_label'   => __( 'Use a different billing address', 'woofunnels-aero-checkout' ),
				'same_as_shipping_label_2' => '',

			);
		}

		$address_field['fields_options']['first_name'] = array(
			'first_name'             => 'false',
			'first_name_label'       => __( 'First name', 'woocommerce' ),
			'first_name_placeholder' => __( 'John', 'woofunnels-aero-checkout' ),
			'hint'                   => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_first_name',
			'required'               => true,
		);
		$address_field['fields_options']['last_name']  = array(
			'last_name'             => 'false',
			'last_name_label'       => __( 'Last name', 'woocommerce' ),
			'last_name_placeholder' => __( 'Doe', 'woofunnels-aero-checkout' ),
			'hint'                  => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_last_name',
			'required'              => true,
		);
		if ( 'shipping' == $type ) {
//			$address_field['fields_options']['first_name']['first_name'] = 'true';
//			$address_field['fields_options']['last_name']['last_name']   = 'true';
		}

		$address_field['fields_options']['company']   = array(
			'company'             => 'false',
			'company_label'       => __( 'Company', 'woocommerce' ),
			'company_placeholder' => '',
			'hint'                => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_company',
			'required'            => false,
		);
		$address_field['fields_options']['address_1'] = array(
			'street_address1'              => 'true',
			'street_address_1_label'       => __( 'Street address', 'woocommerce' ),
			'street_address_1_placeholder' => __( 'House Number and Street Name', 'woocommerce' ),
			'hint'                         => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_address_1',
			'required'                     => true,
		);
		$address_field['fields_options']['address_2'] = array(
			'street_address2'              => 'false',
			'street_address_2_label'       => __( 'Apartment, suite, unit etc', 'woocommerce' ),
			'street_address_2_placeholder' => __( 'Apartment, suite, unit etc. (optional)', 'woocommerce' ),
			'hint'                         => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_address_2',
			'required'                     => false,
		);
		$address_field['fields_options']['city']      = array(
			'address_city'             => 'true',
			'address_city_label'       => __( 'Town / City', 'woocommerce' ),
			'address_city_placeholder' => 'Albany',
			'hint'                     => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_city',
			'required'                 => true,
		);
		$address_field['fields_options']['postcode']  = array(
			'address_postcode'             => 'true',
			'address_postcode_label'       => __( 'Postcode', 'woocommerce' ),
			'address_postcode_placeholder' => 12084,
			'hint'                         => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_postcode',
			'required'                     => true,
		);
		$address_field['fields_options']['country']   = array(
			'address_country'             => 'true',
			'address_country_label'       => __( 'Country', 'woocommerce' ),
			'address_country_placeholder' => 'United States',
			'hint'                        => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_country',
			'required'                    => true,
		);
		$address_field['fields_options']['state']     = array(
			'address_state'             => 'true',
			'address_state_label'       => __( 'State', 'woocommerce' ),
			'address_state_placeholder' => 'New York',
			'hint'                      => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_state',
			'required'                  => false,
		);

		$address_field['fields_options'] = apply_filters( 'wfacp_' . $type . '_address_options', $address_field['fields_options'] );

		return $address_field;
	}

	public static function get_default_steps_fields( $active_steps = false ) {

		return array(
			'single_step' => array(
				'name'          => __( 'Step 1', 'woofunnels-aero-checkout' ),
				'slug'          => 'single_step',
				'friendly_name' => __( 'Single Step Checkout', 'woofunnels-aero-checkout' ),
				'active'        => 'yes',
			),
			'two_step'    => array(
				'name'          => __( 'Step 2', 'woofunnels-aero-checkout' ),
				'slug'          => 'two_step',
				'friendly_name' => __( 'Two Step Checkout', 'woofunnels-aero-checkout' ),
				'active'        => true === $active_steps ? 'yes' : 'no',
			),
			'third_step'  => array(
				'name'          => __( 'Step 3', 'woofunnels-aero-checkout' ),
				'slug'          => 'third_step',
				'friendly_name' => __( 'Three Step Checkout', 'woofunnels-aero-checkout' ),
				'active'        => true === $active_steps ? 'yes' : 'no',
			),
		);
	}

	public static function get_advanced_fields() {
		$field = array(
			'order_comments' => [
				'type'  => 'textarea',
				'class' => [ 'notes' ],
				'id'    => 'order_comments',
				'label' => __( 'Order notes', 'woocommerce' ),
			],
		);


		$label = __( 'Shipping method', 'woocommerce' );


		if ( wc_shipping_enabled() ) {
			$field['shipping_calculator'] = [
				'type'       => 'wfacp_html',
				'field_type' => 'advanced',
				'id'         => 'shipping_calculator',
				'default'    => self::default_shipping_placeholder_text(),
				'class'      => [ 'wfacp_shipping_calculator' ],
				'label'      => '',
				'data_label' => $label,
			];
		}

		$field['order_summary'] = [
			'type'       => 'wfacp_html',
			'field_type' => 'advanced',
			'class'      => [ 'wfacp_order_summary' ],
			'id'         => 'order_summary',
			'label'      => __( 'Order Summary', 'woocommerce' ),
		];

		$field['order_total'] = [
			'type'       => 'wfacp_html',
			'field_type' => 'advanced',
			'class'      => [ 'wfacp_order_total' ],
			'default'    => false,
			'id'         => 'order_summary',
			'label'      => __( 'Order Total', 'woofunnels-aero-checkout' ),
		];

		$success_message       = sprintf( __( 'Congrats! Coupon code %s %s applied successfully.', 'woofunnels-aero-checkout' ), '{{coupon_code}}', '({{coupon_value}})' );
		$field['order_coupon'] = [
			'type'                           => 'wfacp_html',
			'field_type'                     => 'advanced',
			'class'                          => [ 'wfacp_order_coupon' ],
			'id'                             => 'order_coupon',
			'coupon_style'                   => 'true',
			'coupon_success_message_heading' => $success_message,
			'coupon_remove_message_heading'  => __( 'Coupon code removed successfully.', 'woocommerce' ),
			'label'                          => __( 'Coupon', 'woocommerce' ),
		];

		return apply_filters( 'wfacp_advanced_fields', $field );
	}

	public static function get_product_field() {
		$output = [];

		$output['product_switching'] = [
			'type'                           => 'product',
			'class'                          => [],
			'cssready'                       => [ 'wfacp-col-full' ],
			'id'                             => 'product_switching',
			'label'                          => __( 'Products', 'woocommerce' ),
			'is_hide_additional_information' => false,
			'additional_information_title'   => self::get_default_additional_information_title(),
			'hide_quantity_switcher'         => false,
			'hide_you_save'                  => true,
			'default'                        => self::get_default_you_save_text(),
			'field_type'                     => 'product',
			'placeholder'                    => '',
		];

		$output = apply_filters( 'wfacp_products_field', $output );

		return $output;
	}


	/*************** Page layout section End ***************/
	/**
	 * Get default global setting schema
	 * @return array
	 */
	/**
	 * Return Shortcodes of embed form
	 */
	public static function get_short_codes() {
		$id = WFACP_Common::get_id();

		$shortcode = "[wfacp_forms id='{$id}']";
		$lightbox  = "[wfacp_forms id='{$id}' lightbox='yes']";

		return [ 'shortcode' => $shortcode, 'lightbox_shortcode' => $lightbox ];
	}

	public static function get_shortcode_supported_template() {
		return [
			'selected'        => 'embed_forms_1',
			'selected_type'   => 'embed_forms',
			'template_active' => 'yes'
		];
	}

	/**
	 * Check Current Aero page is created by old version
	 * @return bool
	 */
	public static function page_is_old_version( $version = '1.9.3' ) {
		$current_version = WFACP_Common::get_checkout_page_version();
		if ( version_compare( $current_version, $version, '>' ) ) {
			return false;
		}

		return true;
	}

	public static function last_item_delete_message( $resp, $item_key = '' ) {

		if ( apply_filters( 'wfacp_force_deletion_last_item', false ) && '' !== $item_key ) {
			//add_action( 'woocommerce_cart_item_removed', 'WFACP_Common::remove_item_deleted_items', 10, 2 );
			WC()->cart->remove_cart_item( $item_key );
			//remove_action( 'woocommerce_cart_item_removed', 'WFACP_Common::remove_item_deleted_items', 10 );
			$resp['force_redirect'] = apply_filters( 'wfacp_force_redirect_url', wc_get_cart_url() );

			return $resp;
		}

		$last_item_delete_message = __( 'At least one item should be available in your cart to checkout.', 'woofunnels-aero-checkout' );

		if ( apply_filters( 'wfacp_enable_last_item_delete', false ) ) {

			$are_you_sure             = __( 'Click to delete this last item from your cart.', 'woofunnels-aero-checkout' );
			$are_you_sure             = " <a href='' class='wfacp_force_last_delete'>" . $are_you_sure . "</a>";
			$last_item_delete_message .= $are_you_sure;
		}

		$resp['error'] = apply_filters( 'wfacp_last_item_message', $last_item_delete_message );

		return $resp;
	}

	public static function get_address_field_order( $id ) {
		$id       = absint( $id );
		$data     = get_post_meta( $id, '_wfacp_save_address_order', true );
		$defaults = [ 'address' => [], 'shipping-address' => [], 'display_type_address' => 'checkbox', 'display_type_shipping-address' => 'checkbox' ];
		if ( empty( $data ) || ! is_array( $data ) ) {
			return $defaults;
		}

		foreach ( $defaults as $key => $val ) {
			if ( ! isset( $data[ $key ] ) ) {
				$data[ $key ] = $val;
			}
		}


		return $data;

	}

	public static function set_multistep_form_layout( $aero_id, $force = false ) {
		$data = get_post_meta( $aero_id, '_wfacp_page_layout', true );
		if ( ! empty( $data ) && false == $force ) {
			return;
		}
		$data                                = [];
		$layout_data                         = WFACP_Common::get_page_layout_multistep();
		$prepare_data                        = WFACP_Common::prepare_fieldset( $layout_data );
		$data['current_step']                = $layout_data['current_step'];
		$data['have_billing_address']        = wc_string_to_bool( $layout_data['have_billing_address'] );
		$data['have_shipping_address']       = wc_string_to_bool( $layout_data['have_shipping_address'] );
		$data['have_billing_address_index']  = $layout_data['have_billing_address_index'];
		$data['have_shipping_address_index'] = $layout_data['have_shipping_address_index'];
		$data['enabled_product_switching']   = isset( $layout_data['enabled_product_switching'] ) ? $layout_data['enabled_product_switching'] : 'no';
		$data['have_coupon_field']           = $layout_data['have_coupon_field'];
		$data['fieldsets']                   = $prepare_data['fieldsets'];
		update_post_meta( $aero_id, '_wfacp_page_layout', $layout_data );
		update_post_meta( $aero_id, '_wfacp_fieldsets_data', $data );
		update_post_meta( $aero_id, '_wfacp_checkout_fields', $prepare_data['checkout_fields'] );

	}

	public static function get_template_container_atts( $template = '' ) {


		return $template;
	}

	public static function do_not_show_session_expired_message( $status ) {
		if ( isset( $_REQUEST['wfacp_id'] ) && wp_doing_ajax() ) {
			$status = false;
		}

		return $status;
	}


	public static function enable_cart_deletion() {
		$switcher_settings  = WFACP_Common::get_product_switcher_data( WFACP_Common::get_id() );
		$enable_delete_item = wc_string_to_bool( $switcher_settings['settings']['enable_delete_item'] );

		return apply_filters( 'wfacp_enable_cart_deletion', $enable_delete_item );
	}

	public static function show_cart_empty_message() {

		echo '<div class="wfacp_cart_empty">';
		do_action( 'wfacp_cart_empty_before_message' );
		$page_settings = WFACP_Common::get_page_settings( WFACP_Common::get_id() );
		echo apply_filters( 'wfacp_cart_empty_message', __( 'Your cart is currently empty.', 'woocommerce' ) );
		do_action( 'wfacp_cart_empty_after_message' );
		echo '</div>';

	}

	public static function cart_is_sustained() {
		if ( is_null( WC()->session ) ) {
			return false;
		}

		return WC()->session->get( 'wfacp_checkout_processed_' . WFACP_Common::get_Id(), false );

	}

	public static function delete_page_layout( $post_id ) {
		delete_post_meta( $post_id, '_wfacp_page_layout' );
		delete_post_meta( $post_id, '_wfacp_fieldsets_data' );
		delete_post_meta( $post_id, '_wfacp_checkout_fields' );
		delete_post_meta( $post_id, '_wfacp_save_address_order' );

		$wfacp_transient_obj = WooFunnels_Transient::get_instance();
		$meta_key            = 'wfacp_post_meta' . absint( $post_id );
		$wfacp_transient_obj->delete_transient( $meta_key, WFACP_SLUG );
	}

	public static function get_template_filter( $all_pro = false ) {

		$options = [
			'1' => __( 'One Step', 'woofunnels-aero-checkout' ),
			'2' => __( 'Two Step', 'woofunnels-aero-checkout' ),
			'3' => __( 'Three Step', 'woofunnels-aero-checkout' ),
		];

		return $options;
	}

	public static function get_cart_undo_message() {
		$cart_contents = WC()->cart->removed_cart_contents;
		if ( empty( WC()->cart->removed_cart_contents ) ) {
			return;
		}
		wc_clear_notices();
		$out_items = [];
		foreach ( $cart_contents as $cart_item_key => $cart_item ) {
			$item_data = wc_get_product( $cart_item['product_id'] );
			if ( ! $item_data instanceof WC_Product ) {
				continue;
			}
			if ( isset( $cart_item['_wfob_options'] ) ) {
				continue;
			}
			if ( isset( $cart_item['xlwcfg_gift_id'] ) ) {
				continue;
			}
			if ( true === apply_filters( 'wfacp_show_undo_message_for_item', false, $cart_item ) ) {
				continue;
			}

			$item_key   = $cart_item_key;
			$item_class = 'wfacp_restore_cart_item';
			$item_icon  = "&nbsp;" . __( 'Undo?', 'woocommerce' );
			if ( isset( $cart_item['_wfacp_product'] ) && ! WFACP_Core()->public->is_checkout_override() ) {
				$item_key = $cart_item['_wfacp_product_key'];

				if ( isset( $out_items[ $item_key ] ) && $out_items[ $item_key ] > 0 ) {
					continue;
				}
				$out_items[ $item_key ] = 1;
				$wfacp_data             = $cart_item['_wfacp_options'];
				$item_title             = $wfacp_data['title'];
				$status                 = WFACP_Common::get_cart_item_key( $item_key );
				if ( ! is_null( $status ) ) {
					continue;
				}


			} else {
				$item_title = $item_data->get_name();
			}
			if ( $item_data && $item_data->is_in_stock() && $item_data->has_enough_stock( $cart_item['quantity'] ) ) {
				/* Translators: %s Product title. */
				$removed_notice = sprintf( __( '%s removed.', 'woocommerce' ), $item_title );
				$removed_notice .= sprintf( '<a href="javascript:void(0)" class="%s" data-cart_key="%s" data-item_key="%s">%s</a>', $item_class, $cart_item_key, $item_key, $item_icon );
			} else {
				/* Translators: %s Product title. */
				$removed_notice = sprintf( __( '%s removed.', 'woocommerce' ), $item_title );
			}
			echo "<div class='wfacp_product_restore_wrap'>" . $removed_notice . '</div>';
		}
	}

	public static function remove_item_remove_cart_item( $item_key, $cart_key = '' ) {
		$removed_cart_items = WC()->cart->removed_cart_contents;
		if ( empty( $removed_cart_items ) ) {
			return;
		}
		foreach ( $removed_cart_items as $key => $item ) {
			if ( ( isset( $item['_wfacp_product'] ) && $item['_wfacp_product_key'] == $item_key ) || ( $cart_key == $key ) ) {
				unset( $removed_cart_items[ $key ] );
			}
		}
		if ( count( $removed_cart_items ) > 0 ) {
			WC()->cart->set_removed_cart_contents( $removed_cart_items );
		}
	}


	/**
	 * get global price data after tax calculation based
	 *     *
	 *
	 * @param $cart_item
	 * @param int $qty
	 *
	 * @return float
	 */
	public static function get_subscription_cart_item_price( $cart_item, $qty = 1 ) {
		$price = 0;
		if ( ! empty( $cart_item ) ) {
			$display_type = get_option( 'woocommerce_tax_display_cart' );
			if ( 'incl' == $display_type ) {
				$price = round( $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'], wc_get_price_decimals() );
			} else {
				$price = round( $cart_item['line_subtotal'], wc_get_price_decimals() );
			}
		}

		return $price;
	}

	static function get_price_sign_up_fee( $product, $type = '' ) {

		if ( 'inc_tax' == $type ) {
			return wcs_get_price_including_tax( $product, array( 'price' => WC_Subscriptions_Product::get_sign_up_fee( $product ) ) );
		}

		return wcs_get_price_excluding_tax( $product, array( 'price' => WC_Subscriptions_Product::get_sign_up_fee( $product ) ) );
	}


	/**
	 * migrate options for general settings tab
	 */
	public static function wfacp_update_general_setting_fields() {

		$is_migrated = get_option( 'wfacp_general_setting_migrated', 'no' );
		if ( 'yes' == $is_migrated ) {
			return;
		}

		$db_setting = array(
			'wfacp_checkout_pixel_id'                          => 'fb_pixel_key',
			'wfacp_checkout_google_ua_id'                      => 'ga_key',
			'rewrite_slug'                                     => 'checkout_page_base',
			'wfacp_checkout_pixel_add_to_cart_event'           => 'pixel_add_to_cart_event',
			'wfacp_checkout_pixel_page_view_event'             => 'pixel_is_page_view',
			'wfacp_checkout_pixel_initiate_checkout_event'     => 'pixel_initiate_checkout_event',
			'wfacp_checkout_pixel_add_payment_info_event'      => 'pixel_add_payment_info_event',
			'wfacp_checkout_pixel_variable_as_simple'          => 'pixel_variable_as_simple',
			'wfacp_checkout_pixel_content_id_type'             => 'pixel_content_id_type',
			'wfacp_checkout_pixel_content_id_prefix'           => 'pixel_content_id_prefix',
			'wfacp_checkout_pixel_content_id_suffix'           => 'pixel_content_id_suffix',
			'wfacp_checkout_google_ua_add_to_cart_event'       => 'google_ua_add_to_cart_event',
			'wfacp_checkout_google_ua_initiate_checkout_event' => 'google_ua_initiate_checkout_event',
			'wfacp_checkout_google_ua_add_payment_info_event'  => 'google_ua_add_payment_info_event',
			'wfacp_checkout_google_ua_variable_as_simple'      => 'google_ua_variable_as_simple',
			'wfacp_checkout_google_ua_content_id_type'         => 'google_ua_content_id_type',
			'wfacp_checkout_google_ua_content_id_prefix'       => 'google_ua_content_id_prefix',
			'wfacp_checkout_google_ua_content_id_suffix'       => 'google_ua_content_id_suffix',
		);

		$db_setting = apply_filters( 'wfacp_migrate_general_setting_field', $db_setting );

		$global_op  = get_option( '_wfacp_global_settings', [] );
		$general_op = get_option( 'bwf_gen_config', [] );

		foreach ( $db_setting as $old => $new ) {
			if ( isset( $global_op[ $old ] ) && ( ! isset( $general_op[ $new ] ) || empty( $general_op[ $new ] ) ) ) {
				$general_op[ $new ] = $global_op[ $old ];

			}
		}

		update_option( 'bwf_gen_config', $general_op, true );
		update_option( 'wfacp_general_setting_migrated', 'yes', 'yes' );
	}

}

