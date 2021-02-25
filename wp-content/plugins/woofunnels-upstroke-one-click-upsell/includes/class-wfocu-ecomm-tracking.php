<?php

/**
 * This class take care of ecommerce tracking setup
 * It renders necessary javascript code to fire events as well as creates dynamic data for the tracking
 * @author woofunnels.
 */
class WFOCU_Ecomm_Tracking {
	private static $ins = null;

	public function __construct() {

		/**
		 * Global settings script should render on every mode, they should not differentiate between preview and real funnel
		 */
		add_action( 'wfocu_footer_before_print_scripts', array( $this, 'render_global_external_scripts' ), 999 );
		add_action( 'wp_head', array( $this, 'render_global_external_scripts_head' ), 999 );
		add_action( 'wfocu_header_print_in_head', array( $this, 'render_global_external_scripts_head' ), 999 );

		if ( true === WFOCU_Core()->template_loader->is_customizer_preview() ) {
			return;
		}
		/**
		 * Print js on pages
		 */
		add_action( 'wfocu_header_print_in_head', array( $this, 'render_fb' ), 90 );
		add_action( 'wfocu_header_print_in_head', array( $this, 'render_ga' ), 95 );
		add_action( 'wfocu_header_print_in_head', array( $this, 'render_gad' ), 100 );
		add_action( 'wfocu_header_print_in_head', array( $this, 'render_general_data' ), 99 );

		add_action( 'wfocu_header_print_in_head', array( $this, 'maybe_remove_track_data' ), 9999 );

		/**
		 * Tracking js on custom pages/thankyou page
		 */
		add_action( 'wp_head', array( $this, 'render_fb' ), 90 );
		add_action( 'wp_head', array( $this, 'render_ga' ), 95 );
		add_action( 'wp_head', array( $this, 'render_gad' ), 100 );
		add_action( 'wp_head', array( $this, 'render_general_data' ), 99 );

		add_action( 'wp_head', array( $this, 'maybe_remove_track_data' ), 9999 );

		/**
		 * Offer view and offer success script on upsell pages
		 */
		add_action( 'wfocu_header_print_in_head', array( $this, 'render_offer_view_script' ), 100 );
		add_action( 'wfocu_header_print_in_head', array( $this, 'render_offer_success_script' ), 110 );

		/**
		 * Offer view and offer success script on upsell pages for custom pages/thankyou page
		 */
		add_action( 'wp_head', array( $this, 'render_offer_view_script' ), 100 );
		add_action( 'wp_head', array( $this, 'render_offer_success_script' ), 110 );

		/**
		 * Funnel success on thank you page
		 */
		add_action( 'woocommerce_thankyou', array( $this, 'render_funnel_end' ), 200 );

		/**
		 * Generate data on these events that will further used by print functions
		 */
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'maybe_save_order_data' ), 999, 3 );
		add_action( 'woocommerce_before_pay_action', [ $this, 'maybe_save_order_data' ], 11, 1 );
		add_action( 'wfocu_offer_accepted_and_processed', array( $this, 'maybe_save_data_offer_accepted' ), 10, 4 );

		/**
		 * Generate and save the analytics data in session for general services rendering
		 */
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'maybe_save_order_data_general' ), 999, 3 );
		add_action( 'woocommerce_before_pay_action', [ $this, 'maybe_save_order_data_general' ], 11, 1 );
		add_action( 'wfocu_offer_accepted_and_processed', array( $this, 'maybe_save_data_offer_accepted_general' ), 10, 4 );

		add_action( 'wp_head', array( $this, 'render_js_to_track_referer' ), 10 );

		add_action( 'wfocu_header_print_in_head', array( $this, 'render_js_to_track_referer' ), 10 );


		add_action( 'wfocu_header_print_in_head', array( $this, 'render_pint' ), 100 );
		add_action( 'wp_head', array( $this, 'render_pint' ), 100 );

		add_action( 'wp_footer', array( $this, 'maybe_clear_local_storage_for_tracking_log' ) );
		$this->admin_general_settings = BWF_Admin_General_Settings::get_instance();
	}

	public static function get_instance() {
		if ( self::$ins === null ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * render script to load facebook pixel core js
	 */

	public function render_fb() {

		if ( $this->is_tracking_on() && false !== $this->is_fb_pixel() && $this->should_render() ) {
			$fb_advanced_pixel_data = $this->get_advanced_pixel_data(); ?>
			<!-- Facebook Analytics Script Added By WooFunnels -->
			<script>
                !function (f, b, e, v, n, t, s) {
                    if (f.fbq) return;
                    n = f.fbq = function () {
                        n.callMethod ?
                            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                    };
                    if (!f._fbq) f._fbq = n;
                    n.push = n;
                    n.loaded = !0;
                    n.version = '2.0';
                    n.queue = [];
                    t = b.createElement(e);
                    t.async = !0;
                    t.src = v;
                    s = b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t, s)
                }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
				<?php

				$get_all_fb_pixel = $this->is_fb_pixel();
				$get_each_pixel_id = explode( ',', $get_all_fb_pixel );
				if ( is_array( $get_each_pixel_id ) && count( $get_each_pixel_id ) > 0 ) {
				foreach ( $get_each_pixel_id as $pixel_id ) {
				?>
				<?php if ( true === $this->is_fb_advanced_tracking_on() && count( $fb_advanced_pixel_data ) > 0 ) { ?>
                fbq('init', '<?php echo esc_js( trim( $pixel_id ) ); ?>', <?php echo wp_json_encode( $fb_advanced_pixel_data ); ?>);
				<?php } else { ?>
                fbq('init', '<?php echo esc_js( trim( $pixel_id ) ); ?>');
				<?php } ?>
				<?php
				}
				?>

				<?php esc_js( $this->render_fb_view() ); ?>
				<?php esc_js( $this->maybe_print_fb_script() ); ?>
				<?php
				}
				?>
			</script>
			<?php
		}
	}

	/**
	 * render script to print general data.
	 */
	public function render_general_data() {
		if ( $this->should_render() ) {
			$general_data = WFOCU_Core()->data->get( 'general_data', array(), 'track' );
			if ( is_array( $general_data ) && count( $general_data ) > 0 ) { ?>
				<script type="text/javascript">
                    let wfocu_tracking_data =<?php echo wp_json_encode( $general_data ); ?>;
				</script>
				<?php
				do_action( 'wfocu_custom_purchase_tracking', $general_data );

			}
		}
	}

	/**
	 * render script to load facebook pixel core js
	 */
	public function render_pint() {
		if ( $this->is_tracking_on() && $this->pint_code() && false !== $this->do_track_pint() && $this->should_render() ) {
			?>
			<!-- Pinterest Pixel Base Code -->
			<script type="text/javascript">
                !function (e) {
                    if (!window.pintrk) {
                        window.pintrk = function () {
                            window.pintrk.queue.push(
                                Array.prototype.slice.call(arguments))
                        };
                        var
                            n = window.pintrk;
                        n.queue = [], n.version = "3.0";
                        var
                            t = document.createElement("script");
                        t.async = !0, t.src = e;
                        var
                            r = document.getElementsByTagName("script")[0];
                        r.parentNode.insertBefore(t, r)
                    }
                }("https://s.pinimg.com/ct/core.js");
				<?php
				$get_track_data = WFOCU_Core()->data->get( 'data', array(), 'track' );
				if(isset( $get_track_data['pint'] ) && isset( $get_track_data['pint']['email'] )) {
				?>
                pintrk('load', '<?php echo esc_js( $this->pint_code() )?>', {em: '<?php echo esc_js( $get_track_data['pint']['email'] ); ?>'});
				<?php
				}else{
				?> pintrk('load', '<?php echo esc_js( $this->pint_code() )?>');
				<?php
				}
				?>

                pintrk('page');
			</script>
			<noscript>
				<img height="1" width="1" style="display:none;" alt="" src="https://ct.pinterest.com/v3/?tid=YOUR_TAG_ID&noscript=1"/>
			</noscript>
			<!-- End Pinterest Pixel Base Code -->
			<script>
				<?php esc_js( $this->maybe_print_pint_script() ); ?>
			</script>
			<?php
		}
	}

	public function is_tracking_on() {
		return apply_filters( 'wfocu_front_ecomm_tracking', true );
	}

	public function is_fb_pixel() {

		$get_pixel_key = apply_filters( 'wfocu_fb_pixel_ids', $this->admin_general_settings->get_option( 'fb_pixel_key' ) );

		return empty( $get_pixel_key ) ? false : $get_pixel_key;

	}

	/**
	 * Decide whether script should render or not
	 * Bases on condition given and based on the action we are in there exists some boolean checks
	 *
	 * @param bool $allow_thank_you whether consider thank you page
	 * @param bool $without_offer render without an valid offer (valid funnel)
	 *
	 * @return bool
	 */
	public function should_render( $allow_thank_you = true, $without_offer = true ) {

		/**
		 * For customizer templates
		 */
		if ( current_action() === 'wfocu_header_print_in_head' && ( $without_offer === true || ( false === $without_offer && false === WFOCU_Core()->public->is_preview ) ) ) {
			return true;
		}

		/**
		 * For custom pages and single offer post front request
		 */
		if ( current_action() === 'wp_head' && ( ( did_action( 'wfocu_front_before_custom_offer_page' ) || did_action( 'wfocu_front_before_single_page_load' ) ) && ( $without_offer === true || ( false === $without_offer && false === WFOCU_Core()->public->is_preview ) ) || ( $allow_thank_you && is_order_received_page() ) ) ) {

			return true;
		}

		return apply_filters( 'wfocu_should_render_scripts', false, $allow_thank_you, $without_offer, current_action() );
	}

	public function get_advanced_pixel_data() {
		$data = WFOCU_Core()->data->get( 'data', array(), 'track' );

		if ( ! is_array( $data ) ) {
			return array();
		}

		if ( ! isset( $data['fb'] ) ) {
			return array();
		}

		if ( ! isset( $data['fb']['advanced'] ) ) {
			return array();
		}

		return $data['fb']['advanced'];
	}

	public function is_fb_advanced_tracking_on() {
		$is_fb_advanced_tracking_on = $this->admin_general_settings->get_option( 'is_fb_advanced_event' );
		if ( is_array( $is_fb_advanced_tracking_on ) && count( $is_fb_advanced_tracking_on ) > 0 && 'yes' === $is_fb_advanced_tracking_on[0] ) {
			return true;
		}

	}

	/**
	 * maybe render script to fire fb pixel view event
	 */
	public function render_fb_view() {

		if ( $this->is_tracking_on() && $this->do_track_fb_view() && WFOCU_Core()->public->if_is_offer() ) {
			?>
			fbq('track', 'PageView');
			<?php
		}
	}

	public function do_track_fb_view() {
		$fb_tracking = $this->admin_general_settings->get_option( 'is_fb_purchase_page_view' );

		if ( is_array( $fb_tracking ) && count( $fb_tracking ) > 0 && 'yes' === $fb_tracking[0] ) {
			return true;
		}

		return false;

	}

	/**
	 * Maybe print facebook pixel javascript
	 * @see WFOCU_Ecomm_Tracking::render_fb();
	 */
	public function maybe_print_fb_script() {
		$data = WFOCU_Core()->data->get( 'data', array(), 'track' ); //phpcs:ignore


		include_once plugin_dir_path( WFOCU_PLUGIN_FILE ) . '/views/js-blocks/wfocu-analytics-fb.phtml'; //phpcs:ignore WordPressVIPMinimum.Files.IncludingNonPHPFile.IncludingNonPHPFile
		if ( $this->do_track_fb_general_event() ) {

			$get_offer              = WFOCU_Core()->data->get_current_offer();
			$getEventName           = $this->admin_general_settings->get_option( 'general_event_name' );
			$params                 = array();
			$params['post_type']    = 'wfocu_offer';
			$params['content_name'] = get_the_title( $get_offer );
			$params['post_id']      = $get_offer;
			?>
			var wfocuGeneralData = <?php echo wp_json_encode( $params ); ?>;
			wfocuGeneralData = (typeof wfocuAddTrafficParamsToEvent !== "undefined")?wfocuAddTrafficParamsToEvent(wfocuGeneralData,'fb','<?php echo wp_json_encode( $this->get_generic_event_params() ); ?>'):wfocuGeneralData;
			fbq('trackCustom', '<?php echo esc_js( $getEventName ); ?>', wfocuGeneralData);
			<?php
		}

	}

	/**
	 * Maybe print facebook pixel javascript
	 * @see WFOCU_Ecomm_Tracking::render_pint();
	 */
	public function maybe_print_pint_script() {
		$data = WFOCU_Core()->data->get( 'data', array(), 'track' ); //phpcs:ignore
		include_once plugin_dir_path( WFOCU_PLUGIN_FILE ) . '/views/js-blocks/wfocu-analytics-pint.phtml'; //phpcs:ignore WordPressVIPMinimum.Files.IncludingNonPHPFile.IncludingNonPHPFile
	}

	public function do_track_fb_synced_purchase() {

		$do_track_fb_synced_purchase = $this->admin_general_settings->get_option( 'is_fb_synced_event' );
		if ( is_array( $do_track_fb_synced_purchase ) && count( $do_track_fb_synced_purchase ) > 0 && 'yes' === $do_track_fb_synced_purchase[0] ) {
			return true;
		}

		return false;
	}

	public function do_track_fb_purchase_event() {

		$do_track_fb_purchase_event = $this->admin_general_settings->get_option( 'is_fb_purchase_event' );

		if ( is_array( $do_track_fb_purchase_event ) && count( $do_track_fb_purchase_event ) > 0 && 'yes' === $do_track_fb_purchase_event[0] ) {
			return true;
		}

		return false;
	}

	public function do_track_fb_general_event() {

		$enable_general_event = $this->admin_general_settings->get_option( 'enable_general_event' );
		if ( is_array( $enable_general_event ) && count( $enable_general_event ) > 0 && 'yes' === $enable_general_event[0] ) {
			return true;
		}

		return false;
	}

	/**
	 * render google analytics core script to load framework
	 */
	public function render_ga() {
		$get_tracking_code = $this->ga_code();

		if ( false === $get_tracking_code ) {
			return;
		}

		$get_tracking_code = explode( ",", $get_tracking_code );

		if ( $this->is_tracking_on() && ( $this->do_track_ga_purchase() || $this->do_track_ga_view() ) && ( is_array( $get_tracking_code ) && ! empty( $get_tracking_code ) ) && $this->should_render() ) {
			?>
			<!-- Google Analytics Script Added By WooFunnels -->
			<script>
                (function (i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r;
                    i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                    a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

				<?php
				$count = false;

				foreach ( $get_tracking_code as $k => $ga_code ) {
					$tracker = ( true === $count ) ? ", 'tracker" . $k . "'" : "";
					echo "ga( 'create', '" . esc_js( trim( $ga_code ) ) . "', 'auto' " . $tracker . " );"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$count = true;
				}
				?>

				<?php esc_js( $this->maybe_print_ga_script() ); ?>
			</script>


			<?php
		}
	}

	public function ga_code() {
		$get_ga_key = apply_filters( 'wfocu_get_ga_key', $this->admin_general_settings->get_option( 'ga_key' ) );

		return empty( $get_ga_key ) ? false : $get_ga_key;
	}

	/**
	 * Maybe print google analytics javascript
	 * @see WFOCU_Ecomm_Tracking::render_ga();
	 */
	public function maybe_print_ga_script() {
		$data = WFOCU_Core()->data->get( 'data', array(), 'track' );
		if ( $this->do_track_ga_purchase() && is_array( $data ) && isset( $data['ga'] ) ) {
			include_once plugin_dir_path( WFOCU_PLUGIN_FILE ) . '/views/js-blocks/wfocu-analytics-ga.phtml'; //phpcs:ignore WordPressVIPMinimum.Files.IncludingNonPHPFile.IncludingNonPHPFile
		}

	}

	public function do_track_ga_purchase() {

		$do_track_ga_purchase = $this->admin_general_settings->get_option( 'is_ga_purchase_event' );

		if ( is_array( $do_track_ga_purchase ) && count( $do_track_ga_purchase ) > 0 && 'yes' === $do_track_ga_purchase[0] ) {
			return true;
		}

		return false;

	}

	public function do_track_pint() {
		$do_track_ga_purchase = $this->admin_general_settings->get_option( 'is_pint_purchase_event' );
		if ( is_array( $do_track_ga_purchase ) && count( $do_track_ga_purchase ) > 0 && 'yes' === $do_track_ga_purchase[0] ) {
			return true;
		}

		return false;
	}

	public function do_track_ga_view() {
		$ga_tracking = $this->admin_general_settings->get_option( 'is_ga_purchase_page_view' );

		if ( is_array( $ga_tracking ) && count( $ga_tracking ) > 0 && 'yes' === $ga_tracking[0] ) {
			return true;
		}

		return false;

	}

	/**
	 * render google analytics core script to load framework
	 */
	public function render_gad() {
		$get_tracking_code = $this->gad_code();

		if ( false === $get_tracking_code ) {
			return;
		}

		$get_tracking_code = explode( ",", $get_tracking_code );

		if ( $this->is_tracking_on() && $this->do_track_gad_purchase() && ( is_array( $get_tracking_code ) && ! empty( $get_tracking_code ) ) && $this->should_render() ) {
			?>
			<!-- Google Ads Script Added By WooFunnels -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $get_tracking_code[0] ); ?>"></script> <?php //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>

			<script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }

                gtag('js', new Date());

				<?php foreach ( $get_tracking_code as $k => $gad_code ) {
					echo "gtag('config', '" . esc_js( trim( $gad_code ) ) . "');";
					$gad_label = false;
					if ( false !== $this->gad_purchase_label() ) {
						$gad_labels = explode( ",", $this->gad_purchase_label() );
						$gad_label  = isset( $gad_labels[ $k ] ) ? $gad_labels[ $k ] : $gad_labels[0];
					}
					echo esc_js( $this->maybe_print_gad_script( $k, $gad_code, $gad_label ) );
				}

				?>

			</script>
			<?php
		}
	}

	public function gad_code() {

		$get_gad_key = apply_filters( 'wfocu_get_gad_key', $this->admin_general_settings->get_option( 'gad_key' ) );

		return empty( $get_gad_key ) ? false : $get_gad_key;
	}

	public function pint_code() {

		$get_pint_key = apply_filters( 'wfocu_get_pint_key', $this->admin_general_settings->get_option( 'pint_key' ) );

		return empty( $get_pint_key ) ? false : $get_pint_key;
	}

	public function gad_purchase_label() {

		$get_gad_conversion_label = apply_filters( 'wfocu_get_conversion_label', $this->admin_general_settings->get_option( 'gad_conversion_label' ) );

		return empty( $get_gad_conversion_label ) ? false : $get_gad_conversion_label;
	}

	/**
	 * Maybe print google analytics javascript
	 * @see WFOCU_Ecomm_Tracking::render_ga();
	 */
	public function maybe_print_gad_script( $k, $gad_code, $gad_label ) { //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		$data = WFOCU_Core()->data->get( 'data', array(), 'track' );
		if ( $this->do_track_gad_purchase() && is_array( $data ) && isset( $data['gad'] ) ) {

			include plugin_dir_path( WFOCU_PLUGIN_FILE ) . '/views/js-blocks/wfocu-analytics-gad.phtml'; //phpcs:ignore WordPressVIPMinimum.Files.IncludingNonPHPFile.IncludingNonPHPFile
		}

	}

	public function do_track_gad_purchase() {

		$do_track_gad_purchase = $this->admin_general_settings->get_option( 'is_gad_purchase_event' );
		if ( is_array( $do_track_gad_purchase ) && count( $do_track_gad_purchase ) > 0 && 'yes' === $do_track_gad_purchase[0] ) {
			return true;
		}

		return false;
	}

	/**
	 * @hooked over `woocommerce_checkout_order_processed`
	 * Just after funnel initiated we try and setup cookie data for the parent order
	 * That will be further used by WFOCU_Ecomm_Tracking::render_ga() && WFOCU_Ecomm_Tracking::render_ga()
	 *
	 * @param WC_Order $order
	 */
	public function maybe_save_order_data( $order_id, $posted_data = array(), $order = null ) {
		if ( $this->is_tracking_on() ) {
			if ( ! $order instanceof WC_Order ) {
				$order = wc_get_order( $order_id );
			}
			$order_id            = $order->get_id();
			$items               = $order->get_items( 'line_item' );
			$content_ids         = [];
			$content_name        = [];
			$category_names      = [];
			$num_qty             = 0;
			$products            = [];
			$google_products     = [];
			$google_ads_products = [];
			$pint_products       = [];
			$billing_email       = WFOCU_WC_Compatibility::get_order_data( $order, 'billing_email' );
			foreach ( $items as $item ) {
				$pid     = $item->get_product_id();
				$product = wc_get_product( $pid );
				if ( $product instanceof WC_product ) {

					$category       = $product->get_category_ids();
					$content_name[] = $product->get_title();
					$variation_id   = $item->get_variation_id();
					$get_content_id = 0;
					if ( empty( $variation_id ) || ( ! empty( $variation_id ) && true === $this->do_treat_variable_as_simple() ) ) {
						$get_content_id = $content_ids[] = $this->get_woo_product_content_id( $item->get_product_id() );

					} elseif ( false === $this->do_treat_variable_as_simple() ) {

						$get_content_id = $content_ids[] = $this->get_woo_product_content_id( $item->get_variation_id() );

					}
					$category_name = '';

					if ( is_array( $category ) && count( $category ) > 0 ) {
						$category_id = $category[0];
						if ( is_numeric( $category_id ) && $category_id > 0 ) {
							$cat_term = get_term_by( 'id', $category_id, 'product_cat' );
							if ( $cat_term ) {
								$category_name    = $cat_term->name;
								$category_names[] = $category_name;
							}
						}
					}
					$num_qty           += $item->get_quantity();
					$products[]        = array_map( 'html_entity_decode', array(
						'name'       => $product->get_title(),
						'category'   => ( $category_name ),
						'id'         => $get_content_id,
						'quantity'   => $item->get_quantity(),
						'item_price' => $order->get_line_subtotal( $item ),
					) );
					$pint_products[]   = array_map( 'html_entity_decode', array(
						'product_name'     => $product->get_title(),
						'product_category' => ( $category_name ),
						'product_id'       => $get_content_id,
						'product_quantity' => $item->get_quantity(),
						'product_price'    => $order->get_line_subtotal( $item ),
					) );
					$google_products[] = array_map( 'html_entity_decode', array(
						'id'       => $pid,
						'sku'      => empty( $product->get_sku() ) ? $product->get_id() : $product->get_sku(),
						'category' => $category_name,
						'name'     => $product->get_title(),
						'quantity' => $item->get_quantity(),
						'price'    => $order->get_item_subtotal( $item ),
					) );

					$google_ads_products[] = array_map( 'html_entity_decode', array(
						'id'       => $this->gad_product_id( $pid ),
						'sku'      => $product->get_sku(),
						'category' => $category_name,
						'name'     => $product->get_title(),
						'quantity' => $item->get_quantity(),
						'price'    => $order->get_item_subtotal( $item ),
					) );
				}
			}

			$advanced = array();
			/**
			 * Facebook advanced matching
			 */
			if ( $this->is_fb_advanced_tracking_on() ) {

				if ( ! empty( $billing_email ) ) {
					$advanced['em'] = $billing_email;
				}

				$billing_phone = WFOCU_WC_Compatibility::get_order_data( $order, 'billing_phone' );
				if ( ! empty( $billing_phone ) ) {
					$advanced['ph'] = $billing_phone;
				}

				$shipping_first_name = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_first_name' );
				if ( ! empty( $shipping_first_name ) ) {
					$advanced['fn'] = $shipping_first_name;
				}

				$shipping_last_name = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_last_name' );
				if ( ! empty( $shipping_last_name ) ) {
					$advanced['ln'] = $shipping_last_name;
				}

				$shipping_city = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_city' );
				if ( ! empty( $shipping_city ) ) {
					$advanced['ct'] = $shipping_city;
				}

				$shipping_state = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_state' );
				if ( ! empty( $shipping_state ) ) {
					$advanced['st'] = $shipping_state;
				}

				$shipping_postcode = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_postcode' );
				if ( ! empty( $shipping_postcode ) ) {
					$advanced['zp'] = $shipping_postcode;
				}
			}
			WFOCU_Core()->data->set( 'data', array(
				'fb'   => array(
					'products'       => $products,
					'total'          => $this->get_total_order_value( $order, 'order' ),
					'currency'       => WFOCU_WC_Compatibility::get_order_currency( $order ),
					'advanced'       => $advanced,
					'content_ids'    => $content_ids,
					'content_name'   => $content_name,
					'category_name'  => array_map( 'html_entity_decode', $category_names ),
					'num_qty'        => $num_qty,
					'additional'     => $this->purchase_custom_aud_params( $order ),
					'transaction_id' => WFOCU_WC_Compatibility::get_order_id( $order ),

				),
				'pint' => array(
					'order_id' => WFOCU_WC_Compatibility::get_order_id( $order ),
					'products' => $pint_products,
					'total'    => $this->get_total_order_value( $order, 'order', 'pint' ),
					'currency' => WFOCU_WC_Compatibility::get_order_currency( $order ),
					'email'    => $billing_email,
				),
				'ga'   => array(
					'products'    => $google_products,
					'transaction' => array(
						'id'          => WFOCU_WC_Compatibility::get_order_id( $order ),
						'affiliation' => esc_attr( get_bloginfo( 'name' ) ),
						'currency'    => WFOCU_WC_Compatibility::get_order_currency( $order ),
						'revenue'     => $order->get_total(),
						'shipping'    => WFOCU_WC_Compatibility::get_order_shipping_total( $order ),
						'tax'         => $order->get_total_tax(),
					),
				),
				'gad'  => array(
					'event_category'   => 'ecommerce',
					'transaction_id'   => (string) WFOCU_WC_Compatibility::get_order_id( $order ),
					'value'            => $this->get_total_order_value( $order, 'order', 'google' ),
					'currency'         => WFOCU_WC_Compatibility::get_order_currency( $order ),
					'items'            => $google_ads_products,
					'tax'              => $order->get_total_tax(),
					'shipping'         => WFOCU_WC_Compatibility::get_order_shipping_total( $order ),
					'ecomm_prodid'     => wp_list_pluck( $google_ads_products, 'id' ),
					'ecomm_pagetype'   => 'purchase',
					'ecomm_totalvalue' => array_sum( wp_list_pluck( $google_ads_products, 'price' ) ),

				),
			), 'track' );
			WFOCU_Core()->data->save( 'track' );
			WFOCU_Core()->log->log( 'Order #' . $order_id . ': Data for the parent order collected successfully.' );
		}
	}

	public function do_treat_variable_as_simple() {
		$do_treat_variable_as_simple = $this->admin_general_settings->get_option( 'content_id_variable' );
		if ( is_array( $do_treat_variable_as_simple ) && count( $do_treat_variable_as_simple ) > 0 && 'yes' === $do_treat_variable_as_simple[0] ) {
			return true;
		}

		return false;
	}

	public function get_woo_product_content_id( $product_id ) {

		$content_id_format = $this->admin_general_settings->get_option( 'content_id_value' );

		if ( $content_id_format === 'product_sku' ) {
			$content_id = get_post_meta( $product_id, '_sku', true );
		} else {
			$content_id = $product_id;
		}

		$prefix = $this->admin_general_settings->get_option( 'content_id_prefix' );
		$suffix = $this->admin_general_settings->get_option( 'content_id_suffix' );

		$value = $prefix . $content_id . $suffix;

		return ( $value );

	}

	public function gad_product_id( $product_id ) {

		$prefix = $this->admin_general_settings->get_option( 'id_prefix_gad' );
		$suffix = $this->admin_general_settings->get_option( 'id_suffix_gad' );

		$value = $prefix . $product_id . $suffix;

		return $value;
	}

	/**
	 * Get the value of purchase event for the different cases of calculations.
	 *
	 * @param WC_Order/offer_Data $data
	 * @param string $type type for which this function getting called, order|offer
	 *
	 * @return string the modified order value
	 */
	public function get_total_order_value( $data, $type = 'order', $party = 'fb' ) {

		$disable_shipping = $this->is_disable_shipping( $party );
		$disable_taxes    = $this->is_disable_taxes( $party );
		if ( 'order' === $type ) {
			//process order
			if ( ! $disable_taxes && ! $disable_shipping ) {
				//send default total
				$total = $data->get_total();
			} elseif ( ! $disable_taxes && $disable_shipping ) {

				$cart_total     = floatval( $data->get_total( 'edit' ) );
				$shipping_total = floatval( $data->get_shipping_total( 'edit' ) );
				$shipping_tax   = floatval( $data->get_shipping_tax( 'edit' ) );

				$total = $cart_total - $shipping_total - $shipping_tax;
			} elseif ( $disable_taxes && ! $disable_shipping ) {

				$cart_subtotal = $data->get_subtotal();

				$discount_total = floatval( $data->get_discount_total( 'edit' ) );
				$shipping_total = floatval( $data->get_shipping_total( 'edit' ) );

				$total = $cart_subtotal - $discount_total + $shipping_total;
			} else {
				$cart_subtotal = $data->get_subtotal();

				$discount_total = floatval( $data->get_discount_total( 'edit' ) );

				$total = $cart_subtotal - $discount_total;
			}
		} else {
			//process offer
			if ( ! $disable_taxes && ! $disable_shipping ) {

				//send default total
				$total = $data['total'];

			} elseif ( ! $disable_taxes && $disable_shipping ) {
				//total - shipping cost - shipping tax
				$total = $data['total'] - ( isset( $data['shipping']['diff'] ) && isset( $data['shipping']['diff']['cost'] ) ? $data['shipping']['diff']['cost'] : 0 ) - ( isset( $data['shipping']['diff'] ) && isset( $data['shipping']['diff']['tax'] ) ? $data['shipping']['diff']['tax'] : 0 );

			} elseif ( $disable_taxes && ! $disable_shipping ) {
				//total - taxes
				$total = $data['total'] - ( isset( $data['taxes'] ) ? $data['taxes'] : 0 );

			} else {

				//total - taxes - shipping cost
				$total = $data['total'] - ( isset( $data['taxes'] ) ? $data['taxes'] : 0 ) - ( isset( $data['shipping']['diff'] ) && isset( $data['shipping']['diff']['cost'] ) ? $data['shipping']['diff']['cost'] : 0 );

			}
		}

		$total = apply_filters( 'wfocu_ecommerce_pixel_tracking_value', $total, $data );

		return number_format( $total, wc_get_price_decimals(), '.', '' );
	}

	public function is_disable_shipping( $party = 'fb' ) {
		if ( $party === 'fb' ) {
			$exclude_from_total = $this->admin_general_settings->get_option( 'exclude_from_total' );
		} else {
			$exclude_from_total = $this->admin_general_settings->get_option( 'gad_exclude_from_total' );
		}

		if ( is_array( $exclude_from_total ) && count( $exclude_from_total ) > 0 && in_array( 'is_disable_shipping', $exclude_from_total, true ) ) {
			return true;
		}

		return false;

	}

	public function is_disable_taxes( $party = 'fb' ) {
		if ( $party === 'fb' ) {
			$exclude_from_total = $this->admin_general_settings->get_option( 'exclude_from_total' );
		} else {
			$exclude_from_total = $this->admin_general_settings->get_option( 'gad_exclude_from_total' );
		}

		if ( is_array( $exclude_from_total ) && count( $exclude_from_total ) > 0 && in_array( 'is_disable_taxes', $exclude_from_total, true ) ) {
			return true;
		}

		return false;

	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function purchase_custom_aud_params( $order ) {

		$params                = array();
		$get_custom_aud_config = $this->admin_general_settings->get_option( 'custom_aud_opt_conf' );
		$add_address           = in_array( 'add_town_s_c', $get_custom_aud_config, true );
		$add_payment_method    = in_array( 'add_payment_method', $get_custom_aud_config, true );
		$add_shipping_method   = in_array( 'add_shipping_method', $get_custom_aud_config, true );
		$add_coupons           = in_array( 'add_coupon', $get_custom_aud_config, true );

		if ( WFOCU_WC_Compatibility::is_wc_version_gte_3_0() ) {

			// town, state, country
			if ( $add_address ) {

				$params['town']    = $order->get_billing_city();
				$params['state']   = $order->get_billing_state();
				$params['country'] = $order->get_billing_country();

			}

			// payment method
			if ( $add_payment_method ) {
				$params['payment'] = $order->get_payment_method_title();
			}
		} else {

			// town, state, country
			if ( $add_address ) {

				$params['town']    = $order->billing_city;
				$params['state']   = $order->billing_state;
				$params['country'] = $order->billing_country;

			}

			// payment method
			if ( $add_payment_method ) {
				$params['payment'] = $order->payment_method_title;
			}
		}

		// shipping method
		$shipping_methods = $order->get_items( 'shipping' );
		if ( $add_shipping_method && $shipping_methods ) {

			$labels = array();
			foreach ( $shipping_methods as $shipping ) {
				$labels[] = $shipping['name'] ? $shipping['name'] : null;
			}

			$params['shipping'] = implode( ', ', $labels );

		}

		// coupons
		$coupons = $order->get_items( 'coupon' );
		if ( $add_coupons && $coupons ) {

			$labels = array();
			foreach ( $coupons as $coupon ) {
				$labels[] = $coupon['name'] ? $coupon['name'] : null;
			}

			$params['coupon_used'] = 'yes';
			$params['coupon_name'] = implode( ', ', $labels );

		} elseif ( $add_coupons ) {

			$params['coupon_used'] = 'no';

		}

		return $params;

	}

	/**
	 * @hooked over `wfocu_offer_accepted_and_processed`
	 * Sets up a cookie data for tracking based on the offer/upsell accepted by the customer
	 *
	 * @param int $get_current_offer Current offer
	 * @param array $get_package current package
	 */
	public function maybe_save_data_offer_accepted( $get_current_offer, $get_package, $get_parent_order, $new_order ) {
		$get_offer_Data = WFOCU_Core()->data->get( '_current_offer' );
		if ( $this->is_tracking_on() ) {
			$content_ids         = [];
			$content_name        = [];
			$category_names      = [];
			$num_qty             = 0;
			$google_products     = [];
			$products            = [];
			$google_ads_products = [];
			$pint_products       = [];
			$content_id_format   = $this->admin_general_settings->get_option( 'content_id_value' );

			foreach ( $get_package['products'] as $product ) {

				$pid         = $fbpid = $product['id'];
				$product_obj = wc_get_product( $pid );
				if ( $product_obj instanceof WC_product ) {
					$content_name[] = $product_obj->get_title();

					if ( $product_obj->is_type( 'variation' ) && false === $this->do_treat_variable_as_simple() ) {
						$content_ids[] = $this->get_woo_product_content_id( $product_obj->get_id() );
						$fbpid         = $product_obj->get_id();
					} else {
						if ( $product_obj->is_type( 'variation' ) ) {
							$content_ids[] = $this->get_woo_product_content_id( $product_obj->get_parent_id() );
							$fbpid         = $product_obj->get_parent_id();
						} else {
							$content_ids[] = $this->get_woo_product_content_id( $product_obj->get_id() );
							$fbpid         = $product_obj->get_id();
						}
					}
					$category      = $product_obj->get_category_ids();
					$category_name = '';
					if ( is_array( $category ) && count( $category ) > 0 ) {
						$category_id = $category[0];
						if ( is_numeric( $category_id ) && $category_id > 0 ) {
							$cat_term = get_term_by( 'id', $category_id, 'product_cat' );
							if ( $cat_term ) {
								$category_name    = $cat_term->name;
								$category_names[] = $cat_term->name;
							}
						}
					}
					$num_qty           += $product['qty'];
					$products[]        = array_map( 'html_entity_decode', array(
						'name'       => $product['_offer_data']->name,
						'category'   => esc_attr( $category_name ),
						'id'         => ( 'product_sku' === $content_id_format ) ? get_post_meta( $fbpid, '_sku', true ) : $fbpid,
						'quantity'   => $product['qty'],
						'item_price' => $product['args']['total'],
					) );
					$google_products[] = array_map( 'html_entity_decode', array(
						'id'       => $pid,
						'sku'      => empty( $product_obj->get_sku() ) ? $product_obj->get_id() : $product_obj->get_sku(),
						'category' => $category_name,
						'name'     => $product['_offer_data']->name,
						'quantity' => $product['qty'],
						'price'    => $product['args']['total'] / $product['qty'],
					) );
					$pint_products[]   = array_map( 'html_entity_decode', array(
						'product_id'       => $pid,
						'product_category' => $category_name,
						'product_name'     => $product['_offer_data']->name,
						'product_quantity' => $product['qty'],
						'product_price'    => $product['args']['total'],
					) );

					$google_ads_products[] = array_map( 'html_entity_decode', array(
						'id'       => $this->gad_product_id( $pid ),
						'sku'      => $product_obj->get_sku(),
						'category' => $category_name,
						'name'     => $product['_offer_data']->name,
						'quantity' => $product['qty'],
						'price'    => $product['args']['total'] / $product['qty'],
					) );
				}
			}
			$order         = WFOCU_Core()->data->get_current_order();
			$billing_email = WFOCU_WC_Compatibility::get_order_data( $order, 'billing_email' );
			$advanced      = array();
			/**
			 * Facebook advanced matching
			 */
			if ( $this->is_fb_advanced_tracking_on() ) {

				if ( ! empty( $billing_email ) ) {
					$advanced['em'] = $billing_email;
				}

				$billing_phone = WFOCU_WC_Compatibility::get_order_data( $order, 'billing_phone' );
				if ( ! empty( $billing_phone ) ) {
					$advanced['ph'] = $billing_phone;
				}

				$shipping_first_name = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_first_name' );
				if ( ! empty( $shipping_first_name ) ) {
					$advanced['fn'] = $shipping_first_name;
				}

				$shipping_last_name = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_last_name' );
				if ( ! empty( $shipping_last_name ) ) {
					$advanced['ln'] = $shipping_last_name;
				}

				$shipping_city = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_city' );
				if ( ! empty( $shipping_city ) ) {
					$advanced['ct'] = $shipping_city;
				}

				$shipping_state = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_state' );
				if ( ! empty( $shipping_state ) ) {
					$advanced['st'] = $shipping_state;
				}

				$shipping_postcode = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_postcode' );
				if ( ! empty( $shipping_postcode ) ) {
					$advanced['zp'] = $shipping_postcode;
				}
			}

			if ( $new_order instanceof WC_Order ) {
				$ga_transaction_id = WFOCU_WC_Compatibility::get_order_id( $new_order );
			} else {
				$ga_transaction_id = WFOCU_WC_Compatibility::get_order_id( $get_parent_order );
			}
			WFOCU_Core()->data->set( 'data', array(
				'fb'   => array(
					'products'       => $products,
					'total'          => $this->get_total_order_value( $get_package, 'offer' ),
					'currency'       => WFOCU_WC_Compatibility::get_order_currency( $order ),
					'advanced'       => $advanced,
					'content_ids'    => $content_ids,
					'content_name'   => $content_name,
					'category_name'  => array_map( 'html_entity_decode', $category_names ),
					'num_qty'        => $num_qty,
					'additional'     => $this->purchase_custom_aud_params( $order ),
					'transaction_id' => WFOCU_WC_Compatibility::get_order_id( $order ) . '-' . $get_current_offer,
				),
				'pint' => array(
					'order_id' => WFOCU_WC_Compatibility::get_order_id( $order ) . '-' . $get_current_offer,
					'products' => $pint_products,
					'total'    => $this->get_total_order_value( $get_package, 'offer', 'pint' ),
					'currency' => WFOCU_WC_Compatibility::get_order_currency( $order ),
					'email'    => $billing_email,
				),
				'ga'   => array(
					'products'    => $google_products,
					'transaction' => array(
						'id'          => $ga_transaction_id,
						'affiliation' => esc_attr( get_bloginfo( 'name' ) ),
						'currency'    => WFOCU_WC_Compatibility::get_order_currency( $order ),
						'revenue'     => $get_package['total'],
						'shipping'    => ( $get_package['shipping'] && isset( $get_package['shipping']['diff']['cost'] ) ) ? $get_package['shipping']['diff']['cost'] : 0,
						'tax'         => $get_package['taxes'],
						'offer'       => true,
					),
				),
				'gad'  => array(
					'event_category'   => 'ecommerce',
					'transaction_id'   => WFOCU_WC_Compatibility::get_order_id( $order ) . '-' . $get_current_offer,
					'value'            => $this->get_total_order_value( $get_package, 'offer', 'google' ),
					'currency'         => WFOCU_WC_Compatibility::get_order_currency( $order ),
					'items'            => $google_ads_products,
					'tax'              => $get_package['taxes'],
					'shipping'         => ( $get_package['shipping'] && isset( $get_package['shipping']['diff']['cost'] ) ) ? $get_package['shipping']['diff']['cost'] : 0,
					'ecomm_prodid'     => wp_list_pluck( $google_ads_products, 'id' ),
					'ecomm_pagetype'   => 'purchase',
					'ecomm_totalvalue' => array_sum( wp_list_pluck( $google_ads_products, 'price' ) ),

				),

				'success_offer'           => $get_offer_Data->settings->upsell_page_purchase_code,
				'purchase_script_enabled' => $get_offer_Data->settings->check_add_offer_purchase,
			), 'track' );
			WFOCU_Core()->data->save( 'track' );
		}

	}

	public function render_global_external_scripts() {

		if ( '' !== WFOCU_Core()->data->get_option( 'scripts' ) ) {
			echo WFOCU_Core()->data->get_option( 'scripts' );  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public function render_global_external_scripts_head() {

		if ( $this->should_render( false ) && '' !== WFOCU_Core()->data->get_option( 'scripts_head' ) ) {
			echo WFOCU_Core()->data->get_option( 'scripts_head' );  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render Offer View script
	 */
	public function render_offer_view_script() {
		$get_offer_Data = WFOCU_Core()->data->get( '_current_offer' );
		if ( $this->should_render( false, false ) && $get_offer_Data && is_object( $get_offer_Data ) && true === $get_offer_Data->settings->check_add_offer_script && '' !== $get_offer_Data->settings->upsell_page_track_code ) {
			echo $get_offer_Data->settings->upsell_page_track_code;   //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render successful offer script
	 */
	public function render_offer_success_script() {
		$data = WFOCU_Core()->data->get( 'data', array(), 'track' );

		if ( ! is_array( $data ) ) {
			return;
		}

		if ( ! isset( $data['success_offer'] ) || ( isset( $data['purchase_script_enabled'] ) && false === $data['purchase_script_enabled'] ) ) {
			return;
		}

		echo $data['success_offer'];  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render funnel end script
	 */
	public function render_funnel_end() {
		$funnel_id = WFOCU_Core()->data->get_funnel_id();

		if ( empty( $funnel_id ) ) {
			return;
		}

		$script = WFOCU_Core()->funnels->setup_funnel_options( $funnel_id )->get_funnel_option( 'funnel_success_script' );

		if ( '' === $script ) {
			return;
		}

		echo $script;  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function maybe_remove_track_data() {

		$get_tracking_data     = WFOCU_Core()->data->get( 'data', array(), 'track' );
		$get_gen_tracking_data = WFOCU_Core()->data->get( 'general_data', array(), 'track' );

		/**
		 * only set it blank when it exists
		 */
		if ( ! empty( $get_tracking_data ) && ! empty( $get_gen_tracking_data ) && ! is_wc_endpoint_url( 'order-pay' ) ) {
			$data = array();
			WFOCU_Core()->data->set( 'data', $data, 'track' );
			WFOCU_Core()->data->set( 'general_data', $data, 'track' );
			WFOCU_Core()->data->save( 'track' );
		}

	}

	public function render_js_to_track_referer() {
		$live_or_dev = 'live';

		if ( defined( 'WFOCU_IS_DEV' ) && true === WFOCU_IS_DEV ) {
			$live_or_dev = 'dev';
			$suffix      = '';
		} else {
			$suffix = '.min';
		}
		?>
		<script type="text/javascript">

			<?php
			if ( '1' === esc_js( wc_string_to_bool( count( $this->admin_general_settings->get_option( 'track_traffic_source' ) ) ) ) || '1' === esc_js( wc_string_to_bool( count( $this->admin_general_settings->get_option( 'ga_track_traffic_source' ) ) ) ) ) {
			?>
            var wfpxop = {};
            wfpxop.site_url = '<?php echo esc_url( site_url() ); ?>';
            wfpxop.genericParamEvents = '<?php echo wp_json_encode( $this->get_generic_event_params() ); ?>';
            wfpxop.DotrackTrafficSource = {'fb': false, 'ga': false};
			<?php if('1' === esc_js( wc_string_to_bool( count( $this->admin_general_settings->get_option( 'track_traffic_source' ) ) ) ) ) { ?>
            wfpxop.DotrackTrafficSource.fb = true;
			<?php } ?>
			<?php if('1' === esc_js( wc_string_to_bool( count( $this->admin_general_settings->get_option( 'ga_track_traffic_source' ) ) ) ) ) {?>
            wfpxop.DotrackTrafficSource.ga = true;
			<?php } ?>
			<?php echo include( plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'assets/' . $live_or_dev . '/js/utm-tracker' . $suffix . '.js' ); } //phpcs:ignore ?>
		</script><?php
	}

	/**
	 * Add Generic event params to the data in events
	 * @return array
	 */
	public function get_generic_event_params() {

		$user = wp_get_current_user();

		if ( $user->ID !== 0 ) {
			$user_roles = implode( ',', $user->roles );
		} else {
			$user_roles = 'guest';
		}

		return array(
			'domain'     => substr( get_home_url( null, '', 'http' ), 7 ),
			'user_roles' => $user_roles,
			'plugin'     => 'UpStroke',
		);

	}

	/**
	 * @param string $taxonomy Taxonomy name
	 * @param int $post_id (optional) Post ID. Current will be used of not set
	 *
	 * @return string|array List of object terms
	 */
	public function get_object_terms( $taxonomy, $post_id = null, $implode = true ) {

		$post_id = isset( $post_id ) ? $post_id : get_the_ID();
		$terms   = get_the_terms( $post_id, $taxonomy );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return $implode ? '' : array();
		}

		$results = array();

		foreach ( $terms as $term ) {
			$results[] = html_entity_decode( $term->name );
		}

		if ( $implode ) {
			return implode( ', ', $results );
		} else {
			return $results;
		}

	}


	public function get_localstorage_hash( $key ) {
		$data = WFOCU_Core()->data->get( 'data', array(), 'track' );
		if ( ! isset( $data[ $key ] ) ) {
			return 0;
		}

		return md5( wp_json_encode( array( 'key' => WFOCU_Core()->data->get_transient_key(), 'data' => $data[ $key ] ) ) );
	}

	/**
	 * We track in localstorage if we pushed ecommerce event for certain data or not
	 * Unfortunetly we cannot remove the storge on thank you as user still can press the back button and events will fire again
	 * So the next most logical way to remove the storage is during the next updated checkout action.
	 */
	public function maybe_clear_local_storage_for_tracking_log() {
		if ( is_checkout() ) {
			?>
			<script type="text/javascript">
                if (window.jQuery) {
                    (function ($) {
                        if (!String.prototype.startsWith) {
                            String.prototype.startsWith = function (searchString, position) {
                                position = position || 0;
                                return this.indexOf(searchString, position) === position;
                            };
                        }
                        $(document.body).on('updated_checkout', function () {
                            if (localStorage.length > 0) {
                                var len = localStorage.length;
                                var wfocuRemoveLS = [];
                                for (var i = 0; i < len; ++i) {
                                    var storage_key = localStorage.key(i);
                                    if (storage_key.startsWith("wfocuH_") === true) {
                                        wfocuRemoveLS.push(storage_key);
                                    }
                                }
                                for (var eachLS in wfocuRemoveLS) {
                                    localStorage.removeItem(wfocuRemoveLS[eachLS]);
                                }

                            }
                        });

                    })(jQuery);
                }
			</script>
			<?php
		}
	}

	/**
	 * @hooked over `woocommerce_checkout_order_processed`
	 * Just after funnel initiated we try and setup cookie data for the parent order
	 * That will be further used by WFOCU_Ecomm_Tracking::render_general
	 *
	 * @param WC_Order $order
	 */
	public function maybe_save_order_data_general( $order_id, $posted_data = array(), $order = null ) {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}
		$order_id       = $order->get_id();
		$items          = $order->get_items( 'line_item' );
		$content_ids    = [];
		$content_name   = [];
		$category_names = [];
		$num_qty        = 0;
		$products       = [];
		$billing_email  = WFOCU_WC_Compatibility::get_order_data( $order, 'billing_email' );
		foreach ( $items as $item ) {
			$pid     = $item->get_product_id();
			$product = wc_get_product( $pid );
			if ( $product instanceof WC_product ) {

				$category       = $product->get_category_ids();
				$content_name[] = $product->get_title();
				$variation_id   = $item->get_variation_id();
				$get_content_id = 0;
				if ( empty( $variation_id ) || ( ! empty( $variation_id ) && true === $this->do_treat_variable_as_simple() ) ) {
					$get_content_id = $content_ids[] = $this->get_woo_product_content_id( $item->get_product_id() );

				} elseif ( false === $this->do_treat_variable_as_simple() ) {

					$get_content_id = $content_ids[] = $this->get_woo_product_content_id( $item->get_variation_id() );

				}
				$category_name = '';

				if ( is_array( $category ) && count( $category ) > 0 ) {
					$category_id = $category[0];
					if ( is_numeric( $category_id ) && $category_id > 0 ) {
						$cat_term = get_term_by( 'id', $category_id, 'product_cat' );
						if ( $cat_term ) {
							$category_name    = $cat_term->name;
							$category_names[] = $category_name;
						}
					}
				}
				$num_qty    += $item->get_quantity();
				$products[] = array_map( 'html_entity_decode', array(
					'name'       => $product->get_title(),
					'pid'        => $pid,
					'category'   => $category_name,
					'id'         => $get_content_id,
					'sku'        => $product->get_sku(),
					'quantity'   => $item->get_quantity(),
					'item_total' => $order->get_item_subtotal( $item ),
					'line_total' => $order->get_line_subtotal( $item ),
				) );

			}
		}

		$advanced = array();

		if ( ! empty( $billing_email ) ) {
			$advanced['em'] = $billing_email;
		}

		$billing_phone = WFOCU_WC_Compatibility::get_order_data( $order, 'billing_phone' );
		if ( ! empty( $billing_phone ) ) {
			$advanced['ph'] = $billing_phone;
		}

		$shipping_first_name = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_first_name' );
		if ( ! empty( $shipping_first_name ) ) {
			$advanced['fn'] = $shipping_first_name;
		}

		$shipping_last_name = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_last_name' );
		if ( ! empty( $shipping_last_name ) ) {
			$advanced['ln'] = $shipping_last_name;
		}

		$shipping_city = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_city' );
		if ( ! empty( $shipping_city ) ) {
			$advanced['ct'] = $shipping_city;
		}

		$shipping_state = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_state' );
		if ( ! empty( $shipping_state ) ) {
			$advanced['st'] = $shipping_state;
		}

		$shipping_postcode = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_postcode' );
		if ( ! empty( $shipping_postcode ) ) {
			$advanced['zp'] = $shipping_postcode;
		}

		WFOCU_Core()->data->set( 'general_data', array(
			'products'       => $products,
			'total'          => $this->get_total_order_value( $order, 'order' ),
			'currency'       => WFOCU_WC_Compatibility::get_order_currency( $order ),
			'advanced'       => $advanced,
			'content_ids'    => $content_ids,
			'content_name'   => $content_name,
			'category_name'  => array_map( 'html_entity_decode', $category_names ),
			'num_qty'        => $num_qty,
			'additional'     => $this->purchase_custom_aud_params( $order ),
			'transaction_id' => WFOCU_WC_Compatibility::get_order_id( $order ),
			'order_id'       => WFOCU_WC_Compatibility::get_order_id( $order ),
			'email'          => $billing_email,
			'first_name'     => WFOCU_WC_Compatibility::get_order_data( $order, 'billing_first_name' ),
			'last_name'      => WFOCU_WC_Compatibility::get_order_data( $order, 'billing_last_name' ),
			'affiliation'    => esc_attr( get_bloginfo( 'name' ) ),
			'shipping'       => WFOCU_WC_Compatibility::get_order_shipping_total( $order ),
			'tax'            => $order->get_total_tax(),

		), 'track' );
		WFOCU_Core()->data->save( 'track' );
		WFOCU_Core()->log->log( 'Order #' . $order_id . ': General Data for the parent order collected successfully.' );
	}

	/**
	 * @hooked over `wfocu_offer_accepted_and_processed`
	 * Sets up a cookie data for tracking based on the offer/upsell accepted by the customer
	 *
	 * @param int $get_current_offer Current offer
	 * @param array $get_package current package
	 */
	public function maybe_save_data_offer_accepted_general( $get_current_offer, $get_package, $get_parent_order, $new_order ) {
		$get_offer_Data = WFOCU_Core()->data->get( '_current_offer' );

		$content_ids         = [];
		$content_name        = [];
		$category_names      = [];
		$num_qty             = 0;
		$products            = [];
		$google_ads_products = [];
		$content_id_format   = $this->admin_general_settings->get_option( 'content_id_value' );

		foreach ( $get_package['products'] as $product ) {
			$pid         = $fbpid = $product['id'];
			$product_obj = wc_get_product( $pid );
			if ( $product_obj instanceof WC_product ) {
				$content_name[] = $product_obj->get_title();

				if ( $product_obj->is_type( 'variation' ) && false === $this->do_treat_variable_as_simple() ) {
					$content_ids[] = $this->get_woo_product_content_id( $product_obj->get_id() );
					$fbpid         = $product_obj->get_id();
				} else {
					if ( $product_obj->is_type( 'variation' ) ) {
						$content_ids[] = $this->get_woo_product_content_id( $product_obj->get_parent_id() );
						$fbpid         = $product_obj->get_parent_id();
					} else {
						$content_ids[] = $this->get_woo_product_content_id( $product_obj->get_id() );
						$fbpid         = $product_obj->get_id();
					}
				}
				$category      = $product_obj->get_category_ids();
				$category_name = '';
				if ( is_array( $category ) && count( $category ) > 0 ) {
					$category_id = $category[0];
					if ( is_numeric( $category_id ) && $category_id > 0 ) {
						$cat_term = get_term_by( 'id', $category_id, 'product_cat' );
						if ( $cat_term ) {
							$category_name    = $cat_term->name;
							$category_names[] = $cat_term->name;
						}
					}
				}
				$num_qty    += $product['qty'];
				$products[] = array_map( 'html_entity_decode', array(
					'name'       => $product['_offer_data']->name,
					'category'   => $category_name,
					'id'         => ( 'product_sku' === $content_id_format ) ? get_post_meta( $fbpid, '_sku', true ) : $fbpid,
					'sku'        => $product_obj->get_sku(),
					'quantity'   => $product['qty'],
					'item_price' => $product['args']['total'],
					'price'      => $product['args']['total'],
					'product_id' => $pid,
				) );
			}
		}
		$order         = WFOCU_Core()->data->get_current_order();
		$billing_email = WFOCU_WC_Compatibility::get_order_data( $order, 'billing_email' );
		$advanced      = array();


		if ( ! empty( $billing_email ) ) {
			$advanced['em'] = $billing_email;
		}

		$billing_phone = WFOCU_WC_Compatibility::get_order_data( $order, 'billing_phone' );
		if ( ! empty( $billing_phone ) ) {
			$advanced['ph'] = $billing_phone;
		}

		$shipping_first_name = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_first_name' );
		if ( ! empty( $shipping_first_name ) ) {
			$advanced['fn'] = $shipping_first_name;
		}

		$shipping_last_name = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_last_name' );
		if ( ! empty( $shipping_last_name ) ) {
			$advanced['ln'] = $shipping_last_name;
		}

		$shipping_city = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_city' );
		if ( ! empty( $shipping_city ) ) {
			$advanced['ct'] = $shipping_city;
		}

		$shipping_state = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_state' );
		if ( ! empty( $shipping_state ) ) {
			$advanced['st'] = $shipping_state;
		}

		$shipping_postcode = WFOCU_WC_Compatibility::get_order_data( $order, 'shipping_postcode' );
		if ( ! empty( $shipping_postcode ) ) {
			$advanced['zp'] = $shipping_postcode;
		}

		if ( $new_order instanceof WC_Order ) {
			$ga_transaction_id = WFOCU_WC_Compatibility::get_order_id( $new_order );
		} else {
			$ga_transaction_id = WFOCU_WC_Compatibility::get_order_id( $get_parent_order );
		}
		WFOCU_Core()->data->set( 'general_data', array(

			'products'                => $products,
			'total'                   => $this->get_total_order_value( $get_package, 'offer' ),
			'currency'                => WFOCU_WC_Compatibility::get_order_currency( $order ),
			'advanced'                => $advanced,
			'content_ids'             => $content_ids,
			'content_name'            => $content_name,
			'category_name'           => array_map( 'html_entity_decode', $category_names ),
			'num_qty'                 => $num_qty,
			'additional'              => $this->purchase_custom_aud_params( $order ),
			'transaction_id'          => WFOCU_WC_Compatibility::get_order_id( $order ) . '-' . $get_current_offer,
			'email'                   => $billing_email,
			'first_name'              => WFOCU_WC_Compatibility::get_order_data( $order, 'billing_first_name' ),
			'last_name'               => WFOCU_WC_Compatibility::get_order_data( $order, 'billing_last_name' ),
			'ga_transaction_id'       => $ga_transaction_id,
			'affiliation'             => esc_attr( get_bloginfo( 'name' ) ),
			'revenue'                 => $get_package['total'],
			'offer'                   => $get_current_offer,
			'shipping'                => ( $get_package['shipping'] && isset( $get_package['shipping']['diff']['cost'] ) ) ? $get_package['shipping']['diff']['cost'] : 0,
			'tax'                     => $get_package['taxes'],
			'ecomm_prod_ids'          => wp_list_pluck( $google_ads_products, 'id' ),
			'purchase_script_enabled' => $get_offer_Data->settings->check_add_offer_purchase,

		), 'track' );
		WFOCU_Core()->data->save( 'track' );

	}
}

if ( class_exists( 'WFOCU_Core' ) ) {
	WFOCU_Core::register( 'ecom_tracking', 'WFOCU_Ecomm_Tracking' );
}