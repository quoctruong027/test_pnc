<?php
require_once dirname(__FILE__) . '/../basicfeed.php';

class ETCPF_EtsyFeed extends ETCPF_XMLFeed
{
    public $errorstatus = false;

    function __construct()
    {
        parent::__construct();
        $this->providerName = 'Etsy';
        $this->providerNameL = 'Etsy';
        $this->addAttributeMapping('id', 'id', false, true);
        $this->addAttributeMapping('title', 'title', true, true);
        $this->addAttributeMapping('sku', 'sku', true, true);
        $this->addAttributeMapping('has_variation', 'has_variation', false, true);
        $this->addAttributeMapping('is_variation', 'is_variation', false, true);
        $this->addAttributeMapping('variation_attributes', 'variation_attributes', true, false);
        $this->addAttributeMapping('item_group_id', 'item_group_id', false, true);
        $this->addAttributeMapping('stock_quantity', 'quantity', false, true);
        $this->addAttributeMapping('description', 'description', true, true);
        $this->addAttributeMapping('regular_sku', 'sku', true, true);
        $this->addAttributeMapping('sale_price', 'price', false, true);
        $this->addAttributeMapping('feature_imgurl', 'image_link', false, true);
        $this->addAttributeMapping('primary_color', 'color', true, true);
        $this->addAttributeMapping('secondary_color', 'secondary_color', true, true);
        $this->addAttributeMapping('occasion', 'occasion', true, true);
        $this->addAttributeMapping('holiday', 'holiday', true, true);
        $this->addAttributeMapping('state', 'state', false, true);

        $this->addAttributeMapping('who_made', 'who_made', true, true);
        $this->addAttributeMapping('when_made', 'when_made', false, true);
        $this->addAttributeMapping('is_supply', 'is_supply', false, true);
        $this->addAttributeDefault('local_category', 'none', 'ETCPF_CategoryTree'); //store's local category tree

        // $this->addAttributeMapping('regular_sku', 'sku', false, true);
        $this->addAttributeMapping('sale_price', 'sale_price', false, false);
        $this->addAttributeMapping('sale_price_effective_date', 'sale_price_effective_date', true, false);

        $this->addAttributeMapping('current_category', 'etsy_category', true, true);
        $this->addAttributeMapping('', 'category_id', false, false);
        $this->addAttributeMapping('', 'processing_min', false, false);
        $this->addAttributeMapping('', 'processing_max', false, false);
        $this->addAttributeMapping('', 'taxonomy_id', false, false);

        $this->addAttributeMapping('shipping_template_id', 'shipping_template_id', true, true);
        $this->addAttributeMapping('', 'shipping_template_id', true, true);

        $this->addAttributeMapping('weight', 'weight', false, false);
        $this->addAttributeMapping('length', 'length', false, false);
        $this->addAttributeMapping('width', 'width', false, false);
        $this->addAttributeMapping('height', 'height', false, false);
        $this->addAttributeMapping('weight_unit', 'weight_unit', true, false);
        $this->addAttributeMapping('dimension_unit', 'dimension_unit', true, false);

        $this->addAttributeMapping('listing_id', 'listing_id', true, true);
        $this->addAttributeMapping('materials', 'materials', true, false);
        $this->addAttributeMapping('tags', 'tags', true, false);
        $this->addAttributeMapping('currency', 'currency', false, false);
        $this->addAttributeMapping('color', 'color', true, false);
        $this->addAttributeMapping('size', 'size', true, false);
        $this->addAttributeMapping('', 'default1', true, false);
        $this->addAttributeMapping('', 'default2', true, false);
        $this->addAttributeMapping('remote_category_path', 'current_category', false, true);
        $this->addAttributeMapping('taxonomy_path', 'taxonomy_path', false, true);
        $this->addAttributeMapping('', 'default3', true, false);
        $this->addAttributeDefault('additional_images', 'none', 'ETCPF_GoogleAdditionalImages');

        $this->productLevelElement = 'item';
        $this->google_exact_title = false;
        $this->google_combo_title = false;
        //automatic identifier_exists=false function.
        //set google_identifier to false to disable
        $this->google_identifier = false;

        /*$this->addRule('status_standard', 'statusstandard');*/ //'in stock' or 'out of stock'
        $this->addRule('price_rounding', 'pricerounding'); //2 decimals
        //shipping
        $this->addRule('weight_unit', 'weightunit');
        $this->addRule('length_unit', 'dimensionunit', array('length'));
        $this->addRule('width_unit', 'dimensionunit', array('width'));
        $this->addRule('height_unit', 'dimensionunit', array('height'));

        $this->addRule('google_exact_title', 'googleexacttitle'); //true disables ucowrds
        $this->addRule('google_combo_title', 'googlecombotitle');

    }

    function formatProduct($product)
    {
        $this->errorstatus = false;
        $productid = $product->id;
        if (isset($product->attributes['item_group_id'])) {
            $parentId = $product->attributes['item_group_id'];
        } else {
            $parentId = null;
        }

        if (is_array($product->imgurls) && count($product->imgurls) > 0) {
            $product->attributes['additional_image'] = implode(',', $product->imgurls);
        }
        if (empty($product->attributes['sku'])) {
            $product->attributes['sku'] = $product->id;
        }
        if (empty($product->attributes['tags'])) {
            $product->attributes['tags'] = '';
            $tags = get_the_terms($product->id, 'product_tag');
            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    $product->attributes['tags'] = $tag->name . ',' . $product->attributes['tags'];
                }
            }
        }

        if (floatval($product->attributes['sale_price']) <= 0 && isset($product->attributes['price'])) {
            $product->attributes['sale_price'] = $product->attributes['price'];
        }

        if (floatval($product->attributes['sale_price']) <= 0 && isset($product->attributes['regular_price'])) {
            $product->attributes['sale_price'] = $product->attributes['regular_price'];
        }

        if (isset($product->attributes['is_variation'])) {
            $product->attributes['feature_imgurl'] = $product->attributes['image_link'];
        }
        if (empty($product->attributes['sale_price']) || floatval($product->attributes['sale_price']) == 0) {
            $code = 5203;
            $value = $this->getValueFromErrorCode($code, $productid);
            if ($value) {
                $product->attributes['sale_price'] = $value;
            } else {
                $mappedattributes = $this->checkmappingForParticular('price');
                if (empty($product->attributes[$mappedattributes])) {
                    $this->errorstatus = true;
                    $this->prepareErrorReports($parentId, $product->id, isset($product->attributes['sku']) ? $product->attributes['sku'] : 'n/a', $product->attributes['title'], 'Missing Attribute Sale Price', 'Error', $code, '0', $product->attributes['localCategory']);
                }
            }
        }
        if ($product->attributes['valid'] == true &&
            (empty($product->attributes['stock_quantity']) || $product->attributes['stock_quantity'] == 0)) {
            if ($product->attributes['stock_quantity'] >= 999) {
                $stockmessage = "Product Quantity on Etsy cannot be more than 999";
            } else {
                $stockmessage = "Product Quantity must be greater than zero";
            }
            $code = 5204;
            $value = $this->getValueFromErrorCode($code, $productid);
            if ($value) {
                $product->attributes['stock_quantity'] = $value;
            } else {
                $mappedattributes = $this->checkmappingForParticular('stock_quantity');
                if (empty($product->attributes[$mappedattributes])) {
                    $this->errorstatus = true;
                    $this->prepareErrorReports($parentId, $product->id, isset($product->attributes['sku']) ? $product->attributes['sku'] : 'n/a', $product->attributes['title'], $stockmessage, 'Error', $code, '-1', $product->attributes['localCategory']);
                }
            }
        }
        if ($this->errorstatus == false) {
            $this->insertintodb = true;
            $code = 5000; /* 5000 is a success code */
            $this->prepareErrorReports($parentId, $product->id, isset($product->attributes['sku']) ? $product->attributes['sku'] : 'n/a', $product->attributes['title'], 'Product Free of error', 'Success', $code, '1', $product->attributes['localCategory']);
        } else {
            $this->insertintodb = true;
        }
        if (empty($product->attributes['current_category'])) {
            $product->attributes['current_category'] = $this->remote_category_path;
        }
        if (empty($product->attributes['taxonomy_path'])) {
            $product->attributes['taxonomy_path'] = $this->full_taxonomy_path;
        }
        return parent::formatProduct($product);
    }

    function getFeedFooter($file_name, $file_path)
    {
        $output = '</channel></rss>';
        return $output;
    }

    function getFeedHeader($file_name, $file_path)
    {
        $array = explode('/', $file_name);
        $title = end($array);
        $output = '<?xml version="1.0" encoding="UTF-8" ?>
                    <rss version="2.0">
                      <channel>
                        <title>' . $title . '</title>
                        <link><![CDATA[' . $file_path . ']]></link>';
        return $output;
    }

    function getValueFromErrorCode($code, $pid)
    {
        global $wpdb;
        $table = $wpdb->prefix . "etcpf_resolved_product_data";
        $productdata = $wpdb->get_row("SELECT value FROM {$table} WHERE product_id={$pid} AND error_code={$code}");
        if (!empty($productdata->value)) {
            return $productdata->value;
        }
        return null;
    }

    function checkmappingForParticular($checkattribute)
    {
        if (array_key_exists($checkattribute, $this->usermappedAttributes)) {
            return $this->usermappedAttributes[$checkattribute];
        }
        return false;
        /*foreach ($this->attributeMappings as $key => $attribute) {
            $attributename = $attribute->mapTo;
            if ($checkattribute == $attributename) {
                return $attribute->attributeName;
            }
        }*/
    }

}
