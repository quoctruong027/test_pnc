jQuery.fn.viwcaio_get_variations = function (params) {
    new viwcaio_get_variation(this, params);
    return this;
};
var viwcaio_get_variation = function (form, params) {
    this.wc_ajax_url = params.wc_ajax_url || '';
    this.form = form;
    this.product_id = parseInt(form.data('product_id'));
    this.variations = form.data('product_variations') || '';
    this.is_ajax = !this.variations;
    this.xhr = false;
    this.init();
};
viwcaio_get_variation.prototype.init = function () {
    let self = this,
        form = this.form,
        variations = this.variations,
        select = this.form.find('.vi-wcaio-attribute-options'),
        attribute = {},
        count_attr = 0;
    select.each(function () {
        let val = jQuery(this).val();
        if (val) {
            count_attr++;
        }
        attribute[jQuery(this).data('attribute_name')] = val;
    });
    if (count_attr > 0) {
        self.find_variation(attribute, variations, form);
    } else {
        form.find('.vi-wcaio-product-bt-atc').addClass('vi-wcaio-button-swatches-disable');
    }
    select.on('change', function (e) {
        attribute[jQuery(this).data('attribute_name')] = jQuery(this).val();
        self.find_variation(attribute, variations, form);
    });
};
viwcaio_get_variation.prototype.find_variation = function (attrs, variations, form) {
    let self = this, variation, options = [], attrs_name, check_attr_null;
    if (self.is_ajax) {
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
            self.show_variation(self, null, form);
            return false;
        }
        if (self.variations) {
            variations = self.variations;
            jQuery.each(variations, function (key, value) {
                if (self.check_is_equal(attrs, value.attributes)) {
                    variation = value;
                    return false;
                }
            });
            if (variation) {
                self.show_variation(self, variation, form);
            } else {
                if (variations.length < parseInt(form.data('variation_count') || 0)) {
                    self.call_ajax(attrs, variations, form, self);
                } else {
                    self.show_variation(self, variation, form);
                }
            }
        } else {
            variations = [];
            self.call_ajax(attrs, variations, form, self);
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
                form.find('select option').each(function (option_item_k, option_item) {
                    let val = jQuery(option_item).val().toString();
                    if (jQuery.inArray(val, options) === -1) {
                        jQuery(option_item).addClass('vi-wcaio-option-disabled');
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
                        if (i === m) {
                            continue;
                        }
                        let attr_key_t = attrs_name[m];
                        form.find('.vi-wcaio-swatches-wrap select[name="' + attr_key_t + '"] option').removeClass('vi-wcaio-option-disabled');
                    }
                }
                jQuery.each(variations, function (key, val) {
                    if (!val.attributes[attr_key]) {
                        return true;
                    }
                    if (attrs_values.indexOf(val.attributes[attr_key]) === -1) {
                        attrs_values.push(val.attributes[attr_key]);
                    }
                    if (attr_value == val.attributes[attr_key]) {
                        form.find('option[value ="' + val.attributes[attr_key] + '"]').removeClass('vi-wcaio-option-disabled');
                        if (options.indexOf(val.attributes[attr_key]) === -1) {
                            options.push(val.attributes[attr_key]);
                        }
                        for (let j = 0; j < attrs_name.length; j++) {
                            let attr_key_t = attrs_name[j];
                            if (val.attributes[attr_key_t] && attr_key_t !== attr_key) {
                                form.find('option[value ="' + val.attributes[attr_key_t] + '"]').removeClass('vi-wcaio-option-disabled');
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
                            if (attr_value !== '' && attr_value !== null && attr_key_t !== attr_key && options.indexOf(val.attributes[attr_key_t]) === -1) {
                                form.find('option[value ="' + val.attributes[attr_key_t] + '"]').addClass('vi-wcaio-option-disabled');
                            }
                        }
                    }
                });
            }
        }
        if (!check_attr_null) {
            jQuery.each(variations, function (key, value) {
                if (self.check_is_equal(attrs, value.attributes)) {
                    variation = value;
                    return false;
                }
            });
        }
        self.show_variation(self, variation, form);
    }
};
viwcaio_get_variation.prototype.call_ajax = function (attrs, variations, form, self) {
    attrs.product_id = self.product_id;
    self.xhr = jQuery.ajax({
        url: self.wc_ajax_url.toString().replace('%%endpoint%%', 'get_variation'),
        type: 'POST',
        data: attrs,
        beforeSend: function () {
            form.closest('.vi-wcaio-va-cart-form-wrap').addClass('vi-wcaio-container-loading');
        },
        success: function (result) {
            self.show_variation(self, result, form);
            if (result) {
                variations[variations.length || 0] = result;
                self.variations = variations;
            }
            delete attrs.product_id;
        },
        complete: function () {
            form.closest('.vi-wcaio-va-cart-form-wrap').removeClass('vi-wcaio-container-loading');
        }
    });
};
viwcaio_get_variation.prototype.show_variation = function (self, variation, form) {
    if (variation) {
        if (variation.is_in_stock) {
            self.set_add_to_cart(variation.variation_id, form);
            form.find('.vi-wcaio-product-bt-atc').removeClass('vi-wcaio-button-swatches-disable');
        } else {
            self.set_add_to_cart('', form);
            form.find('.vi-wcaio-product-bt-atc').addClass('vi-wcaio-button-swatches-disable');
        }
    } else {
        self.set_add_to_cart('', form);
        form.find('.vi-wcaio-product-bt-atc').addClass('vi-wcaio-button-swatches-disable');
    }
};
viwcaio_get_variation.prototype.set_add_to_cart = function (variation_id, form) {
    variation_id = variation_id || 0;
    form.find('.variation_id').val(variation_id);
};
viwcaio_get_variation.prototype.check_is_equal = function (attrs1, attrs2) {
    let i, aProps = Object.getOwnPropertyNames(attrs1),
        bProps = Object.getOwnPropertyNames(attrs2);
    if (aProps.length !== bProps.length) {
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