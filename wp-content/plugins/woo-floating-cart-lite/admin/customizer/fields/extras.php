<?php

$fields[] = array(
    'id'      => 'extras_features',
    'section' => 'extras',
    'type'    => 'xt-premium',
    'default' => array(
    'type'  => 'image',
    'value' => $this->core->plugin_url() . 'admin/customizer/assets/images/extras.png',
    'link'  => $this->core->plugin_upgrade_url(),
),
);