<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO {
	protected $settings;
	protected $carriers;
	protected $tracking_service_action_buttons;

	public function __construct() {
		$this->settings = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
		VILLATHEME_ADMIN_SHOW_MESSAGE::get_instance();
		$this->carriers = array();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ), 99 );
		add_action( 'admin_head-edit.php', array( $this, 'addCustomImportButton' ) );
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_new_order_admin_list_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array(
			$this,
			'manage_shop_order_posts_custom_column'
		), 10, 2 );
		add_action( 'wp_ajax_vi_wot_refresh_track_info', array( $this, 'refresh_track_info' ) );
		add_action( 'wp_ajax_vi_woo_orders_tracking_send_tracking_email', array( $this, 'send_tracking_email' ) );
		add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ), 10 );
		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
		/*Woo Alidropship*/
		add_filter( 'vi_woo_alidropship_order_item_tracking_data', array(
			$this,
			'vi_woo_alidropship_order_item_tracking_data'
		), 10, 3 );
	}

	public function admin_enqueue_script() {
		global $pagenow, $post_type;
		if ( $pagenow === 'edit.php' && $post_type === 'shop_order' ) {
			wp_enqueue_style( 'semantic-ui-popup', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'popup.min.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			wp_enqueue_style( 'vi-wot-admin-order-manager-icon', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'woo-orders-tracking-icons.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			wp_enqueue_style( 'vi-wot-admin-order-manager-css', VI_WOOCOMMERCE_ORDERS_TRACKING_CSS . 'admin-order-manager.css', '', VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			$css = '.woo-orders-tracking-tracking-number-container-delivered a{color:' . $this->settings->get_params( 'timeline_track_info_status_background_delivered' ) . '}';
			$css .= '.woo-orders-tracking-tracking-number-container-pickup a{color:' . $this->settings->get_params( 'timeline_track_info_status_background_pickup' ) . '}';
			$css .= '.woo-orders-tracking-tracking-number-container-transit a{color:' . $this->settings->get_params( 'timeline_track_info_status_background_transit' ) . '}';
			$css .= '.woo-orders-tracking-tracking-number-container-pending a{color:' . $this->settings->get_params( 'timeline_track_info_status_background_pending' ) . '}';
			$css .= '.woo-orders-tracking-tracking-number-container-alert a{color:' . $this->settings->get_params( 'timeline_track_info_status_background_alert' ) . '}';
			wp_add_inline_style( 'vi-wot-admin-order-manager-css', $css );
			wp_enqueue_script( 'vi-wot-admin-order-manager-js', VI_WOOCOMMERCE_ORDERS_TRACKING_JS . 'admin-order-manager.js', array( 'jquery' ), VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION );
			wp_localize_script(
				'vi-wot-admin-order-manager-js',
				'vi_wot_admin_order_manager',
				array(
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'paypal_image'  => VI_WOOCOMMERCE_ORDERS_TRACKING_PAYPAL_IMAGE,
					'loading_image' => VI_WOOCOMMERCE_ORDERS_TRACKING_LOADING_IMAGE,
					'message_copy'  => esc_html__( 'Tracking number is copied to clipboard', 'woocommerce-orders-tracking' ),
				)
			);
		}
	}

	public function vi_woo_alidropship_order_item_tracking_data( $current_tracking_data, $item_id, $order_id ) {
		if ( ! empty( $current_tracking_data['carrier_slug'] ) ) {
			$carrier = $this->get_shipping_carrier_by_slug( $current_tracking_data['carrier_slug'] );
			if ( is_array( $carrier ) && count( $carrier ) ) {
				$current_tracking_data['carrier_name'] = $carrier['name'];
				$order                                 = wc_get_order( $order_id );
				$postal_code                           = '';
				if ( $order ) {
					$postal_code = $order->get_shipping_postcode();
				}
				$current_tracking_data['carrier_url'] = $this->settings->get_url_tracking( $carrier['url'], $current_tracking_data['tracking_number'], $current_tracking_data['carrier_slug'], $postal_code );
			}
		}

		return $current_tracking_data;
	}

	public static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::set( $name, $set_name );
	}

	public function add_nonce_field() {
		wp_nonce_field( 'vi_wot_item_action_nonce', '_vi_wot_item_nonce' );
	}

	public function addCustomImportButton() {
		global $current_screen;
		if ( 'shop_order' != $current_screen->post_type ) {
			return;
		}
		add_action( 'admin_footer', array( $this, 'add_nonce_field' ) );
		?>
        <script type="text/javascript">
            'use strict';
            jQuery(document).ready(function ($) {
                jQuery(".wrap .page-title-action").eq(0).after("<a class='page-title-action' target='_blank' href='<?php echo esc_url( admin_url( 'admin.php?page=woo-orders-tracking-import-csv' ) ); ?>'><?php esc_html_e( 'Import tracking number', 'woocommerce-orders-tracking' ) ?></a>"
                    + "<a class='page-title-action' target='_blank' href='<?php echo esc_url( admin_url( 'admin.php?page=woo-orders-tracking-export' ) ); ?>'><?php esc_html_e( 'Export tracking number ', 'woocommerce-orders-tracking' ) ?></a>");
            });
        </script>
		<?php
	}

	public function add_new_order_admin_list_column( $columns ) {
		$columns['vi_wot_tracking_code'] = '<span class="' . esc_attr( self::set( array(
				'tracking-service-refresh-bulk-container'
			) ) ) . '">' . esc_html__( 'Tracking Number', 'woocommerce-orders-tracking' ) . '<span class="woo_orders_tracking_icons-refresh ' . esc_attr( self::set( array(
				'tracking-service-refresh-bulk'
			) ) ) . '" title="' . esc_html__( 'Bulk refresh tracking', 'woocommerce-orders-tracking' ) . '"></span></span>';

		return $columns;
	}

	public function tracking_service_action_buttons_html( $tracking_link, $current_tracking_data, $tracking_status ) {
		if ( $this->tracking_service_action_buttons === null ) {
			$this->tracking_service_action_buttons = '';
			$service_carrier_enable                = $this->settings->get_params( 'service_carrier_enable' );
			$service_carrier_api_key               = $this->settings->get_params( 'service_carrier_api_key' );
			$service_carrier_type                  = $this->settings->get_params( 'service_carrier_type' );
			ob_start();
			?>
            <div class="<?php echo esc_attr( self::set( 'tracking-service-action-button-container' ) ) ?>">
                    <span class="woo_orders_tracking_icons-duplicate <?php echo esc_attr( self::set( array(
	                    'tracking-service-action-button',
	                    'tracking-service-copy'
                    ) ) ) ?>" title="<?php echo esc_attr__( 'Copy tracking number', 'woocommerce-orders-tracking' ) ?>">
                    </span>
                <a href="{tracking_link}" target="_blank">
                        <span class="woo_orders_tracking_icons-redirect <?php echo esc_attr( self::set( array(
	                        'tracking-service-action-button',
	                        'tracking-service-track'
                        ) ) ) ?>"
                              title="<?php echo esc_attr__( 'Open tracking link', 'woocommerce-orders-tracking' ) ?>">
                        </span>
                </a>
				<?php
				if ( $service_carrier_enable && $service_carrier_api_key && $service_carrier_type !== 'cainiao' ) {
					?>
                    <span class="woo_orders_tracking_icons-refresh <?php echo esc_attr( self::set( array(
						'tracking-service-action-button',
						'tracking-service-refresh'
					) ) ) ?>" title="{button_refresh_title}">
                        </span>
					<?php
				}
				?>
            </div>
			<?php
			$this->tracking_service_action_buttons = ob_get_clean();
		}
		$button_refresh_title = esc_html__( 'Update latest data', 'woocommerce-orders-tracking' );
		if ( ! empty( $current_tracking_data['last_update'] ) ) {
			$button_refresh_title = sprintf( esc_html__( 'Last update: %s. Click to refresh.', 'woocommerce-orders-tracking' ), date_i18n( 'Y-m-d H:i:s', $current_tracking_data['last_update'] ) );
		}

		return str_replace( array( '{button_refresh_title}', '{tracking_link}' ), array(
			$button_refresh_title,
			esc_url( $tracking_link )
		), $this->tracking_service_action_buttons );
	}

	public function get_shipping_carrier_by_slug( $slug ) {
		if ( ! isset( $this->carriers[ $slug ] ) ) {
			$this->carriers[ $slug ] = $this->settings->get_shipping_carrier_by_slug( $slug );
		}

		return $this->carriers[ $slug ];
	}

	/**
	 * @param $column
	 * @param $order_id
	 *
	 * @throws Exception
	 */
	public function manage_shop_order_posts_custom_column( $column, $order_id ) {
		if ( $column === 'vi_wot_tracking_code' ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$line_items = $order->get_items();
				if ( count( $line_items ) ) {
					$tracking_list          = array();
					$transID                = $order->get_transaction_id();
					$paypal_method          = $order->get_payment_method();
					$paypal_added_trackings = get_post_meta( $order_id, 'vi_wot_paypal_added_tracking_numbers', true );
					if ( ! $paypal_added_trackings ) {
						$paypal_added_trackings = array();
					}
					?>
                    <div class="<?php echo esc_attr( self::set( 'tracking-number-column-container' ) ) ?>">
						<?php
						foreach ( $line_items as $item_id => $line_item ) {
							$item_tracking_data    = wc_get_order_item_meta( $item_id, '_vi_wot_order_item_tracking_data', true );
							$current_tracking_data = array(
								'tracking_number' => '',
								'carrier_slug'    => '',
								'carrier_url'     => '',
								'carrier_name'    => '',
								'carrier_type'    => '',
								'time'            => time(),
							);
							if ( $item_tracking_data ) {
								$item_tracking_data    = vi_wot_json_decode( $item_tracking_data );
								$current_tracking_data = array_pop( $item_tracking_data );
							}
							$this->print_tracking_row( $current_tracking_data, $item_id, $order_id, $order, $transID, $paypal_method, $paypal_added_trackings, $tracking_list );
							$quantity = $line_item->get_quantity();
							if ( $quantity > 1 ) {
								$item_tracking_data = wc_get_order_item_meta( $item_id, '_vi_wot_order_item_tracking_data_by_quantity', true );
								if ( $item_tracking_data ) {
									$item_tracking_data = vi_wot_json_decode( $item_tracking_data );
									for ( $i = 0; $i < $quantity - 1; $i ++ ) {
										if ( isset( $item_tracking_data[ $i ] ) ) {
											$current_tracking_data = $item_tracking_data[ $i ];
											$this->print_tracking_row( $current_tracking_data, $item_id, $order_id, $order, $transID, $paypal_method, $paypal_added_trackings, $tracking_list );
										}
									}
								}
							}
						}
						if ( count( $tracking_list ) ) {
							?>
                            <div class="<?php echo esc_attr( self::set( 'send-tracking-email-container' ) ) ?>">
                                <span class="<?php echo esc_attr( self::set( 'send-tracking-email' ) ) ?> dashicons dashicons-email-alt"
                                      data-order_id="<?php echo esc_attr( $order_id ) ?>"
                                      title="<?php esc_attr_e( 'Send tracking email', 'woocommerce-orders-tracking' ) ?>"></span>
                            </div>
							<?php
						}
						?>
                    </div>
					<?php
				}
			}
		}
	}

	protected function print_tracking_row( $current_tracking_data, $item_id, $order_id, $order, $transID, $paypal_method, $paypal_added_trackings, &$tracking_list ) {
		$tracking_number = apply_filters( 'vi_woo_orders_tracking_current_tracking_number', $current_tracking_data['tracking_number'], $item_id, $order_id );
		$carrier_url     = apply_filters( 'vi_woo_orders_tracking_current_tracking_url', $current_tracking_data['carrier_url'], $item_id, $order_id );
		$carrier_name    = apply_filters( 'vi_woo_orders_tracking_current_carrier_name', $current_tracking_data['carrier_name'], $item_id, $order_id );
		$carrier_slug    = apply_filters( 'vi_woo_orders_tracking_current_carrier_slug', $current_tracking_data['carrier_slug'], $item_id, $order_id );
		$tracking_status = isset( $current_tracking_data['status'] ) ? VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $current_tracking_data['status'] ) : '';
		if ( $tracking_number && ! in_array( $tracking_number, $tracking_list ) ) {
			$tracking_list[] = $tracking_number;
			$carrier         = $this->get_shipping_carrier_by_slug( $current_tracking_data['carrier_slug'] );
			if ( is_array( $carrier ) && count( $carrier ) ) {
				$carrier_url  = $carrier['url'];
				$carrier_name = $carrier['name'];
			}
			$tracking_url_show = apply_filters( 'vi_woo_orders_tracking_current_tracking_url_show', $this->settings->get_url_tracking( $carrier_url, $tracking_number, $carrier_slug, $order->get_shipping_postcode(), false, true, $order_id ), $item_id, $order_id );
			$container_class   = array( 'tracking-number-container' );
			if ( $tracking_status ) {
				$container_class[] = 'tracking-number-container-' . $tracking_status;
			}
			?>
            <div class="<?php echo esc_attr( self::set( $container_class ) ) ?>"
                 data-tracking_number="<?php echo esc_attr( $tracking_number ) ?>"
                 data-carrier_slug="<?php echo esc_attr( $carrier_slug ) ?>"
                 data-order_id="<?php echo esc_attr( $order_id ) ?>" <?php if ( $tracking_status ) {
				echo 'data-tooltip="' . esc_attr( VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_status_text( $tracking_status ) ) . '"';
			} ?>>
                <a class="<?php echo esc_attr( self::set( 'tracking-number' ) ) ?>"
                   href="<?php echo esc_url( $tracking_url_show ) ?>"
                   title="<?php echo esc_attr__( "Tracking carrier {$carrier_name}", 'woocommerce-orders-tracking' ) ?>"
                   target="_blank"><?php echo esc_html( $tracking_number ) ?></a>
				<?php
				echo wp_kses_post( $this->tracking_service_action_buttons_html( $tracking_url_show, $current_tracking_data, $tracking_status ) );
				if ( $transID && in_array( $paypal_method, $this->settings->get_params( 'supported_paypal_gateways' ) ) ) {
					$paypal_class = array( 'item-tracking-button-add-to-paypal-container' );
					if ( ! in_array( $tracking_number, $paypal_added_trackings ) ) {
						$paypal_class[] = 'paypal-active';
						$title          = esc_attr__( 'Add this tracking number to PayPal', 'woocommerce-orders-tracking' );
					} else {
						$paypal_class[] = 'paypal-inactive';
						$title          = esc_attr__( 'This tracking number was added to PayPal', 'woocommerce-orders-tracking' );
					}
					?>
                    <span class="<?php echo esc_attr( self::set( $paypal_class ) ) ?>"
                          data-item_id="<?php echo esc_attr( $item_id ) ?>"
                          data-order_id="<?php echo esc_attr( $order_id ) ?>">
                                        <img class="<?php echo esc_attr( self::set( 'item-tracking-button-add-to-paypal' ) ) ?>"
                                             title="<?php echo esc_attr( $title ) ?>"
                                             src="<?php echo esc_url( VI_WOOCOMMERCE_ORDERS_TRACKING_PAYPAL_IMAGE ) ?>">
                                    </span>
					<?php
				}
				?>
            </div>
			<?php
		}
	}

	/**
	 * @throws Exception
	 */
	public function send_tracking_email() {
		$action_nonce = isset( $_POST['action_nonce'] ) ? wp_unslash( sanitize_text_field( $_POST['action_nonce'] ) ) : '';
		$order_id     = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
		$response     = array(
			'status'          => 'error',
			'message'         => sprintf( esc_html__( '#%s: Error sending email', 'woocommerce-orders-tracking' ), $order_id ),
			'message_content' => '',
		);
		if ( $order_id && wp_verify_nonce( $action_nonce, 'vi_wot_item_action_nonce' ) ) {
			$send_email = VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_IMPORT_CSV::send_mail( $order_id, array(), true );
			if ( $send_email ) {
				$response['status']  = 'success';
				$response['message'] = sprintf( esc_html__( '#%s: Tracking email sent', 'woocommerce-orders-tracking' ), $order_id );
			}
		}

		wp_send_json( $response );
	}

	/**
	 * @param $tracking_number
	 * @param $carrier_slug
	 * @param $status
	 * @param string $change_order_status
	 *
	 * @throws Exception
	 */
	public static function update_order_items_tracking_status( $tracking_number, $carrier_slug, $status, $change_order_status = '' ) {
		$results = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::search_order_item_by_tracking_number( $tracking_number, '', '', $carrier_slug, false );
		$now     = time();
		if ( count( $results ) ) {
			$order_ids      = array_unique( array_column( $results, 'order_id' ) );
			$order_item_ids = array_unique( array_column( $results, 'order_item_id' ) );
			foreach ( $results as $result ) {
				$item_tracking_data = vi_wot_json_decode( $result['meta_value'] );
				if ( $result['meta_key'] === '_vi_wot_order_item_tracking_data' ) {
					$current_tracking_data                = array_pop( $item_tracking_data );
					$current_tracking_data['status']      = $status;
					$current_tracking_data['last_update'] = $now;
					$item_tracking_data[]                 = $current_tracking_data;
					wc_update_order_item_meta( $result['order_item_id'], '_vi_wot_order_item_tracking_data', json_encode( $item_tracking_data ) );
				} elseif ( $result['meta_key'] === '_vi_wot_order_item_tracking_data_by_quantity' ) {
					foreach ( $item_tracking_data as $current_tracking_data ) {
						if ( $current_tracking_data['tracking_number'] == $tracking_number && $current_tracking_data['carrier_slug'] === $carrier_slug ) {
							$current_tracking_data['status']      = $status;
							$current_tracking_data['last_update'] = $now;
						}
					}
					wc_update_order_item_meta( $result['order_item_id'], '_vi_wot_order_item_tracking_data_by_quantity', json_encode( $item_tracking_data ) );
				}
			}
			$convert_status = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $status );
			self::update_order_status( $convert_status, $order_ids, $order_item_ids, $change_order_status );
		}
	}

	/**
	 * @param $status
	 * @param $order_ids
	 * @param $order_item_ids
	 * @param $change_order_status
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function update_order_status( $status, $order_ids, $order_item_ids, $change_order_status ) {
		$changed_orders = array();
		if ( $status === 'delivered' && $change_order_status ) {
			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$line_items = $order->get_items();
					if ( count( $line_items ) ) {
						$delivered = 0;
						foreach ( $line_items as $line_item_k => $line_item_v ) {
							if ( ! in_array( $line_item_k, $order_item_ids ) ) {
								$item_tracking_data = wc_get_order_item_meta( $line_item_k, '_vi_wot_order_item_tracking_data', true );
								if ( $item_tracking_data ) {
									$item_tracking_data    = vi_wot_json_decode( $item_tracking_data );
									$current_tracking_data = array_pop( $item_tracking_data );
									$tracking_status       = isset( $current_tracking_data['status'] ) ? VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $current_tracking_data['status'] ) : '';
									if ( $tracking_status === 'delivered' ) {
										$delivered ++;
									}
								}
							} else {
								$delivered ++;
							}
						}
						if ( apply_filters( 'vi_woo_orders_tracking_is_order_delivered', $delivered === count( $line_items ), $order ) ) {
							$update_status = substr( $change_order_status, 3 );
							if ( $update_status !== $order->get_status() ) {
								$changed_orders[] = $order_id;
								$order->update_status( $update_status );
							}
						}
					}
				}
			}
		}

		return $changed_orders;
	}

	/** For Aftership and Easypost
	 * @throws Exception
	 */
	public function refresh_track_info() {
		$response        = array(
			'status'                   => 'success',
			'message'                  => esc_html__( 'Update tracking data successfully.', 'woocommerce-orders-tracking' ),
			'message_content'          => '',
			'tracking_change'          => 0,
			'tracking_status'          => '',
			'tracking_container_class' => '',
			'button_title'             => sprintf( esc_html__( 'Last update: %s. Click to refresh.', 'woocommerce-orders-tracking' ), date_i18n( 'Y-m-d H:i:s', time() ) ),
		);
		$tracking_number = isset( $_POST['tracking_number'] ) ? sanitize_text_field( $_POST['tracking_number'] ) : '';
		$carrier_slug    = isset( $_POST['carrier_slug'] ) ? sanitize_text_field( $_POST['carrier_slug'] ) : '';
		$order_id        = isset( $_POST['order_id'] ) ? sanitize_text_field( stripslashes( $_POST['order_id'] ) ) : '';
		$order           = wc_get_order( $order_id );
		if ( $order && $tracking_number && $carrier_slug && $this->settings->get_params( 'service_carrier_enable' ) ) {
			$response['message_content'] = '<div>' . sprintf( esc_html__( 'Tracking number: %s', 'woocommerce-orders-tracking' ), $tracking_number ) . '</div>';
			$carrier                     = $this->get_shipping_carrier_by_slug( $carrier_slug );
			if ( is_array( $carrier ) && count( $carrier ) ) {
				$status                      = '';
				$convert_status              = '';
				$carrier_name                = $carrier['name'];
				$tracking_more_slug          = empty( $carrier['tracking_more_slug'] ) ? VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE::get_carrier_slug_by_name( $carrier_name ) : $carrier['tracking_more_slug'];
				$response['message_content'] .= '<div>' . sprintf( esc_html__( 'Carrier: %s', 'woocommerce-orders-tracking' ), $carrier_name ) . '</div>';
				$service_carrier_type        = $this->settings->get_params( 'service_carrier_type' );
				$change_order_status         = $this->settings->get_params( 'change_order_status' );
				switch ( $service_carrier_type ) {
					case 'trackingmore':
						$tracking_from_db        = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::get_row_by_tracking_number( $tracking_number, $carrier_slug, $order_id );
						$service_carrier_api_key = $this->settings->get_params( 'service_carrier_api_key' );
						$trackingMore            = new VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE( $service_carrier_api_key );
						$description             = '';
						$track_info              = '';
						if ( ! count( $tracking_from_db ) ) {
							$track_data = $trackingMore->create_tracking( $tracking_number, $tracking_more_slug, $order_id );
							if ( $track_data['status'] === 'success' ) {
								$status = $track_data['data']['status'];
								VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::insert( $order_id, $tracking_number, $status, $carrier_slug, $carrier_name, VI_WOOCOMMERCE_ORDERS_TRACKING_FRONTEND_FRONTEND::get_shipping_country_by_order_id( $order_id ), $track_info, '' );
							} else {
								if ( $track_data['code'] === 4016 ) {
									/*Tracking exists*/
									$track_data  = $trackingMore->get_tracking( $tracking_number, $tracking_more_slug );
									$modified_at = '';
									if ( $track_data['status'] === 'success' ) {
										if ( count( $track_data['data'] ) ) {
											$track_info  = json_encode( $track_data['data'] );
											$last_event  = array_shift( $track_data['data'] );
											$status      = $last_event['status'];
											$description = $last_event['description'];
											$modified_at = false;
										}
									} else {
										$response['status']  = 'error';
										$response['message'] = $track_data['data'];
									}
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::insert( $order_id, $tracking_number, $status, $carrier_slug, $carrier_name, VI_WOOCOMMERCE_ORDERS_TRACKING_FRONTEND_FRONTEND::get_shipping_country_by_order_id( $order_id ), $track_info, $description, $modified_at );
								} else {
									$response['status']  = 'error';
									$response['message'] = $track_data['data'];
								}
							}
						} else {
							$need_update_tracking_table = true;
							$convert_status             = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] );
							if ( $convert_status !== 'delivered' ) {
								$track_data = $trackingMore->get_tracking( $tracking_number, $tracking_more_slug );
								if ( $track_data['status'] === 'success' ) {
									if ( count( $track_data['data'] ) ) {
										$need_update_tracking_table = false;
										$track_info                 = json_encode( $track_data['data'] );
										$last_event                 = array_shift( $track_data['data'] );
										$status                     = $last_event['status'];
										VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update( $tracking_from_db['id'], $order_id, $status, $carrier_slug, $carrier_name, VI_WOOCOMMERCE_ORDERS_TRACKING_FRONTEND_FRONTEND::get_shipping_country_by_order_id( $order_id ), $track_info, $description );
										if ( $last_event['status'] !== $tracking_from_db['status'] || $track_info !== $tracking_from_db['track_info'] ) {
											$response['tracking_change'] = 1;
										}
									}
								} else {
									if ( $track_data['code'] === 4017 ) {
										/*Tracking NOT exists*/
										$track_data = $trackingMore->create_tracking( $tracking_number, $tracking_more_slug, $order_id );
										if ( $track_data['status'] === 'success' ) {
											$status = $track_data['data']['status'];
											VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::insert( $order_id, $tracking_number, $status, $carrier_slug, $carrier_name, VI_WOOCOMMERCE_ORDERS_TRACKING_FRONTEND_FRONTEND::get_shipping_country_by_order_id( $order_id ), $track_info, '' );
										}
									} else {
										$response['status']  = 'error';
										$response['message'] = $track_data['data'];
									}
								}
							} else {
								$status = $tracking_from_db['status'];
							}

							if ( $need_update_tracking_table ) {
								if ( $order_id != $tracking_from_db['order_id'] ) {
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update( $tracking_from_db['id'], $order_id, $status, $carrier_slug, $carrier_name, VI_WOOCOMMERCE_ORDERS_TRACKING_FRONTEND_FRONTEND::get_shipping_country_by_order_id( $order_id ), $track_info, $description );
								} else {
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update( $tracking_from_db['id'], '', false, false, false, false, false, false, '' );
								}
							}
						}
						break;
					case 'aftership':
						$tracking_from_db        = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_row_by_tracking_number( $tracking_number, $carrier_slug, $service_carrier_type, $order_id );
						$service_carrier_api_key = $this->settings->get_params( 'service_carrier_api_key' );
						$find_carrier            = VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP::get_carrier_slug_by_name( $carrier_name );
						$aftership               = new VI_WOOCOMMERCE_ORDERS_TRACKING_AFTERSHIP( $service_carrier_api_key );
						$description             = '';
						$track_info              = '';
						if ( ! count( $tracking_from_db ) ) {
							$track_data = $aftership->create( $tracking_number, $find_carrier, $order_id );
							if ( $track_data['status'] === 'success' ) {
								$status = $track_data['data']['tag'];
								VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_number, $order_id, $carrier_slug, $service_carrier_type, $status, '', '', $track_data['est_delivery_date'] );
							} else {
								if ( $track_data['code'] === 4003 ) {
									/*Tracking exists*/
									$update_args = array(
										'order_id'      => $order_id,
										'emails'        => array( $order->get_billing_email() ),
										'customer_name' => $order->get_billing_first_name(),
									);
									$mobile      = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::format_phone_number( $order->get_billing_phone(), $order->get_shipping_country() );
									if ( $mobile ) {
										$update_args['smses'] = array( $mobile );
									}
									$track_data = $aftership->update( $tracking_number, $find_carrier, $update_args );
									if ( $track_data['status'] === 'success' ) {
										if ( count( $track_data['data'] ) ) {
											$track_info  = json_encode( $track_data['data'] );
											$last_event  = array_shift( $track_data['data'] );
											$status      = $last_event['status'];
											$description = $last_event['description'];
										}
									} else {
										$response['status']  = 'error';
										$response['message'] = $track_data['data'];
									}
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_number, $order_id, $carrier_slug, $service_carrier_type, $status, $track_info, $description, '', '' );
								} else {
									$response['status']  = 'error';
									$response['message'] = $track_data['data'];
								}
							}
						} else {
							$need_update_tracking_table = true;
							$convert_status             = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] );
							if ( $convert_status !== 'delivered' ) {
								$update_args = array(
									'order_id'      => $order_id,
									'emails'        => array( $order->get_billing_email() ),
									'customer_name' => $order->get_billing_first_name(),
								);
								$mobile      = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::format_phone_number( $order->get_billing_phone(), $order->get_shipping_country() );
								if ( $mobile ) {
									$update_args['smses'] = array( $mobile );
								}
								$track_data = $aftership->update( $tracking_number, $find_carrier, $update_args );
								if ( $track_data['status'] === 'success' ) {
									if ( count( $track_data['data'] ) ) {
										$need_update_tracking_table = false;
										$track_info                 = json_encode( $track_data['data'] );
										$last_event                 = array_shift( $track_data['data'] );
										$status                     = $last_event['status'];
										VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'], $order_id, $carrier_slug, $service_carrier_type, $status, $track_info, $last_event['description'], $track_data['est_delivery_date'] );
										if ( $last_event['status'] !== $tracking_from_db['status'] || $track_info !== $tracking_from_db['track_info'] ) {
											$response['tracking_change'] = 1;
										}
									}
								} else {
									if ( $track_data['code'] === 4004 ) {
										/*Tracking NOT exists*/
										$track_data = $aftership->create( $tracking_number, $find_carrier, $order_id );
										if ( $track_data['status'] !== 'success' ) {
											$response['status']  = 'error';
											$response['message'] = $track_data['data'];
										}
									} else {
										$response['status']  = 'error';
										$response['message'] = $track_data['data'];
									}
								}
							} else {
								$status = $tracking_from_db['status'];
							}

							if ( $need_update_tracking_table && $order_id != $tracking_from_db['order_id'] || $service_carrier_type !== $tracking_from_db['carrier_service'] ) {
								VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'], $order_id, $carrier_slug, $service_carrier_type );
							}
						}
						break;
					case 'easypost':
						$tracking_from_db        = VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::get_row_by_tracking_number( $tracking_number, $carrier_slug, $service_carrier_type, $order_id );
						$service_carrier_api_key = $this->settings->get_params( 'service_carrier_api_key' );
						$find_carrier            = VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST::get_carrier_slug_by_name( $carrier_name );
						$easyPost                = new VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST( $service_carrier_api_key );
						if ( ! count( $tracking_from_db ) ) {
							$track_data = $easyPost->create( $tracking_number, $find_carrier );
							if ( $track_data['status'] === 'success' ) {
								if ( count( $track_data['data'] ) ) {
									$track_info = json_encode( $track_data['data'] );
									$last_event = array_shift( $track_data['data'] );
									$status     = $last_event['status'];
									VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_number, $order_id, $carrier_slug, $service_carrier_type, $status, $track_info, $last_event['description'], $track_data['est_delivery_date'] );
								}
							} else {
								$response['status']  = 'error';
								$response['message'] = $track_data['data'];
							}
						} else {
							$need_update_tracking_table = true;
							$convert_status             = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $tracking_from_db['status'] );
							if ( $convert_status !== 'delivered' ) {
								$track_data = $easyPost->retrieve( $tracking_number );
								if ( $track_data['status'] === 'success' ) {
									if ( count( $track_data['data'] ) ) {
										$need_update_tracking_table = false;
										$track_info                 = json_encode( $track_data['data'] );
										$last_event                 = array_shift( $track_data['data'] );
										$status                     = $last_event['status'];
										VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'], $order_id, $carrier_slug, $service_carrier_type, $status, $track_info, $last_event['description'], $track_data['est_delivery_date'] );
										if ( $last_event['status'] !== $tracking_from_db['status'] || $track_info !== $tracking_from_db['track_info'] ) {
											$response['tracking_change'] = 1;
										}
									}
								} else {
									if ( $track_data['code'] === 404 ) {
										$track_data = $easyPost->create( $tracking_number, $find_carrier );
										if ( $track_data['status'] === 'success' ) {
											if ( count( $track_data['data'] ) ) {
												$track_info = json_encode( $track_data['data'] );
												$last_event = array_shift( $track_data['data'] );
												$status     = $last_event['status'];
												VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::insert( $tracking_number, $order_id, $carrier_slug, $service_carrier_type, $status, $track_info, $last_event['description'], $track_data['est_delivery_date'] );
											}
										} else {
											$response['status']  = 'error';
											$response['message'] = $track_data['data'];
										}
									} else {
										$response['status']  = 'error';
										$response['message'] = $track_data['data'];
									}
								}
							} else {
								$status = $tracking_from_db['status'];
							}
							if ( $need_update_tracking_table && $order_id != $tracking_from_db['order_id'] || $service_carrier_type !== $tracking_from_db['carrier_service'] ) {
								VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update( $tracking_from_db['id'], $order_id, $carrier_slug, $service_carrier_type );
							}
						}

						break;
					default:
				}
				self::update_order_items_tracking_status( $tracking_number, $carrier_slug, $status, $change_order_status );
				if ( $status ) {
					$convert_status                       = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::convert_status( $status );
					$response['message_content']          .= '<div>' . VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_status_text( $convert_status ) . '</div>';
					$response['tracking_container_class'] = self::set( array(
						'tracking-number-container',
						'tracking-number-container-' . $convert_status
					) );
				}
				$response['tracking_status'] = $convert_status;
			} else {
				$response['status']  = 'error';
				$response['message'] = esc_html__( 'Carrier not found', 'woocommerce-orders-tracking' );
			}
		} else {
			$response['status']  = 'error';
			$response['message'] = esc_html__( 'Not available', 'woocommerce-orders-tracking' );
		}
		wp_send_json( $response );
	}

	public function restrict_manage_posts() {
		global $typenow;
		if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ), true ) ) {
			?>
            <input type="text" name="woo_orders_tracking_search_tracking"
                   placeholder="<?php echo esc_attr__( 'Search tracking number', 'woocommerce-orders-tracking' ) ?>"
                   autocomplete="off"
                   value="<?php echo isset( $_GET['woo_orders_tracking_search_tracking'] ) ? esc_attr( htmlentities( sanitize_text_field( $_GET['woo_orders_tracking_search_tracking'] ) ) ) : '' ?>">
			<?php
		}
	}

	public function posts_join( $join, $wp_query ) {
		global $wpdb;
		$join .= " JOIN {$wpdb->prefix}woocommerce_order_items as wotg_woocommerce_order_items ON $wpdb->posts.ID=wotg_woocommerce_order_items.order_id";
		$join .= " JOIN {$wpdb->prefix}woocommerce_order_itemmeta as wotg_woocommerce_order_itemmeta ON wotg_woocommerce_order_items.order_item_id=wotg_woocommerce_order_itemmeta.order_item_id";

		return $join;
	}

	public function posts_where( $where, $wp_query ) {
		global $wpdb;
		$tracking_code = isset( $_GET['woo_orders_tracking_search_tracking'] ) ? $_GET['woo_orders_tracking_search_tracking'] : '';
		if ( isset( $_GET['filter_action'] ) && 'Filter' == $_GET['filter_action'] && $tracking_code ) {
			$where .= $wpdb->prepare( " AND wotg_woocommerce_order_itemmeta.meta_key='_vi_wot_order_item_tracking_data' AND wotg_woocommerce_order_itemmeta.meta_value like %s", '%' . $wpdb->esc_like( $tracking_code ) . '%' );
			add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
			add_filter( 'posts_distinct', array( $this, 'posts_distinct' ), 10, 2 );
		}

		return $where;
	}

	public function posts_distinct( $join, $wp_query ) {
		return 'DISTINCT';
	}
}