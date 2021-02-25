<?php


if ( ! class_exists( 'Richpanel_Woo_Analytics_Integration' ) ) :

	class Richpanel_Woo_Analytics_Integration extends WC_Integration {


		private $integration_version = '2.0.0';
		private $events_queue = array();
		private $single_item_tracked = false;
		private $has_events_in_cookie = false;
		private $identify_call_data = false;
		private $woo = false;
		private $orders_per_import_chunk = 25;
		private $subscription_per_import_chunk = 25;
		private $recent_orders_sync_days = 7;
		private $batch_calls_queue = array();
		private $possible_events = array('view_product' => 'View Product', 'view_category' => 'View Category', 'view_article' => 'View Article', 'add_to_cart' => 'Add to cart', 'remove_from_cart' => 'Remove from cart', 'view_cart' => 'View Cart', 'checkout_start' => 'Started Checkout', 'identify' => 'Identify calls');
		private $endpoint_domain = 'api.richpanel.com/v2';
		public  $tracking_endpoint_domain = 'api.richpanel.com/v2';
		private $is_admin = false;
		// private $cookie_to_set = array();

		private $orders_list = array();
		private $orders_total = 0;
		private $subscription_total = 0;
		private $importing = false;
		private $s_importing = false;


		/**
		 *
		 *
		 * Initialization and hooks
		 *
		 *
		 */

		public function __construct() {
			global $woocommerce, $richpanel_woo_analytics_integration;

			$this->woo = function_exists('WC') ? WC() : $woocommerce;

			$this->id = 'richpanel-woo-analytics';
			$this->method_title = __( 'Richpanel', 'richpanel-woo-analytics' );
			$this->method_description = __( 'Richpanel offers powerful yet simple CRM & Analytics for WooCommerce and WooCommerce Subscription Stores. Enter your API key to activate analytics tracking.', 'richpanel-woo-analytics' );


			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Fetch the integration settings
			$this->api_key = $this->get_option('api_key', false);
			$this->api_secret = $this->get_option('api_secret', false);
			$this->ignore_for_roles = $this->get_option('ignore_for_roles', false);
			$this->ignore_for_events = $this->get_option('ignore_for_events', false);
			$this->product_brand_taxonomy = $this->get_option('product_brand_taxonomy', 'none');
			$this->send_roles_as_tags = $this->get_option('send_roles_as_tags', 'no');
			$this->add_tag_to_every_customer = $this->get_option('add_tag_to_every_customer', '');
			$this->prefix_order_ids = $this->get_option('prefix_order_ids', '');
			$this->http_or_https = 'https'; 
			$this->accept_tracking = true;

			// previous version compatibility - fetch token from Wordpress settings
			if (empty($this->api_key)) {
				$this->api_key = $this->get_previous_version_settings_key();
			}
			if (empty($this->api_secret)) {
				$this->api_secret = false;
			}

			// ensure correct plugin path
			$this->ensure_path();

			// add_action( 'admin_menu', array( $this, 'richpanel_admin_menu' ), 9 );

			// initiate woocommerce hooks and activities
			add_action('woocommerce_init', array($this, 'on_woocommerce_init'));
			add_action('template_redirect', array($this, 'richpanel_endpoint_handler'));

			// hook to integration settings update
			add_action( 'woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));

			// Preparing order import
			global $wpdb;
			$dateMinus12 = date('Y-m-d', strtotime('-24 Months')); // Last day 24 months ago
			$this->orders_total = (int) $wpdb->get_var($wpdb->prepare("select count(id) from `$wpdb->posts` where post_type = 'shop_order' AND post_date >= %s order by id asc", $dateMinus12));
			$this->subscription_total = (int) $wpdb->get_var($wpdb->prepare("select count(id) from `$wpdb->posts` where post_type = 'shop_subscription' AND post_date >= %s order by id asc", $dateMinus12));

		}

		public function set_importing_mode( $mode) {
			$this->importing = $mode;
		}
		public function set_simporting_mode( $mode) {
			$this->s_importing = $mode;
		}

		public function prepare_order_chunks( $orders_per_chunk = 10) {
			$this->chunk_pages_order = ceil($this->orders_total / $orders_per_chunk);
		}

		public function prepare_subscription_chunks( $subscription_per_chunk = 10) {
			$this->chunk_pages_subscription = ceil($this->subscription_total / $subscription_per_chunk);
		}

	
		/**
		 * Add menu items.
		 */
		// public function richpanel_admin_menu() {
		// 	add_menu_page(
		// 		__( 'Richpanel', 'richpanel' ),
		// 		'Richpanel',
		// 		'manage_options',
		// 		plugin_dir_path(__FILE__) . 'views/richpanel_admin_view.php',
		// 		'',
		// 		plugin_dir_url(__FILE__) . 'views/richpanel_sidebar_icon.png',
		// 		6
		// 	);
		// }

		public function addResponseHeader( $die_with_message = false) {
			if ($die_with_message) {
				header('X-Richpanel-Endpoint-Message: ' . $die_with_message);
				die();
			} else {
				header('X-Richpanel-Endpoint-Version: ' . $this->integration_version);
			}
		}

		public function richpanel_endpoint_handler() {
			global $wp_query;
			$richpanel_endpoint = $wp_query->get('richpanel_endpoint');
			$req_id = $wp_query->get('req_id');
			if ( ! $richpanel_endpoint ) {
				return;
			}
			$this->addResponseHeader();
			$this->validate_endpoint_request_id($req_id, $richpanel_endpoint);
			// $endpoint_response = array('endpoint' => $richpanel_endpoint);
			switch ($richpanel_endpoint) {
				case 'sync':
					$days_sync = $wp_query->get('recent_orders_sync_days') ? (int) $wp_query->get('recent_orders_sync_days') : $this->recent_orders_sync_days;
					$this->recent_orders_sync($days_sync);
					break;
				case 'orders':
					$order_ids = explode(',', $wp_query->get('richpanel_order_ids'));
					$this->sync_orders_chunk($order_ids);
					break;
			}

			# expire this request
			$this->expire_endpoint_request_id($req_id, $richpanel_endpoint);
			wp_send_json(array('status' => 1, 'endpoint' => $richpanel_endpoint));
		}

		public function validate_endpoint_request_id( $req_id, $endpoint) {
			if (empty($req_id)) {
				$this->addResponseHeader('No request ID specified');
			}
			$end_point_params = array('req_id' => $req_id, 'endpoint' => $endpoint, 'appClientId' => $this->api_key);
			$response = wp_remote_post($this->http_or_https . '://' . $this->endpoint_domain . '/r', array( 
			'body' => wp_json_encode($end_point_params), 
			'blocking' => true,
			'headers'   => array('Content-Type' => 'application/json; charset=utf-8') 
			));
			$response = json_decode($response['body']);
			if (1 != $response->status) {
				$this->addResponseHeader('Request ID is invalid');
			}
		}

		public function expire_endpoint_request_id( $req_id, $endpoint) {
			$end_point_params = array('req_id' => $req_id, 'endpoint' => $endpoint, 'appClientId' => $this->api_key);
			wp_remote_post($this->http_or_https . '://' . $this->endpoint_domain . '/er', array( 
			'body' => wp_json_encode($end_point_params), 
			'blocking' => true,
			'headers'   => array('Content-Type' => 'application/json; charset=utf-8')
			));
		}

		public function recent_orders_sync( $days_sync) {
			global $wpdb;
			$recent_orders = array();
			// do not accept more than 45 days
			if ($days_sync > 45) {
				$days_sync = 45;
			}

			// prepare query
			$date_after = date('Y-m-d', strtotime("-{$days_sync} days"));
			// $query = "select id from {$wpdb->posts} where (post_type = 'shop_order') && (post_date >= '{$date_after}') order by id desc";

			// fetch orders and prepare the order-status hash
			$order_ids = $wpdb->get_col($wpdb->prepare("select id from `$wpdb->posts` where (post_type = 'shop_order') && (post_date >= %s) order by id desc", $date_after));
			if (!empty($order_ids)) {
				foreach ($order_ids as $order_id) {
					try {
						$order = new WC_Order($order_id);
						if (!empty($order) && !empty($order->id)) {
							$order_id = (string) $order->id;
							$recent_orders[$order_id] = $this->get_order_status($order);
						}
					} catch (Exception $e) {
						// error_log('error recent_orders_sync');
						// error_log($e->getMessage());
						;
					}
				}
			}


			// send the order statuses to the Richpanel endpoint
			try {

				$call = array(
				'uid'       	=> 'integration',
				'appClientId'   => $this->api_key,
				'statuses' 		=> $recent_orders
				);

				// sort for salting and prepare base64
				ksort($call);
				$based_call = base64_encode(wp_json_encode($call));
				$signature = md5($based_call . $this->api_secret);

				// generate API call end point and call it
				$end_point_params = array('s' => $signature, 'hs' => $based_call, 'event_type'	=> 'recent_orders');
				wp_remote_post($this->http_or_https . '://' . $this->endpoint_domain . '/s', array( 
				'body' => wp_json_encode($end_point_params),
				'blocking' => true,
				'headers'   => array('Content-Type' => 'application/json; charset=utf-8') 
				));

			} catch (Exception $e) {
				// error_log('error recent_orders_sync1');
				// error_log($e->getMessage());
				;
			}
			return $recent_orders;
		}

		public function ensure_uid() {
			$this->rpuid = $this->session_get('rpuid');
			$this->rpdid = $this->session_get('rpdid');
			$this->rpsid = $this->session_get('rpsid');
			if (!$this->rpdid) {
				$this->rpdid = $this->wp_generate_uuid4();
				$this->session_set('rpdid', $this->rpdid, 86400*1095);
			}
			if (!$this->rpsid) {
				$this->rpsid = $this->wp_generate_uuid4();
				$this->session_set('rpsid', $this->rpsid, 3600);
			}
		}

		public function wp_generate_uuid4() {
			return md5(uniqid(wp_rand(), true)) . wp_rand();
		}

		public function on_woocommerce_init() {

			// check if I should clear the events cookie queue
			$this->check_for_richpanel_clear();

			// check if API token and Secret are both entered
			$this->check_for_keys();

			// hook to WooCommerce models
			$this->ensure_hooks();

			// process cookie events
			$this->process_cookie_events();

			// ensure identification
			$this->ensure_identify();

			// ensure session identification of visitor
			$this->ensure_uid();

		}

		public function check_for_keys() {
			if (is_admin()) {
				// check_admin_referer();
				if (( empty($this->api_key) || empty($this->api_secret) ) && empty($_POST['save'])) {
					add_action('admin_notices', array($this, 'admin_keys_notice'));
				}
				if (!empty($_POST['save']) && !empty($this->api_key) && !empty($_POST['woocommerce_richpanel-woo-analytics_api_key'])) {
					add_action('admin_notices', array($this, 'admin_import_invite'));
				}
				if (false) {
					check_admin_referer( 'RichpanelKeyMissing', 'woocommerce_richpanel-woo-analytics_api_key' );
				}
			}
		}

		public function admin_keys_notice() {
			if (empty($this->api_key) || empty($this->api_secret)) {
				$message = 'Almost done! Just enter your Richpanel API key to get started';
			}
			if (empty($this->api_secret)) {
				$message = 'Almost done! Just enter your Richpanel API key and Secret';
			}
			echo '<div class="updated" style="display: flex;">
					<img src="https://cdn.jetcommerce.io/wp-content/uploads/sites/11/2018/04/15150526/High-Res-Logo-Icon-Blue.png" style="width: 37px;" />
					<p>
						Almost done. Enter your Richpanel API keys 
						<a href="' . filter_var(admin_url('admin.php?page=richpanel-admin'), FILTER_SANITIZE_STRING) . '">here</a> 
						or get your API keys 
						<a href="https://app.richpanel.com/" target="_blank">here</a>
						.
					</p>
				</div>';
		}

		public function admin_import_invite() {
			echo '<div class="updated"><p>Awesome! Have you tried <a href="' . filter_var(admin_url('tools.php?page=richpanel-import'), FILTER_SANITIZE_STRING) . '"><strong>importing your existing customers to Richpanel</strong></a>?</p></div>';
		}

		public function ensure_hooks() {

			// general tracking snipper hook
			add_filter('wp_head', array($this, 'render_snippet'));
			add_filter('wp_head', array($this, 'woocommerce_tracking'));
			add_filter('wp_footer', array($this, 'woocommerce_footer_tracking'));

			// background events tracking
			add_action('woocommerce_add_to_cart', array($this, 'add_to_cart'), 10, 3);
			// add_action('woocommerce_before_cart_item_quantity_zero', array($this, 'remove_from_cart'), 10);
			add_action('woocommerce_remove_cart_item', array($this, 'remove_from_cart'), 10);
			// add_action('woocommerce_cart_updated', array($this, 'action_woocommerce_cart_updated'), 10, 0); 
			add_filter('woocommerce_applied_coupon', array($this, 'applied_coupon'), 10);
		
			// add_action( 'woocommerce_after_cart_item_quantity_update', 'remove_from_cart', 10, 3 );
			//add_action( 'woocommerce_cart_updated', 'action_woocommerce_cart_updated', 10, 0 ); 

			// hook on new order placed
			add_action('woocommerce_checkout_order_processed', array($this, 'new_order_event'), 10);

			// hook on WooCommerce subscriptions renewal
			add_action('woocommerce_subscriptions_renewal_order_created', array($this, 'new_subscription_order_event'), 10, 4);

			add_action('woocommerce_checkout_subscription_created', array($this, 'new_subscription_event'), 10, 3);
			add_action('woocommerce_subscription_status_updated', array($this, 'subscription_status_update_event'), 10, 3);

			// To Do: Hook woocommerce_subscription_status_updated
			// Reference: https://docs.woocommerce.com/document/subscriptions/develop/action-reference/

			// hook on WooCommerce order update
			add_action('woocommerce_order_status_changed', array($this, 'order_status_changed'), 10, 3);
			add_action('woocommerce_update_order', array($this, 'order_status_changed'), 10, 3);

			// cookie clearing actions
			add_action('wp_ajax_richpanel_chunk_sync', array($this, 'sync_orders_chunk'));
			add_action('wp_ajax_richpanel_subscriptions_sync', array($this, 'sync_subscriptions_chunk'));

			add_action('admin_menu', array($this, 'setup_admin_pages'));

			// Profile Update
			// add_action( 'profile_update', array($this, 'profile_update_event'), 10, 2 );

		}

		public function setup_admin_pages() {
			$this->is_admin = true;
			// add_submenu_page('tools.php', 'Export to Richpanel', 'Export to Richpanel', 'export', 'richpanel-import', array($this, 'richpanel_import_page'));
			// wp_enqueue_style( 'richpanel-plugin-r-icon', plugin_dir_path(__FILE__) . 'views/icons.css' );
			add_menu_page(
			__( 'Richpanel', 'richpanel' ),
			'Richpanel',
			'manage_options',
			'richpanel-admin', //plugin_dir_path(__FILE__) . 'views/richpanel_admin_view.php',
			array($this, 'richpanel_admin_page'), //''
			'data:image/svg+xml;base64,PHN2Zwp4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCnhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIgp3aWR0aD0iNDE2cHgiIGhlaWdodD0iNDE2cHgiPgo8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiICBmaWxsPSJyZ2IoMCwgNzgsIDE1MCkiCmQ9Ik0zODMuNzc5LDQxNi4wMDAgTDMyLjExNSw0MTYuMDAwIEMxNC4zNzksNDE2LjAwMCAtMC4wMDAsNDAxLjYxOCAtMC4wMDAsMzgzLjg3NiBMLTAuMDAwLDMyLjEyNCBDLTAuMDAwLDE0LjM4MiAxNC4zNzksLTAuMDAwIDMyLjExNSwtMC4wMDAgTDM4My43NzksLTAuMDAwIEM0MDEuNTE2LC0wLjAwMCA0MTUuODk1LDE0LjM4MiA0MTUuODk1LDMyLjEyNCBMNDE1Ljg5NSwzODMuODc2IEM0MTUuODk1LDQwMS42MTggNDAxLjUxNiw0MTYuMDAwIDM4My43NzksNDE2LjAwMCBaTTc0LjA5MCwyNDguNTk3IEM2Ny4wMjEsMjU0LjMwMyA1NC42NzIsMjkwLjYzNCA3OS4wMDAsMzEzLjAwMCBDMTA0LjQwOCwzMzYuMzU4IDEzNC4wMDAsMzIzLjAwMCAxMzQuMDAwLDMyMy4wMDAgQzE5Ni4zOTMsMzczLjg2MyAyNTkuMDAwLDM0MC4wMDAgMjU5LjAwMCwzNDAuMDAwIEwyNTguNTg4LDMzOC45MTMgQzI2NC4xMDIsMzM2LjY5NiAyNjguMDAwLDMzMS4zMDcgMjY4LjAwMCwzMjUuMDAwIEMyNjguMDAwLDMxNi43MTYgMjYxLjI4NCwzMTAuMDAwIDI1My4wMDAsMzEwLjAwMCBDMjUwLjQwNiwzMTAuMDAwIDI0Ny45NjYsMzEwLjY1OSAyNDUuODM4LDMxMS44MTggQzE5My43MDAsMzMxLjcwNiAxNTYuMDAwLDMwMy4wMDAgMTU2LjAwMCwzMDMuMDAwIEMxNzIuMzIwLDI2My41MTkgMTQ5LjU0MSwyNDMuNTQwIDEzOC45MjksMjM2Ljc0NiBDMTM2LjQ1MywyMzMuODQ5IDEzMi43MDEsMjMyLjAwMCAxMjguNTAwLDIzMi4wMDAgQzEyMS4wNDQsMjMyLjAwMCAxMTUuMDAwLDIzNy44MjAgMTE1LjAwMCwyNDUuMDAwIEMxMTUuMDAwLDI1MS4wMDcgMTE5LjIzNywyNTYuMDQ4IDEyNC45ODYsMjU3LjUzOSBDMTM0LjgyMSwyNjIuOTQzIDEzNy44NjQsMjc0LjA3OCAxMzYuMDAwLDI4NC4wMDAgQzEzNC4wNzQsMjk0LjI1MyAxMTguNTIyLDMwNy4yNjQgMTAxLjAwMCwyOTcuMDAwIEM4Ni43NDIsMjg4LjY0OCA5MC4zOTEsMjcyLjcwMSA5Mi4yNDUsMjY3LjA1MCBDOTUuMTQ4LDI2NC42NjUgOTcuMDAwLDI2MS4wNTAgOTcuMDAwLDI1Ny4wMDAgQzk3LjAwMCwyNDkuODIwIDkxLjE4MCwyNDQuMDAwIDg0LjAwMCwyNDQuMDAwIEM4MC4wMjYsMjQ0LjAwMCA3Ni40NzUsMjQ1Ljc4OCA3NC4wOTAsMjQ4LjU5NyBaTTI1MC43ODcsODkuNDM5IEMyNDYuMzI4LDgxLjY5OCAyMzQuMzQ0LDY2LjczOSAyMDguMDAwLDY2LjAwMCBDMTcyLjM4Nyw2NS4wMDEgMTYyLjg2OCw5Ny4yMzYgMTYxLjAwMCwxMDkuMDAwIEMxNjEuMDAwLDEwOS4wMDAgMTMzLjc0MiwxMTguMjMwIDExNS4wMDAsMTM4LjAwMCBDOTYuMjU4LDE1Ny43NzAgODcuMzY4LDE3OC4yMjMgODMuMDAwLDIwOS4wMDAgTDgzLjM0MywyMDkuMDgyIEM4My4xMjEsMjEwLjAyMyA4My4wMDAsMjEwLjk5OSA4My4wMDAsMjEyLjAwMCBDODMuMDAwLDIxOS43MzIgODkuOTQwLDIyNi4wMDAgOTguNTAwLDIyNi4wMDAgQzEwNi42ODUsMjI2LjAwMCAxMTMuMzcxLDIyMC4yNjYgMTEzLjk0NCwyMTMuMDA2IEwxMTQuMDAwLDIxMy4wMDAgQzExNC4wMDAsMjEzLjAwMCAxMTguOTc3LDE2MC44MDUgMTY3LjAwMCwxMzkuMDAwIEMxNjcuMDAwLDEzOS4wMDAgMTg5LjE0MiwxNzguMzE0IDIzNS4wMDcsMTU1Ljk5OCBDMjM1LjUxMiwxNTUuNzg3IDIzNS45OTUsMTU1LjUzOCAyMzYuNDY1LDE1NS4yNjggQzIzNi42NDQsMTU1LjE3NyAyMzYuODIwLDE1NS4wOTMgMjM3LjAwMCwxNTUuMDAwIEwyMzYuOTc3LDE1NC45NTcgQzI0MC41OTUsMTUyLjY0OCAyNDMuMDAwLDE0OC42MDkgMjQzLjAwMCwxNDQuMDAwIEMyNDMuMDAwLDEzNi44MjAgMjM3LjE4MCwxMzEuMDAwIDIzMC4wMDAsMTMxLjAwMCBDMjI2LjYyNiwxMzEuMDAwIDIyMy41NjMsMTMyLjI5NiAyMjEuMjUzLDEzNC40MDMgQzIxNi4yMTgsMTM3LjA2NyAyMDYuNjYyLDE0MC4yMjMgMTk2LjAwMCwxMzQuMDAwIEMxODAuMzA5LDEyNC44NDEgMTg0LjU1NiwxMDIuMzE4IDE5NS4wMDAsOTYuMDAwIEMyMDUuMjMzLDg5LjgxMCAyMTkuMzcwLDg3LjQzMCAyMjkuMzg0LDEwMi4wODQgQzIzMC43NzAsMTA3Ljc3MiAyMzUuODg1LDExMi4wMDAgMjQyLjAwMCwxMTIuMDAwIEMyNDkuMTgwLDExMi4wMDAgMjU1LjAwMCwxMDYuMTgwIDI1NS4wMDAsOTkuMDAwIEMyNTUuMDAwLDk1LjIxMyAyNTMuMzcxLDkxLjgxNSAyNTAuNzg3LDg5LjQzOSBaTTMzMi4wMDAsMjM3LjAwMCBDMzMyLjAwMCwyMzcuMDAwIDMzNy42ODQsMjA0LjU5NSAzMjguMDAwLDE3OC4wMDAgQzMxOC45MzQsMTUzLjEwMiAzMDIuNjQyLDEzNC4wNTQgMjg3LjE1MywxMjIuMzAzIEMyODQuMzEyLDExOS4wNTkgMjgwLjE1MSwxMTcuMDAwIDI3NS41MDAsMTE3LjAwMCBDMjY2Ljk0MCwxMTcuMDAwIDI2MC4wMDAsMTIzLjk0MCAyNjAuMDAwLDEzMi41MDAgQzI2MC4wMDAsMTM4Ljg1OCAyNjMuODMyLDE0NC4zMTYgMjY5LjMwOSwxNDYuNzA2IEMyNzkuODk5LDE1Ni40OTAgMzA2LjE3NiwxODUuMzAzIDMwMy4wMDAsMjI4LjAwMCBDMzAzLjAwMCwyMjguMDAwIDI4NS42MTUsMjI3LjIyNyAyNzMuMDAwLDIzOS4wMDAgQzI2MC4zODUsMjUwLjc3MyAyNTIuMDMxLDI2Ny41MzUgMjU1LjAwMCwyODMuMDAwIEwyNTUuMDk4LDI4Mi45OTYgQzI1NS44MzgsMjg5LjE5MyAyNjEuMTA0LDI5NC4wMDAgMjY3LjUwMCwyOTQuMDAwIEMyNzQuMjM0LDI5NC4wMDAgMjc5LjcxMCwyODguNjcwIDI3OS45NzUsMjgyLjAwMSBMMjgwLjAwMCwyODIuMDAwIEMyODAuMDAwLDI4Mi4wMDAgMjc5Ljk5MywyODEuOTIwIDI3OS45ODUsMjgxLjgwMSBDMjc5Ljk4NywyODEuNzAwIDI4MC4wMDAsMjgxLjYwMSAyODAuMDAwLDI4MS41MDAgQzI4MC4wMDAsMjgxLjA0OSAyNzkuOTczLDI4MC42MDQgMjc5LjkyNywyODAuMTY1IEMyNzkuODcyLDI3NC4yNDYgMjgxLjIyNywyNTUuODc3IDMwMC4wMDAsMjU0LjAwMCBDMzIyLjQxMywyNTEuNzU5IDMyNi43OTYsMjcyLjE4NCAzMjYuMDAwLDI4MC4wMDAgQzMyNS4yNTQsMjg3LjMyNyAzMTkuMzI3LDI5OC41NDUgMzA0LjE2NSwzMDAuNjk0IEMzMDIuODU2LDMwMC4yNTEgMzAxLjQ1OSwzMDAuMDAwIDMwMC4wMDAsMzAwLjAwMCBDMjkyLjgyMCwzMDAuMDAwIDI4Ny4wMDAsMzA1LjgyMCAyODcuMDAwLDMxMy4wMDAgQzI4Ny4wMDAsMzIwLjE4MCAyOTIuODIwLDMyNi4wMDAgMzAwLjAwMCwzMjYuMDAwIEMzMDAuNjQyLDMyNi4wMDAgMzAxLjI2OCwzMjUuOTM4IDMwMS44ODUsMzI1Ljg0OCBDMzAxLjk1MywzMjUuOTM4IDMwMi4wMDAsMzI2LjAwMCAzMDIuMDAwLDMyNi4wMDAgQzMwMi4wMDAsMzI2LjAwMCAzMzIuMzE2LDMyNS45NTMgMzQ2LjAwMCwyOTkuMDAwIEMzNTkuNjg0LDI3Mi4wNDcgMzQ1LjM3OSwyNDYuMTk1IDMzMi4wMDAsMjM3LjAwMCBaIi8+Cjwvc3ZnPg==', //plugin_dir_url(__FILE__) . 'views/richpanel_sidebar_icon.png',
			6
			);
			// array($this, 'richpanel_admin_page')
		}

		public function subscription_status_update_event( $subscription, $new_status, $old_status) {

			// Reference: https://docs.woocommerce.com/document/subscriptions/develop/action-reference/

			try {

				$order = $subscription;

				// prepare the order data
				$purchase_params = $this->prepare_order_params($order, array('old_status' => $old_status, 'new_status' => $new_status));
				$purchase_params['context'] = 'status_change';

				$purchase_params['start'] = $order->get_date('date_created') ? strtotime($order->get_date('date_created')) * 1000: null; //start deprecated
				$purchase_params['trial_end'] = $order->get_date('trial_end') ? strtotime($order->get_date('trial_end')) * 1000: null;
				$purchase_params['next_payment'] = $order->get_date('next_payment') ? strtotime($order->get_date('next_payment')) * 1000: null;
				$purchase_params['last_payment'] = $order->get_date('last_order_date_created') ? strtotime($order->get_date('last_order_date_created')) * 1000: null;
				$purchase_params['end'] = $order->get_date('end') ? strtotime($order->get_date('end')) * 1000: null;

				$call_params = array();

				// check if order has customer IP in it
				$order_id = $this->object_property($order, 'order', 'id');
				$customer_ip = $this->get_order_ip($order_id);
				if ($customer_ip) {
					$call_params['ip'] = $customer_ip;
				}

				$order_time_in_ms = get_post_time('U', true, $order_id) * 1000;

				// add the items data to the order
				$order_items = $order->get_items();
				foreach ($order_items as $item_id => $item_data) {
					$product_ = $this->resolve_product($item_data['product_id']);
					$product_hash = $this->prepare_product_hash($product_);
					$product_hash['quantity'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_qty', true) : $order->get_item_meta($item_id, '_qty', true);
					$product_hash['price'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_line_total', true) : $order->get_item_meta($item_id, '_line_total', true);
					
					array_push($purchase_params['items'], $product_hash);
				}

				// prepare order identity data
				$identity_data = $this->prepare_order_identity_data($order);

				$uid = $purchase_params['user_identity'];

				$user_id = $order->get_user_id();
				$call_params['userProperties'] = $this->getUserInfoBatch($purchase_params, $user_id);
				$did = null;
				$sid = null;
				$this->send_api_call($did, $sid, $uid, 'subscription_status_update', $purchase_params, false, $order_time_in_ms, $call_params);

			} catch (Exeption $e) {
				// error_log('error subscription_status_update_event');
				// error_log($e->getMessage());
				;
			}
		

		}

		public function new_subscription_event( $subscription, $order, $recurring_cart) {

			// Reference: https://docs.woocommerce.com/document/subscriptions/develop/action-reference/

			// fetch the order
			$order = $subscription;

			$call_params = array();

			// prepare the order data
			$purchase_params = $this->prepare_order_params($order);
			$purchase_params['context'] = 'new';
		
			$purchase_params['start'] = $order->get_date('date_created') ? strtotime($order->get_date('date_created')) * 1000: null; //start deprecated
			$purchase_params['trial_end'] = $order->get_date('trial_end') ? strtotime($order->get_date('trial_end')) * 1000: null;
			$purchase_params['next_payment'] = $order->get_date('next_payment') ? strtotime($order->get_date('next_payment')) * 1000: null;
			$purchase_params['last_payment'] = $order->get_date('last_order_date_created') ? strtotime($order->get_date('last_order_date_created')) * 1000: null;
			$purchase_params['end'] = $order->get_date('end') ? strtotime($order->get_date('end')) * 1000: null;

			$order_id = $this->object_property($order, 'order', 'id');
			// check if order has customer IP in it
			$customer_ip = $this->get_order_ip($order_id);
			if ($customer_ip) {
				$call_params['ip'] = $customer_ip;
			}

			// prepare the order time
			$order_time_in_ms = get_post_time('U', true, $order_id) * 1000;


			// add the items data to the order
			$order_items = $order->get_items();
			foreach ($order_items as $item_id => $item_data) {
				$product_ = $this->resolve_product($item_data['product_id']);
				$product_hash = $this->prepare_product_hash($product_);
				$product_hash['quantity'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_qty', true) : $order->get_item_meta($item_id, '_qty', true);
				$product_hash['price'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_line_total', true) : $order->get_item_meta($item_id, '_line_total', true);
			
				array_push($purchase_params['items'], $product_hash);
			}

			// prepare order identity data
			$identity_data = $this->prepare_order_identity_data($order);

			// if rpuid is present, use it isntead of the order email when placing the order
			$uid = null;
			$did = null;
			$sid = null;
			// if ($this->is_admin == flase) {
			// 	$uid = (isset($_COOKIE) && isset($_COOKIE['rpuid'])) ? filter_var($_COOKIE['rpuid'], FILTER_SANITIZE_STRING) : null;
			// 	$did = (isset($_COOKIE) && isset($_COOKIE['rpdid'])) ? filter_var($_COOKIE['rpdid'], FILTER_SANITIZE_STRING) : null;
			// 	$sid = (isset($_COOKIE) && isset($_COOKIE['rpsid'])) ? filter_var($_COOKIE['rpsid'], FILTER_SANITIZE_STRING) : null;
	
			// 	if ($uid != $purchase_params['user_identity']){
			// 		$uid = $purchase_params['user_identity'];
			// 		$this->rpdid = $this->wp_generate_uuid4();
			// 		$this->session_set('rpdid', $this->rpdid, 86400*1095);
			// 		$this->rpsid = $this->wp_generate_uuid4();
			// 		$this->session_set('rpsid', $this->rpsid, 3600);
			// 	}
			// }

			$user_id = $order->get_user_id();
			$call_params['userProperties'] = $this->getUserInfoBatch($purchase_params, $user_id);

			// send backend call with the order
			$this->send_api_call($did, $sid, $uid, 'subscription', $purchase_params, false, $order_time_in_ms, $call_params);
			//$this->put_event_in_cookie_queue('track', 'order', $this->prepare_product_hash($product, $variation_id, $variation));
			// put the order and identify data in cookies
			$this->session_set('rpuid', $uid, 86400*1095);
			$this->session_set($this->get_do_identify_cookie_name(), wp_json_encode($this->identify_call_data));

		}

		public function action_woocommerce_cart_updated() {
			//Update customer data

			// $uid = (isset($_COOKIE) && isset($_COOKIE['rpuid'])) ? filter_var($_COOKIE['rpuid'], FILTER_SANITIZE_STRING) : null;

			// if($uid != null){

			// }
		
			if (!is_object($this->woo->cart)) {
				return true;
			}

			$cart_items = $this->woo->cart->get_cart();

			//Send the cart
			$this->put_event_in_queue('track', 'cart_updated', array('items' => $cart_items));
		}

		public function richpanel_admin_page() {
			if (false) {
				check_admin_referer( 'RichpanelKeyMissing', 'woocommerce_richpanel-woo-analytics_api_key' );
			}

			if (isset($_POST['woocommerce_richpanel-woo-analytics_api_key']) && isset($_POST['woocommerce_richpanel-woo-analytics_api_secret'])) {
				$this->api_key = sanitize_text_field($_POST['woocommerce_richpanel-woo-analytics_api_key']);
				$this->api_secret = sanitize_text_field($_POST['woocommerce_richpanel-woo-analytics_api_secret']);
				if (method_exists($this, 'update_option')) {
					$this->update_option('api_key', $this->api_key);
					$this->update_option('api_secret', $this->api_secret);
				} else {
					update_option('api_key', $this->api_key);
					update_option('api_secret', $this->api_secret);
				}
			}

			if (!empty($_GET['import'])) {
				$this->set_importing_mode(true);
				$this->prepare_order_chunks($this->orders_per_import_chunk);
			} elseif (!empty($_GET['simport'])) {
				$this->set_simporting_mode(true);
				$this->prepare_subscription_chunks($this->subscription_per_import_chunk);
			}

			wp_enqueue_script('jquery');
			include_once( plugin_dir_path(__FILE__) . 'views/richpanel_admin_view.php' );
		}

		public function richpanel_import_page() {
			wp_enqueue_script('jquery');
			$richpanel_import = include_once( plugin_dir_path(__FILE__) . 'richpanel_import.php');
			$richpanel_import->prepare_import();
			if (!empty($_GET['import'])) {
				$richpanel_import->set_importing_mode(true);
				$richpanel_import->prepare_order_chunks($this->orders_per_import_chunk);
			} elseif (!empty($_GET['simport'])) {
				$richpanel_import->set_simporting_mode(true);
				$richpanel_import->prepare_subscription_chunks($this->subscription_per_import_chunk);
			}
			$richpanel_import->output();
		}

		public function prepare_shipment_data_from_items($order_items) {
			$shipments = [];

			// $data = array();
			$items = array();

			foreach ($order_items as $item_id => $item_data) {
				try {
					$meta_data = $item_data->get_meta_data();
					if (!empty($meta_data)) {
						foreach($meta_data as $item) {
							$key = strtolower($item['key']);
							if ( strpos($key, 'tracking') !== false ) {
								$items[] = array(
									'trackingCompany' => $key,
									'trackingNumber' => $item['value'],
									'itemId' => $item_id
								);
							}
						}
					}
				} catch (Exception $e) {
					//throw $th;
				}
			}

			return $items;

			// $sdata = array();
			// if (!empty($items)) {
			// 	foreach($items as $item) {
			// 		$id =  $item['trackingNumber'];
			// 		if (empty($sdata[$id])) {
			// 			$sdata[$id] = array();
			// 		} else {
			// 			$sdata[$id] = $item;
			// 		}
			// 		$sdata[$id]['items'][] = array(
			// 			'id' => $item['itemId'],
			// 			'quantity' => 1
			// 		);
			// 	}
			// }

			// if (!empty($sdata)) {
			// 	foreach($sdata as $key => $item) {
			// 		$shipments[] = array(
			// 			'id' => $key,
			// 			'tracking'[0] => array(
			// 				'trackingNumber' => $key,
			// 				'trackingCompany' => $item['trackingCompany']
			// 			),
			// 			'items' => $item['items']
			// 		);
			// 	}
			// }

			// return $items;
		}

		// To Do : Sync Subscription Orders also
		// Reference: https://docs.woocommerce.com/document/subscriptions/develop/data-structure/#section-3
		public function sync_orders_chunk( $specific_order_ids = false) {
			global $wpdb;

			$date = date('Y-m-d'); //Today
			$dateMinus12 = date('Y-m-d', strtotime('-24 Months')); // Last day 12 months ago
			// $dateMinus12 = date("Y-m-d", strtotime("-5 years")); // Last day 12 months ago

			if (!$specific_order_ids) {
				$order_ids = false;
				if (isset($_REQUEST['chunk_page'])) {
					$chunk_page = (int) $_REQUEST['chunk_page'];
					$chunk_offset = $chunk_page * $this->orders_per_import_chunk;

					// fetch order IDs
					// $order_ids = $wpdb->get_col("select id from {$wpdb->posts} where post_type = 'shop_order' AND post_date >= '{$dateMinus12}'  order by id asc limit {$this->orders_per_import_chunk} offset {$chunk_offset}");
					$order_ids = $wpdb->get_col($wpdb->prepare("select id from `$wpdb->posts` where post_type = 'shop_order' AND post_date >= %s  order by id asc limit %d offset %d", $dateMinus12, $this->orders_per_import_chunk, $chunk_offset));
				}
			} else {
				$order_ids = $specific_order_ids;
			}
			if (!empty($order_ids)) {
				foreach ($order_ids as $order_id) {

					try {
						$order = new WC_Order($order_id);
						if (!empty($order_id) && !empty($order)) {

							// prepare the order data
							$purchase_params = $this->prepare_order_params($order);
							$purchase_params['context'] = 'import';
							$purchase_params['order_type'] = 'import';
							$call_params = array();

							// check if order has customer IP in it
							$customer_ip = $this->get_order_ip($order_id);
							if ($customer_ip) {
								$call_params['ip'] = $customer_ip;
							}

							$order_time_in_ms = get_post_time('U', true, $order_id) * 1000;

							// add the items data to the order
							$order_items = $order->get_items();
							foreach ($order_items as $item_id => $item_data) {
								$product_ = $this->resolve_product($item_data['product_id']);
								$product_hash = $this->prepare_product_hash($product_);
								$product_hash['quantity'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_qty', true) : $order->get_item_meta($item_id, '_qty', true);
								$product_hash['price'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_line_total', true) : $order->get_item_meta($item_id, '_line_total', true);

								try {
									$product_hash['custom'] = 'val';
									$product_hash['get_data'] = $item_data->get_data();
									$product_hash['get_meta_data'] = $item_data->get_meta_data();
									$product_hash['get_formatted_meta_data'] = $item_data->get_formatted_meta_data( ' ', true );
									//code...
								} catch (Exception $e) {
									//throw $th;
								}
							
								if ($product_hash['id']) { // If we're not getting product id
									array_push($purchase_params['items'], $product_hash);
								}
							}

							try {
								// $shipmentDetails = $this->prepare_shipment_data_from_items($order_items);
								// if (!empty($shipmentDetails)) {
								// 	$purchase_params['custom_fulfillment'] = $shipmentDetails;
								// }
							} catch (Exception $e) {
								//throw $th;
							}


							// prepare order identity data
							$identity_data = $this->prepare_order_identity_data($order);

							$did = null;
							$sid = null;
							$uid = $purchase_params['user_identity'];

							$user_id = $order->get_user_id();
							$call_params['userProperties'] = $this->getUserInfoBatch($purchase_params, $user_id);

							$this->add_call_to_batch_queue($did, $sid, $uid, 'order', $purchase_params, false, $order_time_in_ms, $call_params);

						}

					} catch (Exception $e) {
						// error_log('error sync_orders_chunk');
						// error_log($e->getMessage());
						;
					}
				}
				$this->send_batch_calls('send_batch');
			}

			return true;
		}

		public function sync_subscriptions_chunk( $specific_order_ids = false) {
			global $wpdb;

			$date = date('Y-m-d'); //Today
			$dateMinus12 = date('Y-m-d', strtotime('-24 Months')); // Last day 12 months ago
			// $dateMinus12 = date("Y-m-d", strtotime("-5 years")); // Last day 12 months ago

			if (!$specific_order_ids) {
				$order_ids = false;
				if (isset($_REQUEST['chunk_page'])) {
					$chunk_page = (int) $_REQUEST['chunk_page'];
					$chunk_offset = $chunk_page * $this->subscription_per_import_chunk;

					// fetch order IDs
					// $order_ids = $wpdb->get_col("select id from {$wpdb->posts} where post_type = 'shop_subscription' AND post_date >= '{$dateMinus12}' order by id asc limit {$this->subscription_per_import_chunk} offset {$chunk_offset}");
					$order_ids = $wpdb->get_col($wpdb->prepare("select id from `$wpdb->posts` where post_type = 'shop_subscription' AND post_date >= %s order by id asc limit %d offset %d", $dateMinus12, $this->subscription_per_import_chunk, $chunk_offset));
				}
			} else {
				$order_ids = $specific_order_ids;
			}
			if (!empty($order_ids)) {
				foreach ($order_ids as $order_id) {

					try {
						//$order = new WC_Subscription($order_id);
						$order = wcs_get_subscription($order_id);
						//$order = WC()->order_factory->get_order( $order_id );
						if (!empty($order_id) && !empty($order)) {

							// prepare the order data
							$purchase_params = $this->prepare_order_params($order);
							$purchase_params['context'] = 'import';
							$purchase_params['order_type'] = 'import';
							$call_params = array();

							// Subscription Params : https://docs.woocommerce.com/document/subscriptions/develop/functions/
							$purchase_params['start'] = $order->get_date('date_created') ? strtotime($order->get_date('date_created')) * 1000: null; //start deprecated
							$purchase_params['trial_end'] = $order->get_date('trial_end') ? strtotime($order->get_date('trial_end')) * 1000: null;
							$purchase_params['next_payment'] = $order->get_date('next_payment') ? strtotime($order->get_date('next_payment')) * 1000: null;
							$purchase_params['last_payment'] = $order->get_date('last_order_date_created') ? strtotime($order->get_date('last_order_date_created')) * 1000: null;
							$purchase_params['end'] = $order->get_date('end') ? strtotime($order->get_date('end')) * 1000: null;

							// check if order has customer IP in it
							$customer_ip = $this->get_order_ip($order_id);
							if ($customer_ip) {
								$call_params['ip'] = $customer_ip;
							}

							$order_time_in_ms = get_post_time('U', true, $order_id) * 1000;

							// add the items data to the order
							$order_items = $order->get_items();
							foreach ($order_items as $item_id => $item_data) {
								$product_ = $this->resolve_product($item_data['product_id']);
								$product_hash = $this->prepare_product_hash($product_);
								$product_hash['quantity'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_qty', true) : $order->get_item_meta($item_id, '_qty', true);
								$product_hash['price'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_line_total', true) : $order->get_item_meta($item_id, '_line_total', true);
							
								array_push($purchase_params['items'], $product_hash);
							}

							// prepare order identity data
							$identity_data = $this->prepare_order_identity_data($order);

							$did = null;
							$sid = null;
							$uid = $purchase_params['user_identity'];

							$user_id = $order->get_user_id();
							$call_params['userProperties'] = $this->getUserInfoBatch($purchase_params, $user_id);

							$this->add_call_to_batch_queue($did, $sid, $uid, 'subscription', $purchase_params, false, $order_time_in_ms, $call_params);

						}

					} catch (Exception $e) {
						// error_log('error sync_subscription_chunk');
						// error_log($e->getMessage());
						;
					}
				}
				$this->send_batch_calls('send_batch');
			}

			return true;
		}

		public function prepare_order_identity_data( $order) {
			$identity_data = array(
						'email' 		=> get_post_meta($this->object_property($order, 'order', 'id'), '_billing_email', true),
						'first_name' 	=> get_post_meta($this->object_property($order, 'order', 'id'), '_billing_first_name', true),
						'last_name' 	=> get_post_meta($this->object_property($order, 'order', 'id'), '_billing_last_name', true),
						'name'			=> get_post_meta($this->object_property($order, 'order', 'id'), '_billing_first_name', true) . ' ' . get_post_meta($this->object_property($order, 'order', 'id'), '_billing_last_name', true),
			);

			if (empty($identity_data['email'])) {
				$order_user = $this->get_order_user($order);
				if ($order_user) {
					$identity_data = array(
						'email'						=> $order_user->data->user_email,
						'name'						=> $order_user->data->display_name
					);
				}
			}

			if ('yes' == $this->send_roles_as_tags) {
				$order_user = $this->get_order_user($order);
				if (!empty($order_user) && !empty($order_user->roles)) {
					$identity_data['tags'] = $order_user->roles;
				}
			}

			if (!empty($this->add_tag_to_every_customer)) {
				if (empty($identity_data['tags'])) {
					$identity_data['tags'] = array();
				}
				array_push($identity_data['tags'], $this->add_tag_to_every_customer);
			}

			return $identity_data;

		}

		public function resolve_product( $product_id) {
			if (function_exists('wc_get_product')) {
				return wc_get_product($product_id);
			} else {
				return get_product($product_id);
			}
		}

		public function get_order_user( $order) {
			if ($this->object_property($order, 'order', 'user_id')) {
				$order_user = get_user_by('id', $this->object_property($order, 'order', 'user_id'));
				return $order_user;
			}
			return false;
		}

		public function ensure_path() {
			define('RICHPANEL_PLUGIN_PATH', dirname(__FILE__));
		}

		public function ensure_identify() {
			// if user is logged in
			if ( !is_admin() && is_user_logged_in() && !( $this->session_get( $this->get_identify_cookie_name() ) ) ) {
				$user = wp_get_current_user();
				$this->identify_call_data = array('uid' => $user->ID, 'properties' => array('email' => $user->user_email, 'name' => $user->display_name));
			
				// check if roles should be sent and if they exist
				if ('yes' == $this->send_roles_as_tags && !empty($user->roles)) {
					$this->identify_call_data['properties']['tags'] = $user->roles;
				}

				// $this->identify_call_data = $this->getUserInfo($this->identify_call_data);

				$this->session_set($this->get_identify_cookie_name(), 'true');
			}

		}


		/**
		 *
		 *
		 * Events tracking methods, event hooks
		 *
		 *
		 */


		public function woocommerce_tracking() {
			// check if woocommerce is installed
			if (class_exists('WooCommerce')) {
				// check certain tracking scenarios

				// if visitor is viewing product
				if (!$this->single_item_tracked && is_product()) {
					$product = $this->resolve_product(get_queried_object_id());
					$this->put_event_in_queue('track', 'view_product', $this->prepare_product_hash($product));
					$this->single_item_tracked = true;
				}

				// if visitor is viewing product category
				if (!$this->single_item_tracked && is_product_category()) {
					$this->put_event_in_queue('track', 'view_category', $this->prepare_category_hash(get_queried_object()));
					$this->single_item_tracked = true;
				}

				// if visitor is viewing shopping cart page
				if (!$this->single_item_tracked && is_cart()) {
					$this->put_event_in_queue('track', 'view_cart', array());
					$this->single_item_tracked = true;
				}
				// if visitor is anywhere in the checkout process
				if (!$this->single_item_tracked && is_order_received_page()) {

					$this->put_event_in_queue('track', 'page_view', array('name' => 'Thank You'));
					$this->single_item_tracked = true;

				} elseif (!$this->single_item_tracked && function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
					$this->put_event_in_queue('track', 'checkout_payment', array());
					$this->single_item_tracked = true;
				} elseif (!$this->single_item_tracked && is_checkout()) {
					$this->put_event_in_queue('track', 'checkout_start', array());
					$this->single_item_tracked = true;
				}
			}

			// ** GENERIC WordPress tracking - doesn't require WooCommerce in order to work **//

			// if visitor is viewing homepage or any text page
			if (!$this->single_item_tracked && is_front_page()) {
				$this->put_event_in_queue('track', 'page_view', array('name' => 'Homepage'));
				$this->single_item_tracked = true;
			} elseif (!$this->single_item_tracked && is_page()) {
				$this->put_event_in_queue('track', 'page_view', array('name' => get_the_title()));
				$this->single_item_tracked = true;
			}

			// if visitor is viewing post
			if (!$this->single_item_tracked && is_single()) {
				$post_id = get_the_id();
				$this->put_event_in_queue('track', 'view_article', array('id' => $post_id, 'name' => get_the_title(), 'url' => get_permalink($post_id)));
				$this->single_item_tracked = true;
			}

			// if nothing else is tracked - send page_view event
			if (!$this->single_item_tracked) {
				$this->put_event_in_queue('track', 'page_view');
			}

			// check if there is identity, if yes call identify event
			if (false !== $this->identify_call_data) {
				$this->put_event_in_queue('track', 'identify');
			}
			// check if there are events in the queue to be sent to Richpanel
			if (count($this->events_queue) > 0) {
				$this->render_events();
			}
			// error_log('at init');
			// error_log(print_r($this->cookie_to_set,true));
			// if(count($this->cookie_to_set) > 0) $this->render_cookie();
			return null;
		}

		// public function set_richpanel_cookie($key, $value, $expiry) {
		// 	error_log('pusinh to cookie_to_set');
		// 	array_push($this->cookie_to_set, array(
		// 		'key' => $key,
		// 		'value' => $value,
		// 		'expiry' => $expiry,
		// 		'path' => COOKIEPATH,
		// 		'domain' => COOKIE_DOMAIN
		// 	));
		// 	error_log(print_r($this->cookie_to_set,true));
		// 	$this->render_cookie();
		// 	return null;
		// }


		public function woocommerce_footer_tracking() {
			if (count($this->events_queue) > 0) {
				$this->render_footer_events();
			}
			return null;
		}

		public function prepare_product_hash( $product, $variation_id = false, $variation = false) {

			if (false == $product) {
				return [];
			}
		
			$product_id = method_exists($product, 'get_id') ? $product->get_id() : $product->id;
			$product_hash = array(
			'id'			=> $product_id,
			'price'			=> method_exists($product, 'get_price') ? $product->get_price() : 0,
			'url'			=> get_permalink($product_id)
			);

			if (method_exists($product, 'get_sku')) {
				$sku =  trim($product->get_sku());
				if ('' != $sku && null != $sku) {
					$product_hash['sku'] = $sku;
				}
			}

			if (method_exists($product, 'get_title')) {
				$product_hash['name'] = $product->get_title();
			} else {
				$product_hash['name'] = $product['name'];
			}

			if (gettype($product) == 'array') {
				if ($product['variation_id']) {
					$variation_id = $product['variation_id'];
				}
			}

			if ($variation_id) {
				$variation_data = $this->prepare_variation_data($variation_id, $variation);
				$product_hash['option_id'] = $variation_data['id'];
				$product_hash['option_name'] = $variation_data['name'];
				$product_hash['option_price'] = $variation_data['price'];
				$product_hash['option_image_url'] = $this->get_image_urls_by_product_id($variation_data['id']);
				if (trim($variation_data['sku']) != '') {
					$product_hash['option_sku'] = $variation_data['sku'];
				}
			}
			// fetch image URL
			$product_hash['image_url'] = $this->get_image_urls_by_product_id($product_id);

			// fetch the categories
			$categories_list = array();
			$categories = wp_get_post_terms($product_id, 'product_cat');
			if (!empty($categories)) {
				foreach ($categories as $cat) {
					array_push($categories_list, array(
						'id' => $cat->term_id, 
						'name' => $cat->name, 
						'parent' => $this->get_parent_category($cat->term_id)
					)
					);
				}
			}

			// fetch brand taxonomy if available
			if ('none' != $this->product_brand_taxonomy) {
				$brand_name = $product->get_attribute($this->product_brand_taxonomy);
				if (!empty($brand_name)) {
					array_push($categories_list, array('id' => 'brand_' . $brand_name, 'name' => $brand_name));
				}
			}

			// include list of categories if any
			if (!empty($categories_list)) {
				$product_hash['categories'] = $categories_list;
			}

			// return
			return $product_hash;
		}

		public function get_parent_category( $category_ids ) {
			$result = array();
			$parentcats = get_ancestors($category_ids, 'product_cat');
			foreach ($parentcats as $category_id) {
				$category = get_term_by( 'id', $category_id, 'product_cat');
				$result[] = array('id' => $category_id, 'name' => $category->name);
			}
			return $result;
		}

		public function get_image_urls_by_product_id( $productId) {

			$imgUrls = array();

			// Woocommerce Product Featured Image
			$image_id = get_post_thumbnail_id($productId);
			$image = wp_get_attachment_image_src($image_id, 'full');
			if ($image) {
				$imgUrls[] = $image[0];
			}
 
			// Woocommerce Gallery Images
			$product = new WC_product($productId);
			$attachmentIds = array();
			if (method_exists($product, 'get_gallery_image_ids')) {
				$attachmentIds = $product->get_gallery_image_ids();
			} else {
				$product->get_gallery_attachment_ids();
			}
			foreach ( $attachmentIds as $attachmentId ) {
				$imgUrls[] = wp_get_attachment_url( $attachmentId );
			}
	 
			return $imgUrls;
		}

		public function prepare_category_hash( $category) {
			$category_hash = array(
			'id'	=>	$category->term_id,
			'name'	=> 	$category->name,
			'parent' => $this->get_parent_category($category->term_id)
			);
			return $category_hash;
		}

		public function put_event_in_queue( $method, $event = '', $properties = array()) {
			if ($this->check_if_event_should_be_ignored($method)) {
				return true;
			}
			if ($this->check_if_event_should_be_ignored($event)) {
				return true;
			}
			array_push($this->events_queue, $this->prepare_event_for_queue($method, $event, $properties));
		}

		public function put_event_in_cookie_queue( $method, $event, $properties) {
			if ($this->check_if_event_should_be_ignored($method)) {
				return true;
			}
			if ($this->check_if_event_should_be_ignored($event)) {
				return true;
			}
			$this->add_item_to_cookie($this->prepare_event_for_queue($method, $event, $properties));
		}

		public function prepare_event_for_queue( $method, $event, $properties) {

			$call = array('method' => $method, 'event' => $event, 'properties' => $properties);
		
			$call = $this->getUserInfo($call);

			return $call;
		}

		public function getUserInfo( $call) {

			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				$data = $this->fillUserData($user);
				$call['userProperties'] = $data;	
			} 

			return $call;
		}

		public function getRichpanelUserData() {
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				$data = $this->fillUserData($user);
				return array(
				'data' => $this->encryptData($data)
				);
			}
		}

		public function encrypt( $data, $key, $method) {
			$iv_size        = openssl_cipher_iv_length($method);
			$iv             = openssl_random_pseudo_bytes($iv_size);
			$ciphertext     = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
			$ciphertext_hex = bin2hex($ciphertext);
			$iv_hex         = bin2hex($iv);
			return "$iv_hex:$ciphertext_hex";
		}
	
		public function encryptData( $data) {

			// $api_key = $this->getApiToken($storeId);
			$api_secret = $this->api_secret;

			$method = 'AES-256-CBC';
			$key = hash('sha256', $api_secret);
		
			return $this->encrypt(wp_json_encode($data), $key, $method); 
		}

		public function getUserInfoBatch( $data, $id) {

			$result = array();
			$user = get_user_by('id', $id);
		
			if ($user) {
				$result = $this->fillUserData($user);
				$result['sourceId'] = $user->ID;
			} else {
				$result = $this->fillUserDataWithOrderData($data);
			}

			return $result;
		}

		public function fillUserDataWithOrderData( $properties) {
			if (!isset($properties['email']) || !$properties['email']) {
				return null;
			}

			$data = array(
				'uid' => isset($properties['email']) ? $properties['email'] : null, 
				'email' => isset($properties['email']) ? $properties['email'] : null, 
				'firstName' => isset($properties['first_name']) ? $properties['first_name'] : null, 
				'lastName' => isset($properties['last_name']) ? $properties['last_name'] : null
			);
		
			$data['billingAddress'] = array(
				'city'	=> isset($properties['billing_city']) ? $properties['billing_city'] : null,
				'state'	=> isset($properties['billing_region']) ? $properties['billing_region'] : null,
				'country'	=> isset($properties['billing_country']) ? $properties['billing_country'] : null,
				'postcode'	=> isset($properties['billing_postcode']) ? $properties['billing_postcode'] : null,
				'phone'	=> isset($properties['billing_phone']) ? $properties['billing_phone'] : null,
				'address1'	=> isset($properties['billing_address']) ? $properties['billing_address'] : null
			);

			$data['shippingAddress'] = array(
				'city'	=> isset($properties['shipping_city']) ? $properties['shipping_city'] : null,
				'state'	=> isset($properties['shipping_region']) ? $properties['shipping_region'] : null,
				'country'	=> isset($properties['shipping_country']) ? $properties['shipping_country'] : null,
				'postcode'	=> isset($properties['shipping_postcode']) ? $properties['shipping_postcode'] : null,
				'phone'	=> isset($properties['shipping_phone']) ? $properties['shipping_phone'] : null,
				'address1'	=> isset($properties['shipping_address']) ? $properties['shipping_address'] : null
			);

			return $data;
		}

		public function fillUserData( $user) {
			$id = $user->ID;
			// $userProperties = get_user_meta($user->ID);
			// $userProperties = get_user_by('id', 1);
			$userProperties = get_metadata( 'user', $user->ID, '', false );
			return array(
			'uid'	=> $user->user_email, 
			'email' => $user->user_email, 
			'name' => $user->display_name, 
			'firstName' => $user->user_firstname, 
			'lastName' => $user->user_lastname,
			'lastLogin'	=>	$this->getFirstElement(isset($userProperties['last_login']) ? $userProperties['last_login'] : null),
			'facebook'	=>	$this->getFirstElement(isset($userProperties['facebook']) ? $userProperties['facebook'] : null) ,
			'twitter'	=>	$this->getFirstElement(isset($userProperties['twitter']) ? $userProperties['twitter'] : null) ,
			'linkedin'	=>	$this->getFirstElement(isset($userProperties['linkedin']) ? $userProperties['linkedin'] : null) ,
			'instagram'	=>	$this->getFirstElement(isset($userProperties['instagram']) ? $userProperties['instagram'] : null) ,
			'pinterest'	=>	$this->getFirstElement(isset($userProperties['pinterest']) ? $userProperties['pinterest'] : null) ,
			'tumblr'	=>	$this->getFirstElement(isset($userProperties['tumblr']) ? $userProperties['tumblr'] : null) ,
			'googleplus'	=>	$this->getFirstElement(isset($userProperties['googleplus']) ? $userProperties['googleplus'] : null) ,
			'billingAddress' => array(
				'firstName'	=> $this->getFirstElement(isset($userProperties['billing_first_name']) ? $userProperties['billing_first_name'] : null) ,
				'lastName'	=> $this->getFirstElement(isset($userProperties['billing_last_name']) ? $userProperties['billing_last_name'] : null) ,
				'city'	=> $this->getFirstElement(isset($userProperties['billing_city']) ? $userProperties['billing_city'] : null) ,
				'state'	=> $this->getFirstElement(isset($userProperties['billing_state']) ? $userProperties['billing_state'] : null) ,
				'country'	=> $this->getFirstElement(isset($userProperties['billing_country']) ? $userProperties['billing_country'] : null) ,
				'email'	=> $this->getFirstElement(isset($userProperties['billing_email']) ? $userProperties['billing_email'] : null),
				'postcode'	=> $this->getFirstElement(isset($userProperties['billing_postcode']) ? $userProperties['billing_postcode'] : null) ,
				'phone'	=> $this->getFirstElement(isset($userProperties['billing_phone']) ? $userProperties['billing_phone'] : null) ,
				'address1'	=> $this->getFirstElement(isset($userProperties['billing_address_1']) ? $userProperties['billing_address_1'] : null),
				'address2'	=> $this->getFirstElement(isset($userProperties['billing_address_2']) ? $userProperties['billing_address_2'] : null)
			),
			'shippingAddress' => array(
				'firstName'	=> $this->getFirstElement(isset($userProperties['shipping_first_name']) ? $userProperties['shipping_first_name'] : null) ,
				'lastName'	=> $this->getFirstElement(isset($userProperties['shipping_last_name']) ? $userProperties['shipping_last_name'] : null) ,
				'city'	=> $this->getFirstElement(isset($userProperties['shipping_city']) ? $userProperties['shipping_city'] : null) ,
				'state'	=> $this->getFirstElement(isset($userProperties['shipping_state']) ? $userProperties['shipping_state'] : null) ,
				'country'	=> $this->getFirstElement(isset($userProperties['shipping_country']) ? $userProperties['shipping_country'] : null) ,
				'email'	=> $this->getFirstElement(isset($userProperties['shipping_email']) ? $userProperties['shipping_email'] : null),
				'postcode'	=> $this->getFirstElement(isset($userProperties['shipping_postcode']) ? $userProperties['shipping_postcode'] : null) ,
				'phone'	=> $this->getFirstElement(isset($userProperties['shipping_phone']) ? $userProperties['shipping_phone'] : null) ,
				'address1'	=> $this->getFirstElement(isset($userProperties['shipping_address_1']) ? $userProperties['shipping_address_1'] : null),
				'address2'	=> $this->getFirstElement(isset($userProperties['shipping_address_2']) ? $userProperties['shipping_address_2'] : null)
			)
			);

		}

		public function getFirstElement( $array) {
			if (empty($array)) {
				return '';
			}
			return reset($array)?reset($array):'';
		}

		public function send_api_call( $did, $sid, $ident, $event, $properties, $identity_data = false, $time = false, $call_params = false) {

			if (!empty($this->api_key) && !empty($this->api_secret)) {
				$this->prepare_secret_call_hash($did, $sid, $ident, $event, $properties, $identity_data, $time, $call_params);
			}

		}

		public function check_if_event_should_be_ignored( $event) {
			if (empty($this->ignore_for_events)) {
				return false;
			}
			if (in_array($event, $this->ignore_for_events)) {
				return true;
			}
			return false;
		}

		private function clear_batch_call_queue() {
			$this->batch_calls_queue = array();
		}

		private function add_call_to_batch_queue( $did, $sid, $ident, $event, $properties, $identity_data = false, $time = false, $call_params = false) { 
			$call = $this->build_call($did, $sid, $ident, $event, $properties, $identity_data, $time, $call_params);
			array_push($this->batch_calls_queue, $call);
		}

		private function send_batch_calls( $event_type) {

			try {

				$call = array(
				'appClientId'					=> $this->api_key,
				'platform'			=> 'WordPress ' . get_bloginfo('version') . ' / WooCommerce ' . WOOCOMMERCE_VERSION,
				'version'				=> $this->integration_version,
				'events'				=> $this->batch_calls_queue,
				'event'		=> $event_type
				);

				// sort for salting and prepare base64
				ksort($call);
				$based_call = base64_encode(wp_json_encode($call));
				$signature = md5($based_call . $this->api_secret);

				// generate API call end point and call it
				$end_point_params = array('s' => $signature, 'hs' => $based_call, 'event_type'	=> $event_type);
				$c = wp_remote_post($this->http_or_https . '://' . $this->endpoint_domain . '/bt', array( 
				'body' => wp_json_encode($end_point_params), 
				'blocking' => false,
				'headers'   => array('Content-Type' => 'application/json; charset=utf-8')
				));

			} catch (Exception $e) {
				// error_log('error send_batch_calls');
				// error_log($e->getMessage());
				return false;
			}

			return true;

		}

		private function build_call( $did, $sid, $ident, $event, $properties, $identity_data = false, $time = false, $call_params = false) {
			$call = array(
				'event'		=> $event,
				'properties'		=> $properties,
				// 'uid'				=> $ident,
				'did'				=> $did,
				'sid'				=> $sid,
				'appClientId'		=> $this->api_key,
				'platform'			=> 'WordPress ' . get_bloginfo('version') . ' / WooCommerce ' . WOOCOMMERCE_VERSION,
				'version'			=> $this->integration_version,
			);

			if ($time) {
				$call['time'] = array('originalTimestamp' => $time, 'sentAt' => round(microtime(true) * 1000));
			} else {
				$call['time'] = array('sentAt' => round(microtime(true) * 1000));
			}

			// check for special parameters to include in the API call
			if ($call_params) {
				if ($call_params['ip']) {
					$ipContext = array('networkIP' => $call_params['ip']);
					$call['context'] = array('ip' => $ipContext);
				}
				if ($call_params['userProperties']) {
					$call['userProperties'] = $call_params['userProperties'];
				}
			}

			// put identity data in call if available
			// if($identity_data){
			// 	$call['identity'] = $identity_data;
			// }

			// $call = $this->getUserInfo($call);

			return $call;
		}

		private function prepare_secret_call_hash( $did, $sid, $ident, $event, $properties, $identity_data = false, $time = false, $call_params = false) {

			// prepare API call properties

			try {

				$call = $this->build_call($did, $sid, $ident, $event, $properties, $identity_data, $time, $call_params);

				// sort for salting and prepare base64
				ksort($call);
				$based_call = base64_encode(wp_json_encode($call));
				$signature = md5($based_call . $this->api_secret);

				// generate API call end point and call it
				$end_point_params = array('s' => $signature, 'h' => $based_call, 'event_type' => 'track');
				wp_remote_post($this->http_or_https . '://' . $this->endpoint_domain . '/t', array( 
				'body' => wp_json_encode($end_point_params), 
				'blocking' => false,
				'headers'   => array('Content-Type' => 'application/json; charset=utf-8') 
				));

				// $sid = (isset($_COOKIE) && isset($_COOKIE['rpsid'])) ? filter_var($_COOKIE['rpsid'], FILTER_SANITIZE_STRING) : $this->wp_generate_uuid4();

				// $this->session_set(rpsid, $encoded_items, 3600);

			} catch (Exception $e) {
				// error_log('error prepare_secret_call_hash');
				// error_log($e->getMessage());
				;
			}

		}

		public function add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id = false, $variation = false, $cart_item_data = false) {
			$product = $this->resolve_product($product_id);
			$product_hash = $this->prepare_product_hash($product, $variation_id, $variation);
			$this->put_event_in_cookie_queue('track', 'add_to_cart', $product_hash);
			// $items = $this->get_items_in_cookie();
		}

		public function remove_from_cart( $key_id, $quantity = 0, $old_quantity = 0) {

			if ($quantity < $old_quantity) {
				if (!is_object($this->woo->cart)) {
					return true;
				}
				$cart_items = $this->woo->cart->get_cart();
				$removed_cart_item = isset($cart_items[$key_id]) ? $cart_items[$key_id] : false;
				if ($removed_cart_item) {
					$event_params = array('id' => $removed_cart_item['product_id']);
					if (!empty($removed_cart_item['variation_id'])) {
						$event_params['option_id'] = $removed_cart_item['variation_id'];
					}
					$this->put_event_in_cookie_queue('track', 'remove_from_cart', $event_params);
				}
			}
		}

		public function prepare_variation_data( $variation_id, $variation = false) {
			// prepare variation data array
			$variation_data = array('id' => $variation_id, 'name' => '', 'price' => '');

			// prepare variation name if $variation is provided as argument
			if ($variation) {
				$variation_attribute_count = 0;
				foreach ($variation as $attr => $value) {
					$variation_data['name'] = $variation_data['name'] . ( 0 == $variation_attribute_count ? '' : ', ' ) . $value;
					$variation_attribute_count++;
				}
			}

			// get variation price from object
			if (function_exists('wc_get_product')) {
				$variation_obj = wc_get_product($variation_id);
			} else {
				$variation_obj = new WC_Product_Variation($variation_id);
			}
			
			$variation_data['price'] = $this->object_property($variation_obj, 'variation', 'price');
			$variation_data['image'] = $this->object_property($variation_obj, 'variation', 'image');

			// return
			return $variation_data;
		}

		public function applied_coupon( $coupon_code) {
			$this->put_event_in_queue('track', 'applied_coupon', array('coupon_code' => $coupon_code));
			return null;
		}

		public function new_order_event( $order_id) {

			// fetch the order
			$order = new WC_Order($order_id);

			$call_params = array();

			// prepare the order data
			$purchase_params = $this->prepare_order_params($order);
			$purchase_params['context'] = 'new';

			// check if order has customer IP in it
			$customer_ip = $this->get_order_ip($order_id);
			if ($customer_ip) {
				$call_params['ip'] = $customer_ip;
			}

			// prepare the order time
			$order_time_in_ms = get_post_time('U', true, $order_id) * 1000;


			// add the items data to the order
			$order_items = $order->get_items();
			foreach ($order_items as $item_id => $item_data) {
				$product_ = $this->resolve_product($item_data['product_id']);
				$product_hash = $this->prepare_product_hash($product_);
				$product_hash['quantity'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_qty', true) : $order->get_item_meta($item_id, '_qty', true);
				$product_hash['price'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_line_total', true) : $order->get_item_meta($item_id, '_line_total', true);
			
				array_push($purchase_params['items'], $product_hash);
			}

			// prepare order identity data
			$identity_data = $this->prepare_order_identity_data($order);

			$user_id = $order->get_user_id();
			$call_params['userProperties'] = $this->getUserInfoBatch($purchase_params, $user_id);

			// if rpuid is present, use it isntead of the order email when placing the order
			$uid = null;
			$did = null;
			$sid = null;
			// error_log('new order');
			// error_log(COOKIEPATH);
			// if (!is_admin()) {
			// 	error_log('not admin');
			// 	if (isset($call_params['userProperties'])) {
			// 		error_log('has userProperties');
			// 		if (isset($call_params['userProperties']['uid'])) {
			// 			error_log('has userProperties & uid');
			// 			$uid = (isset($_COOKIE) && isset($_COOKIE['rpuid'])) ? filter_var($_COOKIE['rpuid'], FILTER_SANITIZE_STRING) : null;
			// 			$did = (isset($_COOKIE) && isset($_COOKIE['rpdid'])) ? filter_var($_COOKIE['rpdid'], FILTER_SANITIZE_STRING) : null;
			// 			$sid = (isset($_COOKIE) && isset($_COOKIE['rpsid'])) ? filter_var($_COOKIE['rpsid'], FILTER_SANITIZE_STRING) : null;

			// 			if (!empty($uid) && $call_params['userProperties']['uid'] != $uid) {
			// 				error_log('updating ids to new');
			// 				$uid = $call_params['userProperties']['uid'];
			// 				$this->set_richpanel_cookie('rpuid', $uid, 3600*48);
			// 				$did = $this->wp_generate_uuid4();
			// 				$this->set_richpanel_cookie('rpdid', $did, 86400*1095);
			// 				$sid = $this->wp_generate_uuid4();
			// 				$this->set_richpanel_cookie('rpsid', $sid, 3600);
			// 			} else if (empty($uid)) {
			// 				//do nothing
			// 				error_log('just update');
			// 				$uid = $call_params['userProperties']['uid'];
			// 				$this->set_richpanel_cookie('rpuid', $uid, 3600*48);
			// 				$this->set_richpanel_cookie('rpdid', $did, 86400*1095);
			// 				$this->set_richpanel_cookie('rpsid', $sid, 3600);
			// 				$this->set_richpanel_cookie('rptest', 'test', 3600);
			// 			} else {
			// 				error_log('do nothign -  uid is !empty and they are match');
			// 				// $uid = null;
			// 				// $did = null;
			// 				// $sid = null;
			// 			}
			// 		}
			// 	}
			// }

			// send backend call with the order
			// $this->send_api_call($did, $sid, $uid, 'order', $purchase_params, false, $order_time_in_ms, $call_params);
			$this->send_api_call($did, $sid, $uid, 'order', $purchase_params, false, $order_time_in_ms, $call_params);
			// $this->put_event_in_cookie_queue('track', 'order', $purchase_params);
			// put the order and identify data in cookies
			$this->session_set('rpuid', $uid, 86400*1095);
			$this->session_set($this->get_do_identify_cookie_name(), wp_json_encode($this->identify_call_data));

		}

		public function check_for_multi_currency( $purchase_params) {
			if (class_exists('Aelia_Order')) {
				$aelia_order = new Aelia_Order($purchase_params['order_id']);
				$purchase_params['amount'] =  method_exists($aelia_order, 'get_total_in_base_currency') ? $aelia_order->get_total_in_base_currency() : $purchase_params['amount'];
				$purchase_params['shipping_amount'] =  method_exists($aelia_order, 'get_total_shipping_in_base_currency') ? $aelia_order->get_total_shipping_in_base_currency() : $purchase_params['shipping_amount'];
				$purchase_params['tax_amount'] =  method_exists($aelia_order, 'get_total_tax_in_base_currency') ? $aelia_order->get_total_tax_in_base_currency() : $purchase_params['tax_amount'];
			}
			return $purchase_params;
		}

		public function new_subscription_order_event( $order, $original_order, $product_id, $new_order_role) {

			try {

				$purchase_params = $this->prepare_order_params($order);
				$purchase_params['context'] = 'renewal';
				$purchase_params['order_type'] = 'renewal';
				$purchase_params['meta_source'] = '_renewal';

				// prepare order identity data
				$identity_data = $this->prepare_order_identity_data($order);

				// prepare product data
				$product_ = $this->resolve_product($product_id);
				$product_data = $this->prepare_product_hash($product_);
				$product_data['quantity'] = 1;

				$purchase_params['items'] = array($product_data);

				// if rpuid is present, use it isntead of the order email when placing the order
				$user_id = $order->get_user_id();
				$call_params['userProperties'] = $this->getUserInfoBatch($purchase_params, $user_id);

				$uid = null;
				$did = null;
				$sid = null;
				// if (!is_admin()) {
				// 	if (isset($call_params['userProperties'])) {
				// 		if (isset($call_params['userProperties']['uid'])) {
				// 			$uid = (isset($_COOKIE) && isset($_COOKIE['rpuid'])) ? filter_var($_COOKIE['rpuid'], FILTER_SANITIZE_STRING) : null;
				// 			$did = (isset($_COOKIE) && isset($_COOKIE['rpdid'])) ? filter_var($_COOKIE['rpdid'], FILTER_SANITIZE_STRING) : null;
				// 			$sid = (isset($_COOKIE) && isset($_COOKIE['rpsid'])) ? filter_var($_COOKIE['rpsid'], FILTER_SANITIZE_STRING) : null;

				// 			if (!empty($uid) && $call_params['userProperties']['uid'] != $uid) {
				// 				$uid = $call_params['userProperties']['uid'];
				// 				$this->session_set('rpuid', $uid, 3600*48);
				// 				$did = $this->wp_generate_uuid4();
				// 				$this->session_set('rpdid', $this->rpdid, 86400*1095);
				// 				$sid = $this->wp_generate_uuid4();
				// 				$this->session_set('rpsid', $this->rpsid, 3600);
				// 			} else {
				// 				$uid = null;
				// 				$did = null;
				// 				$sid = null;
				// 			}
				// 		}
				// 	}
				// }

				$this->send_api_call($did, $sid, $uid, 'order', $purchase_params, false);

			} catch (Exception $e) {
				// error_log('error new_subscription_order_event');
				// error_log($e->getMessage());
				;
			}
			return $order;
		}

		public function order_status_changed( $order_id, $old_status = false, $new_status = false) {
			try {

				$order = new WC_Order($order_id);

				// prepare the order data
				$purchase_params = $this->prepare_order_params($order, array('old_status' => $old_status, 'new_status' => $new_status));
				$purchase_params['context'] = 'status_change';
				$call_params = array();

				// check if order has customer IP in it
				$customer_ip = $this->get_order_ip($order_id);
				if ($customer_ip) {
					$call_params['ip'] = $customer_ip;
				}

				$order_time_in_ms = get_post_time('U', true, $order_id) * 1000;

				// add the items data to the order
				$order_items = $order->get_items();
				foreach ($order_items as $item_id => $item_data) {
					$product_ = $this->resolve_product($item_data['product_id']);
					$product_hash = $this->prepare_product_hash($product_);
					$product_hash['quantity'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_qty', true) : $order->get_item_meta($item_id, '_qty', true);
					$product_hash['price'] = method_exists($order, 'wc_get_order_item_meta') ? $order->wc_get_order_item_meta($item_id, '_line_total', true) : $order->get_item_meta($item_id, '_line_total', true);
					
					array_push($purchase_params['items'], $product_hash);
				}

				// prepare order identity data
				$identity_data = $this->prepare_order_identity_data($order);

				// if rpuid is present, use it isntead of the order email when placing the order
				// $uid = $purchase_params['user_identity'];
				$did = null;
				$sid = null;
				
				$user_id = $order->get_user_id();
				$call_params['userProperties'] = $this->getUserInfoBatch($purchase_params, $user_id);

				if (isset($call_params['userProperties'])) {
					if (isset($call_params['userProperties']['uid'])) {
						$uid = $call_params['userProperties']['uid'];
					}
				}
				
				$this->send_api_call($did, $sid, $uid, 'order_status_update', $purchase_params, false, $order_time_in_ms, $call_params);

			} catch (Exeption $e) {
				// error_log('error order_status_changed');
				// error_log($e->getMessage());
				;
			}
		}

		public function get_order_status( $order_object) {
			if (method_exists($order_object, 'get_status')) {
				return $order_object->get_status();
			} else {
				if (property_exists($order_object, 'status')) {
					return $order_object->status;
				}
			}
		}

		public function prepare_order_params( $order, $order_merge_params = array()) {

			// prepare basic order data
			$purchase_params = array(
			'order_id' 			  => $this->object_property($order, 'order', 'order_number'),
			'order_db_key'		=> $this->object_property($order, 'order', 'id'),
			'order_status' 		=> $this->get_order_status($order),
			'amount' 			    => $order->get_total(),
			'shipping_amount' => method_exists($order, 'get_total_shipping') ? $order->get_total_shipping() : $order->get_shipping(),
			'tax_amount'		  => $order->get_total_tax(),
			'discount_amount' => $order->get_total_discount(),
			'items' 			    => array(),
			'shipping_method'	=> $order->get_shipping_method(),
			'payment_method'	=> $this->object_property($order, 'order', 'payment_method_title'),
			'email' 		=> get_post_meta($this->object_property($order, 'order', 'id'), '_billing_email', true),
			'first_name' 	=> get_post_meta($this->object_property($order, 'order', 'id'), '_billing_first_name', true),
			'last_name' 	=> get_post_meta($this->object_property($order, 'order', 'id'), '_billing_last_name', true)
			// 'data' => $order->data
			);
			if (!empty($order_merge_params)) {
				$purchase_params = array_merge($purchase_params, $order_merge_params);
			}

			// Fetching Tracking Info
			try {
				// $order_data = $order->data;
				// To get all
				// $purchase_params['data'] = get_post_meta( $purchase_params['order_id'] );

				// To get specific
				$trackingFields = get_post_meta( $purchase_params['order_db_key'], '_wc_shipment_tracking_items', true );
				// $purchase_params['test1'] = $trackingFields;

				if (!empty($trackingFields)) {
					$trackingFields = $trackingFields[0];
					if (!empty($trackingFields['tracking_provider'])) {
						$purchase_params['trackingCompany'] = $trackingFields['tracking_provider'];
						$purchase_params['trackingUrl'] = $trackingFields['tracking_link'];
					} else {
						$purchase_params['trackingCompany'] = $trackingFields['custom_tracking_provider'];
						$purchase_params['trackingUrl'] = $trackingFields['custom_tracking_link'];
					}
				
					$purchase_params['trackingNumber'] = $trackingFields['tracking_number'];
					$purchase_params['trackingId'] = $trackingFields['tracking_id'];

					if (!empty($trackingFields['date_shipped'])) {
						$purchase_params['shippingDate'] = (int) $trackingFields['date_shipped'] * 1000;
					}
				} else  {
					$trackingFields = get_post_meta( $purchase_params['order_id'], 'wf_canadapost_shipment_source', true );
					// $purchase_params['$trackingFields'] = $trackingFields
					if (!empty($trackingFields)) {
						$purchase_params['trackingCompany'] = $trackingFields['shipping_service'];
						$purchase_params['trackingUrl'] = "http://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=" . $trackingFields['shipment_id_cs'];	
						$purchase_params['trackingNumber'] = $trackingFields['shipment_id_cs'];
						$purchase_params['trackingId'] = $trackingFields['shipment_id_cs'];

						if (!empty($trackingFields['order_date'])) {
							$purchase_params['shippingDate'] = (int) strtotime($trackingFields['date_shipped']) * 1000;
						}
					}
				}

			} catch (Exception $e) {
				// error_log('error prepare_order_params');
				// error_log($e->getMessage());
				;
			}

			try {
				$checkoutFields = get_post_meta( $purchase_params['order_id'], '_wccf_checkout', true );
				if (!empty($checkoutFields)) {
					foreach ((array) $checkoutFields as $key=>$value) {

						$key = $value['key'];
						$label = 'custom_' . strtolower(implode('_', explode(' ', strip_tags($value['label']))));
						$values = $value['value'];
						$option = $value['option_labels'];
		
						if (is_array($values) && count($option) > 0) {
							foreach ($values as $key=>$value) {
								$values[$key] = $option[$value];
							}
						} else if (count($option) > 0) {
							$values = $option[$values];
						}
						$purchase_params[$label] = $values;
					}
				}
			} catch (Exception $e) {
				;
			}
		

			// check if order ID should be prefixed
			if (!empty($this->prefix_order_ids)) {
				$purchase_params['order_id'] = $this->prefix_order_ids . (string) $purchase_params['order_id'];
			}

			// attach billing data to order
			$order_parameters_map = array(
			'billing_phone'           => 'billing_phone',
			'billing_city'            => 'billing_city',
			'billing_region'          => 'billing_state',
			'billing_postcode'        => 'billing_postcode',
			'billing_country'         => 'billing_country',
			'billing_address_1'  => 'billing_address_1',
			'billing_address_2'  => 'billing_address_2',
			'billing_company'         => 'billing_company',
			'shipping_phone'           => 'shipping_phone',
			'shipping_city'            => 'shipping_city',
			'shipping_region'          => 'shipping_state',
			'shipping_postcode'        => 'shipping_postcode',
			'shipping_country'         => 'shipping_country',
			'shipping_address_1'  => 'shipping_address_1',
			'shipping_address_2'  => 'shipping_address_2',
			'shipping_company'         => 'shipping_company',
			'shipping' => 'shipping',
			'billing' => 'billing'
			);

			foreach ($order_parameters_map as $k => $v) {
				$val = $this->object_property($order, 'order', $v);
				if (!empty($val)) {
					$purchase_params[$k] = $val;
				}
			}

			// attach coupons data
			$coupons_applied = method_exists($order, 'get_coupon_codes') ? $order->get_coupon_codes() : $order->get_used_coupons();
			if (count($coupons_applied) > 0) {
				$purchase_params['coupons'] = $coupons_applied;
			}

			// extra check for multicurrency websites
			$purchase_params = $this->check_for_multi_currency($purchase_params);

			// $user_id = $order->get_user_id();
			// $purchase_params = $this->getUserInfoBatch($purchase_params, $user_id);

			return $purchase_params;

		}

		/**
		 *
		 *
		 * WooCommerce 3.0 object method referencing
		 *
		 */

		public function object_property( $object, $type, $property) {
			if ( 'user' == $type) {
				  return $this->get_user_property($object, $property);
			}
			if ( 'order' == $type) {
				return $this->get_order_property($object, $property);
			}
			if ( 'variation' == $type) {
				   return $this->get_variation_property($object, $property);
			}
		}

		public function get_order_property( $object, $property) {
			switch ($property) {
				case 'id':
					return method_exists($object, 'get_id') ? $object->get_id() : $object->id;
				case 'order_number':
					return method_exists($object, 'get_order_number') ? $object->get_order_number() : $object->id;
				case 'payment_method_title':
					return method_exists($object, 'get_payment_method_title') ? $object->get_payment_method_title() : $object->payment_method_title;
				case 'billing_company':
					return method_exists($object, 'get_billing_company') ? $object->get_billing_company() : $object->billing_company;
				case 'billing_address_1':
					return method_exists($object, 'get_billing_address_1') ? $object->get_billing_address_1() : $object->billing_address_1;
				case 'billing_address_2':
					return method_exists($object, 'get_billing_address_2') ? $object->get_billing_address_2() : $object->billing_address_2;
				case 'billing_country':
					return method_exists($object, 'get_billing_country') ? $object->get_billing_country() : $object->billing_country;
				case 'billing_postcode':
					return method_exists($object, 'get_billing_postcode') ? $object->get_billing_postcode() : $object->billing_postcode;
				case 'billing_state':
					return method_exists($object, 'get_billing_state') ? $object->get_billing_state() : $object->billing_state;
				case 'billing_city':
					return method_exists($object, 'get_billing_city') ? $object->get_billing_city() : $object->billing_city;
				case 'billing_phone':
					return method_exists($object, 'get_billing_phone') ? $object->get_billing_phone() : $object->billing_phone;
				case 'shipping_phone':
					return method_exists($object, 'get_shipping_phone') ? $object->get_shipping_phone() : $object->shipping_phone;
				case 'shipping_city':
					return method_exists($object, 'get_shipping_city') ? $object->get_shipping_city() : $object->shipping_city;
				case 'shipping_state':
					return method_exists($object, 'get_shipping_state') ? $object->get_shipping_state() : $object->shipping_state;
				case 'shipping_postcode':
					return method_exists($object, 'get_shipping_postcode') ? $object->get_shipping_postcode() : $object->shipping_postcode;
				case 'shipping_country':
					return method_exists($object, 'get_shipping_country') ? $object->get_shipping_country() : $object->shipping_country;
				case 'shipping_address_1':
					return method_exists($object, 'get_shipping_address_1') ? $object->get_shipping_address_1() : $object->shipping_address_1;
				case 'shipping_address_2':
					return method_exists($object, 'get_shipping_address_2') ? $object->get_shipping_address_2() : $object->shipping_address_2;
				case 'shipping_company':
					return method_exists($object, 'get_shipping_company') ? $object->get_shipping_company() : $object->shipping_company;

			}
		}

		public function get_user_property( $object, $property) {
			switch ($property) {
				case 'id':
					return method_exists($object, 'get_id') ? $object->get_id() : $object->id;
			}
		}

		public function get_variation_property( $object, $property) {
			switch ($property) {
				case 'price':
					return method_exists($object, 'get_price') ? $object->get_price() : $object->price;
				case 'image':
					return method_exists($object, 'get_image') ? $object->get_image() : $object->image_id;
			}
		}

		/**
		 *
		 *
		 * WooCommerce Subscriptions tracking
		 *
		 */


		public function has_wcs() {
			return class_exists('WC_Subscriptions');
		}

		public function get_wcs_version() {
			return $this->has_wcs() && !empty( WC_Subscriptions::$version ) ? WC_Subscriptions::$version : null;
		}

		public function is_wcs_2() {
			return $this->has_wcs() && version_compare($this->get_wcs_version(), '2.0-beta-1', '>=');
		}


		/**
			 *
			 *
			 * Tracking code rendering
			 *
			 *
			 */


		public function render_events() {
			include_once(RICHPANEL_PLUGIN_PATH . '/render_tracking_events.php');
		}

		// public function render_cookie(){
		// 	// error_log('rendering cookie');
		// 	// error_log(print_r($this->cookie_to_set,true));
		// 	include_once(RICHPANEL_PLUGIN_PATH.'/render_setting_cookie.php');
		// }

		public function render_footer_events() {
			include_once(RICHPANEL_PLUGIN_PATH . '/render_footer_tracking_events.php');
		}

		public function render_snippet() {
			// check if we should track data for this user (if user is available)
			if ( !is_admin() && is_user_logged_in()) {
				$user = wp_get_current_user();
				if ($user->roles && $this->ignore_for_roles) {
					foreach ($user->roles as $r) {
						if (in_array($r, $this->ignore_for_roles)) {
							$this->accept_tracking = false;
						}
					}
				}
			}

			// render the JS tracking code
			include_once(RICHPANEL_PLUGIN_PATH . '/js.php');
			return null;
		}


		/**
		 *
		 *
		 * Session and cookie handling
		 *
		 *
		 */

		public function session_get( $k) {
			if (!is_object($this->woo->session)) {
				return isset($_COOKIE[$k]) ? filter_var($_COOKIE[$k], FILTER_SANITIZE_STRING) : false;
			}
			return $this->woo->session->get($k);
		}

		public function session_set( $k, $v, $time = 43200) {
			if (!is_object($this->woo->session)) {
				setcookie($k, $v, time() + $time, COOKIEPATH, COOKIE_DOMAIN);
				$_COOKIE[$k] = $v;
				return true;
			}
			return $this->woo->session->set($k, $v);
		}

		public function add_item_to_cookie( $data) {
			$items = $this->get_items_in_cookie();
			if (empty($items)) {
				$items = array();
			}
			array_push($items, $data);
			$encoded_items = wp_json_encode($items);
			$this->session_set($this->get_cookie_name(), $encoded_items);
		}

		public function get_items_in_cookie() {
			$items = array();
			$data = $this->session_get($this->get_cookie_name());
			if (!empty($data)) {
				if (get_magic_quotes_gpc()) {
					$data = stripslashes($data);
				}
				$items = json_decode($data, true);
			}
			return $items;
		}

		public function get_identify_data_in_cookie() {
			$identify = array();
			$data = $this->session_get($this->get_do_identify_cookie_name());
			if (!empty($data)) {
				if (get_magic_quotes_gpc()) {
					$data = stripslashes($data);
				}
				$identify = json_decode($data, true);
			}
			return $identify;
		}

		public function clear_items_in_cookie() {
			$this->session_set($this->get_cookie_name(), wp_json_encode(array()));
			$this->session_set($this->get_do_identify_cookie_name(), wp_json_encode(array()));
		}

		public function get_order_ip( $order_id) {
			$ip_address = get_post_meta($order_id, '_customer_ip_address', true);
			if (strpos($ip_address, '.') !== false) {
				return $ip_address;
			}
			return false;
		}

		private function get_cookie_name() {
			return 'richpanelqueue_' . COOKIEHASH;
		}

		private function get_identify_cookie_name() {
			return 'richpanelid_' . COOKIEHASH;
		}

		private function get_do_identify_cookie_name() {
			return 'richpaneldoid_' . COOKIEHASH;
		}


		public function check_for_richpanel_clear() {
			if (!empty($_REQUEST) && !empty($_REQUEST['richpanel_clear'])) {
				$this->clear_items_in_cookie();
				wp_send_json_success();
			}
		}

		public function process_cookie_events() {
			$items = $this->get_items_in_cookie();
			if (count($items) > 0) {
				$this->has_events_in_cookie = true;
				foreach ($items as $event) {
					// put event in queue for sending to the JS library
					$this->put_event_in_queue($event['method'], $event['event'], $event['properties']);
				}
			}

			// check if identify data resides in the session
			$identify_data = $this->get_identify_data_in_cookie();
			if (!empty($identify_data)) {
				$this->identify_call_data = $identify_data;
			}

		}


		/**
		 * Settings compatibility with previous versin - fetch api key from WP options pool
		 */

		public function get_previous_version_settings_key() {
			$api_key = false;

			// fetch settings
			$settings = get_option('richpanel_woo_analytics');
			if (!empty($settings) && !empty($settings['api_token'])) {
				$api_key = $settings['api_token'];
			}
			return $api_key;
		}

		/**
		 * Initialize integration settings form fields.
		 */
		public function init_form_fields() {

			// initiate possible user roles from settings
			$possible_ignore_roles = false;

			if (is_admin()) {
				global $wp_roles;
				$possible_ignore_roles = array();
				foreach ($wp_roles->roles as $role => $stuff) {
					$possible_ignore_roles[$role] = $stuff['name'];
				}
			}

			$this->form_fields = array(
				'api_key' => array(
			'title'             => __( 'API Token', 'richpanel-woo-analytics' ),
			'type'              => 'text',
			'description'       => __( '<strong style="color: green;">(Required)</strong> Enter your Richpanel API token. You can find it under "Settings" in your Richpanel account.<br /> Don\'t have one? <a href="https://www.richpanel.com/signup?ref=woointegration" target="_blank">Sign-up for free</a> now, it only takes a few seconds.', 'richpanel-woo-analytics' ),
			'desc_tip'          => false,
			'default'           => ''
				),
				'api_secret' => array(
			'title'             => __( 'API Secret Key', 'richpanel-woo-analytics' ),
			'type'              => 'text',
			'description'       => __( '<strong style="color: green;">(Required)</strong> Enter your Richpanel API secret key.', 'richpanel-woo-analytics' ),
			'desc_tip'          => false,
			'default'           => ''
				)
			);

			// if($possible_ignore_roles){
			// 	$this->form_fields['ignore_for_roles'] = array(
			// 		'title'             => __( 'Ignore tracking for roles', 'richpanel-woo-analytics' ),
			// 		'type'              => 'multiselect',
			// 		'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you check any of the roles, tracking data will be ignored for WP users with this role', 'richpanel-woo-analytics' ),
			// 		'desc_tip'          => false,
			// 		'default'           => '',
			// 		'options'			=> $possible_ignore_roles
			// 	);
			// }
			// $this->form_fields['ignore_for_events'] = array(
			// 'title'             => __( 'Do not send the selected tracking events', 'richpanel-woo-analytics' ),
			// 'type'              => 'multiselect',
			// 'description'       => __( '<strong style="color: #999;">(Optional)</strong> Tracking won\'t be sent for the selected events', 'richpanel-woo-analytics' ),
			// 'desc_tip'          => false,
			// 'default'           => '',
			// 'options'			=> $this->possible_events
			// );



			// $product_brand_taxonomy_options = array('none' => 'None');
			// foreach(wc_get_attribute_taxonomies() as $v){
			// 	$product_brand_taxonomy_options[$v->attribute_name] = $v->attribute_label;
			// }


			// $this->form_fields['product_brand_taxonomy'] = array(
			// 	'title'             => __( 'Product brand attribute', 'richpanel-woo-analytics' ),
			// 	'type'              => 'select',
			// 	'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you check any of those attributes, it\'ll be synced with Richpanel as the product\'s brand' ),
			// 	'desc_tip'          => false,
			// 	'default'           => '',
			// 	'options'						=> $product_brand_taxonomy_options
			// );

			// $this->form_fields['send_roles_as_tags'] = array(
			// 	'title'             => __( 'Send user roles as tags', 'richpanel-woo-analytics' ),
			// 	'type'              => 'checkbox',
			// 	'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you check this, your user\'s roles will be sent to Richpanel as tags when they browse your website' ),
			// 	'desc_tip'          => false,
			// 	'label'							=> 'Send roles as tags',
			// 	'default'           => false
			// );

			// $this->form_fields['add_tag_to_every_customer'] = array(
			// 		'title'             => __( 'Add this tag to every customer', 'richpanel-woo-analytics' ),
			// 		'type'              => 'text',
			// 		'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you enter tag, it will be added to every customer synced with Richpanel' ),
			// 		'desc_tip'          => false,
			// 		'label'							=> 'Add this tag to every customer in Richpanel',
			// 		'default'           => ''
			// 	);

			// $this->form_fields['prefix_order_ids'] = array(
			// 		'title'             => __( 'Prefix order IDs with', 'richpanel-woo-analytics' ),
			// 		'type'              => 'text',
			// 		'description'       => __( '<strong style="color: #999;">(Optional)</strong> If you enter a prefix, all your order IDs will be prefixed with it. Useful for multiple stores connected to one Richpanel account' ),
			// 		'desc_tip'          => false,
			// 		'label'							=> 'Prefix all your order IDs',
			// 		'default'           => ''
			// 	);
		}

	}

endif;
