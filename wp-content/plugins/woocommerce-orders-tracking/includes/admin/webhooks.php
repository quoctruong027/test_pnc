<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_WEBHOOKS' ) ) {
	class VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_WEBHOOKS {
		protected $settings;

		public function __construct() {
			$this->settings = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 16 );
			add_action( 'admin_init', array( $this, 'save_options' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'rest_api_init', array( $this, 'register_api' ) );
		}

		public function save_options() {
			global $woo_orders_tracking_settings;
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( ! isset( $_POST['wot_save_webhooks_options'] ) || ! $_POST['wot_save_webhooks_options'] ) {
				return;
			}
			if ( ! isset( $_POST['_wot_webhooks_nonce'] ) || ! wp_verify_nonce( $_POST['_wot_webhooks_nonce'], 'wot_webhooks_action' ) ) {
				return;
			}
			$args                 = $this->settings->get_params();
			$service_carrier_type = $this->settings->get_params( 'service_carrier_type' );
			switch ( $service_carrier_type ) {
				case 'trackingmore':
					$args['webhooks_user_email']                            = isset( $_POST['woo_orders_tracking_webhooks_user_email'] ) ? sanitize_email( $_POST['woo_orders_tracking_webhooks_user_email'] ) : '';
					$args[ 'webhooks_send_email_' . $service_carrier_type ] = isset( $_POST[ 'woo_orders_tracking_webhooks_send_email_' . $service_carrier_type ] ) ? stripslashes_deep( $_POST[ 'woo_orders_tracking_webhooks_send_email_' . $service_carrier_type ] ) : array();
					break;
				case 'aftership':
					$args['webhooks_secret']                                = isset( $_POST['woo_orders_tracking_webhooks_secret'] ) ? sanitize_text_field( $_POST['woo_orders_tracking_webhooks_secret'] ) : '';
					$args[ 'webhooks_send_email_' . $service_carrier_type ] = isset( $_POST[ 'woo_orders_tracking_webhooks_send_email_' . $service_carrier_type ] ) ? stripslashes_deep( $_POST[ 'woo_orders_tracking_webhooks_send_email_' . $service_carrier_type ] ) : array();
					break;
				case 'easypost':
					$args['webhooks_password']                              = isset( $_POST['woo_orders_tracking_webhooks_password'] ) ? sanitize_text_field( $_POST['woo_orders_tracking_webhooks_password'] ) : '';
					$args[ 'webhooks_send_email_' . $service_carrier_type ] = isset( $_POST[ 'woo_orders_tracking_webhooks_send_email_' . $service_carrier_type ] ) ? stripslashes_deep( $_POST[ 'woo_orders_tracking_webhooks_send_email_' . $service_carrier_type ] ) : array();
					break;
			}
			$args['webhooks_enable']     = isset( $_POST['woo_orders_tracking_webhooks_enable'] ) ? sanitize_text_field( $_POST['woo_orders_tracking_webhooks_enable'] ) : '';
			$args['change_order_status'] = isset( $_POST['woo_orders_tracking_change_order_status'] ) ? sanitize_text_field( $_POST['woo_orders_tracking_change_order_status'] ) : '';
			update_option( 'woo_orders_tracking_settings', $args );
			$woo_orders_tracking_settings = $args;
			$this->settings               = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance(true);
		}

		public function admin_enqueue_scripts() {
			global $pagenow;
			$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
			if ( $pagenow === 'admin.php' && $page === 'woocommerce-orders-tracking-webhooks' ) {
				VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_SETTINGS::admin_enqueue_semantic();
				if ( ! wp_script_is( 'transition' ) ) {
					wp_enqueue_style( 'transition', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'transition.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
					wp_enqueue_script( 'transition', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'transition.min.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
				}
				wp_enqueue_style( 'woocommerce-orders-tracking-webhooks', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'webhooks.css' );
				wp_enqueue_script( 'woocommerce-orders-tracking-webhooks', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'webhooks.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			}
		}

		public function admin_menu() {
			add_submenu_page( 'woocommerce-orders-tracking', esc_html__( 'Webhooks', 'woocommerce-orders-tracking' ), esc_html__( 'Webhooks', 'woocommerce-orders-tracking' ), 'manage_options', 'woocommerce-orders-tracking-webhooks', array(
				$this,
				'page_callback'
			) );
		}

		public function page_callback() {
			$service_carrier_type = $this->settings->get_params( 'service_carrier_type' );
			$option_field         = '';
			$webhooks_url         = get_site_url( null, "wp-json/woocommerce-orders-tracking/{$service_carrier_type}" );
			$webhooks_url_desc    = '';
			$statuses             = array();
			?>
            <div class="wrap">
                <h2><?php esc_html_e( 'Webhooks Settings', 'woocommerce-orders-tracking' ) ?></h2>
                <div class="vi-ui segment">
                    <div class="vi-ui positive message">
                        <div class="header"><?php esc_html_e( 'How to setup your webhook?', 'woocommerce-orders-tracking' ); ?></div>
                        <ul class="list">
							<?php
							switch ( $service_carrier_type ) {
								case 'trackingmore':
									$statuses = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE::status_text();
									?>
                                    <li><?php _e( 'Go to <a href="https://my.trackingmore.com/webhook_setting.php">https://my.trackingmore.com/webhook_setting.php</a>', 'woocommerce-orders-tracking' ); ?></li>
                                    <li><?php _e( 'Copy Webhook url below and paste it to Webhook URL field of your Webhook Notification Settings', 'woocommerce-orders-tracking' ); ?></li>
                                    <li><?php _e( 'Select statuses that you want to be notified', 'woocommerce-orders-tracking' ); ?></li>
                                    <li><?php _e( 'Save', 'woocommerce-orders-tracking' ); ?></li>
									<?php
									ob_start();
									?>
                                    <tr>
                                        <th>
                                            <label for="<?php echo esc_attr( self::set( 'webhooks_user_email' ) ) ?>"><?php esc_html_e( 'TrackingMore Email', 'woocommerce-orders-tracking' ) ?></label>
                                        </th>
                                        <td>
                                            <input type="email"
                                                   name="<?php echo esc_attr( self::set( 'webhooks_user_email', true ) ) ?>"
                                                   id="<?php echo esc_attr( self::set( 'webhooks_user_email' ) ) ?>"
                                                   value="<?php echo esc_attr( $this->settings->get_params( 'webhooks_user_email' ) ) ?>">
                                            <div class="description"><?php esc_html_e( 'Email is required to verify webhook from TrackingMore', 'woocommerce-orders-tracking' ) ?></div>
                                        </td>
                                    </tr>
									<?php
									$option_field = ob_get_clean();
									break;
								case 'aftership':
									$statuses = VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP::status_text();
									?>
                                    <li><?php _e( 'Go to <a href="https://admin.aftership.com/settings/notifications">Notification settings</a> and scroll down to the bottom of the page', 'woocommerce-orders-tracking' ); ?></li>
                                    <li><?php _e( 'Add webhook URL using below Webhook URL', 'woocommerce-orders-tracking' ); ?></li>
                                    <li><?php _e( 'Copy the AfterShip Webhook secret and paste it to respective Webhook secret field below', 'woocommerce-orders-tracking' ); ?></li>
                                    <li><?php _e( 'Select events to start receiving updates.', 'woocommerce-orders-tracking' ); ?></li>
                                    <li><?php _e( 'Save', 'woocommerce-orders-tracking' ); ?></li>
									<?php
									ob_start();
									?>
                                    <tr>
                                        <th>
                                            <label for="<?php echo esc_attr( self::set( 'webhooks_secret' ) ) ?>"><?php esc_html_e( 'Webhook secret', 'woocommerce-orders-tracking' ) ?></label>
                                        </th>
                                        <td>
                                            <input type="text"
                                                   name="<?php echo esc_attr( self::set( 'webhooks_secret', true ) ) ?>"
                                                   id="<?php echo esc_attr( self::set( 'webhooks_secret' ) ) ?>"
                                                   value="<?php echo esc_attr( $this->settings->get_params( 'webhooks_secret' ) ) ?>">
                                            <div class="description"><?php _e( 'Paste your AfterShip Webhook secret here. You can find it in your AfterShip <a href="https://admin.aftership.com/settings/notifications">Notification settings</a>', 'woocommerce-orders-tracking' ) ?></div>
                                        </td>
                                    </tr>
									<?php
									$option_field = ob_get_clean();
									break;
								case 'easypost':
									$statuses = VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST::status_text();
									$webhooks_url_desc = esc_html__( 'Please note that Webhook URL will change if you change Webhook Secret.', 'woocommerce-orders-tracking' );
									$webhooks_url = add_query_arg( array( 'secret_key' => $this->settings->get_params( 'webhooks_password' ) ), $webhooks_url );
									?>
                                    <li><?php _e( 'Go to <a href="https://www.easypost.com/account/webhooks-and-events">Webhooks & Events</a>', 'woocommerce-orders-tracking' ); ?></li>
                                    <li><?php _e( 'Add webhook URL using below Webhook URL', 'woocommerce-orders-tracking' ); ?></li>
									<?php
									ob_start();
									?>
                                    <tr>
                                        <th>
                                            <label for="<?php echo esc_attr( self::set( 'webhooks_password' ) ) ?>"><?php esc_html_e( 'Webhook secret', 'woocommerce-orders-tracking' ) ?></label>
                                        </th>
                                        <td>
                                            <input type="text" required="required"
                                                   name="<?php echo esc_attr( self::set( 'webhooks_password', true ) ) ?>"
                                                   id="<?php echo esc_attr( self::set( 'webhooks_password' ) ) ?>"
                                                   value="<?php echo esc_attr( $this->settings->get_params( 'webhooks_password' ) ) ?>">
                                            <div class="description"><?php _e( 'This helps prevent any third parties from masquerading as EasyPost and sending fraudulent data', 'woocommerce-orders-tracking' ) ?></div>
                                        </td>
                                    </tr>
									<?php
									$option_field = ob_get_clean();
									break;
								default:
									?>
                                    <li><?php _e( 'Webhook is not available with your currently selected tracking service', 'woocommerce-orders-tracking' ); ?></li>
								<?php
							}
							?>
                        </ul>
                    </div>
					<?php
					if ( $service_carrier_type !== 'cainiao' ) {
						?>
                        <form class="vi-ui form" method="post">
							<?php wp_nonce_field( 'wot_webhooks_action', '_wot_webhooks_nonce' ); ?>
                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th>
                                        <label for="<?php echo esc_attr( self::set( 'webhooks_enable' ) ) ?>"><?php esc_html_e( 'Enable webhook', 'woocommerce-orders-tracking' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php echo esc_attr( self::set( 'webhooks_enable', true ) ) ?>"
                                                   id="<?php echo esc_attr( self::set( 'webhooks_enable' ) ) ?>"
                                                   value="1" <?php checked( $this->settings->get_params( 'webhooks_enable' ), '1' ) ?>>
                                        </div>
                                    </td>
                                </tr>
								<?php
								if ( $option_field ) {
									echo $option_field;
								}
								?>
                                <tr>
                                    <th>
                                        <label for="<?php echo esc_attr( self::set( 'webhooks_send_email_' . $service_carrier_type ) ) ?>"><?php esc_html_e( 'Send email', 'woocommerce-orders-tracking' ) ?></label>
                                    </th>
                                    <td>
										<?php
										$webhooks_send_email = $this->settings->get_params( 'webhooks_send_email_' . $service_carrier_type );
										?>
                                        <select id="<?php echo esc_attr( self::set( 'webhooks_send_email_' . $service_carrier_type ) ) ?>"
                                                class="vi-ui fluid dropdown"
                                                name="<?php echo esc_attr( self::set( 'webhooks_send_email_' . $service_carrier_type, true ) ) ?>[]"
                                                multiple="multiple">
											<?php
											foreach ( $statuses as $status_k => $status_v ) {
												?>
                                                <option value="<?php echo esc_attr( $status_k ) ?>" <?php if ( in_array( $status_k, $webhooks_send_email ) ) {
													echo esc_attr( 'selected' );
												} ?>><?php echo esc_html( $status_v ) ?></option>
												<?php
											}
											?>
                                        </select>
                                        <div class="description"><?php _e( 'Send email to customers if Shipment status changes to one of these values. View <a href="admin.php?page=woocommerce-orders-tracking#email" target="_blank">Email settings</a>.', 'woocommerce-orders-tracking' ) ?></div>
                                        <div class="description"><?php _e( '<strong>*Note: </strong>Statuses you select here must be selected in your Webhook settings', 'woocommerce-orders-tracking' ) ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label><?php esc_html_e( 'Webhook URL', 'woocommerce-orders-tracking' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui fluid right labeled input <?php echo esc_attr( self::set( 'webhooks-url-container' ) ) ?>">
                                            <input type="text" readonly
                                                   class="<?php echo esc_attr( self::set( 'webhooks-url' ) ) ?>"
                                                   value="<?php echo esc_url( $webhooks_url ) ?>">
                                            <i class="check green icon"></i>
                                            <label class="vi-ui label"><span
                                                        class="vi-ui tiny positive button <?php echo esc_attr( self::set( 'webhooks-url-copy' ) ) ?>"><?php esc_html_e( 'Copy', 'woocommerce-orders-tracking' ) ?></span></label>
                                        </div>
										<?php
										if ( $webhooks_url_desc ) {
											?>
                                            <p class="description"><?php echo $webhooks_url_desc ?></p>
											<?php
										}
										?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <p>
                                <input type="submit" class="vi-ui button primary" name="wot_save_webhooks_options"
                                       value="<?php esc_html_e( 'Save', 'woocommerce-orders-tracking' ) ?> "/>
                            </p>
                        </form>
						<?php
					}
					?>
                </div>
				<?php
				if ( is_file( VI_WOOCOMMERCE_ORDERS_TRACKING_CACHE . 'webhooks_logs.txt' ) ) {
					?>
                    <div class="vi-ui segment">
                        <h3><?php esc_html_e( 'Webhooks logs', 'woocommerce-orders-tracking' ); ?></h3>
						<?php
						VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_IMPORT_CSV::print_log_html( array( VI_WOOCOMMERCE_ORDERS_TRACKING_CACHE . 'webhooks_logs.txt' ) );
						?>
                    </div>
					<?php
				}
				?>
            </div>
			<?php
		}

		public static function map_statuses_table( $service_carrier_type ) {
			ob_start();
			$map_statuses = array();
			$title        = '';
			switch ( $service_carrier_type ) {
				case 'trackingmore':
					$map_statuses = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE::map_statuses();
					$title        = esc_html__( 'TrackingMore Status', 'woocommerce-orders-tracking' );
					break;
				case 'aftership':
					$map_statuses = VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP::map_statuses();
					$title        = esc_html__( 'AfterShip Status', 'woocommerce-orders-tracking' );
					break;
				case 'easypost':
					$map_statuses = VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST::map_statuses();
					$title        = esc_html__( 'EasyPost Status', 'woocommerce-orders-tracking' );
					break;
				default:
			}
			?>
            <table class="vi-ui celled table center aligned <?php echo esc_attr( self::set( 'map-statuses' ) ) ?>">
                <thead>
                <tr>
                    <th><?php echo $title; ?></th>
                    <th><?php esc_html_e( 'Converted Status', 'woocommerce-orders-tracking' ) ?></th>
                </tr>
                </thead>
                <tbody>
				<?php
				foreach ( $map_statuses as $from => $to ) {
					?>
                    <tr>
                        <td><?php echo esc_html( ucwords( str_replace( '_', ' ', $from ) ) ) ?></td>
                        <td><?php echo esc_html( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_status_text( $to ) ) ?></td>
                    </tr>
					<?php
				}
				?>
                </tbody>
            </table>
			<?php
			return ob_get_clean();
		}

		/**
		 * Register REST API for Webhook
		 */
		public function register_api() {
			if ( $this->settings->get_params( 'service_carrier_enable' ) ) {
				$service_carrier_type = $this->settings->get_params( 'service_carrier_type' );
				if ( $service_carrier_type !== 'cainiao' ) {
					VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_LOG::create_plugin_cache_folder();
					register_rest_route(
						'woocommerce-orders-tracking', "/{$service_carrier_type}", array(
							'methods'  => 'POST',
							'callback' => array( $this, "webhook_{$service_carrier_type}" ),
							'permission_callback' => '__return_true',
						)
					);
				}
			}
		}

		/**
		 * @param $request WP_REST_Request
		 *
		 * @throws Exception
		 */
		public function webhook_trackingmore( $request ) {
			$input_json = $request->get_body();
			$request->get_body();
			$user_email          = $this->settings->get_params( 'webhooks_user_email' );
			$webhooks_enable     = $this->settings->get_params( 'webhooks_enable' );
			$change_order_status = $this->settings->get_params( 'change_order_status' );
			$send_email          = $this->settings->get_params( 'webhooks_send_email_trackingmore' );
			if ( $webhooks_enable ) {
				$input = vi_wot_json_decode( $input_json );
				if ( isset( $input['verifyInfo'] ) ) {
					$timeStr   = isset( $input['verifyInfo']['timeStr'] ) ? sanitize_text_field( $input['verifyInfo']['timeStr'] ) : '';
					$signature = isset( $input['verifyInfo']['signature'] ) ? sanitize_text_field( $input['verifyInfo']['signature'] ) : '';
					if ( self::verify_trackingmore( $timeStr, $user_email, $signature ) ) {
						if ( isset( $input['data'] ) && is_array( $input['data'] ) && count( $input['data'] ) ) {
							$tracking        = $input['data'];
							$tracking_number = isset( $tracking['tracking_number'] ) ? $tracking['tracking_number'] : '';
							if ( $tracking_number ) {
								$track_info = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE::process_trackinfo( $tracking );
								if ( $track_info ) {
									$track_info = json_encode( $track_info );
								} else {
									$track_info = '';
								}
								$last_event     = $tracking['lastEvent'];
								$status         = $tracking['status'];
								$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $status );
								$carrier_id     = $tracking['carrier_code'];
								$carrier_slug   = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE::get_carrier_slug_by_trackingmore_slug( $carrier_id );
								if ( $carrier_slug ) {
									$results = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::search_order_item_by_tracking_number( $tracking_number, '', '', $carrier_slug );
									if ( count( $results ) ) {
										$order_ids      = array_unique( array_column( $results, 'order_id' ) );
										$order_item_ids = array_unique( array_column( $results, 'order_item_id' ) );
										foreach ( $results as $result ) {
											$item_tracking_data                   = vi_wot_json_decode( $result['meta_value'] );
											$current_tracking_data                = array_pop( $item_tracking_data );
											$current_tracking_data['status']      = $status;
											$current_tracking_data['last_update'] = time();
											$item_tracking_data[]                 = $current_tracking_data;
											wc_update_order_item_meta( $result['order_item_id'], '_vi_wot_order_item_tracking_data', json_encode( $item_tracking_data ) );
										}
										$log = '';
										self::update_order_status( $tracking_number, $convert_status, $order_ids, $order_item_ids, $change_order_status, $log );
										if ( ! $log ) {
											$log = sprintf( esc_html__( 'New status received for tracking number %s: %s', 'woocommerce-orders-tracking' ), $tracking_number, $status );
										}
										self::send_email_based_on_status( $send_email, $status, $order_ids, $log );
										self::log( $log );
										if ( ! VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update_by_tracking_number( $tracking_number, $status, $carrier_slug, false, false, $track_info, $last_event ) ) {
											foreach ( $order_ids as $order_id ) {
												VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::insert( $order_id, $tracking_number, $status, $carrier_slug, '', VI_WOOCOMMERCE_ORDERS_TRACKING_FRONTEND_FRONTEND::get_shipping_country_by_order_id( $order_id ), $track_info, $last_event, '' );
											}
										}
									}
								}
							} else {
								self::log( esc_html__( 'Invalid tracking number', 'woocommerce-orders-tracking' ) );
							}
						}
					} else {
						self::log( esc_html__( 'Cannot verify webhook', 'woocommerce-orders-tracking' ) );
					}
				} else {
					self::log( esc_html__( 'Cannot verify webhook', 'woocommerce-orders-tracking' ) );
				}
			} else {
				self::log( esc_html__( 'Webhook is currently disabled', 'woocommerce-orders-tracking' ) );
			}
		}

		/**
		 * @param $request WP_REST_Request
		 *
		 * @throws Exception
		 */
		public function webhook_aftership( $request ) {
			$input_json          = $request->get_body();
			$webhooks_secret     = $this->settings->get_params( 'webhooks_secret' );
			$webhooks_enable     = $this->settings->get_params( 'webhooks_enable' );
			$change_order_status = $this->settings->get_params( 'change_order_status' );
			$send_email          = $this->settings->get_params( 'webhooks_send_email_aftership' );
			$hmac_header         = $request->get_header( 'aftership-hmac-sha256' );
			if ( $webhooks_enable ) {
				$input = vi_wot_json_decode( $input_json );
				if ( self::verify_webhook( $input_json, $hmac_header, $webhooks_secret ) ) {
					if ( isset( $input['msg'] ) && is_array( $input['msg'] ) && count( $input['msg'] ) ) {
						$tracking        = $input['msg'];
						$tracking_number = isset( $tracking['tracking_number'] ) ? $tracking['tracking_number'] : '';
						if ( $tracking_number ) {
							$track_info = VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP::get_track_info( $tracking['checkpoints'] );
							$last_event = '';
							if ( $track_info ) {
								$last_event = $track_info[0]['description'];
								$track_info = json_encode( $track_info );
							} else {
								$track_info = '';
							}
							$status         = $tracking['tag'];
							$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $status );
							$carrier_id     = $tracking['slug'];
							$carrier_slugs  = VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP::get_original_carrier_slug( $carrier_id );
							if ( count( $carrier_slugs ) ) {
								$results = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::search_order_item_by_tracking_number( $tracking_number, '', '', '' );
								if ( count( $results ) ) {
									$order_ids_by_carriers = array();
									foreach ( $results as $key => $result ) {
										$item_tracking_data    = vi_wot_json_decode( $result['meta_value'] );
										$current_tracking_data = array_pop( $item_tracking_data );
										if ( in_array( $current_tracking_data['carrier_slug'], $carrier_slugs ) ) {
											if ( ! isset( $order_ids_by_carriers[ $current_tracking_data['carrier_slug'] ] ) ) {
												$order_ids_by_carriers[ $current_tracking_data['carrier_slug'] ] = array();
											}
											$order_ids_by_carriers[ $current_tracking_data['carrier_slug'] ][] = $result['order_id'];
											$current_tracking_data['status']                                   = $status;
											$current_tracking_data['last_update']                              = time();
											$item_tracking_data[]                                              = $current_tracking_data;
											wc_update_order_item_meta( $result['order_item_id'], '_vi_wot_order_item_tracking_data', json_encode( $item_tracking_data ) );
										} else {
											unset( $results[ $key ] );
										}
									}
									$order_ids      = array_unique( array_column( $results, 'order_id' ) );
									$order_item_ids = array_unique( array_column( $results, 'order_item_id' ) );
									$log            = '';
									self::update_order_status( $tracking_number, $convert_status, $order_ids, $order_item_ids, $change_order_status, $log );
									if ( ! $log ) {
										$log = sprintf( esc_html__( 'New status received for tracking number %s: %s', 'woocommerce-orders-tracking' ), $tracking_number, $status );
									}
									self::send_email_based_on_status( $send_email, $status, $order_ids, $log );
									self::log( $log );
									foreach ( $order_ids_by_carriers as $carrier_slug => $order_ids_by_carrier ) {
										if ( ! VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update_by_tracking_number( $tracking_number, $carrier_slug, 'aftership', $status, $track_info, $last_event, $tracking['expected_delivery'], $tracking['origin_country_iso3'], $tracking['destination_country_iso3'] ) ) {
											foreach ( $order_ids_by_carrier as $order_id ) {
												VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_number, $order_id, $carrier_slug, 'aftership', $status, $track_info, $last_event, $tracking['expected_delivery'], $tracking['origin_country_iso3'], $tracking['destination_country_iso3'] );
											}
										}
									}
								}
							} else {
								self::log( esc_html__( 'No data', 'woocommerce-orders-tracking' ) );
							}
						} else {
							self::log( esc_html__( 'Invalid tracking number', 'woocommerce-orders-tracking' ) );
						}
					}
				} else {
					self::log( esc_html__( 'Cannot verify webhook', 'woocommerce-orders-tracking' ) );
				}
			} else {
				self::log( esc_html__( 'Webhook is currently disabled', 'woocommerce-orders-tracking' ) );
			}
		}

		/**
		 * @param $request WP_REST_Request
		 *
		 * @throws Exception
		 */
		public function webhook_easypost( $request ) {
			$input_json          = $request->get_body();
			$secret              = $request->get_param( 'secret_key' );
			$webhooks_password   = $this->settings->get_params( 'webhooks_password' );
			$webhooks_enable     = $this->settings->get_params( 'webhooks_enable' );
			$change_order_status = $this->settings->get_params( 'change_order_status' );
			$send_email          = $this->settings->get_params( 'webhooks_send_email_easypost' );
			if ( $webhooks_enable ) {
				if ( $secret && $secret === $webhooks_password ) {
					$input = vi_wot_json_decode( $input_json );
					$event = isset( $input['description'] ) ? sanitize_text_field( $input['description'] ) : '';
					if ( ( $event === 'tracker.created' || $event === 'tracker.updated' ) && isset( $input['result'] ) && is_array( $input['result'] ) && count( $input['result'] ) ) {
						$tracking        = $input['result'];
						$tracking_number = isset( $tracking['tracking_code'] ) ? $tracking['tracking_code'] : '';
						if ( $tracking_number ) {
							$track_info = VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST::get_track_info( $tracking['tracking_details'] );
							$last_event = '';
							if ( $track_info ) {
								$last_event = $track_info[0]['description'];
								$track_info = json_encode( $track_info );
							} else {
								$track_info = '';
							}
							$status         = $tracking['status'];
							$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $status );
							$carrier_id     = $tracking['carrier'];
							$carrier_slugs  = VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST::get_original_carrier_slug( $carrier_id );
							if ( count( $carrier_slugs ) ) {
								$results = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::search_order_item_by_tracking_number( $tracking_number, '', '', '' );
								if ( count( $results ) ) {
									$order_ids_by_carriers = array();
									$order_ids             = array();
									foreach ( $results as $key => $result ) {
										$item_tracking_data    = vi_wot_json_decode( $result['meta_value'] );
										$current_tracking_data = array_pop( $item_tracking_data );
										if ( in_array( $current_tracking_data['carrier_slug'], $carrier_slugs ) ) {
											if ( ! isset( $order_ids_by_carriers[ $current_tracking_data['carrier_slug'] ] ) ) {
												$order_ids_by_carriers[ $current_tracking_data['carrier_slug'] ] = array();
											}
											$order_ids_by_carriers[ $current_tracking_data['carrier_slug'] ][] = $result['order_id'];
//											if ( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $current_tracking_data['status'] ) !== 'delivered' ) {
												$order_ids[] = $result['order_id'];
//											}
											$current_tracking_data['status']      = $status;
											$current_tracking_data['last_update'] = time();
											$item_tracking_data[]                 = $current_tracking_data;
											wc_update_order_item_meta( $result['order_item_id'], '_vi_wot_order_item_tracking_data', json_encode( $item_tracking_data ) );
										} else {
											unset( $results[ $key ] );
										}
									}
									$order_ids      = array_unique( $order_ids );
									$order_item_ids = array_unique( array_column( $results, 'order_item_id' ) );
									$log            = '';
									self::update_order_status( $tracking_number, $convert_status, $order_ids, $order_item_ids, $change_order_status, $log );
									if ( ! $log ) {
										$log = sprintf( esc_html__( 'New status received for tracking number %s: %s', 'woocommerce-orders-tracking' ), $tracking_number, $status );
									}
									self::send_email_based_on_status( $send_email, $status, $order_ids, $log );
									self::log( $log );
									foreach ( $order_ids_by_carriers as $carrier_slug => $order_ids_by_carrier ) {
										if ( ! VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update_by_tracking_number( $tracking_number, $carrier_slug, 'easypost', $status, $track_info, $last_event, $tracking['est_delivery_date'] ) ) {
											foreach ( $order_ids_by_carrier as $order_id ) {
												VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_number, $order_id, $carrier_slug, 'easypost', $status, $track_info, $last_event, $tracking['est_delivery_date'] );
											}
										}
									}
								}
							} else {
								self::log( esc_html__( 'No data', 'woocommerce-orders-tracking' ) );
							}
						} else {
							self::log( esc_html__( 'Invalid tracking number', 'woocommerce-orders-tracking' ) );
						}
					}
				} else {
					self::log( esc_html__( 'Cannot verify webhook', 'woocommerce-orders-tracking' ) );
				}
			} else {
				self::log( esc_html__( 'Webhook is currently disabled', 'woocommerce-orders-tracking' ) );
			}
		}

		/**
		 * @param $tracking_number
		 * @param $convert_status
		 * @param $order_ids
		 * @param $order_item_ids
		 * @param $change_order_status
		 * @param $log
		 *
		 * @throws Exception
		 */
		public static function update_order_status( $tracking_number, $convert_status, $order_ids, $order_item_ids, $change_order_status, &$log ) {
			$changed_orders = VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_status( $convert_status, $order_ids, $order_item_ids, $change_order_status );
			if ( count( $changed_orders ) ) {
				$wc_order_statuses = wc_get_order_statuses();
				$new_order_status  = isset( $wc_order_statuses[ $change_order_status ] ) ? $wc_order_statuses[ $change_order_status ] : $change_order_status;
				$log               .= sprintf( _n( 'New status received for tracking number %s: %s, order %s status changed to %s', 'New status received for tracking number %s: %s, orders %s status changed to %s', count( $changed_orders ), 'woocommerce-orders-tracking' ), $tracking_number, $convert_status, implode( ', ', $changed_orders ), $new_order_status );
			}
		}

		/**
		 * @param $send_email
		 * @param $status
		 * @param $order_ids
		 * @param $log
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function send_email_based_on_status( $send_email, $status, $order_ids, &$log ) {
			$sent_emails = array();
			if ( $send_email && in_array( strtolower( $status ), $send_email ) ) {
				foreach ( $order_ids as $order_id ) {
					if ( VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_IMPORT_CSV::send_mail( $order_id, array(), true ) ) {
						$sent_emails[] = $order_id;
					}
				}
				if ( count( $sent_emails ) ) {
					$log .= PHP_EOL . sprintf( _n( 'Email sent for order %s', 'Email sent for orders %s', count( $sent_emails ), 'woocommerce-orders-tracking' ), implode( ', ', $sent_emails ) );
				}
			}

			return $sent_emails;
		}

		/**Verify Webhook for TrackingMore
		 *
		 * @param $timeStr
		 * @param $useremail
		 * @param $signature
		 *
		 * @return int
		 */
		public static function verify_trackingmore( $timeStr, $useremail, $signature ) {
			$result = hash_hmac( 'sha256', $timeStr, $useremail );

			return strcmp( $result, $signature ) == 0 ? 1 : 0;
		}

		public static function verify_webhook( $data, $hmac_header, $shared_secret ) {
			$calculated_hmac = base64_encode( hash_hmac( 'sha256', $data, $shared_secret, true ) );

			return hash_equals( $hmac_header, $calculated_hmac );
		}

		public static function set( $name, $set_name = false ) {
			return VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::set( $name, $set_name );
		}

		private static function log( $content ) {
			VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_LOG::log( $content, 'webhooks_logs.txt' );
		}
	}
}