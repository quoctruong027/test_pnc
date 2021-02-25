<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ETCPF_FoundAttribute
{

    public $attributes;
    public $attrOptionsTableName = '';
    public $attrOptions;

    function __construct()
    {
        global $etcore;
        $fetchAttributes = 'fetchAttributes' . $etcore->callSuffix;
        $this->$fetchAttributes();
    }

    function fetchAttributesW()
    {
        //From WordPress / Woocommerce
        global $wpdb;
        $attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
        $this->attrOptionsTableName = $wpdb->prefix . 'options';
        $sql = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
        $this->attributes = $wpdb->get_results($sql);
    }

    function fetchAttributesWe()
    {
        //From WordPress / WP-ECommerce
        $this->attributes = array();
        global $wpdb;
        $this->attrOptionsTableName = $wpdb->prefix . 'options';
        $sql = "
			SELECT terms.name as attribute_name 
			FROM $wpdb->term_taxonomy taxo 
			LEFT JOIN $wpdb->terms terms ON taxo.term_id = terms.term_id
			WHERE (taxo.parent = 0) AND (taxo.taxonomy = 'wpsc-variation')";
        $this->attributes = $wpdb->get_results($sql);
    }

    /*function fetchAttrOptions($attrVal) {
      global $wpdb;
      $sql = "SELECT option_value FROM " . $this->attrOptionsTableName . " WHERE option_name='" . $attrVal . "'";
      $this->attrOptions = $wpdb->get_results($sql);
    }*/

}

class ETCPF_FoundOptions
{

    public $option_value = '';

    function __construct($service_name, $attribute)
    {
        global $etcore;
        $internalFetch = 'internalFetch' . $etcore->callSuffix;
        $this->$internalFetch($service_name, $attribute);
    }

    function internalFetchW($service_name, $attribute)
    {
        $this->option_value = get_option($service_name . '_cpf_' . $attribute);
    }

    function internalFetchWe($service_name, $attribute)
    {
        $this->option_value = get_option($service_name . '_cpf_' . $attribute);
    }

}