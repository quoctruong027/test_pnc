<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}

$file = dirname(__FILE__) . '/../classes/OAuth.php';
if (file_exists($file)) {
    require_once $file;
} else {
    print_r("no file");exit;
}

class Variation
{

    public function __construct()
    {
        if (!is_admin()) {
            exit;
        }

    }

    public function index($id)
    {
        $sendback = admin_url().'admin.php?page=etsy-export-feed-upload&msg=u';
        global $wpdb;
        $table = $wpdb->prefix . "etcpf_listings";
        $query = "SELECT id as ID, item_id,uploaded,uploaded_at, upload_result,listing_id,variation_upload_result,item_group_id FROM {$table} where id={$id}";
        $data  = $wpdb->get_row($query);
        $ObtainedVariationData = json_decode($data->variation_upload_result);
        $productid = $data->item_id;
        $product   = wc_get_product($productid);
        $pSKU      = $product->get_sku();
        if (!$pSKU) {
            $this->sku = strtoupper(substr(str_replace(' ', '', $product->get_slug()), 0, 11)) . $productid;
            update_post_meta($productid, '_sku', $this->sku);
        } else {
            $this->sku = $pSKU;
        }
        $variationData    = $this->get_variation($productid);
        $etsyVaiationData = $this->uploadVariation($variationData);
        if ($_POST) {
            $k               = 0;
            $onpropertyValue = $_POST['on_variation'];
            foreach ($etsyVaiationData as $key => $value) {

                foreach ($value as $k => $val) {
                    unset($value[$k]['price']);
                    unset($value[$k]['sku']);
                    unset($value[$k]['quantity']);
                }

                /*
                echo "<pre>";
                print_r ($_POST);
                echo "</pre>";exit;*/
                /*  echo "<pre>";
                print_r($value);exit;
                 */

                $products[] = array(
                    'property_values' => $value,
                    'sku'             => $_POST['variation'][$key]['sku'],
                    'offerings'       => array(
                        array(
                            'price'      => $_POST['variation'][$key]['price'],
                            'quantity'   => $_POST['variation'][$key]['quantity'],
                            'is_enabled' => 1,
                        ),
                    ),
                );
                $k++;
            }

            /*echo "<pre>";
            print_r ($products);
            echo "</pre>";exit;*/

            $listing_id = $data->listing_id;
            $data       = array(
                'data' => array(
                    'products'             => json_encode($products),
                    "price_on_property"    => $onpropertyValue,
                    "quantity_on_property" => $onpropertyValue,
                    "sku_on_property"      => $onpropertyValue,
                ),
            );

            if ($data['data']) {
                $url     = "https://openapi.etsy.com/v2/private/listings/" . $listing_id . "/inventory";
                $acc_req = $this->prepareHash($url, 'POST', $type = true);

                // $response = $this->browsePost($acc_req,json_encode($data),true);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $acc_req);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                // curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data['data']);
                $response = curl_exec($ch);
                if ($response) {
                    global $wpdb;
                    $table = $wpdb->prefix . "etcpf_listings";

                    $data = [
                        'variation_upload_result' => $response,
                    ];
                    $wpdb->update($table, $data, ['listing_id' => $listing_id]);

                    wp_redirect($sendback);exit;
                    return $this->views($response, $variationUpload = true);
                }
            }
        }

        return $this->views($etsyVaiationData);

    }

    public function get_variation($idProduct)
    {
        if (function_exists('get_product')) {
            $product = wc_get_product($idProduct);
            if ($product->is_type('variable')) {

                $product = new WC_Product_Variable($idProduct);

                $available_variations = $product->get_available_variations(); //get all child variations
                $variation_variations = $product->get_variation_attributes(); // get all attributes by variations

                $variation_data     = array();
                $variation_price    = array();
                $variation_price    = array();
                $variation_quantity = array();
                $variation_sku      = array();
                $i                  = 0;
                foreach ($available_variations as $key => $value) {

                    $variation_data[$i] = $value['attributes'];

                    /*
                    if($value['display_price'])
                    $variation_data[$i]['display_price'] = $value['display_price'];
                    $variation_data[$i]['display_regular_price'] = $value['display_regular_price'];
                    $variation_data[$i]['sku'] = $value['sku'];
                    $variation_data[$i]['quantity'] = $value['max_qty'];
                     */

                    $variation_price[$i]    = $value['display_price'];
                    $variation_sku[$i]      = $value['sku'];
                    $variation_quantity[$i] = $value['max_qty'];
                    $i++;
                }
                $varresult = array('variation_data' => $variation_data, 'variation_price' => $variation_price, 'variation_sku' => $variation_sku, 'quantity' => $variation_quantity);

                return $varresult;

            } else {
                return false;
            }
        }
    }

    public function uploadVariation($variation_data = array())
    {

        $varArray = array();
        $i        = 0;
        $j        = 0;
        foreach ($variation_data['variation_data'] as $key => $value) {

            foreach ($value as $key => $val) {
                $check_property = $key;
                $str            = str_replace('attribute_pa_', '', $check_property, $count);

                if ($count > 0) {
                    $property_name = $str;
                } else {
                    $property_name = str_replace('attribute_', '', $check_property);
                }

                if ($check_property == "attribute_pa_size" || $check_property == "attribute_size") {
                    $property_id     = 100;
                    $onpropertyValue = $property_id;

                } elseif ($check_property == "attribute_pa_color" || $check_property == "attribute_color") {
                    $property_id     = 200;
                    $onpropertyValue = $property_id;
                } elseif ($check_property == "attribute_pa_flavor" || $check_property == "attribute_flavor") {
                    $property_id     = 503;
                    $onpropertyValue = $property_id;
                } elseif ($check_property == "attribute_pa_height" || $check_property == "attribute_height") {
                    $property_id     = 505;
                    $onpropertyValue = $property_id;
                } elseif ($check_property == "attribute_pa_length" || $check_property == "attribute_length") {
                    $property_id     = 506;
                    $onpropertyValue = $property_id;
                } elseif ($check_property == "attribute_pa_material" || $check_property == "attribute_material") {
                    $property_id     = 507;
                    $onpropertyValue = $property_id;
                } else {
                    $property_id = 513;
                }
                if ($value[$check_property] !== "") {

                    $varArray[$property_name][$i]['property_id']   = $property_id;
                    $varArray[$property_name][$i]['property_name'] = $property_name;
                    $varArray[$property_name][$i]['values']        = array(str_replace('attribute_pa_', '', $value[$check_property]));
                    // $varArray[$i]['sku'] = $variation_data['variation_sku'][$j];
                    if ($variation_data['variation_price']) {
                        $varArray[$property_name][$i]['price'] = $variation_data['variation_price'][$j];
                    }

                    if ($variation_data['variation_sku']) {
                        $variation_data['variation_sku'][$j];
                        $varArray[$property_name][$i]['sku'] = $variation_data['variation_sku'][$j];
                    } else {
                        $variation_data['variation_sku'][$j] = $this->sku;
                    }
                    if ($variation_data['quantity']) {
                        $varArray[$property_name][$i]['quantity'] = $variation_data['quantity'][$j];
                        $this->globalQuantity                     = $variation_data['quantity'][$j];
                    } else {
                        $this->globalQuantity = 10;
                    }

                }

                $i++;
            }

            $j++;

        } //foreach ends here

        $rawData = $this->processRawData($varArray);

        $managed_var_data = $this->manageVariation($rawData);

        return $managed_var_data;

    } /*End of function*/

    public function manageVariation($data = array())
    {
        $datatobesent = array();
        $i            = 0;
        foreach ($data as $key => $value) {
            $datatobesent[$i] = array_values($value);
            $i++;
        }

        $productcombination = $this->combinations($datatobesent);

        /*if(count($data)>0){
        $k = 0;
        $products = array();
        foreach ($data as $key => $value) {
        foreach ($value as $key => $val) {
        $products[] = [
        'property_values' => $productcombination[$key],
        'sku'             => $val['sku'],
        'offerings'       => [
        [
        'price'      => $val['price'],
        'quantity'   => $val['quantity'],
        'is_enabled' => 1
        ],
        ],
        ] ;
        $k++;
        }
        }
        }*/
        return $productcombination;
    }

    public function combinations($arrays = array())
    {
        $result = array(array());

        foreach ($arrays as $property => $property_values) {
            foreach ($property_values as $k => $q) {

            }
            $tmp = array();
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $array1 = array($property => $property_value);

                    $tmp[] = array_merge($result_item, $array1);

                }
            }
            $result = $tmp;
        }

        return $result;
    }

    public function processRawData($varArray = array())
    {
        $variationCount = count($varArray);
        $variationArray = array();
        if ($variationCount > 2) {
            $i = 0;
            foreach ($varArray as $key => $value) {
                
                if ($i > 1) {
                    unset($varArray[$key]);
                }
                $i++;

                if(isset($varArray[$key])) $varArray[$key] = array_values($varArray[$key]);
            }
        } else {
            foreach ($varArray as $key => $value) {
                $varArray[$key] = array_values($varArray[$key]);
            }
        }

        foreach ($varArray as $k => $value) {
            $value = array_values($value);
            $i     = 0;
            foreach ($value as $key => $val) {
                foreach ($value as $j => $f) {
                    if ($j > $key) {
                        if ($value[$key]['property_name'] == $value[$j]['property_name'] && $value[$key]['values'][0] == $value[$j]['values'][0]) {
                            unset($varArray[$k][$key]);
                        }

                    }
                }
                $i++;
            }
        }

        foreach ($varArray as $key => $value) {
            $varArray[$key] = array_values($varArray[$key]);
        }

        return $varArray;
    }

    public function prepareHash($url, $type, $put = false)
    {

        $api_key            = get_option('etcpf_api_key');
        $secret_key         = get_option('etcpf_secret_key');
        $oauth_token        = get_option('etcpf_oauth_token');
        $oauth_token_secret = get_option('etcpf_oauth_token_secret');
        $hmac_method        = new OAuthSignatureMethod_HMAC_SHA1();
        $consumer           = new OAuthConsumer($api_key, $secret_key);
        $token              = new OAuthConsumer($oauth_token, $oauth_token_secret);

        if ($put) {
            $params = array('method' => 'PUT');
        } else {
            $params = array('method' => $type);
        }

        $acc_req = OAuthRequest::from_consumer_and_token($consumer, $token, $type, $url, $params);
        $acc_req->sign_request($hmac_method, $consumer, $token);
        return $acc_req;
    }

    public function views($data = array(), $variationUpload = false)
    {
      $status=null;
        if($variationUpload==true){
           $data = json_decode($data);
           $status = "success";
        }
        require_once 'variation-view-page.php';
    }
}
