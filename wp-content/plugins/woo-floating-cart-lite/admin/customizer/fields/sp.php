<?php

$fields[] = array(
    'id'      => 'sp_features',
    'section' => 'sp',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/sp.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);