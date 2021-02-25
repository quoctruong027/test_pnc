<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once ETCPF_PATH . '/core/classes/etsyclient.php';
$i = get_post_thumbnail_id(15);
$img = wp_get_attachment_image_src($i, 'small-feature');
$url = wp_upload_dir();
$pos = strpos($img[0], 'uploads');
$img = substr($img[0], $pos + 7);
global $wpdb;
$etsy = new ETCPF_Etsy();
$msg = $etsy->prepare_uploading();
echo $msg;