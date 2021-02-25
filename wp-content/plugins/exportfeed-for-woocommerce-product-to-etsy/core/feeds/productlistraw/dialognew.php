<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 3.0
 * Export a Product List to XML format (Not for any particular destination)
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2015-04
 ********************************************************************/
class ETCPF_ProductlistrawDialog extends ETCPF_PBaseFeedDialog
{
    function __construct()
    {
        parent::__construct();
        $this->service_name = 'Productlistraw';
        $this->service_name_long = 'Product List RAW Export';
        $this->options = array();
        $this->blockCategoryList = true;
    }
    function categoryList($initial_remote_category = 'unknown')
    {
        return '<input type="hidden" name="remote_category" id="remote_category" value="unknown" />';
    }

}
