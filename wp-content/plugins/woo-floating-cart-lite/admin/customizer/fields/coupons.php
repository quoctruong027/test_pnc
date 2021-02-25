<?php

$fields[] = array(
    'id'      => 'coupons_features',
    'section' => 'coupons',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/coupons.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);