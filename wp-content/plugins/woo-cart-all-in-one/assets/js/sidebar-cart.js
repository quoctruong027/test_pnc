(function () {
    if (typeof viwcaio_sc_params === 'undefined') {
        return false;
    }
    jQuery(document).ready(function () {
        'use strict';
        if (typeof wc_cart_fragments_params === 'undefined') {
            return false;
        }
        if (jQuery('.vi-wcaio-sidebar-cart-wrap:not(.vi-wcaio-sidebar-cart-wrap-init)').length) {
            jQuery('.vi-wcaio-sidebar-cart-wrap:not(.vi-wcaio-sidebar-cart-wrap-init)').addClass('vi-wcaio-sidebar-cart-wrap-init');
            viwcaio_sidebar_cart_init();
        }
    });
    jQuery(window).on('load',function () {
        'use strict';
        if (typeof wc_cart_fragments_params === 'undefined') {
            return false;
        }
        if (jQuery('.vi-wcaio-sidebar-cart-wrap:not(.vi-wcaio-sidebar-cart-wrap-init)').length) {
            jQuery('.vi-wcaio-sidebar-cart-wrap:not(.vi-wcaio-sidebar-cart-wrap-init)').addClass('vi-wcaio-sidebar-cart-wrap-init');
            viwcaio_sidebar_cart_init();
        }
    });
})();

function viwcaio_sidebar_cart_refresh(url,data) {
    jQuery(document.body).on('wc_fragments_loaded', function () {
        if (jQuery('.vi-wcaio-sidebar-cart-wrap:not(.vi-wcaio-sidebar-cart-wrap-fragments-load)').length) {
            jQuery('.vi-wcaio-sidebar-cart-wrap:not(.vi-wcaio-sidebar-cart-wrap-fragments-load)').addClass('vi-wcaio-sidebar-cart-wrap-fragments-load');
            jQuery.ajax({
                url: url,
                type: 'POST',
                data:data,
                beforeSend: function () {
                    jQuery('.vi-wcaio-sidebar-cart-wrap').find(' .vi-wcaio-sidebar-cart-loading-wrap').removeClass('vi-wcaio-disabled');
                },
                success: function (response) {
                    if (response.fragments) {
                        jQuery.each(response.fragments, function (key, value) {
                            jQuery(key).replaceWith(value);
                        });
                    }
                    jQuery('.vi-wcaio-sidebar-cart-wrap').addClass('vi-wcaio-sidebar-cart-wrap-updated');
                    jQuery(document.body).trigger("wc_fragment_refresh");
                },
                error: function () {
                    jQuery('.vi-wcaio-sidebar-cart-wrap').find('.vi-wcaio-sidebar-cart-loading-wrap').addClass('vi-wcaio-disabled');
                }
            });
        }
    });
    jQuery(document.body).on('wc_fragments_refreshed wc_fragments_ajax_error', function () {
        jQuery('.vi-wcaio-sidebar-cart-wrap.vi-wcaio-sidebar-cart-wrap-updated').find(' .vi-wcaio-sidebar-cart-loading-wrap').addClass('vi-wcaio-disabled');
        jQuery('.vi-wcaio-sidebar-cart-wrap.vi-wcaio-sidebar-cart-wrap-updated').removeClass('vi-wcaio-sidebar-cart-wrap-updated');
    });
}
function viwcaio_sidebar_cart_update() {
    viwcaio_sidebar_cart_refresh(viwcaio_sc_params.wc_ajax_url.toString().replace('%%endpoint%%', 'viwcaio_get_cart_fragments'), {viwcaio_get_cart_fragments: 1});
    jQuery(document.body).on('removed_from_cart', function (evt, fragments, cart_hash, btn) {
        let cart = jQuery('.vi-wcaio-sidebar-cart-wrap'),
            cart_item_key = jQuery(btn).data('cart_item_key') || '';
        if (cart_item_key && cart.find('.vi-wcaio-sidebar-cart-pd-wrap[data-cart_item_key="' + cart_item_key + '"]').length) {
            cart.find('.vi-wcaio-sidebar-cart-pd-wrap[data-cart_item_key="' + cart_item_key + '"]').remove();
        }
        vi_wcaio_sc_icon_toggle(cart);
    });
    jQuery(document.body).on('added_to_cart', function (evt, fragments, cart_hash, btn) {
        if (btn.hasClass('vi-wcaio-pd_plus-product-bt-atc')){
            return  false;
        }
        let cart = jQuery('.vi-wcaio-sidebar-cart-wrap');
        let effect_after_atc = cart.data('effect_after_atc'),
            fly_to_cart = cart.data('fly_to_cart');
        if (cart.find('.vi-wcaio-sidebar-cart.vi-wcaio-disabled').length){
            vi_wcaio_sc_icon_toggle(cart);
            cart.find('.vi-wcaio-sidebar-cart').removeClass('vi-wcaio-disabled');
        }
        if (cart.find('.vi-wcaio-sidebar-cart-icon-wrap.vi-wcaio-sidebar-cart-icon-wrap-close').length){
            vi_wcaio_sc_icon_toggle(cart, true);
            cart = jQuery('.vi-wcaio-sidebar-cart-wrap');
        }
        if ( !effect_after_atc && !fly_to_cart) {
            return false;
        }
        if (fly_to_cart) {
            let img_product = viwcaio_get_img_product(btn), sc_icon = cart.find('.vi-wcaio-sidebar-cart-icon-wrap');
            if (jQuery(img_product).length) {
                img_product = jQuery(img_product);
                let img_top = img_product.offset().top,
                    img_left = img_product.offset().left,
                    img_width = img_product.width(),
                    img_height = img_product.height(),
                    sc_icon_top = sc_icon.offset().top || cart.offset().top,
                    sc_icon_left = sc_icon.offset().left || cart.offset().left;
                jQuery('body').append('<div class="vi-wcaio-img-flying"></div>');
                let img_flying = jQuery('div.vi-wcaio-img-flying');
                img_product.clone().appendTo(img_flying);
                img_flying.css({
                    'top'   : img_top + 'px',
                    'left'  : img_left + 'px',
                    'width' : img_width + 'px',
                    'height': img_height + 'px'
                }).fadeIn("slow");
                img_flying.animate({
                    'width' : (img_width * 0.6) + 'px',
                    'height': (img_height * 0.6) + 'px',
                    top     : '+=' + (img_height * 0.2) + 'px',
                    left    : '+=' + (img_width * 0.2) + 'px'
                }, 400, 'swing', function () {
                    img_flying.animate({
                        top   : sc_icon_top + 'px',
                        left  : sc_icon_left + 'px',
                        height: '18px',
                        width : '25px'
                    }, 800, "swing", function () {
                        jQuery('div.vi-wcaio-img-flying').fadeIn('fast', function () {
                            jQuery('div.vi-wcaio-img-flying').remove();
                        });
                        jQuery(document.body).trigger('viwcaio_sc_effect_after_atc',[cart,effect_after_atc]);
                    });
                });
            }else {
                jQuery(document.body).trigger('viwcaio_sc_effect_after_atc',[cart,effect_after_atc]);
            }
        } else {
            jQuery(document.body).trigger('viwcaio_sc_effect_after_atc',[cart,effect_after_atc]);
        }
    });
    jQuery(document.body).on('viwcaio_sc_effect_after_atc',function (event, cart,effect_after_atc ) {
        switch (effect_after_atc) {
            case 'open':
                vi_wcaio_sc_toggle('show');
                break;
            case 'shake_horizontal':
                cart.find('.vi-wcaio-sidebar-cart-icon-wrap').removeClass('vi-wcaio-sidebar-cart-icon-wrap-mouseleave vi-wcaio-sidebar-cart-icon-wrap-open').addClass('vi-wcaio-sidebar-cart-icon-wrap-effect-shake_horizontal');
                setTimeout(function () {
                    cart.find('.vi-wcaio-sidebar-cart-icon-wrap').removeClass('vi-wcaio-sidebar-cart-icon-wrap-effect-shake_horizontal');
                }, 1100);
                break;
            case 'shake_vertical':
                cart.find('.vi-wcaio-sidebar-cart-icon-wrap').removeClass('vi-wcaio-sidebar-cart-icon-wrap-mouseleave vi-wcaio-sidebar-cart-icon-wrap-open').addClass('vi-wcaio-sidebar-cart-icon-wrap-effect-shake_vertical');
                setTimeout(function () {
                    cart.find('.vi-wcaio-sidebar-cart-icon-wrap').removeClass('vi-wcaio-sidebar-cart-icon-wrap-effect-shake_vertical');
                }, 1100);
                break;
        }
    });
    jQuery(document.body).on('click', '.vi-wcaio-sidebar-cart-pd-remove-wrap a.vi-wcaio-sidebar-cart-pd-remove', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let button = jQuery(this);
        let wrap = button.closest('.vi-wcaio-sidebar-cart-wrap'), data = {cart_item_key: button.data('cart_item_key')};
        jQuery.ajax({
            url: viwcaio_sc_params.wc_ajax_url.toString().replace('%%endpoint%%', 'viwcaio_remove_item'),
            type: 'POST',
            data: data,
            beforeSend: function () {
                wrap.find(' .vi-wcaio-sidebar-cart-loading-wrap').removeClass('vi-wcaio-disabled');
            },
            success: function (response) {
                if (!response || response.error) {
                    window.location.reload();
                    return false;
                }
                wrap.addClass('vi-wcaio-sidebar-cart-wrap-updated');
                jQuery(document.body).trigger("wc_fragment_refresh");
                jQuery(document.body).trigger('removed_from_cart', [response.fragments, response.cart_hash, button]);
            },
            error: function () {
                wrap.find('.vi-wcaio-sidebar-cart-loading-wrap').addClass('vi-wcaio-disabled');
            }
        });
    });
    jQuery(document.body).on('click', '.vi-wcaio-sidebar-cart-pd-wrap .vi_wcaio_change_qty', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let qty_input = jQuery(this).closest('.vi-wcaio-sidebar-cart-pd-quantity').find('.vi_wcaio_qty');
        let val = parseInt(qty_input.val());
        if (jQuery(this).hasClass('vi_wcaio_plus')){
            val++;
        }else {
            val--;
        }
        qty_input.val(val).trigger('change');
    });
    jQuery(document.body).on('change', '.vi-wcaio-sidebar-cart-pd-wrap input.vi_wcaio_qty', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let val = parseInt(jQuery(this).val()),
            min = parseInt(jQuery(this).attr('min')),
            max = parseInt(jQuery(this).attr('max'));
        if (min > val) {
            val = min;
        }
        if (val > max) {
            val = max;
        }
        jQuery(this).val(val);
        jQuery(this).closest('.vi-wcaio-sidebar-cart-wrap').find('.vi-wcaio-sidebar-cart-bt-update').removeClass('vi-wcaio-disabled');
    });
    jQuery(document.body).on('click', '.vi-wcaio-sidebar-cart-wrap .vi-wcaio-sidebar-cart-bt-update', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let button = jQuery(this);
        let wrap = button.closest('.vi-wcaio-sidebar-cart-wrap');
        let data = wrap.find('.vi-wcaio-sidebar-cart-products input').serialize();
        jQuery.ajax({
            url: viwcaio_sc_params.wc_ajax_url.toString().replace('%%endpoint%%', 'viwcaio_change_quantity'),
            type: 'POST',
            data: data,
            beforeSend: function () {
                wrap.find(' .vi-wcaio-sidebar-cart-loading-wrap').removeClass('vi-wcaio-disabled');
            },
            success: function (response) {
                if (!response || response.error) {
                    window.location.reload();
                    return false;
                }
                wrap.addClass('vi-wcaio-sidebar-cart-wrap-updated');
                button.addClass('vi-wcaio-disabled');
                jQuery(document.body).trigger("wc_fragment_refresh");
                vi_wcaio_sc_icon_toggle(wrap);
            },
            error: function () {
                wrap.find('.vi-wcaio-sidebar-cart-loading-wrap').addClass('vi-wcaio-disabled');
            }
        })
    });
    jQuery(document.body).on('click', '.vi-wcaio-sidebar-cart-wrap .vi-wcaio-bt-coupon-code', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let button = jQuery(this);
        let wrap = button.closest('.vi-wcaio-sidebar-cart-wrap');
        let data = {vi_wcaio_coupon_code: wrap.find('.vi-wcaio-coupon-code').val() || ''};
        jQuery.ajax({
            url: viwcaio_sc_params.wc_ajax_url.toString().replace('%%endpoint%%', 'viwcaio_apply_coupon'),
            type: 'POST',
            data: data,
            beforeSend: function () {
                wrap.find(' .vi-wcaio-sidebar-cart-loading-wrap').removeClass('vi-wcaio-disabled');
            },
            success: function (response) {
                if (!response) {
                    window.location.reload();
                    return false;
                }
                vi_wcaio_show_message(response);
                wrap.addClass('vi-wcaio-sidebar-cart-wrap-updated');
                jQuery(document.body).trigger("wc_fragment_refresh");
                jQuery(document.body).trigger("update_checkout");
                vi_wcaio_sc_icon_toggle(wrap);
            },
            error: function () {
                wrap.find('.vi-wcaio-sidebar-cart-loading-wrap').addClass('vi-wcaio-disabled');
            }
        })
    });
}

function viwcaio_sidebar_cart_init() {
    jQuery(document).on('mouseenter', '.vi-wcaio-sidebar-cart-icon-wrap', function () {
        if (jQuery(this).hasClass('vi-wcaio-sidebar-cart-icon-wrap-click')) {
            jQuery(this).removeClass('vi-wcaio-sidebar-cart-icon-wrap-mouseleave').addClass('vi-wcaio-sidebar-cart-icon-wrap-mouseenter');
        }else {
            vi_wcaio_sc_toggle('show');
        }
    }).on('mouseleave', '.vi-wcaio-sidebar-cart-icon-wrap', function () {
        if (jQuery(this).hasClass('vi-wcaio-sidebar-cart-icon-wrap-mouseenter')) {
            jQuery(this).removeClass('vi-wcaio-sidebar-cart-icon-wrap-mouseenter').addClass('vi-wcaio-sidebar-cart-icon-wrap-mouseleave');
        }
    }).on('click', '.vi-wcaio-sidebar-cart-icon-wrap', function () {
        if (jQuery(this).hasClass('vi-wcaio-sidebar-cart-icon-wrap-click')) {
            vi_wcaio_sc_toggle('show');
        }
    });
    jQuery(document).on('click', '.vi-wcaio-sidebar-cart-overlay, .vi-wcaio-sidebar-cart-close-wrap', function () {
        if (!jQuery(this).hasClass('vi-wcaio-not-hidden')){
            vi_wcaio_sc_toggle('hide');
        }
    });
    jQuery('.vi-wcaio-sidebar-cart-footer-pd-wrap-wrap:not(.vi-wcaio-slide-init)').each(function () {
        vi_wcaio_sc_flexslider(jQuery(this));
    });
    viwcaio_sidebar_cart_update();
}
function vi_wcaio_sc_icon_toggle(cart, show = false) {
    cart = jQuery(cart);
    if (show) {
        jQuery('.vi-wcaio-sidebar-cart-icon-wrap').removeClass('vi-wcaio-sidebar-cart-icon-wrap-close');
        jQuery('.vi-wcaio-sidebar-cart-icon-wrap').addClass('vi-wcaio-sidebar-cart-icon-wrap-open');
        return false;
    }
    if (cart.data('empty_enable') || cart.find('.vi-wcaio-sidebar-cart-pd-wrap').length) {
        return false;
    }
    vi_wcaio_sc_toggle('hide');
    jQuery('.vi-wcaio-coupon-code').val('');
    jQuery('.vi-wcaio-sidebar-cart-icon-wrap').addClass('vi-wcaio-sidebar-cart-icon-wrap-close');
    jQuery('.vi-wcaio-sidebar-cart-icon-wrap').removeClass('vi-wcaio-sidebar-cart-icon-wrap-open');
}

function viwcaio_get_img_product(btn) {
    let  product = btn.closest('.vi-wcaio-sb-wrap'), img_product;//Sticky add to cart
    if (!product.length) {//Uncode
        product = btn.closest('.tmb-woocommerce');
    }
    if (!product.length) {
        product = btn.closest('.product');
    }
    if (!product.length) {
        product = btn.closest('.item-product');
    }
    if (!product.length) {//Milano
        product = btn.closest('.product-item');
    }
    if (!product.length) {//Infinite
        product = btn.closest('.gdlr-core-item-list');
    }
    if (!product.length) {//Zella
        product = btn.closest('.product-warp-item');
    }
    if (product.find('.vi-wcaio-sb-product-img').length) {
        img_product = product.find('.vi-wcaio-sb-product-img').first();
    } else if (product.find('.wp-post-image').length) {
        img_product = product.find('.wp-post-image').first();
    }else if (product.find('.attachment-shop_catalog').length) {//Authentic, //Zella ,Skudmart 1.0.6
        img_product = product.find('.attachment-shop_catalog').first();
    } else if (product.find('.gdlr-core-product-thumbnail').length) {//Infinite
        img_product = product.find('.gdlr-core-product-thumbnail').first();
    } else if (product.find('.woo-entry-image-main').length) {//ocean
        img_product = product.find('.woo-entry-image-main').first();
    } else if (product.find('.wp-post-image.vi-load').length) { //swatches demo
        img_product = product.find('.wp-post-image.vi-load').first();
    } else if (product.find('.attachment-woocommerce_thumbnail').length) {
        img_product = product.find('.attachment-woocommerce_thumbnail').first();
    }
    if (!img_product) {
        if (product && product.find('img')) {
            img_product = product.find('img').first();
        } else {
            img_product = false;
        }
    }
    return img_product;
}

function vi_wcaio_sc_design(wrap) {
    wrap = jQuery(wrap);
    if (window.innerWidth < 782){
        wrap.css({maxHeight: window.innerHeight});
    }
}
function vi_wcaio_sc_toggle(action = '', new_effect = '') {
    let wrap = jQuery('.vi-wcaio-sidebar-cart-content-wrap'),
        position = jQuery('.vi-wcaio-sidebar-cart').data('position'),
        effect = jQuery('.vi-wcaio-sidebar-cart').data('effect'),
        content_show =  'products';
    vi_wcaio_sc_design(wrap);
    if (action === 'show' && wrap.hasClass('vi-wcaio-sidebar-cart-content-open')) {
        wrap.find('.vi-wcaio-sidebar-cart-content-wrap1, .vi-wcaio-sidebar-cart-footer').addClass('vi-wcaio-disabled');
        wrap.find('.vi-wcaio-sidebar-cart-content-wrap1.vi-wcaio-sidebar-cart-'+content_show+'-wrap').removeClass('vi-wcaio-disabled');
        wrap.find('.vi-wcaio-sidebar-cart-footer.vi-wcaio-sidebar-cart-footer-'+content_show).removeClass('vi-wcaio-disabled');
        return false;
    }
    if (action === 'hide' && wrap.hasClass('vi-wcaio-sidebar-cart-content-close')) {
        return false;
    }
    let type = (position === 'top_left' || position === 'bottom_left') ? 'left' : 'right';
    if (action === 'start' && new_effect) {
        if (wrap.hasClass('vi-wcaio-sidebar-cart-content-close')) {
            wrap.removeClass('vi-wcaio-sidebar-cart-content-open vi-wcaio-sidebar-cart-content-open-' + effect + '-' + type);
            wrap.addClass('vi-wcaio-sidebar-cart-content-close vi-wcaio-sidebar-cart-content-close-' + new_effect + '-' + type);
        } else {
            wrap.addClass('vi-wcaio-sidebar-cart-content-open vi-wcaio-sidebar-cart-content-open-' + new_effect + '-' + type);
            wrap.removeClass('vi-wcaio-sidebar-cart-content-close vi-wcaio-sidebar-cart-content-close-' + effect + '-' + type);
        }
        jQuery('.vi-wcaio-sidebar-cart').data('effect', new_effect);
        return false;
    }
    new_effect = new_effect ? new_effect : effect;
    let old_position = jQuery('.vi-wcaio-sidebar-cart').data('old_position') || '';
    let old_type = old_position ? ((old_position === 'top_left' || old_position === 'bottom_left') ? 'left' : 'right') : type;
    let class_open = 'vi-wcaio-sidebar-cart-content-open vi-wcaio-sidebar-cart-content-open-' + new_effect + '-' + type,
        class_close = 'vi-wcaio-sidebar-cart-content-close vi-wcaio-sidebar-cart-content-close-' + new_effect + '-' + type,
        class_open_old = 'vi-wcaio-sidebar-cart-content-open vi-wcaio-sidebar-cart-content-open-' + effect + '-' + old_type,
        class_close_old = 'vi-wcaio-sidebar-cart-content-close vi-wcaio-sidebar-cart-content-close-' + effect + '-' + old_type + ' vi-wcaio-sidebar-cart-content-close-' + effect + '-' + type;
    if (wrap.hasClass('vi-wcaio-sidebar-cart-content-close')) {
        wrap.addClass(class_open).removeClass(class_close).removeClass(class_close_old);
        wrap.find('.vi-wcaio-sidebar-cart-content-wrap1, .vi-wcaio-sidebar-cart-footer').addClass('vi-wcaio-disabled');
        wrap.find('.vi-wcaio-sidebar-cart-content-wrap1.vi-wcaio-sidebar-cart-'+content_show+'-wrap').removeClass('vi-wcaio-disabled');
        wrap.find('.vi-wcaio-sidebar-cart-footer.vi-wcaio-sidebar-cart-footer-'+content_show).removeClass('vi-wcaio-disabled');
        jQuery('.vi-wcaio-sidebar-cart-icon-wrap').addClass('vi-wcaio-sidebar-cart-icon-wrap-close');
        jQuery('.vi-wcaio-sidebar-cart-icon-wrap').removeClass('vi-wcaio-sidebar-cart-icon-wrap-open vi-wcaio-sidebar-cart-icon-wrap-mouseenter vi-wcaio-sidebar-cart-icon-wrap-mouseleave');
        jQuery('.vi-wcaio-sidebar-cart-overlay').removeClass('vi-wcaio-disabled');
        jQuery('html').addClass('vi-wcaio-html-non-scroll');
    } else {
        wrap.addClass(class_close).removeClass(class_open).removeClass(class_open_old);
        jQuery('.vi-wcaio-sidebar-cart-icon-wrap').removeClass('vi-wcaio-sidebar-cart-icon-wrap-close vi-wcaio-sidebar-cart-icon-wrap-mouseenter vi-wcaio-sidebar-cart-icon-wrap-mouseleave');
        jQuery('.vi-wcaio-sidebar-cart-icon-wrap').addClass('vi-wcaio-sidebar-cart-icon-wrap-open');
        jQuery('.vi-wcaio-sidebar-cart-overlay').addClass('vi-wcaio-disabled');
        jQuery('html').removeClass('vi-wcaio-html-non-scroll');
    }
    jQuery('.vi-wcaio-sidebar-cart').data('effect', new_effect);
}

function vi_wcaio_sc_flexslider(wrap) {
    wrap = jQuery(wrap);
    let rtl = false;
    if (wrap.closest('.vi-wcaio-sidebar-cart-rtl').length){
        rtl = true;
    }
    wrap.addClass('vi-wcaio-slider-init');
    wrap.flexslider({
        namespace: 'vi-wcaio-slider-',
        selector: '.vi-wcaio-sidebar-cart-footer-pd-wrap .vi-wcaio-sidebar-cart-footer-pd',
        animation: 'slide',
        animationLoop: 1,
        itemWidth: 145,
        itemMargin: 10,
        controlNav: false,
        maxItems: window.outerWidth > 480 ? 2: 1,
        reverse: rtl,
        rtl: rtl,
        move: 1,
        touch: true,
        slideshow: false,
    });
}
function vi_wcaio_show_message(message){
    if (!jQuery('.vi-wcaio-warning-wrap').length) {
        jQuery('body').append('<div class="vi-wcaio-warning-wrap vi-wcaio-warning-wrap-open">' + message + '</div>');
    } else {
        jQuery('.vi-wcaio-warning-wrap').removeClass('vi-wcaio-warning-wrap-close').addClass('vi-wcaio-warning-wrap-open').html(message);
    }
    setTimeout(function () {
        jQuery('.vi-wcaio-warning-wrap').addClass('vi-wcaio-warning-wrap-close').removeClass('vi-wcaio-warning-wrap-open');
    }, 2000);
}