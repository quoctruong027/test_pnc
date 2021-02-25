<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class xlwcty
 * @package NextMove
 * @author XlPlugins
 */
class xlwcty {

	public static $extend = array();
	private static $ins = null;
	private static $_registered_entity = array(
		'active'   => array(),
		'inactive' => array(),
	);
	public $xlwcty_data = array();
	public $wp_loaded = false;
	public $loop_thank_you_pages = array();
	public $all_thank_you_pages = array();
	public $is_mini_cart = false;
	public $deals = array();
	public $goals = array();
	public $single_thank_you_page = array();
	public $current_cart_item = null;
	public $single_product_css = array();
	public $product_obj = array();
	public $thank_you_page_goal = array();
	public $is_preview = false;
	public $header_info = array();
	public $xlwcty_is_thankyou = false;
	public $social_setting = array(
		'fb' => array(
			'appId'   => '',
			'version' => 'v2.9',
			'status'  => true,
			'cookie'  => true,
			'xfbml'   => true,
			'oauth'   => true,
		),
	);

	public function __construct() {
		/**
		 * Initiating hooks
		 */
		add_action( 'xlwcty_loaded', array( $this, 'init' ) );
	}

	/**
	 * Getting class instance
	 * @return null|xlwcty
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * Initialize hooks and setup core class to run front end functionality
	 */
	public function init() {
		/**
		 * Hook to modify order received url that matches criteria
		 */
		add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'redirect_to_thankyou' ), 99, 2 );
		/**
		 * Hooks for data setup while loading thank you page
		 */
		add_action( 'wp', array( XLWCTY_Core()->data, 'setup_options' ), 1 );
		add_action( 'wp', array( $this, 'validate_preview' ), 1 );
		add_action( 'wp', array( $this, 'maybe_preview_load' ), 1 );
		add_action( 'wp', array( $this, 'validate_request' ), 9 );
		add_action( 'wp', array( XLWCTY_Core()->data, 'load_order_wp' ), 10 );
		add_action( 'wp', array( $this, 'validate_order' ), 11 );
		add_action( 'wp', array( XLWCTY_Core()->data, 'set_page' ), 12 );
		add_action( 'wp', array( XLWCTY_Core()->data, 'load_thankyou_metadata' ), 13 );
		add_action( 'wp', array( $this, 'is_xlwcty_page' ), 14 );
		add_action( 'wp', array( $this, 'maybe_pass_no_cache_header' ), 15 );

		add_action( 'wp_footer', array( $this, 'execute_wc_thankyou_hooks' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'component_script' ), 9999 );
		add_action( 'wp_head', array( $this, 'enqueue_all_css' ) );
		add_action( 'wp_footer', array( $this, 'print_html_header_info' ), 50 );
		add_action( 'wp_footer', array( $this, 'maybe_add_info_footer' ) );
		add_action( 'xlwcty_before_page_render', array( $this, 'register_hooks' ) );
		add_action( 'xlwcty_after_page_render', array( $this, 'de_register_hooks' ) );
		add_filter( 'xlwcty_the_content', array( 'XLWCTY_Common', 'maype_parse_merge_tags' ), 10, 2 );
		add_filter( 'xlwcty_the_content', 'wptexturize' );
		add_filter( 'xlwcty_the_content', 'convert_smilies', 20 );
		add_filter( 'xlwcty_the_content', 'wpautop' );
		add_filter( 'xlwcty_the_content', 'shortcode_unautop' );
		add_filter( 'xlwcty_the_content', 'prepend_attachment' );

		add_filter( 'xlwcty_parse_shortcode', 'do_shortcode', 11 );

		add_filter( 'woocommerce_shipping_free_shipping_is_available', array( $this, 'check_coupons' ), 10, 2 );
		add_action( 'woocommerce_thankyou', array( $this, 'facebook_pixel_tracking_script' ) );

		add_filter( 'woocommerce_is_checkout', array( $this, 'declare_wc_checkout_page' ) );
		add_filter( 'woocommerce_is_order_received_page', array( $this, 'declare_wc_order_received_page' ) );
		add_action( 'wp_footer', array( $this, 'maybe_push_script_for_map_check' ) );

		add_action( 'parse_request', array( $this, 'parse_request_for_thankyou' ), 1 );
		add_action( 'parse_query', array( $this, 'parse_query_for_thankyou' ), 11 );

		add_action( 'parse_request', array( $this, 'maybe_set_query_var' ), 15 );
		add_filter( 'body_class', array( $this, 'add_body_class' ), 10, 2 );

		// setting nextmove page meta in case of any theme to make page full width
		add_action( 'template_redirect', array( $this, 'maybe_set_meta_to_hide_sidebar' ), 20 );

		add_action( 'wp_head', array( $this, 'xlwcty_page_noindex' ) );

		// remove other languages options
		add_filter( 'icl_post_alternative_languages', array( $this, 'post_alternative_languages' ) );
	}

	public function post_alternative_languages( $output ) {
		if ( $this->xlwcty_is_thankyou ) {
			$output = null;
		}

		return $output;
	}

	public function enqueue_all_css() {
		$css              = XLWCTY_Component::get_css();
		$default_settings = XLWCTY_Core()->data->get_option();
		$output           = '';
		if ( is_array( $css ) && count( $css ) > 0 ) {
			ob_start();
			echo "<style>\n";
			if ( isset( $default_settings['wrap_left_right_padding'] ) && (int) $default_settings['wrap_left_right_padding'] >= 0 ) {
				echo '.xlwcty_wrap{padding:0 ' . (int) $default_settings['wrap_left_right_padding'] . 'px;}';
			}
			if ( isset( $default_settings['shop_button_bg_color'] ) && $default_settings['shop_button_bg_color'] != '' ) {
				echo '.xlwcty_wrap .xlwcty_product .xlwcty_products li a.xlwcty_add_cart{background-color: ' . $default_settings['shop_button_bg_color'] . ';}';
				echo '.xlwcty_wrap .xlwcty_product .xlwcty_products li a.xlwcty_add_cart:hover{background-color: rgba(' . XLWCTY_Common::hex2rgb( $default_settings['shop_button_bg_color'], true, ',' ) . ',0.7);}';
			}
			if ( isset( $default_settings['shop_button_text_color'] ) && $default_settings['shop_button_text_color'] != '' ) {
				echo '.xlwcty_wrap .xlwcty_product .xlwcty_products li a.xlwcty_add_cart{color: ' . $default_settings['shop_button_text_color'] . ';}';
			}
			if ( isset( $default_settings['coupon_blur'] ) && absint( $default_settings['coupon_blur'] ) > 0 ) {
				echo '.xlwcty_wrap .xlwcty_coupon_area .xlwcty_cou_text{filter: blur(' . $default_settings['coupon_blur'] . 'px); -webkit-filter: blur(' . $default_settings['coupon_blur'] . 'px); -moz-filter: blur(' . $default_settings['coupon_blur'] . 'px); -ms-filter: blur(' . $default_settings['coupon_blur'] . 'px); -o-filter: blur(' . $default_settings['coupon_blur'] . 'px);}';
			}
			foreach ( $css as $comp => $comp_css ) {
				echo "/*{$comp}*/\n";
				if ( is_array( $comp_css ) && count( $comp_css ) > 0 ) {
					foreach ( $comp_css as $elem => $single_css ) {
						echo $elem . '{';
						if ( is_array( $single_css ) && count( $single_css ) > 0 ) {
							foreach ( $single_css as $css_prop => $css_val ) {
								echo $css_prop . ':' . $css_val . ';';
							}
						}
						echo "}\n";
					}
				}
			}
			echo '</style>';
			$output = ob_get_clean();
		}
		echo $output;
	}

	/**
	 * Setup thank-you page post and get new order-received link for the new order
	 *
	 * @param string $url
	 * @param WC_Order $order
	 *
	 * @return mixed|void Modified URL on success , default otherwise
	 */
	public function redirect_to_thankyou( $url, $order ) {
		$default_settings = XLWCTY_Core()->data->get_option();
		if ( isset( $default_settings['xlwcty_preview_mode'] ) && ( 'sandbox' === $default_settings['xlwcty_preview_mode'] ) ) {
			return $url;
		}
		$external_thankyou_url = apply_filters( 'xlwcty_redirect_to_thankyou', false, $url, $order );
		if ( false !== $external_thankyou_url ) {
			$external_thankyou_url = trim( $external_thankyou_url );
			$external_thankyou_url = wp_specialchars_decode( $external_thankyou_url );

			return $external_thankyou_url;
		} else {

			$order_id = XLWCTY_Compatibility::get_order_id( $order );
			if ( 0 != $order_id ) {
				$get_link = XLWCTY_Core()->data->setup_thankyou_post( XLWCTY_Compatibility::get_order_id( $order ), $this->is_preview )->get_page_link();
				if ( false !== $get_link ) {
					$get_link = trim( $get_link );
					$get_link = wp_specialchars_decode( $get_link );

					return ( XLWCTY_Common::prepare_single_post_url( $get_link, $order ) );
				}
			}
		}

		return $url;
	}

	public function component_script() {
		wp_enqueue_script( 'jquery' );
		if ( ! $this->is_xlwcty_page() ) {
			$localize = array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'version'    => XLWCTY_VERSION,
				'wc_version' => WC()->version,
			);
			wp_localize_script( 'jquery', 'xlwcty', apply_filters( 'xlwcty_localize_js_data', $localize ) );

			return;
		}

		$pluginUrl  = untrailingslashit( plugin_dir_url( XLWCTY_PLUGIN_FILE ) );
		$script_min = '.min';
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG === true ) {
			$script_min = '';
		}
		$fb_app_id                           = XLWCTY_Core()->data->get_option( 'fb_app_id' );
		$this->social_setting['fb']['appId'] = $fb_app_id;
		$google_map_api                      = XLWCTY_Core()->data->get_option( 'google_map_api' );

		wp_enqueue_script( 'xlwcty-component-script', $pluginUrl . '/assets/js/xlwcty-public' . $script_min . '.js', array(), false, true );
		wp_enqueue_style( 'xlwcty-components-css', $pluginUrl . '/assets/css/xlwcty-public' . $script_min . '.css', false );
		if ( is_rtl() ) {
			wp_enqueue_style( 'xlwcty-components-css-rtl', $pluginUrl . '/assets/css/xlwcty-public-rtl.css', false );
		}
		wp_enqueue_style( 'xlwcty-faicon', $pluginUrl . '/assets/fonts/fa.css', false );
		$localize = array(
			'ajax_url'       => admin_url( 'admin-ajax.php' ),
			'plugin_url'     => $pluginUrl,
			'social'         => $this->social_setting,
			'google_map_key' => $google_map_api,
			'version'        => XLWCTY_VERSION,
			'wc_version'     => WC()->version,
			'infobubble_url' => $pluginUrl . '/assets/js/xlwcty-infobubble' . $script_min . '.js',
			'cp'             => 0,
			'or'             => 0,
		);
		$order    = XLWCTY_Core()->data->get_order();
		if ( $order instanceof WC_Order ) {
			$localize['cp'] = XLWCTY_Core()->data->page_id;
			$localize['or'] = XLWCTY_Compatibility::get_order_id( $order );
		}
		$localize['settings']               = XLWCTY_Core()->data->get_option();
		$localize['map_errors']             = array(
			'error'          => __( 'Unable to process the request.', 'thank-you-page-for-woocommerce-nextmove' ),
			'over_limit'     => __( 'Google Map API quota limit reached.', 'thank-you-page-for-woocommerce-nextmove' ),
			'request_denied' => __( 'This API project is not authorized to use this API. Please ensure that this API is activated in the APIs Console.', 'thank-you-page-for-woocommerce-nextmove' ),
		);
		$localize['settings']['is_preview'] = ( $this->is_preview === true ) ? 'yes' : 'no';
		wp_localize_script( 'xlwcty-component-script', 'xlwcty', apply_filters( 'xlwcty_localize_js_data', $localize ) );
	}

	/**
	 * Checks whether its our page or not
	 * @return bool
	 */
	public function is_xlwcty_page() {

		return $this->xlwcty_is_thankyou;
	}

	/**
	 * Hooked over shortcode 'xlwcty_load'
	 * Includes layout files
	 *
	 * @param array $attrs
	 *
	 * @return string|void
	 */
	public function maybe_render_elements( $attrs = array() ) {
		global $post;
		if ( ! $this->is_xlwcty_page() ) {
			return;
		}
		if ( ! XLWCTY_Core()->data->get_order() instanceof WC_Order ) {
			return;
		}

		do_action( 'xlwcty_before_page_render' );
		$this->add_header_logs( sprintf( 'Order: #%s', XLWCTY_Compatibility::get_order_id( XLWCTY_Core()->data->get_order() ) ) );
		$this->add_header_logs( sprintf( 'Page: %s', '<a target="_blank" href="' . get_edit_post_link() . '">' . get_the_title() . '</a>' ) );
		ob_start();
		$this->include_template();
		do_action( 'xlwcty_aftr_page_render' );

		return ob_get_clean();
	}

	public function add_header_logs( $string ) {
		if ( ! in_array( $string, $this->header_info ) ) {
			array_push( $this->header_info, $string );
		}
	}

	/**
	 * Includes template file bases on chosen layout
	 */
	public function include_template() {

		if ( wp_is_mobile() ) {
			$file_data = get_file_data( plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'templates/mobile.php', array( 'XLWCTY Template Name' ) );
			if ( ! empty( $file_data ) ) {
				$this->add_header_logs( sprintf( 'Template: %s', $file_data[0] ) );
			}
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'templates/mobile.php';
		} else {
			$get_layout = XLWCTY_Core()->data->get_layout();
			if ( empty( $get_layout ) ) {
				return;
			}

			$file_data = get_file_data( plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'templates/' . $get_layout . '.php', array( 'XLWCTY Template Name' ) );
			if ( ! empty( $file_data ) ) {
				$this->add_header_logs( sprintf( 'Template: %s', $file_data[0] ) );
			}
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'templates/' . $get_layout . '.php';
		}

		if ( isset( $_REQUEST['order_id'] ) && ! empty( $_REQUEST['order_id'] ) ) {
			update_post_meta( $_REQUEST['order_id'], '_xlwcty_thankyou_page', get_the_ID() );
		}
	}

	/**
	 * Renders a section of a layout
	 * Usually called by the templates so that specific section renders
	 *
	 * @param string $layout layout to call
	 * @param string $section section to render
	 *
	 * @return string
	 * @see xlwcty::include_template()
	 */
	public function render( $layout = 'basic', $section = 'first' ) {
		try {
			$get_layout_data = XLWCTY_Core()->data->get_layout_info();
			if ( isset( $get_layout_data[ $layout ] ) && isset( $get_layout_data[ $layout ][ $section ] ) && is_array( $get_layout_data[ $layout ][ $section ] ) ) {
				foreach ( $get_layout_data[ $layout ][ $section ] as $components ) {
					if ( isset( $components['component'] ) ) {
						XLWCTY_Components::get_components( $components['component'] )->render_view( $components['slug'] );
					} else {
						XLWCTY_Components::get_components( $components['slug'] )->render_view( $components['slug'] );
					}
				}
			}
		} catch ( Exception $ex ) {
			echo '';
		}
	}

	public function validate_request() {
		if ( is_singular( XLWCTY_Common::get_thank_you_page_post_type_slug() ) && $this->is_preview === false && ( is_null( filter_input( INPUT_GET, 'order_id' ) ) || is_null( filter_input( INPUT_GET, 'key' ) ) ) ) {
			if ( filter_input( INPUT_GET, 'permalink_check' ) === 'yes' ) {
				wp_send_json( array(
					'status' => 'success',
				) );
			}
			if ( ! isset( $_REQUEST['elementor-preview'] ) ) {
				wp_redirect( home_url() );
			}
		}
	}

	public function validate_preview() {

		if ( isset( $_REQUEST['elementor-preview'] ) ) {
			return;
		}

		global $post;
		if ( ! is_singular( XLWCTY_Common::get_thank_you_page_post_type_slug() ) || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( filter_input( INPUT_GET, 'order_id' ) === null ) {

			/**
			 * case where we do not get order_id
			 */
			$get_chosen_order_meta = get_post_meta( $post->ID, '_xlwcty_chosen_order_preview', true );
			if ( $get_chosen_order_meta === '' ) {
				$allowed_status = XLWCTY_Core()->data->get_option( 'allowed_order_statuses' );
				$args           = array(
					'status'    => $allowed_status,
					'post_type' => 'shop_order',
					'limit'     => 1,
				);

				$get_orders = wc_get_orders( $args );
				if ( is_array( $get_orders ) && count( $get_orders ) === 0 ) {
					$allowed_status_names = implode( ', ', $allowed_status );
					$mod_status           = array();
					foreach ( $allowed_status as $val ) {
						$mod_status[] = XLWCTY_Common::get_wc_order_formmated_name( $val );
					}
					if ( is_array( $mod_status ) && count( $mod_status ) > 0 ) {
						$allowed_status_names = implode( ', ', $mod_status );
					}

					wp_die( __( 'Sorry! Preview Not Available.<br/>We could not find any orders for following states (' . $allowed_status_names . ').<br/>Please ensure that you have some orders or try adding states for which you have orders.<br/><a href="' . admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you&section=settings' ) . '">Click here</a> to view your order status settings.', 'thank-you-page-for-woocommerce-nextmove' ) );
				} else {
					$current_order = current( $get_orders );
					$link          = add_query_arg( array(
						'order_id' => XLWCTY_Compatibility::get_order_id( $current_order ),
						'key'      => XLWCTY_Compatibility::get_order_data( $current_order, 'order_key' ),
						'mode'     => 'preview',
					), get_permalink( $post ) );

					$link = apply_filters( 'xlwcty_redirect_preview_link', $link );

					wp_redirect( $link );
					exit;
				}
			} else {
				$get_chosen_order = wc_get_order( $get_chosen_order_meta );
				if ( ! $get_chosen_order instanceof WC_Order ) {
					return;
				}
				$link = add_query_arg( array(
					'order_id' => $get_chosen_order_meta,
					'key'      => XLWCTY_Compatibility::get_order_data( $get_chosen_order, 'order_key' ),
					'mode'     => 'preview',
				), get_permalink( $post ) );

				$link = apply_filters( 'xlwcty_redirect_preview_link', $link );

				wp_redirect( $link );
				exit;
			}
		}
	}

	/**
	 * Checking query arguments and validating preview mode
	 */
	public function maybe_preview_load() {
		global $post;

		if ( is_singular( XLWCTY_Common::get_thank_you_page_post_type_slug() ) && filter_input( INPUT_GET, 'mode' ) === 'preview' ) {
			/**
			 * Allowing theme and plugins to allow preview before it checks to user capability
			 */
			$this->is_preview = apply_filters( 'xlwcty_allow_preview', $this->is_preview );
			/**
			 * Checking user capability
			 */
			if ( $this->is_preview === false && ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( 'You are not allowed to access this page. ' );
			}
			$this->is_preview = true;
		}
	}

	/**
	 * Validates current order and checks if order qualifies for the current loading
	 * loads native thank you page if order don't qualify
	 * @uses WC_Order::get_checkout_order_received_url()
	 * @uses WC_Order::post_status
	 */
	public function validate_order() {
		global $post;
		$order = XLWCTY_Core()->data->get_order();

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		/**
		 * Check order key from URL so that users cannot open other's thank you page
		 */
		$order_key = XLWCTY_Compatibility::get_order_data( $order, 'order_key' );

		if ( filter_input( INPUT_GET, 'key' ) !== $order_key ) {

			if ( XLWCTY_Common::get_thank_you_page_post_type_slug() === $post->post_type ) {
				wp_die( __( 'Unable to process your request.', 'thank-you-page-for-woocommerce-nextmove' ) );
			}

			XLWCTY_Core()->data->reset_order();

			return;
		}

		$current_order_status = XLWCTY_Compatibility::get_order_status( $order );

		/**
		 * Check for $this->xlwcty_is_thankyou added to redirect to thank you page only if it's NextMove thank you page or leave as it is.
		 * This check is added as it causes conflict with upstroke plugin because it changes the order status which can be the case with any third party plugin as well.
		 */
		if ( ! in_array( $current_order_status, XLWCTY_Core()->data->get_option( 'allowed_order_statuses' ) ) && true === $this->xlwcty_is_thankyou ) {
			/**
			 * Removing our filter so that it would not modify order_received_url when we fetch it
			 */
			if ( strpos( $current_order_status, 'cancelled' ) == false ) {
				remove_filter( 'woocommerce_get_checkout_order_received_url', array(
					$this,
					'redirect_to_thankyou',
				), 99, 2 );
				$url = $order->get_checkout_order_received_url();

				wp_redirect( $url );
				exit;
			}
		}
	}

	/**
	 * Hooked over `wp_footer`
	 * Trying and executing wc native thankyou hooks
	 * Payment Gateways and other plugin usually use these hooks to read order data and process
	 * Also removes native woocommerce_order_details_table() to prevent order table load
	 */
	public function execute_wc_thankyou_hooks() {

		if ( ! $this->is_xlwcty_page() ) {
			return;
		}
		if ( ! XLWCTY_Core()->data->get_order() instanceof WC_Order ) {
			return;
		}
		$order = XLWCTY_Core()->data->get_order();
		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		$payment_method = XLWCTY_Compatibility::get_order_data( $order, 'payment_method' )
		?>
        <div class="xlwcty_wc_thankyou" style="display: none; opacity: 0">
			<?php

			do_action( 'woocommerce_thankyou', XLWCTY_Compatibility::get_order_id( $order ) );
			do_action( "woocommerce_thankyou_{$payment_method}", XLWCTY_Compatibility::get_order_id( $order ) );
			?>
        </div>
		<?php
	}

	public function print_html_header_info() {
		ob_start();
		if ( $this->header_info && is_array( $this->header_info ) && count( $this->header_info ) > 0 ) {
			foreach ( $this->header_info as $key => $info_row ) {
				?>
                <li id="wp-admin-bar-xlwcty_admin_page_node_<?php echo $key; ?>">
					<span class="ab-item">
						<?php echo $info_row; ?>
					</span>
                </li>
				<?php
			}
		}
		echo "<div class='xlwcty_header_passed' style='display: none;'>" . ob_get_clean() . '</div>';
	}

	/**
	 * Adding Script data to help in debug what campaign is ON for that product.
	 * Using WordPress way to localize a script
	 * @see WP_Scripts::localize()
	 */
	public function maybe_add_info_footer() {
		$l10n = array();
		if ( $this->header_info && is_array( $this->header_info ) && count( $this->header_info ) > 0 ) {
			foreach ( (array) $this->header_info as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}
				$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}
		}
		$script = 'var xlwcty_info = ' . wp_json_encode( $l10n ) . ';';
		?>
        <script type="text/javascript">
			<?php echo $script; ?>
        </script>
		<?php
	}

	public function register_hooks() {
		add_filter( 'woocommerce_short_description', array( $this, 'woocommerce_short_desc_limit_words' ), 99 );
		add_filter( 'woocommerce_product_get_short_description', array(
			$this,
			'woocommerce_short_desc_limit_words',
		), 99 );
	}

	public function de_register_hooks() {
		remove_filter( 'woocommerce_short_description', array( $this, 'woocommerce_short_desc_limit_words' ), 99 );
		remove_filter( 'woocommerce_product_get_short_description', array(
			$this,
			'woocommerce_short_desc_limit_words',
		), 99 );
	}

	public function woocommerce_short_desc_limit_words( $excerpt ) {

		return '<p>' . wp_trim_words( $excerpt, 30 ) . '</p>';
	}

	public function add_body_class( $classes, $class ) {
		global $post, $xlwcty_is_thankyou;
		$nm_slug = XLWCTY_Common::get_thank_you_page_post_type_slug();
		if ( ! is_singular( $nm_slug ) ) {
			return $classes;
		}
		if ( false === $xlwcty_is_thankyou ) {
			return $classes;
		}
		if ( is_array( $classes ) && count( $classes ) > 0 ) {
			$post_type = 'page';
			$classes[] = $post_type;
			$classes[] = "{$post_type}-template";

			$template_slug = get_page_template_slug( $post->ID );
			if ( empty( $template_slug ) ) {
				$template_parts[0] = 'default';
			} else {
				$template_parts = explode( '/', $template_slug );
			}
			foreach ( $template_parts as $part ) {
				$classes[] = "{$post_type}-template-" . sanitize_html_class( str_replace( array(
						'.',
						'/',
					), '-', basename( $part, '.php' ) ) );
			}
			$classes[] = "{$post_type}-template-" . sanitize_html_class( str_replace( '.', '-', $template_slug ) );
		}

		return $classes;
	}

	public function check_coupons( $is_available, $package ) {
		if ( is_array( $package ) && isset( $package['applied_coupons'] ) && is_array( $package['applied_coupons'] ) && count( $package['applied_coupons'] ) > 0 ) {
			foreach ( $package['applied_coupons'] as $coupon_name ) {
				$coupon_obj = null;
				$coupon_obj = new WC_Coupon( $coupon_name );
				if ( $coupon_obj->is_valid() && $this->is_xlwcty_coupon( $coupon_obj ) && $this->is_xlwcty_free_shipping_active( $coupon_obj ) ) {
					return true;
				}
			}
		}

		return $is_available;
	}

	public function is_xlwcty_coupon( $coupon_obj ) {
		$is_xlwcty_coupon = get_post_meta( XLWCTY_Compatibility::get_order_id( $coupon_obj ), 'is_xlwcty_coupon' );
		if ( is_array( $is_xlwcty_coupon ) && isset( $is_xlwcty_coupon[0] ) && $is_xlwcty_coupon[0] == XLWCTY_Compatibility::get_order_id( $coupon_obj ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $coupon_obj WC_Coupon
	 *
	 * @return bool
	 */
	public function is_xlwcty_free_shipping_active( $coupon_obj ) {
		$default_settings = XLWCTY_Core()->data->get_option();
		if ( $default_settings['allow_free_shipping'] == 'on' ) {
			if ( $default_settings['restrict_free_shipping'] == 'no' ) {
				// allowed for all address
				return true;
			} elseif ( $default_settings['restrict_free_shipping'] == 'yes' ) {
				// specific to specified addresses
				$allowed_states = $default_settings['allowed_order_statuses_coupons'];
				$billing_email  = $this->xl_get_customer_email();
				if ( $billing_email !== false && $billing_email != '' ) {
					$customer_email = XLWCTY_Compatibility::get_coupon_data( $coupon_obj, 'customer_email' );
					if ( isset( $customer_email ) && is_array( $customer_email ) && isset( $customer_email[0] ) && $customer_email[0] != '' && ( $customer_email[0] != $billing_email ) ) {
						return false;
					}
					$user_last_order_email_status = $this->check_user_last_order_state( $billing_email );
					if ( $user_last_order_email_status !== false ) {
						$user_last_order_email_status = ( 'wc-' === substr( $user_last_order_email_status, 0, 3 ) ) ? $user_last_order_email_status : 'wc-' . $user_last_order_email_status;
						if ( in_array( $user_last_order_email_status, $allowed_states ) ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	public function xl_get_customer_email() {
		if ( XLWCTY_Compatibility::is_wc_version_gte_3_0() ) {
			$billing_email = WC()->customer->get_billing_email();

			return $billing_email;
		} else {
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				if ( isset( $current_user->billing_email ) && $current_user->billing_email != '' ) {
					return $current_user->billing_email;
				}
			}
		}

		return false;
	}

	public function check_user_last_order_state( $email ) {
		global $wpdb;
		$query  = $wpdb->prepare( "SELECT `post_id` FROM `wp_postmeta` WHERE `meta_key` = '_billing_email' AND `meta_value` = '%s' ORDER BY `post_id` DESC LIMIT 0,1", $email );
		$result = $wpdb->get_results( $query, ARRAY_A );
		if ( is_array( $result ) && isset( $result[0] ) ) {
			$order_id     = $result[0]['post_id'];
			$order_obj    = new WC_Order( $order_id );
			$order_status = $order_obj->get_status();

			return $order_status;
		}

		return false;
	}

	public function facebook_pixel_tracking_script( $order_id ) {

		include __DIR__ . '/google-facebook-ecommerce.php';

	}

	public function facebook_pixel_enabled() {
		$facebook_enable = XLWCTY_Core()->data->get_option( 'enable_fb_ecom_tracking' );
		$facebook_id     = XLWCTY_Core()->data->get_option( 'ga_fb_pixel_id' );

		if ( $facebook_enable == 'on' && $facebook_id > 0 ) {
			return $facebook_id;
		}

		return false;
	}

	public function google_analytics_enabled() {
		$analytic_enable = XLWCTY_Core()->data->get_option( 'enable_ga_ecom_tracking' );
		$analytic_id     = XLWCTY_Core()->data->get_option( 'ga_analytics_id' );
		if ( $analytic_enable == 'on' && $analytic_id != '' ) {
			return $analytic_id;
		}

		return false;
	}


	public function declare_wc_checkout_page( $bool ) {
		if ( $this->is_xlwcty_page() === true ) {
			return true;
		}

		return $bool;

	}

	public function declare_wc_order_received_page( $bool ) {
		if ( $this->is_xlwcty_page() === true ) {
			return true;
		}

		return $bool;

	}

	public function maybe_pass_no_cache_header() {

		if ( $this->is_xlwcty_page() ) {
			$this->set_nocache_constants();
			nocache_headers();
		}
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function set_nocache_constants() {
		$this->maybe_define_constant( 'DONOTCACHEPAGE', true );
		$this->maybe_define_constant( 'DONOTCACHEOBJECT', true );
		$this->maybe_define_constant( 'DONOTCACHEDB', true );

		return null;
	}

	function maybe_define_constant( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}


	public function maybe_push_script_for_map_check() {
		if ( $this->is_xlwcty_page() === false ) {
			return;
		}
		?>
        <script>
            var xlwcty_is_google_map_failed = false;
            if (typeof gm_authFailure !== 'function ') {
                function gm_authFailure() {
                    console.log('Google map error found');
                    xlwcty_is_google_map_failed = true;
                    xlwctyCore.loadmap();
                }
            }
        </script>
		<?php
	}

	public function maybe_set_query_var( $wp_query_obj ) {

		if ( $this->is_xlwcty_page() ) {

			$get_order_id = filter_input( INPUT_GET, 'order_id' );
			if ( $get_order_id === null ) {
				return;
			}
			$wp_query_obj->query_vars['order-received'] = $get_order_id;
			set_query_var( 'order-received', $get_order_id );
		}
	}

	public function parse_request_for_thankyou( $wp_query_obj ) {

		if ( isset( $wp_query_obj->query_vars['post_type'] ) && ( XLWCTY_Common::get_thank_you_page_post_type_slug() == $wp_query_obj->query_vars['post_type'] ) ) {
			$this->xlwcty_is_thankyou = true;
		}
	}

	public function parse_query_for_thankyou( $wp_query_obj ) {

		if ( $this->is_xlwcty_page() && $wp_query_obj->is_main_query() ) {
			$wp_query_obj->is_page   = true;
			$wp_query_obj->is_single = false;
		}
	}

	/**
	 * Perform any changes on NextMove Thank You page only
	 * xlwcty-themes-helper functions working on it.
	 */
	public function maybe_set_meta_to_hide_sidebar() {
		global $post;
		if ( $this->is_xlwcty_page() && $post instanceof WP_Post ) {
			do_action( 'nextmove_template_redirect_single_thankyou_page' );
		}

	}

	public function xlwcty_page_noindex() {
		$post_type = XLWCTY_Common::get_thank_you_page_post_type_slug();
		if ( is_singular( $post_type ) ) {
			echo "<meta name='robots' content='noindex,follow' />\n";
		}
	}

}

if ( class_exists( 'XLWCTY_Core' ) ) {
	XLWCTY_Core::register( 'public', 'xlwcty' );
}
