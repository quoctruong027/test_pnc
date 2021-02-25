<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST' ) ) {
	class VI_WOOCOMMERCE_ORDERS_TRACKING_EASYPOST {
		protected $settings;
		protected $service_carrier_api_key;

		public function __construct( $service_carrier_api_key ) {
			$this->service_carrier_api_key = $service_carrier_api_key;
		}

		public function get_authorization_header() {
			return 'Basic ' . base64_encode( $this->service_carrier_api_key );
		}

		/**
		 * @param $tracking_number
		 * @param $carrier
		 *
		 * @return array
		 */
		public function create( $tracking_number, $carrier ) {
			$response = array(
				'status'            => 'error',
				'est_delivery_date' => '',
				'code'              => '',
				'data'              => esc_html__( 'Can not create tracker', 'woocommerce-orders-tracking' ),
			);
			if ( $this->service_carrier_api_key ) {
				$url              = 'https://api.easypost.com/v2/trackers';
				$args             = array(
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Authorization' => $this->get_authorization_header(),
					),
					'body'    => json_encode( array(
						'tracker' => array(
							'tracking_code' => $tracking_number,
							'carrier'       => $carrier
						)
					) ),
				);
				$request_data     = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_post( $url, $args );
				$response['code'] = $request_data['code'];
				if ( $request_data['status'] === 'success' ) {
					$data = vi_wot_json_decode( $request_data['data'] );
					if ( $response['code'] === 200 || $response['code'] === 201 ) {
						$response['status']            = 'success';
						$response['est_delivery_date'] = $data['est_delivery_date'];
						$response['data']              = self::get_track_info( $data['tracking_details'] );
					} elseif ( isset( $data['error'] ) && isset( $data['error']['message'] ) ) {
						$response['data'] = $data['error']['message'];
					}
				} else {
					$response['data'] = $request_data['data'];
				}
			} else {
				$response['data'] = esc_html__( 'Empty API', 'woocommerce-orders-tracking' );
			}

			return $response;
		}

		/**
		 * @param $tracking_number
		 *
		 * @return array
		 */
		public function retrieve( $tracking_number ) {
			$response         = array(
				'status'            => 'error',
				'est_delivery_date' => '',
				'code'              => '',
				'data'              => esc_html__( 'Tracking not found', 'woocommerce-orders-tracking' ),
			);
			$url              = 'https://api.easypost.com/v2/trackers/' . $tracking_number;
			$args             = array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => $this->get_authorization_header(),
				),
			);
			$request_data     = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_get( $url, $args );
			$response['code'] = $request_data['code'];
			if ( $request_data['status'] === 'success' ) {
				$data = vi_wot_json_decode( $request_data['data'] );
				if ( $response['code'] === 200 || $response['code'] === 201 ) {
					$response['status']            = 'success';
					$response['est_delivery_date'] = $data['est_delivery_date'];
					$response['data']              = self::get_track_info( $data['tracking_details'] );
				} elseif ( isset( $data['error'] ) && isset( $data['error']['message'] ) ) {
					$response['data'] = $data['error']['message'];
				}
			} else {
				$response['data'] = $request_data['data'];
			}

			return $response;
		}

		/**Return orders: latest to oldest by created date
		 * Use before_id=last_tracking_id if $response['has_more'] is true to query next page
		 *
		 * @param string $carrier
		 * @param string $tracking_number
		 * @param string $after_id
		 * @param string $before_id
		 * @param string $start_datetime
		 * @param string $end_datetime
		 * @param int $page_size max 100
		 *
		 * @return array
		 */
		public function retrieve_multiple( $carrier = '', $tracking_number = '', $after_id = '', $before_id = '', $start_datetime = '', $end_datetime = '', $page_size = 100 ) {
			$end_datetime     = $end_datetime ? strtotime( $end_datetime ) : time();
			$start_datetime   = $start_datetime ? strtotime( $start_datetime ) : strtotime( '-30 days' );
			$response         = array(
				'status'            => 'error',
				'est_delivery_date' => '',
				'code'              => '',
				'has_more'          => '',
				'data'              => esc_html__( 'Tracking not found', 'woocommerce-orders-tracking' ),
			);
			$query_args       = array(
				'tracking_code'  => $tracking_number,
				'page_size'      => $page_size,
				'carrier'        => $carrier,
				'after_id'       => $after_id,
				'before_id'      => $before_id,
				'start_datetime' => date( 'Y-m-d\TH:i:s\Z', $start_datetime ),
				'end_datetime'   => date( 'Y-m-d\TH:i:s\Z', $end_datetime ),
			);
			$url              = add_query_arg( $query_args, 'https://api.easypost.com/v2/trackers/' );
			$args             = array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => $this->get_authorization_header(),
				),
			);
			$request_data     = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_get( $url, $args );
			$response['code'] = $request_data['code'];
			if ( $request_data['status'] === 'success' ) {
				$data                 = vi_wot_json_decode( $request_data['data'] );
				$response['has_more'] = $data['has_more'];
				if ( $response['code'] === 200 || $response['code'] === 201 ) {
					$response['status'] = 'success';
					$response['data']   = $data['trackers'];
				} elseif ( isset( $data['error'] ) && isset( $data['error']['message'] ) ) {
					$response['data'] = $data['error']['message'];
				}
			} else {
				$response['data'] = $request_data['data'];
			}

			return $response;
		}

		/**
		 * @param $tracking_details
		 *
		 * @return array
		 */
		public static function get_track_info( $tracking_details ) {
			$track_info = array();
			foreach ( $tracking_details as $tracking_detail ) {
				if ( ! empty( $tracking_detail['datetime'] ) ) {
					$tracking_location = $tracking_detail['tracking_location'];
					/**
					 * $location Tracker
					 */
					$location_a = array();
					if ( ! empty( $tracking_location['city'] ) ) {
						$location_a[] = $tracking_location['city'];
					}
					if ( ! empty( $tracking_location['state'] ) ) {
						$location_a[] = $tracking_location['state'];
					}
					if ( ! empty( $tracking_location['country'] ) ) {
						$location_a[] = $tracking_location['country'];
					}
					$location = '';
					if ( count( $location_a ) ) {
						$location = implode( ', ', $location_a );
					}
					if ( ! empty( $tracking_location['zip'] ) ) {
						$location .= " {$tracking_location['zip']}";
					}

					$track_info[] = array(
						'time'        => $tracking_detail['datetime'],
						'description' => $tracking_detail['message'],
						'location'    => $location,
						'status'      => $tracking_detail['status'],
					);
				}
			}
			/**
			 * Sort event before saving to db as latest to oldest
			 */

			krsort( $track_info );

			return array_values( $track_info );
		}

		/**Carriers supported by EasyPost
		 * @return array
		 */
		public static function get_carriers() {
			return array(
				'AmazonMws'                  => 'AmazonMws',
				'APC'                        => 'APC',
				'Aramex'                     => 'Aramex',
				'ArrowXL'                    => 'ArrowXL',
				'Asendia'                    => 'Asendia',
				'AustraliaPost'              => 'Australia Post',
				'AxlehireV3'                 => 'AxlehireV3',
				'BorderGuru'                 => 'BorderGuru',
				'Cainiao'                    => 'Cainiao',
				'cainiao'                    => 'Aliexpress Standard Shipping',
				'CanadaPost'                 => 'Canada Post',
				'Canpar'                     => 'Canpar Courier',
				'ColumbusLastMile'           => 'CDL Last Mile Solutions',
				'Chronopost'                 => 'Chronopost France',
				'ColisPrive'                 => 'Colis Privé',
				'Colissimo'                  => 'Colissimo',
				'CouriersPlease'             => 'Couriers Please',
				'DaiPost'                    => 'Dai Post',
				'Deliv'                      => 'Deliv',
				'DeutschePost'               => 'Deutsche Post',
				'Deutschepost'               => 'Deutsche Post DHL',
				'DHLEcommerceAsia'           => 'DHL eCommerce Asia',
				'DHLExpress'                 => 'DHL Express',
				'DHLFreight'                 => 'DHL Freight',
				'DHLGermany'                 => 'DHL Germany',
				'DHLGlobalMail'              => 'DHL eCommerce',
				'DHLGlobalmailInternational' => 'DHL eCommerce International',
				'Dicom'                      => 'Dicom',
				'DirectLink'                 => 'Direct Link',
				'Doorman'                    => 'Doorman',
				'DPD'                        => 'DPD',
				'DPDUK'                      => 'DPD UK',
				'ChinaEMS'                   => 'China EMS( ePacket )',
				'Estafeta'                   => 'Estafeta',
				'Estes'                      => 'Estes',
				'Fastway'                    => 'Fastway',
				'fastway'                    => 'Fastway NZ',
				'fastWay'                    => 'Fastway AU',
				'FedEx'                      => 'FedEx',
				'FedExMailview'              => 'FedEx Mailview',
				'FedExSameDayCity'           => 'FedEx SameDay City',
				'FedExUK'                    => 'FedEx UK',
				'FedexSmartPost'             => 'FedEx SmartPost',
				'FirstMile'                  => 'FirstMile',
				'Globegistics'               => 'Globegistics',
				'GSO'                        => 'GSO',
				'Hermes'                     => 'Hermes',
				'hermes'                     => 'Hermes Germany',
				'hermeS'                     => 'myHermes UK',
				'HongKongPost'               => 'Hong Kong Post',
				'InterlinkExpress'           => 'Interlink Express',
				'JancoFreight'               => 'Janco Freight',
				'JPPost'                     => 'Japan Post',
				'KuronekoYamato'             => 'Kuroneko Yamato',
				'LaPoste'                    => 'La Poste',
				'LaserShipV2'                => 'LaserShipV2',
				'LatvijasPasts'              => 'Latvijas Pasts',
				'Liefery'                    => 'Liefery',
				'LoomisExpress'              => 'Loomis Express',
				'LSO'                        => 'LSO',
				'Network4'                   => 'Network4',
				'Newgistics'                 => 'Newgistics',
				'Norco'                      => 'Norco',
				'OnTrac'                     => 'OnTrac',
				'OnTracDirectPost'           => 'OnTrac DirectPost',
				'OrangeDS'                   => 'Orange DS',
				'OsmWorldwide'               => 'Osm Worldwide',
				'Parcelforce'                => 'Parcelforce',
				'parcelforce'                => 'Parcelforce UK',
				'Passport'                   => 'Passport',
				'Pilot'                      => 'Pilot',
				'PostNL'                     => 'PostNL',
				'postNL'                     => 'Netherlands Post( PostNL )',
				'Posten'                     => 'Posten',
				'PostNord'                   => 'PostNord',
				'postNord'                   => 'Denmark Post',
				'Purolator'                  => 'Purolator',
				'RoyalMail'                  => 'Royal Mail',
				'RRDonnelley'                => 'RR Donnelley',
				'Seko'                       => 'Seko',
				'SingaporePost'              => 'Singapore Post',
				'SpeeDee'                    => 'Spee-Dee',
				'SprintShip'                 => 'SprintShip',
				'StarTrack'                  => 'StarTrack',
				'Toll'                       => 'Toll',
				'toll'                       => 'MyToll Australia',
				'TForce'                     => 'TForce',
				'UDS'                        => 'UDS',
				'Ukrposhta'                  => 'Ukrposhta',
				'UPS'                        => 'UPS',
				'UPs'                        => 'UPS SE',
				'UpS'                        => 'UPS DE',
				'UPSIparcel'                 => 'UPS i-parcel',
				'UPSMailInnovations'         => 'UPS Mail Innovations',
				'USPS'                       => 'USPS',
				'Veho'                       => 'Veho',
				'Yanwen'                     => 'Yanwen',
				'Yodel'                      => 'Yodel'
			);
		}

		public static function get_original_carrier_slug( $slug ) {
			$slug             = strtolower( $slug );
			$get_carriers = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_carriers();
			$carriers         = self::get_carriers();
			$names            = array_column( $get_carriers, 'name' );
			$return           = array();
			foreach ( $carriers as $key => $value ) {
				if ( strtolower( $key ) === $slug ) {
					$search = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::array_search_case_insensitive( $value, $names );
					if ( $search !== false ) {
						$return[] = $get_carriers[ $search ]['slug'];
					}
				}
			}

			return $return;
		}

		public static function get_carrier_slug_by_name( $name ) {
			$carriers = self::get_carriers();
			$search   = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::array_search_case_insensitive( $name, $carriers );
			if ( ! $search ) {
				$search = $name;
			}

			return $search;
		}

		/**
		 * @param $trackings
		 * @param $carrier_id
		 * @param $service_carrier_type
		 * @param string $change_order_status
		 *
		 * @throws Exception
		 */
		public static function update_tracking_data( $trackings, $carrier_id, $service_carrier_type, $change_order_status = '' ) {
			foreach ( $trackings as $tracking ) {
				$tracking_number = $tracking['tracking_code'];
				$track_info      = self::get_track_info( $tracking['tracking_details'] );
				$last_event      = '';
				if ( $track_info ) {
					$last_event = $track_info[0]['description'];
					$track_info = json_encode( $track_info );
				} else {
					$track_info = '';
				}
				$status = $tracking['status'];
				VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::update_by_tracking_number( $tracking_number, $carrier_id, $service_carrier_type, $status, $track_info, $last_event, $tracking['est_delivery_date'] );
				if ( $status ) {
					VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_items_tracking_status( $tracking_number, $carrier_id, $status, $change_order_status );
				}
			}
		}

		public static function map_statuses( $status = '' ) {
			$statuses = array(
				'unknown'              => 'pending',
				'pre_transit'          => 'pending',
				'in_transit'           => 'transit',
				'out_for_delivery'     => 'transit',
				'available_for_pickup' => 'pickup',
				'return_to_sender'     => 'alert',
				'failure'              => 'alert',
				'cancelled'            => 'alert',
				'error'                => 'alert',
				'delivered'            => 'delivered',
			);
			if ( $status ) {
				return isset( $statuses[ $status ] ) ? $statuses[ $status ] : '';
			} else {
				return $statuses;
			}
		}
		public static function status_text() {
			return array(
				'unknown'              => esc_html__( 'Unknown', 'woocommerce-orders-tracking' ),
				'pre_transit'          => esc_html__( 'Pre Transit', 'woocommerce-orders-tracking' ),
				'in_transit'           => esc_html__( 'In Transit', 'woocommerce-orders-tracking' ),
				'out_for_delivery'     => esc_html__( 'Out For Delivery', 'woocommerce-orders-tracking' ),
				'available_for_pickup' => esc_html__( 'Available For Pickup', 'woocommerce-orders-tracking' ),
				'return_to_sender'     => esc_html__( 'Return To Sender', 'woocommerce-orders-tracking' ),
				'failure'              => esc_html__( 'Failure', 'woocommerce-orders-tracking' ),
				'cancelled'            => esc_html__( 'Cancelled', 'woocommerce-orders-tracking' ),
				'error'                => esc_html__( 'Error', 'woocommerce-orders-tracking' ),
				'delivered'            => esc_html__( 'Delivered', 'woocommerce-orders-tracking' ),
			);
		}
	}
}
