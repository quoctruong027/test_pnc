<?php

class WCCT_Appearance {

	public static $_instance = null;
	public $header_info = array();
	public $campaign_end_time = array();
	public $is_sticky_header_call = false;

	public function __construct() {

		$this->wcct_url = untrailingslashit( plugin_dir_url( WCCT_PLUGIN_FILE ) );

		add_action( 'template_redirect', array( $this, 'wcct_get_sticky_campaigns' ), 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wcct_wp_enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'wcct_triggers_sticky_header_and_footer' ), 50 );
		add_action( 'wp_footer', array( $this, 'wcct_css_print' ), 55 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_above_title' ), 2.3 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_title' ), 9.3 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_review' ), 11.3 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_price' ), 17.3 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_short_desc' ), 21.3 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_add_cart' ), 39.3 );
		/**
		 * @deprecated positions
		 */ //      add_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_meta' ), 41.3 );
		//      add_action( 'woocommerce_after_single_product_summary', array( $this, 'wcct_position_above_tab_area' ), 9.9 );
		//      add_action( 'woocommerce_after_single_product_summary', array( $this, 'wcct_position_below_related_products' ), 21.3 );

		add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'wcct_change_single_add_to_cart_text' ) );
		add_filter( 'add_to_cart_text', array( $this, 'wcct_change_add_to_cart_text' ) ); // < 2.1
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'wcct_change_add_to_cart_text' ) );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'wcct_show_on_cart' ), 10, 3 );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'wcct_bar_timer_show_on_grid' ), 9 );
		add_filter( 'woocommerce_product_is_visible', array( $this, 'wcct_woocommerce_product_is_visible' ), 10, 2 );
		// for hiding add to cart button
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'wcct_remove_add_to_cart_btn' ), 10 );
		add_filter( 'woocommerce_is_purchasable', array( $this, 'wcct_woocommerce_is_purchasable' ), 10, 2 );
		add_filter( 'woocommerce_variation_is_purchasable', array( $this, 'wcct_woocommerce_is_purchasable' ), 10, 2 );
		add_filter( 'wc_get_template', array( $this, 'wcct_add_to_cart_variable_hide' ), 100, 5 );

		add_action( 'wp_footer', array( $this, 'wcct_print_html_header_info' ), 50 );
		add_filter( 'wcct_localize_js_data', array( $this, 'add_info_localized' ) );
		add_action( 'wp_footer', array( $this, 'maybe_add_info_footer' ) );

		/* wcct_the_content is the replacement of the_content */
		add_filter( 'wcct_the_content', 'wptexturize' );
		add_filter( 'wcct_the_content', 'convert_smilies', 20 );
		add_filter( 'wcct_the_content', 'wpautop' );
		add_filter( 'wcct_the_content', 'shortcode_unautop' );
		add_filter( 'wcct_the_content', 'prepend_attachment' );
		add_filter( 'wcct_the_content', 'wp_make_content_images_responsive' );
		add_filter( 'wcct_the_content', 'do_shortcode', 11 );

		add_shortcode( 'wcct_campaign_start_date', array( $this, 'shortcode_campaign_start_date' ) );
		add_shortcode( 'wcct_campaign_end_date', array( $this, 'shortcode_campaign_end_date' ) );

		add_filter( 'wcct_always_show_days_on_timers', array( $this, 'wcct_always_show_days' ) );
		add_filter( 'wcct_always_show_hrs_on_timers', array( $this, 'wcct_always_show_hrs' ) );
		add_action( 'wp_loaded', array( $this, 'wcct_modify_positions' ), 9999 );
	}

	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Hide add to cart form if during or after campaign action 'Add to cart Hide' is set
	 *
	 * @param $located
	 * @param $template_name
	 * @param $args
	 * @param $template_path
	 * @param $default_path
	 *
	 * @return string
	 * $since 1.3.2
	 * @global $product
	 *
	 */
	public static function wcct_add_to_cart_variable_hide( $located, $template_name, $args, $template_path, $default_path ) {
		global $product;
		if ( $product instanceof WC_Product ) {
			if ( $template_name === 'single-product/add-to-cart/variable.php' ) {
				$product_id = WCCT_Core()->public->wcct_get_product_parent_id( $product );
				$actions    = WCCT_Core()->public->wcct_genrate_actions( $product_id );

				if ( isset( $actions['add_to_cart'] ) && $actions['add_to_cart'] === 'hide' ) {
					$new_located_path = WCCT_PLUGIN_DIR . '/view/add_to_cart_variable_empty.php';

					return $new_located_path;
				}
			}
		}

		return $located;
	}

	public function wcct_wp_enqueue_scripts() {

		$upload_dir = wp_upload_dir();

		$base_url = $upload_dir['baseurl'] . '/' . 'finale-woocommerce-sales-countdown-timer-discount-plugin';

		$min = '';
		if ( true === SCRIPT_DEBUG ) {
			$min = '.min';
		}
		wp_enqueue_style( 'wcct_public_css', $this->wcct_url . '/assets/css/wcct_combined' . $min . '.css', array(), WCCT_VERSION );

		if ( true == SCRIPT_DEBUG ) {
			wp_enqueue_script( 'wcct_countdown', $this->wcct_url . '/assets/js/jquery.countdown.min.js', array( 'jquery' ), WCCT_VERSION, true );
			wp_enqueue_script( 'wcct_visible_js', $this->wcct_url . '/assets/js/wcct-visible.js', array( 'jquery' ), WCCT_VERSION, true );
			wp_enqueue_script( 'wcct_htspan_js', $this->wcct_url . '/assets/js/humanized-time-span.js', array( 'jquery' ), WCCT_VERSION, true );
			wp_enqueue_script( 'wcct_public_js', $this->wcct_url . '/assets/js/wcct-custom.js', array( 'jquery' ), WCCT_VERSION, true );
		} else {
			wp_enqueue_script( 'wcct_public_js', $this->wcct_url . '/assets/js/wcct_combined.min.js', array( 'jquery' ), WCCT_VERSION, true );
		}

		// store currency
		$wcct_currency                = get_woocommerce_currency_symbol();
		$localize_arr['wcct_version'] = WCCT_VERSION;
		$localize_arr['currency']     = $wcct_currency;
		$localize_arr['admin_ajax']   = admin_url( 'admin-ajax.php' );
		$localize_arr['home_url']     = home_url();

		$localize_arr['nonces'] = array(
			'close_sticky_bar' => wp_create_nonce( 'close_sticky_bar' ),
			'get_button_ref'   => wp_create_nonce( 'wcct_get_button_ref' ),
		);

		$localize_arr['log_file']                  = $base_url . '/force.txt';
		$localize_arr['refresh_timings']           = 'yes';
		$localize_arr['reload_page_on_timer_ends'] = 'yes';
		$global_settings                           = WCCT_Common::get_global_default_settings();
		if ( 'no' === $global_settings['wcct_reload_page_on_timer_ends'] ) {
			$localize_arr['reload_page_on_timer_ends'] = 'no';
		}

		wp_localize_script( 'wcct_public_js', 'wcct_data', apply_filters( 'wcct_localize_js_data', $localize_arr ) );
	}

	/**
	 * Get campaign data for sticky header and footer appearance
	 * for complete site
	 * @global: $product
	 * @global: $wcct_style
	 */
	public function wcct_get_sticky_campaigns() {
		$current_post = WCCT_Common::$wcct_post;

		if ( WCCT_Common::$is_executing_rule ) {
			return;
		}

		/**
		 * Final check for Sticky Header or Footer, whether to fetch data or not
		 * If don't want to display on a custom post type page
		 */
		if ( true === apply_filters( 'wcct_sticky_bar_final_check', false ) ) {
			return;
		}

		// set is_sticky_header_call value to true
		$this->is_sticky_header_call = true;

		remove_action( 'wcct_before_apply_rules', array( 'WCCT_Common', 'add_excluded_rules' ), 10, 2 );

		$previous_data = false;
		$product_id    = 0;
		if ( is_product() ) {
			$product_id = $current_post->ID;
		}

		/**
		 * Checking if data is already set previously.
		 *
		 * Earlier caused conflict with "Premmerce Performance Optimizer" plugin
		 */
		if ( isset( WCCT_Core()->public->single_campaign[ $product_id ] ) && is_array( WCCT_Core()->public->single_campaign[ $product_id ] ) && ! empty( WCCT_Core()->public->single_campaign[ $product_id ] ) ) {
			$previous_data = WCCT_Core()->public->single_campaign[ $product_id ];
			unset( WCCT_Core()->public->single_campaign[ $product_id ] );
		}

		$data = WCCT_Core()->public->get_single_campaign_pro_data( $product_id, true, false, true );

		WCCT_Core()->public->sticky_header = ( $data && isset( $data['sticky_header'] ) ) ? $data['sticky_header'] : array();
		WCCT_Core()->public->sticky_footer = ( $data && isset( $data['sticky_footer'] ) ) ? $data['sticky_footer'] : array();

		if ( isset( WCCT_Core()->public->single_campaign[ $product_id ] ) ) {
			unset( WCCT_Core()->public->single_campaign[ $product_id ] );
		}
		if ( false !== $previous_data ) {
			WCCT_Core()->public->single_campaign[ $product_id ] = $previous_data;
		}

		/**
		 * setting 'maybe_set_rule_valid_cache' to true for allowing cahcing rule valid status
		 * restrict caching of rule status when product added to cart as cart process its data before that
		 */
		WCCT_Common::$maybe_set_rule_valid_cache = true;

		// set is_sticky_header_call value to false
		$this->is_sticky_header_call = false;

		add_action( 'wcct_before_apply_rules', array( 'WCCT_Common', 'add_excluded_rules' ), 10, 2 );
	}

	/**
	 * Print Sticky Header and footer on Site
	 * @global: $wcct_style
	 * @global: $post
	 */
	public function wcct_triggers_sticky_header_and_footer() {
		include WCCT_PLUGIN_DIR . '/view/sticky-header-footer.php';
	}

	/**
	 * Print plugin CSS in footer
	 * @global: $wcct_style . This contains all internal css on a page.
	 */
	public function wcct_css_print() {
		global $wcct_style;

		if ( '' !== $wcct_style ) {
			echo "<style>{$wcct_style}</style>";
		}
		if ( is_array( WCCT_Core()->public->single_product_css ) && count( WCCT_Core()->public->single_product_css ) > 0 ) {
			echo '<style>' . implode( "\n", WCCT_Core()->public->single_product_css ) . '</style>';
		}
	}

	public function add_fragments_style( $frags ) {
		global $wcct_style;
		if ( ! is_array( $frags ) ) {
			$frags = array();
		}
		$frags['style.wcct_style_fragments'] = "<style class='wcct_style_fragments'>" . $wcct_style . '<style>';

		return $frags;
	}

	/**
	 * Get sticky bar button classes
	 *
	 * @param type $value
	 *
	 * @return string
	 */
	public function wcct_button_skin_class( $value ) {
		switch ( $value ) {
			case 'button_2':
				return 'wcct_rounded_button';
				break;
			case 'button_3':
				return 'wcct_ghost_button';
				break;
			case 'button_4':
				return 'wcct_shadow_button';
				break;
			case 'button_5':
				return 'wcct_default_style_2';
				break;
			case 'button_6':
				return 'wcct_arrow_button';
				break;
			default:
				return 'wcct_default_style';
				break;
		}
	}

	public function wcct_show_on_cart( $hyper_link_name, $cart_item, $cart_item_key ) {
		if ( WCCT_Core()->cart->is_mini_cart || ! is_array( $cart_item ) || ! isset( $cart_item['product_id'] ) ) {
			return $hyper_link_name;
		}
		$get_item_id = $cart_item['product_id'];
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $get_item_id );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $get_item_id );
		$cp_data     = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		wcct_force_log( "product id => {$get_item_id} \n function wcct_show_on_cart_grid_bar \n\r" . print_r( $cp_data, true ) );
		$this->current_cart_item = $cart_item;
		ob_start();
		$this->wcct_triggers( $cp_data, 0, 'cart' );
		$html = ob_get_clean();

		return $hyper_link_name . $html;
	}

	public function wcct_triggers( $campaign_data, $position = 0, $type = 'single' ) {
		$display_on_checkout = apply_filters( 'wcct_display_campaign_elements_on_checkout', false );
		if ( true === $display_on_checkout || ! is_checkout() ) {
			if ( is_user_logged_in() && current_user_can( 'administrator' ) && isset( $_GET['wcct_positions'] ) && 'yes' === $_GET['wcct_positions'] && $position != '0' ) {
				WCCT_Common::pr( 'Position: ' . $this->get_position_for_index( $position ) );
			}
			$data        = $campaign_data['campaign'];
			$goals       = isset( $data['goals'] ) ? $data['goals'] : array();
			$expiry_text = isset( $data['expiry_text'] ) ? $data['expiry_text'] : array();

			global $product;

			if ( ! $product instanceof WC_Product ) {
				return;
			}

			if ( in_array( $type, array( 'single', 'grid' ), true ) ) {
				$goals_meta = WCCT_Core()->public->wcct_get_goal_object( $goals, WCCT_Core()->public->wcct_get_product_parent_id( $product ) );
			}

			if ( 'single' === $type && is_singular( 'product' ) ) {
				if ( isset( $data['single_bar'] ) && is_array( $data['single_bar'] ) && count( $data['single_bar'] ) > 0 ) {
					$manage_stock_check = true;
					if ( in_array( $product->get_type(), WCCT_Common::get_simple_league_product_types(), true ) ) {
						$manage_stock_check = $product->managing_stock();
					}
					$show_bar = apply_filters( 'wcct_trigger_counter_bar_default', true, $type, $data );
					/*
					 * Final Check to show the counter bar on the products that are not in stock
					 */
					if ( ! $product->is_in_stock() ) {
						$show_bar = false;
					}

					// in some cases manage stock returns blank, that's why below handling
					$manage_stock_check = ( $manage_stock_check ) ? true : false;

					if ( 'same' === $goals['type'] && ! $manage_stock_check ) {
						$show_bar = false;
					}
					if ( $show_bar && 'same' === $goals['type'] && in_array( $product->get_type(), WCCT_Common::get_variable_league_product_types(), true ) && WCCT_Common::get_total_stock( $product ) <= 0 ) {
						// use <= for sometimes stock quantity goes to in negative
						$show_bar = false;
					}

					if ( false === WCCT_Core()->public->wcct_restrict_for_booking_oth( $product->get_id() ) ) {
						$campaign_id = key( $data['single_bar'] );
						$single_bar  = current( $data['single_bar'] );

						if ( $position === (int) $single_bar['position'] && $show_bar ) {

							$this->wcct_trigger_counter_bar( $campaign_id, $single_bar, $goals_meta, 'single' );
						}
					}
				}
				if ( isset( $data['custom_text'] ) && is_array( $data['custom_text'] ) && count( $data['custom_text'] ) > 0 ) {
					foreach ( $data['custom_text'] as $campaign_id => $custom_text ) {
						if ( $position === (int) $custom_text['position'] ) {
							$this->wcct_trigger_custom_text( $campaign_id, $custom_text, 'single', $goals_meta );
						}
					}
				}
				if ( isset( $data['single_timer'] ) && is_array( $data['single_timer'] ) && count( $data['single_timer'] ) > 0 ) {
					foreach ( $data['single_timer'] as $campaign_id => $single_timer ) {
						if ( $position === (int) $single_timer['position'] ) {
							$this->wcct_trigger_countdown_timer( $campaign_id, $single_timer, 'single' );
						}
					}
				}

				if ( is_array( $expiry_text ) && count( $expiry_text ) ) {
					foreach ( $expiry_text as $campaign_id => $exp_text ) {
						if ( $position === (int) $exp_text['position'] ) {
							$this->wcct_trigger_countdown_timer_expiry( $campaign_id, $exp_text );
						}
					}
				}
			}
			if ( 'grid' === $type ) {

				if ( isset( $data['grid_bar'] ) && is_array( $data['grid_bar'] ) && count( $data['grid_bar'] ) > 0 ) {
					$manage_stock_check = true;
					if ( in_array( $product->get_type(), WCCT_Common::get_simple_league_product_types(), true ) ) {
						$manage_stock_check = $product->managing_stock();
					}
					// in some cases manage stock returns blank, that's why below handling
					$manage_stock_check = ( $manage_stock_check ) ? true : false;
					$show_bar           = apply_filters( 'wcct_trigger_counter_bar_default', true, $type, $data );
					if ( 'same' === $goals['type'] && ! $manage_stock_check ) {
						$show_bar = false;
					}
					if ( 'same' === $goals['type'] && $show_bar && in_array( $product->get_type(), WCCT_Common::get_variable_league_product_types(), true ) && WCCT_Common::get_total_stock( $product ) <= 0 ) {
						// use <= for sometimes stock quantity goes to in negaitive
						$show_bar = false;
					}
					if ( false === WCCT_Core()->public->wcct_restrict_for_booking_oth( $product->get_id() ) && $show_bar ) {
						foreach ( $data['grid_bar'] as $campaign_id => $grid_bar ) {
							$this->wcct_trigger_counter_bar( $campaign_id, $grid_bar, $goals_meta, 'grid' );
							break;
						}
					}
				}

				if ( isset( $data['grid_timer'] ) && is_array( $data['grid_timer'] ) && count( $data['grid_timer'] ) > 0 ) {
					foreach ( $data['grid_timer'] as $campaign_id => $grid_timer ) {
						$this->wcct_trigger_countdown_timer( $campaign_id, $grid_timer, 'grid' );
					}
				}
			}
			if ( 'cart' === $type ) {
				if ( isset( $data['show_on_cart'] ) && is_array( $data['show_on_cart'] ) && count( $data['show_on_cart'] ) > 0 ) {
					foreach ( $data['show_on_cart'] as $campaign_id => $show_on_cart ) {
						$this->wcct_trigger_countdown_timer( $campaign_id, $show_on_cart, 'cart' );
					}
				}
			}
		}
	}

	public function get_position_for_index( $index ) {

		$locations = array(
			'1' => __( 'Above the Title', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'2' => __( 'Below the Title', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'3' => __( 'Below the Review Rating', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'4' => __( 'Below the Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'5' => __( 'Below Short Description', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'6' => __( 'Below Add to Cart Button', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'7' => __( 'Below Category and SKU', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $locations[ $index ];
	}

	/**
	 * Abstract function to show counter bar depending upon the data given
	 *
	 * @param int $key Campaign ID
	 * @param array $data Campaign Data
	 * @param array $goal_data Goal Meta Data
	 * @param string $call_type Identifier to the call
	 *
	 * @return string
	 */
	public function wcct_trigger_counter_bar( $key, $data, $goal_data, $call_type = 'single', $product_object = false ) {
		global $product, $wcct_style;

		if ( ! $product_object instanceof WC_Product ) {
			$product_object = $product;
		}
		if ( ! $product_object instanceof WC_Product ) {
			return __return_empty_string();
		}
		if ( is_object( $product_object ) && in_array( $product_object->get_type(), array( 'grouped' ), true ) ) {
			return '';
		}

		if ( is_array( $goal_data ) && count( $goal_data ) === 0 ) {
			return '';
		}

		if ( $goal_data['sold_out'] >= $goal_data['quantity'] ) {
			return '';
		}
		$pr_campaign_id = 0;
		if ( $product_object ) {
			$pr_campaign_id = $product_object->get_id();
		}
		$campaign_id = $key;
		$new_key     = $campaign_id . '_' . $pr_campaign_id;

		if ( isset( $data['delay'] ) && 'on' === $data['delay'] ) {
			if ( isset( $data['delay_items'] ) && (int) $data['delay_items'] >= 0 && isset( $goal_data['sold_out'] ) && ( (int) $goal_data['sold_out'] < (int) $data['delay_items'] ) ) {
				return '';
			}

			if ( isset( $data['delay_items_remaining'] ) && (int) $data['delay_items_remaining'] > 0 ) {
				$remaining_units = $goal_data['quantity'] - $goal_data['sold_out'];
				if ( $remaining_units >= $data['delay_items_remaining'] ) {
					return '';
				}
			}
		}

		$timers_class = 'wcct_cbsh_id_';
		if ( 'single' === $call_type ) {
			$timers_class = 'wcct_cbs_id_';
		} elseif ( 'grid' === $call_type ) {
			$timers_class = 'wcct_cbg_id_';
		} elseif ( 'cart' === $call_type ) {
			$timers_class = 'wcct_cbc_id_';
		}

		$wcct_orientation_classes = ' wcct_bar_orientation_ltr';
		if ( isset( $data['orientation'] ) && ( 'rtl' === $data['orientation'] ) ) {
			$wcct_orientation_classes = ' wcct_bar_orientation_rtl';
		}

		$wcct_aria_classes = ' wcct_bar_stripe';
		if ( isset( $data['skin'] ) && 'fill' === $data['skin'] ) {
			$wcct_aria_classes = ' wcct_bar_fill';
		} elseif ( isset( $data['skin'] ) && 'stripe_animate' === $data['skin'] ) {
			$wcct_aria_classes = ' wcct_bar_stripe wcct_bar_stripe_animate';
		}
		$wcct_progress_classes = '';
		if ( isset( $data['edge'] ) && 'smooth' === $data['edge'] ) {
			$wcct_progress_classes .= ' wcct_bar_edge_smooth';
			$wcct_aria_classes     .= ' wcct_bar_edge_smooth';
		}
		$new_height = 12;
		if ( isset( $data['height'] ) && '' !== $data['height'] ) {
			$new_height = (int) $data['height'];
		}

		ob_start();
		if ( isset( $data['border_style'] ) && 'none' !== $data['border_style'] ) {
			echo '.' . $timers_class . $new_key . ' { border-style: ' . $data['border_style'] . '; border-color: ' . ( isset( $data['border_color'] ) ? $data['border_color'] : '#ffffff' ) . '; border-width: ' . ( isset( $data['border_width'] ) ? $data['border_width'] . 'px' : '1px' ) . '; padding: 10px; }';
		}
		echo '.' . $timers_class . $new_key . ' .wcct_progress_aria { ' . ( isset( $data['bg_color'] ) ? ( 'background: ' . $data['bg_color'] . '; ' ) : '' ) . ( isset( $data['label_color'] ) ? 'color: ' . $data['label_color'] . '; ' : '' ) . ' height: ' . $new_height . 'px; }';
		if ( isset( $data['edge'] ) && 'rounded' === $data['edge'] ) {
			echo '.' . $timers_class . $new_key . ' .wcct_progress_aria { border-radius: ' . ( $new_height / 2 ) . 'px; -moz-border-radius: ' . ( $new_height / 2 ) . 'px; -webkit-border-radius: ' . ( $new_height / 2 ) . 'px; }';
		}

		echo '.' . $timers_class . $new_key . ' .wcct_progress_aria .wcct_progress_bar { ' . ( isset( $data['active_color'] ) ? ( 'background-color: ' . $data['active_color'] . '; ' ) : '' ) . '; }';
		echo '.' . $timers_class . $new_key . ' p span { ' . ( isset( $data['active_color'] ) ? ( 'color: ' . $data['active_color'] . '; ' ) : '' ) . '; }';
		$wcct_bar_css = ob_get_clean();
		$wcct_style   .= $wcct_bar_css;

		if ( ( isset( $data['display'] ) && '' !== $data['display'] ) || 'grid' === $call_type ) {
			$sold_percentage = 0;
			if ( is_array( $goal_data ) && isset( $goal_data['sold_out'] ) && $goal_data['sold_out'] > 0 ) {
				$sold_per = (float) ( $goal_data['sold_out'] / $goal_data['quantity'] ) * 100;
				if ( is_float( $sold_per ) ) {
					$sold_percentage = ceil( $sold_per );
				} else {
					$sold_percentage = $sold_per;
				}
			}
			$remaining_percentage = $sold_percentage;
			if ( isset( $data['orientation'] ) && 'rtl' === $data['orientation'] ) {
				$remaining_percentage = ( 100 - $sold_percentage );
			}

			$is_counter_bar_display = apply_filters( 'wcct_trigger_counter_bar', true, (int) ( isset( $goal_data['sold_out'] ) ? $goal_data['sold_out'] : 0 ) );

			if ( $is_counter_bar_display ) {
				?>
                <div class="wcct_counter_bar_wrap">
                    <div class="wcct_counter_bar <?php echo $timers_class . $new_key; ?>" data-type="<?php echo $call_type; ?>" data-campaign-id="<?php echo $campaign_id; ?>">
						<?php
						$display = sprintf( '<div class="wcct_progress_aria %s"><div class="wcct_progress_bar %s" data-id="%s" role="progressbar" aria-valuenow="%s" aria-valuemin="0" aria-valuemax="100"></div></div>', $wcct_aria_classes, $wcct_progress_classes . $wcct_orientation_classes, $timers_class . $new_key, $remaining_percentage );

						$display = str_replace( "\n", '', $display );
						$output  = isset( $data['display'] ) ? $data['display'] : '';
						$output  = str_replace( '{{counter_bar}}', $display, $output );
						$output  = str_replace( '{{sold_units}}', $this->wcct_merge_tags( $data, $goal_data, 'sold_units' ), $output );
						$output  = str_replace( '{{remaining_units}}', $this->wcct_merge_tags( $data, $goal_data, 'remaining_units' ), $output );
						$output  = str_replace( '{{total_units}}', $this->wcct_merge_tags( $data, $goal_data, 'total_units' ), $output );
						$output  = str_replace( '{{sold_percentage}}', $this->wcct_merge_tags( $data, $goal_data, 'sold_percentage' ), $output );
						$output  = str_replace( '{{remaining_percentage}}', $this->wcct_merge_tags( $data, $goal_data, 'remaining_percentage' ), $output );
						$output  = str_replace( '{{sold_units_price}}', $this->wcct_merge_tags( $data, $goal_data, 'sold_units_price' ), $output );
						$output  = str_replace( '{{total_units_price}}', $this->wcct_merge_tags( $data, $goal_data, 'total_units_price' ), $output );
						$output  = WCCT_Merge_Tags::maybe_parse_merge_tags( $output );

						if ( strpos( $output, '{{countdown_timer}}' ) !== false ) {
							$timer_data = array(
								'label_color'     => '#dd3333',
								'label_font'      => '13',
								'end_timestamp'   => $data['end_timestamp'],
								'start_timestamp' => $data['start_timestamp'],
								'full_instance'   => $data,
							);
							$get_timer  = WCCT_Core()->appearance->wcct_maybe_parse_timer( $campaign_id, $timer_data, 'counter_bar' );
							$output     = str_replace( '{{countdown_timer}}', $get_timer, $output );
						}
						$output = $this->wcct_maybe_decode_campaign_time_merge_tags( $output, $data );
						$output = $this->wcct_content_without_p( $output );
						echo $output;
						?>
                    </div>
                </div>
				<?php
			}
		}

		return '';
	}

	/**
	 * Generate Output of Merge tags like {{Sold_out}}
	 *
	 * @param type $data
	 * @param type $goal_data
	 * @param type $merge_tags
	 *
	 * @return type
	 * @global type $product
	 *
	 */
	public function wcct_merge_tags( $data, $goal_data, $merge_tags = 'sold_units' ) {
		global $product;
		$output        = array();
		$goal_price    = 0;
		$goal_quantity = isset( $goal_data['quantity'] ) ? (int) $goal_data['quantity'] : 0;
		$goal_sold_out = isset( $goal_data['sold_out'] ) ? (int) $goal_data['sold_out'] : 0;

		if ( $product && is_object( $product ) && $product instanceof WC_Product ) {
			if ( in_array( $product->get_type(), WCCT_Common::get_variable_league_product_types() ) ) {
				$children = $product->get_children();

				if ( $children && is_array( $children ) && count( $children ) > 0 ) {
					$child         = $children[0];
					$child_product = wc_get_product( $child );
					$goal_price    = (float) $child_product->get_price();
				} else {
					$goal_price = (float) $product->get_price();
				}
			} else {

				$goal_price = (float) $product->get_price();
			}
		}

		if ( 'sold_units' === $merge_tags ) {
			if ( 0 === $goal_sold_out ) {
				$output['sold_units'] = '0';
			} else {
				$output['sold_units'] = $goal_sold_out;
			}
		} elseif ( 'total_units' === $merge_tags ) {
			$total_units           = (int) $goal_quantity;
			$output['total_units'] = $total_units;
		} elseif ( 'remaining_units' === $merge_tags ) {
			$sold_units  = ( 0 === $goal_sold_out ) ? '0' : $goal_sold_out;
			$total_units = ( $goal_quantity ) ? (int) $goal_quantity : '0';
			if ( $total_units > 0 ) {
				if ( ( $total_units - $sold_units ) >= '0' ) {
					$output['remaining_units'] = $total_units - $sold_units;
				} else {
					$output['remaining_units'] = '0';
				}
			}
		} elseif ( 'sold_percentage' === $merge_tags ) {
			if ( 0 === $goal_sold_out ) {
				$output['sold_percentage'] = '0%';
			} else {
				$sold_per = ( $goal_sold_out / $goal_quantity ) * 100;

				if ( is_float( $sold_per ) ) {
					$output['sold_percentage'] = ceil( $sold_per ) . '%';
				} else {
					$output['sold_percentage'] = $sold_per . '%';
				}
			}
		} elseif ( 'remaining_percentage' === $merge_tags ) {
			if ( 0 === $goal_sold_out ) {
				$output['remaining_percentage'] = '100%';
			} else {
				$sold_per   = (float) ( $goal_sold_out / $goal_quantity ) * 100;
				$remain_per = 100 - $sold_per;
				if ( is_float( $remain_per ) ) {
					$output['remaining_percentage'] = ceil( $remain_per ) . '%';
				} else {
					$output['remaining_percentage'] = $remain_per . '%';
				}
			}
		} elseif ( 'sold_units_price' === $merge_tags ) {
			if ( 0 === $goal_sold_out ) {
				$output['sold_units_price'] = wc_price( 0 );
			} else {
				$sold_price                 = (float) ( $goal_price * $goal_sold_out );
				$output['sold_units_price'] = wc_price( $sold_price );
			}
		} elseif ( 'total_units_price' === $merge_tags ) {
			$total_units_price           = (float) ( $goal_price * $goal_quantity );
			$output['total_units_price'] = wc_price( $total_units_price );
		}
		wcct_force_log( "get all merge tags  \n\r" . print_r( $output, true ) );

		return ( isset( $output[ $merge_tags ] ) && '' !== $output[ $merge_tags ] ) ? $output[ $merge_tags ] : '';
	}

	/**
	 * Print Countdown Timer For Grid and Single
	 *
	 * @param: $key
	 * @param: $data
	 * @param: $call_type
	 *
	 * @return
	 * @global: $product
	 * @global: $wcct_style
	 *
	 */
	public function wcct_maybe_parse_timer( $key, $data, $call_type = 'single', $echo = false ) {
		global $product;
		$campaign_id   = $key;
		$prCampaing_id = 0;
		if ( $product && is_object( $product ) && $product instanceof WC_Product ) {
			$prCampaing_id = $product->get_id();
		}

		if ( 'sticky_header' === $call_type ) {
			$new_key = $campaign_id . '_0_0';
		} elseif ( 'sticky_footer' === $call_type ) {
			$new_key = $campaign_id . '_0_1';
		} else {
			$new_key = $campaign_id . '_' . $prCampaing_id;
		}

		$timers_class = 'wcct_ctsh_id_';
		if ( 'single' === $call_type ) {
			$timers_class = 'wcct_cts_id_';
		} elseif ( 'grid' === $call_type ) {
			$timers_class = 'wcct_ctg_id_';
		} elseif ( 'cart' === $call_type ) {
			$timers_class = 'wcct_ctc_id_';
		} elseif ( 'sticky_header' === $call_type ) {
			$timers_class = 'wcct_ctsh_id_';
		} elseif ( 'sticky_footer' === $call_type ) {
			$timers_class = 'wcct_ctsf_id_';
		}
		if ( isset( $data['timer_font'] ) && 'inherit' === $data['timer_font'] ) {
			unset( $data['timer_font'] );
		}

		$labels_data       = $data['full_instance']['timer_labels'];
		$labels            = $labels_data;
		$labels_final      = array();
		$labels_final['d'] = isset( $labels['label_days'] ) ? $labels['label_days'] : 'days';
		$labels_final['h'] = isset( $labels['label_hrs'] ) ? $labels['label_hrs'] : 'hrs';
		$labels_final['m'] = isset( $labels['label_mins'] ) ? $labels['label_mins'] : 'mins';
		$labels_final['s'] = isset( $labels['label_secs'] ) ? $labels['label_secs'] : 'secs';
		$is_show_days      = apply_filters( 'wcct_always_show_days_on_timers', true );
		$is_show_days      = apply_filters( "wcct_always_show_days_on_timers_{$campaign_id}", $is_show_days );
		$is_show_days_val  = ( $is_show_days ? 'yes' : 'no' );
		$is_show_hrs       = apply_filters( 'wcct_always_show_hrs_on_timers', true );
		$is_show_hrs       = apply_filters( "wcct_always_show_hrs_on_timers_{$campaign_id}", $is_show_hrs );
		$is_show_hrs_val   = ( $is_show_hrs ? 'yes' : 'no' );

		ob_start();

		$element_all_attr = 'class="wcct_countdown_timer ' . $timers_class . $new_key . ' wcct_timer wcct_countdown_default wcct_abstract_timer" data-is-days="' . $is_show_days_val . '" data-is-hrs="' . $is_show_hrs_val . '" data-days="' . $labels_final['d'] . '" data-hrs="' . $labels_final['h'] . '" data-mins="' . $labels_final['m'] . '" data-secs="' . $labels_final['s'] . '" data-campaign-id="' . $campaign_id . '" data-type="' . $call_type . '"';

		echo '<div ' . $element_all_attr . '>';

		/**
		 * Comparing end timestamp with the current timestamp
		 * and getting difference
		 */
		$date_obj            = new DateTime();
		$current_Date_object = clone $date_obj;
		$current_Date_object->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
		$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
		$date_obj->setTimestamp( $data['end_timestamp'] );

		$interval = $current_Date_object->diff( $date_obj );
		$x        = $interval->format( '%R' );
		$is_left  = $x;

		if ( '+' === $is_left ) {
			$total_seconds_left = 0;
			$total_seconds_left = $total_seconds_left + ( YEAR_IN_SECONDS * $interval->y );
			$total_seconds_left = $total_seconds_left + ( MONTH_IN_SECONDS * $interval->m );
			$total_seconds_left = $total_seconds_left + ( DAY_IN_SECONDS * $interval->d );
			$total_seconds_left = $total_seconds_left + ( HOUR_IN_SECONDS * $interval->h );
			$total_seconds_left = $total_seconds_left + ( MINUTE_IN_SECONDS * $interval->i );
			$total_seconds_left = $total_seconds_left + $interval->s;

			$output = '<div class="wcct_timer_wrap" data-date="' . $data['end_timestamp'] . '" data-left="' . $total_seconds_left . '" data-timer-skin="default"></div>';
			$output = $this->wcct_content_without_p( $output );

			echo $output;
		}

		echo '</div>';

		if ( $echo === true ) {
			echo ob_get_clean();

			return;
		} else {
			return ob_get_clean();
		}
	}

	public function wcct_content_without_p( $content ) {
		$content = nl2br( $content );
		remove_filter( 'wcct_the_content', 'wpautop' );
		$content = apply_filters( 'wcct_the_content', WCCT_Merge_Tags::maybe_parse_merge_tags( $content ) );
		add_filter( 'wcct_the_content', 'wpautop' );

		return $content;
	}

	public function wcct_maybe_decode_campaign_time_merge_tags( $content, $campaign_data ) {
		$get_all = array( 'campaign_start_date', 'campaign_end_date' );
		//iterating over all the merge tags
		if ( $get_all && is_array( $get_all ) && count( $get_all ) > 0 ) {
			foreach ( $get_all as $tag ) {

				if ( strpos( $content, '{{' . $tag ) !== false ) {

					$matches = array();
					$re      = sprintf( '/\{{%s(.*?)\}}/', $tag );

					$str = $content;

					//trying to find match w.r.t current tag
					preg_match_all( $re, $str, $matches );

					//if match found
					if ( $matches && is_array( $matches ) && count( $matches ) > 0 ) {

						if ( ! isset( $matches[0] ) ) {
							return;
						}

						//iterate over the found matches
						foreach ( $matches[0] as $exact_match ) {

							//preserve old match
							$old_match = $exact_match;

							//replace the current tag with the square brackets [shortcode compatible]
							$exact_match = str_replace( '{{' . $tag, '[wcct_' . $tag, $exact_match );

							if ( $tag == 'campaign_start_date' ) {
								$get_timestamp = ( isset( $campaign_data['start_timestamp'] ) ? $campaign_data['start_timestamp'] : null );

							} else {
								$get_timestamp = ( isset( $campaign_data['end_timestamp'] ) ? $campaign_data['end_timestamp'] : null );

							}
							$timetsmp_str = ' timestamp="' . $get_timestamp . '"';
							$exact_match  = str_replace( '}}', ' ' . $timetsmp_str . ' ]', $exact_match );
							$exact_match  = do_shortcode( $exact_match );
							$content      = str_replace( $old_match, $exact_match, $content );
						}
					}
				}
			}
		}

		return $content;
	}

	public function wcct_trigger_custom_text( $key, $data, $call_type = 'single', $goal_data = array() ) {
		global $product, $wcct_style;
		$campaign_id   = $key;
		$prCampaing_id = 0;
		if ( $product && is_object( $product ) && $product instanceof WC_Product ) {
			$prCampaing_id = $product->get_id();
		}

		if ( isset( $data['description'] ) && $data['description'] == '' ) {
			return;
		}

		$new_key      = $campaign_id . '_' . $prCampaing_id;
		$timers_class = 'wcct_cu_text_sh_id_';
		if ( $call_type == 'single' ) {
			$timers_class = 'wcct_cu_text_s_id_';
		} elseif ( $call_type == 'grid' ) {
			$timers_class = 'wcct_cu_text_g_id_';
		} elseif ( $call_type == 'cart' ) {
			$timers_class = 'wcct_cu_text_c_id_';
		}

		ob_start();
		if ( isset( $data['border_style'] ) && $data['border_style'] != 'none' ) {
			echo '.' . $timers_class . $new_key . '{ border-style: ' . $data['border_style'] . '; border-color: ' . ( isset( $data['border_color'] ) ? $data['border_color'] : '#ffffff' ) . '; border-width: ' . ( isset( $data['border_width'] ) ? $data['border_width'] . 'px' : '1px' ) . '; padding: 10px 15px; }';
		}
		echo '.' . $timers_class . $new_key . '{ ' . ( ( isset( $data['bg_color'] ) && $data['bg_color'] != '' ) ? ( 'background: ' . $data['bg_color'] . '; ' ) : '' ) . ( isset( $data['text_color'] ) ? 'color: ' . $data['text_color'] . '; ' : '' ) . ( isset( $data['font_size'] ) ? ( 'font-size: ' . $data['font_size'] . 'px; ' ) : '' ) . ' }';
		echo '.' . $timers_class . $new_key . ' p{ ' . ( isset( $data['font_size'] ) ? ( 'font-size: ' . $data['font_size'] . 'px; ' ) : '' ) . ' }';
		$wcct_cust_text_css = ob_get_clean();
		$wcct_style         .= $wcct_cust_text_css;
		?>
        <div class="wcct_custom_text_wrap">
            <div class="wcct_custom_text <?php echo $timers_class . $new_key; ?>" data-campaign-id="<?php echo $campaign_id; ?>" data-type="<?php echo $call_type; ?>">
				<?php
				$output = $data['description'];
				if ( strpos( $output, '{{countdown_timer}}' ) !== false ) {

					$timer_data = array(
						'label_color'     => '#dd3333',
						'label_font'      => '13',
						'end_timestamp'   => $data['end_timestamp'],
						'start_timestamp' => $data['start_timestamp'],
						'full_instance'   => $data,
					);

					$get_timer = WCCT_Core()->appearance->wcct_maybe_parse_timer( $campaign_id, $timer_data, 'custom_text' );
					$output    = str_replace( '{{countdown_timer}}', $get_timer, $output );
				}

				/** Decoding counter bar shortcodes */
				if ( is_array( $goal_data ) && count( $goal_data ) > 0 ) {
					$output = str_replace( '{{sold_units}}', $this->wcct_merge_tags( $data, $goal_data, 'sold_units' ), $output );
					$output = str_replace( '{{remaining_units}}', $this->wcct_merge_tags( $data, $goal_data, 'remaining_units' ), $output );
					$output = str_replace( '{{total_units}}', $this->wcct_merge_tags( $data, $goal_data, 'total_units' ), $output );
					$output = str_replace( '{{sold_percentage}}', $this->wcct_merge_tags( $data, $goal_data, 'sold_percentage' ), $output );
					$output = str_replace( '{{remaining_percentage}}', $this->wcct_merge_tags( $data, $goal_data, 'remaining_percentage' ), $output );
					$output = str_replace( '{{sold_units_price}}', $this->wcct_merge_tags( $data, $goal_data, 'sold_units_price' ), $output );
					$output = str_replace( '{{total_units_price}}', $this->wcct_merge_tags( $data, $goal_data, 'total_units_price' ), $output );
					$output = WCCT_Merge_Tags::maybe_parse_merge_tags( $output );
				}

				$output = $this->wcct_maybe_decode_campaign_time_merge_tags( $output, $data );
				echo $this->wcct_content_without_p( $output );
				?>
            </div>
        </div>
		<?php
	}

	/**
	 * Print Countdown Timer For Grid and Single
	 *
	 * @param $key
	 * @param $data
	 * @param $call_type
	 *
	 * @return
	 * @global $product
	 * @global $wcct_style
	 *
	 */
	public function wcct_trigger_countdown_timer( $key, $data, $call_type = 'single' ) {
		global $product, $wcct_style;
		$campaign_id   = $key;
		$prCampaing_id = 0;
		if ( $product && is_object( $product ) && $product instanceof WC_Product ) {
			$prCampaing_id = $product->get_id();
		}

		$reduce_font_size_mobile = 0;
		if ( true == WCCT_Core()->is_mobile ) {
			$reduce_font_size_mobile = isset( $data['timer_mobile'] ) ? $data['timer_mobile'] : 0;
		}

		$delay_hrs = '';
		if ( isset( $data['delay'] ) && $data['delay'] == 'on' && isset( $data['delay_hrs'] ) && (int) $data['delay_hrs'] >= 0 ) {

			$delay_hrs = $data['delay_hrs'];

			$datetimeob   = new DateTime( 'now', new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
			$current_time = $datetimeob->getTimestamp();

			$diff     = $data['end_timestamp'] - $current_time;
			$diff_hrs = $diff / 3600;
			if ( $diff_hrs > 0 && $diff_hrs > $data['delay_hrs'] ) {
				//              return;
			}
		}

		if ( $call_type == 'sticky_header' ) {
			$new_key = $campaign_id . '_0_0';
		} elseif ( $call_type == 'sticky_footer' ) {
			$new_key = $campaign_id . '_0_1';
		} else {
			$new_key = $campaign_id . '_' . $prCampaing_id;
		}

		$timers_class = 'wcct_ctsh_id_';
		if ( $call_type == 'single' ) {
			$timers_class = 'wcct_cts_id_';
		} elseif ( $call_type == 'grid' ) {
			$timers_class = 'wcct_ctg_id_';
		} elseif ( $call_type == 'cart' ) {
			$timers_class = 'wcct_ctc_id_';
		} elseif ( $call_type == 'sticky_header' ) {
			$timers_class    = 'wcct_ctsh_id_';
			$data['display'] = '{{countdown_timer}}';
		} elseif ( $call_type == 'sticky_footer' ) {
			$timers_class    = 'wcct_ctsf_id_';
			$data['display'] = '{{countdown_timer}}';
		}

		$timer_style      = '';
		$timer_delay_attr = '';
		if ( in_array( $timers_class, array( 'wcct_cts_id_' ) ) ) {
			if ( $delay_hrs != '' ) {
				$timer_delay_attr = 'data-delay="' . ( (int) $delay_hrs * HOUR_IN_SECONDS ) . '"';
			}
		}

		if ( isset( $data['timer_font'] ) && $data['timer_font'] == 'inherit' ) {
			unset( $data['timer_font'] );
		}

		$labels['d'] = isset( $data['label_days'] ) ? $data['label_days'] : 'days';
		$labels['h'] = isset( $data['label_hrs'] ) ? $data['label_hrs'] : 'hrs';
		$labels['m'] = isset( $data['label_mins'] ) ? $data['label_mins'] : 'mins';
		$labels['s'] = isset( $data['label_secs'] ) ? $data['label_secs'] : 'secs';

		$new_height = 8;
		$new_height += ( isset( $data['timer_font'] ) ? round( $data['timer_font'] * 1.2 ) : 0 );
		$new_height += ( isset( $data['label_font'] ) ? round( $data['label_font'] * 1.5 ) : 0 );
		$new_height += 6;

		// reducing defined pixels for mobile
		if ( $reduce_font_size_mobile > 0 ) {
			$new_height         = round( $new_height * ( $reduce_font_size_mobile / 100 ), 1 );
			$data['timer_font'] = round( $data['timer_font'] * ( $reduce_font_size_mobile / 100 ), 1 );
			$data['label_font'] = round( $data['label_font'] * ( $reduce_font_size_mobile / 100 ), 1 );
		}

		ob_start();
		if ( $data['skin'] == 'round_fill' ) {
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_round_wrap { ' . ( isset( $data['bg_color'] ) ? ( 'background: ' . $data['bg_color'] . '; ' ) : '' ) . ( isset( $data['label_color'] ) ? 'color: ' . $data['label_color'] . '; ' : '' ) . ' height: ' . $new_height . 'px; width: ' . $new_height . 'px; }';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_round_wrap .wcct_wrap_border { ' . ( isset( $data['bg_color'] ) ? ( 'border-color: ' . $data['bg_color'] . '; ' ) : '' ) . '}';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_round_wrap span { ' . ( isset( $data['timer_font'] ) ? ( 'font-size: ' . $data['timer_font'] . 'px; ' ) : '' ) . ' }';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_round_wrap .wcct_table_cell { ' . ( isset( $data['label_font'] ) ? ( 'font-size: ' . $data['label_font'] . 'px; ' ) : '' ) . ' }';
		} elseif ( $data['skin'] == 'square_fill' ) {
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_square_wrap { ' . ( isset( $data['bg_color'] ) ? ( 'background: ' . $data['bg_color'] . '; ' ) : '' ) . ( isset( $data['label_color'] ) ? 'color: ' . $data['label_color'] . '; ' : '' ) . ' height: ' . $new_height . 'px; width: ' . $new_height . 'px; }';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_square_wrap .wcct_wrap_border { ' . ( isset( $data['bg_color'] ) ? ( 'border-color: ' . $data['bg_color'] . '; ' ) : '' ) . '}';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_square_wrap span { ' . ( isset( $data['timer_font'] ) ? ( 'font-size: ' . $data['timer_font'] . 'px; ' ) : '' ) . ' }';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_square_wrap .wcct_table_cell { ' . ( isset( $data['label_font'] ) ? ( 'font-size: ' . $data['label_font'] . 'px; ' ) : '' ) . ' }';
		} elseif ( $data['skin'] == 'round_ghost' ) {
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_round_wrap { ' . ( isset( $data['bg_color'] ) ? ( 'border-color: ' . $data['bg_color'] . '; ' ) : '' ) . ( isset( $data['label_color'] ) ? 'color: ' . $data['label_color'] . '; ' : '' ) . ' height: ' . $new_height . 'px; width: ' . $new_height . 'px; }';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_round_wrap .wcct_wrap_border { ' . ( isset( $data['bg_color'] ) ? ( 'border-color: ' . $data['bg_color'] . '; ' ) : '' ) . '}';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_round_wrap span { ' . ( isset( $data['timer_font'] ) ? ( 'font-size: ' . $data['timer_font'] . 'px; ' ) : '' ) . ' }';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_round_wrap .wcct_table_cell { ' . ( isset( $data['label_font'] ) ? ( 'font-size: ' . $data['label_font'] . 'px; ' ) : '' ) . ' }';
		} elseif ( $data['skin'] == 'square_ghost' ) {
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_square_wrap { ' . ( isset( $data['bg_color'] ) ? ( 'border-color: ' . $data['bg_color'] . '; ' ) : '' ) . ( isset( $data['label_color'] ) ? 'color: ' . $data['label_color'] . '; ' : '' ) . ' height: ' . $new_height . 'px; width: ' . $new_height . 'px; }';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_square_wrap .wcct_wrap_border { ' . ( isset( $data['bg_color'] ) ? ( 'border-color: ' . $data['bg_color'] . '; ' ) : '' ) . '}';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_square_wrap span { ' . ( isset( $data['timer_font'] ) ? ( 'font-size: ' . $data['timer_font'] . 'px; ' ) : '' ) . ' }';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_square_wrap .wcct_table_cell { ' . ( isset( $data['label_font'] ) ? ( 'font-size: ' . $data['label_font'] . 'px; ' ) : '' ) . ' }';
		} elseif ( $data['skin'] == 'highlight_1' ) {
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_highlight_1_wrap { ' . ( isset( $data['bg_color'] ) ? ( 'background: ' . $data['bg_color'] . '; ' ) : '' ) . ( isset( $data['label_color'] ) ? 'color: ' . $data['label_color'] . '; ' : '' ) . ( isset( $data['label_font'] ) ? ( 'font-size: ' . $data['label_font'] . 'px; ' ) : '' ) . ' }';
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap .wcct_highlight_1_wrap span { ' . ( isset( $data['timer_font'] ) ? ( 'font-size: ' . $data['timer_font'] . 'px; ' ) : '' ) . ' }';
		} else {
			echo '.' . $timers_class . $new_key . '.wcct_countdown_' . $data['skin'] . ' .wcct_timer_wrap { ' . ( isset( $data['bg_color'] ) ? ( 'background: ' . $data['bg_color'] . '; ' ) : '' ) . ( isset( $data['label_color'] ) ? 'color: ' . $data['label_color'] . '; ' : '' ) . ( isset( $data['timer_font'] ) ? ( 'font-size: ' . $data['timer_font'] . 'px; ' ) : '' ) . ' }';
		}

		if ( isset( $data['border_style'] ) && $data['border_style'] != 'none' && isset( $data['border_color'] ) && $data['border_color'] != '' ) {
			echo '.' . $timers_class . $new_key . ' { padding: 10px; border: ' . ( ( isset( $data['border_width'] ) && $data['border_width'] != '' ) ? $data['border_width'] : '1' ) . 'px ' . $data['border_style'] . ' ' . $data['border_color'] . ' }';
		}
		$wcct_timer_css = ob_get_clean();
		$wcct_style     .= $wcct_timer_css;

		if ( $data['skin'] == 'default' ) {
			$is_show_days_val = 'no';
			$is_show_hrs_val  = 'no';
		} else {
			$is_show_days     = apply_filters( 'wcct_always_show_days_on_timers', true );
			$is_show_days     = apply_filters( "wcct_always_show_days_on_timers_{$campaign_id}", $is_show_days );
			$is_show_days_val = ( $is_show_days ? 'yes' : 'no' );

			$is_show_hrs     = apply_filters( 'wcct_always_show_hrs_on_timers', true );
			$is_show_hrs     = apply_filters( "wcct_always_show_hrs_on_timers_{$campaign_id}", $is_show_hrs );
			$is_show_hrs_val = ( $is_show_hrs ? 'yes' : 'no' );
		}

		if ( isset( $data['display'] ) && '' !== $data['display'] ) {
			$element_all_attr = 'class="wcct_countdown_timer ' . $timers_class . $new_key . ' wcct_timer wcct_countdown_' . $data['skin'] . '" data-is-days="' . $is_show_days_val . '" data-is-hrs="' . $is_show_hrs_val . '" data-days="' . $labels['d'] . '" data-hrs="' . $labels['h'] . '" data-mins="' . $labels['m'] . '" data-secs="' . $labels['s'] . '" data-campaign-id="' . $campaign_id . '" data-type="' . $call_type . '"';
			echo '<div class="wcct_countdown_timer_wrap">';
			echo '<div ' . $element_all_attr . $timer_style . $timer_delay_attr . '>';

			/**
			 * Comparing end timestamp with the current timestamp
			 * and getting difference
			 */
			$date_obj            = new DateTime();
			$current_Date_object = clone $date_obj;
			$current_Date_object->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
			$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
			$date_obj->setTimestamp( $data['end_timestamp'] );

			$interval = $current_Date_object->diff( $date_obj );
			$x        = $interval->format( '%R' );

			$is_left = $x;
			if ( '+' === $is_left ) {
				if ( isset( $this->campaign_end_time[ $campaign_id ] ) && ! empty( $this->campaign_end_time[ $campaign_id ] ) ) {
					$total_seconds_left = $this->campaign_end_time[ $campaign_id ];
				} else {
					$total_seconds_left = 0;
					$total_seconds_left = $total_seconds_left + ( YEAR_IN_SECONDS * $interval->y );
					$total_seconds_left = $total_seconds_left + ( MONTH_IN_SECONDS * $interval->m );
					$total_seconds_left = $total_seconds_left + ( DAY_IN_SECONDS * $interval->d );
					$total_seconds_left = $total_seconds_left + ( HOUR_IN_SECONDS * $interval->h );
					$total_seconds_left = $total_seconds_left + ( MINUTE_IN_SECONDS * $interval->i );
					$total_seconds_left = $total_seconds_left + $interval->s;

					$this->campaign_end_time[ $campaign_id ] = $total_seconds_left;
				}

				$custom_attr = apply_filters( 'wcct_custom_attributes_on_countdown_timer', '', $campaign_id );
				$display     = '<div class="wcct_timer_wrap" data-date="' . $data['end_timestamp'] . '" data-left="' . $total_seconds_left . '" data-timer-skin="' . $data['skin'] . '" ' . $custom_attr . '></div>';
				$output      = str_replace( '{{countdown_timer}}', $display, $data['display'] );

				if ( $data['skin'] == 'default' ) {
					$output = nl2br( $output );
					remove_filter( 'wcct_the_content', 'wpautop' );
					$output = apply_filters( 'wcct_the_content', WCCT_Merge_Tags::maybe_parse_merge_tags( $output ) );
					add_filter( 'wcct_the_content', 'wpautop' );
				} else {
					$output = apply_filters( 'wcct_the_content', WCCT_Merge_Tags::maybe_parse_merge_tags( $output ) );
				}
				$output = $this->wcct_maybe_decode_campaign_time_merge_tags( $output, $data );
				echo $output;
			}
			echo '</div>';
			echo '</div>';
		}
	}

	/**
	 * Display Expiry text when Campaign is expire
	 *
	 * @param type $campaign_id
	 * @param type $data
	 */
	public function wcct_trigger_countdown_timer_expiry( $campaign_id, $data ) {
		?>
        <div class="wcct_counter_timer_expiry">
			<?php echo isset( $data['text'] ) ? apply_filters( 'wcct_the_content', $data['text'] ) : ''; ?>
        </div>
		<?php
	}

	/**
	 * @deprecated position
	 */
	public function wcct_position_below_meta() {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $post->ID );
		$cp_data     = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		$this->wcct_triggers( $cp_data, 7 );
	}

	/**
	 * @deprecated position
	 */
	public function wcct_position_above_tab_area() {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );

		$actions = WCCT_Core()->public->wcct_genrate_actions( $post->ID );
		$cp_data = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		echo '<div class="wcct_clear"></div>';
		$this->wcct_triggers( $cp_data, 8 );
	}

	/**
	 * @deprecated position
	 */
	public function wcct_position_below_related_products() {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $post->ID );
		$cp_data     = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		$this->wcct_triggers( $cp_data, 11 );
	}

	public function wcct_remove_add_to_cart_btn( $btn_links ) {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return $btn_links;
		}
		if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $product->get_id() ) ) {
			return $btn_links;
		}
		$actions = WCCT_Core()->public->wcct_genrate_actions( $product->get_id() );

		if ( isset( $actions['add_to_cart'] ) && $actions['add_to_cart'] === 'hide' ) {
			return '';
		}

		return $btn_links;
	}

	public function wcct_bar_timer_show_on_grid( $show_type = false ) {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $post->ID );

		if ( ! is_array( $single_data ) ) {
			return;
		}

		$cp_data = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		if ( 'bar' === $show_type ) {
			unset( $cp_data['campaign']['grid_timer'] );
		}
		if ( 'timer' === $show_type ) {
			unset( $cp_data['campaign']['grid_bar'] );
		}
		$this->wcct_triggers( $cp_data, 0, 'grid' );
	}

	/**
	 * Modify Add to cart button text of grid products
	 * Only change for product types: simple, external & simple-subscription
	 *
	 * @param $add_to_text
	 *
	 * @return mixed
	 */
	public function wcct_change_add_to_cart_text( $add_to_text ) {
		global $product;

		if ( $product instanceof WC_Product ) {
			$data = WCCT_Core()->public->get_single_campaign_pro_data( $product->get_id() );
			if ( ! empty( $data['add_to_cart_text'] ) ) {
				$get_current = current( $data['add_to_cart_text'] );
				if ( is_array( $get_current ) && isset( $get_current['add_to_cart_btn_exclude'] ) && is_array( $get_current['add_to_cart_btn_exclude'] ) && in_array( $product->get_type(), $get_current['add_to_cart_btn_exclude'] ) ) {
					return $add_to_text;
				}
				if ( is_array( $get_current ) && isset( $get_current['button_text'] ) && ! empty( $get_current['button_text'] ) ) {
					return $get_current['button_text'];
				}
			}
		}

		return $add_to_text;
	}

	/**
	 * Modify Add to cart button text on single product page
	 * Only change for product types: simple, variable, external, group, bundle, subscription & simple-subscription
	 *
	 * @param $add_to_text
	 *
	 * @return mixed
	 */
	public function wcct_change_single_add_to_cart_text( $add_to_text ) {
		global $product;

		if ( $product instanceof WC_Product ) {
			$data = WCCT_Core()->public->get_single_campaign_pro_data( $product->get_id() );

			if ( ! empty( $data['add_to_cart_text'] ) ) {
				$get_current = current( $data['add_to_cart_text'] );
				if ( is_array( $get_current ) && isset( $get_current['button_text'] ) && ! empty( $get_current['button_text'] ) ) {
					return $get_current['button_text'];
				}
			}
		}

		return $add_to_text;
	}

	public function wcct_woocommerce_product_is_visible( $visible, $id ) {
		if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $id ) ) {
			return $visible;
		}
		$actions = WCCT_Core()->public->wcct_genrate_actions( $id );

		if ( isset( $actions['visibility'] ) && in_array( $actions['visibility'], array(
				'catalog',
				'both',
				'hidden',
				'search',
			) ) ) {
			if ( $actions['visibility'] == 'hidden' ) {
				return false;
			} elseif ( is_search() ) {
				if ( $actions['visibility'] == 'both' || $actions['visibility'] == 'search' ) {
					$visible = true;
				} else {
					$visible = false;
				}
			} else {
				if ( $actions['visibility'] == 'both' || $actions['visibility'] == 'catalog' ) {
					$visible = true;
				} else {
					$visible = false;
				}
			}
		}

		return $visible;
	}

	public function wcct_woocommerce_is_purchasable( $visible, $product ) {
		if ( ! $product instanceof WC_Product ) {
			return $visible;
		}

		$product_id = WCCT_Core()->public->wcct_get_product_parent_id( $product );
		$actions    = WCCT_Core()->public->wcct_genrate_actions( $product_id );
		if ( isset( $actions['add_to_cart'] ) && $actions['add_to_cart'] == 'hide' ) {
			return false;
		}

		return $visible;
	}

	public function add_header_info( $content ) {
		$check = apply_filters( 'wcct_add_content_in_header_info', true, $content );
		if ( true === $check ) {
			array_push( $this->header_info, $content );
		}
	}

	public function wcct_print_html_header_info() {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			ob_start();
			if ( $this->header_info && is_array( $this->header_info ) && count( $this->header_info ) > 0 ) {
				foreach ( $this->header_info as $key => $info_row ) {
					?>
                    <li id="wp-admin-bar-wcct_admin_page_node_<?php echo $key; ?>">
					<span class="ab-item">
						<?php echo $info_row; ?>
					</span>
                    </li>
					<?php
				}
			}
			$wcct_header_info = ob_get_clean();

			if ( ! empty( $wcct_header_info ) ) {
				echo "<!--googleoff: all--><div class='wcct_header_passed' style='display: none;'>" . $wcct_header_info . '</div><!--googleon: all-->';
			}
		}
	}

	public function add_info_localized( $localized_data ) {
		if ( $this->header_info && is_array( $this->header_info ) && count( $this->header_info ) > 0 ) {
			$localized_data['info'] = $this->header_info;
		}

		return $localized_data;
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

		$script = 'var wcct_info = ' . wp_json_encode( $l10n ) . ';';
		?>
        <script type="text/javascript">
			<?php echo $script; ?>
        </script>
		<?php
	}

	public function shortcode_campaign_start_date( $attrs ) {
		$date_format = apply_filters( 'wcct_global_date_time_format', 'M j' );
		$atts        = shortcode_atts( array(
			'format'     => $date_format, //has to be user friendly , user will not understand 12:45 PM (g:i A) (https://codex.wordpress.org/Formatting_Date_and_Time)
			'timestamp'  => '',
			'adjustment' => '',

		), $attrs );

		if ( '' === $atts['timestamp'] ) {
			return '';
		}

		if ( $atts['timestamp'] !== null ) {

			$date_start_time = new DateTime();
			$date_start_time->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
			$date_start_time->setTimestamp( $atts['timestamp'] );

			/** Time adjustment */
			if ( $atts['adjustment'] !== '' ) {
				$date_start_time->modify( trim( $atts['adjustment'] ) );
			}

			$realTimeStamp = ( $date_start_time->getTimestamp() + $date_start_time->getOffset() );

			return date_i18n( $atts['format'], $realTimeStamp, true );
		}

		return '';
	}

	public function shortcode_campaign_end_date( $attrs ) {
		$date_format = apply_filters( 'wcct_global_date_time_format', 'M j' );
		$atts        = shortcode_atts( array(
			'format'     => $date_format, //has to be user friendly , user will not understand 12:45 PM (g:i A) (https://codex.wordpress.org/Formatting_Date_and_Time)
			'timestamp'  => '',
			'adjustment' => '',
		), $attrs );

		if ( $atts['timestamp'] == '' ) {
			return '';
		}

		if ( $atts['timestamp'] !== null ) {
			$date_end_time = new DateTime();

			$date_end_time->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
			$date_end_time->setTimestamp( $atts['timestamp'] );

			/** Time adjustment */
			if ( $atts['adjustment'] !== '' ) {
				$date_end_time->modify( trim( $atts['adjustment'] ) );
			}

			$realTimeStamp = ( $date_end_time->getTimestamp() + $date_end_time->getOffset() );

			return date_i18n( $atts['format'], $realTimeStamp, true );
		}

		return '';
	}

	public function wcct_always_show_days( $bool ) {
		$settings = WCCT_Common::get_global_default_settings();
		if ( 'yes' == $settings['wcct_timer_hide_days'] ) {
			return false;
		}

		return $bool;
	}

	public function wcct_always_show_hrs( $bool ) {
		$settings = WCCT_Common::get_global_default_settings();
		if ( 'yes' == $settings['wcct_timer_hide_hrs'] ) {
			return false;
		}

		return $bool;
	}

	public function wcct_modify_positions() {
		$settings = WCCT_Common::get_global_default_settings();
		if ( 'new' == $settings['wcct_positions_approach'] ) {
			return;
		}

		// removing wcct positions action hooks
		remove_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_above_title' ), 2.3 );
		remove_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_title' ), 9.3 );
		remove_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_review' ), 11.3 );
		remove_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_price' ), 17.3 );
		remove_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_short_desc' ), 21.3 );
		remove_action( 'woocommerce_single_product_summary', array( $this, 'wcct_position_below_add_cart' ), 39.3 );

		/** Hooking 'above title' position */
		add_action( 'woocommerce_before_template_part', array( $this, 'wcct_before_template_part' ), 49, 4 );

		/** Hooking 'below title, price, review & short description' position */
		add_action( 'woocommerce_after_template_part', array( $this, 'wcct_after_template_part' ), 49, 4 );

		/** Hooking 'below add to cart' position */
		add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'wcct_add_to_cart_template' ), 49 );
	}

	public function wcct_before_template_part( $template_name = '', $template_path = '', $located = '', $args = array() ) {
		if ( empty( $template_name ) ) {
			return;
		}
		if ( 'single-product/title.php' === $template_name ) {
			$this->wcct_position_above_title();
		}
	}

	public function wcct_position_above_title() {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $post->ID );
		$cp_data     = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		$this->wcct_triggers( $cp_data, 1 );
	}

	public function wcct_after_template_part( $template_name = '', $template_path = '', $located = '', $args = array() ) {
		if ( empty( $template_name ) ) {
			return;
		}
		if ( 'single-product/title.php' === $template_name ) {
			$this->wcct_position_below_title();
		} elseif ( 'single-product/short-description.php' === $template_name ) {
			$this->wcct_position_below_short_desc();
		} elseif ( 'single-product/rating.php' === $template_name ) {
			$this->wcct_position_below_review();
		} elseif ( 'single-product/price.php' === $template_name ) {
			$this->wcct_position_below_price();
		}
	}

	public function wcct_position_below_title() {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $post->ID );
		$cp_data     = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		$this->wcct_triggers( $cp_data, 2 );
	}

	public function wcct_position_below_short_desc() {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $post->ID );
		$cp_data     = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		$this->wcct_triggers( $cp_data, 5 );
	}

	public function wcct_position_below_review() {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $post->ID );
		$cp_data     = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		$this->wcct_triggers( $cp_data, 3 );
	}

	public function wcct_position_below_price() {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $post->ID );
		$cp_data     = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		$this->wcct_triggers( $cp_data, 4 );
	}

	public function wcct_add_to_cart_template() {
		ob_start();
		$this->wcct_position_below_add_cart();
		$output = ob_get_clean();
		if ( '' !== $output ) {
			echo '<div class="wcct_clear" style="height: 15px;"></div>';
		}
		echo $output;
	}

	public function wcct_position_below_add_cart() {
		global $post;
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $post->ID );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $post->ID );
		$cp_data     = array(
			'campaign' => $single_data,
			'actions'  => $actions,
		);
		$this->wcct_triggers( $cp_data, 6 );
	}


}

if ( class_exists( 'WCCT_Appearance' ) ) {
	WCCT_Core::register( 'appearance', 'WCCT_Appearance' );
}
