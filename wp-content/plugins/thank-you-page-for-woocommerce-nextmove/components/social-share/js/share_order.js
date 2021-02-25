(function ($, window, doc) {
    var data = {};

    $(doc).on(
        'click', '.xlwcty_shareTab ul li a', function () {
            var $this = $(this);
            var $parent = $(this).closest('.xlwcty_shareTab');
            var tabID = $this.attr('data-tab');
            $parent.find('ul li a').removeClass('xlwcty_active');
            $this.addClass('xlwcty_active');
            $parent.find('.xlwcty_tabArea').removeClass('xlwcty_openTab');
            $parent.find('#' + tabID).addClass('xlwcty_openTab');
        }
    );

    $(doc).on(
        'change', '.xlwcty_share_facebook_text', function (e) {
            var current_text = $(this).val();
            data.text = current_text;
        }
    );
    $(doc).ready(
        function () {
            if ($(".xlwcty_fb_share_btn").length > 0) {
                var btn = $(".xlwcty_fb_share_btn");
                data.text = btn.attr("data-text");
                data.href = btn.attr("data-url");
            }
            $(".wcxlty_fb_order_share").on(
                "click", function (e) {
                    e.preventDefault();
                    var $this = $(this);
                    facebook_share(
                        data, function (response) {
                            if (typeof response != "undefined" && response != "undefined") {
                                if (typeof response == "object" && Object.keys(response).length == 0) {
                                }
                            }
                        }
                    );
                }
            );
            $(".xl-twitter-share-button").on(
                "click", function (e) {
                    e.preventDefault();
                    var shareUrl = $(this).attr("data-url");
                    var text = $(".xlwcty_share_twitter_text").val();

                    if (shareUrl != "") {
                        shareUrl = encodeURI(shareUrl);
                        text = encodeURI(text);
                        var twUrl = "https://twitter.com/intent/tweet?url=" + shareUrl + "&text=" + text + "\n&tw_p=tweetbutton&related=twitterapi,twitter";
                        var twitterWindow = window.open(twUrl, "share on twitter", "menubar=1,resizable=1,width=350,height=250");
                    }
                }
            );
        }
    );
})(jQuery, window, document);
