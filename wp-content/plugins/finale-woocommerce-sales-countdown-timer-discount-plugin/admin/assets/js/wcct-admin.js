var wcct_admin_change_content = null;

jQuery(function ($) {
    'use strict';

    if (jQuery("input#post_ID").length > 0) {

        var val;
        var postid = (jQuery("input#post_ID").val());
        var name = "wcct_cook_post_tab_open_" + postid + "";

        val = wcct_getCookie(name);

        if (val !== "") {
            if (jQuery('li[data-panel="' + val + '"] a').length > 0) {
                jQuery('li[data-panel="' + val + '"] a').trigger('click');
            }
        }
    }

});

jQuery(document).ready(function ($) {
    'use strict';
    /**
     * Set up the functionality for CMB2 conditionals.
     */
    window.WCCT_CMB2ConditionalsInit = function (changeContext, conditionContext) {
        var loopI, requiredElms, uniqueFormElms, formElms;

        if ('undefined' === typeof changeContext) {
            changeContext = 'body';
        }
        changeContext = $(changeContext);

        if ('undefined' === typeof conditionContext) {
            conditionContext = 'body';
        }
        conditionContext = $(conditionContext);
        window.wcct_admin_change_content = conditionContext;
        changeContext.on('change', 'input, textarea, select', function (evt) {
            var elm = $(this),
                fieldName = $(this).attr('name'),
                dependants,
                dependantsSeen = [],
                checkedValues,
                elmValue;

            var dependants = $('[data-wcct-conditional-id="' + fieldName + '"]', conditionContext);
            if (!elm.is(":visible")) {
                return;
            }

            // Only continue if we actually have dependants.
            if (dependants.length > 0) {

                // Figure out the value for the current element.
                if ('checkbox' === elm.attr('type')) {
                    checkedValues = $('[name="' + fieldName + '"]:checked').map(function () {
                        return this.value;
                    }).get();
                } else if ('radio' === elm.attr('type')) {
                    if ($('[name="' + fieldName + '"]').is(':checked')) {
                        elmValue = elm.val();
                    }
                } else {
                    elmValue = evt.currentTarget.value;
                }

                dependants.each(function (i, e) {
                    var loopIndex = 0,
                        current = $(e),
                        currentFieldName = current.attr('name'),
                        requiredValue = current.data('wcct-conditional-value'),
                        currentParent = current.parents('.cmb-row:first'),
                        shouldShow = false;


                    // Only check this dependant if we haven't done so before for this parent.
                    // We don't need to check ten times for one radio field with ten options,
                    // the conditionals are for the field, not the option.
                    if ('undefined' !== typeof currentFieldName && '' !== currentFieldName && $.inArray(currentFieldName, dependantsSeen) < 0) {
                        dependantsSeen.push = currentFieldName;

                        if ('checkbox' === elm.attr('type')) {
                            if ('undefined' === typeof requiredValue) {
                                shouldShow = (checkedValues.length > 0);
                            } else if ('off' === requiredValue) {
                                shouldShow = (0 === checkedValues.length);
                            } else if (checkedValues.length > 0) {
                                if ('string' === typeof requiredValue) {
                                    shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                                } else if (Array.isArray(requiredValue)) {
                                    for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                        if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                            shouldShow = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        } else if ('undefined' === typeof requiredValue) {
                            shouldShow = (elm.val() ? true : false);
                        } else {
                            if ('string' === typeof requiredValue) {
                                shouldShow = (elmValue === requiredValue);
                            }
                            if ('number' === typeof requiredValue) {
                                shouldShow = (elmValue == requiredValue);
                            } else if (Array.isArray(requiredValue)) {
                                shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                            }
                        }

                        // Handle any actions necessary.
                        currentParent.toggle(shouldShow);

                        window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);

                        if (current.data('conditional-required')) {
                            current.prop('required', shouldShow);
                        }

                        // If we're hiding the row, hide all dependants (and their dependants).
                        if (false === shouldShow) {
                            // CMB2ConditionalsRecursivelyHideDependants(currentFieldName, current, conditionContext);
                        }

                        // If we're showing the row, check if any dependants need to become visible.
                        else {
                            if (1 === current.length) {
                                current.trigger('change');
                            } else {
                                current.filter(':checked').trigger('change');
                            }
                        }
                    } else {
                        /** Handling for */
                        if (current.hasClass("dtheme-cmb2-tabs") || current.hasClass("cmb2-wcct_html")) {


                            if ('checkbox' === elm.attr('type')) {
                                if ('undefined' === typeof requiredValue) {
                                    shouldShow = (checkedValues.length > 0);
                                } else if ('off' === requiredValue) {
                                    shouldShow = (0 === checkedValues.length);
                                } else if (checkedValues.length > 0) {
                                    if ('string' === typeof requiredValue) {
                                        shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                                    } else if (Array.isArray(requiredValue)) {
                                        for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                            if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                                shouldShow = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else if ('undefined' === typeof requiredValue) {
                                shouldShow = (elm.val() ? true : false);
                            } else {
                                if ('string' === typeof requiredValue) {
                                    shouldShow = (elmValue === requiredValue);
                                }
                                if ('number' === typeof requiredValue) {
                                    shouldShow = (elmValue == requiredValue);
                                } else if (Array.isArray(requiredValue)) {
                                    shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                                }
                            }

                            currentParent.toggle(shouldShow);
                            window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);


                        } else if (current.hasClass("wcct_custom_wrapper_group") || current.hasClass("wcct_custom_wrapper_wysiwyg")) {
                            if ('checkbox' === elm.attr('type')) {
                                if ('undefined' === typeof requiredValue) {
                                    shouldShow = (checkedValues.length > 0);
                                } else if ('off' === requiredValue) {
                                    shouldShow = (0 === checkedValues.length);
                                } else if (checkedValues.length > 0) {
                                    if ('string' === typeof requiredValue) {
                                        shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                                    } else if (Array.isArray(requiredValue)) {
                                        for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                            if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                                shouldShow = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else if ('undefined' === typeof requiredValue) {
                                shouldShow = (elm.val() ? true : false);
                            } else {
                                if ('string' === typeof requiredValue) {
                                    shouldShow = (elmValue === requiredValue);
                                }
                                if ('number' === typeof requiredValue) {
                                    shouldShow = (elmValue == requiredValue);
                                } else if (Array.isArray(requiredValue)) {
                                    shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                                }
                            }

                            current.toggle(shouldShow);
                            window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);

                        }
                    }
                });
            }
        });

        window.wcct_admin_change_content.on("wcct_conditional_runs", function (e, current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue) {

            var loopIndex = 0;
            var checkedValues;
            var shouldShow = false;
            if (typeof current.attr('data-wcct-conditional-value') == "undefined") {
                return;
            }

            elm = $("[name='" + current.attr('data-wcct-conditional-id') + "']", changeContext).eq(0);

            if (!elm.is(":visible")) {

                return;
            }
            // Figure out the value for the current element.
            if ('checkbox' === elm.attr('type')) {
                checkedValues = $('[name="' + current.attr('data-wcct-conditional-id') + '"]:checked').map(function () {
                    return this.value;
                }).get();
            } else if ('radio' === elm.attr('type')) {
                elmValue = $('[name="' + current.attr('data-wcct-conditional-id') + '"]:checked').val();

            }

            requiredValue = current.data('wcct-conditional-value');

            // Only check this dependant if we haven't done so before for this parent.
            // We don't need to check ten times for one radio field with ten options,
            // the conditionals are for the field, not the option.
            if ('undefined' !== typeof currentFieldName && '' !== currentFieldName) {


                if ('checkbox' === elm.attr('type')) {
                    if ('undefined' === typeof requiredValue) {
                        shouldShow = (checkedValues.length > 0);
                    } else if ('off' === requiredValue) {
                        shouldShow = (0 === checkedValues.length);
                    } else if (checkedValues.length > 0) {
                        if ('string' === typeof requiredValue) {
                            shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                        } else if (Array.isArray(requiredValue)) {
                            for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                    shouldShow = true;
                                    break;
                                }
                            }
                        }
                    }
                } else if ('undefined' === typeof requiredValue) {
                    shouldShow = (elm.val() ? true : false);
                } else {

                    if ('string' === typeof requiredValue) {
                        shouldShow = (elmValue === requiredValue);
                    }
                    if ('number' === typeof requiredValue) {
                        shouldShow = (elmValue == requiredValue);
                    } else if (Array.isArray(requiredValue)) {

                        shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                    }
                }

                // Handle any actions necessary.
                currentParent.toggle(shouldShow);


                window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);

                if (current.data('conditional-required')) {
                    current.prop('required', shouldShow);
                }

                // If we're hiding the row, hide all dependants (and their dependants).
                if (false === shouldShow) {
                    // CMB2ConditionalsRecursivelyHideDependants(currentFieldName, current, conditionContext);
                }

                // If we're showing the row, check if any dependants need to become visible.
                else {
                    if (1 === current.length) {
                        current.trigger('change');
                    } else {
                        current.filter(':checked').trigger('change');
                    }
                }
            } else {


                if (current.hasClass("dtheme-cmb2-tabs") || current.hasClass("cmb2-wcct_html")) {


                    if ('checkbox' === elm.attr('type')) {
                        if ('undefined' === typeof requiredValue) {
                            shouldShow = (checkedValues.length > 0);
                        } else if ('off' === requiredValue) {
                            shouldShow = (0 === checkedValues.length);
                        } else if (checkedValues.length > 0) {
                            if ('string' === typeof requiredValue) {
                                shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                            } else if (Array.isArray(requiredValue)) {
                                for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                    if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                        shouldShow = true;
                                        break;
                                    }
                                }
                            }
                        }
                    } else if ('undefined' === typeof requiredValue) {
                        shouldShow = (elm.val() ? true : false);
                    } else {
                        if ('string' === typeof requiredValue) {
                            shouldShow = (elmValue === requiredValue);
                        }
                        if ('number' === typeof requiredValue) {
                            shouldShow = (elmValue == requiredValue);
                        } else if (Array.isArray(requiredValue)) {
                            shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                        }
                    }

                    currentParent.toggle(shouldShow);
                    window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);


                } else if (current.hasClass("wcct_custom_wrapper_group") || current.hasClass("wcct_custom_wrapper_wysiwyg")) {
                    if ('checkbox' === elm.attr('type')) {
                        if ('undefined' === typeof requiredValue) {
                            shouldShow = (checkedValues.length > 0);
                        } else if ('off' === requiredValue) {
                            shouldShow = (0 === checkedValues.length);
                        } else if (checkedValues.length > 0) {
                            if ('string' === typeof requiredValue) {
                                shouldShow = ($.inArray(requiredValue, checkedValues) > -1);
                            } else if (Array.isArray(requiredValue)) {
                                for (loopIndex = 0; loopIndex < requiredValue.length; loopIndex++) {
                                    if ($.inArray(requiredValue[loopIndex], checkedValues) > -1) {
                                        shouldShow = true;
                                        break;
                                    }
                                }
                            }
                        }
                    } else if ('undefined' === typeof requiredValue) {
                        shouldShow = (elm.val() ? true : false);
                    } else {
                        if ('string' === typeof requiredValue) {
                            shouldShow = (elmValue === requiredValue);
                        }
                        if ('number' === typeof requiredValue) {
                            shouldShow = (elmValue == requiredValue);
                        } else if (Array.isArray(requiredValue)) {
                            shouldShow = ($.inArray(elmValue, requiredValue) > -1);
                        }
                    }

                    current.toggle(shouldShow);
                    window.wcct_admin_change_content.trigger("wcct_internal_conditional_runs", [current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue]);

                }
            }
        });

        $('[data-wcct-conditional-id]', conditionContext).not(".wcct_custom_wrapper_group").parents('.cmb-row:first').hide({
            "complete": function () {
                $("body").trigger("wcct_w_trigger_conditional_on_load");

                uniqueFormElms = [];
                $(':input', changeContext).each(function (i, e) {
                    var elmName = $(e).attr('name');
                    if ('undefined' !== typeof elmName && '' !== elmName && -1 === $.inArray(elmName, uniqueFormElms)) {
                        uniqueFormElms.push(elmName);
                    }
                });

                for (loopI = 0; loopI < uniqueFormElms.length; loopI++) {
                    formElms = $('[name="' + uniqueFormElms[loopI] + '"]');
                    if (1 === formElms.length || !formElms.is(':checked')) {
                        formElms.trigger('change');
                    } else {
                        formElms.filter(':checked').trigger('change');
                    }
                }

            }
        });

        $('#wcct_campaign_settings .wcct-cmb-tab-nav').on('click', 'a', function (e) {

            var $li = $(this).parent(),
                panel = $li.data('panel');
            if (panel) {
                var d = new Date();
                d.setTime(d.getTime() + (10 * 60 * 1000));
                var expires = "expires=" + d.toUTCString();

                var postid = (jQuery("input#post_ID").val());
                document.cookie = "wcct_cook_post_tab_open_" + postid + "" + "=" + panel + ";" + expires + ";path=/";
            }
        });

        $(document).on('wcct_cmb2_options_tabs_activated', function (e, panel) {

            var uniqueFormElms = [];
            $(':input', ".cmb-tab-panel").each(function (i, e) {
                var elmName = $(e).attr('name');
                if ('undefined' !== typeof elmName && '' !== elmName && -1 === $.inArray(elmName, uniqueFormElms) && $(e).is(":visible")) {
                    uniqueFormElms.push(elmName);
                }
            });
            for (loopI = 0; loopI < uniqueFormElms.length; loopI++) {
                formElms = $('[name="' + uniqueFormElms[loopI] + '"]');
                if (1 === formElms.length || !formElms.is(':checked')) {
                    formElms.trigger('change');
                } else {
                    formElms.filter(':checked').trigger('change');
                }
            }
        });
        $(document).on('wcct_acc_toggled', function (e, elem) {
            var uniqueFormElms = [];
            $(':input', ".ui-tabs-panel").each(function (i, e) {
                var elmName = $(e).attr('name');
                if ('undefined' !== typeof elmName && '' !== elmName && -1 === $.inArray(elmName, uniqueFormElms) && $(e).is(":visible")) {
                    uniqueFormElms.push(elmName);
                }
            });
            for (loopI = 0; loopI < uniqueFormElms.length; loopI++) {
                formElms = $('[name="' + uniqueFormElms[loopI] + '"]');
                if (1 === formElms.length || !formElms.is(':checked')) {
                    formElms.trigger('change');
                } else {
                    formElms.filter(':checked').trigger('change');
                }
            }
        });


    }

    if (typeof pagenow !== "undefined" && "wcct_countdown" == pagenow) {
        WCCTCMB2ConditionalsInit('#post .cmb2-wrap.wcct_options_common', '#post .cmb2-wrap.wcct_options_common');
        WCCT_CMB2ConditionalsInit('#post .cmb2-wrap.wcct_options_common', '#post  .cmb2-wrap.wcct_options_common');
    }

    if ($('.wcct_global_option .wcct_options_page_left_wrap').length > 0) {
        $('.wcct_global_option .wcct_options_page_left_wrap').removeClass('dispnone');
    }

    $(window).load(function () {
        $("body").on("click", ".cmb2_wcct_acc_head", function () {
            if ($(this).hasClass("active")) {
                $(this).next(".cmb2_wcct_wrapper_ac_data").toggle(false);
                $(this).parents(".cmb2_wcct_wrapper_ac").removeClass('opened');
            } else {
                $(this).next(".cmb2_wcct_wrapper_ac_data").toggle(true);
                $(this).parents(".cmb2_wcct_wrapper_ac").addClass('opened');
            }
            $(this).toggleClass("active");
            $(document).trigger("wcct_acc_toggled", [this]);
        });

        if ($("select.wcct_icon_select").length > 0) {
            $("select.wcct_icon_select").each(function () {
                $(this).trigger("change");
            });
        }

        $("body").on("click", ".wcct_detect_checkbox_change input[type='checkbox']", function () {
            var $this = $(this);
            var $wrap = $(this).parents(".wcct_detect_checkbox_change");
            if ($wrap.hasClass("wcct_gif_location")) {
                $(".wcct_load_spin.wcct_load_tab_location").addClass("wcct_load_active");
                setTimeout(function () {
                    $(".wcct_load_spin.wcct_load_tab_location").removeClass("wcct_load_active");
                }, 2000);
            }
            if ($wrap.hasClass("wcct_gif_appearance")) {
                $(".wcct_load_spin.wcct_load_tab_appearance").addClass("wcct_load_active");
                setTimeout(function () {
                    $(".wcct_load_spin.wcct_load_tab_appearance").removeClass("wcct_load_active");
                }, 2000);
            }
        });
        $("body").on("click", ".wcct_detect_radio_change input[type='radio']", function () {
            var $this = $(this);
            var $wrap = $(this).parents(".wcct_detect_radio_change");
            if ($wrap.hasClass("wcct_gif_appearance")) {
                $(".wcct_load_spin.wcct_load_tab_appearance").addClass("wcct_load_active");
                setTimeout(function () {
                    $(".wcct_load_spin.wcct_load_tab_appearance").removeClass("wcct_load_active");
                }, 2000);
            }
        });
        $("body").on("click", ".wcct_thickbox", function () {
            var $this = $(this), screenW = $(window).width(), screenH = $(window).height(), modalW = 1000, modalH = 350;
            var $container_id = $(this).attr("data-id");
            var $thickbox_title = $(this).attr("data-title");

            if (screenW < 1000) {
                modalW = parseInt(screenW * 0.8);
            }
            if (screenH < 350) {
                modalH = parseInt(screenH * 0.8);
            }
            if ($("#" + $container_id).length > 0) {
                tb_show($thickbox_title, '#TB_inline?width=' + modalW + '&height=' + modalH + '&inlineId=' + $container_id, false);
                return false;
            }
        });


        /**
         * Timer style images visibility control by switching options
         */
        $("body").on("change", ".wcct_timer_select input[type='radio']", function () {

            var $this = $(this);

            if ($this.is(':checked') === false) {
                return;
            }
            var type = 'header';
            if ($this.attr('name') === '_wcct_appearance_sticky_footer_skin') {
                var type = 'footer';
            }
            $('.wcct_appearance_sticky_bar_img[data-type="' + type + '"] img').css("display", "none");

            $this.parents(".cmb-td").find('img[data-type="' + $this.val() + '"]').css("display", "block");
        });

        setTimeout(function () {
            $(".cmb2-id--wcct-wrap-tabs").remove();
        }, 2000);


    });

    /**
     * Campaign Type selection radio handling
     */
    $(".cmb-row.wcct_radio_btn").find("input[type='radio']").on("change", function () {
        var $this = $(this);

        if ($this.is(":checked") === false) {
            return;
        }
        $this.parents("ul").find("li.radio-active").removeClass("radio-active");
        $this.parent("li").addClass("radio-active");
    });

    if (window.wcct_admin_change_content) {
        window.wcct_admin_change_content.on("wcct_internal_conditional_runs", function (e, current, currentFieldName, requiredValue, currentParent, shouldShow, elm, elmValue) {
            if (currentFieldName == "_wcct_deal_custom_units") {
                if (shouldShow === true) {
                    var val = $('input[name="_wcct_deal_custom_mode"]:checked').val();
                    if (val == "basic") {
                        $(".cmb2-id--wcct-deal-custom-units").show();
                    } else {
                        $(".cmb2-id--wcct-deal-custom-units").hide();
                    }
                } else {
                    $(".cmb2-id--wcct-deal-custom-units").hide();
                }
            }
            if (current.hasClass('_wcct_deal_custom_advanced-wcct_rep_wrap')) {
                if (shouldShow === true) {
                    var val = $('input[name="_wcct_deal_custom_mode"]:checked').val();
                    if (val == "basic" || val == 'range') {
                        current.hide();
                    } else {
                        current.show();
                    }
                } else {
                    current.hide();
                }
            }

            if (currentFieldName == '_wcct_deal_range_from_custom_units' || currentFieldName == '_wcct_deal_range_to_custom_units') {

                if (shouldShow === true) {
                    var val = $('input[name="_wcct_deal_custom_mode"]:checked').val();

                    if (val == "range") {
                        jQuery('.cmb2-id--wcct-deal-range-from-custom-units').show();
                        jQuery('.cmb2-id--wcct-deal-range-to-custom-units').show();
                    } else {
                        jQuery('.cmb2-id--wcct-deal-range-from-custom-units').hide();
                        jQuery('.cmb2-id--wcct-deal-range-to-custom-units').hide();
                    }
                } else {
                    jQuery('.cmb2-id--wcct-deal-range-from-custom-units').hide();
                    jQuery('.cmb2-id--wcct-deal-range-to-custom-units').hide();
                }
            }

            if (current.attr('id') == '_wcct_deal_inventory_advanced_html') {

                if (shouldShow === true) {
                    var val = $('input[name="_wcct_deal_custom_mode"]:checked').val();

                    if (val == "tiered") {
                        $('.cmb2-id--wcct-deal-inventory-advanced-html').show();

                    } else {
                        $('.cmb2-id--wcct-deal-inventory-advanced-html').hide();
                    }
                } else {
                    $('.cmb2-id--wcct-deal-inventory-advanced-html').hide();
                }
            }
        });
    }

    $('input[name="_wcct_deal_custom_mode"]').on('change', function (e) {

        var val = $('input[name="_wcct_deal_custom_mode"]:checked').val();
        var parent = $('input[name="_wcct_deal_enable_goal"]:checked').val();
        var uts = $('input[name="_wcct_deal_units"]:checked').val();

        if (parent == '0') {
            $(".cmb2-id--wcct-deal-custom-units").hide();
            $(".cmb2-id--wcct-deal-custom-units").next("._wcct_deal_custom_advanced-wcct_rep_wrap").hide();
            $(".cmb2-id--wcct-deal-range-from-custom-units").hide();
            $('.cmb2-id--wcct-deal-inventory-advanced-html').hide();
            return;
        }
        if (uts === "same") {
            $(".cmb2-id--wcct-deal-custom-units").hide();
            $(".cmb2-id--wcct-deal-custom-units").next("._wcct_deal_custom_advanced-wcct_rep_wrap").hide();
            $(".cmb2-id--wcct-deal-range-from-custom-units").hide();
            $('.cmb2-id--wcct-deal-inventory-advanced-html').hide();
            return;
        }
        if (val == "basic") {
            $(".cmb2-id--wcct-deal-custom-units").show();
            $(".cmb2-id--wcct-deal-custom-units").next("._wcct_deal_custom_advanced-wcct_rep_wrap").hide();
            $(".cmb2-id--wcct-deal-range-from-custom-units").hide();
            $(".cmb2-id--wcct-deal-range-to-custom-units").hide();
            $('.cmb2-id--wcct-deal-inventory-advanced-html').hide();
        } else if (val == "tiered") {
            $(".cmb2-id--wcct-deal-custom-units").hide();
            $(".cmb2-id--wcct-deal-custom-units").next("._wcct_deal_custom_advanced-wcct_rep_wrap").show();
            $(".cmb2-id--wcct-deal-range-from-custom-units").hide();
            $(".cmb2-id--wcct-deal-range-to-custom-units").hide();
            $('.cmb2-id--wcct-deal-inventory-advanced-html').show();
        } else if (val == "range") {
            $(".cmb2-id--wcct-deal-custom-units").hide();
            $(".cmb2-id--wcct-deal-custom-units").next("._wcct_deal_custom_advanced-wcct_rep_wrap").hide();
            $(".cmb2-id--wcct-deal-range-from-custom-units").show();
            $(".cmb2-id--wcct-deal-range-to-custom-units").show();
            $('.cmb2-id--wcct-deal-inventory-advanced-html').hide();
        } else {
            $(".cmb2-id--wcct-deal-custom-units").hide();
            $(".cmb2-id--wcct-deal-custom-units").next("._wcct_deal_custom_advanced-wcct_rep_wrap").hide();
            $(".cmb2-id--wcct-deal-range-from-custom-units").hide();
            $(".cmb2-id--wcct-deal-range-to-custom-units").hide();
            $('.cmb2-id--wcct-deal-inventory-advanced-html').hide();
        }
    });


    /** FUNCTIONS DECLARATION STARTS **/
    /**
     * Function to return font value
     * @param $icon_num
     * @returns {*}
     */
    function wcct_return_font_val($icon_num) {
        if ($icon_num.length === 3) {
            return $icon_num;
        } else if ($icon_num.length === 2) {
            return '0' + $icon_num;
        } else if ($icon_num.length === 1) {
            return '00' + $icon_num;
        } else {
            return '001';
        }
    }

    $("body").on("change", ".wcct_select_change select", function () {
        var $this = $(this);
        var groupParent = $this.parents(".postbox.cmb-row");
        var changeType = $this.attr("data-change")
        var actionElem = ".wcct_option_" + changeType;
        var finalHtml = '';
        if (changeType == "event_value") {
            // finalHtml = " <=";
            // if ($this.val() == "units_sold") {
            //     finalHtml = " >=";
            // }
        } else if (changeType == "entity") {
            if ($this.val() == "+") {
                finalHtml = "Adjust";
            } else if ($this.val() == "-") {
                finalHtml = "Adjust";
            } else if ($this.val() == "=") {
                finalHtml = "Assign";
            }
        }
        groupParent.find(actionElem).html(finalHtml);
    });


    $(window).on('load', function (e) {
        if ($(".wcct_countdown_timer_admin").length > 0) {
            $(".wcct_countdown_timer_admin").each(function () {
                var modifiedDate = new Date().getTime() + parseInt($(this).attr("data-timer")) * 1000;
                $(this).wcctCountdown(modifiedDate, {elapse: true}).on('update.countdown', function (event) {
                    $(this).html(event.strftime('%H hrs %M mins %S secs'));
                });
            });
        }
        if ($(".wcct_is_admin_timer").length > 0) {
            $(".wcct_is_admin_timer").each(function () {
                var modifiedDate = new Date().getTime() + parseInt($(this).find('.wcct_timer_wrap').attr("data-left")) * 1000;
                $(this).wcctCountdown(modifiedDate, {elapse: true}).on('update.countdown', function (event) {
                    if (event.offset.totalDays > 0) {
                        $(this).html(event.strftime('%D days %H hrs %M mins %S secs'));
                    } else {
                        $(this).html(event.strftime('%H hrs %M mins %S secs'));
                    }
                });
            });
        }
    });

    $(window).on('load', function (e) {
        if ($(".wcct_cmb2_chosen select").length > 0) {
            $(".wcct_cmb2_chosen select").each(function () {
                var $this = $(this);
                var jsAction = (typeof $this.attr('data_cpt_cb') !== 'undefined') ? $this.attr('data_cpt_cb') : '';
                if (jsAction == '') {
                    jsAction = 'get_coupons_cmb2';
                }
                $this.xlAjaxChosen({
                    type: 'POST',
                    minTermLength: 3,
                    afterTypeDelay: 500,
                    data: {
                        'action': jsAction
                    },
                    url: ajaxurl,
                    dataType: 'json'
                }, function (data) {
                    var results = [];
                    $.each(data, function (i, val) {
                        results.push({value: val.value, text: val.text});
                    });
                    return results;
                }).change(function () {
                    var $this = $(this);
                    var descElem = $this.parents(".cmb-td").children(".cmb2-metabox-description");
                    if (descElem.length > 0 && $this.parents(".cmb-row").hasClass('wcct-coupon-msg')) {
                        var descElemVal = 'Don\'t forget to check this coupon\'s <a href="{coupon_link}" target="_blank">usage restrictions</a>. Finale applies these coupons during the campaign, it does not restrict coupons based on campaign rules. This responsibility lies with native coupon settings.';

                        if ($this.val()) {
                            var newVal = parseInt($this.val());
                            if (newVal > 0) {
                                descElemVal = descElemVal.replace('{coupon_link}', WCCTParams.admin_url + '?post=' + newVal + '&action=edit#usage_restriction_coupon_data');
                                descElem.html(descElemVal);
                                descElem.css("display", "block");
                            } else {
                                descElemVal = descElemVal.replace('{coupon_link}', 'javascript:void(0)');
                                descElem.html(descElemVal);
                                descElem.css("display", "none");
                            }
                        } else {
                            descElemVal = descElemVal.replace('{coupon_link}', 'javascript:void(0)');
                            descElem.html(descElemVal);
                            descElem.css("display", "none");
                        }
                    }
                });
            });
        }
    });

    if ($("#wcct_campaign_quick_view_settings").length > 0) {
        $.post(ajaxurl, {'ID': $("input[name='post_ID']").val(), 'action': 'wcct_quick_view_html'}, function (res) {
            $("#_wcct_qv_html").html(res);
        });
    }

});

function wcct_show_tb(title, id) {
    wcct_modal_show(title, "#WCCT_MB_inline?height=500&amp;width=1000&amp;inlineId=" + id + "");
}

function wcct_manage_radio_active($) {

    if ($(".cmb-row.wcct_radio_btn").length > 0) {
        $(".cmb-row.wcct_radio_btn").each(function () {
            var $this = $(this);
            $this.find("li.radio-active").removeClass("radio-active");

            $this.find("input[type='radio']:checked").parent("li").addClass("radio-active");
        });
    }
}

function wcct_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}


(function ($) {
    wcct_manage_radio_active($);
})(jQuery);
