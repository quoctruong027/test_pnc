'use strict'
jQuery(document).ready(function ($) {
    var woopbTimeout = null;
    $('.woopb-search-products-input').on('keyup', function () {
        let $this = $(this), search = $this.val()
        if (woopbTimeout) {
            clearTimeout(woopbTimeout);
        }
        woopbTimeout = setTimeout(function () {
            if (search) {
                let data = {
                    action: 'woopb_search_product_in_step',
                    search: search,
                    post_id: $this.attr('data-post'),
                    step: $this.attr('data-step'),
                    form_action: $('form.cart').attr('action'),
                    referer: $('input[name=_wp_http_referer]').val(),
                    nonce: $('#_nonce').val()
                }

                $.ajax({
                    url: _woo_product_builder_params.ajax_url,
                    type: 'post',
                    data: data,
                    success: function (response) {
                        if (response.success) {
                            let html = response.data
                            $('.woopb-products-searched').html(html)
                            loadVariationScript()
                            $('.woopb-products, .woopb-products-pagination').hide();
                        }
                    },
                    error: function (response) {
                    },
                    beforeSend: function () {
                        $('.woopb-spinner-inner').removeClass('woopb-hidden');
                    },
                    complete: function () {
                        $('.woopb-spinner-inner').addClass('woopb-hidden');
                    }
                })
            } else {
                $('.woopb-products, .woopb-products-pagination').show()
                $('.woopb-products-searched').html(' ')

            }
        }, 500);

    })

    function loadVariationScript() {
        $.getScript(_woo_product_builder_params.pluginURL + '/woocommerce/assets/js/frontend/add-to-cart-variation.min.js')
    }
})