<?php

$doc_link = 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation';

// one time campaign doc text
$onetime_content  = __( 'One Time option allows you to run single campaign between two fixed dates.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$onetime_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$onetime_content  .= __( 'Need Help with setting up One-Time campaign?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$onetime_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'one-time-campaign',
), $doc_link . '/schedule/' );
$onetime_content  .= '<a href="' . $onetime_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// recurring campaign doc text
$recurring_content  = __( 'Recurring option allows you to run recurring campaign for set duration.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$recurring_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$recurring_content  .= __( 'Need Help with setting up Recurring campaign?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$recurring_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'recurring-campaign',
), $doc_link . '/schedule/#recurring' );
$recurring_content  .= '<a href="' . $recurring_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// discount doc text
$discount_content  = __( 'Enable this to set up sale on your products for the campaign duration.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$discount_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$discount_content  .= __( 'Need Help with setting up Discounts?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$discount_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'discounts',
), $doc_link . '/discount/' );
$discount_content  .= '<a href="' . $discount_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// inventory doc text
$invenotry_content  = __( 'Enable this to define units of item to be sold during campaign.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$invenotry_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$invenotry_content  .= __( 'Need Help with setting up Inventory?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$inventory_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'inventory',
), $doc_link . '/inventory/' );
$invenotry_content  .= '<a href="' . $inventory_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// position mismatch check
$position_mismatch_content = __( 'Select Positions for Single Product Page.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$position_mismatch_content .= '<br/><i class="dashicons dashicons-editor-help"></i> ';
$position_mismatch_content .= __( 'Unable to see this element on product page? Follow this quick ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$position_doc_link         = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'position_masmatch',
), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/troubleshooting-guides/unable-to-see-countdown-timer-or-counter-bar/' );
$position_mismatch_content .= '<a href="' . $position_doc_link . '" target="_blank">' . __( 'troubleshooting guide', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// coupons doc text
$coupons_content  = __( 'Enable this to set up coupons for the campaign duration.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$coupons_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$coupons_content  .= __( 'Need Help with setting up Inventory?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$coupons_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'coupons',
), $doc_link . '/coupons/' );
$coupons_content  .= '<a href="' . $coupons_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// countdown-timer doc text
$elements_ct_content  = __( 'Enable this to show Countdown Timer.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$elements_ct_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$elements_ct_content  .= __( 'Need Help with setting up Countdown Timer?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$elements_ct_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'countdown-timer',
), $doc_link . '/appearance/countdown-timer/' );
$elements_ct_content  .= '<a href="' . $elements_ct_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// counter-bar doc text
$elements_cb_content  = __( 'Enable this to show Counter Bar.<br/><strong>Inventory Goal</strong> should be <strong>enabled</strong> to display the Counter Bar.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$elements_cb_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$elements_cb_content  .= __( 'Need Help with setting up Counter Bar?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$elements_cb_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'counter-bar',
), $doc_link . '/appearance/counter-bar/' );
$elements_cb_content  .= '<a href="' . $elements_cb_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// counter-bar skin text
$elements_cb_skin_content = '<i class="dashicons dashicons-editor-help"></i>' . __( ' Note: These skins are indicative designs. The counter bar would automatically move once a purchase is made during the campaign. However, you can adjust sold units using Events to give campaigns a kickstart.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$elements_cb_skin_content .= '<br/>';
$elements_cb_skin_content .= __( 'Learn more about Events here.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$elements_cb_doc_link     = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'counter-bar-events',
), $doc_link . '/events/' );
$elements_cb_skin_content .= '<a href="' . $elements_cb_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// sticky-header doc text
$elements_sh_content  = __( 'Enable this to show Sticky Header on the site.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$elements_sh_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$elements_sh_content  .= __( 'Need Help with setting up Sticky Header?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$elements_sh_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'sticky-header',
), $doc_link . '/appearance/sticky-headerfooter/' );
$elements_sh_content  .= '<a href="' . $elements_sh_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// sticky-footer doc text
$elements_sf_content  = __( 'Enable this to show Sticky Footer on the site.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$elements_sf_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$elements_sf_content  .= __( 'Need Help with setting up Sticky Footer?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$elements_sf_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'sticky-footer',
), $doc_link . '/appearance/sticky-headerfooter/' );
$elements_sf_content  .= '<a href="' . $elements_sf_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// custom-text doc text
$custom_text_content  = __( 'Enable this to show Custom Text.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
$custom_text_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$custom_text_content  .= __( 'Need Help with setting up Custom Text?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$custom_text_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'custom-text',
), $doc_link . '/custom-text/' );
$custom_text_content  .= '<a href="' . $custom_text_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

// events doc text
$events_content  = __( 'Want Some Ideas On Using Events? ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' <a href="javascript:void(0);" onclick="wcct_show_tb(\'' . __( 'Some Ideas On Using Events', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '\',\'wcct_events_help\');">' . __( 'Click Here', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';
$events_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$events_content  .= __( 'Need Help with setting up Events?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ';
$events_doc_link = add_query_arg( array(
	'utm_source'   => 'finale',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'events',
), $doc_link . '/events/' );
$events_content  .= '<a href="' . $events_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';

return apply_filters( 'wcct_campaign_settings', array(
	array(
		'id'       => 'wcct_campaign_settings',
		'title'    => __( '<i class="flicon flicon-weekly-calendar"></i> Schedule', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '<span class="wcct_load_spin wcct_load_tab_campaign"></span>',
		'position' => 3,
		'fields'   => apply_filters( 'wcct_campaign_settings_fields', array(
			array(
				'name'        => __( 'Type', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_type',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_radio_btn', 'wcct_no_border', 'wcct_pb5' ),
				'options'     => array(
					'fixed_date' => __( 'One Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'recurring'  => __( 'Recurring', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
			),
			array(
				'content'     => $onetime_content,
				'id'          => '_wcct_campaign_fixed_date_title',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'fixed_date',
				),
			),
			array(
				'content'     => $recurring_content,
				'id'          => '_wcct_campaign_recurring_title',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			// fixed date and recurring
			array(
				'name'        => __( 'Start Date & Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_fixed_recurring_start_date',
				'type'        => 'text_date',
				'row_classes' => array( 'wcct_combine_2_field_start' ),
				'date_format' => 'Y-m-d',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => json_encode( array( 'fixed_date', 'recurring' ) ),
				),
			),
			array(
				'name'        => __( 'Start Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_fixed_recurring_start_time',
				'type'        => 'text_time',
				'row_classes' => array( 'wcct_combine_2_field_end' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => json_encode( array( 'fixed_date', 'recurring' ) ),
				),
			),
			// fixed date
			array(
				'name'        => __( 'End Date & Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_fixed_end_date',
				'type'        => 'text_date',
				'row_classes' => array( 'wcct_combine_2_field_start' ),
				'date_format' => 'Y-m-d',
				'attributes'  => array(
					'data-validation'        => 'required',
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'fixed_date',
				),
			),
			array(
				'name'        => __( 'End Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_fixed_end_time',
				'type'        => 'text_time',
				'row_classes' => array( 'wcct_combine_2_field_end' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'fixed_date',
				),
			),
			// recurring
			array(
				'name'        => __( 'Duration', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_duration_days',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_extra_small' ),
				'desc'        => __( 'days', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			array(
				'name'        => __( 'Duration Hrs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_duration_hrs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_extra_small' ),
				'desc'        => __( 'hrs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'max'                    => '23',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			array(
				'name'        => __( 'Duration Min', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_duration_min',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_extra_small' ),
				'desc'        => __( 'mins', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'max'                    => '59',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			array(
				'name'        => __( 'Pause Period', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_gap_days',
				'desc'        => __( 'days', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_extra_small' ),
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			array(
				'name'        => __( 'Pause Period Hrs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_gap_hrs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_extra_small' ),
				'desc'        => __( 'hrs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'max'                    => '23',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			array(
				'name'        => __( 'Pause Period Mins', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_gap_mins',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_extra_small', 'wcct_no_border', 'wcct_pb0' ),
				'desc'        => __( 'mins', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'max'                    => '59',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			array(
				'content'     => __( 'Pauses campaign for set duration and <strong>restart</strong> automatically after Pause Period elapses. If you want to immediately restart campaign without a break, set days/ hours to zero.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_gap_hrs_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array(
					'row_title_classes',
					'wcct_small_text',
					'wcct_label_gap',
					'wcct_pt0',
					'wcct_pb10',
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			array(
				'name'        => __( 'Ends', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_ends',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_pb5', 'wcct_no_border' ),
				'options'     => array(
					'never'         => __( 'Never', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'recurring'     => __( 'After Set Recurrences', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'specific_time' => __( 'At Specific Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			array(
				'content'     => __( 'Sets Recurring Campaigns to go on forever, or end after certain repetitions or end at a specific date.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_ends_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'recurring',
				),
			),
			array(
				'name'        => __( 'Number of Recurrences', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_campaign_recurring_ends_after_x_days',
				'type'        => 'text_small',
				'desc'        => __( 'times', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'wcct_text_extra_small' ),
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-wcct-conditional-id'    => '_wcct_campaign_recurring_ends',
					'data-wcct-conditional-value' => 'recurring',
					'data-conditional-id'         => '_wcct_campaign_type',
					'data-conditional-value'      => 'recurring',
				),
			),
			array(
				'name'        => __( 'End Date & Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_data_end_date_of_deal',
				'type'        => 'text_date',
				'date_format' => 'Y-m-d',
				'row_classes' => array( 'wcct_combine_2_field_start' ),
				'attributes'  => array(
					'data-wcct-conditional-id'    => '_wcct_campaign_recurring_ends',
					'data-wcct-conditional-value' => 'specific_time',
					'data-conditional-id'         => '_wcct_campaign_type',
					'data-conditional-value'      => 'recurring',
				),
			),
			array(
				'name'        => __( 'End Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_data_end_time_of_deal',
				'type'        => 'text_time',
				'row_classes' => array( 'wcct_combine_2_field_end' ),
				'attributes'  => array(
					'data-wcct-conditional-id'    => '_wcct_campaign_recurring_ends',
					'data-wcct-conditional-value' => 'specific_time',
					'data-conditional-id'         => '_wcct_campaign_type',
					'data-conditional-value'      => 'recurring',
				),
			),
		) ),
	),
	array(
		'id'       => 'wcct_deal_price_settings',
		'title'    => __( '<i class="flicon flicon-money-bill-of-one"></i> Discount', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '<span class="wcct_load_spin wcct_load_tab_deal"></span>',
		'position' => 6,
		'fields'   => apply_filters( 'wcct_deal_price_settings_fields', array(
			array(
				'name'                     => __( 'Enable', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'                       => '_wcct_deal_enable_price_discount',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border', 'wcct_pb10' ),
				'label'                    => array(
					'on'  => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'off' => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'wcct_accordion_title'     => __( 'Pricing Discount', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'content'     => $discount_content,
				'id'          => '_wcct_deal_discount_amount_html_discount',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => __( 'Discount Mode', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_mode',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_no_border', 'wcct_pb0' ),
				'options_cb'  => array( 'XLWCCT_Admin', 'wcct_discount_mode_options' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_enable_price_discount',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Amount', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_amount',
				'type'        => 'text_small',
				'before'      => '<p class="wcct_inline">' . __( 'Amount', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>',
				'row_classes' => array( 'wcct_text_extra_small', 'wcct_hide_label', 'wcct_pt15' ),
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'step'                        => '0.01',
					'data-conditional-id'         => '_wcct_deal_enable_price_discount',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_deal_mode',
					'data-wcct-conditional-value' => 'simple',
				),
			),
			/**
			 * Repeater starts
			 */
			array(
				'id'           => '_wcct_discount_custom_advanced',
				'type'         => 'group',
				'before_group' => array( 'WCCT_Admin_CMB2_Support', 'cmb2_wcct_before_call' ),
				'before'       => __( 'Advanced Discount Setup', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'after_group'  => array( 'WCCT_Admin_CMB2_Support', 'cmb2_wcct_after_call' ),
				'repeatable'   => true,
				'attributes'   => array(
					'data-conditional-id'         => '_wcct_deal_enable_price_discount',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_deal_mode',
					'data-wcct-conditional-value' => 'tiered',
				),
				'options'      => array(
					'group_title'   => __( 'Range', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'add_button'    => __( 'Add Discount Range', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'remove_button' => '<i class="dashicons dashicons-no"></i>',
					'sortable'      => false,
					'closed'        => false,
				),
				'fields'       => array(
					array(
						'name'        => __( 'From Regular Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'range_from',
						'type'        => 'text_small',
						'row_classes' => array( 'wcct_combine_2_field_event_middle', 'wcct_text_extra_small' ),
						'attributes'  => array(
							'placeholder' => __( '0', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'type'        => 'number',
							'min'         => '0',
							'pattern'     => '\d*',
						),
					),
					array(
						'name'        => __( 'to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'range_to',
						'type'        => 'text_small',
						'row_classes' => array( 'wcct_combine_2_field_event_middle', 'wcct_text_extra_small' ),
						'attributes'  => array(
							'placeholder' => __( '10', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'type'        => 'number',
							'min'         => '0',
							'pattern'     => '\d*',
						),
					),
					array(
						'name'        => __( 'Discount Amount is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'range_value',
						'type'        => 'text_small',
						'row_classes' => array( 'wcct_combine_2_field_event_middle', 'wcct_text_extra_small' ),
						'attributes'  => array(
							'placeholder' => __( '5', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'type'        => 'number',
							'min'         => '0',
							'step'        => '0.01',
						),
					),
				),
			),
			//repeater ends

			array(
				'name'        => __( 'Discount Type', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_type',
				'type'        => 'select',
				'options'     => array(
					'percentage'      => __( 'Percentage % on Regular Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'percentage_sale' => __( 'Percentage % on Sale Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'fixed_price'     => __( 'Fixed Amount ' . get_woocommerce_currency_symbol() . ' on Regular Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'fixed_sale'      => __( 'Fixed Amount ' . get_woocommerce_currency_symbol() . ' on Sale Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'flat_sale'       => __( 'Flat Amount ' . get_woocommerce_currency_symbol(), 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'row_classes' => array(),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_enable_price_discount',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'       => __( 'Override Discount', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'         => '_wcct_deal_override_price_discount',
				'desc'       => __( 'Override this discount if Sale Price is set locally.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'       => 'checkbox',
				'attributes' => array(
					'data-conditional-id'    => '_wcct_deal_enable_price_discount',
					'data-conditional-value' => '1',
				),
			),
		) ),
	),
	array(
		'id'       => 'wcct_deal_inventory_settings',
		'title'    => __( '<i class="flicon flicon-text-file-filled-interface-paper-sheet"></i> Inventory', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '<span class="wcct_load_spin wcct_load_tab_deal"></span>',
		'position' => 9,
		'fields'   => array(
			array(
				'name'                     => __( 'Enable', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'                       => '_wcct_deal_enable_goal',
				'type'                     => 'wcct_switch',
				'label'                    => array(
					'on'  => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'off' => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'row_classes'              => array(
					'wcct_detect_checkbox_change',
					'wcct_gif_location',
					'wcct_gif_appearance',
					'wcct_no_border',
					'wcct_pb10',
				),
				'wcct_accordion_title'     => __( 'Inventory Goal', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'content'     => $invenotry_content,
				'id'          => '_wcct_deal_inventory_goal_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => __( 'Quantity to be Sold', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_units',
				'type'        => 'radio_inline',
				'options'     => array(
					'custom' => __( 'Custom Stock Quantity', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'same'   => __( 'Existing Stock Quantity', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'row_classes' => array( 'wcct_no_border', 'wcct_pb5' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_enable_goal',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Same Inventory Label', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_same_inventory_html',
				'content'     => __( 'This will pick up stock quantity of individual product and applicable when Manage Stock in product is ON.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0' ),
				'attributes'  => array(
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'same',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),
			array(
				'name'        => __( 'Inventory Mode', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_custom_mode',
				'type'        => 'radio_inline',
				'options'     => array(
					'basic'  => __( 'Basic', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'tiered' => __( 'Advanced', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'range'  => __( 'Range', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'row_classes' => array( 'wcct_hide_label', 'wcct_mt15', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_inline">' . __( 'Inventory Mode', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>',
				'attributes'  => array(
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),
			array(
				'name'        => __( 'Quantity', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_custom_units',
				'type'        => 'text_small',
				'before'      => '<p class="wcct_inline">' . __( 'Quantity', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>',
				'row_classes' => array(
					'wcct_text_extra_small',
					'wcct_pt15',
					'wcct_hide_label',
					'wcct_no_border',
					'wcct_pb10',
				),
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),

			array(
				'id'           => '_wcct_deal_custom_advanced',
				'type'         => 'group',
				'before_group' => array( 'WCCT_Admin_CMB2_Support', 'cmb2_wcct_before_call' ),
				'after_group'  => array( 'WCCT_Admin_CMB2_Support', 'cmb2_wcct_after_call' ),
				'repeatable'   => true,
				'row_classes'  => array( 'wcct_no_border' ),
				'attributes'   => array(
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
				'options'      => array(
					'group_title'   => __( 'Range', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'add_button'    => __( 'Add Inventory Range', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'remove_button' => '<i class="dashicons dashicons-no"></i>',
					'sortable'      => false,
					'closed'        => false,
				),
				'fields'       => array(
					array(
						'name'        => __( 'For Total Quantity', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'range_from',
						'type'        => 'text_small',
						'row_classes' => array( 'wcct_combine_2_field_event_middle', 'wcct_text_extra_small' ),
						'attributes'  => array(
							'placeholder' => __( '0', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'type'        => 'number',
							'min'         => '0',
							'pattern'     => '\d*',
						),
					),
					array(
						'name'        => __( 'to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'range_to',
						'type'        => 'text_small',
						'row_classes' => array( 'wcct_combine_2_field_event_middle', 'wcct_text_extra_small' ),
						'attributes'  => array(
							'placeholder' => __( '10', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'type'        => 'number',
							'min'         => '0',
							'pattern'     => '\d*',
						),
					),
					array(
						'name'        => __( 'Set Custom Quantity', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'range_value',
						'type'        => 'text_small',
						'row_classes' => array( 'wcct_combine_2_field_event_middle', 'wcct_text_extra_small' ),
						'attributes'  => array(
							'placeholder' => __( '8', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'type'        => 'number',
							'min'         => '0',
							'pattern'     => '\d*',
						),
					),
				),
			),
			array(
				'name'        => __( 'From Quantity', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_range_from_custom_units',
				'type'        => 'text_small',
				'before'      => '<p class="wcct_inline">' . __( 'Randomly picks up quantity from range', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>',
				'row_classes' => array(
					'wcct_text_extra_small',
					'wcct_pt15',
					'wcct_hide_label',
					'wcct_no_border',
					'wcct_pb10',
					'wcct_combine_2_field_start',
				),
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),
			array(
				'name'        => __( 'To Quantity', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_range_to_custom_units',
				'type'        => 'text_small',
				'before'      => '<p class="wcct_inline">' . __( 'To', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>',
				'row_classes' => array(
					'wcct_text_extra_small',
					'wcct_pt15',
					'wcct_hide_label',
					'wcct_no_border',
					'wcct_pb10',
					'wcct_combine_2_field_end',
				),
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),

			array(
				'name'        => __( 'Inventory Advcnaced HTML', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_inventory_advanced_html',
				'content'     => __( 'In case of variable products, \'Total Quantity\' is overall stock quantity available for purchase.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'        => 'wcct_html_content_field',
				'row_classes' => array(
					'wcct_label_gap',
					'wcct_pt0',
					'wcct_pb10',
					'wcct_no_border',
					'row_title_classes',
					'wcct_small_text',
				),
				'attributes'  => array(
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),
			array(
				'name'        => __( 'Calculate Sold Units (for counter bar)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_inventory_goal_for',
				'type'        => 'radio_inline',
				'options'     => array(
					'campaign'   => __( 'Overall Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'recurrence' => __( 'Current Occurrence', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'desc'        => 'Need help? <a href="javascript:void(0);" onclick="wcct_show_tb(\'' . __( 'Inventory Sold Units Help', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '\',\'wcct_inventory_sold_unit_help\');">' . __( 'Learn More', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>',
				'row_classes' => array( 'wcct_text_extra_small', 'wcct_light_desc', 'wcct_border_top' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_enable_goal',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Setup campaign on Out of Stock Products', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_custom_units_allow_backorder',
				'type'        => 'radio_inline',
				'options'     => array(
					'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'desc'        => 'Need help? <a href="javascript:void(0);" onclick="wcct_show_tb(\'' . __( 'Setup campaign on Out of Stock Products Help', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '\',\'wcct_inventory_out_of_stock_help\');">' . __( 'Learn More', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>',
				'row_classes' => array( 'wcct_text_extra_small', 'wcct_light_desc' ),
				'attributes'  => array(
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),
			array(
				'name'        => __( 'End Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_deal_end_campaign',
				'type'        => 'radio_inline',
				'options'     => array(
					'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'desc'        => __( 'When all the units set up in the campaign are sold.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'wcct_text_extra_small', 'wcct_light_desc' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_enable_goal',
					'data-conditional-value' => '1',
				),
			),
		),
	),
	array(
		'id'       => 'wcct_coupon_settings',
		'title'    => __( '<i class="flicon flicon-giftbox"></i> Coupons', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		'position' => 12,
		'fields'   => array(
			array(
				'name'                     => __( 'Enable', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'                       => '_wcct_coupons_enable',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border', 'wcct_pb10' ),
				'label'                    => array(
					'on'  => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'off' => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'wcct_accordion_title'     => __( 'Pricing Discount', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'content'     => $coupons_content,
				'id'          => '_wcct_deal_discount_amount_html_coupon',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'id'               => '_wcct_coupons',
				'name'             => __( 'Select Coupon', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'             => 'select',
				'row_classes'      => array( 'row_title_classes', 'wcct_cmb2_chosen', 'wcct-coupon-msg' ),
				'show_option_none' => __( 'Choose a Coupon', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'options_cb'       => array( 'WCCT_Admin_CMB2_Support', 'get_coupons_selected' ),
				'before'           => array( 'WCCT_Admin_CMB2_Support', 'wcct_coupons_set_field_data_attr' ),
				'after'            => array( 'WCCT_Admin_CMB2_Support', 'maybe_show_no_coupon' ),
				'attributes'       => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Apply Coupon ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_apply_mode',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_no_border', 'wcct_pb5' ),
				'options'     => array(
					'auto'   => __( 'Automatically', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'manual' => __( 'Manually', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),
			array(
				'content'     => __( 'Coupons are automatically applied when products are added to cart.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_mode_auto_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array(
					'row_title_classes',
					'wcct_small_text',
					'wcct_label_gap',
					'wcct_pt0',
					'wcct_no_border',
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_coupons_enable',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_coupons_apply_mode',
					'data-wcct-conditional-value' => 'auto',
				),
			),
			array(
				'content'     => __( 'Coupons need to be manually revealed and applied.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_mode_manual_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array(
					'row_title_classes',
					'wcct_small_text',
					'wcct_label_gap',
					'wcct_pt0',
					'wcct_no_border',
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_coupons_enable',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_coupons_apply_mode',
					'data-wcct-conditional-value' => 'manual',
				),
			),
			array(
				'name'        => __( 'Expire Coupon', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_is_expire',
				'type'        => 'radio_inline',
				'desc'        => __( 'When campaign is not running.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'wcct_border_top', 'wcct_mt20', 'wcct_light_desc' ),
				'options'     => array(
					'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'       => __( 'Hide Coupon Errors', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'         => '_wcct_coupons_is_hide_errors',
				'type'       => 'radio_inline',
				'options'    => array(
					'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes' => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Coupon Success Message', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_success_message',
				'type'        => 'textarea_small',
				'desc'        => __( 'Display this text when coupon is successfully applied.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'wcct_no_border', 'wcct_pb10', 'wcct_light_desc' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Checkout Link',
				'id'          => '_wcct_coupons_is_checkout_link',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_td_mt_5', 'wcct_hide_label', 'wcct_pt0', 'wcct_light_desc' ),
				'desc'        => __( 'Enable \'Checkout link\' in coupon success message', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Coupon Success Message Visibility', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_notice_show',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_pb10', 'wcct_light_desc' ),
				'options'     => array(
					'all'    => __( 'Native WooCommerce Pages (Shop, Archives, Single Product, Cart & Checkout)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'custom' => __( 'Specific Products & Pages', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),

			array(
				'name'            => __( 'Select Products', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'              => '_wcct_coupons_notice_products',
				'type'            => 'wcct_post_select',
				'row_classes'     => array( '' ),
				'options_name_cb' => array( 'WCCT_Admin_CMB2_Support', 'cmb2_product_title' ),
				'attributes'      => array(
					'data-conditional-id'         => '_wcct_coupons_enable',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_coupons_notice_show',
					'data-wcct-conditional-value' => 'custom',
					'multiple'                    => 'multiple',
					'name'                        => '_wcct_coupons_notice_products[]',
					'class'                       => 'ajax_chosen_select_products',
					'data-placeholder'            => __( 'Search For a Product...', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
			),

			array(
				'id'          => '_wcct_coupons_notice_pages',
				'name'        => __( 'Select Page', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'        => 'wcct_multiselect',
				'row_classes' => array( 'row_title_classes', 'wcct_cmb2_chosen' ),
//				'options_cb'  => array( 'WCCT_Admin_CMB2_Support', 'get_pages_selected' ),
//				'before'      => array( 'WCCT_Admin_CMB2_Support', 'wcct_pages_set_field_data_attr' ),
				'attributes'  => array(
					'data_cpt_cb'                 => 'get_pages_cmb2',
					'data-conditional-id'         => '_wcct_coupons_enable',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_coupons_notice_show',
					'data-wcct-conditional-value' => 'custom',
					'multiple'                    => 'multiple',
					'name'                        => '_wcct_coupons_notice_pages[]',
					'data-placeholder'            => __( 'Choose Pages', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
			),
			array(
				'name'        => __( 'Show Coupon Success Message After Add To Cart', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_notice_after_add_to_cart',
				'type'        => 'radio_inline',
				'desc'        => __( 'Choose \'Yes\' to show success message (One Time). It will appear right after user has added product to the cart.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'wcct_pb10', 'wcct_light_desc' ),
				'options'     => array(
					'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),


			array(
				'name'        => __( 'Coupon Expiry Message', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_failure_message',
				'type'        => 'textarea_small',
				'desc'        => __( 'Display this text when coupon expires.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'row_title_classes', 'wcct_light_desc' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Coupon Cart Table Message', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_cart_message',
				'type'        => 'textarea_small',
				'desc'        => __( 'Display this text in Cart Totals on Cart/ Checkout page.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'row_title_classes', 'wcct_light_desc' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Empty Cart Error Message', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_coupons_empty_cart_message',
				'type'        => 'textarea_small',
				'desc'        => __( 'Display this text when cart is empty and coupon tries to apply.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'row_title_classes', 'wcct_light_desc' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_coupons_enable',
					'data-conditional-value' => '1',
				),
			),
		),
	),
	array(
		'id'       => 'wcct_appearance_settings',
		'title'    => __( '<i class="flicon flicon-old-elevator-levels-tool"></i> Elements', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '<span class="wcct_load_spin wcct_load_tab_appearance"></span>',
		'position' => 15,
		'fields'   => apply_filters( 'wcct_campaign_elements_fields', array(
			// countdown timer
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'                       => '_wcct_location_timer_show_single',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border', 'wcct_pb10' ),
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'wcct_accordion_title'     => __( 'Countdown Timer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'id'          => '_wcct_location_timer_show_single_html',
				'content'     => $elements_ct_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => 'Position',
				'id'          => '_wcct_location_timer_single_location',
				'type'        => 'select',
				'row_classes' => array( 'wcct_no_border' ),
				'options'     => array(
					'1'    => __( 'Above the Title', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'2'    => __( 'Below the Title', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'3'    => __( 'Below the Review Rating', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'4'    => __( 'Below the Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'5'    => __( 'Below Short Description', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'6'    => __( 'Below Add to Cart Button', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none' => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'id'          => '_wcct_location_timer_show_single_html_below',
				'content'     => $position_mismatch_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Countdown Timer',
				'id'          => '_wcct_appearance_timer_single_skin',
				'type'        => 'radio_inline',
				'before'      => '<p class="wcct_mt5 wcct_mb5">Skins</p>',
				'row_classes' => array( 'wcct_no_border', 'wcct_pb0' ),
				'options'     => array(
					'highlight_1'  => __( 'Highlight', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'round_fill'   => __( 'Round Fill', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'round_ghost'  => __( 'Round Ghost', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'square_fill'  => __( 'Square Fill', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'square_ghost' => __( 'Square Ghost', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'default'      => __( 'Default', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'content'     => '<img src="//storage.googleapis.com/xl-finale/timer_circle.jpg" />',
				'id'          => '_wcct_appearance_timer_single_round_fill_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'round_fill',
				),
			),
			array(
				'content'     => '<img src="//storage.googleapis.com/xl-finale/timer_ghost.jpg" />',
				'id'          => '_wcct_appearance_timer_single_round_ghost_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'round_ghost',
				),
			),
			array(
				'content'     => '<img src="//storage.googleapis.com/xl-finale/timer_square.jpg" />',
				'id'          => '_wcct_appearance_timer_single_square_fill_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'square_fill',
				),
			),
			array(
				'content'     => '<img src="//storage.googleapis.com/xl-finale/timer_square_ghost.jpg" />',
				'id'          => '_wcct_appearance_timer_single_square_ghost_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'square_ghost',
				),
			),
			array(
				'content'     => '<img src="//storage.googleapis.com/xl-finale/timer_text.jpg" />',
				'id'          => '_wcct_appearance_timer_single_highlight_1_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'highlight_1',
				),
			),
			array(
				'content'     => '<img src="//storage.googleapis.com/xl-finale/timer_text_simple.jpg" />',
				'id'          => '_wcct_appearance_timer_single_default_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'default',
				),
			),
			array(
				'content'     => __( 'Note: You may need to adjust the default appearance settings in case you switch the skin.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_html_coutdown_help_1',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_no_border', 'wcct_pb0' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
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
				'name'        => __( 'Text Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
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
				'name'        => __( 'Timer Font Size (px)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
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
				'name'        => __( 'Font Size', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
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
				'name'        => __( 'Timer Days', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
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
				'name'        => __( 'Timer Hours', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_timer_single_label_hrs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">hours</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Minutes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_timer_single_label_mins',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">minutes</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Seconds', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_timer_single_label_secs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">seconds</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Display Single Product', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_timer_single_display',
				'type'        => 'textarea_small',
				'desc'        => '{{countdown_timer}}: ' . __( 'Outputs the countdown timer.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' <br/><a href="javascript:void(0);" onclick="wcct_show_tb(\'' . __( 'Merge Tags', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '\',\'wcct_merge_tags_help\');">' . __( 'Click here to learn to set up more dynamic merge tags in countdown timer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_light_desc', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Display</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Single Border Style', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_timer_single_border_style',
				'type'        => 'select',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Style</p>',
				'options'     => array(
					'dotted' => __( 'Dotted', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'dashed' => __( 'Dashed', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'solid'  => __( 'Solid', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'double' => __( 'Double', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none'   => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Single Border Width', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_timer_single_border_width',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Width (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Single Border Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_timer_single_border_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_gap', 'wcct_hide_label', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Color</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Mobile Timer',
				'id'          => '_wcct_appearance_timer_mobile_reduction',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_text_color' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Reduce Countdown Timer Size on Mobile (%)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Advanced',
				'id'          => '_wcct_appearance_timer_single_delay',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_td_mt_5', 'wcct_no_border', 'wcct_border_top' ),
				'desc'        => __( 'Enable this to delay showing Countdown Timer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Hide Countdown Timer until this many hrs left',
				'id'          => '_wcct_appearance_timer_single_delay_hrs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_text_extra_small', 'wcct_text_inline', 'wcct_pt5' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Hide Countdown Timer until</p>',
				'after_row'   => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'desc'        => __( 'hrs left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_delay',
					'data-wcct-conditional-value' => 'on',
				),
			),
			// counter bar
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'                       => '_wcct_location_bar_show_single',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border', 'wcct_pb10' ),
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'wcct_accordion_title'     => __( 'Counter Bar', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'id'          => '_wcct_location_bar_show_single_html',
				'content'     => $elements_cb_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => 'Position',
				'id'          => '_wcct_location_bar_single_location',
				'type'        => 'select',
				'row_classes' => array( 'wcct_no_border' ),

				'options'    => array(
					'1'    => __( 'Above the Title', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'2'    => __( 'Below the Title', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'3'    => __( 'Below the Review Rating', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'4'    => __( 'Below the Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'5'    => __( 'Below Short Description', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'6'    => __( 'Below Add to Cart Button', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none' => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes' => array(
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_deal_enable_goal',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'id'          => '_wcct_location_bar_show_single_html_below',
				'content'     => $position_mismatch_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Counter Bar', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_single_skin',
				'type'        => 'radio_inline',
				'options'     => array(
					'stripe_animate' => '<img src="//storage.googleapis.com/xl-finale/bar-capsule-animated.gif" />',
					'stripe'         => '<img src="//storage.googleapis.com/xl-finale/bar-capsule-lines.jpg" />',
					'fill'           => '<img src="//storage.googleapis.com/xl-finale/bar-capsule.jpg" />',
				),
				'row_classes' => array( 'wcct_img_options', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Skins</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'id'          => '_wcct_appearance_bar_single_skin_html',
				'content'     => $elements_cb_skin_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Edges', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_single_edges',
				'type'        => 'radio_inline',
				'options'     => array(
					'rounded' => __( 'Rounded', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'smooth'  => __( 'Smooth', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'sharp'   => __( 'Sharp', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Edges</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Direction', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_single_orientation',
				'type'        => 'radio_inline',
				'options'     => array(
					'ltr' => __( 'Left to Right', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ( <i class="dashicons dashicons-arrow-right-alt"></i> )',
					'rtl' => __( 'Right to Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' ( <i class="dashicons dashicons-arrow-left-alt"></i> )',
				),
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Direction</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'content'     => __( 'This moves counter bar left to right. Use this when you want to indicate increase in sales.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_ltr_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_no_border', 'wcct_pb0' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_orientation',
					'data-wcct-conditional-value' => 'ltr',
				),
			),
			array(
				'content'     => __( 'This moves counter bar right to left. Use this when you want to indicate decrease in stocks.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_rtl_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_no_border', 'wcct_pb0' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_orientation',
					'data-wcct-conditional-value' => 'rtl',
				),
			),
			array(
				'name'        => __( 'Counter Bar', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_single_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background/Border</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Bar Active Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_single_active_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Active</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Bar Height', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
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
				'name'        => __( 'Bar Display', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_single_display',
				'type'        => 'textarea_small',
				'desc'        => '{{counter_bar}}: ' . __( 'Outputs the counter bar.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' <br/><a href="javascript:void(0);" onclick="wcct_show_tb(\'Counter Bar Merge Tags\',\'wcct_merge_tags_invenotry_bar_help\');">Click here to learn to set up more dynamic merge tags in counter bar</a>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_light_desc', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Display</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Bar Border Style', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_single_border_style',
				'type'        => 'select',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Style</p>',
				'options'     => array(
					'dotted' => __( 'Dotted', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'dashed' => __( 'Dashed', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'solid'  => __( 'Solid', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'double' => __( 'Double', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none'   => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Bar Border Width', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_single_border_width',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Width (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Bar Border Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_bar_single_border_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_gap', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Color</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Advanced',
				'id'          => '_wcct_appearance_bar_single_delay',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_td_mt_5', 'wcct_no_border' ),
				'desc'        => __( 'Enable this to delay showing Counter Bar', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Hide Counter Bar Until This Many Items Sold',
				'id'          => '_wcct_appearance_bar_single_delay_item',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_text_extra_small', 'wcct_text_inline', 'wcct_pt5', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Hide Counter Bar Until</p>',
				'desc'        => __( 'item(s) sold', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_delay',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'id'          => '_wcct_appearance_bar_single_delay_item_html',
				'content'     => __( '<strong>Example:</strong> If set to 2 and product stock is 10 then the counter bar will display after 2 product units are sold.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_delay',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => 'Hide Counter Bar Until This Many Items Remaining',
				'id'          => '_wcct_appearance_bar_single_delay_item_remaining',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_text_extra_small', 'wcct_text_inline', 'wcct_pt5' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Hide Counter Bar Until</p>',
				'after_row'   => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'desc'        => __( 'item(s) left in stock', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '1',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_delay',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'id'          => '_wcct_appearance_bar_single_delay_item_remaining_html',
				'content'     => __( '<strong>Example:</strong> If set to 5 and product stock is 10 then the counter bar will display when only 5 product units are left.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_delay',
					'data-wcct-conditional-value' => 'on',
				),
			),
			// sticky header
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'                       => '_wcct_location_timer_show_sticky_header',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border', 'wcct_pb10' ),
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'wcct_accordion_title'     => __( 'Sticky Header', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'id'          => '_wcct_location_timer_show_sticky_header_html',
				'content'     => $elements_sh_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_no_border', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => 'Visibility',
				'id'          => '_wcct_appearance_sticky_header_hide_mobile',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_pb0', 'wcct_pt0' ),
				'desc'        => __( 'Hide Sticky Header on Mobile', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Tablet Visibility',
				'id'          => '_wcct_appearance_sticky_header_hide_tablet',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_pb0' ),
				'desc'        => __( 'Hide Sticky Header on Tablet', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Desktop Visibility',
				'id'          => '_wcct_appearance_sticky_header_hide_desktop',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_hide_label' ),
				'desc'        => __( 'Hide Sticky Header on Desktop', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Background Color',
				'id'          => '_wcct_appearance_sticky_header_wrap_bg',
				'type'        => 'colorpicker',
				'row_classes' => array( '' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Headline',
				'id'          => '_wcct_appearance_sticky_header_headline',
				'type'        => 'text',
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text</p>',
				'row_classes' => array( 'wcct_no_border', 'wcct_pb0' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Headline', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_headline_font_size',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Font Size (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Headline Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_headline_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Color</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Headline Alignment', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_headline_alignment',
				'type'        => 'select',
				'options'     => array(
					'left'   => __( 'Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'center' => __( 'Center', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'right'  => __( 'Right', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Alignment</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Sub Headline',
				'id'          => '_wcct_appearance_sticky_header_description',
				'desc'        => '<a href="javascript:void(0);" onclick="wcct_show_tb(\'' . __( 'Merge Tags', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '\',\'wcct_merge_tags_help\');">' . __( 'Click here to learn to set up more dynamic merge tags in sticky header', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>',
				'type'        => 'textarea_small',
				'row_classes' => array( 'wcct_light_desc', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Description Font Size', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_description_font_size',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Font Size (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Description Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_description_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Color</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Description Alignment', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_description_alignment',
				'type'        => 'select',
				'options'     => array(
					'left'   => __( 'Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'center' => __( 'Center', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'right'  => __( 'Right', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Alignment</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Mobile View',
				'id'          => '_wcct_appearance_sticky_header_sub_headline_hide_mobile',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_td_mt_5', 'wcct_hide_label' ),
				'desc'        => __( 'Hide Sub Headline on Mobile', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Countdown Timer',
				'id'          => '_wcct_appearance_sticky_header_disable_timer',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_no_border' ),
				'desc'        => __( 'Disable Countdown Timer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Countdown Timer',
				'id'          => '_wcct_appearance_sticky_header_skin',
				'type'        => 'radio_inline',
				'before'      => '<p class="wcct_mt5 wcct_mb5">Skins</p>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_no_border', 'wcct_p0', 'wcct_timer_select' ),
				'options'     => array(
					'highlight_1'  => __( 'Highlight', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'round_fill'   => __( 'Round Fill', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'round_ghost'  => __( 'Round Ghost', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'square_fill'  => __( 'Square Fill', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'square_ghost' => __( 'Square Ghost', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'default'      => __( 'Default', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
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
				'content'     => __( 'Note: You may need to adjust the default appearance settings in case you switch the default skin.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_html_coutdown_help_3',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background/Border</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Text Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_text_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Label</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Font Size (px)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_font_size_timer',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Timer Font Size (px)</p>',
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Font Size', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_font_size',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Label Font Size (px)</p>',
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Days', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_label_days',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Timer Labels</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">days</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Hours', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_label_hrs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">hours</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Minutes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_label_mins',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">minutes</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Seconds', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_label_secs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">seconds</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Border Style', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_timer_border_style',
				'type'        => 'select',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Style</p>',
				'options'     => array(
					'dotted' => __( 'Dotted', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'dashed' => __( 'Dashed', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'solid'  => __( 'Solid', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'double' => __( 'Double', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none'   => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Border Width', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_timer_border_width',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Width (px)</p>',
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Border Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_timer_border_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_gap', 'wcct_hide_label', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Color</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => 'Mobile Timer',
				'id'          => '_wcct_appearance_sticky_header_timer_mobile_reduction',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_text_color', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Reduce Countdown Timer Size on Mobile (%)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>',
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => 'Timer Position',
				'id'          => '_wcct_appearance_sticky_header_timer_position',
				'type'        => 'radio_inline',
				'before'      => '<p class="wcct_mt5 wcct_mb5">Alignment</p>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border' ),
				'options'     => array(
					'left'   => __( 'Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'center' => __( 'Center', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'right'  => __( 'Right', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => 'Button',
				'id'          => '_wcct_appearance_sticky_header_enable_button',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_td_mt_5', 'wcct_no_border', 'wcct_border_top' ),
				'desc'        => __( 'Enable', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Button Skins',
				'id'          => '_wcct_appearance_sticky_header_button_skin',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_hide_label', 'wcct_img_options', 'wcct_p0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Skins</p>',
				'options'     => array(
					'button_1' => '<img src="//storage.googleapis.com/xl-finale/button_rounded.jpg" />',
					'button_2' => '<img src="//storage.googleapis.com/xl-finale/button_more_rounded.jpg" />',
					'button_3' => '<img src="//storage.googleapis.com/xl-finale/button_ghost.jpg" />',
					'button_4' => '<img src="//storage.googleapis.com/xl-finale/button_shadow.jpg" />',
					'button_5' => '<img src="//storage.googleapis.com/xl-finale/button_edge.jpg" />',
					'button_6' => '<img src="//storage.googleapis.com/xl-finale/button_arrows.jpg" />',
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => 'Button Text',
				'id'          => '_wcct_appearance_sticky_header_button_text',
				'type'        => 'textarea_small',
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text</p>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => 'Button BG Color',
				'id'          => '_wcct_appearance_sticky_header_button_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background/Border</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => 'Button Text Color',
				'id'          => '_wcct_appearance_sticky_header_button_text_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text Color</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => 'Button Action',
				'id'          => '_wcct_appearance_sticky_header_button_action',
				'type'        => 'text',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">URL</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_header_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => __( 'Show this header after ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_header_delay',
				'type'        => 'text_small',
				'desc'        => __( ' seconds.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'after_row'   => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'row_classes' => array( 'wcct_border_top' ),
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_header',
					'data-conditional-value' => '1',
				),
			),
			// sticky footer
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'                       => '_wcct_location_timer_show_sticky_footer',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border', 'wcct_pb10' ),
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'wcct_accordion_title'     => __( 'Sticky Footer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'id'          => '_wcct_location_timer_show_sticky_footer_html',
				'content'     => $elements_sf_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_no_border', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => 'Visibility',
				'id'          => '_wcct_appearance_sticky_footer_hide_mobile',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_pb0', 'wcct_pt0' ),
				'desc'        => __( 'Hide Sticky Footer on Mobile', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Tablet Visibility',
				'id'          => '_wcct_appearance_sticky_footer_hide_tablet',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_pb0' ),
				'desc'        => __( 'Hide Sticky Footer on Tablet', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Desktop Visibility',
				'id'          => '_wcct_appearance_sticky_footer_hide_desktop',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_hide_label' ),
				'desc'        => __( 'Hide Sticky Footer on Desktop', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Background Color',
				'id'          => '_wcct_appearance_sticky_footer_wrap_bg',
				'type'        => 'colorpicker',
				'row_classes' => array( '' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Headline',
				'id'          => '_wcct_appearance_sticky_footer_headline',
				'type'        => 'text',
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text</p>',
				'row_classes' => array( 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Headline', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_headline_font_size',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Font Size (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Headline Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_headline_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Color</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Headline Alignment', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_headline_alignment',
				'type'        => 'select',
				'options'     => array(
					'left'   => __( 'Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'center' => __( 'Center', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'right'  => __( 'Right', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Alignment</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Sub Headline',
				'id'          => '_wcct_appearance_sticky_footer_description',
				'desc'        => '<a href="javascript:void(0);" onclick="wcct_show_tb(\'' . __( 'Merge Tags', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '\',\'wcct_merge_tags_help\');">' . __( 'Click here to learn to set up more dynamic merge tags in sticky footer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>',
				'type'        => 'textarea_small',
				'row_classes' => array( 'wcct_light_desc', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Description', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_description_font_size',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Font Size (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Description Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_description_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Color</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Description Alignment', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_description_alignment',
				'type'        => 'select',
				'options'     => array(
					'left'   => __( 'Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'center' => __( 'Center', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'right'  => __( 'Right', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_no_border', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Alignment</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Mobile View',
				'id'          => '_wcct_appearance_sticky_footer_sub_headline_hide_mobile',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_td_mt_5', 'wcct_hide_label' ),
				'desc'        => __( 'Hide Sub Headline on Mobile', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Countdown Timer',
				'id'          => '_wcct_appearance_sticky_footer_disable_timer',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_no_border' ),
				'desc'        => __( 'Disable Countdown Timer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Countdown Timer',
				'id'          => '_wcct_appearance_sticky_footer_skin',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_no_border', 'wcct_p0', 'wcct_hide_label', 'wcct_timer_select' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Skins</p>',
				'options'     => array(
					'highlight_1'  => __( 'Highlight', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'round_fill'   => __( 'Round Fill', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'round_ghost'  => __( 'Round Ghost', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'square_fill'  => __( 'Square Fill', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'square_ghost' => __( 'Square Ghost', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'default'      => __( 'Default', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
				'after'       => '<div class="wcct_appearance_sticky_bar_img" data-type="footer">
					<img data-type="round_fill" src="//storage.googleapis.com/xl-finale/timer_sticky_circle.jpg" />
					<img data-type="round_ghost" src="//storage.googleapis.com/xl-finale/timer_sticky_ghost.jpg" />
					<img data-type="square_fill" src="//storage.googleapis.com/xl-finale/timer_sticky_square.jpg" />
					<img data-type="square_ghost" src="//storage.googleapis.com/xl-finale/timer_sticky_square_ghost.jpg" />
					<img data-type="highlight_1" src="//storage.googleapis.com/xl-finale/timer_sticky_text.jpg" />
					<img data-type="default" src="//storage.googleapis.com/xl-finale/timer_sticky_text_simple.jpg" />
					</div>',
			),
			array(
				'content'     => __( 'Note: You may need to adjust the default appearance settings in case you switch the default skin.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_html_coutdown_help_2',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background/Border</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Text Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_text_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Label</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Font Size (px)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_font_size_timer',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Timer Font Size (px)</p>',
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Font Size', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_font_size',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Label Font Size (px)</p>',
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Days', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_label_days',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Timer Labels</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">days</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Hours', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_label_hrs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">hours</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Minutes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_label_mins',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">minutes</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Seconds', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_label_secs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">seconds</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Border Style', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_timer_border_style',
				'type'        => 'select',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Style</p>',
				'options'     => array(
					'dotted' => __( 'Dotted', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'dashed' => __( 'Dashed', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'solid'  => __( 'Solid', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'double' => __( 'Double', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none'   => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Border Width', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_timer_border_width',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Width (px)</p>',
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => __( 'Timer Border Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_timer_border_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_gap', 'wcct_hide_label', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Color</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => 'Mobile Timer',
				'id'          => '_wcct_appearance_sticky_footer_timer_mobile_reduction',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_text_color', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Reduce Countdown Timer Size on Mobile (%)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>',
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => 'Timer Position',
				'id'          => '_wcct_appearance_sticky_footer_timer_position',
				'type'        => 'radio_inline',
				'before'      => '<p class="wcct_mt5 wcct_mb5">Alignment</p>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border' ),
				'options'     => array(
					'left'   => __( 'Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'center' => __( 'Center', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'right'  => __( 'Right', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_disable_timer',
					'data-wcct-conditional-value' => 'off',
				),
			),
			array(
				'name'        => 'Button',
				'id'          => '_wcct_appearance_sticky_footer_enable_button',
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_td_mt_5', 'wcct_no_border', 'wcct_border_top' ),
				'desc'        => __( 'Enable', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Button Skins',
				'id'          => '_wcct_appearance_sticky_footer_button_skin',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_hide_label', 'wcct_img_options', 'wcct_p0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Skins</p>',
				'options'     => array(
					'button_1' => '<img src="//storage.googleapis.com/xl-finale/button_rounded.jpg" />',
					'button_2' => '<img src="//storage.googleapis.com/xl-finale/button_more_rounded.jpg" />',
					'button_3' => '<img src="//storage.googleapis.com/xl-finale/button_ghost.jpg" />',
					'button_4' => '<img src="//storage.googleapis.com/xl-finale/button_shadow.jpg" />',
					'button_5' => '<img src="//storage.googleapis.com/xl-finale/button_edge.jpg" />',
					'button_6' => '<img src="//storage.googleapis.com/xl-finale/button_arrows.jpg" />',
				),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => 'Button Text',
				'id'          => '_wcct_appearance_sticky_footer_button_text',
				'type'        => 'textarea_small',
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text</p>',
				'row_classes' => array( 'wcct_hide_label', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => 'Button BG Color',
				'id'          => '_wcct_appearance_sticky_footer_button_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background/Border</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => 'Button Text Color',
				'id'          => '_wcct_appearance_sticky_footer_button_text_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text Color</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => 'Button Action',
				'id'          => '_wcct_appearance_sticky_footer_button_action',
				'type'        => 'text',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">URL</p>',
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_sticky_footer_enable_button',
					'data-wcct-conditional-value' => 'on',
				),
			),
			array(
				'name'        => __( 'Show this footer after ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_sticky_footer_delay',
				'type'        => 'text_small',
				'desc'        => __( ' seconds.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'wcct_border_top' ),
				'after_row'   => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_sticky_footer',
					'data-conditional-value' => '1',
				),
			),
			// custom text
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'                       => '_wcct_location_show_custom_text',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border', 'wcct_pb10' ),
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'wcct_accordion_title'     => __( 'Custom Text', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'id'          => '_wcct_location_show_custom_text_html',
				'content'     => $custom_text_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => 'Position',
				'id'          => '_wcct_location_custom_text_location',
				'type'        => 'select',
				'row_classes' => array( 'wcct_no_border' ),

				'options'    => array(
					'1'    => __( 'Above the Title', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'2'    => __( 'Below the Title', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'3'    => __( 'Below the Review Rating', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'4'    => __( 'Below the Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'5'    => __( 'Below Short Description', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'6'    => __( 'Below Add to Cart Button', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none' => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes' => array(
					'data-conditional-id'    => '_wcct_location_show_custom_text',
					'data-conditional-value' => '1',
				),
			),
			array(
				'id'          => '_wcct_location_custom_show_single_html_below',
				'content'     => $position_mismatch_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_show_custom_text',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Custom Text',
				'id'          => '_wcct_appearance_custom_text_description',
				'type'        => 'textarea_small',
				'desc'        => '<a href="javascript:void(0);" onclick="wcct_show_tb(\'' . __( 'Merge Tags', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '\',\'wcct_merge_tags_help\');">' . __( 'Click here to learn to set up more dynamic merge tags in custom text', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>',
				'row_classes' => array( 'wcct_light_desc', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_show_custom_text',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'BG Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_custom_text_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Background Color</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_show_custom_text',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Text Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_custom_text_text_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Text Color</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_show_custom_text',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Font Size', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_custom_text_font_size',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Font Size (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_show_custom_text',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Border Style', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_custom_text_border_style',
				'type'        => 'select',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Style</p>',
				'options'     => array(
					'dotted' => __( 'Dotted', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'dashed' => __( 'Dashed', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'solid'  => __( 'Solid', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'double' => __( 'Double', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none'   => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_show_custom_text',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Border Width', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_custom_text_border_width',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Width (px)</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_show_custom_text',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Border Color', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_appearance_custom_text_border_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_gap', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">Border Color</p>',
				'after_row'   => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_show_custom_text',
					'data-conditional-value' => '1',
				),
			),
			// custom css
			array(
				'name'                     => __( 'CSS', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'                       => '_wcct_appearance_custom_css',
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'row_classes'              => array( 'wcct_textarea_full' ),
				'wcct_accordion_title'     => __( 'Custom CSS', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => false,
				'after_row'                => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'desc'                     => __( 'Enter Custom CSS to modify the visual.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'                     => 'textarea',
			),
		) )
	),
	array(
		'id'       => 'wcct_events_settings',
		'title'    => __( '<i class="flicon flicon-speaker"></i> Events', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		'position' => 18,
		'fields'   => array(
			array(
				'name'        => __( 'Enable Events', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_events_enable',
				'type'        => 'wcct_switch',
				'desc'        => $events_content,
				'row_classes' => array( 'wcct_no_border', 'wcct_pb10', 'wcct_light_desc', 'wcct_dashicons_color' ),
				'label'       => array(
					'on'  => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'off' => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
			),
			array(
				'id'           => '_wcct_events',
				'type'         => 'group',
				'before_group' => array( 'WCCT_Admin_CMB2_Support', 'cmb2_wcct_before_call' ),
				'after_group'  => array( 'WCCT_Admin_CMB2_Support', 'cmb2_wcct_after_call' ),
				'repeatable'   => true,
				'attributes'   => array(
					'class'                  => 'wcct_events_group',
					'data-conditional-id'    => '_wcct_events_enable',
					'data-conditional-value' => '1',
				),
				'options'      => array(
					'group_title'   => __( 'Event', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'add_button'    => __( 'Add New Event', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'remove_button' => '<i class="dashicons dashicons-no"></i>',
					'sortable'      => true,
					'closed'        => false,
				),
				'fields'       => array(
					array(
						'name'        => __( '<span class="wcct_option_entity"></span>', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'entity',
						'type'        => 'select',
						'row_classes' => array( 'wcct_combine_2_field_event_middle', 'wcct_text_small' ),
						'options'     => array(
							'regular_price'  => __( 'Regular Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'discount'       => __( 'Discount', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'available_unit' => __( 'Available Units', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'sold_unit'      => __( 'Sold Units', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						),
					),
					array(
						'name'        => __( 'Operator', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'operator_discount',
						'type'        => 'select',
						'row_classes' => array(
							'wcct_remove_label',
							'wcct_combine_2_field_event_middle',
							'wcct_text_extra_small',
							'wcct_select_change',
						),
						'options'     => array(
							'=' => '=',
						),
						'attributes'  => array(
							'data-conditional-id'    => json_encode( array( '_wcct_events', 'entity' ) ),
							'data-conditional-value' => 'discount',
							'data-change'            => 'entity',
						),
					),
					array(
						'name'        => __( 'Operator', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'operator_rest',
						'type'        => 'select',
						'row_classes' => array(
							'wcct_remove_label',
							'wcct_combine_2_field_event_middle',
							'wcct_text_extra_small',
							'wcct_select_change',
						),
						'options'     => array(
							'+' => '+',
							'-' => '-',
						),
						'attributes'  => array(
							'data-conditional-id'    => json_encode( array( '_wcct_events', 'entity' ) ),
							'data-conditional-value' => json_encode( array(
								'regular_price',
								'available_unit',
								'sold_unit',
							) ),
							'data-change'            => 'entity',
						),
					),
					array(
						'name'        => __( 'By', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'number',
						'type'        => 'text_small',
						'row_classes' => array(
							'wcct_remove_label',
							'wcct_combine_2_field_event_middle',
							'wcct_text_extra_small',
						),
						'attributes'  => array(
							'placeholder' => __( '5 or 5%', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						),
					),
					array(
						'name'        => __( 'When', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'event',
						'type'        => 'select',
						'row_classes' => array( 'wcct_combine_2_field_event_middle', 'wcct_text_small' ),
						'options'     => array(
							'units_left' => __( 'Unit(s) Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'units_sold' => __( 'Unit(s) Sold', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'days_left'  => __( 'Day(s) Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
							'hrs_left'   => __( 'Hours Left', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						),
						'attributes'  => array(
							'data-change' => 'event_value',
						),
					),
					array(
						'name'        => __( 'between', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'event_value_min',
						'type'        => 'text_small',
						'row_classes' => array( 'wcct_combine_2_field_event_middle', 'wcct_text_extra_small' ),
						// wcct_text_extra_small
						'attributes'  => array(
							'type'        => 'number',
							'min'         => '0',
							'pattern'     => '\d*',
							'placeholder' => __( 'Min', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						),
					),
					array(
						'name'        => __( 'and', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'          => 'event_value_max',
						'type'        => 'text_small',
						'row_classes' => array( 'wcct_combine_2_field_event_end', 'wcct_text_extra_small' ),
						'attributes'  => array(
							'type'        => 'number',
							'min'         => '0',
							'pattern'     => '\d*',
							'placeholder' => __( 'Max', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						),
					),
				),
			),
		),
	),
	array(
		'id'       => 'wcct_actions_settings',
		'title'    => __( '<i class="flicon flicon-shuffle-crossing-arrows"></i> Actions', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		'position' => 21,
		'fields'   => array(
			// After campaign ends
			array(
				'id'                       => '_wcct_actions_after_campaign_html',
				'content'                  => __( 'Set below actions to change Stock Status & Add to Cart Button Visibility <strong>after campaign ends</strong>.<br/>Choose "Do Nothing" if you don\'t want to set any actions.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'                     => 'wcct_html_content_field',
				'row_classes'              => array(
					'row_title_classes',
					'wcct_small_text',
					'wcct_pb0',
					'wcct_no_border',
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'wcct_accordion_title'     => __( 'After Campaign Ends', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'name'    => 'Product Stock Status',
				'id'      => '_wcct_actions_after_end_stock',
				'type'    => 'radio_inline',
				'options' => array(
					'in-stock'     => __( 'In Stock', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'out-of-stock' => __( 'Out of Stock', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none'         => __( 'Do Nothing', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
			),
			array(
				'name'      => 'Add to Cart Button',
				'id'        => '_wcct_actions_after_end_add_to_cart',
				'type'      => 'radio_inline',
				'options'   => array(
					'show' => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'hide' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none' => __( 'Do Nothing', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'after_row' => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
			),
			// During campaign
			array(
				'id'                       => '_wcct_actions_during_campaign_html',
				'content'                  => __( 'Set below actions to change Stock Status & Add to Cart Button Visibility <strong>during campaign</strong>.<br/>Choose "Do Nothing" if you don\'t want to set any actions.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'                     => 'wcct_html_content_field',
				'row_classes'              => array(
					'row_title_classes',
					'wcct_small_text',
					'wcct_pb0',
					'wcct_no_border',
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'wcct_accordion_title'     => __( 'During Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'name'    => 'Product Stock Status',
				'id'      => '_wcct_actions_during_stock',
				'type'    => 'radio_inline',
				'options' => array(
					'in-stock'     => __( 'In Stock', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'out-of-stock' => __( 'Out of Stock', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none'         => __( 'Do Nothing', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
			),
			array(
				'name'      => 'Add to Cart Button',
				'id'        => '_wcct_actions_during_add_to_cart',
				'type'      => 'radio_inline',
				'options'   => array(
					'show' => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'hide' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'none' => __( 'Do Nothing', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				),
				'after_row' => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
			),
			array(
				'name'        => __( 'Actions Help', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_actions_help_html',
				'type'        => 'wcct_html_content_field',
				'content'     => __( 'Need Help? See docs on', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' "<a target="_blank" href="' . add_query_arg( array(
						'utm_source'   => 'plugin-admin',
						'utm_campaign' => 'finale',
						'utm_medium'   => 'form_fields',
						'utm_term'     => 'Help',
					), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/actions/' ) . '">' . __( 'Actions Management', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '"</a> <i class="dashicons dashicons-editor-help"></i>',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_hide_label', 'wcct_html_help' ),
			),
		),
	),
	array(
		'id'       => 'wcct_misc_settings',
		'title'    => __( '<i class="flicon flicon-gear-configuration-interface"></i> Advanced', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		'position' => 24,
		'fields'   => apply_filters( 'wcct_campaign_misc_settings_fields', array(
			//            array(
			//                'content' => __('Misc Settings', 'finale-woocommerce-sales-countdown-timer-discount-plugin'),
			//                'id' => '_wcct_misc_title',
			//                'type' => 'wcct_html_content_field',
			//                'row_classes' => array('row_title_classes'),
			//            ),
			array(
				'name'        => __( 'Add to Cart Button Text', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_misc_add_to_cart_btn_text_enable',
				'desc'        => __( 'Enable this to change `Add to Cart` button text during campaign.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'type'        => 'checkbox',
				'row_classes' => array( 'wcct_td_mt_5', 'wcct_no_border' ),
			),
			array(
				'name'        => __( 'Text', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_misc_add_to_cart_btn_text',
				'type'        => 'text',
				'row_classes' => array( 'row_title_classes', 'wcct_hide_label', 'wcct_no_border', 'wcct_p0' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_misc_add_to_cart_btn_text_enable',
					'data-conditional-value' => 'on',
				),
			),
			array(
				'name'        => __( 'Exclude Product Types', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_misc_add_to_cart_btn_exclude',
				'type'        => 'wcct_multiselect',
				'description' => __( 'Some product types such as variable products require product selection before they can be added to cart. Usually their grids would show "Select Options".', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '<br/>' . __( 'Excluding such product types from grid will NOT change the text of buttons on the grid, even though their product page will show entered text.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '<br/>' . __( 'Example: If you change button text to say "Buy Now" and exclude variable products on grid. Button for this product on grid will show "Select Options" while button of product will show "Buy Now".', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'wcct_radio_btn', 'wcct_no_border', 'wcct_pt5', 'wcct_cmb2_chosen', 'wcct_hide_label', 'wcct_light_desc' ),
				'options_cb'  => array( 'WCCT_Admin_CMB2_Support', 'get_product_types' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( "Don't change above 'Add to Cart' text on following Product Types in Shop/ Grid", 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>',
				'attributes'  => array(
					'multiple'               => 'true',
					'name'                   => '_wcct_misc_add_to_cart_btn_exclude[]',
					'data-conditional-id'    => '_wcct_misc_add_to_cart_btn_text_enable',
					'data-conditional-value' => 'on',
					'data-placeholder'       => __( 'Choose Product Types' ),

				),
			),
			array(
				'name'        => __( 'Countdown Timer Expiry Text', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_misc_timer_expiry_text',
				'type'        => 'textarea_small',
				'desc'        => __( 'Display text in place of Countdown Timer after the campaign ends.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'row_classes' => array( 'row_title_classes', 'wcct_border_top', 'wcct_light_desc' ),
			),
			array(
				'name'        => __( 'Show Sticky Header (or Footer) after', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_misc_cookie_expire_time',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_text_small' ),
				'desc'        => __( 'seconds once user closes it. Ex: 3600 secs = 1 hour.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'attributes'  => array(
					'type'        => 'number',
					'min'         => '0',
					'pattern'     => '\d*',
					'placeholder' => '1',
				),
			),
			array(
				'name'        => __( 'Timer Labels', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_misc_timer_label_days',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color' ),
				'after'       => '<p class="wcct_mt5 wcct_mb5">days</p>',

			),
			array(
				'name'        => __( 'Timer Hours', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_misc_timer_label_hrs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'after'       => '<p class="wcct_mt5 wcct_mb5">hours</p>',

			),
			array(
				'name'        => __( 'Timer Minutes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_misc_timer_label_mins',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'after'       => '<p class="wcct_mt5 wcct_mb5">minutes</p>',

			),
			array(
				'name'        => __( 'Timer Seconds', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'id'          => '_wcct_misc_timer_label_secs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap' ),
				'after'       => '<p class="wcct_mt5 wcct_mb5">seconds</p>',

			),
		) ),
	),
) );
