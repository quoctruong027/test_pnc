<?php
function WoosharkAliexpressImporter_init($file)
{
  require_once('WoosharkAliexpressImporter_Plugin.php');
  $aPlugin = new WoosharkAliexpressImporter_Plugin();
  if (!$aPlugin->isInstalled()) {
    $aPlugin->install();
  } else {
    $aPlugin->upgrade();
  }
  $aPlugin->addActionsAndFilters();
  if (!$file) {
    $file = __FILE__;
  }
  register_activation_hook($file, array(&$aPlugin, 'activate'));
  register_deactivation_hook($file, array(&$aPlugin, 'deactivate'));
}
add_action('rest_api_init', function () {
  register_rest_route('myplugin/v1', '/author/(?P<id>\d+)', array('methods' => 'GET', 'callback' => 'my_awesome_func',));
});
function my_awesome_func($data)
{
  $posts = get_posts(array('author' => $data['id'],));
  if (empty($posts)) {
    return null;
  }
  return $posts[0]->post_title;
}



function get_categories_FROMWP()
{


  $categoriesArray = array();
  $orderby = 'name';
  $order = 'asc';
  $hide_empty = false;
  $cat_args = array(
    'orderby'    => $orderby,
    'order'      => $order,
    'hide_empty' => $hide_empty,
  );

  $product_categories = get_terms('product_cat', $cat_args);

  foreach ($product_categories as $product_category) {
    array_push($categoriesArray, array('name' => $product_category->name, 'count' => $product_category->count, 'term_id' => $product_category->term_id));
  }
  wp_send_json($categoriesArray);
}

function getCountOfProducts()
{
  $args = array(
    'post_type'      => 'product',
    'post_status' => array('publish', 'draft'),
    'meta_query' => array(
      array(
        'key' => 'isExpired',
        'value' => 'true',
        'compare' => 'LIKE',
      )
    ),
  );
  $query = new WP_Query($args);
  $total = $query->found_posts;
  wp_reset_postdata();
  wp_send_json($total);
}



function getProductsCount_FROM_WP()
{

  $args = array(
    'post_type'      => 'product',
    'post_status' => array('publish', 'draft'),
    'meta_query' => array(
      array(
        'key' => 'productUrl', //meta key name here
        'value' => 'aliexpress.com/item',
        'compare' => 'LIKE',
      )
    ),
  );
  $query = new WP_Query($args);
  $total = $query->found_posts;
  wp_reset_postdata();
  wp_send_json($total);
}





add_action('wp_ajax_get_categories', 'get_categories_FROMWP');
add_action('wp_ajax_nopriv_get_categories', 'get_categories_FROMWP');
add_action('wp_ajax_getProductsCountDraft', 'getCountOfProducts');
add_action('wp_ajax_nopriv_getProductsCountDraft', 'getCountOfProducts');
add_action('wp_ajax_getProductsCount', 'getProductsCount_FROM_WP');
add_action('wp_ajax_nopriv_getProductsCount', 'getProductsCount_FROM_WP');




// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 


function getProduct_FROMWP()
{
  $n0 = isset($_POST[base64_decode('cGFnZWQ=')]) ? sanitize_text_field($_POST[base64_decode('cGFnZWQ=')]) : '';
  $g1 = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdHNfcGVyX3BhZ2U=') => 20, base64_decode('cGFnZWQ=') => $n0, base64_decode('bWV0YV9xdWVyeQ==') => array(array(base64_decode('a2V5') => base64_decode('cHJvZHVjdFVybA=='), base64_decode('dmFsdWU=') => base64_decode('YWxpZXhwcmVzcy5jb20vaXRlbQ=='), base64_decode('Y29tcGFyZQ==') => base64_decode('TElLRQ=='),)));
  $g2 = new WP_Query($g1);
  $x3 = array();
  if ($g2->have_posts()) {
    while ($g2->have_posts()) : $g2->the_post();
      $s4 = get_the_ID();
      $y5 = new WC_Product($s4);
      if (has_post_thumbnail()) {
        $y6 = get_post_thumbnail_id();
        $v7 = $y6 ? wp_get_attachment_url($y6) : '';
      }
      $x3[] = array(base64_decode('c2t1') => $y5->get_sku(), base64_decode('aWQ=') => $s4, base64_decode('aW1hZ2U=') => $v7, base64_decode('dGl0bGU=') => $y5->get_title(), base64_decode('cHJvZHVjdFVybA==') => get_post_meta($s4, base64_decode('cHJvZHVjdFVybA=='), true), base64_decode('bGFzdFVwZGF0ZWQ=') => get_post_meta($s4, base64_decode('bGFzdFVwZGF0ZWQ='), true), base64_decode('c3RhdHVz') => $y5->get_status());
    endwhile;
  } else {
    echo __(base64_decode('Tm8gcHJvZHVjdHMgZm91bmQ='));
  }
  wp_reset_postdata();
  wp_send_json($x3);
}
function insertProductInWoocommerce()
{
  // $t8 = $_POST[base64_decode('bm9uY2U=')];
  // $t8 = $_POST[base64_decode('bm9uY2U=')];
  if (isset($_POST)) {
    $y9 = isset($_POST[base64_decode('c2t1')]) ? sanitize_text_field($_POST[base64_decode('c2t1')]) : '';
    $p10 = isset($_POST[base64_decode('aW1hZ2Vz')]) ? $_POST[base64_decode('aW1hZ2Vz')] : array();
    $o11 = isset($_POST[base64_decode('Y2F0ZWdvcmllcw==')]) ? $_POST[base64_decode('Y2F0ZWdvcmllcw==')] : array();
    $p12 = isset($_POST[base64_decode('dGl0bGU=')]) ? sanitize_text_field($_POST[base64_decode('dGl0bGU=')]) : '';
    $c13 = isset($_POST[base64_decode('ZGVzY3JpcHRpb24=')]) ? $_POST[base64_decode('ZGVzY3JpcHRpb24=')] : '';
    $t14 = isset($_POST[base64_decode('cHJvZHVjdFR5cGU=')]) ? sanitize_text_field($_POST[base64_decode('cHJvZHVjdFR5cGU=')]) : base64_decode('c2ltcGxl');
    $u15 = isset($_POST[base64_decode('cmVndWxhclByaWNl')]) ? sanitize_text_field($_POST[base64_decode('cmVndWxhclByaWNl')]) : base64_decode('MA==');
    $r16 = isset($_POST[base64_decode('c2FsZVByaWNl')]) ? sanitize_text_field($_POST[base64_decode('c2FsZVByaWNl')]) : base64_decode('MA==');
    $a17 = isset($_POST[base64_decode('cXVhbnRpdHk=')]) ? sanitize_text_field($_POST[base64_decode('cXVhbnRpdHk=')]) : base64_decode('MA==');
    $c18 = isset($_POST[base64_decode('cG9zdFN0YXR1cw==')]) ? sanitize_text_field($_POST[base64_decode('cG9zdFN0YXR1cw==')]) : base64_decode('ZHJhZnQ=');
    $l19 = isset($_POST[base64_decode('dmFyaWF0aW9ucw==')]) ? $_POST[base64_decode('dmFyaWF0aW9ucw==')] : array();
    $f20 = isset($_POST[base64_decode('YXR0cmlidXRlcw==')]) ? $_POST[base64_decode('YXR0cmlidXRlcw==')] : array();
    $j21 = isset($_POST[base64_decode('cHJvZHVjdFVybA==')]) ? sanitize_text_field($_POST[base64_decode('cHJvZHVjdFVybA==')]) : '';
    $u22 = isset($_POST[base64_decode('c2hvcnREZXNjcmlwdGlvbg==')]) ? sanitize_text_field($_POST[base64_decode('c2hvcnREZXNjcmlwdGlvbg==')]) : '';
    $w23 = isset($_POST[base64_decode('aW1wb3J0VmFyaWF0aW9uSW1hZ2Vz')]) ? sanitize_text_field($_POST[base64_decode('aW1wb3J0VmFyaWF0aW9uSW1hZ2Vz')]) : '';
    $c24 = isset($_POST[base64_decode('cmV2aWV3cw==')]) ? $_POST[base64_decode('cmV2aWV3cw==')] : array();
    $n25 = isset($_POST[base64_decode('dGFncw==')]) ? $_POST[base64_decode('dGFncw==')] : array();
    $h26 = array(base64_decode('YQ==') => array(base64_decode('aHJlZg==') => array(),), base64_decode('aW1n') => array(),);
    $u27 = html_entity_decode($c13);
    $s28 = get_option(base64_decode('aXNBbGxvd2VkVG9JbXBvcnQ='));
    if (isset($s28)) {
      $f29 = (int) $s28 + 1;
      update_option(base64_decode('aXNBbGxvd2VkVG9JbXBvcnQ='), $f29);
    } else {
      update_option(base64_decode('aXNBbGxvd2VkVG9JbXBvcnQ='), base64_decode('MQ=='));
    }
    if (null != get_option(base64_decode('aXNBbGxvd2VkVG9JbXBvcnQ=')) && (int) get_option(base64_decode('aXNBbGxvd2VkVG9JbXBvcnQ=')) > 30) {
      $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('eW91IGhhdmUgcmVhY2hlZCB0aGUgcGVybWl0dGVkIHVzYWdlIGxpbWl0IG9mIDMwIGltcG9ydHMgZm9yIHRoaXMgd2VlaywgeW91IGNhbiBpbXBvcnQgYWdhaW4gaW4gMSB3ZWVrIG9yIHlvdSBjYW4gdXBncmFkZSB0byBhIHByZW11aW0gcGxhbg=='), base64_decode('ZGF0YQ==') => '');
      wp_send_json($t30);
    }
    if (false) {
      $o31 = array(base64_decode('cG9zdF90aXRsZQ==') => $p12, base64_decode('cG9zdF9jb250ZW50') => $u27, base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdF9zdGF0dXM=') => $c18, base64_decode('bWV0YV9pbnB1dA==') => array(base64_decode('X3Zpc2liaWxpdHk=') => base64_decode('dmlzaWJsZQ=='), base64_decode('X3N0b2Nr') => isset($a17) ? absint($a17) : 0, base64_decode('X21hbmFnZV9zdG9jaw==') => base64_decode('eWVz'), base64_decode('X3JlZ3VsYXJfcHJpY2U=') => $u15, base64_decode('X3N0b2NrX3N0YXR1cw==') => base64_decode('aW5zdG9jaw=='), base64_decode('X3ByaWNl') => $r16, base64_decode('X3NrdQ==') => $y9));
      if ($r16) {
        $o31[base64_decode('bWV0YV9pbnB1dA==')][base64_decode('X3NhbGVfcHJpY2U=')] = $r16;
        $o31[base64_decode('bWV0YV9pbnB1dA==')][base64_decode('X3ByaWNl')] = $r16;
      }
      $b32 = wp_insert_post($o31);
      update_post_meta($b32, base64_decode('cHJvZHVjdFVybA=='), $j21);
      if (!is_wp_error($b32)) {
        $o33 = array(base64_decode('SUQ=') => $b32, base64_decode('cG9zdF9leGNlcnB0') => $u22,);
        wp_update_post($o33);
        wp_set_object_terms($b32, base64_decode('c2ltcGxl'), base64_decode('cHJvZHVjdF90eXBl'));
        if (is_array($o11) && count($o11)) {
          wp_set_post_terms($b32, $o11, base64_decode('cHJvZHVjdF9jYXQ='), true);
        }
        wp_send_json(base64_decode('b2sgb2s='));
      } else {
        $x34[base64_decode('bWVzc2FnZQ==')] = $b32->get_error_message();
        wp_send_json($x34);
      }
    } else {
      try {
        $y5 = new WC_Product_Variable();
        if (isset($p12)) {
          $y5->set_name($p12);
        }
        if (isset($c13)) {
          $y5->set_description($c13);
        }
        if (isset($u22)) {
          $y5->set_short_description($u22);
        }
        if (isset($y9)) {
          $y5->set_sku($y9);
        }
        if (isset($c18)) {
          $y5->set_status($c18);
        }
        if (is_array($o11) && count($o11)) {
          $y5->set_category_ids($o11);
        }
        save_product_images($y5, $p10);
        $h35 = array();
        if (is_array($f20) && count($f20)) {
          foreach ($f20 as $p36) {
            $f37 = $p36[base64_decode('dmFsdWVz')];
            $q38 = $p36[base64_decode('bmFtZQ==')];
            $k39 = $p36[base64_decode('dmFyaWF0aW9u')];
            $s40 = new WC_Product_Attribute();
            $s40->set_name($q38);
            $s40->set_options($f37);
            $s40->set_visible(1);
            if ($k39 == base64_decode('dHJ1ZQ==')) {
              $s40->set_variation(1);
            } else {
              $s40->set_variation(0);
            }
            array_push($h35, $s40);
          }
          $y5->set_attributes($h35);
        } else {
          $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('TWlzc2luZyBhdHRyaWJ1dGVzIG9yIHZhcmlhdGlvbnMsIGNvdWxkIG5vdCBpbnNlcnQgcHJvZHVjdCA='), base64_decode('ZGF0YQ==') => '');
          wp_send_json($t30);
        }
      } catch (Exception $h41) {
        $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('RXJyb3IgcmVjZWl2ZWQgd2hlbiB0cnlpbmcgdG8gaW5zZXJ0IHRoZSBwcm9kdWN0') . $h41->getMessage(), base64_decode('ZGF0YQ==') => '');
        wp_send_json($t30);
      }
      try {
        $b32 = $y5->save();
      } catch (Exception $i42) {
        $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('RXJyb3IgcmVjZWl2ZWQgd2hlbiB0cnlpbmcgdG8gaW5zZXJ0IHRoZSBwcm9kdWN0') . $h41->getMessage(), base64_decode('ZGF0YQ==') => '');
        wp_send_json($t30);
      }
      if (isset($j21)) {
        update_post_meta($b32, base64_decode('cHJvZHVjdFVybA=='), $j21);
      }
      if (isset($b32) && isset($n25) && count($n25)) {
        wp_set_object_terms($b32, $n25, base64_decode('cHJvZHVjdF90YWc='));
      }
      if (isset($b32) && isset($c24) && count($c24)) {
        foreach ($c24 as $z43) {
          $u44 = wp_insert_comment(array(base64_decode('Y29tbWVudF9wb3N0X0lE') => sanitize_text_field($b32), base64_decode('Y29tbWVudF9hdXRob3I=') => sanitize_text_field($z43[base64_decode('dXNlcm5hbWU=')]), base64_decode('Y29tbWVudF9hdXRob3JfZW1haWw=') => sanitize_text_field($z43[base64_decode('ZW1haWw=')]), base64_decode('Y29tbWVudF9hdXRob3JfdXJs') => '', base64_decode('Y29tbWVudF9jb250ZW50') => $z43[base64_decode('cmV2aWV3')], base64_decode('Y29tbWVudF90eXBl') => '', base64_decode('Y29tbWVudF9wYXJlbnQ=') => 0, base64_decode('dXNlcl9pZA==') => 5, base64_decode('Y29tbWVudF9hdXRob3JfSVA=') => '', base64_decode('Y29tbWVudF9hZ2VudA==') => '', base64_decode('Y29tbWVudF9kYXRl') => $z43[base64_decode('ZGF0ZWNyZWF0aW9u')], base64_decode('Y29tbWVudF9hcHByb3ZlZA==') => 1,));
          update_comment_meta($u44, base64_decode('cmF0aW5n'), sanitize_text_field($z43[base64_decode('cmF0aW5n')]));
        }
      }
      if (is_array($l19) && count($l19)) {
        foreach ($l19 as $j45) {
          $r46 = $j45[base64_decode('YXR0cmlidXRlc1ZhcmlhdGlvbnM=')];
          $i47 = new WC_Product_Variation();
          $i47->set_parent_id($b32);
          if (!empty($j45[base64_decode('U0tV')])) {
            $i47->set_sku($j45[base64_decode('U0tV')]);
          }
          if (!empty(sanitize_text_field($j45[base64_decode('cmVndWxhclByaWNl')]))) {
            $i47->set_regular_price($j45[base64_decode('cmVndWxhclByaWNl')]);
          }
          if (!empty(sanitize_text_field($j45[base64_decode('c2FsZVByaWNl')]))) {
            $i47->set_sale_price($j45[base64_decode('c2FsZVByaWNl')]);
          }
          $o48 = sanitize_text_field($j45[base64_decode('YXZhaWxRdWFudGl0eQ==')]);
          if (isset($o48)) {
            $i47->set_manage_stock(true);
            $i47->set_stock_quantity($o48);
            $i47->set_stock_status(base64_decode('aW5zdG9jaw=='));
          }
          $e49 = array();
          foreach ($r46 as $o50) {
            $e49[$o50[base64_decode('bmFtZQ==')]] = $o50[base64_decode('dmFsdWU=')];
            $h51 = array();
            if (($w23 == base64_decode('dHJ1ZQ=='))) {
              $f52 = $o50[base64_decode('aW1hZ2U=')];
              if (isset($f52)) {
                $i53 = false;
                foreach ($h51 as $a54) {
                  if ($a54->$t55 == $f52) {
                    $i53 = $a54->$t56;
                    break;
                  }
                }
                if ($i53 != false) {
                  $i47->set_image_id($i53);
                } else {
                  $o57 = save_single_variation_image($i47, $f52);
                  array_push($h51, array(base64_decode('aW1hZ2U=') => $f52, base64_decode('aWQ=') => $o57));
                  if (isset($o57)) {
                    $i47->set_image_id($o57);
                  }
                }
              }
            }
          };
          $i47->set_attributes($e49);
          try {
            $i47->save();
          } catch (Exception $i42) {
            echo $i42;
          }
        }
      }
      $t30 = array(base64_decode('ZXJyb3I=') => false, base64_decode('ZXJyb3JfbXNn') => '', base64_decode('ZGF0YQ==') => base64_decode('UHJvZHVjdCBpbnNlcnRlZCBzdWNjZXNzZnVsbHk='));
      wp_send_json($t30);
    }
  }
}
function getProductsDraft()
{
  $n0 = isset($_POST[base64_decode('cGFnZWQ=')]) ? sanitize_text_field($_POST[base64_decode('cGFnZWQ=')]) : '';
  $g1 = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdHNfcGVyX3BhZ2U=') => 20, base64_decode('cGFnZWQ=') => $n0, base64_decode('bWV0YV9xdWVyeQ==') => array(array(base64_decode('a2V5') => base64_decode('aXNFeHBpcmVk'), base64_decode('dmFsdWU=') => base64_decode('dHJ1ZQ=='), base64_decode('Y29tcGFyZQ==') => base64_decode('TElLRQ=='),)));
  $g2 = new WP_Query($g1);
  $x3 = array();
  if ($g2->have_posts()) {
    while ($g2->have_posts()) : $g2->the_post();
      $s4 = get_the_ID();
      $y5 = new WC_Product($s4);
      if (has_post_thumbnail()) {
        $y6 = get_post_thumbnail_id();
        $v7 = $y6 ? wp_get_attachment_url($y6) : '';
      }
      $x3[] = array(base64_decode('c2t1') => $y5->get_sku(), base64_decode('aWQ=') => $s4, base64_decode('aW1hZ2U=') => $v7, base64_decode('dGl0bGU=') => $y5->get_title(), base64_decode('cHJvZHVjdFVybA==') => get_post_meta($s4, base64_decode('cHJvZHVjdFVybA=='), true));
    endwhile;
  } else {
    echo __(base64_decode('Tm8gcHJvZHVjdHMgZm91bmQ='));
  }
  wp_reset_postdata();
  wp_send_json($x3);
}
function setProductToDraft()
{
  $b32 = isset($_POST[base64_decode('cG9zdF9pZA==')]) ? sanitize_text_field($_POST[base64_decode('cG9zdF9pZA==')]) : '';
  if (isset($b32)) {
    $p58 = update_post_meta($b32, base64_decode('aXNFeHBpcmVk'), base64_decode('dHJ1ZQ=='));
    $y59 = array(base64_decode('SUQ=') => $b32, base64_decode('cG9zdF9zdGF0dXM=') => base64_decode('ZHJhZnQ='));
    wp_update_post($y59);
  }
  wp_send_json(array(base64_decode('cmVzdWx0') => $p58));
}
function sync_all_products()
{
  $g1 = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdHNfcGVyX3BhZ2U=') => 20, base64_decode('bWV0YV9xdWVyeQ==') => array(array(base64_decode('a2V5') => base64_decode('cHJvZHVjdFVybA=='), base64_decode('dmFsdWU=') => base64_decode('YWxpZXhwcmVzcy5jb20vaXRlbQ=='), base64_decode('Y29tcGFyZQ==') => base64_decode('TElLRQ=='),)),);
  $g2 = new WP_Query($g1);
  $x3 = array();
  if ($g2->have_posts()) {
    while ($g2->have_posts()) : $g2->the_post();
      $s4 = get_the_ID();
      array_push($x3, get_post_meta($s4, base64_decode('cHJvZHVjdFVybA=='), true));
    endwhile;
  } else {
    echo __(base64_decode('Tm8gcHJvZHVjdHMgZm91bmQ='));
  }
  wp_reset_postdata();
  $c60 = curl_init();
  $e61 = json_encode(array(base64_decode('cHJvZHVjdFVybHM=') => $x3, base64_decode('Y3VycmVuY3k=') => base64_decode('RVVS')));
  curl_setopt($c60, CURLOPT_URL, base64_decode('aHR0cHM6Ly9lcy5hbGlleHByZXNzLmNvbS9hZWdsb2RldGFpbHdlYi9hcGkvbG9naXN0aWNzL2ZyZWlnaHQ/cHJvZHVjdElkPTMyNjI3NDkwNDkzJmNvdW50PTEmbWluUHJpY2U9MS45OCZtYXhQcmljZT0yLjUxJnNlbmRHb29kc0NvdW50cnk9Q04mY291bnRyeT1VUyZwcm92aW5jZUNvZGU9JmNpdHlDb2RlPSZ0cmFkZUN1cnJlbmN5PUVVUiZzZWxsZXJBZG1pblNlcT0yMjIzNjcwMDImdXNlclNjZW5lPVBDX0RFVEFJTF9TSElQUElOR19QQU5FTA=='));
  curl_setopt($c60, CURLOPT_HTTPHEADER, array(base64_decode('Q29udGVudC1UeXBlOmFwcGxpY2F0aW9uL2pzb24=')));
  curl_setopt($c60, CURLOPT_RETURNTRANSFER, true);
  $o62 = curl_exec($c60);
  curl_close($c60);
  wp_send_json($o62);
}
function getNewProductDetails()
{
  $j21 = isset($_POST[base64_decode('cHJvZHVjdFVybA==')]) ? sanitize_text_field($_POST[base64_decode('cHJvZHVjdFVybA==')]) : '';
  $s63 = isset($_POST[base64_decode('Y3VycmVuY3k=')]) ? sanitize_text_field($_POST[base64_decode('Y3VycmVuY3k=')]) : '';
  $x3 = array($j21);
  $c60 = curl_init();
  $e61 = json_encode(array(base64_decode('cHJvZHVjdFVybHM=') => $x3, base64_decode('Y3VycmVuY3k=') => $s63));
  curl_setopt($c60, CURLOPT_URL, base64_decode('aHR0cHM6Ly93b29zaGFyay53ZWJzaXRlOjgwMDIvZ2V0VmFyaWF0aW9uc0Zyb21BcGlVc2luZ09VckFsaUV4cHJlc3NBcGk='));
  curl_setopt($c60, CURLOPT_POSTFIELDS, $e61);
  curl_setopt($c60, CURLOPT_HTTPHEADER, array(base64_decode('Q29udGVudC1UeXBlOmFwcGxpY2F0aW9uL2pzb24=')));
  curl_setopt($c60, CURLOPT_RETURNTRANSFER, true);
  $o62 = curl_exec($c60);
  $v64 = curl_getinfo($c60, CURLINFO_HTTP_CODE);
  curl_close($c60);
  if (isset($v64) && $v64 == 200) {
    $g65 = json_decode($o62, true);
    wp_send_json($g65);
  } else if (isset($v64) && $v64 == 477) {
    $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('cHJvZHVjdC1ub3QtZm91bmQ='), base64_decode('ZGF0YQ==') => '');
    wp_send_json($t30);
  } else if (isset($v64) && $v64 == 499) {
    $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('ZXJyb3IgcGFyc2luZyBjYXRjaGVkIGVycm9y'), base64_decode('ZGF0YQ==') => '');
    wp_send_json($t30);
  } else if (isset($v64) && $v64 == 488) {
    $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('b3RoZXIgZXJyb3IgZnJvbSB3b29zaGFyayBzZXJ2ZXI='), base64_decode('ZGF0YQ==') => '');
    wp_send_json($t30);
  } else {
    $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('dW5rbm93IGVycm9yLCBwbGVhc2UgY29udGFjdCB3b29zaGFyaw=='), base64_decode('ZGF0YQ==') => '');
    wp_send_json($t30);
  }
}
function updateProductVariations()
{
  $u66 = array();
  $r67 = array();
  $z68 = array();
  $c69 = isset($_POST[base64_decode('dXBkYXRlVmFyaWF0aW9uc09uU2VydmVy')]) ? $_POST[base64_decode('dXBkYXRlVmFyaWF0aW9uc09uU2VydmVy')] : array();
  if (isset($c69) && count($c69)) {
    foreach ($c69 as $y5) {
      if (isset($y5[base64_decode('dmFyaWF0aW9uX2lk')])) {
        $i70 = false;
        $n71 = false;
        $z72 = false;
        if (isset($y5[base64_decode('c2FsZVByaWNl')])) {
          $i70 = update_post_meta($y5[base64_decode('dmFyaWF0aW9uX2lk')], base64_decode('X3NhbGVfcHJpY2U='), $y5[base64_decode('c2FsZVByaWNl')]);
        }
        if (isset($y5[base64_decode('cmVndWxhclByaWNl')])) {
          $n71 = update_post_meta($y5[base64_decode('dmFyaWF0aW9uX2lk')], base64_decode('X3JlZ3VsYXJfcHJpY2U='), $y5[base64_decode('cmVndWxhclByaWNl')]);
          update_post_meta($y5[base64_decode('dmFyaWF0aW9uX2lk')], base64_decode('X3ByaWNl'), $y5[base64_decode('cmVndWxhclByaWNl')]);
          wc_delete_product_transients($y5[base64_decode('dmFyaWF0aW9uX2lk')]);
        }
        if (isset($y5[base64_decode('YXZhaWxRdWFudGl0eQ==')]) && $y5[base64_decode('YXZhaWxRdWFudGl0eQ==')] > -1) {
          $z72 = update_post_meta($y5[base64_decode('dmFyaWF0aW9uX2lk')], base64_decode('X3N0b2Nr'), $y5[base64_decode('YXZhaWxRdWFudGl0eQ==')]);
        }
        if (isset($y5[base64_decode('YXZhaWxRdWFudGl0eQ==')]) && $y5[base64_decode('YXZhaWxRdWFudGl0eQ==')] == 0) {
          array_push($z68, $y5[base64_decode('dmFyaWF0aW9uX2lk')]);
        } else {
          array_push($r67, $y5[base64_decode('dmFyaWF0aW9uX2lk')]);
        }
      }
    }
  }
  $t30 = array(base64_decode('ZXJyb3I=') => false, base64_decode('ZXJyb3JfbXNn') => '', base64_decode('ZGF0YQ==') => array(base64_decode('ZXJyb3I=') => $u66, base64_decode('c3VjY2Vzcw==') => $r67, base64_decode('b3V0T2ZTdG9jaw==') => $z68));
  wp_send_json($t30);
}
function getOldProductDetails()
{
  $b32 = isset($_POST[base64_decode('cG9zdF9pZA==')]) ? sanitize_text_field($_POST[base64_decode('cG9zdF9pZA==')]) : '';
  $y5 = wc_get_product($b32);
  $p73 = $y5->get_available_variations();
  wp_send_json($p73);
}
function searchProductBySku()
{
  $g74 = isset($_POST[base64_decode('c2VhcmNoU2t1VmFsdWU=')]) ? sanitize_text_field($_POST[base64_decode('c2VhcmNoU2t1VmFsdWU=')]) : '';
  if (isset($g74)) {
    $g1 = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdHNfcGVyX3BhZ2U=') => 1, base64_decode('bWV0YV9xdWVyeQ==') => array(array(base64_decode('a2V5') => base64_decode('X3NrdQ=='), base64_decode('dmFsdWU=') => $g74, base64_decode('Y29tcGFyZQ==') => base64_decode('TElLRQ==')), array(base64_decode('a2V5') => base64_decode('cHJvZHVjdFVybA=='), base64_decode('dmFsdWU=') => base64_decode('YWxpZXhwcmVzcy5jb20vaXRlbQ=='), base64_decode('Y29tcGFyZQ==') => base64_decode('TElLRQ=='),)));
    $g2 = new WP_Query($g1);
    $x3 = array();
    if ($g2->have_posts()) {
      while ($g2->have_posts()) : $g2->the_post();
        $s4 = get_the_ID();
        $y5 = new WC_Product($s4);
        if (has_post_thumbnail()) {
          $y6 = get_post_thumbnail_id();
          $v7 = $y6 ? wp_get_attachment_url($y6) : '';
        }
        $x3[] = array(base64_decode('c2t1') => $y5->get_sku(), base64_decode('aWQ=') => $s4, base64_decode('aW1hZ2U=') => $v7, base64_decode('dGl0bGU=') => $y5->get_title(), base64_decode('cHJvZHVjdFVybA==') => get_post_meta($s4, base64_decode('cHJvZHVjdFVybA=='), true));
      endwhile;
    } else {
      echo __(base64_decode('Tm8gcHJvZHVjdHMgZm91bmQ='));
    }
    wp_reset_postdata();
    wp_send_json($x3);
  } else {
    $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('Y2Fubm90IGZpbmQgcmVzdWx0IGZvciB0aGUgaW50cm9kdWNlZCBza3UgdmFsdWUsIHBsZWFzZSBtYWtlIHN1cmUgdGhlIHByb2R1Y3QgaXMgaW1wb3J0ZWQgdXNpbmcgd29vc2hhcms='), base64_decode('ZGF0YQ==') => '');
    wp_send_json($t30);
  }
}
function removeProductFromShop()
{
  $b32 = isset($_POST[base64_decode('cG9zdF9pZA==')]) ? sanitize_text_field($_POST[base64_decode('cG9zdF9pZA==')]) : '';
  if (isset($b32)) {
    $u75 = wp_delete_post($b32);
    if ($u75 != false && isset($u75)) {
      $t30 = array(base64_decode('ZXJyb3I=') => false, base64_decode('ZXJyb3JfbXNn') => '', base64_decode('ZGF0YQ==') => base64_decode('cmVtb3ZlZCBzdWNjZXNzZnVsbHk='));
      wp_send_json($t30);
    } else {
      $t30 = array(base64_decode('ZXJyb3I=') => trye, base64_decode('ZXJyb3JfbXNn') => base64_decode('ZXJyb3Igd2hpbGUgcmVtb3ZpbmcgdGhlIHByb2R1Y3Q='), base64_decode('ZGF0YQ==') => '');
      wp_send_json($t30);
    }
  }
}
function insertReviewsIntoProduct()
{
  $b32 = isset($_POST[base64_decode('cG9zdF9pZA==')]) ? sanitize_text_field($_POST[base64_decode('cG9zdF9pZA==')]) : '';
  $c24 = isset($_POST[base64_decode('cmV2aWV3cw==')]) ? ($_POST[base64_decode('cmV2aWV3cw==')]) : array();
  $o76 = array();
  if (isset($b32) && isset($c24) && count($c24)) {
    foreach ($c24 as $z43) {
      $u44 = wp_insert_comment(array(base64_decode('Y29tbWVudF9wb3N0X0lE') => sanitize_text_field($b32), base64_decode('Y29tbWVudF9hdXRob3I=') => sanitize_text_field($z43[base64_decode('dXNlcm5hbWU=')]), base64_decode('Y29tbWVudF9hdXRob3JfZW1haWw=') => sanitize_text_field($z43[base64_decode('ZW1haWw=')]), base64_decode('Y29tbWVudF9hdXRob3JfdXJs') => '', base64_decode('Y29tbWVudF9jb250ZW50') => sanitize_text_field($z43[base64_decode('cmV2aWV3')]), base64_decode('Y29tbWVudF90eXBl') => '', base64_decode('Y29tbWVudF9wYXJlbnQ=') => 0, base64_decode('dXNlcl9pZA==') => 5, base64_decode('Y29tbWVudF9hdXRob3JfSVA=') => '', base64_decode('Y29tbWVudF9hZ2VudA==') => '', base64_decode('Y29tbWVudF9kYXRl') => date(base64_decode('WS1tLWQgSDppOnM=')), base64_decode('Y29tbWVudF9hcHByb3ZlZA==') => 1,));
      $x34 = update_comment_meta($u44, base64_decode('cmF0aW5n'), sanitize_text_field($z43[base64_decode('cmF0aW5n')]));
      if ($x34 != false && isset($x34)) {
        array_push($o76, $u44);
      }
    }
    wp_send_json(array(base64_decode('aW5zZXJ0ZWRTdWNjZXNzZnVsbHk=') => $o76));
  }
}
function getAlreadyImportedProducts()
{
  $j77 = isset($_POST[base64_decode('bGlzdE9mU2t1cw==')]) ? ($_POST[base64_decode('bGlzdE9mU2t1cw==')]) : array();
  if (isset($j77) && count($j77)) {
    $g1 = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdHNfcGVyX3BhZ2U=') => 40, base64_decode('bWV0YV9xdWVyeQ==') => array(array(base64_decode('a2V5') => base64_decode('X3NrdQ=='), base64_decode('dmFsdWU=') => $j77, base64_decode('Y29tcGFyZQ==') => base64_decode('SU4=')), array(base64_decode('a2V5') => base64_decode('cHJvZHVjdFVybA=='), base64_decode('dmFsdWU=') => base64_decode('YWxpZXhwcmVzcy5jb20vaXRlbQ=='), base64_decode('Y29tcGFyZQ==') => base64_decode('TElLRQ=='),)));
    $g2 = new WP_Query($g1);
    $x3 = array();
    if ($g2->have_posts()) {
      while ($g2->have_posts()) : $g2->the_post();
        $s4 = get_the_ID();
        $y5 = new WC_Product($s4);
        $x3[] = array(base64_decode('c2t1') => $y5->get_sku(), base64_decode('aWQ=') => $s4, base64_decode('dGl0bGU=') => $y5->get_title(), base64_decode('cHJvZHVjdFVybA==') => get_post_meta($s4, base64_decode('cHJvZHVjdFVybA=='), true));
      endwhile;
    } else {
      echo __(base64_decode('Tm8gcHJvZHVjdHMgZm91bmQ='));
    }
    wp_reset_postdata();
    wp_send_json($x3);
  }
}
function getSKuAbdUrlByCategory()
{
  $k78 = isset($_POST[base64_decode('Y2F0ZWdvcnlJZA==')]) ? ($_POST[base64_decode('Y2F0ZWdvcnlJZA==')]) : array();
  if (isset($k78)) {
    $g1 = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdHNfcGVyX3BhZ2U=') => -1, base64_decode('cG9zdF9zdGF0dXM=') => array(base64_decode('cHVibGlzaA==')), base64_decode('bWV0YV9xdWVyeQ==') => array(array(base64_decode('a2V5') => base64_decode('cHJvZHVjdFVybA=='), base64_decode('dmFsdWU=') => base64_decode('YWxpZXhwcmVzcy5jb20vaXRlbQ=='), base64_decode('Y29tcGFyZQ==') => base64_decode('TElLRQ=='),)), base64_decode('dGF4X3F1ZXJ5') => array(array(base64_decode('dGF4b25vbXk=') => base64_decode('cHJvZHVjdF9jYXQ='), base64_decode('ZmllbGQ=') => base64_decode('dGVybV9pZA=='), base64_decode('dGVybXM=') => $k78, base64_decode('b3BlcmF0b3I=') => base64_decode('SU4='))));
    $g2 = new WP_Query($g1);
    $x3 = array();
    if ($g2->have_posts()) {
      while ($g2->have_posts()) : $g2->the_post();
        $s4 = get_the_ID();
        $y5 = new WC_Product($s4);
        $x3[] = array(base64_decode('c2t1') => $y5->get_sku(), base64_decode('aWQ=') => $s4, base64_decode('cHJvZHVjdFVybA==') => get_post_meta($s4, base64_decode('cHJvZHVjdFVybA=='), true));
      endwhile;
    } else {
      echo __(base64_decode('Tm8gcHJvZHVjdHMgZm91bmQ='));
    }
    wp_reset_postdata();
    wp_send_json($x3);
  }
}
function getOrders()
{
  $y79 = new WC_Order_Query(array(base64_decode('bGltaXQ=') => 10, base64_decode('b3JkZXJieQ==') => base64_decode('ZGF0ZQ=='), base64_decode('b3JkZXI=') => base64_decode('REVTQw=='), base64_decode('cmV0dXJu') => base64_decode('aWRz'),));
  $o80 = $y79->get_orders();
  wp_send_json($o80);
}
function searchCategoryByName()
{
  $a81 = isset($_POST[base64_decode('c2VhcmNoQ2F0ZWdvcnlCeU5hbWVJbnB1dA==')]) ? ($_POST[base64_decode('c2VhcmNoQ2F0ZWdvcnlCeU5hbWVJbnB1dA==')]) : array();
  $l82 = get_terms(base64_decode('Y2F0ZWdvcnk='), array(base64_decode('c2VhcmNo') => $a81));
  wp_send_json($l82);
}
function upload_image($u83)
{
  include_once(ABSPATH . base64_decode('d3AtYWRtaW4vaW5jbHVkZXMvaW1hZ2UucGhw'));
  $b84 = end(explode(base64_decode('Lw=='), getimagesize($u83)[base64_decode('bWltZQ==')]));
  $t85 = date(base64_decode('ZG1Z')) . '' . (int) microtime(true);
  $s86 = $t85 . base64_decode('Lg==') . $b84;
  $n87 = wp_upload_dir();
  $e88 = $n87[base64_decode('cGF0aA==')] . base64_decode('Lw==') . $s86;
  $o89 = file_get_contents($u83);
  $r90 = fopen($e88, base64_decode('dw=='));
  fwrite($r90, $o89);
  fclose($r90);
  $g91 = wp_check_filetype(basename($s86), null);
  $z92 = array(base64_decode('cG9zdF9taW1lX3R5cGU=') => $g91[base64_decode('dHlwZQ==')], base64_decode('cG9zdF90aXRsZQ==') => $s86, base64_decode('cG9zdF9jb250ZW50') => '', base64_decode('cG9zdF9zdGF0dXM=') => base64_decode('aW5oZXJpdA=='));
  $w93 = wp_insert_attachment($z92, $e88);
  return $w93;
}
function save_product_images($y5, $p10)
{
  if (is_array($p10)) {
    $x94 = array();
    foreach ($p10 as $u95 => $v7) {
      if (isset($v7)) {
        $a96 = wc_rest_upload_image_from_url(esc_url_raw($v7));
        if (is_wp_error($a96)) {
          if (!apply_filters(base64_decode('d29vY29tbWVyY2VfcmVzdF9zdXBwcmVzc19pbWFnZV91cGxvYWRfZXJyb3I='), false, $a96, $y5->get_id(), $p10)) {
            throw new WC_REST_Exception(base64_decode('d29vY29tbWVyY2VfcHJvZHVjdF9pbWFnZV91cGxvYWRfZXJyb3I='), $a96->get_error_message(), 400);
          } else {
            continue;
          }
        }
        $o97 = wc_rest_set_uploaded_image_as_attachment($a96, $y5->get_id());
      }
      if ($u95 == 0) {
        $y5->set_image_id($o97);
      } else {
        array_push($x94, $o97);
      }
    }
    if (!empty($x94)) {
      $y5->set_gallery_image_ids($x94);
    }
  } else {
    $y5->set_image_id('');
    $y5->set_gallery_image_ids(array());
  }
  return $y5;
}
function save_single_variation_image($y5, $v7)
{
  $x94 = array();
  if (isset($v7)) {
    $a96 = wc_rest_upload_image_from_url(esc_url_raw($v7));
    if (is_wp_error($a96)) {
      if (!apply_filters(base64_decode('d29vY29tbWVyY2VfcmVzdF9zdXBwcmVzc19pbWFnZV91cGxvYWRfZXJyb3I='), false, $a96, $y5->get_id(), $v7)) {
        throw new WC_REST_Exception(base64_decode('d29vY29tbWVyY2VfcHJvZHVjdF9pbWFnZV91cGxvYWRfZXJyb3I='), $a96->get_error_message(), 400);
      }
    }
    $o97 = wc_rest_set_uploaded_image_as_attachment($a96, $y5->get_id());
  }
  $y5->set_image_id($o97);
  return $o97;
}
function searchProductByIdReviews()
{
  $g74 = isset($_POST[base64_decode('c2VhcmNoU2t1VmFsdWU=')]) ? sanitize_text_field($_POST[base64_decode('c2VhcmNoU2t1VmFsdWU=')]) : '';
  if (isset($g74)) {
    $g1 = array(base64_decode('cG9zdF90eXBl') => base64_decode('cHJvZHVjdA=='), base64_decode('cG9zdHNfcGVyX3BhZ2U=') => 1, base64_decode('cA==') => $g74);
    $g2 = new WP_Query($g1);
    $x3 = array();
    if ($g2->have_posts()) {
      while ($g2->have_posts()) : $g2->the_post();
        $s4 = get_the_ID();
        $y5 = new WC_Product($s4);
        if (has_post_thumbnail()) {
          $y6 = get_post_thumbnail_id();
          $v7 = $y6 ? wp_get_attachment_url($y6) : '';
        }
        $x3[] = array(base64_decode('c2t1') => $y5->get_sku(), base64_decode('aWQ=') => $s4, base64_decode('aW1hZ2U=') => $v7, base64_decode('dGl0bGU=') => $y5->get_title(), base64_decode('cHJvZHVjdFVybA==') => get_post_meta($s4, base64_decode('cHJvZHVjdFVybA=='), true), base64_decode('bGFzdFVwZGF0ZWQ=') => get_post_meta($s4, base64_decode('bGFzdFVwZGF0ZWQ='), true), base64_decode('c3RhdHVz') => $y5->get_status());
      endwhile;
    } else {
      echo __(base64_decode('Tm8gcHJvZHVjdHMgZm91bmQ='));
    }
    wp_reset_postdata();
    wp_send_json($x3);
  } else {
    $t30 = array(base64_decode('ZXJyb3I=') => true, base64_decode('ZXJyb3JfbXNn') => base64_decode('Y2Fubm90IGZpbmQgcmVzdWx0IGZvciB0aGUgaW50cm9kdWNlZCBza3UgdmFsdWUsIHBsZWFzZSBtYWtlIHN1cmUgdGhlIHByb2R1Y3QgaXMgaW1wb3J0ZWQgdXNpbmcgd29vc2hhcms='), base64_decode('ZGF0YQ==') => '');
    wp_send_json($t30);
  }
}
function saveOptionsDB()
{
  $q98 = isset($_POST[base64_decode('aXNTaGlwcGluZ0Nvc3RFbmFibGVk')]) ? sanitize_text_field($_POST[base64_decode('aXNTaGlwcGluZ0Nvc3RFbmFibGVk')]) : base64_decode('Tg==');
  $h99 = isset($_POST[base64_decode('aXNFbmFibGVBdXRvbWF0aWNVcGRhdGVGb3JBdmFpbGFiaWxpdHk=')]) ? sanitize_text_field($_POST[base64_decode('aXNFbmFibGVBdXRvbWF0aWNVcGRhdGVGb3JBdmFpbGFiaWxpdHk=')]) : base64_decode('Tg==');
  $d100 = isset($_POST[base64_decode('aXNVcGRhdGVSZWd1bGFyUHJpY2U=')]) ? sanitize_text_field($_POST[base64_decode('aXNVcGRhdGVSZWd1bGFyUHJpY2U=')]) : base64_decode('Tg==');
  $t101 = isset($_POST[base64_decode('aXNVcGRhdGVTYWxlUHJpY2U=')]) ? sanitize_text_field($_POST[base64_decode('aXNVcGRhdGVTYWxlUHJpY2U=')]) : base64_decode('Tg==');
  $b102 = isset($_POST[base64_decode('aXNVcGRhdGVTdG9jaw==')]) ? sanitize_text_field($_POST[base64_decode('aXNVcGRhdGVTdG9jaw==')]) : base64_decode('Tg==');
  $u103 = isset($_POST[base64_decode('cHJpY2VGb3JtdWxhSW50ZXJ2YWxscw==')]) ? $_POST[base64_decode('cHJpY2VGb3JtdWxhSW50ZXJ2YWxscw==')] : array();
  $r104 = isset($_POST[base64_decode('b25seVB1Ymxpc2hQcm9kdWN0V2lsbFN5bmM=')]) ? sanitize_text_field($_POST[base64_decode('b25seVB1Ymxpc2hQcm9kdWN0V2lsbFN5bmM=')]) : base64_decode('Tg==');
  $w105 = isset($_POST[base64_decode('ZW5hYmxlQXV0b21hdGljVXBkYXRlcw==')]) ? sanitize_text_field($_POST[base64_decode('ZW5hYmxlQXV0b21hdGljVXBkYXRlcw==')]) : base64_decode('Tg==');
  $s106 = isset($_POST[base64_decode('YXBwbHlQcmljZUZvcm11bGFBdXRvbWF0aWNVcGRhdGU=')]) ? sanitize_text_field($_POST[base64_decode('YXBwbHlQcmljZUZvcm11bGFBdXRvbWF0aWNVcGRhdGU=')]) : base64_decode('Tg==');
  $x107 = isset($_POST[base64_decode('c3luY1JlZ3VsYXJQcmljZQ==')]) ? sanitize_text_field($_POST[base64_decode('c3luY1JlZ3VsYXJQcmljZQ==')]) : base64_decode('Tg==');
  $g108 = isset($_POST[base64_decode('c3luY1NhbGVQcmljZQ==')]) ? sanitize_text_field($_POST[base64_decode('c3luY1NhbGVQcmljZQ==')]) : base64_decode('Tg==');
  $q109 = isset($_POST[base64_decode('c3luY1N0b2Nr')]) ? sanitize_text_field($_POST[base64_decode('c3luY1N0b2Nr')]) : base64_decode('Tg==');
  $b110 = isset($_POST[base64_decode('X3NhdmVkQ29uZmlndXJhdGlvbg==')]) ? $_POST[base64_decode('X3NhdmVkQ29uZmlndXJhdGlvbg==')] : null;
  if (isset($b110)) {
    update_option(base64_decode('X3NhdmVkQ29uZmlndXJhdGlvbg=='), $b110);
  }
  if (isset($x107)) {
    update_option(base64_decode('c3luY1JlZ3VsYXJQcmljZQ=='), $x107);
  }
  if (isset($g108)) {
    update_option(base64_decode('c3luY1NhbGVQcmljZQ=='), $g108);
  }
  if (isset($q109)) {
    update_option(base64_decode('c3luY1N0b2Nr'), $q109);
  }
  if (isset($u103)) {
    update_option(base64_decode('cHJpY2VGb3JtdWxhSW50ZXJ2YWxscw=='), $u103);
  }
  if (isset($q98)) {
    update_option(base64_decode('aXNTaGlwcGluZ0Nvc3RFbmFibGVk'), $q98);
  }
  if (isset($h99)) {
    update_option(base64_decode('aXNFbmFibGVBdXRvbWF0aWNVcGRhdGVGb3JBdmFpbGFiaWxpdHk='), $h99);
  }
  if (isset($d100)) {
    update_option(base64_decode('aXNVcGRhdGVSZWd1bGFyUHJpY2U='), $d100);
  }
  if (isset($t101)) {
    update_option(base64_decode('aXNVcGRhdGVTYWxlUHJpY2U='), $t101);
  }
  if (isset($b102)) {
    update_option(base64_decode('aXNVcGRhdGVTdG9jaw=='), $b102);
  }
  if (isset($r104)) {
    update_option(base64_decode('b25seVB1Ymxpc2hQcm9kdWN0V2lsbFN5bmM='), $r104);
  }
  if (isset($w105)) {
    update_option(base64_decode('ZW5hYmxlQXV0b21hdGljVXBkYXRlcw=='), $w105);
  }
  if (isset($s106)) {
    update_option(base64_decode('YXBwbHlQcmljZUZvcm11bGFBdXRvbWF0aWNVcGRhdGU='), $s106);
  }
  wp_send_json($q98);
}
add_action(base64_decode('d3BfYWpheF93b29zaGFyay1pbnNlcnQtcHJvZHVjdA=='), base64_decode('aW5zZXJ0UHJvZHVjdEluV29vY29tbWVyY2U='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfd29vc2hhcmstaW5zZXJ0LXByb2R1Y3Q='), base64_decode('aW5zZXJ0UHJvZHVjdEluV29vY29tbWVyY2U='));
add_action(base64_decode('d3BfYWpheF9nZXRfcHJvZHVjdHM='), base64_decode('Z2V0UHJvZHVjdF9GUk9NV1A='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0X3Byb2R1Y3Rz'), base64_decode('Z2V0UHJvZHVjdF9GUk9NV1A='));
add_action(base64_decode('d3BfYWpheF9zZWFyY2gtY2F0ZWdvcnktYnktbmFtZQ=='), base64_decode('c2VhcmNoQ2F0ZWdvcnlCeU5hbWU='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfc2VhcmNoLWNhdGVnb3J5LWJ5LW5hbWU='), base64_decode('c2VhcmNoQ2F0ZWdvcnlCeU5hbWU='));
add_action(base64_decode('d3BfYWpheF9nZXRPcmRlcnM='), base64_decode('Z2V0T3JkZXJz'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0T3JkZXJz'), base64_decode('Z2V0T3JkZXJz'));
add_action(base64_decode('d3BfYWpheF9nZXQtc2t1LWFuZC11cmwtYnktQ2F0ZWdvcnk='), base64_decode('Z2V0U0t1QWJkVXJsQnlDYXRlZ29yeQ=='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0LXNrdS1hbmQtdXJsLWJ5LUNhdGVnb3J5'), base64_decode('Z2V0U0t1QWJkVXJsQnlDYXRlZ29yeQ=='));
add_action(base64_decode('d3BfYWpheF9nZXQtYWxyZWFkeS1pbXBvcnRlZC1wcm9kdWN0cw=='), base64_decode('Z2V0QWxyZWFkeUltcG9ydGVkUHJvZHVjdHM='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0LWFscmVhZHktaW1wb3J0ZWQtcHJvZHVjdHM='), base64_decode('Z2V0QWxyZWFkeUltcG9ydGVkUHJvZHVjdHM='));
add_action(base64_decode('d3BfYWpheF9pbnNlcnQtcmV2aWV3cy10by1wcm9kdWN0'), base64_decode('aW5zZXJ0UmV2aWV3c0ludG9Qcm9kdWN0'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfaW5zZXJ0LXJldmlld3MtdG8tcHJvZHVjdA=='), base64_decode('aW5zZXJ0UmV2aWV3c0ludG9Qcm9kdWN0'));
add_action(base64_decode('d3BfYWpheF9yZW1vdmUtcHJvZHVjdC1mcm9tLXdw'), base64_decode('cmVtb3ZlUHJvZHVjdEZyb21TaG9w'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfcmVtb3ZlLXByb2R1Y3QtZnJvbS13cA=='), base64_decode('cmVtb3ZlUHJvZHVjdEZyb21TaG9w'));
add_action(base64_decode('d3BfYWpheF9zZWFyY2gtcHJvZHVjdC1ieS1za3U='), base64_decode('c2VhcmNoUHJvZHVjdEJ5U2t1'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfc2VhcmNoLXByb2R1Y3QtYnktc2t1'), base64_decode('c2VhcmNoUHJvZHVjdEJ5U2t1'));
add_action(base64_decode('d3BfYWpheF91cGRhdGUtcHJvZHVjdC12YXJpYXRpb25z'), base64_decode('dXBkYXRlUHJvZHVjdFZhcmlhdGlvbnM='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfdXBkYXRlLXByb2R1Y3QtdmFyaWF0aW9ucw=='), base64_decode('dXBkYXRlUHJvZHVjdFZhcmlhdGlvbnM='));
add_action(base64_decode('d3BfYWpheF9zeW5jX2FsbF9wcm9kdWN0cw=='), base64_decode('c3luY19hbGxfcHJvZHVjdHNfRlJPTV9XUA=='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfc3luY19hbGxfcHJvZHVjdHM='), base64_decode('c3luY19hbGxfcHJvZHVjdHNfRlJPTV9XUA=='));
add_action(base64_decode('d3BfYWpheF9zZXQtdG8tZHJhZnQ='), base64_decode('c2V0UHJvZHVjdFRvRHJhZnQ='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfc2V0LXRvLWRyYWZ0'), base64_decode('c2V0UHJvZHVjdFRvRHJhZnQ='));
add_action(base64_decode('d3BfYWpheF9nZXRfcHJvZHVjdHMtZHJhZnQ='), base64_decode('Z2V0UHJvZHVjdHNEcmFmdA=='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0X3Byb2R1Y3RzLWRyYWZ0'), base64_decode('Z2V0UHJvZHVjdHNEcmFmdA=='));
add_action(base64_decode('d3BfYWpheF9nZXQtbmV3LXByb2R1Y3QtZGV0YWlscw=='), base64_decode('Z2V0TmV3UHJvZHVjdERldGFpbHM='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0LW5ldy1wcm9kdWN0LWRldGFpbHM='), base64_decode('Z2V0TmV3UHJvZHVjdERldGFpbHM='));
add_action(base64_decode('d3BfYWpheF9nZXQtb2xkLXByb2R1Y3QtZGV0YWlscw=='), base64_decode('Z2V0T2xkUHJvZHVjdERldGFpbHM='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0LW9sZC1wcm9kdWN0LWRldGFpbHM='), base64_decode('Z2V0T2xkUHJvZHVjdERldGFpbHM='));
add_action(base64_decode('d3BfYWpheF9nZXQtcHJvZHVjdC1ieS1pZA=='), base64_decode('c2VhcmNoUHJvZHVjdEJ5SWRSZXZpZXdz'));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfZ2V0LXByb2R1Y3QtYnktaWQ='), base64_decode('c2VhcmNoUHJvZHVjdEJ5SWRSZXZpZXdz'));
add_action(base64_decode('d3BfYWpheF9zYXZlT3B0aW9uc0RC'), base64_decode('c2F2ZU9wdGlvbnNEQg=='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfc2F2ZU9wdGlvbnNEQg=='), base64_decode('c2F2ZU9wdGlvbnNEQg=='));
function insertReviewsIntoProductRM_PREMUIM_PLUGIN()
{
  $b32 = isset($_POST[base64_decode('cG9zdF9pZA==')]) ? sanitize_text_field($_POST[base64_decode('cG9zdF9pZA==')]) : '';
  $c24 = isset($_POST[base64_decode('cmV2aWV3cw==')]) ? ($_POST[base64_decode('cmV2aWV3cw==')]) : array();
  $o76 = array();
  if (isset($b32) && isset($c24) && count($c24)) {
    foreach ($c24 as $z43) {
      $u44 = wp_insert_comment(array(base64_decode('Y29tbWVudF9wb3N0X0lE') => sanitize_text_field($b32), base64_decode('Y29tbWVudF9hdXRob3I=') => sanitize_text_field($z43[base64_decode('dXNlcm5hbWU=')]), base64_decode('Y29tbWVudF9hdXRob3JfZW1haWw=') => sanitize_text_field($z43[base64_decode('ZW1haWw=')]), base64_decode('Y29tbWVudF9hdXRob3JfdXJs') => '', base64_decode('Y29tbWVudF9jb250ZW50') => $z43[base64_decode('cmV2aWV3')], base64_decode('Y29tbWVudF90eXBl') => '', base64_decode('Y29tbWVudF9wYXJlbnQ=') => 0, base64_decode('dXNlcl9pZA==') => 5, base64_decode('Y29tbWVudF9hdXRob3JfSVA=') => '', base64_decode('Y29tbWVudF9hZ2VudA==') => '', base64_decode('Y29tbWVudF9kYXRl') => $z43[base64_decode('ZGF0ZWNyZWF0aW9u')], base64_decode('Y29tbWVudF9hcHByb3ZlZA==') => 1,));
      $x34 = update_comment_meta($u44, base64_decode('cmF0aW5n'), sanitize_text_field($z43[base64_decode('cmF0aW5n')]));
      if ($x34 != false && isset($x34)) {
        array_push($o76, $u44);
      }
    }
    wp_send_json(array(base64_decode('aW5zZXJ0ZWRTdWNjZXNzZnVsbHk=') => $o76));
  }
}
add_action(base64_decode('d3BfYWpheF9pbnNlcnQtcmV2aWV3cy10by1wcm9kdWN0Uk0='), base64_decode('aW5zZXJ0UmV2aWV3c0ludG9Qcm9kdWN0Uk1fUFJFTVVJTV9QTFVHSU4='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfaW5zZXJ0LXJldmlld3MtdG8tcHJvZHVjUk10'), base64_decode('aW5zZXJ0UmV2aWV3c0ludG9Qcm9kdWN0Uk1fUFJFTVVJTV9QTFVHSU4='));
function restoreConfiguration()
{
  $b110 = get_option(base64_decode('X3NhdmVkQ29uZmlndXJhdGlvbg=='));
  wp_send_json(array(base64_decode('X3NhdmVkQ29uZmlndXJhdGlvbg==') => $b110));
}
add_action(base64_decode('d3BfYWpheF9yZXN0b3JlQ29uZmlndXJhdGlvbg=='), base64_decode('cmVzdG9yZUNvbmZpZ3VyYXRpb24='));
add_action(base64_decode('d3BfYWpheF9ub3ByaXZfcmVzdG9yZUNvbmZpZ3VyYXRpb24='), base64_decode('cmVzdG9yZUNvbmZpZ3VyYXRpb24='));
