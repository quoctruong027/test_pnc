<?php
if (!defined('ABSPATH')) die("Stop there, you asshole");
require_once 'etsy-upload.php';
require_once dirname(__FILE__) . '/../data/data_overrides.php';

class Product_uploadhoook extends ETCPF_EtsyUpload
{
    /* *
     * defining private property
     *      set from constructor
     * */
    private $product;
    private $db;
    private $listing_table;
    private $feed_table;

    /* *
     * @product is the product id obtained from hook during product update
     *
     * */
    function __construct($product)
    {
        parent::__construct();
        $this->product = $product;
        global $wpdb;
        $this->db = $wpdb;
        $this->listing_table = $this->db->prefix . 'etcpf_listings';
        $this->feed_table = $this->db->prefix . 'etcpf_feeds';
    }

    /* *
     * check_if_eligible_toUpload() Checks if the product is already upload to etsy so that update can be done
     *
     * wc_get_product woocommerce global function
     *
     * getFeedDetails gets the related feed details
     *
     * getPreparedData prepares data for uploading to etsy
     * */
    public function Invoker()
    {
        $result = $this->check_if_eligible_toUpload($this->product);
        if (isset($result->listing_id)) {
            try {
                $productDetails = wc_get_product($this->product);
                $feed_details = $this->getFeedDetails($result->feed_id);
                $preparedData = $this->getPreparedData($productDetails, $feed_details, $result);
                $upload = parent::_CURLFORUPLOAD($result->listing_id, true, $preparedData, $result->id);
                if ($productDetails->is_type('variable')) {
                    if ($feed_details->variation_upload_type === 'single') {
                        $variationarray = $this->testSinglyVariatedFormat($productDetails->get_id(), $feed_details->id, $feed_details->variation_upload_profile);
                    } else {
                        $variationarray = $this->prepareVariationForUpload($productDetails, $productDetails->get_available_variations(),$feed_details);
                        $variationarray = parent::getpreparedVariationDataforEtsy($variationarray, $result->listing_id);
                    }
                    if ($variationarray) {
                        return parent::variationUpload($variationarray, $result->listing_id);
                    }
                }
                return $upload;
            } catch (Exception $e) {
                $this->recordFailedProduct($this->product);
            }
        }
        return false;
    }

    public function prepareVariationForUpload($product, $variations,$feed_details)
    {
        $variationArray = array();
        if (is_array($variations) && count($variations) > 0) {
            foreach ($variations as $key => $variation) {
                $variationData = new stdClass();
                $variationData->id = $variation['variation_id'];
                $variationData->data['id'] = $variation['variation_id'];
                $variationData->data['sku'] = $variation['sku'];
                $variationData->data['is_variation'] = true;
                $variationData->data['item_group_id'] = $product->get_id();
                $variationData->data['price'] = $variation['display_price'] ? $variation['display_price'] : $product->get_price();
                $variationData->data['has_sale_price'] = $variation['display_price'] ? true : false;
                $variationData->data['sale_price'] = $variation['display_price'] ? $variation['display_price'] : 0.00;
                $variationData->data['regular_price'] = $variation['display_regular_price'] ? $variation['display_regular_price'] : 0.00;
                $variationData->data['variation_images_details'] = $variation['image'];
                $variationData->data['additional_image_link'] = $variation['image']['full_src'];
                $variationData->data['image_link'] = $variation['image']['full_src'];
                $variationData->data['variation_image_id'] = $variation['image_id'];
                $variationData->data['in_stock'] = $variation['is_in_stock'] ? 'in_stock' : 'out_of_stock';
                $variationData->data['quantity'] = $variation['max_qty'] ? $variation['max_qty'] : $this->getstockofAproduct($product);
                $variationData->data['variation_is_active'] = $variation['variation_is_active'];
                $variationData->data['variation_is_visible'] = $variation['variation_is_visible'];

                if (is_array($variation['attributes'])) {
                    foreach ($variation['attributes'] as $key => $attr_values) {
                        if (!$attr_values) continue;
                        if (stripos($key, 'attribute_pa_') !== false) {
                            $attr = str_replace('attribute_pa_', '', $key);
                        } elseif (stripos($key, 'attribute_') !== false) {
                            $attr = str_replace('attribute_', '', $key);
                        } else {
                            $attr = $key;
                        }
                        $term_details = get_term_by('slug', $attr_values, $key);

                        if (empty($term_details)) {
                            if ($attr_values) {
                                $variationData->data[$attr] = $attr_values;
                                $variationData->data['variation_attributes'][$attr] = $attr_values;
                            }
                        } else {
                            $variationData->data[$attr] = $term_details->name;
                            $variationData->data['variation_attributes'][$attr] = $term_details->name;
                        }
                    }
                    if (isset($variationData->data['variation_attributes'])) {
                        $variationData->data['variation_attributes'] = json_encode($variationData->data['variation_attributes']);
                        $variationData->data = (object)$this->ImplementOverride($variationData->data, $feed_details->feed_overrides, $product);;
                        $variationArray[] = $variationData;
                        $variationData = null;
                    }
                }
            }
            return $variationArray;
        }
        return false;
    }

    /* *
     * Ensures the product provided is already listed in etsy
     *
     * @pid product id with which query is performed
     *
     *  */
    public function check_if_eligible_toUpload($pid)
    {
        return $this->db->get_row("SELECT id,listing_id,feed_id, etsy_state FROM {$this->listing_table} WHERE item_id = {$pid}");
    }

    /* *
     * @fid feed id to perform the query
     *
     * returns the result obtained from query
     *
     * */
    public function getFeedDetails($fid)
    {
        return $this->db->get_row($this->db->prepare("SELECT * FROM {$this->feed_table} WHERE id=%d", array($fid)));
    }

    /*
     * Prepares the data to make upload to etsy
     *
     * Communicates with multiple method as
     *
     * @product wc_get_product , @feed_details, @listingdata
     *
     * returns preparedData
     * */
    public function getPreparedData($product, $feed_details, $listingdata)
    {
        //Etsy accepts json_encoded array values
        $preparedData = array(
            'title' => strip_tags($product->get_name()),
            'sku' => $product->get_sku(),
            'description' => strip_tags($product->get_description()),
            'shipping_template_id' => $this->shipping_template_id,
            'state' => $this->getValidListingState($product, $listingdata),
            'taxonomy_id' => $this->getTaxonomy($feed_details, $product),
            'tags' => $this->getValidTags($product),
            'who_made' => get_etsy_settings('who_made_it'),
            'is_supply' => intval(get_etsy_settings('is_supply')),
            'when_made' => get_etsy_settings('when_made'),
            'recipient' => 'not_specified',
            'style' => array('Avant garde'),
            'language' => get_etsy_settings('shop_language') ? get_etsy_settings('shop_language') : substr(get_locale(), 0, 2),
        );

        // If product is variable,
        // we don't need quantity and price to upload in etsy

        if (!$product->is_type('variable')) {
            $preparedData['quantity'] = $this->getstockofAproduct($product);
            $preparedData['price'] = $product->get_sale_price() ? $product->get_sale_price() : $product->get_regular_price();
        }
        
        $preparedData['regular_price'] = $product->get_regular_price();
        $preparedData['has_sale_price'] = $product->get_sale_price() ? true : false;
        $preparedData['sale_price'] = $product->get_sale_price();
        $preparedData['image_link'] = '';

        if (get_etsy_settings('title_sync') == 'no') {
            unset($preparedData['title']);
        }

        if (get_etsy_settings('description_sync') == 'no') {
            unset($preparedData['description']);
        }

        if (get_etsy_settings('tags_sync') == 'no') {
            unset($preparedData['tags']);
        }
        return $this->ImplementOverride($preparedData, $feed_details->feed_overrides, $product);
    }

    /*
     * If product quantity is 0 and item is uploaded to etsy
     *    make it inactive and send
     *
     * @qty: quantity of product
     *
     * @listingData: data containing all listed items detail
     *
     * */

    public function getValidListingState($product, $listingData)
    {
        if ($this->getstockofAproduct($product) <= 0) {
            return 'inactive';
        } else {
            if (isset($listingData->etsy_state) && ($listingData->etsy_state === 'inactive' || $listingData->etsy_state === 'edit')) {
                return get_etsy_settings('state');
            }else{
                $listingData->etsy_state = $this->checkStatusInEtsy($listingData->listing_id);
                if($listingData->etsy_state === 'inactive' || $listingData->etsy_state === 'edit'){
                    return get_etsy_settings('state');
                }else{
                    return $listingData->etsy_state;
                }
            }
        }
    }


    /*
     * return taxonomy id of a etsy category
     * */
    public function getTaxonomy($details, $product)
    {
        if ($details->feed_type > 0) {
            include_once 'ETCPF_Customfeed.php';
            $CustomObj = new ETCPF_Customfeed(1);
            $params = array(
                'filename' => $details->filename,
                'categories' => $CustomObj->getMappedCategoryOfParticularFeed($details->id),
                'products' => $CustomObj->getProductsOfParticularFeed($details->id),
                'feedtype' => 1
            );
        }
        $localCategoriesArray = get_the_terms($product->get_id(), 'product_cat');
        $localcatString = '';
        foreach ($localCategoriesArray as $key => $values) {
            if (strlen($localcatString) > 3) {
                $localcatString .= ',';
            }
            $localcatString .= $values->slug;
        }
        $incaselocalstring = explode(',', $localcatString);
        if (isset($params['categories'][$localcatString])) {
            $details->texonomy_path = $params['categories'][$localcatString]['taxonomy_path'];
        } elseif (isset($params['categories'][$localcatString])) {
            $details->texonomy_path = $params['categories'][$localcatString]['taxonomy_path'];
        } else {
            foreach ($incaselocalstring as $key => $value) {
                if (isset($params['categories'][$value])) {
                    $details->texonomy_path = $params['categories'][$value]['taxonomy_path'];
                }
            }
        }
        if (stripos($details->texonomy_path, '|')) {
            $current_taxonomy_id = explode('|', $details->texonomy_path);
        } else {
            $current_taxonomy_id = explode(',', $details->texonomy_path);
        }
        return end($current_taxonomy_id);
    }

    /*
     * @returns valid woocommerce tags
     *
     * */
    public function getValidTags($product)
    {
        $t = '';
        $tags = get_the_terms($product->get_id(), 'product_tag');
        if (is_array($tags) && count($tags) > 0) {
            $tag = array();
            foreach ($tags as $key => $t) {
                if (strlen($t->name) <= 20 && preg_match('/^[a-z\d\-_\s]+$/i', $t->name))
                    $tag[] = $t->name;
            }
            $t = implode(",", $tag);
        }
        return $t;
    }

    /* *
     * Handling externally added attributes
     *
     * @params product data
     *
     * returns formulated data to calling function
     * */
    public function ImplementOverride($data, $overrides, $product)
    {
        if ($overrides == null) {
            $overrides = get_option('Etsy-etsy-merchant-settings');
        }
        $invoker = new Data_overrides($overrides);
        return $invoker->implementOverrides($data, $product);
    }

    public function recordFailedProduct($pid)
    {
        /* @TODO : record in db */
        return $pid;
    }

    public function getstockofAproduct($product)
    {
        if ($product->get_stock_quantity() > 0 && ($product->get_stock_quantity() < 999)) {
            return $product->get_stock_quantity();
        } elseif ($product->get_stock_quantity() > 0 && ($product->get_stock_quantity() > 999)) {
            return 999;
        } elseif ($product->get_manage_stock() === false && $product->get_stock_status() === 'instock') {
            return 10;
        } else {
            return 0;
        }
    }

}
