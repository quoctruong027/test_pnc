<?php

class WCCT_Shortcode {

	public static $_instance = null;

	public $is_shortcode_process = false;

	public function __construct() {
		add_shortcode( 'finale_countdown_timer', array( $this, 'wcct_countdown_timer_shortcode' ) );
		add_shortcode( 'finale_counter_bar', array( $this, 'wcct_counter_bar_shortcode' ) );
		add_shortcode( 'finale_custom_text', array( $this, 'wcct_custom_text_shortcode' ) );
		add_shortcode( 'finale_campaign_grid', array( $this, 'wcct_campaign_products' ) );
		add_shortcode( 'finale_product_sale_price', array( $this, 'wcct_finale_sale_price' ) );
		add_shortcode( 'finale_product_regular_price', array( $this, 'wcct_finale_regular_price' ) );
		add_shortcode( 'finale_product_price_html', array( $this, 'wcct_finale_price_html' ) );
	}

	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Shortcode cb to render countdown timer
	 *
	 * @param $atts
	 *
	 * @return mixed|string|void
	 */
	public function wcct_countdown_timer_shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'campaign_id' => 0,
			'product_id'  => 0,
			'type'        => 'single',
			'skip_rules'  => 'yes',
			'debug'       => 'no',
		), $atts );

		do_action( 'wcct_before_running_countdown_timer_shortcode', $atts );

		wcct_force_log( "\n\n wcct_countdown_timer_shortcode \n\r" . print_r( $atts, true ) );

		return $this->wcct_shortcodes( $atts, 'countdown_timer' );
	}

	public function wcct_shortcodes( $attr = array(), $type = 'countdown_timer' ) {
		global $product;
		$this->is_shortcode_process = true;
		//hold previous object for sticky header and footer and other deals
		$post_id     = 0;
		$single_data = array();

		if ( WCCT_Common::$is_executing_rule ) {
			return '';
		}

		/**
		 * CHECKING PRODUCT ID
		 */
		$attr['product_id'] = str_replace( 'XXX', 0, $attr['product_id'] );

		//checking if user provided the ID of the product
		if ( $attr['product_id'] == 0 || $attr['product_id'] == '' ) {
			$product_shortcode = $product;

		} else {
			//getting product from user given ID
			$product_main      = WCCT_Core()->public->wcct_get_product_obj( $attr['product_id'] );
			$product_shortcode = $product_main;
		}

		if ( is_null( $product_shortcode ) || ! is_object( $product_shortcode ) ) {
			$attr['product_id'] = 0;
		} else {
			$attr['product_id'] = $product_shortcode->get_id();
		}

		$campaign_id = $attr['campaign_id'];

		//case where we have campaign ID and Product ID both
		if ( isset( $attr['campaign_id'] ) && $attr['campaign_id'] > 0 && isset( $attr['product_id'] ) && $attr['product_id'] > 0 ) {
			$single_data = WCCT_Core()->public->get_single_campaign_instance( (int) $attr['campaign_id'], (int) $attr['product_id'], ( 'yes' === $attr['skip_rules'] ) ? true : false );

		} elseif ( $attr['campaign_id'] && $attr['campaign_id'] > 0 ) {
			$single_data = WCCT_Core()->public->get_single_campaign_instance( $attr['campaign_id'], 0, ( 'yes' === $attr['skip_rules'] ) ? true : false );

		}

		if ( isset( $attr['campaign_id'] ) && $attr['campaign_id'] == 0 && isset( $attr['product_id'] ) && $attr['product_id'] > 0 ) {

			$single_data = WCCT_Core()->public->get_single_campaign_pro_data( (int) $attr['product_id'], true, ( 'yes' === $attr['skip_rules'] ) ? true : false );
		}

		if ( is_array( $single_data ) && count( $single_data ) === 0 ) {

			if ( 'yes' === $attr['debug'] ) {
				$this->is_shortcode_process = false;

				return __( 'Unable to show shortcode, Campaign ID/Product ID attribute missing.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			}

			return '';

		}
		if ( isset( $attr['product_id'] ) > 0 ) {
			$post_id = (int) $attr['product_id'];
		}

		//checking any global error registered by the specific campaign
		if ( 'yes' === $attr['debug'] && isset( WCCT_Core()->public->errors['global'][ $campaign_id ][0] ) ) {
			$this->is_shortcode_process = false;

			return WCCT_Core()->public->errors['global'][ $campaign_id ][0];

		}

		if ( 'custom_text' === $type ) {

			if ( isset( $single_data['custom_text'] ) && is_array( $single_data['custom_text'] ) && count( $single_data['custom_text'] ) > 0 ) {
				$custom_data = current( $single_data['custom_text'] );

				/**
				 * Checking for goal data in case of Counter bar shortcodes
				 * Note: if shortcode called without product id then counter bar merge tags won't run on custom text element
				 */
				$goals_meta = array();
				if ( isset( $single_data['goals'] ) && $post_id > 0 ) {
					$goals      = $single_data['goals'];
					$goals_meta = WCCT_Core()->public->wcct_get_goal_object( $goals, $post_id );
				}

				ob_start();
				WCCT_Core()->appearance->wcct_trigger_custom_text( $campaign_id, $custom_data, 'single', $goals_meta );

				$this->is_shortcode_process = false;

				return ob_get_clean();

			} else {

				//checking for errors
				if ( 'yes' === $attr['debug'] ) {
					$this->is_shortcode_process = false;

					return __( 'Unable to show shortcode, Go to Elements > Custom Text and check visibility settings. ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				}
			}
		}

		if ( 'countdown_timer' === $type ) {
			$no_single_timer = false;
			$timer_type      = 'single';

			if ( isset( $single_data['single_timer'] ) && is_array( $single_data['single_timer'] ) && count( $single_data['single_timer'] ) ) {

				$timer_data = $single_data['single_timer'];
				if ( isset( $attr['type'] ) && in_array( $attr['type'], array( 'cart', 'grid' ), true ) ) {
					$timer_type = $attr['type'];
					if ( 'cart' === $timer_type ) {
						$timer_data = $single_data['show_on_cart'];
					} else {
						$timer_data = $single_data['grid_timer'];
					}
				}
				if ( $campaign_id == 0 ) {
					$campaign_id = key( $timer_data );
				}

				$timer = current( $timer_data );
				ob_start();
				WCCT_Core()->appearance->wcct_trigger_countdown_timer( $campaign_id, $timer, $timer_type );
				$this->is_shortcode_process = false;

				return ob_get_clean();
			} else {

				if ( isset( $single_data['expiry_text'] ) && is_array( $single_data['expiry_text'] ) && count( $single_data['expiry_text'] ) ) {

					ob_start();
					foreach ( $single_data['expiry_text'] as $campaign_id => $exp_text ) {
						WCCT_Core()->appearance->wcct_trigger_countdown_timer_expiry( $campaign_id, $exp_text );

					}

					return ob_get_clean();
				}
				$no_single_timer = true;

			}

			/**
			 * Check for error and terminate
			 */
			if ( true === $no_single_timer && 'yes' === $attr['debug'] && isset( WCCT_Core()->public->errors['timer'][ $campaign_id ][0] ) ) {
				$this->is_shortcode_process = false;

				return WCCT_Core()->public->errors['timer'][ $campaign_id ][0];
			}
		} elseif ( 'counter_bar' === $type ) {

			$goals      = array();
			$getProduct = WCCT_Core()->public->wcct_get_product_obj( $attr['product_id'] );
			$timer      = array();
			if ( ! is_wp_error( $getProduct ) && ! empty( $getProduct ) ) {

				if ( isset( $single_data['single_bar'] ) && is_array( $single_data['single_bar'] ) && count( $single_data['single_bar'] ) ) {
					$timer_bar = $single_data['single_bar'];

					if ( $campaign_id == 0 ) {
						$campaign_id = key( $timer_bar );
					}
					$timer      = current( $timer_bar );
					if ( isset( $single_data['goals'] ) ) {
						$goals = $single_data['goals'];
					}
				} else {
					/**
					 * Checking for errors and showing them, terminating shortcode if error
					 */
					if ( 'yes' === $attr['debug'] && isset( WCCT_Core()->public->errors['inventory'][ $campaign_id ][0] ) ) {
						$this->is_shortcode_process = false;

						return WCCT_Core()->public->errors['inventory'][ $campaign_id ][0];

					}
				}

				/**
				 * Starting output
				 */
				ob_start();
				$product_obj = WCCT_Core()->public->wcct_get_product_obj( $post_id );
				$goals_meta  = WCCT_Core()->public->wcct_get_goal_object( $goals, $post_id );

				WCCT_Core()->appearance->wcct_trigger_counter_bar( $campaign_id, $timer, $goals_meta, 'shortcode', $product_obj );
				$this->is_shortcode_process = false;

				return ob_get_clean();
			} else {

				//checking for errors
				if ( 'yes' === $attr['debug'] ) {
					$this->is_shortcode_process = false;

					return __( 'Unable to show shortcode, Product ID given is missing/invalid.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				}
			}
		}

		return '';
	}

	/**
	 * @param $atts
	 *
	 * @return string|void
	 */
	public function wcct_custom_text_shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'campaign_id' => 0,
			'product_id'  => 0,
			'type'        => 'single',
			'skip_rules'  => 'yes',
			'debug'       => 'no',
		), $atts );

		do_action( 'wcct_before_running_custom_text_shortcode', $atts );

		wcct_force_log( "\n\n wcct_custom_text_shortcode \n\r" . print_r( $atts, true ) );

		return $this->wcct_shortcodes( $atts, 'custom_text' );
	}

	/**
	 * Generate Output of Counter Bar using shortcode
	 *
	 * @param type $atts
	 */
	public function wcct_counter_bar_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'campaign_id' => 0,
			'product_id'  => get_the_ID(),
			'type'        => 'single',
			'skip_rules'  => 'yes',
			'debug'       => 'no',
		), $atts );

		do_action( 'wcct_before_running_counter_bar_shortcode', $atts );

		wcct_force_log( "\n\n wcct_counter_bar_shortcode \n\r" . print_r( $atts, true ) );

		global $product;
		$old_product = $product;
		$product     = WCCT_Core()->public->wcct_get_product_obj( $atts['product_id'] );
		$shortcode   = $this->wcct_shortcodes( $atts, 'counter_bar' );
		$product     = $old_product;

		return $shortcode;
	}


	public function wcct_campaign_products( $attrs ) {

		$attr = shortcode_atts( array(
			'product_ids' => '',
			'campaign_id' => '',
			'title'       => '',
			'count'       => 999,
		), $attrs );

		if ( $attr['campaign_id'] == '' ) {
			return '';
		}
		if ( $attr['product_ids'] == '' ) {
			return apply_filters( 'wcct_campaign_products_product_id_not_exist', '', $attrs );
		}

		$products = explode( ',', $attr['product_ids'] );

		$get_camp_post = get_post( $attr['campaign_id'] );

		if ( ! $get_camp_post instanceof WP_Post ) {
			return '';
		}

		if ( $get_camp_post->post_status !== 'publish' ) {
			return '';
		}

		if ( ! function_exists( 'WCCT_Core' ) ) {
			return '';
		}
		$filtered_products = array();

		foreach ( $products as $product ) {
			$single_data = WCCT_Core()->public->get_single_campaign_instance( (int) $attr['campaign_id'], (int) $product, false );
			if ( $single_data && is_array( $single_data ) && isset( $single_data['running'] ) && in_array( $attr['campaign_id'], $single_data['running'] ) ) {

				array_push( $filtered_products, $product );

			}
		}

		if ( empty( $filtered_products ) ) {
			return '';
		}
		$query_args = array(
			'post_status' => 'publish',
			'post_type'   => 'product',
			'post__in'    => $filtered_products,
			'orderby'     => 'rand',
			'showposts'   => $attrs['count'],
			'style'       => 'grid',
		);
		$r          = new WP_Query( $query_args );

		ob_start();
		?>
		<?php if ( $attr['title'] !== '' ) { ?>
            <h3><?php echo $attr['title']; ?></h3>
		<?php } ?>

        <div class="woocommerce wcct_clear">
			<?php

			woocommerce_product_loop_start();
			while ( $r->have_posts() ) {
				$r->the_post();
				global $product;
				$product = wc_get_product( get_the_ID() );

				wc_get_template_part( 'content', 'product' );
				unset( $product );
			}
			woocommerce_product_loop_end();

			wp_reset_query();

			?>
        </div>

		<?php
		return ob_get_clean();
	}

	public function wcct_finale_sale_price( $attr = array() ) {
		global $product;
		$attr = shortcode_atts( array(
			'product_id' => '',
			'format'     => 'yes',
		), $attr );

		if ( '' === $attr['product_id'] ) {

			if ( $product instanceof WC_Product ) {
				$attr['product_id'] = $product->get_id();
			} else {
				return __( 'Unable to get the product to show discounted price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			}
		}

		return WCCT_API::get_sale_price( $attr['product_id'], ( 'yes' == $attr['format'] ) ? true : false );

	}

	public function wcct_finale_regular_price( $attr = array() ) {
		global $product;
		$attr = shortcode_atts( array(
			'product_id' => '',
			'format'     => 'yes',
		), $attr );

		if ( '' === $attr['product_id'] ) {

			if ( $product instanceof WC_Product ) {
				$attr['product_id'] = $product->get_id();
			} else {
				return __( 'Unable to get the product to show discounted price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			}
		}

		return WCCT_API::get_regular_price( $attr['product_id'], ( 'yes' == $attr['format'] ) ? true : false );

	}

	public function wcct_finale_campaign_coupon_name( $attr = array() ) {
		global $product;
		$attr = shortcode_atts( array(
			'product_id'  => '',
			'campaign_id' => '',
		), $attr );

		$data = '';

		if ( '' != $attr['campaign_id'] && is_numeric( $attr['campaign_id'] ) ) {
			$data = WCCT_Common::get_item_data( $attr['campaign_id'] );
		} else {
			if ( '' == $attr['product_id'] ) {
				if ( $product instanceof WC_Product ) {
					$product_obj = $product;
				}
			} else {
				$product_obj = WCCT_Core()->public->wcct_get_product_obj( $attr['product_id'] );
			}

			$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product_obj );
			WCCT_Core()->public->wcct_get_product_obj( $parent_id );
			$data = WCCT_Core()->public->get_single_campaign_pro_data( $parent_id, true );
		}

		if ( '' === $data ) {
			return __( 'Unable to get campaign to show coupon name', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		}

		if ( ! isset( $data['coupons'] ) || empty( $data['coupons'] ) ) {
			return __( 'Unable to get coupons in the campaign to show coupon name', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		}

		$coupon_code = '';
		foreach ( $data['coupons'] as $coupon ) {
			if ( isset( $coupon['coupons'] ) && ! empty( $coupon['coupons'] ) ) {
				$coupon_code = wc_get_coupon_code_by_id( $coupon['coupons'] );

				break;
			}
		}

		return $coupon_code;

	}

	public function wcct_finale_campaign_coupon_value( $attr = array() ) {
		global $product;
		$attr = shortcode_atts( array(
			'product_id'  => '',
			'campaign_id' => '',
		), $attr );

		$data = '';

		if ( '' != $attr['campaign_id'] && is_numeric( $attr['campaign_id'] ) ) {
			$data = WCCT_Common::get_item_data( $attr['campaign_id'] );
		} else {
			if ( '' == $attr['product_id'] ) {
				if ( $product instanceof WC_Product ) {
					$product_obj = $product;
				}
			} else {
				$product_obj = WCCT_Core()->public->wcct_get_product_obj( $attr['product_id'] );
			}

			$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product_obj );
			WCCT_Core()->public->wcct_get_product_obj( $parent_id );
			$data = WCCT_Core()->public->get_single_campaign_pro_data( $parent_id, true );
		}

		if ( '' === $data ) {
			return __( 'Unable to get campaign to show coupon value', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		}

		if ( ! isset( $data['coupons'] ) || empty( $data['coupons'] ) ) {
			return __( 'Unable to get coupons in the campaign to show coupon value', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		}

		$coupon_value = '';
		foreach ( $data['coupons'] as $coupon ) {
			if ( isset( $coupon['coupons'] ) && ! empty( $coupon['coupons'] ) ) {
				$coupon_obj = new WC_Coupon( $coupon['coupons'] );
				$amount     = $coupon_obj->get_amount();
				$type       = $coupon_obj->get_discount_type();

				if ( 'percent' == $type ) {
					$coupon_value = $amount . '%';
				} else {
					$coupon_value = wc_price( $amount );
				}

				break;
			}
		}

		return $coupon_value;
	}

	public function wcct_finale_price_html( $attr = array() ) {
		global $product;
		$attr = shortcode_atts(

			array(
				'product_id' => '',
			), $attr );

		if ( '' === $attr['product_id'] ) {

			if ( $product instanceof WC_Product ) {
				$attr['product_id'] = $product->get_id();
			} else {
				return __( 'Unable to get the product to show discounted price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			}
		}

		return WCCT_API::get_price_html( $attr['product_id'] );

	}

}

if ( class_exists( 'WCCT_Shortcode' ) ) {
	WCCT_Core::register( 'shortcode', 'WCCT_Shortcode' );
}
