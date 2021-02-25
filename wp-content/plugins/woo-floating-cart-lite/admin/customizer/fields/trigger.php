<?php

$fields[] = array(
    'id'       => 'trigger_event_type',
    'section'  => 'trigger',
    'label'    => esc_html__( 'Cart Trigger Event Type', 'woo-floating-cart' ),
    'type'     => 'radio-buttonset',
    'choices'  => array(
    'vclick'     => esc_attr__( 'Click Only', 'woo-floating-cart' ),
    'mouseenter' => esc_attr__( 'Mouse Over Or Click', 'woo-floating-cart' ),
),
    'default'  => 'vclick',
    'priority' => 10,
);
$fields[] = array(
    'id'              => 'trigger_hover_delay',
    'section'         => 'trigger',
    'label'           => esc_html__( 'Mouse Over delay before trigger', 'woo-floating-cart' ),
    'type'            => 'slider',
    'choices'         => array(
    'min'    => '0',
    'max'    => '1500',
    'step'   => '10',
    'suffix' => 'ms',
),
    'priority'        => 10,
    'default'         => 200,
    'active_callback' => array( array(
    'setting'  => 'trigger_event_type',
    'operator' => '==',
    'value'    => 'mouseenter',
) ),
);
$fields[] = array(
    'id'      => 'trigger_features',
    'section' => 'trigger',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/trigger.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);