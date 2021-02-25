<?php
if (!defined('ABSPATH')) {
    exit;
}

Class ETCPF_Products_Store
{
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function getProducts(array $params, $limit, $offset)
    {
        $data = array();
        $keywords = isset($params['keywords']) ? $params['keywords'] : "";
        $category_id = isset($params['category']) ? $params['category'] : '';
        $price_range = isset($params['price_range']) ? $params['price_range'] : '';
        $product_sku = isset($params['sku']) ? $params['sku'] : '';
        $stockCheck = isset($params['stockstatus']) ? $params['stockstatus'] : '0';
        $limit = isset($params['limit']) ? $params['limit'] : '';
        $keywordsorsku = isset($params['keywordsorsku']) ? $params['keywordsorsku'] : '';
        $currentPage = array_key_exists('page', $params) ? $params['page'] : 0;
        $cats = "";
        $priceLimit = "";
        $skuQuery = "";
        $title = '';
        $sku = '';
        if ($stockCheck == '0') {
            $hideoutofstock = false;
        } else {
            $hideoutofstock = true;
        }
        $sku = $keywords;
        /*if ($keywordsorsku == 'sku') {
            $sku = $product_sku;
        } else {
            $title = $product_sku;
        }*/
        $table = $this->db->prefix . 'posts';
        $relationTable = $this->db->prefix . 'term_relationships';
        $taxonomyTable = $this->db->prefix . 'term_taxonomy';
        $termTable = $this->db->prefix . 'terms';
        $postMetatable = $this->db->prefix . 'postmeta';
        $postmetaSelect = '';
        $postmetaJoin = '';

        $where = '';
        $or = false;
        if ($sku || $category_id || $title || $hideoutofstock || $price_range) {
            $where .= 'WHERE';
        }
        if ($title) {
            $or = true;
            $where .= " P.post_title like '%{$product_sku}%'";
        }
        if ($sku || $price_range || $hideoutofstock) {
            $metawhere = '';
            $postmetaSelect = ', GROUP_CONCAT(PM.meta_value) as value';
            $postmetaJoin = " LEFT JOIN {$postMetatable} PM ON PM.post_id = P.id";
            if ($or == false) {
                $or = true;
                $where .= " (";
            } else {
                $or = true;
                $where .= "  (";
            }
            $and = false;
            if ($sku) {
                $and = true;
                $metawhere .= " ((PM.meta_key='_sku' AND PM.meta_value like '%{$sku}%') OR (P.post_title like '%{$sku}%')) ";
            }
            if ($price_range) {
                if ($and == true) {
                    $and = true;
                    $metawhere = " AND PM.meta_value {$price_range} ";
                    if (strpos($price_range, '-')) {
                        $price = explode('-', $price_range);
                        $metawhere = " AND (PM.meta_value >= {$price[0]} AND PM.meta_value <= {$price[1]}) ";
                    }
                } else {
                    $and = true;
                    $metawhere = "PM.meta_key = '_regular_price' AND PM.meta_value = {$price_range}";
                    if (strpos($price_range, '-')) {
                        $price = explode('-', $price_range);
                        $metawhere = " (PM.meta_key = '_regular_price' AND PM.meta_value >= {$price[0]} AND PM.meta_value <= {$price[1]}) ";
                    }
                }
            }

            if ($hideoutofstock == true) {
                if ($and == true) {
                    $metawhere .= " AND (PM.meta_key = '_stock' AND PM.meta_value >=1) ";
                } else {
                    $metawhere .= "(PM.meta_key = '_stock' AND PM.meta_value >=1) ";
                }
            }

            $where .= $metawhere . ' )';

        }
        if ($category_id) {
            if (strlen($where) < 5) {
                $where .= "WHERE ";
            }
            $taxonomies = array('taxonomy' => 'product_cat');
            $args = array('child_of' => $category_id);
            $childCategories = get_terms($taxonomies, $args);
            if (is_array($childCategories) && count($childCategories) > 0) {
                $catarray = array($category_id);
                foreach ($childCategories as $key => $value) {
                    array_push($catarray, $value->term_id);
                }
                $category_ids = implode(',', $catarray);
                if ($or == false) {
                    $where .= " T.term_id IN ({$category_ids})";
                } else {
                    $where .= " AND T.term_id IN ({$category_ids}) ";
                }
            } else {
                if ($or == false) {
                    $where .= " T.term_id = $category_id";
                } else {
                    $where .= " AND T.term_id = $category_id ";
                }
            }
        }

        if (strlen($where) > 10) {
            $where .= " AND P.post_type='product' AND P.post_status ='publish' AND P.post_parent=0 AND (tax.taxonomy='category' OR tax.taxonomy='product_cat')";
        } else {
            $where = " WHERE P.post_type='product' AND P.post_status ='publish' AND P.post_parent=0 AND (tax.taxonomy='category' OR tax.taxonomy='product_cat')";
        }
        $count = "SELECT COUNT(DISTINCT P.ID) as count FROM {$table} P
            LEFT JOIN {$relationTable} rel ON rel.object_id = P.ID
            LEFT JOIN {$taxonomyTable} tax ON tax.term_taxonomy_id = rel.term_taxonomy_id
            LEFT JOIN {$termTable} T ON T.term_id = tax.term_id
            {$postmetaJoin}
            {$where}
             ";
        $cresult = $this->db->get_row($count);
        $totalrow = $cresult;
        $pagesthatwillbeformed = ceil(intval($totalrow->count) / 20);
        if ($pagesthatwillbeformed > $currentPage) {
            $data['loadmore'] = true;
            $data['totalProducts'] = $cresult;
            $data['pages'] = ceil($cresult->count / 20);
        } else {
            $data['loadmore'] = false;
            $data['totalProducts'] = $cresult;
            $data['pages'] = ceil($cresult->count / 20);
        }
        $perpage = $limit > 0 ? $limit : 20;
        $offset = $currentPage * $perpage;
        $limit = "LIMIT {$perpage} OFFSET {$offset}";
        $SQL = "SELECT P.*,GROUP_CONCAT(T.name) as category, GROUP_CONCAT(T.slug) as category_slug, GROUP_CONCAT(tax.taxonomy) as taxtype {$postmetaSelect}  FROM {$table} P
            LEFT JOIN {$relationTable} rel ON rel.object_id = P.id
            LEFT JOIN {$taxonomyTable} tax ON tax.term_taxonomy_id = rel.term_taxonomy_id
            LEFT JOIN {$termTable} T ON T.term_id = tax.term_id
            {$postmetaJoin}
            {$where} GROUP BY P.ID {$limit}
             ";
        $results = $this->db->get_results($SQL);
        $data['products'] = $results;
        return $data;
    }

    public function getProductsOfParticularFeed($feedid)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'etcpf_custom_feed_products';
        //$postTable = $wpdb->prefix.'posts';
        //$products = $wpdb->get_results($wpdb->prepare("SELECT P.*,CP.children as variation_children* FROM $table CP LEFT JOIN $postTable P on CP.product_id=P.id WHERE CP.feed_id=%d",array($feedid)));
        $productIds = $wpdb->get_results($wpdb->prepare("SELECT CP.* FROM $table CP WHERE CP.feed_id=%d", array($feedid)));
        $managedSelectedProducts = array();
        foreach ($productIds as $key => $productId) {
            if ($childids = json_decode($productId->children)) {
                $managedSelectedProducts[$productId->product_id] = array(
                    'child' => array('ids' => $childids)
                );
            }else{
                $managedSelectedProducts[$productId->product_id] = array(
                    'child' => array('ids' => array())
                );
            }
        }
        return $managedSelectedProducts;
    }

    public function getMappedCategoryOfParticularFeed($feedid)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'etcpf_category_mappings';
        $categories = $wpdb->get_results($wpdb->prepare("SELECT MC.* FROM $table MC WHERE MC.feed_id=%d", array($feedid)));
        $managedCategories = array();
        foreach ($categories as $key => $category) {
            $managedCategories[$category->local_category_slug] = array(
                'child_count' => $category->child_count,
                'parent_count' => $category->parent_count,
                'remote_category' => $category->remote_category,
                'showValue' => $category->showValue,
                'taxonomy_path' => $category->texonomy_path,
            );
        }
        return $managedCategories;
    }

    public function getFilenameByFeedID($feedid){
        global $wpdb;
        $table = $wpdb->prefix . 'etcpf_feeds';
        $feedDetails = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table F WHERE F.id=%d", array($feedid)));
        return $feedDetails;
    }

    public function getProductChildren($parentId)
    {
        //global $wpdb;
        $args = array(
            'post_parent' => $parentId,
            'post_type' => 'product_variation',
            'numberposts' => -1,
            'post_status' => 'publish'
        );
        $children = get_children($args);
        if ($children) {
            return $children;
        }
        return true;
    }

    public function getMetavalueByProductID($pid)
    {
        $meta = get_post_meta($pid);
        if ($meta) {
            return $meta;
        }
        return null;
    }

    public function getWooProductData($id)
    {
        global $woocommerce;
        if ($woocommerce != null) {
            $wc_version = explode('.', $woocommerce->version);
            if (($wc_version[0] <= 2)) {
                $this->lowerWcVersion = true;
                $productDataByID = get_product($id);
                if (is_object($productDataByID) && !empty($productDataByID)) {
                    return $productDataByID;
                }
                return null;
            } else {
                $this->lowerWcVersion = false;
                $productDataByID = wc_get_product($id);
                if (is_object($productDataByID) && !empty($productDataByID)) {
                    return $productDataByID;
                }
                return null;
            }
        }
        return null;
    }
}