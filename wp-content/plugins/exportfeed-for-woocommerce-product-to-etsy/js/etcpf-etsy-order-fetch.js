(function ($) {
    $(document).on('change', '#etsy-manual-order-sync-dropdown .button-update-order', function () {
        let loader = $('#ajax-loader-cat-import');
        loader.show();
        let days = $(this).val();
        orderFetch(days, 0,function (error, res) {
            if(res)
                location.reload();
            else
                loader.hide();
        });

    });

    $(document).on('click', '.button-update-order', function () {
        let loader = $('#ajax-loader-cat-import');
        loader.show();
        let days = $('#etsy-manual-order-sync-dropdown').val();
        orderFetch(days, 0,function (error, res) {
            if(res)
                location.reload();
            else
                loader.hide();
        });

    });

    var orderFetch = function (days, offset, callback) {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdOrderFectch,
                security: ETCPF.ETCPF_nonce,
                perform: 'fetch_etsy_orders',
                limit: 25,
                offset: offset,
                days: days
            },
            success: function (res) {
                if(res.data.recurring === true && res.data.offset > 0){
                    orderFetch(days, res.data.offset,callback)
                }else{
                    callback(null, res);
                }
            },
            error: function (res) {
                callback(res, null);
            }
        });
    };

    $(document).on('click', '#popup-close', function () {
        $('#etsy_sync_popup').hide();
    });

    $(document).on('click', '#btn-fetch-product', function (event) {
        selector = this;
        let payload = {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdOrderFectch,
            security: ETCPF.ETCPF_nonce,
            perform: 'countEtsyProduct',

        };
        performAjax(this, payload, function (error, data) {});
    });

    var performAjax = function (selector, payload, callback) {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: payload,
            success: function (res) {
                if (res.data) {
                    callback(null, res.data);
                }
            },
            error: function (res) {
                callback(res, null);
            }
        });
    };


})(jQuery);

var fetch_products = function (active, draft, inactive, start_value = 0, type = 'active') {
    jQuery('#ajax-loader-cat-import').show();
    jQuery('#etsy_sync_popup').hide();
    jQuery('#gif-message-span-for-more-than-one-feed')
        .text('Fetching Products from Etsy..!!');
    intervalSetter = setInterval(function () {
        do_fetch_products(active, draft, inactive, start_value, type);
    }, 5000);
};

function do_fetch_products(active, draft, inactive, start_value, type) {
    if (ajaxInteraction == null) {
        ajaxInteraction = jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdOrderFectch,
                security: ETCPF.ETCPF_nonce,
                active: active,
                draft: draft,
                inactive: inactive,
                start_value: start_value,
                type: type,
                perform: 'fetch_etsy_products',
            },
            success: function (res) {
                ajaxInteraction = null;
                if (res.data) {
                    if (res.data.status == true) {
                        jQuery('#gif-message-span-for-more-than-one-feed')
                            .text(res.data.message);
                        jQuery('#ajax-loader-cat-import').hide();
                        clearInterval(intervalSetter);
                        window.location.href = 'admin.php?page=etsy-export-feed-product-list';
                    } else {
                        jQuery('#gif-message-span-for-more-than-one-feed')
                            .text(res.data.message);
                        do_fetch_products(res.data.active, res.data.draft, res.data.inactive, res.data.start_value, res.data.type);
                    }
                } else {
                    jQuery('#ajax-loader-cat-import').hide();
                    clearInterval(intervalSetter);
                }
            },
            error: function (res) {
                jQuery('#ajax-loader-cat-import').hide();
                jQuery('#etsy_sync_popup').find('.message').hide();
                clearInterval(intervalSetter);
                alert('Error !!');
            }
        });
    }

};

//mapping products
var map_products = function () {
    jQuery('#ajax-loader-cat-import').show();
    jQuery('#gif-message-span-for-more-than-one-feed')
        .text('Products from etsy and local products are being checked and mapped, please do not navigate to other page till the process is completed. Thanks');
    mappingIntervalSetter = setInterval(function () {
        do_mapping_products();
        etcpf_produce_logs();
    }, 3000);
};
const etcpf_produce_logs = () => {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: "json",
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdOrderFectch,
            security: ETCPF.ETCPF_nonce,
            perform: 'getMappingDetails',
        },
        success: function (res) {
            let html = '<span style="color: #37b18c;">Successfully Mapped '+res.data.successful +'</span>';
            html += ' | <span style="color: #ffb658;"> Remaining '+ res.data.remaining +'</span>';
            html += ' | <span style="color: #ff4a4c;"> Failed to map '+ res.data.failed + '</span>';
            jQuery('#gif-message-span-for-more-than-one-feed')
                .html(html);
        },
        error: function (res) {
            clearInterval(mappingIntervalSetter);
        }
    });
};
function do_mapping_products() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: "json",
        data: {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdOrderFectch,
            security: ETCPF.ETCPF_nonce,
            perform: 'map_products',
        },
        success: function (res) {
            if (res.data) {
                jQuery('#map_product_' + res.data.id).html(res.data.message);
            } else {
                clearInterval(mappingIntervalSetter);
                jQuery('#ajax-loader-cat-import').hide();
                jQuery('#gif-message-span-for-more-than-one-feed').text("Refreshing changes, please wait....");
                setTimeout(function () {
                    location.reload(true);
                }, 300);
            }
        },
        error: function (res) {
            clearInterval(mappingIntervalSetter);
            alert('There was some problem mapping the etsy products with local products, please try again later. Thanks!!');
        }
    });
}

let ajaxInteraction = null;
