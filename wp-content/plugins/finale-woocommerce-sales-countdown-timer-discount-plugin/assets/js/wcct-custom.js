var wcct_timeOut = false;
var wcct_hold_header = false;
var wcct_hold_footer = false;
var wcctRefresh_timers_count = 0;
var wcctCurrent_received_timers = 0;
var wcctAllUniqueTimers = [];
(function ($) {
    'use strict';
    var wcctArrBar = [];

    $(".variations_form").on("woocommerce_variation_select_change", function () {
        // Fires whenever variation's select is changed
    });
    $(".variations_form").on("show_variation", function (event, variation) {
        // Fired when the user selects all the required dropdowns/ attributes and a final variation is selected/ shown
    });

    $(document).ready(function () {
        wcct_reset_all_timer_data();

        wcct_counter_bar();
        wcct_sticky_bar_init();
        wcct_sticky_bar_close();

        wcct_populate_header_info();
    });


    function wcct_reset_all_timer_data() {

        if (!wcct_data.hasOwnProperty('refresh_timings')) {
            return;
        }

        if (wcct_data.refresh_timings === "no") {
            return;
        }

        $(".wcct_countdown_timer .wcct_timer_wrap").each(function () {
            var currentEleme = this;
            var campID = $(this).parents(".wcct_countdown_timer").attr('data-campaign-id');

            if (wcctAllUniqueTimers.indexOf(campID) > -1) {
                return;
            }

            wcctAllUniqueTimers.push(campID);
            wcct_expiry_timer_init();
            $.ajax({
                url: wcct_data.admin_ajax,
                type: "GET",
                dataType: 'json',
                data: {
                    'wcct_action': 'wcct_refreshed_times',
                    'location': document.location.href,
                    'endDate': $(this).attr('data-date'),
                    'campID': $(this).parents(".wcct_countdown_timer").attr('data-campaign-id')
                },
                beforeSend: function () {
                    if ($(currentEleme).parents(".wcct_countdown_timer").attr('data-type') === "sticky_header") {
                        wcct_hold_header = true;
                    }
                    if ($(currentEleme).parents(".wcct_countdown_timer").attr('data-type') === "sticky_footer") {
                        wcct_hold_footer = true;
                    }
                    wcctRefresh_timers_count++;
                },
                success: function (result) {
                    wcctCurrent_received_timers++;

                    $(".wcct_countdown_timer[data-campaign-id='" + result.id + "']").each(function () {
                        var $timerElem = jQuery(this);
                        var curDataLeft = $timerElem.children(".wcct_timer_wrap").attr("data-left");
                        if (result.diff === 0) {
                            switch (jQuery(this).attr('data-type')) {
                                case "sticky_header":
                                    if (jQuery(this).hasClass('loaded')) {
                                        var stickyHonly = jQuery(this).outerHeight();
                                        jQuery(this).animate({
                                            'top': -(stickyHonly),
                                        }, 700);
                                        $("body").animate({
                                            'margin-top': 0,
                                        }, 700);
                                    }
                                    break;
                                case "sticky_footer":
                                    if (jQuery(this).hasClass('loaded')) {
                                        var stickyFonly = jQuery(this).outerHeight();
                                        jQuery(this).animate({
                                            'bottom': -stickyFonly,
                                        }, 700);
                                        $("body").animate({
                                            'margin-bottom': 0,
                                        }, 700);
                                    }
                                    break;
                                case "cart_table":
                                    break;
                                case "info_notice":
                                    jQuery(this).parents(".woocommerce-info").fadeOut().remove();
                                    break;
                                case "custom_text":
                                    jQuery(this).parents(".wcct_custom_text").eq(0).fadeOut().remove();
                                    break;
                                case "counter_bar":
                                    jQuery(this).parents(".wcct_counter_bar").eq(0).fadeOut().remove();
                                    break;
                                case "single":
                                    jQuery(this).eq(0).fadeOut().remove();
                                    break;
                            }
                        } else {
                            if ($timerElem.attr('data-type') === "sticky_header" && wcct_hold_header === true) {
                                wcct_hold_header = false;
                                if (!$timerElem.hasClass('loaded')) {
                                    wcct_sticky_bar_init();
                                }
                            }
                            if ($timerElem.attr('data-type') === "sticky_footer") {
                                wcct_hold_footer = false;
                                if (!$timerElem.hasClass('loaded')) {
                                    wcct_sticky_bar_init();
                                }
                            }

                            var campDelay = $timerElem.attr('data-delay');
                            if (typeof campDelay != 'undefined' && result.diff > parseInt(campDelay)) {
                                $timerElem.remove();
                            } else {
                                //$timerElem.css("display", "inline-block");
                            }
                            if ((parseInt(curDataLeft) - parseInt(result.diff)) > 10) {
                                $timerElem.removeAttr("data-wctimer-load");
                                $timerElem.children(".wcct_timer_wrap").attr("data-left", result.diff);
                            }
                        }
                    });

                    if (wcctRefresh_timers_count === wcctCurrent_received_timers) {
                        wcct_expiry_timer_init();
                    }
                }
            });
        });
    }

    $(window).scroll(function () {
        wcct_counter_bar();
    });

    $(document).ajaxComplete(function () {
        wcct_expiry_timer_init();
    });
    $(document).bind("wc_fragments_refreshed", function () {
        wcct_expiry_timer_init();
    });

    function wcct_populate_header_info() {
        if ($("#wp-admin-bar-wcct_admin_page_node-default").length > 0) {
            $("#wp-admin-bar-wcct_admin_page_node-default").html($(".wcct_header_passed").html());
        }
    }

    function wcct_expiry_timer_init(aH) {
        if (aH === undefined) {
            aH = true;
        }

        if ($(".wcct_timer").length > 0) {
            $(".wcct_timer").each(function () {
                var $this = $(this);

                // checking data-wctimer-load attr
                var dAl = $this.attr("data-wctimer-load");
                if ('yes' === dAl) {
                    return true;
                }

                var childSpan = $this.find(".wcct_timer_wrap");
                var toTimestamp = parseInt(childSpan.attr("data-date"));
                var displayFormat, valSecs, valMins, valHrs, classMins, classHrs, classDays, classSecWrap, classMinsWrap, classHrsWrap, classDaysWrap;

                var timerSkin = childSpan.attr("data-timer-skin");
                var label_day = $(this).attr("data-days") != "" ? $(this).attr("data-days") : 'day';
                var label_hrs = $(this).attr("data-hrs") != "" ? $(this).attr("data-hrs") : 'hr';
                var label_min = $(this).attr("data-mins") != "" ? $(this).attr("data-mins") : 'min';
                var label_sec = $(this).attr("data-secs") != "" ? $(this).attr("data-secs") : 'sec';
                var is_show_days = $(this).attr("data-is-days") != "" ? $(this).attr("data-is-days") : 'yes';
                var is_show_hrs = $(this).attr("data-is-hrs") != "" ? $(this).attr("data-is-hrs") : 'yes';

                var modifiedDate = new Date().getTime() + parseInt(childSpan.attr("data-left")) * 1000;

                childSpan.wcctCountdown(modifiedDate, {elapse: true}).on('update.countdown', function (event) {

                    valSecs = event.offset.seconds;
                    valMins = event.offset.minutes;
                    valHrs = event.offset.hours;
                    classMins = classHrs = classDays = classSecWrap = classMinsWrap = classHrsWrap = classDaysWrap = '';
                    if (valSecs == '0') {
                        classMins = ' wcct_pulse wcct_animated';
                        classMinsWrap = ' wcct_border_none';
                    }
                    if (valSecs == '0' && classMins == '0') {
                        classHrs = ' wcct_pulse wcct_animated';
                        classHrsWrap = ' wcct_border_none';
                    }
                    if (valSecs == '0' && classMins == '0' && classHrs == '0') {
                        classDays = ' wcct_pulse wcct_animated';
                        classDaysWrap = ' wcct_border_none';
                    }
                    displayFormat = '';
                    if (event.elapsed && aH == true) {
                        var headerParent = $this.parents('.wcct_header_area');
                        if (headerParent.length > 0) {
                            headerParent.find(".wcct_close").trigger("click");
                            setTimeout(function () {
                                headerParent.remove();
                            }, 1000);
                        }
                        var footerParent = $this.parents('.wcct_footer_area');
                        if (footerParent.length > 0) {
                            footerParent.find(".wcct_close").trigger("click");
                            setTimeout(function () {
                                footerParent.remove();
                            }, 1000);
                        }

                        setTimeout(function () {
                            $this.remove();
                        }, 1000);

                        /**
                         * Making sure we only register reload event only once per load so that there would be no chance for further reload.
                         */
                        if (wcct_timeOut === false) {
                            $.ajax({
                                url: wcct_data.admin_ajax,
                                type: "POST",
                                dataType: 'json',
                                data: {
                                    'action': 'wcct_clear_cache',
                                },
                                success: function (result) {
                                    //
                                },
                                timeout: 10
                            });
                            if ('yes' === wcct_data.reload_page_on_timer_ends) {
                                var timeOut = setTimeout(function () {
                                    window.location.reload();
                                }, 2000);
                            }
                        }

                    } else {
                        var WDays = '%D';
                        var WHrs = '%H';
                        var WMins = '%M';
                        var WSecs = '%S';

                        if (aH == false) {
                            WDays = '00';
                            WHrs = '00';
                            WMins = '00';
                            WSecs = '00';
                        }

                        if ('round_fill' === timerSkin) {
                            if (event.offset.totalDays > 0 || 'yes' === is_show_days) {
                                displayFormat = '<div class="wcct_round_wrap ' + classDaysWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WDays + '</span> ' + label_day + '</div></div><div class="wcct_wrap_border ' + classDays + '"></div></div>';
                            }
                            if (event.offset.totalHours > 0 || 'yes' === is_show_hrs) {
                                displayFormat += '<div class="wcct_round_wrap ' + classHrsWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WHrs + '</span> ' + label_hrs + '</div></div><div class="wcct_wrap_border ' + classHrs + '"></div></div>';
                            }
                            displayFormat += '<div class="wcct_round_wrap ' + classMinsWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WMins + '</span> ' + label_min + '</div></div><div class="wcct_wrap_border ' + classMins + '"></div></div>' + '<div class="wcct_round_wrap wcct_border_none"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WSecs + '</span> ' + label_sec + '</div></div><div class="wcct_wrap_border wcct_pulse wcct_animated"></div></div>';
                        } else if ('round_ghost' === timerSkin) {
                            if (event.offset.totalDays > 0 || 'yes' === is_show_days) {
                                displayFormat = '<div class="wcct_round_wrap ' + classDaysWrap + '"><div class="wcct_wrap_border ' + classDays + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WDays + '</span> ' + label_day + '</div></div></div>';
                            }
                            if (event.offset.totalHours > 0 || 'yes' === is_show_hrs) {
                                displayFormat += '<div class="wcct_round_wrap ' + classHrsWrap + '"><div class="wcct_wrap_border ' + classHrs + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WHrs + '</span> ' + label_hrs + '</div></div></div>';
                            }
                            displayFormat += '<div class="wcct_round_wrap ' + classMinsWrap + '"><div class="wcct_wrap_border ' + classMins + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WMins + '</span> ' + label_min + '</div></div></div>' + '<div class="wcct_round_wrap wcct_border_none"><div class="wcct_wrap_border wcct_pulse wcct_animated"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WSecs + '</span> ' + label_sec + '</div></div></div>';
                        } else if ('square_fill' === timerSkin) {
                            if (event.offset.totalDays > 0 || 'yes' === is_show_days) {
                                displayFormat = '<div class="wcct_square_wrap ' + classDaysWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WDays + '</span> ' + label_day + '</div></div><div class="wcct_wrap_border ' + classDays + '"></div></div>';
                            }
                            if (event.offset.totalHours > 0 || 'yes' === is_show_hrs) {
                                displayFormat += '<div class="wcct_square_wrap ' + classHrsWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WHrs + '</span> ' + label_hrs + '</div></div><div class="wcct_wrap_border ' + classHrs + '"></div></div>';
                            }
                            displayFormat += '<div class="wcct_square_wrap ' + classMinsWrap + '"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WMins + '</span> ' + label_min + '</div></div><div class="wcct_wrap_border ' + classMins + '"></div></div>' + '<div class="wcct_square_wrap wcct_border_none"><div class="wcct_table"><div class="wcct_table_cell"><span>' + WSecs + '</span> ' + label_sec + '</div></div><div class="wcct_wrap_border wcct_pulse wcct_animated"></div></div>';
                        } else if ('square_ghost' === timerSkin) {
                            if (event.offset.totalDays > 0 || 'yes' === is_show_days) {
                                displayFormat = '<div class="wcct_square_wrap ' + classDaysWrap + '"><div class="wcct_wrap_border ' + classDays + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WDays + '</span> ' + label_day + '</div></div></div>';
                            }
                            if (event.offset.totalHours > 0 || 'yes' === is_show_hrs) {
                                displayFormat += '<div class="wcct_square_wrap ' + classHrsWrap + '"><div class="wcct_wrap_border ' + classHrs + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WHrs + '</span> ' + label_hrs + '</div></div></div>';
                            }
                            displayFormat += '<div class="wcct_square_wrap ' + classMinsWrap + '"><div class="wcct_wrap_border ' + classMins + '"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WMins + '</span> ' + label_min + '</div></div></div>' + '<div class="wcct_square_wrap wcct_border_none"><div class="wcct_wrap_border wcct_pulse wcct_animated"></div><div class="wcct_table"><div class="wcct_table_cell"><span>' + WSecs + '</span> ' + label_sec + '</div></div></div>';
                        } else if ('highlight_1' === timerSkin) {
                            if (event.offset.totalDays > 0 || 'yes' === is_show_days) {
                                displayFormat = '<div class="wcct_highlight_1_wrap"><span class="wcct_timer_label">' + WDays + '</span> ' + label_day + '<span class="wcct_colon_sep">:</span></div>';
                            }
                            if (event.offset.totalHours > 0 || 'yes' === is_show_hrs) {
                                displayFormat += '<div class="wcct_highlight_1_wrap"><span class="wcct_timer_label">' + WHrs + '</span> ' + label_hrs + '<span class="wcct_colon_sep">:</span></div>';
                            }
                            displayFormat += '<div class="wcct_highlight_1_wrap"><span class="wcct_timer_label">' + WMins + '</span> ' + label_min + '<span class="wcct_colon_sep">:</span></div>' + '<div class="wcct_highlight_1_wrap"><span class="wcct_timer_label">' + WSecs + '</span> ' + label_sec + '</div>';
                        } else {
                            if (event.offset.totalDays > 0 || 'yes' === is_show_days) {
                                displayFormat = WDays + '<span class="wcct_timer_label_default">' + label_day + '</span>';
                            }
                            if (event.offset.totalHours > 0 || 'yes' === is_show_hrs) {
                                displayFormat += ' ' + WHrs + '<span class="wcct_timer_label_default">' + label_hrs + '</span>';
                            }
                            displayFormat += ' ' + WMins + '<span class="wcct_timer_label_default">' + label_min + '</span>' + ' ' + WSecs + '<span class="wcct_timer_label_default">' + label_sec + '</span>';
                        }
                        $(this).html(event.strftime(displayFormat));
                    }

                });
                $this.attr("data-wctimer-load", "yes");
            });
        }
    }

    function wcct_counter_bar() {
        if ($('.wcct_counter_bar').length > 0) {
            $(".wcct_counter_bar").each(function () {
                var elem = $(this);
                elem.css("display", "inline-block");
                if (elem.find(".wcct_progress_aria").length > 0) {
                    var $this = elem.find(".wcct_progress_aria");
                    if ($this.visible(true)) {
                        if (!$this.hasClass("wcct_bar_active")) {
                            $this.addClass("wcct_bar_active");
                            var $ProgressBarVal = $this.find('.wcct_progress_bar').attr('aria-valuenow');
                            setTimeout(function () {
                                $this.find('.wcct_progress_bar').css('width', $ProgressBarVal + '%');
                            }, 200);
                        }
                    }
                }
            });
        }
    }

    function wcct_sticky_bar_init() {
        var stickyHeaderH, stickyFooterH, stickyHeaderD = 1000, stickyFooterD = 1000, adminBarH = 0, instanceIDVal, cookieName, cookieVal;
        if ($("#wpadminbar").length > 0) {
            adminBarH = $("#wpadminbar").outerHeight();
        }
        if ($(".wcct_header_area").length > 0) {
            if (wcct_hold_header === true) {
                return;
            }
            // reading cookie if set
            instanceIDVal = $(".wcct_header_area").eq(0).attr("data-id");
            cookieName = 'wcct_sticky_header_' + instanceIDVal;
            cookieVal = wcct_get_cookie(cookieName);
            if (cookieVal == '1') {
                return;
            }
            if ($(".wcct_header_area").eq(0).attr('data-delay') !== '') {
                stickyHeaderD = parseInt($(".wcct_header_area").eq(0).attr('data-delay')) * 1000;
            }
            $(".wcct_header_area").eq(0).css({"visibility": "hidden", "display": "block"});
            setTimeout(function () {
                stickyHeaderH = $(".wcct_header_area").eq(0).outerHeight();
                if (stickyHeaderH > 0) {
                    $(".wcct_header_area").eq(0).css({"top": "-" + (stickyHeaderH - adminBarH) + "px"});
                    setTimeout(function () {
                        $(".wcct_header_area").eq(0).css({"visibility": "visible"});

                        $(".wcct_header_area").eq(0).animate({
                            'top': (0 + adminBarH)
                        }, {
                            'complete': function () {
                                $(this).addClass("loaded");
                            },
                            'duration': 700
                        });
                        $("body").animate({
                            'margin-top': stickyHeaderH,
                        }, 700);
                    }, stickyHeaderD);
                }
            }, 500);
        }
        if ($(".wcct_footer_area").length > 0) {
            if ($(".wcct_footer_area").eq(0).attr('data-delay') !== '') {
                stickyFooterD = parseInt($(".wcct_footer_area").eq(0).attr('data-delay')) * 1000;
            }
            if (wcct_hold_footer === true) {
                return;
            }
            // reading cookie if set
            instanceIDVal = $(".wcct_footer_area").eq(0).attr("data-id");
            cookieName = 'wcct_sticky_footer_' + instanceIDVal;
            cookieVal = wcct_get_cookie(cookieName);
            if (cookieVal == '1') {
                return;
            }
            $(".wcct_footer_area").eq(0).css({"visibility": "hidden", "display": "block"});
            setTimeout(function () {
                stickyFooterH = $(".wcct_footer_area").eq(0).outerHeight();
                if (stickyFooterH > 0) {
                    $(".wcct_footer_area").eq(0).css({"bottom": "-" + (stickyFooterH) + "px"});
                    setTimeout(function () {
                        $(".wcct_footer_area").eq(0).css({"visibility": "visible"});
                        $(".wcct_footer_area").eq(0).animate({
                            'bottom': 0,
                        }, {
                            'complete': function () {
                                $(this).addClass("loaded");
                            },
                            'duration': 700
                        });
                        $("body").animate({
                            'margin-bottom': stickyFooterH,
                        }, 700);
                    }, stickyFooterD);
                }
            }, 500);
        }
    }

    function wcct_sticky_bar_close() {
        $("body").on("click", ".wcct_close", function () {
            var $this = $(this), stickyH, adminBarH = 0;
            if ($("#wpadminbar").length > 0) {
                adminBarH = $("#wpadminbar").outerHeight();
            }
            var closeRef = $this.attr("data-ref");
            var expireTime = $this.attr("data-expire");
            if ($this.parents(".wcct_" + closeRef + "_area").length > 0) {
                var parentStickyBar = $this.parents(".wcct_" + closeRef + "_area");
                stickyH = parentStickyBar.outerHeight();
                if (closeRef == 'header') {
                    parentStickyBar.animate({
                        'top': -(stickyH - adminBarH),
                    }, 700);
                    $("body").animate({
                        'margin-top': 0,
                    }, 700);
                } else if (closeRef === 'footer') {
                    parentStickyBar.animate({
                        'bottom': -stickyH,
                    }, 700);
                    $("body").animate({
                        'margin-bottom': 0,
                    }, 700);
                }
                wcct_sticky_set_cookie(parentStickyBar, expireTime);
            }
        });
    }

    function wcct_sticky_set_cookie($this, expireTime) {
        var instanceIDVal = $this.attr("data-id");
        var typeVal = $this.find(".wcct_close").attr("data-ref");
        var cookie_name = 'wcct_sticky_' + typeVal + '_' + instanceIDVal;
        var cookie_val = 1;
        wcct_set_cookie(cookie_name, cookie_val, expireTime);
    }

    function wcct_timestamp_converter(UNIX_timestamp) {
        var newDate = new Date(UNIX_timestamp * 1000);
        var year = newDate.getFullYear();
        var month = newDate.getMonth();
        var date = newDate.getDate();
        var hour = newDate.getHours();
        var min = "0" + newDate.getMinutes();
        var sec = "0" + newDate.getSeconds();
        var time = year + "/" + (month + 1) + "/" + date + " " + hour + ":" + min.substr(-2) + ":" + sec.substr(-2);
        return time;
    }

    function wcct_get_cookie(cname) {
        try {
            var decodedCookie = decodeURIComponent(document.cookie);
            var name = cname + "=";
            var ca = decodedCookie.split(';');

            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }

            return '';
        } catch (err) {
            return '';
        }
    }

    function wcct_set_cookie(cname, cvalue, exsecs) {
        var d = new Date();
        d.setTime(d.getTime() + (exsecs * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

})(jQuery);
