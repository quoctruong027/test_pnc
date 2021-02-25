<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class ETCPF_FeedActivityLog
{
    function __construct($feedIdentifier = '')
    {
        //When instantiated (as opposed to static calls) it means we need to log the phases
        //therefore, save the feedIdentifier
        $this->feedIdentifier = $feedIdentifier;
    }

    function __destruct()
    {
        global $etcore;
        $this->deleteLogDataW();
    }

    /********************************************************************
     * Add a record to the activity log for "Manage Feeds"
     ********************************************************************/

    private static function addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $remote_category_path, $full_taxonomy_path)
    {
        global $etcore;
        $addNewFeedData = 'addNewFeedData' . $etcore->callSuffix;
        ETCPF_FeedActivityLog::$addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $remote_category_path, $full_taxonomy_path);
    }

    private static function addNewFeedDataW($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $remote_category_path, $full_taxonomy_path)
    {
        global $wpdb;
        global $etcore;
        /*
         * $etcore->feed_type == 1 Custom product feed type
         * $etcore->feed_type == 0 Default product feed type
         * */
        $product_details = NULL;
        $etcore->feedType = 0;
        $feed_type = 0;
        $feed_table = $wpdb->prefix . 'etcpf_feeds';
        $sql = "INSERT INTO $feed_table(`category`, `remote_category`, `filename`, `url`, `type`, `product_count`,`feed_type`,`product_details`,`texonomy_path`,`category_path`) VALUES ('$category','$remote_category','$file_name','$file_path','$providerName', '$productCount','$feed_type','$product_details','$full_taxonomy_path','$remote_category_path')";
        $wpdb->query($sql);
    }

    private static function addNewFeedDataWe($category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {
        ETCPF_FeedActivityLog::addNewFeedDataW($category, $remote_category, $file_name, $file_path, $providerName, $productCount);
    }

    /********************************************************************
     * Search the DB for a feed matching filename / providerName
     ********************************************************************/

    public static function feedDataToID($file_name, $providerName)
    {
        global $etcore;
        $feedDataToID = 'feedDataToID' . $etcore->callSuffix;
        return ETCPF_FeedActivityLog::$feedDataToID($file_name, $providerName);
    }

    public function getDetailsIfAlreadyExists($file_name, $providerName)
    {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'etcpf_feeds';
        $sql = "SELECT * from $feed_table WHERE `filename`='$file_name' AND `type`='$providerName'";
        $list_of_feeds = $wpdb->get_row($sql);
        if ($list_of_feeds) {
            return $list_of_feeds;
        } else {
            return false;
        }
    }

    private static function feedDataToIDW($file_name, $providerName)
    {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'etcpf_feeds';
        $sql = "SELECT * from $feed_table WHERE `filename`='$file_name' AND `type`='$providerName'";
        $list_of_feeds = $wpdb->get_results($sql, ARRAY_A);
        if ($list_of_feeds) {
            return $list_of_feeds[0]['id'];
        } else {
            return -1;
        }
    }

    private static function feedDataToIDWe($file_name, $providerName)
    {
        return ETCPF_FeedActivityLog::feedDataToIDW($file_name, $providerName);
    }

    /********************************************************************
     * Called from outside... this class has to make sure the feed shows under "Manage Feeds"
     ********************************************************************/

    public static function updateFeedList($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $remote_category_path, $full_taxonomy_path, $cron)
    {
        $id = ETCPF_FeedActivityLog::feedDataToID($file_name, $providerName);
        if ($id == -1)
            ETCPF_FeedActivityLog::addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount, $remote_category_path, $full_taxonomy_path);
        else
            ETCPF_FeedActivityLog::updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount, $remote_category_path, $full_taxonomy_path, $cron);
    }

    public static function updateCustomFeedList($category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {
        $category = implode(',', $category);
        $remote_category = implode('::', $remote_category);
        $id = ETCPF_FeedActivityLog::feedDataToID($file_name, $providerName);
        if ($id == -1)
            ETCPF_FeedActivityLog::addNewFeedData($category, $remote_category, $file_name, $file_path, $providerName, $productCount);
        else
            ETCPF_FeedActivityLog::updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount);
    }

    public function updateEtsyCustomFeed($id = false, $args)
    {
        if ($id !== false) {
            $trans = $this->UpdateEtsyCustomFeedRecord($id, $args);
            if ($trans) {
                return $trans;
            }
            return false;
        } else {
            $trans = $this->insertEtsyCustomFeedRecord($args);
            if ($trans) {
                return $trans;
            } else {
                return false;
            }
        }
    }

    public function UpdateEtsyCustomFeedRecord($id, $args)
    {
        $args = (object)$args;
        global $wpdb;
        $table = $wpdb->prefix . 'etcpf_feeds';
        $data = array(
            'category' => 0,
            'remote_category' => 'will_be_got_from_mapping_table',
            'filename' => $args->filename,
            'url' => $args->fileurl,
            'type' => 'Etsy',
            'product_count' => $args->product_count,
            'texonomy_path' => 'will_be_got_from_mapping_table',
            'category_path' => 'will_be_got_from_mapping_table',
            'feed_type' => $args->feedtype,
            'updated_at' => date('Y-m-d H:i:s')
        );
        if ($wpdb->update($table, $data, array('id' => $id))) {
            $insertcategoryMapping = $this->UpdateEtsyCategoryMapping($id, $args->categories);
            return $id;
        }
        return false;
    }

    public function InsertFeedProductsForCustomFeed($feed_id, $args)
    {
        if ($args) {
            global $wpdb;
            $table = $wpdb->prefix . 'etcpf_custom_feed_products';
            $products = $args['products'];
            $trans = true;
            foreach ($products as $key => $product) {
                $products_data = array(
                    'feed_id' => $feed_id,
                    'product_id' => $key,
                    'added_date' => date('Y-m-d H:i:s')
                );
                if (isset($product['child'])) {
                    $products_data['children'] = json_encode($product['child']['ids']);
                }
                if ($wpdb->insert($table, $products_data)) {
                    $trans = true;
                } else {
                    $trans = false;
                }
            }
            if ($trans == true) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    public function UpdateFeedProductsForCustomFeed($feed_id, $args)
    {
        if ($args) {
            global $wpdb;
            $table = $wpdb->prefix . 'etcpf_custom_feed_products';
            /*Delete All the previous products before insertion*/
            if ($wpdb->delete($table, array('feed_id' => $feed_id))) {
                $products = $args['products'];
                $trans = true;
                foreach ($products as $key => $product) {
                    $products_data = array(
                        'feed_id' => $feed_id,
                        'product_id' => $key,
                        'added_date' => date('Y-m-d H:i:s')
                    );
                    if (isset($product['child'])) {
                        $products_data['children'] = json_encode($product['child']['ids']);
                    }
                    if ($wpdb->insert($table, $products_data)) {
                        $trans = true;
                    } else {
                        $trans = false;
                    }
                }
                if ($trans == true) {
                    return true;
                }
                return false;
            }
            return false;

        } else {
            return false;
        }
    }

    public function insertEtsyCustomFeedRecord($args)
    {
        $args = (object)$args;
        global $wpdb;
        $table = $wpdb->prefix . 'etcpf_feeds';
        $data = array(
            'category' => 0,
            'remote_category' => 'will_be_got_from_mapping_table',
            'filename' => $args->filename,
            'url' => $args->fileurl,
            'type' => 'Etsy',
            'product_count' => 0,
            'texonomy_path' => 'will_be_got_from_mapping_table',
            'category_path' => 'will_be_got_from_mapping_table',
            'feed_type' => $args->feedtype,
            'updated_at' => date('Y-m-d H:i:s')
        );
        if ($wpdb->insert($table, $data)) {
            $feed_id = $wpdb->insert_id;
            $insertcategoryMapping = $this->InsertEtsyCategoryMapping($feed_id, $args->categories);
            return $feed_id;
        }
        return false;
    }

    public function InsertEtsyCategoryMapping($feed_id, $categories)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'etcpf_category_mappings';
        $trans = true;
        foreach ($categories['categories'] as $key => $category) {
            $data = array(
                'feed_id' => $feed_id,
                'local_category_slug' => $key,
                'remote_category' => $category['remote_category'],
                'texonomy_path' => $category['taxonomy_path'],
                'showValue' => $category['showValue'],
                'child_count' => $category['child_count'],
                'parent_count' => $category['parent_count'],
                'updated_at' => date('Y-m-d H:i:s')
            );
            if ($wpdb->insert($table, $data)) {
                $data = null;
                unset($data);
            } else {
                $trans = false;
            }
        }
        if ($trans == true) {
            return true;
        }
        return false;
    }

    public function UpdateEtsyCategoryMapping($feed_id, $categories)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'etcpf_category_mappings';
        $trans = true;
        if ($feed_id && isset($categories)) {
            if (array_key_exists('categories', $categories)) {
                $categories = $categories['categories'];
            }
            if ($wpdb->delete($table, array('feed_id' => $feed_id))) {
                foreach ($categories as $key => $category) {
                    $data = array(
                        'feed_id' => $feed_id,
                        'local_category_slug' => $key,
                        'remote_category' => $category['remote_category'],
                        'texonomy_path' => $category['taxonomy_path'],
                        'showValue' => $category['showValue'],
                        'child_count' => $category['child_count'],
                        'parent_count' => $category['parent_count'],
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    if ($wpdb->insert($table, $data)) {
                        $data = null;
                        unset($data);
                    } else {
                        $trans = false;
                    }
                }
                if ($trans == true) {
                    return true;
                }
                return false;
            }
        }
    }

    public function deleteFeedByID($feedId)
    {
        global $wpdb;
        $feedTable = $wpdb->prefix . 'etcpf_feeds';
        $categoryMappingTable = $wpdb->prefix . 'etcpf_category_mappings';
        $productSavedTable = $wpdb->prefix . 'etcpf_custom_feed_products';
        if ($feedId) {
            $wpdb->delete($feedTable, array('id' => $feedId));
            $wpdb->delete($categoryMappingTable, array('feed_id' => $feedId));
            $wpdb->delete($productSavedTable, array('feed_id' => $feedId));
            return true;
        }
        return false;
    }

    /********************************************************************
     * Update a record in the activity log
     ********************************************************************/

    private static function updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount, $remote_category_path, $full_taxonomy_path, $cron)
    {
        global $etcore;
        $updateFeedData = 'updateFeedData' . $etcore->callSuffix;
        ETCPF_FeedActivityLog::$updateFeedData($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount, $remote_category_path, $full_taxonomy_path, $cron);
    }

    /**
     * @param $id
     * @param $category
     * @param $remote_category
     * @param $file_name
     * @param $file_path
     * @param $providerName
     * @param $productCount
     */
    private static function updateFeedDataW($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount, $remote_category_path, $full_taxonomy_path, $cron)
    {
        global $wpdb;
        global $etcore;
        /*
         * $etcore->feed_type == 1 Custom product feed type
         * $etcore->feed_type == 0 Default product feed type
         * */
        /*$product_details = '';
                if ($etcore->feedType == 1) {
                    $feed_type = 1;
                    $sql = "SELECT * from {$wpdb->prefix}etcpf_custom_products;";
                    $product_details = maybe_serialize($wpdb->get_results($sql, ARRAY_A));
                }
                if ($etcore->feedType == 0) {
                    $feed_type = 0;
                    $product_details = NULL;
                }*/


        $feed_table = $wpdb->prefix . 'etcpf_feeds';

        if ($cron == true) {
            $ready_to_autoupload = 'true';
        } else {
            $ready_to_autoupload = 'true';
        }

        $data = array(
            'category' => $category,
            'remote_category' => $remote_category,
            'filename' => $file_name,
            'url' => $file_path,
            'type' => $providerName,
            'product_count' => $productCount,
            'texonomy_path' => $full_taxonomy_path,
            'category_path' => $remote_category_path,
            'updated_at' => date('Y-m-d H:i:s')
        );
        if ($wpdb->update($feed_table, $data, array('id' => $id))) {
            return true;
        }
        return false;

        /*$sql = "
            UPDATE $feed_table
            SET
                `category`='$category',
                `remote_category`='$remote_category',
                `filename`='$file_name',
                `url`='$file_path',
                `type`='$providerName',
                `product_count`='$productCount',
                `texonomy_path` = '$full_taxonomy_path',
                `category_path` = '$remote_category_path',
                `updated_at` = ".date('Y-m-d H:i:s')."
            WHERE `id`=$id;";
        $wpdb->query($sql);*/
    }

    private static function updateFeedDataWe($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount)
    {
        ETCPF_FeedActivityLog::updateFeedDataW($id, $category, $remote_category, $file_name, $file_path, $providerName, $productCount);
    }

    /********************************************************************
     * Save a Feed Phase
     ********************************************************************/

    function logPhase($activity)
    {
        global $etcore;
        $etcore->settingSet('etcpf_etsyfeedActivity_' . $this->feedIdentifier, $activity);
    }

    function deleteLogDataW()
    {
        delete_option('etcpf_etsyfeedActivity_' . $this->feedIdentifier);
    }
}
