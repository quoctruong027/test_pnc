(function ($, window, doc) {
    var data = {};
    $(doc).ready(
        function () {
            if ($(".xlwcty_fb_share_btn").length > 0) {
                var btn = $(".xlwcty_fb_share_btn");
                data.text = btn.attr("data-text");
                data.href = btn.attr("data-href");
            }

            function get_coupons() {
                var cookieName = "xlwcty_smart_bribe_cookies_displayed_" + xlwcty.or;
                var share_coupons = xlgetCookie(cookieName);
                if (share_coupons == "") {
                    $(".xlwcty_socialBox .xlwcty_component_load").show();
                    xlwcty_get_coupons(
                        'xlwcty_smart_bribe_coupons', function (resp) {
                            $(".xlwcty_socialBox .xlwcty_component_load").hide();
                            if ($(".xlwcty_smart_bribe_show_hidden_coupon").length > 0) {
                                if (resp.cp_html != "") {
                                    $(".xlwcty_smart_bribe_coupon").html(resp.cp_html);
                                    $(".xlwcty_smart_bribe_icons").fadeOut().remove();
                                    xlsetCookie(cookieName, JSON.stringify({"or": xlwcty.or, "cp": xlwcty.cp}), 365);
                                }
                            }
                        }
                    );
                }
            }

            $(".wcxlty_fb_order_smart_bribe").on(
                "click", function (e) {
                    e.preventDefault();
                    var $this = $(this);
                    var $href = $(this).attr("data-href");
                    var $shareTxt = $(this).attr("data-text");

                    facebook_share(
                        {href: $href, text: $shareTxt}, function (response) {

                            if (typeof response != "undefined" && response != "undefined") {
                                if (typeof response == "object" && Object.keys(response).length == 0) {
                                    if ($(".xlwcty_smart_bribe_show_hidden_coupon").length > 0) {
                                        get_coupons();
                                    }
                                }
                            }
                        }
                    );
                }
            );

            $(window).load(
                function () {
                    facebook_like(
                        function (url, html_element) {
                            if ($(".xlwcty_smart_bribe_show_hidden_coupon").length > 0) {
                                get_coupons();
                            }
                        }
                    );

                    twitter_follow(
                        function (event) {
                            //                console.log(event);
                        }
                    );
                }
            );

            $(".wcxlty_fb_like").on(
                "click", function (e) {
                    e.preventDefault();
                    get_coupons();
                }
            );

        }
    );
})(jQuery, window, document);

function xlwcty_smart_bribe_plus_one(obj) {
    //    console.log(obj);
}
