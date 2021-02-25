'use strict'
jQuery(document).ready(function ($) {
    woo_product_builder.init()
    // $('.woopb-compatible-field').select2()
})

var woo_product_builder = {
    init: function () {
        this.sort_by()
        this.review_popup()
        this.review_total_price()
        this.events()
    },
    sort_by: function () {
        jQuery('.woopb-sort-by-button').on('change', function () {
            var href = jQuery(this).val()
            window.location.href = href
        })
    },
    review_popup: function () {
        jQuery('#vi_wpb_sendtofriend').on('click', function () {
            woo_product_builder.review_popup_show()
        })
        jQuery('#vi_wpb_popup_email .vi-wpb_overlay, #vi_wpb_popup_email .woopb-close').on('click', function () {
            woo_product_builder.review_popup_hide()
        })
    },
    review_popup_show: function () {
        jQuery('html').css({'overflow': 'hidden'})
        jQuery('#vi_wpb_popup_email').fadeIn(500)
    },
    review_popup_hide: function () {
        jQuery('#vi_wpb_popup_email').fadeOut(300)
        jQuery('html').css({'overflow': 'inherit'})
    },
    review_total_price: function () {
        jQuery('.woopb-qty-input').on('change', function () {
            var quantity = parseInt(jQuery(this).val())
            var price = parseFloat(jQuery(this).closest('td').attr('data-price'))
            var total_html = jQuery(this).closest('tr').find('.woopb-total .woocommerce-Price-amount').contents()

            if (price > 0) {
                var total = quantity * price
                total_html.filter(function (index) {
                    return this.nodeType == 3
                }).each(function () {
                    this.textContent = total
                })
            } else {
                return
            }
        })
    },
    events: function () {
        jQuery('.woopb-share-link').on('click', function () {
            jQuery(this).select();
            document.execCommand("copy");
        })
    }
}