<?php

class Finale_deal_batch_Admin {

	public static $ins = null;
	public $is_bulk_run = 0;
	public $per_page = 100;
	public $data = array();
	public $post_type = 'wcct-deal-shortcode';
	public $shortcode_id;
	public $css = '';
	public $passed_campaigns = array();
	public $excluded_rules = array();

	public function __construct() {

		if ( is_admin() ) {
			$this->wcct_deal_url = untrailingslashit( WCCT_DEAL_PAGE_PLUGIN_URL );
			$this->file_json_api = new XlCore_file( 'dealpages' );
			add_action( 'wcct-deal-before-page', array( $this, 'edit_form_top' ) );
			add_action( 'edit_form_top', array( $this, 'edit_form_top_shortcode_edit' ) );

			add_filter( 'wcct_deals_cmb2_modify_field_tabs', array( $this, 'append_value_default' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

			add_action( 'admin_init', array( $this, 'maybe_save_batch_size' ), 1 );

			add_action( 'post_submitbox_misc_actions', array( $this, 'wcct_post_publish_box' ) );

			add_action( 'wp_print_scripts', array( $this, 'wcct_wp_print_scripts' ), 999 );
			add_action( 'admin_footer', array( $this, 'wcct_add_mergetag_text' ) );
			//add_action( 'wcct_deal_page_shortcode_page_right_content', array( $this, 'wcct_deal_shortcode_page_right_content' ) );
			add_action( 'save_post', array( $this, 'maybe_mark_campaign_for_modified_rules' ), 8, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'maybe_correct_campaign_data' ) );

			add_action( 'admin_notices', array( $this, 'maybe_show_notification_index_campaign' ), 999 );
			//add_action( 'admin_notices', array( $this, 'maybe_show_notice_for_reindex_all_camps' ), 999 );
			add_action( 'admin_init', array( $this, 'maybe_dismiss_admin_notice' ) );
			add_action( 'admin_init', array( $this, 'maybe_duplicate_post' ) );
			add_action( 'do_meta_boxes', array( $this, 'wcct_do_meta_boxes' ), 999, 3 );

		}


	}


	/**
	 * @return Finale_deal_batch_processing|null
	 */
	public static function instance() {
		if ( self::$ins == null ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function edit_form_top() {

		?>
        <div class="notice">
            <p><?php _e( 'Back to <a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) . '">' . WCCT_FULL_NAME . '</a> settings.', 'finale-woocommerce-deal-pages' ); ?></p>
        </div>
		<?php

	}

	public function edit_form_top_shortcode_edit() {
		global $post;

		if ( $post && is_object( $post ) && $post->post_type === 'wcct-deal-shortcode' ) {
			?>
            <div class="notice">
                <p><?php _e( 'Back to <a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=deal_pages&sub_page=shortcode' ) . '">' . __( 'Deal Pages', 'finale-woocommerce-deal-pages' ) . '</a>.', 'finale-woocommerce-deal-pages' ); ?></p>
            </div>
			<?php
		}

	}

	public function append_value_default( $field_all ) {
		$get_defaults = wcct_get_default_fields_value();
		$clone_fields = $field_all;
		foreach ( $field_all as $key => $fields ) {
			if ( isset( $fields['fields'] ) ) {
				foreach ( $fields['fields'] as $key_inner => $field ) {
					if ( array_key_exists( $field['id'], $get_defaults ) ) {
						$new_field                                    = $field;
						$new_field['default']                         = $get_defaults[ $field['id'] ];
						$clone_fields[ $key ]['fields'][ $key_inner ] = $new_field;
					}
				}
			}
		}

		return $clone_fields;
	}

	/**
	 * Plugin stylesheet and JavaScript.
	 *
	 * @param string $hook The current page loaded in the WP admin.
	 */
	public function scripts( $hook ) {

		// Exclude our scripts and CSS files from loading globally.
		wp_enqueue_style( 'batch-process-styles', WCCT_DEAL_PAGE_PLUGIN_URL . 'assets/main.css' );
		wp_enqueue_style( 'wcct_deal_style_admin', $this->wcct_deal_url . '/assets/css/wcct-deal-admin.css', array(), WCCT_DEAL_PAGE_VERSION );

		if ( isset( $_GET['tab'] ) && isset( $_GET['section'] ) ) {
			if ( 'xl-countdown-timer' === $_GET['tab'] && 'deal_pages' === $_GET['section'] ) {

				wp_enqueue_script( 'batch-js', WCCT_DEAL_PAGE_PLUGIN_URL . 'assets/dist/batch.min.js', array( 'jquery' ), '0.1.0', true );

				wp_localize_script( 'batch-js', 'wcct_deal_batch', array(
					'nonce'          => wp_create_nonce( 'wcct-deal-run-batch-process' ),
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'batches'        => wcct_deal_get_all_batches(),
					'page_title'     => esc_html( get_admin_page_title() ),
					'posts_per_page' => get_option( 'wcct_deal_posts_per_page', $this->per_page ),
				) );
			}
		}
	}

	public function maybe_save_batch_size() {

		if ( filter_input( INPUT_POST, 'wcct_deal_posts_per_page' ) !== null ) {
			update_option( 'wcct_deal_posts_per_page', filter_input( INPUT_POST, 'wcct_deal_posts_per_page' ) );
		}
	}


	public function wcct_post_publish_box() {
		global $post;

		if ( $this->post_type != $post->post_type ) {
			return;
		}

		if ( $post->post_date ) {
			$date_format  = get_option( 'date_format' );
			$date_format  = $date_format ? $date_format : 'M d, Y';
			$publich_date = date( $date_format, strtotime( $post->post_date ) );
		}

		if ( $post->post_date ) {
			?>
            <div class="misc-pub-section curtime misc-pub-curtime wcct_always_show">
                <span id="timestamp">Added on: <b><?php echo $publich_date; ?></b></span>
            </div>
			<?php
		}
	}

	/**
	 * dequeue script from single campaign page
	 * @global type $wp_scripts
	 */
	public function wcct_wp_print_scripts() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && isset( $screen->post_type ) && $screen->post_type == $this->post_type ) {
			wp_dequeue_script( 'yoast-seo-post-scraper' );
			if ( class_exists( 'WPSEO_Admin_Asset_Manager' ) ) {
				wp_dequeue_script( WPSEO_Admin_Asset_Manager::PREFIX . 'admin-media' );
			}

		}
	}

	public function wcct_add_mergetag_text() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && ( $screen->base == 'post' && $screen->post_type == $this->post_type ) ) {

			?>
            <div style="display:none;" class="wcct_tb_content" id="wcct_deals_merge_tags_invenotry_bar_help">
                <p>Here are the merge tags which you can use.</p>
                <p>
                    <em><strong>{{total_units}}</strong></em>: Outputs total quantity to be sold during the campaign.
                    Example, Total Units: 10.<br/>
                    <em><strong>{{sold_units}}</strong></em>: Outputs total quantity sold during the campaign. Example,
                    Currently Sold: 5.<br/>
                    <em><strong>{{remaining_units}}</strong></em>: Outputs total quantity left during the campaign.
                    Example, Currently Left: 5.<br/><br/>
                    <em><strong>{{total_units_price}}</strong></em>: Outputs total price value of total quantity to be
                    sold during the campaign. Example, Total Funds To Be Raised: $100.<br/>
                    <em><strong>{{sold_units_price}}</strong></em>: Outputs price value of quantity sold during the
                    campaign. Example, Funds To Raised Till Now: $50.<br/><br/>
                    <em><strong>{{sold_percentage}}</strong></em>: Outputs percentage of quantity sold during the
                    campaign. Example, Campaign Goal: 51% achieved.<br/>
                    <em><strong>{{remaining_percentage}}</strong></em>: Outputs percentage of remaining quantity left
                    during the campaign. Example, Campaign Goal: 49% left.
                </p>


            </div>
			<?php
		}
	}

	public function maybe_mark_campaign_for_modified_rules( $post_id, $post ) {
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( is_int( wp_is_post_revision( $post ) ) ) {
			return;
		}
		if ( is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		if ( $post->post_type != WCCT_Common::get_campaign_post_type_slug() ) {
			return;
		}

		if ( isset( $_POST['wcct_rule'] ) ) {

			$current_rules   = md5( wp_json_encode( $_POST['wcct_rule'] ) );
			$prev_rules_hash = md5( wp_json_encode( get_post_meta( $post_id, 'wcct_rule', true ) ) );

			if ( $current_rules !== $prev_rules_hash ) {
				update_post_meta( $post_id, '_wcct_deal_page_index_req', 'yes' );
			}
		}
	}

	/**
	 * Try and Update Campaign if they have the old meta in them.
	 */
	public function maybe_correct_campaign_data() {
		global $post;
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		if ( $screen_id == $this->post_type ) {
			$get_shortcode_Campaigns = get_post_meta( $post->ID, "wcct_finale_deal_shortcode_campaign", true );

			if ( false === $get_shortcode_Campaigns ) {
				return;
			}
			if ( '' === $get_shortcode_Campaigns ) {
				return;
			}
			if ( false === is_array( $get_shortcode_Campaigns ) ) {
				$get_shortcode_Campaigns = array( $get_shortcode_Campaigns );
				update_post_meta( $post->ID, "wcct_finale_deal_shortcode_campaign", $get_shortcode_Campaigns );
			}
		}
	}

	public function maybe_show_notification_index_campaign() {
		global $post;
		if ( ! is_admin() ) {
			return;
		}

		if ( is_object( $post ) && $post->post_type == WCCT_Common::get_campaign_post_type_slug() && filter_input( INPUT_GET, 'action' ) == 'edit' ) {
			$output = get_option( 'wcct-deal-process-action-' . $post->ID );
			if ( $output === false ) {
				return;
			}
			$is_notice_required = get_post_meta( $post->ID, '_wcct_deal_page_index_req', true );

			if ( $is_notice_required == 'yes' ) {
				?>
                <div id="message" class="notice notice-error">
                    <p> <?php _e( sprintf( 'The Rules for this campaign were modified and would need re-indexing. <a class="button button-primary" target="_blank" href="%s">Re-Index</a> ', admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages' ) ) ); ?></p>
                </div>
				<?php
			}
		}
	}

	public function maybe_dismiss_admin_notice() {
		if ( filter_input( INPUT_GET, 'wcct_deal_pages_dismiss_notice' ) !== null ) {
			$key = filter_input( INPUT_GET, 'wcct_deal_pages_dismiss_notice' );
			update_option( $key, 'yes', false );
		}
	}

	/**
	 * Try and Update Campaign if they have the old meta in them.
	 */
	public function maybe_show_notice_for_reindex_all_camps() {


		$get_option = get_option( 'wcct_deal_data_update_req_12', '' );

		if ( $get_option == '' ) {
			?>
            <div id="message" class="notice notice-error">
                <p> <?php _e( sprintf( 'Important Notice for Finale Deal Pages: This new version includes important performance related changes. These will speed up page load time and allow you to show several campaigns using single deal page. To make these changes work on your set-up, kindly re-index all campaigns. <br/><br/><a class="button button-primary" target="_blank" href="%s">Re-Index</a> <a class="button button-secondary" target="_blank" href="%s">Ignore</a> ', admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages' ), admin_url( 'index.php?wcct_deal_pages_dismiss_notice=wcct_deal_data_update_req_12' ) ) ); ?></p>
            </div>
			<?php
		}

	}

	public function maybe_duplicate_post() {

		global $wpdb;
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'deal-pages-duplicate' ) {

			if ( wp_verify_nonce( $_GET['_wpnonce'], 'deal-pages-duplicate' ) ) {

				$original_id = filter_input( INPUT_GET, 'postid' );
				$section     = filter_input( INPUT_GET, 'section' );
				if ( $original_id ) {

					// Get the post as an array
					$duplicate = get_post( $original_id, 'ARRAY_A' );

					$settings = $defaults = array(
						'status'                => 'same',
						'type'                  => 'same',
						'timestamp'             => 'current',
						'title'                 => __( 'Copy', 'post-duplicator' ),
						'slug'                  => 'copy',
						'time_offset'           => false,
						'time_offset_days'      => 0,
						'time_offset_hours'     => 0,
						'time_offset_minutes'   => 0,
						'time_offset_seconds'   => 0,
						'time_offset_direction' => 'newer',
					);

					// Modify some of the elements
					$appended                = ( $settings['title'] != '' ) ? ' ' . $settings['title'] : '';
					$duplicate['post_title'] = $duplicate['post_title'] . ' ' . $appended;
					$duplicate['post_name']  = sanitize_title( $duplicate['post_name'] . '-' . $settings['slug'] );

					// Set the status
					if ( $settings['status'] != 'same' ) {
						$duplicate['post_status'] = $settings['status'];
					}

					// Set the type
					if ( $settings['type'] != 'same' ) {
						$duplicate['post_type'] = $settings['type'];
					}

					// Set the post date
					$timestamp     = ( $settings['timestamp'] == 'duplicate' ) ? strtotime( $duplicate['post_date'] ) : current_time( 'timestamp', 0 );
					$timestamp_gmt = ( $settings['timestamp'] == 'duplicate' ) ? strtotime( $duplicate['post_date_gmt'] ) : current_time( 'timestamp', 1 );

					if ( $settings['time_offset'] ) {
						$offset = intval( $settings['time_offset_seconds'] + $settings['time_offset_minutes'] * 60 + $settings['time_offset_hours'] * 3600 + $settings['time_offset_days'] * 86400 );
						if ( $settings['time_offset_direction'] == 'newer' ) {
							$timestamp     = intval( $timestamp + $offset );
							$timestamp_gmt = intval( $timestamp_gmt + $offset );
						} else {
							$timestamp     = intval( $timestamp - $offset );
							$timestamp_gmt = intval( $timestamp_gmt - $offset );
						}
					}
					$duplicate['post_date']         = date( 'Y-m-d H:i:s', $timestamp );
					$duplicate['post_date_gmt']     = date( 'Y-m-d H:i:s', $timestamp_gmt );
					$duplicate['post_modified']     = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
					$duplicate['post_modified_gmt'] = date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) );

					// Remove some of the keys
					unset( $duplicate['ID'] );
					unset( $duplicate['guid'] );
					unset( $duplicate['comment_count'] );

					// Insert the post into the database
					$duplicate_id = wp_insert_post( $duplicate );

					// Duplicate all the taxonomies/terms
					$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
					foreach ( $taxonomies as $taxonomy ) {
						$terms = wp_get_post_terms( $original_id, $taxonomy, array( 'fields' => 'names' ) );
						wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
					}

					// Duplicate all the custom fields
					$custom_fields = get_post_custom( $original_id );
					foreach ( $custom_fields as $key => $value ) {
						if ( is_array( $value ) && count( $value ) > 0 ) {
							foreach ( $value as $i => $v ) {
								$result = $wpdb->insert( $wpdb->prefix . 'postmeta', array(
									'post_id'    => $duplicate_id,
									'meta_key'   => $key,
									'meta_value' => $v,
								) );
							}
						}
					}

					do_action( 'deal_pages_post_duplicated', $original_id, $duplicate_id, $settings );

					wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=' . $section . '&sub_page=shortcode' ) );
				}
			} else {
				die( __( 'Unable to Duplicate', WCCT_SLUG ) );
			}
		}

	}

	/**
	 * removing extra meta boxes on page, added by 3rd party plugin etc
	 * @global type $wp_meta_boxes
	 *
	 * @param type $post_type
	 * @param type $cur_context
	 * @param type $post
	 */
	public function wcct_do_meta_boxes( $post_type, $cur_context, $post ) {
		global $wp_meta_boxes;
		if ( $post_type == $this->post_type ) {
			$allowed_side_metaboxes   = array(
				'wcct_shortcode_box'
			);
			$allowed_normal_metaboxes = array( 'wcct_builder_settings' );
			if ( isset( $wp_meta_boxes[ $this->post_type ]['side']['high'] ) ) {
				unset( $wp_meta_boxes[ $this->post_type ]['side']['high'] );
			}
			if ( isset( $wp_meta_boxes[ $this->post_type ]['advanced'] ) ) {
				unset( $wp_meta_boxes[ $this->post_type ]['advanced'] );
			}
			if ( isset( $wp_meta_boxes[ $this->post_type ]['normal']['low'] ) ) {
				unset( $wp_meta_boxes[ $this->post_type ]['normal']['low'] );
			}
			if ( is_array( $wp_meta_boxes[ $this->post_type ]['normal']['high'] ) && count( $wp_meta_boxes[ $this->post_type ]['normal']['high'] ) > 0 ) {
				if ( isset( $wp_meta_boxes[ $this->post_type ]['side']['low'] ) && is_array( $wp_meta_boxes[ $this->post_type ]['side']['low'] ) && count( $wp_meta_boxes[ $this->post_type ]['side']['low'] ) > 0 ) {
					$meta_box_keys = array_keys( $wp_meta_boxes[ $this->post_type ]['side']['low'] );
					if ( is_array( $meta_box_keys ) && count( $meta_box_keys ) > 0 ) {
						foreach ( $meta_box_keys as $metabox_id ) {
							if ( ! in_array( $metabox_id, $allowed_side_metaboxes ) ) {
								unset( $wp_meta_boxes[ $this->post_type ]['side']['low'][ $metabox_id ] );
							}
						}
					}
				}
				$meta_box_keys = array();
				$meta_box_keys = array_keys( $wp_meta_boxes[ $this->post_type ]['normal']['high'] );
				if ( is_array( $meta_box_keys ) && count( $meta_box_keys ) > 0 ) {
					foreach ( $meta_box_keys as $metabox_id ) {
						if ( ! in_array( $metabox_id, $allowed_normal_metaboxes ) ) {
							unset( $wp_meta_boxes[ $this->post_type ]['normal']['high'][ $metabox_id ] );
						}
					}
				}
			}
		}
	}


}

Finale_deal_batch_Admin::instance();





