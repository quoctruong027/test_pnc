<?php

$templates   = $this->get_templates();
$getinstance = Finale_deal_batch_processing::instance();
$select_temp = array( '-1' => 'Choose Template' );

foreach ( $templates as $key => $temp ) {
	$select_temp[ $key ] = $temp['name'];
}
$box_options    = array(
	'id'           => 'wcct_builder_settings',
	'title'        => __( 'Settings', 'finale-woocommerce-deal-pages' ),
	'classes'      => 'wcct_options_common',
	'show_names'   => true,
	'context'      => 'normal',
	'priority'     => 'high',
	'object_types' => array( $this->post_type ),
);
$config         = array();
$config['slug'] = 'wcct_finale_deal_shortcode';
$form_fields    = array(
	array(
		'id'       => 'wcct_deal_campaign_settings',
		'title'    => __( '<i class="flicon flicon-weekly-calendar"></i> Campaign', 'finale-woocommerce-deal-pages' ) . '',
		'position' => 3,
		'fields'   => array(
			array(
				'name'        => __( 'Select Campaign', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_finale_deal_choose_campaign',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_no_border', 'wcct_pb5' ),
				'options'     => array(
					'multiple' => __( 'Choose Campaign', 'finale-woocommerce-deal-pages' ),
					'all'      => __( 'All Indexed Camapigns', 'finale-woocommerce-deal-pages' ),

				),

			),
			array(
				'name' => __( 'Select Campaign', 'finale-woocommerce-deal-pages' ),
				'id'   => 'wcct_finale_deal_shortcode_campaign',
				'type' => 'wcct_multiselect',

				'description' => __( 'Index the campaigns to generate the shortcodes. All the indexed campaigns are available for generating shortcodes.', 'finale-woocommerce-deal-pages' ),
				'row_classes' => array( 'wcct_radio_btn', 'wcct_no_border', 'wcct_pb5', 'wcct_cmb2_chosen', 'wcct_no_label' ),
				'options_cb'  => array( $getinstance, 'get_campaign_by_index' ),
				'attributes'  => array(
					'multiple'               => 'true',
					'name'                   => 'wcct_finale_deal_shortcode_campaign[]',
					'data-conditional-id'    => '_wcct_finale_deal_choose_campaign',
					'data-conditional-value' => 'multiple',
					'data-placeholder'       => __( 'Choose Campaigns' ),

				),
			),
		),
	),
	array(
		'id'       => 'wcct_deal_template_settings',
		'title'    => __( '<i class="flicon flicon-giftbox"></i> Template', 'finale-woocommerce-deal-pages' ),
		'position' => 12,
		'fields'   => array(
			array(
				'name'        => __( 'Select Template Layout', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_template_layout',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_radio_btn', 'wcct_no_border' ),
				'options'     => array(
					'grid'   => __( 'Grid', 'finale-woocommerce-deal-pages' ),
					'list'   => __( 'List', 'finale-woocommerce-deal-pages' ),
					'native' => __( 'Native Theme', 'finale-woocommerce-deal-pages' ),
				),
			),
			array(
				'name'        => __( 'Help Text Native Theme', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_template_layout_native_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_radio_btn', 'wcct_no_border', 'wcct_pt0' ),
				'content'     => '',
				'before'      => __( 'This is an experimental feature.<br/>Finale Deal Pages will try to show countdown timer, counter bar or savings text on theme\'s native grid.<br/>But themes may modify the native woocommerce grid code so that could result in misaligned visual output.', 'finale-woocommerce-deal-pages' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_template_layout',
					'data-conditional-value' => 'native',
				),
			),
			array(
				'name'        => __( 'Grid Size', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_grid_size',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_border_top' ),
				'options'     => array(
					'4' => '4 Columns',
					'3' => '3 Columns',
					'1' => '1 Column',

				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_template_layout',
					'data-conditional-value' => 'grid',
				),
			),
			array(
				'name'        => __( 'Grid Layout', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_layout_grid',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_radio_btn_img_grid', 'wcct_no_border', 'wcct_pb5', 'wcct_deal_image' ),
				'options'     => array(
					'grid_layout1' => "<img src='//storage.googleapis.com/xl-finale-deal/grid_layout_1.jpg' />",
					'grid_layout2' => "<img src='//storage.googleapis.com/xl-finale-deal/grid_layout_2.jpg' />",
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_template_layout',
					'data-conditional-value' => 'grid',
				),
			),
			array(
				'name'        => __( 'List Layout', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_layout_list',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_radio_btn_img_row', 'wcct_no_border', 'wcct_pb5', 'wcct_deal_image', 'wcct_border_top' ),
				'options'     => array(
					'list_layout1' => "<img src='//storage.googleapis.com/xl-finale-deal/list_layout_1.jpg' />",
					'list_layout2' => "<img src='//storage.googleapis.com/xl-finale-deal/list_layout_2.jpg' />",
					'list_layout3' => "<img src='//storage.googleapis.com/xl-finale-deal/list_layout_3.jpg' />",
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_template_layout',
					'data-conditional-value' => 'list',
				),
			),
		),
	),
	array(
		'id'       => 'wcct_deal_countdown_timer_settings',
		'title'    => __( '<i class="flicon flicon-old-elevator-levels-tool"></i> Countdown Timer', 'finale-woocommerce-deal-pages' ) . '',
		'position' => 15,
		'fields'   => array(
			// countdown timer
			array(
				'name'        => __( 'Visibility', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_location_timer_show_single',
				'type'        => 'wcct_switch',
				'default'     => 0,
				'row_classes' => array( 'wcct_no_border', 'wcct_pb10' ),
				'label'       => array(
					'on'  => __( 'Show', 'finale-woocommerce-deal-pages' ),
					'off' => __( 'Hide', 'finale-woocommerce-deal-pages' ),
				),
				//                'before_row' => array('WCCT_Admin_CMB2_Support', 'cmb_before_row_cb'),
				//                'wcct_accordion_title' => __('Countdown Timer', 'finale-woocommerce-deal-pages'),
				//                'wcct_is_accordion_opened' => true,
			),
			array(
				'id'          => '_wcct_location_timer_show_single_html',
				'content'     => __( 'Enable this to show Countdown Timer.', WCCT_SLUG ),
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0' ),
			),
			array(
				'name'        => 'Countdown Timer',
				'id'          => '_wcct_appearance_timer_single_skin',
				'type'        => 'radio_inline',
				'before'      => '<p class="wcct_mt5 wcct_mb5">Skins</p>',
				'row_classes' => array( 'wcct_no_border', 'wcct_pb0', 'wcct_timer_select' ),
				'options'     => array(
					'highlight_1'  => __( 'Highlight', 'finale-woocommerce-deal-pages' ),
					'round_fill'   => __( 'Round Fill', 'finale-woocommerce-deal-pages' ),
					'round_ghost'  => __( 'Round Ghost', 'finale-woocommerce-deal-pages' ),
					'square_fill'  => __( 'Square Fill', 'finale-woocommerce-deal-pages' ),
					'square_ghost' => __( 'Square Ghost', 'finale-woocommerce-deal-pages' ),
					'default'      => __( 'Default', 'finale-woocommerce-deal-pages' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
				'after'       => '<div class="wcct_appearance_sticky_bar_img" data-type="header">
					<img data-type="round_fill" src="//storage.googleapis.com/xl-finale/timer_sticky_circle.jpg" />
					<img data-type="round_ghost" src="//storage.googleapis.com/xl-finale/timer_sticky_ghost.jpg" />
					<img data-type="square_fill" src="//storage.googleapis.com/xl-finale/timer_sticky_square.jpg" />
					<img data-type="square_ghost" src="//storage.googleapis.com/xl-finale/timer_sticky_square_ghost.jpg" />
					<img data-type="highlight_1" src="//storage.googleapis.com/xl-finale/timer_sticky_text.jpg" />
					<img data-type="default" src="//storage.googleapis.com/xl-finale/timer_sticky_text_simple.jpg" />
					</div>',
			),
			array(
				'content'     => __( 'Note: You may need to adjust the default appearance settings in case you switch the skin.', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_html_coutdown_help_1',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_timer_single_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background/Border</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Text Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_timer_single_text_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Label</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Font Size (px)', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_timer_single_font_size_timer',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Timer Font Size (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Font Size', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_timer_single_font_size',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Label Font Size (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Days', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_timer_single_label_days',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Timer Labels</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">days</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Hours', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_timer_single_label_hrs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">hours</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Minutes', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_timer_single_label_mins',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">minutes</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Seconds', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_timer_single_label_secs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">seconds</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Before Timer Text', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_view_deal_timer_text_before',
				'type'        => 'textarea_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_light_desc', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Before Timer Text', 'finale-woocommerce-deal-pages' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'After Timer Text', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_view_deal_timer_text_after',
				'type'        => 'textarea_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_light_desc' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'After Timer Text', 'finale-woocommerce-deal-pages' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
		),
	),
	array(
		'id'       => 'wcct_deal_counter_bar_settings',
		'title'    => __( '<i class="flicon flicon-old-elevator-levels-tool"></i> Inventory bar', 'finale-woocommerce-deal-pages' ) . '',
		'position' => 16,
		'fields'   => array(
			// counter bar
			array(
				'name'        => __( 'Visibility', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_location_bar_show_single',
				'type'        => 'wcct_switch',
				'row_classes' => array( 'wcct_no_border', 'wcct_pb10' ),
				'label'       => array(
					'on'  => __( 'Show', 'finale-woocommerce-deal-pages' ),
					'off' => __( 'Hide', 'finale-woocommerce-deal-pages' ),
				),
			),
			array(
				'id'          => '_wcct_location_bar_show_single_html',
				'content'     => __( 'Enable this to show Inventory Bar.<br/><strong>Inventory</strong> should be enabled for a product to show counter bar.', WCCT_SLUG ),
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0' ),
			),
			array(
				'name'        => __( 'Counter Bar', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_single_skin',
				'type'        => 'radio_inline',
				'options'     => array(
					'stripe_animate' => '<img src="//storage.googleapis.com/xl-finale/bar-capsule-animated.gif" />',
					'stripe'         => '<img src="//storage.googleapis.com/xl-finale/bar-capsule-lines.jpg" />',
					'fill'           => '<img src="//storage.googleapis.com/xl-finale/bar-capsule.jpg" />',
				),
				'row_classes' => array( 'wcct_img_options', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Skins</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),

			array(
				'name'        => __( 'Edges', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_single_edges',
				'type'        => 'radio_inline',
				'options'     => array(
					'rounded' => __( 'Rounded', 'finale-woocommerce-deal-pages' ),
					'smooth'  => __( 'Smooth', 'finale-woocommerce-deal-pages' ),
					'sharp'   => __( 'Sharp', 'finale-woocommerce-deal-pages' ),
				),
				'row_classes' => array( 'wcct_hide_label', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Edges</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Direction', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_single_orientation',
				'type'        => 'radio_inline',
				'options'     => array(
					'ltr' => __( 'Left to Right', 'finale-woocommerce-deal-pages' ) . ' ( <i class="dashicons dashicons-arrow-right-alt"></i> )',
					'rtl' => __( 'Right to Left', 'finale-woocommerce-deal-pages' ) . ' ( <i class="dashicons dashicons-arrow-left-alt"></i> )',
				),
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Direction</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'content'     => __( 'This moves counter bar left to right. Use this when you want to indicate increase in sales.', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_ltr_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_orientation',
					'data-wcct-conditional-value' => 'ltr',
				),
			),
			array(
				'content'     => __( 'This moves counter bar right to left. Use this when you want to indicate decrease in stocks.', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_rtl_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_orientation',
					'data-wcct-conditional-value' => 'rtl',
				),
			),
			array(
				'name'        => __( 'Counter Bar', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_single_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background/Border</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Bar Active Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_single_active_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_hide_label', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Active</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Bar Height', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_single_height',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_hide_label', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Height (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '5',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Before Bar Text', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_single_display_before',
				'type'        => 'textarea_small',
				'desc'        => '<a href="javascript:void(0);" onclick="wcct_show_tb(\'Counter Bar Merge Tags\',\'wcct_deals_merge_tags_invenotry_bar_help\');">Click here to learn to set up more dynamic merge tags in counter bar</a>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_light_desc', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Before Bar Text', 'finale-woocommerce-deal-pages' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'After Bar Text', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_appearance_bar_single_display_after',
				'type'        => 'textarea_small',
				'desc'        => '<a href="javascript:void(0);" onclick="wcct_show_tb(\'Counter Bar Merge Tags\',\'wcct_deals_merge_tags_invenotry_bar_help\');">Click here to learn to set up more dynamic merge tags in counter bar</a>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_light_desc', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'After Bar Text', 'finale-woocommerce-deal-pages' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
		),
	),
	array(
		'id'       => 'wcct_deal_miscellaneous_settings',
		'title'    => __( '<i class="flicon flicon-old-elevator-levels-tool"></i> Appearance', 'finale-woocommerce-deal-pages' ) . '',
		'position' => 17,
		'fields'   => array(
			array(
				'name'        => __( 'Action', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_action_after_campaign_expired',
				'type'        => 'radio_inline',
				'before_row'  => '<div class="cmb-row wcct_pb0 wcct_no_border" style="font-size:15px;font-weight:600;">' . __( 'Campaign Expired Action', 'finale-woocommerce-deal-pages' ) . '</div>',
				'row_classes' => array( 'wcct_pb0', 'wcct_no_border' ),
				'options'     => array(
					'hide_products'     => __( 'Hide Products', 'finale-woocommerce-deal-pages' ),
					'display_text'      => __( 'Display Text', 'finale-woocommerce-deal-pages' ),
					'continue_products' => __( 'Continue Showing Products', 'finale-woocommerce-deal-pages' ),

				),
			),
			array(
				'name'        => __( 'Campaign Expired Text', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_action_after_campaign_expired_text',
				'type'        => 'textarea_small',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Text', 'finale-woocommerce-deal-pages' ) . '</p>',
				'description' => __( 'This text would come up when <strong>none</strong> of the selected Campaigns are running.', 'finale-woocommerce-deal-pages' ),
				'row_classes' => array( 'wcct_pb0', 'wcct_no_border', 'wcct_hide_label' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_action_after_campaign_expired',
					'data-conditional-value' => 'display_text',
				),
			),
			array(
				'id'          => '_wcct_action_after_camp_hide_products',
				'type'        => 'wcct_html_content_field',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_action_after_campaign_expired',
					'data-conditional-value' => 'hide_products',
				),
				'row_classes' => array( 'wcct_no_border' ),

				'content' => __( '<p class="cmb2-metabox-description" style="padding-left: 180px;">Hide products for the campaigns which have expired.</p>', 'finale-woocommerce-deal-pages' ),
			),
			array(
				'id'          => '_wcct_action_after_camp_continue_showing',
				'type'        => 'wcct_html_content_field',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_action_after_campaign_expired',
					'data-conditional-value' => 'continue_products',
				),
				'row_classes' => array( 'wcct_no_border' ),
				'content'     => __( '<p class="cmb2-metabox-description" style="padding-left: 180px;">Continue to show products for the campaigns which have expired.</p>', 'finale-woocommerce-deal-pages' ),
			),
			array(
				'name'        => __( 'Button', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_add_to_cart_btn_text',
				'type'        => 'text',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Text', 'finale-woocommerce-deal-pages' ) . '</p>',
				'before_row'  => '<div class="cmb-row wcct_pt0 wcct_top_border">&nbsp;</div><div class="cmb-row wcct_pb0 wcct_no_border" style="font-size:15px;font-weight:600;">' . __( 'Add to Cart Button', 'finale-woocommerce-deal-pages' ) . '</div>',
				'row_classes' => array( 'wcct_pb0', 'wcct_no_border' ),
			),
			array(
				'name'        => __( 'Button BG Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_add_to_cart_btn_text_bg_color',
				'type'        => 'colorpicker',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Background Color', 'finale-woocommerce-deal-pages' ) . '</p>',
				'row_classes' => array( 'wcct_pb0', 'wcct_no_border', 'wcct_hide_label', 'wcct_combine_2_field_start' ),
			),
			array(
				'name'        => __( 'Button BG Hover Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_add_to_cart_btn_text_bg_color_hover',
				'type'        => 'colorpicker',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Background Hover Color', 'finale-woocommerce-deal-pages' ) . '</p>',
				'row_classes' => array( 'wcct_pb0', 'wcct_no_border', 'wcct_hide_label', 'wcct_combine_2_field_end' ),
			),
			array(
				'name'        => __( 'Text Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_add_to_cart_btn_text_color',
				'type'        => 'colorpicker',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Text Color', 'finale-woocommerce-deal-pages' ) . '</p>',
				'row_classes' => array( 'wcct_no_border', 'wcct_hide_label', 'wcct_combine_2_field_start', 'wcct_pb0' ),
			),
			array(
				'name'        => __( 'Text Font Size', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_add_to_cart_btn_text_font_size',
				'type'        => 'text',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Text Font Size', 'finale-woocommerce-deal-pages' ) . '</p>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'type'    => 'number',
					'min'     => '5',
					'pattern' => '\d*',
				),
			),
			array(
				'name'        => __( 'Width', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_add_to_cart_btn_width',
				'type'        => 'radio_inline',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Width', 'finale-woocommerce-deal-pages' ) . '</p>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border' ),
				'options'     => array(
					'full'   => 'Full Width',
					'inline' => 'Inline Width',
				),
			),

			array(
				'name'        => __( 'Don\'t change above \'Add to Cart\' text on following Product Types', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_pages_add_to_cart_exclude',
				'type'        => 'wcct_multiselect',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Exclude Product Types', 'finale-woocommerce-deal-pages' ) . '</p>',
				'description' => __( 'Some product types such as variable products require product selection before they can be added to cart. Usually their grids would show "Select Options".', 'finale-woocommerce-deal-pages' ) . '<br/>' . __( 'Excluding such product types from grid will NOT change the text of buttons on the grid, even though their product page will show entered text.', 'finale-woocommerce-deal-pages' ) . '<br/>' . __( 'Example: If you change button text to say "Buy Now" and exclude variable products on grid. Button for this product on grid will show "Select Options" while button of product will show "Buy Now".', 'finale-woocommerce-deal-pages' ),
				'row_classes' => array( 'wcct_cmb2_chosen', 'wcct_light_desc', 'wcct_hide_label' ),
				'options_cb'  => array( 'WCCT_Admin_CMB2_Support', 'get_product_types' ),
				'attributes'  => array(
					'multiple'         => 'true',
					'name'             => '_wcct_deal_pages_add_to_cart_exclude[]',
					'data-placeholder' => __( 'Choose Product Types' ),

				),
			),

			// pagination
			array(
				'name'        => __( 'Style', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_pagination_style',
				'type'        => 'radio_inline',
				'before_row'  => '<div class="cmb-row wcct_pb0 wcct_no_border" style="font-size:15px;font-weight:600;">' . __( 'Pagination', 'finale-woocommerce-deal-pages' ) . '</div>',
				'row_classes' => array( 'wcct_no_border', 'wcct_pb0', 'wcct_timer_select' ),
				'options'     => array(
					'rounded' => __( 'Round', 'finale-woocommerce-deal-pages' ),
					'square'  => __( 'Square', 'finale-woocommerce-deal-pages' ),
				),
				'after'       => '<div class="wcct_appearance_sticky_bar_img" data-type="header">
					<img data-type="rounded" src="//storage.googleapis.com/xl-finale-deal/navigation_circle.jpg" />
					<img data-type="square" src="//storage.googleapis.com/xl-finale-deal/navigation_square.jpg" />
					</div>',
			),
			array(
				'name'        => __( 'Active State', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_pagination_active_border_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Color</p>',

			),
			array(
				'name'        => __( 'BG Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_pagination_active_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background Color</p>',

			),
			array(
				'name'        => __( 'Text Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_pagination_active_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text Color</p>',

			),
			array(
				'name'        => __( 'Default State', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_pagination_default_border_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Color</p>',

			),
			array(
				'name'        => __( 'BG Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_pagination_default_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background Color</p>',

			),
			array(
				'name'        => __( 'Text Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_pagination_default_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text Color</p>',

			),
			array(
				'name'        => __( 'Sale Badge Background Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_sale_badge_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_no_border' ),
				'before_row'  => '<div class="cmb-row wcct_pb0 wcct_no_border" style="font-size:15px;font-weight:600;">' . __( 'Other ', 'finale-woocommerce-deal-pages' ) . '</div>',
			),
			array(
				'name'        => __( 'Sale Badge Text Color', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_sale_badge_text_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_no_border' ),
			),
			array(
				'name' => __( 'Sale Badge Text', 'finale-woocommerce-deal-pages' ),
				'id'   => '_wcct_deal_sale_badge_text',
				'type' => 'text_small',


			),
			array(
				'name'        => __( 'You Save', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_you_save_text',
				'type'        => 'text',
				'row_classes' => array( 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text</p>',
			),
			array(
				'name'        => __( 'You Save Value', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_you_save_value',
				'type'        => 'radio_inline',
				'options'     => array(
					'{{savings_value}}'                         => __( 'Saving Value', 'finale-woocommerce-deal-pages' ),
					'{{savings_percentage}}'                    => __( 'Saving Percentage', 'finale-woocommerce-deal-pages' ),
					'{{savings_value}}({{savings_percentage}})' => __( 'Both', 'finale-woocommerce-deal-pages' ),
				),
				'row_classes' => array( 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Display Value</p>',
			),
			array(
				'name'        => __( 'Hide on Grid/ Timer', 'finale-woocommerce-deal-pages' ),
				'desc'        => __( 'Description (Only appear in the List layout)', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_hide_description',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_img_options', 'wcct_no_border', 'wcct_pb0' ),
			),
			array(
				'name'        => __( 'Hide Rating', 'finale-woocommerce-deal-pages' ),
				'desc'        => __( 'Rating', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_hide_rating',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_img_options', 'wcct_no_border', 'wcct_pb0', 'wcct_hide_label' ),
			),
			array(
				'name'        => __( 'Hide Sale Badge', 'finale-woocommerce-deal-pages' ),
				'desc'        => __( 'Sale Badge', 'finale-woocommerce-deal-pages' ),
				'id'          => '_wcct_deal_hide_sale_badge',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_img_options', 'wcct_hide_label' ),
			),

			array(
				'name'       => __( 'Product Description Length', 'finale-woocommerce-deal-pages' ),
				'id'         => '_wcct_deal_product_desc_length',
				'type'       => 'text',
				'attributes' => array(
					'type'    => 'number',
					'min'     => '10',
					'pattern' => '\d*',
				),
			),
			array(
				'name'       => __( 'Products Grid/List Thumbnail Size', 'finale-woocommerce-deal-pages' ),
				'id'         => '_wcct_deal_shop_thumbnail_size',
				'type'       => 'select',
				'desc'       => __( 'That\'s the product image size used in Product list related components', 'finale-woocommerce-deal-pages' ),
				'options_cb' => 'wcct_deals_modify_image_sizes_options',
			),
		),
	),
);

$tabs_setting['tabs'] = apply_filters( 'wcct_deals_cmb2_modify_field_tabs', $form_fields );
$tabs_setting         = apply_filters( 'wcct_modify_field_config_products', $tabs_setting );
$tabs_setting         = $tabs_setting['tabs'];

$tabs_setting_key_value = array();
foreach ( $tabs_setting as $key1 => $value1 ) {
	$tabs_setting_key_value[ $value1['id'] ] = array(
		'label' => __( $value1['title'], 'cmb2' ),
	);
}
$box_options = array(
	'id'           => 'wcct_builder_settings',
	'title'        => __( 'Settings', 'finale-woocommerce-deal-pages' ),
	'classes'      => 'wcct_options_common',
	'show_names'   => true,
	'context'      => 'normal',
	'priority'     => 'high',
	'object_types' => array( $this->post_type ),
	'wcct_tabs'    => $tabs_setting_key_value,
	'tab_style'    => 'default',
);
$cmb         = new_cmb2_box( $box_options );
// set tabs
$cmb->add_field( array(
		'id'      => '_wcct_wrap_tabs1',
		'type'    => 'wcct_html_content_field',
		'content' => '<div class=""></div>',
	) );
foreach ( $tabs_setting as $key1 => $value1 ) {
	if ( is_array( $value1['fields'] ) && count( $value1['fields'] ) > 0 ) {
		foreach ( $value1['fields'] as $key2 => $value2 ) {
			$value2['tab'] = $value1['id'];

			if ( 'group' === $value2['type'] ) {
				$value2['render_row_cb'] = array( 'CMB2_WCCT_Tabs', 'tabs_render_group_row_cb' );

			} else {
				$value2['render_row_cb'] = array( 'CMB2_WCCT_Tabs', 'tabs_render_row_cb' );

			}
			$cmb->add_field( $value2 );
		}
	}
}

$box_options = array(
	'id'           => 'wcct_shortcode_box',
	'title'        => __( 'ShortCode', 'finale-woocommerce-deal-pages' ),
	'classes'      => '',
	'show_names'   => true,
	'context'      => 'side',
	'priority'     => 'low',
	'object_types' => array( $this->post_type ),
	'show_on_cb'   => 'wcct_deals_admin_handle_shortcode_meta_visibility',
);

$cmb = new_cmb2_box( $box_options );
$p   = isset( $_GET['post'] ) ? $_GET['post'] : 0;
if ( ! is_array( $p ) ) {
	$desc = __( "Arguments: <br/><strong>id</strong>: ID of the current Deal Page <br/><strong>count</strong>: Number of products to show
<br/><strong>pagination</strong>: Allow pagination using pagination='yes'<br/><strong>orderby</strong>: Modify order of products, it could be 'date', 'price', 'sales','rating', 'title', 'rand' or 'campaign_priority'.<br/><strong>order</strong>: ASC or DESC", 'finale-woocommerce-deal-pages' );
	$cmb->add_field( array(
			'id'         => 'shortcode_settings',
			'type'       => 'wcct_html_content_field',
			'attributes' => array(
				'disable'  => 'disable',
				'readonly' => 'readonly',
				'onclick'  => 'this.select()',
			),

			'content' => sprintf( '<textarea onclick="this.select()" rows="3" readonly="readonly">%s</textarea> <p class="cmb2-metabox-description">%s</p>', "[finale_deal id='{$p}' count='12' pagination='yes']", $desc ),
		) );
}
