<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
$check = wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf');
if (!$check)
    return;
require_once dirname(__FILE__) . '/../../classes/cron.php';
require_once dirname(__FILE__) . '/../../../etsy-export-feed-wpincludes.php';

function etcpf_safe_get_update_data($index)
{
    if (isset($_POST[$index])) {
        return $_POST[$index];
    } else {
        return '';
    }
}

add_action('get_etsy_feed_config_hook', 'etcpf_get_feed_config');
do_action('get_etsy_feed_config_hook');

function etcpf_get_feed_config()
{
    global $etcore;

    $feedid = intval(etcpf_safe_get_update_data('feedid'));
    $setting = esc_attr(etcpf_safe_get_update_data('setting'));
    $merchant_attr = array_map(
        'esc_attr', etcpf_safe_get_update_data('cpf_merchant_attr')
    );
    $cpf_prefix = array_map(
        'esc_attr', etcpf_safe_get_update_data('cpf_feed_prefix')
    );
    $cpf_suffix = array_map(
        'esc_attr', etcpf_safe_get_update_data('cpf_feed_suffix')
    );
    $cpf_feed_type = array_map(
        'esc_attr', etcpf_safe_get_update_data('cpf_feed_type')
    );
    $cpf_feed_value_default = array_map(
        'esc_attr', etcpf_safe_get_update_data('cpf_feed_value_default')
    );
    $cpf_feed_value_custom = array_map(
        'esc_attr', etcpf_safe_get_update_data('cpf_feed_value_custom')
    );
    $arr = array();
    foreach ($merchant_attr as $key => $value) {
        $arr[$key]['merchant_attr'] = $value;
    }
    foreach ($cpf_prefix as $key => $value) {
        $arr[$key]['cpf_prefix'] = $value;
    }

    foreach ($cpf_suffix as $key => $value) {
        $arr[$key]['cpf_suffix'] = $value;
    }
    foreach ($cpf_feed_type as $key => $value) {
        $arr[$key]['cpf_feed_type'] = $value;
    }
    foreach ($cpf_feed_value_default as $key => $value) {
        $arr[$key]['cpf_feed_value_default'] = $value;
    }

    foreach ($cpf_feed_value_custom as $key => $value) {
        $arr[$key]['cpf_feed_value_custom'] = $value;
    }

    /*
        1.to add the default value to the products
            setAttributeDefault attribute_name as "value"
            Example: setAttributeDefault brand as "Studio Lilesadi"
        2.to map the value of one attribute to another
            mapAttribute attribute1 to attribute2
            Example: mapAttribute brand to Brand_name
        3. delete the attribute from the feed which you donot want to include
            deleteAttribute attribute_name
            example: deleteAttribute regular_price
        4. set google_exact_title to true  //for google only
        5. $max_custom_field = 500
        6. limitOutput FROM low TO high
            example: limitOutput FROM 0 TO 5000
        7.  mapTaxonomy source as attribute
            example: mapTaxonomy brand as brand
     */
    $postdata = maybe_serialize($arr);
    if ($feedid == 0) {
        update_option('Etsy-etsy-merchant-custom-settings', $postdata);
    } else {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'etcpf_feeds';
        $sql = $wpdb->prepare(
            "
				UPDATE $feed_table 
				SET
					`own_overrides`=1,
					`feed_overrides`='%s'
				WHERE `id`=%s", array($postdata, $feedid)
        );
        $wpdb->query($sql);
    }
    echo 'Updated.';
}