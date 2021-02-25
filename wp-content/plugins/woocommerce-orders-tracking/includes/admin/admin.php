<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_ADMIN {
	protected $settings;

	public function __construct() {
		$this->settings = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
		add_action( 'init', array( $this, 'init' ) );
		add_filter(
			'plugin_action_links_woocommerce-orders-tracking/woocommerce-orders-tracking.php', array(
				$this,
				'settings_link'
			)
		);
	}

	public static function query_order_item_meta( $args1 = array(), $args2 = array(), $limit = 0 ) {
		global $wpdb;
		$sql  = "SELECT * FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta WHERE woocommerce_order_items.order_item_id=woocommerce_order_itemmeta.order_item_id";
		$args = array();
		if ( count( $args1 ) ) {
			foreach ( $args1 as $key => $value ) {
				if ( is_array( $value ) ) {
					$sql .= " AND woocommerce_order_items.{$key} IN (" . implode( ', ', array_fill( 0, count( $value ), '%s' ) ) . ")";
					foreach ( $value as $v ) {
						$args[] = $v;
					}
				} else {
					$sql    .= " AND woocommerce_order_items.{$key}='%s'";
					$args[] = $value;
				}
			}
		}
		if ( count( $args2 ) ) {
			foreach ( $args2 as $key => $value ) {
				if ( is_array( $value ) ) {
					$sql .= " AND woocommerce_order_itemmeta.{$key} IN (" . implode( ', ', array_fill( 0, count( $value ), '%s' ) ) . ")";
					foreach ( $value as $v ) {
						$args[] = $v;
					}
				} else {
					$sql    .= " AND woocommerce_order_itemmeta.{$key}='%s'";
					$args[] = $value;
				}
			}
		}
		if ( $limit ) {
			$sql .= " LIMIT 0,{$limit}";
		}
		$query      = $wpdb->prepare( $sql, $args );
		$line_items = $wpdb->get_results( $query, ARRAY_A );

		return $line_items;
	}

	public function settings_link( $links ) {
		$settings_link = '<a href="' . esc_url(admin_url( 'admin.php' )) . '?page=woocommerce-orders-tracking" title="' . esc_attr__( 'Settings', 'woocommerce-orders-tracking' ) . '">' . esc_html__( 'Settings', 'woocommerce-orders-tracking' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-orders-tracking' );
		load_textdomain( 'woocommerce-orders-tracking', VI_WOOCOMMERCE_ORDERS_TRACKING_LANGUAGES . "woocommerce-orders-tracking-$locale.mo" );
		load_plugin_textdomain( 'woocommerce-orders-tracking', false, VI_WOOCOMMERCE_ORDERS_TRACKING_LANGUAGES );

	}

	public function init() {
		$this->load_plugin_textdomain();
		if(class_exists('VillaTheme_Support_Pro')){
			new VillaTheme_Support_Pro(
				array(
					'support'   => 'https://villatheme.com/supports/forum/plugins/woocommerce-orders-tracking/',
					'docs'      => 'http://docs.villatheme.com/?item=woocommerce-orders-tracking',
					'review'    => 'https://codecanyon.net/downloads',
					'css'       => VI_WOOCOMMERCE_ORDERS_TRACKING_CSS,
					'image'     => VI_WOOCOMMERCE_ORDERS_TRACKING_IMAGES,
					'slug'      => 'woocommerce-orders-tracking',
					'menu_slug' => 'woocommerce-orders-tracking',
					'version'   => VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION,
				)
			);
		}
	}
}