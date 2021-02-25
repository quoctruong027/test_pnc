<?php
defined( 'ABSPATH' ) || exit;

$config             = array();
$config['slug']     = '_xlwcty_social_coupons';
$config['title']    = 'Smart Bribe';
$config['instance'] = require( __DIR__ . '/instance.php' );
$config['fields']   = array(
	'id'                     => $config['slug'],
	'position'               => 90,
	'xlwcty_accordion_title' => $config['title'],
	'xlwcty_icon'            => 'xlwcty-fa xlwcty-fa-rocket',
	'fields'                 => array(
		array(
			'name'                       => __( 'Enable', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'                         => $config['slug'] . '_enable',
			'type'                       => 'xlwcty_switch',
			'row_classes'                => array( 'xlwcty_is_enable' ),
			'label'                      => array(
				'on'  => __( 'Yes', 'thank-you-page-for-woocommerce-nextmove' ),
				'off' => __( 'No', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'before_row'                 => array( 'XLWCTY_Admin_CMB2_Support', 'cmb_before_row_cb' ),
			'xlwcty_accordion_title'     => $config['title'],
			'xlwcty_error_notice'        => array(
				'key'   => 'fb_app_id',
				'error' => 'Facebook APP ID is required. <a target="_blank" href="' . admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you&section=settings' ) . '">Click here</a> to enter the APP ID.',
				'value' => '',
			),
			'xlwcty_component'           => $config['slug'],
			'xlwcty_is_accordion_opened' => false,
			'after'                      => include_once __DIR__ . '/help.php',
		),
		array(
			'name'        => __( 'Heading', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_heading',
			'type'        => 'text',
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'row_classes' => array( 'xlwcty_no_border' ),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Heading Font Size', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_heading_font_size',
			'type'        => 'text_small',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_pt0', 'xlwcty_combine_2_field_start' ),
			'before'      => '<p>Font Size (px)</p>',
			'attributes'  => array(
				'type'                   => 'number',
				'min'                    => '0',
				'pattern'                => '\d*',
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Heading alignment', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_heading_alignment',
			'type'        => 'select',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_pt0', 'xlwcty_combine_2_field_end' ),
			'before'      => '<p>Alignment</p>',
			'options'     => array(
				'left'   => __( 'Left', 'thank-you-page-for-woocommerce-nextmove' ),
				'center' => __( 'Center', 'thank-you-page-for-woocommerce-nextmove' ),
				'right'  => __( 'Right', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Description', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_desc',
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'type'        => 'textarea_small',
			'row_classes' => array( 'xlwcty_no_border' ),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Description alignment', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_desc_alignment',
			'type'        => 'select',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_pt0', 'xlwcty_select_small' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Alignment</p>',
			'options'     => array(
				'left'   => __( 'Left', 'thank-you-page-for-woocommerce-nextmove' ),
				'center' => __( 'Center', 'thank-you-page-for-woocommerce-nextmove' ),
				'right'  => __( 'Right', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Enable Facebook Like', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_fb_like',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_no_border' ),
			'options'     => array(
				'yes' => __( 'Yes', 'thank-you-page-for-woocommerce-nextmove' ),
				'no'  => __( 'No', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Website Page Link', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_fb',
			'type'        => 'text',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p>' . __( 'Website URL', 'thank-you-page-for-woocommerce-nextmove' ) . '</p>',
			'after'       => '<p>' . __( 'You can enter any URL of this site here. <br>Note: Due to recent changes in Facebook policy external URLs such as Facebook page URL won\'t work here.', 'thank-you-page-for-woocommerce-nextmove' ) . '</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_fb_like',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Like Button Text', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_like_btn_text',
			'type'        => 'text',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p>Button Text</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_fb_like',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Enable Facebook Share', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_fb_btn',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_border_top', 'xlwcty_no_border' ),
			'options'     => array(
				'yes' => __( 'Yes', 'thank-you-page-for-woocommerce-nextmove' ),
				'no'  => __( 'No', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Share Button Text', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_share_btn_text',
			'type'        => 'text',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p>Button Text</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_fb_btn',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Share Text', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_share_text',
			'type'        => 'textarea_small',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'before'      => '<p>Message</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_fb_btn',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Share URL', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_share_link',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'options'     => array(
				'order_first' => __( 'Product\'s Purchased (Highest Value)', 'thank-you-page-for-woocommerce-nextmove' ),
				'custom'      => __( 'Custom page', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'before'      => '<p>Share Link</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_fb_btn',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Custom Link', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_share_custom_link',
			'type'        => 'text',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p>' . __( 'Website URL', 'thank-you-page-for-woocommerce-nextmove' ) . '</p>',
			'after'       => '<p>' . __( 'You can enter any URL of this site here. <br>Note: Due to recent changes in Facebook policy external URLs such as Facebook page URL won\'t work here.', 'thank-you-page-for-woocommerce-nextmove' ) . '</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_fb_btn',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Reveal Coupon after Like or Share', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_locked_coupon',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_border_top' ),
			'options'     => array(
				'yes' => __( 'Yes', 'thank-you-page-for-woocommerce-nextmove' ),
				'no'  => __( 'No', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'id'               => $config['slug'] . '_select',
			'name'             => __( 'Select Coupon', 'thank-you-page-for-woocommerce-nextmove' ),
			'type'             => 'select',
			'desc'             => __( 'Don\'t forget to check this coupon\'s <a href="{coupon_link}">usage restrictions</a>. NextMove does not restrict coupons based on campaign rules. This responsibility lies with native coupon settings.', 'thank-you-page-for-woocommerce-nextmove' ),
			'row_classes'      => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_cmb2_chosen', 'xlwcty_cmb2_coupon', 'xlwcty_pt0' ),
			'before'           => '<p class="xlwcty_mt5 xlwcty_mb5">This would only work when one of the features Facebook Share or Like is enabled.<br/><br/>Select Coupon</p>',
			'show_option_none' => __( 'Choose a Coupon', 'thank-you-page-for-woocommerce-nextmove' ),
			'options_cb'       => array( 'XLWCTY_Admin_CMB2_Support', 'get_coupons_selected' ),
			'attributes'       => array(
				'data-pre-data'                 => wp_json_encode( xlwcty_Common::get_coupons( true ) ),
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_locked_coupon',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Personalize Coupon', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_personalize',
			'type'        => 'radio_inline',
			'options'     => array(
				'yes' => __( 'Yes', 'thank-you-page-for-woocommerce-nextmove' ),
				'no'  => __( 'No', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Personalize Coupon</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_locked_coupon',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Coupon Format', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_format',
			'type'        => 'text',
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Format</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_locked_coupon',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Coupon Expiry', 'thank-you-page-for-woocommerce-nextmove' ),
			'desc'        => __( 'days. This will set a new expiry date of personalised coupon based on order date. If left blank, expiry date of selected coupon will be used if available otherwise no expiry.', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_expiry',
			'type'        => 'text_small',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Expiry</p>',
			'attributes'  => array(
				'type'                          => 'number',
				'min'                           => '1',
				'pattern'                       => '\d*',
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_locked_coupon',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Coupon Font Size', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_font_size',
			'type'        => 'text_small',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_combine_2_field_start', 'xlwcty_pt0' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Font Size (px)</p>',
			'attributes'  => array(
				'type'                          => 'number',
				'min'                           => '0',
				'pattern'                       => '\d*',
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_locked_coupon',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Coupon Text/ Border Color', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_color',
			'type'        => 'colorpicker',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_combine_2_field_end', 'xlwcty_pt0' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Text/ BG Color</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_locked_coupon',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Description after coupon displayed', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_desc_after_click',
			'type'        => 'textarea_small',
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Description after coupon displayed</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_locked_coupon',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Border', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_border_style',
			'type'        => 'select',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_select_small', 'xlwcty_combine_3_field_start' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Style</p>',
			'options'     => array(
				'dotted' => __( 'Dotted', 'thank-you-page-for-woocommerce-nextmove' ),
				'dashed' => __( 'Dashed', 'thank-you-page-for-woocommerce-nextmove' ),
				'solid'  => __( 'Solid', 'thank-you-page-for-woocommerce-nextmove' ),
				'double' => __( 'Double', 'thank-you-page-for-woocommerce-nextmove' ),
				'none'   => __( 'None', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Border Width', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_border_width',
			'type'        => 'text_small',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_combine_3_field_middle' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Width (px)</p>',
			'attributes'  => array(
				'type'                   => 'number',
				'min'                    => '0',
				'pattern'                => '\d*',
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Border Color', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_border_color',
			'type'        => 'colorpicker',
			'row_classes' => array( 'xlwcty_border_top', 'xlwcty_hide_label', 'xlwcty_combine_3_field_end' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">Color</p>',
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Background', 'thank-you-page-for-woocommerce-nextmove' ),
			'desc'        => __( 'Component background color', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_component_bg',
			'type'        => 'colorpicker',
			'row_classes' => array(),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'       => __( 'Hide for Repeat Customers', 'thank-you-page-for-woocommerce-nextmove' ),
			'desc'       => __( 'If set to "Yes", smart bribe component won\'t be displayed to customer if she has more than one order.', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'         => $config['slug'] . '_hide_for_repeat_customers',
			'type'       => 'radio_inline',
			'options'    => array(
				'yes' => __( 'Yes', 'thank-you-page-for-woocommerce-nextmove' ),
				'no'  => __( 'No', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes' => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Hide on Device', 'thank-you-page-for-woocommerce-nextmove' ),
			'desc'        => __( 'Desktop', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_hide_desktop',
			'type'        => 'checkbox',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_combine_2_field_start' ),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Hide on Device', 'thank-you-page-for-woocommerce-nextmove' ),
			'desc'        => __( 'Mobile', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_hide_mobile',
			'type'        => 'checkbox',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_combine_2_field_end' ),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
			'after_row'   => array( 'XLWCTY_Admin_CMB2_Support', 'cmb_after_row_cb' ),
		),
	),
);
$config['default']  = array(
	'heading'                   => __( "You're this close to unlocking this awesome coupon code. Tell your friends about us and unlock now.", 'thank-you-page-for-woocommerce-nextmove' ),
	'heading_font_size'         => '20',
	'heading_alignment'         => 'center',
	'desc'                      => __( 'Help your friends discover us and snag the items you love at a surprise discount.', 'thank-you-page-for-woocommerce-nextmove' ),
	'desc_alignment'            => 'center',
	'fb_like'                   => 'yes',
	'fb'                        => '',
	'like_btn_text'             => 'Like',
	'fb_share'                  => 'yes',
	'fb_share_link'             => 'order_first',
	'fb_share_cust_link'        => '',
	'fb_share_text'             => __( "Hey! I just discovered this amazing store, bought some items for myself and loved the options. If you're out on the market for one, you may like to have a look.", 'thank-you-page-for-woocommerce-nextmove' ),
	'share_btn_text'            => 'Share',
	'btn_font_size'             => '20',
	'btn_color'                 => '#ffffff',
	'btn_bg_color'              => '#0978d8',
	'locked_coupon'             => 'no',
	'selected_coupon'           => '',
	'personalize'               => 'no',
	'format'                    => '',
	'exp_days'                  => '',
	'format_font'               => '26',
	'format_color'              => '#1291ff',
	'desc_after_click'          => __( "You're awesome! Here you go: Use this coupon code at the checkout to get {{coupon_value}} off.", 'thank-you-page-for-woocommerce-nextmove' ),
	'border_style'              => 'solid',
	'border_width'              => '1',
	'border_color'              => '#d9d9d9',
	'component_bg_color'        => '#ffffff',
	'hide_for_repeat_customers' => 'no',
);

return $config;