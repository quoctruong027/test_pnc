<?php global $etcore;?>
<div class="attributes-mapping">
    <div id="poststuff">
        <div class="postbox" style="width: 98%;">

            <!-- ***************
                    Page Header
                    ****************** -->

            <!-- <h3 class="hndle" style="float: none;"><?php //echo $this->service_name_long; ?></h3> -->

            <div class="export-target">

                <!-- ***************
                        LEFT SIDE
                    ****************** -->

                <div class="">
                    <!-- ROW 1: Local Categories -->
                    <?php echo $this->localCategoryList; ?>
                    <!-- ROW 2: Remote Categories -->
                    <?php echo $this->line2(); ?>
                    <div class="feed-right-row cs-options">
                        <?php echo $this->categoryList($initial_remote_category); ?>
                    </div>

                    <div id="adv-sec-block" style="display: none;">
                        <div class="adv-section">
                            <label class="label">Attribute Mapping</label>
                            <div class="inside">
                                <?php echo $this->attributeMappings(); ?>
                            </div>
                        </div>
                        <div class="inside">
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
                                    <div class="advanced-section-description" id="advanced_section_description_default"
                                         style="padding-left: 17px;">
                                        <p>Feed Customization Option grant you more control over your feeds. They
                                            provide a way to create
                                            your own attribute, map from non-standard ones or modify and delete feed
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
                                                    id="erase_mappings_default" onclick="googleDoEraseMappings('Etsy')"
                                                    class="button button-primary" type="button"
                                                    value="Reset Attribute Mappings"/></label>
                                    </div>
                                </div>
                                <div class="feed-advanced" id="feed-advanced-default">
                        <textarea class="feed-advanced-text"
                                  id="feed-advanced-text-default"><?php echo $this->advancedSettings; ?></textarea>
                                    <?php echo $this->cbUnique; ?>
                                    <input class="button button-primary" type="button" id="bUpdateSettingDefault"
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

                    <!-- ROW 3: Filename -->
                    <div class="feed-right-row cs-options prl-fname" style="display:flex;">
                        <span class="label">File name for feed : </span>
                        <span><input type="text" name="feed_filename" id="feed_filename_default" class="text_big"
                                     value="<?php echo $this->initial_filename; ?>"/>
                                     <br/>
                                     <label class="label"><span style="color:red">*</span><span> Use alpha-numeric value for filename.</span><br>
                            <span style="color:red">*</span><span> If you use an existing file name, the file will be overwritten.</span></label><br/>
                        <input type="hidden" name=""></label>
                                 </span>
                    </div>
                    <!-- ROW 4: Get Feed Button -->
                    <div class="feed-right-row button-section">
                        <input class="button button-primary button-hero" type="button"
                               onclick="googledoGetFeed('<?php echo $this->service_name ?>' , this)" value="Create Feed"
                               style="line-height:0;height: 35px;padding: 0 20px; margin: 10px auto">
                        <br/><br/>
                        <div id="feed-message-display">&nbsp;</div>
                        <div id="cpf_feed_view"></div>
                        <div id="feed-error-display">&nbsp;</div>
                        <div id="feed-status-display">&nbsp;</div>
                    </div>
                </div>

                <!-- ***************
                        Termination DIV
                        ****************** -->

                <div style="clear: both;">&nbsp;</div>

                <!-- ***************
                FOOTER
                ****************** -->
            </div>
        </div>
    </div>
</div>
<script>
    function toggleAdvanceCommandSection(event) {
        var feed_config = jQuery("#cpf_custom_feed_config").css('display');
        var feed_config_button = jQuery("#cpf_feed_config_link");

        //First slideUp feed config section if displayed
        if (feed_config == "block") {
            jQuery("#cpf_custom_feed_config").slideUp();
            jQuery("#cpf_feed_config_desc").slideUp();
            jQuery(feed_config_button).attr('title', 'This will open feed config section below.You can provide suffix and prefix for the attribute to be included in feed.');
            jQuery(feed_config_button).val('Show Feed Config');
        }

        var display = jQuery("#cpf_advance_section").css('display');
        if (display == 'none') {
            jQuery("#cpf_advance_section").slideDown();
            jQuery(event).val('Hide Advance Section');
            jQuery(event).attr('title', 'Hide Feed config section');
            /* var divPosition = jQuery("#cpf_custom_feed_config").offset();
             jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
        }
        if (display == 'block') {
            jQuery("#cpf_advance_section").slideUp();
            jQuery("#feed-advanced").slideUp();
            // jQuery("#bUpdateSetting").slideUp();
            jQuery(event).attr('title', 'This will open feed advance command section where you can customize your feed using advanced command.');
            jQuery(event).val('Show advance Command section');
        }
    }

    function toggleAdvanceCommandSectionDefault(event) {
        var display = jQuery("#cpf_advance_section_default").css('display');
        if (display == 'none') {
            jQuery("#cpf_advance_section_default").slideDown();
            jQuery(event).val('Hide Advance Section');
            jQuery(event).attr('title', 'Hide Feed config section');
            /* var divPosition = jQuery("#cpf_custom_feed_config").offset();
             jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
        }
        if (display == 'block') {
            jQuery("#cpf_advance_section_default").slideUp();
            jQuery("#feed-advanced-default").slideUp();
            // jQuery("#bUpdateSetting").slideUp();
            jQuery(event).attr('title', 'This will open feed advance command section where you can customize your feed using advanced command.');
            jQuery(event).val('Show advance Command section');
        }
    }
</script>