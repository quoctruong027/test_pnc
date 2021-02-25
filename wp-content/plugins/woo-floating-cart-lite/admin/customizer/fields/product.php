<?php

$fields[] = array(
    'id'      => 'list_features',
    'section' => 'product',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/product.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);