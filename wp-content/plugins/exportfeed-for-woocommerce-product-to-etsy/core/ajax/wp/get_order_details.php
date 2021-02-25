<?php
if (!is_admin()) die("Unauthorized Access");

function getOrderDetails()
{
    global $wpdb;
    $table = $wpdb->prefix . "etcpf_orders";
    $data = $wpdb->get_results($wpdb->prepare("SELECT id,title,price,shipping_cost,quantity FROM $table WHERE receipt_id=%d", array(sanitize_text_field($_POST['order_id']))));
    return array('success' => true, 'data' => $data);
}

echo json_encode(getOrderDetails());