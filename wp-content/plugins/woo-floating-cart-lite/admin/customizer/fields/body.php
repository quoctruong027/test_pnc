<?php

$fields[] = array(
    'id'      => 'body_features',
    'section' => 'body',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/body.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);