<?php
/**
 * Class to control Settings and its behaviour accross the buildwoofunnels
 * @author buildwoofunnels
 */

if ( ! class_exists( 'BWF_Admin_General_Settings' ) ) {

	class BWF_Admin_General_Settings {

		private static $ins = null;
		private $options = array();

		public function __construct() {

			add_filter( 'woofunnels_global_settings', function ( $menu ) {
				array_push( $menu, array(
					'title'    => __( 'General', 'woofunnels' ),
					'slug'     => 'woofunnels_general_settings',
					'link'     => apply_filters( 'bwf_general_settings_link', 'javascript:void(0)' ),
					'priority' => 5,
				) );

				return $menu;
			} );
			add_action( 'wp_ajax_bwf_general_settings_update', [ $this, 'update_general_settings' ] );
			add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 101 );

			add_action( 'admin_head', array( $this, 'hide_from_menu' ) );
			add_filter( 'admin_title', array( $this, 'maybe_change_title' ),99 );
		}

		public static function get_instance() {

			if ( null === self::$ins ) {
				self::$ins = new self;
			}

			return self::$ins;
		}

		public function maybe_flush_rewrite_rules() {
			$is_required_rewrite = get_option( 'bwf_needs_rewrite', 'no' );

			if ( 'yes' === $is_required_rewrite ) {
				flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
				update_option( 'bwf_needs_rewrite', 'no', true );
			}
		}

		public function __callback() {
			?>

			<div class="wrap bwf-funnel-common">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Settings', 'woofunnels' ); ?></h1>


				<?php
				$admin_settings = BWF_Admin_Settings::get_instance();
				$admin_settings->render_tab_html( 'woofunnels_general_settings' );
				$i = 0;
				?>

				<div id="bwf_general_settings_vue_wrap" class="bwf-tabs-view-vertical bwf-widget-tabs">

					<div class="bwf-tabs-wrapper">
						<div class="bwf-tab-title" data-tab="<?php $i ++;
						echo $i; ?>" role="tab">
							<?php esc_html_e( 'Permalinks', 'woofunnels' ); ?>
						</div>
						<div class="bwf-tab-title" data-tab="<?php $i ++;
						echo $i; ?>" role="tab">
							<?php esc_html_e( 'Facebook Pixel', 'woofunnels' ); ?>
						</div>
						<div class="bwf-tab-title" data-tab="<?php $i ++;
						echo $i; ?>" role="tab">
							<?php esc_html_e( 'Google Analytics', 'woofunnels' ); ?>
						</div>


						<?php if ( apply_filters( 'bwf_enable_ecommerce_integration_gad', false ) ) { ?>
							<div class="bwf-tab-title" data-tab="<?php $i ++;
							echo $i; ?>" role="tab">
								<?php esc_html_e( 'Google Ads', 'woofunnels' ); ?>
							</div>
						<?php } ?>
						<?php if ( apply_filters( 'bwf_enable_ecommerce_integration_pinterest', false ) ) { ?>
							<div class="bwf-tab-title" data-tab="<?php $i ++;
							echo $i; ?>" role="tab">
								<?php esc_html_e( 'Pinterest', 'woofunnels' ); ?>
							</div>
						<?php } ?>


					</div>


					<div class="bwf-tabs-content-wrapper">
						<div class="bwf_setting_inner">
							<form class="bwf_forms_wrap">
								<fieldset>
									<vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
								</fieldset>
								<div style="display: none" id="modal-general-settings_success" data-iziModal-icon="icon-home">
								</div>
							</form>
							<div class="bwf_form_button">
								<span class="bwf_loader_global_save spinner" style="float: left;"></span>
								<button v-on:click.self="onSubmit" class="bwf_save_btn_style"><?php esc_html_e( 'Save Settings', 'woofunnels' ); ?></button>

							</div>
						</div>
					</div>
				</div>

			</div>

			<?php
		}

		public function default_general_settings() {
			return apply_filters( 'bwf_general_settings_default_config', array(

				'fb_pixel_key'                      => '',
				'ga_key'                            => '',
				'gad_key'                           => '',
				'gad_conversion_label'              => '',
				'is_fb_purchase_event'              => array(),
				'is_fb_synced_event'                => array(),
				'is_fb_advanced_event'              => array(),
				'content_id_value'                  => '',
				'content_id_variable'               => array(),
				'content_id_prefix'                 => '',
				'content_id_suffix'                 => '',
				'track_traffic_source'              => array(),
				'exclude_from_total'                => array(),
				'enable_general_event'              => array(),
				'general_event_name'                => 'GeneralEvent',
				'custom_aud_opt_conf'               => array(),
				'is_ga_purchase_event'              => array(),
				'is_gad_purchase_event'             => array(),
				'pixel_initiate_checkout_event'     => '',
				'pixel_add_to_cart_event'           => '',
				'pixel_add_payment_info_event'      => '',
				'pixel_variable_as_simple'          => '',
				'pixel_content_id_type'             => '0',
				'pixel_content_id_prefix'           => '',
				'pixel_content_id_suffix'           => '',
				'google_ua_add_to_cart_event'       => '',
				'google_ua_initiate_checkout_event' => '',
				'google_ua_add_payment_info_event'  => '',
				'google_ua_variable_as_simple'      => '',
				'google_ua_content_id_type'         => '0',
				'google_ua_content_id_prefix'       => '',
				'google_ua_content_id_suffix'       => '',
				'ga_track_traffic_source'           => array(),
				'gad_exclude_from_total'            => array(),
				'id_prefix_gad'                     => '',
				'id_suffix_gad'                     => '',
			) );
		}

		public function get_option( $key = 'all' ) {

			if ( empty( $this->options ) ) {
				$this->setup_options();
			}
			if ( 'all' === $key ) {
				return $this->options;
			}

			return isset( $this->options[ $key ] ) ? $this->options[ $key ] : false;
		}

		public function setup_options() {
			$db_options = get_option( 'bwf_gen_config', [] );

			$db_options    = ( ! empty( $db_options ) && is_array( $db_options ) ) ? array_map( function ( $val ) {
				return is_scalar( $val ) ? html_entity_decode( $val ) : $val;
			}, $db_options ) : array();
			$this->options = wp_parse_args( $db_options, $this->default_general_settings() );

			return $this->options;
		}

		public function maybe_add_js( $plugin_url = '', $plugin_ver = '' ) {
			wp_enqueue_script( 'bwf-general-settings', plugin_dir_url( WooFunnel_Loader::$ultimate_path ) . 'woofunnels/assets/js/bwf-general-settings.js', [], $plugin_ver );
			wp_enqueue_style( 'bwf-general-settings', plugin_dir_url( WooFunnel_Loader::$ultimate_path ) . 'woofunnels/assets/css/bwf-general-settings.css', array(), $plugin_ver );

			$localized_data                                  = [
				'nonce_general_settings' => wp_create_nonce( 'bwf_general_settings_update' ),
				'texts'                  => array(
					'settings_success' => __( 'Changes saved', 'woofunnels' ),
					'permalink_help_text' => __( 'Leave empty to remove slug completely from url', 'woofunnels' ),
				),
				'globalOptionsFields'    => array(
					'options'       => $this->filter_admin_options( $this->get_option() ),
					'legends_texts' => array(
						'fb'         => __( 'Facebook Pixel', 'woofunnels' ),
						'ga'         => __( 'Google Analytics', 'woofunnels' ),
						'gad'        => __( 'Google Ads', 'woofunnels' ),
						'pint'       => __( 'Pinterest', 'woofunnels' ),
						'permalinks' => __( 'Permalinks', 'woofunnels' ),
					),
					'fields'        => array(
						'label_section_head_fb'         => array(
							'label'        => __( 'Checkout Events', 'woofunnels' ),
							'styleClasses' => [ 'wfacp_setting_track_and_events_start', 'bwf_wrap_custom_html_tracking_general' ],
						),
						'pixel_initiate_checkout_event' => array(
							'inputType'    => 'text',
							'label'        => __( 'Enable InitiateCheckout Event', 'woofunnels' ),
							'styleClasses' => [ 'wfacp_checkbox_wrap', 'wfacp_setting_track_and_events_end' ],


						),
						'pixel_add_to_cart_event'       => array(
							'inputType' => 'text',
							'label'     => __( 'Enable AddtoCart Event', 'woofunnels' ),

						),
						'pixel_add_payment_info_event'  => array(
							'inputType' => 'text',
							'label'     => __( 'Enable AddPaymentInfo Event', 'woofunnels' ),

						),
						'pixel_variable_as_simple'      => array(
							'label' => __( 'Treat variable products like simple products', 'woofunnels' ),
							'hint'  => __( 'Turn this option ON when your Product Catalog doesn\'t include the variants for variable products.', 'woofunnels' ),

						),
						'pixel_content_id_type'         => array(
							'styleClasses'  => 'group-one-class',
							'label'         => '',
							'default'       => '0',
							'values'        => [
								[ 'id' => '0', 'name' => __( 'Select content id parameter', 'woofunnels' ) ],
								[ 'id' => 'product_id', 'name' => __( 'Product ID', 'woofunnels' ) ],
								[ 'id' => 'product_sku', 'name' => __( 'Product Sku', 'woofunnels' ) ],
							],
							'selectOptions' => [
								'hideNoneSelectedText' => true,
							],
						),
						'pixel_content_id_prefix'       => array(
							'label'       => '',
							'placeholder' => __( 'content id prefix', 'woofunnels' ),
							'hint'        => __( 'Add prefix to the content_id parameter (optional)', 'woofunnels' ),

						),
						'pixel_content_id_suffix'       => array(
							'label'       => '',
							'placeholder' => __( 'content id suffix', 'woofunnels' ),
							'hint'        => __( 'Add suffix to the content_id parameter (optional)', 'woofunnels' ),

						),
						'is_fb_purchase_event'          => array(
							'label'  => __( 'Purchase Events', 'woofunnels' ),
							'hint'   => __( 'Note: WooFunnels will send total order value and store currency based on order. <a target="_blank" href="https://developers.facebook.com/docs/facebook-pixel/pixel-with-ads/conversion-tracking#add-value">Click here to know more.</a>', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Purchase Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						'custom_aud_opt_conf'           => array(
							'label' => '',
							'hint'  => __( 'Choose the parameters you want to send with purchase event', 'woofunnels' ),

							'values' => array(
								array(
									'name'  => __( 'Add Town,State & Country Parameters', 'woofunnels' ),
									'value' => 'add_town_s_c',
								),
								array(
									'name'  => __( 'Add Payment Method Parameters', 'woofunnels' ),
									'value' => 'add_payment_method',
								),
								array(
									'name'  => __( 'Add Shipping Method Parameters', 'woofunnels' ),
									'value' => 'add_shipping_method',
								),
								array(
									'name'  => __( 'Add Coupon parameters', 'woofunnels' ),
									'value' => 'add_coupon',
								),
							),
						),

						'exclude_from_total'   => array(
							'label' => '',
							'hint'  => __( 'Check above boxes to exclude shipping/taxes from the total.', 'woofunnels' ),

							'values' => array(
								array(
									'name'  => __( 'Exclude Shipping from Total', 'woofunnels' ),
									'value' => 'is_disable_shipping',
								),
								array(
									'name'  => __( 'Exclude Taxes from Total', 'woofunnels' ),
									'value' => 'is_disable_taxes',
								),

							),
						),
						'is_fb_synced_event'   => array(
							'label'  => '',
							'hint'   => __( 'Note: Your Product catalog must be synced with Facebook. <a target="_blank" href="https://developers.facebook.com/docs/facebook-pixel/implementation/dynamic-ads">Click here to know more.</a>', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Content Settings for Dynamic Ads', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						'content_id_variable'  => array(
							'label'  => '',
							'hint'   => __( 'Turn this option ON when your Product Catalog doesn\'t include the variants for variable products.', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Treat variable products like simple products', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						'content_id_value'     => array(

							'label'  => '',
							'hint'   => __( 'Select either Product ID or SKU to pass value in content_id parameter', 'woofunnels' ),
							'values' => array(
								array(
									'id'   => '',
									'name' => __( 'Select content_id parameter', 'woofunnels' ),
								),
								array(
									'id'   => 'product_id',
									'name' => __( 'Product ID', 'woofunnels' ),
								),
								array(
									'id'   => 'product_sku',
									'name' => __( 'Product SKU', 'woofunnels' ),
								),

							),
						),
						'content_id_prefix'    => array(

							'label'       => '',
							'placeholder' => __( 'content_id prefix', 'woofunnels' ),
							'hint'        => __( 'Add prefix to the content_id parameter (optional)', 'woofunnels' ),

						),
						'content_id_suffix'    => array(

							'label'       => '',
							'placeholder' => __( 'content_id suffix', 'woofunnels' ),
							'hint'        => __( 'Add suffix to the content_id parameter (optional)', 'woofunnels' ),
						),
						'enable_general_event' => array(

							'label'  => '',
							'hint'   => __( 'Use the GeneralEvent for your Custom Audiences and Custom Conversions.', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable General Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),

						),
						'is_fb_advanced_event' => array(
							'label'  => '',
							'hint'   => __( 'Note: WooFunnels will send customer\'s email, name, phone, address fields whichever available in the order. <a target="_blank" href="https://developers.facebook.com/docs/facebook-pixel/pixel-with-ads/conversion-tracking#advanced_match">Click here to know more.', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Advanced Matching With the Pixel', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						'track_traffic_source' => array(

							'label'  => '',
							'hint'   => __( 'Add traffic source as traffic_source and URL parameters (UTM) as parameters to all your events.', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Track Traffic Source & UTMs', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						'general_event_name'   => array(

							'label'       => '',
							'placeholder' => __( 'General Event Name', 'woofunnels' ),
							'hint'        => __( 'Customize the name of general event.', 'woofunnels' ),
						),

						'label_section_head_tan_ga'         => array(
							'label'        => __( 'Checkout Events', 'woofunnels' ),
							'styleClasses' => [ 'wfacp_setting_track_and_events_start', 'bwf_wrap_custom_html_tracking_general' ],
						),
						'ga_key'                            => array(
							'label' => __( 'Analytics ID', 'woofunnels' ),
							'hint'  => __( 'Log into your Google Analytics account to find your Analytics ID. <a target="_blank" href="http://analytics.google.com/analytics/web/">Click here for more info.</a>', 'woofunnels' ),
						),
						'google_ua_add_to_cart_event'       => array(
							'label'        => __( 'Enable AddtoCart Event', 'woofunnels' ),
							'styleClasses' => [ 'wfacp_checkbox_wrap', 'wfacp_setting_track_and_events_end' ],
						),
						'google_ua_initiate_checkout_event' => array(
							'label' => __( 'Enable BeginCheckout Event', 'woofunnels' ),
						),
						'google_ua_add_payment_info_event'  => array(
							'label' => __( 'Enable AddPaymentInfo Event', 'woofunnels' ),
						),
						'google_ua_variable_as_simple'      => array(
							'label' => __( 'Treat variable products like simple products', 'woofunnels' ),
							'hint'  => __( 'Turn this option ON when your Product Catalog doesn\'t include the variants for variable products.', 'woofunnels' ),
						),
						'google_ua_content_id_type'         => array(
							'label'         => '',
							'hint'          => __( 'Select either Product ID or SKU to pass value in content_id parameter', 'woofunnels' ),
							'values'        => [
								[ 'id' => '0', 'name' => __( 'Select content id parameter', 'woofunnels' ) ],
								[ 'id' => 'product_id', 'name' => __( 'Product ID', 'woofunnels' ) ],
								[ 'id' => 'product_sku', 'name' => __( 'Product Sku', 'woofunnels' ) ],
							],
							'selectOptions' => [
								'hideNoneSelectedText' => true,
							],
						),
						'google_ua_content_id_prefix'       => array(
							'label'       => '',
							'placeholder' => __( 'content id prefix', 'woofunnels' ),
							'hint'        => __( 'Add prefix to the content_id parameter (optional)', 'woofunnels' ),

						),
						'google_ua_content_id_suffix'       => array(
							'label'       => '',
							'placeholder' => __( 'content id suffix', 'woofunnels' ),
							'hint'        => __( 'Add suffix to the content_id parameter (optional)', 'woofunnels' ),

						),
						'is_ga_purchase_event'              => array(
							'label'  => __( 'Purchase Events', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Purchase Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						'ga_track_traffic_source'           => array(

							'label'  => '',
							'hint'   => __( 'Add traffic source as traffic_source and URL parameters (UTM) as parameters to all your events.', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Track Traffic Source & UTMs', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),


						'fb_pixel_key' => array(
							'label' => __( 'Pixel ID', 'woofunnels' ),
							'hint'  => __( 'Log into your Facebook ads account to find your Pixel ID. <a target="_blank" href="https://www.facebook.com/ads/manager/pixel/facebook_pixel">Click here for more info.</a> <br/> Note: PageView Event will be enabled by default.', 'woofunnels' ),
						),
						'gad_key'      => array(
							'label' => __( 'Conversion ID', 'woofunnels' ),
							'hint'  => __( 'Log into your Google Ads account to find your Conversion ID. <a target="_blank" href="https://buildwoofunnels.com/docs/upstroke/global-settings/tracking-analytics/#google-ads-tracking">Click here for more info.</a>', 'woofunnels' ),
						),

						'gad_conversion_label'   => array(
							'label' => __( 'Conversion Label', 'woofunnels' ),
							'hint'  => __( 'Log into your Google Ads account to find your conversion label. <a target="_blank" href="https://buildwoofunnels.com/docs/upstroke/global-settings/tracking-analytics/#google-ads-tracking">Click here for more info.</a>', 'woofunnels' ),

						),
						'is_gad_purchase_event'  => array(
							'label'  => __( 'Purchase Events', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Conversion Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						'gad_exclude_from_total' => array(
							'label' => '',
							'hint'  => __( 'Check above boxes to exclude shipping/taxes from the total.', 'woofunnels' ),

							'values' => array(
								array(
									'name'  => __( 'Exclude Shipping from Total', 'woofunnels' ),
									'value' => 'is_disable_shipping',
								),
								array(
									'name'  => __( 'Exclude Taxes from Total', 'woofunnels' ),
									'value' => 'is_disable_taxes',
								),

							),
						),
						'id_prefix_gad'          => array(

							'label'       => '',
							'placeholder' => __( 'Product ID prefix', 'woofunnels' ),
							'hint'        => __( 'Add prefix to the product_id parameter (optional)', 'woofunnels' ),

						),
						'id_suffix_gad'          => array(

							'label'       => '',
							'placeholder' => __( 'Product ID suffix', 'woofunnels' ),
							'hint'        => __( 'Add suffix to the product_id parameter (optional)', 'woofunnels' ),
						),


						'pint_key'                    => array(
							'label' => __( 'Tag ID', 'woofunnels' ),
						),
						'label_section_head_tan_pint' => array(
							'label' => __( 'Purchase Tracking', 'woofunnels' ),
						),
						'is_pint_purchase_event'      => array(
							'label'  => __( '', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Purchase Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),

					)
				)
			];
			$localized_data['globalOptionsFields']['fields'] = apply_filters( 'bwf_general_settings_fields', $localized_data['globalOptionsFields']['fields'] );

			$localized_data['is_pinterest_enabled']   = ( true === apply_filters( 'bwf_enable_ecommerce_integration_pinterest', false ) ) ? 1 : 0;
			$localized_data['is_gad_enabled']         = ( true === apply_filters( 'bwf_enable_ecommerce_integration_gad', false ) ) ? 1 : 0;
			$localized_data['if_fb_checkout_enabled'] = ( true === apply_filters( 'bwf_enable_ecommerce_integration_fb_checkout', false ) ) ? 1 : 0;
			$localized_data['if_fb_purchase_enabled'] = ( true === apply_filters( 'bwf_enable_ecommerce_integration_fb_purchase', false ) ) ? 1 : 0;
			$localized_data['if_ga_checkout_enabled'] = ( true === apply_filters( 'bwf_enable_ecommerce_integration_ga_checkout', false ) ) ? 1 : 0;
			$localized_data['if_ga_purchase_enabled'] = ( true === apply_filters( 'bwf_enable_ecommerce_integration_ga_purchase', false ) ) ? 1 : 0;
			wp_localize_script( 'bwf-general-settings', 'bwfAdminGen', $localized_data );
		}

		public function update_general_settings() {
			check_admin_referer( 'bwf_general_settings_update', '_nonce' );

			$options = ( isset( $_POST['data'] ) && ( wp_unslash( $_POST['data'] ) ) ) ? ( $_POST['data'] ) : 0;   // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$resp    = [];

			update_option( 'bwf_gen_config', $options, true );
			update_option( 'bwf_needs_rewrite', 'yes', true );
			$resp['status'] = true;
			$resp['msg']    = __( 'Settings Updated', 'woofunnels' );
			$resp['data']   = '';
			wp_send_json( $resp );
		}

		public function get_settings_link() {
			return apply_filters( 'bwf_general_settings_link', 'javascript:void(0)' );
		}

		public function hide_from_menu() {
			global $woofunnels_menu_slug;

			global $parent_file, $plugin_page, $submenu_file; //phpcs:ignore
			if ( filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) === 'bwf_settings' ) :
				$parent_file  = $woofunnels_menu_slug;//phpcs:ignore
				$submenu_file = 'admin.php?page=woofunnels_settings'; //phpcs:ignore
			endif;
		}

		/**
		 * Filter options before passing it to the javascript
		 *
		 * @param $config array configuration array
		 *
		 * @return array
		 */
		public function filter_admin_options( $config ) {
			foreach ( $config as $key => &$data ) {

				/**
				 * Check if data is 'false' (string) then make it blank so that checkboxes works accordingly
				 */
				if ( 'false' === $data ) {
					$config[ $key ] = '';
				}
			}

			return $config;
		}

		public function maybe_change_title( $title ) {
			if ( 'bwf_settings' === filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) || 'bwf_settings' === filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING ) ) {
				$admin_title = get_bloginfo( 'name' );
				$title = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress' ), 'WooFunnels', $admin_title );
			}

			return $title;
		}
	}


}
BWF_Admin_General_Settings::get_instance();
