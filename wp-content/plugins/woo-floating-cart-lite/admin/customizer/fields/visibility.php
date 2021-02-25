<?php

$fields[] = array(
    'id'          => 'cart_zindex_desktop',
    'section'     => 'visibility',
    'label'       => esc_html__( 'Cart Z-Index', 'woo-floating-cart' ),
    'description' => esc_html__( 'Set the stack order for the cart. An element with greater stack order is always in front of an element with a lower stack order. Use this option to adjust the cart order if for some reason it is not visible on your theme or maybe being overlapped by other elements', 'woo-floating-cart' ),
    'type'        => 'slider',
    'choices'     => array(
    'min'  => '999',
    'max'  => '999999',
    'step' => '9',
),
    'priority'    => 10,
    'default'     => '90200',
    'transport'   => 'auto',
    'screen'      => 'desktop',
    'output'      => array( array(
    'element'  => ':root',
    'property' => '--xt-woofc-zindex',
), array(
    'element'       => '.select2-container',
    'value_pattern' => 'calc($ + 2000)!important',
    'property'      => 'z-index',
) ),
);
$fields[] = array(
    'id'          => 'cart_zindex_tablet',
    'section'     => 'visibility',
    'label'       => esc_html__( 'Cart Z-Index', 'woo-floating-cart' ),
    'description' => esc_html__( 'Set the stack order for the cart. An element with greater stack order is always in front of an element with a lower stack order. Use this option to adjust the cart order if for some reason it is not visible on your theme or maybe being overlapped by other elements', 'woo-floating-cart' ),
    'type'        => 'slider',
    'choices'     => array(
    'min'  => '999',
    'max'  => '999999',
    'step' => '9',
),
    'priority'    => 10,
    'default'     => '90200',
    'transport'   => 'auto',
    'screen'      => 'tablet',
    'output'      => array( array(
    'element'  => ':root',
    'property' => '--xt-woofc-zindex',
), array(
    'element'       => '.select2-container',
    'value_pattern' => 'calc($ + 2000)!important',
    'property'      => 'z-index',
) ),
);
$fields[] = array(
    'id'          => 'cart_zindex_mobile',
    'section'     => 'visibility',
    'label'       => esc_html__( 'Cart Z-Index', 'woo-floating-cart' ),
    'description' => esc_html__( 'Set the stack order for the cart. An element with greater stack order is always in front of an element with a lower stack order. Use this option to adjust the cart order if for some reason it is not visible on your theme or maybe being overlapped by other elements', 'woo-floating-cart' ),
    'type'        => 'slider',
    'choices'     => array(
    'min'  => '999',
    'max'  => '999999',
    'step' => '9',
),
    'priority'    => 10,
    'default'     => '90200',
    'transport'   => 'auto',
    'screen'      => 'mobile',
    'output'      => array( array(
    'element'  => ':root',
    'property' => '--xt-woofc-zindex',
), array(
    'element'       => '.select2-container',
    'value_pattern' => 'calc($ + 2000)!important',
    'property'      => 'z-index',
) ),
);
$fields[] = array(
    'id'      => 'visibility_features',
    'section' => 'visibility',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/visibility.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);