<?php
/**
 * @wordpress-plugin
 * Plugin Name: Tracking Per Item Add-on
 * Plugin URI: https://www.zorem.com/shop/tracking-per-item-ast-add-on/ 
 * Description: The tracking per item add-on extends the AST plugin and allows you to attach tracking numbers to order line items and Line item quantities.
 * Version: 1.3.8
 * Author: zorem
 * Author URI: https://zorem.com 
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * WC tested up to: 4.7.0
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package zorem
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ast_woo_advanced_shipment_tracking_by_products {
	
	/**
	 * WooCommerce Advanced Shipment Tracking by Product version.
	 *
	 * @var string
	 */
	public $version = '1.3.8';
	
	/**
	 * Initialize the main plugin function
	*/
    public function __construct() {
		
		$this->plugin_file = __FILE__;
		// Add your templates to this array.		
							
		if ( $this->is_wc_active() && $this->is_ast_active() && $this->ast_version_check()) {	
			
			$this->includes();
			
			//start adding hooks
			$this->init();	

		}				
    }
	
	/**
	 * Check if Advanced Shipment Tracking for WooCommerce is active
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_ast_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		
		if ( is_plugin_active( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}		

		// Do the WC active check
		if ( false === $is_active ) {
			add_action( 'admin_notices', array( $this, 'notice_activate_ast' ) );
		}		
		return $is_active;
	}
	
	/**
	 * Check if Advanced Shipment Tracking for WooCommerce is active
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool
	*/
	private function ast_version_check() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' );
		$ast_version = $plugin_data['Version'];
		
		if (version_compare( $ast_version , '2.7.4', '>=')) {
			$is_version = true;
		} else {
			$is_version = false;
		}		

		// Do the WC active check
		if ( false === $is_version ) {
			add_action( 'admin_notices', array( $this, 'notice_update_ast' ) );
		}		
		return $is_version;
	}
	
	/**
	 * Check if Ad is active
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_wc_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}		

		// Do the WC active check
		if ( false === $is_active ) {
			add_action( 'admin_notices', array( $this, 'notice_activate_wc' ) );
		}		
		return $is_active;
	}
	
	/**
	 * Include files
	*/
	public function includes(){
		
		//license
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-ast-tracking-per-item-license-manager.php';				
		$this->license = AST_Tracking_Per_Item_License_Manager::get_instance();
		
		//update-manager	
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-ast-tracking-per-item-update-manager.php';
		new AST_Tracking_Per_Item_Update_Manager (
			$this->version,
			'ast-tracking-per-order-items/ast-tracking-per-order-items.php',
			$this->license->get_item_code()
		);
		
	}
	
	/**
	 * Display WC active notice
	 *
	 * @access public
	 * @since  1.0.0
	*/
	public function notice_activate_wc() {
		?>
		<div class="error">
			<p><?php printf( __( 'Please install and activate %sWooCommerce%s for Tracking Per Item Add-on!', 'woo-advanced-shipment-tracking' ), '<a href="' . admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Display AST active notice
	 *
	 * @access public
	 * @since  1.0.0
	*/
	public function notice_activate_ast() {
		?>
		<div class="error">		
			<p><?php printf( __( 'You must install and activate the %sAdvanced Shipment Tracking%s plugin for the Tracking Per item add-on to work', 'woo-advanced-shipment-tracking' ), '<a href="' . admin_url( 'plugin-install.php?s=ast+zorem&tab=search&type=term' ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Display AST active notice
	 *
	 * @access public
	 * @since  1.0.0
	*/
	public function notice_update_ast() {
		?>
		<div class="error">
			<p><?php _e( 'Please Update Advanced Shipment Tracking plugin for the Tracking Per item add-on to work' , 'woo-advanced-shipment-tracking' ); ?></p>
		</div>
		<?php
	}
	
	/*
	* init when class loaded
	*/
	public function init(){			
		
		add_action( 'ast_tracking_form_between_form', array( $this, 'ast_tracking_form_products' ), 10, 1 );
		add_action( 'ast_tracking_form_end_meta_box', array( $this, 'ast_tracking_form_include_js' ), 1, 1 );
		add_action( 'wp_ajax_wc_shipment_tracking_save_form', array( $this, 'save_meta_box_ajax' ), 1 );
		add_action( 'wp_ajax_add_inline_tracking_number', array( $this, 'save_inline_tracking_number' ), 1 );					
		add_action(	'ast_after_tracking_number',array( $this, 'ast_after_tracking_number_fun' ),10,2);
		add_action(	'ast_tracking_email_header', array( $this, 'ast_tracking_email_header_fun' ),10,2 );
		add_action(	'ast_tracking_email_body', array( $this, 'ast_tracking_email_body_fun' ),10,3 );
		add_action(	'ast_tracking_simple_list_email_body', array( $this, 'ast_tracking_simple_list_email_body_fun' ),10,2 );		
		add_action(	'ast_tracking_my_acoount_header', array( $this, 'ast_tracking_my_acoount_header_fun' ),10,2 );
		add_action(	'ast_tracking_my_account_body', array( $this, 'ast_tracking_my_account_body_fun' ),10,3 );	
		add_action( 'woocommerce_before_order_itemmeta', array( $this, 'before_order_itemmeta'), 10, 3 );				
		add_action( 'woocommerce_order_item_meta_end', array( $this, 'action_woocommerce_order_item_meta_end'), 10, 3 ); 			
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 10);
		add_action( 'ast_addon_license_form', array( $this, 'tracking_per_item_addon_license_form' ), 1);
		add_action( 'init', array( $this, 'update_database_check'));		
		//add_action( 'ast_generat_settings_end', array( $this, 'ast_general_settings_html'));
		//ajax save admin api settings
		add_action( 'wp_ajax_tpi_settings_form_update', array( $this, 'tpi_settings_form_update_callback' ) );	

		if( !$this->license->get_license_status() )add_action( 'admin_notices', array( $this, 'tracking_per_item_licence_notice') );	
		
		add_action( 'ast_api_create_item_arg', array( $this, 'ast_api_create_item_arg_callback'), 10, 2);
		
		add_filter( 'ast_add_tracking_options', array( $this, 'ast_add_tracking_options'));
		add_action( 'trackship_tracking_header_before', array( $this, 'trackship_tracking_header_before_callback'), 10, 4);
	}		
	
	/*
	* functions for add options in AST settings
	*/
	public function ast_general_settings_html(){
		require_once( 'views/tpi_admin_options.php' );		
	}
	
	/*
	* functions for add options in AST settings
	*/
	public function tpi_general_settings_options(){
		$form_data = array(		
			'enable_tpi_by_default' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the Tracking Per Item option by default', 'woo-advanced-shipment-tracking' ),				
				'tooltip'   => __( 'This will check the option to add tracking per item when adding tracking info to orders', 'woo-advanced-shipment-tracking' ),
				'show'		=> true,
				'class'     => '',
			),
			'display_sku_for_tpi' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Display SKU for the Tracking Per Item', 'woo-advanced-shipment-tracking' ),				
				'show'		=> true,
				'class'     => '',
			),			
		);
		
		return $form_data;
	}
	
	/*
	* functions for add options in AST settings
	*/
	public function ast_add_tracking_options( $ast_add_tracking_options ){
		$tpi_options = array( 
			"enable_tpi_by_default" => __( 'Enable the Tracking Per Item option by default', 'woo-advanced-shipment-tracking' ),
			"display_sku_for_tpi" => __( 'This will check the option to add tracking per item when adding tracking info to orders', 'woo-advanced-shipment-tracking' )
		);
		$form_data = array_merge( $ast_add_tracking_options, $tpi_options );
		return $form_data;
	}
	
	
	
	/*
	* settings form save
	*/
	function tpi_settings_form_update_callback(){

		if ( ! empty( $_POST ) && check_admin_referer( 'tpi_settings_form', 'tpi_settings_form_nonce' ) ) {
			
			$data = $this->tpi_general_settings_options();						
			
			foreach( $data as $key => $val ){				
				if(isset($_POST[ $key ])){						
					update_option( $key, wc_clean($_POST[ $key ]) );
				}
			} 						
		}
	}
	
	/*
	* functions for add products table in tracking form
	*/
	public function ast_tracking_form_products($order_id){		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order = new WC_Order($order_id);
		$items = $order->get_items();
		
		$total_items = count($items);
		if($total_items == 1){
			foreach($items as $item){
				$qty = $item->get_quantity();
				if($qty == 1){
					return;
				}
			}
		}
		
		$product_list = array();
		$tracking_items = $wast->get_tracking_items( $order_id );		
		
		foreach($tracking_items as $tracking_item){			
			if(isset($tracking_item['products_list'])){
				$product_list[] = $tracking_item['products_list'];				
			}
		}
		
		$all_list = array();
		foreach($product_list as $list){			
			foreach($list as $in_list){
				if(isset($all_list[$in_list->product])){
					$all_list[$in_list->product] = (int)$all_list[$in_list->product] + (int)$in_list->qty;							
				} else{
					$all_list[$in_list->product] = $in_list->qty;	
				}
			}				
		}		
		?>
		<div class="ast_tracking_item_div">
			<?php
			$enable_tpi_by_default = get_option('enable_tpi_by_default',0);
			$checked = '';
			if($enable_tpi_by_default == 1)$checked = 'checked';
			?>
			
			<h2 class="product-table-header">
				<input type="checkbox" name="enable_tracking_per_item" class="enable_tracking_per_item" value="1" <?php echo $checked; ?>><?php _e( 'Tracking Per Item', 'woo-advanced-shipment-tracking'); ?>
			</h2>
			
			<table class="wp-list-table widefat fixed posts ast-product-table" style="<?php if($enable_tpi_by_default != 1){echo 'display:none;';}?>margin-bottom: 0.5em;margin-top:0;">			
				<?php $items = $order->get_items();?>
				<tbody>
					<?php 
					$n = 0;
					$total_product = count($items);
					
					foreach($items as $item){													
						$product = $item->get_product();
						$checked = 0;
						$qty = $item->get_quantity();
						
						$variation_id = $item->get_variation_id();
						$product_id = $item->get_product_id();					
						if($variation_id != 0){
							$product_id = $variation_id;
						}
											
						if(array_key_exists($product_id,$all_list)){	
							if(isset($all_list[$product_id])){									  
								$qty = (int)$item->get_quantity() - (int)$all_list[$product_id];
								if($all_list[$product_id] == $item->get_quantity()){
									$checked = 1;										
								}
							}
						}					
					?>
						<tr class="ASTProduct_row <?php if($qty == 0){ echo 'disable_row'; } ?>">
							<td>
								<?php echo $item->get_name();
									$display_sku_for_tpi = get_option('display_sku_for_tpi',0);
									$item_sku = '';
									if($item->get_product_id()){
										$product = wc_get_product($item->get_product_id());																		
										$item_sku = $product->get_sku();
									}
									
								if($display_sku_for_tpi == 1){ ?>
								<br/><span class="ASTProduct_sku"><?php _e( 'SKU:', 'woo-advanced-shipment-tracking');?> <?php echo $item_sku; ?></span>
								<?php } ?>
								<input type="hidden" value="<?php echo $item->get_name(); ?>" name="ASTProduct[<?php echo $n; ?>][title]">
								<input type="hidden" class="product_id" value="<?php echo $product_id; ?>" name="ASTProduct[<?php echo $n; ?>][product]">
							</td>
							<td style="">
							<div class="value-button" id="decrease" value="Decrease Value">-</div>
							<input type="number" class="ast_product_number" name="ASTProduct[<?php echo $n; ?>][qty]" min="0" max="<?php echo $qty; ?>" oninput="(validity.valid)||(value='');"  value="<?php echo $qty; ?>" />
							<div class="value-button" id="increase" value="Increase Value">+</div>						
							</td>
						</tr>	
					<?php $n++; } ?>						
				</tbody>			
			</table>			
		</div>
		<div class="qty_validation"><?php _e( 'Please choose at least one item quantity', 'woo-advanced-shipment-tracking'); ?></div>
		<style>
		.product-table-header{
			background: #f9f9f9;
			border: 1px solid #e0e0e0;
			margin: 10px 0 0;			
			padding: 15px 5px;
			font-size: 14px;
			font-weight: 400;
		}
		table.widefat.ast-product-table{
			border: 1px solid #e0e0e0;
		}
		.add_tracking_number_form input[type=checkbox].enable_tracking_per_item{
			margin: 0px 8px 0 0px;
			vertical-align: bottom;
		}
		.add_tracking_number_form .ast-product-table input[type=checkbox]{
			margin: 0px 4px 0 4px;
		}
		.ast-product-table tr.ASTProduct_row td{
			border-bottom: 1px solid #e0e0e0;
		}
		.ast-product-table tr.ASTProduct_row tr:last-child td{
			border-bottom: 0;
		}
		.ast-product-table tr.ASTProduct_row td:first-child{			
			padding-left: 5px;
		}
		.ast-product-table tr.ASTProduct_row td:last-child{			
			padding-right: 5px;
			text-align: right;
		}
		.ast-product-table tr.ASTProduct_row.disable_row{
			background-color: #efefef;
			opacity: 0.7;
			cursor: not-allowed !important;
		}
		.ast-product-table tr.ASTProduct_row.disable_row td{			
			cursor: not-allowed !important;
		}
		.ast-product-table tr.ASTProduct_row td{
			padding: 5px;
			line-height: 20px;
		}
		.qty_validation{
			display:none;
			color: red;
			margin-bottom: 5px;			
		}
		.ast-product-table .value-button {
			display: inline-block;
			border: 1px solid #ccd0d4;
			margin: 0px;
			width: 20px;
			height: 28px;
			line-height: 28px;			
			text-align: center;
			vertical-align: top;
			padding: 0px 0;
			background: #eee;
			-webkit-touch-callout: none;
			-webkit-user-select: none;
			-khtml-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}
			
		.ast-product-table .value-button:hover {
			cursor: pointer;
		}
		.ast-product-table tr.ASTProduct_row.disable_row .value-button:hover{
			cursor: not-allowed;
		}	
		.ast-product-table #decrease {
			margin-right: -3px;			
		}
			
		.ast-product-table #increase {
			margin-left: -4px;			
		}
			
		.ast-product-table #input-wrap {
			margin: 0px;
			padding: 0px;
		}
		.ast-product-table input.ast_product_number {
			text-align: center;
			border: none;
			border-top: 1px solid #ccd0d4;
			border-bottom: 1px solid #ccd0d4;
			margin: 0px;
			width: 30px;
			padding: 0;
			border-radius: 0;			
		}
		.ast-product-table input.ast_product_number:focus {			
			outline: none;
			box-shadow: none;
		}
		.ast-product-table input.ast_product_number[readonly]{
			background-color: #fff;
		}
		.ast-product-table tr.ASTProduct_row.disable_row input.ast_product_number[readonly]{
			cursor: not-allowed;
		}
		.ast-product-table input[type=number]::-webkit-inner-spin-button,
		.ast-product-table input[type=number]::-webkit-outer-spin-button {
			-webkit-appearance: none;
			margin: 0;
		}
		.ast_tracking_item_div {
			margin-bottom: 10px;
		}
		</style>
		<script>		
		jQuery(document).on("click", "#decrease", function(){			
			var input = jQuery(this).next(".ast_product_number");			
			var value = jQuery(this).next(".ast_product_number").val();
			
			if(value > input.attr('min')) {
				value = isNaN(value) ? 0 : value;				
				value < 1 ? value = 1 : '';
				value--;				
				jQuery(input).val(value);
			}
		});
		jQuery(document).on("click", "#increase", function(){			
			var input = jQuery(this).prev(".ast_product_number");			
			var value = jQuery(this).prev(".ast_product_number").val();
			
			if(value < input.attr('max')) {
				value = isNaN(value) ? 0 : value;
				value++;			
				jQuery(input).val(value);
			}
		});
		jQuery(document).on("change", ".enable_tracking_per_item", function(){	
			if(jQuery(this).prop("checked") == true){
				jQuery( this ).closest('.ast_tracking_item_div').find( ".ast-product-table" ).show();					
			} else{
				jQuery( this ).closest('.ast_tracking_item_div').find( ".ast-product-table" ).hide();				
			}
		});
		</script>
	<?php	
	}
	
	/**	 
	 * Function for include css and js
	 */
	public function ast_tracking_form_include_js(){			
		wp_enqueue_style( 'shipment_tracking_by_products_styles',  plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking_by_products()->version );		
	}		
	
	public function admin_styles($hook){
		if(!isset($_GET['page'])) {
			return;
		}
		if( $_GET['page'] != 'woocommerce-advanced-shipment-tracking') {
			return;
		}
		wp_enqueue_script( 'shipment_tracking_by_products_script', plugin_dir_url( __FILE__ ).'assets/js/admin.js' , array( 'jquery', 'wp-util' ), wc_advanced_shipment_tracking_by_products()->version );	
		wp_localize_script( 'shipment_tracking_by_products_script', 'tpi_ajax_object', array( 
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'i18n' => array(				
				'data_saved'	=> __( 'Data saved successfully.', 'woo-advanced-shipment-tracking' ),				
			),	
		) );
	}
	
	/**
	 * Order Tracking Save AJAX
	 *
	 * Function for saving tracking items via AJAX
	 */
	public function save_meta_box_ajax() {		
		check_ajax_referer( 'create-tracking-item', 'security', true );		
		
		$tracking_number = str_replace(' ', '', $_POST['tracking_number']);				
		
		if ( isset( $_POST['tracking_number'] ) &&  $_POST['tracking_provider'] != '' && isset( $_POST['tracking_provider'] ) && strlen( $_POST['tracking_number'] ) > 0 ) {
	
			$order_id = wc_clean( $_POST['order_id'] );
			$order = new WC_Order($order_id);
			$tracking_product_code = isset($_POST['tracking_product_code']) ? $_POST['tracking_product_code'] : "";
			$args = array(
				'tracking_provider'        => wc_clean( $_POST['tracking_provider'] ),
				'tracking_number'          => wc_clean( $_POST['tracking_number'] ),
				'tracking_product_code'    => wc_clean( $tracking_product_code ),
				'date_shipped'             => wc_clean( $_POST['date_shipped'] ),
			);
			
			$args = apply_filters( 'tracking_info_args', $args, $order_id );
			
			if(	isset( $_POST['enable_tracking_per_item'] ) &&  $_POST['enable_tracking_per_item'] == 1 ){
				$product_data = json_decode(stripslashes($_POST['productlist']));						
			
				$product_args = array(
					'products_list' => wc_clean( $product_data ),				
				);
							
				$args = array_merge($args,$product_args);	
			}			
				
			$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
			$tracking_item = $wast->add_tracking_item( $order_id, $args );		
			
			if($_POST['change_order_to_shipped'] == 'change_order_to_shipped'){				
				if('completed' == $order->get_status()){
					WC()->mailer()->emails['WC_Email_Customer_Completed_Order']->trigger( $order_id, $order );	
					do_action("send_order_to_trackship", $order_id);
				} else{
					$order->update_status('completed');
				}	
			} elseif($_POST['change_order_to_shipped'] == 'change_order_to_partial_shipped'){	
				$previous_order_status = $order->get_status();
				
				if('partial-shipped' == $previous_order_status){								
					WC()->mailer()->emails['WC_Email_Customer_Partial_Shipped_Order']->trigger( $order_id, $order );	
				}				
				
				$order->update_status('partial-shipped');					
				do_action("send_order_to_trackship", $order_id);
			}			
			
			if(isset($_POST['productlist'])){
				echo 'reload';
				die();
			}
			
			$wast->display_html_tracking_item_for_meta_box( $order_id, $tracking_item );
		}
		
		die();
	}
	
	/**
	 * Order Tracking Save AJAX
	 *
	 * Function for saving tracking items via AJAX
	 */
	public function save_inline_tracking_number() {				
		
		if ( isset( $_POST['tracking_number'] ) &&  $_POST['tracking_provider'] != '' && isset( $_POST['tracking_provider'] ) && strlen( $_POST['tracking_number'] ) > 0 ) {	
			$order_id = wc_clean( $_POST['order_id'] );
			$tracking_product_code = isset($_POST['tracking_product_code']) ? $_POST['tracking_product_code'] : "";
			$args = array(
				'tracking_provider'        => wc_clean( $_POST['tracking_provider'] ),
				'tracking_number'          => wc_clean( $_POST['tracking_number'] ),
				'tracking_product_code'    => wc_clean( $tracking_product_code ),	
				'date_shipped'             => wc_clean( $_POST['date_shipped'] ),
			);
			
			$args = apply_filters( 'tracking_info_args', $args, $order_id );
			
			if(	isset( $_POST['enable_tracking_per_item'] ) &&  $_POST['enable_tracking_per_item'] == 1 ){
				$products_list = array();
				
				foreach($_POST['ASTProduct'] as $product){				
					if($product['qty'] > 0){
						$product_data =  (object) array (
							'product' => $product['product'],
							'qty' => $product['qty'],
						);	
						array_push($products_list,$product_data);								
					}
				}																			
			
				$product_args = array(
					'products_list' => $products_list,				
				);
				$args = array_merge($args,$product_args);
			}												
						
			$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
			$tracking_item = $wast->add_tracking_item( $order_id, $args );	
			
			if($_POST['change_order_to_shipped'] == 'change_order_to_shipped' || $_POST['change_order_to_shipped'] == 'yes'){
				$order = new WC_Order($order_id);
				$order->update_status('completed');					
				do_action("send_order_to_trackship", $order_id);
			} elseif($_POST['change_order_to_shipped'] == 'change_order_to_partial_shipped'){
				$order = new WC_Order($order_id);
				$previous_order_status = $order->get_status();
				
				if('partial-shipped' == $previous_order_status){								
					WC()->mailer()->emails['WC_Email_Customer_Partial_Shipped_Order']->trigger( $order_id, $order );	
				}
				$order->update_status('partial-shipped');					
				do_action("send_order_to_trackship", $order_id);
			}		
		}
		echo 'reload';
		die();
	}
	
	/**
	 * Function for return tracking per item args when save tracking information from single order page
	 */
	public function tracking_info_args_callback( $args, $postdata, $order_id ){
		
		$enable_tracking_per_item = isset( $postdata['enable_tracking_per_item'] ) ? $postdata['enable_tracking_per_item'] : "";
				
		if(	$enable_tracking_per_item == 1 ){
			
			$product_data = json_decode(stripslashes($postdata['productlist']));						
		
			$product_args = array(
				'products_list' => wc_clean( $product_data ),				
			);
						
			$args = array_merge($args,$product_args);	
		}		
		
		return $args;
	}
	
	/**
	 * Function for return tracking per item args when save tracking information from orders page 
	 */
	public function tracking_info_args_inline_callback( $args, $postdata, $order_id ){
		
		$enable_tracking_per_item = isset( $postdata['enable_tracking_per_item'] ) ? $postdata['enable_tracking_per_item'] : "";
				
		if(	$enable_tracking_per_item == 1 ){						
			
			if(isset( $postdata['ASTProduct'] )){	
			
				$products_list = array();
				
				foreach($postdata['ASTProduct'] as $product){				
					if($product['qty'] > 0){
						$product_data =  (object) array (
							'product' => $product['product'],
							'qty' => $product['qty'],
						);	
						array_push($products_list,$product_data);								
					}
				}																			
				
				$product_args = array(
					'products_list' => $products_list,				
				);
				
				$args = array_merge($args,$product_args);
			}
		}		
		
		return $args;
	}	
	
	/*
	* Return args with add tracking per item addon arguments
	*/	
	public function ast_api_create_item_arg_callback( $args, $request ){				
		
		$sku_string = $request['sku'];
		$qty_string = $request['qty'];
				
		$sku_array = explode(",",$sku_string);
		$qty_array = explode(",",$qty_string);				
		
		
		if(isset($request['sku']) && isset($request['qty'])){						
						
			$products_list = array();
				
			foreach( $sku_array as $key => $sku){				
				if($qty_array[$key] > 0){
					
					$product_id = wc_get_product_id_by_sku( $sku );
					
					$product_data =  (object) array (
						'product' => $product_id,
						'qty' => $qty_array[$key],
					);	
					array_push($products_list,$product_data);								
				}
			}																			
			//echo '<pre>';print_r($product_id);echo '</pre>';exit;
			$product_args = array(
				'products_list' => $products_list,				
			);
			
			$args = array_merge( $args, $product_args );
		}
		
		return $args;
	}			
	
	/**	 
	 * Function for adding tracking details for product after tracking number
	 */
	public function ast_after_tracking_number_fun($order_id,$tracking_id){	
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();		
		$tracking_items = $wast->get_tracking_items( $order_id );			
		
		$show_products = array();
		$product_list = array();
		$show = false;
		
		$order = wc_get_order( $order_id );
		$items = $order->get_items();		
		
		foreach ( $items as $item ) {			
			$products[] = (object) array (
				'product' => $item->get_product_id(),
				'qty' => $item->get_quantity(),
			);					
		}				
		
		foreach($tracking_items as $t_item){
			
			if(isset($t_item['products_list'])){
				$product_list[$t_item['tracking_id']] = $t_item['products_list'];
			}
		}								
		
		foreach($tracking_items as $t_item){
			if(isset($product_list[$t_item['tracking_id']])){
				$array_check = ($product_list[$t_item['tracking_id']] == $products);						
				if(empty($t_item['products_list']) || $array_check == 1){
					$show_products[$t_item['tracking_id']] = 0;
				} else{
					$show_products[$t_item['tracking_id']] = 1;
				} 
			}
		}
		
		foreach($show_products as $key => $value){
			if($value == 1){
				$show = true;
				break;
			}
		}
        $show=true;
		if($show){
			foreach($tracking_items as $tracking_item){			
				if($tracking_item['tracking_id'] == $tracking_id){
					if(isset($tracking_item['products_list']) && $tracking_item['products_list'] != ''){	
						foreach($tracking_item['products_list'] as $products){						
							$product = wc_get_product( $products->product );
							if($product){
								$product_name = $product->get_name();
								echo '<span class="tracking_product_list">'.$product_name.' x '.$products->qty.'</span>';
							}
						}
					}	
				}
			}	
		}	
	}
	
	/**	 
	 * Function for show product header in tracking info table
	 */
	public function ast_tracking_email_header_fun($order_id,$th_column_style){
		
		$show_products = array();
		$show = false;
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();		
		
		$tracking_items = $wast->get_tracking_items( $order_id );
		$order = wc_get_order( $order_id );
		
		if(!$order)return;
		
		$items = $order->get_items();
		$products_id = array();
		
		foreach ( $items as $item ) {			
			$products[] = (object) array (
				'product' => $item->get_product_id(),
				'qty' => $item->get_quantity(),
			);					
		}
		
		$product_list_string = implode(',',$products_id);
		$product_list = array();
		
		foreach($tracking_items as $t_item){
			
			if(isset($t_item['products_list'])){
				$product_list[$t_item['tracking_id']] = $t_item['products_list'];
			}
		}	
		
		foreach($tracking_items as $t_item){
			if(isset($product_list[$t_item['tracking_id']])){
				$array_check = ($product_list[$t_item['tracking_id']] == $products); 			
				if(empty($t_item['products_list']) || $array_check == 1){
					$show_products[$t_item['tracking_id']] = 0;
				} else{
					$show_products[$t_item['tracking_id']] = 1;
				} 
			}
		}
		
		foreach($show_products as $key => $value){
			if($value == 1){
				$show = true;
				break;
			}
		}
		
		if($show){
			echo '<th class="product-details" style="'.$th_column_style.'">' . __( 'Product', 'woo-advanced-shipment-tracking' ) . '</th>';
		}
	}
	
	/**	 
	 * Function for show tracking info for product based in traking info table
	 */
	public function ast_tracking_email_body_fun($order_id,$tracking_item,$td_column_style){			
		
		$show_products = array();
		$show = false;
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();		
		$wcast_customizer_settings = new wcast_initialise_customizer_settings();
		
		$tracking_items = $wast->get_tracking_items( $order_id );
		$order = wc_get_order( $order_id );
		
		if(!$order)return;
		
		$items = $order->get_items();
		$products_id = array();
		
		foreach ( $items as $item ) {			
			$products[] = (object) array (
				'product' => $item->get_product_id(),
				'qty' => $item->get_quantity(),
			);					
		}
		
		$product_list_string = implode(',',$products_id);
		$product_list = array();
		
		foreach($tracking_items as $t_item){
			
			if(isset($t_item['products_list'])){
				$product_list[$t_item['tracking_id']] = $t_item['products_list'];
			}
		}								
		
		foreach($tracking_items as $t_item){
			if(isset($product_list[$t_item['tracking_id']])){
				$array_check = ($product_list[$t_item['tracking_id']] == $products);						
				if(empty($t_item['products_list']) || $array_check == 1){
					$show_products[$t_item['tracking_id']] = 0;
				} else{
					$show_products[$t_item['tracking_id']] = 1;
				} 
			}
		}
		
		foreach($show_products as $key => $value){
			if($value == 1){
				$show = true;
				break;
			}
		}
		
		$table_content_font_size = $wast->get_option_value_from_array('tracking_info_settings','table_content_font_size',$wcast_customizer_settings->defaults['table_content_font_size']);
	
		if($show){
			echo '<td class="product-details" style="'.$td_column_style.'"><ul style="padding-left: 0px;margin: 0;text-align: left;">';
			
			if(isset($tracking_item['products_list'])){								
				foreach($tracking_item['products_list'] as $products){
					$product = wc_get_product( $products->product );					
					if($product){	
						$product_name = $product->get_name();
						echo '<li style="font-size: '.$table_content_font_size.'px;list-style: none;">'.$product_name.' x '.$products->qty.'</li>';
					}
				}
			}	
			echo '</ul></td>';
		}
	}
	
	/**	 
	 * Function for show tracking info for product based in simple traking list
	 */	
	public function ast_tracking_simple_list_email_body_fun($order_id,$tracking_item){			
		
		$show_products = array();
		$show = false;
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();		
		$wcast_customizer_settings = new wcast_initialise_customizer_settings();
		
		$tracking_items = $wast->get_tracking_items( $order_id );
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		$products_id = array();
		
		foreach ( $items as $item ) {			
			$products[] = (object) array (
				'product' => $item->get_product_id(),
				'qty' => $item->get_quantity(),
			);					
		}
		
		$product_list_string = implode(',',$products_id);
		$product_list = array();
		
		foreach($tracking_items as $t_item){
			
			if(isset($t_item['products_list'])){
				$product_list[$t_item['tracking_id']] = $t_item['products_list'];
			}
		}								
		
		foreach($tracking_items as $t_item){
			if(isset($product_list[$t_item['tracking_id']])){
				$array_check = ($product_list[$t_item['tracking_id']] == $products);						
				if(empty($t_item['products_list']) || $array_check == 1){
					$show_products[$t_item['tracking_id']] = 0;
				} else{
					$show_products[$t_item['tracking_id']] = 1;
				} 
			}
		}
		
		foreach($show_products as $key => $value){
			if($value == 1){
				$show = true;
				break;
			}
		}		
		
		$simple_provider_font_size = $wast->get_option_value_from_array('tracking_info_settings','simple_provider_font_size',$wcast_customizer_settings->defaults['simple_provider_font_size']);
		
		if($show){
			echo '<ul class="product_list_ul">';
			
			if(isset($tracking_item['products_list'])){								
				foreach($tracking_item['products_list'] as $products){
					$product = wc_get_product( $products->product );
					if($product){
						$product_name = $product->get_name();
						echo '<li style="font-size: '.$simple_provider_font_size.'px;list-style: none;">'.$product_name.' x '.$products->qty.'</li>';
					}
				}
			}	
			echo '</ul>';
		}
	}
	
	/**	 
	 * Function for show product column for my account tracking info table
	 */
	public function ast_tracking_my_acoount_header_fun($order_id,$th_column_style){		
	
		$show_products = array();
		$show = false;
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();		
		
		$tracking_items = $wast->get_tracking_items( $order_id );
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		$products_id = array();
		
		foreach ( $items as $item ) {			
			$products[] = (object) array (
				'product' => $item->get_product_id(),
				'qty' => $item->get_quantity(),
			);					
		}
		
		$product_list_string = implode(',',$products_id);
		$product_list = array();
		
		foreach($tracking_items as $t_item){
			
			if(isset($t_item['products_list'])){
				$product_list[$t_item['tracking_id']] = $t_item['products_list'];
			}
		}		
		
		foreach($tracking_items as $t_item){
			if(isset($product_list[$t_item['tracking_id']])){
				$array_check = ($product_list[$t_item['tracking_id']] == $products); 			
				if(empty($t_item['products_list']) || $array_check == 1){
					$show_products[$t_item['tracking_id']] = 0;
				} else{
					$show_products[$t_item['tracking_id']] = 1;
				} 
			}
		}
		
		foreach($show_products as $key => $value){
			if($value == 1){
				$show = true;
				break;
			}
		}
						
		if($show){
			echo '<th class="product-details" style="'.$th_column_style.'">' . __( 'Product', 'woo-advanced-shipment-tracking' ) . '</th>';
		}
	}
	
	/**	 
	 * Function for show tracking info in my account tracking info table
	 */
	public function ast_tracking_my_account_body_fun($order_id,$tracking_item,$td_column_style){								
		
		$show_products = array();
		$show = false;
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();		
		
		$tracking_items = $wast->get_tracking_items( $order_id );
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		$products_id = array();
		
		foreach ( $items as $item ) {			
			$products[] = (object) array (
				'product' => $item->get_product_id(),
				'qty' => $item->get_quantity(),
			);					
		}
				
		$product_list = array();
		
		foreach($tracking_items as $t_item){
			
			if(isset($t_item['products_list'])){
				$product_list[$t_item['tracking_id']] = $t_item['products_list'];
			}
		}		
		
		foreach($tracking_items as $t_item){
			if(isset($product_list[$t_item['tracking_id']])){
				$array_check = ($product_list[$t_item['tracking_id']] == $products); 			
				if(empty($t_item['products_list']) || $array_check == 1){
					$show_products[$t_item['tracking_id']] = 0;
				} else{
					$show_products[$t_item['tracking_id']] = 1;
				} 
			}
		}
		
		foreach($show_products as $key => $value){
			if($value == 1){
				$show = true;
				break;
			}
		}		

		if($show){
			echo '<td class="product-details" style="'.$td_column_style.'"><ul style="padding-left: 10px;margin: 0;text-align: left;">';
			if(isset($tracking_item['products_list'])){								
				foreach($tracking_item['products_list'] as $products){					
					$product = wc_get_product( $products->product );
					$product_name = $product->get_name();
					echo '<li style="font-size: 14px;list-style: none;">'.$product_name.' - '.$products->qty.'</li>';					
				}
			}			
			echo '</ul></td>';
		}
	}
	
	/**	 
	 * Function for show tracking info before order meta
	 */
	public function before_order_itemmeta( $item_id, $item, $_product ){			
		$order_id = $item->get_order_id();		
		
		if(!$_product){
			return;
		}
		
		$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();		
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();				
		$tracking_items = $wast->get_tracking_items( $order_id );
				
		$show_products = array();
		$product_list = array();
		$show = false;
		
		$order = wc_get_order( $order_id );
		$items = $order->get_items();		
		
		foreach ( $items as $item ) {			
			$products[] = (object) array (
				'product' => $item->get_product_id(),
				'qty' => $item->get_quantity(),
			);					
		}				
		
		foreach($tracking_items as $t_item){
			
			if(isset($t_item['products_list'])){
				$product_list[$t_item['tracking_id']] = $t_item['products_list'];
			}
		}								
		
		foreach($tracking_items as $t_item){
			if(isset($product_list[$t_item['tracking_id']])){
				$array_check = ($product_list[$t_item['tracking_id']] == $products);						
				if(empty($t_item['products_list']) || $array_check == 1){
					$show_products[$t_item['tracking_id']] = 0;
				} else{
					$show_products[$t_item['tracking_id']] = 1;
				} 
			}
		}
		
		foreach($show_products as $key => $value){
			if($value == 1){
				$show = true;
				break;
			}
		}
		
//		if(!$show){
//			return;
//		} 
		?>
		<style>
		.before-meta-tracking-content {
			background: #efefef none repeat scroll 0 0;
			padding: 10px;
			position: relative;
			margin: 5px 0;
		}
		</style>	
		<?php
		echo '<div id="tracking-items">';
			foreach($tracking_items as $tracking_item){
				$formatted = $wast->get_formatted_tracking_item( $order_id, $tracking_item ); 				
				if(isset($tracking_item['products_list'])){
					if(in_array($product_id, array_column($tracking_item['products_list'], 'product'))) { ?>
						<div class="before-meta-tracking-content">
							<div class="tracking-content-div">
								<strong><?php echo esc_html( $formatted['formatted_tracking_provider'] ); ?></strong>						
								<?php if ( strlen( $formatted['formatted_tracking_link'] ) > 0 ) { ?>
									- <?php 
									$url = str_replace('%number%',$tracking_item['tracking_number'],$formatted['formatted_tracking_link']);
									echo sprintf( '<a href="%s" target="_blank" title="' . esc_attr( __( 'Track Shipment', 'woo-advanced-shipment-tracking' ) ) . '">' . __( $tracking_item['tracking_number'] ) . '</a>', esc_url( $url ) ); ?>
								<?php } else{ ?>
									<span> - <?php echo $tracking_item['tracking_number']; ?></span>
								<?php } ?>
							</div>					
							<?php 
							foreach($tracking_item['products_list'] as $products){
								if($products->product == $product_id){	
									$product = wc_get_product( $products->product );
									if($product){
										$product_name = $product->get_name();
										echo '<span class="tracking_product_list">'.$product_name.' x '.$products->qty.'</span>';
									}
								}
							} ?>
						</div>		
						<?php
					} 
				}	
			}
		echo '</div>';		
	}
	
	/**	 
	 * Function for show tracking info after order meta
	 */
	public function action_woocommerce_order_item_meta_end( $item_id, $item, $order ){
		$tracking_info_settings = get_option('tracking_info_settings');		
		
		if(isset($tracking_info_settings['tracking_per_item_hide_tracking_order_line_items']) && $tracking_info_settings['tracking_per_item_hide_tracking_order_line_items'] == 1){
			return;
		}
		
		$order_id = $order->get_id();
		
		$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();		
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();				
		$tracking_items = $wast->get_tracking_items( $order_id );
		
		$show_products = array();
		$product_list = array();
		$show = false;
		
		$order = wc_get_order( $order_id );
		
		if(!$order)return;
		
		$items = $order->get_items();		
		
		foreach ( $items as $item ) {			
			$products[] = (object) array (
				'product' => $item->get_product_id(),
				'qty' => $item->get_quantity(),
			);					
		}				
		
		foreach($tracking_items as $t_item){
			
			if(isset($t_item['products_list'])){
				$product_list[$t_item['tracking_id']] = $t_item['products_list'];
			}
		}								
		
		foreach($tracking_items as $t_item){
			if(isset($product_list[$t_item['tracking_id']])){
				$array_check = ($product_list[$t_item['tracking_id']] == $products);						
				if(empty($t_item['products_list']) || $array_check == 1){
					$show_products[$t_item['tracking_id']] = 0;
				} else{
					$show_products[$t_item['tracking_id']] = 1;
				} 
			}
		}
		
		foreach($show_products as $key => $value){
			if($value == 1){
				$show = true;
				break;
			}
		}
		
		if(!$show){
			return;
		}
		?>
		<style>
		.before-meta-tracking-content {
			background: #efefef none repeat scroll 0 0;
			padding: 10px;
			position: relative;
			margin: 5px 0;
		}
		</style>	
		<?php
		echo '<div id="tracking-items">';
			foreach($tracking_items as $tracking_item){
				$formatted = $wast->get_formatted_tracking_item( $order_id, $tracking_item ); 				
				if(isset($tracking_item['products_list'])){
					if(in_array($product_id, array_column($tracking_item['products_list'], 'product'))) { ?>						
						<div class="before-meta-tracking-content">
							<div class="tracking-content-div">
								<strong><?php echo esc_html( $formatted['formatted_tracking_provider'] ); ?></strong><?php if ( strlen( $formatted['formatted_tracking_link'] ) > 0 ) { ?> - <?php 
									$url = str_replace('%number%',$tracking_item['tracking_number'],$formatted['formatted_tracking_link']);
									echo sprintf( '<a href="%s" target="_blank" title="' . esc_attr( __( 'Track Shipment', 'woo-advanced-shipment-tracking' ) ) . '">' . __( $tracking_item['tracking_number'] ) . '</a>', esc_url( $url ) ); ?>
								<?php } else{ ?>
									<span> - <?php echo $tracking_item['tracking_number']; ?></span>
								<?php } ?>
							</div>					
							<?php 
							foreach($tracking_item['products_list'] as $products){								
								if($products->product == $product_id){	
									$product = wc_get_product( $products->product );
									if($product){
										$product_name = $product->get_name();
										echo '<div class="tracking_product_list">'.$product_name.' x '.$products->qty.'</div>';
									}
								}
							} ?>
						</div>		
						<?php
					} 
				}	
			}
		echo '</div>';	
	}

	public function tracking_per_item_addon_license_form(){ 
		$ast_admin = WC_Advanced_Shipment_Tracking_Admin::get_instance();			
	?>		
		<form method="post" id="wc_ast_addons_form" class="addons_inner_container" action="" enctype="multipart/form-data">
			<table class="ast-license-form wp-list-table widefat fixed">
				<tbody>
					<tr class="wp-list-table__row is-ext-header">
						<td class="wp-list-table__ext-details">
							<div class="wp-list-table__ext-title">
								Tracking Per Item Add-on	
							</div>
	
							<div class="wp-list-table__ext-description">
								<input class="input-text regular-input " type="text" name="license_key" id="license_key" value="<?php echo $this->license->get_license_key();?>">
							</div>
						</td>
						<td class="wp-list-table__ext-actions">
							<div class="submit">	
								<?php														
									if( $this->license->get_license_status() ){ ?>
										<a href="https://www.zorem.com/my-account/subscriptions/" class="button-primary btn_green2 btn_large" target="blank"><?php _e('Active','woo-advanced-shipment-tracking');?> <span class="dashicons dashicons-yes"></span></a>
										<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Deactivate"><?php _e('Deactivate','woo-advanced-shipment-tracking');?></button>
									<?php } else{ ?>
										<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Save changes"><?php _e('Activate','woo-advanced-shipment-tracking');?></button>
									<?php } 
								?>											
								<p class="pesan" id="ast_tpi_license_message"></p>
								<div class="spinner"></div>								
								<?php wp_nonce_field( 'wc_ast_addons_form', 'wc_ast_addons_form_nonce' );?>
								<input type="hidden" id="ast-license-action" name="action" value="<?=$this->license->get_license_status() ? $this->license->get_item_code().'_license_deactivate':$this->license->get_item_code().'_license_activate';?>" />
							</div>		
						</td>
					</tr>
				</tbody>
			</table>
		</form>		
		
	<?php }		
	
	/*
	* database update
	*/
	public function update_database_check(){
		
		if ( is_admin() ){			
			
			if(version_compare(get_option( 'tracking_per_item_addon_db_version' ),'1.3.2', '<') ){
				$license_key = get_option( 'ast_product_license_key', false);
				$status = get_option( 'ast_product_license_status', false);
				$instance_id = get_option( 'ast_per_product_instance_id', false);				
				$this->license->set_license_key($license_key);
				$this->license->set_license_status($status);
				$this->license->set_instance_id($instance_id);
				update_option( 'tracking_per_item_addon_db_version', '1.3.2');				
			}
			
			if( isset($_GET['page']) && $_GET['page'] == 'woocommerce-advanced-shipment-tracking' ) {						
				$this->license->check_license_valid();
			}					
		}
	}
	
	/*
	* License notice
	*/
	function tracking_per_item_licence_notice() { 
		$class = 'notice notice-error';		
		$message = sprintf(__( 'Opps! your <strong>Tracking Per Item Add-on for AST</strong> licence key is not activated. To buy license %sclick here%s to activate it.', 'woo-advanced-shipment-tracking' ),'<a href="'.admin_url( '/admin.php?page=woocommerce-advanced-shipment-tracking&tab=addons' ).'">','</a>');
		echo '<div class="notice notice-error"><p>'.$message.'</p></div>';	
	}
	
	/*
	* Display TPI Product details in TrackShip Tracking Page
	*/
	public function trackship_tracking_header_before_callback( $order_id, $tracker, $tracking_provider, $tracking_number ){
		$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();				
		$tracking_items = $ast->get_tracking_items( $order_id ); 
		foreach( $tracking_items as $tracking_item ){
			if($tracking_item['tracking_number'] == $tracking_number){
				//echo '<pre>';print_r($tracking_item);echo '</pre>';
				if(!isset($tracking_item['products_list'])){ return; }
			}
		}	
		?>
		<h4 class="h4-heading tpi_products_heading"><?php _e( 'Products', 'woocommerce' ); ?></h4>			
		<ul class="tpi_product_tracking_ul">
		<?php
		foreach( $tracking_items as $tracking_item ){
			if($tracking_item['tracking_number'] == $tracking_number){
				if(isset($tracking_item['products_list'])){
					foreach((array)$tracking_item['products_list'] as $products){
						if( $products->product ){	
							$product = wc_get_product( $products->product );
							if($product){
								$product_name = $product->get_name();
								echo '<li><a target="_blank" href='.get_permalink( $products->product ).'>'.$product_name.'</a> x '.$products->qty.'</li>';
							}
						}
					}
				}
			}
		} ?>
		</ul>
		<style>
		ul.tpi_product_tracking_ul {
			list-style: none;
		}
		ul.tpi_product_tracking_ul li{
			font-size: 14px;
			margin: 0;
		}
		.tpi_products_heading{
			margin-top: -10px;
		}
		</style>
		<?php
	}
}

/**
 * Returns an instance of zorem_woocommerce_advanced_shipment_tracking.
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 * @return zorem_woocommerce_advanced_shipment_tracking
*/
function wc_advanced_shipment_tracking_by_products() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new ast_woo_advanced_shipment_tracking_by_products();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
wc_advanced_shipment_tracking_by_products();