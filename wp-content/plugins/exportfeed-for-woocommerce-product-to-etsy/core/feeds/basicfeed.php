<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
require_once dirname(__FILE__) . '/../data/rules.php';

class ETCPF_BasicFeed
{

    public $activityLogger = null; //If set, someone wants me to log what phase I'm at in feed-generation';
    public $aggregateProviders = array();
    public $allow_additional_images = true;
    public $allow_attributes = true;
    public $allow_attribute_details = false; //old style attribute detection = source of minor hitches
    public $allow_variation_permutations = false;
    public $allowRelatedData = true;
    public $attributeAssignments = array();
    public $attributeDefaults = array();
    public $attributeDefaultStages = array(0, 0, 0, 0, 0, 0);
    public $attribute_granularity = 5; //0=basic feed(future) 1..4=minimal_postmeta_conversion 5=all_postmeta
    public $attributeMappings = array();
    public $auto_free = true; //Allow descendants to retain productlist
    public $auto_update_feedlist = true;
    public $categories;
    public $create_attribute_slugs = false;
    public $current_category; //This is the active category while in formatProduct()
    public $currency;
    public $currency_shipping = ''; //(Currently uses currency_format)
    public $currency_format = '%1.2f';
    public $dimension_unit;
    public $errors = array();
    public $fileformat = 'xml';
    public $fieldDelimiter = "\t"; //For CSVs
    public $fields; //For CSVs
    public $feed_category;
    public $feedOverrides;
    public $forceCData = false; //Applies to ProductListXML only
    public $force_all_categories = false;
    public $force_currency = false;
    //public $force_featured_imgurl = false; //forces feature_imgurl even for variations
    public $force_featured_image = false; //forces feature_imgurl even for variations
    public $force_wc_api = false;
    public $get_wc_shipping_attributes = false;
    public $get_wc_shipping_class = false;
    public $get_tax_rates = false;
    public $gmc_enabled = false; //Allow Etsy merchant centre woothemes extension (WordPress)
    public $gmc_attributes = array(); //If anything added in here, restrict GMC list to these
    public $has_header = true;
    public $has_footer = true;
    public $has_product_range = false;
    public $ignoreDuplicates = true; //useful when products are assigned multiple categories and insufficient identifiers to distinguish them
    public $lang = '';
    public $max_description_length = 10000;
    //public $max_custom_field = 50000;
    public $message = ''; //For Error detection
    public $permutation_base_id = 1000000; //Fix to more than the max # of products and posts
    public $permutation_variant_multiplier = 1000; //Max # of "any" variants per product. Note: High values will cause IDs to spiral into the billions
    public $providerName = '';
    public $providerNameL = '';
    public $productCount = 0; //Number of products successfully exported
    public $productList;
    public $productTypeFromLocalCategory = false;
    public $providerType = 0;
    public $relatedData = array();
    public $reversible = false; //Feed accepts input data
    public $rules = array();
    //public $sellerName = ''; //Required Bing attribute - Merchant/Store that provides this product
    public $success = false;
    public $stripHTML = false;
    public $updateObject = null;
    public $utf8encode = false; //Temporary until a better encoding system can be engineered
    public $timeout = 0; //If >0 try to override max_execution time
    public $weight_unit;

    public $remote_category_path;
    public $full_taxonomy_path;
    public $preparedReports = array();
    public $insertintodb = false;
    public $usermappedAttributes = array();
    public $no_more_product_in_feed = false;

    public $getProductOfParticularCategory = false;

    public function addAttributeDefault($attributeName, $value, $defaultClass = 'ETCPF_AttributeDefault')
    {

        if (!class_exists($defaultClass)) {
            $this->addErrorMessage(5, 'AttributeDefault class "' . $defaultClass . '" not found. Reconfigure Advanced Commands to resolve.');
            return;
        }
        $thisDefault = new $defaultClass();
        //$thisDefault = new ETCPF_AttributeDefault();
        $thisDefault->attributeName = $attributeName;
        $thisDefault->value = $value;
        $thisDefault->parent_feed = $this;
        $tvalue = trim($value);
        if (strlen($tvalue) > 0 && $tvalue[0] == '$') {
            $thisDefault->value = trim($thisDefault->value);
            $thisDefault->isRuled = true;
        }
        $this->attributeDefaults[] = $thisDefault;
        $this->attributeDefaultStages[$thisDefault->stage] += 1;
        $thisDefault->initialize();
        return $thisDefault;
    }

    public function addAttributeMapping($attributeName, $mapTo, $usesCData = false, $isRequired = false, $isMapped = false)
    {

        $thisMapping = new stdClass();
        $thisMapping->attributeName = $attributeName;
        $thisMapping->mapTo = $mapTo;
        $thisMapping->enabled = true;
        $thisMapping->deleted = false;
        $thisMapping->usesCData = $usesCData;
        $thisMapping->isRequired = $isRequired;
        $thisMapping->systemDefined = false;
        $thisMapping->isMapped = $isMapped;
        $this->attributeMappings[] = $thisMapping;

        //Auto-delete any system defined Mappings with matching mapTo
        foreach ($this->attributeMappings as $mapping) {
            if ($mapping->systemDefined && $mapping->mapTo == $mapTo) {
                $mapping->deleted = true;
            }
        }

        return $thisMapping;
    }

    public function addErrorMessage($id, $msg, $isWarning = false)
    {

        //Allows descendent providers to report errors
        if (!isset($this->errors[$id])) {
            $error = new stdClass();
            $error->msg = $msg;
            $error->occurrences = 0;
            $error->isWarning = $isWarning;
            $this->errors[$id] = $error;
        }
        $this->errors[$id]->occurrences++;

    }

    public function addRule($ruleName, $ruleClass, $parameters = array(), $order = 0)
    {
        $className = 'ETCPF_FeedRule' . ucwords(strtolower($ruleClass));
        if (!class_exists($className)) {
            $this->addErrorMessage(5, 'Rule "' . $ruleClass . '" not found. Reconfigure Advanced Commands to resolve.');
            return null;
        }
        $thisRule = new $className();
        $thisRule->name = $ruleName;
        $thisRule->parameters = $parameters;
        $thisRule->parent_feed = $this;
        $thisRule->order = $order;
        $thisRule->initialize();
        $this->rules[] = $thisRule;
        return $thisRule;
    }

    function checkFolders()
    {

        global $message;

        $dir = ETCPF_FeedFolder::uploadRoot();
        if (!is_writable($dir)) {
            $message = $dir . ' should be writeable';
            return false;
        }

        $dir = ETCPF_FeedFolder::uploadFolder();
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        if (!is_writable($dir)) {
            $message = "$dir should be writeable";
            return false;
        }
        $dir2 = $dir . $this->providerName . '/';
        if (!is_dir($dir2)) {
            mkdir($dir2);
        }

        return true;
    }

    protected function containsNonUTF8Character($text)
    {
        for ($i = 0; $i < strlen($text); $i++) //if ($text[$i] > "\xFF")
        {
            if (ord($text[$i]) > 224) {
                return true;
            }
        }

        return false;
    }

    protected function continueFeed($category, $file_name, $file_path, $remote_category)
    {
        //Note: protected function because it will be deleted in some future version -KH
        $mode = "a";
        if ($this->updateObject->startValue == 0) {
            $mode = "w";
        }

        $this->fileHandle = fopen($file_name, $mode);
        if ($this->has_header && $this->updateObject->startValue == 0) {
            fwrite($this->fileHandle, $this->getFeedHeader($file_name, $file_path));
        }

        $this->productList->productStart = $this->updateObject->startValue;
        $this->productList->getProductList($this, $remote_category);
        $done = false;
        if ($this->productList->products == null || count($this->productList->products) < 50000) {
            $done = true;
        }

        if (isset($this->productList->products)) {
            $this->updateObject->startValue += count($this->productList->products);
            if (!isset($this->updateObject->productCount)) {
                $this->updateObject->productCount = 0;
            }

            $this->updateObject->productCount += $this->productCount;
        }
        if ($this->has_footer && $done) {
            $this->updateObject->finished = true;
            fwrite($this->fileHandle, $this->getFeedFooter($file_name, $file_path));
            $this->productCount = $this->updateObject->productCount;
            ETCPF_FeedActivityLog::updateFeedList($category, $remote_category, $file_name, $file_path, $this->providerName, $this->productCount, $this->remote_category_path, $this->full_taxonomy_path);
        }
        fclose($this->fileHandle);
    }

    protected function createFeed($file_name, $file_path, $remote_category)
    {
        //$file_name is (incorrectly) a url due to an unfortunate nomenclature left over from v2.x
        $this->fileHandle = fopen($file_name, "w");
        if ($this->has_header) {
            fwrite($this->fileHandle, $this->getFeedHeader($file_name, $file_path));
        }
        /*$this->productList->getProductByCategory($this, $remote_category);*/
        if ($this->getProductOfParticularCategory == true) {
            /** @Info: This is used when limit output is used */
            $this->productList->getProductByCategory($this, $remote_category);
        } else {
            $this->productList->getProductList($this, $remote_category);
        }
        if ($this->has_footer) {
            fwrite($this->fileHandle, $this->getFeedFooter($file_name, $file_path));
        }

        fclose($this->fileHandle);
    }

    protected function continueFeedCustom($category, $file_name, $file_path, $remote_category)
    {
        //Note: protected function because it will be deleted in some future version -KH
        $mode = "a";
        if ($this->updateObject->startValue == 0) {
            $mode = "w";
        }

        $this->fileHandle = fopen($this->filename, $mode);
        if ($this->has_header && $this->updateObject->startValue == 0) {
            fwrite($this->fileHandle, $this->getFeedHeader($file_name, $file_path));
        }

        $this->productList->productStart = $this->updateObject->startValue;
        $this->productList->getProductList($this, $remote_category);
        $done = false;
        if ($this->productList->products == null || count($this->productList->products) < 50000) {
            $done = true;
        }

        if (isset($this->productList->products)) {
            $this->updateObject->startValue += count($this->productList->products);
            if (!isset($this->updateObject->productCount)) {
                $this->updateObject->productCount = 0;
            }

            $this->updateObject->productCount += $this->productCount;
        }
        if ($this->has_footer && $done) {
            $this->updateObject->finished = true;
            fwrite($this->fileHandle, $this->getFeedFooter($file_name, $file_path));
            $this->productCount = $this->updateObject->productCount;
            ETCPF_FeedActivityLog::updateFeedList($category, $remote_category, $file_name, $file_path, $this->providerName, $this->productCount);
        }
        fclose($this->fileHandle);
    }

    protected function createFeedCustom($file_name, $file_path, $args)
    {
        //$file_name is (incorrectly) a url due to an unfortunate nomenclature left over from v2.x
        $this->fileHandle = fopen($file_name, "w");
        if ($this->has_header) {
            fwrite($this->fileHandle, $this->getFeedHeader($file_name, $file_path));
        }
        $this->productList->fetchAndwriteProducts($this, $args);
        if ($this->has_footer) {
            fwrite($this->fileHandle, $this->getFeedFooter($file_name, $file_path));
        }
        fclose($this->fileHandle);
        return true;
    }

    function fetchProductAttribute($name, $product)
    {
        $thisAttributeMapping = $this->getMapping($name);
        if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName])) {
            return $product->attributes[$thisAttributeMapping->attributeName];
        } else {
            return '';
        }

    }

    function finalizeRead()
    {
    }

    function formatLine($attribute, $value, $cdata = false, $leader_space = '')
    {
        //Prep a single line for XML
        //Allow the $attribute to be overridden
        if (isset($this->feedOverrides->overrides[$attribute]) && (strlen($this->feedOverrides->overrides[$attribute]) > 0)) {
            $attribute = $this->feedOverrides->overrides[$attribute];
        }

        $c_leader = '';
        $c_footer = '';

            if ($cdata == true) {
                $c_leader = '<![CDATA[';
                $c_footer = ']]>';
            }

            //if not CData, don't allow '&'
            if ($cdata == false) {
                $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
            }

            //Allow force strip HTML
            if ($this->stripHTML) {
                $value = strip_tags(html_entity_decode($value));
            }

        //UTF8Encode is guaranteed to create garbled text because we don't know the source encoding type
        //However, it will create a feed that will process, so it's a good temporary measure
        if ($this->utf8encode || $this->utf8encode == 1) {
            $value = utf8_encode($value);
            $attribute = utf8_encode($attribute);
        }

        if (gettype($value) == 'array') {
            $value = json_encode($value);
        }

        //Done
        return '
        ' . $leader_space . '<' . $attribute . '>' . $c_leader . $value . $c_footer . '</' . $attribute . '>';
    }

    function formatProduct($product)
    {
        return '';
    }

    public function getErrorMessages()
    {

        $error_messages = '';

        foreach ($this->errors as $index => $this_error) {
            if ($this_error->isWarning) {
                $prefix = 'Warning: ';
            } else {
                $prefix = 'Error: ';
            }

            $error_messages .= '<br>' . $prefix . $this_error->msg . '(' . $this_error->occurrences . ')';
        }

        return $this->message . $error_messages;
    }

    function getFeedData($category, $remote_category, $file_name, $saved_feed = null, $remote_category_path = null, $full_taxonomy_path = null, $cron = false)
    {
        $this->logActivity('Initializing...');
        global $message;
        global $etcore;
        $x = new ETCPF_EtsyValidation();
        $this->loadAttributeUserMap();
        if ($remote_category_path != null && $full_taxonomy_path != null) {
            $this->remote_category_path = $remote_category_path;
            $this->full_taxonomy_path = $full_taxonomy_path;
        }

        /*$dir = wp_upload_dir();
                    $upload_die = $dir['basedir'];
                    $myfile = fopen($upload_die . "/etsy.txt", "w") or die("Unable to open file!");
                    $txt = json_encode($this->full_taxonomy_path);
                    fwrite($myfile, $txt);
        */

        if ($this->updateObject == null) {
            $this->initializeFeed($category, $remote_category);
        } else {
            $this->resumeFeed($category, $remote_category, $this->updateObject);
        }

        $this->logActivity('Loading paths...');
        if (!$this->checkFolders()) {
            return;
        }

        $file_url = ETCPF_FeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
        $file_path = ETCPF_FeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;

        //Special (WordPress): where admin is https and site is http, path to wp-uploads works out incorrectly as https
        //  we check the content_url() for https... if not present, patch the file_path
        if (($etcore->cmsName == 'WordPress') && (strpos($file_path, 'https://') !== false) && (strpos(content_url(), 'https') === false)) {
            $file_path = str_replace('https://', 'http://', $file_path);
        }

        $this->file_path = $file_path;

        //Shipping and Taxation systems
        $this->shipping = new ETCPF_ShippingData($this);
        $this->taxData = new ETCPF_TaxationData($this);

        $this->logActivity('Initializing categories...');

        //Figure out what categories the user wants to export
        $this->categories = new ETCPF_ProductCategories($category);
        //Get the ProductList ready
        if ($this->productList == null) {
            $this->productList = new ETCPF_ProductList();
        }

        //Initialize some useful data
        //(must occur before overrides)
        $this->current_category = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
        $this->initializeOverrides($saved_feed);
        //Reorder the rules
        usort($this->rules, 'sort_rule_func');

        //Load relations into ProductList
        //Note: if relation exists, we don't overwrite
        foreach ($this->relatedData as $relatedData) {
            if (!isset($this->productList->relatedData[$relatedData[1]])) {
                $this->productList->relatedData[$relatedData[1]] = new ETCPF_ProductSupplementalData($relatedData[0]);
            }
        }

        //Trying to change max_execution_time will throw privilege errors on some installs
        //so it's been left as an option
        if ($this->timeout > 0) {
            ini_set('max_execution_time', $this->timeout);
        }

        //Add the space in pricestandard rules
        //if (strlen($this->currency) > 0)
        //	$this->currency = ' '.$this->currency;

        //Create the Feed
        $this->logActivity('Creating feed data');
        $this->filename = $file_url;
        if ($this->updateObject == null) {
            $this->createFeed($file_url, $file_path, $remote_category);
            //$transaction = $this->createFeedCustom($file_url, $file_path, $args);
        } else {
            $this->continueFeed($category, $file_url, $file_path, $remote_category);
        }

        $this->logActivity('Updating Feed List');
        if ($this->auto_update_feedlist) {
            ETCPF_FeedActivityLog::updateFeedList($category, $remote_category, $file_name, $file_path, $this->providerName, $this->productCount, $remote_category_path, $full_taxonomy_path, $cron);
        }

        if ($this->auto_free) {
            //Free the Attribute defaults
            for ($i = 0; $i < count($this->attributeDefaults); $i++) {
                unset($this->attributeDefaults[$i]);
            }

            //Free the Attribute Mapping Objects
            for ($i = 0; $i < count($this->attributeMappings); $i++) {
                unset($this->attributeMappings[$i]);
            }

            //De-allocate the overrides object to prevent chain dependency that made the core unload too early
            unset($this->feedOverrides);
        }

        if ($this->productCount <= 0) {
            $this->message .= '<br>No products returned';
            return;
        }

        $this->success = true;
    }

    /*function getCustomFeedData($file_name, $saved_feed = null)
    {
        $this->logActivity('Initializing...');
        global $message;
        global $etcore;
        //Here set feed type to 1 to differentiate between custom and default feed generation
        //        $etcore->feedType = 1;
        $x = new ETCPF_EtsyValidation();
        $this->loadAttributeUserMap();
        if ($this->updateObject == null) {
            $this->initializeFeed($category, $remote_category);
        } else {
            $this->resumeFeed($category, $remote_category, $this->updateObject);
        }

        $this->logActivity('Loading paths...');
        if (!$this->checkFolders()) {
            return;
        }

        $file_url = ETCPF_FeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
        $file_path = ETCPF_FeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;

        //Special (WordPress): where admin is https and site is http, path to wp-uploads works out incorrectly as https
        //  we check the content_url() for https... if not present, patch the file_path
        if (($etcore->cmsName == 'WordPress') && (strpos($file_path, 'https://') !== false) && (strpos(content_url(), 'https') === false)) {
            $file_path = str_replace('https://', 'http://', $file_path);
        }

        $this->file_path = $file_path;

        //Shipping and Taxation systems
        $this->shipping = new ETCPF_ShippingData($this);

        $this->taxData = new ETCPF_TaxationData($this);

        $this->logActivity('Initializing categories...');

        //Figure out what categories the user wants to export
        //$this->categories = new ETCPF_ProductCategories($cats);

        //Get the ProductList ready

        if ($this->productList == null) {
            $this->productList = new ETCPF_ProductList();
        }

        //Initialize some useful data
        //(must occur before overrides)
        //this might be used;
        //        $this->current_category = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));

        // advanced commands
        $this->initializeCustomOverrides($saved_feed);

        //Reorder the rules
        usort($this->rules, 'sort_rule_func');

        //Load relations into ProductList
        //Note: if relation exists, we don't overwrite
        foreach ($this->relatedData as $relatedData) {
            if (!isset($this->productList->relatedData[$relatedData[1]])) {
                $this->productList->relatedData[$relatedData[1]] = new ETCPF_ProductSupplementalData($relatedData[0]);
            }
        }

        //Trying to change max_execution_time will throw privilege errors on some installs
        //so it's been left as an option
        if ($this->timeout > 0) {
            ini_set('max_execution_time', $this->timeout);
        }

        //Add the space in pricestandard rules
        //if (strlen($this->currency) > 0)
        //	$this->currency = ' '.$this->currency;

        //Create the Feed
        $this->logActivity('Creating feed data');
        $this->filename = $file_url;
        // $products= $this->getProductListForCustomFeed();

        if ($this->updateObject == null) {
            $this->createFeedCustom($file_name, $file_path, $prod->remote_category);
        } else {
            $this->continueFeedCustom($prod->category_ids, $file_name, $file_path, $prod->remote_category);
        }

        $this->logActivity('Updating Feed List');
        $cpf_custom_local_category = $this->productList->cpf_custom_local_cats;
        $cpf_custom_remote_category = $this->productList->cpf_custom_remote_cats;
        if ($this->auto_update_feedlist) {
            ETCPF_FeedActivityLog::updateCustomFeedList($cpf_custom_local_category, $cpf_custom_remote_category, $file_name, $file_path, $this->providerName, $this->productCount);
        }

        if ($this->auto_free) {
            //Free the Attribute defaults
            for ($i = 0; $i < count($this->attributeDefaults); $i++) {
                unset($this->attributeDefaults[$i]);
            }

            //Free the Attribute Mapping Objects
            for ($i = 0; $i < count($this->attributeMappings); $i++) {
                unset($this->attributeMappings[$i]);
            }

            //De-allocate the overrides object to prevent chain dependency that made the core unload too early
            unset($this->feedOverrides);
        }

        if ($this->productCount <= 0) {
            $this->message .= '<br>No products returned';
            return;
        }

        $this->success = true;
    }*/

    /* New Function initiatez For Etsy Custom Feed */

    public function createCustomFeed(array $args)
    {
        new ETCPF_EtsyValidation();
        $saved_feed = null;
        if ($args) {
            /*Start Feed Creation Process*/
            $file_name = $args['filename'];
            $remote_category = null;
            $this->logActivity('Initializing...');
            global $etcore;
            $this->loadAttributeUserMap();

            if ($this->updateObject == null) {
                $this->initializeFeed($args['categories'], $remote_category);
            } else {
                $this->resumeFeed($args['categories'], $remote_category, $this->updateObject);
            }
            $this->initializeOverrides($saved_feed);

            $this->logActivity('Loading paths...');
            $file_url = ETCPF_FeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
            $file_path = ETCPF_FeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
            /**
             * Special (WordPress): where admin is https and site is http, path to wp-uploads works out incorrectly as https
             * we check the content_url() for https... if not present, patch the file_path
             */
            if (($etcore->cmsName == 'WordPress') && (strpos($file_path, 'https://') !== false) && (strpos(content_url(), 'https') === false)) {
                $file_path = str_replace('https://', 'http://', $file_path);
            }
            $this->file_path = $file_path;
            $args['fileurl'] = $file_path;
            //Shipping and Taxation systems
            $this->shipping = new ETCPF_ShippingData($this);
            $this->taxData = new ETCPF_TaxationData($this);

            $this->logActivity('Initializing categories...');

            //Get the ProductList ready
            if ($this->productList == null) {
                $this->productList = new ETCPF_ProductList();
            }
            /* Etcpf feed Log Initiation */
            $feedLogObject = new ETCPF_FeedActivityLog();
            $insertFeedFilerecord = null;
            $insertFeedFilerecord = $feedLogObject->updateEtsyCustomFeed(false, $args);
            if ($insertFeedFilerecord == false) {
                return json_encode(array('status' => 'failed', 'msg' => "Database Insertion Failed"));
                exit;
            }
            $insertFeedProducts = $feedLogObject->InsertFeedProductsForCustomFeed($insertFeedFilerecord, $args);
            //$transaction = $this->productList->fetchAndwriteProducts($this,$args);
            $transaction = $this->createFeedCustom($file_url, $file_path, $args);
            if ($transaction == true) {
                $args['product_count'] = $this->productCount;
                $insertFeedFilerecord = $feedLogObject->updateEtsyCustomFeed($insertFeedFilerecord, $args);
                return array('status' => 'success', 'file_url' => $file_path, 'id' => $insertFeedFilerecord);
            } else {
                $deleteCreatedFeedId = $feedLogObject->deleteFeedByID($insertFeedFilerecord);
            }

        } else {
            return json_encode(array('status' => 'failed', 'msg' => "Necessary arguments not provided"));
        }
        return false;
    }

    public function updateCustomFeed($feedid, $args, $saved_feed)
    {
        new ETCPF_EtsyValidation();
        if ($args) {
            /*Start Feed Creation Process*/
            $file_name = $args['filename'];
            $remote_category = null;
            $this->logActivity('Initializing...');
            global $etcore;
            $this->loadAttributeUserMap();
            $this->initializeOverrides($saved_feed);

            if ($this->updateObject == null) {
                $this->initializeFeed($args['categories'], $remote_category);
            } else {
                $this->resumeFeed($args['categories'], $remote_category, $this->updateObject);
            }

            $this->logActivity('Loading paths...');
            $file_url = ETCPF_FeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
            $file_path = ETCPF_FeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
            /**
             * Special (WordPress): where admin is https and site is http, path to wp-uploads works out incorrectly as https
             * we check the content_url() for https... if not present, patch the file_path
             */
            if (($etcore->cmsName == 'WordPress') && (strpos($file_path, 'https://') !== false) && (strpos(content_url(), 'https') === false)) {
                $file_path = str_replace('https://', 'http://', $file_path);
            }
            $this->file_path = $file_path;
            $args['fileurl'] = $file_path;
            //Shipping and Taxation systems
            $this->shipping = new ETCPF_ShippingData($this);
            $this->taxData = new ETCPF_TaxationData($this);

            $this->logActivity('Initializing categories...');

            //Get the ProductList ready
            if ($this->productList == null) {
                $this->productList = new ETCPF_ProductList();
            }
            /* Etcpf feed Log Initiation */
            $feedLogObject = new ETCPF_FeedActivityLog();
            $insertFeedFilerecord = null;
            $insertFeedProducts = $feedLogObject->UpdateFeedProductsForCustomFeed($feedid, $args);
            //$transaction = $this->productList->fetchAndwriteProducts($this,$args);
            $transaction = $this->createFeedCustom($file_url, $file_path, $args);
            if ($transaction == true) {
                $args['product_count'] = $this->productCount;
                $insertFeedFilerecord = $feedLogObject->updateEtsyCustomFeed($feedid, $args);
                if ($insertFeedFilerecord == false) {
                    return json_encode(array('status' => 'failed', 'msg' => "Database Update Failed", 'id' => $insertFeedFilerecord));
                    exit;
                }
                return array('status' => 'success', 'file_url' => $file_path, 'id' => $insertFeedFilerecord);
            } else {
                $deleteCreatedFeedId = $feedLogObject->deleteFeedByID($insertFeedFilerecord);
            }
        } else {
            return json_encode(array('status' => 'failed', 'msg' => "Necessary arguments not provided"));
        }
        return false;
    }

    public function checkIfFeedFileAlreadyExists($filename)
    {
        global $etcore;
        $etcore->callSuffix = 'W';
        $feedLogObject = new ETCPF_FeedActivityLog();
        $check = $feedLogObject->getDetailsIfAlreadyExists($filename, 'Etsy');
        return $check;
    }

    /* Etsy Custom Feed Ends */

    public function getProductListForCustomFeed()
    {

        global $wpdb;

        global $wpdb;
        $table_name = $wpdb->prefix . 'etcpf_custom_products';
        $sql = "
            SELECT remote_category , category as category_ids
             FROM {$table_name}
              ";
        $products = $wpdb->get_results($sql);
        return $products;

    }

    function getFeedFooter($file_name, $file_path)
    {
        return '';
    }

    function getFeedHeader($file_name, $file_path)
    {
        return '';
    }

    function getMapping($name)
    {
        foreach ($this->attributeMappings as $thisAttributeMapping) {
            if ($thisAttributeMapping->attributeName == $name) {
                return $thisAttributeMapping;
            }
        }

        return null;
    }

    function getMappingByMapto($name)
    {
        foreach ($this->attributeMappings as $thisAttributeMapping) {
            if ($thisAttributeMapping->mapTo == $name) {
                return $thisAttributeMapping;
            }
        }
        return null;
    }

    function getRuleByName($name)
    {
        foreach ($this->rules as $rule) {
            if ($rule->name == $name) {
                return $rule;
            }
        }

        return null;
    }

    public function handleProduct($this_product)
    {
        if (empty($this_product->attributes['current_category'])) {
            $this_product->attributes['current_category'] = $this->current_category;
        }
        //********************************************************************
        //Run the rules
        //********************************************************************

        foreach ($this->rules as $rule) {
            if ($rule->enabled) {
                $rule->clearValue();
            }
        }

        foreach ($this->rules as $index => $rule) {
            if ($rule->enabled) {
                $rule->process($this_product);
            }
        }

        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->isRuled) {
                $rule = $this->getRuleByName($thisDefault->value);
                if ($rule != null) {
                    $this_product->attributes[$thisDefault->attributeName] = $rule->value;
                }
            }
        }

        //***********************************************************
        //Send to descendant feed-provider for formatting
        //***********************************************************
        $product_text = $this->formatProduct($this_product);

        if ($this->feed_category->verifyProduct($this_product) && $this_product->attributes['valid']) {
            $this->handleProductSave($this_product, $product_text);
            foreach ($this->aggregateProviders as $thisProvider) {
                $thisProvider->aggregateProductSave($this->savedFeedID, $this_product, $product_text);
            }
            $this->productCount++;
        } else {
            return false;
        }

    }

    public function handleProductCustom($this_product)
    {
        $this_product->attributes['current_category'] = $this->current_category;
        //********************************************************************
        //Run the rules
        //********************************************************************

        foreach ($this->rules as $rule) {
            if ($rule->enabled) {
                $rule->clearValue();
            }
        }

        foreach ($this->rules as $index => $rule) {
            if ($rule->enabled) {
                $rule->process($this_product);
            }
        }

        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->isRuled) {
                $rule = $this->getRuleByName($thisDefault->value);
                if ($rule != null) {
                    $this_product->attributes[$thisDefault->attributeName] = $rule->value;
                }

            }
        }

        //***********************************************************
        //Send to descendant feed-provider for formatting
        //***********************************************************
        $product_text = $this->formatProduct($this_product);
        if ($this->feed_category->verifyProduct($this_product) && $this_product->attributes['valid']) {
            $this->handleProductSave($this_product, $product_text);
            $this->productCount++;
        }
    }

    function handleProductSave($product, $product_text)
    {
        fwrite($this->fileHandle, $product_text);
    }

    function initializeFeed($category, $remote_category)
    {
        //Allow descendant to perform initialization based on category/remote category
    }

    function initializeOverrides($saved_feed)
    {
        $this->logActivity('Initializing overrides...');
        //Mark all existing mappings as "SystemDefined" meaning auto-delete
        foreach ($this->attributeMappings as $mapping) {
            $mapping->systemDefined = true;
        }
        //Load Attribute mappings
        $this->feedOverrides = new ETCPF_FeedOverride($this->providerName, $this, $saved_feed);
    }

    function initializeCustomOverrides($saved_feed)
    {
        $this->logActivity('Initializing overrides...');
        //Mark all existing mappings as "SystemDefined" meaning auto-delete
        foreach ($this->attributeMappings as $mapping) {
            $mapping->systemDefined = true;
        }
        //Load Attribute mappings
        $this->feedOverrides = new ETCPF_FeedOverride($this->providerName, $this, $saved_feed);
    }

    function initalizeRead()
    {
    }

    function insertField($new_field, $index_field)
    {
        //CSV feed providers will sometimes want to insert-field-after-this-other-field, which PHP doesn't provide
        //insertField not currently used because the feedheader is created before productlist so there's no way to
        //know if some later category will need to re-arrange the fields
        //Edit: Debug Bing Feed provider uses insertField() for now
        if (in_array($new_field, $this->fields)) {
            return;
        }

        $new_array = array();
        foreach ($this->fields as $key => $item) {
            $new_array[] = $item;
            if ($item == $index_field) {
                $new_array[] = $new_field;
            }

        }
        $this->fields = $new_array;
    }

    function leaveFeed($updateObject)
    {
        //The system is abandoning this feed.
        //updateObject will be saved in JSON format and provided again in resumeFeed() at some point in the future
    }

    function loadAttributeUserMap()
    {
        //Called during feed initialization to map the Attributes
        global $etcore;
        $map_string = $etcore->settingGet('etcpf_attribute_user_map_' . $this->providerName);
        if ($map_string == '[]') {
            //if map_string is not object //temp fix for backwards compatibility... true fix below
            $etcore->settingSet('etcpf_attribute_user_map_' . $this->providerName, '');
            $map_string = '';
        }
        if (strlen($map_string) == 0) {
            $map = new stdClass();
        } //Was array(); *true fix -K
        else {
            $map = json_decode($map_string);
            $map = get_object_vars($map);
        }

        $this->usermappedAttributes = $map;
        foreach ($map as $mapto => $attr) {
            $thisAttribute = $this->getMappingByMapto($mapto);
            if ($thisAttribute != null && strlen($attr) > 0) {
                $thisAttribute->attributeName = $attr;
            }

        }
    }

    function logActivity($activity)
    {
        if ($this->activityLogger != null) {
            $this->activityLogger->logPhase($activity);
        }

    }

    function must_exit()
    {
        //true means exit when feed complete so the browser page will remain in place (WordPress)
        return true;
    }

    function resumeFeed($category, $remote_category, $updateObject)
    {
        //Allow descendant to perform initialization based on category/remote category
        //upon resuming a Feed. Note that previously saved data is available in updateObject
        $this->auto_update_feedlist = false;
    }

    function __construct($saved_feed = null)
    {

        global $etcore;
        $this->feed_category = new ETCPF_md5y();
        $this->weight_unit = $etcore->weight_unit;
        $this->dimension_unit = $etcore->dimension_unit;
        $this->currency = $etcore->currency;
        $this->addRule('description', 'description');

    }

    public function prepareErrorReports($parentID, $productID, $sku, $title, $attribute_name, $type, $errorcode, $status, $category = array())
    {
        $reports = new stdClass();
        $reports->parentID = $parentID;
        $reports->productid = $productID;
        $reports->sku = $sku;
        $reports->product_name = $title;
        $reports->attribute = $attribute_name;
        $reports->isWarning = $type;
        $reports->status = $status;
        $reports->errorcode = $errorcode;
        if (is_array($category)) {
            $reports->prod_cat = implode(',', $category);
        } else {
            $reports->prod_cat = $category;
        }
        $this->preparedReports[] = $reports;
    }

    public function getErrorReportList($filename, $feed_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . "etcpf_feedproducts";
        $errorcheck = false;
        $wpdb->update($table, array('status'=>'inactive'),array('feed_id'=>$feed_id));
        if ($this->insertintodb == true) {
            if (is_array($this->preparedReports) && count($this->preparedReports) > 0) {
                foreach ($this->preparedReports as $key => $list) {
                    $data = array(
                        'p_id' => $list->productid,
                        'error_status' => $list->status,
                        'sku' => $list->sku,
                        'p_name' => $list->product_name,
                        'message' => $list->attribute,
                        'error_code' => $list->errorcode,
                        'parent_id' => $list->parentID,
                        'prod_categories' => $list->prod_cat,
                        'feed_id' => $feed_id,
                        'status' => 'active'
                    );
                    $check = $wpdb->get_row("SELECT * FROM {$table} WHERE p_id = {$list->productid} AND feed_id = {$feed_id}", OBJECT);
                    if (is_object($check) && !empty($check->id)) {
                        /*$feed_ids = explode(',', $check->feed_id);
                        if (!in_array($feed_id, $feed_ids)) {
                            $data['feed_id'] = $check->feed_id . ',' . $feed_id;
                        } else {
                            $data['feed_id'] = $check->feed_id;
                        }*/
                        if ($check->error_status == '0' || $check->error_status == '-1') {
                            $errorcheck = true;
                            $wpdb->update($table, $data, array('id' => $check->id));
                        }
                        $wpdb->update($table, $data, array('id' => $check->id));
                    } else {
                        /*if ($list->errorcode != 5000) {
                            $errorcheck = true;*/
                        $wpdb->insert($table, $data);
                        /*}
                        if ($errorcheck == true) {
                            $wpdb->insert($table, $data);
                        }*/

                    }
                    /*=================================================================================================
                     * $e[] = [
                            'type' => $list->isWarning,
                            'product_id' => $list->productid,
                            'sku' => $list->sku,
                            'name' => $list->product_name,
                            'attribute' => $list->attribute,
                            'errorcode' => $list->errorcode,
                            'parentID' => $list->parentID
                    ==================================================================================================*/
                }
            }
        }
        /*==========================================================================================================
         * $header = ['Type', 'Product_ID', 'SKU', 'Title', 'Missing Attributes', 'Error Code'];
            $fp = fopen($file_name, 'w');
            fputcsv($fp, $header);
            foreach ($e as $fields) {
                fputcsv($fp, $fields);
            }
        =============================================================================================================*/
        return array('error' => $errorcheck, 'filelink' => null);
    }
} //ETCPF_BasicFeed

//********************************************************************
// ETCPF_XMLFeed has functions an XML Feed would need
//********************************************************************

class ETCPF_XMLFeed extends ETCPF_BasicFeed
{

    public $productLevelElement = 'item';
    public $topLevelElement = 'items';

    function formatProduct($product)
    {
        if (($product->attributes['stock_quantity'] == 0) && ($product->attributes['valid'] == true) && $product->attributes['manage_stock'] == 'yes') {
            $this->addErrorMessage(2000, 'Stock counted as 0 for ' . $product->attributes['title'] . '. you can use advanced commands too for adding quantity.');
        }

        //********************************************************************
        //Mapping 3.0 Pre-processing
        //********************************************************************

        //commented for while
        /* foreach ($this->attributeDefaults as $thisDefault)
                 if ($thisDefault->stage == 2)
*/

        $output = '
	<' . $this->productLevelElement . '>';

        //********************************************************************
        //Add attributes (Mapping 3.0)
        //********************************************************************

        foreach ($this->attributeMappings as $thisAttributeMapping) {
            if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName])) {
                $output .= $this->formatLine($thisAttributeMapping->mapTo, $product->attributes[$thisAttributeMapping->attributeName], $thisAttributeMapping->usesCData);
            }
        }

        //********************************************************************
        //Mapping 3.0 post processing
        //********************************************************************

        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->stage == 3) {
                $thisDefault->postProcess($product, $output);
            }
        }

        $output .= '
	</' . $this->productLevelElement . '>';

        return $output;

    }

    function getFeedFooter($file_name, $file_path)
    {
        $output = '
</' . $this->topLevelElement . '>';
        return $output;
    }

    function getFeedHeader($file_name, $file_path)
    {

        $output = '<?xml version="1.0" encoding="UTF-8" ?>
<' . $this->topLevelElement . '>';
        return $output;
    }

}

//********************************************************************
// ETCPF_XML2Feed has functions an XML Feed would need
// The product level element contains the id (ex: <auction id=“1294”>)
//********************************************************************

class ETCPF_XML2Feed extends ETCPF_BasicFeed
{

    public $productLevelElement = 'item';
    public $topLevelElement = 'items';

    function formatProduct($product)
    {
        echo 'XML2';
        //********************************************************************
        //Mapping 3.0 Pre-processing
        //********************************************************************
        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->stage == 2) {
                $product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);
            }
        }

        $output = '
	<' . $this->productLevelElement . ' id=' . '"' . $product->id . '"' . '>';

        //********************************************************************
        //Add attributes (Mapping 3.0)
        //********************************************************************

        foreach ($this->attributeMappings as $thisAttributeMapping) {
            if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName])) {
                $output .= $this->formatLine($thisAttributeMapping->mapTo, $product->attributes[$thisAttributeMapping->attributeName], $thisAttributeMapping->usesCData);
            }
        }

        //********************************************************************
        //Mapping 3.0 post processing
        //********************************************************************

        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->stage == 3) {
                $thisDefault->postProcess($product, $output);
            }
        }

        $output .= '
	</' . $this->productLevelElement . '>';

        return $output;

    }

    function getFeedFooter($file_name, $file_path)
    {
        $output = '
</' . $this->topLevelElement . '>';
        return $output;
    }

    function getFeedHeader($file_name, $file_path)
    {

        $output = '<?xml version="1.0" encoding="UTF-8" ?>
<' . $this->topLevelElement . '>';
        return $output;
    }

}

//********************************************************************
// ETCPF_CSVFeed has functions a CSV Feed would need
//********************************************************************

class ETCPF_CSVFeed extends ETCPF_BasicFeed
{

    function __construct()
    {

        parent::__construct();
        //apply strictAttribute rule to removes html, special chars
        $this->addRule('description', 'description', array('strict'));
        $this->addRule('strict_attribute', 'strictAttribute', array('description_short'));
        //Descriptions and title: escape any quotes
        $this->addRule('csv_standard', 'CSVStandard', array('title'));
        $this->addRule('csv_standard', 'CSVStandard', array('description'));
        $this->addRule('csv_standard', 'CSVStandard', array('description_short'));
    }

    protected function asCSVString($current_feed)
    {

        //Build output in order of fields
        $output = '';
        foreach ($this->fields as $field) {
            if (isset($current_feed[$field])) {
                $output .= $current_feed[$field] . $this->fieldDelimiter;
            } else {
                $output .= $this->fieldDelimiter;
            }

        }

        //Trim trailing comma
        return substr($output, 0, -1) . "\r\n";

    }

    public function executeOverrides($product, &$current_feed)
    {

        /*Mapping v2.0 Deprecated
                    //Run overrides
                    //Note: One day, when the feed can report errors, we need to report duplicate overrides when used_so_far makes a catch
                    $used_so_far = array();
                    foreach($product->attributes as $key => $a)
                        if (isset($this->feedOverrides->overrides[$key]) && !in_array($this->feedOverrides->overrides[$key], $used_so_far)) {
                            $current_feed[$this->feedOverrides->overrides[$key]] = $a;
                            $used_so_far[] = $this->feedOverrides->overrides[$key];
                        }
        */

    }

    function formatProduct($product)
    {
        //Trigger Mapping 3.0 Before-Feed Event
        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->stage == 2) {
                $product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);
            }
        }

        //Build output in order of fields
        $output = '';
        foreach ($this->fields as $field) {
            $thisAttributeMapping = $this->getMappingByMapto($field);
            if (($thisAttributeMapping != null) && $thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName])) {
                if ($thisAttributeMapping->usesCData) {
                    $quotes = '"';
                } else {
                    $quotes = '';
                }

                $output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;
            }
            $output .= $this->fieldDelimiter;
        }

        //Trigger Mapping 3.0 After-Feed Event
        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->stage == 3) {
                $thisDefault->postProcess($product, $output);
            }
        }

        //Trim trailing comma
        return substr($output, 0, -1) . "\r\n";

    }

    function getFeedHeader($file_name, $file_path)
    {

        $output = '';
        foreach ($this->fields as $field) {
            if (isset($this->feedOverrides->overrides[$field])) {
                $field = $this->feedOverrides->overrides[$field];
            }

            $output .= $field . $this->fieldDelimiter;
        }
        //Trim trailing comma
        return;

    }

}

//********************************************************************
// ETCPF_CSVFeedEx has functions a CSV Feed would need
// but phasing out deprecated functions
//********************************************************************

class ETCPF_CSVFeedEx extends ETCPF_BasicFeed
{

    function __construct()
    {
        parent::__construct();
        //apply strictAttribute rule to removes html, special chars
        $this->addRule('description', 'description', array('strict'));
        $this->addRule('strict_attribute', 'strictAttribute', array('description_short'));
        //Descriptions and title: escape any quotes
        $this->addRule('csv_standard', 'CSVStandard', array('title'));
        $this->addRule('csv_standard', 'CSVStandard', array('description'));
        $this->addRule('csv_standard', 'CSVStandard', array('description_short'));

        $this->reversible = true;
    }

    function formatProduct($product)
    {

        //********************************************************************
        //Trigger Mapping 3.0 Before-Feed Event
        //********************************************************************
        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->stage == 2) {
                $product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);
            }
        }

        //********************************************************************
        //Build output
        //********************************************************************
        $output = '';
        foreach ($this->attributeMappings as $thisAttributeMapping) {
            if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName])) {
                if ($thisAttributeMapping->usesCData) {
                    $quotes = '"';
                } else {
                    $quotes = '';
                }

                $output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;
            }
            if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted) {
                $output .= $this->fieldDelimiter;
            }

        }

        //********************************************************************
        //Trigger Mapping 3.0 After-Feed Event
        //********************************************************************
        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->stage == 3) {
                $thisDefault->postProcess($product, $output);
            }
        }

        //********************************************************************
        //Trim trailing delimiter
        //********************************************************************
        return substr($output, 0, -1) . "\r\n";

    }

    /*function getFeedHeader($file_name, $file_path) {

                $output = '';

                foreach($this->attributeMappings as $thisMapping)
                    if ($thisMapping->enabled && !$thisMapping->deleted)
                        $output .= $thisMapping->mapTo . $this->fieldDelimiter;

                return substr($output, 0, -1) .  "\r\n";

    */

    function getFeedHeader($file_name, $file_path)
    {
        return ''; //Skip header - We'll do it later
    }

    function getFeedFooter($file_name, $file_path)
    {

        //Now we finally write the headers! Start by creating them
        $headers = array();
        foreach ($this->attributeMappings as $thisMapping) {
            if ($thisMapping->enabled && !$thisMapping->deleted) {
                $headers[] = $thisMapping->mapTo;
            }
        }

        $headerString = implode($this->fieldDelimiter, $headers);

        $savedData = file_get_contents($this->filename);
        file_put_contents($this->filename, $headerString . "\r\n" . $savedData);

        //Write the footer as a header in the aggregate
        foreach ($this->aggregateProviders as $thisProvider) {
            $thisProvider->aggregateHeaderWrite($this->savedFeedID, $headerString);
        }

        return '';

    }

    function initializeOverrides($saved_feed)
    {
        parent::initializeOverrides($saved_feed);

        /*Deprecated
                    //Converting Attribute mappings v2.0 to v3.0
                    foreach($this->feedOverrides->overrides as $key => $mapTo) {
                        $n = $this->getMappingByMapto($mapTo);
                        if ($n == null)
                            $this->addAttributeMapping($key, $mapTo, true);
                        else
                            $n->attributeName = $key;
        */

    }

    function read($data)
    {
    }

}

//********************************************************************
// ETCPF_AggregateFeed
//********************************************************************

class ETCPF_AggregateFeed extends ETCPF_BasicFeed
{

    function __construct()
    {
        parent::__construct();
    }

    function aggregateHeaderWrite($id, $headerString)
    {
        //Do nothing. This is used only by AggCSV
    }

    function initializeAggregateFeed($id, $file_name)
    {

        $this->filename = ETCPF_FeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
        $this->file_url = ETCPF_FeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
        $this->productCount = 0;
        $this->file_name_short = $file_name;

        if (!isset($this->feeds) || count($this->feeds) == 0) {
            global $etcore;
            $data = $etcore->settingGet('cpf_aggrfeedlist_' . $id);
            $data = explode(',', $data);
            $this->feeds = array();
            foreach ($data as $datum) {
                $this->feeds[$datum] = true;
            }

        }

    }

}

if (!function_exists('sort_rule_func')) {
    function sort_rule_func($a, $b)
    {
        if ($a->order == $b->order) {
            return 0;
        } else {
            return ($a->order < $b->order) ? -1 : 1;
        }

    }
}
