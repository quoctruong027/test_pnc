<?php

$fields[] = array(
    'id'      => 'cart_menu_features',
    'section' => 'menu-item',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/menu-item.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);