(function ($) {

    $(document).on("click", ".xt-framework-admin-notice .notice-dismiss", function () {

        $(this).parent().find('p').each(function() {

            var cname = $(this).data("id"),
                cvalue = "yes";

            document.cookie = cname + "=" + cvalue + ";path=/";
        })

    });

})(jQuery);