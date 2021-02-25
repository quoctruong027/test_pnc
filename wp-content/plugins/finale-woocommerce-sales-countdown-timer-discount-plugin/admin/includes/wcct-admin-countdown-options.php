<?php

class WCCT_Admin_CountDown_Post_Options {

	protected static $options_data = false;

	/**
	 * Option key, and option page slug
	 * @var string
	 */
	private static $key = 'wcct_post_option';

	/**
	 * Options page metabox id
	 * @var string
	 */
	private static $metabox_id = 'wcct_post_option_metabox';

	/**
	 * Setting Up CMB2 Fields
	 */
	public static function setup_fields() {

		add_filter( 'wcct_modify_field_config_products', array( __CLASS__, 'modify_field_config_wcct_post' ), 99 );

		$get_tabs             = include plugin_dir_path( WCCT_PLUGIN_FILE ) . 'admin/includes/cmb2-countdown-meta-config.php';
		$tabs_setting['tabs'] = apply_filters( 'wcct_cmb2_modify_field_tabs', $get_tabs );

		$tabs_setting = apply_filters( 'wcct_modify_field_config_products', $tabs_setting );
		$tabs_setting = $tabs_setting['tabs'];

		$tabs_setting_key_value = array();
		foreach ( $tabs_setting as $key1 => $value1 ) {
			$tabs_setting_key_value[ $value1['id'] ] = array(
				'label' => __( $value1['title'], 'cmb2' ),
			);
		}

		$box_options = array(
			'id'           => 'wcct_campaign_settings',
			'title'        => __( 'Campaign Settings', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'classes'      => 'wcct_options_common',
			'object_types' => array( WCCT_Common::get_campaign_post_type_slug() ),
			'show_names'   => true,
			'context'      => 'normal',
			'priority'     => 'high',
			'wcct_tabs'    => $tabs_setting_key_value,
			'tab_style'    => 'default',
		);
		$cmb         = new_cmb2_box( $box_options );
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

		// Finale settings page fields
		$cmb_settings = new_cmb2_box( array(
			'id'         => 'wcct_global_settings',
			'cmb_styles' => false,
			'context'    => 'normal',
			'priority'   => 'high',
			'hookup'     => false,
			'show_on'    => array(
				'key'   => 'options-page',
				'value' => array( 'wcct' )
			),
		) );
		$cmb_settings->add_field( array(
			'name'       => __( 'How Have You Built Single Product Pages?', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'desc'       => __( 'Note: If you previously got snippets from the support team that supported custom product pages, it is strongly advised to remove those snippets before you select a setting.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'id'         => 'wcct_positions_approach',
			'type'       => 'radio',
			'options'    => array(
				'new' => __( "Select this if you are using native WooCommerce product pages", 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'old' => __( 'Select this is you are using custom Woocommerce product pages ( built using page builders such as Elementor, Divi Builder, UX-Builder, Beaver Builder etc)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			'before_row' => '<h3>General</h3>',
			'default'    => 'new',
		) );
		$cmb_settings->add_field( array(
			'name'    => __( 'Hide Days', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'desc'    => __( 'Hide Days in Countdown Timer if the time for the campaign to end is less than 24 hrs or 1 day', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'id'      => 'wcct_timer_hide_days',
			'type'    => 'radio_inline',
			'options' => array(
				'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			'default' => 'no',
		) );
		$cmb_settings->add_field( array(
			'name'    => __( 'Hide Hours', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'desc'    => __( 'Hide Hours in Countdown Timer if the time for the campaign to end is less than 60 mins or 1 hour', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'id'      => 'wcct_timer_hide_hrs',
			'type'    => 'radio_inline',
			'options' => array(
				'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			'default' => 'no',
		) );
		$cmb_settings->add_field( array(
			'name'    => __( 'Hide Multiple Countdown Timers', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'desc'    => __( 'If more than 1 countdown timers for a Product then show only first one', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'id'      => 'wcct_timer_hide_multiple',
			'type'    => 'radio_inline',
			'options' => array(
				'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			'default' => 'no',
		) );
		$cmb_settings->add_field( array(
			'name'    => __( 'Reload Page', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'desc'    => __( 'The current page will reload when countdown timer hits zero.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'id'      => 'wcct_reload_page_on_timer_ends',
			'type'    => 'radio_inline',
			'options' => array(
				'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			'default' => 'yes',
		) );
		$cmb_settings->add_field( array(
			'name'       => "_wpnonce",
			'id'         => '_wpnonce',
			'type'       => 'hidden',
			'attributes' => array(
				'value' => wp_create_nonce( 'woocommerce-settings' )
			)
		) );
	}

	public static function shortcode_metabox_fields() {
		$box_options = array(
			'id'           => 'wcct_campaign_shortcode_settings',
			'title'        => __( 'Element Shortcodes', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'classes'      => 'wcct_options_common',
			'object_types' => array( WCCT_Common::get_campaign_post_type_slug() ),
			'show_names'   => true,
			'context'      => 'side',
			'priority'     => 'low',
		);
		$campaign_id = isset( $_GET['post'] ) ? $_GET['post'] : 0;
		if ( is_array( $campaign_id ) === false && $campaign_id != 0 ) {
			$cmb = new_cmb2_box( $box_options );

			$shortcode_box = array(
				array(
					'id'       => 'wcct_campaign_shortcode_metabox',
					'position' => 1,
					'fields'   => array(
						array(
							'name'        => '',
							'id'          => '_wcct_data_choose_trigger_sh_help_link',
							'description' => '',
							'type'        => 'wcct_html_content_field',
							'row_classes' => 'wcct_p0',
							'content'     => "<p class='cmb2-metabox-description'> <a class= \"button button-primary\" style='color: #fff;' href=\"javascript:void(0);\" onclick=\"wcct_show_tb('Shortcode Help','wcct_shortcode_help_box');\">Generate Shortcodes</a></p>
 ",
							'after'       => '<div class="wcct_desc_shortcode">Click the button to get shortcodes for the Elements enabled in this campaign.</div>',
							'options'     => array(),
						),
					),
				),
			);
			foreach ( $shortcode_box as $meta_box ) {
				foreach ( $meta_box['fields'] as $fields ) {
					$cmb->add_field( $fields );
				}
			}
		}
	}

	public static function menu_order_metabox_fields() {

		$box_options   = array(
			'id'           => 'wcct_campaign_menu_order_settings',
			'title'        => __( 'Campaign Priority', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'classes'      => 'wcct_options_common',
			'object_types' => array( WCCT_Common::get_campaign_post_type_slug() ),
			'show_names'   => true,
			'context'      => 'side',
			'priority'     => 'low',
		);
		$cmb           = new_cmb2_box( $box_options );
		$shortcode_box = array(
			array(
				'id'       => 'wcct_campaign_shortcode_metabox',
				'position' => 1,
				'fields'   => array(
					array(
						'row_classes'     => array( 'wcct_desc_menu_order' ),
						'name'            => '',
						'desc'            => __( 'Priority works in ascending order. Lower the priority, higher the chance for campaign to work. <br/> For Eg: If there are two campaigns A & B with respective priority of 1 & 2, then campaign A will be executed before campaign B.  ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'id'              => '_wcct_campaign_menu_order',
						'type'            => 'text',
						'attributes'      => array(
							'type'    => 'number',
							'pattern' => '\d*',
						),
						'sanitization_cb' => 'absint',
						'escape_cb'       => 'absint',
					),
				),
			),
		);
		foreach ( $shortcode_box as $meta_box ) {
			foreach ( $meta_box['fields'] as $fields ) {
				$cmb->add_field( $fields );
			}
		}
	}

	public static function quick_view_metabox_fields() {

		$box_options   = array(
			'id'           => 'wcct_campaign_quick_view_settings',
			'title'        => __( 'Quick View', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'classes'      => 'wcct_options_common',
			'object_types' => array( WCCT_Common::get_campaign_post_type_slug() ),
			'show_names'   => true,
			'context'      => 'side',
			'priority'     => 'default',
		);
		$cmb           = new_cmb2_box( $box_options );
		$shortcode_box = array(
			array(
				'id'       => 'wcct_campaign_shortcode_metabox',
				'position' => 1,
				'fields'   => array(
					array(
						'content'     => apply_filters( 'wcct_get_qyuck_view', '<center><img src="' . admin_url( 'images/spinner.gif' ) . '"></center>' ),
						'id'          => '_wcct_qv_html',
						'type'        => 'wcct_html_content_field',
						'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_p0' ),
					),
				),
			),
		);
		foreach ( $shortcode_box as $meta_box ) {
			foreach ( $meta_box['fields'] as $fields ) {
				$cmb->add_field( $fields );
			}
		}
	}


	public static function wcct_report_order_metabox_fields() {

		$orderID      = filter_input( INPUT_GET, 'post' );
		$wcct_reports = XL_WCCT_Reports::get_instance();

		$wcct_reports->order_id = $orderID;

		$box_options   = array(
			'id'           => 'wcct_order_report_settings',
			'title'        => __( 'Finale Insights', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'classes'      => '',
			'object_types' => array( 'shop_order' ),
			'show_names'   => true,
			'context'      => 'side',
			'priority'     => 'default',
		);
		$cmb           = new_cmb2_box( $box_options );
		$shortcode_box = array(
			array(
				'id'       => 'wcct_campaign_shortcode_metabox1',
				'position' => 1,
				'fields'   => array(
					array(
//						'content'     => $wcct_reports->order_running_campaign_view( $orderID ),
						'content_cb'  => array( $wcct_reports, 'order_running_campaign_view' ),
						'id'          => '_wcct_qv_html',
						'type'        => 'wcct_html_content_field',
						'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_p0' ),
					),
				),
			),
		);
		foreach ( $shortcode_box as $meta_box ) {
			foreach ( $meta_box['fields'] as $fields ) {
				$cmb->add_field( $fields );
			}
		}
	}

	/**
	 * Hooked over `wcct_modify_field_config_products`
	 * Modify field args before passing it to cmb2
	 *
	 * @param $tabs_settings Field Arguments
	 *
	 * @return mixed
	 */
	public static function modify_field_config_wcct_post( $tabs_settings ) {
		$clone_settings = $tabs_settings;

		if ( isset( $tabs_settings['tabs'] ) && is_array( $tabs_settings['tabs'] ) && count( $tabs_settings['tabs'] ) > 0 ) {
			foreach ( $tabs_settings['tabs'] as $keytab => &$tab ) {

				if ( isset( $tab['fields'] ) && is_array( $tab['fields'] ) && count( $tab['fields'] ) > 0 ) {
					foreach ( $tab['fields'] as $keyf_tabs => &$tab_fields ) {
						$current_group                                                        = $tab_fields['id'];
						$clone_settings['tabs'][ $keytab ]['fields'][ $keyf_tabs ]['default'] = self::get_default_config( $tab_fields['id'] );

						if ( $tab_fields['type'] == 'group' ) {
							foreach ( $tab_fields['fields'] as $keyf_group => &$group_fields ) {
								$clone_settings['tabs'][ $keytab ]['fields'][ $keyf_tabs ]['fields'][ $keyf_group ]['default'] = self::get_default_config( array(
									$tab_fields['id'],
									$group_fields['id'],
								) );
							}
						} else {

							$clone_settings['tabs'][ $keytab ]['fields'][ $keyf_tabs ]['default'] = self::get_default_config( $tab_fields['id'] );
						}
					}
				}
			}
		}

		return $clone_settings;
	}

	/**
	 * Getting Default config from the saved values in wp_options
	 * Getter function for config for each field
	 *
	 * @param $key
	 * @param int $index
	 *
	 * @return string
	 */
	public static function get_default_config( $key, $index = 0 ) {

		if ( is_array( $key ) ) {

			if ( $key[1] == 'mode' ) {
				return '0';
			}

			return ( isset( self::$options_data[ $key[0] ][ $index ][ $key[1] ] ) ) ? self::$options_data[ $key[0] ][ $index ][ $key[1] ] : '';
		} else {
			if ( $key == 'mode' ) {
				return '0';
			}

			return ( isset( self::$options_data[ $key ] ) ) ? self::$options_data[ $key ] : '';
		}
	}

	/**
	 * Setting up property `options_data` by options data saved.
	 */
	public static function prepere_default_config() {
		self::$options_data = WCCT_Common::get_default_settings();
	}

	public static function wcct_ideas_inner() {
		ob_start();
		echo '<h4>Try these Ideas</h4>';
		$ideas_arr = array(
			array(
				'name' => 'Offer Fixed Discounts On Chosen Products Or A Specific Category',
				'link' => '',
				'icon' => 'sale_network.jpg',
			),
			array(
				'name' => 'Run Recurring Campaigns On Autopilot That Re-Start After Set Pause Periods',
				'link' => '',
				'paid' => true,
				'icon' => 'recycle.jpg',
			),
			array(
				'name' => 'Set Promotional Calendar In One Go Using Scheduled Campaigns',
				'link' => '',
				'icon' => '1-1.jpg',
			),
			array(
				'name' => 'Trigger Clearance Sales To Start On Autopilot When The Stock Size Hits A Threshold',
				'link' => '',
				'icon' => 'low-stock.jpg',
			),
			array(
				'name' => 'Run Scarcity Marketing Campaigns On A Few Stock Items Instead Of The Entire Stock',
				'link' => '',
				'icon' => 'Run-scarcity.jpg',
			),
			array(
				'name' => 'Add Your Custom CSS',
				'link' => '',
				'icon' => 'CSS.jpg',
			),
			array(
				'name' => 'Activate The Sticky Header And Footer On Chosen Pages To Inform Visitors About On-Going Campaigns',
				'link' => '',
				'paid' => true,
				'icon' => 'sticky.jpg',
			),
			array(
				'name' => 'Use Geo-Targeting To Run Campaigns For Specific Countries',
				'link' => '',
				'paid' => true,
				'icon' => '2.jpg',
			),
			array(
				'name' => 'Run Limited Hours Flash Sales On Specific Products',
				'link' => '',
				'icon' => 'sandbox.jpg',
			),
			array(
				'name' => 'Offer Early Bird Discounts To First Set Of Buyers. Set The Campaign To Expire Once The Goal Is Met',
				'link' => '',
				'paid' => true,
				'icon' => 'early-bird.jpg',
			),
			array(
				'name' => "Run Timer On 'Coming Soon' Products And Make The 'Add To Cart' Button Visible When It Hits Zero",
				'link' => '',
				'paid' => true,
				'icon' => 'timer.jpg',
			),
			array(
				'name' => 'Offer Next Day Or Same Day Shipping As A Reward For Fast Action Using Countdown Timer',
				'link' => '',
				'icon' => 'shipping-van.jpg',
			),
			array(
				'name' => "Set Up 'Deal Of The Day' Campaigns Such As Wow Wednesdays, Fantastic Fridays",
				'link' => '',
				'icon' => 'clock.jpg',
			),
			array(
				'name' => "Customize The CTA Buttons To Make Them More Persuasive During Campaigns. E.G. 'Grab Black Friday Deal- Add To Cart'",
				'link' => '',
				'paid' => true,
				'icon' => 'call-to-action.jpg',
			),
			array(
				'name' => 'Create Unlimited Campaigns And Have Multiple Campaigns, All Running At The Same Time',
				'link' => '',
				'icon' => '5.jpg',
			),
			array(
				'name' => 'Create Genuine Urgency By Instructing Finale To Hide Add To Cart Button As Soon As The Campaign Ends',
				'link' => '',
				'paid' => true,
				'icon' => 'cart-cut.jpg',
			),
			array(
				'name' => "Make Your Store Look Stunning By Matching Finale's Elements With Your Store's Theme",
				'link' => '',
				'icon' => 'eyedropper.jpg',
			),
			array(
				'name' => 'Set Up To Increase Or Decrease Discounts When The Remaining Stocks Hit A Set Threshold',
				'link' => '',
				'paid' => true,
				'icon' => '4.jpg',
			),
		);
		foreach ( $ideas_arr as $idea ) {
			echo '<div class="wcct_m3 wcct_s1 wcct_table">';
			if ( isset( $idea['paid'] ) && $idea['paid'] ) {
				echo '<div class="wcct_corner_tl_ribbon">Pro</div>';
			}
			$icon = '';
			if ( isset( $idea['icon'] ) && $idea['icon'] != '' ) {
				$icon = '<img src="//storage.googleapis.com/xl-finale/ideas/' . $idea['icon'] . '" />';
			}
			echo '<a class="wcct_table_cell" href="' . ( $idea['link'] ? $idea['link'] : 'javascript:void(0)' ) . '" target="_blank">' . $icon . $idea['name'] . '</a>';
			echo '</div>';
		}

		return ob_get_clean();
	}

}
