<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class wfacp_AJAX_Controller
 * Handles All the request came from front end or the backend
 */
abstract class WFACP_AJAX_Controller {
	public static function init() {
		/**
		 * Backend AJAX actions
		 */
		if ( is_admin() ) {
			self::handle_admin_ajax();
		}
		self::handle_public_ajax();

	}


	public static function handle_admin_ajax() {
		add_action( 'wp_ajax_wfacp_save_global_settings', [ __CLASS__, 'save_global_settings' ] );
		add_action( 'wp_ajax_wfacp_preview_details', [ __CLASS__, 'preview_details' ] );
		add_action( 'wp_ajax_wfacp_add_checkout_page', [ __CLASS__, 'add_checkout_page' ] );
		add_action( 'wp_ajax_wfacp_update_page_status', [ __CLASS__, 'update_page_status' ] );
		add_action( 'wp_ajax_wfacp_add_product', [ __CLASS__, 'add_product' ] );
		add_action( 'wp_ajax_wfacp_remove_product', [ __CLASS__, 'remove_product' ] );
		add_action( 'wp_ajax_wfacp_product_search', [ __CLASS__, 'product_search' ] );
		add_action( 'wp_ajax_wfacp_save_products', [ __CLASS__, 'save_products' ] );
		add_action( 'wp_ajax_wfacp_save_layout', [ __CLASS__, 'save_layout' ] );
		add_action( 'wp_ajax_wfacp_add_field', [ __CLASS__, 'add_field' ] );
		add_action( 'wp_ajax_wfacp_delete_custom_field', [ __CLASS__, 'delete_custom_field' ] );
		add_action( 'wp_ajax_wfacp_update_custom_field', [ __CLASS__, 'update_custom_field' ] );
		add_action( 'wp_ajax_wfacp_save_design', [ __CLASS__, 'save_design' ] );
		add_action( 'wp_ajax_wfacp_remove_design', [ __CLASS__, 'remove_design' ] );
		add_action( 'wp_ajax_wfacp_save_settings', [ __CLASS__, 'save_settings' ] );
		add_action( 'wp_ajax_wfacp_make_wpml_duplicate', [ __CLASS__, 'make_wpml_duplicate' ] );
		add_action( 'wp_ajax_wfacp_hide_notification', [ __CLASS__, 'hide_notification' ] );
		add_action( 'wp_ajax_wfacp_import_template', [ __CLASS__, 'import_template' ] );
		add_action( 'wp_ajax_wfacp_activate_plugin', [ __CLASS__, 'activate_plugin' ] );

		add_action( 'wp_ajax_notice_dismise_link', array( __CLASS__, 'notice_dismise_link' ) );

		add_action( 'wfacp_template_removed', [ __CLASS__, 'ps_setting' ] );
	}

	public static function ps_setting( $pageiD ) {

		$page_product_setting = get_post_meta( $pageiD, '_wfacp_product_switcher_setting', true );

		if ( ! is_array( $page_product_setting ) || count( $page_product_setting ) == 0 || ! isset( $page_product_setting['settings'] ) ) {
			return;
		}
		update_post_meta( $pageiD, '_wfacp_product_switcher_setting', '' );
	}

	public static function handle_public_ajax() {
		$endpoints = self::get_available_public_endpoints();
		foreach ( $endpoints as $action => $function ) {
			if ( method_exists( __CLASS__, $function ) ) {
				add_action( 'wc_ajax_' . $action, [ __CLASS__, $function ] );
			} else {
				do_action( 'wfacp_wc_ajax_' . $action, $function );
			}
		}
	}

	public static function get_available_public_endpoints() {
		$endpoints = [
			'wfacp_addon_product'             => 'addon_product',
			'wfacp_remove_addon_product'      => 'remove_addon_product',
			'wfacp_switch_product_addon'      => 'switch_product_addon',
			'wfacp_update_product_qty'        => 'update_product_qty',
			'wfacp_quick_view_ajax'           => 'wf_quick_view_ajax',
			'wfacp_update_cart_item_quantity' => 'update_cart_item_quantity',
			'wfacp_update_cart_multiple_page' => 'update_cart_multiple_page',
			'wfacp_apply_coupon'              => 'apply_coupon',
			'wfacp_remove_cart_item'          => 'remove_cart_item',
			'wfacp_undo_cart_item'            => 'undo_cart_item',
			'wfacp_refresh'                   => 'refresh',
		];

		return apply_filters( 'wfacp_public_endpoints', $endpoints );
	}

	public static function get_public_endpoints() {
		$endpoints        = [];
		$public_endpoints = self::get_available_public_endpoints();
		if ( count( $public_endpoints ) > 0 ) {
			foreach ( $public_endpoints as $key => $function ) {
				$endpoints[ $key ] = WC_AJAX::get_endpoint( $key );
			}
		}

		return $endpoints;
	}

	/**
	 * Create new checkout page OR Update checkout page
	 */
	public static function add_checkout_page() {
		self::check_nonce();
		$resp = array(
			'msg'    => __( 'Checkout Page not found', 'woofunnels-aero-checkout' ),
			'status' => false,
		);
		if ( isset( $_POST['wfacp_name'] ) && $_POST['wfacp_name'] != '' ) {
			$post                = array();
			$post['post_title']  = $_POST['wfacp_name'];
			$post['post_type']   = WFACP_Common::get_post_type_slug();
			$post['post_status'] = 'publish';
			$post['post_name']   = isset( $_POST['post_name'] ) ? $_POST['post_name'] : $post['post_title'];
			$post_description    = isset( $_POST['post_content'] ) ? $_POST['post_content'] : $post['post_content'];
			if ( ! empty( $post ) ) {

				if ( isset( $_POST['wfacp_id'] ) && $_POST['wfacp_id'] > 0 ) {
					$wfacp_id = absint( $_POST['wfacp_id'] );
					$status   = wp_update_post( [
						'ID'         => $wfacp_id,
						'post_title' => $post['post_title'],
						'post_name'  => $post['post_name'],
					] );
					if ( ! is_wp_error( $status ) ) {

						update_post_meta( $wfacp_id, '_post_description', $post_description );
						$resp['status']       = true;
						$resp['new_url']      = get_the_permalink( $wfacp_id );
						$resp['redirect_url'] = '#';
						$resp['msg']          = __( 'Checkout Page Successfully Update', 'woofunnels-aero-checkout' );
					}
					WFACP_Common::save_publish_checkout_pages_in_transient();
					self::send_resp( $resp );
				}
				$wfacp_id = wp_insert_post( $post );
				if ( $wfacp_id !== 0 && ! is_wp_error( $wfacp_id ) ) {

					$resp['status']       = true;
					$resp['redirect_url'] = add_query_arg( array(
						'page'     => 'wfacp',
						'section'  => 'design',
						'wfacp_id' => $wfacp_id,
					), admin_url( 'admin.php' ) );
					$resp['msg']          = __( 'Checkout Page Successfully Created', 'woofunnels-aero-checkout' );
					update_post_meta( $wfacp_id, '_wfacp_version', WFACP_VERSION );
					update_post_meta( $wfacp_id, '_post_description', $post_description );
					update_post_meta( $wfacp_id, '_wp_page_template', 'default' );

					WFACP_Common::save_publish_checkout_pages_in_transient();
				}
			}
		}
		self::send_resp( $resp );
	}

	/*
	 * Send Response back to checkout page builder
	 * With nonce security keys
	 * also delete transient of particular checkout page it page is found in request
	 */

	public static function check_nonce() {
		$rsp = [
			'status' => 'false',
			'msg'    => 'Invalid Call',
		];
		if ( isset( $_POST['post_data'] ) ) {
			$post_data = [];
			parse_str( $_POST['post_data'], $post_data );
			if ( ! empty( $post_data ) ) {
				WFACP_Common::$post_data = $post_data;
			}
		}
		if ( ! isset( $_REQUEST['wfacp_nonce'] ) || ! wp_verify_nonce( $_REQUEST['wfacp_nonce'], 'wfacp_secure_key' ) ) {
			wp_send_json( $rsp );
		}
	}

	public static function send_resp( $data = array() ) {
		if ( ! is_array( $data ) ) {
			$data = [];
		}
		$data['nonce'] = wp_create_nonce( 'wfacp_secure_key' );
		if ( isset( $_REQUEST['wfacp_id'] ) && $_REQUEST['wfacp_id'] > 0 ) {
			$wfacp_transient_obj = WooFunnels_Transient::get_instance();
			$meta_key            = 'wfacp_post_meta' . absint( $_REQUEST['wfacp_id'] );

			$wfacp_transient_obj->delete_transient( $meta_key, WFACP_SLUG );
		}
		if ( function_exists( 'WC' ) && WC()->cart instanceof WC_Cart && ! is_null( WC()->cart ) ) {
			$data['cart_total'] = WC()->cart->get_total( 'edit' );

			if ( class_exists( 'WC_Subscriptions_Cart' ) ) {
				$data['cart_contains_subscription'] = WC_Subscriptions_Cart::cart_contains_subscription();
			}
			$data['cart_is_virtual'] = WFACP_Common::is_cart_is_virtual();
		}
		wp_send_json( $data );
	}

	public static function product_search( $term = false, $return = false ) {
		self::check_nonce();
		$term = wc_clean( empty( $term ) ? stripslashes( $_POST['term'] ) : $term );
		if ( empty( $term ) ) {
			wp_die();
		}
		$variations = true;
		$ids        = WFACP_Common::search_products( $term, $variations );

		/**
		 * Products types that are allowed in the offers
		 */
		$allowed_types   = apply_filters( 'wfacp_offer_product_types', array(
			'simple',
			'variable',
			'course',
			'variation',
			'subscription',
			'variable-subscription',
			'subscription_variation',
			'virtual_subscription',
			'bundle',
			'yith_bundle',
			'woosb',
			'braintree-subscription',
			'braintree-variable-subscription',
		) );
		$product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_editable' );

		$product_objects = array_filter( $product_objects, function ( $arr ) use ( $allowed_types ) {
			return $arr && is_a( $arr, 'WC_Product' ) && in_array( $arr->get_type(), $allowed_types );
		} );

		$products = array();
		/**
		 * @var $product_object WC_Product;
		 */
		foreach ( $product_objects as $product_object ) {
			$products[] = array(
				'id'      => $product_object->get_id(),
				'product' => rawurldecode( WFACP_Common::get_formatted_product_name( $product_object ) ),
			);
		}
		wp_send_json( apply_filters( 'wfacp_woocommerce_json_search_found_products', $products ) );
	}

	/**
	 * Add product to product list of Checkout page
	 */
	public static function add_product() {
		self::check_nonce();
		$resp = array(
			'msg'      => '',
			'status'   => false,
			'products' => [],
		);
		if ( isset( $_POST['wfacp_id'] ) && count( $_POST['products'] ) > 0 ) {
			$wfacp_id = absint( $_POST['wfacp_id'] );
			$products = $_POST['products'];

			$existing_product = WFACP_Common::get_page_product( $wfacp_id );

			foreach ( $products as $pid ) {
				$unique_id = uniqid( 'wfacp_' );
				$product   = wc_get_product( $pid );
				if ( $product instanceof WC_Product ) {
					$product_type                    = $product->get_type();
					$image_id                        = $product->get_image_id();
					$default                         = WFACP_Common::get_default_product_config();
					$default['type']                 = $product_type;
					$default['id']                   = $product->get_id();
					$default['parent_product_id']    = $product->get_parent_id();
					$default['title']                = $product->get_title();
					$default['stock']                = $product->is_in_stock();
					$default['is_sold_individually'] = $product->is_sold_individually();

					$product_image_url = '';
					$images            = wp_get_attachment_image_src( $image_id );
					if ( is_array( $images ) && count( $images ) > 0 ) {
						$product_image_url = wp_get_attachment_image_src( $image_id )[0];
					}
					$default['image'] = apply_filters( 'wfacp_product_image', $product_image_url, $product );

					if ( $default['image'] == '' ) {
						$default['image'] = WFACP_PLUGIN_URL . '/admin/assets/img/product_default_icon.jpg';
					}

					if ( in_array( $product_type, WFACP_Common::get_variable_product_type() ) ) {
						$default['variable'] = 'yes';
						$default['price']    = $product->get_price_html();
					} else {
						if ( in_array( $product_type, WFACP_Common::get_variation_product_type() ) ) {
							$default['title'] = $product->get_name();
						}
						$row_data                 = $product->get_data();
						$sale_price               = $row_data['sale_price'];
						$default['price']         = wc_price( $row_data['price'] );
						$default['regular_price'] = wc_price( $row_data['regular_price'] );
						if ( '' != $sale_price ) {
							$default['sale_price'] = wc_price( $sale_price );
						}
					}

					$resp['products'][ $unique_id ] = $default;
					$default                        = WFACP_Common::remove_product_keys( $default );
					$existing_product[ $unique_id ] = $default;
				}
			}
			WFACP_Common::update_page_product( $wfacp_id, $existing_product );
			if ( count( $resp['products'] ) > 0 ) {
				$resp['status'] = true;
			}
		}
		self::send_resp( $resp );
	}

	/**
	 * Remove a product from product list of checkout page
	 */
	public static function remove_product() {
		self::check_nonce();
		$resp = array(
			'status' => false,
			'msg'    => '',
		);
		if ( isset( $_POST['wfacp_id'] ) && $_POST['wfacp_id'] > 0 && isset( $_POST['product_key'] ) && $_POST['product_key'] != '' ) {
			$wfacp_id         = absint( $_POST['wfacp_id'] );
			$product_key      = trim( $_POST['product_key'] );
			$existing_product = WFACP_Common::get_page_product( $wfacp_id );
			if ( isset( $existing_product[ $product_key ] ) ) {
				unset( $existing_product[ $product_key ] );
				WFACP_Common::update_page_product( $wfacp_id, $existing_product );
				$resp['status'] = true;
				$resp['msg']    = __( 'Product removed from checkout page' );
			}
		}
		self::send_resp( $resp );
	}

	/**
	 * Save product with product settings to checkout page
	 * Save checkout page
	 */
	public static function save_products() {
		self::check_nonce();
		$resp = array(
			'msg'      => __( 'Changes saved', 'woofunnels-aero-checkout' ),
			'status'   => false,
			'products' => [],
		);
		if ( isset( $_POST['products'] ) && count( $_POST['products'] ) > 0 ) {
			$products = $_POST['products'];
			$wfacp_id = $_POST['wfacp_id'];
			$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : [];
			foreach ( $products as $key => $val ) {
				if ( isset( $products[ $key ]['variable'] ) ) {

					$pro                = WFACP_Common::wc_get_product( $products[ $key ]['id'], $key );
					$is_found_variation = WFACP_Common::get_default_variation( $pro );

					if ( count( $is_found_variation ) > 0 ) {
						$products[ $key ]['default_variation']      = $is_found_variation['variation_id'];
						$products[ $key ]['default_variation_attr'] = $is_found_variation['attributes'];
					}
				}
				$products[ $key ] = WFACP_Common::remove_product_keys( $products[ $key ] );
			}

			$old_settings = WFACP_Common::get_page_product_settings( $wfacp_id );
			if ( $old_settings['add_to_cart_setting'] !== $_POST['settings']['add_to_cart_setting'] ) {
				//unset default products
				$s = get_post_meta( $wfacp_id, '_wfacp_product_switcher_setting', true );
				if ( ! empty( $s ) ) {
					$s['default_products'] = [];
					update_post_meta( $wfacp_id, '_wfacp_product_switcher_setting', $s );
				}
			}
			WFACP_Common::update_page_product( $wfacp_id, $products );
			WFACP_Common::update_page_product_setting( $wfacp_id, $settings );
			$resp['status'] = true;
		}
		self::send_resp( $resp );
	}

	public static function is_wfacp_front_ajax() {
		if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX && null !== filter_input( INPUT_POST, 'action' ) && false !== strpos( filter_input( INPUT_POST, 'action' ), 'wfacp_front' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Save form fields of checkout page
	 */
	public static function save_layout() {
		self::check_nonce();
		$resp = array(
			'msg'      => '',
			'status'   => false,
			'products' => [],
		);
		if ( isset( $_POST['wfacp_id'] ) ) {
			$wfacp_id = $_POST['wfacp_id'];
			WFACP_Common::set_id( $wfacp_id );
			WFACP_Common::update_page_layout( $wfacp_id, $_POST );
			$resp['status'] = true;
			$resp['msg']    = __( 'Changes saved', 'woofunnels-aero-checkout' );
		}
		self::send_resp( $resp );
	}

	/**
	 * Add custom field to current form
	 */
	public static function add_field() {
		self::check_nonce();
		$resp = array(
			'msg'      => '',
			'status'   => false,
			'products' => [],
		);
		if ( isset( $_POST['wfacp_id'] ) && $_POST['wfacp_id'] > 0 ) {
			$wfacp_id                      = $_POST['wfacp_id'];
			$name                          = trim( $_POST['fields']['name'] );
			$name                          = sanitize_title( $name );
			$label                         = trim( $_POST['fields']['label'] );
			$placeholder                   = trim( $_POST['fields']['placeholder'] );
			$cssready                      = $_POST['fields']['cssready'] != '' ? explode( ',', trim( $_POST['fields']['cssready'] ) ) : [];
			$field_type                    = trim( $_POST['fields']['field_type'] );
			$section_type                  = trim( $_POST['fields']['section_type'] );
			$show_custom_field_at_thankyou = trim( $_POST['fields']['show_custom_field_at_thankyou'] );
			$show_custom_field_at_email    = trim( $_POST['fields']['show_custom_field_at_email'] );
			$default                       = trim( $_POST['fields']['default'] );
			$options                       = $_POST['fields']['options'] != '' ? ( explode( '|', trim( $_POST['fields']['options'] ) ) ) : [];

			$new_sanitize_option = [];
			if ( is_array( $options ) && count( $options ) > 0 ) {
				foreach ( $options as $key => $option ) {
					$key                         = sanitize_title( trim( $option ) );
					$new_sanitize_option[ $key ] = trim( $option );
				}
			}

			$required = trim( $_POST['fields']['required'] );
			$data     = [
				'label'                         => $label,
				'data_label'                    => $label,
				'placeholder'                   => $placeholder,
				'cssready'                      => $cssready,
				'type'                          => $field_type,
				'required'                      => $required,
				'options'                       => $new_sanitize_option,
				'default'                       => $default,
				'show_custom_field_at_thankyou' => $show_custom_field_at_thankyou,
				'show_custom_field_at_email'    => $show_custom_field_at_email,
				'is_wfacp_field'                => true,
			];
			if ( 'multiselect' == $field_type ) {
				$data['multiselect_maximum']       = trim( $_POST['fields']['multiselect_maximum'] );
				$data['multiselect_maximum_error'] = trim( $_POST['fields']['multiselect_maximum_error'] );
			}
			if ( 'email' == $field_type ) {
				$data['validate'][] = 'email';
			}

			$custom_fields                           = WFACP_Common::get_page_custom_fields( $wfacp_id );
			$custom_fields[ $section_type ][ $name ] = $data;
			WFACP_Common::update_page_custom_fields( $wfacp_id, $custom_fields );
			$data['unique_id']  = $name;
			$data['field_type'] = $section_type;
			$resp['status']     = true;
			$resp['data']       = $data;
			$resp['msg']        = __( 'Field Added Saved', 'woofunnels-aero-checkout' );
		}
		self::send_resp( $resp );
	}

	/**
	 * Delete custom field from form of checkout page
	 */
	public static function delete_custom_field() {
		self::check_nonce();
		$resp = array(
			'msg'    => '',
			'status' => false,
		);
		if ( isset( $_POST['wfacp_id'] ) && $_POST['wfacp_id'] > 0 ) {
			$wfacp_id     = $_POST['wfacp_id'];
			$section_type = $_POST['section'];
			$index        = $_POST['index'];
			if ( '' == $index ) {
				self::send_resp( $resp );
			}
			$custom_fields = WFACP_Common::get_page_custom_fields( $wfacp_id );
			if ( isset( $custom_fields[ $section_type ] ) && isset( $custom_fields[ $section_type ][ $index ] ) ) {

				unset( $custom_fields[ $section_type ][ $index ] );
				WFACP_Common::update_page_custom_fields( $wfacp_id, $custom_fields );
				$resp['status'] = true;
				$resp['msg']    = __( 'Field Deleted', 'woofunnels-aero-checkout' );
			}
		}
		self::send_resp( $resp );
	}

	/**
	 * Update custom field of checkout form
	 */
	public static function update_custom_field() {
		self::check_nonce();
		$resp = array(
			'msg'    => '',
			'status' => false,
		);
		if ( isset( $_POST['wfacp_id'] ) && $_POST['wfacp_id'] > 0 ) {
			$wfacp_id     = $_POST['wfacp_id'];
			$field        = $_POST['field'];
			$section_type = trim( $_POST['section_type'] );
			$index        = $field['id'];
			unset( $field['label'] );
			unset( $field['placeholder'] );
			$custom_fields = WFACP_Common::get_page_custom_fields( $wfacp_id );
			if ( isset( $custom_fields[ $section_type ] ) && isset( $custom_fields[ $section_type ][ $index ] ) ) {
				$find_field                               = $custom_fields[ $section_type ][ $index ];
				$custom_fields[ $section_type ][ $index ] = wp_parse_args( $field, $find_field );
				$options                                  = ( isset( $_POST['field']['options'] ) && count( $_POST['field']['options'] ) > 0 ) ? $_POST['field']['options'] : [];
				if ( is_array( $options ) && count( $options ) > 0 ) {
					foreach ( $options as $key => $option ) {
						unset( $options[ $key ] );

						$key             = sanitize_title( trim( $key ) );
						$options[ $key ] = trim( $option );
					}
					$custom_fields[ $section_type ][ $index ]['options'] = $options;
				}

				WFACP_Common::update_page_custom_fields( $wfacp_id, $custom_fields );
				$resp['status'] = true;
				$resp['msg']    = __( 'Field Updated', 'woofunnels-aero-checkout' );
			}
		}
		self::send_resp( $resp );
	}

	/**
	 * Toggle status of checkout page from Admin page and Builder page
	 */
	public static function update_page_status() {
		self::check_nonce();
		$resp = array(
			'msg'    => '',
			'status' => false,
		);
		if ( isset( $_POST['id'] ) && $_POST['id'] > 0 && isset( $_POST['post_status'] ) ) {
			$args = [
				'ID'          => $_POST['id'],
				'post_status' => 'true' == $_POST['post_status'] ? 'publish' : 'draft',
			];

			$meta = get_post_meta( $_POST['id'], '_wp_page_template', true );
			wp_update_post( $args );

			update_post_meta( $_POST['id'], '_wp_page_template', $meta );
			WFACP_Common::save_publish_checkout_pages_in_transient();
			$resp = array(
				'msg'    => __( 'Checkout Page status updated', 'woofunnels-aero-checkout' ),
				'status' => true,
			);
		}
		self::send_resp( $resp );
	}

	/**
	 * Save selected design template against checkout page
	 */

	public static function save_design() {
		self::check_nonce();
		$resp = array(
			'msg'    => '',
			'status' => false,
		);
		if ( isset( $_POST['wfacp_id'] ) && $_POST['wfacp_id'] > 0 ) {
			$wfacp_id = absint( $_POST['wfacp_id'] );
			WFACP_Common::update_page_design( $wfacp_id, [
				'selected'        => $_POST['selected'],
				'selected_type'   => $_POST['selected_type'],
				'template_active' => $_POST['template_active'],
			] );

			$resp = array(
				'msg'    => __( 'Template imported', 'woofunnels-aero-checkout' ),
				'status' => true,
			);

		}
		self::send_resp( $resp );
	}

	public static function remove_design() {
		self::check_nonce();
		$resp = array(
			'msg'    => '',
			'status' => false,
		);
		if ( isset( $_POST['wfacp_id'] ) && $_POST['wfacp_id'] > 0 ) {
			$wfacp_id                    = absint( $_POST['wfacp_id'] );
			$template                    = WFACP_Common::default_design_data();
			$template['template_active'] = 'no';
			WFACP_Common::update_page_design( $wfacp_id, $template );
			$option = WFACP_SLUG . '_c_' . $wfacp_id;
			delete_option( $option );
			do_action( 'wfacp_template_removed', $wfacp_id );


			//Remove Template Meta key
			global $wpdb;
			$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => '_et_pb_use_builder', 'post_id' => $wfacp_id ] );
			$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => 'tcb_editor_enabled', 'post_id' => $wfacp_id ] );


			$resp = array(
				'msg'    => __( 'Design Saved Successfully', 'woofunnels-aero-checkout' ),
				'status' => true,
			);
		}
		self::send_resp( $resp );
	}

	/**
	 * Add product to cart from product switcher UI when checkbox is clicked
	 */
	public static function addon_product() {
		self::check_nonce();
		$resp      = array(
			'msg'    => '',
			'status' => false,
		);
		$success   = [];
		$cart_data = [];
		if ( isset( $_POST['data'] ) && count( $_POST['data'] ) > 0 ) {

			$post        = $_POST['data'];
			$item_key    = trim( $post['item_key'] );
			$wfacp_id    = absint( $post['wfacp_id'] );
			$product_qty = absint( isset( $post['quantity'] ) ? $post['quantity'] : 1 );

			$product_variation_id = ( isset( $post['variation_id'] ) ? absint( $post['variation_id'] ) : 0 );

			$attributes = ( isset( $post['attributes'] ) && is_array( $post['attributes'] ) ) ? $post['attributes'] : [];

			if ( '' != $item_key ) {
				WFACP_Common::set_id( $wfacp_id );
				WFACP_Core()->public->get_page_data( $wfacp_id );
				WFACP_Common::disable_wcct_pricing();
				$save_product_list = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id() );
				do_action( 'wfacp_before_add_to_cart', $save_product_list );

				if ( isset( $save_product_list[ $item_key ] ) && count( $save_product_list[ $item_key ] ) > 0 ) {
					$product = $save_product_list[ $item_key ];

					$product_id   = absint( $product['id'] );
					$quantity     = absint( $product['org_quantity'] );
					$variation_id = 0;
					if ( $product['parent_product_id'] && $product['parent_product_id'] > 0 ) {
						$product_id   = absint( $product['parent_product_id'] );
						$variation_id = absint( $product['id'] );
					}
					if ( $product_qty > 0 ) {
						$quantity = $quantity * $product_qty;
					}
					if ( $product_variation_id > 0 ) {
						$variation_id  = $product_variation_id;
						$product['id'] = $variation_id;
					}

					try {
						$product_obj = WFACP_Common::wc_get_product( $product_id, $item_key );

						if ( $variation_id > 0 ) {
							$is_found_variation = WFACP_Common::get_first_variation( $product_obj, $variation_id );
							if ( count( $attributes ) == 0 ) {
								$attributes = $is_found_variation['attributes'];
							}
						} else {
							if ( isset( $product['variable'] ) ) {
								$variation_id = absint( $product['default_variation'] );
								$attributes   = $product['default_variation_attr'];
								$manage_stock = WFACP_Common::check_manage_stock( wc_get_product( $variation_id ), $quantity );
								if ( false == $manage_stock ) {
									$dvar = WFACP_Common::get_default_variation( $product_obj );
									if ( count( $dvar ) > 0 ) {
										$variation_id = absint( $dvar['variation_id'] );
										$attributes   = $dvar['attributes'];
									}
								}
							}
						}

						if ( $variation_id > 0 ) {
							$custom_data['wfacp_variable_attributes'] = $attributes;
							$product_obj                              = wc_get_product( $variation_id );
						}

						$stock_status = WFACP_Common::check_manage_stock( $product_obj, $quantity );

						if ( false == $stock_status ) {
							$resp['error']  = __( 'Sorry, we do not have enough stock to fulfill your order. Please change quantity and try again. We apologize for any inconvenience caused.', 'woofunnels-aero-checkout' );
							$resp['qty']    = 1;
							$resp['status'] = false;
							self::send_resp( $resp );
						}

						$custom_data['_wfacp_product']     = true;
						$custom_data['_wfacp_product_key'] = $item_key;
						$custom_data['_wfacp_options']     = $product;

						$cart_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes, $custom_data );
						WFACP_Common::remove_item_remove_cart_item( $item_key );

						if ( is_string( $cart_key ) ) {
							$success[]              = $cart_key;
							$cart_data[ $item_key ] = WFACP_Common::analytics_item( $product_obj, WC()->cart->get_cart_item( $cart_key ) );
						} else {
							$resp['error']  = __( 'Sorry, we do not have enough stock to fulfill your order. Please change quantity and try again. We apologize for any inconvenience caused.', 'woofunnels-aero-checkout' );
							$resp['status'] = false;
							self::send_resp( $resp );
						}

						WFACP_Common::pc( 'Product added from product switcher  ' );
						WFACP_Common::pc( [
							'wfacp_data'    => $product,
							'price'         => $product_obj->get_price(),
							'regular_price' => $product_obj->get_regular_price(),
							'sale_price'    => $product_obj->get_sale_price(),
							'cart_key'      => $cart_key,

						] );
						$save_product_list[ $item_key ]['quantity']      = $product_qty;
						$save_product_list[ $item_key ]['is_added_cart'] = $cart_key;
						WC()->session->set( 'wfacp_product_data_' . WFACP_Common::get_id(), $save_product_list );

					} catch ( Exception $e ) {
						$resp['error']  = $e->getMessage();
						$resp['status'] = false;
						self::send_resp( $resp );
					}
				}
				do_action( 'wfacp_after_add_to_cart' );
			}

			if ( count( $success ) > 0 ) {

				$resp = array(
					'cart_item'      => $cart_data,
					'analytics_data' => WFACP_Common::analytics_checkout_data(),
					'item_key'       => $item_key,
					'attributes'     => $attributes,
					'status'         => true,
					'fragments'      => WFACP_Common::get_fragments( $wfacp_id ),
				);
			}
		}
		self::send_resp( $resp );
	}

	/**
	 * Remove item from cart when client uncheck the product checkbox
	 */
	public static function remove_addon_product() {
		self::check_nonce();
		$post = $_POST['data'];
		$resp = array(
			'msg'      => '',
			'status'   => false,
			'products' => [],
		);

		if ( isset( $post['item_key'] ) && '' != $post['item_key'] ) {
			$wfacp_id = absint( $post['wfacp_id'] );
			$cart_key = $post['item_key'];
			$items    = WC()->cart->get_cart();

			if ( 1 == WFACP_Common::get_cart_count( $items ) && false == WFACP_Common::enable_cart_deletion() ) {
				$resp           = WFACP_Common::last_item_delete_message( $resp, $cart_key );
				$resp['qty']    = isset( $post['old_qty'] ) ? absint( $post['old_qty'] ) : 1;
				$resp['status'] = false;
				self::send_resp( $resp );
			}


			WFACP_Common::set_id( $wfacp_id );
			WFACP_Core()->public->get_page_data( $wfacp_id );
			WFACP_Common::disable_wcct_pricing();
			$save_product_list = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id() );
			$cart_key          = trim( $cart_key );
			$cart_item         = WC()->cart->get_cart_item( $cart_key );
			if ( is_array( $cart_item ) && isset( $cart_item['_wfacp_product'] ) ) {
				do_action( 'wfacp_before_removing_cart_item', $cart_item );
				$item_key = $cart_item['_wfacp_product_key'];
				WC()->cart->remove_cart_item( $cart_key );

				$save_product_list[ $item_key ]['quantity'] = 1;
				unset( $save_product_list[ $item_key ]['is_added_cart'] );

				$wfacp_data = $save_product_list[ $item_key ];
				WC()->session->set( 'wfacp_product_data_' . WFACP_Common::get_id(), $save_product_list );
				do_action( 'wfacp_after_removing_cart_item', $cart_item );
				$resp['item_key']       = $item_key;
				$resp['status']         = true;
				$resp['analytics_data'] = WFACP_Common::analytics_checkout_data();
				$resp['fragments']      = WFACP_Common::get_fragments( $wfacp_id );
				WFACP_Common::pc( 'Product Remove from product switcher  ' );
				WFACP_Common::pc( [
					'wfacp_data' => $wfacp_data,
					'item_key'   => $item_key,
					'cart_key'   => $cart_key,
				] );
			}
		}
		self::send_resp( $resp );
	}

	/**
	 * Switch products it self other add to cart product
	 */
	public static function switch_product_addon() {
		self::check_nonce();
		$post = $_POST['data'];

		$resp = array(
			'msg'    => '',
			'status' => false,
		);
		WC()->cart->set_removed_cart_contents( [] );
		$cart_data = [];
		$wfacp_id  = absint( $post['wfacp_id'] );
		WFACP_Common::set_id( $wfacp_id );
		WFACP_Core()->public->get_page_data( $wfacp_id );
		WFACP_Common::disable_wcct_pricing();
		$save_product_list    = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id() );
		$product_qty          = absint( isset( $post['quantity'] ) ? $post['quantity'] : 1 );
		$field_type           = isset( $post['field_type'] ) ? $post['field_type'] : 'radio';
		$product_variation_id = absint( $post['variation_id'] );
		$attributes           = ( isset( $post['attributes'] ) && is_array( $post['attributes'] ) ) ? $post['attributes'] : [];
		$new_item             = [];
		// Remove Existing Item from cart
		$first_applied_coupons = WC()->cart->get_applied_coupons();
		if ( isset( $post['remove_item_key'] ) && '' != $post['remove_item_key'] ) {
			$remove_item_key = trim( $post['remove_item_key'] );
			$quantity        = absint( $post['quantity'] );
			$cart_key        = $remove_item_key;
			if ( 0 == $quantity && false == WFACP_Common::enable_cart_deletion() ) {
				$resp           = WFACP_Common::last_item_delete_message( $resp, $remove_item_key );
				$resp['qty']    = isset( $post['old_qty'] ) ? $post['old_qty'] : 1;
				$resp['status'] = false;
				self::send_resp( $resp );
			}

			$cart_item = WC()->cart->get_cart_item( $remove_item_key );
			if ( is_array( $cart_item ) ) {

				if ( isset( $cart_item['_wfacp_product'] ) ) {

					$p_item_key = $cart_item['_wfacp_product_key'];
					add_action( 'woocommerce_cart_item_removed', 'WFACP_Common::remove_item_deleted_items', 10, 2 );
					WFACP_Common::order_bump_restored_start();
					$status = WC()->cart->remove_cart_item( $remove_item_key );
					remove_action( 'woocommerce_cart_item_removed', 'WFACP_Common::remove_item_deleted_items', 10 );
					$save_product_list[ $p_item_key ]['quantity'] = 1;
					unset( $save_product_list[ $p_item_key ]['is_added_cart'] );
					$wfacp_data              = $save_product_list[ $p_item_key ];
					$resp['remove_item_key'] = $p_item_key;

					WFACP_Common::pc( 'Product Remove from product switcher  ' );
					WFACP_Common::pc( [
						'wfacp_data' => $wfacp_data,
						'item_key'   => $p_item_key,
						'cart_key'   => $remove_item_key,

					] );

				}
			}
		}
		// Add new item to cart
		if ( isset( $post['new_item'] ) && '' != $post['new_item'] && $product_qty > 0 ) {
			$new_item = $post['new_item'];
			do_action( 'wfacp_before_add_to_cart', $save_product_list );
			if ( isset( $save_product_list[ $new_item ] ) ) {
				$product = $save_product_list[ $new_item ];

				$product_id   = absint( $product['id'] );
				$quantity     = absint( $product['org_quantity'] );
				$variation_id = 0;
				if ( $product['parent_product_id'] && $product['parent_product_id'] > 0 ) {
					$product_id   = absint( $product['parent_product_id'] );
					$variation_id = absint( $product['id'] );
				}
				if ( $product_qty > 0 ) {
					$quantity = $quantity * $product_qty;
				}
				if ( $product_variation_id > 0 ) {
					$variation_id = $product_variation_id;
				}

				try {
					$custom_data = [];
					$product_obj = WFACP_Common::wc_get_product( $product_id, $new_item );

					if ( $variation_id > 0 ) {
						$is_found_variation = WFACP_Common::get_first_variation( $product_obj, $variation_id );
						if ( count( $attributes ) == 0 ) {
							$attributes = $is_found_variation['attributes'];
						}
					} else {
						if ( isset( $product['variable'] ) ) {
							$variation_id = absint( $product['default_variation'] );
							$attributes   = $product['default_variation_attr'];

							$manage_stock = WFACP_Common::check_manage_stock( wc_get_product( $variation_id ), $quantity );
							if ( false == $manage_stock ) {
								$dvar = WFACP_Common::get_default_variation( $product_obj );
								if ( count( $dvar ) > 0 ) {
									$variation_id = absint( $dvar['variation_id'] );
									$attributes   = $dvar['attributes'];
								}
							}
						}
					}

					if ( $variation_id > 0 ) {
						$custom_data['wfacp_variable_attributes'] = $attributes;
						$product_obj                              = wc_get_product( $variation_id );

					}

					$custom_data['_wfacp_product']     = true;
					$custom_data['_wfacp_product_key'] = $new_item;
					$custom_data['_wfacp_options']     = $product;
					$applied_coupons                   = WC()->cart->get_applied_coupons();
					$stock_status                      = WFACP_Common::check_manage_stock( $product_obj, $quantity );
					if ( false == $stock_status || ! $product_obj->is_purchasable() ) {

						WC()->cart->restore_cart_item( $remove_item_key );

						$resp['error']  = __( 'Sorry, we do not have enough stock to fulfill your order. Please change quantity and try again. We apologize for any inconvenience caused.', 'woofunnels-aero-checkout' );
						$resp['status'] = false;
						self::send_resp( $resp );
					} else {
						//if new item in stock then first make cart empty then add new item;
						if ( 'radio' === $field_type ) {
							WFACP_Common::make_cart_empty();
						}
					}

					$cart_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes, $custom_data );
					WFACP_Common::order_bump_restored_end();
					if ( is_string( $cart_key ) ) {


						if ( ( 'radio' === $field_type && ( empty( $applied_coupons ) && ! empty( $first_applied_coupons ) ) ) || apply_filters( 'wfacp_reapply_coupon_for_switch_product', false, $cart_key, $first_applied_coupons, $field_type ) ) {
							do_action( 'wfacp_before_apply_coupon_switch_product', $cart_key, $first_applied_coupons, $field_type );
							$applied_coupons = $first_applied_coupons;
							add_filter( 'woocommerce_coupon_message', function ( $msg, $msg_code ) {
								if ( 200 == $msg_code ) {
									return '';
								}

								return $msg;
							}, 10, 2 );

							if ( ! empty( $applied_coupons ) ) {
								foreach ( $applied_coupons as $cpn ) {
									WC()->cart->apply_coupon( $cpn );
								}
							}
							do_action( 'wfacp_after_apply_coupon_switch_product', $cart_key, $first_applied_coupons, $field_type );
						}

						$cart_data[ $new_item ]                          = WFACP_Common::analytics_item( $product_obj, WC()->cart->get_cart_item( $cart_key ) );
						$save_product_list[ $new_item ]['quantity']      = $product_qty;
						$save_product_list[ $new_item ]['is_added_cart'] = $cart_key;

					}


				} catch ( Exception $e ) {
					WC()->cart->restore_cart_item( $remove_item_key );
					$resp['error']  = $e->getMessage();
					$resp['status'] = false;
					self::send_resp( $resp );
				}
				do_action( 'wfacp_after_add_to_cart' );
			}
		}

		WC()->session->set( 'wfacp_product_data_' . WFACP_Common::get_id(), $save_product_list );
		if ( '' != $cart_key ) {
			$resp['cart_item']      = $cart_data;
			$resp['new_cart_key']   = $cart_key;
			$resp['attributes']     = $attributes;
			$resp['analytics_data'] = WFACP_Common::analytics_checkout_data();
			$resp['item_key']       = $new_item;
		}
		$resp['fragments'] = WFACP_Common::get_fragments( $wfacp_id );
		$resp['status']    = true;
		self::send_resp( $resp );
	}

	/**
	 * Update item from product switcher
	 */
	public static function update_product_qty() {
		self::check_nonce();
		$post = $_POST['data'];
		$resp = array(
			'msg'    => '',
			'status' => false,
		);
		if ( isset( $post['item_key'] ) && isset( $post['cart_key'] ) && isset( $post['qty'] ) ) {
			$cart_key = $post['cart_key'];
			$item_key = $post['item_key'];
			$qty      = absint( $post['qty'] );
			$wfacp_id = absint( $post['wfacp_id'] );
			WFACP_Common::set_id( $wfacp_id );
			WFACP_Core()->public->get_page_data( $wfacp_id );
			WFACP_Common::disable_wcct_pricing();
			$save_product_list = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id() );
			if ( $qty > 0 && $save_product_list[ $item_key ] ) {
				$product_data = $save_product_list[ $item_key ];
				$org_qty      = absint( $product_data['org_quantity'] );
				if ( $org_qty > 0 ) {
					$new_qty   = $org_qty * $qty;
					$cart_item = WC()->cart->get_cart_item( $cart_key );
					if ( ! is_null( $cart_item ) ) {
						/**
						 * @var $product_obj WC_Product;
						 */
						$product_obj  = $cart_item['data'];
						$stock_status = WFACP_Common::check_manage_stock( $product_obj, $new_qty );
						if ( false == $stock_status ) {

							$c_quantity = $cart_item['quantity'];

							$resp['error']    = __( 'Sorry, we do not have enough stock to fulfill your order. Please change quantity and try again. We apologize for any inconvenience caused.', 'woofunnels-aero-checkout' );
							$resp['qty']      = absint( $c_quantity / $org_qty );
							$resp['item_key'] = $item_key;
							$resp['status']   = false;
							self::send_resp( $resp );
						}
						WC()->cart->set_quantity( $cart_key, $new_qty );
						$save_product_list[ $item_key ]['quantity'] = $qty;
						WC()->session->set( 'wfacp_product_data_' . WFACP_Common::get_id(), $save_product_list );

						$resp['status']         = true;
						$resp['analytics_data'] = WFACP_Common::analytics_checkout_data();
						$resp['fragments']      = WFACP_Common::get_fragments( $wfacp_id );
						WFACP_Common::pc( 'Update Product quantity from  product switcher  ' );
						WFACP_Common::pc( [
							'wfacp_data'    => $product_data,
							'price'         => $product_obj->get_price(),
							'regular_price' => $product_obj->get_regular_price(),
							'sale_price'    => $product_obj->get_sale_price(),
							'cart_key'      => $cart_key,

						] );

					}
				}
			}
		}
		self::send_resp( $resp );
	}

	/**
	 * Save checkout page setting to checkout page
	 */
	public static function save_settings() {
		self::check_nonce();
		$resp = [
			'msg'    => '',
			'status' => false,
		];
		if ( isset( $_POST['wfacp_id'] ) && $_POST['wfacp_id'] > 0 ) {
			$wfacp_id = absint( $_POST['wfacp_id'] );
			$settings = $_POST['settings'];
			WFACP_Common::update_page_settings( $wfacp_id, $settings );
			$resp = [
				'msg'    => __( 'Changes saved', 'woofunnels-aero-checkout' ),
				'status' => true,
			];
		}
		self::send_resp( $resp );
	}

	/**
	 * Save aero checkout global settings
	 */
	public static function save_global_settings() {
		self::check_nonce();
		$resp = [
			'msg'    => __( 'Changes saved', 'woofunnels-aero-checkout' ),
			'status' => false,
		];
		if ( isset( $_POST['settings'] ) ) {
			$settings                                 = $_POST['settings'];
			$settings['wfacp_checkout_global_css']    = stripslashes_deep( $settings['wfacp_checkout_global_css'] );
			$settings['wfacp_global_external_script'] = stripslashes_deep( $settings['wfacp_global_external_script'] );

			update_option( '_wfacp_global_settings', $settings, true );
			$resp['status'] = true;
		}
		self::send_resp( $resp );
	}

	public static function preview_details() {
		self::check_nonce();
		$resp = [
			'msg'    => '',
			'status' => false,
		];
		if ( isset( $_POST['wfacp_id'] ) && ( $_POST['wfacp_id'] ) > 0 ) {
			$wfacp_id           = absint( $_POST['wfacp_id'] );
			$post_data          = get_post( $wfacp_id );
			$title              = $post_data->post_title;
			$post_status        = $post_data->post_status;
			$guid               = get_the_permalink( $wfacp_id );
			$productData        = WFACP_Common::get_page_product( $wfacp_id );
			$discount_type_keys = [
				'fixed_discount_reg'    => __( 'on Regular Price', 'woofunnels-aero-checkout' ),
				'fixed_discount_sale'   => __( 'on Sale Price', 'woofunnels-aero-checkout' ),
				'percent_discount_reg'  => '% on Regular Price',
				'percent_discount_sale' => '% on Sale Price',
			];

			ob_start();
			?>
            <div class="wfacp-fp-wrap preview_sec product_preview">

				<?php
				if ( is_array( $productData ) && count( $productData ) > 0 ) {
					?>
                    <h3><?php _e( 'Products', 'woocommerce' ); ?></h3>
                    <div class="wfacp-sec">
                        <div class="wfacp-fp-offer-products">
                            <div class="wfacp_pop_table wfacp_popup_table_wrap">

								<?php
								foreach ( $productData as $product_key => $product_val ) {
									$discount_type   = $product_val['discount_type'];
									$discount_amount = $product_val['discount_amount'];
									$discounted_val  = __( '--', 'woofunnels-aero-checkout' );
									if ( $discount_amount > 0 ) {
										if ( false !== strpos( $discount_type, 'percent_discount_' ) ) {
											$discounted_val = $discount_amount . '' . $discount_type_keys[ $discount_type ];
										} else {
											$discounted_val = wc_price( $discount_amount ) . ' ' . $discount_type_keys[ $discount_type ];
										}
									}
									$strinCon = [];
									if ( isset( $product_val['title'] ) && $product_val['title'] != '' ) {
										$strinCon[] = $product_val['title'];
									}
									if ( isset( $product_val['quantity'] ) && $product_val['title'] != '' ) {
										$strinCon[] = 'x ' . $product_val['quantity'];
									}

									if ( isset( $discounted_val ) && $discounted_val > 0 ) {
										$strinCon[] = '@ ' . $discounted_val;
									}
									$popUptext = '';

									if ( is_array( $strinCon ) ) {
										$popUptext = implode( ' ', $strinCon );
									}

									if ( $popUptext != '' ) {
										echo "<p>$popUptext</p>";
									}
								}
								?>

                            </div>
                        </div>
                    </div>
					<?php
				} else {

					?>
                    <div class="wfacp-sec">
                        <div class="wfacp-fp-offer-products">
                            <div class="wfacp_pop_table wfacp_popup_table_wrap"><?php _e( 'No product is associated in the checkout form', 'woofunnels-aero-checkout' ) ?></div>
                        </div>
                    </div>
					<?php
				}
				?>
            </div>
			<?php
			$html                = ob_get_clean();
			$resp                = [
				'msg'    => 'Settings Data saved',
				'status' => true,
			];
			$resp['wfacp_id']    = $post_data;
			$resp['post_name']   = $title;
			$resp['launch_url']  = $guid;
			$resp['post_status'] = $post_status;
			$resp['html']        = $html;
		}
		self::send_resp( $resp );
	}

	/**
	 * Generate the quick preview html
	 */
	public static function wf_quick_view_ajax() {
		self::check_nonce();
		$resp = [
			'msg'    => '',
			'status' => false,
		];
		if ( isset( $_POST['data'] ) && ( $post = $_POST['data'] ) && isset( $post['wfacp_id'] ) && $post['wfacp_id'] > 0 && isset( $post['product_id'] ) && $post['product_id'] > 0 ) {
			$wfacp_id = absint( $post['wfacp_id'] );
			$item_key = $post['item_key'];
			$cart_key = isset( $post['cart_key'] ) ? $post['cart_key'] : '';
			WFACP_Common::set_id( $wfacp_id );
			$save_product_list = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id() );
			if ( isset( $save_product_list[ $item_key ] ) ) {
				$product_id = absint( $post['product_id'] );
				$params     = array(
					'p'         => $product_id,
					'post_type' => array( 'product', 'product_variation' ),
				);
				$query      = new WP_Query( $params );

				global $wfacp_product, $wfacp_post, $wfacp_qv_data;
				$wfacp_qv_data = $post;

				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						ob_start();
						global $post, $product;
						if ( in_array( $product->get_type(), WFACP_Common::get_variation_product_type() ) ) {
							$wfacp_product = $product;
							$wfacp_post    = $post;

							$parent_id = $product->get_parent_id();
							$product   = null;
							$post      = get_post( $parent_id );
							$product   = wc_get_product( $parent_id );
						}
						require_once( WFACP_TEMPLATE_COMMON . '/quick-view/qv-template.php' );
						$html           = ob_get_clean();
						$resp['status'] = true;
						$resp['html']   = $html;
						break;
					}
				}
				wp_reset_postdata();
			}
			self::send_resp( $resp );
		}
	}


	public static function update_cart_item_quantity() {
		self::check_nonce();
		$resp = [
			'msg'    => '',
			'status' => false,
		];
		if ( isset( $_POST['data'] ) && isset( $_POST['data']['cart_key'] ) ) {

			$post     = $_POST['data'];
			$quantity = absint( $post['quantity'] );
			$wfacp_id = absint( $post['wfacp_id'] );
			if ( $quantity <= 0 ) {
				$quantity = 0;
			}

			$cart_key = $post['cart_key'];

			if ( $quantity == 0 ) {

				$items       = WC()->cart->get_cart();
				$deletion_by = $post['by'];
				if ( 'mini_cart' !== $deletion_by && 1 == WFACP_Common::get_cart_count( $items ) && false == WFACP_Common::enable_cart_deletion() ) {
					$resp             = WFACP_Common::last_item_delete_message( $resp, $cart_key );
					$resp['qty']      = isset( $post['old_qty'] ) ? $post['old_qty'] : 1;
					$resp['cart_key'] = $cart_key;
					$resp['status']   = false;
					self::send_resp( $resp );
				}

				WC()->cart->remove_cart_item( $cart_key );
				$resp['status']         = true;
				$resp['analytics_data'] = WFACP_Common::analytics_checkout_data();
				$resp['fragments']      = WFACP_Common::get_fragments( $wfacp_id );
				self::send_resp( $resp );

			}
			WFACP_Common::disable_wcct_pricing();
			$cart_item = WC()->cart->get_cart_item( $cart_key );
			/**
			 * @var $product_obj WC_Product;
			 */
			$save_product_list = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id(), [] );
			$new_qty           = $quantity;
			$aero_key          = '';
			if ( isset( $cart_item['_wfacp_product_key'] ) ) {
				$aero_key     = $cart_item['_wfacp_product_key'];
				$product_data = $save_product_list[ $aero_key ];
				if ( isset( $save_product_list[ $aero_key ] ) ) {
					$org_qty = absint( $product_data['org_quantity'] );
					if ( $org_qty > 0 ) {
						$new_qty = $org_qty * $quantity;
					}
				}
			}
			$product_obj  = $cart_item['data'];
			$stock_status = WFACP_Common::check_manage_stock( $product_obj, $new_qty );
			if ( false == $stock_status ) {
				$resp['error'] = __( 'Sorry, we do not have enough stock to fulfill your order. Please change quantity and try again. We apologize for any inconvenience caused.', 'woofunnels-aero-checkout' );
				$resp['qty']   = $cart_item['quantity'];

				$resp['status'] = false;

				$resp['cart_key'] = $cart_key;

				self::send_resp( $resp );
			}

			$set = WC()->cart->set_quantity( $cart_key, $new_qty );

			if ( $set ) {
				if ( '' !== $aero_key ) {
					$save_product_list[ $aero_key ]['quantity'] = $quantity;
					WC()->session->set( 'wfacp_product_data_' . WFACP_Common::get_id(), $save_product_list );
				}

				$resp['qty']            = $quantity;
				$resp['status']         = true;
				$resp['analytics_data'] = WFACP_Common::analytics_checkout_data();
				$resp['fragments']      = WFACP_Common::get_fragments( $wfacp_id );
			}
		}
		self::send_resp( $resp );
	}

	public static function make_wpml_duplicate() {
		self::check_nonce();
		$resp = [
			'msg'    => __( 'Something went wrong', 'woofunnel-order-bump' ),
			'status' => false,
		];
		if ( isset( $_POST['trid'] ) && $_POST['trid'] > 0 && class_exists( 'SitePress' ) && method_exists( 'SitePress', 'get_original_element_id_by_trid' ) ) {
			$trid           = absint( $_POST['trid'] );
			$lang           = isset( $_POST['lang'] ) ? trim( $_POST['lang'] ) : '';
			$language_code  = isset( $_POST['language_code'] ) ? trim( $_POST['language_code'] ) : '';
			$lang           = empty( $lang ) ? $language_code : $lang;
			$master_post_id = SitePress::get_original_element_id_by_trid( $trid );
			if ( false !== $master_post_id ) {
				global $sitepress;
				$duplicate_id = $sitepress->make_duplicate( $master_post_id, $lang );
				if ( is_int( $duplicate_id ) && $duplicate_id > 0 ) {
					WFACP_Common::get_duplicate_data( $duplicate_id, $master_post_id );
					$new_post = get_post( $duplicate_id );
					if ( ! is_null( $new_post ) ) {
						$args['post_title'] = $new_post->post_title . ' - ' . __( 'Copy - ' . $lang, 'woofunnels-aero-checkout' );

						$args['ID'] = $duplicate_id;
						wp_update_post( $args );
					}
					$resp['redirect_url'] = add_query_arg( [
						'section'  => 'products',
						'wfacp_id' => $duplicate_id,
					], admin_url( 'admin.php?page=wfacp' ) );
					$resp['duplicate_id'] = $duplicate_id;
					$resp['status']       = true;
				}
			}
		}
		self::send_resp( $resp );
	}

	public static function update_cart_multiple_page() {
		self::check_nonce();
		$resp    = [
			'msg'    => '',
			'status' => false,
		];
		$success = [];
		if ( isset( $_POST['data'] ) ) {
			$post              = $_POST['data'];
			$switcher_products = $post['products'];
			$coupon            = trim( $post['coupon'] );
			$wfacp_id          = absint( $post['wfacp_id'] );

			if ( ! is_array( $switcher_products ) || empty( $switcher_products ) ) {
				self::send_resp( $resp );
			}

			WC()->cart->empty_cart();
			WFACP_Common::set_id( $wfacp_id );
			WFACP_Core()->public->get_page_data( $wfacp_id );

			if ( function_exists( 'WCCT_Core' ) && class_exists( 'WCCT_discount' ) ) {
				add_filter( 'wcct_force_do_not_run_campaign', [ WFACP_Core()->public, 'unset_wcct_campaign' ], 10, 2 );
			}
			$products = WFACP_Core()->public->get_product_list( $wfacp_id );
			do_action( 'wfacp_before_add_to_cart', $products );
			$added_products = [];

			foreach ( $products as $index => $data ) {

				$product_id   = absint( $data['id'] );
				$quantity     = absint( $data['quantity'] );
				$variation_id = 0;
				if ( $data['parent_product_id'] && $data['parent_product_id'] > 0 ) {
					$product_id   = absint( $data['parent_product_id'] );
					$variation_id = absint( $data['id'] );
				}
				$product_obj = WFACP_Common::wc_get_product( ( $variation_id > 0 ? $variation_id : $product_id ), $index );
				if ( ! $product_obj instanceof WC_Product ) {
					continue;
				}

				if ( ! $product_obj->is_purchasable() ) {
					unset( $products[ $index ] );
					continue;
				}
				$stock_status = WFACP_Common::check_manage_stock( $product_obj, $quantity );

				if ( false == $stock_status ) {
					unset( $products[ $index ] );
					continue;
				}
				$products[ $index ] = $data;
				$product_obj->add_meta_data( 'wfacp_data', $data );
				$added_products[ $index ] = $product_obj;
			}

			if ( count( $added_products ) > 0 ) {
				foreach ( $added_products as $index => $product_obj ) {
					if ( ! isset( $switcher_products[ $index ] ) ) {
						continue;
					}
					$data         = $product_obj->get_meta( 'wfacp_data' );
					$product_id   = absint( $data['id'] );
					$quantity     = absint( $data['org_quantity'] );
					$variation_id = 0;
					if ( $data['parent_product_id'] && $data['parent_product_id'] > 0 ) {
						$product_id   = absint( $data['parent_product_id'] );
						$variation_id = absint( $data['id'] );
					}
					try {
						$attributes  = [];
						$custom_data = [];
						if ( isset( $data['variable'] ) ) {
							$variation_id                             = $data['default_variation'];
							$attributes                               = $data['default_variation_attr'];
							$custom_data['wfacp_variable_attributes'] = $attributes;
						}
						$custom_data['_wfacp_product']     = true;
						$custom_data['_wfacp_product_key'] = $index;
						$current_quantity                  = 1;
						if ( $switcher_products[ $index ] ) {
							$current_quantity = absint( $switcher_products[ $index ] );
							if ( $current_quantity > 0 ) {
								$quantity = $current_quantity * $quantity;
							}
						}
						$products[ $index ]['quantity'] = $current_quantity;
						$custom_data['_wfacp_options']  = $data;
						$success[]                      = $cart_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes, $custom_data );
						if ( is_string( $cart_key ) ) {

							$data['is_added_cart'] = $cart_key;
							$added_products[ $index ]->update_meta_data( 'wfacp_data', $data );

							$products[ $index ]['is_added_cart'] = $cart_key;

						} else {
							unset( $added_products[ $index ], $products[ $index ] );
						}
					} catch ( Exception $e ) {

					}
				}
			}
			do_action( 'wfacp_after_add_to_cart' );

			if ( count( $success ) > 0 ) {
				WC()->session->set( 'wfacp_id', WFACP_Common::get_id() );
				WC()->session->set( 'wfacp_cart_hash', md5( maybe_serialize( WC()->cart->get_cart_contents() ) ) );
				WC()->session->set( 'wfacp_product_objects_' . WFACP_Common::get_id(), $added_products );
				WC()->session->set( 'wfacp_product_data_' . WFACP_Common::get_id(), $products );
				if ( '' !== $coupon ) {
					WC()->cart->add_discount( $coupon );
				}
				$resp = array(
					'success'   => $success,
					'status'    => true,
					'fragments' => WFACP_Common::get_fragments( $wfacp_id ),
				);
			}
			do_action( 'wfacp_after_add_to_cart' );
		}
		self::send_resp( $resp );
	}

	public static function apply_coupon() {
		self::check_nonce();
		$resp = [
			'msg'    => '',
			'status' => false,
		];
		wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );

		if ( isset( $_POST['data'] ) ) {
			remove_all_filters( 'woocommerce_coupons_enabled' );
			do_action( 'wfacp_before_coupon_apply', $_POST['data'] );
			$post     = $_POST['data'];
			$wfacp_id = absint( $post['wfacp_id'] );

			if ( apply_filters( 'wfacp_apply_coupon_via_ajax', true, $post ) ) {
				WC()->cart->add_discount( sanitize_text_field( $post['coupon_code'] ) );
			} else {
				do_action( 'wfacp_apply_coupon_via_ajax_placeholder', $post );
			}

			WC()->cart->calculate_totals();

			$all_notices  = WC()->session->get( 'wc_notices', array() );
			$notice_types = apply_filters( 'woocommerce_notice_types', array( 'error', 'success', 'notice' ) );
			$message      = [];
			do_action( 'wfacp_after_coupon_apply', $_POST['data'] );
			foreach ( $notice_types as $notice_type ) {
				if ( wc_notice_count( $notice_type ) > 0 ) {
					$message = array(
						$notice_type => $all_notices[ $notice_type ],
					);
				}
			}

			wc_clear_notices();

			$resp = array(
				'message'   => $message,
				'fragments' => WFACP_Common::get_fragments( $wfacp_id ),
			);
		} else {
			$resp = array(
				'message' => array(
					'error' => [ 'Please provide a coupon code' ],
				),
			);
		}

		self::send_resp( $resp );
	}

	public static function prep_fees() {
		$fees = [];

		foreach ( WC()->cart->get_fees() as $fee ) {
			$out         = (object) [];
			$out->name   = $fee->name;
			$out->amount = ( 'excl' == WC()->cart->tax_display_cart ) ? wc_price( $fee->total ) : wc_price( $fee->total + $fee->tax );
			$fees[]      = $out;
		}

		return $fees;
	}

	public static function remove_cart_item() {
		self::check_nonce();
		$resp = [
			'msg'    => '',
			'status' => false,
		];
		if ( isset( $_POST['data'] ) ) {
			$post     = $_POST['data'];
			$wfacp_id = absint( $post['wfacp_id'] );
			if ( $wfacp_id == 0 ) {
				self::send_resp( $resp );
			}
			if ( isset( $post['cart_key'] ) && '' !== $post['cart_key'] ) {

				$items       = WC()->cart->get_cart();
				$deletion_by = $post['by'];
				if ( 'mini_cart' !== $deletion_by && 1 == WFACP_Common::get_cart_count( $items ) && false == WFACP_Common::enable_cart_deletion() ) {
					$resp = WFACP_Common::last_item_delete_message( $resp, $post['cart_key'] );
					//$resp['qty']      = isset( $post['old_qty'] ) ? $post['old_qty'] : 1;
					$resp['status']   = false;
					$resp['cart_key'] = $post['cart_key'];
					self::send_resp( $resp );
				}

				$cart_item_key = sanitize_text_field( wp_unslash( $post['cart_key'] ) );
				$cart_item     = WC()->cart->get_cart_item( $cart_item_key );
				if ( $cart_item ) {
					WFACP_Common::set_id( $wfacp_id );
					$status = WC()->cart->remove_cart_item( $cart_item_key );
					if ( $status ) {
						$product           = wc_get_product( $cart_item['product_id'] );
						$item_is_available = false;
						// Don't show undo link if removed item is out of stock.
						if ( $product && $product->is_in_stock() && $product->has_enough_stock( $cart_item['quantity'] ) ) {
							$item_is_available = true;
							$removed_notice    = "&nbsp;" . ' <a href="javascript:void(0)" class="wfacp_restore_cart_item" data-cart_key="' . $cart_item_key . '">' . __( 'Undo?', 'woocommerce' ) . '</a>';
						} else {
							$item_is_available = false;
							/* Translators: %s Product title. */
							$removed_notice = sprintf( __( '%s removed.', 'woocommerce' ), '' );
						}
						$resp['item_is_available'] = $item_is_available;
						$resp['status']            = true;
						$resp['fragments']         = WFACP_Common::get_fragments( $wfacp_id );
						$resp['analytics_data']    = WFACP_Common::analytics_checkout_data();
						$resp['remove_notice']     = $removed_notice;
					}
				}
			}
		}
		self::send_resp( $resp );
	}

	public static function undo_cart_item() {
		self::check_nonce();
		$resp = [
			'msg'    => '',
			'status' => false,
		];
		if ( isset( $_POST['data'] ) ) {
			$post     = $_POST['data'];
			$wfacp_id = absint( $post['wfacp_id'] );
			if ( isset( $post['cart_key'] ) && '' !== $post['cart_key'] ) {
				// Undo Cart Item.
				$cart_item_key = sanitize_text_field( wp_unslash( $post['cart_key'] ) );
				WC()->cart->restore_cart_item( $cart_item_key );


				do_action( 'wfacp_restore_cart_item', $cart_item_key, $post );
				$item                 = WC()->cart->get_cart_item( $cart_item_key );
				$resp['restore_item'] = [];
				if ( is_array( $item ) && $item['data'] instanceof WC_Product ) {
					$data                 = [ 'type' => $item['data']->get_type() ];
					$resp['restore_item'] = apply_filters( 'wfacp_restore_cart_item_data', $data, $item );
				}
				if ( isset( $item['_wfacp_product_key'] ) ) {
					$product_key       = $item['_wfacp_product_key'];
					$save_product_list = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id() );
					if ( isset( $save_product_list[ $product_key ] ) ) {
						$save_product_list[ $product_key ]['is_added_cart'] = $cart_item_key;
						WC()->session->set( 'wfacp_product_data_' . WFACP_Common::get_id(), $save_product_list );
					}
				}

				$resp['fragments']      = WFACP_Common::get_fragments( $wfacp_id );
				$resp['analytics_data'] = WFACP_Common::analytics_checkout_data();
				$resp['status']         = true;
			}
		}
		self::send_resp( $resp );
	}


	public static function hide_notification() {
		$rsp = [
			'status' => false,

		];

		if ( isset( $_POST['wfacp_id'] ) && isset( $_POST['index'] ) && isset( $_POST['message_type'] ) ) {
			$index        = $_POST['index'];
			$wfacp_id     = $_POST['wfacp_id'];
			$message_type = $_POST['message_type'];
			if ( isset( $message_type ) && 'global' == $message_type ) {
				$notification = get_option( 'wfacp_global_notifications', [] );
			} else {
				$notification = get_post_meta( $wfacp_id, 'notifications', true );
			}
			if ( ! is_array( $notification ) ) {
				$notification = [];
			}

			$notification[ $index ] = true;
			if ( isset( $message_type ) && 'global' == $message_type ) {
				update_option( 'wfacp_global_notifications', $notification, 'no' );
			} else {
				update_post_meta( $wfacp_id, 'notifications', $notification );
			}
			$rsp['status'] = true;
		}
		wp_send_json( $rsp );
	}

	public static function page_is_cached() {
		if ( isset( $_GET['wfacp_uor'] ) ) {
			$nonce     = trim( $_GET['wfacp_uor'] );
			$wfacp_id  = trim( $_GET['wfacp_id'] );
			$is_cached = wp_verify_nonce( $nonce, 'update-order-review' );

			if ( ! $is_cached ) {

				// page is cached
				WC()->cart->empty_cart();
				do_action( 'wfacp_page_is_cached', $wfacp_id );
				$resp = [
					'status'                    => true,
					'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
					'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
					'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
					'wfacp_nonce'               => wp_create_nonce( 'wfacp_secure_key' ),
				];

			} else {
				// reattemp
				if ( WC()->cart->is_empty() ) {
					do_action( 'wfacp_page_is_cached', $wfacp_id );
				}

				$resp = [
					'status'                    => true,
					'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
					'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
					'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
					'wfacp_nonce'               => wp_create_nonce( 'wfacp_secure_key' ),
				];

			}
			wp_send_json( $resp );
		}
	}

	public function refresh() {
		self::check_nonce();
		$resp                   = [
			'status' => true,
		];
		$wfacp_id               = absint( $_REQUEST['wfacp_id'] );
		$resp['fragments']      = WFACP_Common::get_fragments( $wfacp_id );
		$resp['analytics_data'] = WFACP_Common::analytics_checkout_data();
		self::send_resp( $resp );
	}

	public static function import_template() {
		self::check_nonce();
		$resp     = [
			'status' => false,
			'msg'    => __( 'Importing of template failed', 'woofunnels-aero-checkout' ),
		];
		$builder  = $_REQUEST['builder'];
		$template = $_REQUEST['template'];
		$wfacp_id = $_REQUEST['wfacp_id'];
		$is_multi = $_REQUEST['is_multi'];


		$response = WFACP_Core()->importer->import( $wfacp_id, $builder, $template, $is_multi );
		if ( isset( $response['error'] ) ) {
			$resp['status'] = false;
			$resp['msg']    = $response['error'];
			self::send_resp( $resp );
		}
		if ( isset( $response['status'] ) && true == $response['status'] ) {
			$resp['status'] = true;
			$resp['msg']    = __( 'Importing of template finished', 'woofunnels-aero-checkout' );
			self::send_resp( $resp );
		}

		self::send_resp( $resp );
	}

	/**
	 * Ajax action to activate plugin
	 */
	public static function activate_plugin() {
		self::check_nonce();

		$resp        = array( 'success' => false );
		$plugin_init = isset( $_POST['plugin_init'] ) ? sanitize_text_field( $_POST['plugin_init'] ) : '';
		$activate    = activate_plugin( $plugin_init, '', false, true );

		if ( is_wp_error( $activate ) ) {
			$resp['message'] = $activate->get_error_message();
			$resp['init']    = $plugin_init;
		}

		$resp['success'] = true;
		$resp['message'] = __( 'Plugin Successfully Activated', 'woofunnels-aero-checkout' );
		$resp['init']    = $plugin_init;

		self::send_resp( $resp );
	}

	public static function notice_dismise_link() {
		$results = array(
			'status' => 'failed',
			'msg'    => 'Problem With dismiss',
		);

		if ( ( isset( $_POST['notice_dismise_link'] ) && $_POST['notice_dismise_link'] !== '' ) ) {


			$noticekey = $_POST['notice_dismise_link'];
			$pageID    = $_POST['page_id'];

			if ( $pageID != '' ) {

				update_post_meta( $pageID, $noticekey, $noticekey );

			}

			$results = array(
				'status' => 'success',
				'msg'    => $noticekey . ' Notification Deleted',
				'key'    => $noticekey,
			);
		}

		wp_send_json( $results );
	}

}

WFACP_AJAX_Controller::init();
