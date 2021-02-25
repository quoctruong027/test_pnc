<?php
defined( 'ABSPATH' ) || exit;

$config             = array();
$config['slug']     = '_xlwcty_customer_information';
$config['title']    = __( 'Customer Details', 'thank-you-page-for-woocommerce-nextmove' );
$config['instance'] = require( __DIR__ . '/instance.php' );
$config['fields']   = array(
	'id'                     => $config['slug'],
	'xlwcty_accordion_title' => $config['title'],
	'xlwcty_icon'            => 'xlwcty-fa xlwcty-fa-user-circle-o',
	'position'               => 10,
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
			'xlwcty_component'           => $config['slug'],
			'xlwcty_is_accordion_opened' => false,
			'after'                      => include_once __DIR__ . '/help.php',
		),
		array(
			'name'        => __( 'Heading', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_heading',
			'type'        => 'text',
			'row_classes' => array( 'xlwcty_no_border' ),
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
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
			'id'          => $config['slug'] . '_after_heading_desc',
			'type'        => 'textarea_small',
			'row_classes' => array( 'xlwcty_no_border' ),
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Description alignment', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_after_heading_desc_alignment',
			'type'        => 'select',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_pt0', 'xlwcty_select_small' ),
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
			'name'        => __( 'Show', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_show_shipping',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_combine_2_field_start' ),
			'before'      => '<p>Shipping Address</p>',
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
			'name'        => __( 'Billing Address', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_show_billing',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_combine_2_field_end' ),
			'before'      => '<p>Billing Address</p>',
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
			'name'        => __( 'Layout', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_layout',
			'type'        => 'radio_inline',
			'row_classes' => array( '' ),
			'options'     => array(
				'2c'         => __( 'Two Column', 'thank-you-page-for-woocommerce-nextmove' ),
				'full_width' => __( 'Full Width', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Description after customer information', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_after_customer_information_desc',
			'type'        => 'textarea_small',
			'row_classes' => array( 'xlwcty_no_border' ),
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Description alignment', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_after_customer_information_desc_alignment',
			'type'        => 'select',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_pt0', 'xlwcty_select_small' ),
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
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_combine_3_field_end' ),
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
			'name'        => __( 'Hide on', 'thank-you-page-for-woocommerce-nextmove' ),
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
			'name'        => __( 'Hide on', 'thank-you-page-for-woocommerce-nextmove' ),
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
	'heading'              => __( 'Customer Information', 'thank-you-page-for-woocommerce-nextmove' ),
	'heading_font_size'    => '20',
	'heading_alignment'    => 'left',
	'layout'               => '2c',
	'show_shipping'        => 'yes',
	'show_billing'         => 'yes',
	'border_style'         => 'solid',
	'border_width'         => '1',
	'desc'                 => '',
	'desc_alignment'       => 'left',
	'after_desc'           => '',
	'after_desc_alignment' => 'left',
	'border_color'         => '#d9d9d9',
	'component_bg_color'   => '#ffffff',
);

return $config;
