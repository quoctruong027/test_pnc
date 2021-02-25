<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
require_once dirname(__FILE__) . '/../../classes/etsyclient.php';
global $wpdb;
$etsy = new ETCPF_Etsy();
/*
* Step 3 : When access is allowed by from etsy allow access page
*/
if (isset($_GET['oauth_verifier'])) {

    // getting a secret token key received from oauth
    $sql = $wpdb->prepare("SELECT token,user_id,oauth_verifier FROM " . $wpdb->prefix . "etcpf_etsy_token" . " WHERE user_id = %d", [get_current_user_id()]);
    $st_token = $wpdb->get_row($sql);
    $res_data = array(
        'oauth_verifier' => sanitize_text_field($_GET['oauth_verifier']),
        'oauth_secret_token' => $st_token->token,
        'oauth_token' => sanitize_text_field($_GET['oauth_token']),
        'user_id' => $st_token->user_id,
        'is_default' => 1,
    );

    if ($st_token->oauth_verifier == null || $st_token->oauth_verifier == "") {
        $etsy->authorize($res_data);
        echo "<h3>You are now Connected to our Etsy.</h3>";
        sleep(2);
        echo "<script type='text/javascript'>window.location.href = '" . admin_url() . "admin.php?page=etsy-export-feed-configure" . "'</script>";
    } else {
        echo 'Your Shop is already Connected!';
        sleep(2);
        echo "<script type='text/javascript'>window.location.href = '" . admin_url() . "admin.php?page=etsy-export-feed-configure" . "'</script>";
    }
}