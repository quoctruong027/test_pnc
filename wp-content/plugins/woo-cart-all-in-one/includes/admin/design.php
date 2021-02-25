<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOO_CART_ALL_IN_ONE_Admin_Design {
	protected $settings, $admin,$customize;

	public function __construct() {
		$this->settings         = new VI_WOO_CART_ALL_IN_ONE_DATA();
		$this->admin            = 'VI_WOO_CART_ALL_IN_ONE_Admin_Settings';
		add_action( 'customize_register', array( $this, 'design_option_customizer' ) );
		add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ) );
		add_action( 'customize_controls_print_scripts', array( $this, 'customize_controls_print_scripts' ), 99 );
		add_action( 'wp_print_styles', array( $this, 'customize_controls_print_styles' ) );
	}

	public function customize_controls_print_scripts() {
		if ( ! is_customize_preview() ) {
			return;
		}
		?>
        <script type="text/javascript">
            if (typeof wp.customize !== 'undefined') {
                viwcaio_design_init();
            }
            jQuery(document).ready(function () {
                viwcaio_design_init();
            });

            function viwcaio_design_init() {
                wp.customize.bind('ready', function () {
                    let sc_show = [
                            'vi_wcaio_design_sidebar_cart_general',
                            'vi_wcaio_design_sidebar_header',
                            'vi_wcaio_design_sidebar_footer',
                            'vi_wcaio_design_sidebar_products',
                        ],
                        sc_hide = [
                            'vi_wcaio_design_sidebar_cart_icon',
                            'vi_wcaio_design_menu_cart',
                        ];
                    jQuery.each(sc_show, function (k, v) {
                        wp.customize.section(v, function (section) {
                            section.expanded.bind(function (isExpanded) {
                                if (isExpanded) {
                                    wp.customize.previewer.send('vi_wcaio_sc_toggle', 'show', '');
                                }
                            });
                        });
                    });
                    jQuery.each(sc_hide, function (k, v) {
                        wp.customize.section(v, function (section) {
                            section.expanded.bind(function (isExpanded) {
                                if (isExpanded) {
                                    wp.customize.previewer.send('vi_wcaio_sc_toggle', 'hide', '');
                                }
                            });
                        });
                    });
                    wp.customize.previewer.bind('vi_wcaio_update_url', function (url) {
                        wp.customize.previewer.previewUrl.set(url);
                    });
                    wp.customize.panel('vi_wcaio_design', function (section) {
                        section.expanded.bind(function (isExpanded) {
                            if (isExpanded) {
                                let current_url = wp.customize.previewer.previewUrl.get(),
                                    cart_url = '<?php echo  esc_js(wc_get_page_permalink('cart')); ?>',
                                    checkout_url = '<?php echo  esc_js(wc_get_page_permalink('checkout')); ?>';
                                if (current_url.indexOf(cart_url) > -1 || current_url.indexOf(checkout_url) > -1) {
                                    wp.customize.previewer.send('vi_wcaio_update_url', '<?php echo  esc_js(wc_get_page_permalink('shop')); ?>');
                                }
                            }
                        });
                    });
                });
            }
        </script>
		<?php
	}

	public function customize_controls_print_styles() {
		if ( ! is_customize_preview() ) {
			return;
		}
		global $wp_customize;
		$this->customize = $wp_customize;
		?>
        <style type="text/css" id="vi-wcaio-preview-sc_horizontal">
            <?php
            if ($sc_horizontal = $this->get_params_customize('sc_horizontal')){
                $sc_horizontal_mobile = $sc_horizontal > 20 ? 20- $sc_horizontal : 0;
                ?>
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_left,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-top_left .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-bottom_left .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_left {
                left: <?php echo sprintf('%spx',$sc_horizontal); ?>;
            }

            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_right,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-top_right .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-bottom_right .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_right {
                right: <?php echo sprintf('%spx',$sc_horizontal); ?>;
            }

            @media screen and (max-width: 768px) {
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_left .vi-wcaio-sidebar-cart-content-wrap,
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_left .vi-wcaio-sidebar-cart-content-wrap {
                    left: <?php echo sprintf('%spx', $sc_horizontal_mobile); ?>;
                }

                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_right .vi-wcaio-sidebar-cart-content-wrap,
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_right .vi-wcaio-sidebar-cart-content-wrap {
                    right: <?php echo sprintf('%spx',$sc_horizontal_mobile); ?>;
                }
            }

            <?php
            }
             ?>
        </style>
        <style type="text/css" id="vi-wcaio-preview-sc_vertical">
            <?php
            if ($sc_vertical = $this->get_params_customize('sc_vertical')){
                $sc_vertical_mobile = $sc_vertical > 20 ? 20- $sc_vertical : 0;
                ?>
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_left,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-top_left .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_right,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-top_right .vi-wcaio-sidebar-cart-icon-wrap {
                top: <?php echo sprintf('%spx',$sc_vertical); ?>;
            }

            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-bottom_right .vi-wcaio-sidebar-cart-icon-wrap,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_right,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_left,
            .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-2.vi-wcaio-sidebar-cart-bottom_left .vi-wcaio-sidebar-cart-icon-wrap {
                bottom: <?php echo sprintf('%spx',$sc_vertical); ?>;
            }

            @media screen and (max-width: 768px) {
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_left .vi-wcaio-sidebar-cart-content-wrap,
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-top_right .vi-wcaio-sidebar-cart-content-wrap {
                    top: <?php echo sprintf('%spx', $sc_vertical_mobile); ?>;
                }

                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_right .vi-wcaio-sidebar-cart-content-wrap,
                .vi-wcaio-sidebar-cart.vi-wcaio-sidebar-cart-1.vi-wcaio-sidebar-cart-bottom_left .vi-wcaio-sidebar-cart-content-wrap {
                    bottom: <?php echo sprintf('%spx',$sc_vertical_mobile); ?>;
                }
            }

            <?php
            }
             ?>
        </style>
        <style type="text/css" id="vi-wcaio-preview-sc_icon_box_shadow">
            <?php
            if ($this->get_params_customize('sc_icon_box_shadow')){
                ?>
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap {
                box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.03), 0 4px 10px rgba(0, 0, 0, 0.17);
            }

            <?php
            }
             ?>
        </style>
        <style type="text/css" id="vi-wcaio-preview-sc_icon_scale">
            <?php
            $sc_icon_scale = $this->get_params_customize('sc_icon_scale') ?: 1;
                ?>
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap {
                transform: scale(<?php echo esc_html($sc_icon_scale); ?>) ;
            }
            @keyframes vi-wcaio-cart-icon-slide_in_left {
                from {
                    transform: translate3d(-100%, 0, 0) scale(<?php echo esc_html($sc_icon_scale); ?>);
                    visibility: hidden;
                }
                to {
                    transform: translate3d(0, 0, 0) scale(<?php echo esc_html($sc_icon_scale); ?>);
                }
            }
            @keyframes vi-wcaio-cart-icon-slide_in_right {
                from {
                    transform: translate3d(100%, 0, 0) scale(<?php echo esc_html($sc_icon_scale); ?>);
                    visibility: hidden;
                }
                to {
                    transform: translate3d(0, 0, 0) scale(<?php echo esc_html($sc_icon_scale); ?>);
                }
            }
        </style>
        <style type="text/css" id="vi-wcaio-preview-sc_icon_hover_scale">
            <?php
            $sc_icon_hover_scale =$this->get_params_customize('sc_icon_hover_scale') ?: 1;
                ?>
            @keyframes vi-wcaio-cart-icon-mouseenter {
                from {
                    transform: translate3d(0, 0, 0) scale(<?php echo esc_html($sc_icon_scale); ?>);
                }
                to {
                    transform: translate3d(0, 0, 0) scale(<?php echo esc_html($sc_icon_hover_scale); ?>);
                }
            }
            @keyframes vi-wcaio-cart-icon-mouseleave {
                from {
                    transform: translate3d(0, 0, 0) scale(<?php echo esc_html($sc_icon_hover_scale); ?>);
                }
                to {
                    transform: translate3d(0, 0, 0) scale(<?php echo esc_html($sc_icon_scale); ?>);
                }
            }
            @keyframes vi-wcaio-cart-icon-slide_out_left {
                from {
                    transform: translate3d(0, 0, 0) scale(<?php echo esc_html($sc_icon_hover_scale); ?>);
                    visibility: visible;
                    opacity: 1;
                }
                to {
                    transform: translate3d(-100%, 0, 0) scale(<?php echo esc_html($sc_icon_hover_scale); ?>);
                    visibility: hidden;
                    opacity: 0;
                }
            }
            @keyframes vi-wcaio-cart-icon-slide_out_right {
                from {
                    transform: translate3d(0, 0, 0) scale(<?php echo esc_html($sc_icon_hover_scale); ?>);
                    visibility: visible;
                    opacity: 1;
                }
                to {
                    transform: translate3d(100%, 0, 0) scale(<?php echo esc_html($sc_icon_hover_scale); ?>);
                    visibility: hidden;
                    opacity: 0;
                }
            }
        </style>
        <style type="text/css" id="vi-wcaio-preview-sc_pd_img_box_shadow">
            <?php
            if ($this->get_params_customize('sc_pd_img_box_shadow')){
                ?>
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-img-wrap img {
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
            }

            <?php
            }
             ?>
        </style>
        <style type="text/css" id="vi-wcaio-preview-sc_loading_color">
            <?php
            if ($sc_loading_color = $this->get_params_customize('sc_loading_color')){
                ?>
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-dual_ring:after {
                border-color: <?php echo esc_html($sc_loading_color); ?> transparent <?php echo esc_html($sc_loading_color); ?>  transparent;
            }

            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-ring div {
                border-color: <?php echo esc_html($sc_loading_color); ?> transparent transparent transparent;
            }

            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-ripple div {
                border: 4px solid<?php echo esc_html($sc_loading_color); ?>;
            }

            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-default div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-animation_face_1 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-animation_face_2 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-roller div:after,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-loader_balls_1 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-loader_balls_2 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-loader_balls_3 div,
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-loading-spinner div:after {
                background: <?php echo esc_html($sc_loading_color); ?>;
            }

            <?php
            }
             ?>
        </style>
		<?php
		$this->add_preview_style( 'sc_radius', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-content-wrap', 'border-radius', 'px' );
		$this->add_preview_style( 'sc_icon_border_radius', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap', 'border-radius', 'px' );
		$this->add_preview_style( 'sc_icon_bg_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap', 'background', '' );
		$this->add_preview_style( 'sc_icon_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap .vi-wcaio-sidebar-cart-icon i', 'color', '' );
		$this->add_preview_style( 'sc_icon_count_bg_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap .vi-wcaio-sidebar-cart-count-wrap', 'background', '' );
		$this->add_preview_style( 'sc_icon_count_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap .vi-wcaio-sidebar-cart-count-wrap', 'color', '' );
		$this->add_preview_style( 'sc_icon_count_border_radius', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-icon-wrap .vi-wcaio-sidebar-cart-count-wrap', 'border-radius', 'px' );
		$this->add_preview_style( 'sc_header_bg_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap', 'background', '' );
		$this->add_preview_style( 'sc_header_border_style', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap', 'border-style', '' );
		$this->add_preview_style( 'sc_header_border_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap', 'border-color', '' );
		$this->add_preview_style( 'sc_header_title_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-title-wrap', 'color', '' );
		$this->add_preview_style( 'sc_header_coupon_input_radius',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-coupon-wrap .vi-wcaio-coupon-code',
			'border-radius', 'px' );
		$this->add_preview_style( 'sc_header_coupon_button_bg_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-coupon-wrap .vi-wcaio-bt-coupon-code.button',
			'background', '' );
		$this->add_preview_style( 'sc_header_coupon_button_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-coupon-wrap .vi-wcaio-bt-coupon-code.button',
			'color', '' );
		$this->add_preview_style( 'sc_header_coupon_button_bg_color_hover',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-coupon-wrap .vi-wcaio-bt-coupon-code.button:hover',
			'background', '' );
		$this->add_preview_style( 'sc_header_coupon_button_color_hover',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-coupon-wrap .vi-wcaio-bt-coupon-code.button:hover',
			'color', '' );
		$this->add_preview_style( 'sc_header_coupon_button_border_radius',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-header-wrap .vi-wcaio-sidebar-cart-header-coupon-wrap .vi-wcaio-bt-coupon-code.button',
			'border-radius', 'px' );
		$this->add_preview_style( 'sc_footer_bg_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap', 'background', '' );
		$this->add_preview_style( 'sc_footer_border_type', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap', 'border-style', '' );
		$this->add_preview_style( 'sc_footer_border_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap', 'border-color', '' );
		$this->add_preview_style( 'sc_footer_cart_total_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-footer-cart_total > div:nth-child(1)',
			'color', '' );
		$this->add_preview_style( 'sc_footer_cart_total_color1',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-footer-cart_total > div:nth-child(2)',
			'color', '' );
		$this->add_preview_style( 'sc_footer_button_bg_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-nav.button',
			'background', '' );
		$this->add_preview_style( 'sc_footer_button_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-nav.button',
			'color', '' );
		$this->add_preview_style( 'sc_footer_button_hover_bg_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-nav.button:hover',
			'background', '' );
		$this->add_preview_style( 'sc_footer_button_hover_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-nav.button:hover',
			'color', '' );
		$this->add_preview_style( 'sc_footer_button_border_radius',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-nav.button',
			'border-radius', 'px' );
		$this->add_preview_style( 'sc_footer_bt_update_bg_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-update.button',
			'background', '' );
		$this->add_preview_style( 'sc_footer_bt_update_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-update.button',
			'color', '' );
		$this->add_preview_style( 'sc_footer_bt_update_hover_bg_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-update.button:hover',
			'background', '' );
		$this->add_preview_style( 'sc_footer_bt_update_hover_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-update.button:hover',
			'color', '' );
		$this->add_preview_style( 'sc_footer_bt_update_border_radius',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-bt-update.button',
			'border-radius', 'px' );
		$this->add_preview_style( 'sc_footer_pd_plus_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-footer-wrap .vi-wcaio-sidebar-cart-footer-pd-plus-title',
			'color', '' );
		$this->add_preview_style( 'sc_pd_bg_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products-wrap', 'background', '' );
		$this->add_preview_style( 'sc_pd_img_border_radius', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-img-wrap img', 'border-radius', 'px' );
		$this->add_preview_style( 'sc_pd_name_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-info-wrap .vi-wcaio-sidebar-cart-pd-name-wrap .vi-wcaio-sidebar-cart-pd-name, .vi-wcaio-sidebar-cart-footer-pd-name *',
			'color', '' );
		$this->add_preview_style( 'sc_pd_name_hover_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-info-wrap .vi-wcaio-sidebar-cart-pd-name-wrap .vi-wcaio-sidebar-cart-pd-name:hover, .vi-wcaio-sidebar-cart-footer-pd-name *:hover',
			'color', '' );
		$this->add_preview_style( 'sc_pd_price_color',
			'.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-info-wrap .vi-wcaio-sidebar-cart-pd-price *, .vi-wcaio-sidebar-cart-footer-pd-price *',
			'color', '' );
		$this->add_preview_style( 'sc_pd_delete_icon_font_size',
            '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-remove-wrap i:before', 'font-size', 'px' );
		$this->add_preview_style( 'sc_pd_delete_icon_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-remove-wrap i', 'color', '' );
		$this->add_preview_style( 'sc_pd_delete_icon_hover_color', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-remove-wrap i:hover', 'color', '' );
		$this->add_preview_style( 'mc_icon_color', '.vi-wcaio-menu-cart .vi-wcaio-menu-cart-icon i', 'color', '' );
		$this->add_preview_style( 'mc_icon_hover_color', '.vi-wcaio-menu-cart .vi-wcaio-menu-cart-nav-wrap:hover .vi-wcaio-menu-cart-icon i', 'color', '' );
		$this->add_preview_style( 'mc_color', '.vi-wcaio-menu-cart .vi-wcaio-menu-cart-text-wrap *', 'color', '' );
		$this->add_preview_style( 'mc_hover_color', '.vi-wcaio-menu-cart .vi-wcaio-menu-cart-nav-wrap:hover .vi-wcaio-menu-cart-text-wrap *', 'color', '' );
		?>
        <style type="text/css" id="vi-wcaio-preview-custom_css">
            <?php
            if ($custom_css = $this->get_params_customize('custom_css')){
                echo wp_kses_post($custom_css);
            }
             ?>
        </style>
        <style type="text/css" id="vi-wcaio-preview-sc_pd_qty_border_color">
            <?php
            if ($sc_pd_qty_border_color = $this->get_params_customize('sc_pd_qty_border_color')){
                ?>
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi-wcaio-sidebar-cart-pd-quantity {
                border: 1px solid <?php echo esc_html($sc_pd_qty_border_color); ?>;
            }
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi_wcaio_minus {
                border-right: 1px solid <?php echo esc_html($sc_pd_qty_border_color); ?>;
            }
            .vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi_wcaio_plus {
                border-left: 1px solid <?php echo esc_html($sc_pd_qty_border_color); ?>;
            }

            <?php
            }
             ?>
        </style>
		<?php
		$this->add_preview_style( 'sc_pd_qty_border_radius', '.vi-wcaio-sidebar-cart .vi-wcaio-sidebar-cart-products .vi-wcaio-sidebar-cart-pd-desc .vi-wcaio-sidebar-cart-pd-quantity',
			'border-radius', 'px' );
	}

	private function add_preview_style( $name, $element, $style, $suffix = '' ) {
		$id = 'vi-wcaio-preview-' . $name;
		?>
        <style type="text/css" id="<?php echo esc_attr( $id ); ?>">
            <?php
            $css = $element.'{';
            if($value = $this->get_params_customize($name)){
                $css .= $style.': '.$value.$suffix.' ;';
            }
            $css .= '}';
            echo wp_kses_post($css);
             ?>
        </style>
		<?php
	}
	protected function get_params_customize($name=''){
	    if (!$name){
	        return '';
        }
	    return $this->customize->post_value($this->customize->get_setting('woo_cart_all_in_one_params['.$name.']'),$this->settings->get_params($name));
    }

	public function customize_controls_enqueue_scripts() {
		$this->admin::enqueue_style(
			array( 'vi-wcaio-cart-icons' ),
			array( 'cart-icons.min.css' )
		);
		$this->admin::enqueue_style(
			array( 'vi-wcaio-customize-preview' ),
			array( 'customize-preview.css' )
		);
		$this->admin::enqueue_script(
			array( 'vi-wcaio-customize-setting' ),
			array( 'customize-setting.js' ),
			array( array( 'jquery', 'jquery-ui-button' ) ),
			'enqueue', true
		);
	}

	public function customize_preview_init() {
		$this->admin::enqueue_script(
			array( 'vi-wcaio-customize-preview' ),
			array( 'customize-preview.js' ),
			array( array( 'jquery', 'customize-preview', 'flexslider' ) ),
			'enqueue', true
		);
		$args = array(
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'vi-wcaio-customize-preview', 'vi_wcaio_preview', $args );
	}

	public function design_option_customizer( $wp_customize ) {
		$wp_customize->add_panel( 'vi_wcaio_design', array(
			'priority'       => 200,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Cart All In One For WooCommerce', 'woo-cart-all-in-one' ),
		) );
		$this->add_section_design_sidebar_cart_general( $wp_customize );
		$this->add_section_design_sidebar_icon( $wp_customize );
		$this->add_section_design_sidebar_header( $wp_customize );
		$this->add_section_design_sidebar_products( $wp_customize );
		$this->add_section_design_sidebar_footer( $wp_customize );
		$this->add_section_design_menu_cart( $wp_customize );
		$this->add_section_design_checkout( $wp_customize );
		$this->add_section_design_sticky_atc( $wp_customize );
		$this->add_section_design_custom_css( $wp_customize );
	}

	protected function add_section_design_sidebar_cart_general( $wp_customize ) {
		$wp_customize->add_section( 'vi_wcaio_design_sidebar_cart_general', array(
			'priority'       => 20,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Sidebar Cart', 'woo-cart-all-in-one' ),
			'panel'          => 'vi_wcaio_design',
		) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_display_type]',
			array(
				'default'           => $this->settings->get_default( 'sc_display_type' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_display_type]',
			array(
				'label'   => __( 'Display Sidebar Content', 'woo-cart-all-in-one' ),
				'section' => 'vi_wcaio_design_sidebar_cart_general',
				'type'    => 'select',
				'choices' => array(
					'1' => __( 'Style one', 'woo-cart-all-in-one' ),
					'2' => __( 'Style two', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_position]',
			array(
				'default'           => $this->settings->get_default( 'sc_position' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_position]',
			array(
				'label'   => __( 'Sidebar Cart Position', 'woo-cart-all-in-one' ),
				'section' => 'vi_wcaio_design_sidebar_cart_general',
				'type'    => 'select',
				'choices' => array(
					'top_left'     => __( 'Top Left', 'woo-cart-all-in-one' ),
					'top_right'    => __( 'Top Right', 'woo-cart-all-in-one' ),
					'bottom_left'  => __( 'Bottom Left', 'woo-cart-all-in-one' ),
					'bottom_right' => __( 'Bottom Right', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_radius]',
			array(
				'default'           => $this->settings->get_default( 'sc_radius' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control( new VIWCAIO_Customize_Range_Control( $wp_customize,
			'woo_cart_all_in_one_params[sc_radius]',
			array(
				'label'       => esc_html__( 'Border Radius For Sidebar Cart Content(px)', 'woo-cart-all-in-one' ),
				'section'     => 'vi_wcaio_design_sidebar_cart_general',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 200,
					'step' => 1,
					'id'   => 'vi-wcaio-sc_radius',
				),
			)
		) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_fly_to_cart]',
			array(
				'default'           => $this->settings->get_default( 'sc_fly_to_cart' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCAIO_Customize_Checkbox_Control( $wp_customize,
				'woo_cart_all_in_one_params[sc_fly_to_cart]', array(
					'label'       => esc_html__( 'Fly To Cart', 'woo-cart-all-in-one' ),
					'settings'    => 'woo_cart_all_in_one_params[sc_fly_to_cart]',
					'section'     => 'vi_wcaio_design_sidebar_cart_general',
					'description' => esc_html__( 'The products will be flown to Cart after clicking on add to cart button', 'woo-cart-all-in-one' ),
				) )
		);

		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_effect_after_atc]',
			array(
				'default'           => $this->settings->get_default( 'sc_effect_after_atc' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_effect_after_atc]',
			array(
				'label'   => __( 'Cart Effect After Add Product', 'woo-cart-all-in-one' ),
				'section' => 'vi_wcaio_design_sidebar_cart_general',
				'type'    => 'select',
				'choices' => array(
					'0' => __( 'None', 'woo-cart-all-in-one' ),
					'open' => __( 'Open cart', 'woo-cart-all-in-one' ),
					'shake_horizontal' => __( 'Shake Horizontal', 'woo-cart-all-in-one' ),
					'shake_vertical' => __( 'Shake Vertical', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_trigger_type]',
			array(
				'default'           => $this->settings->get_default( 'sc_trigger_type' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_trigger_type]',
			array(
				'label'       => esc_html__( 'Sidebar Trigger Event Type', 'woo-cart-all-in-one' ),
				'section'     => 'vi_wcaio_design_sidebar_cart_general',
				'type'        => 'select',
				'choices'     => array(
					'hover' => __( 'MouseOver', 'woo-cart-all-in-one' ),
					'click' => __( 'Click', 'woo-cart-all-in-one' ),
				),
				'description' => esc_html__( 'If choose "Click", the cart content will be shown after clicking on the cart icon', 'woo-cart-all-in-one' ),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_trigger_style]',
			array(
				'default'           => $this->settings->get_default( 'sc_trigger_style' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_trigger_style]',
			array(
				'label'   => esc_html__( 'Sidebar Trigger Event Style', 'woo-cart-all-in-one' ),
				'section' => 'vi_wcaio_design_sidebar_cart_general',
				'type'    => 'radio',
				'choices' => array(
					'fade'        => __( 'Fade', 'woo-cart-all-in-one' ),
					'flip'        => __( 'Flip', 'woo-cart-all-in-one' ),
					'slide'       => __( 'Slide', 'woo-cart-all-in-one' ),
					'roll'        => __( 'Roll', 'woo-cart-all-in-one' ),
					'rotate'      => __( 'Rotate', 'woo-cart-all-in-one' ),
					'rotate_down' => __( 'RotateInDown', 'woo-cart-all-in-one' ),
					'rotate_up'   => __( 'RotateInUp', 'woo-cart-all-in-one' ),
					'zoom'        => __( 'Zoom', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_loading]',
			array(
				'default'           => $this->settings->get_default( 'sc_loading' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_loading]',
			array(
				'label'   => esc_html__( 'Loading Type', 'woo-cart-all-in-one' ),
				'section' => 'vi_wcaio_design_sidebar_cart_general',
				'type'    => 'radio',
				'choices' => array(
					'0'                => __( 'Hidden', 'woo-cart-all-in-one' ),
					'default'          => __( 'Default', 'woo-cart-all-in-one' ),
					'dual_ring'        => __( 'Dual Ring', 'woo-cart-all-in-one' ),
					'animation_face_1' => __( 'Animation Facebook 1', 'woo-cart-all-in-one' ),
					'animation_face_2' => __( 'Animation Facebook 2', 'woo-cart-all-in-one' ),
					'ring'             => __( 'Ring', 'woo-cart-all-in-one' ),
					'roller'           => __( 'Roller', 'woo-cart-all-in-one' ),
					'loader_balls_1'   => __( 'Loader Balls 1', 'woo-cart-all-in-one' ),
					'loader_balls_2'   => __( 'Loader Balls 2', 'woo-cart-all-in-one' ),
					'loader_balls_3'   => __( 'Loader Balls 3', 'woo-cart-all-in-one' ),
					'ripple'           => __( 'Ripple', 'woo-cart-all-in-one' ),
					'spinner'          => __( 'Spinner', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_loading_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_loading_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );

		$wp_customize->add_control(
			new WP_Customize_Color_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_loading_color]',
				array(
					'label'    => __( 'Loading Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_cart_general',
					'settings' => 'woo_cart_all_in_one_params[sc_loading_color]',
				)
			)
		);
	}

	protected function add_section_design_sidebar_icon( $wp_customize ) {
		$wp_customize->add_section( 'vi_wcaio_design_sidebar_cart_icon', array(
			'priority'       => 20,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Sidebar Cart Icon', 'woo-cart-all-in-one' ),
			'panel'          => 'vi_wcaio_design',
		) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_style]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_style' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_icon_style]',
			array(
				'label'    => esc_html__( 'Cart Style', 'woo-cart-all-in-one' ),
				'type'     => 'select',
				'settings' => 'woo_cart_all_in_one_params[sc_icon_style]',
				'section'  => 'vi_wcaio_design_sidebar_cart_icon',
				'choices'  => array(
					'1' => __( 'Style one', 'woo-cart-all-in-one' ),
					'2' => __( 'Style two', 'woo-cart-all-in-one' ),
					'3' => __( 'Style three', 'woo-cart-all-in-one' ),
					'4' => __( 'Style four', 'woo-cart-all-in-one' ),
				),
			) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_box_shadow]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_box_shadow' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new VIWCAIO_Customize_Checkbox_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_icon_box_shadow]',
				array(
					'label'    => esc_html__( 'Enable Box Shadow', 'woo-cart-all-in-one' ),
					'settings' => 'woo_cart_all_in_one_params[sc_icon_box_shadow]',
					'section'  => 'vi_wcaio_design_sidebar_cart_icon',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_scale]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_scale' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'woo_cart_all_in_one_params[sc_icon_scale]',
			array(
				'label'       => __( 'Sidebar Cart Icon Size', 'woo-cart-all-in-one' ),
				'type'        => 'number',
				'section'     => 'vi_wcaio_design_sidebar_cart_icon',
				'input_attrs' => array(
					'min'  => 0.5,
					'max'  => 3,
					'step' => 0.01,
				),
				'description' => esc_html__( 'Set the sidebar cart icon size. This new size parameter need to be the a ratio compared with original icon size', 'woo-cart-all-in-one' ),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_hover_scale]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_hover_scale' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'woo_cart_all_in_one_params[sc_icon_hover_scale]',
			array(
				'label'       => __( 'Sidebar Cart Icon Size When Hovering', 'woo-cart-all-in-one' ),
				'type'        => 'number',
				'section'     => 'vi_wcaio_design_sidebar_cart_icon',
				'input_attrs' => array(
					'min'  => 0.5,
					'max'  => 3,
					'step' => 0.01,
				),
				'description' => esc_html__( 'Set the size of Sidebar Cart Icon when hovering', 'woo-cart-all-in-one' ),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_border_radius]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_border_radius' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control( new VIWCAIO_Customize_Range_Control( $wp_customize,
			'woo_cart_all_in_one_params[sc_icon_border_radius]',
			array(
				'label'       => esc_html__( 'Cart Icon Radius(px)', 'woo-cart-all-in-one' ),
				'section'     => 'vi_wcaio_design_sidebar_cart_icon',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 50,
					'step' => 1,
					'id'   => 'vi-wcaio-sc_icon_border_radius',
				),
			)
		) );

		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_horizontal]',
			array(
				'default'           => $this->settings->get_default( 'sc_horizontal' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control( new VIWCAIO_Customize_Range_Control( $wp_customize,
			'woo_cart_all_in_one_params[sc_horizontal]',
			array(
				'label'       => esc_html__( 'Sidebar Cart Horizontal(px)', 'woo-cart-all-in-one' ),
				'section'     => 'vi_wcaio_design_sidebar_cart_icon',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 200,
					'step' => 1,
					'id'   => 'vi-wcaio-sc_horizontal',
				),
			)
		) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_vertical]',
			array(
				'default'           => $this->settings->get_default( 'sc_vertical' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control( new VIWCAIO_Customize_Range_Control( $wp_customize,
			'woo_cart_all_in_one_params[sc_vertical]',
			array(
				'label'       => esc_html__( 'Sidebar Cart Vertical(px)', 'woo-cart-all-in-one' ),
				'section'     => 'vi_wcaio_design_sidebar_cart_icon',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 200,
					'step' => 1,
					'id'   => 'vi-wcaio-sc_vertical',
				),
			)
		) );
		$cart_icons   = $this->settings->get_class_icons( 'cart_icons' );
		$cart_icons_t = array();
		foreach ( $cart_icons as $k => $class ) {
			$cart_icons_t[ $k ] = '<i class="' . $class . '"></i>';
		}
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_default_icon]',
			array(
				'default'    => $this->settings->get_default( 'sc_icon_default_icon' ),
				'type'       => 'option',
				'capability' => 'manage_options',
				'transport'  => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new VIWCAIO_Customize_Radio_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_icon_default_icon]',
				array(
					'label'   => __( 'Cart Icon Type', 'woo-cart-all-in-one' ),
					'section' => 'vi_wcaio_design_sidebar_cart_icon',
					'choices' => $cart_icons_t
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_icon_bg_color]',
				array(
					'label'    => __( 'Cart Icon Background', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_cart_icon',
					'settings' => 'woo_cart_all_in_one_params[sc_icon_bg_color]',
				) )
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_icon_color]',
				array(
					'label'    => __( 'Cart Icon Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_cart_icon',
					'settings' => 'woo_cart_all_in_one_params[sc_icon_color]',
				) )
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_count_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_count_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_icon_count_bg_color]',
				array(
					'label'    => __( 'Product Counter Background Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_cart_icon',
					'settings' => 'woo_cart_all_in_one_params[sc_icon_count_bg_color]',
				) )
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_count_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_count_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_icon_count_color]',
				array(
					'label'    => __( 'Product Counter Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_cart_icon',
					'settings' => 'woo_cart_all_in_one_params[sc_icon_count_color]',
				) )
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_icon_count_border_radius]',
			array(
				'default'           => $this->settings->get_default( 'sc_icon_count_border_radius' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new VIWCAIO_Customize_Range_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_icon_count_border_radius]',
				array(
					'label'       => esc_html__( 'Product Counter Border Radius(px)', 'woo-cart-all-in-one' ),
					'section'     => 'vi_wcaio_design_sidebar_cart_icon',
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
						'id'   => 'vi-wcaio-sc_icon_count_border_radius',
					),
				)
			)
		);
	}

	protected function add_section_design_sidebar_header( $wp_customize ) {
		$wp_customize->add_section( 'vi_wcaio_design_sidebar_header', array(
			'priority'       => 20,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Sidebar Cart Header', 'woo-cart-all-in-one' ),
			'panel'          => 'vi_wcaio_design',
		) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_header_bg_color]',
				array(
					'label'    => __( 'Background Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_header',
					'settings' => 'woo_cart_all_in_one_params[sc_header_bg_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_border_style]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_border_style' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_header_border_style]',
			array(
				'label'   => __( 'Header Border Style ', 'woo-cart-all-in-one' ),
				'section' => 'vi_wcaio_design_sidebar_header',
				'type'    => 'select',
				'choices' => array(
					'none'   => __( 'No border', 'woo-cart-all-in-one' ),
					'solid'  => __( 'Solid', 'woo-cart-all-in-one' ),
					'dotted' => __( 'Dotted', 'woo-cart-all-in-one' ),
					'dashed' => __( 'Dashed', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_border_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_border_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_header_border_color]',
				array(
					'label'    => __( 'Header Border Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_header',
					'settings' => 'woo_cart_all_in_one_params[sc_header_border_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_title]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_title' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'woo_cart_all_in_one_params[sc_header_title]',
			array(
				'label'   => __( 'Cart Title', 'woo-cart-all-in-one' ),
				'type'    => 'text',
				'section' => 'vi_wcaio_design_sidebar_header',
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_title_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_title_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_header_title_color]',
				array(
					'label'    => __( 'Title Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_header',
					'settings' => 'woo_cart_all_in_one_params[sc_header_title_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_coupon_enable]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_coupon_enable' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new VIWCAIO_Customize_Checkbox_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_header_coupon_enable]',
				array(
					'label'    => esc_html__( 'Enable Coupon', 'woo-cart-all-in-one' ),
					'settings' => 'woo_cart_all_in_one_params[sc_header_coupon_enable]',
					'section'  => 'vi_wcaio_design_sidebar_header',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_coupon_input_radius]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_coupon_input_radius' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new VIWCAIO_Customize_Range_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_header_coupon_input_radius]',
				array(
					'label'       => esc_html__( 'Coupon Input Radius(px)', 'woo-cart-all-in-one' ),
					'section'     => 'vi_wcaio_design_sidebar_header',
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
						'id'   => 'vi-wcaio-sc_header_coupon_input_radius',
					),
				)
			) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_coupon_button_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_coupon_button_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_header_coupon_button_bg_color]',
				array(
					'label'    => __( 'Apply Coupon Button Background', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_header',
					'settings' => 'woo_cart_all_in_one_params[sc_header_coupon_button_bg_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_coupon_button_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_coupon_button_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_header_coupon_button_color]',
				array(
					'label'    => __( 'Apply Coupon Button Text Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_header',
					'settings' => 'woo_cart_all_in_one_params[sc_header_coupon_button_color]',
				) )
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_coupon_button_bg_color_hover]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_coupon_button_bg_color_hover' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_header_coupon_button_bg_color_hover]',
				array(
					'label'    => __( 'Apply Coupon Button Hover Background', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_header',
					'settings' => 'woo_cart_all_in_one_params[sc_header_coupon_button_bg_color_hover]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_coupon_button_color_hover]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_coupon_button_color_hover' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_header_coupon_button_color_hover]',
				array(
					'label'    => __( 'Apply Coupon Button Hover Text Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_header',
					'settings' => 'woo_cart_all_in_one_params[sc_header_coupon_button_color_hover]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_header_coupon_button_border_radius]',
			array(
				'default'           => $this->settings->get_default( 'sc_header_coupon_button_border_radius' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new VIWCAIO_Customize_Range_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_header_coupon_button_border_radius]',
				array(
					'label'       => esc_html__( 'Apply Coupon Button Radius(px)', 'woo-cart-all-in-one' ),
					'section'     => 'vi_wcaio_design_sidebar_header',
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
						'id'   => 'vi-wcaio-sc_header_coupon_button_border_radius',
					),
				)
			) );
	}

	protected function add_section_design_sidebar_footer( $wp_customize ) {
		$wp_customize->add_section( 'vi_wcaio_design_sidebar_footer', array(
			'priority'       => 20,
			'capability'     => 'manage_options',
			'theme_supports' => '',
			'title'          => __( 'Sidebar Cart Footer', 'woo-cart-all-in-one' ),
			'panel'          => 'vi_wcaio_design',
		) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_bg_color]',
				array(
					'label'    => __( 'Background Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_bg_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_border_type]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_border_type' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_footer_border_type]',
			array(
				'label'   => __( 'Footer Border Style', 'woo-cart-all-in-one' ),
				'section' => 'vi_wcaio_design_sidebar_footer',
				'type'    => 'select',
				'choices' => array(
					'none'   => __( 'No border', 'woo-cart-all-in-one' ),
					'solid'  => __( 'Solid', 'woo-cart-all-in-one' ),
					'dotted' => __( 'Dotted', 'woo-cart-all-in-one' ),
					'dashed' => __( 'Dashed', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_border_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_border_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_border_color]',
				array(
					'label'    => __( 'Footer Border Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_border_color]',
				) )
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_cart_total]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_cart_total' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_footer_cart_total]',
			array(
				'label'    => esc_html__( 'Price to display', 'woo-cart-all-in-one' ),
				'type'     => 'select',
				'settings' => 'woo_cart_all_in_one_params[sc_footer_cart_total]',
				'section'  => 'vi_wcaio_design_sidebar_footer',
				'choices'  => array(
					'subtotal' => __( 'Subtotal (total of products)', 'woo-cart-all-in-one' ),
					'total'    => __( 'Cart total', 'woo-cart-all-in-one' ),
				),

			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_cart_total_text]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_cart_total_text' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'woo_cart_all_in_one_params[sc_footer_cart_total_text]',
			array(
				'label'   => __( 'Total Text', 'woo-cart-all-in-one' ),
				'type'    => 'text',
				'section' => 'vi_wcaio_design_sidebar_footer',
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_cart_total_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_cart_total_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_cart_total_color]',
				array(
					'label'    => __( 'Total Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_cart_total_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_cart_total_color1]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_cart_total_color1' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_cart_total_color1]',
				array(
					'label'    => __( 'Price Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_cart_total_color1]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_button]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_button' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_footer_button]',
			array(
				'label'    => esc_html__( 'Button Enable', 'woo-cart-all-in-one' ),
				'type'     => 'select',
				'settings' => 'woo_cart_all_in_one_params[sc_footer_button]',
				'section'  => 'vi_wcaio_design_sidebar_footer',
				'choices'  => array(
					'cart'     => __( 'View cart ', 'woo-cart-all-in-one' ),
					'checkout' => __( 'Checkout ', 'woo-cart-all-in-one' ),
				),

			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_bt_cart_text]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_bt_cart_text' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'woo_cart_all_in_one_params[sc_footer_bt_cart_text]',
			array(
				'label'   => __( 'View Cart Button Text', 'woo-cart-all-in-one' ),
				'type'    => 'text',
				'section' => 'vi_wcaio_design_sidebar_footer',
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_bt_checkout_text]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_bt_checkout_text' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'woo_cart_all_in_one_params[sc_footer_bt_checkout_text]',
			array(
				'label'   => __( 'Checkout Button Text', 'woo-cart-all-in-one' ),
				'type'    => 'text',
				'section' => 'vi_wcaio_design_sidebar_footer',
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_button_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_button_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_button_bg_color]',
				array(
					'label'    => __( 'Button Background', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_button_bg_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_button_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_button_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_button_color]',
				array(
					'label'    => __( 'Button Text Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_button_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_button_hover_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_button_hover_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_button_hover_bg_color]',
				array(
					'label'    => __( 'Button Hover Background', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_button_hover_bg_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_button_hover_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_button_hover_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_button_hover_color]',
				array(
					'label'    => __( 'Button Hover Text Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_button_hover_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_button_border_radius]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_button_border_radius' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new VIWCAIO_Customize_Range_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_footer_button_border_radius]',
				array(
					'label'       => esc_html__( 'Button Radius(px)', 'woo-cart-all-in-one' ),
					'section'     => 'vi_wcaio_design_sidebar_footer',
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
						'id'   => 'vi-wcaio-sc_footer_button_border_radius',
					),
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_bt_update_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_bt_update_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_bt_update_bg_color]',
				array(
					'label'    => __( 'Update Button Background', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_bt_update_bg_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_bt_update_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_bt_update_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_bt_update_color]',
				array(
					'label'    => __( 'Update Button Text Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_bt_update_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_bt_update_hover_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_bt_update_hover_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_bt_update_hover_bg_color]',
				array(
					'label'    => __( 'Update Button Hover Background', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_bt_update_hover_bg_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_bt_update_hover_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_bt_update_hover_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_footer_bt_update_hover_color]',
				array(
					'label'    => __( 'Update Button Hover Text Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_bt_update_hover_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_bt_update_border_radius]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_bt_update_border_radius' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new VIWCAIO_Customize_Range_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_footer_bt_update_border_radius]',
				array(
					'label'       => esc_html__( 'Update Button Radius(px)', 'woo-cart-all-in-one' ),
					'section'     => 'vi_wcaio_design_sidebar_footer',
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
						'id'   => 'vi-wcaio-sc_footer_bt_update_border_radius',
					),
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params_premium[sc_footer_message]',
			array(
				'type'              => 'option',
				'capability'        => 'manage_options',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCAIO_Customize_Premium( $wp_customize, 'woo_cart_all_in_one_params_premium[sc_footer_message]',
			array(
				'section'  => 'vi_wcaio_design_sidebar_footer',
				'label'    => __( 'Custom Message', 'woo-cart-all-in-one' ),
				'choices'    => array(
				        'button'    => 'yes',
                    )
			)
		) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_pd_plus]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_pd_plus' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[sc_footer_pd_plus]',
			array(
				'label'    => esc_html__( 'Show Products Plus', 'woo-cart-all-in-one' ),
				'type'     => 'select',
				'settings' => 'woo_cart_all_in_one_params[sc_footer_pd_plus]',
				'section'  => 'vi_wcaio_design_sidebar_footer',
				'choices'  => array(
					''   => __( 'None', 'woo-cart-all-in-one' ),
					'best_selling'   => __( 'Best Selling Products', 'woo-cart-all-in-one' ),
					'viewed_product' => __( 'Recently Viewed products', 'woo-cart-all-in-one' ),
					'product_rating' => __( 'Top Rated Products', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_pd_plus_title]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_pd_plus_title' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'woo_cart_all_in_one_params[sc_footer_pd_plus_title]',
			array(
				'type'     => 'text',
				'section'  => 'vi_wcaio_design_sidebar_footer',
				'label'    => __( 'Product Plus Title', 'woo-cart-all-in-one' ),
                'description' => __('The title of suggested products list in footer','woo-cart-all-in-one'),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_pd_plus_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_pd_plus_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize, 'woo_cart_all_in_one_params[sc_footer_pd_plus_color]',
				array(
					'label'    => __( 'Product Plus Title Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_footer',
					'settings' => 'woo_cart_all_in_one_params[sc_footer_pd_plus_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_footer_pd_plus_limit]',
			array(
				'default'           => $this->settings->get_default( 'sc_footer_pd_plus_limit' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'woo_cart_all_in_one_params[sc_footer_pd_plus_limit]',
			array(
				'label'       => __( 'Number Of Products To Show', 'woo-cart-all-in-one' ),
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 1,
					'max'  => 15,
					'step' => 1,
				),
				'section'     => 'vi_wcaio_design_sidebar_footer',
				'description' => esc_html__( 'The maximum number of  showed products is 15', 'woo-cart-all-in-one' ),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params_premium[sc_footer_pd_plus_bt_atc]',
			array(
				'type'              => 'option',
				'capability'        => 'manage_options',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCAIO_Customize_Premium( $wp_customize, 'woo_cart_all_in_one_params_premium[sc_footer_pd_plus_bt_atc]',
			array(
				'section'  => 'vi_wcaio_design_sidebar_footer',
				'label'    => __( 'Cart button on Product Plus', 'woo-cart-all-in-one' ),
				'choices'    => array(
					'img_src'    => [VI_WOO_CART_ALL_IN_ONE_IMAGES.'sidebar_cart_footer_pre.png'],
				)
			)
		) );
	}

	protected function add_section_design_sidebar_products( $wp_customize ) {
		$wp_customize->add_section( 'vi_wcaio_design_sidebar_products',
			array(
				'priority'       => 20,
				'capability'     => 'manage_options',
				'theme_supports' => '',
				'title'          => __( 'Sidebar Cart List Products', 'woo-cart-all-in-one' ),
				'panel'          => 'vi_wcaio_design',
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params_premium[sc_pd_update_cart]',
			array(
				'type'              => 'option',
				'capability'        => 'manage_options',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCAIO_Customize_Premium( $wp_customize, 'woo_cart_all_in_one_params_premium[sc_pd_update_cart]',
			array(
				'section'  => 'vi_wcaio_design_sidebar_products',
				'label'    => __( 'Update cart when changing the product quantity', 'woo-cart-all-in-one' ),
				'choices'    => array(
					'button'    => 'yes',
				)
			)
		) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_bg_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_bg_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_pd_bg_color]',
				array(
					'label'    => __( 'Background Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_products',
					'settings' => 'woo_cart_all_in_one_params[sc_pd_bg_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_img_box_shadow]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_img_box_shadow' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new VIWCAIO_Customize_Checkbox_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_pd_img_box_shadow]',
				array(
					'label'    => esc_html__( 'Enable Image Box Shadow', 'woo-cart-all-in-one' ),
					'settings' => 'woo_cart_all_in_one_params[sc_pd_img_box_shadow]',
					'section'  => 'vi_wcaio_design_sidebar_products',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_img_border_radius]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_img_border_radius' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new VIWCAIO_Customize_Range_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_pd_img_border_radius]',
				array(
					'label'       => esc_html__( 'Product Image Border Radius(px)', 'woo-cart-all-in-one' ),
					'section'     => 'vi_wcaio_design_sidebar_products',
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
						'id'   => 'vi-wcaio-sc_pd_img_border_radius',
					),
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_name_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_name_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_pd_name_color]',
				array(
					'label'    => __( 'Name Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_products',
					'settings' => 'woo_cart_all_in_one_params[sc_pd_name_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_name_hover_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_name_hover_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_pd_name_hover_color]',
				array(
					'label'    => __( 'Name Hover Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_products',
					'settings' => 'woo_cart_all_in_one_params[sc_pd_name_hover_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_price_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_price_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_pd_price_color]',
				array(
					'label'    => __( 'Price Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_products',
					'settings' => 'woo_cart_all_in_one_params[sc_pd_price_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_qty_border_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_qty_border_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_pd_qty_border_color]',
				array(
					'label'    => __( 'Quantity Border Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_products',
					'settings' => 'woo_cart_all_in_one_params[sc_pd_qty_border_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_qty_border_radius]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_qty_border_radius' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new VIWCAIO_Customize_Range_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_pd_qty_border_radius]',
				array(
					'label'       => esc_html__( 'Quantity Border Radius(px)', 'woo-cart-all-in-one' ),
					'section'     => 'vi_wcaio_design_sidebar_products',
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
						'id'   => 'vi-wcaio-sc_pd_qty_border_radius',
					),
				)
			)
		);
		$delete_icons   = $this->settings->get_class_icons( 'delete_icons' );
		$delete_icons_t = array();
		foreach ( $delete_icons as $k => $class ) {
			$delete_icons_t[ $k ] = '<i class="' . $class . '"></i>';
		}
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_delete_icon]',
			array(
				'default'    => $this->settings->get_default( 'sc_pd_delete_icon' ),
				'type'       => 'option',
				'capability' => 'manage_options',
				'transport'  => 'postMessage',
			) );
		$wp_customize->add_control(
			new VIWCAIO_Customize_Radio_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_pd_delete_icon]',
				array(
					'label'   => __( 'Trash Icon Style', 'woo-cart-all-in-one' ),
					'section' => 'vi_wcaio_design_sidebar_products',
					'choices' => $delete_icons_t,
				)
			) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_delete_icon_font_size]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_delete_icon_font_size' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCAIO_Customize_Range_Control( $wp_customize,
			'woo_cart_all_in_one_params[sc_pd_delete_icon_font_size]',
			array(
				'label'       => __( 'Font Size for Trash Icon(px)', 'woo-cart-all-in-one' ),
				'section'     => 'vi_wcaio_design_sidebar_products',
				'input_attrs' => array(
					'min'  => 5,
					'max'  => 30,
					'step' => 1,
					'id'   => 'vi-wcaio-sc_pd_delete_icon_font_size',
				),
			)
		) );
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_delete_icon_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_delete_icon_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			) );

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[sc_pd_delete_icon_color]',
				array(
					'label'    => __( 'Trash Icon Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_products',
					'settings' => 'woo_cart_all_in_one_params[sc_pd_delete_icon_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[sc_pd_delete_icon_hover_color]',
			array(
				'default'           => $this->settings->get_default( 'sc_pd_delete_icon_hover_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control( $wp_customize, 'woo_cart_all_in_one_params[sc_pd_delete_icon_hover_color]',
				array(
					'label'    => __( 'Trash Icon Hover Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_sidebar_products',
					'settings' => 'woo_cart_all_in_one_params[sc_pd_delete_icon_hover_color]',
				) )
		);
	}

	protected function add_section_design_menu_cart( $wp_customize ) {
		$wp_customize->add_section( 'vi_wcaio_design_menu_cart',
			array(
				'priority'       => 20,
				'capability'     => 'manage_options',
				'theme_supports' => '',
				'title'          => __( 'Menu Cart', 'woo-cart-all-in-one' ),
				'panel'          => 'vi_wcaio_design',
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[mc_nav_page]',
			array(
				'default'           => $this->settings->get_default( 'mc_nav_page' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );
		$wp_customize->add_control(
			'woo_cart_all_in_one_params[mc_nav_page]',
			array(
				'label'       => esc_html__( 'Navigation Page', 'woo-cart-all-in-one' ),
				'type'        => 'select',
				'settings'    => 'woo_cart_all_in_one_params[mc_nav_page]',
				'section'     => 'vi_wcaio_design_menu_cart',
				'choices'     => array(
					'cart'     => __( 'Cart page', 'woo-cart-all-in-one' ),
					'checkout' => __( 'Checkout page', 'woo-cart-all-in-one' ),
				),
				'description' => __( 'Choose the page redirected to when clicking on Menu Cart', 'woo-cart-all-in-one' ),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[mc_content]',
			array(
				'default'           => $this->settings->get_default( 'mc_content' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new VIWCAIO_Customize_Checkbox_Control( $wp_customize, 'woo_cart_all_in_one_params[mc_content]',
				array(
					'label'    => esc_html__( 'Show Content Cart', 'woo-cart-all-in-one' ),
					'settings' => 'woo_cart_all_in_one_params[mc_content]',
					'section'  => 'vi_wcaio_design_menu_cart',
				)
			)
		);
		$cart_icons   = $this->settings->get_class_icons( 'cart_icons' );
		$cart_icons_t = array();
		foreach ( $cart_icons as $k => $class ) {
			$cart_icons_t[ $k ] = '<i class="' . $class . '"></i>';
		}
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[mc_icon]',
			array(
				'default'    => $this->settings->get_default( 'mc_icon' ),
				'type'       => 'option',
				'capability' => 'manage_options',
				'transport'  => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new VIWCAIO_Customize_Radio_Control( $wp_customize, 'woo_cart_all_in_one_params[mc_icon]',
				array(
					'label'   => __( 'Cart Icon Type', 'woo-cart-all-in-one' ),
					'section' => 'vi_wcaio_design_menu_cart',
					'choices' => $cart_icons_t,
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[mc_icon_color]',
			array(
				'default'           => $this->settings->get_default( 'mc_icon_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[mc_icon_color]',
				array(
					'label'    => __( 'Cart Icon Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_menu_cart',
					'settings' => 'woo_cart_all_in_one_params[mc_icon_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[mc_icon_hover_color]',
			array(
				'default'           => $this->settings->get_default( 'mc_icon_hover_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[mc_icon_hover_color]',
				array(
					'label'    => __( 'Cart Icon Hover Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_menu_cart',
					'settings' => 'woo_cart_all_in_one_params[mc_icon_hover_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[mc_display_style]',
			array(
				'default'           => $this->settings->get_default( 'mc_display_style' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );

		$wp_customize->add_control(
			'woo_cart_all_in_one_params[mc_display_style]',
			array(
				'label'    => esc_html__( 'Menu Cart Text', 'woo-cart-all-in-one' ),
				'type'     => 'select',
				'settings' => 'woo_cart_all_in_one_params[mc_display_style]',
				'section'  => 'vi_wcaio_design_menu_cart',
				'choices'  => array(
					'product_counter' => __( 'Product Counter', 'woo-cart-all-in-one' ),
					'price'           => __( 'Price', 'woo-cart-all-in-one' ),
					'all'             => __( 'Product Counter & Price', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[mc_cart_total]',
			array(
				'default'           => $this->settings->get_default( 'mc_cart_total' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			) );

		$wp_customize->add_control(
			'woo_cart_all_in_one_params[mc_cart_total]',
			array(
				'label'    => esc_html__( 'Menu Cart Price', 'woo-cart-all-in-one' ),
				'type'     => 'select',
				'settings' => 'woo_cart_all_in_one_params[mc_cart_total]',
				'section'  => 'vi_wcaio_design_menu_cart',
				'choices'  => array(
					'total'    => __( 'Total', 'woo-cart-all-in-one' ),
					'subtotal' => __( 'Subtotal', 'woo-cart-all-in-one' ),
				),
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[mc_color]',
			array(
				'default'           => $this->settings->get_default( 'mc_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'woo_cart_all_in_one_params[mc_color]',
				array(
					'label'    => __( 'Text Color', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_menu_cart',
					'settings' => 'woo_cart_all_in_one_params[mc_color]',
				)
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[mc_hover_color]',
			array(
				'default'           => $this->settings->get_default( 'mc_hover_color' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control( $wp_customize, 'woo_cart_all_in_one_params[mc_hover_color]',
				array(
					'label'    => __( 'Text Color Hover', 'woo-cart-all-in-one' ),
					'section'  => 'vi_wcaio_design_menu_cart',
					'settings' => 'woo_cart_all_in_one_params[mc_hover_color]',
				)
			)
		);
	}

	protected function add_section_design_sticky_atc( $wp_customize ) {
		$wp_customize->add_section( 'vi_wcaio_design_sticky_atc',
			array(
				'priority'       => 20,
				'capability'     => 'manage_options',
				'theme_supports' => '',
				'title'          => __( 'Sticky Add To Cart Button', 'woo-cart-all-in-one' ),
				'panel'          => 'vi_wcaio_design',
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params_premium[sticky_atc]',
			array(
				'type'              => 'option',
				'capability'        => 'manage_options',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCAIO_Customize_Premium( $wp_customize, 'woo_cart_all_in_one_params_premium[sticky_atc]',
			array(
				'section'  => 'vi_wcaio_design_sticky_atc',
				'choices'    => array(
					'img_src'    => [ VI_WOO_CART_ALL_IN_ONE_IMAGES.'sticky_bar.png', ],
				)
			)
		) );
	}

	protected function add_section_design_checkout( $wp_customize ) {
		$wp_customize->add_section( 'vi_wcaio_design_checkout', array(
				'priority'       => 20,
				'capability'     => 'manage_options',
				'theme_supports' => '',
				'title'          => __( 'Checkout', 'woo-cart-all-in-one' ),
				'panel'          => 'vi_wcaio_design',
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params_premium[sc_checkout]',
			array(
				'type'              => 'option',
				'capability'        => 'manage_options',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( new VIWCAIO_Customize_Premium( $wp_customize, 'woo_cart_all_in_one_params_premium[sc_checkout]',
			array(
				'section'  => 'vi_wcaio_design_checkout',
				'choices'    => array(
					'img_src'    => [
					        VI_WOO_CART_ALL_IN_ONE_IMAGES.'sc_checkout_1.png',
					        VI_WOO_CART_ALL_IN_ONE_IMAGES.'sc_checkout_2.png',
                    ],
				)
			)
		) );
	}
	protected function add_section_design_custom_css( $wp_customize ) {
		$wp_customize->add_section( 'vi_wcaio_design_custom_css', array(
				'priority'       => 20,
				'capability'     => 'manage_options',
				'theme_supports' => '',
				'title'          => __( 'Custom CSS', 'woo-cart-all-in-one' ),
				'panel'          => 'vi_wcaio_design',
			)
		);
		$wp_customize->add_setting( 'woo_cart_all_in_one_params[custom_css]', array(
				'default'           => $this->settings->get_default( 'custom_css' ),
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'wp_kses_post',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control( 'woo_cart_all_in_one_params[custom_css]',
			array(
				'type'     => 'textarea',
				'priority' => 10,
				'section'  => 'vi_wcaio_design_custom_css',
				'label'    => __( 'Custom CSS', 'woo-cart-all-in-one' )
			)
		);
	}
}