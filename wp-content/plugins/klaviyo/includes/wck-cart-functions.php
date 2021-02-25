<?php
/**
 * WooCommerceKlaviyo Order Functions
 *
 * Functions for order specific things.
 *
 * @author    Klaviyo
 * @category  Core
 * @package   WooCommerceKlaviyo/Functions
 * @version   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function add_composite_products_cart ($composite_products) {
  foreach ($composite_products as $product) {
    $container = array();
    foreach ($product as $i => $v) {
      $item = $v['item'];
      $container_id = $item['container_id'];
      if (isset($item['attributes'])) {
        $container[$container_id] = array(
          'product_id' => $item['product_id'],
          'quantity' => $item['quantity'],
          'variation_id' => $item['variation_id'],
          'attributes' => $item['attributes'],
        );
      } else {
        $container[$container_id] = array(
          'product_id' => $item['product_id'],
          'quantity' => $item['quantity'],
        );
      }
    }
    $added = WC_CP()->cart->add_composite_to_cart( $v['composite_id'], $v['composite_quantity'], $container );
  }
}

function add_encoded_composite($container_ids,$values) {
  $composite_product = array();
  foreach ($container_ids as $container_id => $container_values ) {
    $args = array();
    if (isset($container_values['attributes'])) {
      $args = array(
      'composite_id' => $container_values['composite_id'],
      'composite_quantity' => $values['quantity'],
      'item' => array(
      'product_id' => $container_values['product_id'],
      'quantity' => $container_values['quantity'],
      'container_id' => $container_id,
      'attributes' => $container_values['attributes'],
      'variation_id' => $container_values['variation_id'],
      )
    );
  } else {
    $args = array(
      'composite_id' => $container_values['composite_id'],
      'composite_quantity' => $values['quantity'],
      'item' => array(
        'product_id' => $container_values['product_id'],
        'quantity' => $container_values['quantity'],
        'container_id' => $container_id,
        )
      );
    }
    array_push($composite_product, $args);
  }
  return $composite_product;
}

function get_email($current_user) {
  $email = '';
  if ($current_user->user_email) {
    $email = $current_user->user_email;
  } else {
    // See if current user is a commenter
    $commenter = wp_get_current_commenter();
    if ($commenter['comment_author_email']) {
      $email = $commenter['comment_author_email'];
    }
  }
  return $email;
}

function wck_rebuild_cart() {

  // Exit if in back-end
  if(is_admin()){return;}
  global $woocommerce;

  // Exit if not on cart page or no wck_rebuild_cart parameter
  $current_url = build_current_url();
  $utm_wck_rebuild_cart = isset($_GET['wck_rebuild_cart']) ? $_GET['wck_rebuild_cart'] : '';
  if($current_url[0]!==wc_get_cart_url() || $utm_wck_rebuild_cart==='') {return;}

  // Rebuild cart
  $woocommerce->cart->empty_cart(true);
  $woocommerce->cart->get_cart();

  $kl_cart = json_decode(base64_decode($utm_wck_rebuild_cart), true);
  $composite_products = $kl_cart['composite'];
  $normal_products = $kl_cart['normal_products'];

  foreach ($normal_products as $product) {
    $cart_key = $woocommerce->cart->add_to_cart($product['product_id'],$product['quantity'],$product['variation_id'],$product['variation']);
  }

  if ( class_exists( 'WC_Composite_Products' ) ) {
    add_composite_products_cart($composite_products);
  }

  $carturl = wc_get_cart_url();
  if ($current_url[0]==wc_get_cart_url()){
      header("Refresh:0; url=".$carturl);
  }
}

function build_current_url() {
  $server_protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
  $server_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
  $server_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

  return explode( '?', $server_protocol . '://' . $server_host . $server_uri );
}

/**
 * Insert tracking code code for tracking started checkout.
 *
 * @access public
 * @return void
 */
function wck_insert_checkout_tracking($checkout) {

  global $current_user;
  wp_reset_query();

  wp_get_current_user();

  $cart = WC()->cart;
  $event_data = array(
    '$service' => 'woocommerce',
    'CurrencySymbol' => get_woocommerce_currency_symbol(),
    'Currency' => get_woocommerce_currency(),
    '$value' => $cart->total,
    '$extra' => array(
      'Items' => array(),
      'SubTotal' => $cart->subtotal,
      'ShippingTotal' => $cart->shipping_total,
      'TaxTotal' => $cart->tax_total,
      'GrandTotal' => $cart->total
    )
  );
  $wck_cart = array();
  $composite_products = array();
  $normal_products = array();
  $allcategories = array();
  foreach ( $cart->get_cart() as $cart_item_key => $values ) {
    $product = $values['data'];
    $parent_product_id = $product->get_parent_id();

    if ($product->get_parent_id() == 0 ) {
      $parent_product_id = $product->get_id();
    }
    $categories_array = get_the_terms( $parent_product_id, 'product_cat' );
    if ( $categories_array && ! is_wp_error( $categories_array ) ) {
        $categories = wp_list_pluck( $categories_array, 'name' );

        foreach( $categories as $category ) {
          array_push( $allcategories, $category );
        }
    }

    $is_composite_child = false;

    if ( class_exists( 'WC_Composite_Products' ) ) {
        $product_encoded = json_encode($product);
        $is_composite_child = wc_cp_is_composited_cart_item($values);
        $container = wc_cp_get_composited_cart_item_container($values);

        if ($product->get_type() == 'composite') {
          $composite_product = array();

          foreach (wc_cp_get_composited_cart_items($values) as $key => $val) {
            $composite_product = add_encoded_composite($val['composite_data'],$values);
            break;
          }
          array_push($composite_products,$composite_product);
        } else {
          if (!$is_composite_child) {
            $normal_products[$cart_item_key] = normalize_normal_product( $values );
          }
        }
    } else {
        $normal_products[$cart_item_key] = normalize_normal_product( $values );
    }

    $image = wp_get_attachment_url(get_post_thumbnail_id($product->get_id()));

    if ($image == false) {
      $image = wp_get_attachment_url(get_post_thumbnail_id($parent_product_id));
    }

    $event_data['$extra']['Items'] []= array(
      'Quantity' => $values['quantity'],
      'ProductID' => $parent_product_id,
      'VariantID' => $product->get_id(),
      'Name' => $product->get_name(),
      'URL' => $product->get_permalink(),
      'Images' => array(
        array(
          'URL' => $image
          )
        ),
      'Categories' => $categories,
      'Variation' => $values['variation'],
      'SubTotal' => $values['line_subtotal'],
      'Total' => $values['line_subtotal_tax'],
      'LineTotal' => $values['line_total'],
      'Tax' => $values['line_tax'],
      'TotalWithTax' => $values['line_total'] + $values['line_tax']
    );
    $allcategories = array_unique($allcategories);
    $event_data['Categories'] = $allcategories;
  }

  if ( empty($event_data['$extra']['Items']) ) {
    return;
  }
  // Set top-level item names
  $itemNames = array();
  foreach ($event_data['$extra']['Items'] as $val) {
    array_push($itemNames, $val['Name']);
  }
  $event_data['ItemNames'] = $itemNames;

  $email = get_email($current_user);
  $wck_cart['composite'] = $composite_products;
  $wck_cart['normal_products'] = $normal_products;
  $event_data['$extra']['CartRebuildKey'] = base64_encode(json_encode($wck_cart));

  $started_checkout_data = array(
      'email' => $email,
      'event_data' => $event_data
  );

  // Pass Started Checkout event data to javascript attaching to 'wck_started_checkout' handle
  wp_localize_script( 'wck_started_checkout', 'kl_checkout', $started_checkout_data );
}

/**
 * Helper function to normalize normal product.
 *
 * @param array  $item Cart item.
 * @return array Normalized cart item.
 */
function normalize_normal_product( $item ) {
  return array(
           'product_id'=>$item['product_id'],
           'quantity'=>$item['quantity'],
           'variation_id'=>$item['variation_id'],
           'variation'=>$item['variation']
         );
}

add_action( 'woocommerce_after_checkout_form', 'wck_insert_checkout_tracking' );

// Load javascript file for Started Checkout events
add_action( 'wp_enqueue_scripts', 'load_started_checkout' );


/**
 *  Check if page is a checkout page, if so load the Started Checkout javascript file.
 *
 */
function load_started_checkout() {
    $token = get_option('klaviyo_settings')['public_api_key'];

    if ( is_checkout() ) {
        wp_enqueue_script( 'wck_started_checkout', plugins_url( '/js/wck-started-checkout.js', __FILE__ ), null, null, true );
        wp_localize_script( 'wck_started_checkout', 'public_key', array( 'token' => $token ));
    }
}

add_action( 'wp_loaded', 'wck_rebuild_cart');


function kl_checkbox_custom_checkout_field( $checkout ) {
    $klaviyo_settings = get_option('klaviyo_settings');
    woocommerce_form_field( 'kl_newsletter_checkbox', array(
    'type'          => 'checkbox',
    'class'         => array('kl_newsletter_checkbox_field'),
    'label'         => $klaviyo_settings['klaviyo_newsletter_text'],
    'value'  => true,
    'default' => 0,
    'required'  => false,
    ), $checkout->get_value( 'kl_newsletter_checkbox' ));
}


function kl_add_to_list( $order_id ) {
    $klaviyo_settings = get_option('klaviyo_settings');
    if ( isset( $_POST['kl_newsletter_checkbox'] ) && $_POST['kl_newsletter_checkbox'] ) {
          $email = $_POST['billing_email'];
          $url = 'https://manage.kmail-lists.com/ajax/subscriptions/subscribe';
          $response = wp_remote_post( $url, array(
              'method'      => 'POST',
              'httpversion' => '1.0',
              'blocking'    => false,
              'body'        => array(
                  'g' => $klaviyo_settings['klaviyo_newsletter_list_id'],
                  'email' => $email,
                  '$fields' => '$consent,$source',
                  '$consent' => ['email'],
                  '$source' => 'Accepted at Checkout'
              )
            )
          );
          update_post_meta( $order_id, 'klaviyo-response', $response );
        }
}

$klaviyo_settings = get_option('klaviyo_settings');
if (!empty($klaviyo_settings['klaviyo_newsletter_list_id'])) {
    // Add the checkbox field
    add_action('woocommerce_after_checkout_billing_form', 'kl_checkbox_custom_checkout_field');

    // Post list request to Klaviyo
    add_action('woocommerce_checkout_update_order_meta', 'kl_add_to_list');
}
