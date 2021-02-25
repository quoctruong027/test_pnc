<?php

/* @var $customizer XT_Framework_Customizer */
$fields[] = array(
    'id'          => 'ajax_init',
    'section'     => 'general',
    'label'       => esc_html__( 'Force Ajax Initialization', 'woo-floating-cart' ),
    'description' => esc_html__( 'Enable only if encountering caching issues / conflicts with your theme', 'woo-floating-cart' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
    '0' => esc_html__( 'No', 'woo-floating-cart' ),
    '1' => esc_html__( 'Yes', 'woo-floating-cart' ),
),
    'default'     => '0',
    'priority'    => 10,
    'transport'   => 'postMessage',
);
$fields[] = array(
    'id'          => 'active_cart_body_lock_scroll',
    'section'     => 'general',
    'label'       => esc_html__( 'Lock page scroll when active', 'woo-floating-cart' ),
    'description' => esc_html__( 'When the floating cart is open, lock main site body scroll', 'woo-floating-cart' ),
    'type'        => 'radio-buttonset',
    'choices'     => array(
    '0' => esc_html__( 'No', 'woo-floating-cart' ),
    '1' => esc_html__( 'Yes', 'woo-floating-cart' ),
),
    'default'     => '1',
    'priority'    => 10,
);
$fields[] = array(
    'id'          => 'active_cart_body_overlay_color',
    'section'     => 'general',
    'label'       => esc_html__( 'Overlay Color', 'woo-floating-cart' ),
    'description' => esc_html__( 'Set the Overlay Color on top of the page content, behind the cart. This helps focusing on the cart.', 'woo-floating-cart' ),
    'type'        => 'color',
    'choices'     => array(
    'alpha' => true,
),
    'priority'    => 10,
    'default'     => 'rgba(0,0,0,.5)',
    'transport'   => 'auto',
    'output'      => array( array(
    'element'  => ':root',
    'property' => '--xt-woofc-overlay-color',
) ),
);
$fields[] = array(
    'id'        => 'position',
    'section'   => 'general',
    'label'     => esc_html__( 'Trigger / Cart Position', 'woo-floating-cart' ),
    'type'      => 'radio',
    'priority'  => 10,
    'choices'   => array(
    'top-left'     => esc_html__( 'Top Left', 'woo-floating-cart' ),
    'top-right'    => esc_html__( 'Top Right', 'woo-floating-cart' ),
    'bottom-left'  => esc_html__( 'Bottom Left', 'woo-floating-cart' ),
    'bottom-right' => esc_html__( 'Bottom Right', 'woo-floating-cart' ),
),
    'transport' => 'postMessage',
    'js_vars'   => array( array(
    'element'     => '.xt_woofc',
    'function'    => 'class',
    'prefix'      => 'xt_woofc-pos-',
    'media_query' => $customizer->media_query( 'desktop', 'min' ),
), array(
    'element'     => '.xt_woofc',
    'function'    => 'html',
    'attr'        => 'data-position',
    'media_query' => $customizer->media_query( 'desktop', 'min' ),
) ),
    'default'   => 'bottom-right',
    'screen'    => 'desktop',
);
$fields[] = array(
    'id'        => 'position_tablet',
    'section'   => 'general',
    'label'     => esc_html__( 'Trigger / Cart Position', 'woo-floating-cart' ),
    'type'      => 'radio',
    'priority'  => 10,
    'choices'   => array(
    'top-left'     => esc_html__( 'Top Left', 'woo-floating-cart' ),
    'top-right'    => esc_html__( 'Top Right', 'woo-floating-cart' ),
    'bottom-left'  => esc_html__( 'Bottom Left', 'woo-floating-cart' ),
    'bottom-right' => esc_html__( 'Bottom Right', 'woo-floating-cart' ),
),
    'transport' => 'postMessage',
    'js_vars'   => array( array(
    'element'     => '.xt_woofc',
    'function'    => 'class',
    'prefix'      => 'xt_woofc-tablet-pos-',
    'media_query' => $customizer->media_query( 'tablet', 'max' ),
), array(
    'element'     => '.xt_woofc',
    'function'    => 'html',
    'attr'        => 'data-tablet_position',
    'media_query' => $customizer->media_query( 'tablet', 'max' ),
) ),
    'default'   => 'bottom-right',
    'screen'    => 'tablet',
);
$fields[] = array(
    'id'        => 'position_mobile',
    'section'   => 'general',
    'label'     => esc_html__( 'Trigger / Cart Position', 'woo-floating-cart' ),
    'type'      => 'radio',
    'priority'  => 10,
    'choices'   => array(
    'top-left'     => esc_html__( 'Top Left', 'woo-floating-cart' ),
    'top-right'    => esc_html__( 'Top Right', 'woo-floating-cart' ),
    'bottom-left'  => esc_html__( 'Bottom Left', 'woo-floating-cart' ),
    'bottom-right' => esc_html__( 'Bottom Right', 'woo-floating-cart' ),
),
    'transport' => 'postMessage',
    'js_vars'   => array( array(
    'element'     => '.xt_woofc',
    'function'    => 'class',
    'prefix'      => 'xt_woofc-mobile-pos-',
    'media_query' => $customizer->media_query( 'mobile', 'max' ),
), array(
    'element'     => '.xt_woofc',
    'function'    => 'html',
    'attr'        => 'data-mobile_position',
    'media_query' => $customizer->media_query( 'mobile', 'max' ),
) ),
    'default'   => 'bottom-right',
    'screen'    => 'mobile',
);
$fields[] = array(
    'id'        => 'counter_position',
    'section'   => 'general',
    'label'     => esc_html__( 'Product Counter Position', 'woo-floating-cart' ),
    'type'      => 'radio',
    'priority'  => 10,
    'choices'   => array(
    'top-left'     => esc_html__( 'Top Left', 'woo-floating-cart' ),
    'top-right'    => esc_html__( 'Top Right', 'woo-floating-cart' ),
    'bottom-left'  => esc_html__( 'Bottom Left', 'woo-floating-cart' ),
    'bottom-right' => esc_html__( 'Bottom Right', 'woo-floating-cart' ),
),
    'transport' => 'postMessage',
    'js_vars'   => array( array(
    'element'     => '.xt_woofc',
    'function'    => 'class',
    'prefix'      => 'xt_woofc-counter-pos-',
    'media_query' => $customizer->media_query( 'desktop', 'min' ),
) ),
    'default'   => 'top-left',
    'screen'    => 'desktop',
);
$fields[] = array(
    'id'        => 'counter_position_tablet',
    'section'   => 'general',
    'label'     => esc_html__( 'Product Counter Position', 'woo-floating-cart' ),
    'type'      => 'radio',
    'priority'  => 10,
    'choices'   => array(
    'top-left'     => esc_html__( 'Top Left', 'woo-floating-cart' ),
    'top-right'    => esc_html__( 'Top Right', 'woo-floating-cart' ),
    'bottom-left'  => esc_html__( 'Bottom Left', 'woo-floating-cart' ),
    'bottom-right' => esc_html__( 'Bottom Right', 'woo-floating-cart' ),
),
    'transport' => 'postMessage',
    'js_vars'   => array( array(
    'element'     => '.xt_woofc',
    'function'    => 'class',
    'prefix'      => 'xt_woofc-counter-tablet-pos-',
    'media_query' => $customizer->media_query( 'tablet', 'max' ),
) ),
    'default'   => 'top-left',
    'screen'    => 'tablet',
);
$fields[] = array(
    'id'        => 'counter_position_mobile',
    'section'   => 'general',
    'label'     => esc_html__( 'Product Counter Position', 'woo-floating-cart' ),
    'type'      => 'radio',
    'priority'  => 10,
    'choices'   => array(
    'top-left'     => esc_html__( 'Top Left', 'woo-floating-cart' ),
    'top-right'    => esc_html__( 'Top Right', 'woo-floating-cart' ),
    'bottom-left'  => esc_html__( 'Bottom Left', 'woo-floating-cart' ),
    'bottom-right' => esc_html__( 'Bottom Right', 'woo-floating-cart' ),
),
    'transport' => 'postMessage',
    'js_vars'   => array( array(
    'element'     => '.xt_woofc',
    'function'    => 'class',
    'prefix'      => 'xt_woofc-counter-mobile-pos-',
    'media_query' => $customizer->media_query( 'mobile', 'max' ),
) ),
    'default'   => 'top-left',
    'screen'    => 'mobile',
);
$fields[] = array(
    'id'      => 'general_features',
    'section' => 'general',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/general.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);