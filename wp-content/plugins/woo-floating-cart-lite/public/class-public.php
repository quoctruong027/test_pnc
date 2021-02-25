<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart/public
 * @author     XplodedThemes
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
class XT_Woo_Floating_Cart_Public
{
    /**
     * Core class reference.
     *
     * @since    1.0.0
     * @access   private
     * @var      XT_Woo_Floating_Cart    $core
     */
    private  $core ;
    /**
     * Var that holds the menu class object.
     *
     * @since    1.0.0
     * @access   public
     * @var      XT_Woo_Floating_Cart_Theme_Fixes  $theme_fixes   Theme Fixes
     */
    public  $theme_fixes ;
    /**
     * Var that holds the menu class object.
     *
     * @since    1.0.0
     * @access   public
     * @var      XT_Woo_Floating_Cart_Menu  $menu   Menu
     */
    public  $menu ;
    /**
     * Var that holds custom payment buttons
     *
     * @since    2.1.2
     * @access   public
     * @var      array  $payment_buttons   buttons
     */
    public  $payment_buttons = array() ;
    /**
     * Var that holds custom payment buttons enabled count
     *
     * @since    2.1.2
     * @access   public
     * @var      int  $payment_buttons_enabled   count
     */
    public  $payment_buttons_enabled = 0 ;
    /**
     * Initialize the class and set its properties.
     *
     * @param XT_Woo_Floating_Cart $core Plugin core class
     *
     * @since    1.0.0
     */
    public function __construct( &$core )
    {
        $this->core = $core;
        $this->init_ajax();
        $this->core->plugin_loader()->add_action(
            'init',
            $this,
            'init',
            10
        );
    }
    
    public function init()
    {
        $this->core->plugin_loader()->add_action( 'wp_enqueue_scripts', $this, 'enqueue_styles' );
        $this->core->plugin_loader()->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );
        $this->core->plugin_loader()->add_filter(
            'woocommerce_cart_item_price',
            $this,
            'change_cart_price_display',
            30,
            3
        );
        $this->core->plugin_loader()->add_action(
            'template_redirect',
            $this,
            'define_woocommerce_constants',
            10
        );
        $this->core->plugin_loader()->add_action( 'wp_footer', $this, 'render' );
        $this->init_frontend_dependencies();
    }
    
    // Init  Ajax
    public function init_ajax()
    {
        new XT_Woo_Floating_Cart_Ajax( $this->core );
    }
    
    // Init Frontend Dependencies
    public function init_frontend_dependencies()
    {
        $this->theme_fixes = new XT_Woo_Floating_Cart_Theme_Fixes( $this->core );
    }
    
    public function enabled()
    {
        if ( $this->should_not_load() || $this->is_cart_page() || $this->is_checkout_page() ) {
            return false;
        }
        $exclude_pages = $this->core->customizer()->get_option( 'hidden_on_pages', array() );
        if ( !empty($exclude_pages) ) {
            foreach ( $exclude_pages as $page ) {
                if ( !empty($page) && is_page( $page ) ) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public function menu_item_enabled()
    {
        return $this->core->customizer()->get_option_bool( 'cart_menu_enabled', false );
    }
    
    public function shortcode_enabled()
    {
        return $this->core->customizer()->get_option_bool( 'cart_shortcode_enabled', false );
    }
    
    public function suggested_products_enabled()
    {
        $enabled = $this->core->customizer()->get_option_bool( 'suggested_products_enabled', false );
        $enabled_mobile = $this->core->customizer()->get_option_bool( 'suggested_products_mobile_enabled', false );
        return $enabled || $enabled_mobile && wp_is_mobile();
    }
    
    public function suggested_products_slider_enabled()
    {
        return $this->suggested_products_enabled() && $this->core->customizer()->get_option( 'suggested_products_display_type', 'slider' ) === 'slider';
    }
    
    public function totals_enabled()
    {
        return $this->core->customizer()->get_option_bool( 'enable_totals', false );
    }
    
    public function checkout_form_enabled()
    {
        return $this->core->customizer()->get_option_bool( 'cart_checkout_form', false );
    }
    
    public function coupon_form_enabled()
    {
        return $this->core->customizer()->get_option_bool( 'enable_coupon_form', false );
    }
    
    public function coupon_list_enabled()
    {
        return $this->coupon_form_enabled() && $this->core->customizer()->get_option_bool( 'enable_coupon_list', false );
    }
    
    public function is_checkout_page()
    {
        $checkout_page_id = wc_get_page_id( 'checkout' );
        return is_page( $checkout_page_id );
    }
    
    public function is_cart_page()
    {
        $cart_page_id = wc_get_page_id( 'cart' );
        return is_page( $cart_page_id );
    }
    
    public function should_not_load()
    {
        $do_not_load = false;
        // skip if divi or elementor builder
        if ( !empty($_GET['et_fb']) || !empty($_GET['elementor-preview']) ) {
            $do_not_load = true;
        }
        return $do_not_load;
    }
    
    public function define_woocommerce_constants()
    {
        do_action( 'xt_woofc_before_woocommerce_constants' );
        
        if ( $this->enabled() && $this->core->access_manager()->can_use_premium_code__premium_only() ) {
            if ( wp_doing_ajax() && ($this->totals_enabled() || $this->checkout_form_enabled()) ) {
                $this->define_cart_constant();
            }
            if ( $this->checkout_form_enabled() ) {
                add_filter( 'woocommerce_is_checkout', '__return_true' );
            }
        }
    
    }
    
    public function define_cart_constant()
    {
        wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
    }
    
    public function define_checkout_constant()
    {
        wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );
    }
    
    function total_savings()
    {
        $discount_total = WC()->cart->get_cart_discount_total();
        if ( $discount_total > 0 ) {
            echo  '
			<tr class="xt_woofc-total-savings">
			    <th>' . esc_html__( 'Total savings', 'woo-floating-cart' ) . '</th>
			    <td data-title=" ' . esc_html__( 'Total savings', 'woo-floating-cart' ) . ' ">
					<strong>' . wc_price( $discount_total ) . '</strong>
			    </td>
		    </tr>' ;
        }
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in XT_Woo_Floating_Cart_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The XT_Woo_Floating_Cart_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        
        if ( $this->menu_item_enabled() || $this->shortcode_enabled() ) {
            wp_enqueue_style(
                'xt-woo-custom',
                $this->core->plugin_url( 'public/assets/css', 'woo-custom.css' ),
                array(),
                $this->core->plugin_version(),
                'all'
            );
            wp_enqueue_style( 'xt-icons' );
        }
        
        if ( !$this->enabled() ) {
            return;
        }
        wp_register_style(
            $this->core->plugin_slug(),
            $this->core->plugin_url( 'public/assets/css', 'frontend.css' ),
            array(),
            filemtime( $this->core->plugin_path( 'public/assets/css', 'frontend.css' ) ),
            'all'
        );
        wp_enqueue_style( $this->core->plugin_slug() );
        
        if ( $this->core->access_manager()->can_use_premium_code__premium_only() && is_rtl() ) {
            wp_register_style(
                $this->core->plugin_slug( 'rtl' ),
                $this->core->plugin_url( 'public/assets/css', 'rtl.css' ),
                array( $this->core->plugin_slug() ),
                filemtime( $this->core->plugin_path( 'public/assets/css', 'rtl.css' ) ),
                'all'
            );
            wp_enqueue_style( $this->core->plugin_slug( 'rtl' ) );
        }
    
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in XT_Woo_Floating_Cart_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The XT_Woo_Floating_Cart_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ( !$this->enabled() ) {
            return;
        }
        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_style( 'xt-icons' );
        
        if ( $this->core->access_manager()->can_use_premium_code__premium_only() && $this->core->customizer()->get_option_bool( 'flytocart_animation', false ) ) {
            wp_enqueue_script(
                'xt-gsap',
                $this->core->plugin_url( 'public' ) . 'assets/vendors/gsap.min.js',
                array( 'jquery' ),
                $this->core->plugin_version(),
                false
            );
            wp_add_inline_script( 'xt-gsap', '
				window.xt_gsap = window.gsap;
			' );
        }
        
        if ( $this->core->customizer()->get_option_bool( 'active_cart_body_lock_scroll', false ) ) {
            wp_enqueue_script(
                'xt-body-scroll-lock',
                $this->core->plugin_url( 'public' ) . 'assets/vendors/bodyScrollLock' . XTFW_SCRIPT_SUFFIX . '.js',
                array(),
                $this->core->plugin_version(),
                false
            );
        }
        
        if ( $this->suggested_products_slider_enabled() ) {
            wp_enqueue_script(
                'xt-lightslider',
                $this->core->plugin_url( 'public/assets/vendors/lightslider/js', 'lightslider' . XTFW_SCRIPT_SUFFIX . '.js' ),
                array( 'jquery' ),
                $this->core->plugin_version(),
                false
            );
            wp_enqueue_style(
                'xt-lightslider',
                $this->core->plugin_url( 'public/assets/vendors/lightslider/css', 'lightslider.css' ),
                array(),
                $this->core->plugin_version(),
                'all'
            );
        }
        
        if ( !$this->is_cart_page() ) {
            wp_dequeue_script( 'wc-cart' );
        }
        // MAIN SCRIPT
        wp_register_script(
            $this->core->plugin_slug(),
            $this->core->plugin_url( 'public/assets/js', 'frontend' . XTFW_SCRIPT_SUFFIX . '.js' ),
            array(
            'jquery',
            'wc-cart-fragments',
            'xt-jquery-touch',
            'xt-jquery-ajaxqueue',
            'xt-observers-polyfill'
        ),
            filemtime( $this->core->plugin_path( 'public/assets/js', 'frontend' . XTFW_SCRIPT_SUFFIX . '.js' ) ),
            true
        );
        wp_localize_script( $this->core->plugin_slug(), 'XT_WOOFC', $this->get_script_vars() );
        wp_enqueue_script( $this->core->plugin_slug() );
        if ( is_customize_preview() ) {
            wp_add_inline_script( $this->core->plugin_slug(), '

                var disableClickSelectors = [".xt_woofc-remove-coupon"];

				if(XT_WOOFC.cart_menu_enabled === "1" && XT_WOOFC.cart_menu_click_action === "toggle") {
					disableClickSelectors.push(".xt_woofc-menu-link");
				}

				if(XT_WOOFC.cart_shortcode_enabled === "1" && XT_WOOFC.cart_shortcode_click_action === "toggle") {
					disableClickSelectors.push(".xt_woofc-shortcode-link");
				}

                disableClickSelectors = disableClickSelectors.join(",");

                jQuery(document).on("mouseenter", disableClickSelectors, function() {

                    jQuery(this).attr("data-href", jQuery(this).attr("href")).attr("href", "#");

                }).on("mouseleave", disableClickSelectors, function() {

                    jQuery(this).attr("href", jQuery(this).attr("data-href"));
                });
            ' );
        }
    }
    
    /**
     * @return array
     */
    public function get_script_vars()
    {
        return array(
            'customizerConfigId'          => $this->core->customizer()->config_id(),
            'wc_ajax_url'                 => $this->core->wc_ajax()->get_ajax_url(),
            'layouts'                     => $this->core->customizer()->breakpointsJson(),
            'can_use_premium_code'        => $this->core->access_manager()->can_use_premium_code__premium_only(),
            'can_checkout'                => xt_woofc_can_checkout(),
            'body_lock_scroll'            => $this->core->customizer()->get_option_bool( 'active_cart_body_lock_scroll', false ),
            'sp_slider_enabled'           => $this->suggested_products_slider_enabled(),
            'sp_slider_arrow'             => $this->core->customizer()->get_option( 'suggested_products_arrow', 'xt_wooqvicon-arrows-18' ),
            'cart_autoheight'             => xt_woofc_option_bool( 'cart_autoheight_enabled', false ),
            'cart_menu_enabled'           => $this->menu_item_enabled(),
            'cart_menu_click_action'      => xt_woofc_option( 'cart_menu_click_action', 'toggle' ),
            'cart_shortcode_enabled'      => $this->shortcode_enabled(),
            'cart_shortcode_click_action' => xt_woofc_option( 'cart_shortcode_click_action', 'toggle' ),
            'trigger_selectors'           => XT_Framework_Customizer_Helpers::repeater_fields_string_to_array( xt_woofc_option( 'trigger_extra_selectors', array() ) ),
            'lang'                        => array(
            'wait'              => esc_html__( 'Please wait', 'woo-floating-cart' ),
            'loading'           => esc_html__( 'Loading', 'woo-floating-cart' ),
            'min_qty_required'  => esc_html__( 'Min quantity required', 'woo-floating-cart' ),
            'max_stock_reached' => esc_html__( 'Stock limit reached', 'woo-floating-cart' ),
            'restoring'         => esc_html__( 'Restoring product...', 'woo-floating-cart' ),
            'coupons'           => esc_html__( 'Coupons', 'woo-floating-cart' ),
            'title'             => esc_html__( 'Cart', 'woo-floating-cart' ),
        ),
        );
    }
    
    public function do_woocommerce_after_cart_item_name( $cart_item, $cart_item_key )
    {
        // After Cart Item Name Hook
        do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
    }
    
    public function change_cart_price_display( $price, $values, $cart_item_key )
    {
        $slashed_price = $values['data']->get_price_html();
        $is_on_sale = $values['data']->is_on_sale();
        if ( $is_on_sale ) {
            $price = $slashed_price;
        }
        return $price;
    }
    
    public function get_product_image_data( $product )
    {
        $image_id = $product->get_image_id();
        return wp_get_attachment_image_src( $image_id, 'woocommerce_thumbnail', 0 );
    }
    
    public function get_coupons()
    {
        $cache_key = 'xt_woofc_coupons';
        $coupons = wp_cache_get( $cache_key );
        
        if ( false === $coupons ) {
            $showCouponList = $this->coupon_list_enabled();
            if ( !$showCouponList ) {
                return array();
            }
            $couponListType = $this->core->customizer()->get_option( 'coupon_list_type', 'all' );
            $totalCoupons = intval( $this->core->customizer()->get_option( 'coupon_list_total', 20 ) );
            $includes = array();
            
            if ( $couponListType === 'selection' ) {
                $selection = trim( $this->core->customizer()->get_option( 'coupon_list_selection', '' ) );
                if ( !empty($selection) ) {
                    $includes = array_map( 'trim', explode( ',', $selection ) );
                }
            }
            
            $args = array(
                'posts_per_page' => $totalCoupons,
                'include'        => $includes,
                'orderby'        => 'title',
                'order'          => 'asc',
                'post_type'      => 'shop_coupon',
                'post_status'    => 'publish',
            );
            $coupons_post = get_posts( $args );
            if ( empty($coupons_post) ) {
                return array();
            }
            $coupons = array(
                'valid'   => array(),
                'invalid' => array(),
            );
            $hide_for_error_codes = array(
                105,
                //Not exists.
                107,
            );
            $hide_for_error_codes = apply_filters( 'xt_woofc_coupon_hide_invalid_codes', $hide_for_error_codes );
            $applied_coupons = WC()->cart->get_applied_coupons();
            foreach ( $coupons_post as $coupon_post ) {
                $coupon = new WC_Coupon( $coupon_post->ID );
                $discounts = new WC_Discounts( WC()->cart );
                $valid = $discounts->is_coupon_valid( $coupon );
                $code = $coupon->get_code();
                if ( in_array( $code, $applied_coupons ) ) {
                    continue;
                }
                $off_amount = $coupon->get_amount();
                $off_value = ( 'percent' === $coupon->get_discount_type() ? $off_amount . '%' : wc_price( $off_amount ) );
                $data = array(
                    'code'      => $code,
                    'coupon'    => $coupon,
                    'notice'    => '',
                    'off_value' => $off_value,
                );
                
                if ( is_wp_error( $valid ) ) {
                    if ( $couponListType !== 'all' ) {
                        continue;
                    }
                    $error_code = $valid->get_error_code();
                    if ( in_array( $error_code, $hide_for_error_codes ) ) {
                        continue;
                    }
                    $data['notice'] = $valid->get_error_message();
                }
                
                $coupons[( is_wp_error( $valid ) ? 'invalid' : 'valid' )][] = $data;
            }
            wp_cache_set( $cache_key, $coupons );
        }
        
        $coupons = apply_filters( 'xt_woofc_coupons_list', $coupons );
        return $coupons;
    }
    
    public function render()
    {
        if ( !$this->enabled() ) {
            return false;
        }
        WC()->cart->calculate_totals();
        ?>
        <div id="xt_woofc">
            <div class="<?php 
        echo  esc_attr( xt_woofc_class() ) ;
        ?>" <?php 
        xt_woofc_attributes();
        ?>>

                <?php 
        do_action( 'xt_woofc_before_cart' );
        ?>

                <?php 
        $this->core->get_template( 'parts/cart' );
        ?>

                <?php 
        do_action( 'xt_woofc_after_cart' );
        ?>

            </div>
        </div>
        <?php 
    }

}