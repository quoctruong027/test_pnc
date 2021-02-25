<?php

Class singleVariationUploadManager
{

    private $db;
    private $preparationtable;
    private $profileTable;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->preparationtable = $wpdb->prefix . 'etcpf_variationupload_preparation';
        $this->profileTable = $wpdb->prefix . 'etcpf_profiles';
    }


    public function getCustomizedata($feed_id, $attribute, $profileid)
    {
        return $this->db->get_row($this->db->prepare("SELECT * FROM {$this->preparationtable} WHERE profile_id=%d AND (variation_attribute=%s OR variation_attribute=%s)", array($profileid, $attribute, str_replace('-',' ', $attribute))));
    }

    public function getSeperator($profileid){
        return $this->db->get_var($this->db->prepare("SELECT attribute_seperator FROM {$this->profileTable} WHERE id=%d", array($profileid)));
    }

    public function getFormattedVariation($available_variations, $feed_id, $profileid)
    {
        $seperator = $this->getSeperator($profileid);
        $EtsyFormatVariation = array();
        $images = array();
        foreach ($available_variations as $available_variation) {
            $attributesNames = array();
            $attributesValues = array();
            $s = 100;
            foreach ($available_variation['attributes'] as $key => $attributes) {
                $standardAttribute = str_replace(array('attribute_pa_', 'attribute_'), '', $key);
                $term_details = get_term_by('slug', $attributes, 'pa_' . $standardAttribute);
                $customizedData = $this->getCustomizedata($feed_id, $standardAttribute, $profileid);
                if ($customizedData) {
                    $attributesNames[$customizedData->position] = $customizedData->variation_attribute;
                    $attributes = isset($term_details->name) ? $term_details->name :
                        ($attributes ? $attributes : 'any');
                    $attributes = $customizedData->prefix ?
                        $customizedData->prefix .' '. $attributes
                        : $attributes;
                    $attributes = $customizedData->suffix ?
                        $attributes .' '.$customizedData->suffix
                        : $attributes;
                    $attributesValues[$customizedData->position] = $attributes;
                } else {
                    $attributesNames[$s] = $standardAttribute;
                    $attributesValues[$s] = isset($term_details->name) ? $term_details->name :
                        ($attributes ? $attributes : 'any');
                    $s++;
                }
            }

            ksort($attributesNames,SORT_NUMERIC);
            ksort($attributesValues, SORT_NUMERIC);

            $concatinatedValue = implode($seperator, $attributesValues);
            $concatinatedName = implode($seperator, $attributesNames);
            $propertyValues = array(
                array(
                    'property_id' => 513,
                    'property_name' => $concatinatedName,
                    'values' => $concatinatedValue
                )
            );

            $EtsyFormatVariation[] = array('property_values' => $propertyValues,
                'sku' => $available_variation['sku'],
                'offerings' => array(
                    array(
                        'price' => $available_variation['display_price'],
                        'quantity' => $available_variation['is_in_stock'] ? ($available_variation['max_qty'] ? $available_variation['max_qty'] : $available_variation['min_qty']) : $available_variation['min_qty'],
                        'is_enabled' => true
                    )
                )
            );
            $imgArray = explode('/',$available_variation['image']['full_src']);
            $imgname = end($imgArray);
            $images[$available_variation['sku']] = $imgname;
        }
        update_option('etcpf_variation_image_linkls',maybe_serialize($images));
        return $EtsyFormatVariation;
    }

}
