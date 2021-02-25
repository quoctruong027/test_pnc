<?php
defined( 'ABSPATH' ) || exit;

$config             = array();
$config['slug']     = '_xlwcty_order_details';
$config['title']    = 'Order Details';
$config['instance'] = require( __DIR__ . '/instance.php' );
$config['fields']   = array(
	'id'                     => $config['slug'],
	'xlwcty_accordion_title' => $config['title'],
	'xlwcty_icon'            => 'xlwcty-fa xlwcty-fa-shopping-cart',
	'position'               => 5,
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
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'type'        => 'text',
			'row_classes' => array( 'xlwcty_no_border' ),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Heading font size', 'thank-you-page-for-woocommerce-nextmove' ),
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
			'name'       => __( 'Layout', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'         => $config['slug'] . '_layout',
			'type'       => 'radio_inline',
			'options'    => array(
				'two_column' => __( 'Advanced', 'thank-you-page-for-woocommerce-nextmove' ),
				'default'    => __( 'Native WooCommerce', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes' => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'       => __( 'Display Downloads', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'         => $config['slug'] . '_downloads',
			'type'       => 'radio_inline',
			'options'    => array(
				'section' => __( 'Section (Below order details)', 'thank-you-page-for-woocommerce-nextmove' ),
				'inline'  => __( 'Inline (Below each order line item)', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes' => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'       => __( 'Display Images', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'         => $config['slug'] . '_display_images',
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
			'name'        => __( 'Description below order table', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_below_order_table_desc',
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
			'id'          => $config['slug'] . '_below_order_table_desc_alignment',
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
	'heading'              => __( 'Order details', 'thank-you-page-for-woocommerce-nextmove' ),
	'heading_font_size'    => '20',
	'heading_alignment'    => 'left',
	'layout'               => 'two_column',
	'downloads'            => 'section',
	'display_images'       => 'yes',
	'desc'                 => '',
	'desc_alignment'       => 'left',
	'after_desc'           => '',
	'after_desc_alignment' => 'left',
	'border_style'         => 'solid',
	'border_width'         => '1',
	'border_color'         => '#d9d9d9',
	'component_bg_color'   => '#ffffff',
);

return $config;
