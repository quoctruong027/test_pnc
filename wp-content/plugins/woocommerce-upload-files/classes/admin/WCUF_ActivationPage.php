<?php 
class WCUF_ActivationPage
{
	var $page;
	var $page_slug;
	var $plugin_name;
	var $plugin_slug;
	var $plugin_id;
	var $plugin_path;
	
	//rplc: woocommerce-files-upload, wcuf_ , menu icon ('dashicons-images-alt2')
	public function __construct($page_slug, $plugin_name, $plugin_slug, $plugin_id, $plugin_path)
	{
		$this->page_slug = $page_slug;
		$this->plugin_name = $plugin_name;
		$this->plugin_slug = $plugin_slug;
		$this->plugin_id = $plugin_id;
		$this->plugin_path = $plugin_path;
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action( 'wp_ajax_vanquish_activation_'.$this->plugin_id, array($this, 'process_activation') );
		//add_filter('allowed_http_origins', array($this, 'add_allowed_origins'));
		//add_action('wp', array( &$this,'add_headers_meta'));
		
		$this->add_page();
	}
	function add_allowed_origins($origins) 
	{
		$origins[] = 'https://vanquishplugins.com';
		return $origins;
	}
	function add_headers_meta()
	{
		header("Access-Control-Allow-Origin: *");
	}
	public function process_activation()
	{
		$id = isset($_POST['id']) ? $_POST['id'] : 0;
		$domain = isset($_POST['domain']) ? $_POST['domain'] : 'none';
		if($id != 0 && $domain != 'none')
		{
			update_option("_".$id, md5(wcuf_giveHost($domain)));
		}
		wp_die();
	}
	public function add_page($cap = "manage_woocommerce" )
	{
		if(defined('DOING_AJAX') && DOING_AJAX)
			return;
		
		global $wcuf_notice;
		
		if(!$wcuf_notice)
			$this->page = add_submenu_page( null,
											__($this->plugin_name.' Activator', 'woocommerce-files-upload'), 
											__($this->plugin_name.' Activator', 'woocommerce-files-upload'), 
											  $cap, 
											  $this->page_slug, 
											  array($this, 'render_page')); 
			
		else 
		{
			$place = wcuf_get_free_menu_position(59 , .1);
			$this->page = add_menu_page( $this->plugin_name, $this->plugin_name, 
											$cap, 
											$this->page_slug, 
											array($this, 'render_page'), 
											'dashicons-images-alt2', 
											(string)$place);
		}
		add_action('load-'.$this->page,  array($this,'page_actions'),9);
		add_action('admin_footer-'.$this->page,array($this,'footer_scripts'));
	}
	function footer_scripts(){
		?>
		<script> postboxes.add_postbox_toggles(pagenow);</script>
		<?php
	}
	
	function page_actions()
	{
		do_action('add_meta_boxes_'.$this->page, null);
		do_action('add_meta_boxes', $this->page, null);
	}
	public function render_page()
	{
		global $pagenow;
		
		add_screen_option('layout_columns', array('max' => 1, 'default' => 1) );
		
		wp_register_script('vanquish-activator', $this->plugin_path.'/js/vendor/vanquish/activator.js', array('jquery'));
		 $js_settings = array(
				'purchase_code_invalid' => __( 'Purchase code is invalid!', 'woocommerce-files-upload' ),
				'buyer_invalid' => __( 'Buyer name is invalid!', 'woocommerce-files-upload' ),
				'item_id_invalid' => __( 'Item id is invalid!', 'woocommerce-files-upload' ),
				'num_domain_reached' => __( 'Max number of domains reached! You have to purchase a new license. The current license has been activated in the following domains: ', 'woocommerce-files-upload' ),
				'status_default_message' => __( 'Verifing, please wait...', 'woocommerce-files-upload' ),
				'db_error' => __( 'There was an error while verifing the code. Please retry in few minutes!', 'woocommerce-files-upload' ),
				'purchase_code_valid' => __( 'Activation successfully completed!', 'woocommerce-files-upload' ),
				'empty_fields_error' => __( 'Buyer and Purchase code fields must be filled!', 'woocommerce-files-upload' ),
				'verifier_url' => 'https://vanquishplugins.com/activator/verify.php'
			);
		wp_localize_script( 'vanquish-activator', 'vanquish_activator_settings', $js_settings );
		wp_enqueue_script('vanquish-activator'); 
		wp_enqueue_script('postbox');
		
		
		wp_enqueue_style('vanquish-activator',  $this->plugin_path.'/css/vendor/vanquish/activator.css');
		
		?>
		<div class="wrap">
			 <?php //screen_icon(); ?>
			<h2><?php esc_html_e($this->plugin_name.' Activator','woocommerce-files-upload'); ?></h2>
	
			<form id="post"  method="post">
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-<?php echo 1 /* 1 == get_current_screen()->get_columns() ? '1' : '2' */; ?>">
						<div id="post-body-content">
						</div>
						
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes($this->plugin_slug.'-activator','side',null); ?>
						</div>
						
						<div id="postbox-container-2" class="postbox-container">
							  <?php do_meta_boxes($this->plugin_slug.'-activator','normal',null); ?>
							  <?php do_meta_boxes($this->plugin_slug.'-activator','advanced',null); ?>
							  
						</div> 
					</div> <!-- #post-body -->
				</div> <!-- #poststuff -->
				
			</form>
		</div> <!-- .wrap -->
		<?php 
	}
	
	function add_meta_boxes()
	{
		
		 add_meta_box( 'vanquish_activation', 
					__('Activation','woocommerce-files-upload'), 
					array($this, 'render_product_fields_meta_box'), 
					$this->plugin_slug.'-activator', 
					'normal' //side
			); 
			
		/* add_meta_box( 'wcuf_save_data', 
					__('Save','woocommerce-files-upload'), 
					array($this, 'render_save_button_meta_box'), 
					'woocommerce-files-upload', 
					'side' //side
			); 	 */
	}
	function render_product_fields_meta_box()
	{
		$domain = $_SERVER['SERVER_NAME'];
		$result = get_option("_".$this->plugin_id);
		$result = !$result || ($result != md5(wcuf_giveHost($domain)) && $result != md5($domain)  && $result != md5(wcuf_giveHost_deprecated($domain)));
		?>
			<div id="activator_main_container">
				<?php if($result): ?>
					<div id="activation_fields_container">
						<p class="activatior_description">
							<?php _e( 'The plugin can be activate in only <strong>two</strong> domains and they cannot be unregistered. For each activated domain, you can reactivate <strong>unlimited</strong> times (including <strong>subdomains</strong> and <strong>subfolders</strong>). The "localhost" domain will not consume activations. Please enter the following data and hit the activation button', 'woocommerce-files-upload' ); ?>
						</p>
						<div class="fields_blocks_container">
							<div class="inline-container">
								<input type="hidden" id="domain" value="<?php echo $domain;?>"></input>
								<input type="hidden" id="item_id" value="<?php echo $this->plugin_id;?>"></input>
								<label><?php _e( 'Buyer', 'woocommerce-files-upload' ); ?></label>
								<p  class="field_description"><?php _e( 'Insert the Envato username used to purchase the plugin.', 'woocommerce-files-upload' ); ?></p>
								<input type="text" value="" id="input_buyer" class="input_field" placeholder="<?php _e( 'Example: vanquish', 'woocommerce-files-upload' ); ?>"></input>
							</div>
							<div class="inline-container">
								<label><?php _e( 'Purchase code', 'woocommerce-files-upload' ); ?></label>
								<p  class="field_description"><?php _e( 'Insert the purchase code. It can be downloaded from your CodeCanyon "Downloads" profile page.', 'woocommerce-files-upload' ); ?></p>
								<input type="text" value="" class="input_field" id="input_purchase_code" placeholder="<?php _e( 'Example: 7d7c3rt8-f512-227c-8c98-fc53c3b212fe', 'woocommerce-files-upload' ); ?>"></input>
							</div>
							<button class="button button-primary" id="activation_button"><?php _e( 'Activate', 'woocommerce-files-upload' ); ?></button>
						</div>
						<div id="status"><?php _e( 'Verifing, please wait...', 'woocommerce-files-upload' ); ?></div>
					</div>
				<?php else: ?>
					<p class="activatior_description"><?php _e( 'The plugin has been successfully activated!', 'woocommerce-files-upload' ); ?></p>
				<?php endif; ?>
			</div>
		<?php
	}
	
	function render_save_button_meta_box()
	{
		/* $screen = get_current_screen();
		if(!$screen || $screen->base != "toplevel_page_woocommerce-files-upload")
			return;
		submit_button( __( 'Save', 'woocommerce-files-upload' ),
						'primary',
						'submit'
					); */
	}
}
?>