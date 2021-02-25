<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WOO_CART_ALL_IN_ONE_Frontend_Sidebar_Cart {
	protected $settings;
	protected $is_customize, $customize_data;

	public function __construct() {
		$this->settings = new VI_WOO_CART_ALL_IN_ONE_DATA();
		add_action( 'wp_enqueue_scripts', array( $this, 'viwcaio_wp_enqueue_scripts' ), 99 );
		add_action( 'vi_wcaio_get_sidebar_cart_icon', array( $this, 'get_sidebar_cart_icon' ) );
		add_action( 'vi_wcaio_get_sidebar_cart_content', array( $this, 'get_sidebar_cart_content' ) );
	}

	private function get_params( $name = '' ) {
		if ( $this->customize_data && $name && $setting = $this->customize_data->get_setting( 'woo_cart_all_in_one_params[' . $name . ']' ) ) {
			return $this->customize_data->post_value( $setting, $this->settings->get_params( $name ) );
		} else {
			return $this->settings->get_params( $name );
		}
	}

	public function viwcaio_wp_enqueue_scripts() {
		if ( is_checkout() || is_cart() ) {
			return;
		}
		$this->is_customize = is_customize_preview();
		if ( ! $this->is_customize ) {
			if ( ! $this->settings->enable( 'sc_' ) ) {
				return;
			}
			$assign_page = $this->settings->get_params( 'sc_assign_page' );
			if ( $assign_page ) {
				if ( stristr( $assign_page, "return" ) === false ) {
					$assign_page = "return (" . $assign_page . ");";
				}
				if ( ! eval( $assign_page ) ) {
					return;
				}
			}
		} else {
			global $wp_customize;
			$this->customize_data = $wp_customize;
		}
		$has_product_plus  =  $this->settings->get_params( 'sc_footer_pd_plus' );
		wp_enqueue_style( 'vi-wcaio-cart-icons', VI_WOO_CART_ALL_IN_ONE_CSS . 'cart-icons.min.css', array(), VI_WOO_CART_ALL_IN_ONE_VERSION );
		wp_enqueue_style( 'vi-wcaio-loading', VI_WOO_CART_ALL_IN_ONE_CSS . 'loading.min.css', array(), VI_WOO_CART_ALL_IN_ONE_VERSION );
		$suffix = WP_DEBUG ? '' : 'min.';
		wp_enqueue_style( 'vi-wcaio-sidebar-cart', VI_WOO_CART_ALL_IN_ONE_CSS . 'sidebar-cart.' . $suffix . 'css', array(), VI_WOO_CART_ALL_IN_ONE_VERSION );
		wp_enqueue_script( 'vi-wcaio-sidebar-cart', VI_WOO_CART_ALL_IN_ONE_JS . 'sidebar-cart.' . $suffix . 'js', array( 'jquery' ), VI_WOO_CART_ALL_IN_ONE_VERSION );
		if (  $has_product_plus  || $this->is_customize ) {
			wp_enqueue_style( 'vi-wcaio-nav-icons', VI_WOO_CART_ALL_IN_ONE_CSS . 'nav-icons.min.css', array(), VI_WOO_CART_ALL_IN_ONE_VERSION );
			wp_enqueue_style( 'vi-wcaio-flexslider', VI_WOO_CART_ALL_IN_ONE_CSS . 'sc-flexslider.min.css', array(), VI_WOO_CART_ALL_IN_ONE_VERSION );
			wp_enqueue_script( 'vi-wcaio-flexslider', VI_WOO_CART_ALL_IN_ONE_JS . 'vi-flexslider.min.js', array( 'jquery' ), VI_WOO_CART_ALL_IN_ONE_VERSION );
		}
		if ( ! $this->is_customize ) {
			$args = array(
				'wc_ajax_url'                      => WC_AJAX::get_endpoint( "%%endpoint%%" ),
			);
			wp_localize_script( 'vi-wcaio-sidebar-cart', 'viwcaio_sc_params', $args );
			$css = $this->get_inline_css();
			wp_add_inline_style( 'vi-wcaio-sidebar-cart', $css );
		}
		add_action( 'wp_footer', array( $this, 'frontend_html' ) );
	}


	public function get_inline_css() {
		$css = '';
		$frontend = 'VI_WOO_CART_ALL_IN_ONE_Frontend_Frontend';
		if ( $sc_horizontal = $this->settings->get_params( 'sc_horizontal' ) ) {
			$sc_horizontal_mobile = $sc_horizontal > 20 ? 20 - $sc_horizontal : 0;
			$css                  .= '.vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_left,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-top_left .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-bottom_left .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_left{
                left: ' . $sc_horizontal . 'px ;
            }
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_right,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-top_right .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-bottom_right .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_right{
                right: ' . $sc_horizontal . 'px ;
            }
            @media screen and (max-width: 768px) {
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_left .vi-wcaio-sidebar-cart-content-wrap,
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_left .vi-wcaio-sidebar-cart-content-wrap{
                    left: ' . $sc_horizontal_mobile . 'px ;
                }
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_right .vi-wcaio-sidebar-cart-content-wrap,
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_right .vi-wcaio-sidebar-cart-content-wrap{
                    right: ' . $sc_horizontal_mobile . 'px ;
                }
            }
            ';
		}
		if ( $sc_vertical = $this->settings->get_params( 'sc_vertical' ) ) {
			$sc_vertical_mobile = $sc_vertical > 20 ? 20 - $sc_vertical : 0;
			$css                .= '.vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_left,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-top_left .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_right,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-top_right .vi-wcaio-sidebar-cart-icon-wrap{
                top: ' . $sc_vertical . 'px ;
            }
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-bottom_right .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_right,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_left,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-bottom_left .vi-wcaio-sidebar-cart-icon-wrap{
                bottom: ' . $sc_vertical . 'px ;
            }
            @media screen and (max-width: 768px) {
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_left .vi-wcaio-sidebar-cart-content-wrap,
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_right .vi-wcaio-sidebar-cart-content-wrap{
                    top: ' . $sc_vertical_mobile . 'px ;
                }
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_right .vi-wcaio-sidebar-cart-content-wrap,
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_left .vi-wcaio-sidebar-cart-content-wrap{
                    bottom: ' . $sc_vertical_mobile . 'px ;
                }
            }';
		}
		if ( $this->settings->get_params( 'sc_icon_box_shadow' ) ) {
			$css .= '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap{
                box-shadow: inset 0 0 2px rgba(0,0,0,0.03), 0 4px 10px rgba(0,0,0,0.17);
            }';
		}
		if ( $sc_icon_scale = $this->settings->get_params( 'sc_icon_scale' ) ) {
			$css .= '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap {
                transform: scale(' . $sc_icon_scale . ') ;
            }
            @keyframes vi-wcaio-cart-icon-slide_in_left {
                from {
                    transform: translate3d(-100%, 0, 0) scale(' . $sc_icon_scale . ');
                    visibility: hidden;
                }
                to {
                    transform: translate3d(0, 0, 0) scale(' . $sc_icon_scale . ');
                }
            }
            @keyframes vi-wcaio-cart-icon-slide_out_left {
                from {
                    transform: translate3d(0, 0, 0) scale(' . $sc_icon_scale . ');
                    visibility: visible;
                    opacity: 1;
                }
                to {
                    transform: translate3d(-100%, 0, 0) scale(' . $sc_icon_scale . ');
                    visibility: hidden;
                    opacity: 0;
                }
            }
            @keyframes vi-wcaio-cart-icon-shake_horizontal {
               0% {
		            transform: scale(' . $sc_icon_scale . ');
	            }
	           10%, 20% {
		            transform: scale(' . $sc_icon_scale . ') translateX(-10%);
	           }
	           30%, 50%, 70%, 90% {
		            transform: scale(' . $sc_icon_scale . ') translateX(10%);
	           }
	           40%, 60%, 80% {
		            transform: scale(' . $sc_icon_scale . ') translateX(-10%);
	           }
            	100% {
            		transform: scale(' . $sc_icon_scale . ');
            	}
            }
            @keyframes vi-wcaio-cart-icon-shake_vertical {
               0% {
		            transform: scale(' . $sc_icon_scale . ');
	            }
	           10%, 20% {
	                transform: scale(' . ($sc_icon_scale*0.9) . ') rotate3d(0, 0, 1, -3deg);
	           }
	           30%, 50%, 70%, 90% {
		            transform: scale(' . ($sc_icon_scale*1.1) . ') rotate3d(0, 0, 1, 3deg);
	           }
	           40%, 60%, 80% {
		            transform: scale(' . ($sc_icon_scale*1.1) . ') rotate3d(0, 0, 1, -3deg);
	           }
            	100% {
            		transform: scale(' . $sc_icon_scale . ');
            	}
            }';
		}
		if ( $sc_icon_hover_scale = $this->settings->get_params( 'sc_icon_hover_scale' ) ) {
			$css .= '@keyframes vi-wcaio-cart-icon-mouseenter {
                from {
                    transform: translate3d(0, 0, 0) scale(' . $sc_icon_scale . ');
                }
                to {
                    transform: translate3d(0, 0, 0) scale(' . $sc_icon_hover_scale . ');
                }
            }
            @keyframes vi-wcaio-cart-icon-mouseleave {
                from {
                    transform: translate3d(0, 0, 0) scale(' . $sc_icon_hover_scale . ');
                }
                to {
                    transform: translate3d(0, 0, 0) scale(' . $sc_icon_scale . ');
                }
            }
            @keyframes vi-wcaio-cart-icon-slide_out_left {
                from {
                    transform: translate3d(0, 0, 0) scale(' . $sc_icon_hover_scale . ');
                    visibility: visible;
                    opacity: 1;
                }
                to {
                    transform: translate3d(-100%, 0, 0) scale(' . $sc_icon_hover_scale . ');
                    visibility: hidden;
                    opacity: 0;
                }
            }
            @keyframes vi-wcaio-cart-icon-slide_out_right {
                from {
                    transform: translate3d(0, 0, 0) scale(' . $sc_icon_hover_scale . ');
                    visibility: visible;
                    opacity: 1;
                }
                to {
                    transform: translate3d(100%, 0, 0) scale(' . $sc_icon_hover_scale . ');
                    visibility: hidden;
                    opacity: 0;
                }
            }';
		}
		if ( $this->settings->get_params( 'sc_pd_img_box_shadow' ) ) {
			$css .= '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-img-wrap img{
                box-shadow: 0 4px 10px rgba(0,0,0,0.07);
            }';
		}
		if ( $sc_loading_color = $this->settings->get_params( 'sc_loading_color' ) ) {
			$css .= '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-dual_ring:after {
                border-color: ' . $sc_loading_color . '  transparent <?php echo esc_html($sc_loading_color); ?>  transparent;
            }
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-ring div{
                border-color: ' . $sc_loading_color . '  transparent transparent transparent;
            }
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-ripple  div{
                border: 4px solid ' . $sc_loading_color . ' ;
            }
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-default div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-animation_face_1 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-animation_face_2 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-roller div:after,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-loader_balls_1 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-loader_balls_2 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-loader_balls_3 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-spinner div:after{
                background: ' . $sc_loading_color . ' ;
            }';
		}
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-content-wrap' ),
			array( 'sc_radius' ),
			array( 'border-radius' ),
			array( 'px' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap' ),
			array( 'sc_icon_border_radius', 'sc_icon_bg_color' ),
			array( 'border-radius', 'background' ),
			array( 'px', '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap .vi-wcaio-sidebar-cart-icon i' ),
			array( 'sc_icon_color' ),
			array( 'color' ),
			array( '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap .vi-wcaio-sidebar-cart-count-wrap' ),
			array( 'sc_icon_count_bg_color', 'sc_icon_count_color', 'sc_icon_count_border_radius' ),
			array( 'background', 'color', 'border-radius' ),
			array( '', '', 'px' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap' ),
			array( 'sc_header_bg_color', 'sc_header_border_style', 'sc_header_border_color' ),
			array( 'background', 'border-style', 'border-color' ),
			array( '', '', '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-title-wrap' ),
			array( 'sc_header_title_color' ),
			array( 'color' ),
			array( '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-coupon-wrap .vi-wcaio-coupon-code' ),
			array( 'sc_header_coupon_input_radius' ),
			array( 'border-radius' ),
			array( 'px' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-coupon-wrap .vi-wcaio-bt-coupon-code.button' ),
			array( 'sc_header_coupon_button_bg_color', 'sc_header_coupon_button_color', 'sc_header_coupon_button_border_radius' ),
			array( 'background', 'color', 'border-radius' ),
			array( '', '', 'px' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-coupon-wrap .vi-wcaio-bt-coupon-code.button:hover' ),
			array( 'sc_header_coupon_button_bg_color_hover', 'sc_header_coupon_button_color_hover' ),
			array( 'background', 'color' ),
			array( '', '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap' ),
			array( 'sc_footer_bg_color', 'sc_footer_border_type', 'sc_footer_border_color' ),
			array( 'background', 'border-style', 'border-color' ),
			array( '', '', '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-footer-cart_total > div:nth-child(1)' ),
			array( 'sc_footer_cart_total_color' ),
			array( 'color' ),
			array( '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-footer-cart_total > div:nth-child(2)' ),
			array( 'sc_footer_cart_total_color1' ),
			array( 'color' ),
			array( '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-nav.button' ),
			array( 'sc_footer_button_bg_color', 'sc_footer_button_color', 'sc_footer_button_border_radius' ),
			array( 'background', 'color', 'border-radius' ),
			array( '', '', 'px' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-nav.button:hover' ),
			array( 'sc_footer_button_hover_bg_color', 'sc_footer_button_hover_color' ),
			array( 'background', 'color' ),
			array( '', '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-update.button' ),
			array( 'sc_footer_bt_update_bg_color', 'sc_footer_bt_update_color', 'sc_footer_bt_update_border_radius' ),
			array( 'background', 'color', 'border-radius' ),
			array( '', '', 'px' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-update.button:hover' ),
			array( 'sc_footer_bt_update_hover_bg_color', 'sc_footer_bt_update_hover_color' ),
			array( 'background', 'color' ),
			array( '', '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-footer-pd-plus-title' ),
			array( 'sc_footer_pd_plus_color' ),
			array( 'color' ),
			array( '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products-wrap' ),
			array( 'sc_pd_bg_color' ),
			array( 'background' ),
			array( '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-img-wrap img' ),
			array( 'sc_pd_img_border_radius' ),
			array( 'border-radius' ),
			array( 'px' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-info-wrap .vi-wcaio-sidebar-cart-pd-name-wrap .vi-wcaio-sidebar-cart-pd-name, .vi-wcaio-sidebar-cart-footer-pd-name *' ),
			array( 'sc_pd_name_color' ),
			array( 'color' ),
			array( '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-info-wrap .vi-wcaio-sidebar-cart-pd-name-wrap .vi-wcaio-sidebar-cart-pd-name:hover, .vi-wcaio-sidebar-cart-footer-pd-name *:hover' ),
			array( 'sc_pd_name_hover_color' ),
			array( 'color' ),
			array( '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-info-wrap .vi-wcaio-sidebar-cart-pd-price *, .vi-wcaio-sidebar-cart-footer-pd-price *' ),
			array( 'sc_pd_price_color' ),
			array( 'color' ),
			array( '' )
		);
		if ( $sc_pd_qty_border_color = $this->settings->get_params( 'sc_pd_qty_border_color' ) ) {
			$css .= '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi-wcaio-sidebar-cart-pd-quantity{
                 border: 1px solid ' . $sc_pd_qty_border_color . ' ;
            }';
			$css .= '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi_wcaio_minus{
                 border-right: 1px solid ' . $sc_pd_qty_border_color . ' ;
            }';
			$css .= '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi_wcaio_plus{
                 border-left: 1px solid ' . $sc_pd_qty_border_color . ' ;
            }';
			$css .= '.vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-rtl .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi_wcaio_minus{
			     border-right: unset;
                 border-left: 1px solid ' . $sc_pd_qty_border_color . ' ;
            }';
			$css .= '.vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-rtl .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi_wcaio_plus{
			     border-left: unset;
                 border-right: 1px solid ' . $sc_pd_qty_border_color . ' ;
            }';
		}
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi-wcaio-sidebar-cart-pd-quantity' ),
			array( 'sc_pd_qty_border_radius' ),
			array( 'border-radius' ),
			array( 'px' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-remove-wrap i' ),
			array( 'sc_pd_delete_icon_color' ),
			array( 'color' ),
			array( '' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-remove-wrap i:before' ),
			array( 'sc_pd_delete_icon_font_size' ),
			array( 'font-size' ),
			array( 'px' )
		);
		$css .= $frontend::add_inline_style(
			array( '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-remove-wrap i:hover' ),
			array( 'sc_pd_delete_icon_hover_color' ),
			array( 'color' ),
			array( '' )
		);
		$css = str_replace(array("\r","\n",'\r','\n'),' ', $css);
		return $css;
	}

	public function frontend_html() {
		$class           = array(
			'vi-wcaio-sidebar-cart',
			'vi-wcaio-sidebar-cart-' . $sc_display_type = $this->get_params( 'sc_display_type' ),
			'vi-wcaio-sidebar-cart-' . $sc_position = $this->get_params( 'sc_position' ),
		);
		$class[]         = is_rtl() ? 'vi-wcaio-sidebar-cart-rtl' : '';
		$sc_empty_enable = $this->settings->get_params( 'sc_empty_enable' );
		if ( ! $this->is_customize ) {
			$class[] = ! $sc_empty_enable && WC()->cart->is_empty() ? 'vi-wcaio-disabled' : '';
		}
		$class = trim( implode( ' ', $class ) );
		?>
        <div class="vi-wcaio-sidebar-cart-wrap" data-empty_enable="<?php echo esc_attr( $sc_empty_enable ?: '' ); ?>"
             data-effect_after_atc="<?php echo esc_attr( $this->settings->get_params( 'sc_effect_after_atc' ) ?: '' ); ?>"
             data-fly_to_cart="<?php echo esc_attr( $this->settings->get_params( 'sc_fly_to_cart' ) ?: '' ); ?>">
            <div class="vi-wcaio-sidebar-cart-overlay vi-wcaio-disabled"></div>
            <div class="<?php echo esc_attr( $class ); ?>" data-type="<?php echo esc_attr( $sc_display_type ); ?>" data-old_position=""
                 data-position="<?php echo esc_attr( $sc_position ); ?>"
                 data-effect="<?php echo esc_attr( $this->settings->get_params( 'sc_trigger_style' ) ); ?>">
                <div class="vi-wcaio-sidebar-cart-icon-wrap vi-wcaio-sidebar-cart-icon-wrap-<?php echo esc_attr( $sc_trigger_type = $this->get_params( 'sc_trigger_type' ) ); ?>"
                     data-trigger="<?php echo esc_attr( $sc_trigger_type ); ?>">
					<?php
					do_action( 'vi_wcaio_get_sidebar_cart_icon' );
					?>
                </div>
				<?php
				do_action( 'vi_wcaio_get_sidebar_cart_content' );
				?>
            </div>
        </div>
		<?php
	}

	public function get_sidebar_cart_content() {
		$class       = array(
			'vi-wcaio-sidebar-cart-content-close',
			'vi-wcaio-sidebar-cart-content-wrap',
		);
		$class[]     = $this->is_customize ? 'vi-wcaio-sidebar-cart-content-wrap-customize' : '';
		$class[]     = is_user_logged_in() ? 'vi-wcaio-sidebar-cart-content-wrap-logged' : '';
		$class       = trim( implode( ' ', $class ) );
		$wc_cart     = WC()->cart;
		do_action( 'vi_wcaio_before_mini_cart' );
		?>
        <div class="<?php echo esc_attr( $class ); ?>">
            <div class="vi-wcaio-sidebar-cart-header-wrap">
                <div class="vi-wcaio-sidebar-cart-header-title-wrap">
					<?php echo wp_kses_post( $this->get_params( 'sc_header_title' ) ); ?>
                </div>
				<?php
				if ( $this->is_customize || $this->settings->get_params( 'sc_header_coupon_enable' ) ) {
					?>
                    <div class="vi-wcaio-sidebar-cart-header-coupon-wrap">
						<?php
						if ( wc_coupons_enabled() ) {
							$coupon_applied = $wc_cart->get_applied_coupons();
							if ( ! empty( $coupon_applied ) ) {
								$coupon_applied_name = $coupon_applied[ count( $coupon_applied ) - 1 ];
							}
						}
						?>
                        <input type="text" name="coupon_code" id="coupon_code" class="vi-wcaio-coupon-code"
                               placeholder="<?php esc_attr_e( 'Coupon code', 'woo-cart-all-in-one' ); ?>"
                               value="<?php echo esc_attr( $coupon_applied_name ?? '' ); ?>">
                        <button type="submit" class="button vi-wcaio-bt-coupon-code" name="apply_coupon">
							<?php echo sprintf( '%s', apply_filters( 'vi_wcaio_get_bt_coupon_text', esc_html__( 'Apply', 'woo-cart-all-in-one' ) ) ); ?>
                        </button>
                    </div>
					<?php
				}
				?>
                <div class="vi-wcaio-sidebar-cart-close-wrap">
                    <i class="vi_wcaio_cart_icon-clear-button"></i>
                </div>
            </div>
            <div class="vi-wcaio-sidebar-cart-content-wrap1 vi-wcaio-sidebar-cart-products-wrap">
                <ul class="vi-wcaio-sidebar-cart-products">
					<?php
					$this->get_sidebar_content_pd_html( $wc_cart );
					?>
                </ul>
            </div>
            <div class="vi-wcaio-sidebar-cart-footer-wrap">
                <div class="vi-wcaio-sidebar-cart-footer vi-wcaio-sidebar-cart-footer-products">
					<?php
					$sc_footer_cart_total = $this->settings->get_params( 'sc_footer_cart_total' ) ?: 'total';
					$sc_footer_cart_total_title =$this->settings->get_params( 'sc_footer_cart_total_text') ;
					$sc_footer_button     = $this->get_params( 'sc_footer_button' ) ?: 'cart';
					if ( $this->is_customize ) {
						?>
                        <div class="vi-wcaio-sidebar-cart-footer-cart_total-wrap">
                            <div class="vi-wcaio-sidebar-cart-footer-cart_total vi-wcaio-sidebar-cart-footer-total<?php echo $sc_footer_cart_total === 'total' ? '' : esc_attr( ' vi-wcaio-disabled' ); ?>"
                                 data-cart_total="<?php echo esc_attr( $cart_total = $wc_cart->get_total() ); ?>">
                                <div class="vi-wcaio-sidebar-cart-footer-cart_total-title"><?php echo wp_kses_post( $sc_footer_cart_total_title); ?></div>
                                <div class="vi-wcaio-sidebar-cart-footer-cart_total1">
									<?php echo wp_kses_post( $cart_total ); ?>
                                </div>
                            </div>
                            <div class="vi-wcaio-sidebar-cart-footer-cart_total vi-wcaio-sidebar-cart-footer-subtotal<?php echo $sc_footer_cart_total !== 'total' ? '' : esc_attr( ' vi-wcaio-disabled' ); ?>"
                                 data-cart_total="<?php echo esc_attr( $cart_subtotal = $wc_cart->get_cart_subtotal() ); ?>">
                                <div class="vi-wcaio-sidebar-cart-footer-cart_total-title"><?php echo wp_kses_post( $sc_footer_cart_total_title ); ?></div>
                                <div class="vi-wcaio-sidebar-cart-footer-cart_total1">
									<?php echo wp_kses_post( $cart_subtotal ); ?>
                                </div>
                            </div>
                        </div>
                        <div class="vi-wcaio-sidebar-cart-footer-action">
                            <button class="vi-wcaio-sidebar-cart-bt vi-wcaio-sidebar-cart-bt-update button">
								<?php echo wp_kses_post( apply_filters( 'vi_wcaio_get_bt_update_text', __( 'Update Cart', 'woo-cart-all-in-one' ) ) ); ?>
                            </button>
                            <a href="<?php echo esc_attr( esc_url( get_permalink( wc_get_page_id( 'cart' ) ) ) ); ?>"
                               class="button vi-wcaio-sidebar-cart-bt vi-wcaio-sidebar-cart-bt-nav vi-wcaio-sidebar-cart-bt-nav-cart<?php echo $sc_footer_button === 'cart' ? '' : esc_attr( ' vi-wcaio-disabled' ); ?>">
								<?php echo wp_kses_post( $this->get_params( 'sc_footer_bt_cart_text' ) ); ?>
                            </a>
                            <a href="#" data-href="<?php echo esc_attr( esc_url( get_permalink( wc_get_page_id( 'checkout' ) ) ) ); ?>"
                               class="button vi-wcaio-sidebar-cart-bt vi-wcaio-sidebar-cart-bt-nav vi-wcaio-sidebar-cart-bt-nav-checkout<?php echo $sc_footer_button === 'checkout' ? '' : esc_attr( ' vi-wcaio-disabled' ); ?>">
								<?php echo wp_kses_post( $this->get_params( 'sc_footer_bt_checkout_text' ) ); ?>
                            </a>
                        </div>
						<?php
					} else {
						?>
                        <div class="vi-wcaio-sidebar-cart-footer-cart_total-wrap">
                            <div class="vi-wcaio-sidebar-cart-footer-cart_total vi-wcaio-sidebar-cart-footer-<?php echo esc_attr( $sc_footer_cart_total ); ?>">
                                <div class="vi-wcaio-sidebar-cart-footer-cart_total-title"><?php echo wp_kses_post( $sc_footer_cart_total_title);?></div>
                                <div class="vi-wcaio-sidebar-cart-footer-cart_total1">
									<?php echo $sc_footer_cart_total === 'total' ? wp_kses_post( $wc_cart->get_cart_total() ) : wp_kses_post( $wc_cart->get_cart_subtotal() ); ?>
                                </div>
                            </div>
                        </div>
                        <div class="vi-wcaio-sidebar-cart-footer-action">
                            <button class="vi-wcaio-sidebar-cart-bt vi-wcaio-sidebar-cart-bt-update vi-wcaio-disabled button">
								<?php echo wp_kses_post( apply_filters( 'vi_wcaio_get_bt_update_text', __( 'Update Cart', 'woo-cart-all-in-one' ) ) ); ?>
                            </button>
                            <a href="<?php echo esc_attr( esc_url( get_permalink( wc_get_page_id( $sc_footer_button ) ) ) ); ?>"
                               class="button vi-wcaio-sidebar-cart-bt vi-wcaio-sidebar-cart-bt-nav vi-wcaio-sidebar-cart-bt-nav-<?php echo esc_attr( $sc_footer_button ); ?>">
		                        <?php echo wp_kses_post( $this->settings->get_params( 'sc_footer_bt_' . $sc_footer_button . '_text' ) ); ?>
                            </a>
                        </div>
						<?php
					}
					?>
                </div>
                <div class="vi-wcaio-sidebar-cart-footer-message-wrap">
					<?php
					self::get_sc_footer_message_html( $this->get_params( 'sc_footer_message' ));
					?>
                </div>
            </div>
            <div class="vi-wcaio-sidebar-cart-loading-wrap vi-wcaio-disabled">
				<?php
				$sc_loading = $this->settings->get_params( 'sc_loading' );
				if ( $this->is_customize ) {
					$loading = array(
						'default',
						'dual_ring',
						'animation_face_1',
						'animation_face_2',
						'ring',
						'roller',
						'loader_balls_1',
						'loader_balls_2',
						'loader_balls_3',
						'ripple',
						'spinner'
					);
					foreach ( $loading as $item ) {
						$this->get_sidebar_loading( $item );
					}
				} elseif ( $sc_loading ) {
					$this->get_sidebar_loading( $sc_loading );
				}
				?>
            </div>
        </div>
		<?php
		do_action( 'vi_wcaio_after_mini_cart' );
	}

	public static function get_sc_footer_message_html( $text ) {
		if ( ! $text ) {
			return '';
		}
		$text = str_replace( '{product_plus}', self::get_product_plus(  ), $text );
		echo wp_kses( $text, VI_WOO_CART_ALL_IN_ONE_DATA::extend_post_allowed_html() );
	}

	public static function get_product_plus( ) {
		$settings           = new VI_WOO_CART_ALL_IN_ONE_DATA();
		$sc_footer_pd_plus  = $settings->get_params( 'sc_footer_pd_plus' );
		$product_plus_limit = $settings->get_params( 'sc_footer_pd_plus_limit' );
		$product_plus       = self::get_sidebar_pd_plus( $sc_footer_pd_plus, $product_plus_limit );
		if ( empty( $product_plus ) || ! is_array( $product_plus ) ) {
			return '';
		}
		ob_start();
		?>
        <div class="vi-wcaio-sidebar-cart-footer-pd-wrap-wrap vi-wcaio-sidebar-cart-footer-pd-<?php echo esc_attr( $sc_footer_pd_plus ); ?>">
            <div class="vi-wcaio-sidebar-cart-footer-pd-plus-title">
				<?php echo wp_kses_post( $settings->get_params( 'sc_footer_pd_plus_title' ) ); ?>
            </div>
            <div class="vi-wcaio-sidebar-cart-footer-pd-wrap">
				<?php
				foreach ( $product_plus as $product_id ) {
					self::get_product_plus_html( $product_id );
				}
				?>
            </div>
        </div>
		<?php
		$html = ob_get_clean();

		return $html;
	}

	public static function get_product_plus_html( $product_id ) {
		if ( ! $product_id || ! $product = wc_get_product( $product_id ) ) {
			return;
		}
		$product_permalink = $product->get_permalink();
		?>
        <div class="vi-wcaio-sidebar-cart-footer-pd vi-wcaio-sidebar-cart-footer-pd-type-1">
            <div class="vi-wcaio-sidebar-cart-footer-pd-desc-wrap">
                <div class="vi-wcaio-sidebar-cart-footer-pd-img">
					<?php
					echo $product_permalink ? sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $product->get_image() ) : wp_kses_post( $product->get_image() );
					?>
                </div>
                <div class="vi-wcaio-sidebar-cart-footer-pd-desc">
                    <div class="vi-wcaio-sidebar-cart-footer-pd-name">
						<?php
						$product_name = $product->get_name();
						if ( $product_permalink ) {
							echo sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ),esc_attr( $product_name ) );
						} else {
							echo wp_kses_post(  $product_name );
						}
						?>
                    </div>
                    <div class="vi-wcaio-sidebar-cart-footer-pd-price">
						<?php echo wp_kses_post( $product->get_price_html() ); ?>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	public static function get_sidebar_content_pd_html( $wc_cart ) {
		if ( $wc_cart->is_empty() ) {
			echo sprintf( '<li class="vi-wcaio-sidebar-cart-pd-empty">%s</li>',
				apply_filters( 'vi_wcaio_get_cart_empty_text', esc_html__( 'No products in the cart.', 'woo-cart-all-in-one' ) ) );
		} else {
			$settings          = new VI_WOO_CART_ALL_IN_ONE_DATA();
			$delete_icon       = $settings->get_params( 'sc_pd_delete_icon' );
			$delete_icon_class = $settings->get_class_icon( $delete_icon, 'delete_icons' );
			foreach ( $wc_cart->get_cart() as $cart_item_key => $cart_item ) {
				$product    = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				if ( $product && $product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					$product_thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image(), $cart_item, $cart_item_key );
					?>
                    <li class="vi-wcaio-sidebar-cart-pd-wrap" data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>" data-product_id="<?php echo esc_attr( $product_id ); ?>">
                        <div class="vi-wcaio-sidebar-cart-pd-img-wrap">
							<?php
							echo $product_permalink ? sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $product_thumbnail ) : wp_kses_post( $product_thumbnail );
							?>
                        </div>
                        <div class="vi-wcaio-sidebar-cart-pd-info-wrap">
                            <div class="vi-wcaio-sidebar-cart-pd-name-wrap">
								<?php
								if ( ! $product_permalink ) {
									echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<div class="vi-wcaio-sidebar-cart-pd-name">%s</div>', $product->get_name() ), $cart_item, $cart_item_key ) );
								} else {
									echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s" class="vi-wcaio-sidebar-cart-pd-name">%s</a>', esc_url( $product_permalink ), $product->get_name() ), $cart_item, $cart_item_key ) );
								}
								?>
                                <div class="vi-wcaio-sidebar-cart-pd-remove-wrap">
									<?php
									echo apply_filters( 'vi_wcaio_mini_cart_pd_remove',
										sprintf( '<a href="%s" class="vi-wcaio-sidebar-cart-pd-remove" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="%s"></i></a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											esc_html__( 'Remove this item', 'woo-cart-all-in-one' ),
											esc_attr( $product_id ),
											esc_attr( $cart_item_key ),
											esc_attr( $product->get_sku() ),
											$delete_icon_class
										)
										, $cart_item, $cart_item_key );
									?>
                                </div>
                            </div>
                            <div class="vi-wcaio-sidebar-cart-pd-meta">
								<?php echo wp_kses_post( wc_get_formatted_cart_item_data( $cart_item ) ); ?>
                            </div>
                            <div class="vi-wcaio-sidebar-cart-pd-desc">
								<?php
								if ( $product->is_sold_individually() ) {
									echo apply_filters( 'vi_wcaio_mini_cart_pd_qty',
										sprintf( '<div class="vi-wcaio-sidebar-cart-pd-quantity vi-wcaio-hidden"><input type="hidden" name="viwcaio_cart[%s][qty]" value="1"></div>', $cart_item_key ),
										$cart_item_key, $cart_item
									);
								} else {
									$max_value = $product->get_max_purchase_quantity();
									$max_value = ( $max_value < 0 ) ? 99999 : $max_value;
									echo apply_filters( 'vi_wcaio_mini_cart_pd_qty',
										sprintf( '<div class="vi-wcaio-sidebar-cart-pd-quantity"><span class="vi_wcaio_change_qty vi_wcaio_minus">-</span><input type="number" name="viwcaio_cart[%s][qty]" value="%s" step="1" min="0" max="%s" class="vi_wcaio_qty"><span class="vi_wcaio_change_qty vi_wcaio_plus">+</span></div>',
											$cart_item_key,
											esc_attr( $cart_item['quantity'] ),
											esc_attr( $max_value )
										),
										$cart_item_key, $cart_item
									);
								}
								?>
                                <div class="vi-wcaio-sidebar-cart-pd-price">
									<?php
									echo wp_kses_post( apply_filters( 'woocommerce_cart_item_price', $wc_cart->get_product_price( $product ), $cart_item, $cart_item_key ) );
									?>
                                </div>
                            </div>
                        </div>
                    </li>
					<?php
				}
			}
		}
	}

	public function get_sidebar_loading( $type ) {
		if ( ! $type ) {
			return;
		}
		$class   = array(
			'vi-wcaio-sidebar-cart-loading vi-wcaio-sidebar-cart-loading-' . $type
		);
		$class[] = $this->is_customize ? 'vi-wcaio-disabled' : '';
		$class   = trim( implode( ' ', $class ) );
		switch ( $type ) {
			case 'spinner':
			case 'default':
				?>
                <div class="<?php echo esc_attr( $class ); ?>">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
				<?php
				break;
			case 'dual_ring':
				?>
                <div class="<?php echo esc_attr( $class ); ?>"></div>
				<?php
				break;
			case 'animation_face_1':
				?>
            <div class="<?php echo esc_attr( $class ); ?>">
                    <div></div>
                    <div></div>
                    <div></div></div><?php
				break;
			case 'animation_face_2':
			case 'ring':
				?>
            <div class="<?php echo esc_attr( $class ); ?>">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div></div><?php
				break;
			case 'roller':
				?>
                <div class="<?php echo esc_attr( $class ); ?>">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
				<?php
				break;
			case 'loader_balls_1':
				?>
                <div class="<?php echo esc_attr( $class ); ?>">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
				<?php
				break;
			case 'loader_balls_2':
			case 'loader_balls_3':
				?>
                <div class="<?php echo esc_attr( $class ); ?>">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
				<?php
				break;
			case 'ripple':
				?>
                <div class="<?php echo esc_attr( $class ); ?>">
                    <div></div>
                    <div></div>
                </div>
				<?php
				break;
		}
	}

	public static function get_sidebar_pd_plus( $type = '', $limit = 5 ) {
		if ( ! $type || ! $limit ) {
			return false;
		}
		$limit = $limit > 15 ? 15 : $limit;
		switch ( $type ) {
			case 'best_selling':
				$args        = array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'meta_key'       => 'total_sales',
					'orderby'        => 'meta_value_num',
					'order'          => 'DESC',
					'posts_per_page' => $limit,
				);
				$product_ids = array();
				$the_query   = new WP_Query( $args );
				if ( $the_query->have_posts() ) {
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						$product_ids[] = get_the_ID();
					}
				}
				wp_reset_postdata();
				break;
			case 'viewed_product':
				$viewed_products = is_active_widget( false, false, 'woocommerce_recently_viewed_products', true ) ? ( $_COOKIE['woocommerce_recently_viewed'] ?? '' ) : '';
				$viewed_products = $viewed_products ?: ( $_COOKIE['viwcaio_recently_viewed'] ?? '' );
				$product_ids     = $viewed_products ? explode( '|', wp_unslash( $viewed_products ) ) : array();
				if ( $limit < count( $product_ids ) ) {
					$product_ids = array_slice( $product_ids, 0, $limit );
				}
				break;
			case 'product_rating':
				$args        = array(
					'post_type'      => 'product',
					'meta_key'       => '_wc_average_rating',
					'orderby'        => 'meta_value_num',
					'order'          => 'DESC',
					'posts_per_page' => $limit,
					'meta_query'     => WC()->query->get_meta_query(),
					'tax_query'      => WC()->query->get_tax_query(),
				);
				$product_ids = array();
				$the_query   = new WP_Query( $args );
				if ( $the_query->have_posts() ) {
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						$product_ids[] = get_the_ID();
					}
				}
				wp_reset_postdata();
				break;
		}

		return $product_ids ?? false;
	}

	public function get_sidebar_cart_icon() {
		$sc_icon_style        = $this->get_params( 'sc_icon_style' );
		$sc_icon_default_icon = $this->get_params( 'sc_icon_default_icon' );
		$icon_class           = $this->settings->get_class_icon( $sc_icon_default_icon, 'cart_icons' );
		$wrap_class           = array(
			'vi-wcaio-sidebar-cart-icon',
			'vi-wcaio-sidebar-cart-icon-' . $sc_icon_style,
		);
		$wrap_class           = trim( implode( ' ', $wrap_class ) );
		switch ( $sc_icon_style ) {
			case '1':
			case '2':
			case '3':
				?>
                <div class="<?php echo esc_attr( $wrap_class ); ?>" data-display_style="<?php echo esc_attr( $sc_icon_style ); ?>">
                    <i class="<?php echo esc_attr( $icon_class ); ?>"></i>
                    <div class="vi-wcaio-sidebar-cart-count-wrap">
                        <div class="vi-wcaio-sidebar-cart-count">
							<?php echo wp_kses_post( WC()->cart->get_cart_contents_count() ); ?>
                        </div>
                    </div>
                </div>
				<?php
				break;
			default:
				?>
                <div class="<?php echo esc_attr( $wrap_class ); ?>">
                    <i class="<?php echo esc_attr( $icon_class ); ?>"></i>
                </div>
			<?php
		}
	}
}