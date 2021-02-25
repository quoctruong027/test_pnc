<?php
global $etcore;
$etsy = new ETCPF_Etsy();
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
<?php
require_once plugin_dir_path(__FILE__) . '/../../etsy-export-feed-wpincludes.php';
?>
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
                        define('IMAGE_PATH', plugins_url('/', __FILE__) . '../../images/');
                        $reg = new ETCPF_EtsyValidation();
                        if ($reg->valid == 1) {
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
                                   id="submit" name="submit" onclick="etcpf_submitLicenseKey('etcpf_licensekey');">
                        </div>
                    </div>
                </div>

                <?php } ?>

                <div class="clear"></div>
                <div class="postbox" id="jstincse">
                    <div class="nav-wrapper">
                        <nav class="nav-tab-wrapper">
                            <span id="cpf-feeds_by_cats" class="nav-tab active"> Feed By Category </span>
                            <span id="cpf-custom-feed" class="nav-tab"><a href="<?php echo admin_url('admin.php?page=etsy-export-feed-custom-feed&feedType=1'); ?>">Custom Product Feed</a></span>
                        </nav>
                    </div>
                    <div class="inside export-target">
                        <div class="feed-here">
                            <?php echo $this->localCategoryList; ?>
                            <div class="feed-right-row cs-options">
                                <span class="label">Etsy Category : </span>
                                <div class="input-boxes">
                                    <?php echo $this->categoryList($initial_remote_category); ?>
                                    <?php if (is_array($_REQUEST) && array_key_exists('id', $_REQUEST)) {
                                        $catpath = $this->source_feed->category_path;
                                        $tax = $this->source_feed->texonomy_path;
                                    } else {
                                        $catpath = '';
                                        $tax = '';
                                    } ?>
                                    <input type="hidden" id="etsy-category-path" name="category_path"
                                           value="<?php echo $catpath; ?>">
                                    <input type="hidden" id="etsy-taxonomy-path" name="taxonomy_path"
                                           value="<?php echo $tax; ?>">
                                </div>
                            </div>
                            <div class="adv-section">
                                <label class="attr-desc label"><span class="label">If you need to modify your product feed,
                                <a onclick="show_advanced_attr(this)">click here to go to product feed customization options<span
                                            class="dashicons dashicons-arrow-down"></span></a>
                                </span>
                                </label>
                                <div class="inside" style="display: none;">
                                    <p>Product attributes of your Woocommerce are mapped automatically as per Amazonsc's
                                        requirements. You can manually make changes <span class="cpf-danger"
                                                                                          style="color:red;">if you are absolutely sure of what you are doing.</span><br>
                                    </p>
                                    <?php echo $this->attributeMappings(); ?>
                                </div>
                            </div>
                            <div id="adv-sec-block" style="display: none;">
                                <div class="inside advance-sec-block" style="display: none;">
                                    <div id="cpf_advance_command_default">
                            <span id="cpf_advance_command_settings">
                                <a href="#cpf_advance_command_desc"><input class="button button-primary"
                                                                           title="This will open Feed Customization information."
                                                                           type="button"
                                                                           id="cpf_feed_config_link_default"
                                                                           value=" Show Feed Customization Option"
                                                                           onclick="toggleGAdvanceCommandSectionDefault(this);"></a>
                            </span>
                                        <div id="cpf_advance_section_default" style="display: none;">
                                            <div class="advanced-section-description"
                                                 id="advanced_section_description_default"
                                                 style="padding-left: 17px;">
                                                <p>Feed Customization Option grant you more control over your feeds.
                                                    They
                                                    provide a way to create
                                                    your own attribute, map from non-standard ones or modify and delete
                                                    feed
                                                    data.</p>
                                                <ul style="list-style: inherit;">
                                                    <li>
                                                        <a href="http://www.exportfeed.com/documentation/creating-attributes/#3_Creating_Defaults_using_Advanced_Commands">Creating
                                                            Default Attributes with Feed Customization Option</a></li>
                                                    <li>
                                                        <a href="http://www.exportfeed.com/documentation/mapping-attributes/#3_Mapping_from_8216setAttributeDefault8217_Advanced_Commands">Mapping/Remapping
                                                            with Feed Customization Option</a></li>
                                                    <li>Comprehensive Feed Customization Option can be found here: <a
                                                                title="mapping attributes - advanced commands"
                                                                href="http://docs.shoppingcartproductfeed.com/AttributeMappingv3.1.pdf"
                                                                target="_blank">More Advanced Commands</a> – *PDF
                                                    </li>
                                                </ul>
                                            </div>
                                            <div>
                                                <label class="un_collapse_label"
                                                       title="Click to open advance command field to customize your feed"><input
                                                            class="button button-primary" type="button"
                                                            id="toggleAdvancedSettingsButtonDefault"
                                                            onclick="toggleGAdvancedDialogDeafult();"
                                                            value="Open Advance Command Option"/></label>
                                                <label class="un_collapse_label"><input
                                                            title="This will erase your attribute mappings from the feed."
                                                            id="erase_mappings_default"
                                                            onclick="googleDoEraseMappings('Etsy')"
                                                            class="button button-primary" type="button"
                                                            value="Reset Attribute Mappings"/></label>
                                            </div>
                                        </div>
                                        <div class="feed-advanced" id="feed-advanced-default" style="display: none;">
                        <textarea class="feed-advanced-text"
                                  id="feed-advanced-text-default"><?php echo $this->advancedSettings; ?></textarea>
                                            <?php echo $this->cbUnique; ?>
                                            <input class="button button-primary" type="button"
                                                   id="bUpdateSettingDefault"
                                                   name="bUpdateSettingDefault"
                                                   title="Update Setting will update your feed data according to the Feed Customization enterd in Customization section."
                                                   value="Update Settings"
                                                   onclick="googleDoUpdateSetting('feed-advanced-text-default', 'cp_advancedFeedSetting-<?php echo $this->service_name; ?>'); return false;"/>
                                            <div id="updateSettingMsg">&nbsp;</div>
                                        </div>
                                    </div>
                                    <p style="color:red"><strong>Note:</strong>Use this section only if you are pro. <a
                                                href="http://www.exportfeed.com/documentation/advanced-commands/"
                                                target="_blank">Learn more</a>.</p>
                                </div>
                            </div>

                            <div class="feed-bottom-row cs-options">
                                <span class="label field-name">File name for feed : </span>
                                <span class="field-name">Please provide a unique yet identifiable filename for your feed.</span>
                                <div class="input-boxes">
                                    <input type="text" name="feed_filename" id="feed_filename_default" class="text_big"
                                           value="<?php echo $this->initial_filename; ?>"/>
                                    <label><span
                                                style="color:red">*</span><span> Use alpha-numeric value for filename.</span></br>
                                        <span style="color:red">*</span><span> If you use an existing file name, the file will be overwritten.</span></label>
                                </div>
                            </div>
                            <div class="feed-right-row button-section">
                                <input class="button button-primary button-hero" type="button"
                                       onclick="googledoGetFeed('Etsy' , this)" value="Create Feed"
                                       style="line-height:0;height: 35px;padding: 0 20px; margin: 10px auto"/>
                                <div id="feed-error-display" style="display: inline-block;margin: 10px;height: 35px;">
                                    &nbsp;
                                </div>
                            </div>

                            <!-- Never REmove this div as it is important for showing messages while creating feed -->
                            <div id="feed-message-display" style="padding-top: 6px;">&nbsp;</div>
                            <div id="cpf_feed_view"></div>
                            <div id="feed-error-display"></div>
                            <div id="feed-error-report"></div>
                            <div id="feed-status-display">&nbsp;</div>
                            <!-- NEVER -->

                        </div>
                    </div>

                    <div class="clear"></div>

                    <div class="clear"></div>
                    <!-- <div class="postbox" id="submitbtn">
                        <h2 class="hndle">Advanced Command</h2>

                    </div> -->
                    <div class="clear"></div>

                </div>
            </div>
        </div>
    </div>
</div>
