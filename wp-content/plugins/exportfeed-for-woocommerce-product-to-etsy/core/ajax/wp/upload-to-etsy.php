<?php
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
require_once dirname(__FILE__) . '/../../classes/etsyclient.php';
$etsy = new ETCPF_Etsy;
$etsy->timeToUpload();