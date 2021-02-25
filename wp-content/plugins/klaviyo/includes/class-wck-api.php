<?php
/**
 * WooCommerceKlaviyo API
 *
 * Handles WCK-API endpoint requests
 *
 * @author      Klaviyo
 * @category    API
 * @package     WooCommerceKlaviyo/API
 * @since       2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WCK_API
{
    const VERSION = '2.3.6';

    const KLAVIYO_BASE_URL = 'klaviyo/v1';
    const ORDERS_ENDPOINT = 'orders';
    const EXTENSION_VERSION_ENDPOINT = 'version';
    const PRODUCTS_ENDPOINT = 'products';

    // API RESPONSES
    const API_RESPONSE_CODE = 'status_code';
    const API_RESPONSE_ERROR = 'error';
    const API_RESPONSE_REASON = 'reason';
    const API_RESPONSE_SUCCESS = 'success';

    // HTTP CODES
    const STATUS_CODE_AUTHORIZATION_ERROR = 403;
    const STATUS_CODE_INVALID_PARAMS = 400;
    const STATUS_CODE_HTTP_OK = 200;

    const DEFAULT_RECORDS_PER_PAGE = '50';
    const DATE_MODIFIED = 'post_modified_gmt';
    const POST_STATUS_ANY = 'any';

    const ERROR_KEYS_NOT_PASSED = 'consumer key or consumer secret not passed';
    const ERROR_CONSUMER_KEY_NOT_FOUND = 'consumer_key not found';

}

function count_loop(WP_Query $loop)
{
    $loop_ids = array();
    while ($loop->have_posts()) {
        $loop->the_post();
        $loop_id = get_the_ID();
        array_push($loop_ids, $loop_id);
    }
    return $loop_ids;
}

function validate_request($request)
{
    $consumer_key = $request->get_param('consumer_key');
    $consumer_secret = $request->get_param('consumer_secret');
    if (empty($consumer_key) || empty($consumer_secret)) {
        return validation_response(
            true,
            WCK_API::STATUS_CODE_INVALID_PARAMS,
            WCK_API::ERROR_KEYS_NOT_PASSED,
            false
        );
    }

    global $wpdb;
    // this is stored as a hash so we need to query on the hash
    $key = hash_hmac('sha256', $consumer_key, 'wc-api');
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "
    SELECT consumer_key, consumer_secret
    FROM {$wpdb->prefix}woocommerce_api_keys
    WHERE consumer_key = %s
     ",
            $key
        )
    );

    if ($user->consumer_secret == $consumer_secret) {
        return validation_response(
            false,
            WCK_API::STATUS_CODE_HTTP_OK,
            null,
            true
        );
    }
    return validation_response(
        true,
        WCK_API::STATUS_CODE_AUTHORIZATION_ERROR,
        WCK_API::ERROR_CONSUMER_KEY_NOT_FOUND,
        false
    );
}

function validation_response($error, $code, $reason, $success)
{
    return array(
        WCK_API::API_RESPONSE_ERROR => $error,
        WCK_API::API_RESPONSE_CODE => $code,
        WCK_API::API_RESPONSE_REASON => $reason,
        WCK_API::API_RESPONSE_SUCCESS => $success,
    );
}

function process_resource_args($request, $post_type)
{
    $page_limit = $request->get_param('page_limit');
    if (empty($page_limit)) {
        $page_limit = WCK_API::DEFAULT_RECORDS_PER_PAGE;
    }
    $date_modified_after = $request->get_param('date_modified_after');
    $date_modified_before = $request->get_param('date_modified_before');
    $page = $request->get_param('page');

    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => $page_limit,
        'post_status' => WCK_API::POST_STATUS_ANY,
        'paged' => $page,
        'date_query' => array(
            array(
                'column' => WCK_API::DATE_MODIFIED,
                'after' => $date_modified_after,
                'before' => $date_modified_before
            )
        ),
    );
    return $args;
}

function get_orders_count(WP_REST_Request $request)
{
    $validated_request = validate_request($request);
    if ($validated_request['error'] === true) {
        return $validated_request;
    }

    $args = process_resource_args($request, 'shop_order');

    $loop = new WP_Query($args);
    $data = count_loop($loop);
    return array('order_count' => $loop->found_posts);
}

function get_products_count(WP_REST_Request $request)
{
    $validated_request = validate_request($request);
    if ($validated_request['error'] === true) {
        return $validated_request;
    }

    $args = process_resource_args($request, 'product');
    $loop = new WP_Query($args);
    $data = count_loop($loop);
    return array('product_count' => $loop->found_posts);
}

function get_products(WP_REST_Request $request)
{
    $validated_request = validate_request($request);
    if ($validated_request['error'] === true) {
        return $validated_request;
    }

    $args = process_resource_args($request, 'product');

    $loop = new WP_Query($args);
    $data = count_loop($loop);
    return array('product_ids' => $data);
}

function get_orders(WP_REST_Request $request)
{
    $validated_request = validate_request($request);
    if ($validated_request['error'] === true) {
        return $validated_request;
    }

    $args = process_resource_args($request, 'shop_order');

    $loop = new WP_Query($args);
    $data = count_loop($loop);
    return array('order_ids' => $data);
}

function get_extension_version($data)
{
    return array('version' => WCK_API::VERSION);
}

add_action('rest_api_init', function () {
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, WCK_API::EXTENSION_VERSION_ENDPOINT, array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_extension_version',
        'permission_callback' => '__return_true',
    ));
});

add_action('rest_api_init', function ()
{
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, 'orders/count', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_orders_count',
        'permission_callback' => '__return_true',
    ));
});

add_action('rest_api_init', function ()
{
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, 'products/count', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_products_count',
        'permission_callback' => '__return_true',
    ));
});

add_action('rest_api_init', function ()
{
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, WCK_API::ORDERS_ENDPOINT, array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_orders',
        'args' => array(
            'id' => array(
                'validate_callback' => 'is_numeric'
            ),
        ),
        'permission_callback' => '__return_true',
    ));
});

add_action('rest_api_init', function()
{
    register_rest_route(WCK_API::KLAVIYO_BASE_URL, WCK_API::PRODUCTS_ENDPOINT, array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_products',
        'args' => array(
            'id' => array(
                'validate_callback' => 'is_numeric'
            ),
        ),
        'permission_callback' => '__return_true',
    ));
});
