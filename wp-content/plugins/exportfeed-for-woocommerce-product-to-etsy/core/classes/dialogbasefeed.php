<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once dirname(__FILE__) . '/../data/productcategories.php';
require_once dirname(__FILE__) . '/../data/attributesfound.php';
require_once dirname(__FILE__) . '/../data/feedfolders.php';
require_once dirname(__FILE__) . '/../classes/etsyclient.php';

class ETCPF_PBaseFeedDialog
{

    public $blockCategoryList = false;
    public $options; // Array to be filled by constructor of descendant.
    public $service_name = 'Etsy'; // Example only.
    public $service_name_long = 'Etsy XML Export'; // Example only.
    public $custom_feed_config;

    /**
     * ETCPF_PBaseFeedDialog constructor.
     */
    function __construct()
    {
        $this->options = array();
    }

    /**
     * @param $thisAttribute
     * @param $index
     *
     * @return string
     */
    function createDropdown($thisAttribute, $index)
    {
        $found_options = new ETCPF_FoundOptions($this->service_name, $thisAttribute);

        $output = '
    <select class="attribute_select" id="attribute_select' . $index . '" onchange="setAttributeOption(\'' . $this->service_name . '\', \'' . $thisAttribute . '\', ' . $index . ')">
      <option value=""></option>';
        foreach ($this->options as $option) {
            if ($option == $found_options->option_value) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            $output .= '<option value="' . $this->convert_option($option) . '"' . $selected . '>' . $option . '</option>';
        }
        $output .= '
    </select>';

        return $output;
    }

    function createDropdownAttr($found_attributes, $defaultValue = '', $mapTo)
    {
        $output = '
    <select class="attribute_select" service_name="' . $this->service_name . '"
        mapto="' . $mapTo . '"
        onchange="setGoogleAttributeOptionV2(this)" >
      <option value=""></option>
      <option value="(Reset)">(Reset)</option>';
        foreach ($found_attributes->attributes as $attr) {
            if ($defaultValue == $attr->attribute_name) {
                $selected = ' selected="true"';
            } else {
                $selected = '';
            }
            $output .= '<option value="' . $attr->attribute_name . '"' . $selected . '>' . $attr->attribute_name . '</option>';
        }
        $output .= '
        <option value="">--Common attributes--</option>
        <option value="brand">brand</option>
        <option value="description_short">description_short</option>
        <option value="id">id</option>
        <option value="regular_price">regular_price</option>
        <option value="sale_price">sale_price</option>
        <option value="sku">sku</option>
        <option value="tag">tag</option>
        <option value="title">title</option>
        <option value="">--CPF Additional Fields--</option>
        <option value="brand">brand</option>
        <option value="ean">ean</option>
        <option value="mpn">mpn</option>
        <option value="upc">upc</option>
        <option value="description">description</option>
        <option value="">--Dummy attributes--</option>
        <option value="default1">default1</option>
        <option value="default2">default2</option>
        <option value="default3">default3</option>
    </select>';

        return $output;
    }

    function attributeMappings()
    {

        global $etcore;
        $found_attributes = new ETCPF_FoundAttribute();
        $savedAttributes = $found_attributes->attributes;
        $found_attributes->attributes = array();
        foreach ($savedAttributes as $attr) {
            $found_attributes->attributes[] = $attr;
        }

        foreach ($this->provider->attributeMappings as $thisAttributeMapping) {
            //if empty mapping, don't add to drop down list
            if (strlen(trim($thisAttributeMapping->attributeName)) > 0) {
                $attr = new stdClass();
                $attr->attribute_name = $thisAttributeMapping->attributeName;
                $found_attributes->attributes[] = $attr;
            }
        }

        $output = '
                <div class="mapping-container">
                <div class="attr-div required_">
                <label class="attributes-label" title="Required Attributes" id="toggleRequiredAttributes" onclick="toggleGRequiredAttributes(this);">Required Attributes</label>
                <div class="required-attributes" id=\'required-attributes\'>
                <table>
                <tr><td>Attribute</td><td width="20"></td><td>' . $this->service_name . ' Attribute</td></tr>';

        foreach ($this->provider->attributeMappings as $thisAttributeMapping)
            if ($thisAttributeMapping->isRequired)
                $output .= '<tr><td>' . $this->createDropdownAttr($found_attributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</td><td></td><td>' . $thisAttributeMapping->mapTo . '</td></tr>';
        $output .= '
              </table>
              </div>
              </div>

              <div class="attr-div optional_">

              <label class="attributes-label" title="Optional Attributes" id="toggleGOptionalAttributes" onclick="toggleGOptionalAttributes()">Additional Attributes</label>
              <div class="optional-attributes" id=\'optional-attributes\'>
              <table>
              <tr><td>Attribute</td><td width="20"></td><td>' . $this->service_name . ' Attribute</td></tr>';

        foreach ($this->provider->attributeMappings as $thisAttributeMapping)
            if (!$thisAttributeMapping->isRequired)
                $output .= '<tr><td>' . $this->createDropdownAttr($found_attributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</td><td></td><td>' . $thisAttributeMapping->mapTo . '</td></tr>';
        $output .= '
              </table>
              </div>
              </div>
              </div>';

        return $output;
    }

    function categoryList($initial_remote_category)
    {
        $etsy = new ETCPF_Etsy();
        return '<input type="text" name="etsy_category_display" class="text_big" id="etsy_category_display"  onclick="showEtsyCategories(\'' . $this->service_name . '\',\'default\')" value="' . $initial_remote_category . '" autocomplete="off" readonly="true" placeholder="Click here to select your categories"/>
            <input type="hidden" name="remote_category" id="remote_category" value="' . $initial_remote_category . '" />
            ';

        /*      return $etsy->fetch_category($initial_remote_category).'<input type="hidden" id="service_status" value="'.get_current_user_id().'"/><div id="categoryList" class="categoryList"></div><input type="hidden" id="remote_category" name="remote_category" value="'.$initial_remote_category.'" />';*/
    }

    public function getTemplateFile()
    {

        $filename = ETCPF_PATH . '/core/feeds/' . strtolower($this->service_name) . '/dialog.tpl.php';
        if (!file_exists($filename)) {
            $filename = ETCPF_PATH . '/core/classes/dialogbasefeed.tpl.php';
        }
        return $filename;
    }

    public function initializeProvider()
    {
        //Load the feed provider
        require_once dirname(__FILE__) . '/md5.php';
        require_once dirname(__FILE__) . '/../feeds/' . strtolower($this->service_name) . '/feed.php';
        $providerName = 'ETCPF_' . $this->service_name . 'Feed';
        $this->provider = new $providerName;
        $this->provider->loadAttributeUserMap();
    }

    function line2()
    {
        global $etcore;
        if ($etcore->cmsPluginName != 'RapidCart') {
            return '';
        }
        $listOfShops = $etcore->listOfRapidCartShops();
        $output = ' <select class="text_big" id = "edtRapidCartShop" onchange = "googleDoFetchLocalCategories()" > ';
        foreach ($listOfShops as $shop) {
            if ($shop->id == $etcore->shopID) {
                $selected = ' selected = "selected"';
            } else {
                $selected = '';
            }
            $output .= ' < option value = "' . $shop->id . '"' . $selected . ' > ' . $shop->name . '</option > ';
        }
        $output .= '</select > ';

        return '
                <div class="feed-right-row" >
                  <span class="label" > Shop : </span >
    ' . $output . '
                </div > ';
    }

    /**
     * @param null $source_feed
     * @param null $feed_type
     */
    public function mainDialog($source_feed = null, $feed_type = null)
    {
        global $etcore;

        $this->advancedSettings = $etcore->settingGet($this->service_name . '-etsy-merchant-settings');
        if ($source_feed == null) {
            $initial_local_category = '';
            $this->initial_local_category_id = '';
            $initial_remote_category = '';
            $this->initial_filename = '';
            $this->script = '';
            $this->cbUnique = '';
        } else {
            $initial_local_category = $source_feed->local_category;
            $this->initial_local_category_id = $source_feed->category_id;
            $initial_remote_category = $source_feed->remote_category;
            $this->initial_filename = $source_feed->filename;
            if ($source_feed->own_overrides == 1) {
                $strChecked = 'checked = "checked" ';
                $this->advancedSettings = $source_feed->feed_overrides;
            } else {
                $strChecked = '';
            }
            $this->cbUnique = ' <div><label ><input type = "checkbox" id = "cbUniqueOverride" ' . $strChecked . ' />Advanced commands unique to this feed </label ></div > ';
            /*if ($source_feed->own_overrides == 1) {
                $this->advancedSettings = $source_feed->feed_overrides;
                $this->script = '
                    <script type = "text/javascript" >
        jQuery(document) . ready(function () {
            jQuery("#cbUniqueOverride") . prop("checked", true);
        });
                    </script > ';
            }*/
        }

        $this->servName = strtolower($this->service_name);
        $this->initializeProvider();
        $attrVal = array();
        $this->folders = new ETCPF_FeedFolder();
        $this->product_categories = new ETCPF_ProductCategories(); //used?

        $this->localCategoryList = '
            <div class="feed-right-row cs-options">
                <span class="label">WooCommerce Category : </span>
                <div class="input-boxes">
                    <input type = "text" name = "local_category_display" class="text_big" id = "local_category_display"  onclick = "showGLocalCategories(\'' . $this->service_name . '\')" value = "' . $initial_local_category . '" autocomplete = "off" readonly = "true" placeholder = "Click here to select your categories" />
                    <input type = "hidden" name = "local_category" id = "local_category" value = "' . $this->initial_local_category_id . '" />
                </div>
            </div>';
        $this->source_feed = $source_feed;
        $this->feed_type = $feed_type;


        //Pass this to the template for processing
        include $this->getTemplateFile();
    }

    //Strip special characters out of an option so it can safely go in a <select /> in the dialog
    function convert_option($option)
    {
        //Some Feeds (like Etsy & eBay) need to modify this
        return $option;
    }
}
