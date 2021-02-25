<?php

abstract class WFACP_Analytics {
	protected $slug = '';

	protected $checkout_data = [];
	protected $add_to_cart_data = [];
	protected $id = [];
	protected static $available_services = [];
	protected static $global_settings = [];
	protected static $page_settings = [];

	protected $variable_as_simple = false;
	protected $id_prefix = '';
	protected $id_suffix = '';

	protected function __construct() {
		$this->admin_general_settings = BWF_Admin_General_Settings::get_instance();
		if ( wp_doing_ajax() && isset( $_REQUEST['wc-ajax'] ) ) {
			$this->prepare_data();
		} else {
			add_action( 'wfacp_after_checkout_page_found', [ $this, 'prepare_data' ] );
		}


	}

	final public function prepare_data() {
		if ( true !== $this->enable_tracking() ) {
			return;
		}

		self::$page_settings = WFACP_Common::get_page_settings( WFACP_Common::get_id() );

		$this->content_id_type    = $this->admin_general_settings->get_option( $this->slug . '_content_id_type' );
		$this->variable_as_simple = $this->admin_general_settings->get_option( $this->slug . '_variable_as_simple' );
		$this->id_prefix          = $this->admin_general_settings->get_option( $this->slug . '_content_id_prefix' );
		$this->id_suffix          = $this->admin_general_settings->get_option( $this->slug . '_content_id_suffix' );

		$this->get_prepare_data();

		self::$available_services[ $this->slug ] = $this;
	}

	protected function enable_tracking() {
		return apply_filters( 'wfacp_enable_tracking_' . $this->slug, true );
	}

	public function get_checkout_data() {
		return $this->checkout_data;
	}

	public function get_add_to_cart_data() {
		return $this->add_to_cart_data;
	}

	public function get_product_content_id( $product_id ) {

		if ( $this->content_id_type == 'product_sku' ) {
			$content_id = get_post_meta( $product_id, '_sku', true );
			if ( empty( $content_id ) ) {
				$content_id = $product_id;
			}
		} else {
			$content_id = $product_id;
		}
		$value = $this->id_prefix . $content_id . $this->id_suffix;

		return $value;
	}

	public function get_cart_item_id( $item ) {
		$product_id = $item['product_id'];

		if ( false == wc_string_to_bool( $this->variable_as_simple ) && isset( $item['variation_id'] ) && $item['variation_id'] !== 0 ) {

			$product_id = $item['variation_id'];
		}

		return $product_id;
	}


	public function get_options() {

		$page_settings = self::$page_settings;

		$pixel_id = '';
		if ( 'google_ua' === $this->slug ) {
			$pixel_id = $this->admin_general_settings->get_option( 'ga_key' );
		} elseif ( 'pixel' === $this->slug ) {
			$pixel_id = $this->admin_general_settings->get_option( 'fb_pixel_key' );
		}

		$override_global_track_event = wc_string_to_bool( isset( $page_settings['override_global_track_event'] ) ? $page_settings['override_global_track_event'] : false );

		$locals = [];

		$pixel_id = apply_filters( 'wfacp_' . $this->slug . '_id', $pixel_id );

		if ( '' !== $pixel_id ) {

			$add_to_cart = $this->admin_general_settings->get_option( $this->slug . '_add_to_cart_event' ) ? $this->admin_general_settings->get_option( $this->slug . '_add_to_cart_event' ) : 'false';
			$checkout_ev = $this->admin_general_settings->get_option( $this->slug . '_initiate_checkout_event' ) ? $this->admin_general_settings->get_option( $this->slug . '_initiate_checkout_event' ) : 'false';
			$page_view   = $this->admin_general_settings->get_option( $this->slug . '_is_page_view' ) ? $this->admin_general_settings->get_option( $this->slug . '_is_page_view' ) : 'false';

			$payment_info         = $this->admin_general_settings->get_option( $this->slug . '_add_payment_info_event' ) ? $this->admin_general_settings->get_option( $this->slug . '_add_payment_info_event' ) : 'false';
			$add_to_cart_position = 'load';
			$checkout_ev_position = 'load';
			if ( true == $override_global_track_event ) {
				$add_to_cart  = isset( $page_settings[ $this->slug . '_add_to_cart_event' ] ) ? $page_settings[ $this->slug . '_add_to_cart_event' ] : false;
				$checkout_ev  = isset( $page_settings[ $this->slug . '_initiate_checkout_event' ] ) ? $page_settings[ $this->slug . '_initiate_checkout_event' ] : false;
				$payment_info = isset( $page_settings[ $this->slug . '_add_payment_info_event' ] ) ? $page_settings[ $this->slug . '_add_payment_info_event' ] : false;
				$page_view = isset( $page_settings[ $this->slug . '_is_page_view' ] ) ? $page_settings[ $this->slug . '_is_page_view' ] : false;

				if ( wc_string_to_bool( $add_to_cart ) ) {
					$add_to_cart_position = isset( $page_settings[ $this->slug . '_add_to_cart_event_position' ] ) ? $page_settings[ $this->slug . '_add_to_cart_event_position' ] : $add_to_cart_position;
				}
				if ( wc_string_to_bool( $checkout_ev ) ) {
					$checkout_ev_position = isset( $page_settings[ $this->slug . '_initiate_checkout_event_position' ] ) ? $page_settings[ $this->slug . '_initiate_checkout_event_position' ] : $checkout_ev_position;
				}
			}

			$locals = [
				'id'        => $pixel_id,
				'positions' => [
					'add_to_cart' => $add_to_cart_position,
					'checkout'    => $checkout_ev_position,
				],
				'settings'  => [
					'add_to_cart' => $add_to_cart,
					'page_view'   =>  $page_view,
					'checkout'    => $checkout_ev,
					'payment'     => $payment_info,
				]
			];


		}


		return $locals;


	}

	/**
	 * @param $product_obj WC_Product
	 * @param $cart_item
	 *
	 * @return array
	 */
	public function get_item( $product_obj, $cart_item ) {
		return [];
	}

	public function get_prepare_data() {
		$options = $this->get_options();

		if ( isset( $options['id'] ) && '' !== $options['id'] ) {
			if ( wc_string_to_bool( $options['settings']['add_to_cart'] ) ) {
				$this->add_to_cart_data = $this->get_add_to_cart_data();
				$options['add_to_cart'] = $this->add_to_cart_data;
			}
			if ( wc_string_to_bool( $options['settings']['checkout'] ) ) {
				$this->checkout_data = $this->get_checkout_data();
				$options['checkout'] = $this->checkout_data;
			}
		}


		return $options;
	}

	final public static function get_available_service() {
		return self::$available_services;
	}

	/**
	 * @param string $taxonomy Taxonomy name
	 *
	 * @return array Array of object term names
	 */
	public function get_object_terms( $taxonomy, $post_id ) {

		$terms   = get_the_terms( $post_id, $taxonomy );
		$results = array();

		if ( is_wp_error( $terms ) || empty ( $terms ) ) {
			return array();
		}

		// decode special chars
		foreach ( $terms as $term ) {
			$results[] = html_entity_decode( $term->name );
		}

		return $results;

	}

}

class WFACP_Analytics_Pixel extends WFACP_Analytics {
	private static $self = null;
	protected $slug = 'pixel';

	protected function __construct() {
		parent::__construct();

	}

	public static function get_instance() {
		if ( is_null( self::$self ) ) {
			self::$self = new self;
		}

		return self::$self;
	}

	public function get_checkout_data() {
		$output = new stdClass();
		if ( ! function_exists( 'WC' ) || is_null( WC()->cart ) ) {
			return $output;
		}
		$subtotal = WC()->cart->get_subtotal();
		$contents = WC()->cart->get_cart_contents();
		if ( count( $contents ) > 0 ) {
			$output = [];
			foreach ( $contents as $item_key => $item ) {

				if ( $item['data'] instanceof WC_Product ) {
					$item_id = $this->get_cart_item_id( $item );
					$item_id = $this->get_product_content_id( $item_id );

					$output['content_ids'][] = $item_id;
					$output['contents'][]    = [
						'id'         => $item_id,
						'item_price' => $item['line_subtotal'],
						'quantity'   => $item['quantity'],
						'value'      => $item['line_subtotal'],
					];
				}
			}
			$output['currency']     = get_woocommerce_currency();
			$output['value']        = $subtotal;
			$output['content_name'] = __( 'Checkout', 'woofunnels-aero-checkout' );
			$output['num_ids']      = count( $output['content_ids'] );
			$output['num_items']    = count( $output['content_ids'] );
			$output['content_type'] = 'product';
			$output['plugin']       = 'AeroCheckout';
			$output['subtotal']     = $subtotal;
			$output['user_roles']   = WFACP_Common::get_current_user_role();
		}


		return $output;
	}

	/**
	 * @param $product_obj WC_Product
	 * @param $cart_item
	 *
	 * @return array
	 */
	public function get_item( $product_obj, $cart_item ) {


		$item_id = $this->get_cart_item_id( $cart_item );

		$item_id         = $this->get_product_content_id( $item_id );
		$item_added_data = [
			'value'        => $cart_item['line_subtotal'],
			'content_name' => $product_obj->get_name(),
			'content_type' => 'product',
			'currency'     => get_woocommerce_currency(),
			'content_ids'  => [ $item_id ],
			'plugin'       => 'AeroCheckout',
			'contents'     => [
				[
					'id'         => $item_id,
					'item_price' => $cart_item['line_subtotal'],
					'quantity'   => $cart_item['quantity'],
					'value'      => $cart_item['line_subtotal'],
				],
			],
			'user_roles'   => WFACP_Common::get_current_user_role(),
		];

		return $item_added_data;
	}

	public function get_add_to_cart_data() {

		$cart_data = [];
		if ( function_exists( 'WC' ) && ! is_null( WC()->cart ) ) {
			$contents = WC()->cart->get_cart_contents();
			if ( count( $contents ) > 0 ) {
				foreach ( $contents as $item_key => $item ) {
					if ( $item['data'] instanceof WC_Product ) {
						$cart_data[ $item_key ] = $this->get_item( $item['data'], $item );
					}
				}
			}
		}

		return $cart_data;
	}


}

class WFACP_Analytics_GA extends WFACP_Analytics {
	private static $self = null;
	protected $slug = 'google_ua';

	protected function __construct() {
		parent::__construct();
		add_action( 'wfacp_internal_css', [ $this, 'print_js' ] );

	}

	public static function get_instance() {
		if ( is_null( self::$self ) ) {
			self::$self = new self;
		}

		return self::$self;
	}

	public function print_js() {
		if ( true !== $this->enable_tracking() ) {
			return;
		}

		$pixel_id = '';
		if ( 'google_ua' === $this->slug ) {
			$pixel_id = $this->admin_general_settings->get_option( 'ga_key' );
		} elseif ( 'pixel' === $this->slug ) {
			$pixel_id = $this->admin_general_settings->get_option( 'fb_pixel_key' );
		}

		if ( '' !== $pixel_id ) {
			?>
			<script async src='https://www.googletagmanager.com/gtag/js?id=<?php echo $pixel_id ?>'></script>
			<script>
				window.dataLayer = window.dataLayer || [];

				function gtag() {
					dataLayer.push(arguments);
				}

				gtag('js', new Date());
			</script>
			<?php
		}
	}

	public function get_prepare_data() {
		$options = $this->get_options();

		if ( isset( $options['id'] ) && '' !== $options['id'] ) {
			$data = $this->get_items_data();
			if ( wc_string_to_bool( $options['settings']['add_to_cart'] ) ) {
				$this->add_to_cart_data = $data;
				$options['add_to_cart'] = $data;
			}
			if ( wc_string_to_bool( $options['settings']['checkout'] ) ) {
				$this->checkout_data = $data;
				$options['checkout'] = $data;
			}
		}


		return $options;
	}

	public function get_item( $product, $cart_item ) {
		$product_id = $this->get_cart_item_id( $cart_item );
		$content_id = $this->get_product_content_id( $product_id );
		$name       = $product->get_title();
		if ( $cart_item['variation_id'] ) {
			$variation = wc_get_product( $cart_item['variation_id'] );
			if ( $variation->get_type() == 'variation' ) {
				$variation_name = implode( "/", $variation->get_variation_attributes() );
				$categories     = implode( '/', $this->get_object_terms( 'product_cat', $variation->get_parent_id() ) );
			} else {
				$variation_name = null;
				$categories     = implode( '/', $this->get_object_terms( 'product_cat', $product_id ) );
			}
		} else {
			$variation_name = null;
			$categories     = implode( '/', $this->get_object_terms( 'product_cat', $product_id ) );
		}

		$item = array(
			'id'       => $content_id,
			'name'     => $name,
			'category' => $categories,
			'quantity' => $cart_item['quantity'],
			'price'    => ! is_null( $cart_item['data'] ) ? (float) wc_get_price_to_display( $cart_item['data'], [ 'qty' => 1 ] ) : 0,
			'variant'  => $variation_name,
		);

		return $item;
	}

	public function get_items_data() {

		$items       = array();
		$product_ids = array();
		$total_value = 0;
		if ( is_null( WC()->cart ) ) {
			return $items;
		}
		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( $cart_item['data'] instanceof WC_Product ) {
				$product_id = $this->get_cart_item_id( $cart_item );
				$product    = wc_get_product( $product_id );
				$item       = $this->get_item( $product, $cart_item );
				if ( empty( $item ) ) {
					continue;
				}
				$items[]       = $item;
				$product_ids[] = $item['id'];
				$total_value   += $item['price'];
			}
		}

		return $items;
	}
}


WFACP_Analytics_Pixel::get_instance();
WFACP_Analytics_GA::get_instance();

