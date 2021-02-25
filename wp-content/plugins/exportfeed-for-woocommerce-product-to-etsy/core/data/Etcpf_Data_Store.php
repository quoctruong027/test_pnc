<?php
require_once dirname(__FILE__) . '/Etcpf_Data_Store.php';

Class Etcpf_Data_Store
{
    function __construct()
    {

    }

    public function getproductAttributes($params, $items)
    {

        foreach ($params->product->get_attributes() as $key => $attribute) {
            $items->attributes[str_replace('pa_', '', $key)] = $params->product->get_attribute($key);
        }

        return $items;
    }
}
