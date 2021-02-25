<?php
defined( 'ABSPATH' ) || exit;

$config             = array();
$config['slug']     = '_xlwcty_related_product';
$config['title']    = 'Related Products';
$config['instance'] = require( __DIR__ . '/instance.php' );
$config['fields']   = array(
	'id'                     => $config['slug'],
	'position'               => 50,
	'xlwcty_accordion_title' => $config['title'],
	'xlwcty_icon'            => 'xlwcty-fa xlwcty-fa-th-large',
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
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
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
			'id'          => $config['slug'] . '_desc',
			'type'        => 'textarea_small',
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
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
			'name'        => __( 'Layout', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_layout',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_no_border' ),
			'options'     => array(
				'grid'        => __( 'Grid (advanced)', 'thank-you-page-for-woocommerce-nextmove' ),
				'grid_native' => __( 'Grid (theme)', 'thank-you-page-for-woocommerce-nextmove' ),
				'list'        => __( 'List', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Grid Size', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_grid_type',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p>Grid Size</p>',
			'options'     => array(
				'2c' => __( '2 Column', 'thank-you-page-for-woocommerce-nextmove' ),
				'3c' => __( '3 Column', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_layout',
				'data-xlwcty-conditional-value' => 'grid',
			),
		),
		array(
			'name'        => __( 'Display', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_display_count',
			'type'        => 'text_small',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_combine_2_field_start' ),
			'before'      => '<p>Count</p>',
			'attributes'  => array(
				'type'                   => 'number',
				'min'                    => '0',
				'pattern'                => '\d*',
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Display Ratings', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_display_rating',
			'type'        => 'radio_inline',
			'options'     => array(
				'yes' => __( 'Yes', 'thank-you-page-for-woocommerce-nextmove' ),
				'no'  => __( 'No', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'row_classes' => array( 'xlwcty_border_top', 'xlwcty_combine_2_field_end', 'xlwcty_hide_label' ),
			'before'      => '<p>Ratings</p>',
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
	'heading'            => '',
	'heading_font_size'  => '20',
	'heading_alignment'  => 'left',
	'desc'               => '',
	'desc_alignment'     => 'left',
	'layout'             => 'grid',
	'grid_type'          => '3c',
	'display_count'      => '3',
	'display_rating'     => 'no',
	'border_style'       => 'solid',
	'border_width'       => '1',
	'border_color'       => '#d9d9d9',
	'component_bg_color' => '#ffffff',
);

return $config;
