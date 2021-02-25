<?php

$config             = array();
$config['slug']     = '_xlwcty_track_order';
$config['title']    = 'Order Timeline';
$config['instance'] = require( __DIR__ . '/instance.php' );

/**
 * Track your order component fields
 */
$config['fields'] = array(
	'id'                     => $config['slug'],
	'xlwcty_accordion_title' => $config['title'],
	'xlwcty_icon'            => 'xlwcty-fa xlwcty-fa-list-ul',
	'position'               => 12,
	'fields'                 => array(
		array(
			'name'                       => __( 'Enable', 'nextmove-power-pack' ),
			'id'                         => $config['slug'] . '_enable',
			'type'                       => 'xlwcty_switch',
			'row_classes'                => array( 'xlwcty_is_enable' ),
			'label'                      => array(
				'on'  => __( 'Yes', 'nextmove-power-pack' ),
				'off' => __( 'No', 'nextmove-power-pack' ),
			),
			'before_row'                 => array( 'XLWCTY_Admin_CMB2_Support', 'cmb_before_row_cb' ),
			'xlwcty_accordion_title'     => $config['title'],
			'xlwcty_component'           => $config['slug'],
			'xlwcty_is_accordion_opened' => false,
		),

		array(
			'name'        => __( 'Heading', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_heading',
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'type'        => 'text',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_border_top' ),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Heading Alignment', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_heading_alignment',
			'type'        => 'select',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_combine_3_field_start', 'xlwcty_pt0', 'xlwcty_select_small' ),
			'before'      => '<p>' . __( 'Alignment', 'nextmove-power-pack' ) . '</p>',
			'options'     => array(
				'left'   => __( 'Left', 'nextmove-power-pack' ),
				'center' => __( 'Center', 'nextmove-power-pack' ),
				'right'  => __( 'Right', 'nextmove-power-pack' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Heading font size', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_heading_font_size',
			'type'        => 'text_small',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_combine_3_field_middle', 'xlwcty_pt0' ),
			'before'      => '<p>' . __( 'Font Size', 'nextmove-power-pack' ) . '</p>',
			'attributes'  => array(
				'type'                   => 'number',
				'min'                    => '0',
				'pattern'                => '\d*',
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Heading Color', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_heading_color',
			'type'        => 'colorpicker',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_combine_3_field_end', 'xlwcty_pt0' ),
			'before'      => '<p>' . __( 'Color', 'nextmove-power-pack' ) . '</p>',
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),

		array(
			'name'        => __( 'Show Order Creation Date in timeline', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_order_creation',
			'type'        => 'radio_inline',
			'options'     => array(
				'yes' => __( 'Show', 'nextmove-power-pack' ),
				'no'  => __( 'Hide', 'nextmove-power-pack' ),
			),
			'row_classes' => array(),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),

		array(
			'name'        => __( 'Show Customer Notes in timeline', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_customer_note',
			'type'        => 'radio_inline',
			'options'     => array(
				'yes' => __( 'Show', 'nextmove-power-pack' ),
				'no'  => __( 'Hide', 'nextmove-power-pack' ),
			),
			'row_classes' => array(),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),

		array(
			'name'        => __( 'Show Notes from Order Status change', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_order_status',
			'type'        => 'radio_inline',
			'options'     => array(
				'yes' => __( 'Show', 'nextmove-power-pack' ),
				'no'  => __( 'Hide', 'nextmove-power-pack' ),
			),
			'row_classes' => array( 'xlwcty_no_border' ),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Order Content', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_order_content',
			'type'        => 'radio_inline',
			'before'      => '<p>' . __( 'Content Type', 'nextmove-power-pack' ) . '</p>',
			'options'     => array(
				'custom' => __( 'Custom Content', 'nextmove-power-pack' ),
				'full'   => __( 'Order Actual Content', 'nextmove-power-pack' ),
			),
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_order_status',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'        => __( 'Custom Content', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_custom_content',
			'desc'        => '<p>' . __( 'You can use {{current_status}} merge tags.', 'nextmove-power-pack' ) . '</p>',
			'type'        => 'text',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_order_status',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),
		array(
			'name'              => __( 'Hide order\'s notes of following statuses', 'nextmove-power-pack' ),
			'id'                => $config['slug'] . '_hide_status',
			'row_classes'       => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'type'              => 'multicheck_inline',
			'before'            => '<p>' . __( 'Hide the following statuses', 'nextmove-power-pack' ) . '</p>',
			'options_cb'        => array( 'XLWCTY_PP_Common', 'get_wc_order_statuses' ),
			'select_all_button' => false,
			'attributes'        => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_order_status',
				'data-xlwcty-conditional-value' => 'yes',
			),
		),

		array(
			'name'        => __( 'Border', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_border_style',
			'type'        => 'select',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_border_top', 'xlwcty_select_small', 'xlwcty_combine_3_field_start' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">' . __( 'Style', 'nextmove-power-pack' ) . '</p>',
			'options'     => array(
				'dotted' => __( 'Dotted', 'nextmove-power-pack' ),
				'dashed' => __( 'Dashed', 'nextmove-power-pack' ),
				'solid'  => __( 'Solid', 'nextmove-power-pack' ),
				'double' => __( 'Double', 'nextmove-power-pack' ),
				'none'   => __( 'None', 'nextmove-power-pack' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Border Width', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_border_width',
			'type'        => 'text_small',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_border_top', 'xlwcty_hide_label', 'xlwcty_combine_3_field_middle' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">' . __( 'Width (px)', 'nextmove-power-pack' ) . '</p>',
			'attributes'  => array(
				'type'                   => 'number',
				'min'                    => '0',
				'pattern'                => '\d*',
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Border Color', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_border_color',
			'type'        => 'colorpicker',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_border_top', 'xlwcty_combine_3_field_end' ),
			'before'      => '<p class="xlwcty_mt5 xlwcty_mb5">' . __( 'Color', 'nextmove-power-pack' ) . '</p>',
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),

		array(
			'name'        => __( 'Background', 'nextmove-power-pack' ),
			'desc'        => __( 'Component background color', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_component_bg_color',
			'type'        => 'colorpicker',
			'row_classes' => array(),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),

		array(
			'name'        => __( 'Hide on', 'nextmove-power-pack' ),
			'desc'        => __( 'Desktop', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_hide_desktop',
			'type'        => 'checkbox',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_combine_2_field_start' ),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Hide on', 'nextmove-power-pack' ),
			'desc'        => __( 'Mobile', 'nextmove-power-pack' ),
			'id'          => $config['slug'] . '_hide_mobile',
			'type'        => 'checkbox',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_combine_2_field_end' ),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),

		array(
			'name'              => __( 'Hide for following Order Status', 'nextmove-power-pack' ),
			'desc'              => __( 'Check order statuses where you want to hide this component.', 'nextmove-power-pack' ),
			'id'                => $config['slug'] . '_hide_order_status',
			'type'              => 'multicheck_inline',
			'options_cb'        => array( 'XLWCTY_PP_Common', 'get_wc_order_statuses' ),
			'row_classes'       => array( 'xlwcty_border_top' ),
			'select_all_button' => false,
			'attributes'        => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
			'after_row'         => array( 'XLWCTY_Admin_CMB2_Support', 'cmb_after_row_cb' ),
		),
	),
);

/**
 * Track your order component field's default values
 */
$config['default'] = array(
	'heading'            => __( 'Track Your Order', 'nextmove-power-pack' ),
	'heading_font_size'  => '20',
	'heading_alignment'  => 'left',
	'heading_color'      => '#000',
	'order_creation'     => 'yes',
	'customer_note'      => 'yes',
	'order_status'       => 'yes',
	'order_content'      => 'custom',
	'custom_content'     => 'Order Status changed to {{current_status}}',
	'hide_status'        => array(),
	'border_style'       => 'solid',
	'border_width'       => '1',
	'border_color'       => '#d9d9d9',
	'component_bg_color' => '#ffffff',
);

return $config;
