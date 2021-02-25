jQuery(document).ready(function ($) {
    /**
     * Show or hide order status content field in track order component
     */
    show_hide_custom_order_status_content_field();
    $('.cmb2-radio-list.cmb2-list').on('click', function () {
        show_hide_custom_order_status_content_field();
    });

    /**
     * Function to show or hide the order status content field in track order component
     */
    function show_hide_custom_order_status_content_field() {
        var check = 0;
        if ($('.cmb2-id--xlwcty-track-order-enable .cmb2-switch label[for="_xlwcty_track_order_enable1"].cmb2-enable').hasClass('selected')) {
            var val = $('.cmb2-id--xlwcty-track-order-order-status input[name="_xlwcty_track_order_order_status"]:checked').val();
            if ('yes' == val) {
                var content = $('.cmb2-id--xlwcty-track-order-order-content input[name="_xlwcty_track_order_order_content"]:checked').val();
                if ('custom' == content) {
                    check = 1;
                }
            }
        }
        if (1 == check) {
            $('.cmb2-id--xlwcty-track-order-custom-content').show();
        } else {
            $('.cmb2-id--xlwcty-track-order-custom-content').hide();
        }
    }

    /**
     *
     * @type {*|jQuery|HTMLElement}
     *
     * Check if power pack settings page element available
     */
    var check_power_pack_settings_element = $('.xlwcty_global_option .xlwcty-power-pack-wrap');
    if (!check_power_pack_settings_element.length) {
        return;
    }

    /**
     * Set template when document is loaded
     */
    xlwcty_render_track_link_preview();
    xlwcty_render_immediate_coupon_preview();
    xlwcty_render_lock_coupon_preview();

    /**
     * Detect changes of track order email button text field
     */
    $('body').on('keyup', '#append_user_track_order_email_button_text', function () {
        xlwcty_render_track_link_preview();
    });

    /**
     * Detect changes of immediate coupon text fields
     */
    $('body').on('keyup', '#append_coupons_in_email_heading,#append_coupons_in_email_desc,#append_coupons_in_email_btn_text,#append_coupons_in_email_btn_link', function () {
        xlwcty_render_immediate_coupon_preview();
    });

    /**
     * Detect changes of lock coupon text fields
     */
    $('body').on('keyup', '#append_coupons_in_email_heading_lock,#append_coupons_in_email_desc_lock,#append_coupons_in_email_btn_text_lock', function () {
        xlwcty_render_lock_coupon_preview();
    });

    /**
     *
     * @type {{change: change}}
     *
     * Change the colors on the preview when the color picker color is changed
     */
    var change_track_coupon_colors = {
        change: function (event, ui) {
            xlwcty_render_track_link_preview();
            xlwcty_render_immediate_coupon_preview();
            xlwcty_render_lock_coupon_preview();
        }
    };

    $('.xlwcty_change_event input.cmb2-colorpicker').wpColorPicker(change_track_coupon_colors);

    /**
     * Function to change the track link preview template
     */
    function xlwcty_render_track_link_preview() {
        var track_link_template = wp.template('track-link-template');
        $('.xlwcty-power-pack-track-link-preview').html(track_link_template({
            section_bg_color: $('#append_user_track_order_email_section_bg_color').val(),
            bg_color: $('#append_user_track_order_email_bg_color').val(),
            text_color: $('#append_user_track_order_email_text_color').val(),
            button_text: $('#append_user_track_order_email_button_text').val(),
        }));
    }

    /**
     * Function to change the immediate coupon preview template
     */
    function xlwcty_render_immediate_coupon_preview() {
        var immediate_coupon_template = wp.template('immediate-coupon-template');
        $('.xlwcty-power-pack-immediate-coupon-preview').html(immediate_coupon_template({
            section_color: $('#append_coupons_in_email_section_bg_color').val(),
            heading_color: $('#append_coupons_in_email_heading_color').val(),
            heading: $('#append_coupons_in_email_heading').val(),
            content_color: $('#append_coupons_in_email_content_color').val(),
            content: $('#append_coupons_in_email_desc').val(),
            coupon_bg_color: $('#append_coupons_in_email_coupon_bg_color').val(),
            coupon_border_color: $('#append_coupons_in_email_coupon_border_color').val(),
            coupon_text_color: $('#append_coupons_in_email_coupon_text_color').val(),
            coupon_code: 'WELCOME-JOHN',
            btn_link: $('#append_coupons_in_email_btn_link').val(),
            button_text_color: $('#append_coupons_in_email_coupon_button_text_color').val(),
            button_bg_color: $('#append_coupons_in_email_coupon_button_bg_color').val(),
            btn_txt: $('#append_coupons_in_email_btn_text').val(),
        }));
    }

    /**
     * Function to change the lock coupon preview template
     */
    function xlwcty_render_lock_coupon_preview() {
        var lock_coupon_template = wp.template('lock-coupon-template');
        $('.xlwcty-power-pack-lock-coupon-preview').html(lock_coupon_template({
            section_color_lock: $('#append_coupons_in_email_section_bg_color').val(),
            heading_color_lock: $('#append_coupons_in_email_heading_color').val(),
            heading_lock: $('#append_coupons_in_email_heading_lock').val(),
            content_color_lock: $('#append_coupons_in_email_content_color').val(),
            content_lock: $('#append_coupons_in_email_desc_lock').val(),
            coupon_bg_color_lock: $('#append_coupons_in_email_coupon_bg_color').val(),
            coupon_border_color_lock: $('#append_coupons_in_email_coupon_border_color').val(),
            coupon_text_color_lock: $('#append_coupons_in_email_coupon_text_color').val(),
            coupon_code_lock: 'WELCOME-JOHN',
            btn_link_lock: $('#append_coupons_in_email_btn_link').val(),
            button_text_color_lock: $('#append_coupons_in_email_coupon_button_text_color').val(),
            button_bg_color_lock: $('#append_coupons_in_email_coupon_button_bg_color').val(),
            btn_txt_lock: $('#append_coupons_in_email_btn_text_lock').val(),
        }));
    }

    /**
     * Fix coupon preview until track preview starts
     * @type {(function())|jQuery}
     */
    var coupon_top = $('.xlwcty-power-pack-coupon-wrap').offset().top;
    var receipt_top = $('.order_receipt_link_heading').offset().top;
    var coupon_height = $('.xlwcty-power-pack-coupon-wrap').outerHeight();
    var max_top = receipt_top - (coupon_top + coupon_height);
    max_top = max_top - 126;

    $(document).scroll(function () {
        if (window.pageYOffset >= coupon_top) {
            var px_scroll = window.pageYOffset - coupon_top;
            var px = px_scroll - 135;

            if (px < max_top) {
                $('.xlwcty-power-pack-coupon-wrap').css('top', px);
            } else {
                $('.xlwcty-power-pack-coupon-wrap').css('top', max_top);
            }
        } else {
            $('.xlwcty-power-pack-coupon-wrap').css('top', -135);
        }
    });

});
