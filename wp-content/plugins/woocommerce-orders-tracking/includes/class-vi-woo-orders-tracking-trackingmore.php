<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE' ) ) {
	class VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE {
		protected $settings;
		protected $service_carrier_api_key;
		protected static $search_tracking_slugs;

		public function __construct( $service_carrier_api_key ) {
			$this->service_carrier_api_key = $service_carrier_api_key;
		}

		public function create_tracking( $tracking_number, $carrier_slug, $order_id ) {
			$return = array(
				'status'            => 'error',
				'est_delivery_date' => '',
				'code'              => '',
				'data'              => esc_html__( 'Can not create tracker', 'woocommerce-orders-tracking' ),
			);
			if ( $this->service_carrier_api_key ) {
				$url   = self::get_url( 'trackings/post' );
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$language = get_post_meta( $order_id, 'wpml_language', true );
					if ( ! $language && function_exists( 'pll_get_post_language' ) ) {
						$language = pll_get_post_language( $order_id );
					}
					$shipping_country = $order->get_shipping_country();
					$tracking         = array(
						/*required*/
						'tracking_number'  => $tracking_number,
						'carrier_code'     => $carrier_slug,
						/*optional*/
						'customer_name'    => $order->get_formatted_billing_full_name(),
						'customer_email'   => $order->get_billing_email(),
						'order_id'         => $order_id,
						'destination_code' => $shipping_country,
						'lang'             => $language ? strtolower( $language ) : 'en',
					);
					$mobile           = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::format_phone_number( $order->get_billing_phone(), $shipping_country );
					if ( $mobile ) {
						$tracking['customer_phone'] = $mobile;
					}
					$args         = array(
						'headers' => array(
							'Content-Type'         => 'application/json',
							'Trackingmore-Api-Key' => $this->service_carrier_api_key,
						),
						'body'    => json_encode( $tracking )
					);
					$request_data = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_post( $url, $args );
					if ( $request_data['status'] === 'success' ) {
						$data = vi_wot_json_decode( $request_data['data'] );
						if ( $data['meta']['code'] == 200 ) {
							$return['status'] = 'success';
							$return['data']   = $data['data'];
						} else {
							$return['code'] = $data['meta']['code'];
							$return['data'] = $data['meta']['message'];
						}
					} else {
						$return['data'] = $request_data['data'];
					}
				} else {
					$return['data'] = esc_html__( 'Order not found', 'woocommerce-orders-tracking' );
				}
			} else {
				$return['data'] = esc_html__( 'Empty API', 'woocommerce-orders-tracking' );
			}

			return $return;
		}

		/**Create multiple trackings
		 * Max 40
		 *
		 * @param $tracking_array
		 *
		 * @return array
		 */
		public function create_multiple_trackings( $tracking_array ) {
			$return = array(
				'status' => 'error',
				'code'   => '',
				'data'   => esc_html__( 'Can not create tracker', 'woocommerce-orders-tracking' ),
			);
			if ( $this->service_carrier_api_key ) {
				$url          = self::get_url( 'trackings/batch' );
				$args         = array(
					'headers' => array(
						'Content-Type'         => 'application/json',
						'Trackingmore-Api-Key' => $this->service_carrier_api_key,
					),
					'body'    => json_encode( $tracking_array )
				);
				$request_data = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_post( $url, $args );
				if ( $request_data['status'] === 'success' ) {
					$data           = vi_wot_json_decode( $request_data['data'] );
					$return['code'] = $data['meta']['code'];
					if ( $data['meta']['code'] == 201 || $data['meta']['code'] == 200 ) {
						$return['status'] = 'success';
						$return['data']   = $data['data'];
						/*$data['data']*/
//						array(
//							'submitted' => 3,
//							'added'     => 1,
//							'trackings' => array(),
//							'errors'    => array(
//								'tracking_number' => '',
//								'code'            => '',
//								'message'         => '',
//							),
//						);
					} else {
						$return['data'] = $data['meta']['message'];
					}
				} else {
					$return['data'] = $request_data['data'];
				}
			} else {
				$return['data'] = esc_html__( 'Empty API', 'woocommerce-orders-tracking' );
			}

			return $return;
		}

		/**
		 * @param array $numbers
		 * @param array $orders
		 * @param string $created_at_min
		 * @param string $created_at_max
		 * @param string $status
		 * @param int $page
		 * @param int $limit Items per page - Max 2000
		 *
		 * @return array
		 */
		public function get_multiple_trackings( $numbers = array(), $orders = array(), $created_at_min = '', $created_at_max = '', $status = '', $page = 1, $limit = 2000 ) {
			$return = array(
				'status'            => 'error',
				'est_delivery_date' => '',
				'code'              => '',
				'data'              => esc_html__( 'Can not create tracker', 'woocommerce-orders-tracking' ),
			);
			if ( $this->service_carrier_api_key ) {
				$query_args = array(
					'numbers' => implode( ',', $numbers ),
					'orders'  => implode( ',', $orders ),
					'page'    => $page,
					'limit'   => $limit,
					'status'  => $status,
				);
				if ( $created_at_min ) {
					$query_args['created_at_min'] = strtotime( $created_at_min );
				}
				if ( $created_at_max ) {
					$query_args['created_at_max'] = strtotime( $created_at_max );
				}
				$url          = add_query_arg( $query_args, self::get_url( 'trackings/get' ) );
				$args         = array(
					'headers' => array(
						'Content-Type'         => 'application/json',
						'Trackingmore-Api-Key' => $this->service_carrier_api_key,
					),
				);
				$request_data = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_get( $url, $args );
				if ( $request_data['status'] === 'success' ) {
					$data           = vi_wot_json_decode( $request_data['data'] );
					$return['code'] = $data['meta']['code'];
					if ( $data['meta']['code'] == 200 ) {
						$return['status'] = 'success';
						$return['data']   = $data['data'];
					} else {
						$return['data'] = $data['meta']['message'];
					}
				} else {
					$return['data'] = $request_data['data'];
				}
			} else {
				$return['data'] = esc_html__( 'Empty API', 'woocommerce-orders-tracking' );
			}

			return $return;
		}

		public static function get_url( $rout ) {
			return "https://api.trackingmore.com/v2/{$rout}";
		}

		public function get_tracking( $tracking_number, $carrier_slug ) {
			$response     = array(
				'status'              => 'error',
				'est_delivery_date'   => '',
				'origin_country'      => '',
				'destination_country' => '',
				'data'                => esc_html__( 'Tracking not found', 'woocommerce-orders-tracking' ),
				'code'                => '',
			);
			$url          = self::get_url( "trackings/{$carrier_slug}/{$tracking_number}" );
			$args         = array(
				'headers' => array(
					'Content-Type'         => 'application/json',
					'Trackingmore-Api-Key' => $this->service_carrier_api_key,
				),
			);
			$request_data = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_get( $url, $args );
			if ( $request_data['status'] === 'success' ) {
				$data             = vi_wot_json_decode( $request_data['data'] );
				$response['code'] = $data['meta']['code'];
				if ( $data['meta']['code'] == 200 ) {
					$response['status'] = 'success';
					if ( ! empty( $data['data']['original_country'] ) ) {
						$response['origin_country'] = $data['data']['original_country'];
					}
					if ( ! empty( $data['data']['destination_country'] ) ) {
						$response['destination_country'] = $data['data']['destination_country'];
					}
					$response['data'] = self::process_trackinfo( $data['data'] );
				} else {
					$response['data'] = $data['meta']['message'];
				}
			} else {
				$response['data'] = $request_data['data'];
			}

			return $response;
		}

		/**Search for a tracking number in TrackingMore db, add it to API if not exist
		 *
		 * @param $tracking_number
		 * @param $carrier_slug
		 *
		 * @return array
		 */
		public function post_realtime_tracking( $tracking_number, $carrier_slug ) {
			$response     = array(
				'status'              => 'error',
				'est_delivery_date'   => '',
				'origin_country'      => '',
				'destination_country' => '',
				'data'                => esc_html__( 'Tracking not found', 'woocommerce-orders-tracking' ),
				'code'                => '',
			);
			$url          = self::get_url( 'trackings/realtime' );
			$tracking     = array(
				/*required*/
				'tracking_number' => $tracking_number,
				'carrier_code'    => $carrier_slug,
				'lang'            => 'en',
			);
			$args         = array(
				'headers' => array(
					'Content-Type'         => 'application/json',
					'Trackingmore-Api-Key' => $this->service_carrier_api_key,
				),
				'body'    => json_encode( $tracking )
			);
			$request_data = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_post( $url, $args );
			if ( $request_data['status'] === 'success' ) {
				$data             = vi_wot_json_decode( $request_data['data'] );
				$response['code'] = $data['meta']['code'];
				if ( $data['meta']['code'] == 200 ) {
					$response['status'] = 'success';
					$response['data']   = self::process_trackinfo( $data['data'] );
				} else {
					$response['data'] = $data['meta']['message'];
				}
			} else {
				$response['data'] = $request_data['data'];
			}

			return $response;
		}

		public static function process_trackinfo( $data ) {
			$tracking = array();
			if ( isset( $data['destination_info'] ) && ! empty( $data['destination_info']['trackinfo'] ) ) {
				$trackinfo = $data['destination_info']['trackinfo'];
				foreach ( $trackinfo as $event ) {
					$tracking[] = array(
						'time'        => $event['Date'],
						'description' => $event['StatusDescription'],
						'location'    => $event['Details'],
						'status'      => $event['checkpoint_status'],
					);
				}
			} elseif ( isset( $data['origin_info'] ) && ! empty( $data['origin_info']['trackinfo'] ) ) {
				$trackinfo = $data['origin_info']['trackinfo'];
				foreach ( $trackinfo as $event ) {
					$tracking[] = array(
						'time'        => $event['Date'],
						'description' => $event['StatusDescription'],
						'location'    => $event['Details'],
						'status'      => $event['checkpoint_status'],
					);
				}
			}

			return $tracking;
		}

		public function get_carriers() {
			$response = array(
				'status'            => 'error',
				'est_delivery_date' => '',
				'code'              => '',
				'data'              => esc_html__( 'Empty API key', 'woocommerce-orders-tracking' ),
			);
			if ( $this->service_carrier_api_key ) {
				$url          = self::get_url( 'carriers' );
				$args         = array(
					'headers' => array(
						'Content-Type'         => 'application/json',
						'Trackingmore-Api-Key' => $this->service_carrier_api_key,
					),
				);
				$request_data = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::wp_remote_get( $url, $args );
				if ( $request_data['status'] === 'success' ) {
					$data             = vi_wot_json_decode( $request_data['data'] );
					$response['code'] = $data['meta']['code'];
					if ( $data['meta']['code'] == 200 ) {
						$response['status'] = 'success';
						$response['data']   = $data['data'];
					} else {
						$response['data'] = $data['meta']['message'];
					}
				} else {
					$response['data'] = $request_data['data'];
				}
			}

			return $response;
		}

		public static function carriers() {
			return array(
				'dhl'                         => 'DHL Express',
				'ups'                         => 'UPS',
				'fedex'                       => 'Fedex',
				'tnt'                         => 'TNT',
				'china-ems'                   => 'China EMS',
				'china-post'                  => 'China Post',
				'singapore-post'              => 'Singapore Post',
				'singapore-speedpost'         => 'Singapore Speedpost',
				'hong-kong-post'              => 'Hong Kong Post',
				'swiss-post'                  => 'Swiss Post',
				'usps'                        => 'USPS',
				'parcel-force'                => 'Parcel Force',
				'postnl-parcels'              => 'Netherlands Post - PostNL',
				'netherlands-post'            => 'Netherlands Post',
				'australia-post'              => 'Australia Post',
				'australia-ems'               => 'Australia EMS',
				'canada-post'                 => 'Canada Post',
				'new-zealand-post'            => 'New Zealand Post',
				'belgium-post'                => 'Bpost',
				'brazil-correios'             => 'Brazil Correios',
				'russian-post'                => 'Russian Post',
				'sweden-posten'               => 'Sweden Posten',
				'laposte'                     => 'La Poste',
				'chronopost'                  => 'France EMS - Chronopost',
				'colissimo'                   => 'Colissimo',
				'poste-italiane'              => 'Poste Italiane',
				'india-post'                  => 'India Post',
				'magyar-posta'                => 'Magyar Posta',
				'yanwen'                      => 'YANWEN',
				'dhl-germany'                 => 'Deutsche Post DHL',
				'an-post'                     => 'An Post',
				'dhlparcel-nl'                => 'DHL Parcel Netherlands',
				'dhl-poland'                  => 'DHL Poland Domestic',
				'dhl-es'                      => 'DHL Spain Domestic',
				'correos-mexico'              => 'Mexico Post',
				'posten-norge'                => 'Posten Norge',
				'tnt-it'                      => 'TNT Italy',
				'tnt-fr'                      => 'TNT France',
				'ctt'                         => 'Portugal Post - CTT',
				'south-africa-post'           => 'South African Post Office',
				'correos-spain'               => 'Correos Spain',
				'taiwan-post'                 => 'Chunghwa POST',
				'ukraine-post'                => 'Ukraine Post',
				'ukraine-ems'                 => 'Ukraine EMS',
				'emirates-post'               => 'Emirates Post',
				'uruguay-post'                => 'Uruguay Post',
				'japan-post'                  => 'Japan Post',
				'posta-romana'                => 'Poșta Română',
				'korea-post'                  => 'Korea Post',
				'greece-post'                 => 'ELTA Hellenic Post',
				'deutsche-post'               => 'Deutsche Post',
				'czech-post'                  => 'Česká Pošta',
				'correos-chile'               => 'Correos Chile',
				'aland-post'                  => 'Åland Post',
				'macao-post'                  => 'Macao Post',
				'wishpost'                    => 'WishPost',
				'pfcexpress'                  => 'PFC Express',
				'yunexpress'                  => 'Yun Express',
				'cnexps'                      => 'CNE Express',
				'buylogic'                    => 'Buylogic',
				'4px'                         => '4PX',
				'anjun'                       => 'Anjun Logistics',
				'j-net'                       => 'J-NET Express',
				'miuson-international'        => 'Miuson Express',
				'sfb2c'                       => 'SF International',
				'sf-express'                  => 'S.F Express',
				'sto'                         => 'STO Express',
				'yto'                         => 'YTO Express',
				'ttkd'                        => 'TTKD Express',
				'jd'                          => 'JD Express',
				'zto'                         => 'ZTO Express',
				'zjs-express'                 => 'ZJS International',
				'yunda'                       => 'Yunda Express',
				'deppon'                      => 'DEPPON',
				'xqwl'                        => 'XQ Express',
				'chukou1'                     => 'Chukou1 Logistics',
				'xru'                         => 'XRU',
				'ruston'                      => 'Ruston',
				'qfkd'                        => 'QFKD Express',
				'nanjingwoyuan'               => 'Nanjing Woyuan',
				'hhexp'                       => 'Hua Han Logistics',
				'flytexpress'                 => 'Flyt Express',
				'al8856'                      => 'Ali Business Logistics',
				'jcex'                        => 'JCEX',
				'dpe-express'                 => 'DPE Express',
				'lwehk'                       => 'LWE',
				'equick-cn'                   => 'Equick China',
				'cuckooexpress'               => 'Cuckoo Express',
				'dwz'                         => 'DWZ Express',
				'takesend'                    => 'Takesend Logistics',
				'cainiao'                     => 'Aliexpress Standard Shipping',
				'tgx'                         => 'TGX',
				'1dlexpress'                  => '1DL Express',
				'imile'                       => 'iMile',
				'aus'                         => 'Ausworld Express',
				'sxexpress'                   => 'SX-Express',
				'uvan'                        => 'UVAN Express',
				'csd'                         => 'CSD Express',
				'sri-lanka-post'              => 'Sri Lanka Post',
				'ewe'                         => 'EWE Global Express',
				'sudan-post'                  => 'Sudan Post',
				'dex-i'                       => 'DEX-I',
				'nippon'                      => 'Nippon Express',
				'cosco'                       => 'COSCO eGlobal',
				'logistics'                   => 'WEL',
				'ninjavan-vn'                 => 'Ninja Van Vietnam',
				'speedee'                     => 'Spee-Dee Delivery',
				'syrian-post'                 => 'Syrian Post',
				'raiderex'                    => 'RaidereX',
				'allekurier'                  => 'allekurier',
				'guangchi'                    => 'GuangChi Express',
				'lpexpress'                   => 'LP Express',
				'un-line'                     => 'Un-line',
				'rzyexpress'                  => 'RZY Express',
				'transrush'                   => 'Transrush',
				'venipak'                     => 'Venipak',
				'tanzania-post'               => 'Tanzania Post',
				'ste56'                       => 'Suteng Logistics',
				'bab-ru'                      => 'BAB international',
				'thailand-post'               => 'Thailand Post',
				'airpak-express'              => 'Airpak Express',
				'winit'                       => 'Winit',
				'bdm'                         => 'BDM Corriere espresso',
				'togo-post'                   => 'Togo Post',
				'qexpress'                    => 'QEXPRESS',
				'cnilink'                     => 'CNILINK',
				'szendex'                     => 'Szendex',
				'tonga-post'                  => 'Tonga Post',
				'lbcexpress'                  => 'LBC Express',
				'360zebra'                    => '360zebra',
				'spoton'                      => 'Spoton Logistics',
				'tcat-cn'                     => 'Smartcat',
				'chinz56'                     => 'Chinz Logistics',
				'hkdexpress'                  => 'HKD',
				'tunisia-post'                => 'Tunisia Post',
				'pandulogistics'              => 'Pandu Logistics',
				'auexpress'                   => 'Auexpress',
				'dachser'                     => 'Dachser',
				'pitneybowes'                 => 'Pitney Bowes',
				'turkey-post'                 => 'Turkey Post',
				'dpd-hk'                      => 'DPD(HK)',
				'fedex-uk'                    => 'FedEx UK',
				'topyou'                      => 'TopYou',
				'dpd-be'                      => 'DPD Belgium',
				'uganda-post'                 => 'Uganda Post',
				'collectplus'                 => 'Collect+',
				'ztky'                        => 'Zhongtie Logistics',
				'jdpplus'                     => 'Jdpplus',
				'sap-express'                 => 'SAP Express',
				'tnt-click'                   => 'TNT Click',
				'skynetworldwide-uk'          => 'Skynet Worldwide Express UK',
				'idada56'                     => 'Dada logistic',
				'showl'                       => 'Showl',
				'v-xpress'                    => 'V-Xpress',
				'rufengda'                    => 'Rufengda',
				'hermes'                      => 'Hermesworld',
				'jersey-post'                 => 'Jersey Post',
				'sendle'                      => 'Sendle',
				'gdwse'                       => 'WSE Logistics',
				'dhl-uk'                      => 'DHL Parcel UK',
				'nightline'                   => 'Nightline',
				'bombino-express'             => 'Bombino Express',
				'kingruns'                    => 'KINGRUNS',
				'tarrive'                     => 'TONGDA Global',
				'mailamericas'                => 'MailAmericas',
				'ninjavan-my'                 => 'Ninja Van Malaysia',
				'szyn'                        => 'YingNuo Supply Chain',
				'uzbekistan-post'             => 'Uzbekistan Post',
				'apc'                         => 'APC Postal Logistics',
				'lexship'                     => 'Lexship',
				'far800'                      => 'Far International Logistics',
				'whistl'                      => 'Whistl',
				'vanuatu-post'                => 'Vanuatu Post',
				'newgistics'                  => 'Newgistics',
				'dhl-parcel-nl'               => 'DHL Netherlands',
				'ninjaxpress'                 => 'Ninja Van Indonesia',
				'organeds'                    => 'OrangeDS',
				'dxdelivery'                  => 'DX Delivery',
				'redpack-mexico'              => 'Redpack Mexico',
				'old-dominion'                => 'Old Dominion Freight Line',
				'roadbull'                    => 'Roadbull Logistics',
				'ninjavan-ph'                 => 'Ninja Van Philippines',
				'dpd-brazil'                  => 'Jadlog Brazil',
				'vietnam-post'                => 'Vietnam Post',
				'dhl-benelux'                 => 'DHL Benelux',
				'huilogistics'                => 'Hui Logistics',
				'estes'                       => 'Estes',
				'ninjavan-th'                 => 'Ninja Van Thailand',
				'pjbest'                      => 'Pinjun Express',
				'changjiangexpress'           => 'changjiangexpress',
				'yemen-post'                  => 'Yemen Post',
				'superoz'                     => 'SuperOZ Logistics',
				'zambia-post'                 => 'Zambia Post',
				'saicheng'                    => 'Sai Cheng Logistics',
				'leopardschina'               => 'Leopards Express',
				'cse'                         => 'cse',
				'zimbabwe-post'               => 'Zimbabwe Post',
				'beebird'                     => 'Beebird Logistics',
				'greyhound'                   => 'Greyhound',
				'8europe'                     => '8Europe',
				'firstmile'                   => 'FirstMile',
				'overseas-logistics'          => 'Overseas Logistics',
				'royal-shipments'             => 'Royal Shipments',
				'globegistics'                => 'Globegistics Inc.',
				'dellin'                      => 'Dellin',
				'cbtsd'                       => 'Better Express',
				'suyd56'                      => 'SYD Express',
				'bluecare'                    => 'Bluecare Express',
				'nexive'                      => 'Nexive',
				'tk-kit'                      => 'Tk Kit',
				'asendia-hk'                  => 'Asendia HK',
				'linexsolutions'              => 'Linex',
				'intelcom'                    => 'intelcom',
				'overnitenet'                 => 'Overnite Express',
				'airwings-india'              => 'Airwings Courier Express India',
				'hermes-de'                   => 'Hermes Germany',
				'jt-express-th'               => 'J&T Express TH',
				'professional-couriers'       => 'The Professional Couriers (TPC)',
				'esnad'                       => 'ESNAD Express',
				'international-seur'          => 'International Seur',
				'abf'                         => 'ABF Freight',
				'asmred'                      => 'ASM',
				'trakpak'                     => 'TrakPak',
				'xingyunyi'                   => 'XingYunYi',
				'tnt-lt'                      => 'TNT LT',
				'matkahuolto'                 => 'Matkahuolto',
				'mondialrelay'                => 'Mondial Relay',
				'couriers-please'             => 'Couriers Please express',
				'acscourier'                  => 'ACS Courier',
				'bt-exp'                      => 'LJS',
				'dhl-fr'                      => 'dhl-fr',
				'rl-carriers'                 => 'RL Carriers',
				'afghan-post'                 => 'Afghan Post',
				'dpd-poland'                  => 'DPD Poland',
				'asiafly'                     => 'AsiaFly',
				'gls-pl'                      => 'gls-pl',
				'posta-shqiptare'             => 'Albania Post',
				'taxydromiki'                 => 'Geniki Taxydromiki',
				'cess'                        => 'Cess',
				'hanjin'                      => 'Hanjin Shipping',
				'postaplus'                   => 'PostaPlus',
				'andorra-post'                => 'Andorra Post',
				'envialia'                    => 'Envialia',
				'bestex'                      => 'Best Express',
				'1shida'                      => '1SD',
				'canpar'                      => 'Canpar Courier',
				'adicional'                   => 'Adicional Logistics',
				'njfeibao'                    => 'Flying Leopards Express',
				'17postservice'               => '17 Post Service',
				'cbl-logistica'               => 'CBL Logistics',
				'firstflightme'               => 'First Flight Couriers',
				'jet'                         => 'JET',
				'redur-es'                    => 'Redur Spain',
				'antilles-post'               => 'Antilles Post',
				'correo-argentino'            => 'Argentina Post',
				'siodemka'                    => 'Siodemka',
				'360lion'                     => '360lion Express',
				'armenia-post'                => 'Armenia Post',
				'aruba-post'                  => 'Aruba Post',
				'exapaq'                      => 'DPD France',
				'hct'                         => 'HCT Express',
				'ldxpress'                    => 'LDXpress',
				'austria-post'                => 'Austrian Post',
				'azerbaijan-post'             => 'Azerbaijan Post',
				'doortodoor'                  => 'CJ Logistics',
				'bahrain-post'                => 'Bahrain Post',
				'kuajingyihao'                => 'K1 Express',
				'bangladesh-ems'              => 'Bangladesh EMS',
				'qi-eleven'                   => '7-ELEVEN',
				'barbados-post'               => 'Barbados Post',
				'belpochta'                   => 'Belarus Post',
				'orangeconnex'                => 'ORANGE CONNEX',
				'belize-post'                 => 'Belize Post',
				'007ex'                       => '007EX',
				'benin-post'                  => 'Benin Post',
				'bermuda-post'                => 'Bermuda Post',
				'js-exp'                      => 'JS EXPRESS',
				'bhutan-post'                 => 'Bhutan Post',
				'correos-bolivia'             => 'Bolivia Post',
				'gofly'                       => 'Gofly',
				'bosnia-and-herzegovina-post' => 'Bosnia And Herzegovina Post',
				'gaopost'                     => 'Gao Post',
				'botswana-post'               => 'Botswana Post',
				'brunei-post'                 => 'Brunei Post',
				'dbschenker'                  => 'DB Schenker',
				'bulgaria-post'               => 'Bulgaria Post',
				'sonapost'                    => 'Burkina Faso Post',
				'ukmail'                      => 'UK Mail',
				'burundi-post'                => 'Burundi Post',
				'sinoair'                     => 'SINOAIR',
				'cambodia-post'               => 'Cambodia Post',
				'detrack'                     => 'Detrack',
				'campost'                     => 'Cameroon Post',
				'italy-sda'                   => 'Italy SDA',
				'correios-cabo-verde'         => 'Correios Cabo Verde',
				'palletways'                  => 'Palletways',
				't-cat'                       => 'T Cat',
				'yht'                         => 'Eshipping',
				'fastgo'                      => 'Fastgo',
				'colombia-post'               => 'Colombia Post',
				'delcart-in'                  => 'Delcart',
				'pca'                         => 'PCA',
				'fulfillmen'                  => 'Fulfillmen',
				'citylinkexpress'             => 'City-Link Express',
				'ftd'                         => 'FTD Express',
				'tuffnells'                   => 'tuffnells',
				'2go'                         => '2GO',
				'correos-de-costa-rica'       => 'Costa Rica Post',
				'shipgce'                     => 'Shipgce Express',
				'freakyquick'                 => 'freaky quick logistics',
				'turtle-express'              => 'Turtle express',
				'hrvatska-posta'              => 'Croatia Post',
				'xend'                        => 'Xend Express',
				'cuba-post'                   => 'Cuba Post',
				'wise-express'                => 'Wise Express',
				'sure56'                      => 'Sure56',
				'ceva-logistics'              => 'CEVA Logistics',
				'cyprus-post'                 => 'Cyprus Post',
				'air21'                       => 'AIR21',
				'gaticn'                      => 'GATI Courier',
				'cnpex'                       => 'Cnpex',
				'ubx-uk'                      => 'UBX Express',
				'denmark-post'                => 'Denmark post',
				'airspeed'                    => 'Airspeed International Corporation',
				'szdpex'                      => 'DPEX China',
				'1hcang'                      => '1hcang',
				'shree-mahabali-express'      => 'Shree Mahabali Express',
				'raf'                         => 'RAF Philippines',
				'tea-post'                    => 'Tea post',
				'ydhex'                       => 'YDH',
				'tiki'                        => 'Tiki',
				'correos-del-ecuador'         => 'Ecuador Post',
				'sunyou'                      => 'Sunyou',
				'quickway'                    => 'QuicKway',
				'egypt-post'                  => 'Egypt Post',
				'wahana'                      => 'Wahana',
				'el-salvador-post'            => 'El Salvador Post',
				'dhlecommerce-asia'           => 'DHL Global Mail Asia',
				'ghn'                         => 'Giao Hàng Nhanh',
				'eritrea-post'                => 'Eritrea Post',
				'dhl-active'                  => 'DHL Active Tracing',
				'omniva'                      => 'Estonia Post',
				'viettelpost'                 => 'Viettel Post',
				'ethiopia-post'               => 'Ethiopia Post',
				'tnt-reference'               => 'TNT Reference',
				'yunlu'                       => 'YL express',
				'faroe-islands-post'          => 'Faroe Islands Post',
				'dotzot'                      => 'Dotzot',
				'fiji-post'                   => 'Fiji Post',
				'kangaroo-my'                 => 'Kangaroo Worldwide Express',
				'finland-posti'               => 'Finland Post - Posti',
				'jiayi56'                     => 'Jiayi Express',
				'kerryexpress'                => 'Kerry Express',
				'deltec-courier'              => 'Deltec Courier',
				'gel-express'                 => 'GEL Express',
				'maxcellents'                 => 'Maxcellents Pte Ltd',
				'nationwide-my'               => 'Nationwide Express',
				'buffaloex'                   => 'Buffalo',
				'georgian-post'               => 'Georgia Post',
				'rpxonline'                   => 'RPX Online',
				'scorejp'                     => 'Score Jp',
				'ghana-post'                  => 'Ghana Post',
				'gibraltar-post'              => 'Gibraltar  Post',
				'nhans-solutions'             => 'Nhans Solutions',
				'ldlog'                       => 'LD Logistics',
				'espeedpost'                  => 'Espeedpost',
				'tele-post'                   => 'Greenland Post',
				'jet-ship'                    => 'Jet-Ship Worldwide',
				'xpost'                       => 'XPOST',
				'myaustrianpost'              => 'GmbH',
				'elcorreo'                    => 'Guatemala Post',
				'ecargo-asia'                 => 'Ecargo',
				'guernsey-post'               => 'Guernsey Post',
				'delhivery'                   => 'Delhivery',
				'cosex'                       => 'Cosex',
				'gls'                         => 'GLS',
				'bartolini'                   => 'BRT Bartolini',
				'nuvoex'                      => 'NuvoEx',
				'ets-express'                 => 'ETS Express',
				'ht56'                        => 'Hong Tai',
				'dpd'                         => 'DPD',
				'parcelled-in'                => 'Parcelled.in',
				'elianpost'                   => 'E-lian',
				'aramex'                      => 'Aramex',
				'toll'                        => 'TOLL',
				'ecom-express'                => 'Ecom Express',
				'quantium'                    => 'Quantium',
				'btd56'                       => 'Bao Tongda Freight Forwarding',
				'iceland-post'                => 'Iceland Post',
				'gdex'                        => 'GDEX',
				'alpha-fast'                  => 'Alpha Fast',
				'shreemaruticourier'          => 'Shree Maruti Courier',
				'omniparcel'                  => 'Omni Parcel',
				'indonesia-post'              => 'Indonesia Post',
				'skynet'                      => 'SkyNet Malaysia',
				'cdek'                        => 'CDEK Express',
				'bqc'                         => 'BQC',
				'iran-post'                   => 'Iran Post',
				'trackon'                     => 'Trackon Courier',
				'hnfywl'                      => 'Hnfywl',
				'israel-post'                 => 'Israel Post',
				'postnl-3s'                   => 'PostNL International 3S',
				'ocschina'                    => 'OCS Express',
				'kerry-tec'                   => 'Kerry Tec',
				'ivory-coast-ems'             => 'Ivory Coast EMS',
				'naqel'                       => 'Naqel',
				'8256ru'                      => 'BEL',
				'jamaica-post'                => 'Jamaica Post',
				'parcel'                      => 'Pitney Bowes',
				'cxc'                         => 'CXC',
				'jordan-post'                 => 'Jordan Post',
				'oneworldexpress'             => 'One World Express',
				'kazpost'                     => 'Kazakhstan Post',
				'adsone'                      => 'ADSOne',
				'bsi'                         => 'BSI express',
				'amazon'                      => 'Amazon Logistics',
				'kenya-post'                  => 'Kenya Post',
				'landmark-global'             => 'Landmark Global',
				'airfex'                      => 'Airfex',
				'thecourierguy'               => 'The Courier Guy',
				'kke'                         => 'King Kong Express',
				'smsa-express'                => 'SMSA Express',
				'800best'                     => 'Best Express(logistic)',
				'costmeticsnow'               => 'Cosmetics Now',
				'kyrgyzpost'                  => 'Kyrgyzstan Post',
				'sfcservice'                  => 'SFC Service',
				'laos-post'                   => 'Laos Post',
				'latvijas-pasts'              => 'Latvia Post',
				'ec-firstclass'               => 'EC-Firstclass',
				'yji'                         => 'YJI',
				'liban-post'                  => 'Lebanon Post',
				'hermes-uk'                   => 'MyHermes UK',
				'hi-life'                     => 'Hi Life',
				'lesotho-post'                => 'Lesotho Post',
				'dhlglobalmail'               => 'DHL ECommerce',
				'wedo'                        => 'WeDo Logistics',
				'speedpak'                    => 'SpeedPAK',
				'dpd-uk'                      => 'DPD UK',
				'tnt-uk'                      => 'TNT UK',
				'zajil'                       => 'Zajil',
				'liechtenstein-post'          => 'Liechtenstein Post',
				'ltexp'                       => 'Ltexp',
				'lietuvos-pastas'             => 'Lithuania Post',
				'lgs'                         => 'Lazada (LEX)',
				'cj-dropshipping'             => 'CJ Packet',
				'luxembourg-post'             => 'Luxembourg Post',
				'gls-italy'                   => 'GLS Italy',
				'inpost-paczkomaty'           => 'InPost Paczkomaty',
				'jayeek'                      => 'Jayeek',
				'dsv'                         => 'DSV',
				'star-track'                  => 'StarTrack',
				'dekun'                       => 'Dekun',
				'17feia'                      => '17Feia Express',
				'macedonia-post'              => 'Macedonia Post',
				'echo'                        => 'Echo',
				'blueskyexpress'              => 'Blue Sky Express',
				'dpd-ireland'                 => 'DPD Ireland',
				'empsexpress'                 => 'EMPS Express',
				'yimidida'                    => 'YMDD',
				'dhl-global-logistics'        => 'DHL Global Forwarding',
				'toll-ipec'                   => 'Toll IPEC',
				'ontrac'                      => 'OnTrac',
				'poslaju'                     => 'PosLaju',
				'malaysia-post'               => 'Malaysia Post',
				'asendia-usa'                 => 'Asendia USA',
				'cpacket'                     => 'CPacket',
				'ecpost'                      => 'ECPOST',
				'aprche'                      => 'Aprche',
				'maldives-post'               => 'Maldives Post',
				'asendia-uk'                  => 'Asendia UK',
				'cacesapostal'                => 'Cacesa Postal',
				'taqbin-my'                   => 'TAQBIN Malaysia',
				'yodel'                       => 'Yodel',
				'cre'                         => 'CRE',
				'xingyuan'                    => 'Xing Yuan',
				'malta-post'                  => 'Malta Post',
				'asendia-de'                  => 'Asendia Germany',
				'ekart'                       => 'Ekart',
				'bondscouriers'               => 'Bonds Couriers',
				'famiport'                    => 'Famiport',
				'ymy'                         => 'Yong Man Yi',
				'kerry-logistics'             => 'Kerry Express',
				'shree-tirupati'              => 'Shree Tirupati Courier',
				'mauritius-post'              => 'Mauritius Post',
				'courierpost'                 => 'CourierPost',
				'e-can'                       => 'Taiwan Pelican Express',
				'safexpress'                  => 'Safexpress',
				'meest'                       => 'Meest Express',
				'moldova-post'                => 'Moldova Post',
				'purolator'                   => 'Purolator',
				'acommerce'                   => 'ACOMMERCE',
				'pony-express'                => 'Pony Express',
				'la-poste-monaco'             => 'Monaco Post',
				'imlb2c'                      => 'IML Logistics',
				'139express'                  => '139 ECONOMIC Package',
				'hivewms'                     => 'HiveWMS',
				'monaco-ems'                  => 'Monaco EMS',
				'boxc'                        => 'Boxc Logistics',
				'mongol-post'                 => 'Mongol Post',
				'ubi-logistics'               => 'UBI Smart Parcel',
				'posta-crne-gore'             => 'Montenegro Post',
				'fastway-nz'                  => 'Fastway New Zealand',
				'ltian'                       => 'Ltian',
				'dpex'                        => 'DPEX',
				'fastway-au'                  => 'Fastway Australia',
				'directfreight-au'            => 'Direct Freight',
				'global'                      => 'Global Order (Cainiao)',
				'fastway-ie'                  => 'Fastway Ireland',
				'sjtsz'                       => 'SJTSZ Express',
				'mrw-spain'                   => 'MRW',
				'uskyexpress'                 => 'Usky',
				'namibia-post'                => 'Namibia Post',
				'com1express'                 => 'Come One express',
				'packlink'                    => 'Packlink',
				'kye'                         => 'KUAYUE EXPRESS',
				'arkexpress'                  => 'Ark express',
				'upu'                         => 'UPU',
				'arrowxl'                     => 'Arrow XL',
				'i-parcel'                    => 'I-parcel',
				'colis-prive'                 => 'Colis Privé',
				'winlink'                     => 'Winlink logistics',
				'md-express'                  => 'MC Express',
				'bluedart'                    => 'Bluedart',
				'lasership'                   => 'LaserShip',
				'kjkd'                        => 'Fast Express',
				'china-russia56'              => 'China Russia56',
				'new-caledonia-post'          => 'New Caledonia Post',
				'dtdc'                        => 'DTDC',
				'Dtdc'                        => 'DTDC IN',
				'dmm-network'                 => 'DMM Network',
				'xpresspost'                  => 'xpresspost',
				'grandslamexpress'            => 'Grand Slam Express',
				'nicaragua-post'              => 'Nicaragua Post',
				'gojavas'                     => 'GoJavas',
				'fetchr'                      => 'Fetchr',
				'opek'                        => 'FedEx Poland Domestic',
				'xdp-uk'                      => 'XDP Express',
				'efspost'                     => 'EFSPost',
				'epacket'                     => 'ePacket',
				'lbexps'                      => 'LiBang International Logistics',
				'nigeria-post'                => 'Nigeria Post',
				'first-flight'                => 'First Flight',
				'ledii'                       => 'Ledii',
				'skynetworldwide'             => 'SkyNet Worldwide Express',
				'sgt-it'                      => 'SGT Corriere Espresso',
				'eyoupost'                    => 'Eyou800',
				'etotal'                      => 'eTotal',
				'zhuozhi'                     => 'Top Ideal Express',
				'gati-kwe'                    => 'Gati-KWE',
				'imexglobalsolutions'         => 'IMEX Global Solutions',
				'oman-post'                   => 'Oman Post',
				'kgmhub'                      => 'KGM Hub',
				'easy-mail'                   => 'Easy Mail',
				'qxpress'                     => 'Qxpress',
				'idexpress'                   => 'IDEX',
				'fd-express'                  => 'FD Express',
				'dtdc-plus'                   => 'DTDC Plus',
				'hound'                       => 'hound',
				'ocs-worldwide'               => 'OCS Worldwide',
				'rosan'                       => 'ROSAN EXPRESS',
				'rrdonnelley'                 => 'RR Donnelley',
				'wsgd-logistics'              => 'WSGD Logistics',
				'parcel-express'              => 'Parcel Express',
				'con-way'                     => 'Con-way Freight',
				'fedex-freight'               => 'Fedex Freight',
				'ueq'                         => 'UEQ',
				'estafetausa'                 => 'Estafeta USA',
				'ninjavan'                    => 'Ninja Van',
				'zes-express'                 => 'ESHUN International Logistics',
				'srekorea'                    => 'SRE Korea',
				'speedexcourier'              => 'Speedex Courier',
				'fedex-ground'                => 'FedEx Ground',
				'sumxpress'                   => 'Sum Xpress',
				'artlogexpress'               => 'Art Logexpress',
				'overseas-territory-fr-ems'   => 'Overseas Territory FR EMS',
				'expeditors'                  => 'Expeditors',
				'utec'                        => 'utec',
				'taqbin-jp'                   => 'Yamato Japan',
				'spsr'                        => 'SPSR',
				'dhl-hong-kong'               => 'DHL Hong Kong',
				'eparcel-kr'                  => 'eParcel Korea',
				'deltafille'                  => 'Trending Times',
				'chronopost-portugal'         => 'Chronopost Portugal',
				'flywayex'                    => 'Flyway Express',
				'jiaji'                       => 'CNEX',
				'sagawa'                      => 'Sagawa',
				'ups-freight'                 => 'UPS Freight',
				'suning'                      => 'SUNING',
				'ddexpress'                   => 'DD Express',
				'xpressbees'                  => 'XpressBees',
				'xdexpress'                   => 'XDEXPRESS',
				'abxexpress-my'               => 'ABX Express',
				'courier-it'                  => 'Courier IT',
				'ups-ground'                  => 'UPS Ground',
				'dpd-de'                      => 'DPD Germany',
				'dpd-ro'                      => 'DPD Romania',
				'specialised-freight'         => 'Specialised Freight',
				'qichen'                      => 'venucia',
				'overseas-territory-us-post'  => 'Overseas Territory US Post',
				'mypostonline'                => 'Mypostonline',
				'ups-mi'                      => 'UPS Mail Innovations',
				'ups-mail-innovations'        => 'UPS Mail Innovations',
				'rrs'                         => 'RRS Logistics',
				'pakistan-post'               => 'Pakistan Post',
				'13-ten'                      => '13ten',
				'jam-express'                 => 'Jam Express',
				'dpe-south-africa'            => 'DPE South Africa',
				'saia-freight'                => 'Saia LTL Freight',
				'dawn-wing'                   => 'Dawn Wing',
				'138sd'                       => '138sd',
				'hxgj56'                      => 'Hanxuan international express',
				'correos-panama'              => 'Panama Post',
				'jayonexpress'                => 'Jayon Express (JEX)',
				'fastrak-services'            => 'Fastrak Services',
				'fercam'                      => 'FERCAM Logistics & Transport',
				'postpng'                     => 'Papua New Guinea Post',
				'nova-poshta'                 => 'Nova Poshta',
				'kwt56'                       => 'KWT Express',
				'auspost'                     => 'Auspost',
				'correo-paraguayo'            => 'Paraguay Post',
				'rpx'                         => 'RPX Indonesia',
				'168express'                  => 'Antron Express',
				'serpost'                     => 'Serpost',
				'phlpost'                     => 'Philippines Post',
				'sgtwl'                       => 'SGT Express',
				'correosexpress'              => 'correosexpress',
				'poczta-polska'               => 'Poland Post',
				'fbb'                         => 'FAST BEE',
				'wiseloads'                   => 'Wiseloads',
				'coe'                         => 'COE',
				'uc-express'                  => 'UC Express',
				'wndirect'                    => 'wnDirect',
				'elta-courier-gr'             => 'ELTA Courier',
				'iposita-rwanda'              => 'Rwanda Post',
				'eurodis'                     => 'Eurodis',
				'tip-sa'                      => 'TIPSA',
				'saint-lucia-post'            => 'Saint Lucia Post',
				'matdespatch'                 => 'Matdespatch',
				'lhtex'                       => 'LHT Express',
				'svgpost'                     => 'Saint Vincent And The Grenadines',
				'samoa-post'                  => 'Samoa Post',
				'tnt-au'                      => 'TNT Australia',
				'wanbexpress'                 => 'Wanb Express',
				'anserx'                      => 'AnserX',
				'san-marino-post'             => 'San Marino Post',
				'sprintpack'                  => 'SprintPack',
				'kjy'                         => 'KJY Logistics',
				'yakit'                       => 'yakit',
				'choice'                      => 'CHOICE Logistics',
				'saudi-post'                  => 'Saudi Post',
				'kawa'                        => 'Kawa',
				'jd-logistics'                => 'JD Logistics',
				'senegal-post'                => 'Senegal Post',
				'serbia-post'                 => 'Serbia Post',
				'taqbin-hk'                   => 'TAQBIN Hong Kong',
				'line-clear'                  => 'Line Clear Express & Logistics',
				'seychelles-post'             => 'Seychelles Post',
				'speed-post'                  => 'Speed Post',
				'ubonex'                      => 'UBon Express',
				'euasia'                      => 'Euasia Express',
				'oca-ar'                      => 'OCA Argentina',
				'8dt'                         => 'Profit Fields',
				'asendia'                     => 'Asendia',
				'slovakia-post'               => 'Slovakia Post',
				'slovenia-post'               => 'Slovenia Post',
				'2uex'                        => '2U Express',
				'iepost'                      => 'IEPost',
				'espost'                      => 'Espost',
				'solomon-post'                => 'Solomon Post',
				'ane66'                       => 'Ane Express',
				'alljoy'                      => 'Alljoy',
				'global-routers'              => 'Echindia',
				'huidaex'                     => 'Huida Express',
				'aplus100'                    => 'A PLUS EXPRESS'
			);
		}

		public static function get_carrier_slug_by_name( $name ) {
			$carriers = self::carriers();

			return VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::array_search_case_insensitive( $name, $carriers );
		}

		public static function get_carrier_slug_by_trackingmore_slug( $slug ) {
			if ( self::$search_tracking_slugs === null ) {
				self::$search_tracking_slugs = array();
			} elseif ( isset( self::$search_tracking_slugs[ $slug ] ) ) {
				return self::$search_tracking_slugs[ $slug ];
			}

			$get_carriers = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_carriers();
			$search           = array_search( $slug, array_column( $get_carriers, 'tracking_more_slug' ) );
			$return           = '';
			if ( $search !== false ) {
				$return = $get_carriers[ $search ]['slug'];
			} else {
				$carriers     = self::carriers();
				$carrier_name = isset( $carriers[ $slug ] ) ? $carriers[ $slug ] : '';
				$search       = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::array_search_case_insensitive( $carrier_name, array_column( $get_carriers, 'name' ) );
				if ( $search !== false ) {
					$return = $get_carriers[ $search ]['slug'];
				}
			}
			self::$search_tracking_slugs[ $slug ] = $return;

			return $return;
		}

		/**
		 * @param $trackings
		 * @param string $change_order_status
		 *
		 * @throws Exception
		 */
		public static function update_tracking_data( $trackings, $change_order_status = '' ) {
			foreach ( $trackings as $tracking ) {
				$tracking_number = $tracking['tracking_number'];
				$track_info      = self::process_trackinfo( $tracking );
				if ( $track_info ) {
					$track_info = json_encode( $track_info );
				} else {
					$track_info = '';
				}
				$last_event = $tracking['lastEvent'];
				$status     = $tracking['status'];
				$carrier_id = $tracking['carrier_code'];
				VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::update_by_tracking_number( $tracking_number, $status, self::get_carrier_slug_by_trackingmore_slug( $carrier_id ), false, false, $track_info, $last_event );
				if ( $status ) {
					VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ORDERS_TRACK_INFO::update_order_items_tracking_status( $tracking_number, $carrier_id, $status, $change_order_status );
				}
			}
		}

		public static function map_statuses( $status = '' ) {
			$statuses = array(
				'pending'     => 'pending',
				'notfound'    => 'pending',
				'pickup'      => 'pickup',
				'transit'     => 'transit',
				'delivered'   => 'delivered',
				'exception'   => 'alert',
				'expired'     => 'alert',
				'undelivered' => 'alert',
			);
			if ( $status ) {
				return isset( $statuses[ $status ] ) ? $statuses[ $status ] : '';
			} else {
				return $statuses;
			}
		}

		public static function status_text() {
			return array(
				'pending'     => esc_html__( 'Pending', 'woocommerce-orders-tracking' ),
				'notfound'    => esc_html__( 'Not Found', 'woocommerce-orders-tracking' ),
				'pickup'      => esc_html__( 'Pickup', 'woocommerce-orders-tracking' ),
				'transit'     => esc_html__( 'Transit', 'woocommerce-orders-tracking' ),
				'delivered'   => esc_html__( 'Delivered', 'woocommerce-orders-tracking' ),
				'exception'   => esc_html__( 'Exception', 'woocommerce-orders-tracking' ),
				'expired'     => esc_html__( 'Expired', 'woocommerce-orders-tracking' ),
				'undelivered' => esc_html__( 'Undelivered', 'woocommerce-orders-tracking' ),
			);
		}
	}
}
