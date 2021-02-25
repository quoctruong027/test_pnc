'use strict';
jQuery(document).ready(function ($) {
    jQuery('.vi-wpvs-variation-wrap-wc-widget:not(.vi-wpvs-variation-wrap-wc-widget-check)').each(function () {
        jQuery(this).addClass('vi-wpvs-variation-wrap-wc-widget-check').viwpvs_woo_widget_swatches();
    });
    jQuery(document).ajaxComplete(function (event, jqxhr, settings) {
        jQuery('.vi-wpvs-variation-wrap-wc-widget:not(.vi-wpvs-variation-wrap-wc-widget-check)').each(function () {
            jQuery(this).addClass('vi-wpvs-variation-wrap-wc-widget-check').viwpvs_woo_widget_swatches();
        });
        return false;
    });
});
jQuery(window).load(function () {
    jQuery('.vi-wpvs-variation-wrap-wc-widget:not(.vi-wpvs-variation-wrap-wc-widget-check)').each(function () {
        jQuery(this).addClass('vi-wpvs-variation-wrap-wc-widget-check').viwpvs_woo_widget_swatches();
    });
});
let viwpvs_woo_widget = function ($swatches) {
    $swatches = jQuery($swatches);
    let $wrap = $swatches.closest('.wc-layered-nav-term');
    $wrap.addClass('vi-wpvs-wc-layered-nav-term');
    $wrap.parent().addClass('vi-wpvs-woocommerce-widget-layered-nav-list');
    if ($wrap.hasClass('chosen')) {
        $swatches.find('.vi-wpvs-option-wrap').removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-selected');
    }
    $wrap.on('mouseenter', function () {
        jQuery(this).addClass('vi-wpvs-wc-layered-nav-term-hover');
        if (!$swatches.find('.vi-wpvs-option-wrap').hasClass('vi-wpvs-option-wrap-selected')) {
            $swatches.find('.vi-wpvs-option-wrap').removeClass('vi-wpvs-option-wrap-default').addClass('vi-wpvs-option-wrap-hover');
        }
    }).on('mouseleave', function () {
        jQuery(this).removeClass('vi-wpvs-wc-layered-nav-term-hover');
        if (!$swatches.find('.vi-wpvs-option-wrap').hasClass('vi-wpvs-option-wrap-selected')) {
            $swatches.find('.vi-wpvs-option-wrap').removeClass('vi-wpvs-option-wrap-hover').addClass('vi-wpvs-option-wrap-default');
        }
    });
    $swatches.find('.vi-wpvs-option.vi-wpvs-option-color').each(function (color_item_k, color_item) {
        let colors = jQuery(color_item).data('option_color');
        jQuery(color_item).css({background: colors});
    });
    $swatches.find('.vi-wpvs-option-tooltip.vi-wpvs-option-tooltip-top,.vi-wpvs-option-tooltip.vi-wpvs-option-tooltip-bottom').each(function () {
        let tooltip_width = jQuery(this).outerWidth();
        jQuery(this).css({'margin-left': ('-' + (tooltip_width / 2) + 'px')});
    });
    $swatches.find('.vi-wpvs-option-tooltip.vi-wpvs-option-tooltip-left,.vi-wpvs-option-tooltip.vi-wpvs-option-tooltip-right').each(function () {
        let tooltip_width = jQuery(this).outerHeight();
        jQuery(this).css({'margin-top': ('-' + (tooltip_width / 2) + 'px')});
    });
};
jQuery.fn.viwpvs_woo_widget_swatches = function () {
    new viwpvs_woo_widget(this);
    return this;
};