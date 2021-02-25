(function ($, window, doc) {

    function get_coupons($this) {
        var cookieName = "xlwcty_generate_new_coupons_disaplayed_" + xlwcty.or;
        var share_coupons = xlgetCookie(cookieName);
        if (share_coupons == "") {
            $(".xlwcty_coupon .xlwcty_component_load").show();
            xlwcty_get_coupons(
                'xlwcty_generate_new_coupons', function (resp) {
                    if ($(".xlwcty_show_hide_coupon").length > 0) {
                        console.log(resp);
                        $(".xlwcty_coupon .xlwcty_component_load").hide();
                        if (resp.cp_html != "") {
                            $(".xlwcty_coupon").html(resp.cp_html);
                            xlsetCookie(cookieName, JSON.stringify({"or": xlwcty.or, "cp": xlwcty.cp}), 365);
                            $this.hide();
                        }
                    }
                }
            );
        }
    }

    if ($(".xlwcty_generate_new_coupons").length > 0) {
        $(".xlwcty_generate_new_coupons").on(
            "click", function (e) {
                var $this = $(this);
                e.preventDefault();
                get_coupons($this);
            }
        );
    }
})(jQuery, window, document);
