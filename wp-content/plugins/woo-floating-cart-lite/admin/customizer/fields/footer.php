<?php

$fields[] = array(
    'id'        => 'cart_checkout_button_bg_color',
    'section'   => 'footer',
    'label'     => esc_html__( 'Cart Checkout Button Bg Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'choices'   => array(
    'alpha' => true,
),
    'default'   => '#2c97de',
    'transport' => 'auto',
    'output'    => array( array(
    'element'  => ':root',
    'property' => '--xt-woofc-checkout-btn-bg-color',
) ),
);
$fields[] = array(
    'id'        => 'cart_checkout_button_bg_hover_color',
    'section'   => 'footer',
    'label'     => esc_html__( 'Cart Checkout Button Bg Hover Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'choices'   => array(
    'alpha' => true,
),
    'default'   => '#2c97de',
    'transport' => 'auto',
    'output'    => array( array(
    'element'  => ':root',
    'property' => '--xt-woofc-checkout-btn-bg-hover-color',
) ),
);
$fields[] = array(
    'id'        => 'cart_checkout_button_text_color',
    'section'   => 'footer',
    'label'     => esc_html__( 'Cart Checkout Button Text Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'default'   => '#ffffff',
    'transport' => 'auto',
    'output'    => array( array(
    'element'  => ':root',
    'property' => '--xt-woofc-checkout-btn-color',
) ),
);
$fields[] = array(
    'id'        => 'cart_checkout_button_text_hover_color',
    'section'   => 'footer',
    'label'     => esc_html__( 'Cart Checkout Button Text Hover Color', 'woo-floating-cart' ),
    'type'      => 'color',
    'default'   => '#ffffff',
    'transport' => 'auto',
    'output'    => array( array(
    'element'  => ':root',
    'property' => '--xt-woofc-checkout-btn-hover-color',
) ),
);
$fields[] = array(
    'id'      => 'footer_features',
    'section' => 'footer',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/footer.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);