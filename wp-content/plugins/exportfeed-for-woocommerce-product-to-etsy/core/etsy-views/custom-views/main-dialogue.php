<?php
// $file= dirname(__FILE__).'/../../classes/dialogbasefeed.php';
// if(file_exists($includes = realpath($file))){
//     include_once $includes;
//     $forattributes = new ETCPF_PBaseFeedDialog();
// }else{
//     die('file doesnot exist!');
// }
?>
<div style="display: none;" id="ajax-loader-cat-import"><span id="gif-message-span"></span></div>
<style type="text/css">
    .progress {
        display: block;
        text-align: center;
        width: 0;
        height: 3px;
        background: red;
        transition: width .3s;
    }

    .progress.hide {
        opacity: 0;
        transition: opacity 1.3s;
    }

    h2.hndle {
        font-size: 18px;
    }

</style>
<div class="wrap">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-3">
                <div class="postbox">
                    <div style="border: 1px solid #00a0d2; margin-bottom: 10px;">
                        <div class="select-merchant" style="margin-left: 8px; width: 33.33%;
                            clear: both;
                            display: inline-block;">
                            <h4 style="margin-left: 9px;"
                            >Select Feed Type</h4>
                            <select id="selectFeedType" onchange="doGoogleFeed(this.value);">
                                <option value="Etsy">Etsy Feed</option>
                                <option value="Productlistraw">Product List Plain Text Export</option>
                            </select>
                        </div>
                        <?php
                        define('IMAGE_PATH', plugins_url('/', __FILE__) . '../../../images/');
                        if (isset($data['registrationData']->valid) && $data['registrationData']->valid == true) {
                        ?>
                        <div class="logo-am"
                             style="vertical-align: middle; width: 50%;display: inline-block;padding: 0;margin: 0;">
                            <h4 class="icon-margin">Get standalone plugin for</h4>
                            <div class="upsell-icon-logo">
                                <div class="logoup amazon" style="display:inline-block;">
                                    <div class="amazon">

                                        <a value=""
                                           href="https://www.exportfeed.com/woocommerce-product-feed/woocommerce-product-feeds-on-amazon-seller-central/"
                                           target="_blank">

                                            <img src="<?php echo IMAGE_PATH; ?>/amazon.png">
                                        </a>
                                        <span class="plugin-link"><a
                                                    href="https://www.exportfeed.com/woocommerce-product-feed/woocommerce-product-feeds-on-amazon-seller-central/"
                                                    target="_blank">Get Amazon plugin</a></span>
                                        <span class="plugin-desc">Manage bulk products + order & inventory sync</span>
                                    </div>
                                </div>
                                <div class="logoup ebay" style="display:inline-block;">
                                    <div class="ebay">
                                        <a value=""
                                           href="https://www.exportfeed.com/woocommerce-product-feed/send-woocommerce-data-feeds-to-ebay-seller/"
                                           target="_blank">

                                            <img src="<?php echo IMAGE_PATH; ?>/ebay.png">
                                        </a>
                                        <span class="plugin-link"><a
                                                    href="https://www.exportfeed.com/woocommerce-product-feed/send-woocommerce-data-feeds-to-ebay-seller/"
                                                    target="_blank">Get eBay plugin</a></span>
                                        <span class="plugin-desc">Bulk upload products and variations to eBay</span>
                                    </div>
                                </div>

                            </div>

                            <div class="clear"></div>

                        </div>
                    </div>

                    <?php } else {
                    $style = 'style="display: inline-block; vertical-align: middle;"';
                    ?>

                    <div class="inside-export-target upsell-section"
                         style="width: 50%; vertical-align: top; display: inline-block;border:none;">
                        <div <?php echo $style; ?> >
                            <label for="edtLicenseKey">License:</label>
                            <input style="width:300px" type="text" name="license_key" id="edtLicenseKey" value=""
                                   placeholder="Enter full license key"/>
                            <input class="button-primary" type="submit" value="Save Key"
                                   title="Enter license key.License key field will disappear if it is valid one after you reload the page."
                                   id="submit" name="submit" onclick="etetcpf_submitLicenseKey('etetcpf_licensekey');">
                        </div>
                    </div>
                </div>

                <?php } ?>

                <div class="clear"></div>
                <div class="postbox" id="jstincse">
                    <div class="nav-wrapper">
                        <nav class="nav-tab-wrapper">
                            <span id="cpf-feeds_by_cats"
                                  class="nav-tab <?php if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'etsy-export-feed-admin') echo 'active'; ?>"><a
                                        href="<?php echo admin_url('admin.php?page=etsy-export-feed-admin'); ?>">Feed By Category</a></span>
                            <span id="etcpf-custom-feed"
                                  class="nav-tab <?php if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'etsy-export-feed-custom-feed') echo 'active'; ?>"><a
                                        href="<?php echo admin_url('admin.php?page=etsy-export-feed-custom-feed&feedType=1'); ?>">Custom Product Feed</a></span>
                        </nav>
                    </div>
                    <div class="inside export-target">
                        <!-- Product showing section Starts here -->
                        <div id="custom-feed-div-kmpxr" style="">
                            <div class="etcpf-custom_feed_generation" id="etcpf-custom_feed_generation"
                                 style="display: block;">
                                <form name="etcpf-custom_feed" id="etcpf-custom-feed-form" method="POST">
                                    <div class="search-product-header">
                                        <span style="padding-left:15px; font-size: 1.3em; font-weight: 600; color: #23282d;">Search product(s)</span>
                                        <span style="float: right;" onclick="location.reload();"
                                              class="reset_etcpf_etcpfcustomfeed"><img
                                                    src="<?php echo IMAGE_PATH; ?>/reset.png">Reset Search</span>
                                    </div>
                                    <div class="form-search">
                                        <div class="searchrow">
                                            <div class="col-skukey forminp" style="position:relative;">
                                                <input type="search" id="etcpf_sku_filter" name="etcpf_sku_filter"
                                                       placeholder="Search feed by SKU, Title"
                                                       style="width:100%; height: 30px;">
                                                <div class="etcpf-suggestion-box" style="display: none;"></div>
                                            </div>
                                            <div class="col-catagory" id="etcpf_localcategory_list"><select
                                                        name="etcpf_localcategories_filter"
                                                        id="etcpf_locacategories_filter" style="width: 100%">
                                                    <option value="" selected="selected">Select a category</option>
                                                    <?php foreach ($data['product_cat_data'] as $key => $main_cat) { ?>
                                                        <option value="<?php echo $main_cat->term_id; ?>"><?php echo $main_cat->name . ' (' . $main_cat->count . ')'; ?></option>
                                                        <?php if (isset($main_cat->children)) foreach ($main_cat->children as $child) { ?>
                                                            <option value="<?php echo $child->term_id; ?>">
                                                                &nbsp;&nbsp; <?php echo $child->name . ' (' . $child->count . ')'; ?></option>
                                                            <?php if (isset($child->children)) foreach ($child->children as $secondchild) { ?>
                                                                <option value="<?php echo $secondchild->term_id; ?>">
                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $secondchild->name . ' (' . $secondchild->count . ')'; ?></option>
                                                            <?php }
                                                        }
                                                    } ?>
                                                </select></div>
                                            <?php /*<div class="col-price">
                                                <select name="etcpf_price_filter_option" id="etcpf_price_filter_option"
                                                        style="width:100%; height: 30px;">
                                                    <option value="">Select Price Range</option>
                                                    <option value="less_than">Less than or equals to</option>
                                                    <option value="more_than">Greater than or equals to</option>
                                                    <option value="in_between">In Between</option>
                                                </select>
                                                <div id="etcpf_price_selection_list_option">
                                                    <input type="search" name="etcpf_price_filter_less_than"
                                                           placeholder="Enter Amount" id="etcpf_price_filter_less_than"
                                                           style="display: none; width:100%; height: 30px;">
                                                    <input type="search" name="etcpf_price_filter_more_than"
                                                           placeholder="Enter Amount" id="etcpf_price_filter_more_than"
                                                           style="display: none;">
                                                    <input type="search" name="etcpf_price_filter_in_between_first"
                                                           placeholder="Enter First Amount"
                                                           id="etcpf_price_filter_in_between_first"
                                                           style="display: none;">
                                                    <input type="search" name="etcpf_price_filter_in_between_second"
                                                           placeholder="Enter Second Amount"
                                                           id="etcpf_price_filter_in_between_second"
                                                           style="display: none;">
                                                </div>
                                            </div>*/ ?>

                                            <div class="col-catagory" id="etcpf_stock_list"><select
                                                        name="etcpf_stock_filter"
                                                        id="etcpf_stock_filter" style="width: 100%">
                                                    <option value="0">Filter by stock status</option>
                                                    <option value="1">In stock</option>
                                                    <option value="0">Out of stock</option>
                                                </select>
                                            </div>

                                            <div style="float: right;" class="col-search">
                                                <input class="button-primary"
                                                       title="This will search product list from above information you give and generate the result on search result section below."
                                                       type="button" value="Search Product" id="submit_data"
                                                       onclick="return searchProduct();"
                                                       name="submit_data">
                                            </div>
                                        </div>
                                    </div>

                                    <table class="form-table" style="table-layout:fixed;">
                                        <thead>
                                        <tr style="background-color: #8A9FAF;" valign="top">
                                            <th id="cb" class="manage-column column-cb check-column" style="width:40px;"><input type="checkbox"
                                                                           id="etcpf_select_all_checkbox"
                                                                           onclick="selectAllProducts(this);"></th>
                                            <th style="width:40px;"></th>
                                            <th style="width:25%; text-align">Product Title</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th>Sale Price</th>
                                            <th>Regular Price</th>
                                            <th>Quantity</th>
                                        </tr>
                                        </thead>

                                        <tbody id="etcpf-the-list" class="etcpf-table-sortable">
                                        <?php $sn = 0;
                                        if (is_array($data['products_data']['products']) && count($data['products_data']['products'])) foreach ($data['products_data']['products'] as $products_datum) {
                                            $metavalues = $this->getMetavalueByProductID($products_datum->ID);
                                            $childrens = $this->getProductChildren($products_datum->ID);
                                            $catString = '';

                                            try {
                                                foreach (get_the_terms($products_datum->ID, 'product_cat') as $key => $cat) {
                                                    if (strlen($catString) > 3) {
                                                        $catString .= ',' . $cat->slug;
                                                    } else {
                                                        $catString .= $cat->slug;
                                                    }
                                                }
                                            } catch (ErrorException $e) {
                                                $catString = 'N/A';
                                            }

                                            $sn++;
                                            $childclass = 'childof-' . $products_datum->ID;
                                            $parentclass = 'parent-' . $products_datum->ID;
                                            if ($data['previousData'] != null) {
                                                if (array_key_exists($products_datum->ID, $data['previousData'])) {
                                                    $selected = 'checked';
                                                    $checkedClass = 'checked-fcked-class-bg';
                                                } else {
                                                    $selected = '';
                                                    $checkedClass = '';
                                                }
                                            } else {
                                                $selected = '';
                                                $checkedClass = '';
                                            }

                                            if ($sn % 2 == 0) {
                                                $class = 'even-striped';
                                            } else {
                                                $class = 'odd-striped';
                                            } ?>
                                            <tr class="column-cb check-column <?php echo $class . ' ' . $parentclass . ' ' . $checkedClass; ?> parent-tr "
                                                data-cat_slugs="<?php echo $catString; ?>">
                                                <td scope="row" class="check-column" style="text-align:center;"><input
                                                            data-child="<?php echo $childclass; ?>"
                                                            class="parent-product-checkbox" type="checkbox"
                                                        <?php echo $selected; ?>
                                                            value="<?php echo $products_datum->ID; ?>"></td>
                                                <td>&nbsp</td>
                                                <td class="index"><?php
                                                    $title = strlen($products_datum->post_title) > 40 ? substr($products_datum->post_title, 0, 100) . '...' : $products_datum->post_title;
                                                    echo $title; ?></td>
                                                <td class="index">
                                                    <?php
                                                    //  echo $metavalues['_sku'][0];
                                                     $sku = isset($metavalues['_sku']) ? (isset($metavalues['_sku'][0]) ? $metavalues['_sku'][0] : '--') : '--';
                                                     echo $sku;
                                                     ?>
                                                </td>
                                                <td class="index"><?php echo $catString ?></td>
                                                <td style="text-align:center;"><?php
                                                    $sprice = isset($metavalues['_sale_price']) ? (isset($metavalues['_sale_price'][0]) ? $metavalues['_sale_price'][0] : '--') : '--';
                                                    echo $sprice; ?></td>
                                                <td style="text-align:center;"><?php
                                                    $rprice = isset($metavalues['_regular_price']) ? ($metavalues['_regular_price'][0] ? $metavalues['_regular_price'][0] : '--') : '';
                                                    echo $rprice; ?></td>
                                                <td style="text-align:center;">
                                                    <?php
                                                    $quantity = $metavalues['_stock'][0] ? $metavalues['_stock'][0] : '--';
                                                    echo $quantity; ?></td>
                                            </tr>
                                            <?php if (is_array($childrens) && count($childrens) > 0) foreach ($childrens as $key => $value) {
                                                if ($data['previousData'] != null) {
                                                    if (array_key_exists($products_datum->ID, $data['previousData']) && in_array($value->ID, $data['previousData'][$products_datum->ID]['child']['ids'])) {
                                                        $childselected = 'checked';
                                                        $chilcheckedClass = 'checked-fcked-class-bg';
                                                    } else {
                                                        $childselected = '';
                                                        $chilcheckedClass = '';
                                                    }
                                                } else {
                                                    $childselected = '';
                                                    $chilcheckedClass = '';
                                                }
                                                $childmetavalues = $this->getMetavalueByProductID($value->ID);
                                                ?>
                                                <tr class="<?php echo $class . ' ' . $childclass . ' ' . $chilcheckedClass; ?>">
                                                    <td><img src="<?php echo IMAGE_PATH; ?>/enter.png"
                                                             class="arrow_productlist"/></td>
                                                    <td scope="row" class="check-column" style="text-align:center; width:40px;"><input
                                                                data-parent="<?php echo $parentclass; ?>"
                                                                class="child-product-checkbox" type="checkbox"
                                                            <?php echo $childselected; ?>
                                                                value="<?php echo $value->ID ?>">
                                                    </td>
                                                    <td class="index"><?php echo $value->post_title ?></td>
                                                    <td class="index"><?php
                                                        $sku = isset($childmetavalues['_sku']) ? $childmetavalues['_sku'][0] : '--';
                                                        echo $sku; ?></td>
                                                    <td class="index"><?php echo explode(',', $value->category)[0]; ?></td>
                                                    <td style="text-align:center;">
                                                        <?php $csprice = isset($childmetavalues['_sale_price'][0]) ? $childmetavalues['_sale_price'][0] : '--';
                                                        echo $csprice; ?>
                                                    </td>
                                                    <td style="text-align:center;"><?php
                                                        $rprice = isset($childmetavalues['_regular_price']) ? ($childmetavalues['_regular_price'][0] ? $childmetavalues['_regular_price'][0] : '--') : '';
                                                        echo $rprice; ?></td>
                                                    <td style="text-align:center;"><?php
                                                        $quantity = isset($childmetavalues['_stock'][0]) ? $childmetavalues['_stock'][0] : '--';
                                                        echo $quantity; ?></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } ?>
                                        </tbody>

                                        <tfoot>
                                        <tr style="background-color: #ededed;">
                                            <th colspan="4" id="bulk-action" style="">

                                        <span style="display:none;"> <input type="button" id="doaction2"
                                                                            class="button-primary action apply_btn"
                                                                            value="Apply"
                                                                            onclick="etcpf_apply_action(this)">
                                        </span>
                                                <p style="color:grey;float:left;">Note: Only the product that you want
                                                    on your
                                                    feed should be selected.</p>
                                            </th>

                                            <th id="show-more" colspan="4" style="text-align:right">
                                                <div class="etsynav-pages">
                                                <span class="pagination-links">
                                                    <!--<span class="etsynav-pages-navspan" aria-hidden="true">«</span>-->
                                                <a id="prev-cbx-angle-custom-feed"
                                                   class="next-page etcpf_load_more_pagination" disabled
                                                   href="javascript:void(0);" class="etsynav-pages-navspan" id=""
                                                   onclick="searchProduct('prev',true)"><span
                                                            class="etsynav-pages-navspan"
                                                            aria-hidden="true">‹</span></a>
                                                <span class="paging-input"><label for="current-page-selector"
                                                                                  class="screen-reader-text">Current Page</label><input
                                                            class="current-page" id="current-page-selector" type="text"
                                                            name="paged" value="1" size="1"
                                                            aria-describedby="table-paging"><span
                                                            class="tablenav-paging-text"> of <span
                                                                class="total-pages"><?php echo $data['products_data']['pages']; ?></span></span></span>
                                                <a class="next-page etcpf_load_more_pagination"
                                                   href="javascript:void(0);" class="etsynav-pages-navspan"
                                                   id="next-cbx-angle-custom-feed"
                                                   onclick="searchProduct('next',true)"><span
                                                            class="screen-reader-text">Next page</span><span
                                                            aria-hidden="true"
                                                            class="etsynav-pages-navspan">›</span></a>
                                                    <!--<span class="etsynav-pages-navspan" aria-hidden="true">»</span>-->
                                                </span>
                                                    <input type="hidden" id="etcpf_page_hidden_page_item" value="0">
                                                </div>
                                            </th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </form>

                                <!-- Product showing section Ends -->

                                <div class="feed-here">
                                    <!-- Multiple category asiignment div for etsy -->
                                    <div class="etcpf-custom-category-mapping">
                                        <div class="etcpf-custom-category-mapping-loader"></div>
                                        <table id="etcpf-custom-category-mapping-lists" width="65%" cellspacing="0"
                                               cellpadding="2">
                                        </table>
                                    </div>
                                    <!-- Here will be advance command section -->
                                    <div class="adv-section">
                                        <label class="attr-desc label"><span class="label">If you need to modify your product feed,
                                <a onclick="show_advanced_attr(this)">click here to go to product feed customization options<span
                                            class="dashicons dashicons-arrow-down"></span></a>
                                </span>
                                        </label>
                                        <div class="inside" style="display:none;">
                                            <p>Product attributes of your Woocommerce are mapped automatically as per
                                                Amazonsc's requirements. You can manually make changes <span
                                                        class="cpf-danger" style="color:red;">if you are absolutely sure of what you are doing.</span><br>
                                            </p>
                                            <!-- Html for attributes mapping -->

                                            <?php echo $this->attributeMappings(); ?>

                                        </div>
                                    </div>

                                    <div id="adv-sec-block" style="display: none;">
                                        <div class="inside advance-sec-block" style="display: none;">
                                            <div id="cpf_advance_command_default">
                                    <span id="cpf_advance_command_settings">
                                        <a href="#cpf_advance_command_desc">
                                            <input class="button button-primary"
                                                   title="This will open Feed Customization information."
                                                   type="button"
                                                   id="cpf_feed_config_link_default"
                                                   value=" Show Feed Customization Option"
                                                   onclick="toggleGAdvanceCommandSectionDefault(this);">
                                        </a>
                                    </span>
                                                <div id="cpf_advance_section_default" style="display: none;">
                                                    <div class="advanced-section-description"
                                                         id="advanced_section_description_default"
                                                         style="padding-left: 17px;">
                                                        <p>Feed Customization Option grant you more control over your
                                                            feeds.
                                                            They
                                                            provide a way to create
                                                            your own attribute, map from non-standard ones or modify and
                                                            delete
                                                            feed
                                                            data.</p>
                                                        <ul style="list-style: inherit;">
                                                            <li>
                                                                <a href="http://www.exportfeed.com/documentation/creating-attributes/#3_Creating_Defaults_using_Advanced_Commands">Creating
                                                                    Default Attributes with Feed Customization
                                                                    Option</a></li>
                                                            <li>
                                                                <a href="http://www.exportfeed.com/documentation/mapping-attributes/#3_Mapping_from_8216setAttributeDefault8217_Advanced_Commands">Mapping/Remapping
                                                                    with Feed Customization Option</a></li>
                                                            <li>Comprehensive Feed Customization Option can be found
                                                                here: <a title="mapping attributes - advanced commands"
                                                                         href="http://docs.shoppingcartproductfeed.com/AttributeMappingv3.1.pdf"
                                                                         target="_blank">More Advanced Commands</a> –
                                                                *PDF
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div>
                                                        <label class="un_collapse_label"
                                                               title="Click to open advance command field to customize your feed"><input
                                                                    class="button button-primary" type="button"
                                                                    id="toggleAdvancedSettingsButtonDefault"
                                                                    onclick="toggleGAdvancedDialogDeafult();"
                                                                    value="Open Advance Command Option"></label>
                                                        <label class="un_collapse_label"><input
                                                                    title="This will erase your attribute mappings from the feed."
                                                                    id="erase_mappings_default"
                                                                    onclick="googleDoEraseMappings('Etsy')"
                                                                    class="button button-primary" type="button"
                                                                    value="Reset Attribute Mappings"></label>
                                                    </div>
                                                </div>
                                                <div class="feed-advanced" id="feed-advanced-default"
                                                     style="display: none;">
                                                        <textarea class="feed-advanced-text" id="feed-advanced-text-default" placeholder="#rule StrReplace(P, S, description)"><?php if (get_option('Etsy_custom-etsy-merchant-settings')){ echo maybe_unserialize(get_option('Etsy_custom-etsy-merchant-settings'));} ?></textarea>
                                                    <input class="button button-primary" type="button"
                                                           id="bUpdateSettingDefault" name="bUpdateSettingDefault"
                                                           title="Update Setting will update your feed data according to the Feed Customization enterd in Customization section."
                                                           value="Update Settings"
                                                           onclick="googleDoUpdateSetting('feed-advanced-text-default', 'cp_advancedFeedSetting-Etsy_custom'); return false;">
                                                    <div id="updateSettingMsg">&nbsp;</div>
                                                </div>
                                            </div>
                                            <p style="color:red"><strong>Note:</strong>Use this section only if you are
                                                pro.
                                                <a href="http://www.exportfeed.com/documentation/advanced-commands/"
                                                   target="_blank">Learn more</a>.</p>
                                        </div>
                                    </div>
                                    <!-- Html for attributes mapping Ends -->

                                    <?php // echo $forattributes->attributeMappings(); ?>

                                </div>
                            </div>

                            <div class="feed-bottom-row cs-options">
                                <span class="label field-name">File name for feed : </span>
                                <span class="field-name">Please provide a unique yet identifiable filename for your feed.</span>
                                <div class="input-boxes">
                                    <input type="text" name="feed_filename" id="feed_filename_custom"
                                           class="text_big"
                                           value="<?php echo $data['filename']; ?>"/>
                                    <label><span
                                                style="color:red">*</span><span> Use alpha-numeric value for filename.</span></br>
                                        <span style="color:red">*</span><span> If you use an existing file name, the file will be overwritten.</span></label>
                                </div>
                            </div>
                            <div class="feed-right-row button-section">
                                <input class="button button-primary button-hero" type="button"
                                       data-provider="Etsy"
                                       onclick="etcpf_GetcustomFeed(this)" value="Create Feed"
                                       style="line-height:0;height: 35px;padding: 0 20px; margin: 10px auto"/>
                                <div id="feed-error-display"
                                     style="display: inline-block;margin: 10px;height: 35px;">
                                    &nbsp;
                                </div>
                            </div>

                            <div id="feed-message-display" style="padding-top: 6px;">&nbsp;</div>

                        </div>
                    </div>

                    <div class="clear"></div>
                    <div class="clear"></div>
                    <div class="clear"></div>

                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
    $formedData = array('products' => $data["previousData"]);
    $mappedCategory = array('categories' => $data['selectedCategory']);
}
?>

<?php if ($data['previousData'] !== null) { ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            let SelectedProducts = JSON.parse(sessionStorage.getItem('Customfeedselectedproducts'));
            console.log(SelectedProducts);
            let previousData = '<?php echo json_encode($formedData) ?>';
            let previousCategories = '<?php echo json_encode($mappedCategory); ?>';
            sessionStorage.setItem('Customfeedselectedproducts', previousData);
            sessionStorage.setItem('Customfeedcategorymap', previousCategories);
            let MappedCategories = JSON.parse(sessionStorage.getItem('Customfeedcategorymap'));
            console.log(MappedCategories);
            manageHtmlForSelectedCategories(MappedCategories);
            /*var PArsedData = JSON.parse(previousData);
            console.log(PArsedData);*/
        })
    </script>
<?php } ?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#prev-cbx-angle-custom-feed').css({'pointer-events': 'none'});
        /*jQuery(document).on('click',function(){
            jQuery('#prev-cbx-angle-custom-feed').removeAttr('style');
        });*/
    });
</script>
