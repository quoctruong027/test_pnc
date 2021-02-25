<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
//Checks cart-product-feed version
function etcpf_version()
{
    //taken from /include/update.php line 270
    $plugin_info = get_site_transient('update_plugins');

    //we want to always display 'up to date', therefore we don't need the below check
    if (!isset($plugin_info->response[ETCPF_PLUGIN_BASENAME])) {
        return ' | You are up to date';
    }

    $CPF_WP_version
        = $plugin_info->response[ETCPF_PLUGIN_BASENAME]->new_version; //wordpress repository version
    //version_compare:
    //returns -1 if the first version is lower than the second,
    //0 if they are equal,
    //1 if the second is lower.
    $doUpdate = version_compare($CPF_WP_version, ETCPF_PLUGIN_VERSION);
    //if current version is older than wordpress repo version
    if ($doUpdate == 1) {
        return ' | <a href=\'plugins.php\'>Out of date - please update</a>';
    }

    //else, up to date
    return ' | You are up to date';
}

function etcpf_info()
{
    $etsy = new ETCPF_Etsy();
    $etsy->view('show-setup-bar');
}