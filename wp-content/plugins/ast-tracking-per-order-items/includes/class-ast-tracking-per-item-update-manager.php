<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AST_Tracking_Per_Item_Update_Manager {
		
	var $store_url = 'https://www.zorem.com/';	
	
	/**
	 * Initialize the main plugin function
	*/
    public function __construct( $current_version, $pluginFile, $slug = '' ) {
		$this->slug	= $slug;	
		$this->plugin = $pluginFile;		
		$this->current_version = $current_version;
		$this->init();		
		$this->cachedInstalledVersion = null;
	}
	
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Get the class instance
	 *
	 * @return wc_advanced_shipment_tracking_by_products_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init(){
		
		//Insert our update info into the update array maintained by WP
        add_filter('site_transient_update_plugins', array($this,'check_update')); //WP 3.0+
        add_filter('transient_update_plugins', array($this,'check_update')); //WP 2.8+		
		add_action( 'in_plugin_update_message-'.$this->plugin, array($this,'addUpgradeMessageLink') );						
		add_action( 'upgrader_process_complete', array($this,'after_update'), 10, 2 );

	}
		
	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 *
	 * @return object $ transient
	 */
	public function check_update( $transient ) {
		
		//delete_transient( 'zorem_upgrade_'.$this->slug );
		
		if ( empty($transient->checked ) ) {
			return $transient;
		}
		
		// trying to get from cache first, to disable cache comment 10,20,21,22,24
		if( false == $remote_update = get_transient( 'zorem_upgrade_'.$this->slug ) ) {	
			// info.json is the file with the actual plugin information on your server
			$remote_update = $this->getRemote_update();	
		}				
		
		if($remote_update){
			$data = json_decode( wp_remote_retrieve_body( $remote_update ) );
			// If a newer version is available, add the update
			$remote_version = $data->data->package->new_version;
			
			if ( version_compare( $this->current_version, $remote_version, '<' ) ) {								
				$obj = new stdClass();
				$obj->slug = $this->slug;
				$obj->new_version = $remote_version;
				$obj->plugin = $this->plugin;				
				$obj->package = $data->data->package->package;
				$obj->tested = $data->data->package->tested;
				$transient->response[ $this->plugin ] = $obj;
			}	
		}		
		return $transient;
	}		
	
	/**
	 * Return the remote update
	 *
	 * @return string $remote_update
	 */
	public function getRemote_update() {
		
		// FIX SSL SNI
		$filter_add = true;
		if ( function_exists( 'curl_version' ) ) {
			$version = curl_version();
			if ( version_compare( $version['version'], '7.18', '>=' ) ) {
				$filter_add = false;
			}
		}
		if ( $filter_add ) {
			add_filter( 'https_ssl_verify', '__return_false' );
		}	
		
		$instance_id = wc_advanced_shipment_tracking_by_products()->license->get_instance_id();
		
		$domain = home_url();
		
		$api_params = array(
			'wc-api' => 'wc-am-api',
			'wc_am_action' => 'update',
			'instance' => $instance_id,
			'object' => $domain,
			'product_id' => wc_advanced_shipment_tracking_by_products()->license->get_product_id(),
			'api_key' => wc_advanced_shipment_tracking_by_products()->license->get_license_key(),
			'plugin_name' => $this->plugin,
			'version' => $this->current_version,
		);
		
		$request = add_query_arg( $api_params, $this->store_url );

		$response = wp_remote_get( $request, array( 'timeout' => 15, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) )
			return false;
				
		$authorize_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		if ( $filter_add ) {
			remove_filter( 'https_ssl_verify', '__return_false' );
		}
		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
			
			set_transient( 'zorem_upgrade_'.$this->slug, $response, 43200 ); // 12 hours cache
			return $response;
		}

		return false;
	}
	
	/**
	 * Shows message on Wp plugins page with a link for updating from zorem.
	 */
	public function addUpgradeMessageLink() {		
		
		if ( wc_advanced_shipment_tracking_by_products()->license->get_license_status() )return;
		
		$url = admin_url( 'admin.php?page=woocommerce-advanced-shipment-tracking&tab=addons' );
		
		echo sprintf( ' ' . esc_html__( 'To receive automatic updates license activation is required. Please visit %ssettings%s to activate your Tracking Per Item Add-on.', 'tpi' ), '<a href="' . esc_url( $url ) . '" target="_blank">', '</a>' );
		
	}
	
	/**
	 *
	 * after update
	 *
	 */
	public function after_update( $upgrader_object, $options ) {
		if ( $options['action'] == 'update' && $options['type'] === 'plugin' )  {
			// just clean the cache when new plugin version is installed
			delete_transient( 'zorem_upgrade_'.$this->slug );
		}
	}
}
