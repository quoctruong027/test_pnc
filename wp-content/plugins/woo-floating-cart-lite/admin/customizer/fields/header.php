<?php

$fields[] = array(
    'id'      => 'header_features',
    'section' => 'header',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/header.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);