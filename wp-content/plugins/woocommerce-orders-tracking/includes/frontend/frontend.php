<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_ORDERS_TRACKING_FRONTEND_FRONTEND {
	protected $settings;
	protected $language;
	protected static $query_tracking;

	public function __construct() {
		$this->settings = new  VI_WOOCOMMERCE_ORDERS_TRACKING_DATA();
		$this->language = '';
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_shortcode( 'vi_wot_form_track_order', array( $this, 'shortcode_form_track_order' ) );
		add_action( 'widgets_init', array( $this, 'register_example_widget' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_vi_woo_orders_tracking_cainiao_submit_carrier', array( $this, 'submit_carrier' ) );
		add_action( 'wp_ajax_nopriv_vi_woo_orders_tracking_cainiao_submit_carrier', array( $this, 'submit_carrier' ) );
		add_action( 'wp_ajax_vi_woo_orders_tracking_ajax_shortcode_timeline', array(
			$this,
			'ajax_shortcode_timeline'
		) );
		add_action( 'wp_ajax_nopriv_vi_woo_orders_tracking_ajax_shortcode_timeline', array(
			$this,
			'ajax_shortcode_timeline'
		) );
	}


	/**
	 * @throws Exception
	 */
	public function ajax_shortcode_timeline() {
		$tracking_id = isset( $_GET['tracking_id'] ) ? sanitize_text_field( $_GET['tracking_id'] ) : '';
		$_wpnonce    = isset( $_GET['woo_orders_tracking_nonce'] ) ? sanitize_text_field( $_GET['woo_orders_tracking_nonce'] ) : '';
		$response    = array(
			'status' => 'success',
			'data'   => '',
		);
		if ( wp_verify_nonce( $_wpnonce, 'woo_orders_tracking_nonce_action' ) && $tracking_id ) {
			$service_carrier_type = $this->settings->get_params( 'service_carrier_type' );
			if ( $service_carrier_type === 'trackingmore' ) {
				$tracking_from_db = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::get_row( $tracking_id );
				if ( is_array( $tracking_from_db ) && count( $tracking_from_db ) ) {
					ob_start();
					$this->process_tracking_from_db_trackingmore( $tracking_from_db, $tracking_from_db['tracking_number'], $service_carrier_type, $found_tracking );
					if ( ! $found_tracking ) {
						self::tracking_not_available_message();
					}
					$response['data'] = ob_get_clean();
				} else {
					$response['status'] = 'error';
				}
			} else {
				$tracking_from_db = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_row( $tracking_id );
				if ( is_array( $tracking_from_db ) && count( $tracking_from_db ) ) {
					ob_start();
					$this->process_tracking_from_db( $tracking_from_db, $tracking_from_db['tracking_number'], $service_carrier_type, $found_tracking );
					if ( ! $found_tracking ) {
						if ( ! empty( $tracking_from_db['id'] ) ) {
							VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'] );
						}
					}
					if ( ! $found_tracking ) {
						self::tracking_not_available_message();
					}
					$response['data'] = ob_get_clean();
				} else {
					$response['status'] = 'error';
				}
			}
		} else {
			$response['status'] = 'error';
		}
		wp_send_json( $response );
	}

	public function wp_enqueue_scripts() {
		if ( $this->is_tracking_page() ) {
			add_filter( 'the_content', array( $this, 'track_order_page_content' ) );
			if ( ! wp_style_is( 'vi-wot-frontend-shortcode-track-order-icons' ) ) {
				wp_enqueue_style( 'vi-wot-frontend-shortcode-track-order-icons', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'woo-orders-tracking-icons.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			}
			wp_enqueue_style( 'vi-wot-frontend-shortcode-track-order-css', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'frontend-shortcode-track-order.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			wp_enqueue_style( 'vi-wot-frontend-shortcode-track-order-icon', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'frontend-shipment-icon.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			wp_enqueue_script( 'vi-wot-frontend-shortcode-track-order-js', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'frontend-shortcode-track-order.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			wp_localize_script( 'vi-wot-frontend-shortcode-track-order-js',
				'vi_wot_shortcode_timeline',
				array(
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'tracking_number' => isset( $_GET['tracking_id'] ) ? sanitize_text_field( $_GET['tracking_id'] ) : '',
				)
			);
			$css = '';
			//general
			$css .= $this->add_inline_style(
				array(
					'timeline_track_info_title_alignment',
					'timeline_track_info_title_color',
					'timeline_track_info_title_font_size',
				),
				'.woo-orders-tracking-shortcode-timeline-wrap .woo-orders-tracking-shortcode-timeline-title',
				array(
					'text-align',
					'color',
					'font-size',
				), array(
					'',
					'',
					'px'
				)
			);
			$css .= $this->add_inline_style(
				array(
					'timeline_track_info_status_color',
				),
				'.woo-orders-tracking-shortcode-timeline-wrap .woo-orders-tracking-shortcode-timeline-status-wrap',
				array(
					'color',
				), array(
				'',
			) );
			$css .= $this->add_inline_style(
				array(
					'timeline_track_info_status_background_delivered',
				),
				'.woo-orders-tracking-shortcode-timeline-wrap .woo-orders-tracking-shortcode-timeline-status-wrap.woo-orders-tracking-shortcode-timeline-status-delivered',
				array(
					'background-color',
				), array(
				'',
			) );
			$css .= $this->add_inline_style(
				array(
					'timeline_track_info_status_background_pickup',
				),
				'.woo-orders-tracking-shortcode-timeline-wrap .woo-orders-tracking-shortcode-timeline-status-wrap.woo-orders-tracking-shortcode-timeline-status-pickup',
				array(
					'background-color',
				), array(
				'',
			) );
			$css .= $this->add_inline_style(
				array(
					'timeline_track_info_status_background_transit',
				),
				'.woo-orders-tracking-shortcode-timeline-wrap .woo-orders-tracking-shortcode-timeline-status-wrap.woo-orders-tracking-shortcode-timeline-status-transit',
				array(
					'background-color',
				), array(
				'',
			) );
			$css .= $this->add_inline_style(
				array(
					'timeline_track_info_status_background_pending',
				),
				'.woo-orders-tracking-shortcode-timeline-wrap .woo-orders-tracking-shortcode-timeline-status-wrap.woo-orders-tracking-shortcode-timeline-status-pending',
				array(
					'background-color',
				), array(
				'',
			) );
			$css .= $this->add_inline_style(
				array(
					'timeline_track_info_status_background_alert',
				),
				'.woo-orders-tracking-shortcode-timeline-wrap .woo-orders-tracking-shortcode-timeline-status-wrap.woo-orders-tracking-shortcode-timeline-status-alert',
				array(
					'background-color',
				), array(
				'',
			) );
			/*
			 * template one
			 */
			if ( $this->settings->get_params( 'timeline_track_info_template' ) === '1' ) {
				$css .= $this->add_inline_style(
					array(
						'icon_delivered_color',
					),
					'.woo-orders-tracking-shortcode-timeline-wrap.woo-orders-tracking-shortcode-timeline-wrap-template-one
.woo-orders-tracking-shortcode-timeline-events-wrap
.woo-orders-tracking-shortcode-timeline-event
.woo-orders-tracking-shortcode-timeline-icon-delivered i:before',
					array(
						'color',
					),
					array(
						'',
					),
					array(
						'timeline_track_info_template_one',
					) );
				$css .= $this->add_inline_style(
					array(
						'icon_delivered_color',
					),
					'.woo-orders-tracking-shortcode-timeline-wrap.woo-orders-tracking-shortcode-timeline-wrap-template-one
.woo-orders-tracking-shortcode-timeline-events-wrap
.woo-orders-tracking-shortcode-timeline-event
.woo-orders-tracking-shortcode-timeline-icon-delivered svg circle',
					array(
						'fill',
					), array(
					''
				),
					array(
						'timeline_track_info_template_one'
					)
				);

				$css .= $this->add_inline_style(
					array(
						'icon_pickup_color',
					),
					'.woo-orders-tracking-shortcode-timeline-wrap.woo-orders-tracking-shortcode-timeline-wrap-template-one
.woo-orders-tracking-shortcode-timeline-events-wrap
.woo-orders-tracking-shortcode-timeline-event
.woo-orders-tracking-shortcode-timeline-icon-pickup i:before',
					array(
						'color',
					),
					array(
						''
					),
					array(
						'timeline_track_info_template_one'
					)
				);

				$css .= $this->add_inline_style(
					array(
						'icon_pickup_background',
					),
					'.woo-orders-tracking-shortcode-timeline-wrap.woo-orders-tracking-shortcode-timeline-wrap-template-one
.woo-orders-tracking-shortcode-timeline-events-wrap
.woo-orders-tracking-shortcode-timeline-event
.woo-orders-tracking-shortcode-timeline-icon-pickup ',
					array(
						'background-color',
					),
					array(
						'',
					),
					array(
						'timeline_track_info_template_one'
					) );

				$css .= $this->add_inline_style(
					array(
						'icon_transit_color',
					),
					'.woo-orders-tracking-shortcode-timeline-wrap.woo-orders-tracking-shortcode-timeline-wrap-template-one
.woo-orders-tracking-shortcode-timeline-events-wrap
.woo-orders-tracking-shortcode-timeline-event
.woo-orders-tracking-shortcode-timeline-icon-transit i:before',
					array(
						'color',
					),
					array(
						'',
					),
					array(
						'timeline_track_info_template_one'
					) );

				$css .= $this->add_inline_style(
					array(
						'icon_transit_background',
					),
					'.woo-orders-tracking-shortcode-timeline-wrap.woo-orders-tracking-shortcode-timeline-wrap-template-one
.woo-orders-tracking-shortcode-timeline-events-wrap
.woo-orders-tracking-shortcode-timeline-event
.woo-orders-tracking-shortcode-timeline-icon-transit ',
					array(
						'background-color',
					),
					array(
						'',
					),
					array(
						'timeline_track_info_template_one'
					) );
			}
			$css .= $this->settings->get_params( 'custom_css' );
			wp_add_inline_style( 'vi-wot-frontend-shortcode-track-order-css', $css );
		}
	}

	public function enqueue_google_recaptcha() {
		$recaptcha_version  = $this->settings->get_params( 'tracking_form_recaptcha_version' );
		$recaptcha_site_key = $this->settings->get_params( 'tracking_form_recaptcha_site_key' );
		if ( $recaptcha_version == 2 ) {
			?>
            <script src='<?php echo esc_url( 'https://www.google.com/recaptcha/api.js?hl=' . get_locale() ); ?>&render=explicit'
                    async defer></script>
			<?php
		} else {
			?>
            <script src="<?php echo esc_url( 'https://www.google.com/recaptcha/api.js?hl=' . get_locale() ); ?>&render=<?php echo esc_attr( $recaptcha_site_key ); ?>"></script>
			<?php
		}
	}

	public function shortcode_form_track_order( $atts ) {
		$arr                   = shortcode_atts( array(
			'preview' => '',
		), $atts );
		$service_tracking_page = $this->settings->get_params( 'service_tracking_page' );
		if ( $service_tracking_page && $service_tracking_page_url = get_the_permalink( $service_tracking_page ) && ! wp_script_is( 'vi-wot-frontend-shortcode-form-search-js' ) ) {
			if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
				$default_lang     = apply_filters( 'wpml_default_language', null );
				$current_language = apply_filters( 'wpml_current_language', null );

				if ( $current_language && $current_language !== $default_lang ) {
					$this->language = $current_language;
				}
			} else if ( class_exists( 'Polylang' ) ) {
				$default_lang     = pll_default_language( 'slug' );
				$current_language = pll_current_language( 'slug' );
				if ( $current_language && $current_language !== $default_lang ) {
					$this->language = $current_language;
				}
			}
			if ( ! wp_style_is( 'vi-wot-frontend-shortcode-track-order-icons' ) ) {
				wp_enqueue_style( 'vi-wot-frontend-shortcode-track-order-icons', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'woo-orders-tracking-icons.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			}
			wp_enqueue_style( 'vi-wot-frontend-shortcode-form-search-css', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'frontend-shortcode-form-search.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			$inline_css = $this->add_inline_style( 'tracking_form_button_track_color', '.vi-woocommerce-orders-tracking-form-search .vi-woocommerce-orders-tracking-form-row .vi-woocommerce-orders-tracking-form-search-tracking-number-btnclick', 'color', '' );
			$inline_css .= $this->add_inline_style( 'tracking_form_button_track_bg_color', '.vi-woocommerce-orders-tracking-form-search .vi-woocommerce-orders-tracking-form-row .vi-woocommerce-orders-tracking-form-search-tracking-number-btnclick', 'background-color', '' );
			wp_add_inline_style( 'vi-wot-frontend-shortcode-form-search-css', $inline_css );
			$recaptcha_enable     = $this->settings->get_params( 'tracking_form_recaptcha_enable' );
			$recaptcha_version    = $this->settings->get_params( 'tracking_form_recaptcha_version' );
			$recaptcha_site_key   = $this->settings->get_params( 'tracking_form_recaptcha_site_key' );
			$recaptcha_secret_key = $this->settings->get_params( 'tracking_form_recaptcha_secret_key' );
			$recaptcha_check      = false;
			if ( $recaptcha_enable ) {
				if ( $recaptcha_site_key && $recaptcha_secret_key ) {
					$recaptcha_check = true;
					add_action( 'wp_print_scripts', array( $this, 'enqueue_google_recaptcha' ) );
					if ( $recaptcha_version == 2 ) {
						?>
                        <script src='<?php echo esc_url( 'https://www.google.com/recaptcha/api.js?hl=' . get_locale() ); ?>&render=explicit'
                                async defer></script>
						<?php
					} else {
						?>
                        <script src="<?php echo esc_url( 'https://www.google.com/recaptcha/api.js?hl=' . get_locale() ); ?>&render=<?php echo esc_attr( $recaptcha_site_key ); ?>"></script>
						<?php
					}
				}
			}
			wp_enqueue_script( 'vi-wot-frontend-shortcode-form-search-js', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'frontend-shortcode-form-search.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			wp_localize_script( 'vi-wot-frontend-shortcode-form-search-js', 'vi_wot_frontend_form_search',
				array(
					'ajax_url'                              => admin_url( 'admin-ajax.php' ),
					'track_order_url'                       => $service_tracking_page_url,
					'error_empty_text'                      => esc_html__( 'Please enter your order info to track', 'woocommerce-orders-tracking' ),
					'tracking_form_require_order_id'        => $this->settings->get_params( 'tracking_form_order_id' ) && $this->settings->get_params( 'tracking_form_require_order_id' ) ? 1 : 0,
					'tracking_form_require_email'           => $this->settings->get_params( 'tracking_form_email' ) && $this->settings->get_params( 'tracking_form_require_email' ) ? 1 : 0,
					'tracking_form_require_tracking_number' => $this->settings->get_params( 'tracking_form_require_tracking_number' ),
					'recaptcha_check'                       => $recaptcha_check,
					'recaptcha_site_key'                    => $recaptcha_site_key,
					'recaptcha_version'                     => $recaptcha_version,
					'recaptcha_theme'                       => $this->settings->get_params( 'tracking_form_recaptcha_theme' ),
					'is_preview'                            => $arr['preview'],
				) );
		}
		$permalink_structure = get_option( 'permalink_structure' );
		ob_start();
		?>
        <form action="<?php echo esc_url( get_the_permalink( $this->settings->get_params( 'service_tracking_page' ) ) ) ?>"
              method="get"
              class="vi-woocommerce-orders-tracking-form-search">
			<?php
			if ( ! $permalink_structure ) {
				?>
                <input type="hidden" name="page_id"
                       value="<?php echo esc_attr( isset( $_GET['page_id'] ) ? sanitize_text_field( $_GET['page_id'] ) : '' ) ?>">
				<?php
			}
			wp_nonce_field( 'woo_orders_tracking_nonce_action', 'woo_orders_tracking_nonce', false );
			$input_html   = '';
			$fields_count = 1;
			if ( $arr['preview'] ) {
				$class = 'vi-woocommerce-orders-tracking-form-order-email vi-woocommerce-orders-tracking-hidden';
				if ( $this->settings->get_params( 'tracking_form_email' ) ) {
					$class = 'vi-woocommerce-orders-tracking-form-order-email';
					$fields_count ++;
				}
				ob_start();
				?>
                <div class="<?php echo esc_attr( $class ) ?>">
                    <input type="text" name="order_email" class="vi-woocommerce-orders-tracking-form-order-email-input"
                           placeholder="<?php $this->settings->get_params( 'tracking_form_require_email' ) ? esc_html_e( 'Your email(*required)', 'woocommerce-orders-tracking' ) : esc_html_e( 'Your email', 'woocommerce-orders-tracking' ) ?>"
                           value="<?php echo esc_attr( isset( $_GET['order_email'] ) ? sanitize_text_field( $_GET['order_email'] ) : '' ) ?>">
                </div>
				<?php
				$input_html .= ob_get_clean();
				$class      = 'vi-woocommerce-orders-tracking-form-order-id vi-woocommerce-orders-tracking-hidden';
				if ( $this->settings->get_params( 'tracking_form_order_id' ) ) {
					$class = 'vi-woocommerce-orders-tracking-form-order-id';
					$fields_count ++;
				}
				ob_start();
				?>
                <div class="<?php echo esc_attr( $class ) ?>">
                    <input type="text" name="order_id" class="vi-woocommerce-orders-tracking-form-order-id-input"
                           placeholder="<?php $this->settings->get_params( 'tracking_form_require_order_id' ) ? esc_html_e( 'Order ID(*required)', 'woocommerce-orders-tracking' ) : esc_html_e( 'Order ID', 'woocommerce-orders-tracking' ) ?>"
                           value="<?php echo esc_attr( isset( $_GET['order_id'] ) ? sanitize_text_field( $_GET['order_id'] ) : '' ) ?>">
                </div>
				<?php
				$input_html .= ob_get_clean();
			} else {
				if ( $this->settings->get_params( 'tracking_form_email' ) ) {
					$fields_count ++;
					ob_start();
					?>
                    <div class="vi-woocommerce-orders-tracking-form-order-email">
                        <input type="text" name="order_email"
                               class="vi-woocommerce-orders-tracking-form-order-email-input"
                               placeholder="<?php $this->settings->get_params( 'tracking_form_require_email' ) ? esc_html_e( 'Your email(*required)', 'woocommerce-orders-tracking' ) : esc_html_e( 'Your email', 'woocommerce-orders-tracking' ) ?>"
                               value="<?php echo esc_attr( isset( $_GET['order_email'] ) ? sanitize_text_field( $_GET['order_email'] ) : '' ) ?>">
                    </div>
					<?php
					$input_html .= ob_get_clean();
				}
				if ( $this->settings->get_params( 'tracking_form_order_id' ) ) {
					$fields_count ++;
					ob_start();
					?>
                    <div class="vi-woocommerce-orders-tracking-form-order-id">
                        <input type="text" name="order_id" class="vi-woocommerce-orders-tracking-form-order-id-input"
                               placeholder="<?php $this->settings->get_params( 'tracking_form_require_order_id' ) ? esc_html_e( 'Order ID(*required)', 'woocommerce-orders-tracking' ) : esc_html_e( 'Order ID', 'woocommerce-orders-tracking' ) ?>"
                               value="<?php echo esc_attr( isset( $_GET['order_id'] ) ? sanitize_text_field( $_GET['order_id'] ) : '' ) ?>">
                    </div>
					<?php
					$input_html .= ob_get_clean();
				}
			}
			ob_start();
			?>
            <div class="vi-woocommerce-orders-tracking-form-row">
                <input type="search"
                       id="vi-woocommerce-orders-tracking-form-search-tracking-number"
                       class="vi-woocommerce-orders-tracking-form-search-tracking-number"
                       placeholder="<?php $this->settings->get_params( 'tracking_form_require_tracking_number' ) ? esc_html_e( 'Tracking number(*required)', 'woocommerce-orders-tracking' ) : esc_html_e( 'Tracking number', 'woocommerce-orders-tracking' ) ?>"
                       name="tracking_id"
                       autocomplete="off"
                       value="<?php echo esc_attr( isset( $_GET['tracking_id'] ) ? sanitize_text_field( $_GET['tracking_id'] ) : '' ) ?>">
                <button type="submit"
                        class="vi-woocommerce-orders-tracking-form-search-tracking-number-btnclick woo_orders_tracking_icons-search-1"><?php esc_html_e( $this->settings->get_params( 'tracking_form_button_track_title', '', $this->language ) ) ?></button>
            </div>
			<?php
			$input_html .= ob_get_clean();
			?>
            <div class="vi-woocommerce-orders-tracking-form-inputs <?php echo esc_attr( 'vi-woocommerce-orders-tracking-form-inputs-' . $fields_count ) ?>">
				<?php
				echo wp_kses( $input_html, VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::extend_post_allowed_html() );
				?>
            </div>
			<?php
			$recaptcha_v2_enable = false;
			if ( $this->settings->get_params( 'tracking_form_recaptcha_enable' ) && $this->settings->get_params( 'tracking_form_recaptcha_site_key' ) && $this->settings->get_params( 'tracking_form_recaptcha_secret_key' ) ) {
				if ( $this->settings->get_params( 'tracking_form_recaptcha_version' ) == 2 ) {
					$recaptcha_v2_enable = true;
					$recaptcha_class     = 'vi-woocommerce-orders-tracking-recaptcha-field';
				} else {
					$recaptcha_class = 'vi-woocommerce-orders-tracking-recaptcha-field vi-woocommerce-orders-tracking-hidden';
				}
				?>
                <div class="<?php echo esc_attr( $recaptcha_class ) ?>">
                    <div class="vi-woocommerce-orders-tracking-recaptcha"></div>
                    <input type="hidden" value=""
                           class="vi-woocommerce-orders-tracking-g-validate-response" name="recaptcha">
                </div>
				<?php
			}
			?>
            <div class="vi-woocommerce-orders-tracking-form-message vi-woocommerce-orders-tracking-hidden">
				<?php
				if ( $fields_count > 1 ) {
					esc_html_e( 'Please enter all required information to track your order.', 'woocommerce-orders-tracking' );
				} else {
					if ( $recaptcha_v2_enable ) {
						esc_html_e( 'Please enter your tracking number and verify the reCaptcha to track.', 'woocommerce-orders-tracking' );
					} else {
						esc_html_e( 'Please enter your tracking number to track.', 'woocommerce-orders-tracking' );
					}
				}
				?>
            </div>
        </form>
		<?php
		return ob_get_clean();
	}

	public function register_example_widget() {
		register_widget( 'VI_WOOCOMMERCE_ORDERS_TRACKING_WIDGET' );
	}

	protected function is_tracking_page() {
		$service_tracking_page = $this->settings->get_params( 'service_tracking_page' );
		$return                = false;
		if ( $service_tracking_page ) {
			$return = is_page( $service_tracking_page );
		}

		return $return;
	}

	public function track_order_page_content( $content ) {
		if ( is_customize_preview() ) {
			ob_start();
			echo do_shortcode( '[vi_wot_form_track_order preview="true"]' );
			echo do_shortcode( '[vi_wot_track_order_timeline tracking_code = "customize_preview" preview="true"]' );
			$html    = ob_get_clean();
			$content .= $html;

			return ent2ncr( $content );
		} elseif ( $this->settings->get_params( 'service_carrier_enable' ) ) {
			ob_start();
			echo do_shortcode( '[vi_wot_form_track_order]' );
			$tracking_code = isset( $_GET['tracking_id'] ) ? sanitize_text_field( $_GET['tracking_id'] ) : '';
			?>
            <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-container' ) ); ?>"
                 data-tracking_code="<?php echo esc_attr( $tracking_code ) ?>">
				<?php
				if ( isset( $_GET['woo_orders_tracking_nonce'] ) && wp_verify_nonce( $_GET['woo_orders_tracking_nonce'], 'woo_orders_tracking_nonce_action' ) ) {
					$verify               = true;
					$recaptcha_enable     = $this->settings->get_params( 'tracking_form_recaptcha_enable' );
					$recaptcha_version    = $this->settings->get_params( 'tracking_form_recaptcha_version' );
					$recaptcha_site_key   = $this->settings->get_params( 'tracking_form_recaptcha_site_key' );
					$recaptcha_secret_key = $this->settings->get_params( 'tracking_form_recaptcha_secret_key' );
					$recaptcha_response   = isset( $_GET['recaptcha'] ) ? sanitize_text_field( $_GET['recaptcha'] ) : '';
					if ( $recaptcha_enable && $recaptcha_site_key && $recaptcha_secret_key ) {
						if ( $recaptcha_response ) {
							$url  = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret_key . '&response=' . $recaptcha_response;
							$curl = curl_init();
							curl_setopt_array( $curl, array(
								CURLOPT_URL            => $url,
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_ENCODING       => "",
								CURLOPT_MAXREDIRS      => 10,
								CURLOPT_TIMEOUT        => 30,
								CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
								CURLOPT_CUSTOMREQUEST  => "POST",
								CURLOPT_POSTFIELDS     => '{}',
								CURLOPT_HTTPHEADER     => array(
									"content-type: application/json"
								),
							) );

							$response = curl_exec( $curl );
							$err      = curl_error( $curl );
							curl_close( $curl );
							if ( $err ) {
								$verify = false;
							} else {
								$data = vi_wot_json_decode( $response );
								if ( $recaptcha_version == 2 ) {
									if ( ! $data['success'] ) {
										$verify = false;
									}
								} else {
									$g_score = isset( $data['score'] ) ? $data['score'] : 0;
									if ( $g_score < 0.5 ) {
										$verify = false;
									}
								}
							}
						} else {
							$verify = false;
						}
					}
					if ( $verify ) {
						echo do_shortcode( "[vi_wot_track_order_timeline tracking_code = {$tracking_code}]" );
					} else {
						?>
                        <div><?php esc_html_e( 'Google reCAPTCHA verification failed', 'woocommerce-orders-tracking' ) ?></div>
						<?php
					}
				} else {
					?>
                    <div class="vi-woocommerce-orders-tracking-message-empty-nonce"><?php esc_html_e( 'Please click button Track to track your order.', 'woocommerce-orders-tracking' ) ?></div>
					<?php
				}

				?>
            </div>
			<?php
			$html    = ob_get_clean();
			$content .= $html;

			return ent2ncr( $content );
		}

		return $content;
	}

	/**
	 * @param $name
	 * @param bool $set_name
	 *
	 * @return string
	 */
	public static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::set( $name, $set_name );
	}

	/**
	 *
	 */
	public function init() {
		add_shortcode( 'vi_wot_track_order_timeline', array( $this, 'shortcode_track_order_timeline' ) );
	}

	/**
	 * @throws Exception
	 */
	public function submit_carrier() {
		$_wpnonce         = isset( $_GET['woo_orders_tracking_nonce'] ) ? sanitize_text_field( $_GET['woo_orders_tracking_nonce'] ) : '';
		$origincp         = isset( $_GET['origincp'] ) ? sanitize_text_field( $_GET['origincp'] ) : '';
		$tracking_code    = isset( $_GET['tracking_code'] ) ? sanitize_text_field( $_GET['tracking_code'] ) : '';
		$tracking_from_db = isset( $_GET['tracking_from_db'] ) ? stripslashes_deep( $_GET['tracking_from_db'] ) : array();
		$carrier_name     = isset( $_GET['carrier_name'] ) ? sanitize_text_field( $_GET['carrier_name'] ) : '';
		$display_name     = isset( $_GET['display_name'] ) ? sanitize_text_field( $_GET['display_name'] ) : '';
		$response         = array(
			'status' => 'success',
			'data'   => '',
		);
		$carrier_service  = $tracking_from_db['carrier_service'];
		if ( wp_verify_nonce( $_wpnonce, 'woo_orders_tracking_nonce_action' ) && $origincp && $tracking_code ) {
			ob_start();
			if ( $tracking_from_db['id'] ) {
				if ( $carrier_service === 'trackingmore' ) {
					$tracking_from_db = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::get_row( $tracking_from_db['id'] );
				} else {
					$tracking_from_db = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_row( $tracking_from_db['id'] );
				}
			} else {
				if ( $carrier_service === 'trackingmore' ) {
					$tracking_from_db = array_merge( VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::get_cols(), $tracking_from_db );
				} else {
					$tracking_from_db = array_merge( VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_cols(), $tracking_from_db );
				}
			}
			self::cainiao_get_track_info( $tracking_code, $found_tracking, $origincp, $tracking_from_db, $carrier_service, $carrier_name, $display_name );
			if ( ! $found_tracking ) {
				self::tracking_not_available_message();
			}
			$response['data'] = ob_get_clean();
		} else {
			$response['status'] = 'error';
		}
		wp_send_json( $response );
	}

	public function shortcode_track_order_timeline( $atts ) {
		$arr           = shortcode_atts( array(
			'tracking_code' => '',
			'preview'       => '',
		), $atts );
		$tracking_code = $arr['tracking_code'];
		if ( $tracking_code === 'customize_preview' && $arr['preview'] === "true" ) {
			return $this->get_template( 'customize', 'require' );
		}

		return $this->get_template( 'shortcode_timeline', 'function', $tracking_code );
	}

	public static function display_timeline( $data, $tracking_code ) {
		$settings        = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
		$sort_event      = $settings->get_params( 'timeline_track_info_sort_event' );
		$template        = $settings->get_params( 'timeline_track_info_template' );
		$title           = $settings->get_params( 'timeline_track_info_title' );
		$date_format     = $settings->get_params( 'timeline_track_info_date_format' );
		$time_format     = $settings->get_params( 'timeline_track_info_time_format' );
		$datetime_format = $date_format . ' ' . $time_format;
		$status          = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $data['status'] );
		$status_text     = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_status_text( $status );
		$track_info      = apply_filters( 'woo_orders_tracking_timeline_track_info', $data['tracking'], $tracking_code, $status );
		$carrier_name    = $data['carrier_name'];
		$title           = str_replace(
			array(
				'{carrier_name}',
				'{tracking_number}',
			),
			array(
				$carrier_name,
				strtoupper( $tracking_code )
			),
			$title
		);
		if ( is_array( $track_info ) && count( $track_info ) ) {
			if ( $sort_event === 'oldest_to_most_recent' ) {
				krsort( $track_info );
				$track_info = array_values( $track_info );
			}
			$template_class = '';
			$timeline_html  = '';

			switch ( $template ) {
				case '1':
					$template_class = 'template-one';
					$timeline_html  = self::get_timeline_html_1( $track_info, $datetime_format, $sort_event, $template );
					break;
				case '2':
					$template_class = 'template-two';
					$timeline_html  = self::get_timeline_html_2( $track_info, $datetime_format );
					break;
				default:
			}
			?>
            <div class="<?php echo esc_attr( self::set( array(
				'shortcode-timeline-wrap-' . $template_class,
				'shortcode-timeline-wrap-' . $sort_event,
				'shortcode-timeline-wrap'
			) ) ) ?>">
				<?php
				if ( $title ) {
					?>
                    <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-title' ) ) ?>">
                        <span><?php echo esc_html( $title ) ?></span>
                    </div>
					<?php
				}
				?>
                <div class="<?php echo esc_attr( self::set( array(
					'shortcode-timeline-status-wrap',
					'shortcode-timeline-status-' . $status
				) ) ) ?>">
					<?php echo esc_html( $status_text ); ?>
                </div>
				<?php
				if ( ! empty( $data['modified_at'] ) ) {
					?>
                    <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-last-update' ) ) ?>">
						<?php
						if ( $status !== 'delivered' && ! empty( $data['est_delivery_date'] ) && strtotime( $data['est_delivery_date'] ) > time() ) {
							?>
                            <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-estimated-delivery-date' ) ) ?>">
								<?php esc_html_e( 'Estimated Delivery Date: ', 'woocommerce-orders-tracking' ) ?>
                                <span><?php echo esc_html( date_format( date_create( $data['est_delivery_date'] ), $datetime_format ) ) ?></span>
                            </div>
							<?php
						}
						?>
                        <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-last-update-text' ) ) ?>"><?php esc_html_e( 'Last Updated: ', 'woocommerce-orders-tracking' ) ?>
                            <span><?php echo esc_html( date_format( date_create( $data['modified_at'] ), $datetime_format ) ) ?></span>
                        </div>
                    </div>
					<?php
				}
				echo apply_filters( 'woo_orders_tracking_timeline_html', $timeline_html, $status, $tracking_code, $carrier_name, $track_info );
				?>
            </div>
			<?php
		} else {
			self::tracking_not_available_message();
		}
	}

	public static function get_timeline_html_1( $track_info, $datetime_format, $sort_event, $template ) {
		ob_start();
		$track_info_count = count( $track_info );
		$event_no         = $sort_event === 'oldest_to_most_recent' ? 1 : $track_info_count;
		?>
        <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-events-wrap' ) ); ?>">
			<?php
			for ( $i = 0; $i < $track_info_count; $i ++ ) {
				$event_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $track_info[ $i ]['status'] );
				?>
                <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-event' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( array(
						'shortcode-timeline-icon',
						'shortcode-timeline-icon-' . $event_status
					) ) ) ?>"
                         title="<?php echo esc_attr( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_status_text( $event_status ) ) ?>">
						<?php
						echo wp_kses_post( self::get_icon_status( $event_status, $template ) );
						?>
                    </div>
                    <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-content-wrap' ) ) ?>">
                        <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-content' ) ) ?>">
                            <span class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-des' ) ) ?>">
                                <?php echo esc_html( "$event_no. {$track_info[ $i ]['description']}" ) ?>
                            </span>
                            <div>
                                <span class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-location' ) ) ?>">
                                    <?php echo esc_html( trim( $track_info[ $i ]['location'], ' ' ) ) ?>
                                </span>
                                <span class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-time' ) ) ?>">
                                    <?php echo esc_html( date_format( date_create( $track_info[ $i ]['time'] ), $datetime_format ) ); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
				if ( $sort_event === 'oldest_to_most_recent' ) {
					$event_no ++;
				} else {
					$event_no --;
				}
			}
			?>
        </div>
		<?php
		return ob_get_clean();
	}

	public static function get_timeline_html_2( $track_info, $datetime_format ) {
		ob_start();
		$group_event      = '';
		$track_info_count = count( $track_info );
		?>
        <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-events-wrap' ) ); ?>">
			<?php
			for ( $i = 0; $i < count( $track_info ); $i ++ ) {
				ob_start();
				$event_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $track_info[ $i ]['status'] );
				?>
                <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-event' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( array(
						'shortcode-timeline-icon',
						'shortcode-timeline-icon-' . $event_status
					) ) ) ?>">
                    </div>
                    <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-content-wrap' ) ) ?>">
                        <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-content-date' ) ) ?>">
							<?php
							echo esc_html( date_format( date_create( $track_info[ $i ]['time'] ), $datetime_format ) )
							?>
                        </div>
                        <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-content-des-wrap' ) ) ?>">
                            <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-content-des' ) ) ?>">
								<?php echo esc_html( $track_info[ $i ]['description'] ) ?>
                            </div>
                            <div class="<?php echo esc_attr( self::set( 'shortcode-timeline-event-location' ) ) ?>">
								<?php echo esc_html( trim( $track_info[ $i ]['location'], ' ' ) ) ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
				$group_event .= ob_get_clean();
				if ( $i < $track_info_count - 1 ) {
					if ( strtotime( date( 'Y-m-d', strtotime( $track_info[ $i ]['time'] ) ) ) !== strtotime( date( 'Y-m-d', strtotime( $track_info[ $i + 1 ]['time'] ) ) ) ) {
						?>
                        <div class="woo-orders-tracking-shortcode-timeline-events-group"><?php echo wp_kses_post( $group_event ) ?></div>
						<?php
						$group_event = '';
					}
				} else {
					?>
                    <div class="woo-orders-tracking-shortcode-timeline-events-group"><?php echo wp_kses_post( $group_event ) ?></div>
					<?php
					$group_event = '';
				}
			}
			?>
        </div>
		<?php
		return ob_get_clean();
	}

	public static function get_shipping_country_by_order_id( $order_id ) {
		$shipping_country = get_post_meta( $order_id, '_shipping_country', true );
		if ( ! $shipping_country ) {
			$shipping_country = get_post_meta( $order_id, '_billing_country', true );
		}

		return $shipping_country;
	}

	/**
	 * @param $tracking_code
	 *
	 * @throws Exception
	 */
	public function shortcode_timeline( $tracking_code ) {
		$order_email = isset( $_GET['order_email'] ) ? sanitize_email( $_GET['order_email'] ) : '';
		$order_id    = isset( $_GET['order_id'] ) ? sanitize_text_field( $_GET['order_id'] ) : '';

		$tracking_form_email     = $this->settings->get_params( 'tracking_form_email' );
		$require_tracking_number = $this->settings->get_params( 'tracking_form_require_tracking_number' );
		$require_email           = $this->settings->get_params( 'tracking_form_require_email' );
		$tracking_form_order_id  = $this->settings->get_params( 'tracking_form_order_id' );
		$require_order_id        = $this->settings->get_params( 'tracking_form_require_order_id' );
		$change_order_status     = $this->settings->get_params( 'change_order_status' );
		if ( ( $require_tracking_number && ! $tracking_code ) || ( $require_email && ! $order_email ) || ( $require_order_id && ! $order_id ) ) {
			?>
            <div class="vi-woocommerce-orders-tracking-message-empty-nonce"><?php esc_html_e( 'Please enter all required information to track your order.', 'woocommerce-orders-tracking' ) ?></div>
			<?php
		} else {
			if ( ! $tracking_form_email && ! $require_email ) {
				$order_email = '';
			}
			if ( ! $tracking_form_order_id && ! $require_order_id ) {
				$order_id = '';
			}
			if ( ! $tracking_code && ! $order_id && ! $order_email ) {
				?>
                <div class="vi-woocommerce-orders-tracking-message-empty-nonce"><?php esc_html_e( 'Please enter your tracking number to track your order.', 'woocommerce-orders-tracking' ) ?></div>
				<?php
			} else {
				self::$query_tracking = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::search_order_item_by_tracking_number( $tracking_code, $order_id, $order_email );
//				print_r(self::$query_tracking);
//				die;
				if ( count( self::$query_tracking ) ) {
					$order_ids            = array_column( self::$query_tracking, 'order_id' );
					$service_carrier_type = $this->settings->get_params( 'service_carrier_type' );
					$found_tracking       = false;
					if ( $service_carrier_type === 'trackingmore' ) {
						$tracking_from_db       = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::get_rows_by_tracking_number_carrier_pairs( array_column( self::$query_tracking, 'tracking_number_carrier_pair' ) );
						$tracking_from_db_count = count( $tracking_from_db );
						if ( $tracking_from_db_count === 1 ) {
							$tracking_from_db = $tracking_from_db[0];
							$tracking_code    = $tracking_from_db['tracking_number'];
							$carrier          = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
							$carrier_name     = $tracking_from_db['carrier_name'];
							$display_name     = $carrier_name;
							if ( is_array( $carrier ) && count( $carrier ) ) {
								$carrier_name = $carrier['name'];
								$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
							}
							if ( $tracking_from_db['modified_at'] === null ) {
								self::cainiao_search_tracking( $tracking_code, $found_tracking, $tracking_from_db, $service_carrier_type, $tracking_from_db['carrier_name'], $display_name );
							} else {
								$this->process_tracking_from_db_trackingmore( $tracking_from_db, $tracking_code, $service_carrier_type, $found_tracking );
							}
						} else if ( $tracking_from_db_count > 1 ) {
							$multiple_tracking = $this->process_multiple_tracking( $tracking_from_db, $found_tracking );
							if ( $found_tracking ) {
								if ( $multiple_tracking ) {
									echo $multiple_tracking;
								} else {
									$tracking_code = $tracking_from_db['tracking_number'];
									$carrier       = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
									$carrier_name  = $tracking_from_db['carrier_name'];
									$display_name  = $carrier_name;
									if ( is_array( $carrier ) && count( $carrier ) ) {
										$carrier_name = $carrier['name'];
										$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
									}
									if ( $tracking_from_db['modified_at'] === null ) {
										self::cainiao_search_tracking( $tracking_code, $found_tracking, $tracking_from_db, $service_carrier_type, $tracking_from_db['carrier_name'], $display_name );
									} else {
										$this->process_tracking_from_db_trackingmore( $tracking_from_db, $tracking_code, $service_carrier_type, $found_tracking );
									}
								}
							}
						} else if ( $this->settings->get_params( 'service_add_tracking_if_not_exist' ) ) {
							$current_tracking = self::$query_tracking[0];
							if ( ! $tracking_code ) {
								$tracking_code = $current_tracking['tracking_number'];
							}
							$tracking_from_db                          = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::get_cols();
							$tracking_from_db['order_id']              = $current_tracking['order_id'];
							$tracking_from_db['tracking_number']       = $tracking_code;
							$item_tracking_data                        = $current_tracking['meta_value'];
							$tracking_from_db['shipping_country_code'] = self::get_shipping_country_by_order_id( $current_tracking['order_id'] );
							if ( $item_tracking_data ) {
								$item_tracking_data             = vi_wot_json_decode( $item_tracking_data );
								$current_tracking_data          = array_pop( $item_tracking_data );
								$carrier_name                   = $current_tracking_data['carrier_name'];
								$display_name                   = $carrier_name;
								$carrier_slug                   = $current_tracking_data['carrier_slug'];
								$tracking_from_db['carrier_id'] = $carrier_slug;
								$carrier                        = $this->settings->get_shipping_carrier_by_slug( $carrier_slug );
								if ( is_array( $carrier ) && count( $carrier ) ) {
									$carrier_name       = $carrier['name'];
									$display_name       = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
									$tracking_more_slug = empty( $carrier['tracking_more_slug'] ) ? VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE::get_carrier_slug_by_name( $carrier_name ) : $carrier['tracking_more_slug'];
									if ( ! empty( $tracking_more_slug ) ) {
										$service_carrier_api_key = $this->settings->get_params( 'service_carrier_api_key' );
										if ( $service_carrier_api_key ) {
											$trackingMore = new VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE( $service_carrier_api_key );
											$track_data   = $trackingMore->create_tracking( $tracking_code, $tracking_more_slug, $current_tracking['order_id'] );
											$status       = '';
											$track_info   = '';
											$description  = '';
											if ( $track_data['status'] === 'success' ) {
												$status = $track_data['data']['status'];
												VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::insert( $current_tracking['order_id'], $tracking_code, $status, $carrier_slug, $carrier_name, $tracking_from_db['shipping_country_code'], $track_info, '' );
											} else {
												if ( $track_data['code'] === 4016 ) {
													/*Tracking exists*/
													$track_data = $trackingMore->get_tracking( $tracking_code, $tracking_more_slug );
													if ( $track_data['status'] === 'success' ) {
														if ( count( $track_data['data'] ) ) {
															$track_info                           = json_encode( $track_data['data'] );
															$last_event                           = array_shift( $track_data['data'] );
															$status                               = $last_event['status'];
															$description                          = $last_event['description'];
															$current_tracking_data['status']      = $last_event['status'];
															$current_tracking_data['last_update'] = time();
															$found_tracking                       = true;
															self::display_timeline( array(
																'status'            => $status,
																'tracking'          => $track_data['data'],
																'last_event'        => $last_event,
																'carrier_name'      => $display_name,
																'est_delivery_date' => '',
																'modified_at'       => date( 'Y-m-d H:i:s' ),
															), $tracking_code );
															$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $last_event['status'] );
															if ( $convert_status !== VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) || $track_info !== $tracking_from_db['track_info'] ) {
																$tracking_change = 1;
																VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_items_tracking_status( $tracking_code, $tracking_from_db['carrier_id'], $last_event['status'], $change_order_status );
															}
														}
													}
													VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::insert( $current_tracking['order_id'], $tracking_code, $status, $carrier_slug, $carrier_name, $tracking_from_db['shipping_country_code'], $track_info, $description );
												}
											}
										}
									}
								}
								if ( ! $found_tracking ) {
									self::cainiao_search_tracking( $tracking_code, $found_tracking, $tracking_from_db, $service_carrier_type, $carrier_name, $display_name );
								}
							}
						} else {
							$tracking_from_db = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_rows_by_tracking_number_carrier_pairs( array_column( self::$query_tracking, 'tracking_number_carrier_pair' ) );
							$this->track_with_cainiao( $tracking_code, $tracking_from_db, $service_carrier_type, $found_tracking );
						}

					} elseif ( $service_carrier_type === 'cainiao' ) {
						/**
						 * Search tracking in db
						 */
						$tracking_from_db = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_rows_by_tracking_number_carrier_pairs( array_column( self::$query_tracking, 'tracking_number_carrier_pair' ), $service_carrier_type );
//                        print_r($tracking_from_db);
//                        die;
						$tracking_from_db_count = count( $tracking_from_db );
						if ( $tracking_from_db_count === 1 ) {
							$this->track_with_cainiao( $tracking_code, $tracking_from_db, $service_carrier_type, $found_tracking );
						} else if ( $tracking_from_db_count > 1 ) {
							$multiple_tracking = $this->process_multiple_tracking( $tracking_from_db, $found_tracking );
							if ( $found_tracking ) {
								if ( $multiple_tracking ) {
									echo $multiple_tracking;
								} else {
									$tracking_code = $tracking_from_db['tracking_number'];
									$this->track_with_cainiao( $tracking_code, $tracking_from_db, $service_carrier_type, $found_tracking );
								}
							}
						} else {
							if ( ! count( $tracking_from_db ) ) {
								$current_tracking                    = self::$query_tracking[0];
								$tracking_from_db                    = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_cols();
								$tracking_from_db['order_id']        = $current_tracking['order_id'];
								$tracking_from_db['tracking_number'] = $tracking_code;
								$item_tracking_data                  = $current_tracking['meta_value'];
								if ( $item_tracking_data ) {
									$item_tracking_data             = vi_wot_json_decode( $item_tracking_data );
									$current_tracking_data          = array_pop( $item_tracking_data );
									$carrier_slug                   = $current_tracking_data['carrier_slug'];
									$tracking_from_db['carrier_id'] = $carrier_slug;
									$tracking_from_db['id']         = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_code, $current_tracking['order_id'], $carrier_slug, $service_carrier_type, '', '', '', '', '' );
								}
							}
							$this->track_with_cainiao( $tracking_code, $tracking_from_db, $service_carrier_type, $found_tracking );
						}
					} else {
						/**
						 * Search tracking in db
						 */
						$carrier_id             = isset( $_GET['carrier_id'] ) ? sanitize_text_field( $_GET['carrier_id'] ) : '';
						$tracking_from_db       = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_rows_by_tracking_number_carrier_pairs( array_column( self::$query_tracking, 'tracking_number_carrier_pair' ), $service_carrier_type );
						$tracking_from_db_count = count( $tracking_from_db );
						if ( $tracking_from_db_count === 1 ) {
							$tracking_from_db = $tracking_from_db[0];
							$tracking_code    = $tracking_from_db['tracking_number'];
							if ( $tracking_from_db['modified_at'] === null ) {
								$carrier      = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
								$carrier_name = $carrier_id;
								$display_name = $carrier_name;
								if ( is_array( $carrier ) && count( $carrier ) ) {
									$carrier_name = $carrier['name'];
									$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
								}
								self::cainiao_search_tracking( $tracking_code, $found_tracking, $tracking_from_db, $service_carrier_type, $carrier_name, $display_name );
							} else {
								$this->process_tracking_from_db( $tracking_from_db, $tracking_code, $service_carrier_type, $found_tracking );
							}
						} elseif ( $tracking_from_db_count > 1 ) {
							$multiple_tracking = $this->process_multiple_tracking( $tracking_from_db, $found_tracking );
							if ( $found_tracking ) {
								if ( $multiple_tracking ) {
									echo $multiple_tracking;
								} else {
									$tracking_code = $tracking_from_db['tracking_number'];
									if ( $tracking_from_db['modified_at'] === null ) {
										$carrier      = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
										$carrier_name = $carrier_id;
										$display_name = $carrier_name;
										if ( is_array( $carrier ) && count( $carrier ) ) {
											$carrier_name = $carrier['name'];
											$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
										}
										self::cainiao_search_tracking( $tracking_code, $found_tracking, $tracking_from_db, $service_carrier_type, $carrier_name, $display_name );
									} else {
										$this->process_tracking_from_db( $tracking_from_db, $tracking_code, $service_carrier_type, $found_tracking );
									}
								}
							}
						} else if ( $this->settings->get_params( 'service_add_tracking_if_not_exist' ) ) {
							/**
							 * Tracking from old orders but not exist in tracking table
							 */
							$current_tracking                    = self::$query_tracking[0];
							$tracking_from_db                    = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_cols();
							$tracking_from_db['order_id']        = $current_tracking['order_id'];
							$tracking_from_db['tracking_number'] = $tracking_code;
							$item_tracking_data                  = $current_tracking['meta_value'];
							if ( $item_tracking_data ) {
								$item_tracking_data             = vi_wot_json_decode( $item_tracking_data );
								$current_tracking_data          = array_pop( $item_tracking_data );
								$carrier_name                   = $current_tracking_data['carrier_name'];
								$carrier_slug                   = $current_tracking_data['carrier_slug'];
								$tracking_from_db['carrier_id'] = $carrier_slug;
								$carrier                        = $this->settings->get_shipping_carrier_by_slug( $carrier_slug );
								$display_name                   = $carrier_name;
								if ( is_array( $carrier ) && count( $carrier ) ) {
									$carrier_name = $carrier['name'];
									$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
								}
								$service_carrier_api_key = $this->settings->get_params( 'service_carrier_api_key' );
								if ( $service_carrier_api_key ) {
									switch ( $service_carrier_type ) {
										case 'aftership':
											$find_carrier = VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP::get_carrier_slug_by_name( $carrier_name );
											$aftership    = new VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP( $service_carrier_api_key );
											$track_data   = $aftership->create( $tracking_code, $find_carrier, $current_tracking['order_id'] );
											if ( $track_data['status'] === 'success' ) {
												$status                 = $track_data['data']['tag'];
												$tracking_from_db['id'] = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_code, $current_tracking['order_id'], $carrier_slug, $service_carrier_type, $status, '', '', $track_data['est_delivery_date'], '' );
											} elseif ( $track_data['code'] === 4003 ) {
												$tracking_from_db['id'] = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_code, $current_tracking['order_id'], $carrier_slug, $service_carrier_type, '', '', '', '', '' );
											}
											break;
										case 'easypost':
											$easyPost     = new VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST( $service_carrier_api_key );
											$find_carrier = VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST::get_carrier_slug_by_name( $carrier_name );
											$track_data   = $easyPost->create( $tracking_code, $find_carrier );
											if ( $track_data['status'] === 'success' ) {
												if ( count( $track_data['data'] ) ) {
													$track_info                           = json_encode( $track_data['data'] );
													$last_event                           = array_shift( $track_data['data'] );
													$status                               = $last_event['status'];
													$current_tracking_data['status']      = $last_event['status'];
													$current_tracking_data['last_update'] = time();
													$tracking_from_db['id']               = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_code, $current_tracking['order_id'], $carrier_slug, $service_carrier_type, $status, $track_info, $last_event['description'], $track_data['est_delivery_date'] );
													$found_tracking                       = true;
													self::display_timeline( array(
														'status'            => $status,
														'tracking'          => $track_data['data'],
														'last_event'        => $last_event,
														'carrier_name'      => $display_name,
														'est_delivery_date' => '',
														'modified_at'       => date( 'Y-m-d H:i:s' ),
													), $tracking_code );
													$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $last_event['status'] );
													if ( $convert_status !== VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) || $track_info !== $tracking_from_db['track_info'] ) {
														$tracking_change = 1;
														VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_items_tracking_status( $tracking_code, $tracking_from_db['carrier_id'], $last_event['status'], $change_order_status );
													}
												} else {
													$tracking_from_db['id'] = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_code, $current_tracking['order_id'], $carrier_slug, $service_carrier_type, '', '', '', '' );
												}
											}
											break;
										default:
									}
								}
								if ( ! $found_tracking ) {
									self::cainiao_search_tracking( $tracking_code, $found_tracking, $tracking_from_db, $service_carrier_type, $carrier_name, $display_name );
								}
							}
						} else {
							$tracking_from_db = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_rows_by_tracking_number_carrier_pairs( array_column( self::$query_tracking, 'tracking_number_carrier_pair' ) );
							$this->track_with_cainiao( $tracking_code, $tracking_from_db, $service_carrier_type, $found_tracking );
						}
						if ( ! $found_tracking ) {
							if ( ! empty( $tracking_from_db['id'] ) ) {
								VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'] );
							}
						}
					}

					if ( ! $found_tracking ) {
						self::tracking_not_available_message();
					}
				} else {
					self::get_not_found_text();
				}
			}
		}
	}

	/**
	 * @param $tracking_from_db
	 * @param $found_tracking
	 *
	 * @return false|string
	 */
	public function process_multiple_tracking( &$tracking_from_db, &$found_tracking ) {
		$return                        = '';
		$temp_html                     = '';
		$used_db                       = array();
		$tracking_numbers              = array();
		$tracking_number_carrier_pairs = array();
		foreach ( $tracking_from_db as $key => $item ) {
			$carrier = $this->settings->get_shipping_carrier_by_slug( $item['carrier_id'] );
			if ( is_array( $carrier ) && count( $carrier ) ) {
				$display_name                 = empty( $carrier['display_name'] ) ? $carrier['name'] : $carrier['display_name'];
				$tracking_number_carrier_pair = "{$item['tracking_number']}|{$carrier['slug']}";
				if ( ! in_array( $tracking_number_carrier_pair, $tracking_number_carrier_pairs ) ) {
					$used_db                         = $item;
					$tracking_number_carrier_pairs[] = $tracking_number_carrier_pair;
					$tracking_numbers[]              = $item['tracking_number'];
					ob_start();
					?>
                    <div class="<?php echo esc_attr( self::set( 'multiple-carriers-select-link' ) ) ?>"
                         data-tracking_id="<?php echo esc_attr( $item['id'] ) ?>"
                         data-woo_orders_tracking_nonce="<?php echo esc_attr( isset( $_GET['woo_orders_tracking_nonce'] ) ? sanitize_text_field( $_GET['woo_orders_tracking_nonce'] ) : '' ) ?>">
                        <span class="<?php echo esc_attr( self::set( 'multiple-carriers-select-link-tracking-number' ) ) ?>"><?php echo esc_html( $item['tracking_number'] ) ?></span>
                        <span class="<?php echo esc_attr( self::set( 'multiple-carriers-select-link-carrier-name' ) ) ?>"><?php echo esc_html( $display_name ) ?></span>
                    </div>
					<?php
					$temp_html .= ob_get_clean();
				}
			}
		}
		$tracking_numbers = array_unique( $tracking_numbers );
		if ( count( $tracking_number_carrier_pairs ) > 1 ) {
			$found_tracking = true;
			ob_start();
			?>
            <div class="<?php echo esc_attr( self::set( 'multiple-carriers-select' ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'multiple-carriers-select-title' ) ) ?>">
                    <span><?php count( $tracking_numbers ) > 1 ? esc_html_e( 'Please select a tracking number:', 'woocommerce-orders-tracking' ) : esc_html_e( 'Please select a carrier:', 'woocommerce-orders-tracking' ); ?></span>
                </div>
                <div class="<?php echo esc_attr( self::set( 'multiple-carriers-select-content' ) ) ?>">
					<?php echo $temp_html; ?>
                </div>
                <div class="<?php echo esc_attr( self::set( array(
					'cainiao-originCp-selector-overlay',
					'hidden'
				) ) ) ?>">
                </div>
            </div>
			<?php
			$return = ob_get_clean();
		} elseif ( count( $tracking_number_carrier_pairs ) > 0 ) {
			$found_tracking   = true;
			$tracking_from_db = $used_db;
		}

		return $return;
	}

	/**
	 * @param $tracking_from_db
	 * @param $tracking_code
	 * @param $service_carrier_type
	 * @param $found_tracking
	 *
	 * @throws Exception
	 */
	public function process_tracking_from_db_trackingmore( $tracking_from_db, $tracking_code, $service_carrier_type, &$found_tracking ) {
		$now            = time();
		$found_tracking = true;
		if ( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) === 'delivered' && $tracking_from_db['track_info'] ) {
			$track_info   = vi_wot_json_decode( $tracking_from_db['track_info'] );
			$carrier_name = $tracking_from_db['carrier_id'];
			$display_name = $carrier_name;
			$carrier      = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
			if ( is_array( $carrier ) && count( $carrier ) ) {
				$carrier_name = $carrier['name'];
				$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
			}
			self::display_timeline( array(
				'status'            => $tracking_from_db['status'],
				'tracking'          => $track_info,
				'last_event'        => $tracking_from_db['last_event'],
				'carrier_name'      => $display_name,
				'est_delivery_date' => '',
				'modified_at'       => $tracking_from_db['modified_at'],
			), $tracking_code );
		} else {
			$modified_at = $tracking_from_db['modified_at'];
			if ( ( $now - strtotime( $modified_at ) ) > $this->settings->get_cache_request_time() ) {
				$service_carrier_api_key = $this->settings->get_params( 'service_carrier_api_key' );
				$change_order_status     = $this->settings->get_params( 'change_order_status' );
				if ( $service_carrier_api_key ) {
					$carrier_id = $tracking_from_db['carrier_id'];
					$carrier    = $this->settings->get_shipping_carrier_by_slug( $carrier_id );
					if ( is_array( $carrier ) && count( $carrier ) ) {
						$carrier_name       = $carrier['name'];
						$display_name       = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
						$tracking_more_slug = empty( $carrier['tracking_more_slug'] ) ? VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE::get_carrier_slug_by_name( $carrier_name ) : $carrier['tracking_more_slug'];
						if ( ! empty( $tracking_more_slug ) ) {
							$shipping_country_code = $tracking_from_db['shipping_country_code'];
							if ( ! $shipping_country_code ) {
								$shipping_country_code = self::get_shipping_country_by_order_id( $tracking_from_db['order_id'] );
							}
							$trackingMore = new VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE( $service_carrier_api_key );
							$track_data   = $trackingMore->get_tracking( $tracking_code, $tracking_more_slug );
							if ( $track_data['status'] === 'success' ) {
								if ( count( $track_data['data'] ) ) {
									$track_info  = json_encode( $track_data['data'] );
									$last_event  = array_shift( $track_data['data'] );
									$status      = $last_event['status'];
									$description = $last_event['description'];
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update_by_tracking_number( $tracking_code, $status, $carrier_id, false, $shipping_country_code, $track_info, $description );
									self::display_timeline( array(
										'status'            => $status,
										'tracking'          => $track_data['data'],
										'last_event'        => $last_event,
										'carrier_name'      => $display_name,
										'est_delivery_date' => '',
										'modified_at'       => date( 'Y-m-d H:i:s' ),
									), $tracking_code );
									$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $last_event['status'] );
									if ( $convert_status !== VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) || $track_info !== $tracking_from_db['track_info'] ) {
										$tracking_change = 1;
										VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_items_tracking_status( $tracking_code, $tracking_from_db['carrier_id'], $last_event['status'], $change_order_status );
									}
								} else {
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update( $tracking_from_db['id'], '', false, false, false, false, false, false, null );
									$found_tracking = false;
								}
							} else {
								if ( $track_data['code'] == 4017 && $this->settings->get_params( 'service_add_tracking_if_not_exist' ) ) {
									$trackingMore->create_tracking( $tracking_code, $tracking_more_slug, $tracking_from_db['order_id'] );
								}
								if ( ! $tracking_from_db['track_info'] ) {
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update( $tracking_from_db['id'], '', false, false, false, false, false, false, null );
								} else {
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update( $tracking_from_db['id'], '', false, false, false, false, false, false );
								}
								$found_tracking = false;
							}
						} else {
							$found_tracking = false;
						}
					} else {
						$found_tracking = false;
					}
				}
//				if ( $found_tracking === false && $tracking_from_db['track_info'] ) {
//					$found_tracking = true;
//					$track_info     = vi_wot_json_decode( $tracking_from_db['track_info'] );
//					$carrier_name   = $tracking_from_db['carrier_id'];
//					$display_name   = $carrier_name;
//					$carrier        = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
//					if ( is_array( $carrier ) && count( $carrier ) ) {
//						$carrier_name = $carrier['name'];
//						$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
//					}
//					self::display_timeline( array(
//						'status'            => $tracking_from_db['status'],
//						'tracking'          => $track_info,
//						'last_event'        => $tracking_from_db['last_event'],
//						'carrier_name'      => $display_name,
//						'est_delivery_date' => '',
//						'modified_at'       => strtotime( $tracking_from_db['modified_at'] ) ? $tracking_from_db['modified_at'] : date( 'Y-m-d H:i:s' ),
//					), $tracking_code );
//				}
				if ( $found_tracking === false ) {
					$modified_at_real                = strtotime( $tracking_from_db['modified_at'] ) ? $tracking_from_db['modified_at'] : date( 'Y-m-d H:i:s' );
					$tracking_from_db['modified_at'] = '0000-00-00 00:00:00';
					$this->track_with_cainiao( $tracking_code, $tracking_from_db, $service_carrier_type, $found_tracking, $modified_at_real );
				}
			} elseif ( $tracking_from_db['track_info'] ) {
				$track_info   = vi_wot_json_decode( $tracking_from_db['track_info'] );
				$carrier_name = $tracking_from_db['carrier_id'];
				$display_name = $carrier_name;
				$carrier      = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
				if ( is_array( $carrier ) && count( $carrier ) ) {
					$carrier_name = $carrier['name'];
					$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
				}
				self::display_timeline( array(
					'status'            => $tracking_from_db['status'],
					'tracking'          => $track_info,
					'last_event'        => $tracking_from_db['last_event'],
					'carrier_name'      => $display_name,
					'est_delivery_date' => '',
					'modified_at'       => $tracking_from_db['modified_at'],
				), $tracking_code );
			} else {
				self::tracking_not_available_message();
			}
		}
	}

	/**
	 * @param $tracking_from_db
	 * @param $tracking_code
	 * @param $service_carrier_type
	 * @param $found_tracking
	 *
	 * @throws Exception
	 */

	public function process_tracking_from_db( $tracking_from_db, $tracking_code, $service_carrier_type, &$found_tracking ) {
		$now            = time();
		$found_tracking = true;
		$carrier_name   = $tracking_from_db['carrier_id'];
		$display_name   = $carrier_name;
		$carrier        = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
		if ( is_array( $carrier ) && count( $carrier ) ) {
			$carrier_name = $carrier['name'];
			$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
		}
		if ( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) === 'delivered' && $tracking_from_db['track_info'] ) {
			$track_info = vi_wot_json_decode( $tracking_from_db['track_info'] );
			self::display_timeline( array(
				'status'            => $tracking_from_db['status'],
				'tracking'          => $track_info,
				'last_event'        => $tracking_from_db['last_event'],
				'carrier_name'      => $display_name,
				'est_delivery_date' => $tracking_from_db['est_delivery_date'],
				'modified_at'       => $tracking_from_db['modified_at'],
			), $tracking_code );
		} else {
			$modified_at = $tracking_from_db['modified_at'];
			if ( ( $now - strtotime( $modified_at ) ) > $this->settings->get_cache_request_time() ) {
				$service_carrier_api_key = $this->settings->get_params( 'service_carrier_api_key' );
				$change_order_status     = $this->settings->get_params( 'change_order_status' );
				if ( $service_carrier_api_key ) {
					switch ( $service_carrier_type ) {
						case 'aftership':
							$aftership_carrier_slug = VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP::get_carrier_slug_by_name( $carrier_name );
							$afterShip              = new VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP( $service_carrier_api_key );
							$track_data             = $afterShip->get_tracking_data( $tracking_code, $aftership_carrier_slug );
							if ( $track_data['status'] === 'success' ) {
								if ( count( $track_data['data'] ) ) {
									$tracking   = $track_data['data'];
									$track_info = json_encode( $track_data['data'] );
									$last_event = array_shift( $track_data['data'] );
									self::display_timeline( array(
										'status'            => $last_event['status'],
										'tracking'          => $tracking,
										'last_event'        => $last_event,
										'carrier_name'      => $display_name,
										'est_delivery_date' => $track_data['est_delivery_date'],
										'modified_at'       => $tracking_from_db['modified_at'],
									), $tracking_code );
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update_by_tracking_number( $tracking_code, '', $service_carrier_type, $last_event['status'], $track_info, $last_event['description'], $track_data['est_delivery_date'], $track_data['origin_country'], $track_data['destination_country'] );
									$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $last_event['status'] );
									if ( $convert_status !== VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) || $track_info !== $tracking_from_db['track_info'] ) {
										$tracking_change = 1;
										VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_items_tracking_status( $tracking_code, $tracking_from_db['carrier_id'], $last_event['status'], $change_order_status );
									}
								} else {
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'], '', '', $service_carrier_type, '', '', '', $track_data['est_delivery_date'] );
//									self::tracking_not_available_message();
									$found_tracking = false;
								}
							} else {
								if ( $track_data['code'] === 4004 && $this->settings->get_params( 'service_add_tracking_if_not_exist' ) ) {
									/*Tracking NOT exists*/
									$afterShip->create( $tracking_code, $aftership_carrier_slug, $tracking_from_db['order_id'] );
								}
								VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'] );
								$found_tracking = false;
							}
							break;
						case 'easypost':
							$easyPost   = new VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST( $service_carrier_api_key );
							$track_data = $easyPost->retrieve( $tracking_code );
							if ( $track_data['status'] === 'success' ) {
								if ( count( $track_data['data'] ) ) {
									$tracking   = $track_data['data'];
									$track_info = json_encode( $track_data['data'] );
									$last_event = array_shift( $track_data['data'] );
									self::display_timeline( array(
										'status'            => $last_event['status'],
										'tracking'          => $tracking,
										'last_event'        => $last_event,
										'carrier_name'      => $display_name,
										'est_delivery_date' => $track_data['est_delivery_date'],
										'modified_at'       => $tracking_from_db['modified_at'],
									), $tracking_code );
//									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'], '', '', $service_carrier_type, $last_event['status'], $track_info, $last_event['description'], $track_data['est_delivery_date'] );
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update_by_tracking_number( $tracking_code, '', $service_carrier_type, $last_event['status'], $track_info, $last_event['description'], $track_data['est_delivery_date'] );
									$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $last_event['status'] );
									if ( $convert_status !== VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) || $track_info !== $tracking_from_db['track_info'] ) {
										$tracking_change = 1;
										VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_items_tracking_status( $tracking_code, $tracking_from_db['carrier_id'], $last_event['status'], $change_order_status );
									}
								} else {
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'], '', '', $service_carrier_type, '', '', '', $track_data['est_delivery_date'] );
									$found_tracking = false;
								}
							} else {
								/*Tracking NOT exists*/
								if ( $track_data['code'] === 404 && $this->settings->get_params( 'service_add_tracking_if_not_exist' ) ) {
									$find_carrier = VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST::get_carrier_slug_by_name( $carrier_name );
									$easyPost->create( $tracking_code, $find_carrier );
								}
								VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'] );
								$found_tracking = false;
							}
							break;
						default:
					}
				}
				if ( $found_tracking === false || $service_carrier_type === 'cainiao' ) {
					$this->track_with_cainiao( $tracking_code, $tracking_from_db, $service_carrier_type, $found_tracking );
				}
			} elseif ( $tracking_from_db['track_info'] ) {
				$track_info = vi_wot_json_decode( $tracking_from_db['track_info'] );
				self::display_timeline( array(
					'status'            => $tracking_from_db['status'],
					'tracking'          => $track_info,
					'last_event'        => $tracking_from_db['last_event'],
					'carrier_name'      => $display_name,
					'est_delivery_date' => $tracking_from_db['est_delivery_date'],
					'modified_at'       => $tracking_from_db['modified_at'],
				), $tracking_code );
			} else {
				self::tracking_not_available_message();
			}
		}
	}

	public static function tracking_not_available_message() {
		?>
        <p><?php esc_html_e( 'Tracking data is not available now. Please come back later. Thank you.', 'woocommerce-orders-tracking' ) ?></p>
		<?php
	}

	public static function get_not_found_text() {
		if ( empty( $_GET['tracking_id'] ) ) {
			?>
            <p><?php esc_html_e( 'No tracking number found', 'woocommerce-orders-tracking' ) ?></p>
			<?php
		} else {
			?>
            <p><?php esc_html_e( 'Tracking number is expired or not found in existing orders.', 'woocommerce-orders-tracking' ) ?></p>
			<?php
		}
	}

	/**
	 * @param $tracking_code
	 * @param $tracking_from_db
	 * @param $service_carrier_type
	 * @param $found_tracking
	 * @param string $modified_at_real
	 *
	 * @throws Exception
	 */
	public function track_with_cainiao( $tracking_code, $tracking_from_db, $service_carrier_type, &$found_tracking, $modified_at_real = '' ) {
		$now = time();
		if ( count( $tracking_from_db ) ) {
			if ( ! isset( $tracking_from_db['id'] ) ) {
				$tracking_from_db = $tracking_from_db[0];
			}
			if ( ! $tracking_code ) {
				$tracking_code = $tracking_from_db['tracking_number'];
			}
			$found_tracking = true;
			$modified_at    = $tracking_from_db['modified_at'];
			if ( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) === 'delivered' && $tracking_from_db['track_info'] ) {
				$track_info   = vi_wot_json_decode( $tracking_from_db['track_info'] );
				$carrier_name = $tracking_from_db['carrier_id'];
				$display_name = $carrier_name;
				$carrier      = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
				if ( is_array( $carrier ) && count( $carrier ) ) {
					$carrier_name = $carrier['name'];
					$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
				}
				self::display_timeline( array(
					'status'            => $tracking_from_db['status'],
					'tracking'          => $track_info,
					'last_event'        => $tracking_from_db['last_event'],
					'carrier_name'      => $display_name,
					'est_delivery_date' => isset( $tracking_from_db['est_delivery_date'] ) ? $tracking_from_db['est_delivery_date'] : '',
					'modified_at'       => $modified_at_real ? $modified_at_real : $tracking_from_db['modified_at'],
				), $tracking_code );
			} else {
				if ( ( $now - strtotime( $modified_at ) ) > $this->settings->get_cache_request_time() ) {
					$carrier_name = $tracking_from_db['carrier_id'];
					$display_name = $carrier_name;
					$carrier      = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
					if ( is_array( $carrier ) && count( $carrier ) ) {
						$carrier_name = $carrier['name'];
						$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
					}
					self::cainiao_search_tracking( $tracking_code, $found_tracking, $tracking_from_db, $service_carrier_type, $carrier_name, $display_name );
				} else {
					$found_tracking = false;
				}
				if ( $found_tracking === false && $tracking_from_db['track_info'] ) {
					$found_tracking = true;
					$track_info     = vi_wot_json_decode( $tracking_from_db['track_info'] );
					$carrier_name   = $tracking_from_db['carrier_id'];
					$display_name   = $carrier_name;
					$carrier        = $this->settings->get_shipping_carrier_by_slug( $tracking_from_db['carrier_id'] );
					if ( is_array( $carrier ) && count( $carrier ) ) {
						$carrier_name = $carrier['name'];
						$display_name = empty( $carrier['display_name'] ) ? $carrier_name : $carrier['display_name'];
					}
					self::display_timeline( array(
						'status'            => $tracking_from_db['status'],
						'tracking'          => $track_info,
						'last_event'        => $tracking_from_db['last_event'],
						'carrier_name'      => $display_name,
						'est_delivery_date' => isset( $tracking_from_db['est_delivery_date'] ) ? $tracking_from_db['est_delivery_date'] : '',
						'modified_at'       => $tracking_from_db['modified_at'],
					), $tracking_code );
				}
			}
		}
	}

	/**
	 * @param $tracking_code
	 * @param $found_tracking
	 * @param $originCp
	 * @param $tracking_from_db
	 * @param $service_carrier_type
	 * @param $carrier_name
	 * @param $display_name
	 *
	 * @throws Exception
	 */
	public static function cainiao_get_track_info( $tracking_code, &$found_tracking, $originCp, $tracking_from_db, $service_carrier_type, $carrier_name, $display_name ) {
		$url            = "https://slw16.global.cainiao.com/trackSyncQueryRpc/queryAllLinkTrace.json?callback=jQuery&mailNo={$tracking_code}&originCp={$originCp}";
		$request_data   = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_get( $url );
		$found_tracking = false;
		if ( $request_data['status'] === 'success' ) {
			$result              = vi_wot_json_decode( substr( $request_data['data'], 7, strlen( $request_data['data'] ) - 8 ) );
			$section2            = $result['section2'];
			$origin_country      = empty( $result['originCountry'] ) ? false : $result['originCountry'];
			$destination_country = empty( $result['destCountry'] ) ? false : $result['destCountry'];
			if ( isset( $section2['detailList'] ) && is_array( $section2['detailList'] ) && count( $section2['detailList'] ) ) {
				$found_tracking = true;
				$tracking       = self::get_track_info( $section2['detailList'] );
				$track_info     = json_encode( $tracking );
				$last_event     = $tracking[0];
				if ( $tracking_from_db['id'] ) {
					if ( $service_carrier_type === 'trackingmore' ) {
						$shipping_country_code = $tracking_from_db['shipping_country_code'];
						if ( ! $shipping_country_code ) {
							$shipping_country_code = self::get_shipping_country_by_order_id( $tracking_from_db['order_id'] );
						}
						VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update( $tracking_from_db['id'], '', $last_event['status'], false, false, $shipping_country_code, $track_info, $last_event['description'] );
					} else {
						VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'], '', '', '', $last_event['status'], $track_info, $last_event['description'], false, $origin_country, $destination_country );
					}
				} else {
					if ( $service_carrier_type === 'trackingmore' ) {
						$shipping_country_code = $tracking_from_db['shipping_country_code'];
						if ( ! $shipping_country_code ) {
							$shipping_country_code = self::get_shipping_country_by_order_id( $tracking_from_db['order_id'] );
						}
						VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::insert( $tracking_from_db['order_id'], $tracking_code, $last_event['status'], $tracking_from_db['carrier_id'], $carrier_name, $shipping_country_code, $track_info, $last_event['description'] );
					} else {
						VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_code, $tracking_from_db['order_id'], $tracking_from_db['carrier_id'], $service_carrier_type, $last_event['status'], $track_info, $last_event['description'], '', false, $origin_country, $destination_country );
					}
				}
				$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $last_event['status'] );
				$settings       = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
				if ( $convert_status !== VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) || $track_info !== $tracking_from_db['track_info'] ) {
					$tracking_change = 1;
					VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_items_tracking_status( $tracking_code, $tracking_from_db['carrier_id'], $last_event['status'], $settings->get_params( 'change_order_status' ) );
				}
				self::display_timeline( array(
					'status'            => $last_event['status'],
					'tracking'          => $tracking,
					'last_event'        => $last_event,
					'carrier_name'      => $display_name,
					'est_delivery_date' => isset( $tracking_from_db['est_delivery_date'] ) ? $tracking_from_db['est_delivery_date'] : '',
					'modified_at'       => strtotime( $tracking_from_db['modified_at'] ) ? $tracking_from_db['modified_at'] : date( 'Y-m-d H:i:s' ),
				), $tracking_code );
			}
		}
	}

	/**
	 * @param $tracking_code
	 * @param $found_tracking
	 * @param $tracking_from_db
	 * @param $service_carrier_type
	 * @param $carrier_name
	 * @param $display_name
	 *
	 * @throws Exception
	 */
	public static function cainiao_search_tracking( $tracking_code, &$found_tracking, $tracking_from_db, $service_carrier_type, $carrier_name, $display_name ) {
		$url            = "https://global.cainiao.com/detail.htm?mailNoList={$tracking_code}";
		$request_data   = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_get( $url );
		$found_tracking = false;
		if ( $request_data['status'] === 'success' ) {
			$html = $request_data['data'];
			preg_match( '/id="waybill_list_val_box">(.+?)<\/textarea>/im', $html, $matches );
			if ( $matches ) {
				$results = vi_wot_json_decode( html_entity_decode( $matches[1] ) );
				if ( ! empty( $results['success'] ) ) {
					$data = isset( $results['data'] ) ? $results['data'] : array();
					if ( is_array( $data ) && count( $data ) ) {
						$result = $data[0];
						if ( empty( $result['errorCode'] ) ) {
							$section2            = $result['section2'];
							$origin_country      = empty( $result['originCountry'] ) ? false : $result['originCountry'];
							$destination_country = empty( $result['destCountry'] ) ? false : $result['destCountry'];
							if ( isset( $section2['detailList'] ) && is_array( $section2['detailList'] ) && count( $section2['detailList'] ) ) {
								$found_tracking = true;
								$tracking       = self::get_track_info( $section2['detailList'] );
								$track_info     = json_encode( $tracking );
								$last_event     = $tracking[0];
								if ( $tracking_from_db['id'] ) {
									if ( $service_carrier_type === 'trackingmore' ) {
										$shipping_country_code = $tracking_from_db['shipping_country_code'];
										if ( ! $shipping_country_code ) {
											$shipping_country_code = self::get_shipping_country_by_order_id( $tracking_from_db['order_id'] );
										}
										VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update( $tracking_from_db['id'], '', $last_event['status'], false, false, $shipping_country_code, $track_info, $last_event['description'] );
									} else {
										VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'], '', '', '', $last_event['status'], $track_info, $last_event['description'], '', $origin_country, $destination_country );
									}
								} else {
									if ( $service_carrier_type === 'trackingmore' ) {
										$shipping_country_code = $tracking_from_db['shipping_country_code'];
										if ( ! $shipping_country_code ) {
											$shipping_country_code = self::get_shipping_country_by_order_id( $tracking_from_db['order_id'] );
										}
										VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::insert( $tracking_from_db['order_id'], $tracking_code, $last_event['status'], $tracking_from_db['carrier_id'], $carrier_name, $shipping_country_code, $track_info, $last_event['description'] );
									} else {
										VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_code, $tracking_from_db['order_id'], $tracking_from_db['carrier_id'], $service_carrier_type, $last_event['status'], $track_info, $last_event['description'], '', false, $origin_country, $destination_country );
									}
								}
								$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $last_event['status'] );
								$settings       = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
								if ( $convert_status !== VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] ) || $track_info !== $tracking_from_db['track_info'] ) {
									$tracking_change = 1;
									VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_items_tracking_status( $tracking_code, $tracking_from_db['carrier_id'], $last_event['status'], $settings->get_params( 'change_order_status' ) );
								}
								self::display_timeline( array(
									'status'            => $last_event['status'],
									'tracking'          => $tracking,
									'last_event'        => $last_event,
									'carrier_name'      => $display_name,
									'est_delivery_date' => isset( $tracking_from_db['est_delivery_date'] ) ? $tracking_from_db['est_delivery_date'] : '',
									'modified_at'       => date( 'Y-m-d H:i:s' ),
								), $tracking_code );
							} elseif ( is_array( $result['originCpList'] ) ) {
								$found_tracking = true;
								$originCpList   = $result['originCpList'];
								$countCp        = count( $originCpList );
								if ( $countCp == 1 ) {
									self::cainiao_get_track_info( $tracking_code, $found_tracking, $originCpList[0]['cpCode'], $tracking_from_db, $service_carrier_type, $carrier_name, $display_name );
								} elseif ( $countCp > 1 ) {
									$search_carrier = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_cp_list( $carrier_name );
									if ( $search_carrier ) {
										if ( array_search( $search_carrier, array_column( $originCpList, 'cpCode' ) ) !== false ) {
											self::cainiao_get_track_info( $tracking_code, $found_tracking, $search_carrier, $tracking_from_db, $service_carrier_type, $carrier_name, $display_name );

											return;
										}
									}

									if ( ! $tracking_from_db['id'] ) {
										if ( $service_carrier_type === 'trackingmore' ) {
											$tracking_from_db['id'] = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::insert( $tracking_from_db['order_id'], $tracking_code, '', $tracking_from_db['carrier_id'], $carrier_name, $tracking_from_db['shipping_country_code'], '', '', null );
										} else {
											$tracking_from_db['id'] = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_code, $tracking_from_db['order_id'], $tracking_from_db['carrier_id'], $service_carrier_type, '', '', '', '', null, $origin_country, $destination_country );
										}
									}
									?>
                                    <div class="<?php echo esc_attr( self::set( 'cainiao-originCp-selector' ) ) ?>"
                                         data-tracking_from_db="<?php echo esc_attr( json_encode( array(
										     'id'              => $tracking_from_db['id'],
										     'carrier_id'      => $tracking_from_db['carrier_id'],
										     'carrier_service' => $service_carrier_type
									     ) ) ) ?>"
                                         data-carrier_name="<?php echo esc_attr( $carrier_name ) ?>"
                                         data-display_name="<?php echo esc_attr( $display_name ) ?>">
                                        <span class="<?php echo esc_attr( self::set( 'cainiao-originCp-title' ) ) ?>"><?php esc_html_e( 'Please select a carrier:', 'woocommerce-orders-tracking' ) ?></span>
                                        <div class="<?php echo esc_attr( self::set( 'cainiao-originCp-options' ) ) ?>">
											<?php
											foreach ( $originCpList as $cp ) {
												?>
                                                <div class="<?php echo esc_attr( self::set( 'cainiao-originCp-option' ) ) ?>"
                                                     data-originCp="<?php echo esc_attr( $cp['cpCode'] ) ?>">
                                                    <div class="<?php echo esc_attr( self::set( 'cainiao-originCp-option-name' ) ) ?>"><?php esc_html_e( $cp['cpName'] ) ?></div>
                                                    <div class="<?php echo esc_attr( self::set( 'cainiao-originCp-option-country' ) ) ?>"><?php esc_html_e( $cp['country'] ) ?></div>
                                                </div>
												<?php
											}
											?>
                                        </div>
                                        <div class="<?php echo esc_attr( self::set( 'cainiao-originCp-submit-container' ) ) ?>">
                                            <span class="<?php echo esc_attr( self::set( 'cainiao-originCp-submit' ) ) ?>"><?php esc_html_e( 'Submit', 'woocommerce-orders-tracking' ) ?></span>
                                        </div>
                                    </div>
                                    <div class="<?php echo esc_attr( self::set( array(
										'cainiao-originCp-selector-overlay',
										'hidden'
									) ) ) ?>">
                                    </div>
									<?php
								}
							}
						}
					}
				}
			}
		}
	}

	public static function get_track_info( $detailList ) {
		$track_info = array();
		foreach ( $detailList as $item ) {
			$time = $item['time'];
			if ( $item['timeZone'] ) {
				$time = date( 'Y-m-d H:i:s', strtotime( "{$item['time']} {$item['timeZone']}" ) );
			}
			$item['desc'] = trim( $item['desc'] );
			$track_info[] = array(
				'time'        => $time,
				'description' => strtolower( $item['desc'] ) === '[cn]stopcrawler' ? esc_html__( 'Waiting for the seller shipping', 'woocommerce-orders-tracking' ) : $item['desc'],
				'location'    => '',
				'status'      => $item['status'],
			);
		}

		return $track_info;
	}

	/**Print out links to carrier page if tracking is not found from current service carrier
	 *
	 * @param $tracking_code
	 * @param $found_tracking
	 */
	private static function search_tracking_original_url( $tracking_code, &$found_tracking ) {
		$settings = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
		if ( self::$query_tracking === null ) {
			self::$query_tracking = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::search_order_item_by_tracking_number( $tracking_code );
		}
		$results = self::$query_tracking;
		if ( count( $results ) ) {
			if ( ! $found_tracking ) {
				$carrier_slugs     = array();
				$working_trackings = array();
				foreach ( $results as $result ) {
					if ( $result['meta_value'] ) {
						$item_tracking_data    = vi_wot_json_decode( $result['meta_value'] );
						$current_tracking_data = array_pop( $item_tracking_data );
						if ( $current_tracking_data['tracking_number'] == $tracking_code && $current_tracking_data['carrier_slug'] && ! in_array( $current_tracking_data['carrier_slug'], $carrier_slugs ) ) {
							$carrier_slugs[] = $current_tracking_data['carrier_slug'];
							$carrier         = $settings->get_shipping_carrier_by_slug( $current_tracking_data['carrier_slug'], '' );
							if ( is_array( $carrier ) && count( $carrier ) ) {
								$carrier_url         = $settings->get_url_tracking( $carrier['url'], $tracking_code, $current_tracking_data['carrier_slug'], '', true );
								$working_trackings[] = array(
									'name' => empty( $carrier['display_name'] ) ? $carrier['name'] : $carrier['display_name'],
									'url'  => $carrier_url,
								);
							}
						}
					}
				}
				if ( count( $working_trackings ) ) {
					$found_tracking = true;
					?>
                    <div>
                        <p>
							<?php esc_html_e( 'This tracking can not be found in our system. Please try:', 'woocommerce-orders-tracking' ) ?>
                        </p>
                        <ul>
							<?php
							foreach ( $working_trackings as $working_tracking ) {
								?>
                                <li><a href="<?php echo esc_url( $working_tracking['url'] ) ?>"
                                       target="_blank"><?php esc_html_e( "Tracking with {$working_tracking['name']}", 'woocommerce-orders-tracking' ) ?></a>
                                </li>
								<?php
							}
							?>
                        </ul>
                    </div>
					<?php
				}
			}
		}
	}

	private static function get_icon_status_delivered( $setting_icon ) {
		$icons = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_delivered_icons();

		return isset( $icons[ $setting_icon ] ) ? "<i class='{$icons[$setting_icon]}'></i>" : '';
	}

	private static function get_icon_status_pickup( $setting_icon ) {
		$icons = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_pickup_icons();

		return isset( $icons[ $setting_icon ] ) ? "<i class='{$icons[$setting_icon]}'></i>" : '';
	}

	private static function get_icon_status_transit( $setting_icon ) {
		$icons = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_transit_icons();

		return isset( $icons[ $setting_icon ] ) ? "<i class='{$icons[$setting_icon]}'></i>" : '';
	}

	public static function get_default_icon() {
		return '<span class="woo-orders-tracking-icon-default"></span>';
	}

	public static function get_icon_status( $status, $template, $icon = '' ) {
		$settings = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
		$result   = '';
		if ( $template === '1' ) {
			switch ( $status ) {
				case 'delivered':
					if ( ! $icon ) {
						$icon = $settings->get_params( 'timeline_track_info_template_one', 'icon_delivered' );
					}
					$result = self::get_icon_status_delivered( $icon );
					break;
				case 'pickup':
					if ( ! $icon ) {
						$icon = $settings->get_params( 'timeline_track_info_template_one', 'icon_pickup' );
					}
					$result = self::get_icon_status_pickup( $icon );
					break;
				case 'transit':
					if ( ! $icon ) {
						$icon = $settings->get_params( 'timeline_track_info_template_one', 'icon_transit' );
					}
					$result = self::get_icon_status_transit( $icon );
					break;
				case 'alert':
					$result = '<span class="woo_orders_tracking_icons-warning"></span>';
					break;
				default:
					$result = self::get_default_icon();
			}
		}

		return $result;
	}

	/**
	 * @param $name
	 * @param $type
	 * @param string $tracking_code
	 *
	 * @return string
	 */
	protected function get_template( $name, $type, $tracking_code = '' ) {
		ob_start();
		if ( $type === 'require' ) {
			require_once VI_WOOCOMMERCE_ORDERS_TRACKING_TEMPLATES . $name . '.php';
		} elseif ( $type === 'function' ) {
			$this->$name( $tracking_code );
		}
		$html = ob_get_clean();

		return ent2ncr( $html );
	}

	private function add_inline_style( $name, $element, $style, $suffix = '', $type = array(), $echo = false ) {
		$return = $element . '{';
		if ( is_array( $name ) && count( $name ) ) {
			foreach ( $name as $key => $value ) {
				$t      = isset( $type[ $key ] ) ? $type[ $key ] : '';
				$return .= $style[ $key ] . ':' . ( $t ? $this->settings->get_params( $t, $name[ $key ] ) : $this->settings->get_params( $name[ $key ] ) ) . $suffix[ $key ] . ';';
			}
		} else {
			$return .= $style . ':' . $this->settings->get_params( $name ) . $suffix . ';';
		}
		$return .= '}';
		if ( $echo ) {
			echo wp_kses( $return, VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::extend_post_allowed_style_html() );
		}

		return $return;
	}
}