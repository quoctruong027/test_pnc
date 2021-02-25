jQuery(document).ready(function () {
    'use strict';
    jQuery('.vi-ui.checkbox').checkbox();
    jQuery('.vi-wcaio-customize-range').each(function () {
        let range_wrap = jQuery(this),
            range = jQuery(this).find('.vi-wcaio-customize-range1');
        let min = range.attr('min') || 0,
            max = range.attr('max') || 0,
            start = range.data('start');
        range.range({
            min: min,
            max: max,
            start: start,
            input: range_wrap.find('.vi-wcaio-customize-range-value'),
            onChange: function (val) {
                let setting = range_wrap.find('.vi-wcaio-customize-range-value').attr('data-customize-setting-link');
                wp.customize(setting, function (e) {
                    e.set(val);
                });
            }
        });
        range_wrap.next('.vi-wcaio-customize-range-min-max').find('.vi-wcaio-customize-range-min').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            range.range('set value', min);
            let setting = range_wrap.find('.vi-wcaio-customize-range-value').attr('data-customize-setting-link');
            wp.customize(setting, function (e) {
                e.set(min);
            });
        });
        range_wrap.next('.vi-wcaio-customize-range-min-max').find('.vi-wcaio-customize-range-max').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            range.range('set value', max);
            let setting = range_wrap.find('.vi-wcaio-customize-range-value').attr('data-customize-setting-link');
            wp.customize(setting, function (e) {
                e.set(max);
            });
        });
        range_wrap.find('.vi-wcaio-customize-range-value').on('change', function () {
            let setting = jQuery(this).attr('data-customize-setting-link'),
                val = parseInt(jQuery(this).val() || 0);
            if (val > parseInt(max)) {
                val = max
            } else if (val < parseInt(min)) {
                val = min;
            }
            range.range('set value', val);
            wp.customize(setting, function (e) {
                e.set(val);
            });
        });
    });
    jQuery('.vi-wcaio-customize-radio').each(function () {
        jQuery(this).buttonset();
        jQuery(this).find('input:radio').on('change', function () {
            let setting = jQuery(this).attr('data-customize-setting-link'),
                val = parseInt(jQuery(this).val() || 0);
            wp.customize(setting, function (e) {
                e.set(val);
            });
        });
    });
    viwcaio_sc_icon_design();
    viwcaio_sc_header_design();
    viwcaio_sc_footer_design();
});

function viwcaio_sc_icon_design() {
    if (jQuery('select[id="_customize-input-woo_cart_all_in_one_params[sc_icon_style]"]').val() === '4') {
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_bg_color"]').addClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_color"]').addClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_border_radius"]').addClass('vi-wcaio-disabled');
    } else {
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_bg_color"]').removeClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_color"]').removeClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_border_radius"]').removeClass('vi-wcaio-disabled');
    }
    jQuery('select[id="_customize-input-woo_cart_all_in_one_params[sc_icon_style]"]').on('change', function () {
        if (jQuery(this).val() === '4') {
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_bg_color"]').addClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_color"]').addClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_border_radius"]').addClass('vi-wcaio-disabled');
        } else {
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_bg_color"]').removeClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_color"]').removeClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_icon_count_border_radius"]').removeClass('vi-wcaio-disabled');
        }
    });
}

function viwcaio_sc_header_design() {
    if (jQuery('input:checkbox[name="woo_cart_all_in_one_params[sc_header_coupon_enable]"]').prop('checked')) {
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_input_radius"]').removeClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_bg_color"]').removeClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_color"]').removeClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_bg_color_hover"]').removeClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_color_hover"]').removeClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_border_radius"]').removeClass('vi-wcaio-disabled');
    } else {
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_input_radius"]').addClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_bg_color"]').addClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_color"]').addClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_bg_color_hover"]').addClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_color_hover"]').addClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_border_radius"]').addClass('vi-wcaio-disabled');
    }
    jQuery('input:checkbox[name="woo_cart_all_in_one_params[sc_header_coupon_enable]"]').on('change', function () {
        if (jQuery(this).prop('checked')) {
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_input_radius"]').removeClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_bg_color"]').removeClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_color"]').removeClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_bg_color_hover"]').removeClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_color_hover"]').removeClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_border_radius"]').removeClass('vi-wcaio-disabled');
        } else {
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_input_radius"]').addClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_bg_color"]').addClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_color"]').addClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_bg_color_hover"]').addClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_color_hover"]').addClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_header_coupon_button_border_radius"]').addClass('vi-wcaio-disabled');
        }
    });
}

function viwcaio_sc_footer_design() {
    if (jQuery('select[id="_customize-input-woo_cart_all_in_one_params[sc_footer_button]"]').val() === 'cart') {
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_footer_bt_cart_text"]').removeClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_footer_bt_checkout_text"]').addClass('vi-wcaio-disabled');
    } else {
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_footer_bt_cart_text"]').addClass('vi-wcaio-disabled');
        jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_footer_bt_checkout_text"]').removeClass('vi-wcaio-disabled');
    }
    jQuery('select[id="_customize-input-woo_cart_all_in_one_params[sc_footer_button]"]').on('change', function () {
        if (jQuery(this).val() === 'cart') {
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_footer_bt_cart_text"]').removeClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_footer_bt_checkout_text"]').addClass('vi-wcaio-disabled');
        } else {
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_footer_bt_cart_text"]').addClass('vi-wcaio-disabled');
            jQuery('li[id="customize-control-woo_cart_all_in_one_params-sc_footer_bt_checkout_text"]').removeClass('vi-wcaio-disabled');
        }
    });
}
