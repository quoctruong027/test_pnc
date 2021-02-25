<?php

$fields[] = array(
    'id'      => 'cart_shortcode_features',
    'section' => 'shortcode',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/shortcode.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);