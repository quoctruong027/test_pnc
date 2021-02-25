var feedajaxhost = "";
var category_lookup_timer;
//the commands are WordPress defaults, declared as variables so Joomla can replace them
var feedIdentifier = 0; //A value we create and inform the server of that allows us to track errors during feed generation
// var feed_id = 0; //A value the server gives us if we're in a feed that exists already. Will be needed when we want to set overrides specific to this feed
var feedFetchTimer = null;
var localCategories = {children: []};
var chosen_merchant = '';
var feed_type = '';
var security_nonce = jQuery('#_wpnonce').val();
var this_item;
var etcpf_AjaxCall = null;
var TotalItemSelected;
var ErrorSolved = false;

//console.log(feed_type);
function googleParseFetchCategoryResult(res) {
    document.getElementById("categoryList").innerHTML = res;
    if (res.length > 0) {
        document.getElementById("categoryList").style.border = "1px solid #A5ACB2";
        document.getElementById("categoryList").style.display = "inline";
    } else {
        document.getElementById("categoryList").style.border = "0px";
        document.getElementById("categoryList").style.display = "none";
        document.getElementById("categoryList").style.display = "none";
        document.getElementById("remote_category").value = "";
    }
}

function googleParseFetchLocalCategories(res) {
    localCategories = jQuery.parseJSON(res);
}

function googleParseGetFeedResults(res, btn) {

    //Stop the intermediate status interval
    window.clearInterval(feedFetchTimer);
    feedFetchTimer = null;
    jQuery('#feed-status-display').html("");

    results = jQuery.parseJSON(res);
    //Show results
    jQuery('#feed-message-display').html('');
    if (results.errors.length > 0) {
        jQuery('#feed-error-display').html(results.errors);
    }
    if (results.errorsreport != null && results.errorsreport.length > 0) {
        console.log(results.errorsreport.length);
        location.replace(results.errorUrl);
        return;
        var view_report = '<a href = "' + results.errorUrl + '" target="_blank" class="button button-primary" style="height: 35px;line-height: 35px;padding: 0 8px; margin: 10px auto;">View Error Report</a>';
        jQuery('#feed-error-report').html(view_report);
    } else {
        jQuery('#feed-status-display').html("Feed Created Successfully without feed error. Click to view feed above to view feed.<br> OR go to <a href='" + window.location.protocol + "//" + window.location.hostname + "/wp-admin/admin.php?page=etsy-export-feed-manage" + "'>manage feed</a>");
        if (results.url.length > 0) {
            // var view_feed = '<input class="button button-primary button-hero" style="line-height:0;height: 35px;padding: 0 20px;" type="button" id="cpf_open_feed" onclick="openCreatedFeed(\'?page=etsy-export-feed-manage\')" value="Manage Feed">';
            var view_feed = '<a href = "' + results.url + '" target="_blank" class="button button-primary button-hero" style="height: 35px;line-height: 35px;padding: 0 25px; margin: 10px auto;">View Feed</a>';
            jQuery('#feed-error-display').html(view_feed);
            // jQuery(btn).parent().find('span.open_feed').html(view_feed);
        }
    }
    if (results.errors.length > 0)
        jQuery('#feed-error-display').html(results.errors);
}

function openCreatedFeed(url) {
    window.open(url);
}


function googleParseUploadFeedResults(res, provider) {

    //Stop the intermediate status interval
    window.clearInterval(feedFetchTimer);
    feedFetchTimer = null;
    jQuery('#feed-error-display2').html("");
    jQuery('#feed-status-display2').html("Uploading feed...");

    var results = jQuery.parseJSON(res);

    //Show results
    if (results.url.length > 0) {
        jQuery('#feed-error-display2').html("&nbsp;");
        //window.open(results.url);
        var data = {content: results.url, provider: provider};
        jQuery('.remember-field').each(function () {
            data[this.name] = this.value;
        });
        console.log(provider);
    }
    if (results.errors.length > 0) {
        jQuery('#feed-error-display2').html(results.errors);
        jQuery('#feed-status-display2').html("");
    }
}

function googleParseUploadFeedResultstatus(data, id) {

}

function googleParseGetFeedStatus(res) {
    if (feedFetchTimer != null)
        jQuery('#feed-status-display').html(res);
}

function googleParseUploadFeedStatus(res) {
    if (feedFetchTimer != null)
        jQuery('#feed-status-display2').html(res);
}


function googleParseSelectFeedChange(res, merchant) {
    console.log(merchant);
    if (merchant == 'Productlistraw') {
        jQuery('.hndle').html('Create Feed (Raw Product Feed)');
        jQuery('.feed-here').html(res);

    } else {
        jQuery('#feedPageBody').html(res);

    }
    googleDoFetchLocalCategories();
}

function googleParseUpdateSetting(res) {
    jQuery('#updateCustomSettingMessage').html(res);
}

function googleDoEraseMappings(service_name) {
    var r = confirm("This will clear your current Attribute Mappings including saved Maps from previous attributes. Proceed?");
    if (r == true) {
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                service_name: service_name,
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdMappingsErase,
                security: ETCPF.ETCPF_nonce
            },
            success: function (res) {
                console.log(res);
                ETCPF_erase_confirmation(res)
            }
        });
    }
}

function googleDoFetchCategory(service_name, partial_data) {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            partial_data: partial_data,
            shop_id: shopID,
            feedpath: ETCPF.cmdFetchCategory,
            action: 'exportfeed_etsy',
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            googleParseFetchCategoryResult(res)
        }
    });
}

function googleDoFetchCategory_1(service_name, selector) {
    var partial_data = selector.value;
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            partial_data: partial_data,
            shop_id: shopID,
            feedpath: ETCPF.cmdFetchCategory_custom,
            action: 'exportfeed_etsy',
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            googleParseFetchCategoryResult_1(res, selector)
        }
    });
}

function googleParseFetchCategoryResult_1(res, selector) {
    jQuery('.no_remote_category').css('display', 'none');
    var list = jQuery(selector).parent().siblings('.categoryList');
    //console.log(list);
    jQuery(list).html(res);
    if (res.length > 0) {
        jQuery(list).css('border', '1px solid #A5ACB2');
        jQuery(list).css('display', 'inline');
    } else {
        jQuery(list).css('border', '0px');
        jQuery(list).css('display', 'none');
        jQuery(list).value = "";
    }
}

function googleDoFetchCategory_timed(service_name, partial_data) {
    if (!category_lookup_timer) {
        window.clearTimeout(category_lookup_timer);
    }

    category_lookup_timer = setTimeout(function () {
        googleDoFetchCategory(service_name, partial_data)
    }, 100);
}

function googleDoFetchCategory_timed_custom(service_name, partial_data) {
    if (!category_lookup_timer) {
        window.clearTimeout(category_lookup_timer);
    }

    category_lookup_timer = setTimeout(function () {
        googleDoFetchCategory_1(service_name, partial_data)
    }, 100);
}

function googleDoFetchLocalCategories() {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            shop_id: shopID,
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdFetchLocalCategories,
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            googleParseFetchLocalCategories(res)
        }
    });
}

function doFetchLocalCategories_google() {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            shop_id: shopID,
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdFetchLocalCategories_custom,
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            googleParseFetchLocalCategories_1(res)
        }
    });
}

function googleParseFetchLocalCategories_1(res) {
    localCategories_custom = jQuery.parseJSON(res);
    //chosen_merchant = jQuery("#selectFeedType").val();
    var html = '';
    html += '<select name="cpf_localcategories_filter" id="cpf_locacategories_filter"  style="width: 100%"><option value="">Select Category</option>';
    html += googleGetLocalCategoryBranch_1(localCategories_custom.children, 0, chosen_merchant);
    html += '</select>';
    jQuery("#cpf_localcategory_list").html(html);
}

//jQuery(".chosen-select").chosen();
function googleGetLocalCategoryBranch_1(branch, gap, chosen_merchant) {
    // var result = '';
    var select_html = '';
    var span = '<span style="width: ' + gap + 'px; display: inline-block;">&nbsp;</span>';
    select_html += '';
    for (var i = 0; i < branch.length; i++) {
        select_html += '' + span + '<option value="' + branch[i].id + '">' + branch[i].title + '</option>';
        select_html += googleGetLocalCategoryBranch_1(branch[i].children, gap + 20, chosen_merchant);
    }
    return select_html;
    //return result;
}

function googleGetLocalCategoryList(chosen_categories) {
    return googleGetLocalCategoryBranch(localCategories.children, 0, chosen_categories);
}

function googleDoUploadFeed(provider, service, userid) {

    jQuery('#feed-error-display2').html("Uploading feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            provider: provider,
            local_category: jQuery('#local_category').val(),
            remote_category: jQuery('#remote_category').val(),
            file_name: jQuery('#feed_filename').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID,
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdGetFeed,
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            googleParseUploadFeedResults(res, provider)
        }
    });
    feedFetchTimer = window.setInterval(function () {
        updateUploadFeedStatus()
    }, 500);
}

/*function googledoGetFeed(provider,btn) {
    jQuery('#feed-error-display').html("Generating feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();

    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    var category_path = jQuery('#etsy-category-path').val();
    var full_taxonomy_path = jQuery('#etsy-taxonomy-path').val();

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            provider: provider,
            local_category: jQuery('#local_category').val(),
            remote_category: jQuery('#remote_category').val(),
            remote_category_id: jQuery('#remote_category_id').val(),
            category_path:category_path,
            full_taxonomy_path:full_taxonomy_path,
            file_name: jQuery('#feed_filename_default').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID,
            feed_type: 0,
            action : 'exportfeed_etsy',
            feedpath:ETCPF.cmdGetFeed,
            security:ETCPF.ETCPF_nonce
        },
        success: function (res) {
             googleParseGetFeedResults(res,btn)
             console.log(res);
            jQuery('#ajax-loader-cat-import').hide();
            // guide_to_upload();
        }
    });
    feedFetchTimer = window.setInterval(function () {
        updateGetGoogleFeedStatus()
    }, 500);
}*/

function clearmessagesdiv() {
    jQuery('#feed-error-display').html('');
    jQuery('#feed-error-report').html('');
}

function googledoGetFeed(provider, btn, regenerate = false) {
    jQuery('#feed-message-display').html("Generating feed...");
    clearmessagesdiv();
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();

    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    if (regenerate == true)
        feed_id = jQuery('#feedidinput').val();

    jQuery('#ajax-loader-cat-import').show();

    var category_path = jQuery('#etsy-category-path').val();
    var full_taxonomy_path = jQuery('#etsy-taxonomy-path').val();

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            provider: provider,
            local_category: jQuery('#local_category').val(),
            remote_category: jQuery('#remote_category').val(),
            remote_category_id: jQuery('#remote_category_id').val(),
            category_path: category_path,
            full_taxonomy_path: full_taxonomy_path,
            file_name: jQuery('#feed_filename_default').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            regenerate: regenerate,
            shop_id: shopID,
            feed_type: 0,
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdGetFeed,
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            googleParseGetFeedResults(res, btn);
            if (regenerate == true) {
                jQuery('#no-error-label').html("Feed has been generated Successsfully!!!");
                jQuery('#regenerate-feed-link-label').html("Goto <a href='" + window.location.protocol + "//" + window.location.hostname + "/wp-admin/admin.php?page=etsy-export-feed-manage" + "'>Manage Feed</a> for uploading feed to Etsy Shop.");
            }
            jQuery('#ajax-loader-cat-import').hide();
            // guide_to_upload();
        }
    });
    feedFetchTimer = window.setInterval(function () {
        updateGetGoogleFeedStatus()
    }, 500);
}


function googleDoGetCustomFeed(provider, btn) {
    jQuery('#feed-error-display').html("Generating feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();
    //var remote_category = jQuery("input[name='cpf_1']").val();
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            provider: provider,
            feedLimit: jQuery("#cpf_feed_output_limit").val(),
            file_name: jQuery('#feed_filename').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID,
            feed_type: 1,
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdGetCustomFeed,
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            googleParseGetFeedResults(res, btn)
        }
    });
    feedFetchTimer = window.setInterval(function () {
        updateGetGoogleFeedStatus()
    }, 500);
}

function googleDoSelectCategory_default(category, option, service_name) {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    jQuery(category).parent().parent().find('#categoryDisplayText').val(category.innerHTML);
    //document.getElementById("categoryDisplayText").value = category.innerHTML;
    document.getElementById("remote_category").value = option;
    document.getElementById("categoryList").style.display = "none";
    document.getElementById("categoryList").style.border = "0px";
}


function doSelectCategory_google(category, option, service_name) {
    console.log(option);
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    cat = category;
    jQuery(cat).parent().parent().find('.text_big').val(category.innerHTML);
    //jQuery(category).parent().siblings('.text_big').val(category.innerHTML);
    //jQuery(category).html(res);
    // document.getElementById("categoryDisplayText").value = category.innerHTML;
    document.getElementById("remote_category").value = option;
    jQuery(cat).parent().parent().find('.categoryList').css('display', 'none');
    jQuery(cat).parent().parent().find('.categoryList').css('display', 'none');

}

function googleDoSelectLocalCategory(id) {

    //Build a list of checked boxes
    var category_string = "";
    var category_ids = "";
    jQuery(".cbLocalCategory").each(
        function (index) {
            tc = document.getElementById(jQuery(this).attr('id'));
            if (tc.checked) {
                //if (jQuery(this).attr('checked') == 'checked') {
                category_string += jQuery(this).val() + ", ";
                category_ids += jQuery(this).attr('category') + ",";
            }
        }
    );

    //Trim the trailing commas
    category_ids = category_ids.substring(0, category_ids.length - 1);
    category_string = category_string.substring(0, category_string.length - 2);

    //Push the results to the form
    jQuery("#local_category").val(category_ids);
    jQuery("#local_category_display").val(category_string);

}

function doGoogleFeed(chosen_merchant) {
    jQuery('#ajax-loader-cat-import').show();
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            feedtype: chosen_merchant,
            feedpath: ETCPF.cmdSelectFeed,
            action: 'exportfeed_etsy',
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            googleParseSelectFeedChange(res, chosen_merchant);
            doFetchLocalCategories_google();
            jQuery('#ajax-loader-cat-import').hide();
        }
    });
}


function googleDoUpdateAllFeeds() {

    /**
     * in Joomla, this message is hidden, so unhide
     * jQuery('#update-message').css({"display": "block"});
     * */
    let etcpf_manage_table_body = jQuery("#etcpf_manage_table_originals");
    let countfeed = etcpf_manage_table_body.find('input:checked').length;

    if (countfeed <= 0) {
        alert("Please select at least one feed to update. Thanks");
        return false;
    }

    jQuery('#ajax-loader-cat-import').show();

    /** This is done in loader itself
     * jQuery('#update-message').html("Updating feeds...").css({"display": "block"});
     * */


    /**
     * @updateAllFeeds
     * This is new method to obtain the funcationality we seek
     * Updates the feeds in interval fashion
     * Once all selected feeds gets updated, returns true
     *
     */

    updateAllFeeds();
    return true;


    /**
     * @INFO: This functionality is handeled by above code snippet itself
     *        Incase of need we can use it later on

     if (countfeed > 0) {
            if (countfeed == 1) {
                var feed = 'feed';
            } else {
                feed = 'selected feeds';
            }
            $html = "Updating " + feed + ", please wait ...";
            jQuery('.update-message').html($html);
            feedcount = countfeed;
        } else {
            $html = "Updating all feeds. Please wait ...";
            jQuery('.update-message').html($html);
            feedcount = etcpf_manage_table_body.find('tr').length;
        }
     var feed_id = [];
     etcpf_manage_table_body.find('input:checkbox').each(function (i, data) {
            if (this.checked) {
                var checked_feed_id = jQuery(this).parent().parent().find('.cpf_hidden_feed_id').val();
                console.log(typeof checked_feed_id)
                if (typeof checked_feed_id !== 'undefined')
                    feed_id.push(checked_feed_id);
            }
        });
     console.log(feed_id);
     jQuery.ajax({
            type: "post",
            url: ajaxurl,
            // data: "",
            data: {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdUpdateAllFeeds,
                security: ETCPF.ETCPF_nonce,
                feed_id: feed_id
            },
            success: function (res) {
                jQuery('#update-message').html(res);
            }
        });

     **/

}

function etcpf_check_all_feeds(selector) {
    let checked = jQuery("#etcpf_select_all_feed").is(':checked');
    let tbody = jQuery("#etcpf_manage_table_originals");
    if (checked === true) {
        tbody.find("input[type=checkbox]").prop('checked', true);
    } else {
        tbody.find("input[type=checkbox]").prop('checked', false);
    }
}

function googleDoUpdateSetting(source, settingName) {
    //Note: Value must always come last...
    //and &amp after value will be absorbed into value
    jQuery('#updateSettingMsg').html('Updating...');
    if (jQuery("#" + source).parent().find('div>label>input[type=checkbox]').attr('checked') == 'checked')
        unique_setting = '&feedid=' + feed_id;
    else
        unique_setting = '';
    var shopID = jQuery("#edtRapidCartShop").val();
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: 'action=exportfeed_etsy&feedpath=' + ETCPF.cmdUpdateSetting + "&security=" + ETCPF.ETCPF_nonce + "&setting=" + settingName + unique_setting + "&shop_id=" + shopID + "&value=" + jQuery("#" + source).val(),
        success: function (res) {
            jQuery("#set_interval_time").html(jQuery("#selectDelay option:selected").html());
            googleParseUpdateMessage(res);
        }
    });
}

function googleParseUpdateMessage(res) {
    jQuery('#updateSettingMessage').html(res);
    jQuery('#updateSettingMsg').html(res);
}


function googledoCustomFeedSetting(source) {
    //Note: Value must always come last...
    //and &amp after value will be absorbed into value
    var feed_body = jQuery("#cpf_custom_feed_config_body");
    var cpf_merchant_attr = [];
    var cpf_feed_prefix = [];
    var cpf_feed_suffix = [];
    var cpf_feed_type = [];
    var cpf_feed_value_default = [];
    var cpf_feed_value_custom = [];

    jQuery(feed_body).find(".cpf_merchantAttributes").each(function (i, data) {
        cpf_merchant_attr.push(jQuery(data).val());
    });
    jQuery(feed_body).find(".cpf_prefix").each(function (i, data) {
        cpf_feed_prefix.push(jQuery(data).val());
    });

    jQuery(feed_body).find(".cpf_suffix").each(function (i, data) {
        cpf_feed_suffix.push(jQuery(data).val());
    });
    jQuery(feed_body).find(".cpf_change_type").each(function (i, data) {
        cpf_feed_type.push(jQuery(data).val());
    });
    var attr_html = jQuery("#cpf-sort_config").find(".attribute_select");
    var attr_custom = jQuery("#cpf-sort_config").find(".cpf_custom_value_attr");

    jQuery(attr_html).each(function (i, data) {
        //console.log(jQuery(data).val());
        cpf_feed_value_default.push(jQuery(data).val());
    });

    jQuery(attr_custom).each(function (i, data) {
        //console.log(jQuery(data).val());
        cpf_feed_value_custom.push(jQuery(data).val());
    });


    var shopID = jQuery("#edtRapidCartShop").val();
    s = source;
    var settingName = jQuery(source).parent().find("input[name='cpf_custom_merchant_type']").val();
    var feedLimit = jQuery("#cpf_feed_output_limit").val();
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdUpdateFeedConfig,
            security: ETCPF.ETCPF_nonce,
            setting: settingName,
            feedLimit: feedLimit,
            shop_id: shopID,
            cpf_merchant_attr: cpf_merchant_attr,
            cpf_feed_prefix: cpf_feed_prefix,
            cpf_feed_suffix: cpf_feed_suffix,
            cpf_feed_type: cpf_feed_type,
            cpf_feed_value_default: cpf_feed_value_default,
            cpf_feed_value_custom: cpf_feed_value_custom
        },
        success: function (res) {
            googleParseUpdateSetting(res)
        }
    });
}

function googleGetLocalCategoryBranch(branch, gap, chosen_categories) {
    var result = '';
    var span = '<span style="width: ' + gap + 'px; display: inline-block;">&nbsp;</span>';
    for (var i = 0; i < branch.length; i++) {
        if (jQuery.inArray(branch[i].id, chosen_categories) > -1)
            checkedState = ' checked="true"';
        else
            checkedState = '';
        result += '<div>' + span + '<input type="checkbox" class="cbLocalCategory" id="cbLocalCategory' + branch[i].id + '" value="' + branch[i].title +
            '" onclick="googleDoSelectLocalCategory(' + branch[i].id + ')" category="' + branch[i].id + '"' + checkedState + ' />' + branch[i].title + '(' + branch[i].tally + ')</div>';
        result += googleGetLocalCategoryBranch(branch[i].children, gap + 20, chosen_categories);
    }
    return result;
}

function googleGetLocalCategoryList(chosen_categories) {
    return googleGetLocalCategoryBranch(localCategories.children, 0, chosen_categories);
}

/*function googleGeteBayCategoryList() {
    var html;
    var cmdFetcheBayCategory = 'core/ajax/wp/fetch_etcpf_category.php';
    var loading = document.getElementById('loading-gif');
    jQuery.ajax({
        type: "post",
        url: feedajaxhost + cmdFetcheBayCategory,
        data: {service_name: 'etcpfSeller'},
        dataType: "html",
        success: function (res) {
            jQuery("#loading-gif").css('display', 'none');
            document.getElementById('eBayCategoryList').innerHTML = res;
        },
        error: function () {
            html += '<div class="error">No Category Found.</div>'
        }
    });
    return html;

}*/

/*function googleFetchChildCategory(parent_id, selector) {
    if (jQuery(selector).hasClass('active')) {
        jQuery("#child-" + parent_id).css('display', 'none');
        jQuery(selector).removeClass("dashicons dashicons-arrow-down-alt2");
        jQuery(selector).addClass("dashicons dashicons-arrow-right-alt2");
        jQuery(selector).removeClass('active');
        return;
    }

    jQuery(selector).addClass('active');
    var html;
    var cmdFetcheBayCategory = 'core/ajax/wp/fetch_etcpf_category.php';
    var result = '';
    jQuery.ajax({
        type: "post",
        url: feedajaxhost + cmdFetcheBayCategory,
        data: {service_name: 'etcpfSeller', parent_id: parent_id},
        dataType: "html",
        success: function (res) {
            if (jQuery(selector).hasClass('active')) {
                jQuery(selector).removeClass("dashicons-arrow-right-alt2");
                jQuery(selector).addClass("dashicons dashicons-arrow-down-alt2");
            }
            jQuery("#child-" + parent_id).css('display', 'block');
            document.getElementById('child-' + parent_id).innerHTML = res;
        },
        error: function () {
            html += '<div class="error">No Category Found.</div>'
        }
    });

    return html;
}*/

/*function doSelecteBayCategories(id) {
    var selectCategory = document.getElementById('hiddenCategoryName-' + id).value;
    selectCategory = selectCategory.split(':');
    selectCategory = selectCategory.join(">");
    document.getElementById('categoryDisplayText').value = selectCategory;
    document.getElementById('categoryDisplayText').innerHTML = selectCategory;
    document.getElementById('remote_category').value = selectCategory + ':' + id;
    document.getElementById('remote_category_id').value = id;
    parent.jQuery.etcpf_colorbox.close();

}*/

/*function searsPostByRestAPI() {
    jQuery.ajax({
        type: "post",
        url: feedajaxhost + cmdSearsPostByRestAPI,
        data: {username: jQuery("#edtUsername").val(), password: jQuery("#edtPassword").val()},
        success: function (res) {
            searsPostByRestAPIResults(res)
        }
    });
}*/

/*function searsPostByRestAPIResults(res) {

}*/

function setGoogleAttributeOption(service_name, attribute, select_index) {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: "service_name=" + service_name + "&attribute=" + attribute + '&mapto=' + jQuery('#attribute_select' + select_index).val() + "&action=exportfeed_etsy&feedpath=" + ETCPF.cmdSetAttributeOption,
        success: function (res) {
            console.log(res);
        }
    });
}

function setGoogleAttributeOptionV2(sender) {
    var service_name = jQuery(sender).attr('service_name');
    var attribute_name = jQuery(sender).val();
    var mapto = jQuery(sender).attr('mapto');
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            attribute: attribute_name,
            mapto: mapto, shop_id: shopID,
            feedpath: ETCPF.cmdSetAttributeUserMap,
            action: 'exportfeed_etsy',
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            console.log(res);
        }
    });
}

function etcpf_submitLicenseKey(keyname) {
    var r = alert("License field will disappear if key is successful. Please reload the page.");
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            security: ETCPF.ETCPF_nonce,
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdUpdateSetting,
            setting: keyname,
            value: jQuery("#edtLicenseKey").val()
        },
        success: function (res) {
            console.log(res);
        }
    });
    //window.location.reload(1);
}

function ETCPF_erase_confirmation(res) {
    alert("Attribute Mappings Cleared"); //Dropped message and just reloaded instead
    if (document.getElementById("selectFeedType") == null)
        jQuery(".attribute_select").val("");
    else
        doGoogleFeed();
}

function showGLocalCategories(provider) {
    provider = chosen_merchant;
    chosen_categories = jQuery("#local_category").val();
    chosen_categories = chosen_categories.split(",");
    jQuery.etcpf_colorbox({html: "<div class='categoryListLocalFrame'><div class='categoryListLocal'><h1>Categories</h1>" + googleGetLocalCategoryList(chosen_categories) + "</div></div>"});
}


function toggleGAdvancedDialog() {
    toggleButton = document.getElementById("toggleAdvancedSettingsButton");
    if (jQuery(toggleButton).val() == 'Open Advanced Commands') {
        toggleButton.value = "Close Advance Command";
        document.getElementById("feed-advanced").style.display = "inline";
        jQuery('#feed-advanced-text').css('border-color', "#D0D");
    } else {
        toggleButton.value = "Open Advanced Commands";
        document.getElementById("feed-advanced").style.display = "none";
    }
}

function toggleGAdvancedDialogDeafult() {
    toggleButton = document.getElementById("toggleAdvancedSettingsButtonDefault");
    if (jQuery(toggleButton).val() == 'Open Advance Command Option') {
        toggleButton.value = "Close Advance Command Option";
        document.getElementById("feed-advanced-default").style.display = "inline";
    } else {
        toggleButton.value = "Open Advance Command Option";
        document.getElementById("feed-advanced-default").style.display = "none";
    }
}

function toggleGOptionalAttributes() {
    toggleButton = document.getElementById("toggleGOptionalAttributes");

    if (toggleButton.innerHTML.indexOf("h") > 0) {
        //Open the dialog
        toggleButton.innerHTML = "[Hide] Additional Attributes";
        document.getElementById("optional-attributes").style.display = "inline-block";
    } else {
        //Close the dialog
        toggleButton.innerHTML = "[Show] Additional Attributes";
        document.getElementById("optional-attributes").style.display = "none";
    }
}//toggleGOptionalAttributes

function toggleGRequiredAttributes() {
    toggleButton = document.getElementById("toggleRequiredAttributes");

    if (toggleButton.innerHTML.indexOf("h") > 0) {
        //Open the dialog
        toggleButton.innerHTML = "[Hide] Required Attributes";
        document.getElementById("required-attributes").style.display = "inline-block";
    } else {
        //Close the dialog
        toggleButton.innerHTML = "[Show] required Attributes";
        document.getElementById("required-attributes").style.display = "none";
    }
    /*toggleButton = document.getElementById("required-attributes");

    if (toggleButton.style.display == "none") {
        //Open the dialog
        document.getElementById("required-attributes").style.display = "inline";
    } else {
        //Close the dialog
        document.getElementById("required-attributes").style.display = "none";
    }*/
}//toggleGRequiredAttributes
function updateGetGoogleFeedStatus() {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: "feed_identifier=" + feedIdentifier + "&feedpath=" + ETCPF.cmdGetFeedStatus + "&action=exportfeed_etsy&security=" + ETCPF.ETCPF_nonce,
        success: function (res) {
            googleParseGetFeedStatus(res)
        }
    });
}

function googleSaveTocustomTable(feed_id) {
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
            feed_id: feed_id,
            feedpath: ETCPF.cmdFetchProductAjax,
            action: 'exportfeed_etsy',
            cmd: 'saveEdit',
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            console.log('here');
        }
    });
}

function cpf_remove_feed(row) {
    // console.log(row);
    if (confirm("Are you sure you want to deleted this feed?")) {
        t = row;
        jQuery(t).parent().find('.spinner').css('visibility', 'visible');
        var product_id = jQuery(t).parent().parent().find(".cpf_feed_id_hidden").html();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                id: product_id,
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdFetchProductAjax,
                cmd: 'delR',
                security: ETCPF.ETCPF_nonce
            },
            success: function (res) {
                console.log("Deleted successsfully");
                jQuery(t).parent().find('.spinner').css('visibility', 'hidden');
                googleShowSelectedProductTables();
            }
        });
    } else {
        return;
    }
}

function cpf_remove_feed_parent(row) {
    if (confirm("Are you sure you want to deleted this feed?")) {
        t = row;
        jQuery(row).parent().find('.spinner').css('visibility', 'visible');
        var rows_number = jQuery("#cpf-the-list tr").length;
        var parent = jQuery(row).parent().parent();
        jQuery(parent).remove();
        if (rows_number == 1) {
            jQuery("#cpf-the-list").append('<tr id="cpf-no-products-search"><td colspan="5">No product search.</td></tr>');
        } else {
            return;
        }
    }

}

function selectAllProducts(selector) {
    var checked = jQuery("#etcpf_select_all_checkbox").is(':checked');
    let SelectedProducts = JSON.parse(sessionStorage.getItem('Customfeedselectedproducts'));
    let MappedCategories = JSON.parse(sessionStorage.getItem('Customfeedcategorymap'));
    if (checked === true) {
        jQuery("#etcpf-the-list").find("input[type=checkbox]").prop('checked', true);
        jQuery('.form-table tbody').find('tr.parent-tr').each(function (index, row) {
            var selected = jQuery(this).find('input[type=checkbox]').prop('checked', true);
            jQuery(this).addClass('checked-fcked-class-bg');
            selected.each(function (i, item) {
                let parentId = jQuery(this).val();
                let childrensClass = jQuery(this).data('child');
                let selectedproductcategory = jQuery('.parent-' + parentId).data('cat_slugs');
                if (!MappedCategories.categories.hasOwnProperty(selectedproductcategory)) {
                    MappedCategories.categories[selectedproductcategory] = {
                        'child_count': 0,
                        'parent_count': 1,
                        'remote_category': '',
                        'has_parent': true
                    };
                } else {
                    MappedCategories.categories[selectedproductcategory].parent_count++;
                }
                childrens = [];
                jQuery("." + childrensClass).find("input[type=checkbox]").prop('checked', true).each(function (index, data) {
                    jQuery('.' + childrensClass).addClass('checked-fcked-class-bg');
                    childrens.push(jQuery(data).val());
                    MappedCategories.categories[selectedproductcategory].child_count++;
                });
                SelectedProducts.products[parentId] = {'child': {'ids': childrens}};
            })
        });
    } else {
        jQuery("#etcpf-the-list").find("input[type=checkbox]").removeAttr('checked');
        jQuery("#etcpf-the-list").find("input[type=checkbox]").attr('checked', false);
        jQuery('.form-table tbody').find('tr.parent-tr').each(function (index, row) {
            jQuery(this).removeClass('checked-fcked-class-bg');
            var selected = jQuery(this).find('input[type=checkbox]').attr('checked', false);
            selected.each(function (i, item) {
                let parentId = jQuery(this).val();
                let selectedproductcategory = jQuery('.parent-' + parentId).attr('data-cat_slugs');
                console.log(selectedproductcategory);
                let childrensClass = jQuery(this).attr('data-child');
                jQuery('.' + childrensClass).removeClass('checked-fcked-class-bg');
                if (MappedCategories.categories.hasOwnProperty(selectedproductcategory)) {
                    MappedCategories.categories[selectedproductcategory].parent_count--;
                }
                if (MappedCategories.categories[selectedproductcategory].parent_count <= 0) {
                    delete MappedCategories.categories[selectedproductcategory];
                }
                delete SelectedProducts.products[parentId];
                /*======================================================================================================
                 *let childrensClass = jQuery(this).attr('data-child');
                childrens = [];
                jQuery("." + childrensClass).find("input[type=checkbox]").attr('checked', true).each(function (index, data) {
                    childrens.push(jQuery(data).val());
                });
                data.products[parentId] = {'child':{'ids':childrens}};
                ======================================================================================================*/
            })
        });
    }
    console.log(SelectedProducts);
    sessionStorage.setItem('Customfeedselectedproducts', JSON.stringify(SelectedProducts));
    sessionStorage.setItem('Customfeedcategorymap', JSON.stringify(MappedCategories));
    manageHtmlForSelectedCategories(MappedCategories);
}


function selectAllProducts_1(selector) {
    var checked = jQuery("#cpf_select_all_checkbox_1").attr('checked');
    if (checked == 'checked') {
        jQuery("#cpf-the-list_1").find("input[type=checkbox]").attr('checked', true);
    } else {
        jQuery("#cpf-the-list_1").find("input[type=checkbox]").removeAttr('checked');
    }
}

function addRows(selector) {
    var tr_html = '';
    var categoryList = jQuery("#cpf_attrdropdownlist .cpf_default_attributes").html();
    var merchantList = jQuery("#cpf_merchantAttributes").html();
    jQuery(categoryList).find('.cpf_custom_value_span').remove();
    tr_html += '<tr>';
    tr_html += '<td style="text-align: center">' + merchantList + '</td>';
    tr_html += '<td style="text-align: center" ><select name="cpf_type " id="cpf_change_type" class="cpf_change_type" onchange="cpf_changeType(this);"><option value="0">Attributes</option><option value="1">Custom Value</option></select></td>';
    tr_html += '<td style="text-align: center" class="cpf_value_td">' + categoryList;
    tr_html += '<span class="cpf_custom_value_span" style="display:none;"><input type="text"  class="cpf_custom_value_attr" name="cpf_custom_value" style="width:100%"/></span></td>';
    tr_html += '<td style="text-align: center"><input type="text" class="cpf_prefix" name="cpf_prefix" style="width:100%"/></td>';
    tr_html += '<td style="text-align: center"><input type="text" class="cpf_suffix" name="cpf_suffix" style="width:100%" /></td>';
    tr_html += '<td style="text-align: center"></td>';
    tr_html += '<td style="width: 5%;text-align: center"><span class="dashicons dashicons-plus" onclick="addRows(this);" title="Add Rows"></span></td>';
    tr_html += '<td style="width: 5%;text-align: center"><span class="dashicons dashicons-trash" onclick="removeRows(this);" title="Delete Rows"></span></td>';
    tr_html += '</tr>';
    jQuery("#cpf_custom_feed_config_body").append(tr_html);
}

function removeRows(selector) {
    var tr_length = jQuery("#cpf_custom_feed_config_body tr").length;
    if (tr_length == 1) {
        jQuery("#cpf_custom_feed_config_body tr").find("span.dashicons-trash").removeAttr('onclick');
    }
    var parent = jQuery(selector).parent().parent();
    jQuery(parent).remove();
}

function cpf_changeType(selector) {
    t = selector;
    if (selector.value == 1) {
        //jQuery(t).parent().parent().find(".attribute_select").removeAttr('selected');
        jQuery(t).parent().parent().find(".cpf_custom_value_span").show();
        jQuery(t).parent().parent().find(".cpf_custom_value_attr").focus();
        jQuery(t).parent().parent().find(".cpf_default_attributes").hide();
        jQuery(t).parent().parent().find(".attribute_select").hide();
    }
    if (selector.value == 0) {
        jQuery(t).parent().parent().find(".cpf_custom_value_span").hide();
        jQuery(t).parent().parent().find(".cpf_custom_value_attr").hide();
        jQuery(t).parent().parent().find(".cpf_default_attributes").show();
        jQuery(t).parent().parent().find(".attribute_select").show();
        jQuery(t).parent().parent().find(".attribute_select").focus();
    }
}

function toggleFeedSettings() {
    var display = jQuery("#cpf_custom_feed_config").css('display');
    var event = jQuery("#cpf_feed_config_link");
    var advance_section = jQuery("#cpf_advance_section").css('display');
    var advance_section_button = jQuery("#cpf_advance_section_link");
    if (advance_section == 'block') {
        jQuery("#cpf_advance_section").slideUp();
        jQuery("#feed-advanced").slideUp();
        // jQuery("#bUpdateSetting").slideUp();
        jQuery(advance_section_button).attr('title', 'This will open Feed Customization section where you can customize your feed using Feed Customization.');
        jQuery(advance_section_button).val('Show Feed Customization Option');
        //jQuery('.loadcustomfeed').remove();
    }
    if (display == 'none') {
        jQuery("#cpf_feed_config_desc").slideDown();
        jQuery("#cpf_custom_feed_config").slideDown();
        jQuery(event).val('Hide Feed Config');
        jQuery(event).attr('title', 'Hide Feed Customization Option');

        //getCustomFeedConfig();
        /* var divPosition = jQuery("#cpf_custom_feed_config").offset();
         jQuery('#custom_feed_settingd').animate({scrollBottom: divPosition.top}, "slow");*/
    }
    if (display == 'block') {
        jQuery("#cpf_custom_feed_config").slideUp();
        jQuery("#cpf_feed_config_desc").slideUp();
        jQuery(event).attr('title', 'This will open feed config section below.You can provide suffix and prefix for the attribute to be included in feed.');
        jQuery(event).val('Show Feed Customization Option');
        //jQuery('.loadcustomfeed').remove();
    }
}

/*============================================ Custom Feed Initialized ================================================*/

function etcpf_getCustomProductsTab() {

}

function searchProduct(action = null, loadmore = false) {
    //let SelectedProducts = sessionStorage.getItem('Customfeedselectedproducts');
    let SelectedProducts = JSON.parse(sessionStorage.getItem('Customfeedselectedproducts'));
    let MappedCategories = JSON.parse(sessionStorage.getItem('Customfeedcategorymap'));
    console.log(SelectedProducts);
    console.log(MappedCategories);
    jQuery("#etcpf_select_all_checkbox").attr('checked', false);
    etcpf_sku_filter = jQuery('#etcpf_sku_filter').val();
    etcpf_localcategory_list = jQuery('#etcpf_locacategories_filter').val();
    etcpf_stock_list = jQuery('#etcpf_stock_filter').val();
    pagevalue = jQuery('#etcpf_page_hidden_page_item').val();
    if (loadmore == true && action == 'next') {
        console.log(pagevalue);
        pagevalue = parseInt(pagevalue) + 1;
        console.log(pagevalue);
        jQuery('#etcpf_page_hidden_page_item').val(pagevalue);
    } else if (loadmore == true && action == 'prev') {
        pagevalue = parseInt(pagevalue) - 1;
        jQuery('#etcpf_page_hidden_page_item').val(pagevalue);
    } else {
        jQuery('#etcpf_page_hidden_page_item').val(0);
        pagevalue = 0;
    }
    jQuery('#ajax-loader-cat-import').show();
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdSearchProduct,
            security: ETCPF.ETCPF_nonce,
            perform: 'searchProduct',
            params: {
                'keywords': etcpf_sku_filter,
                'category': etcpf_localcategory_list,
                'stockstatus': etcpf_stock_list,
                'page': pagevalue,
                'products': JSON.stringify(SelectedProducts.products)
            }
        },
        success: function (res) {
            if (res.data) {
                if (loadmore == false) {
                    jQuery('#etcpf-the-list').html(res.data.products);
                } else {
                    jQuery('#etcpf-the-list').html(res.data.products);
                }
                jQuery('.total-pages').html(res.data.pages);
                if (action == 'prev') {
                    if (pagevalue + 1 >= (parseInt(res.data.pages))) {
                        jQuery('#next-cbx-angle-custom-feed').css({'pointer-events': 'none'});
                    } else if (pagevalue + 1 == 1) {
                        jQuery('#prev-cbx-angle-custom-feed').css({'pointer-events': 'none'});
                        jQuery('#next-cbx-angle-custom-feed').removeAttr('style');
                    } else {
                        jQuery('#next-cbx-angle-custom-feed').removeAttr('style');
                    }
                    jQuery('#current-page-selector').val(parseInt(pagevalue) + 1);
                } else if (action == 'next') {
                    console.log('pagevalue:' + pagevalue + 'totalpages:' + res.data.pages);
                    jQuery('#prev-cbx-angle-custom-feed').removeAttr('style');
                    if (pagevalue + 1 >= res.data.pages) {
                        jQuery('#next-cbx-angle-custom-feed').css({'pointer-events': 'none'});
                    }
                    jQuery('#current-page-selector').val(parseInt(pagevalue) + 1);
                } else {
                    console.log('pagevalue:' + pagevalue + 'totalpages:' + res.data.pages);
                    if (pagevalue + 1 >= (parseInt(res.data.pages))) {
                        console.log('pagevalue is greater');
                        jQuery('#next-cbx-angle-custom-feed').css({'pointer-events': 'none'});
                    } else if (pagevalue + 1 <= 1) {
                        jQuery('#prev-cbx-angle-custom-feed').css({'pointer-events': 'none'});
                        if (pagevalue + 1 < res.data.pages) {
                            jQuery('#next-cbx-angle-custom-feed').removeAttr('style');
                        }
                    } else {
                        console.log('next should be clickable');
                        jQuery('#next-cbx-angle-custom-feed').removeAttr('style');
                    }
                    jQuery('#current-page-selector').val(parseInt(pagevalue) + 1);
                }
                jQuery('#ajax-loader-cat-import').hide();
            } else {
                console.log("Data should be in array from");
                jQuery('#ajax-loader-cat-import').hide();
            }
        },
        error: function (res) {
            console.log('Error: ' + res);
            jQuery('#ajax-loader-cat-import').hide();
        }
    });
}

jQuery(document).ready(function () {
    if (typeof SelectedProducts == 'undefined') {
        SelectedProducts = {'products': {}};
    }
    if (typeof MappedCategory == 'undefined') {
        MappedCategory = {'categories': {}};
    }
    //let SelectedProducts = {'products': {}};
    /* _proto of custom product list */
    sessionStorage.setItem('Customfeedselectedproducts', JSON.stringify(SelectedProducts));
    sessionStorage.setItem('Customfeedcategorymap', JSON.stringify(MappedCategory));
    jQuery(document).on("click", '.parent-product-checkbox', function () {
        jQuery('.etcpf-custom-category-mapping-loader').show();
        let SelectedProducts = JSON.parse(sessionStorage.getItem('Customfeedselectedproducts'));
        let MappedCategories = JSON.parse(sessionStorage.getItem('Customfeedcategorymap'));
        t = this;
        parentId = jQuery(this).val();
        currentClass = jQuery(this).closest('tr')[0].className;
        var childClass = jQuery(this).data('child');
        let selectedproductcategory = jQuery('.parent-' + parentId).data('cat_slugs');
        if (jQuery(this).is(':checked')) {
            if (!MappedCategories.categories.hasOwnProperty(selectedproductcategory)) {
                MappedCategories.categories[selectedproductcategory] = {
                    'child_count': 0,
                    'parent_count': 1,
                    'remote_category': '',
                    'has_parent': true
                };
            } else {
                MappedCategories.categories[selectedproductcategory].parent_count++;
            }
            jQuery(this).closest('tr').addClass('checked-fcked-class-bg');
            jQuery('.' + childClass).addClass('checked-fcked-class-bg');
            childrens = [];
            var childClass = jQuery(this).data('child');
            console.log("=====================================");
            console.log('childClass',childClass);
            console.log("=====================================");
            jQuery("." + childClass).find("input[type=checkbox]").prop('checked', true).each(function (index, data) {
                if (jQuery(data).val()) {
                    childrens.push(jQuery(data).val());
                } else {
                    childrens.push(null);
                }
            });
            MappedCategories.categories[selectedproductcategory].child_count = parseInt(MappedCategories.categories[selectedproductcategory].child_count) + parseInt(childrens.length);
            SelectedProducts.products[parentId] = {'child': {'ids': childrens}};
            /* for(var key in data.products){
                 console.log(key);
             }*/
        } else {
            MappedCategories.categories[selectedproductcategory].parent_count--;
            if (MappedCategories.categories[selectedproductcategory].parent_count <= 0) {
                delete MappedCategories.categories[selectedproductcategory];
            }
            jQuery(this).closest('tr').removeClass('checked-fcked-class-bg');
            jQuery('.' + childClass).removeClass('checked-fcked-class-bg');
            delete SelectedProducts.products[parentId];
            var temp = jQuery(this).closest('tr')[0].className.split(" ");
            var childClass = jQuery(this).data('child');

            /** jQuery(this).closest('tr').removeClass(temp[2]); */

            jQuery("." + childClass).find("input[type=checkbox]").prop('checked', false);

            /** jQuery("." + childClass).removeClass(temp[1]); */

        }
        list = SelectedProducts;
        sessionStorage.setItem('Customfeedselectedproducts', JSON.stringify(list));
        sessionStorage.setItem('Customfeedcategorymap', JSON.stringify(MappedCategories));
        manageHtmlForSelectedCategories(MappedCategories);
        jQuery('.etcpf-custom-category-mapping-loader').hide();
        return true;
    });

    jQuery(document).on("click", '.child-product-checkbox', function () {
        let SelectedProducts = JSON.parse(sessionStorage.getItem('Customfeedselectedproducts'));
        let MappedCategories = JSON.parse(sessionStorage.getItem('Customfeedcategorymap'));
        t = this;
        parentClass = jQuery(this).data('parent');
        let selectedproductcategory = jQuery('.' + parentClass).data('cat_slugs');
        var temp = parentClass.split('-');
        let parentId = temp[1];
        /**
         * If child is selected insert into session storage
         * */
        if (jQuery(this).is(':checked')) {
            /** Assigining multiple categories **/
            if (!MappedCategories.categories.hasOwnProperty(selectedproductcategory)) {
                MappedCategories.categories[selectedproductcategory] = {
                    'child_count': 1,
                    'parent_count': 0,
                    'remote_category': '',
                    'has_parent': true
                };
            } else {
                MappedCategories.categories[selectedproductcategory].child_count++;
            }
            jQuery('.' + parentClass).addClass('checked-fcked-class-bg');
            jQuery(this).closest('tr').addClass('checked-fcked-class-bg');
            jQuery("." + parentClass).find("input[type=checkbox]").prop('checked', true);
            if (SelectedProducts.products.hasOwnProperty(parentId)) {
                if (jQuery(this).val()) {
                    SelectedProducts.products[parentId].child.ids.push(jQuery(this).val());
                } else {
                    SelectedProducts.products[parentId].child.ids.push(null);
                }
            } else {
                MappedCategories.categories[selectedproductcategory].parent_count++;
                if (jQuery(this).val()) {
                    SelectedProducts.products[parentId] = {'child': {'ids': [jQuery(this).val()]}}
                } else {
                    SelectedProducts.products[parentId] = {'child': {'ids': [null]}}
                }
            }
        } else {
            MappedCategories.categories[selectedproductcategory].child_count--;
            /*if (MappedCategories.categories[selectedproductcategory].child_count <= 0 && MappedCategories.categories[selectedproductcategory].has_parent == true) {
                delete MappedCategories.categories[selectedproductcategory];
                console.log(MappedCategories);
            }*/
            /* If child is unselected remove from session storage */
            if (jQuery('.childof-' + parentId).find('input:checkbox:checked').length <= 0) {
                MappedCategories.categories[selectedproductcategory].parent_count--;
                if (MappedCategories.categories[selectedproductcategory].parent_count <= 0) {
                    delete MappedCategories.categories[selectedproductcategory];
                }
                jQuery('.' + parentClass).removeClass('checked-fcked-class-bg');
                jQuery(this).closest('tr').removeClass('checked-fcked-class-bg');
                jQuery("." + parentClass).find("input[type=checkbox]").attr('checked', false);
            } else {
                jQuery(this).closest('tr').removeClass('checked-fcked-class-bg');
            }
            if (SelectedProducts.products.hasOwnProperty(parentId)) {
                SelectedProducts.products[parentId].child.ids.pop(jQuery(this).val());
            } else {
                SelectedProducts.products[parentId] = {'child': {'ids': [jQuery(this).val()]}}
            }
        }
        list = SelectedProducts;
        sessionStorage.setItem('Customfeedselectedproducts', JSON.stringify(list));
        sessionStorage.setItem('Customfeedcategorymap', JSON.stringify(MappedCategories));
        manageHtmlForSelectedCategories(MappedCategories);
        return true;
    });

    jQuery(document).on('click', '.assign-multiple-remote-category', function () {
        let localcategories = jQuery(this).attr('data-localcategories');
        jQuery(this).val("");
        etsycustomcategoryselection('Etsy', 'default', localcategories);
    });

});

var manageHtmlForSelectedCategories = function (categories) {
    html = '<tr><th>Shop Category</th><th>Assign Etsy Category</th><th>No. of products selected</th></tr>';
    jQuery.each(categories.categories, function (i, data) {
        let remote_category = "click to select Etsy category";
        if (data.hasOwnProperty('showValue')) {
            remote_category = data.showValue;
        }
        //html += '<li>' + i + '<input id="'+i.replace(',','-')+'" class="assign-multiple-remote-category" data-localcategories="'+i+'" data-attribute="' + (data.child_count + data.parent_count) + '" type="text" value="'+remote_category+'" placeholder="select remote category">' + (data.child_count + data.parent_count) + ' products selected from this category</li>';
        html += '<tr><td align="center"><li>' + i + '<td align="center"><input id="' + i.replace(/,/g,'-') + '" class="assign-multiple-remote-category" data-localcategories="' + i + '" data-attribute="' + (parseInt(data.child_count) + parseInt(data.parent_count)) + '" type="text" value="' + remote_category + '" placeholder="select Etsy category"></td><td align="center">' + (parseInt(data.child_count) + parseInt(data.parent_count)) + ' products selected from this category</li></td>';
    });
    jQuery('#etcpf-custom-category-mapping-lists').html(html);
    return true;
};


/*=============================================== Custom Feed End ==================================================*/

function toggleGAdvanceCommandSectionDefault(event) {
    var display = jQuery("#cpf_advance_section_default").css('display');
    if (display == 'none') {
        jQuery("#cpf_advance_section_default").slideDown();
        jQuery(event).val('Hide Feed Customization Option');
        jQuery(event).attr('title', 'Hide Feed Customization Option section');
    }
    if (display == 'block') {
        jQuery("#cpf_advance_section_default").slideUp();
        jQuery("#feed-advanced-default").slideUp();
        // jQuery("#bUpdateSetting").slideUp();
        jQuery(event).attr('title', 'This will open feed Feed Customization Option section where you can customize your feed using Feed Customization Option.');
        jQuery(event).val('Show Feed Customization Option section');
    }
}

function showEtsyCategories(service_name, type) {
    if (type != 'default') {
        this_item = type;
    }
    chosen_categories = jQuery("#remote_category").val();

    var spinner = '<img class="etsy_fetch_category_spinner" src="' + ETCPF.plugins_url + '/images/ripple.gif" style="margin:0 150px;" />';
    jQuery.etcpf_colorbox({
        width: '500px',
        height: '420px',
        html: "<div class='categoryListRemoteFrame'><div class='categoryListRemote'><h1>Categories</h1>" + spinner + "<div id='etsy_list' style='display:none'>" + getEtsyCategoryList(chosen_categories) + "</div></div></div>"
    });
}

var etsycustomcategoryselection = function (service_name, type, localcategories) {
    var spinner = '<img class="etsy_fetch_category_spinner" src="' + ETCPF.plugins_url + '/images/ripple.gif" style="margin:0 150px;" />';
    jQuery.etcpf_colorbox({
        width: '500px',
        height: '420px',
        html: "<div class='categoryListRemoteFrame'><div class='categoryListRemote'><h1>Categories</h1>" + spinner + "<div id='etsy_list' style='display:none'>" + getEtsyCategoryListcustom(localcategories) + "</div></div></div>"
    });
};

function getEtsyCategoryListcustom(localcategories) {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            level: 1,
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdGetcustomfeed_etsy,
            security: ETCPF.ETCPF_nonce,
            perform: 'getEtsyCategories',
            localcat: localcategories
        },
        type: 'post',
        success: function (res) {
            console.log(res);
            jQuery('.etsy_fetch_category_spinner').hide();
            jQuery('#etsy_list').show().html(res);
        }
    });
}

function getEtsyCategoryList(chosen_category) {
    return parseEtsyCategories(chosen_category);
}

function parseEtsyCategories(chosen_category) {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            level: 1,
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce
        },
        type: 'post',
        success: function (res) {
            jQuery('.etsy_fetch_category_spinner').hide();

            jQuery('#etsy_list').show().html(res);
        }
    });
}

function connectToEtsy() {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyConnect,
            security: ETCPF.ETCPF_nonce,
            cmd: 'connect'
        },
        success: function (res) {
            winndow.location.reload();
        }
    });
}

var cpf_auth_url = "<?php echo $cpf_auth_url; ?>";

function cpf_update_auth_url() {
    var site_id = jQuery('#cpf-etcpf_site_id').val();
    var sandbox = jQuery('#cpf-sandbox_mode').val();
    jQuery('#btn_connect').attr('href', cpf_auth_url + '&sandbox=' + sandbox + '&site_id=' + site_id);
    jQuery('#frm_site_id').attr('value', site_id);
    jQuery('#frm_sandbox').attr('value', sandbox);

}

function etsy_custom_cat_tree(localcat, selector, level, paths = null, taxonomy = null, localcategory) {
    //console.log(ETCPF);
    let MappedCategories = JSON.parse(sessionStorage.getItem('Customfeedcategorymap'));
    MappedCategories.categories[localcat].remote_category = paths;
    MappedCategories.categories[localcat].taxonomy_path = taxonomy;
    MappedCategories.categories[localcat].showValue = jQuery(selector).val();
    jQuery(selector).parent('div').siblings('div.active').children('div').css('display', 'none');
    jQuery(selector).parent('div').children('div').css('display', 'block');
    jQuery(selector).parent('div').addClass('active');
    /* console.log(jQuery(selector).val());
     console.log(taxonomy);
     console.log(paths);*/
    jQuery('#' + localcat.replace(/,/g, '-')).val(jQuery(selector).val());
    sessionStorage.setItem('Customfeedcategorymap', JSON.stringify(MappedCategories));
    console.log(MappedCategories.categories);
}

function etsy_cat_tree(selector, level, paths = null, taxonomy = null) {

    // console.log(paths); console.log(taxonomy);
    jQuery(selector).parent('div').siblings('div.active').children('div').css('display', 'none');

    jQuery(selector).parent('div').children('div').css('display', 'block');
    jQuery(selector).parent('div').addClass('active');
    if (ETCPF.isTable) {
        jQuery(ETCPF.item).val(jQuery(selector).val());
        return;
    }
    jQuery('#remote_category').val(jQuery(selector).val());
    if (this_item) {
        jQuery(this_item).val(jQuery(selector).val());
    }

    if (paths != null && taxonomy != null) {
        var category_path = paths;
        var full_taxonomy_path = taxonomy;
        jQuery('#etsy-category-path').val(category_path);
        jQuery('#etsy-taxonomy-path').val(full_taxonomy_path);
    } else if (paths == null && taxonomy != null) {
        var category_path = paths;
        var full_taxonomy_path = taxonomy;
        jQuery('#etsy-category-path').val(category_path);
        jQuery('#etsy-taxonomy-path').val(full_taxonomy_path);
    } else {
        jQuery('#etsy-category-path').val('');
        jQuery('#etsy-taxonomy-path').val('');
    }

    jQuery('#etsy_category_display').val(jQuery(selector).val());

}

function makeDefaultEtsyShipping(selector) {
    var shipping_id = jQuery(selector).val();

    jQuery(selector).siblings('div.spinner1').find('img#loadgif2').css('display', 'block');
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
            shipping_id: shipping_id,
            level: 6
        },
        type: "post",
        success: function (res) {
            alert(res);
            window.location.reload();
            // guide_to_create_new_feed();
        }
    });
}

function changeSettingsofEtsy() {
    var request = jQuery('#request_per_minute').val(),
        uploads = jQuery('#max_upload').val(),
        who_made = jQuery("#who_made").val(),
        when_made = jQuery("#when_made").val(),
        state = jQuery("#state").val(),
        etsy_api_limit = jQuery("#etsy_api_limit").val(),
        etsy_calculated_shipping = jQuery('#etsy_calculated_shipping').val();
    jQuery("#etsy-fkp-p").html("updating please wait...");
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
            request_per_minute: request,
            max_upload: uploads,
            level: 9,
            who_made: who_made,
            when_made: when_made,
            state: state,
            etsy_api_limit: etsy_api_limit,
            etsy_calculated_shipping: etsy_calculated_shipping
        },
        type: "post",
        dataType: "json",
        success: function (res) {
            if (res.status == true) {
                var html = "<p>All changes have been updated!</p>";
                jQuery('#submitdiv').find('p').html(html);
                jQuery("#etsy-fkp-p").html("Updated.");

            } else {
                jQuery('#submitdiv').find('p').html(res.error_msg);
                jQuery("#etsy-fkp-p").html(res.error_msg);
            }
        }
    });
}


function deleteEtsyShop() {
    var check = confirm('Are you sure ? You are about to delete the shop.');
    if (check) {
        jQuery.ajax({
            url: ajaxurl,
            data: {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdEtsyProcessings,
                security: ETCPF.ETCPF_nonce,
                level: 10
            },
            type: "post",
            dataType: "json",
            success: function (res) {
                if (res.result === true) {
                    alert(res.msg);
                    window.location.reload();
                } else {
                    alert(res.msg)
                }
            }
        });
    }
}

function newShippingTemplate() {
    jQuery.ajax({
        url: ajaxurl,
        type: "post",
        data: {
            level: 2,
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
        },
        success: function (res) {
            jQuery('#the-list').append(res);
        }
    });
}

function addShippingTemplate() {

    var title = jQuery('input[name=title]').val();
    var country_id = jQuery('select[name=origin_country_id]').val();
    var min_processing_days = parseInt(jQuery('input[name=min_processing_days]').val());
    var max_processing_days = parseInt(jQuery('input[name=max_processing_days]').val());
    var primary_cost = jQuery('input[name=primary_cost]').val();
    var secondary_cost = jQuery('input[name=secondary_cost]').val();
    var make_default = jQuery('#make_default:checked').val();
    var flag = 1;
    if (!make_default) {
        flag = 0;
    }
    if (!title) {
        jQuery('input[name=title]').focus();
        return;
    }
    if (!country_id) {
        jQuery('input[name=origin_country_id]').focus();
        return;
    }
    if (!min_processing_days) {
        jQuery('input[name=min_processing_days]').focus();
        return;
    }
    if (!max_processing_days) {
        jQuery('input[name=max_processing_days]').focus();
        return;
    }
    if (!primary_cost) {
        jQuery('input[name=primary_cost]').focus();
        return;
    }
    if (!secondary_cost) {
        jQuery('input[name=secondary_cost]').focus();
        return;
    }
    if (min_processing_days > max_processing_days) {
        jQuery('.msg_box_new_ship').find('th').html('Mininimum processing days cannot be greater than Maximum Processing days');
        return;
    }
    jQuery('.shipping_loader').show();
    var upload_data = {
        title: title,
        origin_country_id: country_id,
        min_processing_days: min_processing_days,
        max_processing_days: max_processing_days,
        primary_cost: primary_cost,
        secondary_cost: secondary_cost
    };
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
            shipping_details: upload_data,
            flag: flag,
            level: 5
        },
        type: "post",
        success: function (res) {
            jQuery('.shipping_loader').hide();
            jQuery('#shipsubmit').html(res);
            window.location.reload();
        }
    });
}

function updateShippingTemplate() {
    jQuery('.etcpf_gif_loader').show();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
            level: 11
        },
        success: function (res) {
            jQuery('.etcpf_gif_loader').hide();
            jQuery('.msg_box_ship').html(res);
            console.log(res);
        }
    });
}

function etcpf_call_out_for_account() {
    var html;
    /*html = '<div class="noUserFrame">';
    html += '<div class="nouser" style="margin: 5px">';
    html += '<h1>Welcome To Etsy Feed Plugin</h1>';
    html += '<p>You need to connect with Etsy Shop First.</p>';
    html += '<p>Then only you can proceed to create new feed and select Etsy Categories.</p>';
    html += '<p><a href="'+ETCPF.cmdEtsyShop+'" class="button button-primary">Connect with Etsy</a>.</p>';
    html += '</div>';
    html += '</div>';*/
    html = '<div id="etcpf_connect_shop">';
    html += '<div id="etcpf_connect_header"><h1>Welcome to Etsy Feed Plugin</h1></div>';
    html += '<div id="etcpf_connect_body"><p>You need to connect with Etsy shop first to create feeds.</p></div>';
    html += '<div id="etcpf_connect_footer"><p><a href="' + ETCPF.cmdEtsyShop + '" class="button button-primary button-hero">Connect with Etsy</a></p></div>';
    html += '</div>';
    jQuery.etcpf_colorbox({
        html: html,
        closeButton: false,
        width: '443px',
        height: '208px',
        className: 'bordercurl'
    });
}

function guide_to_etsy_shipping() {
    var html;
    html = '<div class="noShippingFrame">';
    html += '<div class="noshipping" style="margin: 5px">';
    html += '<h1>Few More Steps</h1>';
    html += '<p>Congratulation on connecting Etsy with Etsy Feed.</p>';
    html += '<p>Now lets define some shipping template.</p>';
    html += '<p><a href="' + ETCPF.cmdEtsyShop + ETCPF.cmdEtsyShipping + '" class="button button-primary"> Make Shipping Template</a></p>';
    html += '</div>';
    html += '</div>';
    jQuery.etcpf_colorbox({
        html: html,
        closeButton: false
    });
}

function guide_to_create_new_feed() {
    var html;
    html = '<div class="noShippingFrame">';
    html += '<div class="noshipping" style="margin: 5px">';
    html += '<h1>Setup Completed!</h1>';
    html += '<p>You are now ready to create some Feed and upload.</p>';
    html += '<p>Proceed to create feed or you can make some changes regarding to feeds and system functionality.</p>';
    html += '<p></p>';
    html += '<p><a href="' + ETCPF.cmdEtsyShop + ETCPF.cmdEtsyCreateFeed + '" class="button button-primary">Start Creating Feed</a> OR';
    html += '<p><a href="' + ETCPF.cmdEtsyShop + ETCPF.cmdEtsyConfiguration + '" class="button button-primary">To Some Settings</a>';
    html += '</p>';
    html += '</div>';
    html += '</div>';
    jQuery.etcpf_colorbox({
        html: html,
        closeButton: false
    });
}

function guide_to_upload() {
    var html;
    html = '<div class="noShippingFrame">';
    html += '<div class="noshipping" style="margin: 5px">';
    html += '<h1>New Feed Created</h1>';
    html += '<p>Your feed to be uploaded.</p>';
    html += '<p>Proceed to manage feed and upload, or you can click the button below.</p>';
    html += '<strong>Before you proceed further, please add these lines of cron schedules to cpanel of your website:</strong><br> <blockquote>php -f ' + ETCPF.cmdEtsyUploadFeed + ' > /dev/null</blockquote>';
    html += '<p>If you are new to scheduling a cron. Please follow this guidance <a href="https://documentation.cpanel.net/display/ALD/Cron+Jobs" target="_blank">here</a>.</p>';
    html += '<p><a href="' + ETCPF.cmdEtsyUploadData + '" class="button button-primary">Start Uploading</a>';
    html += '</p>';
    html += '</div>';
    html += '</div>';
    jQuery.etcpf_colorbox({
        html: html,
        closeButton: false
    });
}

function etsy_success_verification() {
    // reloadpage();
    window.open('http://www.exportfeed.com/etsy-connected-successfully/', '_blank');
    window.focus();

}

function reloadpage() {
    window.location.href = ETCPF.cmdEtsyShop;
}

function get_product_list_by_feeds() {
    var category = jQuery('select[name=feeds]').val();
    // var products = jQuery('select[name=product_status]').val();
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
            level: 'fetch_products',
            feed_id: category
            // products : products
        },
        type: 'post',
        success: function (res) {
            jQuery('#etcpf-product-list').html(res);
        }
    });
}

function upload_listing(id, category) {
    jQuery.etcpf_colorbox({
        width: '500px',
        height: '420px',
        html: "<div class='etsyUploadStatus'><div class='Upload'><h1>Upload Status</h1><div id='upload_list'>" + ETCPF.loadImg + uploadListing(id, category) + "</div><div id='status_message'></div></div></div>"
    });
}

function uploadListing(id, category) {
    load_img = '<img src="' + ETCPF.plugins_url + "images/spinner-2x.gif" + '" id="load_img" />';
    console.log(load_img);
    // jQuery('#upload_list').html(load_img);

    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
            level: 'upload_to_etsy',
            remote_category: category,
            item_id: id
        },
        type: 'post',
        success: function (res) {
            console.log(res);
            jQuery('#upload_list').hide();
            jQuery('#status_message').append(res);
        }
    });
}

function get_etsy_category(item) {
    jQuery.etcpf_colorbox({
        width: '500px',
        height: '420px',
        html: "<div class='categoryListRemoteFrame'><div class='categoryListRemote'><h1>Categories</h1><div id='etsy_list'>" + listEtsyCategory(item) + "</div></div></div>"
    });
}

function listEtsyCategory(item) {
    ETCPF.isTable = true;
    ETCPF.item = item;
    parseEtsyCategories();
}

function refreshEtsyUploadStatus() {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
            level: 'update_upload_message'
        },
        type: 'post',
        success: function (res) {
            console.log(res);
        }
    });
}

function view_etsy_uploaded_listing(item_id, thisItem) {
    var upload_details = {
        details_id: jQuery(thisItem).siblings('input[type=hidden].details_id').val(),
        shop_id: jQuery(thisItem).siblings('input[type=hidden].shop_id').val(),
        shipping_template_id: jQuery(thisItem).siblings('input[type=hidden].shipping_template_id').val(),
        listing_id: jQuery(thisItem).siblings('input[type=hidden].listing_id').val(),
        who_made: jQuery(thisItem).siblings('input[type=hidden].who_made').val(),
        when_made: jQuery(thisItem).siblings('input[type=hidden].when_made').val(),
        state: jQuery(thisItem).siblings('input[type=hidden].state').val(),
        is_supply: jQuery(thisItem).siblings('input[type=hidden].is_supply').val()
    };
    var html = "<div>";
    html += "<p>LISTING ID : " + upload_details.listing_id + "</p>";
    html += "<p>Shipping Template : " + upload_details.shipping_template_id + "</p>";
    html += "<p>Etsy User : " + upload_details.shop_id + "</p>";
    html += "<p>State : " + upload_details.state + "</p>";
    html += "<p>When Made : " + upload_details.when_made + "</p>";
    html += "<p>Who Made : " + upload_details.who_made + "</p>";
    html += "<div>";
    jQuery.etcpf_colorbox({
        width: '500px',
        height: '420px',
        html: html
    });
}

function upload_in_bulk(opt) {
    if ((jQuery('tbody[data-wp-lists]').find('input:checkbox:checked').length) == 0) {
        alert("Please select atleast one product from product search list.");
        return false;
    }
    jQuery('tbody[data-wp-lists]').find('input:checkbox:checked').each(function (i, data) {

        var product_id = jQuery(this).val();
        var remote_category = jQuery(this).attr('data-remote');
        upload_listing(product_id, remote_category);
    });
}

function doupload_listing() {
    jQuery('#report_msg').html('Uploading if any products are left to be uploaded.');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdUploadListing,
            security: ETCPF.ETCPF_nonce,
        },
        success: function (res) {
            jQuery('#report-msg').html(res);
        }
    });
}

function etcpf_fetch_login_url(selector) {
    var anchor = selector;
    jQuery('.login_token_etsy').show();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdFetchLoginURL,
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            jQuery('.login_token_etsy').hide();
            if (res.success) {
                jQuery('#redirect_to_login').show().find('a').attr({
                    href: res.url
                    // target : '_blank'
                });
                jQuery('a.redirect_to_login').attr('href', res.url).parent().addClass('current');
                jQuery(anchor).parent().addClass('visited');
                //jQuery(anchor).remove();
                var html = '<span class="dashicons dashicons-arrow-right"></span>';
                html += '<span>Login Url Fetched from Etsy</span>';
                html += '<span class="dashicons dashicons-yes"></span>';
                jQuery('#fetch_login_token').html(html);
            } else {
                alert("Seems like you made an error on API connection details. Please try again.");
                location.reload();
            }

        }
    });
}

function etcpf_authorize(selector) {
    var anchor = selector;
    jQuery('.authorize_token_etsy').show();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdAuthorizeURL,
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            console.log(res);
            jQuery('.authorize_token_etsy').hide();
            if (res.success) {
                jQuery('#show_myshop').show();
                // jQuery(anchor).remove();
                jQuery(anchor).parent().addClass('visited');
                var html = '<span class="dashicons dashicons-arrow-right"></span>';
                html += '<span>Token Authorized</span>';
                html += '<span class="dashicons dashicons-yes"></span>';
                jQuery('#authorize_token').html(html);
                jQuery('.etcpf_shipping').attr('href', '?page=etsy-export-feed-configure&tab=settings').parent().addClass('current');

            }
        }
    });
}

function updateListing() {
    jQuery('.etcpf_edit_loader').show();
    var listing_id = jQuery('input[name=listing_id]').val();
    var id = jQuery('input[name=item_id]').val();
    var who_made = jQuery('#who_made').val();
    var when_made = jQuery('input[name=when_made]').val();
    var state = jQuery('#state').val();
    var shipping_template_id = jQuery('#shippingTemplate').val();

    if (who_made < 1)
        jQuery('#who_made').focus();

    if (when_made < 1)
        jQuery('input[name=item_price]').focus();

    if (state < 1)
        jQuery('#state').focus();

    if (shipping_template_id < 1)
        jQuery('#shippingTemplate').focus();

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdUpdateItem,
            security: ETCPF.ETCPF_nonce,
            listing_id: listing_id,
            who_made: who_made,
            when_made: when_made,
            state: state,
            shipping_template_id: shipping_template_id,
            id: id
        },
        success: function (res) {
            jQuery('.etcpf_edit_loader').hide();
            if (res) {
                jQuery('#submit_msg_box').html(res.msg);
                clearInterval(feedFetchTimer);
            }
        }
    });
    feedFetchTimer = window.setInterval(function () {
        etcpf_submitFeedStatus()
    }, 500);
}

function etcpf_submitFeedStatus() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdUploadStatus,
            security: ETCPF.ETCPF_nonce
        },
        success: function (res) {
            jQuery('#submit_msg_box').html(res);
        }
    });
}

function add_new_shipping() {
    var zone_ids = [];
    jQuery('.woocommerce_zones:checked').each(function () {
        zone_ids.push(jQuery(this).val());
    });
    console.log(zone_ids);
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
            level: 'create_new_shipping',
            zone_ids: zone_ids
        },
        success: function (res) {
            console.log(res);
        }
    });
}

let uploadfeedInterval;

function runJsCronForUpload(feedId, status = 'START') {
    ajaxRequest = null;
    etcpf_AjaxCall = null;
    jQuery("#showspin").html("Uploading...");
    jQuery(".etcpf-step li.current").removeClass("current");
    let activeSelector = jQuery(".etcpf-step li.active");
    activeSelector.next("li").addClass("active");
    activeSelector.next("li").addClass("current");
    activeSelector.next("li").find("span").addClass("active");
    uploadfeedInterval = setInterval(function () {
        doUploadFeed(feedId, status)
    }, 3000);
}

var getCurrentListing = function (FeedId) {
    if (jQuery("[name=choose]").is(':checked') === false) {
        alert("Please! Select at-least one variation upload type");
        return false;
    }

    if(jQuery("[name=choose]:checked").val()==='single' && jQuery("[name=variation_upload_profile]").val()==='0'){
        alert("Please select appropriate variation profile or choose another upload type. Thanks.");
        return false;
    }
    //let FeedId = jQery('#currently-uploading-feed').val();
    let resubmit = jQuery('#etsy-feed-resubmit').val(),
        uploadType = jQuery('input[name=choose]:checked').val(),
        variation_profile = jQuery('#variation-upload-type').val();

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyUploadFeed,
            security: ETCPF.ETCPF_nonce,
            feed_id: FeedId,
            resubmit: resubmit,
            status: 'FetchAllItemIds',
            uploadType: uploadType,
            variation_profile: variation_profile
        },

        success: function (items) {
            console.log(items)
            console.log(items.data.length)
            ITEMSIDS = items.data;
            if (items.data.length > 0) {
                runJsCronForUpload(FeedId, 'START');
            } else {
                let c = confirm("It seems all the listing are uploaded, do you want to reupload instead ?");
                if (c === true) {
                    location.replace(location.protocol + '//' + location.host + '/wp-admin/admin.php?page=etsy-export-feed-admin&cmd=upload&id=' + FeedId + '&resubmit=1');
                }
            }
        },

        error: function (res) {
            console.log("Error");
        }
    });
};

ITEMSIDS = null;

function UploadTheItem(itemid, feed_id, status) {
    jQuery('.etcpf-loader').show();
    let doneStatus = false;
    if (etcpf_AjaxCall == null) {

        jQuery('.item_id').html("Fetching...");
        jQuery('.upload_result').html('Fetching...');
        jQuery('.variation_result').html('Fetching...');
        jQuery('.message_span').html('Uploading...');
        resubmit = jQuery('#etsy-feed-resubmit').val();
        updatefailed = jQuery('#etsy-feed-uploadfailed').val();

        etcpf_AjaxCall = jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdEtsyUploadFeed,
                security: ETCPF.ETCPF_nonce,
                feed_id: feed_id,
                resubmit: resubmit,
                uploadfailed: updatefailed,
                status: status
            },
            success: function (res) {
                if (res) {
                    console.log(res.status);
                    var pid = res.item_id;
                    var listing_id = res.listing_id;
                    var listing_upload_result = '';

                    if (res.status == 'FINISH_CONFIRMED') {
                        jQuery(".etcpf-loader-div").html("COMPLETED");
                        jQuery(".etcpf-footer-action .spinner").hide();
                        jQuery("#showspin").attr("disabled", true);
                        jQuery(".etcpf-step li.active").next("li").addClass("active");
                        doneStatus = true;
                        jQuery("#showspin").html("Finished");

                    } else if (res.status == 'HAULT') {
                        jQuery('.item_id').html("Haulted");
                        jQuery('.upload_result').html('Haulted');
                        jQuery('.variation_result').html('Haulted');
                        jQuery('.message_span').html('Haulted becasuse api limit exceded. Try again in ' + res.time + ' hours.').css({'color': 'red'});
                        jQuery("#showspin").html("Haulted");
                    }

                    handleHtmlResponse(res, function (error, data) {
                        if (data == true) {
                            return true;
                        }
                    });


                }
            },
            error: function (res) {
                clearInterval(uploadfeedInterval);
            }
        });

    }
}

function doUploadFeed(feedID = null, status = 'START') {
    jQuery(".spinner").show();
    jQuery('.etcpf-loader').show();
    let doneStatus = false;
    if (ITEMSIDS.length > 0) {
        if (etcpf_AjaxCall === null) {
            let item_id = ITEMSIDS.pop();
            let itemid = item_id.id;
            jQuery('.item_id').html("Fetching...");
            jQuery('.upload_result').html('Fetching...');
            jQuery('.variation_result').html('Fetching...');
            jQuery('.message_span').html('Uploading...');
            let resubmit = jQuery('#etsy-feed-resubmit').val(),
                uploadType = jQuery('input[name=choose]:checked').val(),
                variation_profile = jQuery('#variation-upload-type').val(),
                updatefailed = jQuery('#etsy-feed-uploadfailed').val();

            etcpf_AjaxCall = jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'exportfeed_etsy',
                    feedpath: ETCPF.cmdEtsyUploadFeed,
                    security: ETCPF.ETCPF_nonce,
                    feed_id: feed_id,
                    resubmit: resubmit,
                    item_id: itemid,
                    uploadfailed: updatefailed,
                    status: status,
                    uploadType: uploadType,
                    variation_profile: variation_profile
                },
                success: function (res) {
                    etcpf_AjaxCall = null;
                    if (res) {
                        if (res.status === 'error') {
                            alert(res.message);
                            clearInterval(uploadfeedInterval);
                            location.reload();
                            return;
                        }
                        console.log(res.status);
                        let pid = res.item_id,
                            listing_id = res.listing_id,
                            listing_upload_result = '';

                        if (res.status == 'FINISH_CONFIRMED') {
                            clearInterval(uploadfeedInterval);
                            jQuery(".etcpf-loader-div").html("COMPLETED");
                            jQuery(".etcpf-footer-action .spinner").hide();
                            jQuery("#showspin").attr("disabled", true);
                            jQuery(".etcpf-step li.active").next("li").addClass("active");
                            doneStatus = true;
                            jQuery("#showspin").html("Finished");

                        } else if (res.status == 'FINISHED') {
                            ajaxRequest = null;
                            etcpf_AjaxCall = null;
                            clearInterval(uploadfeedInterval);
                            doUploadFeed(feedID, 'FINISHED');

                        } else if (res.status == 'CONFIRM_FINISHED') {
                            ajaxRequest = null;
                            etcpf_AjaxCall = null;
                            clearInterval(uploadfeedInterval);
                            doUploadFeed(feedID, 'CONFIRM_FINISHED');

                        } else if (res.status == 'HAULT') {
                            jQuery('.item_id').html("Haulted");
                            jQuery('.upload_result').html('Haulted');
                            jQuery('.variation_result').html('Haulted');
                            jQuery('.message_span').html('Haulted becasuse api limit exceded. Try again in ' + res.time + ' hours.').css({'color': 'red'});
                            jQuery("#showspin").html("Haulted");
                            clearInterval(uploadfeedInterval);
                            return;

                        } else if (res.status == 'CONTINUE') {
                            if ((res.failed_reason && res.failed_reason.includes("oauth_problem"))) {
                                console.log('Oauth Connection problem');
                                deleteAccountOauth();
                                clearInterval(uploadfeedInterval);
                            } else {
                                ajaxRequest = null;
                                etcpf_AjaxCall = null;
                                runJsCronForUpload(feedID, 'CONTINUE');
                            }
                        }

                        if (ITEMSIDS.length <= 0) {
                            jQuery('#showspin').html('Finished');
                            jQuery('.spinner').hide();
                        }

                        handleHtmlResponse(res, function (error, data) {
                            if (data == true) {
                                return true;
                            }
                        });


                    } else {
                        clearInterval(uploadfeedInterval);
                    }

                },

                error: function (res) {
                    clearInterval(uploadfeedInterval);
                    alert("Error");
                }
            });

        }
    } else {
        clearInterval(uploadfeedInterval);
        //jQuery('#showspin').html('Finished');
        jQuery('.spinner').hide();
    }
}

function deleteAccountOauth(){
    r = confirm("We are unable to authenticate with your Etsy account. Please reconnect to your Etsy shop to authenticate.");
    if (r == true) {
        console.log('inside delete');
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdEtsyProcessings,
                security: ETCPF.ETCPF_nonce,
                level: 'reconnect_etsy'
            },
            success: function (res) {
                console.log(res);
                window.location.href = 'admin.php?page=etsy-export-feed-configure';
            }
        });
    }
}

function delete_listing(listing_id, selector) {
    r = confirm("Are you sure you want to delete.");
    if (r == false) {
        return false;
    }
    t = selector;
    etcpf_AjaxCall = jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyUploadFeed,
            security: ETCPF.ETCPF_nonce,
            feed_id: feed_id,
            resubmit: 0,
            uploadfailed: 0,
            uploaditembyid: 0,
            itemId: listing_id,
            status: 'DELETE'
        },
        success: function (res) {
            if (res) {
                jQuery(selector).closest('tr').html("<td style='color: #ffffff;' colspan='5'>Particular Product Removed</td>").css({
                    'text-align': 'center',
                    'background-color': 'red'
                }).delay(800).fadeOut('slow', function () {
                    jQuery(selector).closest('tr').remove();
                });
            } else {
                alert("product could not be deleted. Please try again later");
            }
        },

        error: function (res) {
            alert("Error");
        }
    });
}

jQuery(document).on('click', '.relist-to-etsy', function (e) {
    t = this;
    let identifier = jQuery(this).attr('data-id');
    let parentId = jQuery(this).parent().attr('id');
    let resubmit = jQuery('#etsy-feed-resubmit').val();
    let updatefailed = jQuery('#etsy-feed-uploadfailed').val(),
        uploadType = jQuery('input[name=choose]:checked').val(),
        variation_profile = jQuery('#variation-upload-type').val();

    console.log(parentId);
    let c = confirm("Are you sure you want to relist this item. We didn't find this listing in etsy, but you can recheck to confirm. Continue Anyway ?");
    if (c == true) {
        jQuery('#' + parentId).html('Retrying to upload...').css({'color':'#343a40'});
        if(etcpf_AjaxCall==null){
            etcpf_AjaxCall = jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'exportfeed_etsy',
                    feedpath: ETCPF.cmdEtsyUploadFeed,
                    security: ETCPF.ETCPF_nonce,
                    feed_id: feed_id,
                    resubmit: resubmit,
                    uploadfailed: updatefailed,
                    uploaditembyid: true,
                    itemId: identifier,
                    status: 'REUPLOAD',
                    uploadType: uploadType,
                    variation_profile: variation_profile
                },
                success: function (res) {
                    if (res) {
                        var pid = res.item_id;
                        var listing_id = res.listing_id;
                        var listing_upload_result = '';
                        if (res.status == 'SUCCESS' || res.status == 'CONTINUE') {
                            etcpf_AjaxCall = null;
                            let pid = res.item_id;
                            jQuery(".etcpf-loader-div").html("COMPLETED");
                            jQuery(".etcpf-footer-action .spinner").hide();
                            jQuery("#showspin").attr("disabled", true);
                            jQuery(".etcpf-step li.active").next("li").addClass("active");
                            doneStatus = true;
                            jQuery("#showspin").html("Finished");
                            if(res.variation_result) {
                                jQuery('#upload_status_message_' + pid).html(res.variation_result);
                                var current_img = jQuery("#error_image_" + pid).find("img").attr("src").replace("alert.png",'tick.png');
                            } else {
                                jQuery('#upload_status_message_' + pid).html("Reupload Successful");
                                var current_img = jQuery("#error_image_" + pid).find("img").attr("src").replace("alert.png",'tick.png');
                            }
                        }
                    }
                },

                error: function (res) {
                    etcpf_AjaxCall = null;
                    clearInterval(uploadfeedInterval);
                    alert("Error");
                }
            });
        }
    } else {
        return false;
    }
    e.preventDefault();
    return false;
});


function handleHtmlResponse(res, callback) {
    console.log(res);
    var pid = res.item_id;
    var prepare_data = jQuery.parseJSON(res.data.prepared_data);
    var listing_id = res.listing_id;
    var state = prepare_data.state;
    var listing_upload_result = '';
    if (listing_id && (res.hasOwnProperty('failed_reason') == true && res.failed_reason != 'null')) {
        var img = null;
        var status = 'Uploaded Successfully';
        if (res.updatetask == true) {
            status = 'Updated Successfully';
        }
        var htmlclass = "etsy-success";
        if (res.image_id) {
            listing_upload_result = listing_upload_result + '<br> Image Id : ' + res.image_id;
        } else {
            listing_upload_result = listing_upload_result + '<p>Image Id : ' + 'Either Feature image was not there or there was some problem uploading it. Please try again or <a href="https://www.exportfeed.com/contact">contact</a> us </p>';
        }
    } else {
        var img = "alert.png";
        if (listing_id) {
            var status = 'Update Failed: ';
        } else {
            var status = 'Upload Failed: ';
        }
        listing_upload_result = res.failed_reason;
        var htmlclass = "error";
    }
    if (img !== null) {
        var current_img = jQuery("#error_image_" + pid).find("img").attr("src");
        if (img == 'alert.png') {
            if (current_img) {
                img = current_img.replace("tick.png", img);
            } else {
                img = null;
            }
        } else {
            img = current_img.replace("alert.png", img);
        }
        jQuery("#error_image_" + pid).find("img").attr("src", img);
    }

    status += listing_upload_result;
    console.log(listing_upload_result);
    if (res.variation_result) {
        status += '<br>' + res.variation_result;
    }
    jQuery('#product_item_id_' + pid).html(listing_id);
    jQuery('#product_state_id_' + pid).html(state);
    //jQuery('#product_item_id_message_' + pid).html(listing_upload_result);
    //jQuery('#product_item_id_message_' + pid).removeClass('upload_result');
    //jQuery('#product_item_id_variation_message_' + pid).html(res.variation_result).css({'color': '#66c6e4'});
    jQuery('#upload_status_message_' + pid).removeClass('message_span');
    jQuery('#product_item_id_' + pid).removeClass('item_id');
    jQuery('#upload_status_message_' + pid).addClass(htmlclass);
    jQuery('#upload_status_message_' + pid).html(status);
    callback(null, true);

}

function removeQueuelist() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdEtsyProcessings,
            security: ETCPF.ETCPF_nonce,
            level: 'remove_queue_list'
        },
        success: function (res) {
            console.log(res);
        }
    });
}

function colorVariation() {
    jQuery('.color-table').show();
    jQuery('.size-table').hide();
}

function sizeVariation() {
    jQuery('.color-table').hide();
    jQuery('.size-table').show();
}

function CheckPropertyPrice() {
    var $_on_property_value = jQuery("input[name='on_variation']:checked").val();
    console.log($_on_property_value);
    var attribute_value = jQuery('td .' + $_on_property_value).html();
    console.log(attribute_value);
    var t = jQuery('.red').siblings("td.price-td").find("select");
    jQuery(t).each(function (index, value) {
        console.log(jQuery(value).val())

    });
}

function showEditBox(selector, id, ecode) {
    jQuery(selector).closest('tbody').siblings().find("th.checkbox-cell input:checkbox").attr("checked", false);
    jQuery(selector).closest('tr').siblings().find("input[type=checkbox]").attr('checked', false);
    jQuery(selector).closest('tr').find("input[type=checkbox]").attr('checked', true);
    jQuery(".error-resolve-div-pep").hide();
    jQuery('#edit_product_' + id + '_' + ecode).show();
}

function show_advanced_attr(selector) {
    // console.log(selector);
    jQuery('#attributeMappings').toggle();
    jQuery('#adv-sec-block').toggle();
    jQuery('.dashicons').toggleClass('dashicons-arrow-down').toggleClass('dashicons-arrow-up');
    jQuery('.adv-section .inside').toggle();
    jQuery('.inside.advance-sec-block').toggle();
}


jQuery(document).ready(function (e) {

    jQuery('.price-selection-select').on('change', function () {
        var $_on_property_value = jQuery("input[name='on_variation']:checked").attr('data-value');
        console.log(this.id);
        var thisid = this.id.split("-")[1];
        console.log(this.value);
        var globalValue = this.value;
        var $test = jQuery(this).parent().siblings().find($_on_property_value + '-' + thisid);
        console.log($test);
        console.log(jQuery($test).selector);
        var actual_selector = jQuery($test).selector;
        var $attribute_val = jQuery('#' + actual_selector).html();
        console.log($attribute_val);

        var t = jQuery('.' + $attribute_val).siblings("td.price-td").find("select");
        jQuery(t).each(function (index, value) {
            console.log(jQuery(value));
            var $value_of_on_propery = jQuery(value).val(globalValue);
            if (jQuery(value).val() == null) {
                jQuery(value).append(jQuery('<option>', {
                    value: globalValue,
                    text: globalValue,
                    selected: 'selected'
                }));
            }
        });
    });

    jQuery('.sku-selection-select').on('change', function () {
        var $_on_property_value = jQuery("input[name='on_variation']:checked").attr('data-value');

        var thisid = this.id.split("-")[1];
        var globalValue = this.value;
        var $test = jQuery(this).parent().siblings().find($_on_property_value + '-' + thisid);

        var actual_selector = jQuery($test).selector;
        var $attribute_val = jQuery('#' + actual_selector).html();

        var t = jQuery('.' + $attribute_val).siblings("td.sku-td").find("select");
        jQuery(t).each(function (index, value) {

            var $value_of_on_propery = jQuery(value).val(globalValue);
            if (jQuery(value).val() == null) {
                jQuery(value).append(jQuery('<option>', {
                    value: globalValue,
                    text: globalValue,
                    selected: 'selected'
                }));
            }

        });
    });

    jQuery('.quantity-selection-select').on('change', function () {
        var $_on_property_value = jQuery("input[name='on_variation']:checked").attr('data-value');

        var thisid = this.id.split("-")[1];

        var globalValue = this.value;
        var $test = jQuery(this).parent().siblings().find($_on_property_value + '-' + thisid);

        var actual_selector = jQuery($test).selector;
        var $attribute_val = jQuery('#' + actual_selector).html();

        var t = jQuery('.' + $attribute_val).siblings("td.quantity-td").find("select");
        jQuery(t).each(function (index, value) {
            jQuery(value).attr("value", globalValue);
            var $value_of_on_propery = jQuery(value).val(globalValue);
            if (jQuery(value).val() == null) {
                jQuery(value).append(jQuery('<option>', {
                    value: globalValue,
                    text: globalValue,
                    selected: 'selected'
                }));
            }
        });
    });

    jQuery('.quantity-selection-input').on("input", function () {
        var $_on_property_value = jQuery("input[name='on_variation']:checked").attr('data-value');

        var thisid = this.id.split("-")[1];

        var globalValue = this.value;
        var $test = jQuery(this).parent().siblings().find($_on_property_value + '-' + thisid);

        var actual_selector = jQuery($test).selector;
        var $attribute_val = jQuery('#' + actual_selector).html();

        var t = jQuery('.' + $attribute_val).siblings("td.quantity-td").find("input");
        jQuery(t).each(function (index, value) {
            jQuery(value).attr("value", globalValue);
            var $value_of_on_propery = jQuery(value).val(globalValue);
            // if(jQuery(value).val()==null){
            //    jQuery(value).append(jQuery('<option>', {
            //        value: globalValue,
            //        text: globalValue,
            //        selected : 'selected'
            //    }));
            // }
        });
    });

    jQuery('.on-property-attribute').on('change', function () {
        jQuery('#variationForm')[0].reset();
        jQuery('.quantity-selection-input').val('');
        jQuery('.price-selection-select').val('');
        jQuery('.sku-selection-select').val('');
        jQuery('.quantity-selection-select').val('');
        jQuery(this).attr('checked', true);
    });

    var $_on_property_value = jQuery("input[name='on_variation']:checked").val();

});

function closemanagefeedblock() {
    jQuery(".manage-feed-block").hide();
}

function doSelectFeed() {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            feedType: jQuery('#selectFeedType').val(),
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdSelectFeed,
            security: ETCPF.ETCPF_nonce,

        },
        success: function (res) {
            googleParseSelectFeedChange(res)
        }
    });
}

/* Error Resolve Javascript code Started */
function show_advanced_attr(selector) {
    // console.log(selector);
    jQuery('#attributeMappings').toggle();
    jQuery('#adv-sec-block').toggle();
    jQuery('.dashicons').toggleClass('dashicons-arrow-down').toggleClass('dashicons-arrow-up');
    jQuery('.adv-section .inside').toggle();
    jQuery('.inside.advance-sec-block').toggle();
}

function showfield(identifiers) {
    // jQuery('#' + identifiers).addClass('currentdiv');
    jQuery('#' + identifiers).toggle();
    jQuery(".advance_command_class").not("#" + identifiers).hide();
    jQuery('tr#' + identifiers + ' textarea').focus();
}

function resolveProductattributes(attributes) {
    jQuery('#').show();
    jQuery('#')
}

function show_inner_table(value) {
    if (value == 'products') {
        jQuery('ol.inner-categories-table').show();
        jQuery('ol.inner-categories-table').prev().hide();
        jQuery('ol.inner-categories-table').next().hide();
    }

}

function select_all_productschk(selector, pointer) {
    jQuery("#ajax-loader-cat-import").show();
    var checked = jQuery(pointer).attr("checked");
    if (checked == 'checked') {
        jQuery(pointer).parent().parent().parent().siblings().find("th.checkbox-cell input:checkbox").attr("checked", true);
        jQuery("#table_" + selector + " tbody td:first-child").find("input[type=checkbox]").attr('checked', true);
        jQuery('#table_' + selector + '-count-span').html(jQuery("#table_" + selector + " tbody td:first-child").find("input[type=checkbox]").length + ' items selected total');
        /*document.getElementById("#resolvation_value_"+selector).scrollIntoView();*/
        var position = jQuery(jQuery('#resolvation_value_' + selector)).offset().top;
        jQuery("body, html").animate({
            scrollTop: position
        }, 1000, 'linear');
    } else {
        jQuery("#table_" + selector + " tbody td:first-child").find("input[type=checkbox]").removeAttr('checked');
        jQuery('#table_' + selector + '-count-span').html('');
        jQuery(pointer).parent().parent().parent().siblings().find("th.checkbox-cell input:checkbox").attr("checked", false);
    }
    jQuery("#ajax-loader-cat-import").hide();
}

function showproductresolutionfield($id) {
    jQuery(".product_attribute_table").removeClass('currentdiv');
    jQuery('#' + $id).addClass('currentdiv');
    jQuery('#' + $id).toggle();
    jQuery(".product_attribute_table").not(".currentdiv").hide();

}

function ResolesubmitbuttonCall($id) {
    /*var table = jQuery('#table_' + $id);*/
    AllproductTable = jQuery('table#error-free-product-table').DataTable();
    jQuery("#ajax-loader-cat-import").show();
    var feedid = jQuery('#feedidinput').val();
    var inputValue = jQuery('#resolvation_value_' + $id).val();
    var error_code = jQuery('#resolvation_value_' + $id).data('error_code');
    /* Validate before keeping records */
    var validate = validateValueAssigned(error_code, inputValue);
    if (validate == false) {
        jQuery("#ajax-loader-cat-import").hide();
        return false;
    }
    var tabledata = jQuery('#table_' + $id + ' > tbody');
    /*var $tablerowperpage = jQuery('#table_'+$id).find('tbody').find('tr');*/
    var product_id = [];
    var dtable = jQuery('table#table_' + $id).DataTable();
    tabledata.find('input[type=checkbox]:checked').each(function (i, data) {
        product_id.push(data.value);
    });
    var productcount = product_id.length;
    var previouslyresolvedproducts = jQuery('#resolved-products-rpc').html();
    var newpcount = parseInt(productcount) + parseInt(previouslyresolvedproducts);
    var initialerrorp = jQuery('#initial-error-product').html();
    var newremainingp = parseInt(initialerrorp) - parseInt(productcount);
    jQuery('#initial-error-product').html(newremainingp);
    if (parseInt(newremainingp) <= 0) {
        jQuery('#error-product-table').hide();
    }
    var totalP = jQuery('#total-product-count').html();
    if (parseInt(totalP) >= parseInt(newpcount)) {
        jQuery('#resolved-products-rpc').html(newpcount);
        jQuery('#initial-error-product').html(newremainingp);
    }
    jQuery("#ajax-loader-cat-import").show();
    if (product_id.constructor == Array) {
        jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'exportfeed_etsy',
                    feedpath: ETCPF.cmdResolveFeed,
                    security: ETCPF.ETCPF_nonce,
                    product_ids: product_id,
                    feed_id: feedid,
                    attribute_code: error_code,
                    attribute_value: inputValue
                },
                success: function (res) {
                    jQuery("#gif-message-span").html("Value successfully set. Now removing resolved products from errors");
                    jQuery("#ajax-loader-cat-import").hide();
                    tabledata.find('input[type=checkbox]:checked').each(function (i, data) {
                        dtable.row(jQuery(this).closest('tr'))
                            .remove()
                            .draw();
                        let pid = data.value;
                        shitTd = AllproductTable.cell('td#aetsmtd_' + pid);
                        var data = shitTd.data();
                        var tempHTml = jQuery(data).find('#li' + '_' + pid + '_' + error_code).siblings().html();
                        var parentHtml = "";
                        /*Make Html Dom Here*/
                        var checksiblings = jQuery(data).find('#li' + '_' + pid + '_' + error_code).siblings().length;
                        if (parseInt(checksiblings) > 0) {
                            console.log("Sibling exists");
                            /* jQuery.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                dataType: 'json',
                                data: {
                                    action: 'exportfeed_etsy',
                                    feedpath: ETCPF.cmdResolveProcess,
                                    security: ETCPF.ETCPF_nonce,
                                    pid: pid,
                                    error_code: error_code,
                                    value: inputValue,
                                    feedID: feedid,
                                    perform: 'getHtmlOfPArticularProduct'
                                },
                                success: function (res) {
                                    console.log(res.html);
                                    shitTd.data(res.html);
                                },
                                error: function () {

                                }

                            });*/
                        } else {
                            shitTd.data('<td style="color: green;">Resolved</td>');
                        }

                    });
                    var totalleftrows = dtable.rows().count();
                    if (totalleftrows == 0) {
                        location.reload();
                    } else {
                        jQuery('#table_' + $id).find("input[type=checkbox]").attr('checked', false);
                    }
                    /*var $html = res.html;
                    jQuery($html).each(function (i,value) {
                        var tabletrhtml = '<tr><td>'+value.p_name+'</td><td>'+value.sku+'</td><td>'+value.prod_categories+'</td><td style="color:green;">'+value.message+'</td></tr>';
                        jQuery('#error-resolved-product-table').find('tbody').append(tabletrhtml);
                    });*/
                    jQuery('#select_all_products_' + $id).attr('checked', false);
                    jQuery("#ajax-loader-cat-import").hide();
                    let html = res.remaining_errors + ' has same error. <a' +
                        'onclick="return SelectBulkResolutionofecode(' + error_code + ')"' +
                        'style="cursor: pointer;"' +
                        'class="' + error_code + '">Resolve in bulk</a>';
                    jQuery('.resolve-error-in-bluk-' + error_code).html(html);
                },
                error: function (res) {
                    jQuery("#ajax-loader-cat-import").hide();
                    console.log(res);
                }
            }
        );
    }

}

function AssignProductValueinWoo(selector, pid, ecode, feed_id) {
    AllproductTable = jQuery('table#error-free-product-table').DataTable();
    shitTd = AllproductTable.cell('td#aetsmtd_' + pid);
    var data = shitTd.data();
    var tempHTml = jQuery(data).find('#li' + '_' + pid + '_' + ecode).siblings().html();
    var parentHtml = "";
    /*Make Html Dom Here*/
    var checksiblings = jQuery(data).find('#li' + '_' + pid + '_' + ecode).siblings().length;
    assignedtable = jQuery('table#table_' + ecode).DataTable();
    var ProductVal = jQuery('#edit_product_' + pid + '_' + ecode + ' input').val();
    var validate = validateValueAssigned(ecode, ProductVal);
    if (validate == false) {
        return false;
    }
    jQuery("#ajax-loader-cat-import").show();
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdResolveProcess,
            security: ETCPF.ETCPF_nonce,
            pid: pid,
            error_code: ecode,
            value: ProductVal,
            feedID: feed_id,
            perform: 'AssignProductValueinWoo'
        },
        success: function (res) {
            if (res.status == true) {
                var shitTd = AllproductTable.cell('td#aetsmtd_' + pid);
                console.log(jQuery("#li_" + pid + '_' + ecode).siblings());
                if (res.contains_errors == false) {
                    if (res.all_product_resolved && res.all_product_resolved == true) {
                        location.reload();
                        return;
                    }
                    var resultingCount = parseInt(jQuery("#total-product-count").html()) - 1;
                    jQuery("#total-product-count").html(resultingCount);
                    /*==========================================================================
                     *AllproductTable.row(jQuery('allproduct_'+pid).closest('tr'))
                         .remove()
                         .draw();
                     ==========================================================================*/
                }
                console.log(checksiblings);
                if (parseInt(checksiblings) > 0) {
                    console.log("Sibling exists");
                    /*==================================================================================================
                     *jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        dataType: 'json',
                        data: {
                            action: 'exportfeed_etsy',
                            feedpath: ETCPF.cmdResolveProcess,
                            security: ETCPF.ETCPF_nonce,
                            pid: pid,
                            error_code: ecode,
                            value: ProductVal,
                            feedID: feed_id,
                            perform: 'getHtmlOfPArticularProduct'
                        },
                        success: function (res) {
                            console.log(res.html);
                            shitTd.data(res.html);
                        },
                        error: function () {

                        }
                }
            );
            ==========================================================================================================*/
                } else {
                    shitTd.data('<td style="color: green;">Resolved</td>');
                }
                jQuery("#ajax-loader-cat-import").hide();
                jQuery(selector).html("Error Solved").delay(200).fadeOut('slow', function () {
                    assignedtable.row(jQuery(selector).closest('tr'))
                        .remove()
                        .draw();
                    var $eCount = parseInt(jQuery("#initial-error-product").html()) - 1;
                    jQuery("#initial-error-product").html($eCount);
                });
                var totalleftrows = assignedtable.rows().count();
                if (parseInt(totalleftrows) == 0) {
                    location.reload();
                }
                return;
            }
            jQuery("#ajax-loader-cat-import").hide();
        },
        error: function (res) {
            console.log(res);
        }
    })
    ;
}

var validateValueAssigned = function (ecode, value) {
    switch (parseInt(ecode)) {
        case 5201:
            /* Check value */
            return true;
            break;

        case 5202:
            /* Check value */
            if (!isInt(value) && !isFloat(value)) {
                alert("Regular price must be either int or flat value.eg:10,20.45,5 etc.");
                return false;
            } else {
                return true;
            }
            break;

        case 5203:
            /* Check value */
            if (!isInt(value) && !isFloat(value)) {
                alert("Sale price must be either int or flat value.eg:10,20.45,5 etc.");
                return false;
            } else {
                return true;
            }
            break;

        case 5204:
            /*Check value*/
            if (!isInt(value)) {
                alert("Quantity must be integer value. eg: 10,20,30 etc.");
                return false;
            } else {
                return true;
            }

        default:
            return 'unchecked';            /*Return something in default*/
    }
};

function isInt(n) {
    return Number(n).toString() === n.toString() && n % 1 === 0;
}

function isFloat(n) {
    return Number(n).toString() === n.toString() && n % 1 !== 0;
}

function AMWSCP_AssignProductValueinWooFromAllProductTab(selector, pid, ecode, feed_id) {
    g = selector;
    AllproductTable = jQuery('table#error-free-product-table').DataTable();
    ErrorTable = jQuery('table#table_' + ecode).DataTable();
    var ProductVal = jQuery('#product_value_' + pid + '_' + ecode).val();
    jQuery("#ajax-loader-cat-import").show();
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdResolveProcess,
            security: ETCPF.ETCPF_nonce,
            pid: pid,
            feedID: feed_id,
            error_code: ecode,
            value: ProductVal,
            perform: 'AssignProductValueinWooFromAllProductTab'
        },
        success: function (res) {
            if (res.status == true) {
                ErrorTable.row('#row_' + pid + '_' + ecode).remove().draw();
                if (res.contains_errors == true) {
                    console.log("inside contains errors");
                    jQuery("#ajax-loader-cat-import").hide();
                    jQuery("#li_" + pid + '_' + ecode).html("Resolved").delay(200).fadeOut('slow', function () {
                        jQuery("#li_" + pid + '_' + ecode).remove();
                    });
                    var $eCount = parseInt(jQuery("#initial-error-product").html()) - 1;
                    jQuery("#initial-error-product").html($eCount);
                    jQuery("#allproduct_" + pid + " ul").children().first().children('div').show();
                } else {
                    jQuery("#ajax-loader-cat-import").hide();
                    jQuery(selector).closest('td').html("Resolved").delay(200, function () {
                        jQuery("#aetsmtd_" + pid).css({'color': 'green'});
                        var $eCount = parseInt(jQuery("#initial-error-product").html()) - 1;
                        jQuery("#initial-error-product").html($eCount);
                        if (res.all_product_resolved && res.all_product_resolved == true) {
                            location.reload();
                        }
                    });
                }
                jQuery("#ajax-loader-cat-import").hide();
                return;
            }
            jQuery("#ajax-loader-cat-import").hide();
        },
        error: function (res) {
            console.log('Error: ' + res);
        }
    });
}

function fetchResolveSection(feed_id, pid, target) {
    jQuery("#ajax-loader-cat-import").show();
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdResolveProcess,
            security: ETCPF.ETCPF_nonce,
            pid: pid,
            feedID: feed_id,
            perform: 'getdivHtmlOfPArticularProduct'
        },
        success: function (res) {
            console.log(res);
            jQuery('#allproduct_' + pid).html(res.html);
            jQuery(".error-resolve-div-pep").hide();
            jQuery("#allproduct_" + target).show();
            jQuery("#allproduct_" + target + " ul").children().first().children('div').show();
            jQuery("#ajax-loader-cat-import").hide();
            //return res;
        },
        error: function (res) {
            return res;
        }

    });
}

function SelectBulkResolutionofecode(code) {
    jQuery('.error-products').trigger('click');
    jQuery('#' + code).trigger('click');
    console.log(code);
}

function view_all(feed_id) {
    jQuery('.view-all').addClass('active');
    jQuery('.view-all').siblings('a').removeClass('active');
    jQuery('.main-table').hide();
    jQuery("#all-product-div").show();
    jQuery("#error-free-product-table_wrapper").show();
    jQuery('#error-free-product-table').show();
    // AllproductTable = jQuery('table#error-free-product-table').DataTable();
    // jQuery("#ajax-loader-cat-import").show();
    /*================================================================================================================
     *jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdResolveProcess,
            security: ETCPF.ETCPF_nonce,
            feedID:feed_id,
            perform: 'getAllProducts'
        },
        success: function (res) {
            if(res.success==true){
                if(res.data){
                    InnerAllproductHtml = '';
                    dataprepared = array();
                    jQuery.each(res.data,function (i,value) {
                        /!*InnerAllproductHtml += '<tr>' +
                            '<td><input type="checkbox"></td>'+
                            '<td>'+value.name+'</td>'+
                            '<td>'+value.sku+'</td>'+
                            '<td>'+value.prod_categories+'</td>'+
                            '<td id="aetsmtd_"'+value.p_id+'></td>'+
                        '</tr>';*!/
                        temp = {
                            "Product Name": value.parent_data.p_name ,
                            "SKU": value.parent_data.sku,
                            "Categories": value.parent_data.prod_categories,
                            "Action": ''
                        };


                        dataprepared.push("Product Name": value.p_name,)
                    });
                    AllproductTable.clear();
                    /!*AllproductTable.rows.add([
                        res.data
                    ]);*!/
                    /!*jQuery("#error-free-product-table tbody").html(InnerAllproductHtml);*!/
                    jQuery("#ajax-loader-cat-import").hide();
                }
            }
        }
    });
    ==================================================================================================================*/
}

function showResolvedProducts() {
    jQuery('.selected-products').addClass('active');
    jQuery('.selected-products').siblings('a').removeClass('active');
    jQuery('.main-table').hide();
    jQuery('#error-resolved-product-table').show();
}

function error_products() {
    jQuery("#error-free-product-table_wrapper").hide();
    jQuery('.main-table').hide();
    jQuery('.error-products').siblings('a').removeClass('active');
    jQuery('#error-product-table').show();
    jQuery('.error-products').addClass('active');
    var initialerrorp = jQuery('#initial-error-product').html();
    if (parseInt(initialerrorp) <= 0) {
        jQuery('#all-resolved-table').show();
        jQuery('#no-error-popup-modal').show();
    }
}

function etcpf_ClearDataFromDb(pointer, pid, error_code) {
    jQuery('#edit_product').slideDown('slow');
    event.preventDefault();
    alert("While editing in product, please make sure you review in variation product too. Thanks.");
    console.log(pointer.href);
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdResolveProcess,
            security: ETCPF.ETCPF_nonce,
            pid: pid,
            error_code: error_code,
            perform: 'ClearDataFromDb'
        },
        success: function (res) {

            console.log(res);
            if (res.success == true) {
                window.open(pointer.href, '_blank');
                location.reload();
            }

        },
        error: function (res) {
            console.log(res);
        }
    });
}

/* Custom Feed creation Process for Etsy */

var etcpf_GetcustomFeed = function (args) {
    let SelectedProducts = JSON.parse(sessionStorage.getItem('Customfeedselectedproducts'));

    if (!SelectedProducts.products) {
        jQuery('#feed-error-display').html("You have not selected any items");
    }
    let filename = jQuery('#feed_filename_custom').val();
    let categories = sessionStorage.getItem('Customfeedcategorymap');
    jQuery("#feed-message-display").html("Generating Feeds...");
    jQuery('#ajax-loader-cat-import').show();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdGetcustomfeed_etsy,
            security: ETCPF.ETCPF_nonce,
            perform: 'index',
            filename: filename,
            products: JSON.stringify(SelectedProducts.products),
            categories: categories,
        },
        success: function (res) {
            console.log(res);
            if (res.data.status && res.data.status == 'success') {
                jQuery("#feed-message-display").html("Feed Generated Successfully. Please go to <a href='" + window.location.protocol + "//" + window.location.hostname + "/wp-admin/admin.php?page=etsy-export-feed-manage" + "'>manage</a> feed and click upload to upload the generated feeds.");
                location.replace(res.data.pagelink);
            } else {
                let color;
                if (res.data.type) {
                    color = getColor(res.data.type);
                    if (color) {
                        jQuery("#feed-message-display").html(res.data.msg).css({'color': color});
                    } else {
                        jQuery("#feed-message-display").html(res.data.msg);
                    }
                } else {
                    jQuery("#feed-message-display").html(res.data.msg);
                }

            }
            jQuery('#ajax-loader-cat-import').hide();
        },
        error: function (res) {
            console.log("Error");
        }
    })
};

var getColor = function (type) {
    let color;
    switch (type) {
        case 'info':
            color = '#F9800F';
            break;

        case 'warning':
            color = 'warning';
            break;

        default:
            color = '#ffffff';
            break;
    }

    return color;
};

let feed_ids;
let ajaxRequest = null;
let selectedFeeds;
let updatedFeeds = 0;
let updateInterval;

function updateAllFeeds() {
    let etcpf_manage_table_body = jQuery("#etcpf_manage_table_originals");
    feed_ids = [];
    etcpf_manage_table_body.find('input:checkbox').each(function (i, data) {
        if (this.checked) {
            let checked_feed_id = jQuery(this).parent().parent().find('.cpf_hidden_feed_id').val();
            if (typeof checked_feed_id !== 'undefined')
                feed_ids.push(checked_feed_id);
        }
    });

    selectedFeeds = feed_ids.length;
    let textspan = jQuery('#gif-message-span');
    textspan.text("preparing update...");
    if (selectedFeeds > 2) {
        jQuery('#gif-message-span-for-more-than-one-feed')
            .text('You are updating ' + selectedFeeds + ' feeds. This may take some time, please do not navigate away from this page');
    } else {
        jQuery('#gif-message-span-for-more-than-one-feed').text("Refreshing changes, please wait....");
    }
    updateInterval = setInterval(function () {
        feedUpdateJsCron();
    }, 500)
}

function feedUpdateJsCron() {
    let textspan = jQuery('#gif-message-span');
    console.log(feed_ids.length)
    if (feed_ids.length > 0) {
        if (ajaxRequest == null) {
            updatedFeeds += 1;
            textspan.text("updating " + updatedFeeds + " of " + selectedFeeds);
            let currentId = feed_ids.pop();
            ajaxRequest = jQuery.ajax({
                type: "post",
                url: ajaxurl,
                data: {
                    action: 'exportfeed_etsy',
                    feedpath: ETCPF.cmdUpdateAllFeeds,
                    security: ETCPF.ETCPF_nonce,
                    feed_id: currentId
                },
                success: function (res) {
                    ajaxRequest = null;
                },
                error: function (err) {
                    ajaxRequest = null;
                }
            });
        }
    } else {
        updatedFeeds = 0;
        clearInterval(updateInterval);
        textspan.text("Update Completed").css({'color': 'green'});
        jQuery('#gif-message-span-for-more-than-one-feed').text("Refreshing changes, please wait....");
        setTimeout(function (e) {
            console.log(e);
            location.reload();
        }, 200);
    }
}

var etcpfglobalAjax = function (selector, payload, callback) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: payload,
        success: function (res) {
            if (res) {
                callback(null, res.data);
            } else {
                callback(null, null);
            }
        },
        error: function (res) {
            callback(res, null);
        }
    });
};

jQuery(document).on('click', '#target-signlevariation-submit', function (event) {
    event.preventDefault();
    jQuery("[name='attributes[]']").each(function (index, data) {
        console.log(jQuery(this).attr('data-position'))
    });
    let payload = {
        action: 'exportfeed_etsy',
        feedpath: ETCPF.cmdsinglevariationpreparation,
        security: ETCPF.ETCPF_nonce,
        data: jQuery("form").serialize(),
        perform: 'set',
    };
    etcpfglobalAjax(this, payload, function (error, data) {
        if (error) {
            console.log(error);
        } else {
            console.log(data);
        }
    });
});

var getCategoriesSelected = function () {
    return {'cat': {'clothing': 'hoodies'}};
};
/*Custom Feed creation Procerss Ends*/

jQuery(document).ready(function () {
    if (jQuery('div.wrap').find('div').hasClass('error-display-message')) {
        jQuery("<div id='etcpf_cboxOverlay' style='opacity: 0.9;cursor: pointer;visibility: visible;'></div>").insertBefore(".error-display-message");
    } else {
        return false;
    }

});
/*-----Error pop-up-----*/
jQuery(window).load(function () {
    jQuery("#myBtn").click(function () {
        jQuery('#no-error-popup-modal').show();
    });
    jQuery('.close').click(function () {
        jQuery('#no-error-popup-modal').hide();
    });
});

/* Error Resolved Javascript Ended */

// Etsy Orders

jQuery(document).ready(function (e) {
    jQuery(document).on('click','#etcpf_select_all_feed',function(){
        etcpf_check_all_feeds(this);
    })
});



