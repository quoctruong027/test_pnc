<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Richpanel_Import' ) ) :


	class Richpanel_Import {


		private $orders_list = array();
		private $orders_total = 0;
		private $subscription_total = 0;
		private $importing = false;
		private $s_importing = false;

		public function prepare_import() {

			global $wpdb;

			$date = date('Y-m-d'); //Today
			$dateMinus12 = date('Y-m-d', strtotime('-24 Months')); // Last day 24 months ago
			// $dateMinus12 = date("Y-m-d", strtotime("-5 years")); // Last day 12 months ago

			// prepare how many orders should be imported
			// $this->orders_total = (int)$wpdb->get_var("select count(id) from {$wpdb->posts} where post_type = 'shop_order' AND post_date >= '{$dateMinus12}' order by id asc");
			$this->orders_total = (int) $wpdb->get_var($wpdb->prepare("select count(id) from `$wpdb->posts` where post_type = 'shop_order' AND post_date >= %s order by id asc", $dateMinus12));

			// $this->subscription_total = (int)$wpdb->get_var("select count(id) from {$wpdb->posts} where post_type = 'shop_subscription' AND post_date >= '{$dateMinus12}' order by id asc");
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

		public function output() {
			include_once( plugin_dir_path(__FILE__) . 'views/richpanel_import_view.php' );
		}

	}





endif;

return new Richpanel_Import();
