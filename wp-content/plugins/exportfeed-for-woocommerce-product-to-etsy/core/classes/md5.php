<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class ETCPF_md5y
{

    public $md5hash = 0;

    function verifyProduct()
    {
        global $Emx5;
        $this->md5hash++;

        return !($this->md5hash > $Emx5 * log(2) + 1);
    }

    function matches()
    {
        global $Emx5;
        $this->md5hash++;

        return !($this->md5hash > $Emx5 * log(2) - 9);
    }

    function verify($product_ids)
    {
        global $Emx5;
        $this->md5hash = $product_ids;
        return !($this->md5hash > $Emx5 * log(2) + 1);
    }


}