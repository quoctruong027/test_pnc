'use strict';
jQuery(document).ready(function () {
    jQuery('.vi_wpvs_loop_variation_form:not(.vi_wpvs_loop_variation_form_check)').each(function () {
        let swacthes =  viwpvs_set_swatches_position(jQuery(this));
        swacthes.removeClass('vi-wpvs-hidden').addClass('vi_wpvs_loop_variation_form_check').wpvs_get_variation();
        swacthes.addClass('vi_wpvs_variation_form vi_wpvs_variation_form_init').viwpvs_woo_product_variation_swatches();

    });
    if (viwpvs_frontend_loop_product_params.viwpvs_position) {
        jQuery.ajaxSetup({
            beforeSend: function () {
                let type = typeof this.data;
                if (type == 'string') {
                    this.data += '&viwpvs_position=' + viwpvs_frontend_loop_product_params.viwpvs_position;
                }
            },
        });
    }
    jQuery(document).on('ajaxComplete', function (event, jqxhr, settings) {
        jQuery('.vi_wpvs_loop_variation_form:not(.vi_wpvs_loop_variation_form_check)').each(function () {
            let swacthes =  viwpvs_set_swatches_position(jQuery(this));
            swacthes.removeClass('vi-wpvs-hidden').addClass('vi_wpvs_loop_variation_form_check').wpvs_get_variation();
            swacthes.addClass('vi_wpvs_variation_form vi_wpvs_variation_form_init').viwpvs_woo_product_variation_swatches();

        });
        return false;
    });
    //Compatible
    viwpvs_fixed();
});

jQuery(window).load(function () {
    jQuery('.vi_wpvs_loop_variation_form:not(.vi_wpvs_loop_variation_form_check)').each(function () {
        let swacthes =  viwpvs_set_swatches_position(jQuery(this));
        swacthes.removeClass('vi-wpvs-hidden').addClass('vi_wpvs_loop_variation_form_check').wpvs_get_variation();
        swacthes.addClass('vi_wpvs_variation_form vi_wpvs_variation_form_init').viwpvs_woo_product_variation_swatches();

    });
});

function viwpvs_set_swatches_position(swatches) {
    swatches = jQuery(swatches);
    if (jQuery.inArray(viwpvs_frontend_loop_product_params.theme, ['infinite','infinite-child']) !== -1) {
         let product =  swatches.closest('.gdlr-core-item-list');
        if (viwpvs_frontend_loop_product_params.theme_swatches_pos){
            let clone = swatches.clone(),
                div_append = product.find('.gdlr-core-product-grid-content-wrap .gdlr-core-product-grid-content');
            swatches.remove();
            div_append.append('<span class="vi_wpvs_loop_variation_price vi_wpvs_loop_variation_hidden"></span>');
            // clone.viwpvs_woo_product_variation_swatches();
            if (viwpvs_frontend_loop_product_params.theme_swatches_pos ==='after_price') {
                clone.appendTo(div_append);
            }else {
                clone.prependTo(div_append);
            }
            swatches = clone;
        }
    }
    return swatches;
}

function viwpvs_fixed() {
    //flatsome
    if (jQuery.inArray(viwpvs_frontend_loop_product_params.theme, ['flatsome',]) !== -1) {
        jQuery(document).on('append.infiniteScroll', function (event, response, path, items) {
            let form_div = jQuery(items).find('.vi_wpvs_loop_variation_form:not(.vi_wpvs_loop_variation_form_check)');
            if (form_div.length > 0) {
                form_div.each(function () {
                    jQuery(this).removeClass('vi-wpvs-hidden').addClass('vi_wpvs_loop_variation_form_check vi_wpvs_variation_form').wpvs_get_variation().viwpvs_woo_product_variation_swatches();
                });
            }
        });
    }
}

var viwpvs_get_variations = function ($swatches) {
    this.is_atc = viwpvs_frontend_loop_product_params.is_atc;
    this.wc_ajax_url = viwpvs_frontend_loop_product_params.wc_ajax_url;
    this.swatches = $swatches;
    this.product_id = parseInt($swatches.data('product_id'));
    this.variation = $swatches.data('product_variations');
    this.is_find_variation = $swatches.data('vpvs_find_variation');
    this.is_ajax = !this.variation;
    this.xhr = false;
    this.init();
};
viwpvs_get_variations.prototype.init = function () {
    let self = this,
        swatches = this.swatches,
        variation = this.variation,
        $select = this.swatches.find('select'),
        attribute = {},
        product, count_attr = 0, img_product, img_src;
    product = swatches.closest('.product');
    if (!product.length){//Infinite
        product =  swatches.closest('.gdlr-core-item-list');
    }
    img_product = jQuery(self.get_img_product(swatches));
    img_src = img_product.attr('data-src') || img_product.attr('src') || img_product.attr('content') || img_product.attr('srcset') || '';
    $select.each(function () {
        let val = jQuery(this).val();
        if (val) {
            count_attr++;
        }
        attribute[jQuery(this).data('attribute_name')] = val;
    });
    if (count_attr > 0) {
        self.find_variation(attribute, variation, product, img_product, img_src, true);
    }
    $select.on('change', function (e) {
        attribute[jQuery(this).data('attribute_name')] = jQuery(this).val();
        img_product = self.get_img_product(swatches, true);
        self.find_variation(attribute, variation, product, img_product, img_src);
    });
    product.find('.vi_wpvs_loop_atc_button').on('click', function () {
        if (jQuery(this).hasClass('vi_wpvs_loop_variation_no_pointer')) {
            return false;
        }
        product.find('.viwcuf_product_qty_tooltip').addClass('vi_wpvs_loop_variation_hidden');
        let $thisbutton = jQuery(this), data = swatches.serialize();
        if (product.find('.viwcuf_product_qty').length > 0) {
            let qty = parseInt(product.find('.viwcuf_product_qty').val() || 0),
                min_qty = parseInt(product.find('.viwcuf_product_qty').attr('min') || 1),
                max_qty = parseInt(product.find('.viwcuf_product_qty').attr('max') || 0);
            if (qty === 0) {
                return false;
            }
            if (qty < min_qty) {
                product.find('.viwcuf_product_qty_tooltip').removeClass('vi_wpvs_loop_variation_hidden').html(viwpvs_frontend_loop_product_params.less_min_qty + ' ' + min_qty + '.');
                setTimeout(function () {
                    product.find('.viwcuf_product_qty_tooltip').addClass('vi_wpvs_loop_variation_hidden');
                }, 2000);
                return false;
            }
            if (max_qty > 0 && qty > max_qty) {
                product.find('.viwcuf_product_qty_tooltip').removeClass('vi_wpvs_loop_variation_hidden').html(viwpvs_frontend_loop_product_params.greater_max_qty + ' ' + max_qty + '.');
                setTimeout(function () {
                    product.find('.viwcuf_product_qty_tooltip').addClass('vi_wpvs_loop_variation_hidden');
                }, 2000);
                return false;
            }
            data += '&quantity=' + qty;
        }
        jQuery.ajax({
            type: 'post',
            url: self.wc_ajax_url.toString().replace('%%endpoint%%', 'wpvs_add_to_cart'),
            data: data,
            beforeSend: function (response) {
                $thisbutton.removeClass('added').addClass('loading');
            },
            complete: function (response) {
                $thisbutton.removeClass('loading').addClass('added');
            },
            success: function (response) {
                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return false;
                } else {
                    jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
                    jQuery(document.body).trigger('wc_fragments_refreshed');
                    jQuery(document.body).trigger("update_checkout");
                    jQuery(document.body).trigger("viwpvs_added_to_cart", [response, $thisbutton]);
                }

            },
        });
    });
};
viwpvs_get_variations.prototype.find_variation = function (attrs, variations, product, img_product, img_src, first_time = false) {
    let self = this, swatches = self.swatches, variation, options = [], attrs_name, check_attr_null;
    if (self.is_ajax) {
        if (!self.is_find_variation) {
            if (first_time) {
                swatches.find('.vi-wpvs-option-wrap-selected').removeClass('vi-wpvs-option-wrap-selected').addClass('vi-wpvs-option-wrap-default');
                swatches.find('.vi-wpvs-select-attribute  select').each(function () {
                    jQuery(this).val('').trigger('change');
                });
                return false;
            }
            if (img_product) {
                let img_option_src = swatches.find('.vi-wpvs-option-wrap-selected img').data('loop_src');
                if (img_option_src) {
                    if (jQuery(img_product).parent().is('picture')) {
                        jQuery(img_product).parent().find('source').each(function (k, v) {
                            jQuery(v).attr({'srcset': img_option_src});
                        })
                    }
                    img_product.attr({'src': img_option_src, 'srcset': img_option_src});
                }
            }
            return false;
        }
        if (self.xhr) {
            self.xhr.abort();
        }
        jQuery.each(attrs, function (k, v) {
            if (!v) {
                check_attr_null = true;
                return false;
            }
        });
        if (check_attr_null) {
            return false;
        }
        if (self.variation) {
            variations = self.variation;
            jQuery.each(variations, function (key, value) {
                if (self.check_is_equal(attrs, value.attributes)) {
                    variation = value;
                    return false;
                }
            });
            if (variation) {
                if (img_product) {

                    if (jQuery(img_product).parent().is('picture')) {
                        jQuery(img_product).parent().find('source').each(function (k, v) {
                            jQuery(v).attr({'srcset': variation.image.thumb_src});
                        })
                    }
                    img_product.attr({'src': variation.image.thumb_src, 'srcset': variation.image.thumb_src});
                }
                if (variation.price_html) {
                    product.find('.price:not(.vi_wpvs_loop_variation_price),.gdlr-core-product-price:not(.vi_wpvs_loop_variation_price)').addClass('vi_wpvs_loop_variation_hidden');
                    product.find('.vi_wpvs_loop_variation_price').removeClass('vi_wpvs_loop_variation_hidden').html(variation.price_html);
                }
                if (self.is_atc) {
                    if (variation.is_in_stock) {
                        self.set_add_to_cart(variation.variation_id, swatches);
                        product.find('.add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                        product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden vi_wpvs_loop_variation_no_pointer');
                        product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
                        product.find('.vi_wpvs_loop_action .viwcuf_product_qty').attr({min: variation.min_qty, max: variation.max_qty});
                    } else {
                        self.set_add_to_cart('', swatches);
                        product.find('.add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                        product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden').addClass('vi_wpvs_loop_variation_no_pointer');
                        product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
                    }
                }
            } else {
                if (variations.length < parseInt(swatches.data('variation_count'))) {
                    self.call_ajax(attrs, self, variations, img_product, img_src, product, swatches);
                } else {
                    if (img_product) {
                        if (jQuery(img_product).parent().is('picture')) {
                            jQuery(img_product).parent().find('source').each(function (k, v) {
                                jQuery(v).attr({'srcset': img_src});
                            })
                        }
                        img_product.attr({'src': img_src, 'srcset': img_src});
                    }
                    product.find('.price:not(.vi_wpvs_loop_variation_price),.gdlr-core-product-price:not(.vi_wpvs_loop_variation_price)').removeClass('vi_wpvs_loop_variation_hidden');
                    product.find('.vi_wpvs_loop_variation_price').addClass('vi_wpvs_loop_variation_hidden');
                    if (self.is_atc) {
                        self.set_add_to_cart('', swatches);
                        product.find('.add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                        product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden').addClass('vi_wpvs_loop_variation_no_pointer');
                        product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
                    }
                }
            }
        } else {
            variations = [];
            self.call_ajax(attrs, self, variations, img_product, img_src, product, swatches);
        }
    } else {
        attrs_name = Object.getOwnPropertyNames(attrs);
        if (attrs_name.length === 1) {
            jQuery.each(variations, function (k, v) {
                let propName = v.attributes[attrs_name[0]];
                if (propName && options.indexOf(propName) === -1) {
                    options.push(propName);
                }
            });
            if (options.length > 0) {
                options.push('');
                swatches.find('select option').each(function (option_item_k, option_item) {
                    let val = jQuery(option_item).val().toString();
                    if (jQuery.inArray(val, options) === -1) {
                        jQuery(option_item).addClass('vi-wpvs-option-disabled');
                    }
                });
            }
        } else {
            let attrs_values = [];
            for (let i = 0; i < attrs_name.length; i++) {
                let attr_key = attrs_name[i],
                    attr_value = attrs[attr_key];
                if (!attr_value || attr_value === '') {
                    check_attr_null = true;
                    for (let m = 0; m < attrs_name.length; m++) {
                        if (i == m) {
                            continue;
                        }
                        let attr_key_t = attrs_name[m];
                        swatches.find('.vi-wpvs-select-attribute select[name="' + attr_key_t + '"] option').removeClass('vi-wpvs-option-disabled');
                    }
                }
                jQuery.each(variations, function (key, val) {
                    if (!val.attributes[attr_key]) {
                        swatches.find('select[name ="' + attr_key + '"]').addClass('vi-wpvs-select-option-show');
                        return true;
                    }
                    if (attrs_values.indexOf(val.attributes[attr_key]) === -1) {
                        attrs_values.push(val.attributes[attr_key]);
                    }
                    if (attr_value == val.attributes[attr_key]) {
                        swatches.find('option[value ="' + val.attributes[attr_key] + '"]').removeClass('vi-wpvs-option-disabled');
                        if (options.indexOf(val.attributes[attr_key]) === -1) {
                            options.push(val.attributes[attr_key]);
                        }
                        for (let j = 0; j < attrs_name.length; j++) {
                            let attr_key_t = attrs_name[j];
                            if (val.attributes[attr_key_t] && attr_key_t !== attr_key) {
                                swatches.find('option[value ="' + val.attributes[attr_key_t] + '"]').removeClass('vi-wpvs-option-disabled');
                                if (options.indexOf(val.attributes[attr_key_t]) === -1) {
                                    options.push(val.attributes[attr_key_t]);
                                }
                                if (attrs_values.indexOf(val.attributes[attr_key_t]) === -1) {
                                    attrs_values.push(val.attributes[attr_key_t]);
                                }
                            }
                        }
                    } else {
                        for (let k = 0; k < attrs_name.length; k++) {
                            let attr_key_t = attrs_name[k];
                            if (val.attributes[attr_key_t] === '' || val.attributes[attr_key_t] === null) {
                                swatches.find('select[name ="' + attr_key_t + '"]').addClass('vi-wpvs-select-option-show');
                            } else if (attr_value !== '' && attr_value !== null && attr_key_t !== attr_key && options.indexOf(val.attributes[attr_key_t]) === -1) {
                                swatches.find('option[value ="' + val.attributes[attr_key_t] + '"]').addClass('vi-wpvs-option-disabled');
                            }
                        }
                    }
                });
            }
            if (attrs_values.length > 0) {
                attrs_values.push('');
                swatches.find('.vi-wpvs-select-attribute select:not(.vi-wpvs-select-option-show) option').each(function (option_item_k, option_item) {
                    let val = jQuery(option_item).val().toString();
                    if (jQuery.inArray(val, attrs_values) === -1) {
                        jQuery(option_item).addClass('vi-wpvs-option-disabled');
                    }
                });
            }
            swatches.find('.vi-wpvs-select-attribute select').removeClass('vi-wpvs-select-option-show');
        }
        if (!self.is_find_variation) {
            if (first_time) {
                swatches.find('.vi-wpvs-option-wrap-selected').removeClass('vi-wpvs-option-wrap-selected').addClass('vi-wpvs-option-wrap-default');
                swatches.find('select').each(function (k, v) {
                    jQuery(v).val('');
                });
                return false;
            }
            if (img_product) {
                let img_option_src = swatches.find('.vi-wpvs-option-wrap-selected img').data('loop_src');
                if (img_option_src) {
                    if (jQuery(img_product).parent().is('picture')) {
                        jQuery(img_product).parent().find('source').each(function (k, v) {
                            jQuery(v).attr({'srcset': img_option_src});
                        })
                    }
                    img_product.attr({'src': img_option_src, 'srcset': img_option_src});
                }
            }
            return false;
        }

        if (!check_attr_null) {
            jQuery.each(variations, function (key, value) {
                if (self.check_is_equal(attrs, value.attributes)) {
                    variation = value;
                    return false;
                }
            });
        }
        if (variation) {
            if (img_product) {
                if (jQuery(img_product).parent().is('picture')) {
                    jQuery(img_product).parent().find('source').each(function (k, v) {
                        jQuery(v).attr({'srcset': variation.image.thumb_src});
                    })
                }
                img_product.attr({'src': variation.image.thumb_src, 'srcset': variation.image.thumb_src});
            }
            if (variation.price_html) {
                product.find('.price:not(.vi_wpvs_loop_variation_price),.gdlr-core-product-price:not(.vi_wpvs_loop_variation_price)').addClass('vi_wpvs_loop_variation_hidden');
                product.find('.vi_wpvs_loop_variation_price').removeClass('vi_wpvs_loop_variation_hidden').html(variation.price_html);
            }

            if (self.is_atc) {
                if (variation.is_in_stock) {
                    self.set_add_to_cart(variation.variation_id, swatches);
                    product.find('.add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                    product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden vi_wpvs_loop_variation_no_pointer');
                    product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
                    product.find('.vi_wpvs_loop_action .viwcuf_product_qty').attr({min: variation.min_qty, max: variation.max_qty});
                } else {
                    self.set_add_to_cart('', swatches);
                    product.find('.add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                    product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden').addClass('vi_wpvs_loop_variation_no_pointer');
                    product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
                }
            }
        } else {
            if (img_product) {
                if (jQuery(img_product).parent().is('picture')) {
                    jQuery(img_product).parent().find('source').each(function (k, v) {
                        jQuery(v).attr({'srcset': img_src});
                    })
                }
                img_product.attr({'src': img_src, 'srcset': img_src});
            }
            product.find('.price:not(.vi_wpvs_loop_variation_price),.gdlr-core-product-price:not(.vi_wpvs_loop_variation_price)').removeClass('vi_wpvs_loop_variation_hidden');
            product.find('.vi_wpvs_loop_variation_price').addClass('vi_wpvs_loop_variation_hidden');
            if (self.is_atc) {
                self.set_add_to_cart('', swatches);
                product.find('.add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden').addClass('vi_wpvs_loop_variation_no_pointer');
                product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
            }
        }
    }
};
viwpvs_get_variations.prototype.call_ajax = function (attrs, self, variations, img_product, img_src, product, swatches) {
    attrs.product_id = self.product_id;
    self.xhr = jQuery.ajax({
        url: self.wc_ajax_url.toString().replace('%%endpoint%%', 'get_variation'),
        type: 'POST',
        data: attrs,
        beforeSend: function () {
            swatches.find('.vi_wpvs_loop_variation_form_loading').removeClass('vi_wpvs_loop_variation_form_loading_hidden').addClass('vi_wpvs_loop_variation_form_loading_visible');
        },
        success: function (result) {
            if (result) {
                if (img_product) {
                    if (jQuery(img_product).parent().is('picture')) {
                        jQuery(img_product).parent().find('source').each(function (k, v) {
                            jQuery(v).attr({'srcset': result.image.thumb_src});
                        })
                    }
                    img_product.attr({'src': result.image.thumb_src, 'srcset': result.image.thumb_src});
                }
                if (result.price_html) {
                    product.find('.price:not(.vi_wpvs_loop_variation_price),.gdlr-core-product-price:not(.vi_wpvs_loop_variation_price)').addClass('vi_wpvs_loop_variation_hidden');
                    product.find('.vi_wpvs_loop_variation_price').removeClass('vi_wpvs_loop_variation_hidden').html(result.price_html);
                }
                if (self.is_atc) {
                    if (result.is_in_stock) {
                        self.set_add_to_cart(result.variation_id, swatches);
                        product.find('.add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                        product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden vi_wpvs_loop_variation_no_pointer');
                        product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
                        product.find('.vi_wpvs_loop_action .viwcuf_product_qty').attr({min: result.min_qty, max: result.max_qty});
                    } else {
                        self.set_add_to_cart('', swatches);
                        product.find('.add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                        product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden').addClass('vi_wpvs_loop_variation_no_pointer');
                        product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
                    }
                }
                variations[variations.length || 0] = result;
                self.variation = variations;
            } else {
                if (img_product) {
                    if (jQuery(img_product).parent().is('picture')) {
                        jQuery(img_product).parent().find('source').each(function (k, v) {
                            jQuery(v).attr({'srcset': img_src});
                        })
                    }
                    img_product.attr({'src': img_src, 'srcset': img_src});
                }
                product.find('.price:not(.vi_wpvs_loop_variation_price),.gdlr-core-product-price:not(.vi_wpvs_loop_variation_price)').removeClass('vi_wpvs_loop_variation_hidden');
                product.find('.vi_wpvs_loop_variation_price').addClass('vi_wpvs_loop_variation_hidden');
                if (self.is_atc) {
                    self.set_add_to_cart('', swatches);
                    product.find('.add_to_cart_button:not(.vi_wpvs_loop_atc_button)').addClass('vi_wpvs_loop_variation_hidden');
                    product.find('.vi_wpvs_loop_atc_button').removeClass('vi_wpvs_loop_variation_hidden').addClass('vi_wpvs_loop_variation_no_pointer');
                    product.find('.vi_wpvs_loop_action').removeClass('vi_wpvs_loop_variation_hidden');
                }
            }
            delete attrs.product_id;
        },
        complete: function () {
            swatches.find('.vi_wpvs_loop_variation_form_loading').removeClass('vi_wpvs_loop_variation_form_loading_visible').addClass('vi_wpvs_loop_variation_form_loading_hidden');
        }
    });
};
viwpvs_get_variations.prototype.check_is_equal = function (attrs1, attrs2) {
    let i,
        aProps = Object.getOwnPropertyNames(attrs1),
        bProps = Object.getOwnPropertyNames(attrs2);
    if (aProps.length != bProps.length) {
        return false;
    }

    for (i = 0; i < aProps.length; i++) {
        let propName = aProps[i];
        if (!attrs2[propName]) {
            continue;
        }
        if (attrs1[propName] !== attrs2[propName]) {
            return false;
        }
    }
    return true;
};
viwpvs_get_variations.prototype.set_add_to_cart = function (variation_id, swatches) {
    swatches.find('.variation_id').val(variation_id);
};
viwpvs_get_variations.prototype.get_img_product = function (swatches, hover = false) {
    let product = swatches.closest('.product'), img_product;
    if (!product.length){//Infinite
        product =  swatches.closest('.gdlr-core-item-list');
    }
    if (!product.length){//Zella
        product = swatches.closest('.product-warp-item');
    }
    if (hover && window.outerWidth > 549 && product.find('img.show-on-hover').length) {//flatsome theme
        img_product = product.find('img.show-on-hover').first();
    } else if (product.find('.attachment-shop_catalog').length) {//Zella ,Skudmart 1.0.6
        img_product = product.find('.attachment-shop_catalog').first();
    } else if (product.find('.gdlr-core-product-thumbnail').length) {//Infinite
        img_product = product.find('.gdlr-core-product-thumbnail').first();
    }else if (product.find('.woo-entry-image-main').length) {//ocean
        img_product = product.find('.woo-entry-image-main').first();
    } else if (product.find('.wp-post-image.vi-load').length) { //demo
        img_product = product.find('.wp-post-image.vi-load ').first();
    } else if (product.find('.attachment-woocommerce_thumbnail').length) {
        img_product = product.find('.attachment-woocommerce_thumbnail').first();
    } else if (product.find('.wp-post-image').length) {
        img_product = product.find('.wp-post-image').first();
    }

    if(!img_product){
        if (product && product.find('img')) {
            img_product = product.find('img').first();
        }else{
            img_product = false;
        }
    }
    if (img_product && img_product.find('img').length > 0) {
        img_product = img_product.find('img').first();
    }
    return img_product;
};
jQuery.fn.wpvs_get_variation = function () {
    new viwpvs_get_variations(this);
    return this;
};