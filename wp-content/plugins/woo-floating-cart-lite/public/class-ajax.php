<?php

/**
 * The Ajax functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart_Ajax/public
 * @author     XplodedThemes
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
class XT_Woo_Floating_Cart_Ajax
{
    /**
     * Var that holds the cart notice
     *
     * @since    1.0.0
     * @access   public
     * @var      string  $notice   Notice
     */
    public  $notice = '' ;
    /**
     * Core class reference.
     *
     * @since    1.0.0
     * @access   private
     * @var      XT_Woo_Floating_Cart    $core    Core Class
     */
    public function __construct( &$core )
    {
        $this->core = $core;
        // Add WC Ajax Events
        $this->core->plugin_loader()->add_filter(
            $this->core->plugin_prefix( 'wc_ajax_add_events' ),
            $this,
            'ajax_add_events',
            1
        );
        // Remove WC Ajax Events
        $this->core->plugin_loader()->add_filter(
            $this->core->plugin_prefix( 'wc_ajax_remove_events' ),
            $this,
            'ajax_remove_events',
            1
        );
        // Set Fragments
        $this->core->plugin_loader()->add_filter(
            'woocommerce_add_to_cart_fragments',
            $this,
            'cart_fragments',
            1
        );
        $this->core->plugin_loader()->add_filter(
            'woocommerce_update_order_review_fragments',
            $this,
            'cart_fragments',
            1
        );
        // Remove / Restore hooks
        $this->core->plugin_loader()->add_filter(
            'woocommerce_remove_cart_item',
            $this,
            'remove_cart_item',
            10,
            2
        );
        $this->core->plugin_loader()->add_filter(
            'woocommerce_cart_item_restored',
            $this,
            'cart_item_restored',
            10,
            2
        );
        // Added to cart action
        $this->core->plugin_loader()->add_action(
            'woocommerce_add_to_cart',
            $this,
            'added_to_cart',
            10,
            0
        );
    }
    
    /**
     * Add ajax events
     */
    public function ajax_add_events( $ajax_events )
    {
        $ajax_events[] = array(
            'function' => 'xt_woofc_update',
            'callback' => array( $this, 'update_qty' ),
            'nopriv'   => true,
        );
        $ajax_events[] = array(
            'function' => 'xt_woofc_remove',
            'callback' => array( $this, 'remove_item' ),
            'nopriv'   => true,
        );
        $ajax_events[] = array(
            'function' => 'xt_woofc_restore',
            'callback' => array( $this, 'restore_item' ),
            'nopriv'   => true,
        );
        return $ajax_events;
    }
    
    /**
     * Remove ajax events
     */
    public function ajax_remove_events( $ajax_events )
    {
        if ( xt_woo_floating_cart()->access_manager()->can_use_premium_code__premium_only() && !xt_woo_floating_cart()->frontend()->is_checkout_page() ) {
            $ajax_events[] = array(
                'function' => 'update_order_review',
                'callback' => array( WC_AJAX::class, 'update_order_review' ),
                'nopriv'   => true,
            );
        }
        return $ajax_events;
    }
    
    public function set_notice( $notice, $type = 'success' )
    {
        $this->notice = '<span class="xt_woofc-notice xt_woofc-notice-' . esc_attr( $type ) . '" data-type="' . esc_attr( $type ) . '">' . $notice . '</span>';
    }
    
    public function get_notice()
    {
        if ( empty($this->notice) ) {
            return null;
        }
        $notice = $this->notice;
        $notice = apply_filters( 'xt_woofc_notice_html', $notice );
        $this->notice = '';
        return $notice;
    }
    
    public function cart_fragments( $fragments )
    {
        /* @var $frontend XT_Woo_Floating_Cart_Public */
        $frontend = $this->core->frontend();
        $frontend->define_cart_constant();
        WC()->cart->calculate_totals();
        $type = ( !empty($_POST['type']) ? filter_var( $_POST['type'], FILTER_SANITIZE_STRING ) : null );
        $add_to_cart = !empty($_GET['wc-ajax']) && $_GET['wc-ajax'] === 'add_to_cart';
        $single_add_to_cart = !empty($_GET['wc-ajax']) && $_GET['wc-ajax'] === 'xt_atc_single';
        $update_order_review = !empty($_GET['wc-ajax']) && $_GET['wc-ajax'] === 'update_order_review';
        $add_to_cart_module = $this->core->modules()->get( 'add-to-cart' );
        if ( $single_add_to_cart && $add_to_cart_module->customizer()->get_option_bool( 'single_refresh_fragments', true ) ) {
            return $fragments;
        }
        $show_notices = !$update_order_review && !in_array( $type, array( 'totals', 'refresh' ) );
        
        if ( $show_notices ) {
            $notice = $this->get_notice();
            if ( !empty($notice) ) {
                $fragments['.xt_woofc-notice'] = $notice;
            }
        }
        
        $total = xt_woofc_checkout_total();
        $count = WC()->cart->get_cart_contents_count();
        $previous_count = WC()->session->get( 'xt_woofc_previous_count', 0 );
        $update_count_class = ( $previous_count !== $count ? ' xt_woofc-update-count' : '' );
        WC()->session->set( 'xt_woofc_previous_count', $count );
        $fragments['.xt_woofc-checkout span.amount'] = '<span class="amount">' . $total . '</span>';
        $fragments['.xt_woofc-count'] = '<ul class="xt_woofc-count' . $update_count_class . '"><li>' . $previous_count . '</li><li>' . $count . '</li></ul>';
        $fragments['.xt_woofc-spinner-wrap'] = xt_woofc_spinner_html( true );
        
        if ( in_array( $type, array(
            'totals',
            'update',
            'remove',
            'restore'
        ) ) ) {
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $product = xt_woofc_item_product( $cart_item, $cart_item_key );
                
                if ( $product && $product->exists() && $cart_item['quantity'] > 0 && apply_filters(
                    'woocommerce_widget_cart_item_visible',
                    true,
                    $cart_item,
                    $cart_item_key
                ) ) {
                    $vars = array(
                        'cart_item_key' => $cart_item_key,
                        'cart_item'     => $cart_item,
                        'product'       => $product,
                    );
                    $fragments['li[data-key="' . $cart_item_key . '"] .xt_woofc-price'] = xt_woo_floating_cart()->get_template( 'parts/cart/list/product/price', $vars, true );
                    $fragments['li[data-key="' . $cart_item_key . '"] .xt_woofc-quantity'] = xt_woo_floating_cart()->get_template( 'parts/cart/list/product/quantity', $vars, true );
                }
            
            }
        } else {
            
            if ( !$update_order_review ) {
                $list = $this->core->get_template( 'parts/cart/list', array(), true );
                $fragments['.xt_woofc-list-wrap'] = $list;
            }
        
        }
        
        return $fragments;
    }
    
    /**
     * Update item qty
     */
    public function update_qty()
    {
        $this->core->frontend()->define_cart_constant();
        $cart_item_key = ( !empty($_POST['cart_item_key']) ? filter_var( $_POST['cart_item_key'], FILTER_SANITIZE_STRING ) : null );
        
        if ( !empty($cart_item_key) ) {
            $cart_item_qty = intval( $_POST['cart_item_qty'] );
            WC()->cart->set_quantity( $cart_item_key, $cart_item_qty );
        }
        
        WC_Ajax::get_refreshed_fragments();
    }
    
    /**
     * Remove item
     */
    public function remove_item()
    {
        $this->core->frontend()->define_cart_constant();
        $cart_item_key = ( !empty($_POST['cart_item_key']) ? filter_var( $_POST['cart_item_key'], FILTER_SANITIZE_STRING ) : null );
        
        if ( !empty($cart_item_key) ) {
            WC()->cart->remove_cart_item( $cart_item_key );
            $this->set_notice( sprintf( esc_html__( 'Item Removed. %s', 'woo-floating-cart' ), '<a class="xt_woofc-undo" href="#0">' . esc_html__( 'Undo', 'woo-floating-cart' ) ) . '</a>' );
        }
        
        WC_Ajax::get_refreshed_fragments();
    }
    
    /**
     * Restore last removed item
     */
    public function restore_item()
    {
        $this->core->frontend()->define_cart_constant();
        $cart_item_key = ( !empty($_POST['cart_item_key']) ? filter_var( $_POST['cart_item_key'], FILTER_SANITIZE_STRING ) : null );
        
        if ( !empty($cart_item_key) ) {
            WC()->cart->restore_cart_item( $cart_item_key );
            $this->set_notice( esc_html__( 'Item restored successfully!', 'woo-floating-cart' ) );
        }
        
        WC_Ajax::get_refreshed_fragments();
    }
    
    /**
     * AJAX apply coupon on checkout page.
     */
    public function apply_coupon()
    {
        $this->core->frontend()->define_cart_constant();
        
        if ( !empty($_POST['coupon_code']) ) {
            $coupon_code = wc_format_coupon_code( wp_unslash( $_POST['coupon_code'] ) );
            
            if ( !WC()->cart->has_discount( $coupon_code ) ) {
                
                if ( WC()->cart->apply_coupon( $coupon_code ) ) {
                    $this->set_notice( esc_html__( 'Coupon applied successfully!', 'woo-floating-cart' ) );
                } else {
                    $coupon = new WC_Coupon( $coupon_code );
                    $discounts = new WC_Discounts( WC()->cart );
                    $valid = $discounts->is_coupon_valid( $coupon );
                    if ( is_wp_error( $valid ) ) {
                        WC()->session->set( 'xt_woofc_coupon_error', $valid->get_error_message() );
                    }
                    $this->set_notice( esc_html__( 'Coupon is invalid!', 'woo-floating-cart' ), 'error' );
                }
            
            } else {
                $this->set_notice( esc_html__( 'Coupon already applied!', 'woo-floating-cart' ), 'error' );
            }
        
        } else {
            $this->set_notice( esc_html__( 'Please enter a coupon!', 'woo-floating-cart' ), 'error' );
        }
        
        wc_clear_notices();
        WC_Ajax::get_refreshed_fragments();
    }
    
    /**
     * AJAX remove coupon on cart and checkout page.
     */
    public function remove_coupon()
    {
        $this->core->frontend()->define_cart_constant();
        $coupon = ( isset( $_POST['coupon'] ) ? wc_format_coupon_code( wp_unslash( $_POST['coupon'] ) ) : false );
        
        if ( empty($coupon) ) {
            $this->set_notice( esc_html__( 'Failed removing coupon!', 'woo-floating-cart' ), 'error' );
        } else {
            WC()->cart->remove_coupon( $coupon );
            $this->set_notice( esc_html__( 'Coupon has been removed!', 'woo-floating-cart' ) );
        }
        
        WC_Ajax::get_refreshed_fragments();
    }
    
    /**
     * AJAX update shipping method on cart page.
     * Override native function because the nonce check is failing if caching plugin enabled
     */
    public function update_shipping_method()
    {
        $this->core->frontend()->define_cart_constant();
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        $posted_shipping_methods = ( isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array() );
        if ( is_array( $posted_shipping_methods ) ) {
            foreach ( $posted_shipping_methods as $i => $value ) {
                $chosen_shipping_methods[$i] = $value;
            }
        }
        WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
        $this->set_notice( esc_html__( 'Shipping info updated', 'woo-floating-cart' ) );
        WC_Ajax::get_refreshed_fragments();
    }
    
    /**
     * AJAX update order review on checkout.
     */
    public function update_order_review()
    {
        $this->core->frontend()->define_checkout_constant();
        do_action( 'woocommerce_checkout_update_order_review', ( isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : '' ) );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        $posted_shipping_methods = ( isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array() );
        if ( is_array( $posted_shipping_methods ) ) {
            foreach ( $posted_shipping_methods as $i => $value ) {
                $chosen_shipping_methods[$i] = $value;
            }
        }
        WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
        WC()->session->set( 'chosen_payment_method', ( empty($_POST['payment_method']) ? '' : wc_clean( wp_unslash( $_POST['payment_method'] ) ) ) );
        WC()->customer->set_props( array(
            'billing_country'   => ( isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null ),
            'billing_state'     => ( isset( $_POST['state'] ) ? wc_clean( wp_unslash( $_POST['state'] ) ) : null ),
            'billing_postcode'  => ( isset( $_POST['postcode'] ) ? wc_clean( wp_unslash( $_POST['postcode'] ) ) : null ),
            'billing_city'      => ( isset( $_POST['city'] ) ? wc_clean( wp_unslash( $_POST['city'] ) ) : null ),
            'billing_address_1' => ( isset( $_POST['address'] ) ? wc_clean( wp_unslash( $_POST['address'] ) ) : null ),
            'billing_address_2' => ( isset( $_POST['address_2'] ) ? wc_clean( wp_unslash( $_POST['address_2'] ) ) : null ),
        ) );
        
        if ( wc_ship_to_billing_address_only() ) {
            WC()->customer->set_props( array(
                'shipping_country'   => ( isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null ),
                'shipping_state'     => ( isset( $_POST['state'] ) ? wc_clean( wp_unslash( $_POST['state'] ) ) : null ),
                'shipping_postcode'  => ( isset( $_POST['postcode'] ) ? wc_clean( wp_unslash( $_POST['postcode'] ) ) : null ),
                'shipping_city'      => ( isset( $_POST['city'] ) ? wc_clean( wp_unslash( $_POST['city'] ) ) : null ),
                'shipping_address_1' => ( isset( $_POST['address'] ) ? wc_clean( wp_unslash( $_POST['address'] ) ) : null ),
                'shipping_address_2' => ( isset( $_POST['address_2'] ) ? wc_clean( wp_unslash( $_POST['address_2'] ) ) : null ),
            ) );
        } else {
            WC()->customer->set_props( array(
                'shipping_country'   => ( isset( $_POST['s_country'] ) ? wc_clean( wp_unslash( $_POST['s_country'] ) ) : null ),
                'shipping_state'     => ( isset( $_POST['s_state'] ) ? wc_clean( wp_unslash( $_POST['s_state'] ) ) : null ),
                'shipping_postcode'  => ( isset( $_POST['s_postcode'] ) ? wc_clean( wp_unslash( $_POST['s_postcode'] ) ) : null ),
                'shipping_city'      => ( isset( $_POST['s_city'] ) ? wc_clean( wp_unslash( $_POST['s_city'] ) ) : null ),
                'shipping_address_1' => ( isset( $_POST['s_address'] ) ? wc_clean( wp_unslash( $_POST['s_address'] ) ) : null ),
                'shipping_address_2' => ( isset( $_POST['s_address_2'] ) ? wc_clean( wp_unslash( $_POST['s_address_2'] ) ) : null ),
            ) );
        }
        
        
        if ( isset( $_POST['has_full_address'] ) && wc_string_to_bool( wc_clean( wp_unslash( $_POST['has_full_address'] ) ) ) ) {
            WC()->customer->set_calculated_shipping( true );
        } else {
            WC()->customer->set_calculated_shipping( false );
        }
        
        WC()->customer->save();
        // Calculate shipping before totals. This will ensure any shipping methods that affect things like taxes are chosen prior to final totals being calculated. Ref: #22708.
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();
        // Get order review fragment.
        ob_start();
        woocommerce_order_review();
        $woocommerce_order_review = ob_get_clean();
        // Get checkout payment fragment.
        ob_start();
        woocommerce_checkout_payment();
        $woocommerce_checkout_payment = ob_get_clean();
        // Get messages if reload checkout is not true.
        $reload_checkout = ( isset( WC()->session->reload_checkout ) ? true : false );
        
        if ( !$reload_checkout ) {
            $messages = wc_print_notices( true );
        } else {
            $messages = '';
        }
        
        unset( WC()->session->refresh_totals, WC()->session->reload_checkout );
        wp_send_json( array(
            'result'    => ( empty($messages) ? 'success' : 'failure' ),
            'messages'  => $messages,
            'reload'    => ( $reload_checkout ? 'true' : 'false' ),
            'fragments' => apply_filters( 'woocommerce_update_order_review_fragments', array(
            '.woocommerce-checkout-review-order-table' => $woocommerce_order_review,
            '.woocommerce-checkout-payment'            => $woocommerce_checkout_payment,
        ) ),
        ) );
    }
    
    public function remove_cart_item( $cart_item_key, $cart )
    {
        $position = array_search( $cart_item_key, array_keys( $cart->cart_contents ) );
        WC()->session->set( 'xt_woofc_removed_position', $position );
    }
    
    public function cart_item_restored( $cart_item_key, $cart )
    {
        $bundled_product = function_exists( 'wc_pb_is_bundled_cart_item' );
        
        if ( !$bundled_product ) {
            $position = WC()->session->get( 'xt_woofc_removed_position' );
            $restored_item = $cart->cart_contents[$cart_item_key];
            array_splice(
                $cart->cart_contents,
                $position,
                0,
                array( $restored_item )
            );
            $cart->cart_contents = $this->replace_array_key( $cart->cart_contents, "0", $cart_item_key );
        }
        
        WC()->session->__unset( 'xt_woofc_removed_position' );
    }
    
    public function added_to_cart()
    {
        $this->set_notice( esc_html__( 'Item added to cart.', 'woo-floating-cart' ) );
    }
    
    public function replace_array_key( $arr, $oldkey, $newkey )
    {
        
        if ( array_key_exists( $oldkey, $arr ) ) {
            $keys = array_keys( $arr );
            $keys[array_search( $oldkey, $keys )] = $newkey;
            return array_combine( $keys, $arr );
        }
        
        return $arr;
    }

}