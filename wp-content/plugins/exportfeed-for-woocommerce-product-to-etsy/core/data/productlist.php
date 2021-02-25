<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class ETCPF_PAProduct
{

    public $id = 0;
    public $title = '';
    public $taxonomy = '';
    public $imgurls;
    public $attributes;

    function __construct()
    {
        $this->imgurls = array();
        $this->attributes = array();
    }
}

class ETCPF_ProductEntry
{
    public $taxonomyName;
    public $ProductID;
    public $Attributes;

    function __construct()
    {
        $this->Attributes = array();
    }

    function GetAttributeList()
    {
        $result = '';
        foreach ($this->Attributes as $ThisAttribute) {
            $result .= $ThisAttribute . ', ';
        }
        return '[' . $this->Name . '] ' . substr($result, 0, -2);
    }
}

global $etcore;
$productListScript = 'productlist' . strtolower($etcore->callSuffix) . '.php';
require_once $productListScript;