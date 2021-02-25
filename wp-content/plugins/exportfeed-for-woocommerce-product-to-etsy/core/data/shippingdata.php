<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
//Retrieves user-defined shipping settings and saves into class-local variable
class ETCPF_ShippingData
{

    function __construct($parentfeed)
    {
        global $etcore;
        $loadProc = 'loadShippingData' . $etcore->callSuffix;

        return $this->$loadProc($parentfeed);
    }

    function loadShippingDataW($parentfeed)
    {
    }

    function loadShippingDataWe($parentfeed)
    {
    }

}