<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Plugin' ) ) {

	class XT_Framework_Plugin {

		public $version;
		public $name;
		public $menu_name;
		public $url;
		public $icon;
		public $slug;
		public $prefix;
		public $short_prefix;
		public $market;
		public $markets = array();
		public $market_product;
		public $dependencies = array();
		public $conflicts = array();
		public $top_menu = false;
		public $file;

		public function __construct( $params ) {

			$params                 = json_decode( json_encode( $params ) );
			$params->market_product = $params->markets->{$params->market};

			foreach ( $params as $attribute => $value ) {

				$this->{$attribute} = $value;
			}
		}

		public function version() {
			return $this->version;
		}

		public function name() {
			return $this->name;
		}

		public function menu_name() {
			return $this->menu_name;
		}

		public function url() {
			return $this->url;
		}

		public function icon() {
			return $this->icon;
		}

		public function slug() {
			return $this->slug;
		}

		public function prefix() {
			return $this->prefix;
		}

		public function short_prefix() {
			return $this->short_prefix;
		}

		public function market() {
			return $this->market;
		}

		public function markets() {
			return $this->markets;
		}

		public function market_product() {
			return $this->market_product;
		}

		public function dependencies() {
			return $this->dependencies;
		}

		public function conflicts() {
			return $this->conflicts;
		}

		public function top_menu() {
			return $this->top_menu;
		}

		public function file() {
			return $this->file;
		}

		public function __call( $name, $arguments ) {
			if ( isset( $this->{$name} ) ) {
				return $this->{$name};
			} else {
				return null;
			}
		}
	}
}