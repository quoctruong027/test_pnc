<?php
defined( 'ABSPATH' ) || exit;

$config['slug']     = '_xlwcty_google_map';
$config['title']    = __( 'Location Map', 'thank-you-page-for-woocommerce-nextmove' );
$config['instance'] = require( __DIR__ . '/instance.php' );
$config['fields']   = array(
	'id'       => $config['slug'],
	'position' => 15,

	'xlwcty_accordion_title' => $config['title'],
	'xlwcty_icon'            => 'xlwcty-fa xlwcty-fa-map',
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
				'key'   => 'google_map_api',
				'error' => 'Google Map API Key is required. <a target="_blank" href="' . admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you&section=settings' ) . '">Click here</a> to enter the key.',
				'value' => '',
			),
			'xlwcty_component'           => $config['slug'],
			'xlwcty_is_accordion_opened' => false,
			'after'                      => include_once __DIR__ . '/help.php',
		),
		array(
			'name'        => __( 'Marker Address', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_address',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_no_border' ),
			'options'     => array(
				'shipping' => __( 'Shipping Address', 'thank-you-page-for-woocommerce-nextmove' ),
				'billing'  => __( 'Billing Address', 'thank-you-page-for-woocommerce-nextmove' ),
				'custom'   => __( 'Custom', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Custom Marker Address', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_marker_custom_address',
			'type'        => 'textarea_small',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_no_border', 'xlwcty_pt0' ),
			'before'      => '<p>Custom Address</p>',
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_address',
				'data-xlwcty-conditional-value' => 'custom',
			),
		),
		array(
			'name'        => __( 'Map', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_style',
			'type'        => 'select',
			'options'     => array(
				'standard'     => __( 'Standard', 'thank-you-page-for-woocommerce-nextmove' ),
				'light'        => __( 'Light', 'thank-you-page-for-woocommerce-nextmove' ),
				'grey'         => __( 'Grey', 'thank-you-page-for-woocommerce-nextmove' ),
				'retro'        => __( 'Retro', 'thank-you-page-for-woocommerce-nextmove' ),
				'mid-night'    => __( 'Mid Night', 'thank-you-page-for-woocommerce-nextmove' ),
				'blue-essence' => __( 'Blue Essence', 'thank-you-page-for-woocommerce-nextmove' ),
				'muted-blue'   => __( 'Muted Blue', 'thank-you-page-for-woocommerce-nextmove' ),
				'facebook'     => __( 'Facebook', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_combine_2_field_start', 'xlwcty_select_small' ),
			'before'      => '<p>Style</p>',
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Map Zoom Level', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_zoom_level',
			'type'        => 'text_small',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_combine_2_field_end', 'xlwcty_border_top' ),
			'before'      => '<p>Zoom Level</p>',
			'attributes'  => array(
				'type'                   => 'number',
				'min'                    => '5',
				'max'                    => '22',
				'pattern'                => '\d*',
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Marker Text', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_marker_text',
			'desc'        => '<a href="javascript:void(0);" onclick="xlwcty_show_tb(\'Merge Tags\',\'xlwcty_merge_tags_invenotry_bar_help\');">Dynamic merge tags list</a>',
			'type'        => 'textarea_small',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_no_border', 'xlwcty_pt0' ),
			'before'      => '<p>Marker Text</p>',
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'        => __( 'Marker Icon', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_icon',
			'type'        => 'radio_inline',
			'row_classes' => array( 'xlwcty_hide_label', 'xlwcty_no_border', 'xlwcty_pt0' ),
			'options'     => array(
				'built_in' => __( 'Built-in', 'thank-you-page-for-woocommerce-nextmove' ),
				'custom'   => __( 'Custom', 'thank-you-page-for-woocommerce-nextmove' ),
				'default'  => __( 'Default', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'before'      => '<p>Marker Icon</p>',
			'attributes'  => array(
				'data-conditional-id'    => $config['slug'] . '_enable',
				'data-conditional-value' => '1',
			),
		),
		array(
			'name'         => __( 'Built In', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'           => $config['slug'] . '_built_in_icon',
			'type'         => 'select',
			'row_classes'  => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_select_small', 'xlwcty_pt0' ),
			'before'       => '<p>Icon</p>',
			'before_field' => '<div class="xlwcty_icon_preview_before" >',
			'after_field'  => '<div class="xlwcty_icon_preview"></div></div>',
			'options'      => array(
				'pin-blue-1'           => __( 'Pin Blue 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-2'           => __( 'Pin Blue 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-3'           => __( 'Pin Blue 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-4'           => __( 'Pin Blue 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-5'           => __( 'Pin Blue 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-6'           => __( 'Pin Blue 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-7'           => __( 'Pin Blue 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-8'           => __( 'Pin Blue 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-9'           => __( 'Pin Blue 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-10'          => __( 'Pin Blue 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-11'          => __( 'Pin Blue 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-12'          => __( 'Pin Blue 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-13'          => __( 'Pin Blue 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-14'          => __( 'Pin Blue 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-15'          => __( 'Pin Blue 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-16'          => __( 'Pin Blue 16', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-1'     => __( 'Pin Blue Solid 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-2'     => __( 'Pin Blue Solid 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-3'     => __( 'Pin Blue Solid 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-4'     => __( 'Pin Blue Solid 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-5'     => __( 'Pin Blue Solid 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-6'     => __( 'Pin Blue Solid 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-7'     => __( 'Pin Blue Solid 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-8'     => __( 'Pin Blue Solid 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-9'     => __( 'Pin Blue Solid 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-10'    => __( 'Pin Blue Solid 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-11'    => __( 'Pin Blue Solid 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-12'    => __( 'Pin Blue Solid 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-13'    => __( 'Pin Blue Solid 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-14'    => __( 'Pin Blue Solid 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-15'    => __( 'Pin Blue Solid 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-blue-solid-16'    => __( 'Pin Blue Solid 16', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-1'          => __( 'Pin Green 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-2'          => __( 'Pin Green 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-3'          => __( 'Pin Green 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-4'          => __( 'Pin Green 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-5'          => __( 'Pin Green 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-6'          => __( 'Pin Green 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-7'          => __( 'Pin Green 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-8'          => __( 'Pin Green 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-9'          => __( 'Pin Green 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-10'         => __( 'Pin Green 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-11'         => __( 'Pin Green 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-12'         => __( 'Pin Green 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-13'         => __( 'Pin Green 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-14'         => __( 'Pin Green 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-15'         => __( 'Pin Green 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-16'         => __( 'Pin Green 16', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-1'    => __( 'Pin Green Solid 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-2'    => __( 'Pin Green Solid 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-3'    => __( 'Pin Green Solid 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-4'    => __( 'Pin Green Solid 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-5'    => __( 'Pin Green Solid 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-6'    => __( 'Pin Green Solid 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-7'    => __( 'Pin Green Solid 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-8'    => __( 'Pin Green Solid 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-9'    => __( 'Pin Green Solid 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-10'   => __( 'Pin Green Solid 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-11'   => __( 'Pin Green Solid 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-12'   => __( 'Pin Green Solid 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-13'   => __( 'Pin Green Solid 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-14'   => __( 'Pin Green Solid 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-15'   => __( 'Pin Green Solid 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-green-solid-16'   => __( 'Pin Green Solid 16', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-1'        => __( 'Pin Magenta 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-2'        => __( 'Pin Magenta 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-3'        => __( 'Pin Magenta 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-4'        => __( 'Pin Magenta 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-5'        => __( 'Pin Magenta 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-6'        => __( 'Pin Magenta 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-7'        => __( 'Pin Magenta 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-8'        => __( 'Pin Magenta 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-9'        => __( 'Pin Magenta 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-10'       => __( 'Pin Magenta 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-11'       => __( 'Pin Magenta 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-12'       => __( 'Pin Magenta 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-13'       => __( 'Pin Magenta 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-14'       => __( 'Pin Magenta 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-15'       => __( 'Pin Magenta 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-16'       => __( 'Pin Magenta 16', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-1'  => __( 'Pin Magenta Solid 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-2'  => __( 'Pin Magenta Solid 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-3'  => __( 'Pin Magenta Solid 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-4'  => __( 'Pin Magenta Solid 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-5'  => __( 'Pin Magenta Solid 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-6'  => __( 'Pin Magenta Solid 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-7'  => __( 'Pin Magenta Solid 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-8'  => __( 'Pin Magenta Solid 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-9'  => __( 'Pin Magenta Solid 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-10' => __( 'Pin Magenta Solid 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-11' => __( 'Pin Magenta Solid 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-12' => __( 'Pin Magenta Solid 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-13' => __( 'Pin Magenta Solid 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-14' => __( 'Pin Magenta Solid 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-15' => __( 'Pin Magenta Solid 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-magenta-solid-16' => __( 'Pin Magenta Solid 16', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-1'            => __( 'Pin Red 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-2'            => __( 'Pin Red 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-3'            => __( 'Pin Red 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-4'            => __( 'Pin Red 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-5'            => __( 'Pin Red 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-6'            => __( 'Pin Red 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-7'            => __( 'Pin Red 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-8'            => __( 'Pin Red 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-9'            => __( 'Pin Red 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-10'           => __( 'Pin Red 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-11'           => __( 'Pin Red 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-12'           => __( 'Pin Red 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-13'           => __( 'Pin Red 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-14'           => __( 'Pin Red 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-15'           => __( 'Pin Red 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-16'           => __( 'Pin Red 16', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-1'      => __( 'Pin Red Solid 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-2'      => __( 'Pin Red Solid 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-3'      => __( 'Pin Red Solid 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-4'      => __( 'Pin Red Solid 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-5'      => __( 'Pin Red Solid 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-6'      => __( 'Pin Red Solid 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-7'      => __( 'Pin Red Solid 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-8'      => __( 'Pin Red Solid 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-9'      => __( 'Pin Red Solid 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-10'     => __( 'Pin Red Solid 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-11'     => __( 'Pin Red Solid 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-12'     => __( 'Pin Red Solid 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-13'     => __( 'Pin Red Solid 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-14'     => __( 'Pin Red Solid 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-15'     => __( 'Pin Red Solid 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-red-solid-16'     => __( 'Pin Red Solid 16', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-1'         => __( 'Pin Yellow 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-2'         => __( 'Pin Yellow 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-3'         => __( 'Pin Yellow 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-4'         => __( 'Pin Yellow 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-5'         => __( 'Pin Yellow 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-6'         => __( 'Pin Yellow 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-7'         => __( 'Pin Yellow 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-8'         => __( 'Pin Yellow 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-9'         => __( 'Pin Yellow 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-10'        => __( 'Pin Yellow 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-11'        => __( 'Pin Yellow 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-12'        => __( 'Pin Yellow 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-13'        => __( 'Pin Yellow 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-14'        => __( 'Pin Yellow 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-15'        => __( 'Pin Yellow 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-16'        => __( 'Pin Yellow 16', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-1'   => __( 'Pin Yellow Solid 1', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-2'   => __( 'Pin Yellow Solid 2', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-3'   => __( 'Pin Yellow Solid 3', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-4'   => __( 'Pin Yellow Solid 4', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-5'   => __( 'Pin Yellow Solid 5', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-6'   => __( 'Pin Yellow Solid 6', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-7'   => __( 'Pin Yellow Solid 7', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-8'   => __( 'Pin Yellow Solid 8', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-9'   => __( 'Pin Yellow Solid 9', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-10'  => __( 'Pin Yellow Solid 10', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-11'  => __( 'Pin Yellow Solid 11', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-12'  => __( 'Pin Yellow Solid 12', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-13'  => __( 'Pin Yellow Solid 13', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-14'  => __( 'Pin Yellow Solid 14', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-15'  => __( 'Pin Yellow Solid 15', 'thank-you-page-for-woocommerce-nextmove' ),
				'pin-yellow-solid-16'  => __( 'Pin Yellow Solid 16', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			'attributes'   => array(
				'class'                         => 'cmb2_select xlwcty_map_icon_select',
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_icon',
				'data-xlwcty-conditional-value' => 'built_in',
			),
		),
		array(
			'name'        => __( 'Custom', 'thank-you-page-for-woocommerce-nextmove' ),
			'id'          => $config['slug'] . '_custom_icon',
			'type'        => 'file',
			'row_classes' => array( 'xlwcty_no_border', 'xlwcty_hide_label', 'xlwcty_pt0' ),
			'before'      => '<p>Icon</p>',
			'options'     => array(
				'url' => false,
			),
			'text'        => array(
				'add_upload_file_text' => 'Add/ Update Icon',
			),
			'attributes'  => array(
				'data-conditional-id'           => $config['slug'] . '_enable',
				'data-conditional-value'        => '1',
				'data-xlwcty-conditional-id'    => $config['slug'] . '_icon',
				'data-xlwcty-conditional-value' => 'custom',
			),
		),
		array(
			'name'        => __( 'Heading', 'thank-you-page-for-woocommerce-nextmove' ),
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

$config['default'] = array(
	'address'               => 'shipping',
	'marker_custom_address' => '',
	'marker_icon'           => 'built_in',
	'built_in_icon'         => 'pin-blue-2',
	'custom_icon'           => '',
	'marker_text'           => "{{order_shipping_address}}\ncustom text can come here",
	'zoom_level'            => '14',
	'style'                 => 'standard',
	'heading'               => __( 'Your order is confirmed', 'thank-you-page-for-woocommerce-nextmove' ),
	'heading_font_size'     => '20',
	'heading_alignment'     => 'left',
	'desc'                  => "We've accepted your order, and we're getting it ready. We'll update on order status on emails. A confirmation was sent to {{customer_email}}",
	'desc_alignment'        => 'left',
	'border_style'          => 'solid',
	'border_width'          => '1',
	'border_color'          => '#d9d9d9',
	'component_bg_color'    => '#ffffff',
);

return $config;
