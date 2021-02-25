<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;

require_once dirname(__FILE__) . '/../../data/feedcore.php';
require_once dirname(__FILE__) . '/../../classes/dialogbasefeed.php';
do_action('load_etcpf_modifiers');

global $etcore;

$etcore->trigger('cpf_init_feeds');
add_action('etcpf_select_feed_main_hook', 'etcpf_select_main_feed');
do_action('etcpf_select_feed_main_hook');

function etcpf_select_main_feed()
{
    $feed_type = array_key_exists('feedtype', $_POST) ? sanitize_text_field($_POST['feedtype']) : null ;
    if ($feed_type == null || strlen($feed_type) === 0) {
        return;
    }

    $inc = dirname(__FILE__) . '/../../feeds/'.strtolower($feed_type).'/dialognew.php';

    $feed_object_name = 'ETCPF_'.$feed_type.'Dialog';
    if (file_exists($inc)) {
        include_once $inc;
    }
    $f = new $feed_object_name();
    echo $f->mainDialog();

}