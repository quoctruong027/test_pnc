<?php
if (!defined("ABSPATH")) exit;
require_once 'core/classes/cron.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

function etcpf_activate_plugin()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // feed table
    $table_name = $wpdb->prefix . "etcpf_feeds";
    $sql = "CREATE TABLE `$table_name` (
          `id` bigint(20) AUTO_INCREMENT,
          `category` varchar(250) NOT NULL,
          `remote_category` varchar(1000) NOT NULL,
          `filename` varchar(250) NOT NULL,
          `url` varchar(500) NOT NULL,
          `type` varchar(50) NOT NULL DEFAULT 'etsy',
          `own_overrides` int(10) DEFAULT NULL,
          `feed_overrides` text,
          `product_count` int(11) DEFAULT NULL,
          `feed_errors` text,
          `feed_title` varchar(250) DEFAULT NULL,
          `feed_type` int(10) DEFAULT NULL,
          `category_path` varchar(250) NOT NULL,
          `texonomy_path` varchar(250) NOT NULL,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `product_details` blob,
          `variation_upload_profile` int (11),
          `variation_upload_type` varchar (50),
          PRIMARY KEY (id)
        ) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "etcpf_variationupload_preparation";
    $sql = "CREATE TABLE `$table_name` (
           `id` int(11) NOT NULL AUTO_INCREMENT,
            `profile_id` int(11) NOT NULL,
            `variation_attribute` varchar(255) NOT NULL,
            `prefix` varchar(255),
            `suffix` varchar(255),
            `position` int(11),
            PRIMARY KEY (id)
        ) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "etcpf_profiles";
    $sql = "CREATE TABLE `$table_name` (
           `id` int(11) NOT NULL AUTO_INCREMENT,
            `profile_name` varchar(255) NOT NULL,
            `profile_description` varchar(255),
            `attribute_seperator` varchar(255),
            PRIMARY KEY (id)
        ) $charset_collate";
    dbDelta($sql);


    $table_name = $wpdb->prefix . "etcpf_settings";
    $sql = "CREATE TABLE `$table_name` (
          `id` bigint(20) AUTO_INCREMENT,
          `_settings_mkey` text NOT NULL,
          `_settings_mvalue` text NOT NULL,
          `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "etcpf_image_links";
    $sql = "CREATE TABLE `$table_name` (
          `id` bigint(20) AUTO_INCREMENT,
          `parent_product_id` bigint(20),
          `remote_image_id` varchar(255) NOT NULL,
          `image_name` varchar(255) NOT NULL,
          `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) $charset_collate";
    dbDelta($sql);

    $data = array(
        array(
            '_settings_mkey'=>'etsy_api_limit',
            '_settings_mvalue' => '5000'
        ),
        array(
            '_settings_mkey'=>'state',
            '_settings_mvalue' => 'draft'
        ),
        array(
            '_settings_mkey'=>'when_made',
            '_settings_mvalue' => '2010_2019'
        ),
        array(
            '_settings_mkey'=>'is_supply',
            '_settings_mvalue' => '0'
        ),
        array(
            '_settings_mkey'=>'who_made_it',
            '_settings_mvalue' => 'collective'
        ),
        array(
            '_settings_mkey'=>'shop_language',
            '_settings_mvalue' => 'en-US'
        )
    );

    do_action('etcpf_insert_into_db',array('table'=>$wpdb->prefix . "etcpf_settings",'data'=>$data));

    $table_name = $wpdb->prefix . "etcpf_etsy_product_count";
    $sql = "CREATE TABLE `$table_name` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `active` int(11) NOT NULL,
            `draft` int(11) NOT NULL,
            `inactive` int(11) NOT NULL,
            PRIMARY KEY (id)
            ) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "etcpf_etsy_sync";
    $sql = "CREATE TABLE `$table_name` (
           `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `title` varchar(255) NOT NULL,
            `listing_id` varchar(255) NOT NULL,
            `quantity` int(11) NOT NULL,
            `sku` text NOT NULL,
            `state` varchar(255) NOT NULL,
            `prepare_data` text NOT NULL,
            `message` text NOT NULL,
            `error_status` int(11) NOT NULL,
            `mapped_status` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (id)
        ) $charset_collate";
    dbDelta($sql);

// etsy uploaded products
    $table_name = $wpdb->prefix . "etcpf_listings";
    $sql = "CREATE TABLE `$table_name` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `item_id` bigint(20) NOT NULL,
    `title` varchar(300) NOT NULL,
    `has_variation` enum('1','0') NOT NULL DEFAULT '0',
    `feed_id` int(11) NOT NULL,
    `uploaded` bigint(20) DEFAULT '0',
    `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_updated` datetime DEFAULT NULL,
    `upload_result` text DEFAULT NULL,
    `variation_upload_result` text DEFAULT NULL,
    `data` text,
    `listing_id` varchar(255) DEFAULT NULL,
    `listing_image_id` varchar(100) DEFAULT NULL,
    `additional_image_id` text DEFAULT NULL,
    `item_group_id` int(11) DEFAULT NULL,
    `etsy_status` enum('removed','existing') NOT NULL DEFAULT 'existing',
    `prepared_data` text NOT NULL,
    `needs_to_reupload` enum('true','false') NOT NULL DEFAULT 'false',
    `error` varchar(255) NOT NULL,
    `etsy_state` enum('active','draft','inactive','removed') NOT NULL DEFAULT 'draft',
    `sku` varchar(100) NOT NULL,
     PRIMARY KEY (id)
  ) $charset_collate";
    dbDelta($sql);


    $table_name = $wpdb->prefix . "etcpf_orders";
    $sql = "CREATE TABLE `$table_name` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar (300) NOT NULL,
    `transaction_id` varchar(100) NOT NULL,
    `seller_user_id` varchar(100) NOT NULL ,
    `buyer_user_id` varchar(100) NOT NULL ,
    `creation_tsz` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `paid_tsz` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `shipped_tsz` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ordered_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `price` FLOAT NOT NULL ,
    `currency_code` varchar(10), 
    `quantity` int (11),
    `tags` varchar (300),
    `materials` varchar (300),
    `image_listing_id` varchar (100),
    `receipt_id` varchar (30),
    `shipping_cost` FLOAT NOT NULL ,
    `listing_id` varchar(50) NOT NULL ,
    `transaction_type` varchar(20) NOT NULL ,
    `url` varchar(100) NOT NULL ,
    `variations` TEXT NOT NULL ,
    `product_data` TEXT NOT NULL ,
    `product_id` int(11) NOT NULL ,
    `parent_sku` varchar (50) NOT NULL ,
    `offerings` TEXT NOT NULL ,
    `buyers_info` TEXT NOT NULL ,
    `state` enum('shipped','pending','cancelled') NOT NULL DEFAULT 'pending',
     PRIMARY KEY (id),
     INDEX receipt_id (receipt_id)
  ) $charset_collate";
    dbDelta($sql);


    // etsy variation listing products
    $table_name = $wpdb->prefix . "etcpf_listing_variations";
    $sql = "CREATE TABLE `$table_name` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `item_id` int(11) NOT NULL,
    `parent_id` int(11) NOT NULL,
    `data` text NOT NULL,
    `upload` int(11) NOT NULL,
    `listing_id` int(11) NOT NULL,
    `submitted` datetime NOT NULL,
    `updated` datetime NOT NULL,
     PRIMARY KEY (id)
  ) $charset_collate";
    dbDelta($sql);


    $table_name = $wpdb->prefix . "etcpf_custom_feed_products";
    $sql = "CREATE TABLE `$table_name` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `feed_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `children` varchar (300) NOT NULL,
    `added_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_date` datetime NULL,
     PRIMARY KEY (id)
  ) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "etcpf_category_mappings";
    $sql = "CREATE TABLE `$table_name` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `feed_id` int(11) NOT NULL,
    `child_count` int(11) NOT NULL,
    `parent_count` int(11) NOT NULL,
    `local_category_slug` VARCHAR (255) NULL,
    `remote_category` varchar(255) NULL,
    `texonomy_path` varchar(255) NULL,
    `showValue` varchar(255) NULL,
    `updated_at` datetime NULL,
     PRIMARY KEY (id)
  ) $charset_collate";
    dbDelta($sql);

    /*Etsy error product table */
    $table_name = $wpdb->prefix . "etcpf_resolved_product_data";
    $sql = "CREATE TABLE `$table_name` (
         `id` int(11) AUTO_INCREMENT NOT NULL,
          `feed_id` int(11) NOT NULL,
          `product_id` int(11) NOT NULL,
          `attribute` varchar(255) NOT NULL,
          `value` text NOT NULL,
          `error_code` int(11) NOT NULL,
          PRIMARY KEY (id)
        ) $charset_collate";
    dbDelta($sql);

    /* Etsy error feeds data */
    $table_name = $wpdb->prefix . "etcpf_feedproducts";
    $sql = "CREATE TABLE `$table_name` (
         `id` int(11) AUTO_INCREMENT NOT NULL,
          `p_id` int(11) NOT NULL,
          `parent_id` int(11) DEFAULT NULL,
          `sku` varchar(255) DEFAULT NULL,
          `p_name` varchar(255) DEFAULT NULL,
          `error_status` enum('1','0','-1','2') NOT NULL DEFAULT '1' COMMENT '1=successful, -1=fatalerror, 0=warning,2=Resolved',
          `error_code` int(11) NOT NULL,
          `prod_categories` varchar(300) NOT NULL,
          `feed_id` varchar (255) NOT NULL,
          `message` varchar(255) NOT NULL,
          `status` varchar (30) DEFAULT 'inactive',
          PRIMARY KEY (id)
        ) $charset_collate";
    dbDelta($sql);

    //etsy_configuration table
    $table_name = $wpdb->prefix . "etcpf_etsy_configuration";
    $sql = "CREATE TABLE `$table_name` (
          `id` int(11) AUTO_INCREMENT,
          `configuration_title` varchar(45) DEFAULT NULL,
          `configuration_value` varchar(45) DEFAULT NULL,
          `configuration_description` text,
          `options` varchar(30) DEFAULT '0',
          PRIMARY KEY (id)
        ) $charset_collate";
    dbDelta($sql);

    //insert data for configuration table

    $data = array(
        array(
            'configuration_title' => 'who_made',
            'configuration_value' => 'collective',
            'configuration_description' => 'Examples: I did.<a href="https://www.etsy.com/developers/documentation/reference/listing#method_createlisting" target="_blank">see documentation</a>',
            'options' => 'i_did,collective,someone_else'
        ),
        array(
            'configuration_title' => 'when_made',
            'configuration_value' => '2010_2019',
            'configuration_description' => '1980_2000,2000_2010,2010_2017/16 <a href="https://www.etsy.com/developers/documentation/reference/listing#method_createlisting" target="_blank">see documentation</a>',
            'options' => '0'
        ),
        array(
            'configuration_title' => 'state',
            'configuration_value' => 'draft',
            'configuration_description' => 'Examples: active,draft,inactive <a href="https://www.etsy.com/developers/documentation/reference/listing#method_createlisting" target="_blank">see documentation</a>',
            'options' => 'active,draft,inactive'
        ),
        array(
            'configuration_title' => 'etsy_api_limit',
            'configuration_value' => '5000',
            'configuration_description' => 'The Api Limit of your shop can be found <a href="https://www.etsy.com/developers/your-apps" target="_blank">here</a>',
            'options' => '0'
        ),
        array(
            'configuration_title' => 'etsy_calculated_shipping',
            'configuration_value' => '',
            'configuration_description' => 'Calculated shipping templates of etsy, configured in etsy shopmanager.',
            'options' => '0'
        )
    );

    foreach ($data as $key => $datum) {
        $sql = $wpdb->prepare("SELECT id FROM $table_name WHERE configuration_title = %s", $datum['configuration_title']);
        $knownID = $wpdb->get_var($sql);
        if (!$knownID) {
            $wpdb->insert($table_name, $datum);
        }
    }

    // shipping template from etsy
    $table_name = $wpdb->prefix . "etcpf_shipping_template";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE `$table_name` (
        `id` int(11) AUTO_INCREMENT,
        `shipping_template_id` varchar(100) DEFAULT NULL,
        `title` varchar(255) DEFAULT NULL,
        `processing_days_display_label` text,
        `country` varchar(255) DEFAULT NULL,
        PRIMARY KEY (id)
      	) $charset_collate";
        dbDelta($sql);
    }
}

function etcpf_deactivate_plugin()
{
    $next_refresh = wp_next_scheduled('update_etsyfeeds_hook');
    if ($next_refresh) {
        wp_unschedule_event($next_refresh, 'update_etsyfeeds_hook');
    }
}

add_action('etcpf_insert_into_db','insertintoTable');
function insertintoTable($params)
{
    global $wpdb;
    foreach ($params['data'] as $key => $value) {
        if(!get_etsy_settings($value['_settings_mkey'])){
            $wpdb->insert($params['table'], $value);
        }
    }
}
