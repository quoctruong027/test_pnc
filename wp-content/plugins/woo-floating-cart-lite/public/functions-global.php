<?php

/**
 * @return string
 */
function xt_woofc_class()
{
    $classes = array( 'xt_woofc', 'woocommerce' );
    $trigger_hidden = xt_woofc_option_bool( 'trigger_hidden', false );
    $animation_type = xt_woofc_option( 'animation_type', 'morph' );
    $dimensions_unit = xt_woofc_option( 'cart_dimensions_unit', 'pixels' );
    $position = xt_woofc_option( 'position', 'bottom-right' );
    $tablet_position = xt_woofc_option( 'position_tablet', 'bottom-right' );
    $mobile_position = xt_woofc_option( 'position_mobile', 'bottom-right' );
    $counter_position = xt_woofc_option( 'counter_position', 'top-left' );
    $counter_tablet_position = xt_woofc_option( 'counter_position_tablet', 'top-left' );
    $counter_mobile_position = xt_woofc_option( 'counter_position_mobile', 'top-left' );
    $visibility = xt_woofc_option( 'visibility', 'show-on-all' );
    $keep_visible_on_empty = xt_woofc_option_bool( 'visible_on_empty', false );
    $hide_thumbs = xt_woofc_option( 'cart_product_hide_thumb', 'show-thumbs' );
    $squared_thumb = xt_woofc_option_bool( 'cart_product_squared_thumb', true );
    $enable_coupon_form = xt_woofc_option_bool( 'enable_coupon_form', false );
    $enable_coupon_list = $enable_coupon_form && xt_woofc_option_bool( 'enable_coupon_list', false );
    $enable_totals = xt_woofc_option_bool( 'enable_totals', false );
    $header_close_icon_enabled = xt_woofc_option_bool( 'cart_header_close_enabled', false );
    $actions_icons_enabled = xt_woofc_option_bool( 'cart_product_delete_icon_enabled', false );
    if ( $trigger_hidden ) {
        $classes[] = 'xt_woofc-hide-trigger';
    }
    if ( !empty($animation_type) ) {
        $classes[] = 'xt_woofc-animation-' . $animation_type;
    }
    if ( !empty($dimensions_unit) ) {
        $classes[] = 'xt_woofc-dimensions-' . $dimensions_unit;
    }
    if ( !empty($position) ) {
        $classes[] = 'xt_woofc-pos-' . $position;
    }
    if ( !empty($tablet_position) ) {
        $classes[] = 'xt_woofc-tablet-pos-' . $tablet_position;
    }
    if ( !empty($mobile_position) ) {
        $classes[] = 'xt_woofc-mobile-pos-' . $mobile_position;
    }
    if ( !empty($counter_position) ) {
        $classes[] = 'xt_woofc-counter-pos-' . $counter_position;
    }
    if ( !empty($counter_tablet_position) ) {
        $classes[] = 'xt_woofc-counter-tablet-pos-' . $counter_tablet_position;
    }
    if ( !empty($counter_mobile_position) ) {
        $classes[] = 'xt_woofc-counter-mobile-pos-' . $counter_mobile_position;
    }
    if ( !empty($visibility) ) {
        $classes[] = 'xt_woofc-' . $visibility;
    }
    if ( $keep_visible_on_empty ) {
        $classes[] = 'xt_woofc-force-visible';
    }
    if ( $hide_thumbs ) {
        $classes[] = 'xt_woofc-' . $hide_thumbs;
    }
    if ( $squared_thumb ) {
        $classes[] = 'xt_woofc-squared-thumbnail';
    }
    if ( $enable_coupon_form ) {
        $classes[] = 'xt_woofc-enable-coupon';
    }
    if ( $enable_coupon_list ) {
        $classes[] = 'xt_woofc-enable-coupon-list';
    }
    if ( $enable_totals ) {
        $classes[] = 'xt_woofc-enable-totals';
    }
    if ( $header_close_icon_enabled ) {
        $classes[] = 'xt_woofc-header-close-enabled';
    }
    if ( $actions_icons_enabled ) {
        $classes[] = 'xt_woofc-icon-actions';
    }
    if ( WC()->cart->is_empty() ) {
        $classes[] = 'xt_woofc-empty';
    }
    $classes = apply_filters( 'xt_woofc_container_class', $classes );
    return implode( ' ', $classes );
}

/**
 *
 */
function xt_woofc_attributes()
{
    $attributes = array(
        'data-ajax-init'        => xt_woofc_option_bool( 'ajax_init', false ),
        'data-animation'        => xt_woofc_option( 'animation_type', 'morph' ),
        'data-express-checkout' => xt_woofc_option_bool( 'cart_checkout_form', false ),
        'data-position'         => xt_woofc_option( 'position', 'bottom-right' ),
        'data-tablet-position'  => xt_woofc_option( 'position_tablet', 'bottom-right' ),
        'data-mobile-position'  => xt_woofc_option( 'position_mobile', 'bottom-right' ),
        'data-trigger-event'    => xt_woofc_option( 'trigger_event_type', 'vclick' ),
        'data-hoverdelay'       => xt_woofc_option( 'trigger_hover_delay', 0 ),
        'data-flytocart'        => xt_woofc_option_bool( 'flytocart_animation', false ),
        'data-flyduration'      => xt_woofc_option( 'flytocart_animation_duration', '650' ),
        'data-shaketrigger'     => xt_woofc_option( 'shake_trigger', 'vertical' ),
        'data-opencart-onadd'   => xt_woofc_option_bool( 'open_cart_on_product_add', false ),
        'data-loadingtimeout'   => xt_woofc_option( 'loading_timeout', 100 ),
    );
    $attributes = apply_filters( 'xt_woofc_container_attributes', $attributes );
    $data_string = '';
    foreach ( $attributes as $key => $value ) {
        $data_string .= ' ' . $key . '="' . esc_attr( $value ) . '"';
    }
    echo  $data_string ;
}

/**
 * @return string
 */
function xt_woofc_trigger_cart_icon_class()
{
    $classes = array( 'xt_woofc-trigger-cart-icon' );
    $icon_type = xt_woofc_option( 'trigger_icon_type', 'image' );
    
    if ( $icon_type == 'font' ) {
        $icon = xt_woofc_option( 'cart_trigger_icon' );
        if ( !empty($icon) ) {
            $classes[] = $icon;
        }
    }
    
    $classes = apply_filters( 'xt_woofc_trigger_cart_icon_class', $classes );
    return implode( ' ', $classes );
}

/**
 * @return string
 */
function xt_woofc_trigger_close_icon_class()
{
    $classes = array( 'xt_woofc-trigger-close-icon' );
    $icon_type = xt_woofc_option( 'trigger_icon_type', 'image' );
    
    if ( $icon_type == 'font' ) {
        $icon = xt_woofc_option( 'cart_trigger_close_icon' );
        if ( !empty($icon) ) {
            $classes[] = $icon;
        }
    }
    
    $classes = apply_filters( 'xt_woofc_trigger_close_icon_class', $classes );
    return implode( ' ', $classes );
}

/**
 * @return string
 */
function xt_woofc_header_close_icon_class()
{
    $classes = array( 'xt_woofc-header-close' );
    $icon = xt_woofc_option( 'cart_header_close_icon' );
    if ( !empty($icon) ) {
        $classes[] = $icon;
    }
    $classes = apply_filters( 'xt_woofc_header_close_icon_class', $classes );
    return implode( ' ', $classes );
}

/**
 * @return string
 */
function xt_woofc_product_delete_icon_class()
{
    $classes = array( 'xt_woofc-delete-icon' );
    $icon = xt_woofc_option( 'cart_product_delete_icon' );
    if ( !empty($icon) ) {
        $classes[] = $icon;
    }
    $classes = apply_filters( 'xt_woofc_product_delete_icon_class', $classes );
    return implode( ' ', $classes );
}

/**
 * @return mixed
 */
function xt_woofc_get_spinner()
{
    
    if ( isset( $_POST['customized'] ) && is_object( $_POST['customized'] ) ) {
        $customized = $_POST['customized'];
        if ( !empty($customized->xt_woofc["loading_spinner"]) ) {
            return $customized->xt_woofc["loading_spinner"];
        }
    }
    
    return xt_woofc_option( 'loading_spinner', '7-three-bounce' );
}

/**
 * @param false $return
 * @param bool $wrapSpinner
 *
 * @return string
 */
function xt_woofc_spinner_html( $return = false, $wrapSpinner = true )
{
    $spinner_class = 'xt_woofc-spinner';
    $spinner_type = xt_woofc_get_spinner();
    if ( empty($spinner_type) ) {
        if ( $return ) {
            return "";
        }
    }
    $spinner = '';
    switch ( $spinner_type ) {
        case '1-rotating-plane':
            $spinner = '<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-rotating-plane"></div>';
            break;
        case '2-double-bounce':
            $spinner = '
			<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-double-bounce">
		        <div class="xt_woofc-spinner-child xt_woofc-spinner-double-bounce1"></div>
		        <div class="xt_woofc-spinner-child xt_woofc-spinner-double-bounce2"></div>
		    </div>';
            break;
        case '3-wave':
            $spinner = '
			<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-wave">
		        <div class="xt_woofc-spinner-rect xt_woofc-spinner-rect1"></div>
		        <div class="xt_woofc-spinner-rect xt_woofc-spinner-rect2"></div>
		        <div class="xt_woofc-spinner-rect xt_woofc-spinner-rect3"></div>
		        <div class="xt_woofc-spinner-rect xt_woofc-spinner-rect4"></div>
		        <div class="xt_woofc-spinner-rect xt_woofc-spinner-rect5"></div>
		    </div>';
            break;
        case '4-wandering-cubes':
            $spinner = '
			<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-wandering-cubes">
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube1"></div>
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube2"></div>
		    </div>';
            break;
        case '5-pulse':
            $spinner = '<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-spinner-pulse"></div>';
            break;
        case '6-chasing-dots':
            $spinner = '
			<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-chasing-dots">
		        <div class="xt_woofc-spinner-child xt_woofc-spinner-dot1"></div>
		        <div class="xt_woofc-spinner-child xt_woofc-spinner-dot2"></div>
		    </div>';
            break;
        case '7-three-bounce':
            $spinner = '
			<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-three-bounce">
		        <div class="xt_woofc-spinner-child xt_woofc-spinner-bounce1"></div>
		        <div class="xt_woofc-spinner-child xt_woofc-spinner-bounce2"></div>
		        <div class="xt_woofc-spinner-child xt_woofc-spinner-bounce3"></div>
		    </div>';
            break;
        case '8-circle':
            $spinner = '
			<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-circle">
		        <div class="xt_woofc-spinner-circle1 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle2 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle3 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle4 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle5 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle6 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle7 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle8 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle9 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle10 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle11 xt_woofc-spinner-child"></div>
		        <div class="xt_woofc-spinner-circle12 xt_woofc-spinner-child"></div>
		    </div>';
            break;
        case '9-cube-grid':
            $spinner = '
			<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-cube-grid">
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube1"></div>
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube2"></div>
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube3"></div>
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube4"></div>
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube5"></div>
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube6"></div>
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube7"></div>
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube8"></div>
		        <div class="xt_woofc-spinner-cube xt_woofc-spinner-cube9"></div>
		    </div>';
            break;
        case '10-fading-circle':
            $spinner = '
			<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-fading-circle">
		        <div class="xt_woofc-spinner-circle1 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle2 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle3 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle4 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle5 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle6 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle7 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle8 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle9 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle10 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle11 xt_woofc-spinner-circle"></div>
		        <div class="xt_woofc-spinner-circle12 xt_woofc-spinner-circle"></div>
		    </div>';
            break;
        case '11-folding-cube':
            $spinner = '
			<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-folding-cube">
		        <div class="xt_woofc-spinner-cube1 xt_woofc-spinner-cube"></div>
		        <div class="xt_woofc-spinner-cube2 xt_woofc-spinner-cube"></div>
		        <div class="xt_woofc-spinner-cube4 xt_woofc-spinner-cube"></div>
		        <div class="xt_woofc-spinner-cube3 xt_woofc-spinner-cube"></div>
		    </div>';
            break;
        case 'loading-text':
            $spinner = '<div class="' . esc_attr( $spinner_class ) . ' xt_woofc-spinner-loading-text">' . esc_html__( 'Loading...', 'woo-floating-cart' ) . '</div>';
            break;
    }
    $spinner = '<div class="xt_woofc-spinner-inner">' . $spinner . '</div>';
    if ( $wrapSpinner ) {
        $spinner = '<div class="xt_woofc-spinner-wrap">' . $spinner . '</div>';
    }
    if ( $return ) {
        return $spinner;
    }
    echo  $spinner ;
}

/**
 * @return bool
 */
function xt_woofc_can_checkout()
{
    return !(!WC()->checkout->is_registration_enabled() && WC()->checkout->is_registration_required() && !is_user_logged_in());
}

/**
 * @return mixed|void
 */
function xt_woofc_checkout_link()
{
    
    if ( xt_woofc_option_bool( 'cart_checkout_form', false ) ) {
        $link = wc_get_checkout_url();
    } else {
        $checkout_link_type = xt_woofc_option( 'cart_checkout_link', 'checkout' );
        
        if ( $checkout_link_type == 'checkout' ) {
            $link = wc_get_checkout_url();
        } else {
            $link = wc_get_cart_url();
        }
    
    }
    
    $link = apply_filters_deprecated(
        'xt_woo_floating_cart_checkout_link',
        array( $link ),
        '1.3.2',
        'xt_woofc_checkout_link'
    );
    return apply_filters( 'xt_woofc_checkout_link', $link );
}

/**
 * @return mixed|void
 */
function xt_woofc_checkout_label()
{
    
    if ( xt_woofc_option_bool( 'cart_checkout_form', false ) ) {
        
        if ( xt_woofc_can_checkout() ) {
            $label = apply_filters( 'woocommerce_order_button_text', esc_html__( 'Place order', 'woo-floating-cart' ) );
        } else {
            $label = esc_html__( 'Checkout', 'woo-floating-cart' );
        }
    
    } else {
        $checkout_link_type = xt_woofc_option( 'cart_checkout_link', 'checkout' );
        
        if ( $checkout_link_type == 'checkout' ) {
            $label = esc_html__( 'Checkout', 'woo-floating-cart' );
        } else {
            $label = esc_html__( 'Cart', 'woo-floating-cart' );
        }
    
    }
    
    $label = apply_filters_deprecated(
        'xt_woofc_lang_footer_checkout',
        array( $label ),
        '1.3.2',
        'xt_woofc_checkout_label'
    );
    return apply_filters( 'xt_woofc_checkout_label', $label );
}

/**
 * @return mixed|void
 */
function xt_woofc_checkout_processing_label()
{
    $label = esc_html__( 'Please Wait...', 'woo-floating-cart' );
    if ( xt_woofc_option_bool( 'cart_checkout_form', false ) && xt_woofc_can_checkout() ) {
        $label = apply_filters( 'woocommerce_order_button_text', esc_html__( 'Placing Order...', 'woo-floating-cart' ) );
    }
    return apply_filters( 'xt_woofc_checkout_processing_label', $label );
}

/**
 * @return string
 */
function xt_woofc_checkout_total()
{
    /* @var $frontend XT_Woo_Floating_Cart_Public */
    $frontend = xt_woo_floating_cart()->frontend();
    
    if ( $frontend->totals_enabled() || $frontend->checkout_form_enabled() || $frontend->coupon_form_enabled() ) {
        return strip_tags( apply_filters( 'xt_woofc_checkout_total', WC()->cart->get_total() ) );
    } else {
        return strip_tags( apply_filters( 'xt_woofc_checkout_subtotal', WC()->cart->get_cart_subtotal() ) );
    }

}

/**
 * @param $cart_item
 * @param $cart_item_key
 *
 * @return WC_Product
 */
function xt_woofc_item_product( &$cart_item, $cart_item_key )
{
    return apply_filters(
        'woocommerce_cart_item_product',
        $cart_item['data'],
        $cart_item,
        $cart_item_key
    );
}

/**
 * @param $product
 * @param $cart_item
 * @param $cart_item_key
 *
 * @return string
 */
function xt_woofc_item_permalink( &$product, &$cart_item, $cart_item_key )
{
    return esc_url( apply_filters(
        'woocommerce_cart_item_permalink',
        ( $product->is_visible() ? $product->get_permalink( $cart_item ) : '' ),
        $cart_item,
        $cart_item_key
    ) );
}

/**
 * @param $product
 * @param $cart_item
 * @param $cart_item_key
 *
 * @return string
 */
function xt_woofc_item_title( &$product, &$cart_item, $cart_item_key )
{
    $product_permalink = apply_filters(
        'woocommerce_cart_item_permalink',
        ( $product->is_visible() && xt_woofc_option_bool( 'cart_product_link_to_single', true ) ? $product->get_permalink( $cart_item ) : '' ),
        $cart_item,
        $cart_item_key
    );
    
    if ( !$product_permalink ) {
        return wp_kses_post( apply_filters(
            'woocommerce_cart_item_name',
            sprintf( '<span class="xt_woofc-product-title-inner">%s</span>', $product->get_title() ),
            $cart_item,
            $cart_item_key
        ) );
    } else {
        return wp_kses_post( apply_filters(
            'woocommerce_cart_item_name',
            sprintf( '<a class="xt_woofc-product-title-inner" href="%s">%s</a>', esc_url( $product_permalink ), $product->get_title() ),
            $cart_item,
            $cart_item_key
        ) );
    }

}

/**
 * @param $product
 * @param $cart_item
 * @param $cart_item_key
 *
 * @return mixed|void
 */
function xt_woofc_item_image( &$product, &$cart_item, $cart_item_key )
{
    return apply_filters(
        'woocommerce_cart_item_thumbnail',
        $product->get_image(),
        $cart_item,
        $cart_item_key
    );
}

/**
 * @param $product
 * @param $cart_item
 * @param $cart_item_key
 *
 * @return mixed|void
 */
function xt_woofc_item_price( &$product, &$cart_item, $cart_item_key )
{
    $display = xt_woofc_option( 'cart_product_price_display', 'subtotal' );
    
    if ( $display === 'subtotal' ) {
        $qty = xt_woofc_item_qty( $cart_item, $cart_item_key );
        return apply_filters(
            'woocommerce_cart_item_subtotal',
            WC()->cart->get_product_subtotal( $product, $qty ),
            $cart_item,
            $cart_item_key
        );
    } else {
        return apply_filters(
            'woocommerce_cart_item_price',
            WC()->cart->get_product_price( $product ),
            $cart_item,
            $cart_item_key
        );
    }

}

/**
 * @param $cart_item
 * @param $cart_item_key
 *
 * @return string
 */
function xt_woofc_item_qty( &$cart_item, $cart_item_key )
{
    return $cart_item['quantity'];
}

/**
 * Output the quantity input.
 *
 * @param  array           $args Args for the input.
 * @param  WC_Product|null $product Product.
 * @param  array           $cart_item_key Cart item key.
 * @param  array           $cart_item Cart item.
 * @param  boolean         $echo Whether to return or echo|string.
 *
 * @return string
 */
function xt_woofc_quantity_input(
    $args = array(),
    $product,
    $cart_item_key,
    $cart_item
)
{
    xt_woo_floating_cart()->frontend()->define_cart_constant();
    $defaults = array(
        'input_id'     => uniqid( 'quantity_' ),
        'input_name'   => 'quantity',
        'input_value'  => '1',
        'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text' ), $product ),
        'max_value'    => apply_filters( 'woocommerce_quantity_input_max', -1, $product ),
        'min_value'    => apply_filters( 'woocommerce_quantity_input_min', 0, $product ),
        'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
        'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', ( has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ) ),
        'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', ( has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ) ),
        'product_name' => ( $product ? $product->get_title() : '' ),
        'placeholder'  => apply_filters( 'woocommerce_quantity_input_placeholder', '', $product ),
    );
    $args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), $product );
    if ( $product->is_sold_individually() ) {
        return sprintf( '<input type="hidden" name="%s" value="1" />', $args['input_name'] );
    }
    // Apply sanity to min/max args - min cannot be lower than 0.
    $args['min_value'] = max( $args['min_value'], 0 );
    $args['max_value'] = ( 0 < $args['max_value'] ? $args['max_value'] : '' );
    // Max cannot be lower than min if defined.
    if ( '' !== $args['max_value'] && $args['max_value'] < $args['min_value'] ) {
        $args['max_value'] = $args['min_value'];
    }
    return xt_woo_floating_cart_template( 'parts/cart/list/product/quantity-input', $args, true );
}

/**
 * @param $car_item_key
 *
 * @return mixed|null
 */
function xt_woofc_get_cart_item( $car_item_key )
{
    $cart_content = WC()->cart->get_cart();
    if ( !empty($cart_content[$car_item_key]) ) {
        return $cart_content[$car_item_key];
    }
    return null;
}

/**
 * @param $cart_item
 *
 * @return mixed|void
 */
function xt_woofc_item_attributes( &$cart_item )
{
    $display_type = xt_woofc_option( 'cart_product_attributes_display', 'list' );
    $hide_attribute_label = (bool) xt_woofc_option( 'cart_product_attributes_hide_label', 0 );
    $class = array( 'xt_woofc-variation' );
    $class[] = 'xt_woofc-variation-' . $display_type;
    $class = implode( ' ', $class );
    $html = '';
    $item_data = array();
    if ( $cart_item['data']->is_type( 'variation' ) && is_array( $cart_item['variation'] ) ) {
        foreach ( $cart_item['variation'] as $name => $value ) {
            if ( !is_string( $value ) ) {
                continue;
            }
            $taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );
            // If this is a term slug, get the term's nice name
            
            if ( taxonomy_exists( $taxonomy ) ) {
                $term = get_term_by( 'slug', $value, $taxonomy );
                if ( !is_wp_error( $term ) && $term && $term->name ) {
                    $value = $term->name;
                }
                $label = wc_attribute_label( $taxonomy );
                // If this is a custom option slug, get the options name.
            } else {
                $value = apply_filters( 'woocommerce_variation_option_name', $value );
                $label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cart_item['data'] );
            }
            
            // Check the nicename against the title.
            if ( '' === $value ) {
                continue;
            }
            $item_data[] = array(
                'key'   => $label,
                'value' => $value,
            );
        }
    }
    // Filter item data to allow 3rd parties to add more to the array
    $item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $cart_item );
    // Format item data ready to display
    foreach ( $item_data as $key => $data ) {
        // Set hidden to true to not display meta on cart.
        
        if ( !empty($data['hidden']) ) {
            unset( $item_data[$key] );
            continue;
        }
        
        $key = ( !empty($data['key']) ? $data['key'] : $data['name'] );
        $display = ( !empty($data['display']) ? $data['display'] : $data['value'] );
        $display = strip_tags( $display );
        $html .= '<dl class="' . esc_attr( $class ) . '">';
        
        if ( $hide_attribute_label ) {
            $html .= '	<dt>' . ucfirst( wp_kses_post( $display ) ) . '</dt>';
        } else {
            if ( !empty($key) ) {
                $html .= '	<dt>' . esc_html( $key ) . ':</dt>';
            }
            $html .= '<dd>' . ucfirst( wp_kses_post( $display ) ) . '</dd>';
        }
        
        $html .= '</dl>';
    }
    return apply_filters( 'xt_woo_floating_cart_attributes', $html );
}

/**
 * @param $n
 *
 * @return int
 */
function xt_woofc_digits_count( $n )
{
    $count = 0;
    if ( $n >= 1 ) {
        ++$count;
    }
    while ( $n / 10 >= 1 ) {
        $n /= 10;
        ++$count;
    }
    return $count;
}

/**
 * @param $item_id
 *
 * @return array
 */
function xt_woofc_get_variation_data_from_variation_id( $item_id )
{
    $_product = new WC_Product_Variation( $item_id );
    $variation_data = $_product->get_variation_attributes();
    return $variation_data;
}

/**
 * @param $slug
 * @param array $vars
 * @param false $return
 * @param false $locateOnly
 *
 * @return string
 */
function xt_woo_floating_cart_template(
    $slug,
    $vars = array(),
    $return = false,
    $locateOnly = false
)
{
    return xt_woo_floating_cart()->get_template(
        $slug,
        $vars,
        $return,
        $locateOnly
    );
}

/**
 * @param $id
 * @param null $default
 *
 * @return mixed
 */
function xt_woofc_option( $id, $default = null )
{
    return xt_woo_floating_cart()->customizer()->get_option( $id, $default );
}

/**
 * @param $id
 * @param null $default
 *
 * @return bool
 */
function xt_woofc_option_bool( $id, $default = null )
{
    return xt_woo_floating_cart()->customizer()->get_option_bool( $id, $default );
}

/**
 * @param $id
 * @param $value
 */
function xt_woofc_update_option( $id, $value )
{
    xt_woo_floating_cart()->customizer()->update_option( $id, $value );
}

/**
 * @param $id
 */
function xt_woofc_delete_option( $id )
{
    xt_woo_floating_cart()->customizer()->delete_option( $id );
}
